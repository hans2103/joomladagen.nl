<?php
/**
 * @package     CSVI
 * @subpackage  Fieldmapper
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Field mapper model.
 *
 * @package     CSVI
 * @subpackage  Fieldmapper
 * @since       6.0
 */
class CsviModelMaps extends JModelList
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
	 * @since   6.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'component', 'm.component',
				'csvi_map_id', 'm.csvi_map_id',
				'action', 'm.action',
				'operation', 'm.operation',
				'title', 'm.title',
				'csvi_map_id', 'm.csvi_map_id',
			);
		}

		// Initialise some values
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
		$query->select(
			array(
			'csvi_map_id',
			'title',
			'mapfile',
			'component',
			'action',
			'operation',
			'auto_detect_delimiters',
			'im_mac',
			'field_delimiter',
			'text_enclosure',
			'locked_by',
			'locked_on'
		)
		);
		$query->from($this->db->quoteName('#__csvi_maps', 'm'));
		$query->select($this->db->quoteName('u.name', 'editor'));

		// Join the user table to get the editor
		$query->leftJoin(
			$this->db->quoteName('#__users', 'u')
			. ' ON ' . $this->db->quoteName('u.id') . ' = ' . $this->db->quoteName('m.locked_by')
		);

		// Filter by search field
		$search = $this->getState('filter.search');

		if ($search)
		{
			$query->where(
				$this->db->quoteName('m.title') . ' LIKE ' . $this->db->quote('%' . $search . '%')
			);
		}

		// Filter by action field
		$action = $this->getState('filter.action');

		if ($action)
		{
			$query->where($this->db->quoteName('m.action') . ' = ' . $this->db->quote($action));
		}

		// Filter by component field
		$component = $this->getState('filter.component');

		if ($component)
		{
			$query->where($this->db->quoteName('m.component') . ' = ' . $this->db->quote($component));
		}

		// Add the list ordering clause.
		$query->order(
			$this->db->quoteName(
				$this->db->escape(
					$this->getState('list.ordering', 'm.title')
				)
			)
			. ' ' . $this->db->escape($this->getState('list.direction', 'ASC'))
		);

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
	protected function populateState($ordering = 'm.title', $direction = 'ASC')
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
	 * Create a template from mapped settings.
	 *
	 * @param   int     $mapId  The ID of the field map.
	 * @param   string  $title  The name of the template to create.
	 *
	 * @return  bool  True if table has been created | False if template has not been created.
	 *
	 * @since   5.8
	 *          
	 * @throws  RuntimeException
	 */
	public function createTemplate($mapId, $title)
	{
		// Get the models
		$template = JTable::getInstance('Template', 'Table', array('dbo' => $this->db));
		$templateFields = JTable::getInstance('Templatefield', 'Table', array('dbo' => $this->db));

		// Collect the data
		$templateData = $this->getTemplateData($mapId);

		$data = array(
			'settings'      => json_encode($templateData),
			'action'        => $templateData['action'],
			'template_name' => $title,
			'enabled'       => 1,
		);

		if ($data)
		{
			if ($template->save($data))
			{
				$fields = $this->getTemplateFields($mapId);

				foreach ($fields as $order => $field)
				{
					$saveField                        = new stdClass;
					$saveField->csvi_templatefield_id = 0;
					$saveField->csvi_template_id      = $template->get('csvi_template_id');
					$saveField->field_name            = $field;
					$saveField->enabled               = 1;
					$saveField->ordering              = $order + 1;

					$templateFields->save($saveField);
					$templateFields->reset();
				}

				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get the data to create a new template.
	 *
	 * @param   int  $mapId  The ID of the field map.
	 *
	 * @return  array  The template data.
	 *
	 * @since   5.8
	 *
	 * @throws  RuntimeException
	 */
	private function getTemplateData($mapId)
	{
		$data = array();

		// Get the map details
		$query = $this->db->getQuery(true)
			->select(
				array(
					$this->db->quoteName('m.action'),
					$this->db->quoteName('m.component'),
					$this->db->quoteName('m.operation'),
					$this->db->quoteName('m.auto_detect_delimiters'),
					$this->db->quoteName('m.field_delimiter'),
					$this->db->quoteName('m.text_enclosure'),
					$this->db->quoteName('m.im_mac')
				)
			)
			->from($this->db->quoteName('#__csvi_maps', 'm'))
			->where($this->db->quoteName('m.csvi_map_id') . ' = ' . (int) $mapId);
		$this->db->setQuery($query);
		$map = $this->db->loadObject();

		// Get the options if we have a result
		if ($map)
		{
			$data['action'] = $map->action;
			$data['component'] = $map->component;
			$data['operation'] = $map->operation;
			$data['auto_detect_delimiters'] = $map->auto_detect_delimiters;
			$data['field_delimiter'] = $map->field_delimiter;
			$data['text_enclosure'] = $map->text_enclosure;
			$data['use_column_headers'] = 0;
			$data['source'] = 'fromupload';
			$data['im_mac'] = $map->im_mac;
		}

		// Return the data
		return $data;
	}

	/**
	 * Get the fields to create template fields.
	 *
	 * @param   int  $mapId  The ID of the field map.
	 *
	 * @return  array  The template fields.
	 *
	 * @since   5.8
	 *
	 * @throws  RuntimeException
	 */
	private function getTemplateFields($mapId)
	{
		$data = array();

		if ($mapId)
		{
			// Get the map details
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('templateheader'))
				->from($this->db->quoteName('#__csvi_mapheaders'))
				->where($this->db->quoteName('map_id') . ' = ' . (int) $mapId);
			$this->db->setQuery($query);
			$data = $this->db->loadColumn();
		}

		// Return the data
		return $data;
	}
}
