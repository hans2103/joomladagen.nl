<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjlms
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Shika is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access.
defined('_JEXEC') or die;


jimport('joomla.application.component.modeladmin');

/**
 * Methods supporting a jticketing media.
 *
 * @since  2.0.0
 */
class JticketingModelMedia extends JModelAdmin
{
	private $fileStorage = 'local';

	private $fileAccess = 'public';

	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		$jtParams = JComponentHelper::getParams('com_jticketing');
		$this->storagePath = $jtParams->get('jticketing_media_upload_path', '/media/com_jticketing/events/');
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return	JTable	A database object
	 *
	 * @since	2.0
	 */
	public function getTable($type = 'Media', $prefix = 'JticketingTable', $config = array())
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return JTable::getInstance($type, $prefix, $config);
		}
		else
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

			return JTable::getInstance($type, $prefix, $config);
		}
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.media', 'media', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  The user id on success, false on failure.
	 *
	 * @since  2.0
	 */
	public function save($data)
	{
		$mediaData = array();

		if ($data)
		{
			if (isset($data['upload_type']) && $data['upload_type'] == "link")
			{
				$mediaData = $this->uploadLink($data);
			}
			elseif (isset($data['upload_type']) && $data['upload_type'] == "move")
			{
				$mediaData = $this->moveFile($data);
			}
			else
			{
				$mediaData = $this->uploadFile($data);
			}
		}

		if ($mediaData)
		{
			$mediaData['storage'] = $this->fileStorage;
			$mediaData['created_by'] = isset($data['created_by']) ? $data['created_by'] : JFactory::getUser()->id;
			$mediaData['created_date'] = JFactory::getDate()->toSql();
			$mediaData['access'] = $this->fileAccess;
			$mediaData['params'] = '';

			if (parent::save($mediaData))
			{
				$mediaData['id'] = $this->getState($this->getName() . '.id');
				$mediaType = explode(".", $mediaData['type']);
				$mediaPath = JUri::root() . $this->storagePath;

				if ($mediaType[0] == 'image')
				{
					$mediaData['media'] = $mediaPath . '/images/' . $mediaData['source'];
					$mediaData['media_s'] = $mediaPath . '/images/S_' . $mediaData['source'];
					$mediaData['media_m'] = $mediaPath . '/images/M_' . $mediaData['source'];
					$mediaData['media_l'] = $mediaPath . '/images/L_' . $mediaData['source'];
				}
				elseif ($mediaType[0] == 'video')
				{
					if ($mediaType[1] == 'youtube')
					{
						$mediaData['media'] = $mediaData['source'];
					}
					else
					{
						$mediaData['media'] = $mediaPath . '/videos/' . $mediaData['source'];
					}
				}
				elseif ($mediaType[0] == 'application')
				{
					$mediaData['media'] = $mediaPath . '/applications/' . $mediaData['source'];
				}
				elseif ($mediaType[0] == 'audio')
				{
					$mediaData['media'] = $mediaPath . '/audios/' . $mediaData['source'];
				}

				return $mediaData;
			}
		}

		return;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 *
	 * @since	2.0
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			$jtParams = JComponentHelper::getParams('com_jticketing');
			$uploadPath = $jtParams->get('small_width');
			$mediaType = explode(".", $item->type);
			$mediaPath = JUri::root() . $item->path;
			$item->media = '';

			if ($mediaType[0] == 'image')
			{
				$item->media = $mediaPath . '/images/' . $item->source;
				$item->media_s = $mediaPath . '/images/S_' . $item->source;
				$item->media_m = $mediaPath . '/images/M_' . $item->source;
				$item->media_l = $mediaPath . '/images/L_' . $item->source;
			}
			elseif ($mediaType[0] == 'video')
			{
				if ($mediaType[1] == 'youtube')
				{
					$item->media = $item->source;
				}
				else
				{
					$item->media = $mediaPath . '/videos/' . $item->source;
				}
			}
			elseif ($mediaType[0] == 'application')
			{
				$item->media = $mediaPath . '/applications/' . $item->source;
			}
			elseif ($mediaType[0] == 'audio')
			{
				$item->media = $mediaPath . '/audios/' . $item->source;
			}

			return $item;
		}

		return false;
	}

	/**
	 * Method to delete media record
	 *
	 * @param   string  &$mediaId  post data
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   2.0
	 */
	public function delete(&$mediaId)
	{
		$result    = $this->getTable('mediaxref');
		$result->load(array('media_id' => (int) $mediaId));

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$dispatcher->trigger('onBeforeJTMediaDelete', array($mediaId));

		if ($result->id)
		{
			$modelMediaXref = JModelLegacy::getInstance('MediaXref', 'JTicketingModel');
			$modelMediaXref->delete($result->id);
		}

		$media = $this->getItem($mediaId);
		$filePath = JPATH_ROOT . '/media/com_jticketing/events/images/' . $media->source;

		if (parent::delete($mediaId))
		{
			if (JFile::exists($filePath))
			{
				JFile::delete($filePath);

				return true;
			}
		}

		return false;
	}

	/**
	 * Method to upload the file (image/video/PDF/Audio)
	 *
	 * @param   ARRAY  $fileData  fileData
	 *
	 * @return	return
	 *
	 * @since   2.0
	 */
	public function uploadFile($fileData)
	{
		// Clean up filename to get rid of strange characters like spaces etc
		$fileName = JFile::makeSafe($fileData['name']);
		$fileType = explode("/", $fileData['type']);
		$type = strtolower($fileType[0]);
		$fileExt = strtolower(JFile::getExt($fileName));
		$sourceFile = JFactory::getDate()->format('YmdHism') . '.' . $fileExt;

		if ($type == 'image')
		{
			$dest = $this->storagePath . '/images/' . $sourceFile;
		}
		elseif ($type == 'video')
		{
			$dest = $this->storagePath . '/videos/' . $sourceFile;
		}
		elseif ($type == 'application')
		{
			$dest = $this->storagePath . '/applications/' . $sourceFile;
		}
		elseif ($type == 'audio')
		{
			$dest = $this->storagePath . '/audios/' . $sourceFile;
		}

		if (JFile::upload($fileData['tmp_name'], JPATH_SITE . '/' . $dest))
		{
			$returnData = array();
			$returnData['path'] = $this->storagePath;

			// File original name
			$returnData['name'] = str_replace(' ', '_', $fileName);
			$returnData['original_filename'] = $returnData['name'];
			$returnData['type'] = $type . '.' . $fileExt;

			// Source is replace original file name with date
			$returnData['source'] = $sourceFile;
			$returnData['size'] = $fileData['size'];

			if ($type == 'image')
			{
				$this->resizeImage(JPATH_SITE . '/' . $dest, $this->storagePath . '/images/', $sourceFile);
			}

			return $returnData;
		}

		return false;
	}

	/**
	 * Method to upload video file link
	 *
	 * @param   string  $uploadLink  post data
	 *
	 * @return	return
	 *
	 * @since   2.0
	 */
	public function uploadLink($uploadLink)
	{
		$returnData = array();
		$returnData['path'] = $uploadLink['name'];

		// File original name
		$returnData['name'] = $uploadLink['name'];
		$returnData['original_filename'] = $uploadLink['name'];
		$returnData['type'] = 'video.' . $uploadLink['type'];
		$returnData['source'] = $uploadLink['name'];
		$returnData['size'] = '';

		return $returnData;
	}

	/**
	 * Method to create small, medium and large images of original image
	 *
	 * @param   string  $src       source path with file name
	 *
	 * @param   string  $imgPath   destination path
	 *
	 * @param   string  $fileName  new file name
	 *
	 * @return	return
	 *
	 * @since   2.0
	 */
	public function resizeImage($src, $imgPath, $fileName)
	{
		// Creating a new JImage object, passing it an image path
		$image = new JImage($src);
		$file = explode(".", $fileName);
		$destPath = JPATH_SITE . '/' . $imgPath;
		$format = '';
		$jtParams = JComponentHelper::getParams('com_jticketing');

		if ($file[1] == 'jpeg' || $file[1] == 'jpg')
		{
			$format = IMAGETYPE_JPEG;
		}
		elseif ($file[1] == 'png')
		{
			$format = IMAGETYPE_PNG;
		}
		elseif ($file[1] == 'gif')
		{
			$format = IMAGETYPE_GIF;
		}

		// Small image
		if ($format)
		{
			$smallWidth = $jtParams->get('small_width', '128');
			$smallHeight = $jtParams->get('small_height', '128');
			$destFile = 'S_' . $fileName;
			$newImage = $image->resize($smallWidth, $smallHeight);
			$newImage->toFile($destPath . $destFile, $format);
		}

		// Medium image
		if ($format)
		{
			$mediumWidth = $jtParams->get('medium_width', '240');
			$mediumHeight = $jtParams->get('medium_height', '240');
			$destFile = 'M_' . $fileName;
			$newImage = $image->resize($mediumWidth, $mediumHeight);
			$newImage->toFile($destPath . $destFile, $format);
		}

		// Large image
		if ($format)
		{
			$largeWidth = $jtParams->get('large_width', '400');
			$largeHeight = $jtParams->get('large_height', '400');
			$destFile = 'L_' . $fileName;

			// Resize the image using the SCALE_INSIDE method
			$newImage = $image->resize($largeWidth, $largeHeight);

			// Write it to disk
			$newImage->toFile($destPath . $destFile, $format);
		}

		return true;
	}

	/**
	 * Method to Move the file
	 *
	 * @param   ARRAY  $fileData  fileData
	 *
	 * @return	return
	 *
	 * @since   2.0
	 */
	public function moveFile($fileData)
	{
		$fileName = JFile::makeSafe($fileData['name']);
		$fileType = explode(".", $fileData['type']);
		$type = strtolower($fileType[0]);
		$fileExt = strtolower(JFile::getExt($fileName));
		$sourceFile = JFactory::getDate()->format('YmdHism') . '-' . rand(1, 5) . '.' . $fileExt;
		$destPath = $this->storagePath . '/images/' . $sourceFile;

			if (JFile::move($fileData['tmp_name'], JPATH_SITE . '/' . $destPath))
			{
				$this->resizeImage(JPATH_SITE . '/' . $destPath, $this->storagePath . '/images/', $sourceFile);

				$returnData = array();

				// File original name
				$returnData['name'] = $fileName;
				$returnData['original_filename'] = $fileName;
				$returnData['type'] = $fileData['type'];
				$returnData['source'] = $sourceFile;
				$returnData['size'] = '';
				$returnData['path'] = $this->storagePath;

				return $returnData;
			}

		return false;
	}
}
