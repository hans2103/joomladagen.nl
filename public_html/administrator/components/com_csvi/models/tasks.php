<?php
/**
 * @package     CSVI
 * @subpackage  Tasks
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Tasks model.
 *
 * @package     CSVI
 * @subpackage  Tasks
 * @since       6.0
 */
class CsviModelTasks extends JModelList
{
	/**
	 * The database class
	 *
	 * @var    JDatabaseDriver
	 * @since  6.0
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
				'ordering', 'a.ordering',
				'csvi_task_id', 'a.csvi_task_id',
				'task_name', 'a.task_name',
				'action', 'a.action',
				'component', 'a.component',
				'enabled', 'a.enabled',
			);
		}

		// Load the basics
		$this->db = JFactory::getDbo();

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
	 * @since   6.6.0
	 */
	protected function populateState($ordering = 'a.component', $direction = 'ASC')
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
		$id .= ':' . $this->getState('filter.action');
		$id .= ':' . $this->getState('filter.component');
		$id .= ':' . $this->getState('filter.task_name');

		return md5($this->context . ':' . $id);
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
		// Get the parent query
		$query = $this->db->getQuery(true)
			->from($this->db->quoteName('#__csvi_tasks', 'a'))
			->leftJoin(
				$this->db->quoteName('#__users', 'u')
				. ' ON ' . $this->db->quoteName('a.locked_by') . ' = ' . $this->db->quoteName('u.id')
			)
			->select(
				$this->db->quoteName(
					array(
						'csvi_task_id',
						'task_name',
						'action',
						'component',
						'url',
						'enabled',
						'ordering',
					)
				)
			)
			->select($this->db->quoteName('u.name', 'editor'));

		// Filter by search field
		$search = $this->getState('filter.search');

		if ($search)
		{
			$query->where($this->db->quoteName('a.task_name') . ' LIKE ' . $this->db->quote('%' . $search . '%'));
		}

		// Filter by action
		$action = $this->getState('filter.action');

		if ($action)
		{
			$query->where($this->db->quoteName('a.action') . ' = ' . $this->db->quote($action));
		}

		// Filter by component
		$component = $this->getState('filter.component');

		if ($component)
		{
			$query->where($this->db->quoteName('component') . ' = ' . $this->db->quote($component));
		}

		// Filter by published
		$enabled = $this->getState('filter.enabled', '');

		if ('' !== $enabled)
		{
			$query->where($this->db->quoteName('enabled') . ' = ' . $enabled);
		}

		// Add the list ordering clause.
		$query->order(
			$this->db->quoteName(
				$this->db->escape(
					$this->getState('list.ordering', 'a.component')
				)
			)
			. ' ' . $this->db->escape($this->getState('list.direction', 'ASC'))
		);

		return $query;
	}

	/**
	 * Load the operations.
	 *
	 * @param   mixed  $type       The type of template to filter on.
	 * @param   mixed  $component  The name of the component.
	 *
	 * @return  array  List of template types.
	 *
	 * @since   3.0
	 *          
	 * @throws  RuntimeException
	 */
	public function getOperations($type, $component)
	{
		$types = array();

		if ($type && $component)
		{
			$query = $this->db->getQuery(true)
				->select(
					"CONCAT('COM_CSVI_', " . $this->db->quoteName('component') . ", '_', " . $this->db->quoteName('task_name') . ") AS " . $this->db->quoteName('name')
					. ',' . $this->db->quoteName('task_name', 'value')
				)
				->from($this->db->quoteName('#__csvi_tasks'));

			// Check any selectors
			$query->where($this->db->quoteName('action') . ' = ' . $this->db->quote($type));
			$query->where($this->db->quoteName('component') . ' = ' . $this->db->quote($component));

			// Order by name
			$query->order($this->db->quoteName('ordering'));
			$this->db->setQuery($query);
			$types = $this->db->loadObjectList();

			// Translate the strings
			foreach ($types as $key => $type)
			{
				$type->name = JText::_($type->name);
				$types[$key] = $type;
			}
		}

		return $types;
	}

	/**
	 * Load the option tabs for a specific task.
	 *
	 * @param   string  $component  The name of the component.
	 * @param   string  $action     The action to perform.
	 * @param   string  $operation  The operation to execute.
	 *
	 * @return  array  List of option tabs.
	 *
	 * @since   4.0
	 *          
	 * @throws  RuntimeException
	 */
	public function getTaskOptions($component, $action, $operation)
	{
		$options = array();

		if ($component && $action && $operation)
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('options'))
				->from($this->db->quoteName('#__csvi_tasks'))
				->where($this->db->quoteName('task_name') . ' = ' . $this->db->quote($operation))
				->where($this->db->quoteName('action') . ' = ' . $this->db->quote($action))
				->where($this->db->quoteName('component') . ' = ' . $this->db->quote($component));
			$this->db->setQuery($query);
			$result = $this->db->loadResult();
			$options = explode(',', $result);
		}

		return $options;
	}
}
