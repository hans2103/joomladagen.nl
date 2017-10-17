<?php
/**
 * @version    SVN:
 * @package    Com_Tjvendors
 * @author     Techjoomla  <contact@techjoomla.com>
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
class TjvendorsViewPayout extends JViewLegacy
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
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->input = JFactory::getApplication()->input;
		$com_params = JComponentHelper::getParams('com_tjvendors');
		$this->bulkPayoutStatus = $com_params->get('bulk_payout');

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

		// Let's get the extension name
		$client = JFactory::getApplication()->input->get('client', '', 'STRING');
		$extensionName = strtoupper($client);

		if ($isNew)
		{
			$viewTitle = JText::_('COM_TJVENDOR_ADD_USER_SPECIFIC_COMM');
		}
		else
		{
			$viewTitle = JText::_('COM_TJVENDOR_EDIT_PAYOUT_SPECIFIC_COMM');
		}

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = TjvendorsHelpersTjvendors::getActions();

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title(JText::_('COM_TJVENDORS_TITLE_PAYOUTS') . $viewTitle,  'pencil-2');
		}
		else
		{
			JToolbarHelper::title(JText::_('COM_TJVENDORS_TITLE_PAYOUTS') . $viewTitle, 'course.png');
		}

		if (empty($this->item->id))
		{
			JToolBarHelper::cancel('payout.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel('payout.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
