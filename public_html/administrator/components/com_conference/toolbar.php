<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceToolbar extends FOFToolbar
{
	protected function getMyViews()
	{
		$views = array(
			'events',
			'days',
			'slots',
			'levels',
			'rooms',
			'sessions',
			'speakers',
		);
		return $views;
	}
	
	public function onBrowse()
	{
		parent::onBrowse();

		JToolBarHelper::preferences('com_conference');
	}
}