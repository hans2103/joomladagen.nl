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
jimport('techjoomla.jsocial.jsocial');
jimport('joomla.application.component.helper');

/**
 * Plugin for JTicketing_activities
 *
 * @package     JTicketing_Activities
 * @subpackage  site
 * @since       1.0
 */
class PlgSystemJticketingActivitiesHelper
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		if (JComponentHelper::isEnabled('com_activitystream', true))
		{
			// Load activity component models
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_activitystream/models');

			// Load activity component models
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_activitystream/models');

			// Load activity component tables
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_activitystream/tables');
		}
		else
		{
			JError::raiseError('SOME_ERROR', JText('Component requires the Some Extension component'));
		}

		$path = JPATH_SITE . '/components/com_jticketing/helpers/main.php';

		if (!class_exists('Jticketingmainhelper'))
		{
			JLoader::register('Jticketingmainhelper', $path);
			JLoader::load('Jticketingmainhelper');
		}

		$this->Jticketingmainhelper = new Jticketingmainhelper;
	}

	/**
	 * Method to get actor data
	 *
	 * @param   OBJECT  $user  user object
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function getActorData($user)
	{
		$user = JFactory::getUser($user);
		$userData = array();
		$userData['type'] = 'person';
		$userData['id'] = $user->id;
		$userData['name'] = $user->get('name');

		JLoader::import('integrations', JPATH_SITE . '/components/com_jticketing/helpers');
		$jTicketingIntegrationsHelper = new JTicketingIntegrationsHelper;
		$userData['url'] = $jTicketingIntegrationsHelper->getUserProfileUrl($user->id, true);

		$imageData = array();
		$imageData['type'] = "link";
		$imageData['avatar'] = $jTicketingIntegrationsHelper->getUserAvatar($user->id, true);

		if (strpos($imageData['avatar'], 'www.gravatar.com'))
		{
			$imageData['gravatar'] = true;
		}
		else
		{
			$imageData['gravatar'] = false;
		}

		$userData['image'] = json_encode($imageData);

		return $userData;
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
		$user = JFactory::getUser();

		if (empty($user->id))
		{
			$result = array();

			$result['error'] = JText::_('COM_JTICKETING_TEXT_ACTIVITY_POST_GUEST_ERROR_MSG');

			return $result;
		}

		$activityData = array();
		$activityData['id'] = '';
		$actorData = $this->getActorData($user->id);

		$activityData['actor'] = json_encode($actorData);
		$activityData['actor_id'] = $user->id;

		$eventId = $data['eventid'];

		// Load component models
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		$jticketingEventformModel = JModelLegacy::getInstance('Eventform', 'JticketingModel');
		$eventData = $jticketingEventformModel->getItem($eventId);

		$objectData = array();
		$objectData['type'] = 'text';
		$objectData['postData'] = $data['postData'];
		$activityData['object'] = json_encode($objectData);
		$activityData['object_id'] = 'text';

		$targetData = array();
		$targetData['id'] = $eventData->id;
		$targetData['name'] = $eventData->title;
		$targetData['url'] = str_replace(JUri::root(), '', $this->Jticketingmainhelper->getEventlink($eventData->id));
		$targetData['type'] = 'event';
		$activityData['target'] = json_encode($targetData);
		$activityData['target_id'] = $eventId;

		$activityData['type'] = 'jticketing.textpost';
		$activityData['template'] = 'textpost.mustache';

		if ($data['cdate'])
		{
			$activityData['created_date'] = $data['cdate'];
		}

		if ($data['mdate'])
		{
			$activityData['updated_date'] = $data['mdate'];
		}

		$activityStreamModelActivity = JModelLegacy::getInstance('Activity', 'ActivityStreamModel');
		$result = $activityStreamModelActivity->save($activityData);

		return $result;
	}

	/**
	 * Function to add activity for new event added
	 *
	 * @param   Array  $eventData  Eventdata
	 *
	 * @return  void.
	 *
	 * @since  1.8
	 */
	public function addEventActivity($eventData)
	{
		$user = $eventData['created_by']?$eventData['created_by']:JFactory::getUser();

		$activityData = array();
		$activityData['id'] = '';
		$actorData = $this->getActorData($user);

		$activityData['actor'] = json_encode($actorData);
		$user = JFactory::getUser($user);
		$activityData['actor_id'] = $user->id;

		$objectData = array();
		$objectData['type'] = 'event';
		$objectData['name'] = $eventData['title'];
		$objectData['id'] = $eventData['eventId'];
		$objectData['url'] = 'index.php?option=com_jticketing&view=event&id=' . $eventData['eventId'];
		$activityData['object'] = json_encode($objectData);
		$activityData['object_id'] = $eventData['eventId'];

		$targetData = array();
		$targetData['type'] = 'event';
		$targetData['name'] = $eventData['title'];
		$targetData['id'] = $eventData['eventId'];
		$targetData['url'] = str_replace(JUri::root(), '', $this->Jticketingmainhelper->getEventlink($eventData->id));
		$activityData['target'] = json_encode($targetData);
		$activityData['target_id'] = $eventData['eventId'];

		$activityData['type'] = 'jticketing.addevent';
		$activityData['template'] = 'addevent.mustache';
		$activityStreamModelActivity = JModelLegacy::getInstance('Activity', 'ActivityStreamModel');

		$result = $activityStreamModelActivity->save($activityData);

		return $result;
	}

	/**
	 * Method to add activity for event date extension
	 *
	 * @param   Array  $eventNewOlddata  event data
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function eventEndDateChangeActivity($eventNewOlddata)
	{
		if ($eventNewOlddata['enddate'] != $eventNewOlddata['eventOldData']->enddate)
		{
			$date_diff = date_diff(date_create($eventNewOlddata['eventOldData']->enddate), date_create($eventNewOlddata['enddate']));

			if ($date_diff->days > 0)
			{
				// Load component models
				JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
				$jticketingEventformModel = JModelLegacy::getInstance('Eventform', 'JticketingModel');
				$eventData = $jticketingEventformModel->getItem($eventNewOlddata['eventId']);
				$user = $eventData->created_by?$eventData->created_by:JFactory::getUser();

				$data = array();
				$data['id'] = '';
				$actorData = $this->getActorData($user);
				$user = JFactory::getUser();
				$data['actor_id'] = $user->id;
				$data['actor'] = json_encode($actorData);

				// Get date difference in
				$date_diff = date_diff(date_create($eventNewOlddata['eventOldData']->enddate), date_create($eventNewOlddata['enddate']));

				$objectData = array();
				$objectData['type'] = 'event';
				$objectData['newenddate'] = date("d M Y", strtotime($eventNewOlddata['enddate']));
				$objectData['url'] = 'index.php?option=com_jticketing&view=event&id=' . $eventNewOlddata['eventId'];

				$data['object'] = json_encode($objectData);
				$data['object_id'] = $eventData->id;

				$targetData = array();
				$targetData['type'] = 'event';
				$targetData['name'] = $eventData->title;
				$targetData['id'] = $eventData->id;
				$targetData['url'] = str_replace(JUri::root(), '', $this->Jticketingmainhelper->getEventlink($eventData->id));
				$data['target'] = json_encode($targetData);
				$data['target_id'] = $eventData->id;

				$data['type'] = 'event.extended';

				// If campaign end date is extended then use extended template else use datechange template
				if ($date_diff->invert == 0)
				{
					$data['template'] = 'extended.mustache';
				}
				else
				{
					$data['template'] = 'datechange.mustache';
				}

				$activityStreamModelActivity = JModelLegacy::getInstance('Activity', 'ActivityStreamModel');
				$result = $activityStreamModelActivity->save($data);

				return $result;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to add activity for event booking date extension
	 *
	 * @param   Array  $eventNewOlddata  event data
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function eventBookingEndDateChangeActivity($eventNewOlddata)
	{
		if ($eventNewOlddata['booking_end_date'] != $eventNewOlddata['eventOldData']->booking_end_date)
		{
			$date_diff = date_diff(date_create($eventNewOlddata['eventOldData']->booking_end_date), date_create($eventNewOlddata['booking_end_date']));

			if ($date_diff->days > 0)
			{
				// Load component models
				JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
				$jticketingEventformModel = JModelLegacy::getInstance('Eventform', 'JticketingModel');
				$eventData = $jticketingEventformModel->getItem($eventNewOlddata['eventId']);

				$user = $eventData->created_by?$eventData->created_by:JFactory::getUser();
				$data = array();
				$data['id'] = '';
				$actorData = $this->getActorData($user);
				$user = JFactory::getUser();
				$data['actor_id'] = $user->id;
				$data['actor'] = json_encode($actorData);

				// Get date difference in
				$dateDiff = (strtotime($eventNewOlddata['booking_end_date']) - strtotime($eventNewOlddata['eventOldData']->booking_end_date));

				// Get date difference in
				$dateDiff = (strtotime($eventNewOlddata['enddate']) - strtotime($eventNewOlddata['eventOldData']->enddate));

				$objectData = array();
				$objectData['type'] = 'event';
				$objectData['newenddate'] = date("d M Y", strtotime($eventNewOlddata['booking_end_date']));
				$objectData['url'] = 'index.php?option=com_jticketing&view=event&id=' . $eventNewOlddata['eventId'];

				$data['object'] = json_encode($objectData);
				$data['object_id'] = $eventData->id;

				$targetData = array();
				$targetData['type'] = 'event';
				$targetData['name'] = $eventData->title;
				$targetData['id'] = $eventData->id;
				$targetData['url'] = str_replace(JUri::root(), '', $this->Jticketingmainhelper->getEventlink($eventData->id));
				$data['target'] = json_encode($targetData);
				$data['target_id'] = $eventData->id;

				$data['type'] = 'eventBooking.extended';

				// If campaign end date is extended then use extended template else use datechange template
				if ($date_diff->invert == 0)
				{
					$data['template'] = 'bookingExtended.mustache';
				}
				else
				{
					$data['template'] = 'bookingDatechange.mustache';
				}

				$activityStreamModelActivity = JModelLegacy::getInstance('Activity', 'ActivityStreamModel');
				$result = $activityStreamModelActivity->save($data);

				return $result;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to add activity for event ticket order completed
	 *
	 * @param   Object  $orderDetails  order data
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function addEventOrderActivity($orderDetails)
	{
		JLoader::import('frontendhelper', JPATH_SITE . '/components/com_jticketing/helpers');
		$jticketingFrontendHelper = new Jticketingfrontendhelper;

		$user = $orderDetails->user_id;
		$activityData = array();
		$activityData['id'] = '';

		$actorData = $this->getActorData($user);
		$activityData['actor'] = json_encode($actorData);
		$user = JFactory::getUser();
		$activityData['actor_id'] = $user->id;

		if ($orderDetails->eventData->online_events == 0)
		{
			$eventType = 'Offline';
		}
		else
		{
			$eventType = 'Online';
		}

		$objectData = array();
		$objectData['type'] = $eventType;
		$objectData['amount'] = str_replace("&nbsp;", "", strip_tags($this->Jticketingmainhelper->getFormattedPrice($orderDetails->amount)));
		$activityData['object'] = json_encode($objectData);
		$activityData['object_id'] = 'order';

		// Get event-target data
		$targetData = array();
		$targetData['id'] = $orderDetails->event_details_id;
		$targetData['type'] = 'event';
		$targetData['url'] = str_replace(JUri::root(), '', $this->Jticketingmainhelper->getEventlink($orderDetails->event_details_id));
		$targetData['name'] = $orderDetails->eventData->title;
		$activityData['target'] = json_encode($targetData);
		$activityData['target_id'] = $orderDetails->event_details_id;
		$activityData['type'] = 'jticketing.order';

		if ($objectData['type'] == 'Offline')
		{
			$activityData['template'] = 'offlineEventOrder.mustache';
		}
		elseif ($objectData['type'] == 'Online')
		{
			$activityData['template'] = 'onlineEventOrder.mustache';
		}

		$activityStreamModelActivity = JModelLegacy::getInstance('Activity', 'ActivityStreamModel');
		$result = $activityStreamModelActivity->save($activityData);

		return $result;
	}

	/**
	 * Method to add activity for adding images to the event
	 *
	 * @param   ARRAY    $newGalleryImages  array of newly added images
	 * @param   Integer  $eventid           event Id
	 * @param   Array    $eventData         eventData
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function addImageActivity($newGalleryImages, $eventid, $eventData)
	{
		if (!empty($newGalleryImages))
		{
			$jticketingModelMedia = JModelLegacy::getInstance('Media', 'JticketingModel');
			$user = $eventData['created_by']?$eventData['created_by']:JFactory::getUser();
			$data = array();
			$data['id'] = '';
			$actorData = $this->getActorData($user);
			$data['actor'] = json_encode($actorData);

			foreach ($newGalleryImages as $newGalleryImage)
			{
				if ($newGalleryImage != 0)
				{
					$eventMediaData = $jticketingModelMedia->getItem($newGalleryImage);
				}

				$eventContentType = substr($eventMediaData->type, 0, 5);

				if ($eventContentType == "image")
				{
					$eventImageGalleryData[] = str_replace(JUri::root(), '', $eventMediaData->media);
				}
				elseif ($eventContentType == "video")
				{
					$eventVideoGalleryData[] = $eventMediaData;
				}
			}

			$user = JFactory::getUser();

			if (isset($eventImageGalleryData))
			{
				// Get event-object data
				$objectData = array();
				$objectData['type'] = 'image';
				$objectData['url'] = json_encode($eventImageGalleryData);
				$objectData['count'] = count($eventImageGalleryData);
				$data['object'] = json_encode($objectData);

				$data['actor_id'] = $user->id;
				$data['object_id'] = "image";
				$data['type'] = 'event.addimage';
				$data['template'] = 'image.mustache';

				$data['target_id'] = $eventData['id'];
				$targetData = array();
				$targetData['id'] = $eventData['id'];
				$targetData['name'] = $eventData['title'];
				$targetData['url'] = str_replace(JUri::root(), '', $this->Jticketingmainhelper->getEventlink($eventData['id']));
				$targetData['type'] = 'event';
				$data['target'] = json_encode($targetData);

				$activityStreamModelActivity = JModelLegacy::getInstance('Activity', 'ActivityStreamModel');
				$result = $activityStreamModelActivity->save($data);
			}

			if (isset($eventVideoGalleryData))
			{
				JLoader::import('media', JPATH_SITE . '/components/com_jticketing/helpers');
				$jticketingMediaHelper = new JticketingMediaHelper;

				foreach ($eventVideoGalleryData as $eventVideoData)
				{
					$eventVideoType = substr($eventVideoData->type, 6);
					$videoIdThumb = $jticketingMediaHelper->videoId($eventVideoType, $eventVideoData->original_filename);
					$videoDetails['thumbSrc'] = $jticketingMediaHelper->videoThumbnail($eventVideoType, $videoIdThumb);
					$videoDetails['url'] = $eventVideoData->media;

					$videoDetails['link'] = "index.php?option=com_jticketing&view=event&layout=default_playvideo&id=" . $eventid . "&vid=" .
					$eventVideoData->id . "&type=" . trim($eventVideoType) . "&tmpl=component";
					$videoDetails['playIcon'] = 'media/com_jticketing/images/play_icon.png';

					$VideoActivityData[] = $videoDetails;
				}

				// Get campaign-object data
				$objectData = array();
				$objectData['videos'] = json_encode($VideoActivityData);
				$objectData['count'] = count($VideoActivityData);

				$data['object'] = json_encode($objectData);
				$data['actor_id'] = $user->id;
				$data['object_id'] = "video";
				$data['type'] = 'event.addvideo';
				$data['template'] = 'video.mustache';

				$data['target_id'] = $eventData['id'];
				$targetData = array();
				$targetData['id'] = $eventData['id'];
				$targetData['name'] = $eventData['title'];
				$targetData['url'] = str_replace(JUri::root(), '', $this->Jticketingmainhelper->getEventlink($eventData['id']));
				$targetData['type'] = 'event';
				$data['target'] = json_encode($targetData);

				$activityStreamModelActivity = JModelLegacy::getInstance('Activity', 'ActivityStreamModel');
				$result = $activityStreamModelActivity->save($data);
			}

			return $result;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method for remove media activity
	 *
	 * @param   Integer  $mediaId  Media Id
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function removeMediaActivity($mediaId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'client_id', 'client', 'is_gallery')));
		$query->from($db->quoteName('#__media_files_xref'));
		$query->where($db->quoteName('media_id') . '=' . (int) $mediaId);
		$query->where($db->quoteName('client') . '=' . $db->quote('com_jticketing.event'));
		$db->setQuery($query);
		$mediaxrefData = $db->loadObject();

		// Getting Event Id
		$eventId = $mediaxrefData->client_id;

		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'eventform');
		$jtickeitngModelEventFrom = JModelLegacy::getInstance('eventform', 'JticketingModel');

		// Getting event data from event id
		$eventData = $jtickeitngModelEventFrom->getItem($eventId);

		foreach ($eventData->gallery as $k => $eventMediaData)
		{
			$mediaIsRemoved = 0;

			if ($eventData->gallery[$k]->id == $mediaId)
			{
				$mediaIsRemoved = 1;
			}

			if ($mediaIsRemoved)
			{
				$eventContentType = substr($eventData->gallery[$k]->type, 0, 5);

				if ($eventContentType == "image")
				{
					$type = "event.addimage";
				}
				elseif ($eventContentType == "video")
				{
					$type = "event.addvideo";
				}

				$deleteMediaPath = $eventData->gallery[$k]->media;

				$query = $db->getQuery(true);
				$query->select('*');
				$query->from($db->quoteName('#__tj_activities'));
				$query->where($db->quoteName('target_id') . ' = ' . $eventId);
				$query->where($db->quoteName('type') . " = '" . $type . "'");
				$db->setQuery($query);
				$activities = $db->loadAssocList();

				$activityStreamModelActivity = JModelLegacy::getInstance('Activity', 'ActivityStreamModel');

				if (!empty($activities))
				{
					foreach ($activities as $activity)
					{
						$objectData = json_decode($activity['object']);

						if (isset($objectData->url))
						{
							$images = json_decode($objectData->url);

							foreach ($images as $k => $image)
							{
								if ($objectData->count == '1')
								{
									if (strpos($image, $deleteMediaPath) !== false)
									{
										$activityStreamModelActivity->delete($activity['id']);
									}
								}
								else
								{
									if (strpos($image, $deleteMediaPath) !== false)
									{
										unset($images[$k]);
										$activityImages = array();

										foreach ($images as $img)
										{
											$activityImages[] = $img;
										}

										$objectData->url = json_encode($activityImages);
										$objectData->count -= 1;
										$activity['object'] = json_encode($objectData);
										$activityStreamModelActivity->save($activity);
									}
								}
							}
						}

						if (isset($objectData->videos))
						{
							$videos = json_decode($objectData->videos);

							foreach ($videos as $k => $video)
							{
								if ($objectData->count == '1')
								{
									if (strpos($video->url, $deleteMediaPath) !== false)
									{
										$activityStreamModelActivity->delete($activity['id']);
									}
								}
								else
								{
									if (strpos($video->url, $deleteMediaPath) !== false)
									{
										unset($videos[$k]);

										$activityVideos = array();

										foreach ($videos as $vid)
										{
											$activityVideos[] = $vid;
										}

										$objectData->videos = json_encode($activityVideos);
										$objectData->count -= 1;
										$activity['object'] = json_encode($objectData);
										$activityStreamModelActivity->save($activity);
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
