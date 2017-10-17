<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');

$session   = JFactory::getSession();
$session->set('JT_orderid', '');
$session->set("JT_fee", '');
echo $msg = JText::_('OPERATION_CANCELLED');

$user = JFactory::getUser();
$input   = JFactory::getApplication()->input;
$eventid = $input->get('eventid', '', 'INT');

$jticketingMainHelper = new jticketingmainhelper;
$itemId      = $jticketingMainHelper->getItemId($linkcreateevent);
$integration = $jticketingMainHelper->getIntegration();
$linkCreateEvent = '';

if ($integration == 2)
{
	$linkCreateEvent = JRoute::_(JUri::base() . '?option=com_jticketing&view=events' . '&Itemid=' . $itemId);
}

if ($integration == 3)
{
	$linkCreateEvent = JRoute::_(JUri::base() . '?option=com_jevents&task=month.calendar' . '&Itemid=' . $itemId);
}

if ($integration == 1)
{
	$linkCreateEvent = JRoute::_(JUri::base() . '?option=com_community&view=events&task=viewevent' . '&Itemid=' . $itemId);
}

echo "<div style='float:right'><a href='" . $linkCreateEvent . "'>" . JText::_('BACK') . "</a></div>";
