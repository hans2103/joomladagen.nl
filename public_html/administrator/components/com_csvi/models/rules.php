<?php
/**
 * @package     CSVI
 * @subpackage  Rules
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Rules model.
 *
 * @package     CSVI
 * @subpackage  Rules
 * @since       6.0
 */
class CsviModelRules extends JModelList
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
				'csvi_rule_id', 'a.csvi_rule_id',
				'name', 'a.name',
				'action', 'a.action',
				'plugin', 'a.plugin',
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
	protected function populateState($ordering = 'a.ordering', $direction = 'ASC')
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
			->from($this->db->quoteName('#__csvi_rules', 'a'))
			->leftJoin(
				$this->db->quoteName('#__users', 'u')
				. ' ON ' . $this->db->quoteName('a.locked_by') . ' = ' . $this->db->quoteName('u.id')
			)
			->select(
				$this->db->quoteName(
					array(
						'csvi_rule_id',
						'plugin',
						'action',
						'ordering',
					)
				)
			)
			->select($this->db->quoteName('a.name'))
			->select($this->db->quoteName('u.name', 'editor'));

		// Filter by search field
		$search = $this->getState('filter.search');

		if ($search)
		{
			$query->where($this->db->quoteName('a.name') . ' LIKE ' . $this->db->quote('%' . $search . '%'));
		}

		// Filter by action
		$action = $this->getState('filter.action');

		if ($action)
		{
			$query->where($this->db->quoteName('a.action') . ' = ' . $this->db->quote($action));
		}

		// Filter by plugin
		$plugin = $this->getState('filter.plugin');

		if ($plugin)
		{
			$query->where($this->db->quoteName('a.plugin') . ' = ' . $this->db->quote($plugin));
		}

		// Add the list ordering clause.
		$query->order(
			$this->db->quoteName(
				$this->db->escape(
					$this->getState('list.ordering', 'a.ordering')
				)
			)
			. ' ' . $this->db->escape($this->getState('list.direction', 'DESC'))
		);

		return $query;
	}

	/**
	 * Get the list of items to show.
	 *
	 * @return  array  List of items to shown.
	 *
	 * @since   6.6.0
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Get the translated name
		$dispatcher = new RantaiPluginDispatcher;
		$dispatcher->importPlugins('csvirules', $this->db);
		$pluginNames = array();

		foreach ($items as $key => $item)
		{
			if (!array_key_exists($item->plugin, $pluginNames))
			{
				$singleName = $dispatcher->trigger('getSingleName', array($item->plugin));

				if (array_key_exists(0, $singleName))
				{
					$pluginNames[$item->plugin] = $singleName[0];
				}
			}

			if (array_key_exists($item->plugin, $pluginNames))
			{
				$item->plugin = $pluginNames[$item->plugin];
				$items[$key] = $item;
			}
		}

		return $items;
	}

	/**
	 * Check if rules plugins are disabled
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 */
	public function getRulesPluginStatus()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('extension_id'))
			->from($this->db->quoteName('#__extensions'))
			->where($this->db->quoteName('enabled') . ' = 0')
			->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('csvirules'));
		$this->db->setQuery($query);
		$pluginIds = $this->db->loadColumn();

		if ($pluginIds)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_CSVI_RULES_PLUGINS_DISABLED'), 'message');
		}
	}
}
