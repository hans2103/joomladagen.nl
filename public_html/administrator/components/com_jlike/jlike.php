<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */


// No direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_jlike'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

jimport('joomla.filesystem.file');
$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

if (JFile::exists($tjStrapperPath))
{
	require_once $tjStrapperPath;
	TjStrapper::loadTjAssets('com_jlike');
}

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root() . 'components/com_jlike/assets/css/like.css');
$document->addStyleSheet(JURI::base() . 'components/com_jlike/assets/css/like.css');
$helperPath = JPATH_SITE . '/' . 'components/com_jlike/helper.php';

if (!class_exists('comjlikeHelper'))
{
	// Require_once $path;
	JLoader::register('comjlikeHelper', $helperPath);
	JLoader::load('comjlikeHelper');
}
// Load laguage constant in javascript
ComjlikeHelper::getLanguageConstant();

$helperPath = JPATH_ADMINISTRATOR . '/' . 'components/com_jlike/helpers/jlike.php';

if (!class_exists('JLikeHelper'))
{
	// Require_once $path;
	JLoader::register('JLikeHelper', $helperPath);
	JLoader::load('JLikeHelper');
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

// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JControllerLegacy::getInstance('Jlike');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
