<?php
/**
 * @package     CSVI
 * @subpackage  Imports
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Imports view.
 *
 * @package     CSVI
 * @subpackage  Imports
 * @since       6.0
 */
class CsviViewImports extends JViewLegacy
{
	/**
	 * The sidebar to show
	 *
	 * @var    string
	 * @since  6.6.0
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
	 * List of export templates.
	 *
	 * @var    array
	 * @since  6.6.0
	 */
	protected $templates = array();

	/**
	 * ID of the template last used.
	 *
	 * @var    int
	 * @since  6.6.0
	 */
	protected $lastRunId;

	/**
	 * Holds the step of in the process.
	 *
	 * @var    int
	 * @since  6.7.0
	 */
	protected $step = 1;

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
		// Load the list of export template
		/** @var CsviModelTemplates $templateModel */
		$templateModel = JModelLegacy::getInstance('Templates', 'CsviModel', array('ignore_request' => true));
		$templateModel->setState('filter.action', 'import');
		$templateModel->setState('filter.enabled', 1);
		$templateModel->setState('list.ordering', 'ordering');
		$templateModel->setState('list.direction', 'ASC');
		$this->templates = $templateModel->getItems();

		if ($this->templates)
		{
			// Get the last template id which was used for export
			$templateModel->setState('list.ordering', 'lastrun');
			$templateModel->setState('list.direction', 'DESC');
			$lastRunItems = $templateModel->getItems();
			$lastRun = array_shift($lastRunItems);
			$this->lastRunId = '';

			// If template id is set in the URL use that to select the template
			$csvi_template_id = JFactory::getApplication()->input->get('csvi_template_id', 0, 'int');
			$db = JFactory::getDbo();

			if ($csvi_template_id)
			{
				$this->lastRunId = $csvi_template_id;
			}
			elseif ($lastRun->lastrun !== $db->getNullDate())
			{
				$this->lastRunId = $lastRun->csvi_template_id;
			}
		}

		// Show the toolbar
		$this->toolbar();

		// Render the sidebar
		$this->csviHelper = new CsviHelperCsvi;
		$this->csviHelper->addSubmenu('imports');
		$this->sidebar = JHtmlSidebar::render();

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
		JToolbarHelper::title(JText::_('COM_CSVI') . ' - ' . JText::_('COM_CSVI_TITLE_IMPORTS'), 'upload');
		JToolBarHelper::custom('imports.selectsource', 'upload', 'upload', JText::_('COM_CSVI_SELECT_IMPORTFILE'), false);
	}
}
