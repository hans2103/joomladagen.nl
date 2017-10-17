<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * View class for a list of cities.
 *
 * @package     Tjfields
 * @subpackage  com_tjfields
 * @since       2.2
 */
class TjfieldsViewCities extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display ($tpl = null)
	{
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->input = JFactory::getApplication()->input;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		TjfieldsHelper::addSubmenu('cities');

		if (JVERSION >= '3.0')
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		if (JVERSION < '3.0')
		{
			// Creating status filter.
			$sstatus = array();
			$sstatus[] = JHtml::_('select.option', '', JText::_('JOPTION_SELECT_PUBLISHED'));
			$sstatus[] = JHtml::_('select.option', 1, JText::_('JPUBLISHED'));
			$sstatus[] = JHtml::_('select.option', 0, JText::_('JUNPUBLISHED'));

			$this->sstatus = $sstatus;

			// Creating country filter.
			$countries = array();
			$countries[] = JHtml::_('select.option', '', JText::_('COM_TJFIELDS_FILTER_SELECT_COUNTRY'));

			require_once JPATH_COMPONENT . '/models/fields/countries.php';
			$countriesField = new JFormFieldCountries;
			$this->countries = $countriesField->getOptionsExternally();

			// Merge options
			$this->countries = array_merge($countries, $this->countries);

			// Creating regions filter.
			$regions = array();
			$regions[] = JHtml::_('select.option', '', JText::_('COM_TJFIELDS_FILTER_SELECT_REGION'));

			require_once JPATH_COMPONENT . '/models/fields/regions.php';
			$regionsField = new JFormFieldRegions;
			$this->regions = $regionsField->getOptionsExternally();

			// Merge options
			$this->regions = array_merge($regions, $this->regions);
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar ()
	{
		require_once JPATH_COMPONENT . '/helpers/tjfields.php';

		$state = $this->get('State');
		$canDo = TjfieldsHelper::getActions();

		// Let's get the extension name
		$client = JFactory::getApplication()->input->get('client', '', 'STRING');
		$extensionName = strtoupper($client);

		// Need to load the menu language file as mod_menu hasn't been loaded yet.
		$lang = JFactory::getLanguage();
		$lang->load($client, JPATH_ADMINISTRATOR, null, false, true);

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_($extensionName) . ': ' . JText::_('COM_TJFIELDS_TITLE_CITIES'), 'list');
		}
		else
		{
			JToolBarHelper::title(JText::_($extensionName) . ': ' . JText::_('COM_TJFIELDS_TITLE_CITIES'), 'cities.png');
		}

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/city';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('city.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('city.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('cities.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('cities.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_tjfields');
		}

		if (JVERSION >= '3.0')
		{
			// Set sidebar action
			JHtmlSidebar::setAction('index.php?option=com_tjfields&view=cities');
		}

		$this->extra_sidebar = '';
	}
}
