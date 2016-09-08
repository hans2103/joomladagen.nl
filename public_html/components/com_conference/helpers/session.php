<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceHelperSession
{
	public static function room($id)
	{		
		$item = FOFModel::getTmpInstance('Rooms', 'ConferenceModel')
			->id($id)
			->getFirstItem();
	
		return $item;
	}
	
	public static function speaker($id)
	{		
		$item = FOFModel::getTmpInstance('Speakers', 'ConferenceModel')
			->id($id)
			->getFirstItem();
			
		return $item;
	}
	
	public static function slot($id)
	{		
		$slot = FOFModel::getTmpInstance('Slots', 'ConferenceModel')
			->id($id)
			->getFirstItem();
			
		$day_id = $slot->conference_day_id;
		
		$day = FOFModel::getTmpInstance('Days', 'ConferenceModel')
			->id($day_id)
			->getFirstItem();	
			
		$slot = $day->title.', '.JHtml::_('date', $slot->start_time, 'H:i');
		
		return $slot;
	}
	
	public static function level($id)
	{		
		$item = FOFModel::getTmpInstance('Levels', 'ConferenceModel')
			->id($id)
			->getFirstItem();
	
		return $item;
	}
	
	public static function language($lang)
	{	
		$languages = JComponentHelper::getParams('com_conference')->get('languages');
		$languages = explode("\n", $languages);
		foreach($languages as $language) {
			$list[] = explode("=", $language);
		}
		
		foreach($list as $item) {
			if($lang == $item[0]) {
				$language = $item[1];
			}
		}
	
		return $language;
	}
}