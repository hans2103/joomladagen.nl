<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined( '_JEXEC' ) or die;
jimport( 'joomla.application.component.modellist' );

/**
 * obSocialSubmit Component Adapter Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.5
 */
class ObsocialSubmitModelAdapters extends JModelList {
	protected $text_prefix = 'COM_OBSOCIALSUBMIT_ADAPTER';
	protected $connections = null;

	/**
	 * Constructor.
	 *
	 * @param    array    An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct( $config = array() ) {
		if ( empty( $config['filter_fields'] ) ) {
			$config['filter_fields'] = array(
				'id', 'i.id',
				'addon', 'i.addon',
				'title', 'i.title',
				'checked_out', 'i.checked_out',
				'checked_out_time', 'i.checked_out_time',
				'published', 'i.published',
				'access', 'i.access',
				'ordering', 'i.ordering',
			);
		}

		parent::__construct( $config );
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since    1.6
	 */
	protected function populateState( $ordering = null, $direction = null ) {
		$app = JFactory::getApplication( 'administrator' );
		// Load the filter state.
		$search = $this->getUserStateFromRequest( $this->context . '.filter.search', 'filter_search' );
		$this->setState( 'filter.search', $search );

		$state = $this->getUserStateFromRequest( $this->context . '.filter.state', 'filter_state', '', 'string' );
		$this->setState( 'filter.state', $state );

		$adapter = $this->getUserStateFromRequest( $this->context . '.filter.adapter', 'filter_adapter', '', 'string' );
		$this->setState( 'filter.adapter', $adapter );


		// Load the parameters.
		$params = JComponentHelper::getParams( 'com_obsocialsubmit' );
		$this->setState( 'params', $params );

		// List state information.
		parent::populateState( 'i.ordering', 'asc' );
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param    string    A prefix for the store id.
	 *
	 * @return    string    A store id.
	 */
	protected function getStoreId( $id = '' ) {
		// Compile the store id.
		$id .= ':' . $this->getState( 'filter.search' );
		$id .= ':' . $this->getState( 'filter.access' );
		$id .= ':' . $this->getState( 'filter.state' );

		return parent::getStoreId( $id );
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 */
	protected function getListQuery() {
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery( true );

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'i.id, i.addon, i.addon_type, i.title, i.description, i.cids,' .
				'i.created, i.modified,' .
				'i.params, i.published,  i.ordering, i.checked_out, i.checked_out_time, i.debug' )
		);
		$query->from( $db->quoteName( '#__obsocialsubmit_instances' ) . ' AS i' );

		$query->where( 'i.addon_type ="intern"' );

		// Filter by published state
		$state = $this->getState( 'filter.state' );
		if ( is_numeric( $state ) ) {
			$query->where( 'i.published = ' . (int) $state );
		} elseif ( $state === '' ) {
			$query->where( '(i.published IN (0, 1))' );
		}

		// Filter by search in title
		$search = $this->getState( 'filter.search' );
		if ( ! empty( $search ) ) {
			if ( stripos( $search, 'id:' ) === 0 ) {
				$query->where( 'i.id = ' . (int) substr( $search, 3 ) );
			} else {
				$search = $db->Quote( '%' . $db->escape( $search, true ) . '%' );
				$query->where( '(' . 'i.title LIKE ' . $search . ' OR i.description LIKE ' . $search . ')' );
			}
		}

		// Filter by published state
		$filter_adapter = trim( $this->getState( 'filter.adapter' ) );
		if ( $filter_adapter ) {
			$query->where( 'i.addon = "' . $filter_adapter . '"' );
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get( 'list.ordering', 'i.ordering' );
		$orderDirn = $this->state->get( 'list.direction', 'ASC' );
		$query->order( "i.published DESC" );

		$query->order( $db->escape( $orderCol . ' ' . $orderDirn ) );

		return $query;
	}

	public function getAdapterTypes() {
		$db  = JFactory::getDbo();
		$sql = "SELECT * FROM `#__extensions` WHERE `type`='plugin' AND folder='obss_intern' AND enabled=1";
		$db->setQuery( $sql );
		$adapters = $db->loadObjectList();

		return $adapters;
	}

	/**
	 * Get list of connections
	 * @return type
	 */
	public function getConnections() {
		if ( ! $this->connections ) {
			$app = JFactory::getApplication();
			$db  = JFactory::getDbo();
			$sql = "SELECT * "
				. " FROM #__obsocialsubmit_instances "
				. " WHERE `addon_type`='extern'"
				. " AND `published`=1";
			$db->setQuery( $sql );
//			$res	= $db->loadObjectList();
			$this->connections = $db->loadObjectList( 'id' );
			if ( $db->getErrorNum() ) {
				$app->enqueueMessage( $db->getErrorMsg() );
			}
		}

		return $this->connections;
	}

	/** Get adapters
	 */
	public function getAdapters() {
		// Get the list of items from the database.
		$items  = $this->getListQueryAdapters();
		$client = JApplicationHelper::getClientInfo( 0 );
		$lang   = JFactory::getLanguage();
		// Loop through the results to add the XML metadata,
		// and load language support.
		foreach ( $items as &$item ) {
			$path = JPath::clean( $client->path . '/plugins/' . $item->folder . '/' . $item->element . '/' . $item->element . '.xml' );
			if ( file_exists( $path ) ) {
				$item->xml = simplexml_load_file( $path );
			} else {
				$item->xml = null;
			}
			// 1.5 Format; Core files or language packs then
			// 1.6 3PD Extension Support
			$lang->load( 'plg_' . $item->folder . '_' . $item->element, JPATH_ADMINISTRATOR, null, false, false )
			|| $lang->load( 'plg_' . $item->folder . '_' . $item->element, JPATH_SITE . '/plugins/' . $item->folder . '/' . $item->element, null, false, false )
			|| $lang->load( 'plg_' . $item->folder . '_' . $item->element, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false )
			|| $lang->load( 'plg_' . $item->folder . '_' . $item->element, JPATH_SITE . '/plugins/' . $item->folder . '/' . $item->element, $lang->getDefault(), false, false );
			$item->name = JText::_( $item->name );

			if ( isset( $item->xml ) && $text = trim( $item->xml->description ) ) {
				$item->desc = JText::_( $text );
			} else {
				$item->desc = JText::_( 'COM_OBSOCIALSUBMIT_NODESCRIPTION' );
			}
		}
		$items = JArrayHelper::sortObjects( $items, 'name', 1, true, $lang->getLocale() );


		return $items;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 */
	protected function getListQueryAdapters() {
		// Create a new query object.
		$db     = $this->getDbo();
		$query  = $db->getQuery( true );
		$type   = JRequest::getVar( 'type' );
		$folder = 'obss_intern';

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', '*'
			)
		);
		$query->from( $db->quoteName( '#__extensions' ) . ' AS a' );

		// Filter by module
		$query->where( '`type` = ' . $db->Quote( 'plugin' ) );
		$query->where( '`folder` = ' . $db->Quote( $folder ) );
		// Filter by enabled
		$query->where( '`enabled` = 1' );
		$query->where( '`state` = 0' );
		// Add the list ordering clause.		

		//echo nl2br(str_replace('#__','jos_',$query));
		$db->setQuery( $query );

		return $db->loadObjectList();
	}

	public function getConections() {
		// Get the list of items from the database.
		$items  = $this->getListQueryConections();
		$client = JApplicationHelper::getClientInfo( 0 );
		$lang   = JFactory::getLanguage();
		// Loop through the results to add the XML metadata,
		// and load language support.
		foreach ( $items as &$item ) {
			$path = JPath::clean( $client->path . '/plugins/' . $item->folder . '/' . $item->element . '/' . $item->element . '.xml' );
			if ( file_exists( $path ) ) {
				$item->xml = simplexml_load_file( $path );
			} else {
				$item->xml = null;
			}
			// 1.5 Format; Core files or language packs then
			// 1.6 3PD Extension Support
			$lang->load( 'plg_' . $item->folder . '_' . $item->element, JPATH_ADMINISTRATOR, null, false, false )
			|| $lang->load( 'plg_' . $item->folder . '_' . $item->element, JPATH_SITE . '/plugins/' . $item->folder . '/' . $item->element, null, false, false )
			|| $lang->load( 'plg_' . $item->folder . '_' . $item->element, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false )
			|| $lang->load( 'plg_' . $item->folder . '_' . $item->element, JPATH_SITE . '/plugins/' . $item->folder . '/' . $item->element, $lang->getDefault(), false, false );
			$item->name = JText::_( $item->name );

			if ( isset( $item->xml ) && $text = trim( $item->xml->description ) ) {
				$item->desc = JText::_( $text );
			} else {
				$item->desc = JText::_( 'COM_OBSOCIALSUBMIT_NODESCRIPTION' );
			}
		}
		$items = JArrayHelper::sortObjects( $items, 'name', 1, true, $lang->getLocale() );

		return $items;
	}

	public function getListQueryConections() {
		// Create a new query object.
		$db     = $this->getDbo();
		$query  = $db->getQuery( true );
		$type   = JRequest::getVar( 'type' );
		$folder = 'obss_extern';

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', '*'
			)
		);
		$query->from( $db->quoteName( '#__extensions' ) . ' AS a' );

		// Filter by module
		$query->where( '`type` = ' . $db->Quote( 'plugin' ) );
		$query->where( '`folder` = ' . $db->Quote( $folder ) );
		// Filter by enabled
		$query->where( '`enabled` = 1' );
		$query->where( '`state` = 0' );
		// Add the list ordering clause.

		//echo nl2br(str_replace('#__','jos_',$query));
		$db->setQuery( $query );

		return $db->loadObjectList();
	}

}