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
 * Messages model.
 *
 * @package  JDiDEAL
 * @since    4.0
 */
class JdidealgatewayModelMessages extends JModelList
{
	/**
	 * JDatabase connector
	 *
	 * @var    JDatabaseDriver
	 * @since  4.0
	 */
	private $db;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   4.0
	 */
	public function __construct($config = array())
	{
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
	 * @since   4.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('a.id', 'desc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$query = $this->db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
				array(
					$this->db->quoteName('a.id'),
					$this->db->quoteName('a.subject'),
					$this->db->quoteName('a.orderstatus'),
					$this->db->quoteName('a.language'),
					$this->db->quoteName('p.name'),
				)
			)
			->from($this->db->quoteName('#__jdidealgateway_messages', 'a'))
			->leftJoin(
				$this->db->quoteName('#__jdidealgateway_profiles', 'p')
				. ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('a.profile_id')
			);

		// Add the list ordering clause.
		$query->order(
			$this->db->quoteName(
				$this->db->escape(
					$this->getState('list.ordering', 'a.id')
				)
			) . ' ' . $this->db->escape($this->getState('list.direction', 'DESC'))
		);

		return $query;
	}
}
