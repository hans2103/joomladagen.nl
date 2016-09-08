<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceTableSession extends FOFTable
{
	public function check() {
	
		$result = true;
		
		// Make sure assigned speaker really exists and normalize the list
		if(!empty($this->conference_speaker_id)) {
			if(is_array($this->conference_speaker_id)) {
				$sprs = $this->conference_speaker_id;
			} else {
				$sprs = explode(',', $this->conference_speaker_id);
			}
			if(empty($sprs)) {
				$this->conference_speaker_id = '';
			} else {
				$speakers = array();
				foreach($sprs as $id) {
					$subObject = FOFModel::getTmpInstance('Speakers','ConferenceModel')
						->setId($id)
						->getItem();
					$id = null;
					if(is_object($subObject)) {
						if($subObject->conference_speaker_id > 0) {
							$id = $subObject->conference_speaker_id;
						}
					}
					if(!is_null($id)) $speakers[] = $id;
				}
				$this->conference_speaker_id = implode(',', $speakers);
			}
		}
		
		return $result;
	}
}