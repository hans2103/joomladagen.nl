<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceHelperSelect
{
	protected static function genericlist($list, $name, $attribs, $selected, $idTag)
	{
		if(empty($attribs))
		{
			$attribs = null;
		}
		else
		{
			$temp = '';
			foreach($attribs as $key=>$value)
			{
				$temp .= $key.' = "'.$value.'"';
			}
			$attribs = $temp;
		}

		return JHTML::_('select.genericlist', $list, $name, $attribs, 'value', 'text', $selected, $idTag);
	}
	
	public static function booleanlist( $name, $attribs = null, $selected = null )
	{
		$options = array(
			JHTML::_('select.option','','---'),
			JHTML::_('select.option',  '0', JText::_( 'No' ) ),
			JHTML::_('select.option',  '1', JText::_( 'Yes' ) )
		);
		return self::genericlist($options, $name, $attribs, $selected, $name);
	}
	
	public static function published($selected = null, $id = 'enabled', $attribs = array('class' => 'chosen'))
	{
		$options = array();
		$options[] = JHTML::_('select.option',null,JText::_('COM_CONFERENCE_SELECT_STATE'));
		$options[] = JHTML::_('select.option',0,JText::_((version_compare(JVERSION, '1.6.0', 'ge')?'J':'').'UNPUBLISHED'));
		$options[] = JHTML::_('select.option',1,JText::_((version_compare(JVERSION, '1.6.0', 'ge')?'J':'').'PUBLISHED'));

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}
	
	public static function roomtype($selected = null, $name = 'type', $attribs = array('class' => 'chosen'))
	{
		$options = array();
		$options[] = JHTML::_('select.option','',JText::_('COM_CONFERENCE_SELECT_TYPE'));
		$options[] = JHTML::_('select.option','0',JText::_('General'));
		$options[] = JHTML::_('select.option','1',JText::_('Room'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}
	
	public static function language($selected = null, $name = 'language', $attribs = array('class' => 'chosen'))
	{
		$languages = JComponentHelper::getParams('com_conference')->get('languages');
		$languages = explode("\n", $languages);
		foreach($languages as $language) {
			$list[] = explode("=", $language);
		}
		
		$options = array();
		$options[] = JHTML::_('select.option','',JText::_('COM_CONFERENCE_SELECT_LANGUAGE'));
		foreach($list as $item) {
			$options[] = JHTML::_('select.option',$item[0],$item[1]);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}
	
	public static function status($selected = null, $name = 'status', $attribs = array('class' => 'chosen'))
	{
		$status = JComponentHelper::getParams('com_conference')->get('statusoptions');
		$status = explode("\n", $status);
		foreach($status as $state) {
			$list[] = explode("=", $state);
		}
		
		$options = array();
		$options[] = JHTML::_('select.option','',JText::_('COM_CONFERENCE_SELECT_STATUS'));
		foreach($list as $item) {
			$options[] = JHTML::_('select.option',$item[0],$item[1]);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	/**
	 * Shows a speakers	 
	 */
	public static function speakers($selected = null, $name = 'conference_speaker_id[]', $attribs = array('multiple' => 'multiple', 'class' => 'chosen'), $showselect = false)
	{
		$list = FOFModel::getTmpInstance('Speakers','ConferenceModel')
			->savestate(0)
			->filter_order('ordering')
			->filter_order_Dir('ASC')
			->limit(0)
			->offset(0)
			->getList();
		
		$options   = array();
		
		if($showselect) {
			$options[] = JHTML::_('select.option','',JText::_('COM_CONFERENCE_SELECT_SPEAKER'));
		}
	
		foreach($list as $item) {
			$options[] = JHTML::_('select.option',$item->conference_speaker_id,$item->title);
		}
		
		return self::genericlist($options, $name, $attribs, $selected, $name);
	}
	
	/**
	 * Drop down list of levels
	 */
	public static function levels($selected = null, $id = 'conference_level_id', $attribs = array('class' => 'chosen'))
	{
		$model = FOFModel::getTmpInstance('Levels','ConferenceModel');
		$items = $model->savestate(0)->limit(0)->limitstart(0)->getItemList();
		
		$options = array();
		
		$options[] = JHTML::_('select.option','',JText::_('COM_CONFERENCE_SELECT_LEVEL'));
		
		if(count($items)) foreach($items as $item)
		{
			$options[] = JHTML::_('select.option',$item->conference_level_id, $item->title);
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}
	
	/**
	 * Drop down list of rooms
	 */
	public static function rooms($selected = null, $id = 'conference_room_id', $attribs = array('class' => 'chosen'))
	{
		$model = FOFModel::getTmpInstance('Rooms','ConferenceModel');
		$items = $model->savestate(0)->limit(0)->limitstart(0)->getItemList();
		
		$options = array();
		
		$options[] = JHTML::_('select.option','',JText::_('COM_CONFERENCE_SELECT_ROOM'));

		if(count($items)) foreach($items as $item)
		{
			$options[] = JHTML::_('select.option',$item->conference_room_id, $item->title);
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}
	
	/**
	 * Drop down list of events
	 */
	public static function events($selected = null, $id = 'conference_event_id', $attribs = array('class' => 'chosen'))
	{
		$model = FOFModel::getTmpInstance('Events','ConferenceModel');
		$items = $model->savestate(0)->limit(0)->limitstart(0)->getItemList();
		
		$options = array();
		
		$options[] = JHTML::_('select.option','',JText::_('COM_CONFERENCE_SELECT_EVENT'));

		if(count($items)) foreach($items as $item)
		{
			$options[] = JHTML::_('select.option',$item->conference_event_id, $item->title);
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}
	
	/**
	 * Shows multiple events	 
	 */
	public static function multi_events($selected = null, $name = 'conference_event_id[]', $attribs = array('multiple' => 'multiple', 'class' => 'chosen'))
	{
		$list = FOFModel::getTmpInstance('Events','ConferenceModel')
			->savestate(0)
			->filter_order('ordering')
			->filter_order_Dir('ASC')
			->limit(0)
			->offset(0)
			->getList();
		
		$options   = array();
		
		$options[] = JHTML::_('select.option','',JText::_('COM_CONFERENCE_SELECT_EVENT'));
	
		foreach($list as $item) {
			$options[] = JHTML::_('select.option',$item->conference_event_id,$item->title);
		}
		
		return self::genericlist($options, $name, $attribs, $selected, $name);
	}
	
	/**
	 * Drop down list of days
	 */
	public static function days($selected = null, $id = 'conference_day_id', $attribs = array('class' => 'chosen'))
	{
		$model = FOFModel::getTmpInstance('Days','ConferenceModel');
		$items = $model->savestate(0)->limit(0)->limitstart(0)->getItemList();
		
		$options = array();
		
		$options[] = JHTML::_('select.option','',JText::_('COM_CONFERENCE_SELECT_DAY'));

		if(count($items)) foreach($items as $item)
		{
			$options[] = JHTML::_('select.option',$item->conference_day_id, $item->title);
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}
	
	/**
	 * Drop down list of slots
	 */
	public static function slots($selected = null, $id = 'conference_slot_id', $attribs = array('class' => 'chosen'))
	{
		$model = FOFModel::getTmpInstance('Days','ConferenceModel');
		$slotmodel = FOFModel::getTmpInstance('Slots','ConferenceModel');
		$days = $model->savestate(0)->filter_order('date')->filter_order_Dir('asc')->limit(0)->enabled(1)->limitstart(0)->getItemList();

		$options = array();
		$options[] = JHTML::_('select.option','',JText::_('COM_CONFERENCE_SELECT_SLOT'));
		foreach($days as $day){
			$options[] = JHTML::_('select.optgroup', JHtml::_('date', $day->date, JText::_('l, j F Y')));			
			$items = $slotmodel->savestate(0)->filter_order('start_time')->filter_order_Dir('asc')->limit(0)->enabled(1)->limitstart(0)->getItemList();
		
			if(count($items)) foreach($items as $item)
			{
				if ($day->conference_day_id == $item->conference_day_id)	
				{
					$options[] = JHTML::_('select.option',$item->conference_slot_id, $day->title.': '.JHtml::_('date', $item->start_time, 'H:i').' - '.JHtml::_('date', $item->end_time, 'H:i'));
				}
			}
			$options[] = JHTML::_('select.optgroup', $day->title);
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}
}