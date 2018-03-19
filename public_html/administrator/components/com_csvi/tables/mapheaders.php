<?php
/**
 * Map fields table
 *
 * @author 		RolandD Cyber Produksi
 * @link 		https://csvimproved.com
 * @copyright 	Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: templatetype.php 2273 2013-01-03 16:33:30Z RolandD $
 */

// No direct access
defined('_JEXEC') or die;

class TableMapheaders extends JTable {


	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver  $db  A database connector object.
	 *
	 * @since   6.6.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__csvi_mapheaders', 'id', $db);
	}
}
