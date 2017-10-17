<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Unauthorized Access');
jimport('joomla.application.component.view');

/**
 * Class for Jticketing Event view
 *
 * @package  JTicketing
 * @since    1.5
 */
class JticketingViewEvent extends JViewLegacy
{
	/**
	 * Method to display event
	 *
	 * @param   object  $tpl  template name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		return $this->export();
	}

	/**
	 * Method to display event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function export()
	{
		$app         = JFactory::getApplication();
		$user        = JFactory::getUser();
		$this->state = $this->get('State');
		$event       = $this->get('Data');
		header('Content-type: text/calendar; charset=utf-8');
		require_once JPATH_SITE . '/components/com_jticketing/views/event/tmpl/default_ical.php';
		$ts       = substr(md5(rand(0, 100)), 0, 5);
		$fileName = 'calendar_' . $event->title . '_' . $ts . '.ics';
		header('Content-Disposition: inline; filename=' . $fileName);
		exit;
	}
}
