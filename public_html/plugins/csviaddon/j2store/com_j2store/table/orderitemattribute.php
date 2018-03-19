<?php
/**
 * @package     CSVI
 * @subpackage  J2Store
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * J2Store Order item attribute table.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class J2StoreTableOrderitemattribute extends CsviTableDefault
{
	/**
	 * Table constructor.
	 *
	 * @param   string     $table   Name of the database table to model.
	 * @param   string     $key     Name of the primary key field in the table.
	 * @param   JDatabase  &$db     Database driver
	 * @param   array      $config  The configuration parameters array
	 *
	 * @since   7.3.0
	 */
	public function __construct($table, $key, &$db, $config = array())
	{
		parent::__construct('#__j2store_orderitemattributes', 'j2store_orderitemattribute_id', $db, $config);
	}

	/**
	 * Reset the primary key.
	 *
	 * @return  boolean  Always returns true.
	 *
	 * @since   7.3.0
	 */
	public function reset()
	{
		parent::reset();

		// Reset the primary key
		$this->j2store_orderitemattribute_id = null;

		return true;
	}
}
