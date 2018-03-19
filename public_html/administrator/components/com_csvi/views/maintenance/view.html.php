<?php
/**
 * @package     CSVI
 * @subpackage  View
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Maintenance view.
 *
 * @package     CSVI
 * @subpackage  View
 * @since       6.0
 */
class CsviViewMaintenance extends JViewLegacy
{
	/**
	 * Show the extra help
	 *
	 * @var    int
	 * @since  6.5.0
	 */
	protected $extraHelp;

	/**
	 * List of supported components
	 *
	 * @var    array
	 * @since  6.0
	 */
	protected $components;

	/**
	 * Array of options for the component
	 *
	 * @var    array
	 * @since  6.0
	 */
	protected $options = array();

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
	 * JInput class
	 *
	 * @var    JInput
	 * @since  6.7.0
	 */
	protected $input;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   6.7.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 * @throws  UnexpectedValueException
	 */
	public function display($tpl = null)
	{
		// Load the extra help settings
		$db              = JFactory::getDbo();
		$settings        = new CsviHelperSettings($db);
		$this->extraHelp = $settings->get('extraHelp');
		$this->input     = JFactory::getApplication()->input;

		/** @var CsviModelMaintenance $model */
		$model = $this->getModel();

		// Get the component list
		$this->components = $model->getComponents();

		// Get the maintenance options
		$component = strtolower(JFactory::getApplication()->input->get('component'));

		if (!empty($component))
		{
			$this->options = $model->getOperations($component);
		}

		// Check if available fields needs to be checked
		$task = $this->input->get('task', '', 'cmd');

		if (!$task)
		{
			$model->checkAvailableFields();
		}

		// Show the toolbar
		$this->toolbar($this->getLayout());

		// Render the sidebar
		$this->csviHelper = new CsviHelperCsvi;
		$this->csviHelper->addSubmenu('maintenance');
		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @param   string  $layout  The layout being used.
	 *
	 * @return  void
	 *
	 * @since   6.7.0
	 *
	 * @throws  Exception
	 */
	private function toolbar($layout = '')
	{
		JToolbarHelper::title('CSVI - ' . JText::_('COM_CSVI_TITLE_MAINTENANCE'), 'tools');

		switch ($layout)
		{
			case 'run':
				JToolbarHelper::custom('maintenance.canceloperation', 'cancel', 'cancel', JText::_('COM_CSVI_CANCEL'), false);
				break;
			default:
				JToolbarHelper::custom('maintenance.read', 'arrow-right', 'arrow-right', JText::_('COM_CSVI_CONTINUE'), false);
				JToolbarHelper::divider();
				JToolbarHelper::custom('hidetips', 'help', 'help', JText::_('COM_CSVI_HELP'), false);
				break;
		}
	}
}
