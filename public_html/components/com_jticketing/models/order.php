<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die(';)');
jimport('joomla.application.component.model');
jimport('joomla.database.table.user');
JLoader::import('vendors', JPATH_SITE . '/components/com_tjvendors/models');
JLoader::import('common', JPATH_SITE . '/components/com_jticketing/helpers');
JLoader::import('route', JPATH_SITE . '/components/com_jticketing/helpers');

/**
 * Model for buy for creating order and other
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelOrder extends JModelAdmin
{
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		$this->jticketingmainhelper = new jticketingmainhelper;
		$this->jticketingfrontendhelper = new jticketingfrontendhelper;
		$this->JticketingCommonHelper = new JticketingCommonHelper;
		$this->JTRouteHelper = new JTRouteHelper;

		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$this->TjGeoHelper = new TjGeoHelper;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm   A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.order', 'order', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Get an instance of JTable class
	 *
	 * @param   string  $type    Name of the JTable class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the JTable object. Optional.
	 *
	 * @return  JTable|bool JTable if success, false on failure.
	 */
	public function getTable($type = 'Order', $prefix = 'JticketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get an object.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed    Object on success, false on failure.
	 */
	public function getItem($id = null)
	{
		$this->item = parent::getItem($id);

		return $this->item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   data  $data  TO  ADD
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		$session    = JFactory::getSession();
		$com_params = JComponentHelper::getParams('com_jticketing');
		$integration    = $com_params->get('integration');

		JLoader::import('components.com_jticketing.models.eventform', JPATH_SITE);
		$eventModel = JModelLegacy::getInstance('EventForm', 'JticketingModel');
		$eventdata = $eventModel->getItem($data['eventid']);

		$orderdata = array();

		if (!$orderdata)
		{
			$orderdata['user_id']           = $data['user_id'];
			$orderdata['name']              = $data['name'];
			$orderdata['email']             = $data['email'];
			$orderdata['parent_order_id']   = 0;
			$ticketcount                    = 0;
			$ticketdata['type_ticketcount'] = $data['type_ticketcount'];
			$ticketdata['type_id']          = $data['type_id'];

			if ($integration == '2')
			{
				if ($eventdata->online_events == '0')
				{
					foreach ($ticketdata['type_ticketcount'] as $key => $count)
					{
						$ticketcount += $count;
					}
				}
				else
				{
					$ticketdata['type_ticketcount'] = array($ticketdata['type_id'][0] => '1');
					$ticketcount = '1';
				}
			}
			else
			{
				foreach ($ticketdata['type_ticketcount'] as $key => $count)
				{
					$ticketcount += $count;
				}
			}

			$orderdata['no_of_tickets'] = $ticketcount;
			$ticketdata['coupon_code']  = $data['coupon_code'];
			$allowTaxation             = $com_params->get('allow_taxation');
			$eventData['eventid']      = $data['eventid'];

			// RecalculateAmount Based on TIcket Type and Ticket count
			$amountData                = $this->recalculateTotalAmount($ticketdata, $allowTaxation, $eventData);
			$orderdata['original_amt'] = $amountData['original_amt'];

			if ($amountData['amt'] < 0)
			{
				$amountData['amt'] = 0;
				$amountData['fee'] = 0;
			}

			$orderdata['amount']          = $amountData['amt'];
			$orderdata['fee']             = $amountData['fee'];
			$orderdata['coupon_discount'] = $amountData['coupon_discount'];

			if (isset($amountData['order_tax']))
			{
				$orderdata['order_tax'] = $amountData['order_tax'];
			}
			else
			{
				$orderdata['order_tax'] = 0;
			}

			if (isset($amountData['order_tax']))
			{
				$orderdata['order_tax_details'] = $amountData['order_tax_details'];
			}
			else
			{
				$orderdata['order_tax_details'] = 0;
			}

			$orderdata['coupon_discount_details'] = $data['coupon_code'];
			$orderdata['coupon_code']             = $data['coupon_code'];
			$eventIntegrationId                 = $orderdata['integraton_id'] = $data['event_integraton_id'];
		}

		$JtOrderId = $session->get('JT_orderid');
		$currentSessionTickets = $session->get('type_ticketcount_current');

		if (empty($currentSessionTickets))
		{
			$session->set('type_ticketcount_current', $ticketdata['type_ticketcount']);
			$session->set('type_ticketcount_old', $ticketdata['type_ticketcount']);
		}
		else
		{
			$session->set('type_ticketcount_old', $session->get('type_ticketcount_current'));
			$session->set('type_ticketcount_current', $ticketdata['type_ticketcount']);
		}

		$currentSessionTickets = $session->get('type_ticketcount_current');
		$oldSessionTickets     = $session->get('type_ticketcount_old');
		$removedTicketTypes = array();
		$addedTicketTypes   = array();

		foreach ($currentSessionTickets AS $key => $value)
		{
			foreach ($oldSessionTickets AS $oldkey => $oldval)
			{
				if ($key == $oldkey and $value < $oldval)
				{
					$removedTicketTypes[$key] = $oldval - $value;
				}

				if ($key == $oldkey and $value > $oldval)
				{
					$addedTicketTypes[$key] = $value - $oldval;
				}
			}
		}

		if (!empty($removedTicketTypes))
		{
			$ticketdata['removed_ticket_types'] = $removedTicketTypes;
		}

		// Create Main order
		if (!$orderdata['integraton_id'])
		{
			$orderdata['integraton_id'] = $this->JticketingCommonHelper->getEventIntegXrefId($orderdata['eventid']);
		}

		$ordData = array();
		$ordData['event_details_id'] = $orderdata['integraton_id'];

		if (isset($orderdata['name']))
		{
			$ordData['name'] = $orderdata['name'];
		}

		if (isset($orderdata['email']))
		{
			$ordData['email'] = $orderdata['email'];
		}

		if (isset($orderdata['user_id']))
		{
			$ordData['user_id'] = $orderdata['user_id'];
		}

		$ordData['coupon_code']             = $orderdata['coupon_code'];
		$ordData['coupon_discount']         = $orderdata['coupon_discount'];
		$ordData['coupon_discount_details'] = $orderdata['coupon_discount_details'];
		$ordData['order_tax']               = $orderdata['order_tax'];
		$ordData['order_tax_details']       = $orderdata['order_tax_details'];
		$ordData['cdate']                   = JFactory::getDate()->toSql();
		$ordData['mdate']                   = JFactory::getDate()->toSql();

		if (isset($orderdata['processor']))
		{
			$ordData['processor'] = $orderdata['processor'];
		}

		if (isset($orderdata['customer_note']))
		{
			$ordData['customer_note'] = $orderdata['customer_note'];
		}

		$ordData['ticketscount'] = $orderdata['no_of_tickets'];

		if (!$orderdata['parent_order_id'])
		{
			$ordData['parent_order_id'] = 0;
		}
		else
		{
			$ordData['parent_order_id'] = $orderdata['parent_order_id'];
		}

		$ordData['status'] = 'P';

		// This is calculated amount
		$ordData['original_amount'] = $orderdata['original_amt'];
		$ordData['amount']          = $orderdata['amount'];
		$ordData['fee']             = $orderdata['fee'];
		$ordData['ip_address']      = $_SERVER["REMOTE_ADDR"];

		if (parent::save($ordData))
		{
			$orderId = (int) $this->getState($this->getName() . '.id');

			$db    = JFactory::getDBO();
			$table = JTable::getInstance('Order', 'JticketingTable', array('dbo', $db));

			if (empty($table->order_id))
			{
				$this->generateOrderID($orderId);
			}

			if (!empty($orderId))
			{
				JLoader::import('components.com_jticketing.models.orderitem', JPATH_SITE);
				$ordrItemModel = JModelLegacy::getInstance('Orderitem', 'JticketingModel');
				$ticketdata['order_id'] = $orderId;
				$ordrItemModel->save($ticketdata);
			}

			return $orderId;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Apply coupon
	 *
	 * @param   integer  $originalAmount  originalAmount
	 * @param   integer  $couponCode      couponCode
	 *
	 * @return  array coupon amount
	 *
	 * @since   1.0
	 */
	public function applyCoupon($originalAmount, $couponCode = '')
	{
		$couponCode     = trim($couponCode);
		$val             = 0;
		$couponDiscount = $this->getCoupon($couponCode);

		if ($couponDiscount)
		{
			if ($couponDiscount[0]->val_type == 1)
			{
				$val = ($couponDiscount[0]->value / 100) * ($originalAmount);
			}
			else
			{
				$val = $couponDiscount[0]->value;
			}

			$vars['coupon_discount_details'] = json_encode($couponDiscount);
		}

		$amt = $originalAmount - $val;
		$vars['original_amt']    = $originalAmount;
		$vars['amt']             = $amt;
		$vars['coupon_discount'] = $val;

		return $vars;
	}

	/**
	 * Update order Items in ajax calls in steps
	 *
	 * @param   integer  $ticketdata  orderdata for successed
	 * @param   integer  $orderid     order id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updateOrderItems($ticketdata, $orderid)
	{
		$session = JFactory::getSession();
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id','order_id','attendee_id')));
		$query->from($db->quoteName('#__jticketing_order_items'));
		$query->where($db->quoteName('order_id') . ' = ' . $db->quote($orderid));
		$db->setQuery($query);
		$orderitems = $db->loadObjectlist();

		JLoader::import('components.com_jticketing.models.event', JPATH_SITE);
		$ticketTypesModel = JModelLegacy::getInstance('Tickettypes', 'JticketingModel');

		// Firstly Delete ticket types in order items that are removed
		if (!empty($orderitems))
		{
			if (!empty($ticketdata['removed_ticket_types']))
			{
				foreach ($ticketdata['removed_ticket_types'] as $key => $count)
				{
					if ($count > 0)
					{
						$query = $db->getQuery(true);
						$query->delete($db->quoteName('#__jticketing_order_items'));
						$query->where($db->quoteName('order_id') . ' = ' . $db->quote($orderid));
						$query->where($db->quoteName('type_id') . ' = ' . $db->quote($key));
						$query->setLimit($count);
						$db->setQuery($query);
					}
				}
			}
		}

		foreach ($ticketdata['type_ticketcount'] as $key => $multipleTickets)
		{
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__jticketing_order_items'));
			$query->where($db->quoteName('order_id') . ' = ' . $db->quote($orderid));
			$query->where($db->quoteName('type_id') . ' = ' . $db->quote($key));
			$db->setQuery($query);
			$orderitemIdArray           = $db->loadAssoclist();
			$resdetails                 = new stdClass;
			$resdetails->id             = '';
			$resdetails->order_id       = $orderid;
			$resdetails->ticketcount    = 1;
			$resdetails->type_id        = $key;
			$resdetails->payment_status = 'P';
			$ticketTypes = $ticketTypesModel->getItem($resdetails->type_id);
			$resdetails->ticket_price   = $ticketTypes->price;

			// @TODO For Deposit Change This to deposit Fee.
			$resdetails->amount_paid    = $resdetails->ticket_price;
			$totalUpdatedCount = 0;

			// Now update order items that already present
			if (!empty($orderitemIdArray))
			{
				foreach ($orderitemIdArray AS $key => $value)
				{
					$resdetails->id = $value['id'];

					if (!$db->updateObject('#__jticketing_order_items', $resdetails, 'id'))
					{
						echo $db->stderr();
					}

					$totalUpdatedCount++;
				}
			}

			if ($totalUpdatedCount)
			{
				$multipleTickets = $multipleTickets - $totalUpdatedCount;
			}

			// Insert Newly Created order items
			for ($i = 0; $i < $multipleTickets; $i++)
			{
				$resdetails->id = '';

				if (!$db->insertObject('#__jticketing_order_items', $resdetails, 'id'))
				{
					echo $db->stderr();
				}
			}
		}
	}

	/**
	 * Verify amount data for coupon code calculation and calculate final amount of order
	 *
	 * @param   ARRAY  $amountData     amountData
	 * @param   ARRAY  $allowTaxation  1 or 0
	 * @param   ARRAY  $eventData      eventData
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function recalculateTotalAmount($amountData, $allowTaxation = 0, $eventData = '')
	{
		$eventId = $eventData['eventid'];

		// Get user specific commission data.
		$userSpecificComm = $this->getUserSpecificCommision($eventId);
		$com_params          = JComponentHelper::getParams('com_jticketing');

		$siteAdminCommPer = isset($userSpecificComm->percent_commission)?$userSpecificComm->percent_commission:$com_params->get('siteadmin_comm_per');

		$siteAdminCommFlat = isset($userSpecificComm->flat_commission) ? $userSpecificComm->flat_commission :$com_params->get('siteadmin_comm_flat');

		$siteAdminCommCap = $com_params->get('siteadmin_comm_cap');

		$originalAmt       = 0;
		$typeTicketCounts  = $amountData['type_ticketcount'];
		$typeids           = $amountData['type_id'];

		JLoader::import('components.com_jticketing.models.event', JPATH_SITE);
		$ticketTypesModel = JModelLegacy::getInstance('Tickettypes', 'JticketingModel');

		// Calculate original Amt to pay Based on ticket Types And Price.
		foreach ($typeTicketCounts AS $key => $multipleTickets)
		{
			$resDetails             = new stdClass;
			$resDetails->type_id    = $key;
			$resDetails->type_price = 0;
			$ticketTypes = $ticketTypesModel->getItem($resDetails->type_id);
			$resDetails->type_price = $ticketTypes->price;

			// @TODO For Deposit Change This to deposit Fee.
			$originalAmt += $resDetails->type_price * $multipleTickets;
		}

		if (!empty($amountData['coupon_code']))
		{
			$vars = $this->applyCoupon($originalAmt, $amountData['coupon_code']);
		}
		else
		{
			$vars['original_amt']    = $originalAmt;
			$vars['amt']             = $originalAmt;
			$vars['coupon_code']     = $amountData['coupon_code'];
			$vars['coupon_discount'] = 0;
		}

		// Calculated as 0.1+1  1
		if ($siteAdminCommCap < $vars['amt'] and $siteAdminCommCap > 0 )
		{
			$vars['fee'] = $siteAdminCommCap * $siteAdminCommPer / 100;
		}
		else
		{
			$vars['fee'] = $vars['amt'] * $siteAdminCommPer / 100;
		}

		if (isset($siteAdminCommFlat) and $siteAdminCommFlat > 0)
		{
			$fee = $vars['fee'] + $siteAdminCommFlat;

			// If fee is 1.1 And amt to pay is 1 in that case apply only percentage commission
			if ($fee <= $vars['amt'])
			{
				$vars['fee'] = $fee;
			}
		}

		if ($allowTaxation)
		{
			$taxAmt = $this->applyTax($vars);

			if (isset($taxAmt->taxvalue) and $taxAmt->taxvalue > 0)
			{
				$vars['order_tax']         = $taxAmt->taxvalue;
				$vars['amt']               = $vars['net_amt_after_tax'] = $vars['amt'] + $taxAmt->taxvalue;
				$vars['order_tax_details'] = json_encode($taxAmt);
			}
		}

		return $vars;
	}

	/**
	 * Calculate tax from and apply from taxation plugin
	 *
	 * @param   ARRAY  $vars  vars contains array for amt to apply tax
	 *
	 * @return  int    amount
	 *
	 * @since   1.0
	 */
	public function applyTax($vars)
	{
		// Set Required Sessions
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('jticketingtax');
		$taxResults = $dispatcher->trigger('addTax', array($vars['amt']));

		// Call the plugin and get the result
		if (isset($taxResults[0]) and $taxResults['0']->taxvalue > 0)
		{
			return $taxResults['0'];
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Generate random no
	 *
	 * @param   INT  $length  length of random no
	 *
	 * @return  int  $random  random no
	 *
	 * @since   1.0
	 */

	public function _random($length = 5)
	{
		$salt   = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len    = strlen($salt);
		$random = '';
		$stat = @stat(__FILE__);

		if (empty($stat) || !is_array($stat))
		{
			$stat = array(php_uname());
		}

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i++)
		{
			$random .= $salt[mt_rand(0, $len - 1)];
		}

		return $random;
	}

	/**
	 * Validate coupon for date and other condition
	 *
	 * @param   INT  $couponCode  couponCode
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getCoupon($couponCode)
	{
		$db    = JFactory::getDbo();
		$user = JFactory::getUser();
		$userId = $user->id;

		if (empty($userId))
		{
			$userId = 0;
		}

		// If user is login then only check max per user conditiotn
		if ($userId)
		{
			$subquery2 = $db->getQuery(true);
			$subquery2->select('COUNT(api.coupon_code)');
			$subquery2->from("#__jticketing_order as api");
			$subquery2->where($db->quoteName('coupon_discount') . "> 0");
			$subquery2->where($db->quoteName('api.status') . "LIKE 'C'");
			$subquery2->where($db->quoteName('api.coupon_code') . " = " . $db->quote($db->escape($couponCode)));
			$subquery2->where($db->quoteName('api.user_id') . " = " . $userId);
		}

		$subquery1 = $db->getQuery(true);
		$subquery1->select('COUNT(api.coupon_code)');
		$subquery1->from("#__jticketing_order as api");
		$subquery1->where($db->quoteName('coupon_discount') . "> 0");
		$subquery1->where($db->quoteName('api.status') . "LIKE 'C'");
		$subquery1->where($db->quoteName('api.coupon_code') . " = " . $db->quote($db->escape($couponCode)));

		$now = $db->quote(JFactory::getDate('now', 'UTC', true));

		$query = $db->getQuery(true);
		$query->select("value, val_type");
		$query->from("#__jticketing_coupon as cop");
		$query->where($db->quoteName('state') . "= 1");
		$query->where($db->quoteName('code') . " = " . $db->quote($db->escape($couponCode)));
		$query->where("(" . ("(" . $now . 'BETWEEN' . $db->quoteName('from_date')
		. 'AND' . $db->quoteName('exp_date') . ")") . " OR " . $db->quoteName('from_date') . "= '0000-00-00 00:00:00'" . ")");
		$query->where("(" . $db->quoteName('max_use') . " > (" . $subquery1 . ") OR" . $db->quoteName('max_use') . " = 0)");

		// If user is login then only check max per user conditiotn
		if ($userId)
		{
			$query->where("(" . $db->quoteName('max_per_user') . " > (" . $subquery2 . ") OR" . $db->quoteName('max_per_user') . " = 0)");
		}

		$db->setQuery($query);
		$count = $db->loadObjectList();

		return $count;
	}

	/**
	 * Get Contry list from tjfields
	 *
	 * @param   integer  $country  country id
	 *
	 * @return  object state list
	 *
	 * @since   1.0
	 */
	public function getRegionList($country)
	{
		return $this->TjGeoHelper->getRegionListFromCountryID($country, 'com_jticketing');
	}

	/**
	 * Retrieve details for a country
	 *
	 * @return  object  $country  Details
	 *
	 * @since   1.0
	 */
	public function getCountry()
	{
		return $this->TjGeoHelper->getCountryList('com_jticketing');
	}

	/**
	 * Get Event data
	 *
	 * @return  array event list
	 *
	 * @since   1.0
	 */
	public function getEventdata()
	{
		$com_params   = JComponentHelper::getParams('com_jticketing');
		$integration  = $com_params->get('integration');
		$session      = JFactory::getSession();
		$input        = JFactory::getApplication()->input;
		$post         = $input->post;

		$eventId = $post->get('eventid');

		if (empty($eventId))
		{
			$eventId = $session->get('JT_eventid');
		}
		else
		{
			$eventId = $eventId;
		}

		if (empty($eventId))
		{
			$eventId = $input->get('eventid', '', 'INT');
		}

		$db    = JFactory::getDBO();

		if ($integration == 1)
		{
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__community_events'));
			$query->where($db->quoteName('id') . ' = ' . $db->quote($eventId));
		}
		elseif ($integration == 2)
		{
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from($db->quoteName('#__jticketing_events'));
			$query->where($db->quoteName('id') . ' = ' . $db->quote($eventId));
		}
		elseif ($integration == 3)
		{
			$query = $db->getQuery(true);
			$query->select(array('event.*', 'DATE(FROM_UNIXTIME(event.dtstart))', 'DATE(FROM_UNIXTIME(event.dtend))'), array('startdate', 'enddate'));
			$query->from($db->qn('#__jevents_vevdetail', 'event'));
			$query->where($db->qn('evdet_id') . ' = ' . $db->quote($eventId));
		}
		elseif ($integration == 4)
		{
			$query = $db->getQuery(true);
			$query->select(array('event.*', 'event_det.start', 'event_det.end'), array('start', 'enddate'));
			$query->from($db->quoteName('#__social_clusters', 'event'));
			$query->join('INNER', $db->quoteName('#__social_events_meta', 'event_det')
			. 'ON (' . $db->quoteName('event_det.cluster_id') . ' = ' . $db->quoteName('event.id') . ')');
			$query->where($db->quoteName('event.id') . ' = ' . $db->quote($eventId));
		}

		$db->setQuery($query);
		$result = $db->loadobject();

		return $result;
	}

	/**
	 * Check if joomla user exists
	 *
	 * @param   INT  $email  email of joomla user
	 *
	 * @return  Boolean true or false
	 *
	 * @since   1.0
	 */
	public function checkuserExistJoomla($email)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id')));
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('email') . ' LIKE ' . $db->quote($email));
		$db->setQuery($query);
		$id = $db->loadResult();

		if ($id)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get User specific % commission and flat commission
	 *
	 * @param   Int  $eventId  Event ID
	 *
	 * @return Array of data
	 */
	public function getUserSpecificCommision($eventId)
	{
		$db = JFactory::getDBO();
		$eventVendor = $this->JticketingCommonHelper->getEventVendor($eventId);

		// Get user specific % commission and flat commission set by Admin
		$query = $db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__tjvendors_fee'))
				->where($db->quoteName('vendor_id') . ' = ' . $db->quote($eventVendor->vendor_id));
			$db->setQuery($query);
		$userSpecificData = $db->loadObject();

		return $userSpecificData;
	}

	/**
	 * To generate order id with prefix
	 *
	 * @param   integer  $orderID  order id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function generateOrderID($orderID)
	{
		$db  = JFactory::getDBO();

		// Update order if orde_id present
		if (!empty($orderID))
		{
			// Store Order to Jticketing Table
			$lang      = JFactory::getLanguage();
			$extension = 'com_jticketing';
			$baseDir   = JPATH_ROOT;
			$lang->load($extension, $baseDir);
			$comParams     = JComponentHelper::getParams('com_jticketing');
			$integration   = $comParams->get('integration');
			$guestRegId    = $comParams->get('guest_reg_id');
			$autoFixSeats  = $comParams->get('auto_fix_seats');
			$currency      = $comParams->get('currency');
			$orderPrefix   = $comParams->get('order_prefix');
			$separator     = $comParams->get('separator');
			$randomOrderId = $comParams->get('random_orderid');
			$paddingCount  = $comParams->get('padding_count');

			// Lets make a random char for this order take order prefix set by admin
			$orderPrefix = (string) $orderPrefix;

			// String length should not be more than 5
			$orderPrefix = substr($orderPrefix, 0, 5);

			// Take separator set by admin
			$separator     = (string) $separator;
			$orderid_prefix = $orderPrefix . $separator;

			// Check if we have to add random number to order id
			$useRandomOrderId = (int) $randomOrderId;

			if ($useRandomOrderId)
			{
				$randomNumer = $this->_random(5);
				$orderid_prefix .= $randomNumer . $separator;

				// Order_id_column_field_length - prefix_length - no_of_underscores - length_of_random number
				$len = (23 - 5 - 2 - 5);
			}
			else
			{
				/* This length shud be such that it matches the column lenth of primary key
				It is used to add pading
				order_id_column_field_length - prefix_length - no_of_underscores*/
				$len = (23 - 5 - 2);
			}

			$insertOrderId = $ordersKey = $sticketid = $orderID;
			$maxlen       = 23 - strlen($orderID) - strlen($ordersKey);
			$paddingCount = (int) $paddingCount;

			// Use padding length set by admin only if it is les than allowed(calculate) length
			if ($paddingCount > $maxlen)
			{
				$paddingCount = $maxlen;
			}

			if (strlen((string) $ordersKey) <= $len)
			{
				$append = '';

				for ($z = 0; $z < $paddingCount; $z++)
				{
					$append .= '0';
				}

				$append = $append . $ordersKey;
			}

			$resd     = new stdClass;
			$resd->id = $ordersKey;
			$orderId  = $resd->order_id = $orderid_prefix . $append;

			if (!$db->updateObject('#__jticketing_order', $resd, 'id'))
			{
			}
		}
	}

	/**
	 * Function to get attendee info field
	 *
	 * @param   array  $eventData  eventdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getAttendeeInfoFields($eventData)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_order_items'));
		$query->where($db->quoteName('order_id') . ' = ' . $db->quote($eventData['order_id']));
		$db->setQuery($query);
		$this->orderitems = $db->loadObjectList();

		$this->fields = $this->jticketingfrontendhelper->getAllfields($eventData['eventid']);

		// Get ticket type and show ticket type title on the attendee data.
		$tickeTypeObj  = $this->jticketingmainhelper->GetTicketTypes($eventData['eventid']);

		$ticketTypeArr = array();

		foreach ($tickeTypeObj AS $key => $value)
		{
			$ticketTypeArr[$value->id] = $value->title;
		}

		if (!empty($this->orderitems))
		{
			foreach ($this->orderitems as $key => $orderitem)
			{
				$orderitemsId = $orderitem->id;

				if ($eventData['user_id'])
				{
					$paramsopass['user_id'] = $eventData['user_id'];
				}

				$attendeeId                            = $paramstopass['attendee_id'] = $orderitem->attendee_id;
				$paramstopass_ticket['order_items_id'] = $orderitemsId;

				JLoader::import('components.com_jticketing.models.attendeefields', JPATH_SITE);
				$attendeeFieldsModel = JModelLegacy::getInstance('Attendeefields', 'JticketingModel');

				// Get core and event specific field values  for this Attendee.
				$orderitemFieldValues = $attendeeFieldsModel->getUserEntryField($paramstopass);

				if (!empty($orderitemFieldValues))
				{
					foreach ($orderitemFieldValues AS $key => $orderitemFieldValue)
					{
						foreach ($orderitemFieldValue as $value)
						{
							$finalOrderItemsValue[$orderitem->attendee_id][$value->name] = $value->field_value;
						}
					}
				}

				// GetUniversal Field values for this Attendee.
				$orderitemFieldValuesUniversal = $attendeeFieldsModel->getUniversalUserEntryField($paramstopass);

				if (!empty($orderitemFieldValuesUniversal))
				{
					foreach ($orderitemFieldValuesUniversal AS $key => $orderitemFieldValueUniversal)
					{
						foreach ($orderitemFieldValueUniversal as $valueUniversal)
						{
							$finalOrderItemsValue[$orderitem->attendee_id][$valueUniversal->name] = $valueUniversal->field_value;
						}
					}
				}
			}
		}

		$orderitems = array();
		$orderitems['orderitems'] = $this->orderitems;
		$orderitems['fields'] = $this->fields;
		$orderitems['ticketTypeArr'] = $ticketTypeArr;

		return $orderitems;
	}

	/**
	 * Check if joomla user exists
	 *
	 * @param   INT    $orderid    orderid
	 * @param   ARRAY  $orderInfo  orderinfo array
	 *
	 * @return  Boolean true or false
	 *
	 * @since   1.0
	 */
	public function updateOrderDetails($orderid, $orderInfo = array())
	{
		$db = JFactory::getDbo();
		$obj = new stdClass;

		$obj->id = $orderid;

		if (isset($orderInfo['user_id']))
		{
			$obj->user_id = $orderInfo['user_id'];
		}

		if (isset($orderInfo['email']))
		{
			$obj->email = $orderInfo['email'];
		}

		// Update order entry.
		if (!$db->updateObject('#__jticketing_order', $obj, 'id'))
		{
			echo $db->stderr();

			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Function to create order
	 *
	 * @param   array  $data  data
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function createOrderAPI($data)
	{
		if (!$data['user_id'] || !$data['eventid'])
		{
			return false;
		}

		if (isset($data['eventid']))
		{
			JLoader::import('components.com_jticketing.helpers.common', JPATH_SITE);
			$JticketingCommonHelper = new JticketingCommonHelper;
			$data['event_integraton_id'] = $JticketingCommonHelper->getEventIntegXrefId($data['eventid']);

			if (!$data['event_integraton_id'])
			{
				return false;
			}

			if (empty($data['type_ticketcount']))
			{
				$db    = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select(array('id'));
				$query->from($db->quoteName('#__jticketing_types'));
				$query->where($db->quoteName('state') . ' = 1');
				$query->where($db->quoteName('eventid') . ' = ' . $db->quote($data['event_integraton_id']));
				$db->setQuery($query);
				$ticketTypes = $db->loadObjectlist();

				foreach ($ticketTypes as $ticketType)
				{
					$key = $ticketType->id;
					$data['type_ticketcount'][$key] = 1;
					$data['type_id'][] = $ticketType->id;
				}
			}
		}

		$user = JFactory::getUser($data['user_id']);
		isset($data['name']) ? $data['name'] : $data['name'] = $user->name;
		isset($data['email']) ? $data['email'] : $data['email'] = $user->email;
		isset($data['fnam']) ? $data['fnam'] : $data['fnam'] = $user->name;
		isset($data['email1']) ? $data['email1'] : $data['email1'] = $user->email;

		$data['coupon_code'] = '';

		if ($orderID = $this->save($data))
		{
			if (isset($data['attendee_id']))
			{
				// To get orderitems data
				JLoader::import('components.com_jticketing.models.orderitem', JPATH_SITE);
				$orderItemsModel = JModelLegacy::getInstance('Orderitem', 'JticketingModel');
				$attendeeData = array();

				if ($orderItems = $orderItemsModel->getOrderItems($orderID))
				{
					foreach ($orderItems as $key => $val)
					{
						$attendeeData[$key]['order_items_id'] = $val->id;
						$attendeeData[$key]['ticket_type'] = $val->type_id;
						$attendeeData[$key]['1'] = $data['atte_fname'];
						$attendeeData[$key]['2'] = $data['atte_lname'];
						$attendeeData[$key]['3'] = $data['atte_mob'];
						$attendeeData[$key]['4'] = $data['atte_email'];
						$attendeeData[$key]['attendee_id'] = $data['attendee_id'];
						$attendeeData[$key]['user_id'] = $data['user_id'];
						$attendeeData[$key]['order_id'] = $orderID;
					}
				}

				// To save attendees data.
				JLoader::import('components.com_jticketing.models.attendees', JPATH_SITE);
				$attendeesModel = JModelLegacy::getInstance('Attendees', 'JticketingModel');
				$result = $attendeesModel->save($attendeeData);
			}

			if (isset($data['user_id']))
			{
				$data['order_id']        = $orderID;
				$data['checkout_method'] = 'registered';
				isset($data['comment']) ? $data['comment'] : $data['comment'] = '';

				// To save user data.
				JLoader::import('components.com_jticketing.models.user', JPATH_SITE);
				$userModel = JModelLegacy::getInstance('User', 'JticketingModel');
				$result = $userModel->save($data);

				if (!empty($orderID))
				{
					$orderData = $this->getItem($orderID);

					if ($orderData->amount == '0' && !empty($user->id))
					{
						JLoader::import('components.com_jticketing.helpers.common', JPATH_SITE);
						$JticketingCommonHelper = new JticketingCommonHelper;
						$result = $JticketingCommonHelper->createFreeTicket($user->id, $orderID);

						return $result;
					}
				}
			}

			return true;
		}
		else
		{
			return false;
		}
	}
}
