<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2014 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       http://www.jdideal.nl
 */

/**
 * This is a CRON script which should be called from the command-line, not the
 * web. For example something like:
 * /usr/bin/php /path/to/site/cli/statusupdate.php --host=http://www.example.com/
 */

// Make sure we're being called from the command line, not a web interface
if (array_key_exists('REQUEST_METHOD', $_SERVER))
{
	die();
}

// Set flag that this is a parent file.
define('_JEXEC', 1);

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', 1);

if (file_exists((dirname(__DIR__)) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

if (file_exists(JPATH_LIBRARIES . '/import.legacy.php'))
{
	require_once JPATH_LIBRARIES . '/import.legacy.php';
}
elseif (file_exists(JPATH_LIBRARIES . '/import.php'))
{
	require_once JPATH_LIBRARIES . '/import.php';
}

require_once JPATH_LIBRARIES . '/cms.php';

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

// Import necessary classes not handled by the autoloaders
jimport('joomla.environment.uri');
jimport('joomla.event.dispatcher');
jimport('joomla.utilities.utility');
jimport('joomla.utilities.arrayhelper');
jimport('joomla.environment.request');
jimport('joomla.application.component.helper');
jimport('joomla.application.component.helper');

// Fool Joomla into thinking we're in the administrator with com_app as active component
JFactory::getApplication('administrator');
JFactory::getApplication()->input->set('option', 'com_jdidealgateway');

// Set our component define
define('JPATH_COMPONENT', JPATH_BASE . '/components/com_jdidealgateway');
define('JPATH_COMPONENT_SITE', JPATH_BASE . '/components/com_jdidealgateway');
define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/com_jdidealgateway');

/**
 * Runs a cron job
 *
 * --arguments can have any value
 * -arguments are boolean
 *
 * @since  3.1
 */
class Statusupdate extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0
	 */
	public function doExecute()
	{
		// Merge the default translation with the current translation
		$jlang = JFactory::getLanguage();
		$jlang->load('com_jdidealgateway', JPATH_COMPONENT_SITE, 'en-GB', true);
		$jlang->load('com_jdidealgateway', JPATH_COMPONENT_SITE, $jlang->getDefault(), true);
		$jlang->load('com_jdidealgateway', JPATH_COMPONENT_SITE, null, true);

		// Check if we are being asked for help
		$help = $this->input->get('h', false, 'bool');

		if ($help)
		{
			$this->out(JText::_('COM_JDIDEALGATEWAY_CRON_HELP'));
			$this->out('============================');
			$this->out();
			$this->out(JText::_('COM_JDIDEALGATWAY_USE_CRON'));
			$this->out();
		}
		else
		{
			$this->out(JText::_('COM_JDIDEALGATEWAY_START_SCRIPT'));

			require_once JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/helpers/jdidealgateway.php';
			$jdideal = new JdIdealgatewayHelper;
			$jinput = JFactory::getApplication()->input;
			$host = $this->input->getString('host', '');

			$cids = $this->loadTransactions();

			$this->out(JText::sprintf('COM_JDIDEALGATEWAY_PROCESS_TRANSACTIONS', count($cids)));

			// Loop through all IDs and call the notify script
			foreach ($cids as $cid)
			{
				$url = false;

				// Load the details
				$details = $jdideal->getDetails($cid);

				// Construct the URL
				switch ($jdideal->ideal)
				{
					case 'advanced':
						$url = $host . 'components/com_jdidealgateway/models/notify.php?trxid=' . $details->trans . '&ec=' . $details->id;
						break;
				}

				if ($url)
				{
					$this->out(JText::sprintf('COM_JDIDEALGATEWAY_PROCESS_URL', $url));

					$ch = curl_init();
					$timeout = 5;
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

					$res = curl_exec($ch);

					if ($res === false)
					{
						$jinput->set('logid', $details->id);
						$jdideal->log('Curl error: ' . curl_error($ch));
						$this->out(curl_error($ch));
					}

					curl_close($ch);
				}
			}

			// Set the last runtime
			$this->setRuntime();

			$this->out(JText::_('COM_JDIDEALGATEWAY_END_SCRIPT'));
		}
	}

	/**
	 * Load the transactions to check.
	 *
	 * @return  array  List of IDs to check.
	 *
	 * @since   3.1
	 */
	private function loadTransactions()
	{
		$db = JFactory::getDbo();

		// Find last run time
		$query = $db->getQuery(true)
			->select($db->quoteName('ideal') . ',' . $db->quoteName('payment_extrainfo'))
			->from($db->quoteName('#__jdidealgateway_config'))
			->where($db->quoteName('published') . ' = 1');
		$db->setQuery($query);
		$config = $db->loadObject();

		if (!empty($config))
		{
			$configuration = new JRegistry($config->payment_extrainfo);

			$lastrun = $configuration->get('lastrun', '1970-01-01 00:00:00');
		}
		else
		{
			$lastrun = '1970-01-01 00:00:00';
		}

		// Check if we need to override lastrun
		$lastruncli = $this->input->getString('lastrun', false);

		if ($lastruncli)
		{
			$lastrun = $lastruncli;
		}

		// Load the transactions
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__jdidealgateway_logs'))
			->where($db->quoteName('result') . ' IS NULL')
			->where('LENGTH(' . $db->quoteName('trans') . ') > 0')
			->where($db->quoteName('date_added') . ' > ' . $db->quote($lastrun));
		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Set the last runtime.
	 *
	 * @return  void.
	 *
	 * @since   3.1
	 */
	private function setRuntime()
	{
		$db = JFactory::getDbo();

		// Find last run time
		$query = $db->getQuery(true)
			->select($db->quoteName('ideal') . ',' . $db->quoteName('payment_extrainfo'))
			->from($db->quoteName('#__jdidealgateway_config'))
			->where($db->quoteName('published') . ' = 1');
		$db->setQuery($query);
		$config = $db->loadObject();

		if (!empty($config))
		{
			$configuration = new JRegistry($config->payment_extrainfo);
			$configuration->set('lastrun', JFactory::getDate()->toSql());

			$query = $db->getQuery(true)
				->update($db->quoteName('#__jdidealgateway_config'))
				->set($db->quoteName('payment_extrainfo') . ' = ' . $db->quote($configuration->toString()))
				->where($db->quoteName('published') . ' = 1');
			$db->setQuery($query)->execute();
		}
	}
}

try
{
	JApplicationCli::getInstance('Statusupdate')->execute();
}
catch (Exception $e)
{

	echo $e->getMessage() . "\r\n";

	exit($e->getCode());
}
