<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

// Get the Joomla framework
define('_JEXEC', 1);

// Setup the path related constants.
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', str_ireplace('cli', '', __DIR__));
define('JPATH_ROOT', JPATH_BASE);
define('JPATH_SITE',			JPATH_ROOT);
define('JPATH_CONFIGURATION',	JPATH_ROOT);
define('JPATH_ADMINISTRATOR',	JPATH_ROOT . 'administrator');
define('JPATH_LIBRARIES',		JPATH_ROOT . 'libraries');
define('JPATH_PLUGINS',			JPATH_ROOT . 'plugins');
define('JPATH_INSTALLATION',	JPATH_ROOT . 'installation');
define('JPATH_THEMES',			JPATH_BASE . 'templates');
define('JPATH_CACHE',			JPATH_BASE . 'cache');
define('JPATH_MANIFESTS',		JPATH_ADMINISTRATOR . '/manifests');
define('JPATH_COMPONENT_ADMINISTRATOR',	JPATH_ADMINISTRATOR . '/components/com_jdidealgateway');

// Load the library importer.
require_once JPATH_BASE . '/includes/framework.php';
require_once JPATH_LIBRARIES . '/import.php';
require_once JPATH_CONFIGURATION . '/configuration.php';

// Import library dependencies.
jimport('joomla.application.application');
jimport('joomla.utilities.utility');
jimport('joomla.language.language');
jimport('joomla.utilities.string');
jimport('joomla.factory');

// Create the Application
$app = JFactory::getApplication('site');
$jinput = JFactory::getApplication()->input;

// Load the language files
$jlang = JFactory::getLanguage();
$jlang->load('com_jdidealgateway', JPATH_SITE . '/components/com_jdidealgateway/', 'en-GB', true);
$jlang->load('com_jdidealgateway', JPATH_SITE . '/components/com_jdidealgateway/', $jlang->getDefault(), true);
$jlang->load('com_jdidealgateway', JPATH_SITE . '/components/com_jdidealgateway/', null, true);

// Setup the autoloader
JLoader::registerNamespace('Jdideal', JPATH_LIBRARIES);

// Load JD iDEAL
$statusRequest = new \Jdideal\Status\Request;
// https://www.dungensemolen.nl/cli/notify.php?transaction_id=1511203263&order_id=14&status=COMPLETED&signature=f39558230b3558d234eb9747951efe570641ba838b6682bcad5d201b17edc641771b21c6906e645a1c2ede72816d08c0d4693af502a0aa962e2d0326a9d0196a
try
{
	$result = $statusRequest->process();

	if ($result['isCustomer'])
	{
		$app->redirect($result['url'], $result['message'], $result['level']);
	}
	else
	{
		echo $result['status'];
	}
}
catch (Exception $e)
{
	// Write the error log
	$statusRequest->writeErrorLog($e->getMessage());

	try
	{
		$customer = $statusRequest->whoIsCalling();

		if ($customer)
		{
			echo $e->getMessage();
		}
		else
		{
			echo 'NOK';
		}
	}
	catch (Exception $e)
	{
		// Cannot determine if customer or PSP is calling, just show the message
		echo $e->getMessage();
	}


}

