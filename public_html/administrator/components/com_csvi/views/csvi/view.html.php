<?php
/**
 * @package     CSVI
 * @subpackage  Dashboard
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Dashboard list.
 *
 * @package     CSVI
 * @subpackage  Dashboard
 * @since       6.6.0
 */
class CsviViewCsvi extends JViewLegacy
{
	/**
	 * The items to display.
	 *
	 * @var    array
	 * @since  6.6.0
	 */
	protected $items;

	/**
	 * The sidebar to show
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $sidebar = '';

	/**
	 * Executes before rendering a generic page, default to actions necessary
	 * for the Browse task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 *
	 * @since   6.6.0
	 *
	 * @throws  RuntimeException
	 */
	public function display($tpl = null)
	{
		$helper = new CsviHelperCsvi;
		$helper->setDownloadId();

		// Call the logs model
		$model = JModelLegacy::getInstance('Logs', 'CsviModel', array('ignore_request' => true));

		// Set a default ordering
		$model->setState('filter_order', 'start');
		$model->setState('filter_order_Dir', 'DESC');
		$model->setState('list.start', 0);
		$model->setState('list.limit', 10);
		$this->items = $model->getItems();

		// Check if available fields needs to be updated
		/** @var CsviModelMaintenance $maintainenceModel */
		$maintainenceModel = JModelLegacy::getInstance('Maintenance', 'CsviModel', array('ignore_request' => true));
		$maintainenceModel->checkAvailableFields();

		// Check if there are any outdated addons
		$maintainenceModel->checkAddonVersion();

		$this->addToolbar();

		// Render the sidebar
		$helper->addSubmenu('csvi');
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
	private function addToolbar()
	{
		JToolbarHelper::title('CSVI - ' . JText::_('COM_CSVI_TITLE_DASHBOARD'), 'info');
		JToolbarHelper::preferences('com_csvi');
	}
}
