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
 * Module maintenance.
 *
 * @package     CSVI
 * @subpackage  JoomlaModule
 * @since       7.4.0
 */
class Com_ModulesMaintenance
{
	/**
	 * Database connector
	 *
	 * @var    JDatabaseDriver
	 * @since  7.4.0
	 */
	private $db = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  7.4.0
	 */
	private $log = null;

	/**
	 * CSVI Helper.
	 *
	 * @var    CsviHelperCsvi
	 * @since  7.4.0
	 */
	private $csvihelper = null;

	/**
	 * Constructor.
	 *
	 * @param   JDatabase       $db          The database class
	 * @param   CsviHelperLog   $log         The CSVI logger
	 * @param   CsviHelperCsvi  $csvihelper  The CSVI helper
	 *
	 * @since   7.4.0
	 */
	public function __construct($db, $log, $csvihelper)
	{
		$this->db         = $db;
		$this->log        = $log;
		$this->csvihelper = $csvihelper;
	}

	/**
	 * Threshold available fields for extension
	 *
	 * @return  int Hardcoded available fields
	 *
	 * @since   7.4.0
	 */
	public function availableFieldsThresholdLimit()
	{
		return 25;
	}
}
