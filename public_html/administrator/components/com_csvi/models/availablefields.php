<?php
/**
 * @package     CSVI
 * @subpackage  AvailableFields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Model for available fields.
 *
 * @package     CSVI
 * @subpackage  AvailableFields
 * @since       6.0
 */
class CsviModelAvailablefields extends JModelList
{
	/**
	 * The database class
	 *
	 * @var    JDatabase
	 * @since  6.6.0
	 */
	protected $db;

	/**
	 * Construct the class.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   6.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'action', 'tbl.action',
				'component', 'tbl.component',
				'operation', 'tbl.operation',
				'template_name', 't.template_name',
				'idfields', 'tbl.idfields',
				'csvi_name', 'tbl.csvi_name',
				'component_name', 'tbl.component_name',
				'template_table', 't.template_table',
			);
		}

		// Load the basics
		$this->db = JFactory::getDbo();

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery  The query to execute.
	 *
	 * @since   4.0
	 *
	 * @throws  RuntimeException
	 */
	protected function getListQuery()
	{
		$query = $this->db->getQuery(true);

		// Clean out some settings so we can reset them
		$query->clear('select');
		$query->clear('from');
		$query->clear('where');

		// Setup the new settings
		$query->select(
			array(
				$this->db->quoteName('csvi_name'),
				$this->db->quoteName('component_name'),
				$this->db->quoteName('component_table'),
				$this->db->quoteName('isprimary')
			)
		);

		// Join the template table
		$query->from($this->db->quoteName('#__csvi_availablefields', 'tbl'));
		$query->leftJoin(
			$this->db->quoteName('#__csvi_availabletables', 't')
			. ' ON ' . $this->db->quoteName('t.template_table') . ' = ' . $this->db->quoteName('tbl.component_table')
		);

		// Filter by search field
		$search = $this->getState('filter.search');

		if ($search)
		{
			$query->where(
				'('
				. $this->db->quoteName('csvi_name') . ' LIKE ' . $this->db->quote('%' . $search . '%')
				. ' OR ' . $this->db->quoteName('component_name') . ' LIKE ' . $this->db->quote('%' . $search . '%')
				. ' OR ' . $this->db->quoteName('csvi_name') . ' LIKE ' . $this->db->quote('%' . $search . '%')
				. ')'
			);
		}

		// Filter by action
		$action = $this->getState('filter.action');

		if ($action)
		{
			$query->where($this->db->quoteName('tbl.action') . ' = ' . $this->db->quote($action));
			$query->where($this->db->quoteName('t.action') . ' = ' . $this->db->quote($action));
		}

		// Filter by component
		$component = $this->getState('filter.component');

		if ($component)
		{
			$query->where($this->db->quoteName('tbl.component') . ' = ' . $this->db->quote($component));
			$query->where($this->db->quoteName('t.component') . ' = ' . $this->db->quote($component));
		}

		// Filter by operation
		$operation = $this->getState('filter.operation');

		if ($operation)
		{
			$query->where($this->db->quoteName('t.task_name') . ' = ' . $this->db->quote($operation));
		}

		// Filter by operation
		$idfields = $this->getState('filter.idfields');

		if (!$idfields)
		{
			$query->where(
				'(' . $this->db->quoteName('csvi_name') . ' NOT LIKE ' . $this->db->quote('%\_id') . ' AND ' . $this->db->quoteName('csvi_name')
				. ' NOT LIKE ' . $this->db->quote('id') . ')'
			);
		}

		// Add the list ordering clause.
		$query->order(
			$this->db->quoteName(
				$this->db->escape(
					$this->getState('list.ordering', 'tbl.csvi_name')
				)
			)
			. ' ' . $this->db->escape($this->getState('list.direction', 'ASC'))
		);

		// Group the value
		$query->group($this->db->quoteName(array('csvi_name', 'component_table')));

		return $query;
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
	 * @since   6.6.0
	 */
	protected function populateState($ordering = 'tbl.csvi_name', $direction = 'ASC')
	{
		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   12.2
	 */
	protected function getStoreId($id = '')
	{
		// Add the list state to the store id.
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');
		$id .= ':' . $this->getState('filter.search');

		return md5($this->context . ':' . $id);
	}
}
