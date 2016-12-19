<?php
/**
 * @version     backend/watchfulli.php 2014-10-21 12:53:00 UTC zanardi
 * @package     Watchful Client
 * @author      Watchful
 * @authorUrl   https://watchful.li
 * @copyright   Copyright (c) 2012-2016 watchful.li
 * @license     GNU/GPL v3 or later
 */

(defined('_JEXEC') or defined('JPATH_PLATFORM')) or die;

defined('WATCHFULLI_PATH') or define('WATCHFULLI_PATH', dirname(__FILE__));
defined('WATCHFULLI_ROOT') or define('WATCHFULLI_ROOT', WATCHFULLI_PATH);

require_once WATCHFULLI_PATH . '/classes/watchfulli.php';

Watchfulli::debug("Joomla version: ".Watchfulli::joomla()->RELEASE);

// import joomla controller library and get a controller instance
if ('1.5' == Watchfulli::joomla()->RELEASE)
{
    $canAdmin = in_array(JFactory::getUser()->gid, array(24, 25));

    require_once WATCHFULLI_ROOT . '/controller.php';
    $controller = new watchfulliController();
    $task = JRequest::getCmd('task');
}
else
{
    $canAdmin = JFactory::getUser()->authorise('core.manage', 'com_watchfulli');

    $task = JFactory::getApplication()->input->get('task');
    if (Watchfulli::joomla()->isCompatible('3.0'))
    {
        jimport('legacy.controller.legacy');
        try
        {
            $controller = JControllerLegacy::getInstance('watchfulli');
        }
        catch (Exception $ex)
        {
            Watchfulli::debug("Exception in JControllerLegacy::getInstance");
            die("Exception in JControllerLegacy::getInstance");
        }
    }
    else
    {
        jimport('joomla.application.component.controller');
        $controller = JController::getInstance('watchfulli');
    }
}

if ( ! $canAdmin && JFactory::getApplication()->isAdmin())
{
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$controller->execute($task);
$controller->redirect();
