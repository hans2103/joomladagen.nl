<?php
/**
 * @version    SVN: <svn_id>
 * @package    ActivityStream
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

JLoader::registerPrefix('Activitystream', JPATH_COMPONENT);
JLoader::register('ActivitystreamController', JPATH_COMPONENT . '/controller.php');

$lang = JFactory::getLanguage();
$lang->load('com_activitystream', JPATH_SITE);

$path = JPATH_SITE . '/components/com_activitystream/helper.php';

if (!class_exists('ComActivityStreamHelper'))
{
	JLoader::register('ComActivityStreamHelper', $path);
	JLoader::load('ComActivityStreamHelper');
}

$ComActivityStreamHelper = new ComActivityStreamHelper;

// Execute the task.
$controller = JControllerLegacy::getInstance('Activitystream');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
