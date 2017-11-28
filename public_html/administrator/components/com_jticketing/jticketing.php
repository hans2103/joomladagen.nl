<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_jticketing'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

define('JTICKETING_WRAPPER_CLASS', 'jticketing-wrapper');

if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
{
	require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
	TjStrapper::loadTjAssets('com_jticketing');
}

// Require_once( JPATH_COMPONENT.DS.'controller.php' );
require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";
require_once JPATH_SITE . "/components/com_jticketing/helpers/frontendhelper.php";
require_once JPATH_SITE . "/components/com_jticketing/helpers/order.php";

// Get bootstrap
jimport('joomla.html.html.bootstrap');

$doc = JFactory::getDocument();
$doc->addStyleSheet(JUri::base() . 'components/com_jticketing/assets/css/jticketing.css');

$JticketingHelperadmin = JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php';

if (!class_exists('JticketingHelperadmin'))
{
	JLoader::register('JticketingHelperadmin', $JticketingHelperadmin);
	JLoader::load('JticketingHelperadmin');
}

$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

if (!class_exists('jticketingfrontendhelper'))
{
	JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
	JLoader::load('jticketingfrontendhelper');
}

$jteventHelper = JPATH_ROOT . '/components/com_jticketing/helpers/event.php';

if (!class_exists('jteventHelper'))
{
	JLoader::register('jteventHelper', $jteventHelper);
	JLoader::load('jteventHelper');
}

$mediaHelperPath = JPATH_SITE . '/components/com_jticketing/helpers/media.php';

if (!class_exists('jticketingMediaHelper'))
{
	JLoader::register('jticketingMediaHelper', $mediaHelperPath);
	JLoader::load('jticketingMediaHelper');
}

$JticketingmainHelper = JPATH_SITE . '/components/com_jticketing/helpers/main.php';

if (!class_exists('jticketingmainhelper'))
{
	// Require_once $path;
	JLoader::register('jticketingmainhelper', $JticketingmainHelper);
	JLoader::load('jticketingmainhelper');
}

$JticketingmainHelper = JPATH_SITE . '/components/com_jticketing/helpers/order.php';

if (!class_exists('JticketingOrdersHelper'))
{
	// Require_once $path;
	JLoader::register('JticketingOrdersHelper', $JticketingOrdersHelper);
	JLoader::load('JticketingOrdersHelper');
}

// Define wrapper class
define('COM_JTICKETING_WRAPPER_CLASS', "jticketing-wrapper");

// Tabstate
JHtml::_('behavior.tabstate');

// Bootstrap tooltip and chosen js
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$input = JFactory::getApplication()->input;
$get   = $input->get;

$com_params         = JComponentHelper::getParams('com_jticketing');
$siteadmin_comm_per = $com_params->get('siteadmin_comm_per');

// Include dependancies.
jimport('joomla.application.component.controller');

$controller = JControllerLegacy::getInstance('Jticketing');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();

$document = JFactory::getDocument();
$document->addScript(JUri::root(true) . '/media/com_jticketing/js/jticketing.js');
