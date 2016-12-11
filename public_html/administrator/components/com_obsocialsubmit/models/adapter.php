<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined( '_JEXEC' ) or die;
jimport( 'joomla.application.component.modeladmin' );

/**
 * Module model.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
class ObSocialSubmitModelAdapter extends JModelAdmin {
	/**
	 * @var    string  The prefix to use with controller messages.
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_OBSOCIALSUBMIT_ADAPTER';

	/**
	 * @var    string  The help screen key for the module.
	 * @since  1.6
	 */
//	protected $helpKey = 'JHELP_EXTENSIONS_MODULE_MANAGER_EDIT';

	/**
	 * @var    string  The help screen base URL for the module.
	 * @since  1.6
	 */
//	protected $helpURL;

	/**
	 * Method to perform batch operations on a set of modules.
	 *
	 * @param   array $commands An array of commands to perform.
	 * @param   array $pks      An array of item ids.
	 * @param   array $contexts An array of item contexts.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   1.7
	 */
	public function batch( $commands, $pks, $contexts ) {
		// Sanitize user ids.
		$pks = array_unique( $pks );
		JArrayHelper::toInteger( $pks );

		// Remove any values of zero.
		if ( array_search( 0, $pks, true ) ) {
			unset( $pks[array_search( 0, $pks, true )] );
		}

		if ( empty( $pks ) ) {
			$this->setError( JText::_( 'JGLOBAL_NO_ITEM_SELECTED' ) );

			return false;
		}

		$done = false;

		if ( ! empty( $commands['connections_id'] ) ) {
			$cmd    = JArrayHelper::getValue( $commands, 'move_copy', 'c' );
			$action = $commands['action'];
			if ( ! empty( $commands['connections_id'] ) && $action == 'connect' ) {
				$result = $this->batchConnect( $commands['connections_id'], $pks, $contexts );
				if ( is_array( $result ) ) {
					$pks = $result;
				} else {
					return false;
				}
				$done = true;
			}

			if ( ! empty( $commands['connections_id'] ) && $action == 'disconnect' ) {
				$result = $this->batchDisconnect( $commands['connections_id'], $pks, $contexts );
				if ( is_array( $result ) ) {
					$pks = $result;
				} else {
					return false;
				}
				$done = true;
			}
		}

		if ( ! $done ) {
			$this->setError( JText::_( 'JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION' ) );

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm( $data = array(), $loadData = true ) {
		// The folder and element vars are passed when saving the form.
		if ( empty( $data ) ) {
			$item  = $this->getItem();
			$addon = $item->addon;
		} else {
			$addon = JArrayHelper::getValue( $data, 'addon' );
		}
		// These variables are used to add data from the plugin XML files.
		$this->setState( 'item.adapter', $addon );

		require_once JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obsocialsubmit' . DS . 'helpers' . DS . 'class.internaddon.php';
		// Get the form.
		$form = $this->loadForm( 'com_obsocialsubmit.adapter', 'adapter', array( 'control' => 'jform', 'load_data' => $loadData ) );
		if ( empty( $form ) ) {
			return false;
		}

		// Modify the form based on access controls.
		if ( ! $this->canEditState( (object) $data ) ) {
			// Disable fields for display.
			$form->setFieldAttribute( 'ordering', 'disabled', 'true' );
			$form->setFieldAttribute( 'published', 'disabled', 'true' );

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute( 'ordering', 'filter', 'unset' );
			$form->setFieldAttribute( 'published', 'filter', 'unset' );
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData() {
		$app               = JFactory::getApplication();
		$data              = $this->getItem();
		$data->connections = explode( ',', $data->cids );

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer $pk The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem( $pk = null ) {
		$app  = JFactory::getApplication();
		$item = parent::getItem( $pk );
		$ds = DIRECTORY_SEPARATOR;
		if ( ! $item->id ) {
			$addon       = $app->getUserState( 'com_obsocialsubmit.add.adapter.addon' );
			$item->addon = $addon;
		}

		$xmlpath = JPATH_SITE . $ds . 'plugins' . $ds . 'obss_intern' . $ds . $item->addon . $ds . $item->addon . '.xml';
		if ( JFile::exists( $xmlpath ) ) {
			$item->xml = simplexml_load_file( $xmlpath );
		} else {
			$item->xml = null;
		}

		return $item;
	}

	/**
	 * Get the necessary data to load an item help screen.
	 *
	 * @return  object  An object with key, url, and local properties for loading the item help screen.
	 *
	 * @since   1.6
	 */
	public function getHelp() {
		return (object) array( 'key' => $this->helpKey, 'url' => $this->helpURL );
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string $type   The table type to instantiate
	 * @param   string $prefix A prefix for the table class name. Optional.
	 * @param   array  $config Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 *
	 * @since   1.6
	 */
	public function getTable( $type = 'Instances', $prefix = 'ObSocialSubmitTable', $config = array() ) {
		return JTable::getInstance( $type, $prefix, $config );
	}

	/**
	 * Method to preprocess the form
	 *
	 * @param   JForm  $form  A form object.
	 * @param   mixed  $data  The data expected for the form.
	 * @param   string $group The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  Exception if there is an error loading the form.
	 */
	protected function preprocessForm( JForm $form, $data, $group = 'obss_intern' ) {
		jimport( 'joomla.filesystem.path' );
		$lang  = JFactory::getLanguage();
		$addon = isset( $data->addon ) ? $data->addon : 'content';

		$formFile = JPath::clean( JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'obss_intern' . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR . $addon . '.xml' );
//		Load the core and/or local language file(s).

		$lang->load( 'plg_obss_intern_' . $addon, JPATH_ADMINISTRATOR, null, false, false )
		|| $lang->load( 'plg_obss_intern_' . $addon, JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'obss_intern' . DIRECTORY_SEPARATOR . $addon, null, false, false )
		|| $lang->load( 'plg_obss_intern_' . $addon, JPATH_SITE, $lang->getDefault(), false, false )
		|| $lang->load( 'plg_obss_intern_' . $addon, JPATH_SITE . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'obss_intern' . DIRECTORY_SEPARATOR . $addon, $lang->getDefault(), false, false );
		if ( file_exists( $formFile ) ) {
			// Get the module form.
			if ( ! $form->loadFile( $formFile, false, '//config' ) ) {
				throw new Exception( JText::_( 'JERROR_LOADFILE_FAILED' ) );
			}

			// Attempt to load the xml file.
			if ( ! $xml = simplexml_load_file( $formFile ) ) {
				throw new Exception( JText::_( 'JERROR_LOADFILE_FAILED' ) );
			}

			// Get the help data from the XML file if present.
			$help = $xml->xpath( '/extension/help' );
			if ( ! empty( $help ) ) {
				$helpKey = trim( (string) $help[0]['key'] );
				$helpURL = trim( (string) $help[0]['url'] );

				$this->helpKey = $helpKey ? $helpKey : $this->helpKey;
				$this->helpURL = $helpURL ? $helpURL : $this->helpURL;
			}

		}

		// Trigger the default form events.
		parent::preprocessForm( $form, $data, $group );
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array $data The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function save( $data ) {
		$registry = new JRegistry();
		if ( isset( $data['connections'] ) ) {
			$data['cids'] = implode( ',', $data['connections'] );
		} else {
			$data['cids'] = '';
		}
		$params_str = $_REQUEST['jform']['params'];
		$registry->loadArray( $params_str );
		$params         = $registry->toString();
		$data['params'] = $params;
		$res            = parent::save( $data );

		return $res;
	}

	public function batchConnect( $value, $pks, $contexts ) {
		$pks_str = implode( ',', $pks );
		$db      = JFactory::getDbo();
		$sql     = "SELECT id, cids
						FROM #__obsocialsubmit_instances
						WHERE id IN ({$pks_str})";
		$db->setQuery( $sql );
		$rows = $db->loadObjectList( 'id' );
		$res  = array();
		foreach ( $rows as $row ) {
			$cids = explode( ',', $row->cids );
			if ( ! $cids ) {
				$cids = array();
			}
			$cids     = array_merge( $cids, $value );
			$cids     = array_unique( $cids );
			$cids_str = implode( ',', $cids );

			$sql = "UPDATE `#__obsocialsubmit_instances`
						SET `cids` = '{$cids_str}'
						WHERE `id` = {$row->id}";
			$db->setQuery( $sql );
			$db->query();
			if ( ! $db->getErrorNum() ) {
				$res[] = $row->id;
			}
		}

		return $res;
	}


	public function batchDisconnect( $value, $pks, $contexts ) {
		$pks_str = implode( ',', $pks );
		$db      = JFactory::getDbo();
		$sql     = "SELECT id, cids
						FROM #__obsocialsubmit_instances
						WHERE id IN ({$pks_str})";
		$db->setQuery( $sql );
		$rows = $db->loadObjectList( 'id' );
		$res  = array();
		foreach ( $rows as $row ) {
			$cids = explode( ',', $row->cids );
			if ( ! $cids ) {
				$cids = array();
			}
			$cids     = array_diff( $cids, $value );
			$cids     = array_unique( $cids );
			$cids_str = implode( ',', $cids );

			$sql = "UPDATE `#__obsocialsubmit_instances`
			SET `cids` = '{$cids_str}'
			WHERE `id` = {$row->id}";
			$db->setQuery( $sql );
			$db->query();
			if ( ! $db->getErrorNum() ) {
				$res[] = $row->id;
			}
		}

		return $res;
	}

	public function debug( $cid, $value ) {
		$db      = JFactory::getDbo();
		$cid_str = implode( ",", $cid );
		$sql     = "UPDATE `#__obsocialsubmit_instances`
		SET `debug` = '{$value}'
		WHERE `id` IN ($cid_str)";
		$db->setQuery( $sql );
		$db->query();
		if ( $db->getErrorNum() ) {
			return false;
		} else {
			return true;
		}
	}

	public function setConnect( $aid, $cid, $ac ) {
		if ( ! $aid || ! $cid | ! $ac ) {
			return;
		}
		$db  = JFactory::getDbo();
		$sql = 'SELECT * '
			. 'FROM `#__obsocialsubmit_instances` '
			. 'WHERE `addon_type`="intern"  '
			. 'AND `id`=' . $aid;
		$db->setQuery( $sql );
		$adapter = $db->loadObject();
		$cids    = explode( ',', $adapter->cids );
		if ( $ac == 'c' ) {
			$cids[] = $cid;
		} elseif ( $ac == 'd' ) {
			$cids = array_diff( $cids, array( $cid ) );
		}
		$cids     = array_unique( $cids );
		$cids_str = implode( ',', $cids );
		$sql      = 'UPDATE `#__obsocialsubmit_instances` SET `cids`="' . $cids_str . '" WHERE `id`=' . $aid;
		$db->setQuery( $sql );
		$db->query();
		if ( $db->getErrorNum() ) {
			return false;
		} else {
			return true;
		}
	}

	public function add_temp( $addon ) {
		$db  = JFactory::getDBO();
		$qry = "SELECT MAX(id) FROM `#__obsocialsubmit_instances`";
		$db->setQuery( $qry );
		$max_cur = $db->LoadResult();
		$maxid   = $max_cur + 1;

		$name    = 'Stream_' . $addon . '_#' . $maxid;
		$created = date( 'Y-m-d H:i:s', time() );
		$ins_q   = "INSERT INTO `#__obsocialsubmit_instances` (`id`,`addon`,`title`,`addon_type`,`created`,`published`) "
			. "\n VALUES ({$maxid},'{$addon}', '{$name}', 'intern', '{$created}', 1)";
		$db->setQuery( $ins_q );
		if ( ! $db->query() ) {
			$error = $db->getErrorMsg();

			return $error;
		}

		return $maxid;
	}
}
