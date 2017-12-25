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
 * Dashboard view.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealgatewayViewJdidealgateway extends JViewLegacy
{
	/**
	 * JD iDEAL Gateway helper
	 *
	 * @var    JdidealGatewayHelper
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
	 * List of addons that have been loaded
	 *
	 * @var    array
	 * @since  4.0
	 */
	protected $classes = array();

	/**
	 * The sidebar to show
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $sidebar = '';

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.0
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		/** @var JdidealgatewayModelJdidealgateway $model */
		$model = $this->getModel();
		$model->checkNotifyScript();

		$this->jdidealgatewayHelper = new JdidealGatewayHelper;

		// Get the model
		/** @var JdidealgatewayModelLogs $logsModel */
		$logsModel = JModelLegacy::getInstance('Logs', 'JdidealgatewayModel');

		// Load the items
		$this->items = $logsModel->getItems();

		// Load all addons
		$this->classes = $logsModel->getAddons();

		// Check if cURL is active
		if (!function_exists('curl_init') || !is_callable('curl_init'))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JDIDEALGATEWAY_CURL_NOT_AVAILABLE'), 'error');
		}

		// Show the toolbar
		$this->toolbar();

		// Render the sidebar
		$this->jdidealgatewayHelper->addSubmenu('jdidealgateway');
		$this->sidebar = JHtmlSidebar::render();

		// Display it all
		return parent::display($tpl);
	}

	/**
	 * Displays a toolbar for a specific page.
	 *
	 * @return  void.
	 *
	 * @since   2.0
	 *
	 * @throws  Exception
	 */
	private function toolbar()
	{
		// Use our own layout because Joomla forces the use of the icon- prefix
		$layout = new JLayoutFile('joomla.toolbar.jdtitle');
		$html   = $layout->render(array('title' => JText::_('COM_JDIDEALGATEWAY_JDIDEAL'), 'icon' => 'jdideal'));

		$app = JFactory::getApplication();
		$app->JComponentTitle = $html;
		JFactory::getDocument()->setTitle(
			$app->get('sitename') . ' - ' . JText::_('JADMINISTRATION') . ' - ' . strip_tags(JText::_('COM_JDIDEALGATEWAY_JDIDEAL'))
		);

		// Options button.
		if (JFactory::getUser()->authorise('core.admin', 'com_jdidealgateway'))
		{
			JToolbarHelper::preferences('com_jdidealgateway');
		}
	}
}
