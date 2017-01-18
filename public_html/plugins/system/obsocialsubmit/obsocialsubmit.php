<?php
/**
 * @version          $Id: obsocialsubmit.php 712 2011-06-14 10:39:46Z phonglq $
 * @author           foobla.com
 * @package          foobla Social Submit
 * @copyright    (C) 2010 foobla.com. All rights reserved.
 * @license          GNU/GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
defined( 'DS' ) or define( 'DS', DIRECTORY_SEPARATOR );

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.html.parameter' );
jimport( 'joomla.filesystem.file' );

$filename = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obsocialsubmit' . DS . 'helpers' . DS . 'class.internaddon.php';
if ( is_file( $filename ) ) {
	require_once $filename;
}


class plgSystemObSocialSubmit extends JPlugin {
	public $_externs = array();
	public $_adapter = null;

	public function __construct(&$subject)
	{
		parent::__construct( $subject );
	}

//	function plgSystemObSocialSubmit( &$subject ) {
//		parent::__construct( $subject );
//	}

	public function onCronJob() {
		#TODO: get config cronjob config of obSocialSubmit
		$obsstask = JRequest::getVar( 'obsstask' );
		if ( $obsstask != 'cronjob' ) {
			return;
		}

		if ( ! ini_get( 'display_errors' ) ) {
			ini_set( 'display_errors', 1 );
		}

		$obss_config = JComponentHelper::getParams( 'com_obsocialsubmit' );
		$cronjob     = $obss_config->get( 'cronjob', 0 );
		if ( ! $cronjob ) {
			echo "Cronjob Is Disabled";
			exit();
		}
		if ( $obss_config->get( 'allow_ips' ) != '' ) {
			$list_allow_ips = explode( ',', $obss_config->get( 'allow_ips' ) );
			$user_ip        = $this->getRealIpAddr();
			if ( ! in_array( $user_ip, $list_allow_ips ) ) {
				echo "Your IP not allowed";
				exit();
			}
		}

		#TODO: clear logs
		echo '<h1>' . JText::_( 'OBSS_CLEAR_LOGS' ) . '</h1>';
		$num = $this->clearLog();
		echo JText::_( 'OBSS_NUMBER_OF_LOGS_REMOVED_IS' ) . ' ' . $num;
		#TODO: process queues
		echo '<h1>' . JText::_( 'OBSS_PROCESS_QUEUES' ) . '</h1>';
		$cronitems = $obss_config->get( 'cronitems', 1 );
		$db        = JFactory::getDbo();
		$date      = JFactory::getDate();
		$now       = $date->toSql();
		$adas      = array();
		$cons      = array();
		$post_objs = array();

		$sql = "SELECT * FROM `#__obsocialsubmit_instances` WHERE `published`=1 AND `addon` = 'feed'";
		$db->setQuery( $sql );
		$feed_streams = $db->loadObjectList();
		if(count($feed_streams)>0){
			foreach($feed_streams as $feed_str){
				if($feed_str->cids == ''){
					continue;
				}
				$feed_cids = explode(",",$feed_str->cids);

				$feed_adapter = $this->getAdapter($feed_str->id);
				if ( method_exists( $feed_adapter, 'onCronJob' ) ) {
					$post_feed_obj = call_user_func( array( $feed_adapter, 'onCronJob' ) );
					foreach($feed_cids as $f_cid){
						$connection = $this->getConnection( $f_cid );
						if ( ! $connection ) {
							continue;
						}
						if ( method_exists( $connection, 'postMessage' ) ) {
							foreach($post_feed_obj as $feed_obj) {
								call_user_func( array( $connection, 'postMessage' ), $feed_obj );
							}
						}
					}
				}
			}
		}

		$sql = "SELECT * FROM `#__obsocialsubmit_logs` WHERE `processed`=0 AND ( `publish_up`<'{$now}' OR `publish_up` IS NULL ) ORDER BY `publish_up` ASC, `id` ASC  LIMIT " . $cronitems;
		$db->setQuery( $sql );
		$logs = $db->loadObjectList();
		if ( ! $logs ) {
			if ( $db->getErrorNum() ) {
				print_r( $db->getErrorMsg() );
			} else {
				echo '<h1>Empty Logs</h1>';
			}
			exit();
		}

		if ( is_object( $obss_config ) ) {
			$republished = $obss_config->get( 'republished' );
		} else {
			$republished = json_decode( $obss_config )->republished;
		}
		foreach ( $logs as $log ) {
			if ( $log->processed > 0 && $republished == 0 ) {
				continue;
			}
			echo '<hr>';
			#TODO: get apdater
			if ( key_exists( $log->aid, $adas ) ) {
				$adapter = $adas[$log->aid];
			} else {
				$adapter = $this->getAdapter( $log->aid );
				if ( ! $adapter ) {
					$this->changeLogX( $log->iid, $log->aid, $log->cid, 1, 0 );
					echo 'not adapter';
					continue;
				}
				$adas[$log->aid] = $adapter;
			}

			#TODO: get post object
			if ( ! method_exists( $adapter, 'getPostObjecByItemId' ) && ! method_exists( $adapter, 'getPostObjectByItemId' ) ) {
				$this->changeLogX( $log->iid, $log->aid, $log->cid, 1, 0 );
				$classname = get_class( $adapter );
				echo 'Class ' . $classname . ' not exists method getPostObjectByItemId<br/>';
				continue;
			}

			$post_obj = null;
			if ( key_exists( $log->aid . '' . $log->iid, $post_objs ) && $post_objs[$log->aid . '' . $log->iid] ) {
				$post_obj = $post_objs[$log->aid . '' . $log->iid];
			} else {
				if ( method_exists( $adapter, 'getPostObjecByItemId' ) ) {
					$post_obj = call_user_func( array( $adapter, 'getPostObjecByItemId' ), $log->iid );
				} else {
					$post_obj = call_user_func( array( $adapter, 'getPostObjectByItemId' ), $log->iid );
				}

				if ( $post_obj ) {
					$post_objs[$log->aid . '' . $log->iid] = $post_obj;
				}
			}

			#TODO: get connextion
			if ( key_exists( $log->cid, $cons ) ) {
				$connection = $cons[$log->cid];
			} else {
				$connection = $this->getConnection( $log->cid );
				if ( ! $connection ) {
					$this->changeLogX( $log->iid, $log->aid, $log->cid, 1, 0 );
					echo 'connection not exists';
					continue;
				}
				$cons[$log->cid] = $connection;
			}

			if ( ! method_exists( $connection, 'postMessage' ) ) {
				$this->changeLogX( $log->iid, $log->aid, $log->cid, 1, 0 );
				echo 'connection not exists method postMessage';
				continue;
			}

			#TODO: Post pos object to social network
			$res = call_user_func( array( $connection, 'postMessage' ), $post_obj );

			if ( $res ) {
				$this->changeLogX( $log->iid, $log->aid, $log->cid, 1, 1 );
				echo 'true';
			} else {
				$this->changeLogX( $log->iid, $log->aid, $log->cid, 1, 0 );
				echo 'false';
			}
			#TODO: Change Log
		}
		exit( '<h2>End Of Cronjob</h2>' );
		jexit( '' . __LINE__ );

	}

	public function postmsg() {
		$post = JRequest::get( 'post' );
		$cids = JRequest::getVar( 'cids', array(), 'post' );
		$msg  = isset( $post['msg'] ) ? $post['msg'] : '';
		if ( ! $msg ) {
			jexit();
		}

		if ( empty( $cids ) ) {
			JLog::add( JText::_( $this->text_prefix . '_NO_ITEM_SELECTED' ), JLog::WARNING, 'jerror' );
		} else {
			$return = $this->do_postmsg( $msg, $cids );
			if ( isset( $return['false'] ) && count( $return['false'] ) ) {
				?>
				<div class="alert alert-danger">
					<?php echo implode( '<br/>', $return['false'] ); ?>
				</div>
			<?php
			}
			if ( isset( $return['true'] ) && count( $return['true'] ) ) {
				?>
				<div class="alert alert-success">
					<?php echo implode( '<br/>', $return['true'] ); ?>
				</div>
			<?php
			}

		}
		jexit();
	}

	public function do_postmsg( $msg, $cids ) {
		$app  = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$lang->load( "mod_obssdemo", JPATH_SITE );
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

	public function updateLog( $iid, $aid, $cid, $processed = 1, $status = 0 ) {
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

	public function onAfterInitialise() {

		/*$app           = JFactory::getApplication();
		$view          = JRequest::getVar( 'view' );*/
		$option       = JRequest::getVar( 'option' );
		$filters_type = JRequest::getVar( 'filters_type' );
		//$filters_group = JRequest::getVar( 'filters_group' );
		if ( $option == 'com_obsocialsubmit' && $filters_type == 'module' ) {
			$this->postmsg();
		}

		# run get sef
		$this->obssTaskGetSef();
		$mainframe = JFactory::getApplication();
		$user      = JFactory::getUser();
		if ( $mainframe->isAdmin() && ! $user->guest ) {
			$this->addonFunc();
			$this->obssDebug();
		}

		$params  = JComponentHelper::getParams( 'com_obsocialsubmit' );
		$cronjob = $params->get( 'cronjob' );

		if ( $cronjob ) {
			$this->addJsCronJobScript();
			$this->onCronJob();
		}

		$this->execIntern( __FUNCTION__ );

		return true;
	}

	public function getRealIpAddr() {
		if ( isset( $_GET['x12'] ) ) {
			echo "\n\n<br /><i><b>File:</b>" . __FILE__ . ' <b>Line:</b>' . __LINE__ . "</i><br />\n\n"; //exit();
			echo '<pre>$_SERVER:';
			print_r( $_SERVER );
			echo '</pre>';
			exit();
		}
		global $ogb_ip;
		$a = explode( '.', $ogb_ip );
		if ( isset( $a[1] ) ) {
			return $ogb_ip;
		}
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			// check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			//to check ip is pass from proxy
			$ip = $_SERVER['REMOTE_ADDR'];
		} else {
			$ip = '0.0.0.0';
		}
		$ip     = preg_replace( '/,.*/i', '', $ip ); //only get the first ip address
		$ogb_ip = $ip;

		return $ip;
	}


	public function onAfterRoute() {
		$params = JComponentHelper::getParams( 'com_obsocialsubmit' );
		//$cronjob = $params->get( 'cronjob' );
		$this->execIntern( __FUNCTION__ );

		return true;
	}


	function onBeforeDisplayContent( &$article, &$params, $limitstart ) {
		$args = func_get_args();
		$this->execIntern( __FUNCTION__, $args );
	}


	function onAfterContentSave( &$article, $isNew ) {
		$args = func_get_args();
		$this->execIntern( __FUNCTION__, $args );

		return true;
	}


	function onContentAfterSave( $context, $article, $isNew ) {

		$args = func_get_args();

		$this->execIntern( __FUNCTION__, $args );

		return true;

	}


	function onAfterDisplayContent( &$article, &$params, $limitstart = 0 ) {

		$args = func_get_args();

		$this->execIntern( __FUNCTION__, $args );

	}


	function onContentAfterDisplay( $context, &$article, &$params, $limitstart = 0 ) {

		$args = func_get_args();

		$this->execIntern( __FUNCTION__, $args );

	}


	function execIntern( $event = 'onAfterInitialise', $array = null, $option = '' ) {

		$mainframe = JFactory::getApplication();

		# - - - - - - - - - - - - - - - - - - - - - - - - -

		# TODO: get information of adapters have type = addon_type

		# - - - - - - - - - - - - - - - - - - - - - - - - -

		if ( ! $option ) {

			$option = JRequest::getVar( 'option' );

		}

		if ( ! $option ) {

			return;

		}

		$addon = substr( $option, strlen( 'com_' ) );

		$event = trim( $event );


		$db = JFactory::getDBO();

		$sql = "SELECT * FROM `#__obsocialsubmit_instances` "

			. "WHERE `addon_type`='intern' and `addon`='$addon' and `published`=1";

		$db->setQuery( $sql );

		$adapter_datas = $db->loadObjectList();


		if ( empty( $adapter_datas ) && $event == 'onAfterK2Save' ) {

			$addon = 'k2';

			$sql = "SELECT * FROM `#__obsocialsubmit_instances` "

				. "WHERE `addon_type`='intern' and `addon`='$addon' and `published`=1";

			$db->setQuery( $sql );

			$adapter_datas = $db->loadObjectList();

		}

//		if( !$adapter_datas && $addon != 'content' && ($event == 'onAfterContentSave'||$event == 'onContentAfterSave' )) {

		if ( ! $adapter_datas && $addon != 'content' ) {

			$addon = 'content';

			$sql = "SELECT * FROM `#__obsocialsubmit_instances` "

				. "WHERE `addon_type`='intern' and `addon`='$addon' and `published`=1";

			$db->setQuery( $sql );

			$adapter_datas = $db->loadObjectList();

		}


		if ( ! $adapter_datas ) {
			return;
		}

		# END: get information of adapters have type = addon_type


		# - - - - - - - - - - - - - - - - - - - - - - - - -

		# TODO: include addon file

		# - - - - - - - - - - - - - - - - - - - - - - - - -

		$classname = 'OBSSInAddon' . ucfirst( $addon );


		if ( ! class_exists( $classname ) ) {

			$addon_path = JPath::clean( JPATH_SITE . DS . 'plugins' . DS . 'obss_intern' . DS . $addon . DS . $addon . '.php' );

			if ( JFile::exists( $addon_path ) ) {

				require_once $addon_path;

			} else {

				$mainframe->enqueueMessage( "File " . $addon_path . " not exit!" );

			}

		}


		if ( ! class_exists( $classname ) ) {

			$mainframe->enqueueMessage( "Class " . $classname . " not exit!" );

			return;

		}

		# END: include addon file
		if ( $adapter_datas ) {
			$adapter  = new $classname();
			$messages = array();
			foreach ( $adapter_datas as $adapter_data ) {
				$adapter->config = null;
				$adapter->data   = $adapter_data;
				if ( method_exists( $adapter, $event ) ) {
					$message = null;
					if ( $array ) {
						$message = @call_user_func_array( array( $adapter, $event ), $array );
					} else {
						$message = @call_user_func( array( $adapter, $event ) );
					}
					if ( $message && is_object( $message ) ) {
						if ( ! is_array( $message ) ) {
							$message = array( $message );
						}
						for ( $i = 0; $i < count( $message ); $i ++ ) {
							$message[$i]->cids = $adapter_data->cids;
							if ( ! isset( $message[$i]->aid ) && ! $message[$i]->aid ) {
								$message[$i]->aid = $adapter_data->id;
							}
							$messages[] = $message[$i];
						}
					}
				}
			}

			if ( count( $messages ) ) {
				$this->postMessage( $messages );
			}
		}

		return;
	}


	function postMessage( $messages, $notice = true ) {
		$successCounter = 0;
		$obsstask       = JRequest::getVar( 'obsstask' );
		$obssdebug      = JRequest::getVar( 'obssdebug' );
		$obIsCronJob    = ( $obsstask == 'cronjob' );
		$mainframe      = JFactory::getApplication();

		if ( ! $messages ) {
			if ( $obsstask == 'cronjob' && $obssdebug ) {
				echo '<pre>' . print_r( $messages, true ) . '</pre>';
				exit();
			}

			return $successCounter;
		}

		if ( ! is_array( $messages ) ) {
			$messages = array( $messages );
		}

		$db      = JFactory::getDbo();
		$externs = $this->loadExternAddons();
		$keys    = array_keys( $externs );

		foreach ( $keys as $key ) {
			$extern = $externs[$key];
			if ( ! is_object( $extern ) ) {
				continue;
			}

			$externname = substr( get_class( $extern ), strlen( 'OBSSExAddon' ) );
			$conname    = $extern->data->title;
			$conlink    = JURI::base() . 'index.php?option=com_obsocialsubmit&task=connection.edit&id=' . $extern->data->id;
			$t          = $c = $f = 0;

			foreach ( $messages as $pobj ) {
				#XXX Check connections
				$cids = explode( ',', trim( $pobj->cids, "," ) );
				if ( ! in_array( $key, $cids ) ) {
					continue;
				}
				#END Check connections

				#XXX Check log
				$exists_aid = property_exists( $pobj, 'aid' );
				$exists_iid = property_exists( $pobj, 'iid' );
				if ( $exists_aid && $exists_iid && $obIsCronJob ) {
					$aid = $pobj->aid;
					$iid = $pobj->iid;
					$sql = "SELECT `cid` FROM `#__obsocialsubmit_logs` WHERE `aid`=" . $aid . " AND `iid`=" . $iid . " AND `cid`=" . $key . " AND `status`=1";
					$db->setQuery( $sql );
					$cids_posted = $db->loadResultArray();
					if ( $cids_posted ) {
						if ( in_array( $key, $cids_posted ) ) {
							continue;
						}
					}
				}

				#END Check log;

				#XXX Post messate
				$aid = isset( $pobj->aid ) ? $pobj->aid : null;
				$iid = isset( $pobj->iid ) ? $pobj->iid : null;

				if ( $aid && $iid && $key ) {
					$sql = "SELECT `cid` FROM `#__obsocialsubmit_logs` WHERE `aid`=" . $aid . " AND `iid`=" . $iid . " AND `cid`=" . $key . " AND `status`=1";
					$db->setQuery( $sql );
					$checkPost = $db->loadObjectList();
				}

				$params = JComponentHelper::getParams( 'com_obsocialsubmit' );

				if ( is_object( $params ) ) {
					$republished = $params->get( 'republished' );
				} else {
					$republished = json_decode( $params )->republished;
				}

				if ( ! $republished ) {
					if ( count( $checkPost ) < 1 ) {
						if ( $extern->postMessage( $pobj ) ) {
							$this->changeLog( $pobj, $key, true );
							$c ++;
							if ( $obssdebug && $obsstask == 'cronjob' ) {
								echo '<h1>' . __LINE__ . '-' . __FILE__ . '</h1>';
								echo '<pre>' . print_r( $extern, true ) . '</pre>';
							}
							$successCounter ++;
						} else {
							$this->changeLog( $pobj, $key, false );
							$f ++;
							if ( $obssdebug && $obsstask == 'cronjob' ) {
								echo '<h1>' . __LINE__ . '-' . __FILE__ . '</h1>';
								echo '<pre>' . print_r( $extern, true ) . '</pre>';
							}
						}
					}
				} else {
					if ( $extern->postMessage( $pobj ) ) {
						$this->changeLog( $pobj, $key, true );
						$c ++;
						if ( $obssdebug && $obsstask == 'cronjob' ) {
							echo '<h1>' . __LINE__ . '-' . __FILE__ . '</h1>';
							echo '<pre>' . print_r( $extern, true ) . '</pre>';
						}
						$successCounter ++;
					} else {
						$this->changeLog( $pobj, $key, false );
						$f ++;
						if ( $obssdebug && $obsstask == 'cronjob' ) {
							echo '<h1>' . __LINE__ . '-' . __FILE__ . '</h1>';
							echo '<pre>' . print_r( $extern, true ) . '</pre>';
						}
					}
				}
				#End Post message
			}
			//end foreach ( $messages as $pobj )
// 			exit();

			$t = $c + $f;
			if ( $notice && $t && $obsstask != 'cronjob' ) {
				$mainframe->enqueueMessage( "$t message is posted to {$externname} via <a href=\"{$conlink}\" target=\"blank\">{$conname}</a> connection, $c complete and $f false;" );
			}
		}

		return $successCounter;

	}


	function loadExternAddons() {
		if ( ! $this->_externs || ! count( $this->_externs ) ) {
			$sql = "
				SELECT 	*

				FROM 	`#__obsocialsubmit_instances`

				WHERE 	`addon_type`='extern' and `published`=1";

			$db = JFactory::getDBO();
			$db->setQuery( $sql );
			$externs = $db->loadObjectList();
			if ( ! $externs ) {
				return array();
			}

			foreach ( $externs as $extern ) {
				$exobj                       = $this->loadExterAddon( $extern );
				$this->_externs[$extern->id] = $exobj;
			}
		}

		return $this->_externs;
	}


	private function loadExterAddon( $extern ) {
		$addon     = $extern->addon;
		$classname = 'OBSSExAddon' . ucfirst( $addon );
		if ( ! class_exists( 'OBSSExAddon' . $addon ) ) {
			$filepath = JPath::clean( JPATH_SITE . DS . "plugins" . DS . "obss_extern" . DS . $addon . DS . $addon . ".php" );
			if ( JFile::exists( $filepath ) ) {
				require_once $filepath;
			}
		}

		if ( class_exists( $classname ) ) {
			$exobj = new $classname( $extern );

			return $exobj;
		}

		return false;
	}


	public function onAfterDispatch() {
		$app = JFactory::getApplication();
		if ( $app->isAdmin() ) {
			JHtml::_( 'stylesheet', 'administrator/components/com_obsocialsubmit/assets/css/obsocialsubmit _icon.css' );
			$option = JRequest::getVar( 'option' );
			if ( trim( $option ) == 'com_obsocialsubmit' ) {
				JHtml::_( 'stylesheet', 'administrator/components/com_obsocialsubmit/assets/css/obsocialsubmit.css' );
				JHtml::_( 'stylesheet', 'administrator/components/com_obsocialsubmit/assets/css/bootstrap-extended.css' );
			}
		}

		$this->execIntern( __FUNCTION__ );

		return true;
	}


	function onK2AfterDisplayTitle( & $item, & $params, $limitstart ) {
		$args = func_get_args();
		$this->execIntern( __FUNCTION__, $args );

		return '';
	}


	public function onAfterK2Save( &$row, $isNew ) {
		$args = func_get_args();
		$this->execIntern( __FUNCTION__, $args );

		return true;
	}


	public function onBeforeK2Save( &$row, $isNew ) {
		$args = func_get_args();
		$this->execIntern( __FUNCTION__, $args );

		return true;
	}


	/**
	 *
	 * Save log after post message to social network
	 *
	 * @param object $message
	 * @param int    $cid
	 * @param bool   $result
	 */

	function changeLog( $message, $cid, $result = true ) {
		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();
		// echo '<pre>'.print_r( $message, true ).'</pre>';
		// echo '<pre>'.print_r( $cid, true ).'</pre>';
		// exit();
		if ( ! property_exists( $message, 'aid' ) || ! property_exists( $message, 'iid' ) || ! $cid ) {
			return;
		}

		# load log
		$sql = "SELECT * FROM `#__obsocialsubmit_logs` "
			. " WHERE `aid`=" . $message->aid . " AND `iid`=" . $message->iid . " AND `cid`=" . $cid;
		$db->setQuery( $sql );
		$logObj = $db->loadObject();
		$date   = JFactory::getDate();
		$now    = $date->toSql();
		$status = ( $result ) ? '1' : '0';
		if ( ! $logObj ) {
			# INSERT NEW LOG
			$sql = "INSERT INTO `#__obsocialsubmit_logs`(`aid`,`iid`,`cid`,`status`, `publish_up`, `processed`,`process_time`)
						VALUES ( " . $message->aid . ", " . $message->iid . ", " . $cid . ", " . $status . ", '" . $now . "',1,'" . $now . "')";
			$db->setQuery( $sql );
			$db->query();
			if ( $db->getErrorNum() ) {
				$app->enqueueMessage( JText::_( 'OBSS_ERROR_CHANGE_LOG' ) . ' ' . __LINE__, 'notice' );
			}

			return $status;
		}

		#UPDATE LOG
		$sql = "UPDATE `#__obsocialsubmit_logs`
					SET `status` = " . $status . ", `processed`=1, `process_time`='" . $now . "'
					WHERE `aid`=" . $message->aid . " AND `iid`=" . $message->iid . " AND `cid`=" . $cid;
		$db->setQuery( $sql );
		$db->query();
		if ( $db->getErrorNum() ) {
			return false;
		}

		return true;
	}


	function obssTaskGetSef() {
		$obsstask   = JRequest::getVar( 'obsstask' );
		$option     = JRequest::getVar( 'option' );
		$controller = JRequest::getVar( 'controller' );
		$task       = JRequest::getVar( 'task' );
		if ( ! $option && ! $controller && ! $task && $obsstask == 'getsef' ) {
			$url_b64      = JRequest::getVar( 'url' );
			$url          = base64_decode( $url_b64 );
			$url_route    = JRoute::_( $url );
			$urlinfo      = parse_url( JURI::root() );
			$search_res_2 = strpos( $url_route, $urlinfo['path'] );
			if ( $search_res_2 === 0 ) {
				$url_route = $urlinfo['scheme'] . '://' . $urlinfo['host'] . ( ( isset( $urlinfo['port'] ) && $urlinfo['port'] ) ? ':' . $urlinfo['port'] : '' ) . $url_route;
			} else {
				$url_route = JURI::root() . $url_route;
			}
			echo $url_route;
			jexit();
		}
	}


	/**
	 * Auto remove logs record processed
	 */
	function clearLog() {
		return;
		$params  = JComponentHelper::getParams( 'com_obsocialsubmit' );
		$logtime = $params->get( 'logtime', 10 );
		$db      = JFactory::getDbo();
		$sql     = "DELETE FROM `#__obsocialsubmit_logs` WHERE `status`=1 AND `publish_up` IS NOT NULL AND `publish_up`<DATE_SUB(NOW(), INTERVAL " . $logtime . " DAY)";
		$db->setQuery( $sql );
		$db->query();
		if ( $db->getErrorNum() ) {
			echo '<pre>' . print_r( $db, true ) . '</pre>';
			exit();
		}
		$afectedRows = $db->getAffectedRows();

		return $afectedRows;
	}


	function addJsCronJobScript() {
		#add script to call cronjob of obss via ajax
		$configs    = JComponentHelper::getParams( 'com_obsocialsubmit' );
		$js_cronjob = $configs->get( 'js_cronjob' );
		if ( ! $js_cronjob ) {
			return;
		}
		JHtml::_('behavior.framework');
		$juri_root = JURI::root();
		$path      = explode( '/', $juri_root );
		$script    = "
			window.addEvent('domready',function(){
				var juri_root = '" . $juri_root . "';
				var juri_root_path2 = '" . $path[2] . "';
				juri_root = juri_root.replace(juri_root_path2,window.location.hostname)
				mVersion = MooTools.version.substring( 0, 3 );
				if(mVersion=='1.1'){
					new Ajax(juri_root+'index.php?obsstask=cronjob', {method: 'post'}).request();
				}else{
					new Request({
						url: juri_root+'index.php?obsstask=cronjob',method:'post'
					}).send();
				}
			});";
		$doc       = JFactory::getDocument();
		$doc->addScriptDeclaration( $script );
	}

	function onAfterRender() {
		$this->execIntern( __FUNCTION__ );
	}


	function getAdapter( $aid ) {
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
		$classname = 'OBSSInAddon' . ucfirst( $addon );
		$adapter   = new $classname( $data );

		return $adapter;
	}


	function getConnection( $cid ) {
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
		$classname  = 'OBSSExAddon' . ucfirst( $addon );
		$connection = new $classname( $data );

		return $connection;
	}


	public function obssDebug() {
		$obsstask = JRequest::getVar( 'obsstask', '' );
		if ( $obsstask == 'obssdebug' ) {
			$obssfunc = JRequest::getVar( 'obssfunc' );
			switch ( $obssfunc ) {
				case 'view_logs':
					$this->_viewLogs();
					break;

				case 'remove_logs':
					$this->_removeLogs();
					break;

				default:
					break;
			}
			exit( '' . __LINE__ );
		}
	}


	private function _viewLogs() {
		$app = JFactory::getApplication();
		echo '<style>
		.message {
			color: blue;
		}

		.error {
			color: red;
		}
		</style>';
		$queueMsg = $app->getMessageQueue();
		if ( $queueMsg ) {
			foreach ( $queueMsg as $msg ) {
				echo '<h2 class="' . $msg['type'] . '">' . $msg['message'] . '</h2>';
			}
		}

		$db  = JFactory::getDbo();
		$msg = JRequest::getVar( 'msg' );
		if ( $msg ) {
			echo '<h2>' . $msg . '</h2>';
		}

		$status = JRequest::getVar( 'status', '0' );
		if ( $status ) {
			$where_status = '`status` = 1';
		} else {
			$where_status = '`status` = 0';
		}

		$sql = "SELECT * FROM `#__obsocialsubmit_logs` WHERE " . $where_status . " ORDER BY `publish_up` ASC";
		$db->setQuery( $sql );
		$rows = $db->loadObjectList();
		echo '<table border="1"><tr><th>AID</th><th>CID</th><th>IID</th><th>PUBLISH_UP</th><th>STATUS</th><th>REMOVE</th></tr>';
		if ( $rows ) {
			foreach ( $rows as $row ) {
				echo "\n" . '<tr><td>' . $row->aid . '</td>'
					. '<td>' . $row->cid . '</td>'
					. '<td>' . $row->iid . '</td>'
					. '<td>' . $row->publish_up . '</td>'
					. '<td>' . $row->status . '</td>'
					. '<td><a href="index.php?obsstask=obssdebug&obssfunc=remove_logs&cid=' . $row->cid . '&aid=' . $row->aid . '&iid=' . $row->iid . '">Remove</a></td></tr>';
			}
		} else {
			if ( $db->getErrorNum() ) {
				echo '<tr><td colspan="6">' . print_r( $db->getErrorMsg(), true ) . '</td></tr>';
			} else {
				echo '<tr><td colspan="6">No records</td></tr>';
			}
		}
		echo '</table>';
	}


	private function _removeLogs() {
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();
		if ( $user->guest == 1 ) {
			$app->redirect( JURI::root() );
		}
		$db  = JFactory::getDbo();
		$cid = JRequest::getVar( 'cid' );
		$aid = JRequest::getVar( 'aid' );
		$iid = JRequest::getVar( 'iid' );
		$msg = '';

		if ( ! $cid || ! $aid || ! $iid ) {
			$msg = 'Log record not found, Please try again.';
		} else {
			$sql = 'DELETE FROM `#__obsocialsubmit_logs` WHERE `aid`=$aid AND `iid`=$iid AND `cid`=' . $cid;
			$db->setQuery( $sql );
			$db->query();
			if ( $db->getErrorNum() ) {
				$msg = $db->getErrorMsg();
			} else {
				$msg = 'A log record just removed!';
			}
		}
		$app->redirect( 'index.php?obsstask=obssdebug&obssfunc=view_logs', $msg );
	}


	function changeLogX( $iid, $aid, $cid, $processed = 1, $status = 0 ) {
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


	function addonFunc() {
		$app      = JFactory::getApplication();
		$obsstask = JRequest::getVar( 'obsstask' );
		if ( $obsstask != 'addonfunc' ) {
			return;
		}
		$addonfunc = JRequest::getVar( 'addonfunc' );
		$addon_id  = JRequest::getVar( 'aid' );
		if ( ! $addon_id && isset ( $_SESSION['ss_addon_id'] ) ) {
			$addon_id = $_SESSION['ss_addon_id'];
			unset( $_SESSION['ss_addon_id'] );
		}
		if ( ! $addon_id ) {
			exit( "" . __LINE__ );

			return;
		}

		$addon_type = JRequest::getVar( 'atype' );
		$addon      = null;
		if ( $addon_type == 'ex' ) {
			$addon = $this->getConnection( $addon_id );
		} elseif ( $addon_type == 'in' ) {
			$addon = $this->getAdapter( $addon_id );
		} else {
			return;
		}

		if ( ! $addon ) {
			$app->enqueueMessage( '', 'error' );
			exit( "" . __LINE__ );

			return;
		}

		$funcallows       = array( 'connect', 'callback' );
		$addon_funcallows = isset( $addon->functions ) ? $addon->functions : array();
		$funcallows       = array_merge( $funcallows, $addon_funcallows );
		if ( ! in_array( $addonfunc, $funcallows ) ) {
			return;
		}
		call_user_func( array( $addon, $addonfunc ) );
	}


	public function onExtensionAfterInstall( $installer, $eid ) {
		if (!$eid) return;
		$db    = JFactory::getDbo();
		$query = 'SELECT * FROM `#__extensions` WHERE `extension_id`=' . $eid;
		$db->setQuery( $query );
		$extension = $db->loadObject();
		if ( $extension->type == 'plugin' && in_array( $extension->folder, array( 'obss_extern', 'obss_intern' ) ) ) {
			$adapters = $installer->get( '_adapters' );
			$plugin   = $adapters['plugin'];
			$route    = $plugin->get( 'route' );
			if ( $route == 'update' ) {
				return;
			}

			$query = 'UPDATE `#__extensions` SET `enabled`=1 WHERE `type`="plugin" AND `extension_id`=' . $eid;
			$db->setQuery( $query );
			$db->execute();

			# TODO : create new instance for plugin\
			$date = JFactory::getDate();
			$now  = $date->toSql();

			$data               = array();
			$data['title']      = $extension->name;
			$data['addon']      = $extension->element;
			$data['addon_type'] = substr( $extension->folder, 5 );
			$data['published']  = 0;
			$data['created']    = $now;

			JTable::addIncludePath( JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_obsocialsubmit' . DIRECTORY_SEPARATOR . 'tables' );
			$instance = JTable::getInstance( 'instances', 'ObSocialSubmitTable' );
			$instance->bind( $data );
			$instance->store();
		}
	}
}
