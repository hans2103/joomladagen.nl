<?php
/**
 * @package     CSVI
 * @subpackage  Imports
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Import model class.
 *
 * @package     CSVI
 * @subpackage  Imports
 * @since       6.0
 */
class RantaiImportModel extends RantaiModel
{
	/**
	 * The fields handler
	 *
	 * @var    CsviHelperImportFields
	 * @since  6.0
	 */
	protected $fields;

	/**
	 * Initialise the import.
	 *
	 * @param   int  $csvi_process_id  The ID of the running import
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 * @throws  CsviException
	 * @throws  RuntimeException
	 */
	public function initialiseImport($csvi_process_id)
	{
		// Load the run details
		$query = $this->db->getQuery(true)
			->select(
				array(
					$this->db->quoteName('csvi_template_id'),
					$this->db->quoteName('csvi_log_id'),
					$this->db->quoteName('userId'),
					$this->db->quoteName('processfile'),
					$this->db->quoteName('processfolder'),
					$this->db->quoteName('position')
				)
			)
			->from($this->db->quoteName('#__csvi_processes'))
			->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $csvi_process_id);
		$this->db->setQuery($query);
		$details = $this->db->loadObject();

		if ($details)
		{
			if ($details->csvi_template_id)
			{
				// Run ID is correct, set it
				$this->runId = $csvi_process_id;

				// Set the user ID
				$this->userId = $details->userId;

				// Load the template
				$this->loadTemplate($details->csvi_template_id);

				// Setup the addon autoloader
				$component      = $this->template->get('component');
				$extension      = substr($component, 4);
				$override       = $this->template->get('override');
				$operation      = $this->template->get('operation');
				$adminTemplate  = $this->getAdminTemplate();

				if (!$component && !$operation)
				{
					throw new CsviException(JText::_('COM_CSVI_EXPORT_NO_COMPONENT_NO_OPERATION'), 514);
				}

				if ($override)
				{
					if (file_exists(JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' . $component . '/model/import/' . $override . '.php'))
					{
						JLoader::registerPrefix(ucfirst($component), JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' . $component);
					}
					else
					{
						JLoader::registerNamespace($extension, JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/');
					}
				}

				// Setup the component autoloader
				JLoader::registerNamespace($extension, JPATH_PLUGINS . '/csviaddon/');

				// This loader is still needed for other classes than the import
				JLoader::registerPrefix(ucfirst($component), JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component);

				// Setup the logger
				$this->log->setActive($this->template->getLog());
				$this->log->setLogId($details->csvi_log_id);

				// Check the source
				$source = $this->template->get('source', 'fromupload');

				// Check if we already have a file to process
				if (empty($details->processfile) && ($source !== 'fromdatabase'))
				{
					// Load the folder class
					jimport('joomla.filesystem.folder');

					// Load the first file in line
					$files = JFolder::files($details->processfolder);

					if (is_array($files) && !empty($files))
					{
						$details->processfile = $details->processfolder . '/' . $files[0];
					}
				}

				// Add a dummy file for the database as it doesn't use a real file
				if ($source === 'fromdatabase')
				{
					$details->processfile = 'database';
				}

				// Set the file being processed
				if (!$details->processfile)
				{
					throw new CsviException(JText::_('COM_CSVI_NO_FOLDER_FOUND_TO_PROCESS'), 504);
				}

				// Set the folder to process
				$this->processfolder = $details->processfolder;

				// Set the file to process
				$this->processfile = $details->processfile;

				// Load the file
				$this->loadImportFile();

				// Tell the logger about the filename
				$this->log->setFilename(basename($this->processfile));

				// Load the fields
				$this->loadFields();

				// Both the file and fields have been setup, let's connect them
				$this->file->setFields($this->fields);

				// Let's setup the fields
				$this->fields->setupFields();

				// Move the file pointer if needed
				if ($details->position > 0)
				{
					$this->file->setFilePos($details->position);
				}

				return true;
			}
			else
			{
				throw new CsviException(JText::sprintf('COM_CSVI_NO_TEMPLATE_ID_FOUND_RUN', $csvi_process_id), 505);
			}
		}
		else
		{
			throw new CsviException(JText::_('COM_CSVI_NO_VALID_RUNID_FOUND', $csvi_process_id), 506);
		}

	}

	/**
	 * Load the import file helper.
	 *
	 * @return    void.
	 *
	 * @throws  CsviException
	 *
	 * @since   6.0
	 */
	protected function loadImportFile()
	{
		// Get the template
		$templateId = $this->template->getId();

		if (empty($templateId))
		{
			throw new CsviException(JText::_('COM_CSVI_NO_TEMPLATE_LOADED'), 501);
		}

		if ($this->template->get('source') === 'fromdatabase')
		{
			$fileclass = 'CsviHelperFileImportDatabase';
		}
		else
		{
			// Get the file parts
			$upload_parts = pathinfo($this->processfile);

			// Force an extension if needed
			$force_ext = $this->template->get('use_file_extension');

			if (!empty($force_ext))
			{
				$upload_parts['extension'] = $force_ext;
			}

			// Set the file helper
			if (!array_key_exists('extension', $upload_parts))
			{
				throw new CsviException(JText::sprintf('COM_CSVI_NO_EXTENSION_FOUND_ON_IMPORT_FILE', $this->processfile), 502);
			}

			$fileclass = 'CsviHelperFileImport';

			switch (strtolower($upload_parts['extension']))
			{
				case 'xml':
				case 'xls':
				case 'ods':
					$fileclass .= ucfirst($upload_parts['extension']);
					break;
				case 'csv':
				case 'txt':
				case 'tsv':
					$fileclass .= 'Csv';
					break;
			}
		}

		// Load the file processor
		$this->file = new $fileclass($this->template, $this->log, $this->csvihelper, $this->input);

		// Set the filename
		$this->file->setFilename($this->processfile);

		// Validate the file
		if (!$this->file->processFile())
		{
			throw new CsviException(JText::sprintf('COM_CSVI_CANNOT_PROCESS_FILE', $this->processfile), 503);
		}
		else
		{
			$this->processfile = $this->file->getFilename();
		}
	}

	/**
	 * Pre-process any tasks.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function onBeforeImport()
	{
		// Setup the add-on
		$this->initialiseAddon();

		// Get the file position
		$started = $this->log->getLinenumber();

		// Write out the import details to the debug log if we haven't started yet
		if (!$started)
		{
			$this->log->add('==========', false);
			$this->importDetails();
			$this->log->add('==========', false);
		}

		// Move 1 row forward as we are skipping the first line only at start
		if (!$started && $this->template->get('skip_first_line'))
		{
			$this->file->next();
		}

		// Processes that only need to be called at the start of the file
		if (!$started)
		{
			// Un-publish any items if needed
			if ($this->helper && method_exists($this->helper, 'unpublishBeforeImport'))
			{
				$this->helper->unpublishBeforeImport($this->template, $this->log, $this->db);
			}

			// Delete rows before import
			if ($this->helper && method_exists($this->helper, 'truncateTableBeforeImport'))
			{
				$this->helper->truncateTableBeforeImport();
			}
		}
	}

	/**
	 * Post-process any tasks.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 *
	 * @throws  UnexpectedValueException
	 */
	public function onAfterImport()
	{
		// Check if the import file needs to be deleted
		$from = $this->template->get('source', 'fromupload');

		if ($this->template->get('delete_file') && in_array($from, array('fromserver', 'fromftp'), true))
		{
			switch ($from)
			{
				case 'fromftp':
					$ftpFile = $this->template->get('ftpfile');

					// Start the FTP
					jimport('joomla.client.ftp');
					$ftp = JClientFtp::getInstance(
						$this->template->get('ftphost'),
						$this->template->get('ftpport'),
						array(),
						$this->template->get('ftpusername'),
						$this->template->get('ftppass')
					);

					$ftpRoot = $this->template->get('ftproot', '');

					if (substr($ftpRoot, -1) !== '/')
					{
						$ftpRoot .= '/';
					}

					$serverFile = $ftpRoot . JPath::clean($ftpFile, '/');

					// Check if a specific file has been set
					if (strlen($ftpFile) === 0)
					{
						$serverFile = $ftpRoot . basename($this->processfile);
					}

					$this->log->add('Delete file:' . $serverFile);
					$ftp->delete($serverFile);
					break;
				case 'fromserver':
					$local_file = JPath::clean($this->template->get('local_csv_file'), '/');

					if (is_dir($local_file))
					{
						JFile::delete($local_file . '/' . basename($this->processfile));
					}
					else
					{
						$this->log->add('Delete temporary file:' . $this->processfile);
						JFile::delete($this->processfile);

						$this->log->add('Delete original file:' . $local_file);
						JFile::delete($local_file);
					}
					break;
			}
		}
	}

	/**
	 * Print out import details.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	private function importDetails()
	{
		// Only write out the details if debugging is enabled
		if ($this->template->getLog())
		{
			$this->log->add(JText::_('COM_CSVI_CSVI_VERSION_TEXT') . JText::_('COM_CSVI_CSVI_VERSION'), false);

			if (function_exists('phpversion'))
			{
				$this->log->add(JText::sprintf('COM_CSVI_PHP_VERSION', phpversion()), false);
			}

			// Push out all settings
			$this->processSettings($this->template->getSettings());
		}
	}

	/**
	 * Add all the settings to the debug log.
	 *
	 * @param   array  $settings  The settings to report in debug log.
	 *
	 * @return  void.
	 *
	 * @since   5.3
	 */
	private function processSettings($settings)
	{
		foreach ($settings as $name => $value)
		{
			switch ($name)
			{
				case 'fields':
					break;
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
							case 'database_password':
								break;
							case 'source':
								$this->log->add(JText::_('COM_CSVI_JFORM_' . $name . '_LABEL') . ': ' . $value, false);
								$this->log->add(JText::_('COM_CSVI_IMPORT_UPLOAD_FILE_LABEL') . ': ' . $this->processfile, false);
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
									case ',':
										$value = 'comma';
										break;
								}

								$this->log->add(JText::_('COM_CSVI_JFORM_' . $name . '_LABEL') . ': ' . $value, false);
								break;
						}
					}
					break;
			}
		}
	}

	/**
	 * Run the import.
	 *
	 * @param   bool  $isCli  Set if the import is run from CLI
	 *
	 * @return  bool  true if we continue importing | false if the import is finished/terminated.
	 *
	 * @throws  CsviException
	 *
	 * @since   6.0
	 */
	final public function runImport($isCli = false)
	{
		// Load the template settings
		$component = $this->template->get('component');
		$extension = substr($component, 4);
		$operation = $this->template->get('operation');
		$override  = $this->template->get('override');

		// Set the system limits to the user settings if needed
		$this->systemLimits();

		// Auto detect line-endings to also support Mac line-endings
		if ($this->template->get('im_mac', false))
		{
			ini_set('auto_detect_line_endings', true);
		}

		// Load the import routine
		$classname = '\\' . $extension . '\\' . $component . '\\model\import\\' . $operation;

		// Load the import routine
		$adminTemplate = $this->getAdminTemplate();

		if ($override)
		{
			$classname = '\\' . $extension . '\\' . $component . '\\model\import\\' . $override;

			// Check for the old style override pre 7.2.0
			if (file_exists(JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' . $component . '/model/import/' . $override . '.php'))
			{
				$classname = ucwords($component) . 'ModelImport' . ucwords($override);

				if (!class_exists($classname))
				{
					$classname = '\\' . $extension . '\\' . $component . '\\model\import\\' . $override;
				}
			}
		}

		// Instantiate the import routine
		$routine = new $classname(
			$this->db,
			$this->template,
			$this->log,
			$this->csvihelper,
			$this->fields,
			$this->helper,
			$this->helperconfig,
			$this->userId
		);

		if ($routine)
		{
			// Set the last run time for the template
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__csvi_templates'))
				->set($this->db->quoteName('lastrun') . ' = ' . $this->db->quote(JFactory::getDate()->toSql()))
				->where($this->db->quoteName('csvi_template_id') . ' = ' . (int) $this->template->getId());
			$this->db->setQuery($query)->execute();

			// Initialize process data so the import starts
			$processdata = true;
			$return = true;

			// Run setup if needed
			if (method_exists($routine, 'onBeforeStart'))
			{
				$routine->onBeforeStart();
			}

			// Load any tables if needed
			if (method_exists($routine, 'loadTables'))
			{
				$routine->loadTables();
			}

			// Start processing data
			while ($processdata)
			{
				// If the number of lines is set to 0, do unlimited import
				if (($this->template->get('import_nolines', 0) == 0) || $isCli)
				{
					$nolines = $this->recordsProcessed + 1;
				}
				else
				{
					$nolines = $this->template->get('import_nolines', 1000);
				}

				if (($this->recordsProcessed + 1) <= $nolines)
				{
					$this->fields->setProcessRecord(true);

					// Load the data
					$result = $this->file->readNextLine();

					// Check the load result
					if ($result == false)
					{
						// Finish processing
						$this->finishProcess(true);
						$processdata = false;

						// Post Processing now the import is done
						if (method_exists($routine, 'getPostProcessing'))
						{
							$routine->getPostProcessing($this->fields->getFieldnames());
						}

						$return = false;
					}
					else
					{
						// Increase the line number as we process
						$this->log->incrementLinenumber();

						// Load ICEcat data if user wants to
						if (method_exists($this->helper, 'getIcecat'))
						{
							$this->helper->getIcecat();
						}

						// Set the data to import
						$this->fields->prepareData();

						// Notify the debug log what line we are one
						$this->log->add(JText::sprintf('COM_CSVI_DEBUG_PROCESS_LINE', $this->log->getLinenumber()), false);

						// Clear the state
						$routine->clearState();

						// Clear the tables
						if (method_exists($routine, 'clearTables'))
						{
							$routine->clearTables();
						}

						// Start processing record
						if ($routine->getStart())
						{
							// Start processing the records
							$routine->getProcessRecord();

							// Increase the number of records processed
							$this->recordsProcessed++;
						}
						else
						{
							// The routine reports a problem, usually unmet conditions

							// Finish processing
							$this->finishProcess(true);

							// Stop from processing an error occurred
							$processdata = false;

							$return = false;
						}
					}

					// Check for time between imports
					if ($this->recordsProcessed === (int) $nolines && !$isCli)
					{
						$importWait = $this->template->get('import_wait', 5);
						sleep($importWait);
					}
				}
				else
				{
					// Prepare for page reload
					$this->finishProcess(false);

					// Stop from processing any further, no time left
					$processdata = false;
				}

				// Clean the fields
				$this->fields->reset();
			}

			// Run setup if needed
			if (method_exists($routine, 'onAfterStart'))
			{
				$routine->onAfterStart();
			}
		}
		else
		{
			$this->log->addStats('incorrect', 'COM_CSVI_NO_VALID_CLASS_FOUND');

			// Finish processing
			$this->finishProcess(true);

			throw new CsviException(JText::sprintf('COM_CSVI_NO_VALID_CLASS_FOUND', $classname));
		}

		return $return;
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
			if ($this->template->get('source') !== 'fromdatabase')
			{
				// Close the file
				$this->file->closeFile(false);

				// Remove the file that has just been imported
				if (JFile::delete($this->file->getFilename()))
				{
					// Remove the processfile setting, this is needed for folder processing to continue
					$query = $this->db->getQuery(true)
						->update($this->db->quoteName('#__csvi_processes'))
						->set($this->db->quoteName('processfile') . ' = ""')
						->set($this->db->quoteName('position') . ' = 0')
						->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $this->runId);
					$this->db->setQuery($query)->execute();
				}
			}

			// Set the log end timestamp
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__csvi_logs'))
				->set($this->db->quoteName('end') . ' = ' . $this->db->quote(JFactory::getDate(time())->toSql()))
				->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $this->log->getLogId());
			$this->db->setQuery($query);
			$this->db->execute();
		}
		else
		{
			// Store the current file pointer
			$filepos = $this->file->getFilePos();

			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__csvi_processes'))
				->set($this->db->quoteName('position') . ' = ' . (int) $filepos)
				->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $this->runId);
			$this->db->setQuery($query);
			$this->db->execute();

			// Close the file
			$this->file->closeFile(false);
		}
	}
}
