<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceHelperSchedule
{
	public static function slots($conference_day_id)
	{		
		$slots = FOFModel::getTmpInstance('Slots', 'ConferenceModel')
			->limit(0)
			->limitstart(0)
			->enabled(1)
			->day($conference_day_id)
			->filter_order('start_time')
			->filter_order_Dir('ASC')
			->getList();
					
		return $slots;
	}
	
	public static function rooms()
	{		
		$slots = FOFModel::getTmpInstance('Rooms', 'ConferenceModel')
			->limit(0)
			->limitstart(0)
			->enabled(1)
			->type(1)
			->filter_order('ordering')
			->filter_order_Dir('ASC')
			->getList();
					
		return $slots;
	}
	
	public static function generalroom()
	{		
		$generalroom = FOFModel::getTmpInstance('Rooms', 'ConferenceModel')
			->limit(1)
			->limitstart(0)
			->enabled(1)
			->type(0)
			->filter_order('ordering')
			->filter_order_Dir('ASC')
			->getList();
			
		$generalroom = $generalroom[0]->conference_room_id;
					
		return $generalroom;
	}
}