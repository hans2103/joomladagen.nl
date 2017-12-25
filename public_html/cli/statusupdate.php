<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
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

			// Register our namespace
			JLoader::registerNamespace('Jdideal', JPATH_LIBRARIES);

			/** @var Jdideal\Gateway $jdideal */
			$jdideal = new Jdideal\Gateway;
			$host = $this->input->getString('host', '');

			$cids = $this->loadTransactions();

			$this->out(JText::sprintf('COM_JDIDEALGATEWAY_PROCESS_TRANSACTIONS', count($cids)));

			// Need to set the processed status to 0, to make sure they get processed again
			if (count($cids) > 0)
			{
				$this->setProcessedStatus($cids);
			}

			// Loop through all IDs and call the notify script
			foreach ($cids as $cid)
			{
				try
				{
					$url = false;

					// Load the details
					$details = $jdideal->getDetails($cid);

					$this->out(JText::_('COM_JDIDEALGATEWAY_ORDERID') . $details->order_id);
					$this->out(JText::_('COM_JDIDEALGATEWAY_ORDERNUMBER') . $details->order_number);

					// Construct the URL
					switch ($jdideal->psp)
					{
						case 'advanced':
							$url = $host . 'cli/notify.php?trxid=' . $details->trans . '&ec=' . $details->id;
							break;
						case 'buckaroo':
							$url = $host . 'cli/notify.php?transactionId=' . $details->trans . '&add_logid=' . $details->id;
							break;
						case 'kassacompleet':
							$url = $host . 'cli/notify.php?order_id=' . $details->trans;
							break;
						case 'mollie':
							if (!$details->paymentId)
							{
								throw new InvalidArgumentException(JText::_('COM_JDIDEALGATEWAY_MISSING_PAYMENT_ID'));
							}

							$url = $host . 'cli/notify.php?transaction_id=' . $details->trans . '&id=' . $details->paymentId;
							break;
						case 'onlinekassa':
							if (!$details->paymentId)
							{
								throw new InvalidArgumentException(JText::_('COM_JDIDEALGATEWAY_MISSING_PAYMENT_ID'));
							}

							$url = $host . 'cli/notify.php?' . $details->paymentId;
							break;
						case 'sisow':
							$url = $host . 'cli/notify.php?trxid=' . $details->trans . '&callback=1';
							break;
						case 'targetpay':
							$url = $host . 'cli/notify.php?trxid=' . $details->trans;
							break;
					}

					if ($url)
					{
						try
						{
							$this->out(JText::sprintf('COM_JDIDEALGATEWAY_PROCESS_URL', $url));

							$http = JHttpFactory::getHttp(null, array('curl', 'stream'));

							/** @var JHttpResponse $response */
							$response = $http->get($url);

							$message = JText::_('COM_JDIDEALGATEWAY_CHECKED_TRANSACTION_OK');

							if (500 === $response->code)
							{
								$message = JText::sprintf('COM_JDIDEALGATEWAY_CHECKED_TRANSACTION_ERROR', $response->body);
							}
						}
						catch (Exception $e)
						{
							$jdideal->log($e->getMessage(), $details->id);
							$message = $e->getMessage();
						}
					}
				}
				catch (Exception $e)
				{
					$message = $e->getMessage();
				}

				$this->out($message);
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
	 *
	 * @throws  RuntimeException
	 */
	private function loadTransactions()
	{
		// Find last run time
		$configuration = JComponentHelper::getParams('com_jdidealgateway');

		$lastrun = $configuration->get('lastrun', '1970-01-01 00:00:00');

		// Check if we need to override lastrun
		$lastruncli = $this->input->getString('lastrun', false);

		if ($lastruncli)
		{
			$lastrun = $lastruncli;
		}

		$this->out('Last run date ' . $lastrun);

		// Get any extra results to check
		$statuses = explode(',', $this->input->getString('status', ''));

		// Load the transactions
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__jdidealgateway_logs'))
			->where('LENGTH(' . $db->quoteName('trans') . ') > 0')
			->where($db->quoteName('date_added') . ' > ' . $db->quote($lastrun));

		// Add the extra statuses
		if (is_array($statuses))
		{
			$where = '(' . $db->quoteName('result') . ' IS NULL';

			foreach ($statuses as $status)
			{
				$where .= ' OR ' . $db->quoteName('result') . ' = ' . $db->quote($status);
			}

			$where .= ')';

			$query->where($where);
		}
		else
		{
			$query->where($db->quoteName('result') . ' IS NULL');
		}

		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Set the processed status to 0 to make sure they get updated again.
	 *
	 * @param   array  $cids  The IDs of the transactions to reset the processd status for
	 *
	 * @return  void.
	 *
	 * @since   3.1
	 *
	 * @throws  RuntimeException
	 */
	private function setProcessedStatus($cids)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__jdidealgateway_logs'))
			->set($db->quoteName('processed') . ' = 0')
			->where($db->quoteName('id') . ' IN (' . implode(',', $cids) . ')');
		$db->setQuery($query)->execute();
	}

	/**
	 * Set the last runtime.
	 *
	 * @return  void.
	 *
	 * @since   3.1
	 *
	 * @throws  RuntimeException
	 */
	private function setRuntime()
	{
		$configuration = JComponentHelper::getParams('com_jdidealgateway');
		$configuration->set('lastrun', JFactory::getDate()->toSql());

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . ' = ' . $db->quote($configuration->toString()))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'))
			->where($db->quoteName('element') . ' = ' . $db->quote('com_jdidealgateway'));
		$db->setQuery($query)->execute();
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
