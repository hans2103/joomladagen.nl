<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
	defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

class jlikeViewElement_config extends JViewLegacy
{
	function display($tpl = null)
	{
		$JlikeHelper=new JLikeHelper();
		// Get the toolbar object instance
		$JlikeHelper->addSubmenu('element_config');
		$this->_setToolBar();

		//Get the model
		$model = $this->getModel();
		$input=JFactory::getApplication()->input;

		if(JVERSION>=3.0)
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		parent::display();
	}

	function _setToolBar()
	{
		JToolBarHelper::title( JText::_( 'COM_JLIKE_CONTENT_TYPE' ), 'jlike.png' );

		if(JVERSION >='1.6.0')
     		JToolBarHelper::apply('save','JTOOLBAR_APPLY');
    		else
     		JToolBarHelper::save();
			//JToolBarHelper::back( JText::_('COM_JLIKE_HOME') , 'index.php?option=com_jlike');
	}
}
