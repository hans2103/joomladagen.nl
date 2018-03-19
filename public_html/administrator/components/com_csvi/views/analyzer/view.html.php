<?php
/**
 * @package     CSVI
 * @subpackage  Analyzer
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Analyzer view.
 *
 * @package     CSVI
 * @subpackage  Analyzer
 * @since       6.0
 */
class CsviViewAnalyzer extends JViewLegacy
{
	/**
	 * The items to display.
	 *
	 * @var    array
	 * @since  6.6.0
	 */
	protected $items;

	/**
	 * Set if the file should be analyzed
	 *
	 * @var    integer
	 * @since  6.6.0
	 */
	protected $process;

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
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 * @throws  UnexpectedValueException
	 */
	public function display($tpl = null)
	{
		$jinput = JFactory::getApplication()->input;
		$this->process = $jinput->get('process', false, 'bool');

		// Check if we need to run the analyzer
		if ($this->process)
		{
			/** @var CsviModelAnalyzer $model */
			$model = $this->getModel();
			$this->items = $model->analyze();
		}

		// Show the toolbar
		$this->toolbar();

		// Render the sidebar
		$this->csviHelper = new CsviHelperCsvi;
		$this->csviHelper->addSubmenu('analyzer');
		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   6.6.0
	 *
	 * @throws  Exception
	 */
	private function toolbar()
	{
		JToolbarHelper::title('CSVI - ' . JText::_('COM_CSVI_TITLE_ANALYZER'), 'health');

		JToolbarHelper::custom('analyzer.add', 'health', 'health', JText::_('COM_CSVI_ANALYZE'), false);
	}
}
