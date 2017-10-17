<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * Main view class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */

class JticketingViewcp extends JViewLegacy
{
	/**
	 * Function to display.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths
	 *
	 * @return  void.
	 *
	 * @since	1.8
	 */
	public function display($tpl = null)
	{
		$model                    = $this->getModel();
		$com_params               = JComponentHelper::getParams('com_jticketing');
		$this->downloadid         = $com_params->get('downloadid');
		$this->currency           = $com_params->get('currency');
		$this->siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
		$input             = JFactory::getApplication()->input;
		$layout            = $input->get('layout');
		$model             = $this->getModel();
		$this->ordersArray = $model->getOrdersArray();
		$this->salesArray  = $model->getSalesArray();

		if (isset($this->siteadmin_comm_per) and $this->siteadmin_comm_per > 0)
		{
			$this->commisionsArray = $model->getCommisionsArray();
		}

		$this->ticketSalesLastweek = $model->getTicketSalesLastweek();
		$com_params     = JComponentHelper::getParams('com_jticketing');
		$this->currency = $com_params->get('currency');

		// Get data from the model
		$orderscount                   = $this->get('orderscount');
		$this->latestVersion           = $model->getLatestVersion();
		$tot_periodicorderscount       = $this->get('periodicorderscount');
		$this->tot_periodicorderscount = $tot_periodicorderscount;
		$statsforbar                   = $model->statsforbar();
		$this->statsforbar             = $statsforbar;

		// Calling line-graph function
		$this->statsForPie = $model->statsForPie();

		// Get data from the model
		$this->allincome = $this->get('AllOrderIncome');
		$this->monthIncome  = $this->get('MonthIncome');
		$this->allMonthName = $this->get('Allmonths');
		$this->topFiveEvents = $this->get('TopFiveEvents');
		$this->dashboardData = $this->get('DashboardData');

		if ($this->dashboardData['integrationSource'] == 'com_jticketing')
		{
			$this->eventUrl = 'index.php?option=com_jticketing&view=events';
		}

		if ($this->dashboardData['integrationSource'] == 'com_jevents')
		{
			$this->eventUrl = 'index.php?option=com_jevents&task=icalevent.list';
		}

		if ($this->dashboardData['integrationSource'] == 'com_community')
		{
			$this->eventUrl = 'index.php?option=com_community&view=events';
		}

		if ($this->dashboardData['integrationSource'] == 'com_easysocial')
		{
			$this->eventUrl = 'index.php?option=com_easysocial&view=events';
		}

		// Get installed version from xml file
		$xml           = JFactory::getXML(JPATH_COMPONENT . '/jticketing.xml');
		$version       = (string) $xml->version;
		$this->version = $version;
		$model = $this->getModel();
		$model->refreshUpdateSite();
		$JticketingHelper = new JticketingHelper;
		$JticketingHelper->addSubmenu('cp');
		$this->_setToolBar();

		if (!$layout)
		{
			$this->setLayout('default');
		}

		if (JVERSION >= '3.0')
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		parent::display($tpl);
	}

	/**
	 * Function to set tool bar.
	 *
	 * @return void
	 *
	 * @since	1.8
	 */
	public function _setToolBar()
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::base() . 'components/com_jticketing/assets/css/jticketing.css');
		$bar = JToolBar::getInstance('toolbar');

			JToolBarHelper::custom('cp.migrate', 'refresh', 'refresh', 'JTOOLBAR_MIGRATE', false);
			JToolBarHelper::title(JText::_('COM_JTICKETING_COMPONENT') . JText::_('COM_JTICKETING_COMPONENT_DASHBOARD'), 'dashboard');

		$input = JFactory::getApplication()->input;
		JToolBarHelper::preferences('com_jticketing');
	}
}
