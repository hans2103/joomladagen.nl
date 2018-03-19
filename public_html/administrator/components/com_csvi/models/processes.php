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
 * Processes model.
 *
 * @package     CSVI
 * @subpackage  Processes
 * @since       6.0
 */
class CsviModelProcesses extends JModelList
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
				'csvi_process_id', 'a.csvi_process_id',
				'name', 'u.name',
				'processfile', 'a.processfile',
				'processfolder', 'a.processfolder',
				'position', 'a.position',
				'template_name', 'a.template_name',
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
	protected function populateState($ordering = 'a.csvi_process_id', $direction = 'DESC')
	{
		// List state information.
		parent::populateState($ordering, $direction);
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
			->from($this->db->quoteName('#__csvi_processes', 'a'))
			->leftJoin(
				$this->db->quoteName('#__csvi_templates', 't')
				. ' ON ' . $this->db->quoteName('t.csvi_template_id') . ' = ' . $this->db->quoteName('a.csvi_template_id')
			)
			->leftJoin(
				$this->db->quoteName('#__users', 'u')
				. ' ON ' . $this->db->quoteName('u.id') . ' = ' . $this->db->quoteName('a.userId')
			)
			->select(
				$this->db->quoteName(
					array(
						'csvi_process_id',
						'processfile',
						'processfolder',
						'position',
						't.template_name',
						'u.name',
					)
				)
			);

		// Filter by search field
		$search = $this->getState('filter.search');

		if ($search)
		{
			$query->where($this->db->quoteName('t.template_name') . ' LIKE ' . $this->db->quote('%' . $search . '%'), 'OR');
			$query->where($this->db->quoteName('u.name') . ' LIKE ' . $this->db->quote('%' . $search . '%'));
			$query->where($this->db->quoteName('a.processfile') . ' LIKE ' . $this->db->quote('%' . $search . '%'));
			$query->where($this->db->quoteName('a.processfolder') . ' LIKE ' . $this->db->quote('%' . $search . '%'));
		}

		// Add the list ordering clause.
		$query->order(
			$this->db->quoteName(
				$this->db->escape(
					$this->getState('list.ordering', 'a.csvi_process_id')
				)
			)
			. ' ' . $this->db->escape($this->getState('list.direction', 'DESC'))
		);

		return $query;
	}
}
