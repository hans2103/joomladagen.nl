<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
$input           = JFactory::getApplication()->input;
$post            = $input->post;
$com_params      = JComponentHelper::getParams('com_jticketing');
$integration     = $com_params->get('integration');
$allow_buy_guest = $com_params->get('allow_buy_guest');
$session         = JFactory::getSession();
$input           = JFactory::getApplication()->input;
$mainframe       = JFactory::getApplication();
$option          = $input->get('option');
$Itemid          = $input->get('Itemid', '', 'INT');
$session         = JFactory::getSession();

if ($integration < 1)
{
	// Native Event Manager.
	echo JText::_('COMJTICKETING_INTEGRATION_NOTICE');

	return false;
}

if ($Itemid)
{
	$session->set('JT_Itemid', $Itemid);
}

$doc = JFactory::getDocument();
$doc->addStyleSheet(JUri::base() . 'modules/mod_jticketing_buy/css/jticketing.css');
require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";
$jticketingmainhelper = new jticketingmainhelper;
$user                 = JFactory::getUser();

$view = $input->get('view', '');
$task = $input->get('task', '');

// This is only For Joomla Day Site
$eventid = $params->get('eventid', 0);

if ($eventid)
{
	$input->set('eventid', $eventid);
}

if ($integration == 3)
{
	$eventid = $input->get('eventid', '', 'INT');

	if (!$eventid)
	{
		$view = $input->get('view');
		$task = $input->get('task');

		if ($task == 'icalevent.detail')
		{
			$eventid = $input->get('evid', '', 'INT');
		}
		elseif ($view == 'icalrepeat' or $task == 'icalrepeat.detail')
		{
			$rp_id = $input->get('evid', '', 'INT');

			if ($rp_id)
			{
				$eventid = $jticketingmainhelper->getEventDetailsid($rp_id);
			}
			else
			{
				return;
			}
		}
		else
		{
			return;
		}
	}
	else
	{
		return;
	}
}

if ($integration == 4 or $integration == 2)
{
	$eventid = $input->get('id', '', 'INT');
}

if ($integration == 1)
{
	$eventid = $input->get('eventid', '', 'INT');
}

if (!$eventid)
{
	return false;
}

if ($integration == 1)
{
	$find   = JPATH_SITE . '/components/com_community';
	$status = file_exists($find);

	if (!$status)
	{
		return;
	}

	$jticketingmainhelper->includeJomsocailscripts();

	if ($view != "events" and $task != 'viewevent')
	{
		return false;
	}
}
elseif ($integration == 3)
{
	$find   = JPATH_SITE . '/components/com_jevents';
	$status = file_exists($find);

	if (!$status)
	{
		return;
	}
}

$lang = JFactory::getLanguage();
$lang->load('mod_jticketing_buy', JPATH_SITE);

require JModuleHelper::getLayoutPath('mod_jticketing_buy');
