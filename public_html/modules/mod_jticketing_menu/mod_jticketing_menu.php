<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
$input = JFactory::getApplication()->input;
require_once JPATH_SITE . DS . "components" . DS . "com_jticketing" . DS . "helpers" . DS . "main.php";
$option        = $input->get('option', '');
$view        = $input->get('view', '');
$task        = $input->get('task', '');
$com_params  = JComponentHelper::getParams('com_jticketing');
$integration = $com_params->get('integration');
$user = JFactory::getUser();
$uid  = $user->id;

if (empty($uid))
{
	return;
}

/*if ($integration != 3)
{
	if (($view != "events" and $task != 'viewevent'))
	{
		return false;
	}
}
else
{
	$view = $input->get('view', '');
	$task = $input->get('task', '');
	$flag = 0;

	if ($task == 'icalevent.detail' or $view == 'icalrepeat' or $task == 'icalrepeat.detail')
	{
		$flag = 1;
	}

	if ($flag == 0)
	{
		return false;
	}
}

if (!empty($eventid))
{
	$input->set('eventid', $eventid);
}

$eventid              = $input->get('eventid', '', 'INT');
$jticketingmainhelper = new jticketingmainhelper;

if ($integration == 3)
{
	$eventid = $input->get('eventid', '', 'INT');

	if (empty($eventid))
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
		$eventid = $jticketingmainhelper->getEventDetailsid($eventid);
	}
}

if ($integration == 4 and $option == 'com_easysocial' and $view == 'events')
{
	$eventid = $input->get('id', '', 'INT');
}

if ($integration == 1)
{
	$user = JFactory::getUser();

	if (empty($user->id))
	{
		if (($view != "events" and $task != 'viewevent'))
		{
			return false;
		}
	}
}*/

require	JModuleHelper::getLayoutPath('mod_jticketing_menu');
