<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

/**
 * Log Model.
 *
 * @package  JDiDEAL
 * @since    3.0
 */
class JdidealgatewayModelLogs extends JModelList
{
	/**
	 * Database driver
	 *
	 * @var    JDatabaseDriver
	 * @since  3.0
	 */
	private $db;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   4.0
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'origin', 'a.origin',
				'order_id', 'a.order_id',
				'order_number', 'a.order_number',
				'amount', 'a.amount',
				'card', 'a.card',
				'trans', 'a.trans',
				'psp', 'a.psp',
				'result', 'a.',
			);
		}

		parent::__construct($config);

		$this->db = JFactory::getDbo();
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState('a.date_added',  'DESC');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 */
	protected function getListQuery()
	{
		// Build the query
		$query = $this->db->getQuery(true)
			->select(
				array(
					$this->db->quoteName('a.id'),
					$this->db->quoteName('a.trans'),
					$this->db->quoteName('a.order_id'),
					$this->db->quoteName('a.order_number'),
					$this->db->quoteName('a.amount'),
					$this->db->quoteName('a.card'),
					$this->db->quoteName('a.origin'),
					$this->db->quoteName('a.date_added'),
					$this->db->quoteName('a.result'),
					$this->db->quoteName('p.alias'),
					$this->db->quoteName('p.psp'),
				)
			)
			->from($this->db->quoteName('#__jdidealgateway_logs', 'a'))
			->leftJoin(
				$this->db->quoteName('#__jdidealgateway_profiles', 'p')
				. ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('a.profile_id')
			);

		// Filter by search field
		$search = $this->getState('filter.search');

		if ($search)
		{
			$searchArray = array(
				$this->db->quoteName('a.order_id') . ' LIKE ' . $this->db->quote('%' . $search . '%'),
				$this->db->quoteName('a.order_number') . ' LIKE ' . $this->db->quote('%' . $search . '%'),
				$this->db->quoteName('a.amount') . ' LIKE ' . $this->db->quote('%' . $search . '%'),
				$this->db->quoteName('a.trans') . ' LIKE ' . $this->db->quote('%' . $search . '%'),
			);

			$query->where('(' . implode(' OR ', $searchArray) . ')');
		}

		// Filter by origin field
		$origin = $this->getState('filter.origin');

		if ($origin)
		{
			$query->where($this->db->quoteName('a.origin') . ' = ' . $this->db->quote($origin));
		}

		// Filter by card field
		$card = $this->getState('filter.card');

		if ($card)
		{
			$query->where($this->db->quoteName('a.card') . ' = ' . $this->db->quote($card));
		}

		// Filter by result field
		$result = $this->getState('filter.result');

		if ($result)
		{
			$query->where($this->db->quoteName('a.result') . ' = ' . $this->db->quote($result));
		}

		// Filter by provider field
		$psp = $this->getState('filter.psp');

		if ($psp)
		{
			$query->where($this->db->quoteName('a.profile_id') . ' = ' . (int) $psp);
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.date_added');
		$orderDirn = $this->state->get('list.direction', 'desc');

		$query->order($this->db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Load the history of a log entry.
	 *
	 * @return  string  The log history.
	 *
	 * @since   3.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 */
	public function getHistory()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('history'))
			->from($this->db->quoteName('#__jdidealgateway_logs'))
			->where($this->db->quoteName('id') . ' = ' . (int) JFactory::getApplication()->input->getInt('log_id', 0));
		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Load all available addons.
	 *
	 * @return  array  An array of available addons.
	 *
	 * @since   2.8
	 */
	public function getAddons()
	{
		jimport('joomla.filesystem.folder');
		$files = JFolder::files(JPATH_COMPONENT_ADMINISTRATOR . '/models/addons', '.php');
		$classes = array();

		foreach ($files as $file)
		{
			if ($file !== 'addon.php')
			{
				require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/addons/' . $file;
				$origin = basename($file, '.php');
				$className = 'Addon' . $origin;
				$classes[$origin] = new $className;
			}
		}

		return $classes;
	}
}
