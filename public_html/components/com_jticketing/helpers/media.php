<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.image.image');

/**
 * main helper class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingMediaHelper
{
	/**
	 * Constructor.
	 *
	 * @see     JController
	 * @since   1.8
	 */
	public function __construct()
	{
	}

	/**
	 * Method for uploadEventImage
	 *
	 * @param   string  $evenId  eventid
	 *
	 * @return  void
	 */
	public function uploadEventImage($evenId)
	{
		$app    = JFactory::getApplication();
		$db     = JFactory::getDBO();
		$params = JComponentHelper::getParams('com_jticketing');

		// Support for file field: image
		if (isset($_FILES['jform']['name']['image']))
		{
			$file = $_FILES['jform'];

			// Check if the server found any error.
			$fileError = $file['error']['image'];
			$message   = '';

			if ($fileError > 0 && $fileError != 4)
			{
				switch ($fileError)
				{
					case 1:
						$message = JText::_('File size exceeds allowed by the server');
						break;
					case 2:
						$message = JText::_('File size exceeds allowed by the html form');
						break;
					case 3:
						$message = JText::_('Partial upload error');
						break;
				}

				if ($message != '')
				{
					JError::raiseWarning(500, $message);

					return false;
				}
			}
			elseif ($fileError == 4)
			{
				$query = $db->getQuery(true);
				$query->select('image');
				$query->from($db->quoteName('#__jticketing_events'));
				$query->where('id = ' . "'" . $evenId . "'");
				$db->setQuery($query);
				$existingImage = $db->loadResult();

				if (empty($existingImage))
				{
					$filename = 'default-event-image.png';

					// Save event id into integration xref table.
					$obj        = new stdclass;
					$obj->id    = $evenId;
					$obj->image = $filename;

					if (!$db->updateObject('#__jticketing_events', $obj, 'id'))
					{
						$app->enqueueMessage($db->stderr());

						return false;
					}
				}
			}
			else
			{
				$file_type        = $file['type']['image'];
				$media_type_group = $this->check_media_type_group($file_type);

				if (!$media_type_group['allowed'])
				{
					$message = JText::_('COM_JTICKETING_FILE_TYPE_NOT_ALLOWED');
					$message .= JText::_('COM_JTICKETING_FILE_ALLOWED_FILE_TYPES_ARE') . str_replace("image/", "", $media_type_group['allowed_file_types']);

					JError::raiseWarning(500, $message);

					// Tweak *important
					$app->setUserState('com_jticketing.edit.event.id', $evenId);

					return false;
				}

				// Replace any special characters in the filename
				$filename    = explode('.', $file['name']['image']);
				$filename[0] = preg_replace("/[^A-Za-z0-9]/i", "-", $filename[0]);

				// Add Timestamp MD5 to avoid overwriting
				$filename   = md5(time()) . '-' . implode('.', $filename);
				$uploadPath = JPATH_SITE . '/media/com_jticketing/images/';
				$uploadPath = $uploadPath . $filename;
				$fileTemp   = $file['tmp_name']['image'];

				if (!JFile::exists($uploadPath))
				{
					if (!JFile::upload($fileTemp, $uploadPath))
					{
						JError::raiseWarning(500, 'Error moving file');

						return false;
					}
				}

				$array['image'] = $filename;

				// Save event id into integration xref table.
				$obj              = new stdclass;
				$obj->id          = $evenId;
				$obj->image       = $filename;
				$basePath         = '/media/com_jticketing/images/';
				$imagePath        = JPATH_SITE . $basePath . $obj->image;
				$image_resolution = $params->get('image_resolution', '600', 'INT');

				// Create our object
				$image = new JImage($imagePath);

				// Resize the file as a new object
				$resizedImage = $image->resize($image_resolution, $image_resolution, true);

				// Delete the original image
				JFile::delete($imagePath);

				// Save the resized image
				$resizedImage->toFile(JPATH_SITE . $basePath . $obj->image);

				if ($filename)
				{
					if (!$db->updateObject('#__jticketing_events', $obj, 'id'))
					{
						echo $db->stderr();

						return false;
					}
				}
			}

			return true;
		}
	}

	/**
	 * Method for check_media_type_group
	 *
	 * @param   string  $file_type  file_type
	 *
	 * @return  void
	 */
	public function check_media_type_group($file_type)
	{
		$allowed_media_types = array(
			'image' => array(
				'image/png',
				'image/jpeg',
				'image/pjpeg',
				'image/jpeg',
				'image/pjpeg',
				'image/jpeg',
				'image/pjpeg'
			)
		);

		$media_type_group = '';
		$flag             = 0;

		foreach ($allowed_media_types as $key => $value)
		{
			if (in_array($file_type, $value))
			{
				$media_type_group = $key;
				$flag             = 1;
				break;
			}
		}

		$this->media_type       = $file_type;
		$this->media_type_group = $media_type_group;

		$return['media_type']       = $file_type;
		$return['media_type_group'] = $media_type_group;

		// If file type is not allowed.
		if (!$flag)
		{
			$return['allowed']            = 0;
			$return['allowed_file_types'] = implode(",", $allowed_media_types['image']);

			return $return;
		}

		// If file type is allowed.
		$return['allowed'] = 1;

		return $return;
	}

	/**
	 * Get Video Id From embed url
	 *
	 * @param   STRING  $videoProvider  Video Provider
	 * @param   INT     $videoEmbedUrl  Video embed url
	 *
	 * @return  INT  Video Id
	 */
	public static function videoId($videoProvider = null, $videoEmbedUrl = null)
	{
		$videoId = null;

		if ($videoProvider)
		{
			switch ($videoProvider)
			{
				// For video provider youtube & vimeo
				case 'youtube':
				case 'vimeo':
					// Get vimeo video ID from embed url, after explode in array 4th index contain actual video id
					$explodedUrl = explode('/', $videoEmbedUrl);

					if (!empty($explodedUrl))
					{
						$videoId = end($explodedUrl);
					}
				break;

				// Other video provider than above
				default:
						// For future
				break;
			}
		}

		return $videoId;
	}

	/**
	 * Get Video Thumbnail source
	 *
	 * @param   STRING  $videoProvider  Video Provider
	 * @param   INT     $videoId        Video Id
	 *
	 * @return  Object Video thumbnail
	 */
	public static function videoThumbnail($videoProvider, $videoId)
	{
		$videoId = explode('?', $videoId);

		switch ($videoProvider)
		{
			// For video provider youtube
			case 'youtube':
				// Get youtube video ID from embed url, after explode in array 4th index contain actual video id
				$thumbSrc = 'https://img.youtube.com/vi/' . $videoId[0] . '/sddefault.jpg';
				break;

			// For video provider vimeo
			case 'vimeo':
				// Get vimeo video ID from embed url, after explode in array 4th index contain actual video id
				$hash     = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$videoId[0].php"));
				$thumbSrc = $hash[0]['thumbnail_medium'];
				break;

			// Other video provider than above
			default:
				$thumbSrc = JUri::root(true) . '/media/com_jticketing/images/no_thumb.png';
				break;
		}

		return $thumbSrc;
	}
}
