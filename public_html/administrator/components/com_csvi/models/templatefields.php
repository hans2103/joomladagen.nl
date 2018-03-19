<?php
/**
 * @package     CSVI
 * @subpackage  Templatefields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * The template fields model.
 *
 * @package     CSVI
 * @subpackage  Templatefields
 * @since       6.0
 */
class CsviModelTemplatefields extends JModelList
{
	/**
	 * Holds the database driver
	 *
	 * @var    JDatabase
	 * @since  6.0
	 */
	private $db;

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
				'csvi_templatefield_id', 'a.csvi_templatefield_id',
				'csvi_template_id', 'a.csvi_template_id',
				'field_name', 'a.field_name',
				'xml_node', 'a.xml_node',
				'column_header', 'a.column_header',
				'default_value', 'a.default_value',
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
	 *
	 * @throws  Exception
	 */
	protected function populateState($ordering = 'a.ordering', $direction = 'ASC')
	{
		// List state information.
		parent::populateState($ordering, $direction);

		$app = JFactory::getApplication();

		// Check if there is an override in the URL
		$templateId = $app->input->get->getInt('csvi_template_id', null);

		if ($templateId)
		{
			// Override for the database query
			$this->setState('filter.csvi_template_id', $templateId);

			// Override for the search tools filters
			$app->setUserState($this->context . '.filter.csvi_template_id', $templateId);
		}
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

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery  The query to execute.
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 */
	protected function getListQuery()
	{
		// Get the parent query
		$query = $this->db->getQuery(true)
			->from($this->db->quoteName('#__csvi_templatefields', 'a'))
			->leftJoin(
				$this->db->quoteName('#__csvi_templates', 't')
				. ' ON ' . $this->db->quoteName('a.csvi_template_id') . ' = ' . $this->db->quoteName('t.csvi_template_id')
			)
			->leftJoin(
				$this->db->quoteName('#__users', 'u')
				. ' ON ' . $this->db->quoteName('a.locked_by') . ' = ' . $this->db->quoteName('u.id')
			)
			->select(
				$this->db->quoteName(
					array(
						'a.csvi_templatefield_id',
						'a.csvi_template_id',
						'a.field_name',
						'a.table_name',
						'a.xml_node',
						'a.source_field',
						'a.column_header',
						'a.default_value',
						'a.enabled',
						'a.sort',
						'a.cdata',
						'a.ordering',
					)
				)
			)
			->select($this->db->quoteName('t.template_name'))
			->select($this->db->quoteName('u.name', 'editor'));

		// Filter by search field
		$search = $this->getState('filter.search');

		if ($search)
		{
			$query->where($this->db->quoteName('a.field_name') . ' LIKE ' . $this->db->quote('%' . $search . '%'));
		}

		// Filter by enabled
		$enabled = $this->getState('filter.enabled');

		if ('' !== $enabled && null !== $enabled)
		{
			$query->where($this->db->quoteName('a.enabled') . ' = ' . $this->db->quote($enabled));
		}

		// Filter by template
		$templateId = $this->getState('filter.csvi_template_id');

		if ('' !== $templateId && null !== $templateId)
		{
			$query->where($this->db->quoteName('a.csvi_template_id') . ' = ' . (int) $templateId);
		}

		// Add the list ordering clause.
		$query->order(
			$this->db->quoteName(
				$this->db->escape(
					$this->getState('list.ordering', 'a.ordering')
				)
			)
			. ' ' . $this->db->escape($this->getState('list.direction', 'ASC'))
		);

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   6.6.0
	 *
	 * @throws  RuntimeException
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if ($items)
		{
			foreach ($items as $key => $result)
			{
				$result->rules = $this->loadRules($result->csvi_templatefield_id);
				$items[$key] = $result;
			}
		}

		return $items;
	}

	/**
	 * Load the rules for a given field.
	 *
	 * @param   int  $csvi_templatefield_id  The ID of the field to get the rules for.
	 *
	 * @return  array  List of rules.
	 *
	 * @since   6.2.0
	 *
	 * @throws  RuntimeException
	 */
	private function loadRules($csvi_templatefield_id)
	{
		// Load the rule IDs
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('csvi_rule_id'))
			->from($this->db->quoteName('#__csvi_templatefields_rules'))
			->where($this->db->quoteName('csvi_templatefield_id') . ' = ' . (int) $csvi_templatefield_id);
		$this->db->setQuery($query);

		return $this->db->loadColumn();
	}
}
