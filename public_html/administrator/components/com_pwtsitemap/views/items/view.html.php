<?php
/**
 * @package    Pwtsitemap
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

/**
 * View class for a list of menuitems
 *
 * @since  1.0.0
 */
class PwtSitemapViewItems extends JViewLegacy
{
	/**
	 * The item data.
	 *
	 * @var    object
	 * @since  1.0.0
	 */
	protected $items;

	/**
	 * The model state.
	 *
	 * @var    JObject
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws Exception
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.manage', 'com_pwtsitemap'))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 404);
		}

		// Add submenus
		PwtSitemapHelper::addSubmenu('items');

		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->state         = $this->get('State');
		$this->sidebar       = JHtmlSidebar::render();

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
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
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_PWTSITEMAP_TITLE_ITEMS'), 'pwtsitemap');

		$title = JText::_('JTOOLBAR_BATCH');

		// Instantiate a new JLayoutFile instance and render the batch button
		$layout = new JLayoutFile('joomla.toolbar.batch');

		$dhtml = $layout->render(array('title' => $title));
		JToolbar::getInstance()->appendButton('Custom', $dhtml, 'batch');
	}
}
