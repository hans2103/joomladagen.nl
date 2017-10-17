<?php
defined ( '_JEXEC' ) or die ( 'Restricted access' );
/**
 * @package		jomLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

jimport ( 'joomla.application.component.view' );

class jLikeViewButtonset extends JViewLegacy {

	function display($tpl = null) {

		$JlikeHelper=new JLikeHelper();
		$JlikeHelper->addSubmenu('buttonset');
		$list =  $this->get ( 'Data' );
		$this->list=$list ;

		$this->_setToolBar();

		if(JVERSION>=3.0)
		{
			$this->sidebar = JHtmlSidebar::render();
		}
		parent::display ( $tpl );
	}
	function _setToolBar()
	{
		JToolBarHelper::title( JText::_( 'COM_JLIKE_BTN_SETTING' ), 'jlike.png' );
		JToolBarHelper::apply();
		//JToolBarHelper::back( JText::_('COM_JLIKE_HOME') , 'index.php?option=com_jlike');
	}
}
