<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

class ConferenceControllerMy extends FOFController
{
	public function __construct($config = array()) {
		parent::__construct($config);
		$this->modelName = 'ConferenceModelSpeakers';
		$this->cacheableTasks = array();
	}
	
	public function execute($task) {
		if(!in_array($task,array('browse'))) {
			$task = 'browse';
		}
		FOFInput::setVar('task', $task, $this->input);
		return parent::execute($task);
	}

	protected function onBeforeBrowse() {
		$result = parent::onBeforeBrowse();
		if($result) {
			if(JFactory::getUser()->guest) {
				// Not a logged in user, redirect to login page
				$returl = base64_encode(JFactory::getURI()->toString());
				$url = JRoute::_('index.php?option=com_users&view=login&return='.$returl);
				JFactory::getApplication()->redirect($url, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
			}
			
			$this->getThisModel()
				->user_id(JFactory::getUser()->id)
				->getFirstItem();
			
			$speaker = FOFModel::getTmpInstance('Speakers', 'ConferenceModel')
				->user_id(JFactory::getUser()->id)
				->getFirstItem();
			$this->getThisView()->assign('speaker', $speaker);
			
			$sessions = FOFModel::getTmpInstance('Sessions', 'ConferenceModel')
				->limit(0)
				->speaker($speaker->conference_speaker_id)
				->filter_order('title')
				->filter_order_Dir('ASC')
				->getList()
				;
				
			$this->getThisView()->assign('sessions', $sessions);

			if($this->getThisModel()->getTotal() && !count($this->getThisModel()->getItemList()) ) {
				$this->getThisModel()->limitstart(0);
			}
			
			// Fetch page parameters
			$params = JFactory::getApplication()->getPageParameters('com_conference');
			
			// Push page parameters
			$this->getThisView()->assign('pageparams',		$params);
		}
		return $result;
	}
}