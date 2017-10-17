<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Class for showing toolbar in backend jticketing toolbar
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingOrdersHelper
{
	/**
	 * function for adding credit entry
	 *
	 * @param   integer  $order_id  integer
	 *
	 * @param   integer  $status    string
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addEntry($order_id, $status)
	{
		$data = $this->getOrderDetails($order_id);
		$data->client = "com_jticketing";
		$xrefId = $data->event_details_id;
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'Integrationxref');
		$JticketingModelIntegrationxref = JModelLegacy::getInstance('Integrationxref', 'JTicketingModel');
		$integrationDetails = $JticketingModelIntegrationxref->getItem($xrefId);
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'vendor');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/tables', 'vendor');
		$TjvendorsModelVendor = JModelLegacy::getInstance('Vendor', 'TJvendorsModel');
		$vendorDetails = $TjvendorsModelVendor->getItem($integrationDetails->vendor_id);
		$com_params = JComponentHelper::getParams($data->client);
		$configCurrency = $com_params->get('currency');
		$checkOrderPayout = $this->checkOrderPayout($order_id);

		// Column name will change as per in the database for currency
		if (!empty($data->currency))
		{
			$currency = $data->currency;
		}
		else
		{
			$currency = $configCurrency;
		}

		$entry_data['vendor_id'] = $integrationDetails->vendor_id;
		$totalAmount = TjvendorsHelpersTjvendors::getTotalAmount($entry_data['vendor_id'], $currency, 'com_jticketing');
		$entry_data['reference_order_id'] = $data->order_id;
		$transactionClient = "Jticketing";
		$entry_data['transaction_id'] = $transactionClient . '-' . $currency . '-' . $entry_data['vendor_id'] . '-';
		$entry_data['transaction_time'] = JFactory::getDate()->toSql();

		if ($status != "C")
		{
			if ($checkOrderPayout == $entry_data['transaction_id'])
			{
				return false;
			}
			else
			{
				if ($status == "RF")
				{
					$entry_status = "debit_refund";
				}
				elseif ($status == "P")
				{
					$entry_status = "debit_pending";
				}

					$entry_data['debit'] = $data->amount - $data->fee;
					$entry_data['credit'] = '0.00';
					$entry_data['total'] = $totalAmount['total'] - $entry_data['debit'];
			}
		}

		elseif ($status == "C")
		{
			$entry_data['credit'] = $data->amount - $data->fee;
			$entry_data['total'] = $totalAmount['total'] + $entry_data['credit'];
			$entry_status = "credit_for_ticket_buy";
		}

		$params = array("customer_note" => $data->customer_note,"entry_status" => $entry_status);
		$entry_data['params'] = json_encode($params);
		$entry_data['currency'] = $currency;
		$entry_data['client'] = $data->client;
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'payout');
		$TjvendorsModelPayout = JModelLegacy::getInstance('Payout', 'TjvendorsModel');
		$vendorDetail = $TjvendorsModelPayout->addCreditEntry($entry_data);
	}

	/**
	 * function for chec order payout
	 *
	 * @param   integer  $order_id  integer
	 *
	 * @return  array|$orderDetails
	 *
	 * @since   2.0
	 */
	public function checkOrderPayout($order_id)
	{
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName('reference_order_id'))
			->from($db->quoteName('#__tjvendors_passbook'))
			->where($db->quoteName('debit') . ' > 0 ');
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * function for order check
	 *
	 * @param   integer  $order_id  integer
	 *
	 * @return  array|$orderDetails
	 *
	 * @since   2.0
	 */
	public function orderCheck($order_id)
	{
		$orderDetails = $this->getOrderDetails($order_id);
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName('total'))
			->from($db->quoteName('#__tjvendors_passbook'))
			->where($db->quoteName('reference_order_id') . ' = ' . $db->quote($orderDetails['order_id']));
		$db->setQuery($query);
		$orderDetails = $db->loadResult();

		return $orderDetails;
	}

	/**
	 * function for geting order details
	 *
	 * @param   integer  $order_id  integer
	 *
	 * @return  array|$orderDetails
	 *
	 * @since   2.0
	 */
	public function getOrderDetails($order_id)
	{
		$db = JFactory::getDbo();
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'order');
		$JticketingModelOrders = JModelLegacy::getInstance('Order ', 'JticketingModel');
		$orderDetails = $JticketingModelOrders->getItem($order_id);

		return $orderDetails;
	}

	/**
	 * function for geting order details
	 *
	 * @return  true
	 *
	 * @since   2.0
	 */
	public function checkPayoutPermit()
	{
		$com_params = JComponentHelper::getParams('com_tjvendors');
		$payout_day_limit = $com_params->get('payout_limit_days', '0', 'INT');
		$date = JFactory::getDate();
		$presentDate = $date->modify("-" . $payout_day_limit . " day");
		$payout_date_limit = $presentDate->format('Y-m-d');

		if ($date >= $payout_date_limit)
		{
			return true;
		}
	}

	/**
	 * function for geting order details
	 *
	 * @param   integer  $userId  integer
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function checkGatewayDetails($userId)
	{
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$arrayColumns = array('vc.params');
		$query->select($db->quoteName($arrayColumns));
		$query->from($db->quoteName('#__tjvendors_vendors', 'v'));
		$query->join('LEFT', $db->quoteName('#__vendor_client_xref', 'vc') .
		' ON (' . $db->quoteName('v.vendor_id') . ' = ' . $db->quoteName('vc.vendor_id') . ')');
		$query->where($db->quoteName('v.user_id') . ' = ' . $db->quote($userId));
		$db->setQuery($query);
		$result = $db->loadAssoc();
		$params = json_decode($result['params']);

		if (empty($params->payment_email_id))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
