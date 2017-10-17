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
		$this->state = $this->get('State');
		$this->user_id = jFactory::getuser()->id;
		$this->vendor  = $this->get('Item');
		$this->form  = $this->get('Form');
		$this->input = JFactory::getApplication()->input;
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_tjvendors/models', 'vendor');
		$TjvendorsModelVendor = JModelLegacy::getInstance('Vendor', 'TjvendorsModel');
		$TjvendorFrontHelper = new TjvendorFrontHelper;
		$this->vendor_id = $TjvendorFrontHelper->getvendor();
		$this->VendorDetail = $TjvendorsModelVendor->getItem($this->vendor_id);
		$this->clientsForVendor = $TjvendorFrontHelper->getClientsForVendor($this->vendor_id);
		$this->client = $this->input->get('client', '', 'STRING');
		$app = JFactory::getApplication();
		$app->setUserState("vendor.client", $this->client);
		$app->setUserState("vendor.vendor_id", $this->vendor->vendor_id);
		$this->layout = $this->input->get('layout', '', 'STRING');
		JText::script('COM_TJVENDOR_PAYMENTGATEWAY_NO_FIELD_MESSAGE');

		if (!empty($this->vendor_id) && $this->layout == "edit")
		{
			foreach ($this->clientsForVendor as $client)
			{
				if ($client == $this->client)
				{
					$link = JRoute::_('index.php?option=com_tjvendors&view=vendor&layout=profile&client=' . $this->client . '&vendor_id=' . $this->vendor_id);
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('COM_TJVENDOR_REGISTRATION_REDIRECT_MESSAGE'));
					$app->redirect($link);
				}
			}
		}

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		parent::display($tpl);
	}
}
