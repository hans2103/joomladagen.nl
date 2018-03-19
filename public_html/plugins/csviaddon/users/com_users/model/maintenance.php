<?php
/**
 * @package     CSVI
 * @subpackage  Users
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Users maintenance.
 *
 * @package     CSVI
 * @subpackage  Users
 * @since       7.0
 */
class Com_usersMaintenance
{
	/**
	 * Database connector
	 *
	 * @var    JDatabaseDriver
	 * @since  7.0
	 */
	private $db = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  7.0
	 */
	private $log = null;

	/**
	 * CSVI Helper.
	 *
	 * @var    CsviHelperCsvi
	 * @since  7.0
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
	 * @since   7.0
	 */
	public function __construct(JDatabaseDriver $db, CsviHelperLog $log, CsviHelperCsvi $csvihelper, $isCli = false)
	{
		$this->db         = $db;
		$this->log        = $log;
		$this->csvihelper = $csvihelper;
		$this->isCli      = $isCli;
	}

	/**
	 * Update Custom available fields that require extra processing.
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 */
	public function customAvailableFields()
	{
		if (JComponentHelper::isEnabled('com_fields'))
		{
			// Update Joomla custom fields
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('name'))
				->from($this->db->quoteName('#__fields'))
				->where($this->db->quoteName('state') . ' = 1')
				->where($this->db->quoteName('context') . ' = ' . $this->db->quote('com_users.user'));
			$this->db->setQuery($query);

			$customFields = $this->db->loadRowList();

			if ($customFields)
			{
				$query->clear()
					->insert($this->db->quoteName('#__csvi_availablefields'))
					->columns($this->db->quoteName(array('csvi_name', 'component_name', 'component_table', 'component', 'action')));

				foreach ($customFields as $cfield)
				{
					$query->values(
						$this->db->quote($cfield[0]) . ',' .
						$this->db->quote($cfield[0]) . ',' .
						$this->db->quote('user') . ',' .
						$this->db->quote('com_users') . ',' .
						$this->db->quote('import')
					);
					$query->values(
						$this->db->quote($cfield[0]) . ',' .
						$this->db->quote($cfield[0]) . ',' .
						$this->db->quote('user') . ',' .
						$this->db->quote('com_users') . ',' .
						$this->db->quote('export')
					);
				}

				$this->db->setQuery($query)->execute();
			}
		}
	}

	/**
	 * Threshold available fields for extension
	 *
	 * @return  int Hardcoded available fields
	 *
	 * @since   7.0
	 */
	public function availableFieldsThresholdLimit()
	{
		return 33;
	}
}
