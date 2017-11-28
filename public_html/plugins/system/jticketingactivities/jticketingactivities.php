<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing_Activities
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport('joomla.plugin.plugin');
jimport('joomla.application.component.model');
jimport('techjoomla.jsocial');

/**
 * Jticketing_Activities
 *
 * @package     Jticketing_Activities
 * @subpackage  site
 * @since       1.0
 */
class PlgSystemJticketingActivities extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   string  &$subject  subject
	 *
	 * @param   string  $config    config
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		require_once dirname(__FILE__) . '/helper.php';
		$this->PlgSystemJticketingActivitiesHelper = new PlgSystemJticketingActivitiesHelper;
	}

	/**
	 * Method to include required scripts for activity streams
	 *
	 * @param   string  $theme  Theme used
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function getActivityScript($theme)
	{
		$document = JFactory::getDocument();
		$document->addScriptDeclaration('var root_url = \'' . JUri::base() . '\'');
		$document->addScript(JUri::root() . '/media/com_activitystream/scripts/mustache.min.js');
		$document->addScript(JUri::root() . '/media/com_activitystream/scripts/activities.jQuery.js');
		$document->addStyleSheet(JUri::root() . '/media/com_activitystream/themes/' . $theme . '/css/theme.css');
	}

	/**
	 * Function postActivity
	 *
	 * @param   MIXED  $data  data
	 *
	 * @return  void.
	 *
	 * @since	1.8
	 */
	public function postActivity($data)
	{
		$jinput    = JFactory::getApplication()->input;
		$componentName = $jinput->post->get('option');

		if ($componentName == 'com_jticketing' && !empty($data))
		{
			$result = $this->PlgSystemJticketingActivitiesHelper->postActivity($data);

			return $result;
		}
	}

	/**
	 * Function OnAfterEventCreate
	 *
	 * @param   Array  $eventData  Event id, new data, old data
	 *
	 * @return  void.
	 *
	 * @since  1.8
	 */
	public function jt_OnAfterEventCreate($eventData)
	{
		$eventId = $eventData['eventId'];

		if (isset($eventData['eventOldData']))
		{
			$oldDetails = $eventData['eventOldData'];
		}

		if (!empty($eventId))
		{
			if (isset($oldDetails))
			{
				// Activity for adding images to the event
				$result = false;
				$newGalleryImages = array();

				for ($i = 0; $i < count($eventData['gallery_file']['media']); $i++)
				{
					$imageIsNew = 1;

					if (isset($oldDetails->gallery))
					{
						foreach ($oldDetails->gallery as $k => $image)
						{
							if ($eventData['gallery_file']['media'][$i] == $oldDetails->gallery[$k]->id)
							{
								$imageIsNew = 0;
								break;
							}
							else
							{
								continue;
							}
						}
					}

					if ($imageIsNew)
					{
						$newGalleryImages[] = $eventData['gallery_file']['media'][$i];
					}
				}

				$newGalleryImages = array_filter($newGalleryImages);

				// Activity for gallery images
				if (!empty($newGalleryImages))
				{
					$result = $this->PlgSystemJticketingActivitiesHelper->addImageActivity($newGalleryImages, $eventId, $eventData);
				}

				// Event date activity
				if (!empty($eventData['enddate']) && !empty($oldDetails->enddate))
				{
					if ($eventData['enddate'] != $oldDetails->enddate)
					{
						$result = $this->PlgSystemJticketingActivitiesHelper->eventEndDateChangeActivity($eventData);
					}
				}

				// Event Book date activity
				if (!empty($eventData['booking_end_date']) && !empty($oldDetails->booking_end_date))
				{
					if ($eventData['booking_end_date'] != $oldDetails->booking_end_date)
					{
						$result = $this->PlgSystemJticketingActivitiesHelper->eventBookingEndDateChangeActivity($eventData);
					}
				}
			}
			else
			{
				$result = $this->PlgSystemJticketingActivitiesHelper->addEventActivity($eventData);
			}

			return $result;
		}
	}

	/**
	 * Function OnAfterProcessPayment
	 *
	 * @param   Array    $post       Event post data
	 * @param   Integer  $order_id   Event order id
	 * @param   String   $pg_plugin  Plugin Name
	 *
	 * @return  void.
	 *
	 * @since  1.8
	 */
	public function jt_OnAfterProcessPayment($post, $order_id, $pg_plugin = null)
	{
		JLoader::import('main', JPATH_SITE . '/components/com_jticketing/helpers');
		$jticketingMainHelper  = new Jticketingmainhelper;

		if (is_array($post))
		{
			$post = $post;
			$status = $post['status'];
			$order_id = $jticketingMainHelper->getIDFromOrderID($order_id);
		}
		else
		{
			$post = $post->getArray(array());
			$status = $post['payment_status'];
		}

		if ($status == 'C')
		{
			$orderDetails = $jticketingMainHelper->getOrderDetail($order_id);
			$eventData = $jticketingMainHelper->getAllEventDetails($orderDetails->event_details_id);

			// Append event details to order details
			$orderDetails->eventData = $eventData;
			$result = $this->PlgSystemJticketingActivitiesHelper->addEventOrderActivity($orderDetails);

			return $result;
		}

		return false;
	}

	/**
	 * Function onAfterMediaDelete
	 *
	 * @param   Integer  $mediaId  Media Id
	 *
	 * @return  void.
	 *
	 * @since  1.8
	 */
	public function onBeforeJTMediaDelete($mediaId)
	{
		if ($mediaId)
		{
			$result = $this->PlgSystemJticketingActivitiesHelper->removeMediaActivity($mediaId);
		}
	}
}
