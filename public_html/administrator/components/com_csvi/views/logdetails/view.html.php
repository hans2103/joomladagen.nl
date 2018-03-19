<?php
/**
 * @package     CSVI
 * @subpackage  Logdetails
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Log Details view.
 *
 * @package     CSVI
 * @subpackage  Logdetails
 * @since       6.0
 */
class CsviViewLogdetails extends JViewLegacy
{
	/**
	 * The run ID
	 *
	 * @var    int
	 * @since  6.6.0
	 */
	protected $runId;

	/**
	 * List of log results
	 *
	 * @var    array
	 * @since  6.6.0
	 */
	protected $logResult = array();

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
	 * The return URL
	 *
	 * @var    int
	 * @since  6.6.0
	 */
	protected $returnUrl;

	/**
	 * Executes before rendering the page for the Browse task.
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
		$this->runId     = JFactory::getApplication()->input->getInt('run_id', 0);
		$this->returnUrl = JFactory::getApplication()->input->get('return', '', 'base64');

		if (!$this->runId)
		{
			JFactory::getApplication()->redirect('index.php?option=com_csvi&view=logs');
		}

		$model               = $this->getModel();
		$this->logResult     = $model->getStats($this->runId);
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Show the toolbar
		$this->toolbar();

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
		JToolbarHelper::title(JText::_('COM_CSVI') . ' - ' . JText::_('COM_CSVI_TITLE_LOGDETAILS'), 'lamp');

		JToolbarHelper::custom('logdetails.cancel', 'arrow-left', 'arrow-left', JText::_('COM_CSVI_BACK'), false);
	}
}
