<?php
/**
 * @package     CSVI
 * @subpackage  Tasks
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Tasks view.
 *
 * @package     CSVI
 * @subpackage  Tasks
 * @since       6.0
 */
class CsviViewTasks extends JViewLegacy
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

		// Show the toolbar
		$this->toolbar();

		// Render the sidebar
		$this->csviHelper = new CsviHelperCsvi;
		$this->csviHelper->addSubmenu('tasks');
		$this->sidebar = JHtmlSidebar::render();

		// Display it all
		parent::display($tpl);
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
		JToolbarHelper::title(JText::_('COM_CSVI') . ' - ' . JText::_('COM_CSVI_TITLE_TASKS'), 'list-view');

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::addNew('task.add');
		}

		if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
		{
			JToolbarHelper::editList('task.edit');
		}

		if ($this->canDo->get('core.edit.state'))
		{
			JToolbarHelper::publish('tasks.publish');
			JToolbarHelper::unpublish('tasks.unpublish');
		}

		if ($this->canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'tasks.delete');
		}

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::custom('tasks.reload', 'refresh', 'refresh', JText::_('COM_CSVI_RESET_SETTINGS'), false);
		}
	}
}
