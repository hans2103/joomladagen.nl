<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die;

// Add stylesheet
HTMLHelper::_('stylesheet', 'com_pwtseo/pwtseo.css', array('relative' => true, 'version' => 'auto'));

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_pwtseo'))
{
	throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

JLoader::register('PWTSEOHelper', __DIR__ . '/helpers/pwtseo.php');

// Execute the task
$controller = BaseController::getInstance('pwtseo');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
