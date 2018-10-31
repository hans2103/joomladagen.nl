<?php
/**
 * @package     CSVI
 * @subpackage  Export
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use phpseclib\Net\SFTP;

/**
 * Export model.
 *
 * @package     CSVI
 * @subpackage  Export
 * @since       6.0
 */
class CsviModelExports extends CsviModelDefault
{
	/**
	 * The export file handler
	 *
	 * @var    object
	 * @since  6.0
	 */
	protected $exportclass;

	/**
	 * The file handler for writing the export file
	 *
	 * @var    resource
	 * @since  6.0
	 */
	private $handle;

	/**
	 * Set the export format being used
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $exportFormat;

	/**
	 * Set the array of export formats that use nodes
	 *
	 * @var    array
	 * @since  6.0
	 */
	protected $nodeformats = array('xml', 'html');

	/**
	 * CSVI SEF processor
	 *
	 * @var    CsviHelperSef
	 * @since  6.0
	 */
	protected $sef;

	/**
	 * Contents to be exported
	 *
	 * @var    array
	 * @since  3.0
	 */
	private $contents = array();

	/**
	 * Export fields helper
	 *
	 * @var    CsviHelperExportFields
	 * @since  6.0
	 */
	protected $fields;

	/**
	 * Export  data
	 *
	 * @var    array
	 * @since  6.7.0
	 */
	protected $exportData;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  The configuration array.
	 *
	 * @since   6.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config = array());

		// Load Joomla helpers
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
	}

	/**
	 * Initialise the needed classes, it all starts with the template ID
	 *
	 * @param   int  $template_id  The ID of the template to load
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 * @throws  CsviException
	 */
	public function initialise($template_id)
	{
		// Check the temporary folder
		$this->checkTmpFolder();

		// Load the CSVI language files
		$this->loadLanguageFiles();

		// Load the template
		$this->loadTemplate($template_id);

		// Set the export format
		$this->setExportFormat();

		// Generate the filename to create
		$this->exportFilename();

		// Initialise run
		$runId = $this->initialiseRun();

		// Prepare for export
		$this->initialiseExport($runId);
	}

	/**
	 * Initialise the export.
	 *
	 * @param   int  $csvi_process_id  The ID of the import run
	 *
	 * @return  bool  Always returns true.
	 *
	 * @since   6.0
	 *
	 * @throws  CsviException
	 */
	public function initialiseExport($csvi_process_id)
	{
		parent::initialiseExport($csvi_process_id);

		// Load the log
		$this->initialiseLog();

		// Load the SEF helper
		$this->sef = new CsviHelperSef($this->settings, $this->template, $this->log);

		return true;
	}

	/**
	 * Set the log basics.
	 *
	 * @return  bool  Always returns true.
	 *
	 * @since   6.0
	 */
	public function initialiseLog()
	{
		$this->log->setAction('export');

		return parent::initialiseLog();
	}

	/**
	 * Set the export format.
	 *
	 * @return  void.
	 *
	 * @since   7.0.1
	 */
	public function setExportFormat()
	{
		$this->exportFormat = $this->template->get('export_file', 'csv');
	}

	/**
	 * Load the export file handler.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	protected function loadExportFile()
	{
		// Set the export format
		$this->setExportFormat();

		// Get the export site
		switch ($this->exportFormat)
		{
			case 'xml':
			case 'html':
				$exportsite = $this->template->get('export_site', 'csvimproved');
				break;
			default:
				$exportsite = 'csvimproved';
				break;
		}

		// Construct the class name
		$classname = 'CsviHelperFileExport' . ucfirst($this->exportFormat) . ucfirst($exportsite);

		// Instantiate the new export class
		$this->exportclass = new $classname($this->template);
	}

	/**
	 * Get the template ID.
	 *
	 * @param   int  $csvi_process_id  The ID of the export run
	 *
	 * @return  int  The template ID.
	 *
	 * @since   6.0
	 *
	 * @throws  RuntimeException
	 */
	public function getTemplateId($csvi_process_id)
	{
		// Load the run details
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('csvi_template_id'))
			->from($this->db->quoteName('#__csvi_processes'))
			->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $csvi_process_id);
		$this->db->setQuery($query);
		$templateId = $this->db->loadResult();

		if ($templateId)
		{
			return $templateId;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Returns a list of items
	 *
	 * @param   boolean  $overrideLimits  Should I override set limits?
	 * @param   string   $group           The group by clause
	 *
	 * @return  array  of items
	 *
	 * @since   6.0
	 */
	public function &getItemList($overrideLimits = false, $group = '')
	{
		$this->list = array();

		return $this->list;
	}

	/**
	 * Get the number of all items.
	 *
	 * This is always 0 as we don't have a traditional list.
	 *
	 * @return  integer  The number of records.
	 *
	 * @since   6.0
	 */
	public function getTotal()
	{
		$this->total = 0;

		return $this->total;
	}

	/**
	 * Prepare for export.
	 *
	 * @param   string  $addon  The addon to run the export for
	 *
	 * @return  bool  True if all is OK | False if something is not OK.
	 *
	 * @since   3.0
	 *
	 * @throws  RuntimeException
	 */
	public function onBeforeExport($addon)
	{
		// Setup the addon
		$this->initialiseAddon($addon);

		// Load the fields to export
		$this->retrieveConfigFields();

		if (!empty($this->fields))
		{
			// Allow big SQL selects
			$this->db->setQuery('SET SQL_BIG_SELECTS=1')->execute();

			// Get the filename for the export file
			$this->exportFilename();
		}
		else
		{
			$this->log->addStats('incorrect', 'COM_CSVI_NO_EXPORT_FIELDS');
			throw new RuntimeException(JText::_('COM_CSVI_NO_EXPORT_FIELDS'), 500);
		}

		// Load component language files
		$language = new CsviHelperLanguage;
		$language->loadAddonLanguage($addon);

		// All is good
		return true;
	}

	/**
	 * Run the export.
	 *
	 * @return  bool  True if export started | False if export cannot be started.
	 *
	 * @since   6.0
	 *
	 * @throws  \Exception
	 * @throws  \CsviException
	 * @throws  \RuntimeException
	 * @throws  \UnexpectedValueException
	 */
	final public function runExport()
	{
		// Set the system limits to the user settings if needed
		$this->systemLimits();

		// Start export
		if ($this->startExport())
		{
			// Set the last run time for the template
			$templateTable = $this->getTable('Template', 'Table');
			$templateTable->load($this->template->getId());
			$templateTable->set('lastrun', JFactory::getDate()->toSql());
			$templateTable->store();

			// Export header
			$this->exportHeader();

			// Export body
			$this->exportBody();

			// Export footer
			$this->exportFooter();

			// Finalize export
			$this->endExport();

			// Finish processing
			$this->finishProcess(true);

			return true;
		}
		else
		{
			$this->log->addStats('incorrect', 'COM_CSVI_CANNOT_START_EXPORT');

			// Finish processing
			$this->finishProcess(true);

			return false;
		}
	}

	/**
	 * Start the export.
	 *
	 * @return  bool  True if export started | False if export cannot be started.
	 *
	 * @since   6.0
	 */
	protected function startExport()
	{
		// Write out some export settings
		if ($this->template->getLog())
		{
			$this->exportDetails();
		}

		// Create the temporary export file
		$error = false;

		// Check if the folder exists
		if (!JFolder::exists(dirname($this->processfile)))
		{
			if (!JFolder::create(dirname($this->processfile)))
			{
				$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_CANNOT_CREATE_FOLDER', dirname($this->processfile)));

				$error = true;
			}
		}

		// Open the file for writing
		$this->handle = fopen($this->processfile, 'w+');

		if (!$this->handle)
		{
			$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_CANNOT_OPEN_FILE', $this->processfile));

			$error = true;
		}
		else
		{
			// Let's make sure the file exists and is writable first.
			if (!is_writable($this->processfile))
			{
				$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_CANNOT_WRITE_FILE', $this->processfile));

				$error = true;
			}
		}

		// Start the export
		if ($error)
		{
			// Store the log results
			$this->finishProcess(true);

			return false;
		}
		else
		{
			// All good
			return true;
		}
	}

	/**
	 * Export header.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	protected function exportHeader()
	{
		// Add signature for Excel
		if ($this->template->get('signature'))
		{
			$this->contents['signature'] = "\xEF\xBB\xBF";
		}

		// Add the header
		$exportfields = $this->fields->getFieldNames();
		$headerline = $this->exportclass->headerText($exportfields);

		if (!empty($headerline))
		{
			$this->contents[] = $headerline;
		}

		// Add header for XML
		if ($this->exportFormat == 'xml')
		{
		}
		// Add header for HTML
		elseif ($this->exportFormat == 'html')
		{
			$this->contents[] = $this->exportclass->bodyText();
		}

		// Write out the data
		$this->writeOutput();
	}

	/**
	 * Export the body contents.
	 *
	 * @return  bool  True if body is exported | False if body is not exported.
	 *
	 * @since   6.0
	 *
	 * @throws  CsviException
	 */
	protected function exportBody()
	{
		$exportfields = $this->fields->getFields();

		if (count($exportfields) == 0)
		{
			throw new CsviException(JText::_('COM_CSVI_NO_EXPORT_FIELDS_SET'), 517);
		}

		return true;
	}

	/**
	 * Export the footer.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	protected function exportFooter()
	{
		// Get the footer from the export class
		$footer = $this->exportclass->footerText();

		// Write the footer
		if ($footer && !empty($footer))
		{
			$this->contents[] = $footer;
			$this->writeOutput();
		}
	}

	/**
	 * Close the output.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 *
	 * @throws  \Exception
	 */
	protected function endExport()
	{
		// Close the temporary file
		fclose($this->handle);

		// Only export the data if we are on the admin side of things, the front side controls this in the controller
		if (JFactory::getApplication()->isAdmin())
		{
			// Get output destinations
			$destinations = $this->template->get('exportto', 'todownload');

			// Set not to keep the temporary file
			$keep = false;

			// Delete any empty file if needed
			$this->deleteEmptyFile($this->processfile);

			if (JFile::exists($this->processfile))
			{
				if (!is_array($destinations))
				{
					$destinations = array($destinations);
				}

				// Check output destinations
				foreach ($destinations as $destination)
				{
					switch ($destination)
					{
						case 'todownload':
							break;
						case 'tofile':
							$keep = $this->writeFile($this->processfile);
							break;
						case 'toftp':
							$this->ftpFile($this->processfile);
							break;
						case 'toemail':
							$this->emailFile($this->processfile);
							break;
						case 'todatabase':
							$this->exportToDatabase();
							break;
					}
				}
			}

			// Remove the temporary file if needed
			if (!$keep && !in_array('todownload', $destinations, true))
			{
				JFile::delete($this->processfile);
			}
		}

		// Store the filename for the log
		$query = $this->db->getQuery(true);
		$query->update($this->db->quoteName('#__csvi_logs'))
			->set($this->db->quoteName('file_name') . ' = ' . $this->db->quote(basename($this->processfile)))
			->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $this->getLogId());
		$this->db->setQuery($query)->execute();
	}

	/**
	 * Write the output to download or to file.
	 *
	 * @param   bool  $emptyLine  Should the output be followed by an empty line.
	 *
	 * @return  bool  True if data is output | False if data is not output.
	 *
	 * @since   3.0
	 */
	protected function writeOutput($emptyLine = false)
	{
		// Let's take the local contents if nothing is supplied
		$contents = $this->contents;

		// Clean the local contents
		$this->contents = array();

		if (!empty($contents))
		{
			if (!is_array($contents))
			{
				$contents = (array) $contents;
			}

			// The content to write
			$writedata = '';

			// Check if there is a signature
			if (isset($contents['signature']))
			{
				$writedata = $contents['signature'];
				unset($contents['signature']);
			}

			// Prepare the data for writing
			$writedata .= $this->exportclass->prepareContent($contents);

			if ($emptyLine)
			{
				$writedata = "\r\n" . $writedata;
			}

			$this->exportData[] = $writedata;

			// Write the data to file
			if (fwrite($this->handle, $writedata . "\r\n") === false)
			{
				$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_CANNOT_WRITE_FILE', $this->processfile));

				return false;
			}
		}

		return true;
	}

	/**
	 * Constructs a limit for a query.
	 *
	 * @return  array  The limits to apply to the query.
	 *
	 * @since   3.0
	 */
	protected function getExportLimit()
	{
		$recordstart = $this->template->get('recordstart', 0, 'int');
		$recordend = $this->template->get('recordend', 0, 'int');
		$limits = array();
		$limits['offset'] = 0;
		$limits['limit'] = 0;

		// Check if the user only wants to export some products
		if ($recordstart && $recordend)
		{
			// Check if both values are greater than 0
			if (($recordstart > 0) && ($recordend > 0))
			{
				// We have valid limiters, add the limit to the query
				// Recordend needs to have 1 deducted because MySQL starts from 0
				$limits['offset'] = $recordstart - 1;
				$limits['limit'] = $recordend - $recordstart;
			}
		}

		return $limits;
	}

	/**
	 * Process an array of data to add to the output.
	 *
	 * @param   boolean  $addXml  Set if the XML tags need to be added.
	 *
	 * @return  void.
	 *
	 * @since   5.0
	 */
	protected function addExportFields($addXml = true)
	{
		// Initialise record processing to true
		$this->fields->setProcessRecord(true);

		// Fire the rules
		$this->fields->runRules();

		// Create a clean row
		$row = array();
		$nodestart = false;
		$linecount = 0;

		// Add the start node
		if ($addXml && in_array($this->exportFormat, $this->nodeformats))
		{
			$nodestart = $this->exportclass->NodeStart();
		}

		// Add all fields to the export
		foreach ($this->fields->getData() as $data)
		{
			// Get the field details
			$field = reset($data);

			if ($field->enabled)
			{
				if (strlen($field->value) > 0)
				{
					$value = $field->value;
				}
				else
				{
					$value = $field->default_value;
				}

				if (strlen($value) > 0)
				{
					$linecount++;
				}

				// Format the contents
				$row[] = $this->exportclass->contentText($value, $field->column_header, $field->field_name, $field->cdata);
			}
		}

		if ($linecount > 0)
		{
			if ($addXml && $nodestart)
			{
				$this->contents[] = $nodestart;
			}

			// Add the content
			$this->contents = array_merge($this->contents, $row);

			if ($addXml)
			{
				// Add the end node
				if (in_array($this->exportFormat, $this->nodeformats))
				{
					$this->contents[] = $this->exportclass->NodeEnd();
				}
			}
		}

		// All fields added for export, empty the fields
		$this->fields->reset();
	}

	/**
	 * Add data to the export content.
	 *
	 * @param   string  $content  The content to export
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	protected function addExportContent($content)
	{
		$this->contents[] = $content;
	}

	/**
	 * Setup the fields to export.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	private function retrieveConfigFields()
	{
		$query = $this->db->getQuery(true)
			->select(
				array(
					$this->db->quoteName('f.csvi_templatefield_id'),
					$this->db->quoteName('f.field_name'),
					$this->db->quoteName('f.column_header'),
					$this->db->quoteName('f.table_name'),
					$this->db->quoteName('f.xml_node'),
					$this->db->quoteName('f.default_value'),
					$this->db->quoteName('f.field_date_format'),
					$this->db->quoteName('f.enabled'),
					$this->db->quoteName('f.sort'),
					$this->db->quoteName('f.ordering'),
					$this->db->quoteName('f.cdata')
				)
			)
			->from($this->db->quoteName('#__csvi_templatefields', 'f'))
			->where($this->db->quoteName('f.csvi_template_id') . ' = ' . (int) $this->template->getId())
			->order($this->db->quoteName('f.ordering'));
		$this->db->setQuery($query);

		// Add the fields
		foreach ($this->db->loadAssocList() as $field)
		{
			/**
			 * Setup the XML fields, we mimic them for none XML files
			 */
			if (empty($field['xml_node']))
			{
				$field['xml_node'] = $field['field_name'];
			}

			// Load the associated rules for the field
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('csvi_rule_id'))
				->from($this->db->quoteName('#__csvi_templatefields_rules'))
				->where($this->db->quoteName('csvi_templatefield_id') . ' = ' . (int) $field['csvi_templatefield_id'])
				->order($this->db->quoteName('csvi_templatefields_rule_id'));
			$this->db->setQuery($query);
			$field['rules'] = $this->db->loadColumn();

			// Convert to the field to an JObject
			$data = new JObject($field);

			// Add the field to the field helper
			$this->fields->add($data);
		}
	}

	/**
	 * Create the export filename.
	 *
	 * @return  string  The name of the export file.
	 *
	 * @since   3.0
	 *
	 * @throws  UnexpectedValueException
	 */
	public function exportFilename()
	{
		$fileLimit = '';

		// Check if the export is limited, if so add it to the filename
		if (($this->template->get('recordstart') > 0) && ($this->template->get('recordend') > 0))
		{
			// We have valid limiters, add the limit to the filename
			$fileLimit = '_' . $this->template->get('recordstart') . '_' . ($this->template->get('recordend'));
		}

		// Set the filename to use for export
		$exportFilename = trim($this->template->get('export_filename'));
		$localFile = 'CSVI_' . $this->template->getName() . '_' . date("j-m-Y_H.i") . $fileLimit . '.' . $this->exportFormat;

		// Format the filename
		$exportFilename = $this->formatExportFilename($exportFilename);

		if (!empty($exportFilename))
		{
			$localFile = $exportFilename;
		}

		// Clean up
		$localFile = JPath::clean(CSVIPATH_TMP . '/export/' . $localFile, '/');

		// Set the process filename
		$this->processfile = $localFile;

		// Return the filename
		return $localFile;
	}

	/**
	 * Format a given filename.
	 *
	 * @param   string  $exportFilename  The filename to format.
	 *
	 * @return  string  The formatted filename.
	 *
	 * @since   6.5.7
	 */
	private function formatExportFilename($exportFilename)
	{
		// Do some customizing
		// Replace year
		$exportFilename = str_replace('[Y]', date('Y', time()), $exportFilename);
		$exportFilename = str_replace('[y]', date('y', time()), $exportFilename);

		// Replace month
		$exportFilename = str_replace('[M]', date('M', time()), $exportFilename);
		$exportFilename = str_replace('[m]', date('m', time()), $exportFilename);
		$exportFilename = str_replace('[F]', date('F', time()), $exportFilename);
		$exportFilename = str_replace('[n]', date('n', time()), $exportFilename);

		// Replace day
		$exportFilename = str_replace('[d]', date('d', time()), $exportFilename);
		$exportFilename = str_replace('[D]', date('D', time()), $exportFilename);
		$exportFilename = str_replace('[j]', date('j', time()), $exportFilename);
		$exportFilename = str_replace('[l]', date('l', time()), $exportFilename);

		// Replace hour
		$exportFilename = str_replace('[g]', date('g', time()), $exportFilename);
		$exportFilename = str_replace('[G]', date('G', time()), $exportFilename);
		$exportFilename = str_replace('[h]', date('h', time()), $exportFilename);
		$exportFilename = str_replace('[H]', date('H', time()), $exportFilename);

		// Replace minute
		$exportFilename = str_replace('[i]', date('i', time()), $exportFilename);

		// Replace seconds
		$exportFilename = str_replace('[s]', date('s', time()), $exportFilename);

		return $exportFilename;
	}

	/**
	 * Print out export details to the debug log.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	private function exportDetails()
	{
		$this->log->add(JText::_('COM_CSVI_CSVI_VERSION_TEXT') . JText::_('COM_CSVI_CSVI_VERSION'), false);
		$this->log->add(JText::sprintf('COM_CSVI_JOOMLA_VERSION', JVERSION), false);

		if (function_exists('phpversion'))
		{
			$this->log->add(JText::sprintf('COM_CSVI_PHP_VERSION', phpversion()), false);
		}

		// Push out all settings
		$this->log->add(str_repeat('=', 25), false);
		$settings = $this->template->getSettings();
		$this->processSettings($settings);
		$this->log->add(str_repeat('=', 25), false);

		// Push out all fields
		foreach ($this->fields->getFieldnames() as $fieldname)
		{
			$this->log->add('Export field: ' . $fieldname, false);
		}
	}

	/**
	 * Add all the settings to the debug log.
	 *
	 * @param   array  $data  An array of template settings to print
	 *
	 * @return  void.
	 *
	 * @since   5.3
	 */
	private function processSettings($data)
	{
		foreach ($data as $name => $value)
		{
			$name = (string) $name;

			switch ($name)
			{
				default:
					if (is_object($value) || is_array($value))
					{
						$this->processSettings($value);
					}
					else
					{
						switch ($name)
						{
							case 'ftpusername':
							case 'ftppass':
							case 'export_email_addresses':
							case 'export_email_addresses_cc':
							case 'export_email_addresses_bcc':
							case 'database_password':
								break;
							default:
								switch ($value)
								{
									case '0':
										$value = JText::_('JNO');
										break;
									case '1':
										$value = JText::_('JYES');
										break;
								}

								$this->log->add($name . ': ' . $value, false);
								break;
						}
					}
					break;
			}
		}
	}

	/**
	 * Handle the end of the import.
	 *
	 * @param   bool  $finished  Set if the import is finished or not.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	private function finishProcess($finished = false)
	{
		// Check if the import is finished or if we are going to reload
		if ($finished)
		{
			// Remove the running process
			$query = $this->db->getQuery(true)
				->delete($this->db->quoteName('#__csvi_processes'))
				->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $this->runId);
			$this->db->setQuery($query)->execute();

			// Set the log end timestamp
			$date = new JDate(date('Y-m-d H:i:s', time()));
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__csvi_logs'))
				->set($this->db->quoteName('end') . ' = ' . $this->db->quote($date->toSql()))
				->set($this->db->quoteName('records') . ' = ' . (int) $this->log->getLinenumber())
				->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $this->log->getLogId());
			$this->db->setQuery($query)->execute();

			// Trigger any plugins to run after import completes
			$options = array();
			$options[] = $this->template->getSettings();
			$dispatcher = new RantaiPluginDispatcher;
			$dispatcher->importPlugins('csvi', $this->db);
			$dispatcher->trigger('onExportComplete', $options);
		}
	}

	/**
	 * Get the number of records exported.
	 *
	 * @return  int  The number of records exported.
	 *
	 * @since   6.0
	 */
	public function getRecords()
	{
		return $this->log->getLinenumber();
	}

	/**
	 * Get the download URL if file needs to be downloaded.
	 *
	 * @return  string  The URL of the download file.
	 *
	 * @since   6.0
	 */
	public function getDownloadUrl()
	{
		// Get output destinations
		$destinations = $this->template->get('exportto', 'todownload');

		if (!is_array($destinations))
		{
			$destinations = array($destinations);
		}

		// Generate the URL
		$url = '';

		if (in_array('todownload', $destinations))
		{
			$url = JUri::root() . 'administrator/index.php?option=com_csvi&task=exports.downloadfile&tmpl=component&file=' . base64_encode($this->processfile);
		}

		return $url;
	}

	/**
	 * Download a generated file.
	 *
	 * @param   string  $downloadFile  The name of the file to download
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function downloadFile($downloadFile)
	{
		// Load the file class
		jimport('joomla.filesystem.file');

		// Check if the file exists
		if (JFile::exists($downloadFile))
		{
			if (preg_match('/Opera(\/| )([0-9].[0-9]{1,2})/', $_SERVER['HTTP_USER_AGENT']))
			{
				$UserBrowser = "Opera";
			}
			elseif (preg_match('/MSIE ([0-9].[0-9]{1,2})/', $_SERVER['HTTP_USER_AGENT']))
			{
				$UserBrowser = 'IE';
			}
			else
			{
				$UserBrowser = '';
			}

			$mime_type = ($UserBrowser === 'IE' || $UserBrowser === 'Opera') ? 'application/octetstream' : 'application/octet-stream';

			// Clean the buffer
			ob_end_clean();

			header('Content-Type: ' . $mime_type);
			header('Content-Encoding: UTF-8');
			header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Content-Length: ' . filesize($downloadFile));

			if ($UserBrowser === 'IE')
			{
				header('Content-Disposition: inline; filename="' . basename($downloadFile) . '"');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
			}
			else
			{
				header('Content-Disposition: attachment; filename="' . basename($downloadFile) . '"');
			}

			// Output the file
			readfile($downloadFile);

			// Delete the file
			@unlink($downloadFile);
		}
	}

	/**
	 * Display a generated file.
	 *
	 * @param   string  $downloadFile  The name of the file to download
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function displayFile($downloadFile)
	{
		// Load the file class
		jimport('joomla.filesystem.file');

		// Check if the file exists
		if (JFile::exists($downloadFile))
		{
			// Find out which header we need to send
			$extension = JFile::getExt($downloadFile);

			switch ($extension)
			{
				case 'xml':
					$contentType = 'application/xml';
					break;
				case 'html':
				case 'tsv':
				case 'csv':
				default:
					// We always use this content type because it otherwise downloads the file
					$contentType = 'text/html';
					break;
			}

			// Clean the buffer
			ob_end_clean();

			// Send the correct headers
			header('Content-Type: ' . $contentType);
			header('Content-Disposition: inline; filename="' . basename($downloadFile) . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($downloadFile));

			// Output the file
			readfile($downloadFile);
		}
	}

	/**
	 * Write a file to the server.
	 *
	 * @param   string  $downloadFile  The full path and name of the file to email.
	 *
	 * @return  bool  True if the file needs to be kept | False if file can be deleted.
	 *
	 * @since   6.5.6
	 */
	public function writeFile($downloadFile)
	{
		$keep = false;

		// Copy the file to the correct location
		$destinationFile = $this->template->get('localpath', JPATH_SITE) . '/' . basename($downloadFile);

		if ($this->processfile === $destinationFile)
		{
			$keep = true;
		}

		if ($this->processfile === $destinationFile || JFile::copy($this->processfile, $destinationFile))
		{
			$this->log->setFilename($destinationFile);
			$this->log->addStats('information', JText::sprintf('COM_CSVI_EXPORTFILE_CREATED', $destinationFile));
		}
		else
		{
			$this->log->addStats('error', JText::sprintf('COM_CSVI_EXPORTFILE_NOT_CREATED', $destinationFile));
		}

		return $keep;
	}

	/**
	 * Email a generated file.
	 *
	 * @param   string  $downloadFile  The full path and name of the file to email.
	 *
	 * @return  bool   True on success | False on failure.
	 *
	 * @since   6.5.0
	 *
	 * @throws  \Exception
	 */
	public function emailFile($downloadFile)
	{
		// Load the mailer
		$app = JFactory::getApplication();
		jimport('joomla.mail.helper');

		// Get the mailer
		$mailer           = JFactory::getMailer();
		$mailer->From     = $app->get('mailfrom');
		$mailer->FromName = $app->get('sitename');
		$result           = true;
		$mailer->isHtml(true);

		try
		{
			$mailer->addReplyTo($app->get('mailfrom'), $app->get('sitename'));
		}
		catch (Exception $e)
		{
			$this->log->add($e->getMessage());
			$result = false;
		}

		// Add the email address
		$addresses = explode(',', $this->template->get('export_email_addresses'));

		// Addresses
		foreach ($addresses as $address)
		{
			try
			{
				$mailer->addAddress($address);
			}
			catch (Exception $e)
			{
				$this->log->add($e->getMessage());
				$result = false;
			}
		}

		// Carbon copy addresses
		$addresses_cc = explode(',', $this->template->get('export_email_addresses_cc'));

		if ($addresses_cc)
		{
			foreach ($addresses_cc as $address)
			{
				try
				{
					$mailer->addCc($address);
				}
				catch (Exception $e)
				{
					$this->log->add($e->getMessage());
					$result = false;
				}
			}
		}

		// Blind carbon copy addresses
		$addresses_bcc = explode(',', $this->template->get('export_email_addresses_bcc'));

		if ($addresses_bcc)
		{
			foreach ($addresses_bcc as $address)
			{
				try
				{
					$mailer->addBcc($address);
				}
				catch (Exception $e)
				{
					$this->log->add($e->getMessage());
					$result = false;
				}
			}
		}

		// Set the body text
		$htmlmsg = '<html><body>' . $this->template->get('export_email_body') . '</body></html>';
		$mailer->setBody($htmlmsg);

		// Set the subject
		$mailer->setSubject($this->template->get('export_email_subject'));

		// Add the attachment
		try
		{
			$mailer->addAttachment($downloadFile);
		}
		catch (Exception $e)
		{
			$this->log->add($e->getMessage());
			$result = false;
		}

		// Send the mail
		try
		{
			$sendmail = $mailer->Send();

			if (is_a($sendmail, 'JException'))
			{
				$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_NO_MAIL_SEND', $sendmail->getMessage()));
				$result = false;
			}
			else
			{
				$this->log->addStats('information', 'COM_CSVI_MAIL_SEND');
			}
		}
		catch (Exception $e)
		{
			$this->log->addStats('incorrect', $e->getMessage());

			$result = false;
		}

		// Clear the mail details
		$mailer->clearAddresses();

		return $result;
	}

	/**
	 * FTP a generated file.
	 *
	 * @param   string  $downloadFile  The full path and name of the file to email.
	 *
	 * @return  bool   True on success | False on failure.
	 *
	 * @since   3.5.0
	 */
	public function ftpFile($downloadFile)
	{
		if (!JFile::exists($downloadFile))
		{
			$this->log->addStats('information', JText::sprintf('COM_CSVI_FTP_EXPORTFILE_NOT_CREATED', $downloadFile));
			$this->log->add('Could not store the file ' . $downloadFile . ' on the FTP server', false);

			return false;
		}

		if ($this->template->get('sftp', '', 'int'))
		{
			return $this->sftpFile($downloadFile);
		}

		// Generate the FTP file
		$ftpFile = $this->formatExportFilename($this->template->get('ftpfile', basename($downloadFile), 'string'));

		// Start the FTP
		jimport('joomla.client.ftp');
		$ftp = JClientFtp::getInstance(
			$this->template->get('ftphost', '', 'string'),
			$this->template->get('ftpport', 21, 'int'),
			array(),
			$this->template->get('ftpusername', '', 'string'),
			$this->template->get('ftppass', '', 'string')
		);
		$ftp->chdir($this->template->get('ftproot', '/', 'string'));
		$result = $ftp->store($downloadFile, $ftpFile);
		$ftp->quit();

		if ($result)
		{
			$this->log->addStats('information', JText::sprintf('COM_CSVI_FTP_EXPORTFILE_CREATED', $ftpFile));
			$this->log->add('Stored the file ' . $ftpFile . ' on the FTP server', false);
		}
		else
		{
			$this->log->addStats('information', JText::sprintf('COM_CSVI_FTP_EXPORTFILE_NOT_CREATED', $downloadFile));
			$this->log->add('Could not store the file  ' . $downloadFile . ' on the FTP server', false);
		}

		return $result;
	}

	/**
	 * SFTP a generated file.
	 *
	 * @param   string  $downloadFile  The full path and name of the file to email.
	 *
	 * @return  bool   True on success | False on failure.
	 *
	 * @since   3.5.0
	 */
	public function sftpFile($downloadFile)
	{
		if (!JFile::exists($downloadFile))
		{
			$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_FTP_EXPORTFILE_NOT_CREATED', $downloadFile));
			$this->log->add('The export file  ' . $downloadFile . ' does not exist', false);

			return false;
		}

		// Generate the FTP file
		$ftpFile = $this->formatExportFilename($this->template->get('ftpfile', basename($downloadFile), 'string'));

		$host     = $this->template->get('ftphost', '', 'string');
		$port     = $this->template->get('ftpport', 21, 'int');
		$username = $this->template->get('ftpusername', '', 'string');
		$pass     = $this->template->get('ftppass', '', 'string');
		$root     = $this->template->get('ftproot', '/', 'string');

		// Start the SFTP
		$sftp = new SFTP($host, $port);

		// Get the data to store
		$data = file_get_contents($downloadFile);

		if (!$sftp->login($username, $pass))
		{
			$this->log->addStats('incorrect', JText::_('COM_CSVI_FTP_CANNOT_LOGIN'));
			$this->log->add('Cannot login into the FTP server.', false);

			return false;
		}

		$this->log->add('Logged into the SFTP server', false);

		if (!$sftp->chdir($root))
		{
			$this->log->add('Cannot change to folder ' . $root, false);

			return false;
		}

		$this->log->add('Changed to folder ' . $root, false);

		$sftp->put($ftpFile, $data);

		$this->log->addStats('information', JText::sprintf('COM_CSVI_FTP_EXPORTFILE_CREATED', $ftpFile));
		$this->log->add('Stored the file ' . $ftpFile . ' on the FTP server', false);

		return true;
	}

	/**
	 * Set the cancel status.
	 *
	 * @param   int  $csvi_process_id  The ID of the import process
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function setEndTimestamp($csvi_process_id)
	{
		if ($csvi_process_id > 0)
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('csvi_log_id'))
				->from($this->db->quoteName('#__csvi_processes'))
				->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $csvi_process_id);
			$this->db->setQuery($query);
			$csvi_log_id = $this->db->loadResult();

			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__csvi_logs'))
				->set($this->db->quoteName('end') . ' = ' . $this->db->quote(JFactory::getDate()->toSql()))
				->set($this->db->quoteName('run_cancelled') . ' = 1')
				->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $csvi_log_id);
			$this->db->setQuery($query);
			$this->db->execute();

			$query = $this->db->getQuery(true)
				->delete($this->db->quoteName('#__csvi_processes'))
				->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $csvi_process_id);
			$this->db->setQuery($query);
			$this->db->execute();
		}
	}

	/**
	 * Get a list of XML or HTML sites.
	 *
	 * @param   string  $type  The type of files to find (XML or HTML).
	 *
	 * @return  array  List of available sites.
	 *
	 * @since   4.0
	 */
	public function getExportSites($type)
	{
		$path = JPATH_ADMINISTRATOR . '/components/com_csvi/helper/file/export/' . $type;
		$options = array();

		if (JFolder::exists($path))
		{
			$files = JFolder::files($path, '.php', false, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'orderadvanced.php'));

			if (!empty($files))
			{
				foreach ($files as $file)
				{
					$file = basename($file, '.php');
					$options[$file] = JText::_('COM_CSVI_' . $file);
				}

				ksort($options);
			}
		}

		return $options;
	}

	/**
	 * Export to another database table
	 *
	 * @return  bool True if the export went good | False otherwise.
	 *
	 * @since   6.7.0
	 *
	 * @throws  Exception
	 */
	private function exportToDatabase()
	{
		$details             = array();
		$columns             = array();
		$details['user']     = $this->template->get('database_username', '', 'string');
		$details['password'] = $this->template->get('database_password', '', 'string');
		$details['database'] = $this->template->get('database_name', '', 'string');
		$portNo              = $this->template->get('database_portno');
		$hostName            = $this->template->get('database_host', '', 'string');
		$result              = false;
		$primarySourceTable  = '';
		$this->log->setLinenumber(0);

		if ((strpos($hostName, ':') === false) && $portNo)
		{
			$hostName = $hostName . ':' . $portNo;
		}

		$details['host']    = $hostName;
		$tableName          = $this->template->get('database_table', '', 'string');
		$primaryKeySource   = $this->template->get('primary_key_source', '', 'string');
		$primaryKeyTarget   = $this->template->get('primary_key_target', '', 'string');
		$createNewColumns   = $this->template->get('create_new_columns', 1);
		$database           = JDatabaseDriver::getInstance($details);
		$database->connect();

		foreach ($this->fields->getFieldnames() as $fieldName)
		{
			$columns[] = $fieldName;
		}

		// If the Source unique identifier field is not in columns let the user know
		if (!in_array($primaryKeySource, $columns, true))
		{
			$this->log->addStats('error', JText::_('COM_CSVI_SOURCE_FIELD_NOT_IN_TEMPLATE_FIELDS'));

			return false;
		}

		if ($database->connected())
		{
			// Find the primary key of target table
			$primaryKeyColumns     = $database->getTableKeys($tableName);
			$primaryKeyTargetArray = json_decode(json_encode($primaryKeyColumns), true);

			// Retrieve only primary key fields from the columns array
			if (!empty($primaryKeyTargetArray))
			{
				foreach ($primaryKeyTargetArray as $primaryValue)
				{
					if ($primaryValue['Key_name'] === 'PRIMARY')
					{
						$primarySourceTable = $primaryValue['Column_name'];
					}
				}
			}

			// Compare the table columns with exported template fields
			$tableColumns     = $database->getTableColumns($tableName, false);
			$tableColumnNames = ArrayHelper::getColumn(json_decode(json_encode($tableColumns), true), 'Field');
			$query            = $database->getQuery(true);

			// Check if user wants to create new columns
			if ($createNewColumns)
			{
				$fieldExists      = array_diff($columns, $tableColumnNames);

				if (!empty($fieldExists))
				{
					foreach ($fieldExists as $field)
					{
						$alterQuery = "ALTER TABLE " . $database->quoteName($tableName)
							. " ADD " . $database->quoteName($field) . " TEXT NOT NULL";
						$database->setQuery($alterQuery)->execute();
					}

					$this->log->add(JText::_('COM_CSVI_CREATED_TABLE_COLUMNS'), false);
				}
			}
			else
			{
				// Ignore the primary key field of target table
				if ($primarySourceTable)
				{
					$tableKey = array_search($primarySourceTable, $tableColumnNames);
					unset($tableColumnNames[$tableKey]);
				}

				$columns = $tableColumnNames;
			}

			if (!in_array($primaryKeyTarget, $columns, true))
			{
				$this->log->addStats('error', JText::_('COM_CSVI_TARGET_FIELD_NOT_IN_TABLE'));

				return false;
			}

			// Remove the headers from export data
			$headers = explode(',', $this->exportData[0]);
			$linecount = 0;

			foreach (array_slice($this->exportData, 1) as $data)
			{
				$rowValue            = array();
				$updateValue         = array();
				$linecount++;

				if (!empty($data))
				{
					// Only export the columns user wants as per his table
					$newValue = explode(',', str_replace('"', '', $data));
					$newValue = array_splice($newValue, 0, count($columns));
					$primaryArrayKey       = array_search($primaryKeySource, $headers);

					if ($newValue[$primaryArrayKey])
					{
						foreach ($newValue as $key => $value)
						{
							$rowValue[$key] = $database->quote(str_replace('"', '', $value));

							// Set the values for update if primary key field is used
							$updateValue[] = $database->quoteName(str_replace('"', '', $headers[$key])) . " = " . $database->quote(str_replace('"', '', $value));
						}

						$insert = true;
					}
					else
					{
						$this->log->setLinenumber($linecount);
						$this->log->addStats('skipped', JText::_('COM_CSVI_UNIQUE_IDENTIFIER_VALUE_EMPTY'));
						$insert = false;
					}

					$values                = implode(',', $rowValue);
					$updateDuplicateValues = implode(',', $updateValue);
					$numRows               = 0;
					$primaryKeyValue       = str_replace('"', '', $newValue[$primaryArrayKey]);

					if ($primaryKeyValue)
					{
						$query->clear()
							->select(array('*'))
							->from($database->quoteName($tableName))
							->where($database->quoteName($primaryKeyTarget) . ' = ' . $database->quote($primaryKeyValue));
						$database->setQuery($query)->execute();
						$numRows = $database->getNumRows();
						$this->log->add(JText::_('COM_CSVI_FOUND_PRIMARY_KEY_ROWS'));
					}

					if ($numRows)
					{
						$query->clear()
							->update($database->quoteName($tableName))
							->set($updateDuplicateValues)
							->where($database->quoteName($primaryKeyTarget) . '=' . $database->quote($primaryKeyValue));

						try
						{
							$database->setQuery($query);
							$database->execute();
							$result = true;
							$this->log->add(JText::_('COM_CSVI_DUPLICATE_PRIMARY_KEY_ROWS_UPDATED'));
						}
						catch (Exception $e)
						{
							$this->log->add(JText::sprintf('COM_CSVI_EXPORT_DATA_ERROR', $e->getMessage()), false);
						}
					}
					else
					{
						$query->clear()
							->insert($database->quoteName($tableName))
							->columns($database->quoteName($columns))
							->values($values);

						try
						{
							if ($insert)
							{
								$database->setQuery($query);
								$database->execute();
								$result = true;
								$this->log->add(JText::_('COM_CSVI_EXPORT_DATA_INSERTED'));
							}
						}
						catch (Exception $e)
						{
							$this->log->add(JText::sprintf('COM_CSVI_EXPORT_DATA_ERROR', $e->getMessage()), false);

						}
					}
				}
				else
				{
					$this->log->addStats('information', JText::_('COM_CSVI_NO_DATA_TO_EXPORT'));
				}
			}

			if ($result)
			{
				$this->log->setLinenumber($linecount);
				$this->log->addStats('information', JText::sprintf('COM_CSVI_DATABASE_EXPORTED_COMPLETE', $details['database'], $tableName));
			}
		}
		else
		{
			$this->log->addStats('error', JText::_('COM_CSVI_DATABASE_EXPORTED_ERROR'));
		}

		$database->disconnect();
	}

	/**
	 * Delete empty file if user wants to do.
	 *
	 * @param   string  $file  The path of file to delete.
	 *
	 * @return  void.
	 *
	 * @since   7.0
	 */
	public function deleteEmptyFile($file)
	{
		// Settings check if user wants to export empty file or not
		if (!$this->log->getLinenumber() && !$this->template->get('output_empty_file'))
		{
			JFile::delete($file);
			$this->log->addStats('information', JText::_('COM_CSVI_EXPORTFILE_EMPTY_DELETED'));
			$this->log->add('No export file has been created because there are no records to export. Set Output empty file to Yes in the template settings to generate an empty file.', false);
		}
	}
}