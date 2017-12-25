<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

/**
 * JD iDEAL Gateway Profiles table.
 *
 * @package  JDiDEAL
 * @since    4.0
 */
class TableProfile extends JTable
{
	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver  $db  A database connector object.
	 *
	 * @since   4.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__jdidealgateway_profiles', 'id', $db);
	}

	/**
	 * Check that everything is OK.
	 *
	 * @return  bool  True if all checks are OK | False if there is an issue.
	 *
	 * @since   4.2.0
	 *
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 */
	public function check()
	{
		// Check that the alias is unique
		$query = $this->_db->getQuery(true)
			->select($this->_db->quoteName('id'))
			->from($this->_db->quoteName($this->_tbl))
			->where($this->_db->quoteName('alias') . ' = ' . $this->_db->quote($this->get('alias')));
		$this->_db->setQuery($query);

		$profileId = $this->_db->loadResult();

		if ($profileId !== $this->get('id') && $profileId > 0)
		{
			throw new InvalidArgumentException('Alias already exists');
		}

		return true;
	}
}
