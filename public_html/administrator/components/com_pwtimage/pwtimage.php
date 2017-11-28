<?php
/**
 * @package    Pwtimage
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die;

try
{
	// Get the input object
	$input = Factory::getApplication()->input;

	// Global helper
	require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/pwtimage.php';

	$controller = BaseController::getInstance('Pwtimage');
	$controller->execute($input->get('task', 'pwtimage'));
	$controller->redirect();
}
catch (Exception $e)
{
	// Check if we are in display format
	$format = $input->getCmd('format', $input->getCmd('tmpl', null));

	if (0 === strlen($format))
	{
		JToolbarHelper::title(Text::_('com_pwtimage'), 'image');
		Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
	}
	else
	{
		echo $e->getMessage();
	}
}
