<?php
/**
 * @package        obSocialSubmit
 * @author         foobla.com.
 * @copyright      Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL
 */
 
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

class ObsocialSubmitModelCpanel extends JModelList {
	protected $adapters_data = null;
	protected $connections_data = null;
	protected $adapters = array();
	protected $connections = array();
	protected $text_prefix = 'COM_OBSOCIALSUBMIT_CPANEL';
	protected $newversion = null;
	protected $version = null;

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
				'aid', 'a.addon',
				'iid', 'c.addon',
				'cid', 'l.iid',
				'filter_adapter_type',
				'filter_connection_type',
				'processed',
				'publish_up',
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
		$adapter_type = $this->getUserStateFromRequest( $this->context . '.filter.adapter_type', 'filter_adapter_type' );
		$this->setState( 'filter.adapter_type', $adapter_type );

		$connection_type = $this->getUserStateFromRequest( $this->context . '.filter.connection_type', 'filter_connection_type' );
		$this->setState( 'filter.connection_type', $connection_type );

		$state = $this->getUserStateFromRequest( $this->context . '.filter.status', 'filter_status', '' );
		$this->setState( 'filter.status', $state );

		// Load the parameters.
		$params = JComponentHelper::getParams( 'com_obsocialsubmit' );
		$this->setState( 'params', $params );

		// List state information.
		parent::populateState( 'l.publish_up', 'asc' );
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
		$id .= ':' . $this->getState( 'filter.adapter' );
		$id .= ':' . $this->getState( 'filter.adapter_type' );
		$id .= ':' . $this->getState( 'filter.connection' );
		$id .= ':' . $this->getState( 'filter.connection_type' );
		$id .= ':' . $this->getState( 'filter.status' );
		$id .= ':' . $this->getState( 'filter.processed' );

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
				'l.iid, l.aid, a.title AS `atitle`,'
				. ' a.addon AS adapter_type, l.cid, c.title AS `ctitle`,'
				. ' c.addon AS connection_type, l.publish_up, l.processed, l.process_time, l.status' )
		);
		$query->from( $db->quoteName( '#__obsocialsubmit_logs' ) . ' AS l' );
		$query->join( 'left', '`#__obsocialsubmit_instances` AS `a` ON `l`.`aid` = `a`.`id` and `a`.`addon_type` = "intern"' );
		$query->join( 'left', '`#__obsocialsubmit_instances` AS `c` ON `l`.`cid` = `c`.`id` and `c`.`addon_type` = "extern"' );

		// Filter by published state
		$status = $this->getState( 'filter.status' );
		if ( is_numeric( $status ) ) {
			$query->where( 'l.status = ' . (int) $status );
		} elseif ( $status === '' ) {
			$query->where( '(l.status IN (0, 1))' );
		}

		// Filter by published state
		$filter_adapter_type = trim( $this->getState( 'filter.adapter_type' ) );
		if ( $filter_adapter_type ) {
			$query->where( 'a.addon = "' . $filter_adapter_type . '"' );
		}

		$filter_connection_type = trim( $this->getState( 'filter.connection_type' ) );
		if ( $filter_connection_type ) {
			$query->where( 'c.addon = "' . $filter_connection_type . '"' );
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get( 'list.ordering', 'l.publish_up' );
		$orderDirn = $this->state->get( 'list.direction', 'ASC' );

		$query->order( $db->escape( $orderCol . ' ' . $orderDirn ) );
		// echo '<pre>'.print_r($query->__toString(), true).'</pre>';
		// exit();
		return $query;
	}

	public function getExternPlugins() {
		$db  = JFactory::getDbo();
		$sql = "SELECT * "
			. "FROM `#__extensions` "
			. "WHERE `type`='plugin' "
			. "AND `folder`='obss_extern' "
//					. "AND `enabled`=1 "
			. "AND `state` = 0";
		$db->setQuery( $sql );
		$types = $db->loadObjectList();

		return $types;
	}

	public function getInternPlugins() {
		$db  = JFactory::getDbo();
		$sql = "SELECT * "
			. "FROM `#__extensions` "
			. "WHERE `type`='plugin' "
			. "AND folder='obss_intern' "
//					. "AND enabled=1 "
			. "AND `state` = 0";
		$db->setQuery( $sql );
		$types = $db->loadObjectList();

		return $types;
	}

	public function getAdapter( $aid ) {
		if ( key_exists( $aid, $this->adapters ) ) {
			$adapter = $this->adapters[$aid];
		} else {
			$db  = JFactory::getDbo();
			$sql = "SELECT * FROM `#__obsocialsubmit_instances` WHERE id='{$aid}' AND `published`=1 AND `addon_type`='intern'";
			$db->setQuery( $sql );
			$data = $db->loadObject();
			if ( ! $data ) {
				return null;
			}
			$addon      = $data->addon;
			$addon_type = $data->addon_type;
			$file_path  = JPATH_SITE . DS . 'plugins' . DS . 'obss_intern' . DS . $addon . DS . $addon . '.php';
			if ( ! JFile::exists( $file_path ) ) {
				return null;
			}
			require_once $file_path;
			$classname            = 'OBSSInAddon' . ucfirst( $addon );
			$adapter              = new $classname( $data );
			$this->adapters[$aid] = $adapter;
		}

		return $this->adapters[$aid];
	}

	public function getConnection( $cid ) {
		if ( ! key_exists( $cid, $this->connections ) ) {
			$db  = JFactory::getDbo();
			$sql = "SELECT * FROM #__obsocialsubmit_instances WHERE `id`=$cid AND `published`=1 AND `addon_type`='extern'";
			$db->setQuery( $sql );
			$data = $db->loadObject();
			if ( ! $data ) {
				return null;
			}
			$addon     = $data->addon;
			$file_path = JPATH_SITE . DS . 'plugins' . DS . 'obss_extern' . DS . $addon . DS . $addon . '.php';
			if ( ! JFile::exists( $file_path ) ) {
				return null;
			}
			require_once $file_path;
			$classname               = 'OBSSExAddon' . ucfirst( $addon );
			$connection              = new $classname( $data );
			$this->connections[$cid] = $connection;
		}

		return $this->connections[$cid];
	}

	/**
	 * Get list of connections
	 * @return type
	 */
	public function getConnections() {
		if ( ! $this->connections_data ) {
			$app = JFactory::getApplication();
			$db  = JFactory::getDbo();
			$sql = "SELECT * FROM #__obsocialsubmit_instances WHERE `addon_type`='extern'";
			$db->setQuery( $sql );
//			$res	= $db->loadObjectList();
			$this->connections_data = $db->loadObjectList();
			if ( $db->getErrorNum() ) {
				$app->enqueueMessage( $db->getErrorMsg() );
			}
		}

		return $this->connections_data;
	}

	/**
	 * Get list of adapters
	 * @return type
	 */
	public function getAdapters() {
		if ( ! $this->adapters_data ) {
			$app = JFactory::getApplication();
			$db  = JFactory::getDbo();
			$sql = "SELECT * FROM #__obsocialsubmit_instances WHERE `published`=1 AND `addon_type`='intern'";
			$db->setQuery( $sql );
			$this->adapters_data = $db->loadObjectList();
			if ( $db->getErrorNum() ) {
				$app->enqueueMessage( $db->getErrorMsg() );
			}
		}

		return $this->adapters_data;
	}

	public function getManifestCache() {
		$db  = JFactory::getDbo();
		$sql = "SELECT
						e.extension_id, e.name, e.type, e.element, e.manifest_cache
					FROM
						#__extensions as e
					WHERE
						`type`='component' and element='com_obsocialsubmit'";
		$db->setQuery( $sql );
		$obj      = $db->loadObject();
		$manifest = json_decode( $obj->manifest_cache );

		return $manifest;
	}

	public function postmsg( $msg, $cids ) {
		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();

//		if( !$post_obj ) {
//			$app->enqueuemessage('COM_OBSOCIALSUBMIT_LOGS_POST_OBJECT_IS_NULL');
//			return false;
//		}

		// get post object
		$post_obj           = new stdClass();
		$post_obj->message  = $msg;
		$post_obj->title    = '';
		$post_obj->url      = '';
		$post_obj->shorturl = '';
		$post_obj->template = '';

		$return = array();
		// get connection

		foreach ( $cids as $cid ) {
			$connection = $this->getConnection( $cid );
			if ( ! $connection ) {
				$return['false'][] = JText::_( 'COM_OBSOCIALSUBMIT_LOGS_CONNECTION_NOT_EXIST' );
				$app->enqueuemessage( 'COM_OBSOCIALSUBMIT_LOGS_CONNECTION_NOT_EXIST', 'error' );
				continue;
			}

			// post post object to social network
			if ( ! method_exists( $connection, 'postMessage' ) ) {
				$return['false'][] = JText::_( 'COM_OBSOCIALSUBMIT_LOGS_CONNECTION_MUST_HAVE_POSTMESSAGE_METHOD' );
				$app->enqueuemessage( 'COM_OBSOCIALSUBMIT_LOGS_CONNECTION_MUST_HAVE_POSTMESSAGE_METHOD', 'error' );
				continue;
			}

			$res = call_user_func( array( $connection, 'postMessage' ), $post_obj );
			if ( $res ) {
				$return['true'][] = JText::sprintf( 'COM_OBSOCIALSUBMIT_CPANEL_POST_MESSAGE_SUCCESS_MSG', $connection->data->title );
				$this->updateLog( 0, 0, $cid, 1, 1 );
			} else {
				$return['false'][] = JText::sprintf( 'COM_OBSOCIALSUBMIT_CPANEL_POST_MESSAGE_FALSE_MSG', $connection->data->title );
				$this->updateLog( 0, 0, $cid, 1, 0 );
			}
		}

		return $return;
	}


	function updateLog( $iid, $aid, $cid, $processed = 1, $status = 0 ) {
		$app  = JFactory::getApplication();
		$date = JFactory::getDate();
		$now  = $date->toSql();
		$db   = JFactory::getDbo();
		$sql  = "UPDATE `#__obsocialsubmit_logs` SET `processed`=$processed, `process_time`='{$now}', `status`=$status WHERE `iid`=$iid AND `aid`=$aid AND `cid`=$cid";
		$db->setQuery( $sql );
		$db->query();
		if ( $db->getErrorNum() ) {
			echo '<pre>' . print_r( $db->getErrorMsg(), true ) . '</pre>';
		}
	}

	function update_config( $params ) {
		$db    = JFactory::getDBO();
		$query = $db->getQuery( true );

// Build the query
		$query->update( '#__extensions AS a' );
		$query->set( 'a.params = ' . $db->quote( (string) $params ) );
		$query->where( 'a.element = "com_obsocialsubmit"' );

// Execute the query
		$db->setQuery( $query );
		$db->query();
		if ( $db->getErrorNum() ) {
			return '<pre>' . print_r( $db->getErrorMsg(), true ) . '</pre>';
		}

		return '';
	}
}