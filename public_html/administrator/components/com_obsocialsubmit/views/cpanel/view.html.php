<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;

class ObSocialSubmitViewCpanel extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $state;
	
	protected $sidebar;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$this->addToolbar();
		JToolbarHelper::preferences('com_obsocialsubmit');
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		global $isJ25;
		JToolbarHelper::title(JText::_('COM_OBSOCIALSUBMIT_MANAGER_CPANEL'), 'module.png');
		$this->sidebar = obSocialSubmitHelper::addSubmenu($this->get("Name"));
	}
}
