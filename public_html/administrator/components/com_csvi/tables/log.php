<?php
/**
 * @package     CSVI
 * @subpackage  Table
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * CSVI Logs table.
 *
 * @package     CSVI
 * @subpackage  Table
 * @since       6.6.0
 */
class TableLog extends JTable
{
	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver  $db  A database connector object.
	 *
	 * @since   6.6.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__csvi_logs', 'csvi_log_id', $db);
	}
}
