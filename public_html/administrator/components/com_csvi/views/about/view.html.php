<?php
/**
 * @package     CSVI
 * @subpackage  About
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * The About view.
 *
 * @package     CSVI
 * @subpackage  About
 * @since       6.0
 */
class CsviViewAbout extends JViewLegacy
{
	/**
	 * List of folders and their status.
	 *
	 * @var    array
	 * @since  6.0
	 */
	protected $folders;

	/**
	 * Hold the database version.
	 *
	 * @var    float
	 * @since  6.0
	 */
	protected $schemaVersion;

	/**
	 * Array of database errors.
	 *
	 * @var    array
	 * @since  6.6.0
	 */
	protected $errors;

	/**
	 * CSVI Helper file.
	 *
	 * @var    CsviHelperCsvi
	 * @since  6.6.0
	 */
	protected $csviHelper;

	/**
	 * The sidebar to show
	 *
	 * @var    string
	 * @since  6.6.0
	 */
	protected $sidebar = '';

	/**
	 * Executes before rendering the page for the Read task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 *
	 * @since   6.0
	 */
	public function display($tpl = null)
	{
		/** @var CsviModelAbout $model */
		$model = $this->getModel();

		// Assign the values
		$this->folders = $model->getFolderCheck();

		// Get the schema version
		$this->schemaVersion = $model->getSchemaVersion();

		// Check for database errors
		$changeSet = $model->getChangeSet();
		$this->errors = $changeSet->check();

		$this->addToolbar();

		// Render the sidebar
		$this->csviHelper = new CsviHelperCsvi;
		$this->csviHelper->addSubmenu('about');
		$this->sidebar = JHtmlSidebar::render();

		// Check if available fields needs to be updated
		$maintainenceModel = JModelLegacy::getInstance('Maintenance', 'CsviModel', array('ignore_request' => true));
		$maintainenceModel->checkAvailableFields();

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
		JToolbarHelper::title('CSVI - ' . JText::_('COM_CSVI_TITLE_ABOUT'), 'info');
		JToolbarHelper::custom('about.fix', 'refresh', 'refresh', 'COM_CSVI_TOOLBAR_DATABASE_FIX', false);
		JToolbarHelper::custom('about.fixmenu', 'refresh', 'refresh', 'COM_CSVI_TOOLBAR_MENU_FIX', false);
	}
}
