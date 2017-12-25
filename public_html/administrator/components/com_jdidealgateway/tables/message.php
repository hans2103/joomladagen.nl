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
 * Message table.
 *
 * @package  JDiDEAL
 * @since    4.0
 */
class TableMessage extends JTable
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
		parent::__construct('#__jdidealgateway_messages', 'id', $db);
	}

	/**
	 * Overrides JTable::store to set modified data and user id.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   4.1.0
	 */
	public function store($updateNulls = false)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$this->set('modified', $date->toSql());

		if ($this->get('id', false))
		{
			// Existing item
			$this->set('modified_by', $user->get('id'));
		}
		else
		{
			$this->set('created', $date->toSql());

			if (empty($this->created_by))
			{
				$this->set('created_by', $user->get('id'));
			}
		}

		return parent::store($updateNulls);
	}
}
