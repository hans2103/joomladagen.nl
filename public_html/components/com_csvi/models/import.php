<?php
/**
 * @package     CSVI
 * @subpackage  Import
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Front-end import model.
 *
 * @package     CSVI
 * @subpackage  Import
 * @since       6.6.0
 */
class CsviModelImport extends JModelLegacy
{
	/**
	 * Settings class
	 *
	 * @var    CsviHelperSettings
	 * @since  6.6.0
	 */
	private $settings;

	/**
	 * Database driver
	 *
	 * @var    JDatabaseDriver
	 * @since  6.6.0
	 */
	private $db;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  6.6.0
	 */
	protected $log;

	/**
	 * CSVI helper
	 *
	 * @var    CsviHelperCsvi
	 * @since  6.6.0
	 */
	protected $csviHelper;

	/**
	 * CSVI template
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.6.0
	 */
	protected $template;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   6.6.0
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		// Load the database handler
		$this->db = JFactory::getDbo();

		// Load the global CSVI settings
		$this->settings = new CsviHelperSettings($this->db);

		// Load the logger
		$this->log = new CsviHelperLog($this->settings, $this->db);

		parent::__construct($config);
	}

	/**
	 * Prepare the basics for the import.
	 *
	 * @param   int  $templateId  The ID of the template to use for import.
	 *
	 * @return  void.
	 *
	 * @since   6.6.0
	 *
	 * @throws  Exception
	 */
	public function prepareImport($templateId)
	{
		// Prepare the log
		$this->log = new CsviHelperLog($this->settings, $this->db);

		// Load the CSVI helper
		$this->csviHelper = new CsviHelperCsvi;
		$this->csviHelper->initialise($this->log);

		// Load the template
		$this->template = new CsviHelperTemplate($templateId);
	}

	/**
	 * Invoke a running import or export.
	 *
	 * @return  int  The run ID
	 *
	 * @since   6.6.0
	 *
	 * @throws  Exception
	 */
	public function initialiseRun()
	{
		// Setup the logger
		$this->log->setActive($this->template->getLog());
		$this->log->setAddon($this->template->get('component'));
		$this->log->setAction($this->template->get('action'));
		$this->log->setActionType($this->template->get('operation'));
		$this->log->setTemplateName($this->template->getName());
		$this->log->initialise();

		// Empty the processed table
		$this->db->truncateTable('#__csvi_processed');

		// Process the file to use for import
		$source        = new CsviHelperSource;
		$data          = array('file' => $this->template->get('local_csv_file'));
		$location      = ($this->template->get('source') === 'fromupload') ? 'fromserver' : $this->template->get('source');
		$processFolder = '';

		if ($location !== 'fromdatabase')
		{
			$processFolder = $source->validateFile($location, $data, $this->template, $this->log, $this->csviHelper);
		}

		// Assemble the columns and values
		$columns = array($this->db->quoteName('csvi_template_id'), $this->db->quoteName('csvi_log_id'), $this->db->quoteName('userId'));
		$values = $this->template->getId() . ', ' . (int) $this->log->getLogId() . ', 0';

		// Check if the process file exists
		if ($processFolder)
		{
			$columns[] = $this->db->quoteName('processfolder');
			$values .= ', ' . $this->db->quote($processFolder);
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
	 * Returns an instance of the template.
	 *
	 * @return  CsviHelperTemplate
	 *
	 * @since   6.6.0
	 */
	public function getTemplate()
	{
		return $this->template;
	}
}
