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
 * J2Store Geo zone rules table.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class J2StoreTableGeozonerule extends CsviTableDefault
{
	/**
	 * Table constructor.
	 *
	 * @param   string    $table  Name of the database table to model.
	 * @param   string    $key    Name of the primary key field in the table.
	 * @param   JDatabase &$db    Database driver
	 * @param   array     $config The configuration parameters array
	 *
	 * @since   7.5.0
	 */
	public function __construct($table, $key, &$db, $config = array())
	{
		parent::__construct('#__j2store_geozonerules', 'j2store_geozonerule_id', $db, $config);
	}

	/**
	 * Check if a geozone rule already exists
	 *
	 * @return  bool  True if price exists | False if price does not exist.
	 *
	 * @since   7.5.0
	 */
	public function check()
	{
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName($this->_tbl_key))
			->from($this->db->quoteName($this->_tbl))
			->where($this->db->quoteName('geozone_id') . ' = ' . (int) $this->get('geozone_id', 0))
			->where($this->db->quoteName('zone_id') . ' = ' . (int) $this->get('zone_id', 0))
			->where($this->db->quoteName('country_id') . ' = ' . (int) $this->get('country_id', 0));

		$this->db->setQuery($query);
		$this->log->add('Finding geozone rule id');
		$id = $this->db->loadResult();

		if (!$id)
		{
			$this->j2store_geozonerule_id = null;

			return false;
		}

		$this->j2store_geozonerule_id = $id;
		$this->load($id);

		return true;
	}

	/**
	 * Reset the primary key.
	 *
	 * @return  boolean  Always returns true.
	 *
	 * @since   7.5.0
	 */
	public function reset()
	{
		parent::reset();

		// Reset the primary key
		$this->j2store_geozonerule_id = null;

		return true;
	}
}
