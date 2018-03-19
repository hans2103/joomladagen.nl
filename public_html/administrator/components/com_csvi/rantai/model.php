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
class RantaiModel
{
	/**
	 * JDatabase handler
	 *
	 * @var    JDatabaseDriver
	 * @since  6.0
	 */
	protected $db = null;

	/**
	 * Template helper
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	protected $template = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  6.0
	 */
	protected $log = null;

	/**
	 * Settings helper
	 *
	 * @var    CsviHelperSettings
	 * @since  6.0
	 */
	protected $settings = null;

	/**
	 * CSVI helper
	 *
	 * @var    CsviHelperCsvi
	 * @since  6.0
	 */
	protected $csvihelper = null;

	/**
	 * Fields helper
	 *
	 * @var    CsviHelperFields
	 * @since  6.0
	 */
	protected $fields = null;

	/**
	 * File helper
	 *
	 * @var    CsviHelperFile
	 * @since  6.0
	 */
	protected $file = null;

	/**
	 * Input handler
	 *
	 * @var    JInput
	 * @since  6.0
	 */
	protected $input = null;

	/**
	 * The addon helper
	 *
	 * @var    object
	 * @since  6.0
	 */
	protected $helper = null;

	/**
	 * The addon config helper
	 *
	 * @var    object
	 * @since  6.0
	 */
	protected $helperconfig = null;

	/**
	 * The ID of the current run
	 *
	 * @var    int
	 * @since  6.0
	 */
	protected $runId = 0;

	/**
	 * The ID of the user initiated the run
	 *
	 * @var    int
	 * @since  6.0
	 */
	protected $userId = 0;

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
	 * Keeps the number of processed records
	 *
	 * @var    int
	 * @since  6.0
	 */
	protected $recordsProcessed = 0;

	/**
	 * Construct the class.
	 *
	 * @param   JInput  $input  The input handler
	 *
	 * @since   6.0
	 */
	public function __construct(JInput $input)
	{
		// Set the input handler
		$this->input = $input;

		// Load the database class
		$this->db = JFactory::getDbo();

		// Load the global CSVI settings
		$this->settings = new CsviHelperSettings($this->db);

		// Load the logger
		$this->log = new CsviHelperLog($this->settings, $this->db);

		// Load the CSVI helper
		$this->csvihelper = new CsviHelperCsvi;
		$this->csvihelper->initialise($this->log);
	}

	/**
	 * Load the template helper.
	 *
	 * @param   int  $template_id  The ID of the template to load
	 *
	 * @return  mixed  True if template is loaded | Throw exception if not loaded.
	 *
	 * @throws  Exception
	 *
	 * @since   6.0
	 */
	protected function loadTemplate($template_id)
	{
		if (!$template_id)
		{
			throw new Exception(JText::_('COM_CSVI_NO_TEMPLATE_SPECIFIED'));
		}

		$this->template = new CsviHelperTemplate($template_id);

		return true;
	}

	/**
	 * Get the configuration fields the user wants to use
	 *
	 * The configuration fields can be taken from the uploaded file or from
	 * the database. Depending on the template settings..
	 *
	 * @return  bool  true|false true when there are config fields|false when there are no or unsupported fields.
	 *
	 * @since   3.0
	 */
	protected function loadFields()
	{
		// Get the correct fields helper
		$className = 'CsviHelper' . ucfirst($this->template->get('action')) . 'fields';

		$this->fields = new $className($this->template, $this->log, $this->db);
		$this->fields->setFile($this->file);

		return true;
	}

	/**
	 * Return the log ID.
	 *
	 * @return  int  The ID of the log.
	 *
	 * @since   6.0
	 */
	public function getRunId()
	{
		return $this->log->getLogId();
	}

	/**
	 * Return the number of lines processed.
	 *
	 * @return  int  The number of lines processed.
	 *
	 * @since   6.0
	 */
	public function getLinesProcessed()
	{
		return $this->log->getLinenumber();
	}

	/**
	 * Store the number of lines processed.
	 *
	 * @return  mixed  True on success | Throws exception on failure.
	 *
	 * @since   6.0
	 *
	 * @throws  RuntimeException
	 */
	public function storeLinesProcessed()
	{
		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__csvi_logs'))
			->set($this->db->quoteName('records') . ' = ' . $this->getLinesProcessed())
			->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $this->log->getLogId());
		$this->db->setQuery($query)->execute();

		return true;
	}

	/**
	 * Get the statistics of import.
	 *
	 * @return  array Details of imported data.
	 *
	 * @since   6.6.0
	 *
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 */
	public function getStatistics()
	{
		// Load the log details model
		require_once JPATH_ROOT . '/administrator/components/com_csvi/models/logdetails.php';
		$model = new CsviModelLogdetails;

		$result = $model->getStats($this->log->getLogId());

		return $result->resultstats;
	}

	/**
	 * Initialise the addons.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function initialiseAddon()
	{
		// Load the component helpers
		JLoader::import('joomla.filesystem.file');
		$component = $this->template->get('component');
		$extension = substr($component, 4);

		// Load the helper
		if (JFile::exists(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component . '/helper/' . $component . '.php'))
		{
			$helperName = ucfirst($component) . 'Helper' . ucfirst($component);
			$this->helper = new $helperName($this->template, $this->log, $this->fields, $this->db);
		}

		// Load the config helper
		if (JFile::exists(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component . '/helper/' . $component . '_config.php'))
		{
			$helperName = ucfirst($component) . 'Helper' . ucfirst($component) . '_config';
			$this->helperconfig = new $helperName;
		}
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
			$this->log->add('Setting system limits:');

			// Apply the new memory limits
			$execution_time = $this->template->get('max_execution_time');

			if (strlen($execution_time) > 0)
			{
				$this->log->add('Setting max_execution_time to ' . $execution_time . ' seconds');
				@ini_set('max_execution_time', $execution_time);
			}

			$memory_limit = $this->template->get('memory_limit');

			if ($memory_limit == '-1')
			{
				$this->log->add('Setting memory_limit to ' . $memory_limit);
				@ini_set('memory_limit', $memory_limit);
			}
			elseif (strlen($memory_limit) > 0)
			{
				$this->log->add('Setting memory_limit to ' . $memory_limit . 'M');
				@ini_set('memory_limit', $memory_limit . 'M');
			}

			$post_size = $this->template->get('post_max_size');

			if (strlen($post_size) > 0)
			{
				$this->log->add('Setting post_max_size to ' . $post_size . 'M');
				@ini_set('post_max_size', $post_size . 'M');
			}

			$file_size = $this->template->get('upload_max_filesize');

			if (strlen($file_size) > 0)
			{
				$this->log->add('Setting upload_max_filesize to ' . $file_size . 'M');
				@ini_set('upload_max_filesize', $file_size . 'M');
			}
		}
	}

	/**
	 * Check if there are more files to process.
	 *
	 * @return  bool  True if there are more files | False if there are no more files.
	 *
	 * @since   6.0
	 */
	public function moreFiles()
	{
		if (empty($this->processfolder))
		{
			return false;
		}

		$files = JFolder::files($this->processfolder);

		if (empty($files))
		{
			// There are no more files to process, remove the folder if needed
			$pos = strpos($this->processfolder, CSVIPATH_TMP);

			if ($pos !== false)
			{
				if (JFolder::exists($this->processfolder))
				{
					JFolder::delete($this->processfolder);
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * Do a little house keeping and clean up whatever is needed.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function cleanup()
	{
		// Remove the running process
		$query = $this->db->getQuery(true)
			->delete($this->db->quoteName('#__csvi_processes'))
			->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $this->runId);
		$this->db->setQuery($query)->execute();

		// Set the log end timestamp
		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__csvi_logs'))
			->set($this->db->quoteName('end') . ' = ' . $this->db->quote(JFactory::getDate()->toSql()))
			->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $this->log->getLogId());
		$this->db->setQuery($query)->execute();

		// Remove the temporary folder
		JLoader::import('joomla.filesystem.folder');

		if (JFolder::exists($this->processfolder))
		{
			JFolder::delete($this->processfolder);
		}

		// Trigger any plugins to run after import completes
		$options    = array();
		$options[]  = $this->template->getSettings();
		$dispatcher = new RantaiPluginDispatcher;
		$dispatcher->importPlugins('csvi', $this->db);
		$dispatcher->trigger('onImportComplete', $options);
	}

	/**
	 * Get administrator active template.
	 *
	 * @return  string  Name of administrator template.
	 *
	 * @since   6.6.0
	 *
	 * @throws  RuntimeException
	 */
	public function getAdminTemplate()
	{
		$query = $this->db->getQuery(true)
			->select('template')
			->from('#__template_styles')
			->where($this->db->quoteName('client_id') . ' = 1')
			->where($this->db->quoteName('home') . ' = 1');
		$this->db->setQuery($query)->execute();

		return $this->db->loadResult();
	}
}
