<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://joomladagen.nl
 */

defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_conference'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
}

// Load the helper class for the submenu
require_once JPATH_ADMINISTRATOR . '/components/com_conference/helpers/conference.php';

JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_conference/models/fields');

try
{
	$input      = JFactory::getApplication()->input;
	$controller = JControllerLegacy::getInstance('conference');
	$controller->execute($input->get('task'));
	$controller->redirect();

}
catch (Exception $e)
{
	echo $e->getMessage();
}