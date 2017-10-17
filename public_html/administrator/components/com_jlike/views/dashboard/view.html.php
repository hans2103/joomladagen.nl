<?php

/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * View class for a list of Jlike.
 *
 * @since  1.0.0
 */
class JLikeViewDashboard extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since  1.0.0
	 */
	public function display($tpl = null)
	{
		global $option,$mainframe;

		// Get download id
		$params = JComponentHelper::getParams('com_jlike');
		$this->downloadid = $params->get('downloadid');

		// Get installed version from xml file
		$xml     = JFactory::getXML(JPATH_COMPONENT . '/jlike.xml');
		$version = (string) $xml->version;
		$this->version = $version;

		$model = $this->getModel();
		$model->refreshUpdateSite();

		// Get new version
		$this->latestVersion = $this->get('LatestVersion');

		$JlikeHelper = new JLikeHelper;
		$JlikeHelper->addSubmenu('dashboard');

		// Get chart data
		$linechart = $this->get('LineChartValues');
		$this->linechart = $linechart;

		// Get other required data necessary for dashboard
		$this->data = $this->get('DashboardData');

		// Get Most liked content
		$this->mostLikedData = $this->_GetMostLikes();

		$checkMigrate = $this->get('checkMigrate');
		$this->checkMigrate = $checkMigrate;

		$input = JFactory::getApplication()->input;
		$post = $input->getArray($_POST);

		if (isset($post['todate']))
		{
			$to_date = $post['todate'];
		}
		else
		{
			$to_date = date('Y-m-d', strtotime(date('Y-m-d') . ' + 1 days'));
		}

		if (isset($post['fromdate']))
		{
			$from_date = $post['fromdate'];
		}
		else
		{
			$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		}

		$this->todate = $to_date;
		$this->fromdate = $from_date;

		if (JVERSION >= 3.0)
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		// Default layout is default.php
		$layout = JFactory::getApplication()->input->get('layout', 'dashboard');
		$this->setLayout($layout);

		$this->_setToolBar();

		parent::display($tpl);
	}

	/**
	 * Get Most liked content
	 *
	 * @return  int
	 *
	 * @since   2.5
	 * @todo    Purge updates has to be replaced with an events system
	 */
	protected function _GetMostLikes()
	{
		require_once JPATH_SITE . '/components/com_jlike/helper.php';
		$jlikehelperobj = new comjlikeHelper;

		return $mostlikes = $jlikehelperobj->GetMostLikes(10);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  toolbar
	 *
	 * @since  1.6
	 */
	protected function _setToolBar()
	{
		JToolBarHelper::title(JText::_('COM_JLIKE_DASHBOARD'), 'jlike.png');
		JToolBarHelper::preferences('com_jlike');
		JToolbarHelper::custom('database.fix', 'refresh', 'refresh', 'COM_JLIKE_TOOLBAR_DATABASE_FIX', false);
	}
}
