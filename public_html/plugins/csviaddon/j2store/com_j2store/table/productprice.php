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
 * J2Store Product price table.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class J2StoreTableProductPrice extends CsviTableDefault
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
		parent::__construct('#__j2store_product_prices', 'j2store_productprice_id', $db, $config);
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
		$this->j2store_productprice_id = null;
	}

	/**
	 * Check if a price already exists
	 *
	 * @return  bool  True if price exists | False if price does not exist.
	 *
	 * @since   7.3.0
	 */
	public function check()
	{
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName($this->_tbl_key))
			->from($this->db->quoteName($this->_tbl))
			->where($this->db->quoteName('customer_group_id') . ' = ' . (int) $this->get('customer_group_id', 0))
			->where($this->db->quoteName('variant_id') . ' = ' . (int) $this->get('variant_id'));

		$query->where('(' .
			$this->db->quoteName('quantity_from') . ' = ' . (int) $this->get('quantity_from', 0) . ' OR ' .
			$this->db->quoteName('quantity_from') . ' IS NULL)');

		$query->where('(' .
			$this->db->quoteName('quantity_to') . ' = ' . (int) $this->get('quantity_to', 0) . ' OR ' .
			$this->db->quoteName('quantity_to') . ' IS NULL)');

		if ($this->get('date_from', false))
		{
			$query->where($this->db->quoteName('date_from') . ' = ' . $this->db->quote($this->get('date_from')));
		}
		else
		{
			$query->where('(' .
				$this->db->quoteName('date_from') . ' = ' . $this->db->quote('0000-00-00 00:00:00') . ' OR ' .
				$this->db->quoteName('date_from') . ' IS NULL)');
		}

		if ($this->get('date_to', false))
		{
			$query->where($this->db->quoteName('date_to') . ' = ' . $this->db->quote($this->get('date_to')));
		}
		else
		{
			$query->where('(' .
				$this->db->quoteName('date_to') . ' = ' . $this->db->quote('0000-00-00 00:00:00') . ' OR ' .
				$this->db->quoteName('date_to') . ' IS NULL)');
		}

		$this->db->setQuery($query);
		$this->log->add('Finding a product price for a variant');
		$id = $this->db->loadResult();

		if (!$id)
		{
			$this->j2store_productprice_id = null;

			return false;
		}

		$this->j2store_productprice_id = $id;
		$this->load($id);

		return true;
	}
}
