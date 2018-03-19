<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaFields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Joomla Fields Groups table.
 *
 * @package     CSVI
 * @subpackage  JoomlaFieldsGroups
 * @since       7.2.0
 */
class FieldsTableFieldGroups extends CsviTableDefault
{
	/**
	 * Should rows be tracked as ACL assets?
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $_trackAssets = true;

	/**
	 * Table constructor.
	 *
	 * @param   string     $table   Name of the database table to model.
	 * @param   string     $key     Name of the primary key field in the table.
	 * @param   JDatabase  &$db     Database driver
	 * @param   array      $config  The configuration parameters array
	 *
	 * @since   7.2.0
	 */
	public function __construct($table, $key, &$db, $config = array())
	{
		parent::__construct('#__fields_groups', 'id', $db, $config);
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   7.2.0
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_content.fieldgroup.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 *
	 * @since   7.2.0
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Reset the primary key.
	 *
	 * @return  boolean  Always returns true.
	 *
	 * @since   7.2.0
	 */
	public function reset()
	{
		parent::reset();

		// Reset the primary key
		$this->id = null;
	}
}
