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
 * View class for a list of Tjvendors.
 *
 * @since  1.6
 */
class TjvendorsViewVendorFees extends JViewLegacy
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
		$input = JFactory::getApplication()->input;
		$this->vendor_id = $input->get('vendor_id', '', 'INT');
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->input = JFactory::getApplication()->input;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		TjvendorsHelpersTjvendors::addSubmenu('vendorfees');
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

		$state = $this->get('State');
		$canDo = TjvendorsHelpersTjvendors::getActions();

		JToolBarHelper::custom('vendorfees.back', 'chevron-left.png', '', 'Back', false);
		JToolBarHelper::addNew('vendorfee.add');

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_('COM_TJVENDORS_TITLE_VENDORS_FEES'), 'book');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_TJVENDORS_TITLE_VENDORS_FEES'), 'vendors.png');
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('vendorfees.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('vendorfees.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_tjvendors');
		}

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_tjvendors&view=vendorfees');

		$this->extra_sidebar = '';
	}

	/**
	 * Method to ord$this->itemer fields
	 *
	 * @return void
	 */
	protected function getSortFields()
	{
		return array(
			'b.`percent_commission`' => JText::_('COM_TJVENDORS_VENDORS_PERCENT_COMMISSION'),
			'b.`flat_commission`' => JText::_('COM_TJVENDORS_VENDORS_FLAT_COMMISSION'),
		);
	}
}
