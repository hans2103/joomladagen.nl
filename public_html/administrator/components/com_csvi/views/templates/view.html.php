<?php
/**
 * @package     CSVI
 * @subpackage  Templates
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Templates view.
 *
 * @package     CSVI
 * @subpackage  Templates
 * @since       6.0
 */
class CsviViewTemplates extends JViewLegacy
{
	/**
	 * The items to display.
	 *
	 * @var    array
	 * @since  6.6.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    JPagination
	 * @since  6.6.0
	 */
	protected $pagination;

	/**
	 * The user state.
	 *
	 * @var    JObject
	 * @since  6.6.0
	 */
	protected $state;

	/**
	 * Form with filters
	 *
	 * @var    array
	 * @since  6.6.0
	 */
	public $filterForm = array();

	/**
	 * List of active filters
	 *
	 * @var    array
	 * @since  6.6.0
	 */
	public $activeFilters = array();

	/**
	 * Access rights of a user
	 *
	 * @var    JObject
	 * @since  6.6.0
	 */
	protected $canDo;

	/**
	 * An instance of JDatabaseDriver.
	 *
	 * @var    JDatabaseDriver
	 * @since  6.6.0
	 */
	protected $db;

	/**
	 * The sidebar to show
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $sidebar = '';

	/**
	 * CSVI Helper file.
	 *
	 * @var    CsviHelperCsvi
	 * @since  6.6.0
	 */
	protected $csviHelper;


	/**
	 * Executes before rendering the page for the Browse task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 *
	 * @since   6.0
	 */
	public function display($tpl = null)
	{
		// Load the data
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->canDo         = JHelperContent::getActions('com_csvi');
		$this->db            = JFactory::getDbo();

		// Check if we have any templates
		if (count($this->items) === 0 && !$this->state->get('filter.search'))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_CSVI_TEMPLATES_SUGGEST_EXAMPLE_TEMPLATES'));
		}

		// Show the toolbar
		$this->toolbar();

		// Render the sidebar
		$this->csviHelper = new CsviHelperCsvi;
		$this->csviHelper->addSubmenu('templates');
		$this->sidebar = JHtmlSidebar::render();

		// Check if available fields needs to be updated
		/** @var CsviModelMaintenance $maintainenceModel */
		$maintainenceModel = JModelLegacy::getInstance('Maintenance', 'CsviModel', array('ignore_request' => true));
		$maintainenceModel->checkAvailableFields();

		// Check if there are any outdated addons
		$maintainenceModel->checkAddonVersion();

		// Check if addons are installed
		$maintainenceModel->checkInstalledAddons();

		// Display it all
		return parent::display($tpl);
	}

	/**
	 * Displays a toolbar for a specific page.
	 *
	 * @return  void.
	 *
	 * @since   6.6.0
	 */
	private function toolbar()
	{
		JToolbarHelper::title(JText::_('COM_CSVI') . ' - ' . JText::_('COM_CSVI_TITLE_TEMPLATES'), 'folder');

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::addNew('template.add');
		}

		if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
		{
			JToolbarHelper::editList('template.edit');
		}

		if ($this->canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'templates.delete');
		}

		JToolbarHelper::custom('templates.templatefields', 'list', 'list', JText::_('COM_CSVI_FIELDS'));

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::custom('templates.duplicate', 'save-copy', 'save-copy', JText::_('COM_CSVI_COPY'));
		}
	}
}
