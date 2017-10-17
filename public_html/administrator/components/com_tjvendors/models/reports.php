<?php
/**
 * @version    SVN:
 * @package    Com_Tjvendors
 * @author     Techjoomla  <contact@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Tjvendors records.
 *
 * @since  1.6
 */
class TjvendorsModelReports extends JModelList
{
/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'pass.`id`',
				'total', 'pass.`total`',
				'currency', 'pass.`currency`',
				'vendor_title', 'vendors.`vendor_title`',
				'client', 'vendors.`vendor_client`',
				'transaction_id', 'pass.`transaction_id`',
				'transaction_time', 'pass.`transaction_time`',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Set ordering.
		$orderCol = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'vendors.vendor_id';
		}

		$this->setState('list.ordering', $orderCol);

		$vendorId = $app->getUserStateFromRequest($this->context . '.filter.vendor_id', 'vendor_id', '0', 'string');
		$this->setState('filter.vendor_id', $vendorId);

		$currency = $app->getUserStateFromRequest($this->context . '.filter.currency', 'currency', '0', 'string');
		$this->setState('filter.currency', $currency);

		$fromDate = $app->getUserStateFromRequest($this->context . '.filter.fromDate', 'fromDates', '0', 'string');
		$this->setState('filter.fromDate', $fromDate);

		$toDate = $app->getUserStateFromRequest($this->context . '.filter.toDate', 'toDates', '0', 'string');
		$this->setState('filter.toDate', $toDate);

		$client = $app->getUserStateFromRequest($this->context . '.filter.vendor_client', 'vendor_client', '0', 'string');
		$this->setState('filter.vendor_client', $client);

		$paidUnpaid = $app->getUserStateFromRequest($this->context . '.filter.paidUnpaid', 'paidUnpaid', '0', 'string');
		$this->setState('filter.paidUnpaid', $paidUnpaid);

		$transactionType = $app->getUserStateFromRequest($this->context . '.filter.transactionType', 'transactionType', '0', 'string');
		$this->setState('filter.transactionType', $transactionType);

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_tjvendors');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('vendors.vendor_id', 'desc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */

	public function getListQuery()
	{
		$transactionType = $this->getState('filter.transactionType', '');
		$client = $this->getState('filter.vendor_client', '');
		$currency = $this->getState('filter.currency', '');
		$vendor_id = $this->getState('filter.vendor_id', '');

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select(array('vendors.vendor_id','vendors.vendor_title','pass.*'));
		$query->from($db->quoteName('#__tjvendors_vendors', 'vendors'));
		$query->join('LEFT', $db->quoteName('#__tjvendors_passbook', 'pass') .
			' ON (' . $db->quoteName('vendors.vendor_id') . ' = ' . $db->quoteName('pass.vendor_id') . ')');
		$query->where($db->quoteName('pass.id') . ' is not null');

		if (!empty($transactionType))
		{
			if ($transactionType == "debit")
			{
				$query->where($db->quoteName('debit') . " >0 ");
			}
			else
			{
				$query->where($db->quoteName('credit') . " >0 ");
			}
		}

		if (!empty($client))
		{
				$query->where($db->quoteName('client') . ' = ' . $db->quote($client));
		}

		if (!empty($vendor_id))
		{
				$query->where($db->quoteName('pass.vendor_id') . ' = ' . $db->quote($vendor_id));
		}

		if (!empty($currency))
		{
				$query->where($db->quoteName('pass.currency') . ' = ' . $db->quote($currency));
		}

		$fromDate = $this->getState('filter.fromDate', '');
		$toDate = $this->getState('filter.toDate', '');

		if (empty($toDate) && !empty($fromDate))
		{
			$query->where($db ->quoteName('transaction_time') . " >= " . $db->quote($fromDate));
		}
		elseif (empty($fromDate) && !empty($toDate))
		{
			$query->where($db ->quoteName('transaction_time') . " <= " . $db->quote($toDate));
		}
		elseif (!empty($fromDate) && !empty($toDate))
		{
			$query->where($db ->quoteName('transaction_time') . 'BETWEEN' . "'$fromDate'" . 'AND' . "'$toDate'");
		}
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * get the list items
	 *
	 * @return   items
	 *
	 * @since    1.6
	 */

	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $i => $item)
		{
			$entry_status = json_decode($item->params);

			if ($entry_status->entry_status == "credit_remaining_payout")
			{
				unset($items[$i]);
			}
		}

		return $items;
	}
}
