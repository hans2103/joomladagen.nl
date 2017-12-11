<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceHelperSpeaker
{
	public static function speakerid($user_id)
	{		
		$speaker = FOFModel::getTmpInstance('Speakers', 'ConferenceModel')
			->user_id($user_id)
			->getFirstItem();
			
		$speakerID = $speaker->conference_speaker_id;
					
		return $speakerID;
	}
}