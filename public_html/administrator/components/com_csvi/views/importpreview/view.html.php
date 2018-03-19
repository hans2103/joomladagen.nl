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
 * Import show the preview view.
 *
 * @package     CSVI
 * @subpackage  Imports
 * @since       6.0
 */
class CsviViewImportpreview extends JViewLegacy
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
	 * JInput.
	 *
	 * @var    JInput
	 * @since  6.7.0
	 */
	protected $input;

	/**
	 * Holds the step of in the process.
	 *
	 * @var    int
	 * @since  6.7.0
	 */
	protected $step = 3;

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
		// Show the toolbar
		$this->toolbar();

		// Render the sidebar
		$this->csviHelper = new CsviHelperCsvi;
		$this->csviHelper->addSubmenu('imports');
		$this->sidebar = JHtmlSidebar::render();
		$this->input   = JFactory::getApplication()->input;

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
		JToolBarHelper::cancel('importsource.cancel');
		JToolBarHelper::divider();
		JToolBarHelper::custom('import.start', 'play', 'play', JText::_('COM_CSVI_START_IMPORT'), false);
	}
}
