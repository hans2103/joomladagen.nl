<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');


/**
 * JlikeModeljlike_likes
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JlikeModelContents extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since    1.6
	 */
	public function __construct($config = array())
	{
		$lang = JFactory::getLanguage();
		$lang->load('com_jlike', JPATH_ADMINISTRATOR);

		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.id',
				'a.title',
				'statusCount',
				'a.dislike_cnt'
			);
		}

		$this->comjlikeHelper = new comjlikeHelper;

		parent::__construct($config);
	}

	/**
	 *  Method to auto-populate the model state.  Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Ordering to fetch rec.
	 * @param   string  $direction  Direction to fetch rec.
	 *
	 * @return  null
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');
		$jinput = JFactory::getApplication()->input;
		$post = $jinput->post;

		// Load the parameters.
		$params = JComponentHelper::getParams('com_jlike');
		$this->setState('params', $params);

		// Date filter
		$defToDate = date('Y-m-d', strtotime(date('Y-m-d') . ' + 1 days'));
		$todate = $post->get('todate', $defToDate,  'STRING');

		$defFromDate = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		$fromdate = $post->get('fromdate', $defFromDate, 'STRING');

		$fromdate_filter = $app->getUserStateFromRequest('global.filter.fromdate', 'fromdate', $fromdate);
		$this->setState('filter.fromdate', $fromdate_filter);

		$todate_filter = $app->getUserStateFromRequest('global.filter.todate', 'todate', $todate);
		$this->setState('filter.todate', $todate_filter);

		// Zoo cateogry filter
		$jlZooCatId = $post->getInt('jlZooCatId', '');
		$jlZooCatId = $app->getUserStateFromRequest('global.filter.jlZooCatId', 'jlZooCatId', $jlZooCatId);
		$this->setState('filter.jlZooCatId', $jlZooCatId);

		// Get status filter
		$status_filter = $app->input->getInt('status_filter', '');
		$defStatusId = $this->comjlikeHelper->getDefStatus();
		$status_filter = $app->getUserStateFromRequest('global.filter.status_filter', 'status_filter', $defStatusId);
		$this->setState('filter.status_filter', $status_filter);

		// List state information.
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);
		$limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);

		if ($limit == 0)
		{
			$this->setState('list.start', 0);
		}
		else
		{
			$this->setState('list.start', $limitstart);
		}

		// Set ordering.
		$orderCol = $app->getUserStateFromRequest($this->context . 'filter_order', 'filter_order');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.id';
		}

		$this->setState('list.ordering', $orderCol);

		// Set ordering direction.
		$listOrder = $app->getUserStateFromRequest($this->context . 'filter_order_Dir', 'filter_order_Dir');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'DESC';
		}

		$this->setState('list.direction', $listOrder);

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $published);

		// Filter client.
		$client = $app->getUserStateFromRequest($this->context . '.filter.client', 'filter_client', '', 'string');
		$this->setState('filter.client', $client);

		// List state information.
		parent::populateState('a.id', 'asc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 *
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$jinput = JFactory::getApplication()->input;
		$post = $jinput->post;

		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select("a. * , zcat.category_id, count(sref.id) as statusCount");
		$query->from('`#__jlike_content` AS a')
		->join('LEFT', '#__zoo_category_item AS zcat ON a.`element_id` = zcat.item_id')
		->join('LEFT', '#__jlike_likeStatusXref AS sref ON sref.`content_id` = a.id')
		->group('a.id');

		// Zoo cateogry filter
		$jlZooCatId = $this->getState('filter.jlZooCatId');

		if (!empty($jlZooCatId))
		{
			$query->where('zcat.category_id = ' . (int) $jlZooCatId);
		}

		// Date filter
		$todate = $this->getState('filter.todate');
		$fromdate = $this->getState('filter.fromdate');
		$query->where("DATE(mdate) BETWEEN DATE('" . $fromdate . "') AND DATE('" . $todate . "')");

		// Get filter status.
		$status_filter = $this->getState('filter.status_filter');

		if ($status_filter)
		{
			$query->where('sref.status_id = ' . $status_filter);
		}

		// Filter by published state
		$published = $this->getState('filter.state');

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
				$query->where('a.title LIKE ' . $search);
			}
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
	 * Method to get an items.
	 *
	 * @return	mixed	Object Item list
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Return the zoo app id from alias.
	 *
	 * @param   string  $alias  alias.
	 *
	 * @since   2.2
	 * @return  list.
	 */
	public function getZooAppId($alias = 'plans-catalog')
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('c.id');
		$query->from('#__zoo_application AS c');
		$query->where("c.alias= '" . $alias . "'");
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get Item and status related data for csv emport
	 *
	 * @since   2.2
	 * @return  list.
	 */
	public function csvExportStatusdetails()
	{
		// Filter by search in title
		$limitstart = $this->getState('list.start');
		$limitEnd = $limitstart + $this->getState('list.limit');
		$this->comjlikeHelper = new comjlikeHelper;

		try
		{
			$db = JFactory::getDBO();
			$query = $this->getListQuery();
			$query = $query . " LIMIT " . $limitstart . " , " . $limitEnd;
			$db->setQuery($query);

			return $return = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());
			throw new Exception($this->_db->getErrorMsg());

			return;
		}
	}
}
