<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceModelSessions extends FOFModel
{			
	private function getFilterValues()
	{		
		$enabled = $this->getState('enabled','','cmd');
		
		return (object)array(
			'title'			=> $this->getState('title',null,'string'),
			'speaker'		=> $this->getState('speaker',null,'int'),
			'level'			=> $this->getState('level',null,'int'),
			'room'			=> $this->getState('room',null,'int'),
			'day'			=> $this->getState('day',null,'int'),
			'slot'			=> $this->getState('slot',null,'int'),
			'listview'		=> $this->getState('listview',null,'int'),
			'status'		=> $this->getState('status',null,'int'),
			'event'			=> $this->getState('event',null,'int'),
			'enabled'		=> $enabled,
		);
	}
	
	protected function _buildQueryColumns($query)
	{
		$db = $this->getDbo();
		$state = $this->getFilterValues();

		$query->select(array(
			$db->qn('tbl').'.*',
			$db->qn('l').'.'.$db->qn('title').' AS '.$db->qn('level'),
			$db->qn('l').'.'.$db->qn('label').' AS '.$db->qn('level_label'),
			$db->qn('r').'.'.$db->qn('title').' AS '.$db->qn('room'),
			$db->qn('d').'.'.$db->qn('title').' AS '.$db->qn('day'),
			$db->qn('t').'.'.$db->qn('start_time').' AS '.$db->qn('start_time'),
			$db->qn('t').'.'.$db->qn('end_time').' AS '.$db->qn('end_time'),
			$db->qn('t').'.'.$db->qn('conference_day_id').' AS '.$db->qn('day_id'),
			$db->qn('s').'.'.$db->qn('image').' AS '.$db->qn('speakerimage'),
			$db->qn('s').'.'.$db->qn('user_id').' AS '.$db->qn('user_id'),
			$db->qn('s').'.'.$db->qn('title').' AS '.$db->qn('speaker'),
			$db->qn('e').'.'.$db->qn('title').' AS '.$db->qn('event'),
			$db->qn('e').'.'.$db->qn('conference_event_id').' AS '.$db->qn('conference_event_id'),
		));
		
		$order = $this->getState('filter_order', 'conference_session_id', 'cmd');
		if(!in_array($order, array_keys($this->getTable()->getData()))) $order = 'conference_session_id';
		$dir = $this->getState('filter_order_Dir', 'DESC', 'cmd');
		$query->order($order.' '.$dir);

	}
	
	protected function _buildQueryJoins($query)
	{
		$db = $this->getDbo();

		$query
			->join('LEFT OUTER', $db->qn('#__conference_speakers').' AS '.$db->qn('s').' ON '.
					$db->qn('s').'.'.$db->qn('conference_speaker_id').' = '.
					$db->qn('tbl').'.'.$db->qn('conference_speaker_id'))
			->join('LEFT OUTER', $db->qn('#__conference_levels').' AS '.$db->qn('l').' ON '.
					$db->qn('l').'.'.$db->qn('conference_level_id').' = '.
					$db->qn('tbl').'.'.$db->qn('conference_level_id'))
			->join('LEFT OUTER', $db->qn('#__conference_rooms').' AS '.$db->qn('r').' ON '.
					$db->qn('r').'.'.$db->qn('conference_room_id').' = '.
					$db->qn('tbl').'.'.$db->qn('conference_room_id'))
			->join('LEFT OUTER', $db->qn('#__conference_slots').' AS '.$db->qn('t').' ON '.
					$db->qn('t').'.'.$db->qn('conference_slot_id').' = '.
					$db->qn('tbl').'.'.$db->qn('conference_slot_id'))
			->join('LEFT OUTER', $db->qn('#__conference_days').' AS '.$db->qn('d').' ON '.
					$db->qn('d').'.'.$db->qn('conference_day_id').' = '.
					$db->qn('t').'.'.$db->qn('conference_day_id'))
			->join('LEFT OUTER', $db->qn('#__conference_events').' AS '.$db->qn('e').' ON '.
					$db->qn('d').'.'.$db->qn('conference_event_id').' = '.
					$db->qn('e').'.'.$db->qn('conference_event_id'))
		;	
		
	}
	
	protected function _buildQueryWhere($query)
	{
		$db = $this->getDbo();
		$state = $this->getFilterValues();
	
		if(is_numeric($state->speaker) && ($state->speaker > 0)) {
			$query->where(
				$db->qn('tbl').'.'.$db->qn('conference_speaker_id').' = '.
					$db->q($state->speaker)
			);
		}
		
		if(is_numeric($state->level) && ($state->level > 0)) {
			$query->where(
				$db->qn('tbl').'.'.$db->qn('conference_level_id').' = '.
					$db->q($state->level)
			);
		}
		
		if(is_numeric($state->room) && ($state->room > 0)) {
			$query->where(
				$db->qn('tbl').'.'.$db->qn('conference_room_id').' = '.
					$db->q($state->room)
			);
		}
		
		if(is_numeric($state->day) && ($state->day > 0)) {
			$query->where(
				$db->qn('t').'.'.$db->qn('conference_day_id').' = '.
					$db->q($state->day)
			);
		}
		
		if(is_numeric($state->slot) && ($state->slot > 0)) {
			$query->where(
				$db->qn('tbl').'.'.$db->qn('conference_slot_id').' = '.
					$db->q($state->slot)
			);
		}
		
		if(is_numeric($state->event) && ($state->event > 0)) {
			$query->where(
				$db->qn('e').'.'.$db->qn('conference_event_id').' = '.
					$db->q($state->event)
			);
		}
		
		if(is_numeric($state->status) && ($state->status > 0)) {
			$query->where(
				$db->qn('tbl').'.'.$db->qn('status').' = '.
					$db->q($state->status)
			);
		}
		
		if(is_numeric($state->enabled)) {
			$query->where(
				$db->qn('tbl').'.'.$db->qn('enabled').' = '.
					$db->q($state->enabled)
			);
		}
		
		if(is_numeric($state->listview)) {
			$query->where(
				$db->qn('tbl').'.'.$db->qn('listview').' = 1'
			);
		}

		if($state->title) {
			$search = '%'.$state->title.'%';
			$query->where(
				$db->qn('tbl').'.'.$db->qn('title').' LIKE '.
					$db->q($search)
			);
		}	
		
	}
	
	public function buildQuery($overrideLimits = false) {
		$db = $this->getDbo();
		$query = FOFQueryAbstract::getNew($db)
			->from($db->quoteName('#__conference_sessions').' AS '.$db->qn('tbl'));
		
		$this->_buildQueryColumns($query);
		$this->_buildQueryJoins($query);
		$this->_buildQueryWhere($query);
		//$this->_buildQueryGroup($query);
		
		return $query;
	}
}