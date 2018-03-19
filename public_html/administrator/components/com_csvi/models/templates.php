<?php
/**
 * @package     CSVI
 * @subpackage  Template
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Template model.
 *
 * @package     CSVI
 * @subpackage  Template
 * @since       6.0
 */
class CsviModelTemplates extends JModelList
{
	/**
	 * Holds the database driver
	 *
	 * @var    JDatabase
	 * @since  6.0
	 */
	protected $db;

	/**
	 * Holds the template settings
	 *
	 * @var    array
	 * @since  6.0
	 */
	protected $options;

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
				'csvi_template_id', 'a.csvi_template_id',
				'template_name', 'a.template_name',
				'action', 'a.action',
				'lastrun', 'a.lastrun',
				'frontend', 'a.frontend',
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
	protected function populateState($ordering = 'a.ordering', $direction = 'DESC')
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
		$id .= ':' . $this->getState('filter.enabled');

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
			->from($this->db->quoteName('#__csvi_templates', 'a'))
			->leftJoin(
				$this->db->quoteName('#__users', 'u')
				. ' ON ' . $this->db->quoteName('a.locked_by') . ' = ' . $this->db->quoteName('u.id')
			)
			->select(
				$this->db->quoteName(
					array(
						'csvi_template_id',
						'template_name',
						'settings',
						'advanced',
						'action',
						'frontend',
						'secret',
						'log',
						'lastrun',
						'enabled',
						'ordering',
					)
				)
			)
			->select($this->db->quoteName('enabled', 'published'))
			->select($this->db->quoteName('u.name', 'editor'));

		// Filter by search field
		$search = $this->getState('filter.search');

		if ($search)
		{
			$query->where($this->db->quoteName('a.template_name') . ' LIKE ' . $this->db->quote('%' . $search . '%'));
		}

		// Filter by action
		$action = $this->getState('filter.action');

		if ($action)
		{
			$query->where($this->db->quoteName('a.action') . ' = ' . $this->db->quote($action));
		}

		// Filter by enabled
		$enabled = $this->getState('filter.enabled');

		if ('' !== $enabled && null !== $enabled)
		{
			$query->where($this->db->quoteName('a.enabled') . ' = ' . (int) $enabled);
		}

		// Filter by frontend
		$enabled = $this->getState('filter.frontend');

		if ('' !== $enabled && null !== $enabled)
		{
			$query->where($this->db->quoteName('a.frontend') . ' = ' . (int) $enabled);
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
	 * Get a list of templates.
	 *
	 * @return  array  List of template objects.
	 *
	 * @since   3.0
	 *
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 */
	public function getTemplates()
	{
		$query = $this->db->getQuery(true);
		$query->select(
				array(
					$this->db->quoteName('template_name', 'text'),
					$this->db->quoteName('csvi_template_id', 'value'),
					$this->db->quoteName('action')
				)
			)
			->from($this->db->quoteName('#__csvi_templates'))
			->order($this->db->quoteName('template_name'));

		$this->db->setQuery($query);
		$loadtemplates = $this->db->loadObjectList();

		if (!is_array($loadtemplates))
		{
			$templates = array();
			$templates[] = JHtml::_('select.option', '', JText::_('COM_CSVI_SAVE_AS_NEW_FOR_NEW_TEMPLATE'));
		}

		$import = array();
		$export = array();

		// Group the templates by process
		if (!empty($loadtemplates))
		{
			foreach ($loadtemplates as $tmpl)
			{
				if ($tmpl->action === 'import')
				{
					$import[] = $tmpl;
				}
				elseif ($tmpl->action === 'export')
				{
					$export[] = $tmpl;
				}
			}
		}

		// Merge the whole thing together
		$templates[] = JHtml::_('select.option', '', JText::_('COM_CSVI_SELECT_TEMPLATE'));
		$templates[] = JHtml::_('select.option', '', JText::_('COM_CSVI_TEMPLATE_IMPORT'), 'value', 'text', true);
		$templates = array_merge($templates, $import);
		$templates[] = JHtml::_('select.option', '', JText::_('COM_CSVI_TEMPLATE_EXPORT'), 'value', 'text', true);
		$templates = array_merge($templates, $export);

		return $templates;
	}
}
