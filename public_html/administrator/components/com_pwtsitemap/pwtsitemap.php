<?php
/**
 * @package    Pwtsitemap
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

JLoader::register('PwtSitemapHelper', __DIR__ . '/helpers/pwtsitemap.php');
JLoader::register('PwtSitemapMenuHelper', __DIR__ . '/helpers/pwtsitemapmenu.php');
JLoader::register('JHtmlPwtSitemap', __DIR__ . '/helpers/html/pwtsitemap.php');

$controller = JControllerLegacy::getInstance('PwtSitemap');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
