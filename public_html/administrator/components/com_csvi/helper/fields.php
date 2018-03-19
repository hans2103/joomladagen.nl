<?php
/**
 * @package     CSVI
 * @subpackage  Helper.Fields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * The CsviFields class handles all import/export field operations.
 *
 * @package     CSVI
 * @subpackage  Helper.Fields
 * @since       6.0
 */
class CsviHelperFields
{
	/**
	 * Contains all the fields and their data
	 *
	 * @var    array
	 * @since  6.0
	 */
	protected $fields = array();

	/**
	 * Holds the template
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	protected $template = null;

	/**
	 * Holds the logger
	 *
	 * @var    CsviHelperLog
	 * @since  6.0
	 */
	protected $log = null;

	/**
	 * Holds the file handler
	 *
	 * @var    CsviHelperFile
	 * @since  6.0
	 */
	protected $file = null;

	/**
	 * Holds the database connector
	 *
	 * @var    JDatabaseDriver
	 * @since  6.0
	 */
	protected $db = null;

	/**
	 * Array of supported fields
	 *
	 * @var    array
	 * @since  6.0
	 */
	protected $supportedFields = null;

	/**
	 * Check if needs to process record
	 *
	 * @var    bool
	 * @since  7.0
	 */
	private $processRecord = true;

	/**
	 * Constructor.
	 *
	 * @param   CsviHelperTemplate  $template  An instance of CsviHelperTemplate
	 * @param   CsviHelperLog       $log       An instance of CsviHelperLog
	 * @param   JDatabaseDriver     $db        An instance of JDatabaseDriver
	 *
	 * @since   4.6
	 */
	public function __construct(CsviHelperTemplate $template, CsviHelperLog $log, JDatabaseDriver $db)
	{
		// Set the parameters
		$this->template = $template;
		$this->log = $log;
		$this->db = $db;

		// Load the supported fields
		$this->loadSupportedFields();
	}

	/**
	 * Set the file helper.
	 *
	 * @param   CsviHelperFile  $file  An instance of CsviHelperFile
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function setFile($file)
	{
		$this->file = $file;
	}

	/**
	 * Method to get all the fields.
	 *
	 * @return  array  The list of field objects.
	 *
	 * @since   5.0
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * Create an array of supported fields.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	private function loadSupportedFields()
	{
		$component = $this->template->get('component');
		$operation = $this->template->get('operation');
		$action = $this->template->get('action');

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('a.csvi_name'))
			->from($this->db->quoteName('#__csvi_availablefields', 'a'))
			->join('left',
						$this->db->quoteName('#__csvi_availabletables', 't') . ' ON ' .
							$this->db->quoteName('t.template_table') . ' = ' . $this->db->quoteName('a.component_table')
				)
			->where($this->db->quoteName('a.component') . ' = ' . $this->db->quote($component))
			->where($this->db->quoteName('t.component') . ' = ' . $this->db->quote($component))
			->where($this->db->quoteName('t.task_name') . ' = ' . $this->db->quote($operation))
			->where($this->db->quoteName('a.action') . ' = ' . $this->db->quote($action))
			->where($this->db->quoteName('t.action') . ' = ' . $this->db->quote($action));

		$this->db->setQuery($query);

		$this->supportedFields = $this->db->loadColumn();

		$this->log->add(sprintf('Loading the supported fields for %s, operation %s and action %s', $component, $operation, $action));
	}

	/**
	 * Check if the field already exists.
	 *
	 * @param   string  $field  The name of the field to check.
	 *
	 * @return  bool  True if field exists | False if field does not exist.
	 *
	 * @since   6.1.1
	 */
	public function isFieldAvailable($field)
	{
		return in_array($field, $this->supportedFields);
	}

	/**
	 * Set if the record needs to be processed
	 *
	 * @param   bool  $value  True or False
	 *
	 * @return  void
	 *
	 * @since   7.0
	 */
	public function setProcessRecord($value)
	{
		$this->processRecord = $value;
	}

	/**
	 * Get state if the record needs to be processed
	 *
	 * @return  bool true if record has to be processed false otherwise
	 *
	 * @since   7.0
	 */
	public function getProcessRecord()
	{
		return $this->processRecord;
	}
}
