<?php
/**
 * @version     1.0.0
 * @package     com_tjfields
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      TechJoomla <extensions@techjoomla.com> - http://www.techjoomla.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class jticketingViewfields extends JViewLegacy
{

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		if(JVERSION>='3.0')
			JHtmlBehavior::framework();
		else
			JHtml::_('behavior.mootools');
		$JticketingHelper=new JticketingHelper();
		$JticketingHelper->addSubmenu('fields');
		if(JVERSION>='3.0')
		$this->sidebar = JHtmlSidebar::render();


		parent::display($tpl);
	}


}
