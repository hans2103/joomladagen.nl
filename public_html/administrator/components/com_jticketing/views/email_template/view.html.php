<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * Email Template view for email invite
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewemail_Template extends JViewLegacy
{
	/**
	 * Display view
	 *
	 * @param   STRING  $tpl  template name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
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

		$JticketingHelper = new JticketingHelper;
		$JticketingHelper->addSubmenu('email_template');
		$this->_setToolBar();

		if (JVERSION >= '3.0')
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		// Get the model
		$model  = $this->getModel();
		$input  = JFactory::getApplication()->input;
		$option = $input->set('layout', 'email_template');
		$this->setLayout('email_template');

		parent::display($option);
	}

	/**
	 * Display toolbar
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function _setToolBar()
	{
		// Get the toolbar object instance
		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::base() . 'components/com_jticketing/assets/css/jticketing.css');
		$bar = JToolBar::getInstance('toolbar');

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_('COM_JTICKETING_COMPONENT') . JText::_('COM_JTICKETING_EMAIL_TEMPLATE'), 'folder');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_JTICKETING_COMPONENT') . JText::_('COM_JTICKETING_EMAIL_TEMPLATE'), 'icon-48-jticketing.png');
		}
		JToolBarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');

		if (JVERSION >= '1.6.0')
		{
			JToolBarHelper::save('email_template.save', 'COM_JTICKETING_SAVE');
		}
		else
		{
			JToolBarHelper::save();
		}

		JToolBarHelper::preferences('com_jticketing');
	}
}
