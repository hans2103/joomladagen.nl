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
 * Log model.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealgatewayModelPays extends JModelList
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
				'user_email', 'a.user_email',
				'amount', 'a.amount',
				'status', 'a.status',
				'remark', 'a.remark',
				'cdate', 'a.cdate',
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
		parent::populateState('a.id',  'DESC');
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
					$this->db->quoteName('a.user_email'),
					$this->db->quoteName('a.amount'),
					$this->db->quoteName('a.status'),
					$this->db->quoteName('a.remark'),
					$this->db->quoteName('a.cdate'),
				)
			)
			->from($this->db->quoteName('#__jdidealgateway_pays', 'a'));

		// Filter by search field
		$search = $this->getState('filter.search');

		if ($search)
		{
			$searchArray = array(
				$this->db->quoteName('a.id') . ' LIKE ' . $this->db->quote('%' . $search . '%'),
				$this->db->quoteName('a.user_email') . ' LIKE ' . $this->db->quote('%' . $search . '%'),
				$this->db->quoteName('a.amount') . ' LIKE ' . $this->db->quote('%' . $search . '%'),
				$this->db->quoteName('a.remark') . ' LIKE ' . $this->db->quote('%' . $search . '%'),
			);

			$query->where('(' . implode(' OR ', $searchArray) . ')');
		}

		// Filter by status field
		$status = $this->getState('filter.status');

		if ($status)
		{
			$query->where($this->db->quoteName('a.status') . ' = ' . $this->db->quote($status));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.date_added');
		$orderDirn = $this->state->get('list.direction', 'desc');

		$query->order($this->db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}
