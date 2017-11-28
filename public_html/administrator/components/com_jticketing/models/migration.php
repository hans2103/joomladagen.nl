<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/database.php';

/**
 * JTicketing Migration Model
 *
 * @since  1.6
 */
class JticketingModelMigration extends InstallerModelDatabase
{
	/**
	 * Method to add activity for old event prior to v 2.0
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function migrateData()
	{
		$migration = array();
		$migration['event'] = $this->addOldEventVendor();
		$migration['venue'] = $this->addOldVenueVendor();
		$migration['payout'] = $this->fixPayoutsTable();
		$migration['activity'] = $this->addActivity();
		$migration['media'] = $this->imageMigration();

		return $migration;
	}

	/**
	 * Method to add activity for event order
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function pushOrderActivity()
	{
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_activitystream/models');
		$activityStreamModelActivity = JModelLegacy::getInstance('Activity', 'ActivityStreamModel');

		JLoader::import('main', JPATH_SITE . '/components/com_jticketing/helpers');
		$jticketingMainHelper  = new Jticketingmainhelper;

		require_once JPATH_SITE . '/plugins/system/jticketingactivities/helper.php';
		$plgSystemJticketingActivities = new PlgSystemJticketingActivitiesHelper;

		// Actitivty for donations - start
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__jticketing_order'));
		$query->where($db->quoteName('status') . "=" . "'C'");
		$query->order('order_id ASC');
		$db->setQuery($query);
		$completedOrders = $db->loadColumn();

		foreach ($completedOrders as $completedOrder)
		{
			$orderDetails = $jticketingMainHelper->getOrderDetail($completedOrder);
			$eventData = $jticketingMainHelper->getAllEventDetails($orderDetails->event_details_id);

			// Append event details to order details
			$orderDetails->eventData = $eventData;

			$user = JFactory::getUser($orderDetails->user_id);
			$activityData = array();
			$activityData['id'] = '';
			$actorData = $plgSystemJticketingActivities->getActorData($user->get('id'));
			$activityData['actor'] = json_encode($actorData);
			$user = JFactory::getUser();
			$activityData['actor_id'] = $user->get('id');
			$activityData['created_date'] = $orderDetails->cdate;

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
			$objectData['amount'] = str_replace("&nbsp;", "", strip_tags($jticketingMainHelper->getFormattedPrice($orderDetails->amount)));
			$activityData['object'] = json_encode($objectData);
			$activityData['object_id'] = 'order';

			// Get event-target data
			$targetData = array();
			$targetData['id'] = $orderDetails->event_details_id;
			$targetData['type'] = 'event';
			$targetData['url'] = JUri::root() . 'index.php?option=com_jticketing&view=event&id=' . $orderDetails->event_details_id;
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

			$result = $activityStreamModelActivity->save($activityData);
		}

	return true;
	}

	/**
	 * transfer credit and debit entries in passbook table of vendors
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function fixPayoutsTable()
	{
		$check = $this->checkTableExists('jticketing_ticket_payouts');

		if ($check)
		{
			$oldCreditData = $this->getOldData();
			$oldPayoutData = $this->getOldPayoutData();

			if (empty($oldCreditData))
			{
				$db = JFactory::getDbo();
				$db->dropTable('#__jticketing_ticket_payouts', true);
			}
			else
			{
				if (!empty($oldPayoutData))
				{
					$result = $this->formatPayoutData($oldCreditData, $oldPayoutData);

					if ($result)
					{
						$db = JFactory::getDbo();
						$db->dropTable('#__jticketing_ticket_payouts', true);
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to add activity for old event prior to v 2.0
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function addActivity()
	{
		require_once JPATH_SITE . '/plugins/system/jticketingactivities/helper.php';
		$plgSystemJticketingActivities = new PlgSystemJticketingActivitiesHelper;

		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_activitystream/models');
		$activityStreamModelActivity = JModelLegacy::getInstance('Activity', 'ActivityStreamModel');

		$db    = JFactory::getDbo();

		// Getting all event id. Create the base select statement.
		$query = $db->getQuery(true);
		$query->select('eventid');
		$query->from($db->quoteName('#__jticketing_integration_xref', 'i'));
		$query->where($db->quoteName('i.source') . ' = ' . $db->quote("com_jticketing"));
		$db->setQuery($query);
		$eventIds = $db->loadColumn();

		$type = ("'event.addvideo', 'event.addimage','jticketing.addevent', 'jticketing.textpost',
		'event.extended', 'eventBooking.extended', 'jticketing.order'");
		$query = $db->getQuery(true);
		$query->select('DISTINCT target_id');
		$query->from($db->quoteName('#__tj_activities'));
		$query->where($db->quoteName('type') . ' IN (' . $type . ')');
		$db->setQuery($query);
		$targetIds = $db->loadColumn();

		if (!empty($eventIds))
		{
			foreach ($eventIds as $eventId)
			{
				// Add campaign create activity
				if (!in_array($eventId, $targetIds))
				{
					// Fetching event data by id
					$jtickeitngModelEventFrom = JModelLegacy::getInstance('eventform', 'JticketingModel');
					$eventData = $jtickeitngModelEventFrom->getItem($eventId);

					$user = JFactory::getUser($eventData->created_by);
					$activityData = array();
					$activityData['id'] = '';
					$activityData['created_date'] = $eventData->created;

					$actorData = $plgSystemJticketingActivities->getActorData($user->get('id'));
					$activityData['actor'] = json_encode($actorData);
					$activityData['actor_id'] = $user->get('id');

					$objectData = array();
					$objectData['type'] = 'event';
					$objectData['name'] = $eventData->title;
					$objectData['id'] = $eventData->id;
					$objectData['url'] = JUri::root() . 'index.php?option=com_jticketing&view=event&id=' . $eventData->id;
					$activityData['object'] = json_encode($objectData);
					$activityData['object_id'] = $eventData->id;

					$targetData = array();
					$targetData['type'] = 'event';
					$targetData['name'] = $eventData->title;
					$targetData['id'] = $eventData->id;
					$targetData['url'] = JUri::root() . 'index.php?option=com_jticketing&view=event&id=' . $eventData->id;
					$activityData['target'] = json_encode($targetData);
					$activityData['target_id'] = $eventData->id;

					$activityData['type'] = 'jticketing.addevent';
					$activityData['template'] = 'addevent.mustache';

					$result = $activityStreamModelActivity->save($activityData);
				}
			}
		}

		if (empty($targetIds))
		{
			$this->pushOrderActivity();
		}

		return true;
	}

	/**
	 * add credit entries in the passbook table
	 *
	 * @param   integer  $id  The xref id
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function getIntegrationData($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_integration_xref'));
		$query->where($db->quoteName('id') . ' = ' . $db->quote($id));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * format the payout data according to date
	 *
	 * @param   object  $oldCreditData  The credit data of tickets.
	 *
	 * @param   object  $oldPayoutData  The debit data of payouts.
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function formatPayoutData($oldCreditData, $oldPayoutData)
	{
		require_once JPATH_SITE . '/components/com_jticketing/helpers/common.php';
		require_once JPATH_SITE . '/components/com_jticketing/helpers/main.php';
		require_once JPATH_SITE . '/components/com_jticketing/helpers/order.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers/tjvendors.php';

		$dataSize = sizeof($oldCreditData);
		$count = 0;

		foreach ($oldCreditData as $data)
		{
			$count++;

			foreach ($oldPayoutData as $payoutData)
			{
				$date = new JDate($payoutData->date . ' +23 hour +59 minutes');

				if ($date <= $data->cdate)
				{
					$this->addPayoutData($payoutData);
				}
			}

			$this->addCreditData($data);

			if ($dataSize == $count)
			{
				foreach ($oldPayoutData as $payoutData)
				{
					$date = new JDate($payoutData->date . ' +23 hour +59 minutes');

					if ($date > $data->cdate)
					{
						$this->addPayoutData($payoutData);
					}
				}
			}
		}

		return true;
	}

	/**
	 * add credit entries in the passbook table
	 *
	 * @param   object  $data  The credit data of tickets.
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function addCreditData($data)
	{
		$data->client = "com_jticketing";
		$JticketingCommonHelper = new JticketingCommonHelper;
		$xrefId = $data->event_details_id;
		$integrationDetails = $this->getIntegrationData($xrefId);

		if (!empty($integrationDetails))
		{
			$vendorCheck = $this->vendorCheck($integrationDetails->userid);

			if (!$vendorCheck)
			{
				$vendor_id = $this->addOldVendor($integrationDetails->userid);
			}
			else
			{
				$vendor_id = $vendorCheck;
			}

			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'vendor');
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables', 'vendor');
			$TjvendorsModelVendor = JModelLegacy::getInstance('Vendor', 'TJvendorsModel');
			$vendorDetails = $TjvendorsModelVendor->getItem($vendor_id);
			$com_params = JComponentHelper::getParams($data->client);
			$currency = $com_params->get('currency');
			$entry_data['vendor_id'] = $vendor_id;
			$totalAmount = TjvendorsHelpersTjvendors::getTotalAmount($entry_data['vendor_id'], $currency, 'com_jticketing');
			$entry_data['reference_order_id'] = $data->order_id;
			$transactionClient = "Jticketing";
			$entry_data['transaction_id'] = $transactionClient . '-' . $currency . '-' . $entry_data['vendor_id'] . '-';
			$entry_data['transaction_time'] = $data->cdate;
			$entry_data['credit'] = $data->amount - $data->fee;
			$entry_data['total'] = $totalAmount['total'] + $entry_data['credit'];
			$entry_data['debit'] = 0;
			$entry_status = "credit_for_ticket_buy";
			$params = array("customer_note" => $data->customer_note,"entry_status" => $entry_status);
			$entry_data['params'] = json_encode($params);
			$entry_data['currency'] = $currency;
			$entry_data['client'] = $data->client;
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'payout');
			$TjvendorsModelPayout = JModelLegacy::getInstance('Payout', 'TjvendorsModel');
			$vendorDetail = $TjvendorsModelPayout->addCreditEntry($entry_data);
		}
	}

	/**
	 * add debit entries in the passbook table
	 *
	 * @param   object  $payoutData  The payout old data.
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function addPayoutData($payoutData)
	{
		$com_params = JComponentHelper::getParams('com_jticketing');
		$currency = $com_params->get('currency');
		$vendorCheck = $this->vendorCheck($payoutData->user_id);

		if (!$vendorCheck)
		{
			$vendor_id = $this->addOldVendor($payoutData->user_id);
		}
		else
		{
			$vendor_id = $vendorCheck;
		}

		$newPayoutData = new stdClass;
		$newPayoutData->debit = $payoutData->amount;
		$payableAmount = TjvendorsHelpersTjvendors::getTotalAmount($vendor_id, $currency, 'com_jticketing');
		$newPayoutData->total = $payableAmount['total'] - $newPayoutData->debit;
		$newPayoutData->transaction_time = $payoutData->date;
		$newPayoutData->client = 'com_jticketing';
		$newPayoutData->currency = $currency;
		$transactionClient = "Jticketing";
		$newPayoutData->transaction_id = $transactionClient . '-' . $currency . '-' . $vendor_id . '-';
		$newPayoutData->id = '';
		$newPayoutData->vendor_id = $vendor_id;
		$newPayoutData->status = $payoutData->status;
		$newPayoutData->credit = '0.00';
		$params = array("customer_note" => "", "entry_status" => "debit_payout");
		$newPayoutData->params = json_encode($params);

		// Insert the object into the user passbook table.
		$result = JFactory::getDbo()->insertObject('#__tjvendors_passbook', $newPayoutData);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('max(' . $db->quotename('id') . ')');
		$query->from($db->quoteName('#__tjvendors_passbook'));
		$db->setQuery($query);

		$payout_id = $db->loadResult();

		$payout_update = new stdClass;

		// Must be a valid primary key value.
		$payout_update->id = $payout_id;
		$payout_update->transaction_id = $newPayoutData->transaction_id . $payout_update->id;

		// Update their details in the passbook table using id as the primary key.
		$result = JFactory::getDbo()->updateObject('#__tjvendors_passbook', $payout_update, 'id');
	}

	/**
	 * add a user as a vendor
	 *
	 * @param   string  $user_id  The user id.
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function addOldVendor($user_id)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers/tjvendors.php';
		$vendorData['userName'] = JFactory::getUser($user_id)->name;
		$vendorData['vendor_client'] = "com_jticketing";
		$vendorData['user_id'] = $user_id;
		$vendorData['vendor_title'] = $vendorData['userName'];
		$vendorData['state'] = "1";
		$vendorData['params'] = null;
		$vendorData['vendor_id'] = null;
		$vendorData['payment_gateway'] = null;
		$vendorData['paymentDetails'] = null;
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'vendor');
		$TjvendorsModelVendors = JModelLegacy::getInstance('Vendor', 'TjvendorsModel');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables', 'vendor');
		$TjvendorsModelVendors->save($vendorData);
		$vendor_id = $this->vendorCheck($user_id);

		return $vendor_id;
	}

	/**
	 * check id the table exists
	 *
	 * @param   string  $table  The table name.
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function checkTableExists($table)
	{
		$db = JFactory::getDBO();
		$config = JFactory::getConfig();

		$dbname = $config->get('db');
		$dbprefix = $config->get('dbprefix');

		$query = $db->getQuery(true);
		$query->select($db->quoteName('table_name'));
		$query->from($db->quoteName('information_schema.tables'));
		$query->where($db->quoteName('table_schema') . ' = ' . $db->quote($dbname));
		$query->where($db->quoteName('table_name') . ' = ' . $db->quote($dbprefix . $table));
		$db->setQuery($query);
		$check = $db->loadResult();

		if ($check)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * get old orders data
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function getOldData()
	{
		$com_params = JComponentHelper::getParams('com_jticketing');
		$handle_transactions = $com_params->get('handle_transactions', 0);

		if ($handle_transactions == 0)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__jticketing_order'));
			$query->where($db->quoteName('status') . ' = ' . $db->quote('C'));
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		else
		{
			return false;
		}
	}

	/**
	 * get old payouts data
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function getOldPayoutData()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_ticket_payouts'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * check if user is already a vendor
	 *
	 * @param   string  $user_id  The user id.
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public static function vendorCheck($user_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('vendor_id'));
		$query->from($db->quoteName('#__tjvendors_vendors'));
		$query->where($db->quoteName('user_id') . ' = ' . $user_id);
		$db->setQuery($query);
		$vendor = $db->loadResult();

		if (!$vendor)
		{
			return false;
		}
		else
		{
			return $vendor;
		}
	}

	/**
	 * get old event xref data
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function getOldEventXrefData()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_integration_xref'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * get venue data
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function getOldVenueData()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_venues'));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Add vendor id in xref table against an event
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function addOldEventVendor()
	{
		$oldEventXrefData = $this->getOldEventXrefData();

		foreach ($oldEventXrefData as $eventXrefData)
		{
			$vendorCheck = $this->vendorCheck($eventXrefData->userid);

			if (empty($vendorCheck))
			{
				if (!empty($eventXrefData->paypal_email))
				{
					$vendor_id = $this->addOldVendor($eventXrefData->userid);

					if (!empty($eventXrefData->paypal_email))
					{
						$params = JComponentHelper::getParams('com_jticketing');
						$handle_transactions = $params->get('handle_transactions');

						$emailVendorData = new stdClass;

						if ($handle_transactions == 1)
						{
							$emailVendorData->payment_gateway = "paypal";
						}
						else
						{
							$emailVendorData->payment_gateway = "adaptive_paypal";
						}

						$emailVendorData->vendor_id = $vendor_id;
						$paymentGateway = $emailVendorData->payment_gateway;
						$paymentEmailId = $eventXrefData->paypal_email;
						$gatewayDetails = array("payment_gateway" => $emailVendorData->payment_gateway, "payment_email_id" => $paymentEmailId);
						$emailVendorData->params = json_encode($gatewayDetails);
						$result = JFactory::getDbo()->updateObject('#__vendor_client_xref', $emailVendorData, 'vendor_id');
					}
				}
				else
				{
					$vendor_id = $this->addOldVendor($eventXrefData->userid);
				}

				$newEventData = new stdClass;
				$newEventData->vendor_id = $vendor_id;
				$newEventData->id = $eventXrefData->id;

				// Insert the object into the user integration table.
				$result = JFactory::getDbo()->updateObject('#__jticketing_integration_xref', $newEventData, 'id');
			}
			else
			{
				$vendor_id = $vendorCheck;

				if (!empty($eventXrefData->paypal_email))
				{
					$params = JComponentHelper::getParams('com_jticketing');
					$handle_transactions = $params->get('handle_transactions');

					$emailVendorData = new stdClass;

					if ($handle_transactions == 1)
					{
						$emailVendorData->payment_gateway = "paypal";
					}
					else
					{
						$emailVendorData->payment_gateway = "adaptive_paypal";
					}

					$emailVendorData->vendor_id = $vendor_id;
					$paymentGateway = $emailVendorData->payment_gateway;
					$paymentEmailId = $eventXrefData->paypal_email;
					$gatewayDetails = array("payment_gateway" => $emailVendorData->payment_gateway, "payment_email_id" => $paymentEmailId);
					$emailVendorData->params = json_encode($gatewayDetails);
					$result = JFactory::getDbo()->updateObject('#__vendor_client_xref', $emailVendorData, 'vendor_id');
				}

				$newEventData = new stdClass;
				$newEventData->vendor_id = $vendor_id;
				$newEventData->id = $eventXrefData->id;

				// Insert the object into the user integration table.
				$result = JFactory::getDbo()->updateObject('#__jticketing_integration_xref', $newEventData, 'id');
			}
		}

		return true;
	}

	/**
	 * Add vendor id to venue.
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function addOldVenueVendor()
	{
		$oldVenueData = $this->getOldVenueData();

		foreach ($oldVenueData as $venueData)
		{
			$vendorCheck = $this->vendorCheck($venueData->created_by);

			if (empty($vendorCheck))
			{
				$vendor_id = $this->addOldVendor($venueData->created_by);
			}
			else
			{
				$vendor_id = $vendorCheck;
			}

			$newVenueData = new stdClass;
			$newVenueData->vendor_id = $vendor_id;
			$newVenueData->id = $venueData->id;

			// Insert the object into the user profile table.
			$result = JFactory::getDbo()->updateObject('#__jticketing_venues', $newVenueData, 'id');
		}

		return true;
	}

	/**
	 * Image Migration
	 *
	 * @return void
	 *
	 * @since 2.0
	 */
	public function imageMigration()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'image')));
		$query->from($db->quoteName('#__jticketing_events'));
		$db->setQuery($query);
		$eventImages = $db->loadAssocList();

		foreach ($eventImages as $image)
		{
			if ($image['image'] != 'default-event-image.png' && $image['image'] != '')
			{
				$mediaData = array();
				$mediaData['name'] = $image['image'];
				$type = explode(".", $mediaData['name']);
				$mediaData['type'] = "image." . $type[1];
				$mediaData['size'] = 0;
				$mediaData['tmp_name'] = JPATH_ROOT . '/media/com_jticketing/images/' . $mediaData['name'];
				$mediaData['upload_type'] = "move";

				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'media');
				$jtMediaModel = JModelLegacy::getInstance('Media', 'JticketingModel');

				if ($returnData = $jtMediaModel->save($mediaData))
				{
					JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'mediaxref');
					$jtMediaxrefModel = JModelLegacy::getInstance('MediaXref', 'JticketingModel');
					$mediaXref = array();
					$mediaXref['media_id'] = $returnData['id'];
					$mediaXref['client_id'] = $image['id'];
					$mediaXref['client'] = 'com_jticketing.event';

					if ($jtMediaxrefModel->save($mediaXref))
					{
						$image_update = new stdClass;

						// Must be a valid primary key value.
						$image_update->id = $image['id'];
						$image_update->image = '';

						// Update their details in the events table using id as the primary key.
						$result = JFactory::getDbo()->updateObject('#__jticketing_events', $image_update, 'id');
					}
				}
			}
		}

		return true;
	}
}
