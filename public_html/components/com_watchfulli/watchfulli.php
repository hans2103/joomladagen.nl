<?php
/**
 * @version     frontend/watchfulli.php 2015-01-22 16:37:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */

(defined('_JEXEC') or defined('JPATH_PLATFORM')) or die;

// define our base paths
defined('WATCHFULLI_PATH') or define('WATCHFULLI_PATH', JPATH_ADMINISTRATOR . '/components/com_watchfulli');
defined('WATCHFULLI_ROOT') or define('WATCHFULLI_ROOT', dirname(__FILE__));

// Enable class autoloader, taking care of including the default Joomla one
if (function_exists('__autoload'))
{
    spl_autoload_register('__autoload');
}
require_once JPATH_COMPONENT_ADMINISTRATOR . '/autoloader.php';
spl_autoload_register('classLoader');

if (isset($_GET['debug']))
{
    define('WATCHFULLI_DEBUG', 1);
    $debug = new stdClass();
    $debug->time['1. Start'] = time();
}

// ensure there's no notices or anything
@error_reporting(0);
@ini_set('error_reporting', 0);

// just use admin index, as it does the same thing & is based on the two paths defined above
require_once WATCHFULLI_PATH . '/watchfulli.php';
