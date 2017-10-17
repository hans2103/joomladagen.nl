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

require_once JPATH_COMPONENT . '/controller.php';

jimport('joomla.application.component.controller');

/**
 * JTicketing
 *
 * @since  1.6
 */
class JticketingControllerOrder extends jticketingController
{
	/**
	 * Function changegateway
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function changegateway()
	{
		$model = $this->getModel('payment');
		$model->changegateway();
	}

	// @TODO:Add this in booking ticket email

	/**
	 * Get checkGeustForOnlineEvent
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function checkGeustForOnlineEvent()
	{
		$redirect = JRoute::_('index.php?option=com_jticketing&view=order&layout=default_online', false);
		$this->setRedirect($redirect, $msg);
	}

	/**
	 * Function to create order 
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function createOrderAPI()
	{
		$input   = JFactory::getApplication()->input;
		$eventID = $input->get('eventid');
		$user    = JFactory::getUser();

		if (!empty($eventID) && !empty($user->id))
		{
			$data = array();
			$data['user_id'] = $user->id;
			$data['eventid'] = $eventID;

			if (!empty($data))
			{
				$model = $this->getModel('order');
				$result = $model->createOrderAPI($data);

				if ($result)
				{
					$msg = JText::_('COM_JTICKETING_ENROLL_SUCCESS_MSG');
					$redirect = JRoute::_('index.php?option=com_jticketing&view=event&id=' . $eventID, false);
					$this->setRedirect($redirect, $msg);
				}
			}
		}
	}
}
