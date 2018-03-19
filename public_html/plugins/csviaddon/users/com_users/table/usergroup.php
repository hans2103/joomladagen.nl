<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaUser
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * User group table.
 *
 * @package     CSVI
 * @subpackage  JoomlaUser
 * @since       6.5.0
 */
class UsersTableUsergroup extends CsviTableDefault
{
	/**
	 * Table constructor.
	 *
	 * @param   string     $table   Name of the database table to model.
	 * @param   string     $key     Name of the primary key field in the table.
	 * @param   JDatabase  &$db     Database driver
	 * @param   array      $config  The configuration parameters array
	 *
	 * @since   4.0
	 */
	public function __construct($table, $key, &$db, $config)
	{
		parent::__construct('#__usergroups', 'id', $db, $config);
	}

	/**
	 * Store the usergroup.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.5.0
	 */
	public function storeUsergroup()
	{
		// Verify that the alias is unique
		$table = JTable::getInstance('Usergroup', 'JTable', array('dbo' => $this->getDbo()));

		return $table->save($this->getProperties());
	}

	/**
	 * Delete the user group and any underlying user groups
	 *
	 * @param   int  $id  The ID of the user group to delete.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.5.0
	 */
	public function deleteUsergroup($id)
	{
		// Verify that the alias is unique
		$table = JTable::getInstance('Usergroup', 'JTable', array('dbo' => $this->getDbo()));

		return $table->delete($id);
	}

	/**
	 * Reset the primary key.
	 *
	 * @return  boolean  Always returns true.
	 *
	 * @since   6.0
	 */
	public function reset()
	{
		parent::reset();

		// Empty the primary key
		$this->id = null;

		return true;
	}
}
