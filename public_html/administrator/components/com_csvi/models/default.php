<?php
/**
 * @package     CSVI
 * @subpackage  Model
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * The default model
 *
 * @package     CSVI
 * @subpackage  Model
 * @since       6.0
 */
class CsviModelDefault extends JModelLegacy
{
	/**
	 * Template helper
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	protected $template;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  6.0
	 */
	protected $log;

	/**
	 * CSVI helper
	 *
	 * @var    CsviHelperCsvi
	 * @since  6.0
	 */
	protected $csvihelper;

	/**
	 * Fields helper
	 *
	 * @var    CsviHelperImportfields
	 * @since  6.0
	 */
	protected $fields;

	/**
	 * File helper
	 *
	 * @var    CsviHelperFile
	 * @since  6.0
	 */
	protected $file;

	/**
	 * Name of the file to process
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $processfile = '';

	/**
	 * Name of the folder to process
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $processfolder = '';

	/**
	 * Database connector
	 *
	 * @var    JDatabaseDriver
	 * @since  6.0
	 */
	protected $db;

	/**
	 * The CSVI Language helper
	 *
	 * @var    CsviHelperLanguage
	 * @since  7.1.0
	 */
	protected $language;

	/**
	 * The addon helper
	 *
	 * @var    object
	 * @since  6.0
	 */
	protected $helper;

	/**
	 * The addon config helper
	 *
	 * @var    object
	 * @since  6.0
	 */
	protected $helperConfig;

	/**
	 * The ID of the current run
	 *
	 * @var    int
	 * @since  6.0
	 */
	protected $runId = 0;

	/**
	 * The CSVI Helper Settings
	 *
	 * @var    CsviHelperSettings
	 * @since  6.0
	 */
	protected $settings;

	/**
	 * JInput instance.
	 *
	 * @var    JInput
	 * @since  6.0
	 */
	protected $input;

	/**
	 * Public class constructor
	 *
	 * @param   array  $config  The configuration array
	 *
	 * @since   6.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Load the database class
		$this->db = JFactory::getDbo();

		// Load the global CSVI settings
		$this->settings = new CsviHelperSettings($this->db);

		// Load the logger
		$this->log = new CsviHelperLog($this->settings, $this->db);

		// Load the languages
		$this->language = new CsviHelperLanguage;

		// Load the CSVI helper
		$this->csvihelper = new CsviHelperCsvi;
		$this->csvihelper->initialise($this->log);

		$this->input = JFactory::getApplication()->input;

		if (array_key_exists('input', $config))
		{
			$this->input = $config['input'];
		}
	}

	/**
	 * Initialise the import.
	 *
	 * @param   int  $csvi_process_id  The unique ID of the import run
	 *
	 * @return  bool  True if template ID is found.
	 *
	 * @throws  CsviException
	 *
	 * @since   6.0
	 */
	public function initialiseImport($csvi_process_id)
	{
		// Set the run ID
		$this->runId = $csvi_process_id;

		// Load the run details
		$query = $this->db->getQuery(true)
			->select(
				array(
					$this->db->quoteName('csvi_template_id'),
					$this->db->quoteName('csvi_log_id'),
					$this->db->quoteName('processfile'),
					$this->db->quoteName('processfolder'),
					$this->db->quoteName('position')
				)
			)
			->from($this->db->quoteName('#__csvi_processes'))
			->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $csvi_process_id);
		$this->db->setQuery($query);
		$details = $this->db->loadObject();

		if ($details->csvi_template_id)
		{
			// Empty the processed table
			$this->db->truncateTable('#__csvi_processed');

			// Load the template
			$this->loadTemplate($details->csvi_template_id);

			// Setup the addon autoloader
			$component = $this->template->get('component');
			$extension = substr($component, 4);

			// If the addon is not installed show message to install it
			if (!file_exists(JPATH_PLUGINS . '/csviaddon/' . $extension))
			{
				throw new CsviException(JText::sprintf('COM_CSVI_NO_ADDON_INSTALLED', $component));
			}

			JLoader::registerPrefix(ucfirst($component), JPATH_PLUGINS . '/csviaddon/' . $extension);

			// Setup the logger
			$this->log->setLogId($details->csvi_log_id);
			$this->log->setActive($this->template->getLog());

			// Check the source
			$source = $this->template->get('source', 'fromupload');

			// Set the file being processed
			if (empty($details->processfile) && empty($details->processfolder))
			{
				// If fromserver we already have the full path and name, store it
				if ($source == 'fromserver')
				{
					$local_file = JPath::clean($this->template->get('local_csv_file'), '/');

					if (is_dir($local_file))
					{
						$details->processfolder = $local_file;
						$this->storeFolder($details->processfolder);
					}
					elseif (is_file($local_file))
					{
						$details->processfile = $local_file;
						$this->storeFilename($details->processfile);
					}
				}
			}

			// Check if we already have a file to process
			if (empty($details->processfile) && !empty($details->processfolder))
			{
				jimport('joomla.filesystem.folder');

				// Load the first file in line
				$files = JFolder::files(
					$details->processfolder,
					'.',
					false,
					false,
					$exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'),
					array('^\..*', '.*~'),
					true
				);

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

			if ($details->processfile)
			{
				// Set the folder to process
				$this->processfolder = $details->processfolder;

				// Set the file to process
				$this->processfile = $details->processfile;

				// Source is always from server as we already uploaded it to our server
				$this->loadImportFile();

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
			}

			return true;
		}
		else
		{
			throw new CsviException(JText::sprintf('COM_CSVI_NO_TEMPLATE_ID_FOUND_RUN', $csvi_process_id), 509);
		}
	}

	/**
	 * Load the import file helper.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.0
	 *
	 * @throws  CsviException
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
			// Get the file extension of the import file
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
			else
			{
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
					default:
						throw new CsviException(
							JText::sprintf('COM_CSVI_EXTENSTION_NOT_RECOGNIZED_ON_IMPORT_FILE', $upload_parts['extension'], $this->processfile), 516
						);
						break;
				}
			}
		}

		// Load the file processor
		$this->file = new $fileclass($this->template, $this->log, $this->csvihelper, $this->input);

		// Set the filename
		$this->file->setFilename($this->processfile);

		// Validate the file
		if ($this->file->processFile())
		{
			// Set the filename generated by the file handler in case it changed
			$this->processfile = $this->file->getFilename();

			return true;
		}
		else
		{
			throw new CsviException(JText::sprintf('COM_CSVI_CANNOT_PROCESS_FILE', $this->processfile), 503);
		}
	}

	/**
	 * Initialise the export.
	 *
	 * @param   int  $csvi_process_id  The unique ID of the import run
	 *
	 * @return  bool  True if template ID is found.
	 *
	 * @since   6.0
	 *
	 * @throws  CsviException
	 */
	public function initialiseExport($csvi_process_id)
	{
		// Set the run ID
		$this->runId = $csvi_process_id;

		// Load the run details
		$query = $this->db->getQuery(true)
			->select(
				array(
					$this->db->quoteName('csvi_template_id'),
					$this->db->quoteName('csvi_log_id'),
					$this->db->quoteName('processfile'),
				)
			)
			->from($this->db->quoteName('#__csvi_processes'))
			->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $csvi_process_id);
		$this->db->setQuery($query);
		$details = $this->db->loadObject();

		if ($details->csvi_template_id)
		{
			// Load the template if needed
			if (is_null($this->template))
			{
				$this->loadTemplate($details->csvi_template_id);
			}

			// Setup the logger
			$this->log->setLogId($details->csvi_log_id);

			// Set the file being processed
			if ($details->processfile)
			{
				$this->processfile = $details->processfile;

				// Load the file handler
				$this->loadExportFile();

				// Load the fields
				$this->loadFields();
			}

			return true;
		}
		else
		{
			throw new CsviException(JText::sprintf('COM_CSVI_NO_TEMPLATE_ID_FOUND_RUN', $csvi_process_id), 509);
		}
	}

	/**
	 * Store the filename to process.
	 *
	 * @param   string  $filename  The name of the file to store
	 *
	 * @return  bool  True if filename is stored | False if it cannot be stored.
	 *
	 * @since   6.0
	 *
	 * @throws  CsviException
	 */
	protected function storeFilename($filename)
	{
		if (!empty($filename))
		{
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__csvi_processes'))
				->set($this->db->quoteName('processfile') . ' = ' . $this->db->quote($filename))
				->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $this->runId);
			$this->db->setQuery($query);

			return $this->db->execute();
		}
		else
		{
			throw new CsviException(JText::_('COM_CSVI_CANNOT_STORE_FILENAME_FOR_IMPORT'));
		}
	}

	/**
	 * Store the folder to process.
	 *
	 * @param   string  $folder  The name of the file to store
	 *
	 * @return  bool  True if filename is stored | False if it cannot be stored.
	 *
	 * @throws  CsviException
	 *
	 * @since   6.0
	 */
	protected function storeFolder($folder)
	{
		if (!empty($folder))
		{
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__csvi_processes'))
				->set($this->db->quoteName('processfolder') . ' = ' . $this->db->quote($folder))
				->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $this->runId);
			$this->db->setQuery($query);

			return $this->db->execute();
		}
		else
		{
			throw new CsviException(JText::_('COM_CSVI_CANNOT_STORE_FOLDER_FOR_IMPORT'));
		}
	}

	/**
	 * Check the temporary folder.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	protected function checkTmpFolder()
	{
		// Define the tmp folder
		$config = JFactory::getConfig();
		$tmp_path = $config->get('tmp_path');

		if (!defined('CSVIPATH_TMP'))
		{
			define('CSVIPATH_TMP', JPath::clean($tmp_path . '/com_csvi', '/'));
		}
	}

	/**
	 * Load the necessary language files.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	protected function loadLanguageFiles()
	{
		$jlang = JFactory::getLanguage();

		// Language files located in the component folder
		$jlang->load('com_csvi', JPATH_COMPONENT_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_csvi', JPATH_COMPONENT_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_csvi', JPATH_COMPONENT_ADMINISTRATOR, null, true);
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
		// Set some log info
		$this->log->setActive($this->template->getLog());
		$this->log->setAddon($this->template->get('component'));
		$this->log->setActionType($this->template->get('operation'));
		$this->log->setTemplateName($this->template->getName());
		$this->log->initialise();

		return true;
	}

	/**
	 * Initialise the addons.
	 *
	 * @param   string  $addon  The addon to run the export for
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function initialiseAddon($addon)
	{
		// Load the component helpers
		JLoader::import('joomla.filesystem.file');

		$extension = substr($addon, 4);

		// Load the helper
		if (JFile::exists(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $addon . '/helper/' . $addon . '.php'))
		{
			$helperName = ucfirst($addon) . 'Helper' . ucfirst($addon);
			$this->helper = new $helperName($this->template, $this->log, $this->fields, $this->db);
		}

		// Load the config helper
		if (JFile::exists(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $addon . '/helper/' . $addon . '_config.php'))
		{
			$helperName = ucfirst($addon) . 'Helper' . ucfirst($addon) . '_config';
			$this->helperConfig = new $helperName;
		}
	}

	/**
	 * Invoke a running import or export.
	 *
	 * @return  int  The run ID
	 *
	 * @since   6.0
	 */
	public function initialiseRun()
	{
		// Assemble the columns and values
		$columns = array($this->db->quoteName('csvi_template_id'), $this->db->quoteName('csvi_log_id'), $this->db->quoteName('userId'));
		$values = $this->db->quote($this->template->getId()) . ', '
			. $this->db->quote($this->log->getLogId()) . ', '
			. (int) JFactory::getUser()->get('id');

		// Check if the processfile exists
		if ($this->processfile)
		{
			$columns[] = $this->db->quoteName('processfile');
			$values .= ', ' . $this->db->quote($this->processfile);
		}

		$query = $this->db->getQuery(true)
			->insert($this->db->quoteName('#__csvi_processes'))
			->columns($columns)
			->values($values);
		$this->db->setQuery($query);

		$this->db->execute();

		return $this->db->insertid();
	}

	/**
	 * Load the template helper.
	 *
	 * @param   int  $template_id  The ID of the template to load
	 *
	 * @return  mixed  True if template is loaded | Throw exception if not loaded.
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 * @throws  CsviException
	 */
	public function loadTemplate($template_id)
	{
		if (!$template_id)
		{
			throw new CsviException(JText::_('COM_CSVI_NO_TEMPLATE_SPECIFIED'), 402);
		}

		$this->template = new CsviHelperTemplate($template_id);

		return true;
	}

	/**
	 * Get the template instance.
	 *
	 * @return  CsviHelperTemplate  An instance of CsviHelperTemplate.
	 *
	 * @since   6.0
	 */
	public function getTemplate()
	{
		return  $this->template;
	}

	/**
	 * Set the template instance.
	 *
	 * @param   CsviHelperTemplate  $template  An instance of CsviHelperTemplate.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function setTemplate(CsviHelperTemplate $template)
	{
		$this->template = $template;
	}

	/**
	 * Get the file instance.
	 *
	 * @return  CsviHelperFile  An instance of CsviHelperFile.
	 *
	 * @since   6.0
	 */
	public function getFile()
	{
		return  $this->file;
	}

	/**
	 * Get the configuration fields the user wants to use
	 *
	 * The configuration fields can be taken from the uploaded file or from
	 * the database. Depending on the template settings.
	 *
	 * @return  bool  true|false true when there are config fields|false when there are no or unsupported fields.
	 *
	 * @since   3.0
	 */
	protected function loadFields()
	{
		// Get the correct fields helper
		$className = 'CsviHelper' . ucfirst($this->template->get('action')) . 'fields';

		// Instantiate the fields
		$this->fields = new $className($this->template, $this->log, $this->db);

		// Add the file handler to the fields
		$this->fields->setFile($this->file);

		return true;
	}

	/**
	 * Sets the system limits to user defined values.
	 *
	 * Sets the system limits to user defined values to allow for longer and
	 * bigger uploaded files.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	protected function systemLimits()
	{
		// See if we need to use the new limits
		if ($this->template->get('use_system_limits'))
		{
			$this->log->add('Setting system limits:', false);

			// Apply the new memory limits
			$execution_time = $this->template->get('max_execution_time');

			if (strlen($execution_time) > 0)
			{
				$this->log->add('Setting max_execution_time to ' . $execution_time . ' seconds', false);
				@ini_set('max_execution_time', $execution_time);
			}

			$memory_limit = $this->template->get('memory_limit');

			if ($memory_limit == '-1')
			{
				$this->log->add('Setting memory_limit to ' . $memory_limit, false);
				@ini_set('memory_limit', $memory_limit);
			}
			elseif (strlen($memory_limit) > 0)
			{
				$this->log->add('Setting memory_limit to ' . $memory_limit . 'M', false);
				@ini_set('memory_limit', $memory_limit . 'M');
			}

			$post_size = $this->template->get('post_max_size');

			if (strlen($post_size) > 0)
			{
				$this->log->add('Setting post_max_size to ' . $post_size . 'M', false);
				@ini_set('post_max_size', $post_size . 'M');
			}

			$file_size = $this->template->get('upload_max_filesize');

			if (strlen($file_size) > 0)
			{
				$this->log->add('Setting upload_max_filesize to ' . $file_size . 'M', false);
				@ini_set('upload_max_filesize', $file_size . 'M');
			}
		}
	}

	/**
	 * Return the log ID.
	 *
	 * Used in export
	 *
	 * @return  int  The ID of the log.
	 *
	 * @since   6.0
	 */
	public function getLogId()
	{
		return $this->log->getLogId();
	}

	/**
	 * Set the log ID.
	 *
	 * Used in export
	 *
	 * @param   int  $csvi_log_id  The ID of the log
	 *
	 * @return  int  The ID of the log.
	 *
	 * @since   7.2.0
	 */
	public function setLogId($csvi_log_id)
	{
		return $this->log->setLogId($csvi_log_id);
	}

	/**
	 * Return the run ID.
	 *
	 * Used in export
	 *
	 * @return  int  The ID of the run.
	 *
	 * @since   6.0
	 */
	public function getRunId()
	{
		return $this->runId;
	}

	/**
	 * Return the process file.
	 *
	 * @return  string  The name of the process file.
	 *
	 * @since   6.0
	 */
	public function getProcessfile()
	{
		return $this->processfile;
	}
}
