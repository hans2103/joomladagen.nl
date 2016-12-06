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
 * Modules Component Module Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.5
 */
class ObsocialSubmitModelConnections extends JModelList {
	protected $text_prefix = 'COM_OBSOCIALSUBMIT_CONNECTION';

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

		$accessId = $this->getUserStateFromRequest( $this->context . '.filter.access', 'filter_access', null, 'int' );
		$this->setState( 'filter.access', $accessId );

		$state = $this->getUserStateFromRequest( $this->context . '.filter.state', 'filter_state', '', 'string' );
		$this->setState( 'filter.state', $state );

		$connection = $this->getUserStateFromRequest( $this->context . '.filter.connection', 'filter_connection', '', 'string' );
		$this->setState( 'filter.connection', $connection );


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
		$id .= ':' . $this->getState( 'filter.connection' );
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
				'i.params, i.published,  i.ordering, i.checked_out, i.checked_out_time' )
		);
		$query->from( $db->quoteName( '#__obsocialsubmit_instances' ) . ' AS i' );

		$query->where( 'i.addon_type ="extern"' );

		// Filter by published state
		$state = $this->getState( 'filter.state' );
		if ( is_numeric( $state ) ) {
			$query->where( 'i.published = ' . (int) $state );
		} elseif ( $state === '' ) {
			$query->where( '(i.published IN (0, 1))' );
		}

		// Filter by published state
		$filter_connection = trim( $this->getState( 'filter.connection' ) );
		if ( $filter_connection ) {
			$query->where( 'i.addon = "' . $filter_connection . '"' );
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get( 'list.ordering', 'i.ordering' );
		$orderDirn = $this->state->get( 'list.direction', 'ASC' );

		$query->order( $db->escape( $orderCol . ' ' . $orderDirn ) );

		return $query;
	}

	protected function duplicate() {

	}

	public function getConnectionTypes() {
		$db  = JFactory::getDbo();
		$sql = "SELECT * FROM `#__extensions` WHERE `type`='plugin' AND folder='obss_extern' AND enabled=1";
		$db->setQuery( $sql );
		$adapters = $db->loadObjectList();

		return $adapters;
	}

	/** Get Conections
	 */
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

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return    JDatabaseQuery
	 */
	protected function getListQueryConections() {
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