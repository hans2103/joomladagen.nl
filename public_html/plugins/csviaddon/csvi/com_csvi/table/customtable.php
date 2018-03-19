<?php
/**
 * @package     CSVI
 * @subpackage  CSVI
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Custom table.
 *
 * @package     CSVI
 * @subpackage  CSVI
 * @since       6.0
 */
class CsviTableCustomtable extends CsviTableDefault
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
	 *
	 * @throws  CsviException
	 */
	public function __construct($table, $key, &$db, $config = array())
	{
		if (!isset($config['template']))
		{
			throw new CsviException(JText::_('COM_CSVI_TEMPLATE_NOT_AVAIlABLE'), 515);
		}

		// Get the settings for this table
		$tbl  = $config['template']->get('custom_table');
		$keys = $config['template']->get('import_based_on');

		// Check if there are any keys, otherwise use the primary key field
		if (!$keys)
		{
			// Find the primary key for this table
			$helper = new CsviHelperCsvi;
			$keys = $helper->getPrimaryKey($tbl);
		}

		// Make it an array
		$keys = explode(',', $keys);
		$keys = array_filter(array_map('trim', $keys));

		// Make sure there are any keys for updating
		if (!$keys || (is_array($keys) && $keys[0] === ''))
		{
			throw new CsviException(JText::_('COM_CSVI_CUSTOM_NO_PRIMARY_KEY_AND_NO_FIELDS_SET'));
		}

		parent::__construct('#__' . $tbl, $keys, $db, $config);
	}

	/**
	 * Check if a custom row already exists
	 *
	 * @return  bool  True if row exists | False if row does not exist.
	 *
	 * @since   7.3.0
	 */
	public function checkIfRowExists()
	{
		$keys  = $this->getKeyName(true);
		$keys  = array_filter(array_map('trim', $keys));
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName($keys))
			->from($this->db->quoteName($this->getTableName()));

		foreach ($keys as $importKey)
		{
			if ($importKey)
			{
				$importKey = trim($importKey);
				$query->where($this->db->quoteName($importKey) . ' = ' . $this->db->quote($this->get($importKey)));
			}
		}

		$this->db->setQuery($query);
		$this->log->add('Finding the matching row');
		$id = $this->db->loadResult();

		if ($id)
		{
			$this->load();

			return true;
		}

		return false;
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

		// Empty the primary keys
		if ($this->_tbl_keys)
		{
			foreach ($this->_tbl_keys as $key)
			{
				$this->$key = null;
			}
		}

		return true;
	}
}
