<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
require_once JPATH_COMPONENT . '/controller.php';
jimport('joomla.application.component.controller');

/**
 * Controller for mypayout to show payout
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingControllermypayouts extends JControllerLegacy
{
	/**
	 * Add new payout entry opens edit payout view
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function add()
	{
		$msg      = '';
		$redirect = JRoute::_('index.php?option=com_jticketing&view=mypayouts&layout=edit_payout', false);
		$this->setRedirect($redirect, $msg);
	}

	/**
	 * Add new payout entry
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function save()
	{
		JRequest::checkToken() or jexit('Invalid Token');

		// Get model
		$model = $this->getModel('mypayouts');
		$post = JRequest::get('post');
		$obj  = new stdClass;

		if (!empty($post['edit_id']))
		{
			$result = $model->editPayout();
		}
		else
		{
			$result = $model->savePayout();
		}

		$redirect = JRoute::_('index.php?option=com_jticketing&view=mypayouts', false);

		if ($result)
		{
			$msg = JText::_('COM_JTICKETING_PAYOUT_SAVED');
		}
		else
		{
			$msg = JText::_('COM_JTICKETING_PAYOUT_ERROR_SAVING');
		}

		$this->setRedirect($redirect, $msg);
	}

	/**
	 * Cancels payout
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_jticketing');
	}

	/**
	 * Publish payout data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function publish()
	{
		$input = JFactory::getApplication()->input;
		$post  = $input->post;

		// Get some variables from the request
		$cid = $input->get('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);
		$model = $this->getModel('mypayouts');

		if ($model->setItemState($cid, 1))
		{
			$msg = JText::_('AMOUNT_PAID_S');
		}
		else
		{
			$msg = $model->getError();
		}

		$this->setRedirect('index.php?option=com_jticketing&view=mypayouts&layout=default', $msg);
	}

	/**
	 * Unpublish payout data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function unpublish()
	{
		$input = JFactory::getApplication()->input;

		// Get some variables from the request
		$cid = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);
		$model = $this->getModel('mypayouts');

		if ($model->setItemState($cid, 0))
		{
			$msg = JText::_('AMOUNT_UNPAID_S');
		}
		else
		{
			$msg = $model->getError();
		}

		$this->setRedirect('index.php?option=com_jticketing&view=mypayouts&layout=default', $msg);
	}

	/**
	 * Remove payout data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function remove()
	{
		$post    = JRequest::get('post');
		$payeeid = $post['cid'];
		$model   = $this->getModel('mypayouts');
		$confrim = $model->delete_payout($payeeid);

		if ($confrim)
		{
			$msg = JText::_('PAOUT_DELETED_SCUSS');
		}
		else
		{
			$msg = JText::_('PAOUT_DELETED_ERROR');
		}

		if (JVERSION >= '1.6.0')
		{
			$this->setRedirect(JURI::base() . "index.php?option=com_jticketing&view=mypayouts", $msg);
		}
		else
		{
			$this->setRedirect(JURI::base() . "index.php?option=com_jticketing&view=mypayouts", $msg);
		}
	}
}
