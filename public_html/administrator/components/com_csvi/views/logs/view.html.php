<?php
/**
 * @package     CSVI
 * @subpackage  Log
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;


/**
 * Log list.
 *
 * @package     CSVI
 * @subpackage  Log
 * @since       6.0
 */
class CsviViewLogs extends JViewLegacy
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
	 * The sidebar to show
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $sidebar = '';

	/**
	 * An array with log details.
	 *
	 * @var    array
	 * @since  6.6.0
	 */
	protected $logdetails = array();

	/**
	 * The path and name of the log file.
	 *
	 * @var    string
	 * @since  6.6.0
	 */
	protected $logfile;

	/**
	 * Executes before rendering a generic page, default to actions necessary
	 * for the Browse task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		$layout = $this->getLayout();

		if ($layout === 'logreader')
		{
			$app = JFactory::getApplication();
			$this->logdetails = $this->get('Logfile');
			$this->logfile = JPATH_SITE . '/logs/com_csvi.log.' . $app->input->get('run_id', 0, 'int') . '.php';

			return parent::display($tpl);
		}

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->canDo         = JHelperContent::getActions('com_csvi');

		$this->toolbar();

		// Render the sidebar
		$helper = new CsviHelperCsvi;
		$helper->addSubmenu('logs');
		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	private function toolbar()
	{
		JToolbarHelper::title(JText::_('COM_CSVI') . ' - ' . JText::_('COM_CSVI_TITLE_LOGS'), 'info');

		JToolbarHelper::custom('logs.logdetails', 'zoom-in', 'zoom-in', JText::_('COM_CSVI_DETAILS'), false);

		if ($this->canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'logs.delete');
			JToolbarHelper::custom('logs.deleteAll', 'trash', 'trash', JText::_('COM_CSVI_DELETE_ALL'), false);
		}
	}
}
