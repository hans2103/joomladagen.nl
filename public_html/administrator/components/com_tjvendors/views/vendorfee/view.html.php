<?php
/**
 * @version    SVN: 
 * @package    Com_Tjvendors
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 *
 * @since  1.6
 */
class TjvendorsViewVendorFee extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$input = JFactory::getApplication()->input;
		$this->vendor_id = $input->get('vendor_id', '', 'INT');
		$this->id = $input->get('id', '', 'INT');
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->input = JFactory::getApplication()->input;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user  = JFactory::getUser();
		$isNew = ($this->item->id == 0);

		$input = JFactory::getApplication()->input;
		$this->full_client = $input->get('client', '', 'STRING');

		if ($isNew)
		{
			$viewTitle = JText::_('COM_TJVENDOR_EDIT_USER_SPECIFIC_COMMISSION');
		}
		else
		{
			$viewTitle = JText::_('COM_TJVENDOR_EDIT_USER_SPECIFIC_COMMISSION');
		}

		JToolbarHelper::title(JText::_('COM_TJVENDORS_TITLE_VENDOR') . $viewTitle,  'pencil-2');
		JToolBarHelper::apply('vendorfee.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('vendorfee.save', 'JTOOLBAR_SAVE');

		if (empty($this->item->id))
		{
			JToolBarHelper::cancel('vendorfee.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel('vendorfee.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
