<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_jdidealgateway'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Get the input object
$jinput = JFactory::getApplication()->input;

// Add stylesheet
JHtml::stylesheet('com_jdidealgateway/jdidealgateway.css', false, true);

// Register our namespace
JLoader::registerNamespace('Jdideal', JPATH_LIBRARIES);

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/jdidealgateway.php';

// Create the controller
try
{
	$controller = JControllerLegacy::getInstance('jdidealgateway');
	$controller->execute($jinput->get('task'));
	$controller->redirect();

	// Show the footer
	$format = $jinput->getCmd('format', $jinput->getCmd('tmpl', null));

	if (0 === count($format))
	{
		?>
			<div class="span-12 center">
				<a href="https://jdideal.nl/" target="_blank">JD iDEAL Gateway</a> 4.8.0 | Copyright (C) 2009 - <?php echo date('Y', time()); ?>
				<a href="http://www.rolandd.com/" target="_blank">RolandD Cyber Produksi</a>
		</div>
		<?php
	}
}
catch (Exception $e)
{
	JFactory::getApplication()->redirect('index.php?option=com_jdidealgateway', $e->getMessage(), 'error');
}
