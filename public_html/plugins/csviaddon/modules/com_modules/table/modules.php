<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaModule
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Module import.
 *
 * @package     CSVI
 * @subpackage  JoomlaModule
 * @since       7.4.0
 */
class ModulesTableModules extends CsviTableDefault
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
	 * @since   7.4.0
	 */
	public function __construct($table, $key, &$db, $config = array())
	{
		parent::__construct('#__modules', 'id', $db, $config);
	}

	/**
	 * Check if insert id has to be kept as given
	 *
	 * @return  bool  True if row inserted | False otherwise.
	 *
	 * @since   7.4.0
	 */

	public function checkId()
	{
		if (!$this->id)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName($this->_tbl_key))
			->from($this->db->quoteName($this->_tbl))
			->where($this->db->quoteName($this->_tbl_key) . ' = ' . (int) $this->id);
		$this->db->setQuery($query);

		$id = $this->db->loadResult();

		if (!$id && $this->template->get('keepid'))
		{
			$query->clear()
				->insert($this->db->quoteName($this->_tbl))
				->columns(array($this->db->quoteName($this->_tbl_key)))
				->values((int) $this->id);
			$this->db->setQuery($query)->execute();
			$this->log->add('Insert a new Joomla module row with id in import file');
		}

		return true;
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since   7.4.0
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_modules.module.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 *
	 * @since   7.4.0
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Reset the primary key.
	 *
	 * @return  void
	 *
	 * @since   7.4.0
	 */
	public function reset()
	{
		parent::reset();

		// Reset the primary key
		$this->id = null;
	}
}
