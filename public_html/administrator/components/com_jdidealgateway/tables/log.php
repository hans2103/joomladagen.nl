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
 * JD iDEAL Gateway logs table.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class TableLog extends JTable
{
	/**
	 * Constructor.
	 *
	 * @param   JDatabase  $db  A database connector object.
	 *
	 * @since   2.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__jdidealgateway_logs', 'id', $db);
	}
}
