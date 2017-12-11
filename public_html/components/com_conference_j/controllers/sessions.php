<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceControllerSessions extends FOFController
{
	public function onBeforeRead() {			
		$params = JFactory::getApplication()->getPageParameters('com_conference');
		$this->getThisView()->assign('pageparams',	$params);
		
		return true;
	}

	/**
	 * This runs before the browse() method. Return false to prevent executing
	 * the method.
	 * 
	 * @return bool
	 */
	public function onBeforeBrowse() {
		$result = parent::onBeforeBrowse();
		if($result) {
			// Get the current order by column
			$orderby = $this->getThisModel()->getState('filter_order','');
			// If it's not one of the allowed columns, force it to be the "ordering" column
			if(!in_array($orderby, array('conference_session_id','ordering','title','due'))) {
				$orderby = 'ordering';
			}
			
			// Get the event ID
			$params = JFactory::getApplication()->getPageParameters('com_conference');
			$eventid = $params->get('eventid', 0);
			
			// Apply ordering and filter only the enabled items
			$this->getThisModel()
				->filter_order($orderby)
				->enabled(1)
				->listview(1)
				->event($eventid)
				->filter_order('title')
				->filter_order_Dir('ASC');
				
			// Fetch page parameters
			$params = JFactory::getApplication()->getPageParameters('com_conference');
			
			// Push page parameters
			$this->getThisView()->assign('pageparams', $params);
		}
		return $result;
	}
	
	/**
	 * Save the incoming data and then return to the Browse task
	 */
	public function onAfterSave()
	{
		// Redirect
		$this->setRedirect(JRoute::_('index.php?option=com_conference&view=my'), JText::_('COM_CONFERENCE_LBL_SESSION_SAVED'),'info');
		
		return true;
	}
	
	protected function onBeforeEdit()
	{
		$session = $this->getThisModel()->getItem();
		$speaker = FOFModel::getTmpInstance('Speakers', 'ConferenceModel')->getThisModel()->getItem($session->conference_speaker_id);

		if($speaker->user_id == JFactory::getUser()->id) {
			return true;
		}
	}
	
	protected function onBeforeAdd()
	{
		return $this->checkACL('core.create');
	}
	
	protected function onBeforeSave()
	{
		return $this->checkACL('core.edit.own');
	}
}