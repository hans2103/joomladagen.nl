<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');
JLoader::import('attendeefields', JPATH_SITE . '/components/com_jticketing/models');
JLoader::import('tickettypes', JPATH_SITE . '/components/com_jticketing/models');
JLoader::import('attendeecorefields', JPATH_ADMINISTRATOR . '/components/com_jticketing/models');

/**
 * JteventHelper
 *
 * @since  1.0
 */
class JteventHelper
{
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		// Add social library according to the social integration
		$Params = JComponentHelper::getParams('com_jticketing');
		$socialintegration = $Params->get('integrate_with', 'none');

		// Load main file
		jimport('techjoomla.jsocial.jsocial');
		jimport('techjoomla.jsocial.joomla');

		if ($socialintegration != 'none')
		{
			if ($socialintegration == 'JomSocial')
			{
				jimport('techjoomla.jsocial.jomsocial');
			}
			elseif ($socialintegration == 'EasySocial')
			{
				jimport('techjoomla.jsocial.easysocial');
			}
		}
	}

	/**
	 * cancal ordered ticket
	 *
	 * @param   integer  $order_id  order_id
	 *
	 * @return  object void
	 *
	 * @since   1.0
	 */
	public static function cancelTicket($order_id)
	{
		$path                     = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';
		$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

		if (!class_exists('jticketingmainhelper'))
		{
		JLoader::register('jticketingmainhelper', $path);
		JLoader::load('jticketingmainhelper');
		}

		if (!class_exists('jticketingfrontendhelper'))
		{
		JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
		JLoader::load('jticketingfrontendhelper');
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/controllers/attendee_list.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/attendee_list.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/orders.php';

		$JticketingModelattendee_List = new JticketingModelattendee_List;
		$JticketingModelattendee_List->cancelTicket($order_id);

		$paymentHelper = JPATH_SITE . '/components/com_jticketing/models/payment.php';

		if (!class_exists('jticketingModelpayment'))
		{
			JLoader::register('jticketingModelpayment', $paymentHelper);
			JLoader::load('jticketingModelpayment');
		}

		$orderobj = new jticketingModelorders;
		$status    = $orderobj->getOrderStatus($order_id);

		$obj       = new jticketingModelpayment;
		$orderobj->eventsTypesCountIncrease($order_id);
		$orderobj->updateOrderStatus($order_id, 'D', 1);
	}

	/**
	 * Get Social library object
	 *
	 * @param   integer  $integration_option  this may be joomla,jomsocial,Easysocial
	 *
	 * @return  object social library
	 *
	 * @since   1.0
	 */
	public function getJticketSocialLibObj($integration_option = '')
	{
		$jtParams = JComponentHelper::getParams('com_jticketing');
		$integration_option = $jtParams->get('integrate_with', 'none');

		if ($integration_option == 'Community Builder')
		{
			$SocialLibraryObject = new JSocialCB;
		}
		elseif ($integration_option == 'JomSocial')
		{
			$SocialLibraryObject = new JSocialJomsocial;
		}
		elseif ($integration_option == 'Jomwall')
		{
			$SocialLibraryObject = new JSocialJomwall;
		}
		elseif ($integration_option == 'EasySocial')
		{
			$SocialLibraryObject = new JSocialEasysocial;
		}
		elseif ($integration_option == 'none')
		{
			$SocialLibraryObject = new JSocialJoomla;
		}

		return $SocialLibraryObject;
	}

	/**
	 * This will add pending entries to reminder queue
	 *
	 * @param   INT  $xrefid  xrefid
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updateReminderQueue($xrefid ='')
	{
		$db = JFactory::getDbo();

		// Delete entries which are present for that reminder and still not sent
		$query = 'SELECT id FROM #__jticketing_queue
			WHERE sent=0';

		if ($xrefid)
		{
			$query .= " and event_id = " . $xrefid;
		}

		$db->setQuery($query);
		$reminder_queue_ids = $db->loadObjectList();

		if (!empty($reminder_queue_ids))
		{
			foreach ($reminder_queue_ids AS $qid)
			{
				// Update entries for existing reminder
				$this->addPendingEntriestoQueue($xrefid, $qid->id);
			}
		}
		else
		{
			$this->addPendingEntriestoQueue($xrefid);
		}
	}

	/**
	 * This will add pending entries to reminder queue
	 *
	 * @param   INT  $xrefid             xrefid
	 * @param   INT  $reminder_queue_id  reminder_queue_id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addPendingEntriestoQueue($xrefid = '', $reminder_queue_id = '')
	{
		$db = JFactory::getDbo();
		$input = JFactory::getApplication()->input;
		$jticketingmainhelper = new Jticketingmainhelper;
		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$integration              = $jticketingmainhelper->getIntegration();
		$client                   = $jticketingfrontendhelper->getClientName($integration);

		/*$events = $jticketingmainhelper->getEvents();

		$query                = "select max(remtypes.days)
		from #__jticketing_reminder_types AS remtypes
		WHERE state=1";

		$db->setQuery($query);
		$days = $db->loadResult();
		$today        = date('Y-m-d');
		$date_expires = strtotime($today. ' +'.$days.'day');
		$date_expires = date('Y-m-d', $date_expires);

		$date_expires_old = strtotime($today. ' -2day');
		$date_expires_old = date('Y-m-d', $date_expires_old);

		$newevent = array();
		$i =0;

		foreach ($events AS $event)
		{

			$evstartdate = $event['startdate'];

			if ($evstartdate >= $date_expires_old and $evstartdate <= $date_expires)
			{
				$newevent[$i] =new stdclass;
				$newevent[$i]->startdate = $evstartdate;
				$newevent[$i]->eventid =  $event['id'];
				$i++;
			}
		}*/

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

		if ($xrefid)
		{
			$query .= " AND xref.id=" . $xrefid;
		}

		if (!empty($eventdt->eventid))
		{
			$query .= " AND xref.eventid=" . $eventdt->eventid;
		}

		$query .= " AND xref.source LIKE '" . $client . "'";

		$db->setQuery($query);
		$orders = $db->loadObjectList();

		if (!empty($orders))
		{
			foreach ($orders AS $orderdata)
			{
				$order = $jticketingmainhelper->getorderinfo($orderdata->id);
				$reminder_data              = '';
				$reminder_data              = $jticketingmainhelper->getticketDetails($orderdata->eventid, $order['items']['0']->order_items_id);
				$reminder_data->nofotickets = $orderdata->ticketscount;
				$reminder_data->totalprice  = $orderdata->amount;
				$reminder_data->eid         = $orderdata->eventid;

				if ($reminder_queue_id)
				{
					$reminder_data->reminder_queue_id         = $reminder_queue_id;
				}

				$eventupdate = $jticketingModelpayment->addtoReminderQueue($reminder_data, $order);
			}
		}
	}

	/**
	 * Function to idetify passed field hidden or not from component config.
	 *
	 * @param   String  $field_name  Description
	 *
	 * @return void
	 */
	public function filedToShowOrHide($field_name)
	{
		$params       = JComponentHelper::getParams('com_jticketing');
		$creatorfield = array();
		$creatorfield = $params->get('creatorfield');

		$show_selected_fields = $params->get('show_selected_fields');

		if ($show_selected_fields AND (!empty($creatorfield)))
		{
			// If field is hidden & not to show on form
			if (in_array($field_name, $creatorfield))
			{
				return false;
			}
		}

		// If field is to show on form
		return true;
	}

	/**
	 * Get Event Categories description
	 *
	 * @param   String  $firstOption  Description
	 *
	 * @return  Option
	 */
	public function getEventCategories($firstOption = '')
	{
		$db = JFactory::getDbo();
		$app     = JFactory::getApplication();
		$com_params = JComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');

		if ($integration == 1)
		{
			$source = 'com_community';
		}
		elseif ($integration == 2)
		{
			$source = 'com_jticketing';
		}
		elseif ($integration == 3)
		{
			$source = 'com_jevents';
		}
		elseif ($integration == 4)
		{
			$source = 'com_easysocial';
		}

		if ($source == 'com_jticketing' or  $source == 'com_jevents')
		{
			$categories  = JHtml::_('category.options', $source, array('filter.published' => array(1)));
			$cat_options = array();

			if (!empty($categories))
			{
				foreach ($categories as $category)
				{
					if (!empty($category))
					{
						$cat_options[] = JHtml::_('select.option', $category->value, $category->text);
					}
				}
			}
		}
		else
		{
			if ($source == 'com_easysocial')
			{
				$query = "Select id,title FROM #__social_clusters_categories WHERE type LIKE 'event'";
			}

			if ($source == 'com_community')
			{
				$query = "Select id,name AS title FROM #__community_events_category";
			}

			$db->setQuery($query);
			$categories = $db->loadObjectlist();

			if (!empty($categories))
			{
				$cat_options[] = JHtml::_('select.option', "0", "All Category");

				foreach ($categories as $category)
				{
					$cat_options[] = JHtml::_('select.option', $category->id, $category->title);
				}
			}
		}

		return $cat_options;
	}

	/**
	 * Get Event Type description
	 *
	 * @return  Option
	 */
	public function getEventType()
	{
		$online_events   = array();
		$online_events[] = JHtml::_('select.option', '', JText::_('COM_JTK_FILTER_SELECT_EVENT_DEFAULT'));
		$online_events[] = JHtml::_('select.option', '0', JText::_('COM_JTK_FILTER_SELECT_EVENT_OFFLINE'));
		$online_events[] = JHtml::_('select.option', '1', JText::_('COM_JTK_FILTER_SELECT_EVENT_ONLINE'));

		return $online_events;
	}

	/**
	 * EventsToShowOptions description
	 *
	 * @return  Array  Options
	 */
	public function eventsToShowOptions()
	{
		$options = array();
		$app     = JFactory::getApplication();
		$options[] = JHtml::_('select.option', 'featured', JText::_('COM_JTK_FEATURED_CAMP'));
		$options[] = JHtml::_('select.option', '0', JText::_('COM_JTK_FILTER_ONGOING'));
		$options[] = JHtml::_('select.option', '-1', JText::_('COM_JTK_FILTER_PAST_EVNTS'));

		return $options;
	}

	/**
	 * SaveCustom Attendee fields description
	 *
	 * @param   Array  $ticket_fields  Tickets fiedls
	 * @param   INT    $eventid        Event Id
	 * @param   INT    $userid         User Id
	 *
	 * @return void
	 */
	public function saveCustomAttendee_fields($ticket_fields, $eventid, $userid)
	{
		// Get xref id for this event
		$jticketingmainhelper = new jticketingmainhelper;
		$XrefID               = $jticketingmainhelper->getEventrefid($eventid);

		$db                       = JFactory::getDbo();
		$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

		if (!class_exists('jticketingfrontendhelper'))
		{
			JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
			JLoader::load('jticketingfrontendhelper');
		}

		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$fields_selected          = $fields_in_DB = array();
		$attendee_fields          = $jticketingfrontendhelper->getAllfields($eventid);

		// Firstly Delete Attendee Fields That are Removed
		foreach ($attendee_fields['attendee_fields'] as $atkey => $atvalue)
		{
			if ($atvalue->id)
			{
				$fields_in_DB[] = $atvalue->id;
			}
		}

		$fields_selected[] = '';

		foreach ($ticket_fields AS $key => $value)
		{
			if ($value['id'])
			{
				$fields_selected[] = $value['id'];
			}
		}

		if ($fields_in_DB)
		{
			$diff_ids = array_diff($fields_in_DB, $fields_selected);

			if (!empty($diff_ids))
			{
				$this->delete_Ateendee_fields($diff_ids);
			}
		}

		// Now Insert or Update New Fields
		foreach ($ticket_fields AS $tkey => $tvalue)
		{
			$ticket_field_to_insert = new StdClass;

			foreach ($tvalue AS $ntkey => $nvalue)
			{
				$ticket_field_to_insert->$ntkey = $nvalue;
			}

			$ticket_field_to_insert->eventid = $XrefID;
			$ticket_field_to_insert->state   = 1;

			if (!$ticket_field_to_insert->id)
			{
				// Create Unique Name
				$ticket_field_to_insert->name = $this->CreateField_Name($ticket_field_to_insert->label);

				if ($ticket_field_to_insert->label)
				{
					if (!$db->insertObject('#__jticketing_attendee_fields', $ticket_field_to_insert, 'id'))
					{
						echo $db->stderr();

						return false;
					}

					$tickettypeid = $db->insertid();
				}
			}
			else
			{
				$db->updateObject('#__jticketing_attendee_fields', $ticket_field_to_insert, 'id');
			}
		}
	}

	/**
	 * CreateField_Name description
	 *
	 * @param   String  $string  Description
	 *
	 * @return string
	 *
	 * @since  1.0
	 */
	public function CreateField_Name($string)
	{
		$string = strtolower($string);

		// Replaces all spaces with hyphens.
		$string = str_replace(' ', '_', $string);

		// Removes special chars.
		return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
	}

	/**
	 * Delete_Ateendee_fields description
	 *
	 * @param   Array  $delete_ids  Delete attendees ids
	 *
	 * @return void
	 */
	public function delete_Ateendee_fields($delete_ids)
	{
		$db = JFactory::getDbo();

		foreach ($delete_ids as $key => $value)
		{
			$query = 'DELETE FROM #__jticketing_attendee_fields
				WHERE id = "' . $value . '"';
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $db->stderr();

				return false;
			}
		}
	}

	/**
	 * Function that allows child controller access to model data
	 *
	 * @param   array  	$integration_ids  array of id of integration xref table
	 *
	 * @return   1 or 0
	 *
	 * @since   1.5.1
	 */
	public function delete_Event($integration_ids)
	{
		$db = JFactory::getDbo();

		// Delete From universal field values which are saved against that event
		$TjfieldsHelperPath = JPATH_ROOT . DS . 'components' . DS . 'com_tjfields' . DS . 'helpers' . DS . 'tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$content_id_array = $integration_ids;
		$TjfieldsHelper   = new TjfieldsHelper;
		$JteventHelper   = new JteventHelper;

		$this->deleteFieldValues($content_id_array, 'com_jticketing.event');

		foreach ($integration_ids AS $xrefid)
		{
			// Find main order
			$query = "SELECT id FROM #__jticketing_order WHERE event_details_id=" . $xrefid;
			$db->setQuery($query);
			$order_ids = $db->loadColumn();

			if (!empty($order_ids))
			{
				foreach ($order_ids AS $oid)
				{
					$query = "SELECT attendee_id FROM #__jticketing_order_items WHERE attendee_id<>0 AND attendee_id<>'' AND order_id=" . $oid;
					$db->setQuery($query);
					$attendee_ids     = $db->loadColumn();
					$attendee_ids_str = implode("','", $attendee_ids);

					if (!empty($attendee_ids))
					{
						// Delete From attendee field values
						$query = "DELETE FROM #__jticketing_attendee_field_values	WHERE attendee_id IN ('" . $attendee_ids_str . "') ";
						$db->setQuery($query);
						$db->execute();

						// Delete From attendees
						$query = "DELETE FROM #__jticketing_attendees	WHERE id IN ('" . $attendee_ids_str . "') ";
						$db->setQuery($query);
						$db->execute();
					}

					// Delete From order items
					$query = "SELECT id FROM #__jticketing_order_items WHERE order_id=" . $oid;
					$db->setQuery($query);
					$order_items_id     = $db->loadColumn();
					$order_items_id_str = implode("','", $order_items_id);

					$query = "DELETE FROM #__jticketing_order_items	WHERE id IN ('" . $order_items_id_str . "') ";
					$db->setQuery($query);
					$db->execute();
				}
			}

			// Delete From attendee fields per event
			$query = "DELETE FROM #__jticketing_attendee_fields	WHERE  eventid=" . $xrefid;
			$db->setQuery($query);
			$db->execute();

			// Delete Ticket Types
			$query = "DELETE FROM #__jticketing_types	WHERE  eventid=" . $xrefid;
			$db->setQuery($query);
			$db->execute();

			// Delete From Checkin Details Table
			$query = "DELETE FROM #__jticketing_checkindetails	WHERE eventid=" . $xrefid;
			$db->setQuery($query);
			$db->execute();

			// Delete From order table
			$query = "DELETE FROM #__jticketing_order	WHERE event_details_id=" . $xrefid;
			$db->setQuery($query);
			$db->execute();

			// Delete From xref table
			$query = "DELETE FROM #__jticketing_integration_xref	WHERE id=" . $xrefid;
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * UpdatePaypalEmail description
	 *
	 * @param   INT     $userid        UserId
	 * @param   String  $paypal_email  Email
	 *
	 * @return void
	 */
	public function updatePaypalEmail($userid, $paypal_email)
	{
		$db    = JFactory::getDbo();
		$paypal_email = trim($paypal_email);

		if (!empty($paypal_email))
		{
			$query = "UPDATE #__jticketing_integration_xref SET paypal_email='" . $paypal_email . "' WHERE userid=" . $userid;
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	/**
	 * FixavailableSeats description
	 *
	 * @param   INT     $available_current    Current available tickets
	 * @param   Object  $ticket_type_info_db  Ticket info
	 * @param   INT     $xrefid               Xref id
	 *
	 * @return void
	 */
	public function fixavailableSeats($available_current, $ticket_type_info_db, $xrefid)
	{
		$db         = JFactory::getDbo();
		$difference = 0;
		$difference = $available_current - $ticket_type_info_db->count;

		$query = "SELECT id from #__jticketing_order WHERE status LIKE 'C' AND event_details_id=" . $xrefid;
		$db->setQuery($query);
		$orders    = $db->loadObjectlist();
		$soldcount = '';

		if ($orders)
		{
			$soldcounts = '';

			foreach ($orders AS $order)
			{
				$soldres = '';
				$query   = "SELECT count(id) from #__jticketing_order_items WHERE order_id=" . $order->id . " AND type_id=" . $ticket_type_info_db->id;
				$db->setQuery($query);
				$soldres = $db->loadResult();

				if ($soldres)
				{
					$soldcounts[] = $soldres;
				}
			}

			$finalsoldcount = 0;

			foreach ($soldcounts AS $soldcount)
			{
				$finalsoldcount = $finalsoldcount + $soldcount;
			}

			$available_current = $available_current + $finalsoldcount;
		}
		else
		{
			if ($difference > 0)
			{
				$available_current = $ticket_type_info_db->count + $difference;
			}
			elseif ($difference < 0)
			{
				$positive_diff = ($difference * (-1));

				if ($ticket_type_info_db->count != 0)
				{
					$final_diff = $ticket_type_info_db->count - $positive_diff;

					// Do not make available as 0 since it will becomes unlimited seats
					if ($final_diff != 0)
					{
						$available_current = $ticket_type_info_db->count - $positive_diff;
					}
				}
			}
		}

		return $available_current;
	}

	/**
	 * SaveEvent description
	 *
	 * @param   INT     $eventid              Event Id
	 * @param   INT     $backend_integration  Integration set
	 * @param   String  $ev_creator           Event creator
	 *
	 * @return void
	 */
	public function saveEvent($eventid, $backend_integration = 1, $ev_creator = '')
	{
		$jteventHelper = new jteventHelper;
		$app = JFactory::getApplication();
		$input = $app->input;
		$userName = JFactory::getUser();
		$post  = $input->post;
		$this->loadJTclasses();
		$source = array(
			1 => 'com_community',
			2 => 'com_jticketing',
			3 => 'com_jevents',
			4 => 'com_easysocial'
		);

		// Get creator of event
		if (!$ev_creator)
		{
			$userid = JFactory::getUser()->id;
		}

		$dat                            = new Stdclass;
		$dat->source                    = $source[$backend_integration];
		$dat->userid                    = $userid;
		$com_params                     = JComponentHelper::getParams('com_jticketing');
		$collect_attendee_info_checkout = $com_params->get('collect_attendee_info_checkout');
		$enforceVendor = $com_params->get('enforce_vendor');
		$attendee_fields              = $post->get('attendeefields', '', 'array');
		$ticket_fields              = $post->get('tickettypes', '', 'array');
		$frontHelper = new Jticketingfrontendhelper;

		$data_edit = 0;
		$jticketingmainhelper = new jticketingmainhelper;
		$jteventHelper        = new jteventHelper;

		$xrefData = array();

			$tJvendorsHelper = new TjvendorsHelpersTjvendors;
			$getVendorId = $tJvendorsHelper->getVendorId($dat->userid);

			if (empty($getVendorId))
			{
				$vendorData['vendor_client'] = 'com_jticketing';
				$vendorData['user_id'] = $userid;
				$vendorData['vendor_title'] = $userName->name;
				$vendorData['state'] = "1";
				$vendorData['params'] = null;
				$xrefData['vendor_id'] = $tJvendorsHelper->addVendor($vendorData);
			}
			else
			{
				$xrefData['vendor_id'] = $getVendorId;
			}

			if ($eventid)
			{
				$xrefData['eventid'] = $eventid;
				$xrefData['source'] = $dat->source;
				$xrefData['userid'] = $dat->userid;
				$xrefId = $frontHelper->getXreftableID($dat->source, $xrefData['eventid']);

				if (empty($xrefId->eventid))
				{
					JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'integrationxref');
					$JTIcketingModelIntegrationXref = JModelLegacy::getInstance('Integrationxref', 'JTicketingModel');
					JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables', 'Integrationxref');
					$JTIcketingModelIntegrationXref->save($xrefData);
				}
			}

			$xrefId = $frontHelper->getXreftableID($dat->source, $eventid);

			// Save Attendee fields
			$attendeeFields = $attendee_fields;
			$attendeeFieldsModel = JModelLegacy::getInstance('Attendeefields', 'JticketingModel');
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables', 'Attendeefields');
			$attendeeCoreFieldsModel = JModelLegacy::getInstance('AttendeeCoreFields', 'JticketingModel');
			$existingAttendeeFields = $attendeeCoreFieldsModel->getAttendeeFields($xrefId->id);
			$attendeeFieldsArray = array();
			$newAttendeeField = array();
			$existingId = array();
			$newCount = 0;
			$existingCount = 0;

			// Saving new Attendee fields
			foreach ($attendeeFields as $attendeeField)
			{
				if (!empty($attendeeField['id']))
				{
					$attendeeFieldsArray['id'] = $attendeeField['id'];
				}
				else
				{
					$attendeeFieldsArray['id'] = '';
				}

				$attendeeFieldsArray['label'] = $attendeeField['label'];
				$attendeeFieldsArray['type'] = $attendeeField['type'];
				$attendeeFieldsArray['core'] = 0;
				$attendeeFieldsArray['default_selected_option'] = $attendeeField['default_selected_option'];
				$attendeeFieldsArray['required'] = $attendeeField['required'];
				$attendeeFieldsArray['eventid'] = $xrefId->id;
				$attendeeFieldsArray['state'] = 1;

				if (!empty($attendeeFieldsArray['label']))
				{
					$string = strtolower($attendeeField['label']);
					$string = str_replace(' ', '_', $string);
					$attendeeFieldsArray['name'] = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
					$return = $attendeeFieldsModel->save($attendeeFieldsArray);
				}
			}

			// Collecting existing attendee fields
			foreach ($existingAttendeeFields as $existingAttendeeField)
			{
				$existingId[$existingCount] = $existingAttendeeField['id'];
				$existingCount++;

				foreach ($attendeeFields as $attendeeField)
				{
					if ($attendeeField['id'] == $existingAttendeeField['id'])
					{
						$newAttendeeField[$newCount] = $attendeeField['id'];
						$newCount++;
					}
				}
			}

			// Collecting attendee fields to be deleted
			$invalidAttendeeFieldIds = array_diff($existingId, $newAttendeeField);

			// Deleting attendee fields
			foreach ($invalidAttendeeFieldIds as $invalidId)
			{
				// A check to see if this particular attendee field has any attendee field values against it.
				$attendeeFieldCheck = $attendeeFieldsModel->checkAttendeeFieldValue($invalidId);

				if (empty($attendeeFieldCheck))
				{
					$attendeeFieldsModel->delete($invalidId);
				}
				else
				{
					$app->enqueueMessage(JText::_('COM_JTICKETING_EVENT_ATENDEE_FIELDS_DELETE_ERROR'), 'warning');

					return false;
				}
			}

			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'tickettypes');
			$ticketTypesModel = JModelLegacy::getInstance('Tickettypes', 'JticketingModel');
			$ticketTypes = $ticket_fields;
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'tickettypes');
			$ticketTypesModel = JModelLegacy::getInstance('Tickettypes', 'JticketingModel');
			$existingTicketTypes = $ticketTypesModel->getTicketTypes($xrefId->id);

			$tickets = array();
			$newTicketType = array();
			$existingId = array();
			$newCount = 0;
			$existingCount = 0;

			// Saving new ticket types
			foreach ($ticketTypes as $ticketType)
			{
				if (!empty($ticketType['id']))
				{
					$tickets['id'] = $ticketType['id'];
				}
				else
				{
					$tickets['id'] = '';
				}

				$tickets['count'] = $ticketType['available'];
				$tickets['title'] = $ticketType['title'];
				$tickets['desc'] = $ticketType['desc'];
				$tickets['unlimited_seats'] = $ticketType['unlimited_seats'];
				$tickets['available'] = $ticketType['available'];
				$tickets['state'] = $ticketType['state'];
				$tickets['price'] = $ticketType['price'];

				if ($com_params->get('show_access_level') == 0)
				{
					$tickets['access'] = $com_params->get('default_accesslevels', '1');
				}
				else
				{
					$tickets['access'] = $ticketType['access'];
				}

				$tickets['eventid'] = $xrefId->id;
				$ticketTypesModel->save($tickets);
			}

			// Collecting existing ticket types
			foreach ($existingTicketTypes as $existingTicketType)
			{
				$existingId[$existingCount] = $existingTicketType['id'];
				$existingCount++;

				foreach ($ticketTypes as $ticketType)
				{
					if ($ticketType['id'] == $existingTicketType['id'])
					{
						$newTicketType[$newCount] = $ticketType['id'];
						$newCount++;
					}
				}
			}

			// Collecting ticket types to be deleted
			$invalidTicketTypeIds = array_diff($existingId, $newTicketType);

			// Deleting ticket types
			foreach ($invalidTicketTypeIds as $invalidId)
			{
				// A check to see if this particular ticket type has any orders against it.
				$ticketOrder = $ticketTypesModel->checkOrderExistsTicketType($invalidId);

				if (empty($ticketOrder))
				{
					$ticketTypesModel->delete($invalidId);
				}
				else
				{
					$app->enqueueMessage(JText::_('COM_JTICKETING_EVENT_TICKET_TYPES_DELETE_ERROR'), 'warning');

					return false;
				}
			}

		if ($data_edit == 1)
		{
			// Find event xref id and add entries to reminder queues
			$jteventHelper->updateReminderQueue($xrefid);
		}
	}

	/**
	 * Saveintegration description
	 *
	 * @param   INT  $eventid  Event id
	 * @param   INT  $dat      Description
	 *
	 * @return  INT  Integration xref if
	 */
	public function saveintegration($eventid, $dat)
	{
		$db                        = JFactory::getDbo();
		$integration               = new stdClass;
		$integration->id           = '';
		$integration->eventid      = $eventid;
		$integration->source       = $dat->source;
		$integration->paypal_email = $dat->paypal_email;
		$integration->userid       = $dat->userid;

		if (!$db->insertObject('#__jticketing_integration_xref', $integration, 'id'))
		{
			return false;
		}
		else
		{
			return $db->insertid();
		}
	}

	/**
	 * Updateintegration description
	 *
	 * @param   INT     $xrefid  Description
	 * @param   STRING  $dat     Description
	 *
	 * @return  void
	 */
	public function updateintegration($xrefid, $dat)
	{
		$db                        = JFactory::getDbo();
		$integration               = new stdClass;
		$integration->id           = $xrefid;

		// If (!empty($dat->paypal_email))
		{
			$integration->paypal_email = $dat->paypal_email;
		}

		$db->updateObject('#__jticketing_integration_xref', $integration, 'id');

		return $xrefid;
	}

	/**
	 * loadJTclasses description
	 *
	 * @return void
	 */
	public function loadJTclasses()
	{
		// Load all required helpers.
		$jticketingmainhelperPath = JPATH_ROOT . DS . 'components' . DS . 'com_jticketing' . DS . 'helpers' . DS . 'main.php';

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $jticketingmainhelperPath);
			JLoader::load('jticketingmainhelper');
		}

		$jticketingfrontendhelper = JPATH_ROOT . DS . 'components' . DS . 'com_jticketing' . DS . 'helpers' . DS . 'frontendhelper.php';

		if (!class_exists('jticketingfrontendhelper'))
		{
			JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
			JLoader::load('jticketingfrontendhelper');
		}

		$jteventHelperPath = JPATH_ROOT . DS . 'components' . DS . 'com_jticketing' . DS . 'helpers' . DS . 'event.php';

		if (!class_exists('jteventHelper'))
		{
			JLoader::register('jteventHelper', $jteventHelperPath);
			JLoader::load('jteventHelper');
		}
	}

	/**
	 * Validate JomSocial integration.
	 *
	 * @param   String  $backend_integration  Integration set
	 *
	 * @return  Boolean  Depend on the result
	 */
	public function validateIntegration($backend_integration)
	{
		$com_params  = JComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');

		if ($integration != $backend_integration)
		{
			return false;
		}

		return true;
	}

	/**
	 * Delete field values in tjfields table
	 *
	 * @param   array   $content_id_array  array of content ID
	 * @param   string  $client            Client Name()
	 *
	 * @return  void
	 */
	public function deleteFieldValues($content_id_array,$client)
	{
		$db = JFactory::getDbo();

		if (!empty($content_id_array))
		{
			$content_id_str = implode("','", $content_id_array);
			$query = "DELETE FROM #__tjfields_fields_value
			WHERE  content_id IN ('" . $content_id_str . "') AND client LIKE '" . $client . "'";
			$db->setQuery($query);

			if (!$db->execute())
			{
			}
		}
	}

	/**
	 * Function to get specific col of specific event
	 *
	 * @param   int  $event_id       id of event
	 * @param   ARR  $columns_array  array of teh columns
	 *
	 * @return  Object  $statusDetails
	 *
	 * @since  1.0.0
	 */
	public function getEventColumn($event_id,$columns_array)
	{
		$db   = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($columns_array);
		$query->from($db->quoteName('#__jticketing_events'));
		$query->where($db->quoteName('id') . " = " . $event_id);

		$db->setQuery($query);
		$event = $db->loadObject();

		return $event;
	}

	/**
	 * Function getCoordinates
	 *
	 * @param   int  $id     id of event
	 * @param   ARR  $venue  array of teh columns
	 *
	 * @return  Object  $statusDetails
	 *
	 * @since  1.0.0
	 */
	public function getCoordinates($id, $venue)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		if ($venue == '0')
		{
			$query->select($db->quoteName('location'));
			$query->from($db->quoteName('#__jticketing_events'));
			$query->where($db->quoteName('id') . ' = ' . $db->quote($id));
		}
		else
		{
			$query->select($db->quoteName('address'));
			$query->from($db->quoteName('#__jticketing_venues'));
			$query->where($db->quoteName('id') . ' = ' . $db->quote($venue));
		}

		$db->setQuery($query);
		$res = $db->loadResult();
		$string = str_replace(",", "+", $res);
		$array = explode(" ", $string);
		$address = implode($array);
		$request = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=" . $address . "&sensor=false");
		$decodedCoOrdinates = json_decode($request);

		if (!empty($decodedCoOrdinates))
		{
			$coOrdinates = array();
			$coOrdinates['latitude'] = $decodedCoOrdinates->results[0]->geometry->location->lat;
			$coOrdinates['longitude'] = $decodedCoOrdinates->results[0]->geometry->location->lng;

			return $coOrdinates;
		}

		return false;
	}

	/**
	 * function for generating ICS file.
	 *
	 * @param   integer  $data  data of event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function generateIcs($data)
	{
		ob_start();
		include JPATH_SITE . '/components/com_jticketing/views/event/tmpl/eventIcs.php';
		$html .= ob_get_contents();
		ob_end_clean();

		$file    = str_replace(" ", "_", $data['title']);
		$file    = str_replace("/", "", $data['title']);
		$file = preg_replace('/\s+/', '', $file);
		$icsFileName = $file . "" . $data['created_by'] . ".ics";
		$icsname = JPATH_SITE . "/libraries/techjoomla/dompdf/tmp/" . $icsFileName;
		$file    = fopen($icsname, "w");

		if ($file)
		{
			fwrite($file, $html);
			fclose($file);
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  $data  data of event
	 *
	 * @return	JObject
	 *
	 * @since	1.6
	 */
	public static function deleteIcs($data)
	{
		$file = JPATH_SITE . "/libraries/techjoomla/dompdf/tmp/" . preg_replace('/\s+/', '', $data['eventTitle'] . '' . $data['createdBy'] . '.ics');

		if (!($file))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTICKETING_EVENT_NO_FILE_FOUND'));
		}
		elseif (unlink($file))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTICKETING_MEDIA_FILE_DELETED'));
		}
	}

	/**
	 * Get recommend user for the course.
	 *
	 * @param   int  $courseId  id of course
	 * @param   int  $userId    id of user
	 *
	 * @return  ARRAY $record
	 *
	 * @since   1.0.0
	 */
	public function getuserRecommendedUsers($courseId, $userId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('lr.assigned_to');
		$query->from('`#__jlike_todos` as lr');
		$query->join('INNER', '`#__jlike_content` as lc ON lc.id=lr.content_id');
		$query->where('lr.assigned_by=' . (int) $userId);
		$query->where('lr.type="reco"');
		$query->where('lc.element_id=' . (int) $courseId . ' LIMIT 0,5');

		// Set the query for execution.
		$db->setQuery($query);

		$recommendedusers = $db->loadColumn();

		foreach ($recommendedusers as $index => $recommend_userid)
		{
			$this->sociallibraryobj = $this->getJticketSocialLibObj();
			$recommendedusers[$index] = new stdClass;
			$student = JFactory::getUser($recommend_userid);
			$recommendedusers[$index]->username = JFactory::getUser($recommend_userid)->username;
			$recommendedusers[$index]->name = JFactory::getUser($recommend_userid)->name;
			$recommendedusers[$index]->avatar = $this->sociallibraryobj->getAvatar($student, 50);

			$link = '';

			if ($this->sociallibraryobj->getProfileUrl($student))
			{
				$link = JUri::root() . substr(JRoute::_($this->sociallibraryobj->getProfileUrl($student)), strlen(JUri::base(true)) + 1);
			}

			$recommendedusers[$index]->profileurl = $link;
		}

		return $recommendedusers;
	}

	/**
	 * Method getEventAttendeeInfo
	 *
	 * @param   integer  $eventId      event Id
	 * @param   integer  $limit_start  limit start value
	 * @param   integer  $limit        limit
	 *
	 * @return	JObject
	 *
	 * @since	1.6
	 */
	public function getEventAttendeeInfo($eventId, $limit_start = 0, $limit = null)
	{
		if ($eventId)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select(
			array(
				'o.customer_note, o.amount as amount, check.checkin, o.order_id as order_id,
				o.email as buyeremail, i.eventid as evid, o.cdate, e.attendee_id, o.id, o.status,
				o.name, o.event_details_id, o.user_id, e.type_id, e.id AS order_items_id, e.ticketcount AS ticketcount,
				f.title AS ticket_type_title, f.price AS amount, (f.price * e.ticketcount) AS totalamount,
				user.firstname, user.lastname'
				)
			);
			$query->from($db->qn('#__jticketing_order', 'o'));
			$query->join('LEFT', $db->qn('#__jticketing_order_items', 'e') . 'ON (' . $db->qn('o.id') . ' = ' . $db->qn('e.order_id') . ')');
			$query->join('LEFT', $db->qn('#__jticketing_integration_xref', 'i') . 'ON (' . $db->qn('i.id') . ' = ' . $db->qn('o.event_details_id') . ')');
			$query->join('LEFT', $db->qn('#__jticketing_checkindetails', 'check') . 'ON (' . $db->qn('check.ticketid') . ' = ' . $db->qn('e.id') . ')');
			$query->join('INNER', $db->qn('#__jticketing_types', 'f') . 'ON (' . $db->qn('f.id') . ' = ' . $db->qn('e.type_id') . ')');
			$query->join('INNER', $db->qn('#__jticketing_users', 'user') . 'ON (' . $db->qn('o.id') . ' = ' . $db->qn('user.order_id') . ')');
			$query->where($db->qn('o.event_details_id') . ' = ' . $db->quote($eventId) . "AND" . $db->qn('o.status') . ' = ' . $db->quote("C"));
			$query->order($db->quote('o.id') . 'DESC');

			if ($limit != null)
			{
				$query->setLimit($limit);
			}

			$db->setQuery($query);
			$results = $db->loadObjectList();

			return $results;
		}

		return false;
	}

	/**
	 * Method to get Buyers count
	 *
	 * @param   integer  $id  event id
	 *
	 * @return  integer
	 *
	 * @since   1.6
	 */
	public function getBuyersCount($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('count(ordr.user_id) AS buyersCount');
		$query->from('#__jticketing_order AS ordr');
		$query->where('ordr.event_details_id = ' . $id . ' AND ordr.status = "C"');
		$db->setQuery($query);
		$buyersCount = $db->loadResult();

		return $buyersCount;
	}
}
