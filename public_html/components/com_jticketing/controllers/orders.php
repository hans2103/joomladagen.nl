<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

/**
 * controller for order
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingControllerorders extends JControllerLegacy
{
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		JLoader::import('components.com_jticketing.helpers.route', JPATH_SITE);
		$this->JTRouteHelper = new JTRouteHelper;
		$this->jticketingmainhelper = new jticketingmainhelper;
	}

	/**
	 * Changes order status for example pending to completed
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function save()
	{
		$com_params               = JComponentHelper::getParams('com_jticketing');
		$model         = $this->getModel('orders');
		$input         = JFactory::getApplication()->input;
		$post          = $input->post;
		$paymentHelper = JPATH_SITE . '/components/com_jticketing/models/payment.php';
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

		if (!class_exists('jticketingModelpayment'))
		{
			JLoader::register('jticketingModelpayment', $paymentHelper);
			JLoader::load('jticketingModelpayment');
		}

		$orderobj = new jticketingModelorders;

		if ($post->get('payment_status') == 'C')
		{
			$order_id  = $post->get('order_id');
			$obj       = new jticketingModelpayment;
			$member_id = $obj->getEventMemberid($order_id, 'P');
			$orderobj->updateOrderStatus($order_id, $post->get('payment_status'));
			$eventupdate          = $obj->eventupdate($order_id, $member_id);
			$jticketingfrontendhelper = new jticketingfrontendhelper;
			$jticketingmainhelper     = new jticketingmainhelper;
			$JticketingMailHelper = new JticketingMailHelper;
			$orderobj->eventsTypesCountDecrease($order_id);

			// Add entries to reminder queue to send reminder for Event
			$order                      = $this->jticketingmainhelper->getorderinfo($order_id);
			$orderid = $order['order_info']['0']->orderid_with_prefix;

			// Add entries to JLike TODO table to send reminder for Event
			$reminderData             = $order['eventinfo'];
			$eventupdate                = $obj->addtoReminderQueue($reminderData, $order['order_info'][0]->user_id);

			if (!$order['eventinfo']->online_events)
			{
				$email = JticketingMailHelper::sendmailnotify($order_id, 'afterordermail');
			}
			// Enroll user to Online events.
			$integration = $com_params->get('integration');
			$eventType = $order['eventinfo']->online_events;

			if ($integration == 2 && $eventType == 1)
			{
				$meeting_url           = json_decode($order['eventinfo']->jt_params);
				$orderDetails          = $jticketingmainhelper->getOrderDetail($order_id);
				$venueDetails          = $jticketingfrontendhelper->getvenue($order['eventinfo']->venue);
				$venueParams           = (object) $venueDetails->params;
				$randomPassword        = $jticketingmainhelper->rand_str(8);
				$venueParams->user_id  = $orderDetails->user_id;
				$venueParams->name     = $orderDetails->name;
				$venueParams->email    = $orderDetails->email;
				$venueParams->password = $randomPassword;
				$venueParams->meeting_url = $meeting_url->event_url;
				$venueParams->sco_id = $meeting_url->event_sco_id;

				if (($eventType == '1') && (!empty($venueParams->meeting_url)))
				{
					// TRIGGER After create event
					$dispatcher = JDispatcher::getInstance();
					JPluginHelper::importPlugin('tjevents');
					$result = $dispatcher->trigger('tj_inviteUsers', array($venueParams));
					$email  = JticketingMailHelper::onlineEventNotify($order_id, $venueParams, $order['eventinfo']);
				}
			}

			// Redirect link
			$link = 'index.php?option=com_jticketing&view=orders';
			$ordersLink = $this->JTRouteHelper->JTRoute($link);
		}
		else
		{
			$order_id  = $post->get('order_id');
			$status    = $orderobj->getOrderStatus($order_id);
			$obj       = new jticketingModelpayment;
			$member_id = $obj->getEventMemberid($order_id, 'C');
			$orderobj->eventsTypesCountIncrease($order_id);
			$orderobj->updateOrderStatus($order_id, $post->get('payment_status'));
			$link = 'index.php?option=com_jticketing&view=orders';
			$ordersLink = $this->JTRouteHelper->JTRoute($link);
		}

		$this->setRedirect($ordersLink, $msg);
	}

	/**
	 * Retry payment gateway on confirm payment view frontend.
	 *
	 * @return  json.
	 *
	 * @since   1.6
	 */
	public function retryPayment()
	{
		$input = JFactory::getApplication()->input;
		$getdata = $input->get;
		$pg_plugin = $getdata->get('gateway_name', '', 'STRING');
		$order = $getdata->get('order', '', 'STRING');
		$orders = (explode("-", $order));
		$order_id = $orders[1];
		$modelObj = $this->getModel('payment');
		$payment_getway_form = $modelObj->getHTMLS($pg_plugin, $order_id, $order);

		echo json_encode($payment_getway_form);
		jexit();
	}

	/**
	 * Get Ticket types data
	 *
	 * @param   integer  $eventid  eventid
	 *
	 * @return  array  ticket types
	 *
	 * @since   1.0
	 */
	public function gettickettypesdata($eventid)
	{
		if (empty($eventid))
		{
			$eventid = $input->get('eventid');
		}

		if (empty($client))
		{
			echo "Please select integration in backend option";
		}

		$jticketingmainhelper     = new jticketingmainhelper;
		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$integration              = $jticketingmainhelper->getIntegration();
		$client                   = $jticketingfrontendhelper->getClientName($integration);

		if (empty($client))
		{
			echo "Please select integration in backend option";
		}

		$query = "SELECT id FROM #__jticketing_types WHERE state=1 AND eventid = " . $integration;
		$db->setQuery($query);

		return $ticket_types = $db->loadAssocList();
	}

	/**
	 * Book tickets based on data
	 *
	 * @param   integer  $userid        eventid
	 * @param   string   $profile_type  profile_type
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getUserMobile($userid, $profile_type = 'joomla')
	{
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$data1 = $dispatcher->trigger('jt_OnBeforeMobileforReminder', array($userid, $profile_type));

		if (!empty($data1['0']))
		{
			return $mobile_no = $data1['0'];
		}

		/* @TODO for bajaj add this to plugin
		if($userid)
		{
		$query = "SELECT mobile FROM #__tjlms_user_xref WHERE `user_id`=".$userid;
		$db->setQuery($query);

		return $mobile_no = $db->loadResult();
		}*/

		$db    = JFactory::getDBO();
		$query = "SELECT profile_value FROM #__user_profiles WHERE `profile_key` like 'profile.phone'";
		$db->setQuery($query);

		return $mobile_no = $db->loadResult();
	}

	/**
	 * Generate random no
	 *
	 * @param   integer  $length  length for field
	 * @param   string   $chars   Allowed characters
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
	{
		// Length of character list
		$chars_length = (strlen($chars) - 1);

		// Start our string
		$string = $chars{rand(0, $chars_length)};

		// Generate random string
		for ($i = 1; $i < $length; $i = strlen($string))
		{
			// Grab a random character from our list
			$r = $chars{rand(0, $chars_length)};

			// Make sure the same two characters don't appear next to each other
			if ($r != $string{$i - 1})
			{
				$string .= $r;
			}
		}

		// Return the string
		return $string;
	}

	/**
	 * Send Reminders to client
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendReminder()
	{
		$model    = $this->getModel('orders');
		$response = $model->sendReminder();
	}

	/**
	 * This function fixes available seats for ticket types(if only one ticket type present)
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function fixSeats()
	{
		$db = JFactory::getDBO();

		$query = "SELECT SUM( orderd.ticketscount ) AS seats, xref.eventid,xref.id
		FROM  #__jticketing_order AS orderd,  #__jticketing_integration_xref AS xref
		WHERE STATUS =  'C'
		AND orderd.event_details_id = xref.id
		GROUP BY orderd.event_details_id";
		$db->setQuery($query);
		$eventlists = $db->loadObjectList();

		if (!empty($eventlists))
		{
			foreach ($eventlists AS $events)
			{
				$obj          = new StdClass;
				$obj->eventid = $events->id;
				$query        = "SELECT count(`id`) FROM #__jticketing_types WHERE eventid=" . $obj->eventid;
				$db->setQuery($query);
				$records = 0;
				$records = $db->loadResult();

				if ($records == 1)
				{
					$query        = "SELECT available FROM #__jticketing_types WHERE eventid=" . $obj->eventid;
					$db->setQuery($query);
					$available  = $db->loadResult();
					echo "count==" . $obj->count = $available - $events->seats;

					if (!$db->updateObject('#__jticketing_types', $obj, 'eventid'))
					{
					}
				}
			}
		}
	}

	/**
	 * Send Pending ticket Emails to purchaser
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendPendingTicketEmails()
	{
		$db = JFactory::getDBO();

		$input                  = JFactory::getApplication()->input;
		$Jticketingmainhelper   = new Jticketingmainhelper;
		$com_params             = JComponentHelper::getParams('com_jticketing');
		$integration            = $com_params->get('integration');
		$pkey_for_pending_email = $com_params->get("pkey_for_pending_email");
		$input                  = JFactory::getApplication()->input;
		$private_keyinurl       = $input->get('pkey', '', 'STRING');
		$passed_start           = $input->get('start_date', '', 'STRING');
		$passed_end             = $input->get('end_date', '', 'STRING');
		$accessible_groups_str  = $input->get('accessible_groups', '', 'STRING');
		$accessible_groups      = explode(",", $accessible_groups_str);
		$event_ids              = $input->get('event_id', '', 'STRING');
		$today_date             = date('Y-m-d');
		$skipuser = '';

		if ($pkey_for_pending_email != $private_keyinurl)
		{
			echo "You are Not authorized To send Pending mails";

			return;
		}

		$pending_email_batch_size = $com_params->get("pending_email_batch_size");
		$enb_batch                = $com_params->get("pending_email_enb_batch");
		$path                     = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';
		$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

		if (!class_exists('jticketingfrontendhelper'))
		{
			JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
			JLoader::load('jticketingfrontendhelper');
		}

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $path);
			JLoader::load('jticketingmainhelper');
		}

		$Jticketingfrontendhelper = new Jticketingfrontendhelper;
		$Jticketingfrontendhelper->loadHelperClasses();
		$jticketingmainhelper = new jticketingmainhelper;
		$clientnm             = $Jticketingfrontendhelper->getClientName($integration);

		if ($integration == 2)
		{
			$query = "SELECT orderd.*,xref.eventid AS eventid
			FROM  #__jticketing_order AS orderd,#__jticketing_events AS events,#__jticketing_integration_xref AS xref
			WHERE orderd.STATUS =  'C' AND orderd.ticket_email_sent=0
			AND orderd.event_details_id = xref.id AND xref.eventid=events.id AND  DATE(NOW()) <= DATE(`startdate`)";

			if ($passed_start)
			{
				$query .= " AND DATE(`startdate`)>='$passed_start' ";
			}

			if ($passed_end)
			{
				$query .= " AND DATE(`startdate`)<='$passed_end'";
			}

			if ($event_ids)
			{
				$event_id_arr = explode(",", $event_ids);
				$event_id_str = implode("','", $event_id_arr);
				$query .= " AND events.id IN ('$event_id_str')";
			}
		}
		else
		{
			$query = "SELECT orderd.*,xref.eventid
			FROM  #__jticketing_order AS orderd,#__jticketing_integration_xref AS xref WHERE orderd.STATUS =  'C' AND orderd.ticket_email_sent=0
			AND orderd.event_details_id = xref.id AND xref.source='" . $clientnm . "'";
		}

		if ($enb_batch == '1')
		{
			$query .= " LIMIT {$pending_email_batch_size}";
		}

		$db->setQuery($query);
		$orders = $db->loadObjectList();
		$result = array();
		$i      = 0;

		foreach ($orders AS $orderdata)
		{
			$allow_email = $email = 0;

			if ($integration != 2)
			{
				$eventdetails = $Jticketingmainhelper->getAllEventDetails($orderdata->eventid);

				if (date($eventdetails->startdate) < $today_date)
				{
					continue;
				}
			}

			if ($accessible_groups_str)
			{
				$uid = $orderdata->user_id;
				$query = $db->getQuery(true);
				$query
				->select('title')->from('#__usergroups')
				->where('id IN (' . implode(',', array_values(JFactory::getUser($uid)->groups)) . ')');
				$db->setQuery($query);
				$groups = $db->loadColumn();
				$allow_email = count(array_intersect($groups, $accessible_groups));

				if ($allow_email)
				{
					$email = JticketingMailHelper::sendmailnotify($orderdata->id, 'afterordermail');
				}
				else
				{
					$skipuser = 1;
				}
			}
			else
			{
				$email = JticketingMailHelper::sendmailnotify($orderdata->id, 'afterordermail');
			}

			if ($email['success'])
			{
				$obj                    = new StdClass;
				$obj->id                = $orderdata->id;

				$obj->ticket_email_sent = 1;

				if ($db->updateObject('#__jticketing_order', $obj, 'id'))
				{
				}

				echo "==Mailsent Successfully===";
				echo "<br/>";
				echo "<br/>";
				echo "To Email===" . $orderdata->email;
				echo "<br/>";
				echo "<br/>";
			}

			if ($skipuser)
			{
				echo "===Skipping since group is==" . implode(",", $groups);
			}

			$i++;
		}
	}

	/**
	 * This will add pending entries to reminder queue
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addPendingEntriestoQueue()
	{
		$db = JFactory::getDBO();

		$input = JFactory::getApplication()->input;
		$pkay  = $input->get('pkey', '');

		if ($pkey != "ascd2456")
		{
		}

		$path                             = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';
		$jticketingfrontendhelper         = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';
		$JTicketingIntegrationsHelperPath = JPATH_ROOT . '/components/com_jticketing/helpers/integrations.php';
		$helperPath                       = JPATH_SITE . '/components/com_jticketing/helpers/event.php';
		$mediaHelperPath                  = JPATH_SITE . '/components/com_jticketing/helpers/media.php';
		$field_manager_path               = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('jticketingfrontendhelper'))
		{
			JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
			JLoader::load('jticketingfrontendhelper');
		}

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $path);
			JLoader::load('jticketingmainhelper');
		}

		$paymenthelper = JPATH_ROOT . '/components/com_jticketing/models/payment.php';

		if (!class_exists('jticketingModelpayment'))
		{
			JLoader::register('jticketingModelpayment', $paymenthelper);
			JLoader::load('jticketingModelpayment');
		}

		$JticketingModelbuypath = JPATH_ROOT . '/components/com_jticketing/models/buy.php';

		if (!class_exists('JticketingModelbuy'))
		{
			JLoader::register('JticketingModelbuy', $JticketingModelbuypath);
			JLoader::load('JticketingModelbuy');
		}

		$JticketingModelbuy       = new JticketingModelbuy;
		$jticketingModelpayment   = new jticketingModelpayment;
		$Jticketingfrontendhelper = new Jticketingfrontendhelper;
		$Jticketingfrontendhelper->loadHelperClasses();
		$jticketingmainhelper = new jticketingmainhelper;
		$query                = "SELECT orderd.*,xref.eventid AS eventid
		FROM  #__jticketing_order AS orderd,  #__jticketing_integration_xref AS xref
		WHERE STATUS =  'C'
		AND orderd.event_details_id = xref.id";
		$db->setQuery($query);
		$orders = $db->loadObjectList();

		foreach ($orders AS $orderdata)
		{
			$order = $jticketingmainhelper->getorderinfo($orderdata->id);

			if ($order['order_info']['0']->user_id)
			{
				$order['order_info']['0']->user_email = JFactory::getUser($order['order_info']['0']->user_id)->email;
			}

			$reminder_data              = '';
			$reminder_data              = $jticketingmainhelper->getticketDetails($orderdata->eventid, $order['items']['0']->order_items_id);
			$reminder_data->ticketprice = $data->ticketprice;
			$reminder_data->nofotickets = $data->ticketscount;
			$reminder_data->totalprice  = $data->amount;
			$reminder_data->eid         = $orderdata->eventid;

			$eventupdate = $jticketingModelpayment->addtoReminderQueue($order['eventinfo'], $order['order_info']['0']->user_id);
		}
	}
}
