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

// Set flag that this is a parent file.
// We are a valid Joomla entry point.
define('_JEXEC', 1);

// Setup the base path related constants.
define('JPATH_BASE', dirname(dirname(dirname(dirname(__DIR__)))));
define('JPATH_COMPONENT_ADMINISTRATOR', dirname(__DIR__));
define('JPATH_PLATFORM', JPATH_BASE . '/libraries');

// Bootstrap the application.
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_PLATFORM . '/import.legacy.php';

// Load the configuration file
require_once JPATH_CONFIGURATION . '/configuration.php';

// Register PHP namespaces
require_once JPATH_PLATFORM . '/vendor/autoload.php';
require_once JPATH_PLATFORM . '/classmap.php';
require_once JPATH_PLATFORM . '/cms.php';

// Import the JApplicationWeb class from the platform.
jimport('joomla.application.web');

// Setup the auto loader
JLoader::registerPrefix('Csvi', JPATH_BASE . '/administrator/components/com_csvi/');
JLoader::registerPrefix('Rantai', JPATH_BASE . '/administrator/components/com_csvi/rantai/');

// All Joomla loaded, set our exception handler
require_once JPATH_BASE . '/administrator/components/com_csvi/rantai/error/exception.php';

/**
 * The import runner.
 *
 * @package     CSVI
 * @subpackage  Imports
 * @since       6.0
 */
class Import extends JApplicationWeb
{
	/**
	 * The constructor.
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 */
	public function __construct()
	{
		// Get the base path
		$root = str_replace('/administrator/components/com_csvi/rantai.php', '', JUri::root());

		// Setup the environment
		$_SERVER['SCRIPT_NAME'] = $root . '/administrator/index.php';
		$_SERVER['REQUEST_URI'] = $root . '/administrator/index.php?option=com_csvi';

		// Call the parent __construct method so it bootstraps the application class.
		parent::__construct();

		// Define the tmp folder
		jimport('joomla.filesystem.path');

		if (!defined('CSVIPATH_TMP'))
		{
			$tmpPath = $this->config->get('tmp_path');

			if (!is_dir($tmpPath))
			{
				$tmpPath = JPath::clean(JPATH_SITE . '/tmp', '/');
			}

			define('CSVIPATH_TMP', $tmpPath . '/com_csvi');
		}

		if (!defined('CSVIPATH_DEBUG'))
		{
			$logPath = $this->config->get('log_path');

			if (!is_dir($logPath))
			{
				$logPath = JPath::clean(JPATH_SITE . '/logs', '/');
			}

			define('CSVIPATH_DEBUG', $logPath);
		}

		// Load the JFactory application
		JFactory::getApplication('administrator');

		// Merge the default translation with the current translation
		$conf   = $this->config;
		$locale = $conf->get('language');
		$debug  = $conf->get('debug_lang');
		$jlang  = JLanguage::getInstance($locale, $debug);

		$jlang->load('com_csvi', JPATH_COMPONENT_ADMINISTRATOR, 'en-GB', true);
		$jlang->load('com_csvi', JPATH_COMPONENT_ADMINISTRATOR, $jlang->getDefault(), true);
		$jlang->load('com_csvi', JPATH_COMPONENT_ADMINISTRATOR, null, true);
	}

	/**
	 * Execute the import.
	 *
	 * @return  void
	 *
	 * @since   6.0
	 */
	protected function doExecute()
	{
		// 1. Load the model
		require_once JPATH_BASE . '/administrator/components/com_csvi/rantai/model.php';
		$model = new RantaiImportModel($this->input);

		try
		{
			// Set the result
			$result = array();

			// Get the ID
			$runId = $this->input->getInt('runId', 0);

			if ($runId)
			{
				// 1. Initialise the import
				$model->initialiseImport($runId);

				// 2. onBeforeImport
				$model->onBeforeImport();

				// Load the table
				require_once JPATH_BASE . '/administrator/components/com_csvi/tables/default.php';

				// Fire the import
				if ($model->runImport())
				{
					// 5. Build the result
					$result['process'] = true;
					$result['records'] = $model->getLinesProcessed();
				}
				else
				{
					// Check if there are more files to process
					if ($model->moreFiles())
					{
						$result['process'] = true;
						$result['records'] = $model->getLinesProcessed();
					}
					else
					{
						// Clean up after ourselves
						$model->cleanup();

						// Remove the extra path from the url
						$url = str_replace('/administrator/components/com_csvi/', '/', JUri::root());
						$returnUrl = $url . 'administrator/index.php?option=com_csvi&view=imports';

						$result['process'] = false;
						$result['url'] = 'administrator/index.php?option=com_csvi&view=logdetails&run_id=' . $model->getRunId() . '&return=' . base64_encode($returnUrl);
					}
				}

				$model->onAfterImport();

				// Store the lines processed
				$model->storeLinesProcessed();
			}
			else
			{
				$result['error'] = true;
				$result['process'] = false;
				$result['url'] = 'administrator/index.php?option=com_csvi&view=imports';
				$result['message'] = JText::_('COM_CSVI_NO_RUNID_FOUND');
			}
		}
		catch (Exception $e)
		{
			$result['error'] = true;
			$result['process'] = false;
			$result['url'] = 'administrator/index.php?option=com_csvi&view=imports';
			$result['message'] = $e->getMessage();
		}

		// Output the result
		$this->appendBody(json_encode($result));
	}
}

JApplicationWeb::getInstance('Import')->execute();
