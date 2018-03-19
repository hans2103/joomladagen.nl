<?php
/**
 * @package     CSVI
 * @subpackage  CSVI helper
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * CSVI helper.
 *
 * @package     CSVI
 * @subpackage  CSVI
 * @since       7.4.0
 */
class Com_CsviHelperCom_Csvi
{
	/**
	 * Template helper
	 *
	 * @var    CsviHelperTemplate
	 * @since  7.4.0
	 */
	protected $template = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  7.4.0
	 */
	protected $log = null;

	/**
	 * Fields helper
	 *
	 * @var    CsviHelperFields
	 * @since  7.4.0
	 */
	protected $fields = null;

	/**
	 * Database connector
	 *
	 * @var    JDatabase
	 * @since  7.4.0
	 */
	protected $db = null;

	/**
	 * Constructor.
	 *
	 * @param   CsviHelperTemplate  $template  An instance of CsviHelperTemplate.
	 * @param   CsviHelperLog       $log       An instance of CsviHelperLog.
	 * @param   CsviHelperFields    $fields    An instance of CsviHelperFields.
	 * @param   JDatabase           $db        Database connector.
	 *
	 * @since   4.0
	 */
	public function __construct(CsviHelperTemplate $template, CsviHelperLog $log, CsviHelperFields $fields, JDatabase $db)
	{
		$this->template = $template;
		$this->log      = $log;
		$this->fields   = $fields;
		$this->db       = $db;
	}

	/**
	 * Delete Rows before import.
	 *
	 * @return  void.
	 *
	 * @since   7.4.0
	 */
	public function truncateTableBeforeImport()
	{
		if ($this->template->get('delete_table_records', false))
		{
			$this->db->truncateTable('#__' . $this->template->get('custom_table', false));
			$this->log->add('Custom table data removed');
		}
	}
}
