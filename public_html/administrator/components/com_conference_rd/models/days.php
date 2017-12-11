<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceModelDays extends FOFModel
{			
	private function getFilterValues()
	{		
		$enabled = $this->getState('enabled','','cmd');
		
		return (object)array(
			'event'			=> $this->getState('event',null,'int'),
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
			$db->qn('e').'.'.$db->qn('title').' AS '.$db->qn('event'),
		));
		
		$order = $this->getState('filter_order', 'conference_day_id', 'cmd');
		if(!in_array($order, array_keys($this->getTable()->getData()))) $order = 'conference_day_id';
		$dir = $this->getState('filter_order_Dir', 'DESC', 'cmd');
		$query->order($order.' '.$dir);

	}
	
	protected function _buildQueryJoins($query)
	{
		$db = $this->getDbo();

		$query
			->join('LEFT OUTER', $db->qn('#__conference_events').' AS '.$db->qn('e').' ON '.
					$db->qn('e').'.'.$db->qn('conference_event_id').' = '.
					$db->qn('tbl').'.'.$db->qn('conference_event_id'))
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
		
		if(is_numeric($state->event) && ($state->event > 0)) {
			$query->where(
				$db->qn('tbl').'.'.$db->qn('conference_event_id').' = '.
					$db->q($state->event)
			);
		}
		
		if(is_numeric($state->id) && ($state->id > 0)) {
			$query->where(
				$db->qn('tbl').'.'.$db->qn('conference_day_id').' = '.
					$db->q($state->id)
			);
		}
		
	}
	
	public function buildQuery($overrideLimits = false) {
		$db = $this->getDbo();
		$query = FOFQueryAbstract::getNew($db)
			->from($db->quoteName('#__conference_days').' AS '.$db->qn('tbl'));
		
		$this->_buildQueryColumns($query);
		$this->_buildQueryJoins($query);
		$this->_buildQueryWhere($query);
		//$this->_buildQueryGroup($query);

		return $query;
	}
}