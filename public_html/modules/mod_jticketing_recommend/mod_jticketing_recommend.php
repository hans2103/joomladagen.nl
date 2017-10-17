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
require_once JPATH_SITE . '/components/com_jticketing/helpers/event.php';

if (JFile::exists(JPATH_SITE . '/components/com_jticketing/jticketing.php'))
{
	$moduleData = new stdClass;
	$moduleData->jLikepluginParams = '';
	$jLikePlugin = JPluginHelper::getPlugin('content', 'jlike_events');
	$moduleData->event_id = $eventID = JFactory::getApplication()->input->get('id', '', 'INT');
	$show_user_or_username = "name";

	if ($eventID)
	{
		$moduleData->jticketingparams = JComponentHelper::getParams('com_jticketing');
		$moduleData->social_integration = $moduleData->jticketingparams->get('social_integration');
		$moduleData->loggedInUserID = JFactory::getUser()->id;
		$moduleData->loggedInUser = JFactory::getUser();
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'eventform');
		$eventFormsModel = JModelLegacy::getInstance('EventForm', 'JticketingModel');
		$moduleData->eventInfo = $eventFormsModel->getItem($eventID);
		$eventHelper = new JteventHelper;

		if (!empty($jLikePlugin))
		{
			// Get Params each component
			$dispatcher        = JDispatcher::getInstance();
			JPluginHelper::importPlugin('content', 'jlike_events');
			$paramsArray = $dispatcher->trigger('jlike_eventsGetParams', array());
			$moduleData->jLikepluginParams = !empty ($paramsArray[0]) ? $paramsArray[0] : '';
		}

		/*
		if ($params->get('assign_user', 1) &&  ($moduleData->loggedInUserID == $moduleData->eventInfo->created_by) )
		{
			if (!empty($moduleData->jLikepluginParams))
			{
				if ($moduleData->jLikepluginParams->get('assignment') == 1)
				{
					$showassign = 1;
					$mod_data->getuserAssignedUsers = $model->getuserAssignedUsersInfo($eventID, $moduleData->eventInfo->created_by);
				}
			}
		}*/

		if ($params->get('recommend', 1) && $moduleData->loggedInUserID)
		{
			if (!empty($moduleData->jLikepluginParams))
			{
				if ($moduleData->jLikepluginParams->get('recommendation') == 1)
				{
					$showrecommend = 1;
					$moduleData->getuserRecommendedUsers = $eventHelper->getuserRecommendedUsers($eventID, $moduleData->loggedInUserID);
				}
			}
		}

		require	JModuleHelper::getLayoutPath('mod_jticketing_recommend');
	}
}
