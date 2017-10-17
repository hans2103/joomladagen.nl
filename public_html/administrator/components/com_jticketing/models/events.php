<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jticketing
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Jticketing records.
 *
 * @since  1.6
 */
class JticketingModelEvents extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'ordering', 'a.ordering',
				'published', 'a.published',
				'title', 'a.title',
				'catid', 'a.catid',
				'created_by', 'a.created_by',
				'startdate', 'a.startdate',
				'enddate', 'a.enddate',
				'featured', 'a.featured',
				'created_by', 'a.created_by',
				'id', 'a.id'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$online_offline = $app->getUserStateFromRequest($this->context . '.filter.online_offline', 'filter_online_offline');
		$this->setState('filter.search', $online_offline);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Filtering catid
		$this->setState('filter.catid', $app->getUserStateFromRequest($this->context . 'filter.catid', 'filter_catid', '', 'string'));

		// Filtering created_by
		$this->setState('filter.created_by', $app->getUserStateFromRequest($this->context . '.filter.created_by', 'filter_created_by', '', 'string'));

		// Load the parameters.
		$params = JComponentHelper::getParams('com_jticketing');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.title', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'a.*'
			)
		);
		$query->from('`#__jticketing_events` AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the venue table
		$query->select('v.name, v.online_provider, v.address, v.country, v.state_id, v.city, v.zipcode, v.params');
		$query->join('LEFT', '#__jticketing_venues AS v ON v.id=a.venue');

		// Join over the venue table
		$query->select('con.country AS coutryName');
		$query->join('LEFT', '#__tj_country AS con ON con.id=v.country');

		// Join over the venue table
		$query->select('r.region');
		$query->join('LEFT', '#__tj_region AS r ON r.id=v.state_id');

		// Join over the category 'catid'
		$query->select('catid.id AS categoryID,catid.title AS catid');
		$query->join('LEFT', '#__categories AS catid ON catid.id = a.catid');

		// Join over the user field 'creator'
		$query->select('creator.name AS creator');
		$query->join('LEFT', '#__users AS creator ON creator.id = a.created_by');

		// Join over the user field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		$online_offline = $this->getState('filter.online_offline');

		if (!empty($online_offline))
		{
			if ($online_offline == 1)
			{
				$query->where($db->quoteName('a.online_events') . ' = ' . $db->quote($online_offline));
			}
			else
			{
				$query->where($db->quoteName('a.online_events') . ' = ' . $db->quote('0'));
			}
		}

		// Filter by published state
		$published = $this->getState('filter.state');

		// Code change by KOMAL line no 155 orignal code is elseif($published === '')
		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}
		elseif ($published == '')
		{
			$query->where('(a.state IN (0,1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.title LIKE ' . $search . ' )');
			}
		}

		// Filtering catid
		$filter_catid = $this->state->get("filter.catid");

		if ($filter_catid)
		{
			$query->where("a.catid = '" . $db->escape($filter_catid) . "'");
		}

		// Filtering created_by
		$filter_created_by = $this->state->get("filter.created_by");

		if ($filter_created_by)
		{
			$query->where("a.created_by = '" . $db->escape($filter_created_by) . "'");
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		return $query;
	}

	/**
	 * Method to get a list of users.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items AS $item)
		{
			$item->ticket_types = $this->getTicketTypes($item->id);
		}

		return $items;
	}

	/**
	 * Method to get a list of users.
	 *
	 * @param   integer  $eventid  An event id
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getTicketTypes($eventid = '')
	{
		$input            = JFactory::getApplication()->input;
		$eventid           = $input->get('id');

		$jticketingmainhelper = new jticketingmainhelper;

		if (!empty($eventid))
		{
			$xrefid = $jticketingmainhelper->getEventrefid($eventid);

			if ($xrefid)
			{
				$db    = JFactory::getDBO();
				$query = "SELECT id,paypal_email
				 FROM #__jticketing_integration_xref	WHERE id = {$xrefid}";
				$db->setQuery($query);
				$eventpresent = $db->loadObject();

				if (!empty($xrefid))
				{
					$query = "SELECT * FROM #__jticketing_types	WHERE eventid = " . $xrefid;
					$db->setQuery($query);
					$tickettypes  = $db->loadObjectlist();
				}

				return $tickettypes;
			}
		}
	}

	/**
	 * Method to ____.
	 *
	 * @param   array    $items     An array.
	 * @param   integer  $featured  set featured value.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function setItemFeatured($items, $featured)
	{
		$db    = $this->getDbo();
		$count = 0;

		if (is_array($items))
		{
			foreach ($items as $id)
			{
				$query = "UPDATE #__jticketing_events SET featured=" . $featured . " WHERE id=" . $id;
				$db->setQuery($query);

				if (!$db->execute())
				{
					$this->setError($this->_db->getErrorMsg());

					return 0;
				}

				$count++;
			}
		}

		return $count;
	}

	/**
	 * Method toget category name
	 *
	 * @param   integer  $id  category id
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	public function getCategoryName($id)
	{
		$db = JFactory::getDBO();
		$sql = "SELECT title FROM #__categories WHERE #__categories.id = $id";
		$db->setQuery($sql);
		$category = $db->loadResult();

		return $category;
	}
}
