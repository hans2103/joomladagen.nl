<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
require_once JPATH_COMPONENT . DS . 'controller.php';

jimport('joomla.application.component.controller');

/**
 * JticketingController helper
 *
 * @package     Jticketing
 * @subpackage  site
 * @since       2.2
 */
class JticketingControllerticket extends JControllerLegacy
{
	/**
	 * setRefund.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function setRefund()
	{
		$data  = JRequest::get('post');

		// $oid	= JRequest::getVar( 'oid', array(), 'post', 'array' );
		$model = $this->getModel('ticket');
		$val   = $model->processRefund($data);

		if ($val != 0)
		{
			$msg = JText::_('REFUND_SUCESSFULLY');
			$this->setRedirect('index.php?option=com_jticketing&view=ticket', $msg);
		}
		else
		{
			$msg = JText::_('REFUND_ERROR');
			$this->setRedirect('index.php?option=com_jticketing&view=ticket&layout=refund&id=' . $oid, $msg);
		}
	}

	/**
	 * setTransfer.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function setTransfer()
	{
		$data  = JRequest::get('post');

		// $oid	= JRequest::getVar( 'oid', array(), 'post', 'array' );
		$model = $this->getModel('ticket');
		$val   = $model->processTransfer($data);

		if ($val != 0)
		{
			$msg = JText::_('TRANSFER_SUCESSFULLY');
			$this->setRedirect('index.php?option=com_jticketing&view=ticket', $msg);
		}
		else
		{
			$msg = JText::_('TRANSFER_ERROR');
			$this->setRedirect('index.php?option=com_jticketing&view=ticket&layout=transfer&id=' . $oid, $msg);
		}
	}
}
