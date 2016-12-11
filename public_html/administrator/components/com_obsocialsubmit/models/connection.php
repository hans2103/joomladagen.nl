<?php
/**
 * @package        obSocialSubmit
 * @author         foobla.com.
 * @copyright      Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL
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
class ObSocialSubmitModelConnection extends JModelAdmin {
	/**
	 * @var    string  The prefix to use with controller messages.
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_OBSOCIALSUBMIT';

	/**
	 * @var    string  The help screen key for the module.
	 * @since  1.6
	 */
	protected $helpKey = 'JHELP_EXTENSIONS_MODULE_MANAGER_EDIT';

	/**
	 * @var    string  The help screen base URL for the module.
	 * @since  1.6
	 */
	protected $helpURL;

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

		if ( ! empty( $commands['position_id'] ) ) {
			$cmd = JArrayHelper::getValue( $commands, 'move_copy', 'c' );

			if ( ! empty( $commands['position_id'] ) ) {
				if ( $cmd == 'c' ) {
					$result = $this->batchCopy( $commands['position_id'], $pks, $contexts );
					if ( is_array( $result ) ) {
						$pks = $result;
					} else {
						return false;
					}
				} elseif ( $cmd == 'm' && ! $this->batchMove( $commands['position_id'], $pks, $contexts ) ) {
					return false;
				}
				$done = true;
			}
		}

		if ( ! empty( $commands['assetgroup_id'] ) ) {
			if ( ! $this->batchAccess( $commands['assetgroup_id'], $pks, $contexts ) ) {
				return false;
			}

			$done = true;
		}

		if ( ! empty( $commands['language_id'] ) ) {
			if ( ! $this->batchLanguage( $commands['language_id'], $pks, $contexts ) ) {
				return false;
			}

			$done = true;
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
		$this->setState( 'item.connection', $addon );

		require_once JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obsocialsubmit' . DS . 'helpers' . DS . 'class.externaddon.php';
		// Get the form.
		$form = $this->loadForm( 'com_obsocialsubmit.connection', 'connection', array( 'control' => 'jform', 'load_data' => $loadData ) );
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
//		echo '<pre>'.print_r($item, true).'</pre>';
		$ds = DIRECTORY_SEPARATOR;
		if ( ! $item->id ) {
			$addon       = $app->getUserState( 'com_obsocialsubmit.add.connection.addon' );
			$item->addon = $addon;
		}

		$xmlpath = JPATH_SITE . $ds . 'plugins' . $ds . 'obss_extern' . $ds . $item->addon . $ds . $item->addon . '.xml';
		if ( JFile::exists( $xmlpath ) ) {
			$item->xml = simplexml_load_file( $xmlpath );
		} else {
			$item->xml = null;
		}

		return $item;
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
	protected function preprocessForm( JForm $form, $data, $group = 'content' ) {
		jimport( 'joomla.filesystem.path' );
		$lang  = JFactory::getLanguage();
		$addon = isset( $data->addon ) ? $data->addon : 'content';

		$formFile = JPath::clean( JPATH_SITE . '/plugins/obss_extern/' . $addon . '/' . $addon . '.xml' );
//		Load the core and/or local language file(s).

		$lang->load( 'plg_obss_extern_' . $addon, JPATH_ADMINISTRATOR, null, false, false )
		|| $lang->load( 'plg_obss_extern_' . $addon, JPATH_SITE . '/plugins/obss_extern/' . $addon, null, false, false )
		|| $lang->load( 'plg_obss_extern_' . $addon, JPATH_SITE, $lang->getDefault(), false, false )
		|| $lang->load( 'plg_obss_extern_' . $addon, JPATH_SITE . '/plugins/obss_extern/' . $addon, $lang->getDefault(), false, false );
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
		$registry     = new JRegistry();
		$data['cids'] = '';
		$registry->loadArray( $_POST['jform']['params'] );
		$params         = $registry->toString();
		$data['params'] = $params;
		$res            = parent::save( $data );

		return $res;
	}

	public function add_temp( $addon ) {
		$db  = JFactory::getDBO();
		$qry = "SELECT MAX(id) FROM `#__obsocialsubmit_instances`";
		$db->setQuery( $qry );
		$max_cur = $db->LoadResult();
		$maxid   = $max_cur + 1;

		$name = 'Connection_' . $addon . '_#' . $maxid;
		$created = date( 'Y-m-d H:i:s', time() );
		$ins_q = "INSERT INTO `#__obsocialsubmit_instances` (`id`,`addon`,`title`,`addon_type`,`created`,`published`) "
			. "\n VALUES ({$maxid},'{$addon}', '{$name}', 'extern', '{$created}', 1)";
		$db->setQuery( $ins_q );
		if ( ! $db->query() ) {
			$error = $db->getErrorMsg();

			return $error;
		}

		return $maxid;
	}

	public function update_auto() {
		if ( ! isset( $_POST['connect_id'] ) ) {
			return false;
		}
		$db  = JFactory::getDBO();
		$qry = "SELECT `params` FROM `#__obsocialsubmit_instances` WHERE `id`={$_POST['connect_id']}";
		$db->setQuery( $qry );
		$pipes  = $db->LoadObject();
		$params = json_decode( $pipes->params );
		if ( ! is_object( $params ) ) {
			$params = new stdClass();
		}
		foreach ( $_POST as $key => $value ) {
			if ( $key != 'connect_id' ) {
				$params->$key = $value;
			}
		}
		$registry = new JRegistry();
		$registry->loadArray( $params );
		$paramStr = $registry->toString();
		$db       = JFactory::getDBO();
		$qry      = "UPDATE `#__obsocialsubmit_instances` SET `params` = '" . addslashes( $paramStr ) . "', `published` = 1 WHERE `id` ={$_POST['connect_id']}";
		$db->setQuery( $qry );
		if ( ! $db->query() ) {
			echo $db->getErrorMsg();
			exit();
		}

		return true;
	}
}
