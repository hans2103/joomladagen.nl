<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaContacts
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Joomla Contacts table.
 *
 * @package     CSVI
 * @subpackage  JoomlaContacts
 * @since       7.2.0
 */
class ContactTableContact extends CsviTableDefault
{
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
		parent::__construct('#__contact_details', 'id', $db, $config);
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
