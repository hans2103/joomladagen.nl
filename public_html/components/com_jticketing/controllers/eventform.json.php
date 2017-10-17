<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';
require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/venue.php';

$helperPath = JPATH_SITE . '/components/com_jticketing/helpers/time.php';

if (!class_exists('JticketingTimeHelper'))
{
	JLoader::register('JticketingTimeHelper', $helperPath);
	JLoader::load('JticketingTimeHelper');
}

/**
 * Event controller class.
 *
 * @since  1.6
 */
class JticketingControllerEventForm extends JticketingController
{
	/**
	 * Get venue list
	 * 
	 * @return null
	 * 
	 * @since   1.6
	 */
	public function getVenueList()
	{
		$input  = JFactory::getApplication()->input->post;
		$eventData["radioValue"] = $input->get('radioValue', '', 'STRING');
		$eventData["enforceVendor"] = $input->get('enforceVendor', '', 'STRING');

		if ($eventData["enforceVendor"] == 1)
		{
			$eventData["vendor_id"] = $input->get('vendor_id', '', 'INTEGER');
		}
		else
		{
			$eventData["created_by"] = $input->get('created_by', '', 'STRING');
		}

		$eventData["eventStartDate"] = $input->get('eventStartTime', '', 'STRING');
		$eventData["eventEndDate"] = $input->get('eventEndTime', '', 'STRING');
		$model = $this->getModel('eventform');
		$results = $model->getVenueList($eventData);
		echo json_encode($results);
		jexit();
	}

	/**
	 * Method to get all existing events
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function getAllMeetings()
	{
		$post = JFactory::getApplication()->input->post;
		$venueId = $post->get('venueId');

		// Load AnnotationForm Model
		$model = JModelLegacy::getInstance('Venue', 'JticketingModel');
		$licenceContent = $model->getItem($venueId);
		$licence = (object) $licenceContent->params;

		if (!empty($venueId))
		{
			// TRIGGER After create event
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('tjevents');
			$result = $dispatcher->trigger('getAllMeetings', array($licence));
			echo json_encode($result);
		}

		jexit();
	}

	/**
	 * Method to get all existing events
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function getScoID()
	{
		$post = JFactory::getApplication()->input->post;
		$venueId = $post->get('venueId');
		$venueurl = $post->get('venueurl');

		// Load AnnotationForm Model
		$model = JModelLegacy::getInstance('Venue', 'JticketingModel');
		$licenceContent = $model->getItem($venueId);
		$licence = (object) $licenceContent->params;

		if (!empty($venueId))
		{
			// TRIGGER After create event
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('tjevents');
			$result = $dispatcher->trigger('getscoID', array($licence, $venueurl));
			echo json_encode($result);
		}

		jexit();
	}

	/**
	 * upload media files and links
	 *
	 * @return JSON
	 *
	 * @since   2.0
	 */
	public function uploadMedia()
	{
		$input = JFactory::getApplication()->input;
		$uploadFile = $input->post->get('upload_type', '', 'string');
		$isGallary = $input->post->get('isGallary', '', 'INT');

		$model = $this->getModel('Media', 'JTicketingModel');
		$userId = JFactory::getUser()->id;
		$returnData = array();

		if ($uploadFile == "link")
		{
			$data = array();
			$data['name'] = $input->post->get('name', '', 'string');
			$data['type'] = $input->post->get('type', '', 'string');
			$data['upload_type'] = $uploadFile;
			$returnData = $model->save($data);
		}
		else
		{
			$files = $input->files->get('file', '', 'array');
			$fileType = explode("/", $files['type']);

			// Image and video specific validation

			if ($isGallary && ( $fileType[0] === 'video' || $fileType[0] === 'image' ))
			{
				$returnData = $model->save($files);
			}
			elseif (!$isGallary && $fileType[0] === 'image')
			{
				$returnData = $model->save($files);
			}
			else
			{
				echo new JResponseJson($returnData, JText::_('COM_JTICKETING_MEDIA_INVALID_FILE_TYPE'), true);
			}
		}

		if ($returnData)
		{
			echo new JResponseJson($returnData, JText::_('COM_JTICKETING_MEDIA_FILE_UPLOADED'));
		}
	}

	/**
	 * Delete media file
	 *
	 * @return JSON
	 *
	 * @since   2.0
	 */
	public function deleteMedia()
	{
		$mediaId = $this->input->get('id', '0', 'INT');

		if (!$mediaId)
		{
			return false;
		}

		$model = $this->getModel('media');
		$model->delete($mediaId);
		echo new JResponseJson(1, JText::_('COM_JTICKETING_MEDIA_FILE_DELETED'));
	}
}
