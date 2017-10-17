<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jticketing
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Featured Table class.
 *
 * @since  2.0
 */
class JTicketingTableMediaXref extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since   2.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__media_files_xref', 'id', $db);
	}
}
