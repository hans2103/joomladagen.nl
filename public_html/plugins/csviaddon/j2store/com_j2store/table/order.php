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
 * J2Store Orders table.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class J2StoreTableOrder extends CsviTableDefault
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
		parent::__construct('#__j2store_orders', 'j2store_order_id', $db, $config);
	}

	/**
	 * Do some sanity checking.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @since   7.3.0
	 */
	public function check()
	{
		// Check if the order ID already exists, if not create it if needed
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName($this->_tbl_key))
			->from($this->db->quoteName($this->_tbl))
			->where($this->db->quoteName($this->_tbl_key) . ' = ' . (int) $this->j2store_order_id);
		$this->db->setQuery($query);

		$id = $this->db->loadResult();

		if (!$id && $this->template->get('keepid'))
		{
			$query->clear()
				->insert($this->db->quoteName($this->_tbl))
				->columns(array($this->db->quoteName($this->_tbl_key)))
				->values((int) $this->j2store_order_id);
			$this->db->setQuery($query)->execute();
		}

		return true;
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
		$this->j2store_order_id = null;

		return true;
	}
}
