<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_tjfields'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Define constants
if (JVERSION < '3.0')
{
	// Define wrapper class
	define('TJFIELDS_WRAPPER_CLASS', "tjfields-wrapper techjoomla-bootstrap");

	// Other
	JHtml::_('behavior.tooltip');
}
else
{
	// Define wrapper class
	define('TJFIELDS_WRAPPER_CLASS', "tjfields-wrapper");

	// Tabstate
	JHtml::_('behavior.tabstate');

	// Other
	JHtml::_('behavior.tooltip');

	// Bootstrap tooltip and chosen js
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('formbehavior.chosen', 'select');
}

// Load techjoomla strapper
if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
{
	require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
	TjStrapper::loadTjAssets('com_tjfields');
}

$document = JFactory::getDocument();
$document->addStyleSheet(JUri::base() . 'components/com_tjfields/assets/css/tjfields.css');

// Include helper file
$helperPath = dirname(__FILE__) . '/helpers/tjfields.php';

if (!class_exists('TjfieldsHelper'))
{
	JLoader::register('TjfieldsHelper', $helperPath);
	JLoader::load('TjfieldsHelper');
}

// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JControllerLegacy::getInstance('Tjfields');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
