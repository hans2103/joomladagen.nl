<?php
/**
 * @version    SVN:
 * @package    Com_Tjvendors
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright  2009-2017 TechJoomla. All rights reserved.
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
class TjvendorsViewVendor extends JViewLegacy
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
		$app = JFactory::getApplication();
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->params = JComponentHelper::getParams('com_tjvendors');
		$this->input = JFactory::getApplication()->input;
		$this->clientsForVendor = TjvendorsHelpersTjvendors::getClientsForVendor($this->item->vendor_id);
		JText::script('COM_TJVENDOR_DUPLICARE_VENDOR_ERROR');
		JText::script('COM_TJVENDOR_PAYMENTGATEWAY_NO_FIELD_MESSAGE');
		JText::script('COM_TJVENDOR_USER_ERROR');
		$this->client = $this->input->get('client', '', 'STRING');

		if (empty($this->item->vendor_id))
		{
			$currUrl = $this->input->get('currency', '', 'ARRAY');
			$this->item->currency = json_encode($currUrl);
			$this->item->vendor_client = $this->client;
		}

		$app->setUserState("vendor.client", $this->client);
		$app->setUserState("vendor.vendor_id", $this->item->vendor_id);

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
		$isNew = ($this->item->vendor_id == 0);

		$input = JFactory::getApplication()->input;
		$this->full_client = $input->get('client', '', 'STRING');

		// Let's get the extension name
		$client = JFactory::getApplication()->input->get('client', '', 'STRING');
		$extensionName = strtoupper($client);

		if ($isNew)
		{
			$viewTitle = JText::_('COM_TJVENDOR_VENDORS_ADD_USER_SPECIFIC_COMM');
		}
		else
		{
			$viewTitle = JText::_('COM_TJVENDOR_VENDORS_EDIT_USER_SPECIFIC_COMM');
		}

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->id);
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = TjvendorsHelpersTjvendors::getActions();

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title(JText::_('COM_TJVENDORS_TITLE_VENDOR') . $viewTitle,  'pencil-2');
		}
		else
		{
			JToolbarHelper::title(JText::_('COM_TJVENDORS_TITLE_VENDOR') . $viewTitle, 'course.png');
		}

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			JToolBarHelper::apply('vendor.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('vendor.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::custom('vendor.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		if (empty($this->item->vendor_id))
		{
			JToolBarHelper::cancel('vendor.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel('vendor.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
