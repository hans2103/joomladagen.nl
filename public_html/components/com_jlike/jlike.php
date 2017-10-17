<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

If (JVERSION >= 3.0)
{
	JHtml::_('formbehavior.chosen', 'select');
}

jimport('joomla.filesystem.file');
$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (JFile::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_jlike');
}

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'components/com_jlike/assets/scripts/jlike.js');

// $document->addStyleSheet(JURI::base() . 'components/com_jlike/assets/css/like.css');
$document->addStyleSheet(JUri::root(true) . '/components/com_jlike/assets/css/jlike-tables.css');


$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

if (!class_exists('comjlikeHelper'))
{
	// Require_once $path;
	JLoader::register('comjlikeHelper', $helperPath);
	JLoader::load('comjlikeHelper');
}

$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

if (!class_exists('ComjlikeMainHelper'))
{
	// Require_once $path;
	JLoader::register('ComjlikeMainHelper', $helperPath);
	JLoader::load('ComjlikeMainHelper');
}

$helperPath = JPATH_SITE . '/components/com_jlike/helpers/integration.php';

if (!class_exists('comjlikeIntegrationHelper'))
{
	// Require_once $path;
	JLoader::register('comjlikeIntegrationHelper', $helperPath);
	JLoader::load('comjlikeIntegrationHelper');
}

$helperPath = JPATH_SITE . '/components/com_jlike/helpers/socialintegration.php';

if (!class_exists('socialintegrationHelper'))
{
	// Require_once $path;
	JLoader::register('socialintegrationHelper', $helperPath);
	JLoader::load('socialintegrationHelper');
}

// Load bootstrap on joomla > 3.0 ; This option will be usefull if site is joomla 3.0 but not a bootstrap template
if (JVERSION > '3.0')
{
	$params = JComponentHelper::getParams('com_jlike');
	$load_bootstrap = $params->get('load_bootstrap');

	// Check config
	if ($load_bootstrap)
	{
		// Load bootstrap CSS.
		JHtml::_('bootstrap.loadcss');
	}
}

// Load Global language constants to in .js file
ComjlikeHelper::getLanguageConstant();

require_once JPATH_COMPONENT . '/controller.php';

// Require specific controller if requested
if ($controller = JRequest::getWord('controller'))
{
	$path = JPATH_COMPONENT . '/controllers/' . $controller . '.php';

	if (file_exists($path))
	{
		require_once $path;
	}
	else
	{
		$controller = '';
	}
}

$controller = JControllerLegacy::getInstance('jlike');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
