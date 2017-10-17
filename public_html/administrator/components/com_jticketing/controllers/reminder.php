<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticketing
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Jticketing is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Reminder controller.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_jticketing
 *
 * @since       1.0
 */
class JticketingControllerReminder extends JControllerForm
{
	/**
	 * Constructor.
	 *
	 * @see     JControllerLegacy
	 *
	 * @since   1.0.0
	 *
	 * @throws  Exception
	 */
	public function __construct()
	{
		$this->view_list = 'reminders';
		parent::__construct();
	}
	/**
	 *Function to get reminder days
	 *
	 * @return  void
	 *
	 * @since  1.7
	 */
	public function getDays()
	{
		$input = JFactory::getApplication()->input;
		$selectedDays = $input->get('selecteddays', '', 'INT');
		$model = $this->getModel('reminder');
		$reminderDays = $model->getDays(trim($selectedDays));
		echo $reminderDays;
		exit();
	}

	/**
	 *Function to get reminder days of present reminder
	 *
	 * @return  void
	 *
	 * @since  1.7
	 */
	public function getselectDays()
	{
		$input = JFactory::getApplication()->input;
		$selectedDays = $input->get('selecteddays', '', 'INT');
		$rid = $input->get('id', 0, 'INT');
		$model = $this->getModel('reminder');
		$reminderDays = $model->getSelectDays(trim($selectedDays), $rid);
		echo $reminderDays;

		exit();
	}
}
