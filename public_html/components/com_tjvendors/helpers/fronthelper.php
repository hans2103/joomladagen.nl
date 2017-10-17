<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Tjvendors
 * @author     Parth Lawate <contact@techjoomla.com>
 * @copyright  2016 Parth Lawate
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Class TjvendorsFrontendHelper
 *
 * @since  1.6
 */
class TjvendorFrontHelper
{
	/**
	 * Get an instance of the named model
	 *
	 * @param   string  $name  Model name
	 *
	 * @return null|object
	 */
	public static function getModel($name)
	{
		$model = null;

		// If the file exists, let's
		if (file_exists(JPATH_SITE . '/components/com_tjvendors/models/' . strtolower($name) . '.php'))
		{
			require_once JPATH_SITE . '/components/com_tjvendors/models/' . strtolower($name) . '.php';
			$model = JModelLegacy::getInstance($name, 'TjvendorsModel');
		}

		return $model;
	}

	/**
	 * Get array of unique Clients
	 *
	 * @param   string  $user_id  To give user specific clients for the filter
	 *
	 * @return null|object
	 */
	public static function getUniqueClients($user_id)
	{
		$vendor_id = self::getvendor();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT' . $db->quoteName('client'));
		$query->from($db->quoteName('#__tjvendors_passbook', 'vendors'));

		if (!empty($vendor_id))
		{
			$query->where($db->quoteName('vendors.vendor_id') . ' = ' . $vendor_id);
		}

		$db->setQuery($query);
		$clients[] = JText::_('JFILTER_PAYOUT_CHOOSE_CLIENTS');

		$result = $db->loadAssocList();

		foreach ($result as $i)
		{
			$clients[] = $i;
		}

		return $clients;
	}

	/**
	 * Get array of pending payout amount
	 *
	 * @param   integer  $client    required to give vendor specific result
	 *
	 * @param   integer  $user_id   required to give user specific result
	 *
	 * @param   integer  $currency  required to give user specific result
	 *
	 * @return $totalDetails|array
	 */
	public static function getTotalDetails($client, $user_id, $currency)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$subQuery = $db->getQuery(true);
		$query->select('sum(CASE WHEN ' . $db->quoteName('credit') . ' >= 0 THEN ' . $db->quoteName('credit') . ' ELSE 0 END' . ') As credit');
		$query->select('sum(' . $db->quoteName('debit') . ') As debit');

		$query->from($db->quoteName('#__tjvendors_passbook'));

			$subQuery->select('vendor_id');
			$subQuery->from($db->quoteName('#__tjvendors_vendors'));
			$subQuery->where($db->quoteName('user_id') . ' = ' . $db->quote($user_id));
			$query->where($db->quoteName('vendor_id') . ' IN (' . $subQuery . ')');
			$query->order($db->quoteName('vendor_id') . ' ASC');

		if (!empty($currency))
		{
			$query->where($db->quoteName('currency') . ' = ' . $db->quote($currency));
		}

		if (!empty($client))
		{
			$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		}

		$db->setQuery($query);
		$rows = $db->loadAssoc();
		$totalDebitAmount = self::getPaidAmount($user_id, $currency, $client);
		$totalCreditAmount = $rows['credit'];
		$totalpendingAmount = $totalCreditAmount - $totalDebitAmount;

		$totalDetails = array("debitAmount" => $totalDebitAmount,"creditAmount" => $totalCreditAmount,"pendingAmount" => $totalpendingAmount);

		return $totalDetails;
	}

	/**
	 * Get paid amount
	 *
	 * @param   string  $user_id       integer
	 *
	 * @param   string  $currency      integer
	 *
	 * @param   string  $filterClient  client from filter
	 *
	 * @return amount
	 */
	public static function getPaidAmount($user_id, $currency, $filterClient)
	{
		$input = JFactory::getApplication()->input;
		$urlClient = $input->get('client', '', 'STRING');
		$com_params = JComponentHelper::getParams('com_tjvendors');
		$bulkPayoutStatus = $com_params->get('bulk_payout');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__tjvendors_passbook'));

		if ($filterClient != '0')
		{
			$client = $filterClient;
		}
		else
		{
			$client = $urlClient;
		}

		$vendor_id = self::getVendor();

		if (!empty($vendor_id))
		{
			$query->where($db->quoteName('vendor_id') . ' = ' . $db->quote($vendor_id));
		}

		if (!empty($currency))
		{
			$query->where($db->quoteName('currency') . ' = ' . $db->quote($currency));
		}

		if ($bulkPayoutStatus == 0 && !empty($client))
		{
			$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		}

		$db->setQuery($query);
		$paidDetails = $db->loadAssocList();
		$amount = 0;

		foreach ($paidDetails as $detail)
		{
			$entryStatus = json_decode($detail['params']);
			$entryStatus->entry_status;

			if ($entryStatus->entry_status == "debit_payout" && $detail['status'] == 1)
			{
				$amount = $amount + $detail['debit'];
			}
		}

		return $amount;
	}

	/**
	 * Get clients for vendors
	 *
	 * @param   integer  $vendor_id  required to give vendor specific result
	 *
	 * @return clientsForVendor|array
	 */
	public static function getClientsForVendor($vendor_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__vendor_client_xref'));

		if (!empty($vendor_id))
		{
			$query->where($db->quoteName('vendor_id') . ' = ' . $vendor_id);
		}

		$db->setQuery($query);
		$result = $db->loadAssocList();

		if (!empty($result))
		{
			foreach ($result as $client)
			{
				$clientsForVendor[] = $client['client'];
			}

			return $clientsForVendor;
		}
	}

	/**
	 * Get vendor for that user
	 *
	 * @return vendor
	 */
	public static function getvendor()
	{
		$user_id = jFactory::getuser()->id;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('vendor_id'));
		$query->from($db->quoteName('#__tjvendors_vendors'));
		$query->where($db->quoteName('user_id') . ' = ' . $user_id);
		$db->setQuery($query);
		$vendor = $db->loadResult();

		return $vendor;
	}

	/**
	 * Get vendor for that user
	 *
	 * @return vendor
	 */
	public static function getCurrencies()
	{
		$vendor_id = self::getvendor();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT' . $db->quoteName('currency'));
		$query->from($db->quoteName('#__tjvendors_passbook'));

		if (!empty($vendor_id))
		{
			$query->where($db->quoteName('vendor_id') . ' = ' . $vendor_id);
		}

		$db->setQuery($query);
		$currencies[] = JText::_('JFILTER_PAYOUT_CHOOSE_CURRENCY');

		$result = $db->loadAssocList();

		foreach ($result as $i)
		{
			$currencies[] = $i;
		}

		return $currencies;
	}

	/**
	 * Check for duplicate clients
	 *
	 * @param   integer  $vendor_id      required to give vendor specific result
	 *
	 * @param   integer  $vendor_client  client taken from the form
	 *
	 * @return vendor_client|string
	 */
	public static function checkForDuplicateClient($vendor_id,$vendor_client)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('client'));
		$query->from($db->quoteName('#__vendor_client_xref'));
		$query->where($db->quoteName('vendor_id') . ' = ' . $vendor_id);
		$db->setQuery($query);
		$result = $db->loadAssocList();

		foreach ($result as $client)
		{
			if ($client['client'] == $vendor_client)
			{
				return $vendor_client;
			}
		}
	}

	/**
	 * Get get paymentDetails
	 *
	 * @param   string  $vendor_id  integer
	 *
	 * @param   string  $client     integer
	 *
	 * @return res|integer
	 */
	public static function getPaymentDetails($vendor_id, $client)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__vendor_client_xref'));

		if (!empty($vendor_id))
		{
			$query->where($db->quoteName('vendor_id') . ' = ' . $db->quote($vendor_id));
		}

		if (!empty($client))
		{
			$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		}

		$db->setQuery($query);
		$res = $db->loadObject();

		return $res;
	}
}
