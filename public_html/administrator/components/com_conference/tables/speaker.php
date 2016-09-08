<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceTableSpeaker extends FOFTable
{
	public function check() {
	
		$result = true;
		
		// Make sure assigned speaker really exists and normalize the list
		if(!empty($this->conference_event_id)) {
			if(is_array($this->conference_event_id)) {
				$sprs = $this->conference_event_id;
			} else {
				$sprs = explode(',', $this->conference_event_id);
			}
			if(empty($sprs)) {
				$this->conference_event_id = '';
			} else {
				$events = array();
				foreach($sprs as $id) {
					$subObject = FOFModel::getTmpInstance('Events','ConferenceModel')
						->setId($id)
						->getItem();
					$id = null;
					if(is_object($subObject)) {
						if($subObject->conference_event_id > 0) {
							$id = $subObject->conference_event_id;
						}
					}
					if(!is_null($id)) $events[] = $id;
				}
				$this->conference_event_id = implode(',', $events);
			}
		}
		
		return $result;
	}
}