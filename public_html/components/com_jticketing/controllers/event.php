<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
jimport('techjoomla.common');
require_once JPATH_COMPONENT . '/controller.php';

/**
 * Event controller class.
 *
 * @since  1.7
 */
class JticketingControllerEvent extends JticketingController
{
	/**
	 * Class constructor.
	 *
	 * @since   1.6
	 */
	public function __construct()
	{
		$this->techjoomlacommon = new TechjoomlaCommon;

		parent::__construct();
	}

	/**
	 * Cancel description
	 *
	 * @return description
	 */
	public function cancel()
	{
		$app        = JFactory::getApplication();
		$previousId = (int) $app->getUserState('com_raector_crm.edit.project.id');

		if ($previousId)
		{
			// Get the model.
			$model = $this->getModel('Project', 'Raector_crmModel');
			$model->checkin($previousId);
		}

		$menu =& JSite::getMenu();
		$item = $menu->getActive();
		$this->setRedirect(JRoute::_($item->link, false));
	}

	/**
	 * remove description
	 *
	 * @return void
	 */
	public function remove()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = JFactory::getApplication();
		$model = $this->getModel('Event', 'JticketingModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_jticketing.edit.event.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.event.id');
			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->delete($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_jticketing.edit.event.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.event.id');
			$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

			return false;
		}

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_jticketing.edit.event.id', null);

		// Redirect to the list screen.
		$this->setMessage(JText::_('COM_JTICKETING_ITEM_DELETED_SUCCESSFULLY'));
		$menu =& JSite::getMenu();
		$item = $menu->getActive();
		$this->setRedirect(JRoute::_($item->link, false));

		// Flush the data from the session.
		$app->setUserState('com_jticketing.edit.event.data', null);
	}

	/**
	 * remove renderbook
	 *
	 * @return void
	 */
	public function renderbook()
	{
		$eventid = 1;
		$data = $this->renderBookingHTML($eventid);
		print_r($data);
	}

	/**
	 * Render booking HTML
	 *
	 * @param   int  $eventid  id of event
	 * @param   int  $userid   userid
	 *
	 * @return  booking HTML
	 *
	 * @since   1.0
	 */
	public function renderBookingHTML($eventid, $userid='')
	{
		require_once JPATH_SITE . "/components/com_jticketing/models/event.php";

		if (empty($eventid))
		{
			return false;
		}

		$model = new JticketingModelEvent;

		return $model->renderBookingHTML($eventid, $userid);
	}

	/**
	 * Online Meeting URL
	 *
	 * @return  meeting URL
	 *
	 * @since   1.0
	 */
	public function onlineMeetingUrl()
	{
		$user = JFactory::getUser();

		if ($user->id)
		{
			$input = JFactory::getApplication()->input;
			$eventId = $input->get('eventId');

			$model = JModelLegacy::getInstance('eventForm', 'JticketingModel');
			$eventData = $model->getItem($eventId);
			$eventURL = json_decode($eventData->jt_params, "true");
			$venueId = $eventData->venue;

			$params = array();
			$model = JModelLegacy::getInstance('Venue', 'JticketingModel');
			$venueData = $model->getItem($venueId);
			$params['api_username'] = $venueData->params['api_username'];
			$params['api_password'] = $venueData->params['api_password'];
			$params['host_url'] = preg_replace('{/$}', '', $venueData->params['host_url']);
			$params['online_provider'] = $venueData->online_provider;
			$params['licence'] = (object) $venueData->params;
			$params['online_provider_params']['meeting_url'] = $params['host_url'] . $eventURL['event_url'];
			$params['sco_id'] = $eventURL['event_sco_id'];

			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('tjevents');
			$result = $dispatcher->trigger('generateMeeting_HTML', array($params,$eventData,$dataFormat = ''));
			$url = $result[0];
		}
		else
		{
			$url = 1;
		}

		echo json_encode($url);
		jexit();
	}

	/**
	 * Online Meeting URL
	 *
	 * @return  meeting URL
	 *
	 * @since   1.0
	 */
	public function meetingRecordingUrl()
	{
		$user = JFactory::getUser();

		if ($user->id)
		{
			$input = JFactory::getApplication()->input;
			$eventId = $input->get('eventId');

			$model = JModelLegacy::getInstance('eventForm', 'JticketingModel');
			$eventData = $model->getItem($eventId);
			$eventURL = json_decode($eventData->jt_params, "true");
			$venueId = $eventData->venue;

			$params = array();
			$model = JModelLegacy::getInstance('Venue', 'JticketingModel');
			$venueData = $model->getItem($venueId);
			$params['api_username'] = $venueData->params['api_username'];
			$params['api_password'] = $venueData->params['api_password'];
			$params['host_url'] = preg_replace('{/$}', '', $venueData->params['host_url']);
			$params['online_provider'] = $venueData->online_provider;
			$params['licence'] = (object) $venueData->params;
			$params['online_provider_params']['meeting_url'] = $params['host_url'] . $eventURL['event_url'];
			$params['sco_id'] = $eventURL['event_sco_id'];

			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('tjevents');
			$result = $dispatcher->trigger('getMeetingRecording', array($params,$eventData,$dataFormat = ''));

			if (isset ($result[0]))
			{
				$url = $result[0];
			}
			else
			{
				$url = $result;
			}
		}
		else
		{
			$url = 1;
		}

		echo json_encode($url);
		jexit();
	}

	/**
	 * Function to save text activity
	 *
	 * @return  object  activities
	 *
	 * @since   1.6
	 */
	public function addPostedActivity()
	{
		$input = JFactory::getApplication()->input;
		$activityData = array();
		$activityData['postData'] = $input->get('activity-post-text', '', 'STRING');
		$activityData['type'] = 'text';
		$activityData['eventid'] = $input->get('id', '0', 'INT');

		$jticketingmainHelper       = new jticketingmainhelper;
		$itemId   = $jticketingmainHelper->getItemId('index.php?option=com_jticketing&view=event');
		$redirect = JRoute::_('index.php?option=com_jticketing&view=event&id=' . $input->get('id', '0', 'INT') . '&Itemid=' . $itemId, false);

		if (!empty($activityData['postData']))
		{
			// Trigger jgiveactivity plugin to add test activity
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');

			$result = $dispatcher->trigger('postActivity', array($activityData));

			if (empty($result[0]['error']))
			{
				$msg = JText::_("COM_JTICKETING_TEXT_ACTIVITY_POST_SUCCESS_MSG");
				$this->setRedirect($redirect, $msg);
			}
			else
			{
				$msg = JText::_("COM_JTICKETING_TEXT_ACTIVITY_POST_GUEST_ERROR_MSG");
				$this->setRedirect($redirect, $msg, 'error');
			}
		}
	}

	/**
	 * Method added for add event to Google Calender
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addGoogleEvent()
	{
		$app = JFactory::getApplication();
		$eventId = $app->input->get('id', '', 'INTEGER');

		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'eventform');
		$jTicketingModelEventform = JModelLegacy::getInstance('Eventform', 'JTicketingModel');
		$eventDetails = $jTicketingModelEventform->getItem($eventId);

		$model = $this->getModel('Event', 'JticketingModel');
		$url = $model->addGoogleEvent($eventDetails);
		$this->setRedirect(JRoute::_($url, false));
	}

	/**
	 * Method for getting Event specific orders and average orders data for showing graph
	 *
	 * @return   json
	 *
	 * since 2.0
	 */
	public function getEventOrderGrapgData()
	{
		$params = JComponentHelper::getParams('com_jticketing');
		$currency = $params->get('currency_symbol');

		$input = JFactory::getApplication()->input;

		$this->techjoomlacommon = new TechjoomlaCommon;
		$lastTwelveMonth = $this->techjoomlacommon->getLastTwelveMonths();

		$duration = $input->get('filtervalue');
		$eventId = $input->get('eventId');

		$model = $this->getModel('event');
		$results = $model->getEventGarphData($duration, $eventId);

		if ($duration == 0)
		{
			$graphDuration = 7;
		}
		elseif ($duration == 1)
		{
			$graphDuration = 30;
			$arraychunkvar = 7;
		}
		elseif ($duration == 2)
		{
			$arraychunkvar = 30;

			$todate = JFactory::getDate(date('Y-m-d'))->Format(JText::_('Y-m-d'));
			$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - 1 year'));
			$graphDuration = round(abs(strtotime($todate) - strtotime($backdate)) / 86400);
		}

		// To order amount
		$totalOrdersAmt = 0;

		foreach ($results as $key => $result)
		{
			if (isset($result->order_amount))
			{
				$totalOrdersAmt += $result->order_amount;
			}
		}

		if ($duration == 0 || $duration == 1)
		{
			for ($i = 0; $i < $graphDuration; $i++)
			{
				// Making order Date Array
				$graphDataArr['orderDate'][$i] = date("Y-m-d", strtotime($i . " days ago"));

				// Making Average order Array
				$graphDataArr['orderAvg'][$i] = $totalOrdersAmt / $graphDuration;

				if (!empty($results))
				{
					// Making order amount Array
					for ($j = 0; $j < count($results); $j++)
					{
						if ($graphDataArr['orderDate'][$i] == $results[$j]->cdate)
						{
							$graphDataArr['orderAmount'][$i] = $results[$j]->order_amount;
							break;
						}
						else
						{
							$graphDataArr['orderAmount'][$i] = "0";
						}
					}
				}
				else
				{
					$graphDataArr['orderAmount'][$i] = "0";
				}
			}
		}
		elseif ($duration == 2)
		{
			// Making order amount Array
			for ($i = 0; $i < count($lastTwelveMonth); $i++)
			{
				$graphDataArr['orderDate'][$i] = $lastTwelveMonth[$i]['month'];
				$graphDataArr['orderAvg'][$i] = $totalOrdersAmt / $graphDuration;

				if (!empty($results))
				{
					for ($j = 0; $j < count($results); $j++)
					{
						$monthNum  = $results[$j]->month_name;
						$dateObj   = DateTime::createFromFormat('!m', $monthNum);
						$monthName = $dateObj->format('F');

						if ($lastTwelveMonth[$i]['month'] == $monthName)
						{
							$graphDataArr['orderAmount'][$i] = $results[$j]->order_amount;
							break;
						}
						else
						{
							$graphDataArr['orderAmount'][$i] = "0";
						}
					}
				}
				else
				{
					$graphDataArr['orderAmount'][$i] = "0";
				}
			}
		}

		$avgOrdersAmount = $totalOrdersAmt / $graphDuration;

		$graphDataArr['totalOrdersAmount'] = JText::_("COM_JTICKETING_EVENT_DETAIL_TOTAL_ORDERS_AMOUNT") .
											$currency . @number_format($totalOrdersAmt, 2, '.', ',');
		$graphDataArr['avgOrdersAmount'] = JText::_("COM_JTICKETING_EVENT_DETAIL_AVG_ORDERS_AMOUNT") .
											$currency . @number_format($avgOrdersAmount, 2, '.', ',');

		if ($duration == 1)
		{
			$graphOrderAmount = array_chunk($graphDataArr['orderAmount'], $arraychunkvar);
			$graphOrderAmountNewArr = array();

			$graphOrderAvgAmount = array_chunk($graphDataArr['orderAvg'], $arraychunkvar);
			$graphOrderAvgAmountNewArr = array();

			for ($i = 0; $i < count($graphOrderAmount); $i++)
			{
				$graphOrderAmountNewArr[] = array_sum($graphOrderAmount[$i]);
				$graphDataArr['orderAmount'] = $graphOrderAmountNewArr;

				// Avg Donation divide in chunk
				$graphOrderAvgAmountNewArr[] = array_sum($graphOrderAvgAmount[$i]);
				$graphDataArr['orderAvg'] = $graphOrderAvgAmountNewArr;
			}

			$graphOrderDate = array_chunk($graphDataArr['orderDate'], $arraychunkvar);
			$graphOrderDateNewArr = [];

			for ($i = 0; $i < count($graphOrderDate); $i++)
			{
				$graphOrderDateNewArr[] = reset($graphOrderDate[$i]);
				$graphDataArr['orderDate'] = $graphOrderDateNewArr;
			}
		}

		if ($duration == 0)
		{
			for ($k = 0; $k < count($graphDataArr['orderDate']); $k++)
			{
				$graphDataArr['orderDate'][$k] = date("D", strtotime($graphDataArr['orderDate'][$k]));
			}
		}
		elseif ($duration == 1)
		{
			for ($k = 0; $k < count($graphDataArr['orderDate']); $k++)
			{
				$graphDataArr['orderDate'][$k] = date("d/m", strtotime($graphDataArr['orderDate'][$k]));
			}
		}

		if ($duration == 0 || $duration == 1)
		{
			$graphDataArr['orderAvg'] = array_reverse($graphDataArr['orderAvg']);
			$graphDataArr['orderAmount'] = array_reverse($graphDataArr['orderAmount']);
			$graphDataArr['orderDate'] = array_reverse($graphDataArr['orderDate']);
		}

		echo json_encode($graphDataArr);
		jexit();
	}

	/**
	 * View more Attendee information
	 *
	 * @return  Attendee information
	 *
	 * since 1.7
	 */
	public function viewMoreAttendee()
	{
		$input = JFactory::getApplication()->input;
		$post  = $input->post;

		$eventId         = $post->get('eventId', '', 'INT');
		$jticketing_index = $post->get('jticketing_index', '', 'INT');

		$model  = $this->getModel('event');
		$result = $model->viewMoreAttendee($eventId, $jticketing_index);

		echo json_encode($result);
		jexit();
	}
}
