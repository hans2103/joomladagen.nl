<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

/**
 * Logs view.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealgatewayViewLogs extends JViewLegacy
{
	/**
	 * JD iDEAL Gateway helper
	 *
	 * @var    JdIdealgatewayHelper
	 * @since  4.0
	 */
	protected $jdidealgatewayHelper;

	/**
	 * List of properties
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $items = array();

	/**
	 * The pagination object
	 *
	 * @var    JPagination
	 * @since  1.0
	 */
	protected $pagination;

	/**
	 * Object of the user state
	 *
	 * @var    object
	 * @since  1.0
	 */
	protected $state;

	/**
	 * Form with filters
	 *
	 * @var    array
	 * @since  1.0
	 */
	public $filterForm = array();

	/**
	 * List of active filters
	 *
	 * @var    array
	 * @since  1.0
	 */
	public $activeFilters = array();

	/**
	 * List of addons that have been loaded
	 *
	 * @var    array
	 * @since  4.0
	 */
	protected $classes = array();

	/**
	 * The log history
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $history = '';

	/**
	 * Access rights of a user
	 *
	 * @var    JObject
	 * @since  4.0
	 */
	protected $canDo;

	/**
	 * The sidebar to show
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $sidebar = '';

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   2.13
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		$task = JFactory::getApplication()->input->get('task');
		$this->canDo = JHelperContent::getActions('com_jdidealgateway');

		if ($task === 'history')
		{
			$this->history = $this->get('History');
		}
		else
		{
			$this->items = $this->get('Items');
			$this->pagination = $this->get('Pagination');
			$this->filterForm = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');

			// Load all addons
			$this->classes = $this->get('Addons');
		}

		// Show the toolbar
		$this->toolbar();

		// Render the submenu
		$this->jdidealgatewayHelper = new JdidealGatewayHelper;
		$this->jdidealgatewayHelper->addSubmenu('logs');
		$this->sidebar = JHtmlSidebar::render();

		// Display it all
		parent::display($tpl);
	}

	/**
	 * Display the toolbar.
	 *
	 * @return  void.
	 *
	 * @since   2.13
	 *
	 * @throws  RuntimeException
	 */
	private function toolbar()
	{
		JToolbarHelper::title(JText::_('COM_JDIDEALGATEWAY_JDIDEAL_LOGS'), 'clock');

		if ($this->canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'logs.delete', 'JTOOLBAR_DELETE');
		}
	}
}
