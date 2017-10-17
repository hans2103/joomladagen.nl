<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

// Define directory separator
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if (file_exists(JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php'))
{
	require_once JPATH_ROOT . '/media/techjoomla_strapper/tjstrapper.php';
	TjStrapper::loadTjAssets('com_jticketing');
}

// Require the base controller
require_once JPATH_COMPONENT . '/controller.php';
require_once JPATH_COMPONENT . '/helpers/main.php';
require_once JPATH_COMPONENT . '/helpers/common.php';


$app        = JFactory::getApplication();
$document   = JFactory::getDocument();
$com_params = JComponentHelper::getParams('com_jticketing');
$root_url   = JUri::root();

// Load various helpers
$path                     = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';
$jticketingfrontendhelper = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';
$JTicketingIntegrationsHelperPath   = JPATH_ROOT . '/components/com_jticketing/helpers/integrations.php';
$helperPath               = JPATH_SITE . '/components/com_jticketing/helpers/event.php';
$mediaHelperPath          = JPATH_SITE . '/components/com_jticketing/helpers/media.php';
$field_manager_path       = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';
$JTRouteHelper            = JPATH_SITE . '/components/com_jticketing/helpers/route.php';

$JticketingCommonHelper = JPATH_SITE . '/components/com_jticketing/helpers/common.php';


if (!class_exists('JticketingCommonHelper'))
{
	JLoader::register('JticketingCommonHelper', $JticketingCommonHelper);
	JLoader::load('JticketingCommonHelper');
}

if (!class_exists('jticketingmainhelper'))
{
	JLoader::register('jticketingmainhelper', $path);
	JLoader::load('jticketingmainhelper');
}

if (!class_exists('jticketingfrontendhelper'))
{
	JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
	JLoader::load('jticketingfrontendhelper');
}

if (!class_exists('JTicketingIntegrationsHelper'))
{
	JLoader::register('JTicketingIntegrationsHelper', $JTicketingIntegrationsHelperPath);
	JLoader::load('JTicketingIntegrationsHelper');
}

if (!class_exists('jteventHelper'))
{
	JLoader::register('jteventHelper', $helperPath);
	JLoader::load('jteventHelper');
}

if (file_exists($field_manager_path))
{
	if (!class_exists('TjfieldsHelper'))
	{
		JLoader::register('TjfieldsHelper', $field_manager_path);
		JLoader::load('TjfieldsHelper');
	}
}

if (!class_exists('jticketingMediaHelper'))
{
	JLoader::register('jticketingMediaHelper', $mediaHelperPath);
	JLoader::load('jticketingMediaHelper');
}

if (!class_exists('JTRouteHelper'))
{
	JLoader::register('JTRouteHelper', $JTRouteHelper);
	JLoader::load('JTRouteHelper');
}

// Load JHtml libraries
if (version_compare(JVERSION, '3.0', 'lt'))
{
	JHtml::_('behavior.tooltip');
}
else
{
	// Tabstate
	JHtml::_('behavior.tabstate');

	// Bootstrap tooltip and chosen js
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('formbehavior.chosen', 'select');

	$load_bootstrap = $com_params->get('load_bootstrap');

	if ($load_bootstrap)
	{
		// Load bootstrap CSS and JS.
		JHtml::_('bootstrap.loadcss');
		JHtml::_('bootstrap.framework');
	}
}

$input = JFactory::getApplication()->input;
$view  = $input->get('view');

if ($view == 'order')
{
	$document->addStyleSheet($root_url . '/media/com_jticketing/css/fuelux2.3.1.css');
}

$jticketingfrontendhelper = new Jticketingfrontendhelper;
$jticketingfrontendhelper->loadjticketingAssetFiles();
$jticketingmainhelper = new jticketingmainhelper;

// Load Global language constants to in .js file
$JticketingCommonHelper = new JticketingCommonHelper;
$JticketingCommonHelper->getLanguageConstant();

// Frontend css
$document->addStyleSheet($root_url . 'media/com_jticketing/css/jticketing.css');
$document->addStyleSheet($root_url . 'media/com_jticketing/css/jt-tables.css');

// Load common jticketing js
$document->addScript(JUri::root(true) . '/media/com_jticketing/js/jticketing.js');

// Use font-awesome library
JHtml::stylesheet($root_url . 'media/techjoomla_strapper/vendors/font-awesome/css/font-awesome.min.css', array(), true);

// Execute the task.
$controller = JControllerLegacy::getInstance('Jticketing');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
