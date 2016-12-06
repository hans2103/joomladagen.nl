<?php
/**
 * @package        obSocialSubmit
 * @author         foobla.com.
 * @copyright      Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL
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
class ObsocialSubmitModelLogs extends JModelList {
	protected $adapters = array();
	protected $connections = array();
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

		$adapter = $this->getUserStateFromRequest( $this->context . '.filter.adapter', 'filter_adapter' );
		$this->setState( 'filter.adapter', $adapter );

		$connection = $this->getUserStateFromRequest( $this->context . '.filter.connection', 'filter_connection' );
		$this->setState( 'filter.connection', $connection );

		$state = $this->getUserStateFromRequest( $this->context . '.filter.status', 'filter_status', '' );
		$this->setState( 'filter.status', $state );

		$processed = $this->getUserStateFromRequest( $this->context . '.filter.processed', 'filter_processed', '' );
		$this->setState( 'filter.processed', $processed );

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

		// Filter by processed state
		$processed = $this->getState( 'filter.processed' );
		if ( is_numeric( $processed ) ) {
			if ( $processed == 1 ) {
				$query->where( 'l.processed >= ' . (int) $processed );
			} else {
				$query->where( 'l.processed = ' . (int) $processed );
			}
		} elseif ( $status === '' ) {
			$query->where( '(l.processed >= 0)' );
		}


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

	public function getConnectionTypes() {
		$db  = JFactory::getDbo();
		$sql = "SELECT * FROM `#__extensions` WHERE `type`='plugin' AND folder='obss_extern' AND enabled=1";
		$db->setQuery( $sql );
		$types = $db->loadObjectList();

		return $types;
	}

	public function getAdapterTypes() {
		$db  = JFactory::getDbo();
		$sql = "SELECT * FROM `#__extensions` WHERE `type`='plugin' AND folder='obss_intern' AND enabled=1";
		$db->setQuery( $sql );
		$types = $db->loadObjectList();

		return $types;
	}

	/**
	 * Change process status
	 */
	public function process( $cid, $value ) {
		$app   = JFactory::getApplication();
		$db    = JFactory::getDbo();
		$where = array();

		foreach ( $cid as $id ) {
			list( $iid, $aid, $cid ) = explode( ',', $id );
			$where[] = "( `iid`={$iid} AND `aid`={$aid} AND `cid`={$cid} )";
		}

		if ( count( $where ) ) {
			$sql = "UPDATE `#__obsocialsubmit_logs`
						SET `processed` = '{$value}'
						WHERE " . implode( ' OR ', $where );
			$db->setQuery( $sql );
			$db->query();
			if ( $db->getErrorNum() ) {
				$app->enqueuemessage( $db->getErrorMsg(), 'error' );

				return false;
			} else {
				return true;
			}
		}
	}

	/**
	 * Change status
	 */
	public function status( $cid, $value ) {
		$app   = JFactory::getApplication();
		$db    = JFactory::getDbo();
		$where = array();

		foreach ( $cid as $id ) {
			list( $iid, $aid, $cid ) = explode( ',', $id );
			$where[] = "( `iid`={$iid} AND `aid`={$aid} AND `cid`={$cid} )";
		}

		if ( count( $where ) ) {
			$sql = "UPDATE `#__obsocialsubmit_logs`
						SET `status` = '{$value}'
						WHERE " . implode( ' OR ', $where );
			$db->setQuery( $sql );
			$db->query();
			if ( $db->getErrorNum() ) {
				$app->enqueuemessage( $db->getErrorMsg(), 'error' );

				return false;
			} else {
				return true;
			}
		}
	}

	public function delete_all(){
		$app   = JFactory::getApplication();
		$db    = JFactory::getDbo();
		$sql = "TRUNCATE `#__obsocialsubmit_logs` ";
		$db->setQuery( $sql );
		$db->query();
		if ( $db->getErrorNum() ) {
			$app->enqueuemessage( $db->getErrorMsg(), 'error' );

			return false;
		} else {
			return true;
		}
	}

	public function trash( $cid ) {
		$app   = JFactory::getApplication();
		$db    = JFactory::getDbo();
		$where = array();

		foreach ( $cid as $id ) {
			list( $iid, $aid, $cid ) = explode( ',', $id );
			$where[] = "( `iid`={$iid} AND `aid`={$aid} AND `cid`={$cid} )";
		}

		if ( count( $where ) ) {
			$sql = "DELETE FROM `#__obsocialsubmit_logs`
						WHERE " . implode( ' OR ', $where );
			$db->setQuery( $sql );
			$db->query();
			if ( $db->getErrorNum() ) {
				$app->enqueuemessage( $db->getErrorMsg(), 'error' );

				return false;
			} else {
				return true;
			}
		}
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
			$addon      = $data->addon;
			$addon_type = $data->addon_type;
			$file_path  = JPATH_SITE . DS . 'plugins' . DS . 'obss_extern' . DS . $addon . DS . $addon . '.php';
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

	function updateLog( $iid, $aid, $cid, $processed = 1, $status = 0 ) {
		$app  = JFactory::getApplication();
		$date = JFactory::getDate();
		$now  = $date->toSql();
		$db   = JFactory::getDbo();
		$sql  = "SELECT `processed` FROM `#__obsocialsubmit_logs` WHERE `iid`=$iid AND `aid`=$aid AND `cid`=$cid";
		$db->setQuery( $sql );
		$current_processed = $db->loadResult();
		if ( $processed != 0 ) {
			$processed = $current_processed + 1;
		}
		$sql = "UPDATE `#__obsocialsubmit_logs` SET `processed`=$processed, `process_time`='{$now}', `status`=$status WHERE `iid`=$iid AND `aid`=$aid AND `cid`=$cid";
		$db->setQuery( $sql );
		$db->query();
		if ( $db->getErrorNum() ) {
			echo '<pre>' . print_r( $db->getErrorMsg(), true ) . '</pre>';
		}
	}

	public function processLogs( $cids ) {
		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();
		$id  = is_array( $cids ) ? $cids[0] : $cids;

		list( $iid, $aid, $cid ) = explode( ',', $id );
		if ( ! $iid || ! $aid || ! $cid ) {
			return false;
		}

		// get adapter
		$adapter = $this->getAdapter( $aid );
		if ( ! $adapter ) {
			$app->enqueuemessage( 'COM_OBSOCIALSUBMIT_LOGS_ADAPTER_NOT_EXIST', 'error' );

			return false;
		}

		// get post object
		if ( ! method_exists( $adapter, 'getPostObjectByItemId' ) && ! method_exists( $adapter, 'getPostObjecByItemId' ) ) {
			$app->enqueuemessage( 'COM_OBSOCIALSUBMIT_LOGS_METHOD_GETPOSTOBJECTBYITEMID_NOT_EXIST', 'error' );

			return false;
		}
		$post_obj = null;
		if ( method_exists( $adapter, 'getPostObjectByItemId' ) ) {
			$post_obj = call_user_func( array( $adapter, 'getPostObjectByItemId' ), $iid );
		}

		if ( method_exists( $adapter, 'getPostObjecByItemId' ) && ! $post_obj ) {
			$post_obj = call_user_func( array( $adapter, 'getPostObjecByItemId' ), $iid );
		}

		if ( ! $post_obj ) {
			$app->enqueuemessage( 'COM_OBSOCIALSUBMIT_LOGS_POST_OBJECT_IS_NULL' );

			return false;
		}

		// get connection
		$connection = $this->getConnection( $cid );
		if ( ! $connection ) {
			$app->enqueuemessage( 'COM_OBSOCIALSUBMIT_LOGS_CONNECTION_NOT_EXIST', 'error' );

			return false;
		}

		// post post object to social network
		if ( ! method_exists( $connection, 'postMessage' ) ) {
			$app->enqueuemessage( 'COM_OBSOCIALSUBMIT_LOGS_CONNECTION_MUST_HAVE_POSTMESSAGE_METHOD', 'error' );

			return false;
		}
		$sql = "SELECT `processed` FROM `#__obsocialsubmit_logs` WHERE `iid`=$iid AND `aid`=$aid AND `cid`=$cid";
		$db->setQuery( $sql );
		$current_processed = $db->loadResult();
		$params            = JComponentHelper::getParams( 'com_obsocialsubmit' );

		if ( is_object( $params ) ) {
			$republished = $params->get( 'republished' );
		} else {
			$republished = json_decode( $params )->republished;
		}
		if ( $current_processed > 0 && $republished == 0 ) {
			$app->enqueuemessage( 'Your system not allow re-published!', 'error' );

			return false;
		} else {
			$res = call_user_func( array( $connection, 'postMessage' ), $post_obj );
		}
		if ( $res ) {
			$this->updateLog( $iid, $aid, $cid, 1, 1 );

			return true;
		} else {
			$this->updateLog( $iid, $aid, $cid, 1, 0 );

			return false;
		}
	}
}
