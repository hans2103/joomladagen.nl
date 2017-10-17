<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Load helper File module helper object
require_once dirname(__FILE__) . '/helper.php';
$modJTicketingHelper = new modJTicketingHelper;

// Get Params
$orderby          = $params->get('event_order_by');
$orderby_dir      = $params->get('order_dir');
$no_of_event_show = $params->get('no_of_event_show');
$featured_event   = $params->get('featured_event');
$ticket_type      = $params->get('ticket_type');
$image            = $params->get('image');

$com_params = JComponentHelper::getParams('com_jticketing');
$integration = $com_params->get('integration');

// Use font-awesome library
JHtml::stylesheet(JUri::root() . 'media/techjoomla_strapper/vendors/font-awesome/css/font-awesome.min.css', array(), true);

if ($integration != 2)
{
	echo JText::_('MOD_JTICKETING_EVENT_NATIVE_INTEGRATION');

	return;
}

require JModuleHelper::getLayoutPath('mod_jticketing_event');
