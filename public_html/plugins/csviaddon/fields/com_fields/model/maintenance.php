<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaFields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Joomla! Custom Fields maintenance.
 *
 * @package     CSVI
 * @subpackage  JoomlaFields
 * @since       7.2.0
 */
class Com_FieldsMaintenance
{
	/**
	 * Database connector
	 *
	 * @var    JDatabaseDriver
	 * @since  7.2.0
	 */
	private $db = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  7.2.0
	 */
	private $log = null;

	/**
	 * CSVI Helper.
	 *
	 * @var    CsviHelperCsvi
	 * @since  7.2.0
	 */
	private $csvihelper = null;

	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver  $db          The database class
	 * @param   CsviHelperLog    $log         The CSVI logger
	 * @param   CsviHelperCsvi   $csvihelper  The CSVI helper
	 * @param   bool             $isCli       Set if we are running CLI mode
	 *
	 * @since   7.2.0
	 */
	public function __construct(JDatabaseDriver $db, CsviHelperLog $log, CsviHelperCsvi $csvihelper, $isCli = false)
	{
		$this->db         = $db;
		$this->log        = $log;
		$this->csvihelper = $csvihelper;
		$this->isCli      = $isCli;
	}

	/**
	 * Threshold available fields for extension
	 *
	 * @return  int Hardcoded available fields
	 *
	 * @since   7.2.0
	 */
	public function availableFieldsThresholdLimit()
	{
		return 60;
	}
}
