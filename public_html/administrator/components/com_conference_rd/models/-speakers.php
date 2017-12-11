<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceModelSpeakers extends FOFModel
{			
	private function getFilterValues()
	{		
		$enabled = $this->getState('enabled','','cmd');
		
		return (object)array(
			'title'			=> $this->getState('title',null,'string'),
			'speaker'		=> $this->getState('speaker',null,'int'),
			'id'			=> $this->getState('id',null,'int'),
			'enabled'		=> $enabled,
		);
	}
	
	protected function _buildQueryColumns($query)
	{
		$db = $this->getDbo();
		$state = $this->getFilterValues();

		$query->select(array(
			$db->qn('tbl').'.*',
			//$db->qn('d').'.'.$db->qn('title').' AS '.$db->qn('day'),
			//$db->qn('d').'.'.$db->qn('date').' AS '.$db->qn('date'),
			//$db->qn('e').'.'.$db->qn('title').' AS '.$db->qn('event'),
			//$db->qn('e').'.'.$db->qn('conference_event_id').' AS '.$db->qn('conference_event_id'),
		));
		
		$order = $this->getState('filter_order', 'conference_speaker_id', 'cmd');
		if(!in_array($order, array_keys($this->getTable()->getData()))) $order = 'conference_speaker_id';
		$dir = $this->getState('filter_order_Dir', 'DESC', 'cmd');
		$query->order($order.' '.$dir);

	}
	
	protected function _buildQueryJoins($query)
	{
		$db = $this->getDbo();

		$query
			//->join('LEFT OUTER', $db->qn('#__conference_sessions').' AS '.$db->qn('s').' ON '.
			//		$db->qn('s').'.'.$db->qn('conference_speaker_id').' = '.
			//		$db->qn('tbl').'.'.$db->qn('conference_speaker_id'))
			//->join('LEFT OUTER', $db->qn('#__conference_events').' AS '.$db->qn('e').' ON '.
			//		$db->qn('e').'.'.$db->qn('conference_event_id').' = '.
			//		$db->qn('s').'.'.$db->qn('conference_event_id'))
		;	
		
	}
	
	protected function _buildQueryWhere($query)
	{
		$db = $this->getDbo();
		$state = $this->getFilterValues();
		
		if(is_numeric($state->enabled)) {
			$query->where(
				$db->qn('tbl').'.'.$db->qn('enabled').' = '.
					$db->q($state->enabled)
			);
		}
		
		if(is_numeric($state->speaker) && ($state->speaker > 0)) {
			$query->where(
				$db->qn('tbl').'.'.$db->qn('conference_speaker_id').' = '.
					$db->q($state->speaker)
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
			->from($db->quoteName('#__conference_speakers').' AS '.$db->qn('tbl'));
		
		$this->_buildQueryColumns($query);
		$this->_buildQueryJoins($query);
		$this->_buildQueryWhere($query);
		//$this->_buildQueryGroup($query);

		return $query;
	}
}