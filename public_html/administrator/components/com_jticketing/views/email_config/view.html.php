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

class jticketingViewemail_config extends JViewLegacy
{
	function display($tpl = null)
	{
		$params = JComponentHelper::getParams('com_jticketing');
		$integration = $params->get('integration');

		// Native Event Manager.
		if($integration<1)
		{
			$this->sidebar = JHtmlSidebar::render();
			JToolBarHelper::preferences('com_jticketing');
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		$JticketingHelper=new JticketingHelper();
		$JticketingHelper->addSubmenu('email_config');
		$this->_setToolBar();
		if(JVERSION>='3.0')
		$this->sidebar = JHtmlSidebar::render();

		//Get the model
		$model = $this->getModel();
		$input=JFactory::getApplication()->input;
		$option = $input->set('layout','email_config');
		$this->setLayout('email_config');

		parent::display($option);
	}

	function _setToolBar()
	{

		// Get the toolbar object instance

		$document =JFactory::getDocument();
		$document->addStyleSheet(JUri::base().'components/com_jticketing/assets/css/jticketing.css');
		$bar =JToolBar::getInstance('toolbar');
		JToolBarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title( JText::_('COM_JTICKETING_COMPONENT') . JText::_( 'TABS_TEMPLATE' ), 'folder' );
		}
		else
		{
			JToolBarHelper::title( JText::_('COM_JTICKETING_COMPONENT') . JText::_( 'TABS_TEMPLATE' ), 'icon-48-jticketing.png' );
		}

		if(JVERSION >='1.6.0')
     		JToolBarHelper::save('email_config.save','COM_JTICKETING_SAVE');
    		else
     		JToolBarHelper::save();
				JToolBarHelper::preferences('com_jticketing');

	}
}
