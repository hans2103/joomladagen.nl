<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

require_once JPATH_COMPONENT . '/controller.php';

jimport('joomla.application.component.controller');

/**
 * Class for Jticketing Calendar List Controller
 *
 * @package  JTicketing
 * @since    1.5
 */
class JticketingControllercalendar extends JControllerLegacy
{
	/**
	 * For calender.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventList()
	{
		include JPATH_ROOT . '/components/com_jticketing/models/calendar.php';
		$ob   = new JticketingModelCalendar;
		$data = $ob->getEvents();
		echo json_encode($data, true);
		jexit();
	}
}
