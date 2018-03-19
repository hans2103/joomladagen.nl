<?php
/**
 * @package     CSVI
 * @subpackage  Database file
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die();

use Joomla\Utilities\ArrayHelper;

/**
 * CSV database importer.
 *
 * @package     CSVI
 * @subpackage  Database file
 * @since       7.0
 */
class CsviHelperFileImportDatabase extends CsviHelperFile
{
	/**
	 * Contains the data that is read from database table
	 *
	 * @var    JDatabaseIteratorMysqli
	 * @since  7.0
	 */
	private $dataRecords;

	/**
	 * The array pointer position
	 *
	 * @var    int
	 * @since  7.0
	 */
	private $pointer = 0;

	/**
	 * The fields handler
	 *
	 * @var    CsviHelperImportFields
	 * @since  7.0
	 */
	protected $fields;

	/**
	 * The database handler
	 *
	 * @var    JDatabaseDriver
	 * @since  7.0
	 */
	protected $database;

	/**
	 * The query result
	 *
	 * @var    string
	 * @since  7.0
	 */
	protected $queryResult;

	/**
	 * Construct the class and its settings.
	 *
	 * @param   CsviHelperTemplate  $template  An instance of CsviHelperTemplate
	 * @param   CsviHelperLog       $log       An instance of CsviHelperLog
	 * @param   CsviHelperCsvi      $helper    An instance of CsviHelperCsvi
	 * @param   JInput              $input     An instance of JInput
	 *
	 * @since   7.0
	 */
	public function __construct(CsviHelperTemplate $template, CsviHelperLog $log, CsviHelperCsvi $helper, JInput $input)
	{
		$db                  = JFactory::getDbo();
		$details             = array();
		$details['user']     = $template->get('database_username', '', 'string');
		$details['password'] = $template->get('database_password', '', 'string');
		$details['database'] = $template->get('database_name', '', 'string');
		$details['host']     = $template->get('database_host', '', 'string');
		$this->database      = JDatabaseDriver::getInstance($details);
		$this->template      = $template;

		// Initiate the fields helper
		$className    = 'CsviHelper' . ucfirst($template->get('action')) . 'fields';
		$this->fields = new $className($template, $log, $db);

		parent::__construct($template, $log, $helper, $input);
	}

	/**
	 * Open the file to read.
	 *
	 * @return  bool  Return true if file can be opened | False if file cannot be opened.
	 *
	 * @since   7.0
	 *
	 * @throws  CsviException
	 */
	public function openFile()
	{
		if (!$this->database->connected())
		{
			try
			{
				$this->database->connect();
				$this->closed = false;
			}
			catch (Exception $e)
			{
				$this->log->addStats('incorrect', JText::_('COM_CSVI_ERROR_DATABASE_CONNECTION'));
				throw new CsviException(JText::_('COM_CSVI_ERROR_DATABASE_CONNECTION'), 'error');
			}
		}

		return true;
	}

	/**
	 * Close the database connection.
	 *
	 * @param   bool  $removeFolder  Specify if the temporary folder should be removed
	 *
	 * @return  void.
	 *
	 * @since   7.0
	 */
	public function closeFile($removeFolder = true)
	{
		$this->database->disconnect();
		$this->closed = true;
	}

	/**
	 * Get the file position.
	 *
	 * @return  int  The current position in the file.
	 *
	 * @since   7.0
	 */
	public function getFilePos()
	{
		return $this->pointer;
	}

	/**
	 * Set the file position.
	 *
	 * @param   int  $position  The position to move to
	 *
	 * @return  int   0 if success | -1 if not success.
	 *
	 * @since   7.0
	 */
	public function setFilePos($position)
	{
		if (!$this->database->connected())
		{
			$this->openFile();
		}

		$result = current($this->dataRecords);

		if (!$result)
		{
			$this->pointer = $position;
		}

		return $result;
	}

	/**
	 * Load the column headers.
	 *
	 * @return   mixed    array when column headers are found | false if column headers cannot be read.
	 *
	 * @since   7.0
	 *
	 * @throws  UnexpectedValueException
	 */
	public function loadColumnHeaders()
	{
		$tableName     = $this->template->get('database_table', '', 'string');
		$tableColumns  = $this->database->getTableColumns($tableName);
		$columnHeaders = array();
		$index         = 1;

		foreach ($tableColumns as $tableColumn => $fieldType)
		{
			$columnHeaders[$index++] = $tableColumn;
		}

		return $columnHeaders;
	}

	/**
	 * Read the next line in the database records.
	 *
	 * @param   bool  $headers  Set if the column headers are being read.
	 *
	 * @return  mixed  Array with the line of data read | false if data cannot be read.
	 *
	 * @since   7.0
	 *
	 * @throws  CsviException
	 * @throws  UnexpectedValueException
	 */
	public function readNextLine($headers = false)
	{
		// Load the database iterator if needed
		$this->openDatabase();

		// Make sure we have a connection
		if (!$this->dataRecords)
		{
			throw new CsviException(JText::_('COM_CSVI_NO_DATABASE_RECORDS_FOUND'));
		}

		// Get the next record
		$dataCurrentRecords = $this->dataRecords->current();

		// Check if there is any more data to process
		if (!$dataCurrentRecords)
		{
			return false;
		}

		$counters = array();

		$columnHeaders = array_flip($this->fields->getAllFieldnames());

		if (!empty($dataCurrentRecords))
		{
			foreach ($dataCurrentRecords as $key => $value)
			{
				if (isset($columnHeaders[$key]))
				{
					if (!isset($counters[$key]))
					{
						$counters[$key] = 0;
					}

					$counters[$key]++;

					$this->fields->set($key, $value, $counters[$key]);
				}
			}
		}

		// Move to the next record
		$this->dataRecords->next();

		return $columnHeaders;

	}

	/**
	 * Process the database records to import.
	 *
	 * @return  bool  True if file can be processed | False if file cannot be processed.
	 *
	 * @since   7.0
	 *
	 * @throws  CsviException
	 */
	public function processFile()
	{
		if (!$this->openFile())
		{
			return false;
		}

		return true;
	}

	/**
	 * Sets the file pointer back to beginning of array.
	 *
	 * Since there is no rewind available we start all over.
	 *
	 * @return  void.
	 *
	 * @since   7.0
	 */
	public function rewind()
	{
		$this->processFile();
	}

	/**
	 * Return the number of records in a database
	 *
	 * @return  int  The number of records in database table
	 *
	 * @since   7.0
	 */
	public function lineCount()
	{
		return $this->dataRecords->count();
	}

	/**
	 * Setup the database iterator.
	 *
	 * @return  void
	 *
	 * @since   7.0
	 *
	 * @throws  CsviException
	 */
	private function openDatabase()
	{
		if (!$this->closed && !$this->dataRecords)
		{
			$selectFields     = $this->fields->getFieldNames(true);
			$tableName        = $this->template->get('database_table', '', 'string');
			$tableColumns     = $this->database->getTableColumns($tableName, false);
			$tableColumnNames = ArrayHelper::getColumn(json_decode(json_encode($tableColumns), true), 'Field');

			// Get the fields not found in the database table
			$resultNames  = array_diff($selectFields, $tableColumnNames);

			// Remove the unsupported fields from the field list
			$selectFields = array_diff($selectFields, $resultNames);

			// Build the select query to get the data
			$query = $this->database->getQuery(true)
				->select($this->database->quoteName($selectFields))
				->from($this->database->quoteName($tableName));

			// Set the query
			$this->database->setQuery($query);

			// Load the database iterator
			$this->dataRecords = $this->database->getIterator();
		}
	}
}
