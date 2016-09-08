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
	public $defaultView = 'sessions';
	
	public function onBeforeDispatch() {
		$result = parent::onBeforeDispatch();
		
		if($result) {
			if (!JFactory::getUser()->authorise('core.manage', 'com_conference'))
			{
				JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
				return false;
			}
			
			// Load Akeeba Strapper
			include_once JPATH_ROOT . '/media/akeeba_strapper/strapper.php';
			AkeebaStrapper::bootstrap();
			AkeebaStrapper::addCSSfile('media://com_conference/css/backend.css');
			AkeebaStrapper::addCSSfile('media://com_conference/css/frontend.css');

			JHTML::_('formbehavior.chosen', 'select');
		}
		
		return $result;
	}
}