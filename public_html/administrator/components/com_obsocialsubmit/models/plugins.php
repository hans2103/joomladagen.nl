<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
/**
 * Modules Component Module Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.5
 */
class ObsocialSubmitModelPlugins extends JModelList
{
	protected $text_prefix = 'COM_OBSOCIALSUBMIT_PLUGINS';
	protected $connections = null;
	protected $event_change_state = null;
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id',
				'folder',
				'name',
				'checked_out',
				'checked_out_time',
				'enabled',
				'access',
				'ordering',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		$module = $this->getUserStateFromRequest($this->context.'.filter.adapter', 'filter_type', '', 'string');
		$this->setState('filter.type', $module);


		// Load the parameters.
		$params = JComponentHelper::getParams('com_obsocialsubmit');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('i.ordering', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string	A prefix for the store id.
	 *
	 * @return	string	A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState( 'filter.search' );
		$id	.= ':'.$this->getState( 'filter.access' );
		$id	.= ':'.$this->getState( 'filter.state' );
		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'`extension_id` AS `id`, `name`, `folder`, `enabled`, `manifest_cache`, `ordering`')
		);
		$query->from($db->quoteName('#__extensions').' AS i');

		

		// Filter by published state
		$state = $this->getState('filter.state');
		if (is_numeric($state)) {
			$query->where('enabled = '.(int) $state);
		}
		elseif ($state === '') {
			$query->where('(enabled IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0) {
				$query->where('i.id = '.(int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('('.'name LIKE '.$search.' OR element LIKE '.$search.')');
			}
		}
		
		// Filter by published state
		$filter_type = trim($this->getState('filter.type'));
		if ( $filter_type ) {
			$query->where('`folder` = "'.$filter_type.'"');
		} else {
			$query->where("`folder` IN ('obss_intern' , 'obss_extern')");
		}

		// Add the list ordering clause.
		$orderCol		= $this->state->get('list.ordering', 'ordering');
		$orderDirn		= $this->state->get('list.direction', 'ASC');

		$query->order($db->escape($orderCol.' '.$orderDirn));
		return $query;
	}
	
	public function getPluginTypes(){
		$options	= array();
		$options[]	= JHtml::_('select.option',	'obss_intern',	JText::_($this->text_prefix.'_INTERN'));
		$options[]	= JHtml::_('select.option',	'obss_extern',	JText::_($this->text_prefix.'_EXTERN'));
		return $options;
	}
	
	public function getAdapterTypes(){
		$db 	= JFactory::getDbo();
		$sql 	= "SELECT * FROM `#__extensions` WHERE `type`='plugin' AND folder='obss_intern' AND enabled=1";
		$db->setQuery($sql);
		$adapters = $db->loadObjectList();
		return $adapters;
	}
	
	/**
	 * Get list of connections
	 * @return type
	 */
	public function getConnections( ) {
		if( !$this->connections ) {
			$app	= JFactory::getApplication();
			$db		= JFactory::getDbo();
			$sql	= "SELECT * "
					. " FROM #__obsocialsubmit_instances "
					. " WHERE `addon_type`='extern'"
					. " AND `published`=1";
			$db->setQuery( $sql );
//			$res	= $db->loadObjectList();
			$this->connections = $db->loadObjectList();
			if( $db->getErrorNum() ) {
				$app->enqueueMessage($db->getErrorMsg());
			}
		}
		return $this->connections;
	}
	
	public function publish(&$pks, $value = 1)
	{
		$dispatcher = JDispatcher::getInstance();#JEventDispatcher::getInstance();
		$user = JFactory::getUser();
		$table = JTable::getInstance('extension');
		$pks = (array) $pks;

		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();
			$table->load($pk);
		}

		// Attempt to change the state of the records.
		if (!$table->publish($pks, $value, $user->get('id')))
		{
			$this->setError($table->getError());

			return false;
		}

		$context = $this->option . '.' . $this->name;

		// Trigger the onContentChangeState event.
		$result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
}
