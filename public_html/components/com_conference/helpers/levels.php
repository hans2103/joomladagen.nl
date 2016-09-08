<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceHelperLevels
{
	public static function sessions($conference_level_id)
	{
		// Get the event ID
		$params = JFactory::getApplication()->getPageParameters('com_conference');
		$eventid = $params->get('eventid', 0);
		
		$sessions = FOFModel::getTmpInstance('Sessions', 'ConferenceModel')
			->limit(0)
			->limitstart(0)
			->enabled(1)
			->event($eventid)
			->level($conference_level_id)
			->filter_order('title')
			->filter_order_Dir('ASC')
			->getList();
					
		return $sessions;
	}
}