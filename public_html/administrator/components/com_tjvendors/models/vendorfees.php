<?php
/**
 * @version    SVN: 
 * @package    Com_Tjvendors
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.application.component.model');

/**
 * Methods supporting a list of Tjvendors records.
 *
 * @since  1.6
 */
class TjvendorsModelVendorFees extends JModelList
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
				'id', 'b.`id`',
				'currency', 'b.`currency`',
				'percent_commission', 'b.`percent_commission`',
				'flat_commission', 'b.`flat_commission`',
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
			$orderCol = 'b.id';
		}

		$this->setState('list.ordering', $orderCol);

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $search);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_tjvendors');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('b.id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$input = JFactory::getApplication()->input;
		$this->vendor_id = $input->get('vendor_id', '', 'INT');
		$vendor_id = $this->vendor_id;
		$client = $input->get('client', '', 'STRING');

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
	$query->select($db->quoteName(array('a.vendor_id','a.vendor_title','b.client','b.percent_commission','b.flat_commission','b.id','b.currency')));

		$query->from($db->quoteName('#__tjvendors_fee', 'b'));

	$query->join('LEFT', ($db->quoteName('#__tjvendors_vendors', 'a') . 'ON ' . $db->quoteName('b.vendor_id') . ' = ' . $db->quoteName('a.vendor_id') ));

		$query->where($db->quoteName('a.vendor_id') . ' = ' . $vendor_id);

		if (!empty($client))
		{
			$query->where($db->quoteName('b.client') . ' = ' . $db->quote($client));
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('b.id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('(b.currency LIKE ' . $search .
							'OR b.percent_commission LIKE' . $search .
							'OR b.flat_commission LIKE' . $search . ')');
			}

			$this->search = $search;
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
}
