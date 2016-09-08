<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceDispatcher extends FOFDispatcher
{	
	public function __construct($config = array()) {
		$this->defaultView = 'days';
		
		parent::__construct($config);
	}
	
	public function onBeforeDispatch() {
		$result = parent::onBeforeDispatch();
		
		if($result) {
			// Load js & css
			$doc = JFactory::getDocument();
			$doc->addStyleSheet(JURI::root(true).'/media/com_conference/css/frontend.css');
		}
		
		return $result;
	}
}