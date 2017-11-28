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
 * View class for a list of Tjvendors.
 *
 * @since  1.6
 */
class TjvendorsViewPayouts extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

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
		$this->items = $this->get('Items');
		$this->model = $this->getModel('payouts');
		$this->input = JFactory::getApplication()->input;

		// Getting vendor id from url
		$vendor_id = $this->input->get('vendor_id', '', 'INT');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjvendors/models', 'vendors');
		$TjvendorsModelVendors = JModelLegacy::getInstance('Vendors', 'TjvendorsModel');
		$vendorsDetail = $TjvendorsModelVendors->getItems();
		$this->vendor_details = $vendorsDetail;
		$this->uniqueClients = TjvendorsHelpersTjvendors::getUniqueClients();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		TjvendorsHelpersTjvendors::addSubmenu('payouts');

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		$input = JFactory::getApplication()->input;
		$this->client = $input->get('client', '', 'STRING');

		$state = $this->get('State');
		$canDo = TjvendorsHelpersTjvendors::getActions();
		JToolBarHelper::custom('back', 'chevron-left.png', '', 'COM_TJVENDORS_BACK', false);

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_('COM_TJVENDORS_TITLE_PAYOUTS'), 'book');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_TJVENDORS_TITLE_PAYOUTS'), 'payouts.png');
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_tjvendors');
		}

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_tjvendors&view=payouts');

		$this->extra_sidebar = '';
	}
}
