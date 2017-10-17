<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */


defined('_JEXEC') or die('Restricted access');
require_once JPATH_COMPONENT . DS . 'controller.php';
jimport('joomla.application.component.controller');

/**
 * Controller for payment
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingControllerpayment extends jticketingController
{
	/**
	 * Confirm payment
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function confirmpayment()
	{
		$model    = $this->getModel('payment');
		$session  = JFactory::getSession();
		$jinput   = JFactory::getApplication()->input;
		$order_id = $session->get('JT_orderid');

		if (!$order_id)
		{
			$order_id = $jinput->get("order_id", '', 'string');
		}

		$session->set('JT_order_id', $order_id);
		$pg_plugin = $jinput->get('processor');
		$response  = $model->confirmpayment($pg_plugin, $order_id);
	}

	/**
	 * process payment and pass data to model
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function processpayment()
	{
		$mainframe = JFactory::getApplication();
		$jinput    = JFactory::getApplication()->input;
		$session   = JFactory::getSession();

		if ($session->has('payment_submitpost'))
		{
			$post = $session->get('payment_submitpost');
			$session->clear('payment_submitpost');
		}
		else
		{
			$post = JRequest::get('post');
		}

		$pg_plugin = $jinput->get('processor');
		$model     = $this->getModel('payment');
		$order_id  = $jinput->get('order_id', '', 'STRING');

		if (empty($post) || empty($pg_plugin))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('SOME_ERROR_OCCURRED'), 'error');

			return;
		}

		$response = $model->processpayment($post, $pg_plugin, $order_id);
		$mainframe->redirect($response['return'], $response['msg']);
	}

	/**
	 * Change payment gateway
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function changegateway()
	{
		$model = $this->getModel('payment');
		$model->changegateway();
	}

	/**
	 * Redirect to stripe
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function authStripeConnect()
	{
		$input = JFactory::getApplication()->input;
		JPluginHelper::importPlugin('payment', 'stripe');
		$dispatcher = JDispatcher::getInstance();
		$authUrl    = $dispatcher->trigger('stripeConnectAuthUrl', array());

		if (!empty($authUrl[0]))
		{
			header('Location:' . $authUrl[0]);
		}
	}

	/**
	 * Get autorisation url for stripe
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function StoreStripeConnectParmas()
	{
		$input   = JFactory::getApplication()->input;
		$ac_code = $input->get('code', '', 'STRING');

		JPluginHelper::importPlugin('payment', 'stripe');
		$dispatcher = JDispatcher::getInstance();

		//  Params auth code, component name
		$result = $dispatcher->trigger('StoreStripeConnectParmas', array($ac_code, 'com_jticketing'));

		$session  = JFactory::getSession();
		$redirect = $session->get('url_create_event');

		if ($result[0])
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('COM_JGIVE_STRIPE_CONNECTED'), 'success');
			$this->setRedirect($redirect);
		}
		else
		{
			$this->setRedirect($redirect);
		}
	}

	/**
	 * Add stripe data to payout
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function stripeAddPayout()
	{
		// If( $pg_plugin == 'stripe')
		{
			// Retrieve the request's body and parse it as JSON
			$body = @file_get_contents('php://input');

			// Grab the event information
			$post = $event_json = json_decode($body, true);
			file_put_contents('stripeweb.txt', json_encode($post), FILE_APPEND);

			if ($post['type'] == "application_fee.created")
			{
				$model = $this->getModel('payment');

				// Parmas data, refund flag
				$model->stripeAddPayout($post, 0);
			}
			elseif ($post['type'] == "application_fee.refunded")
			{
				// Parmas data, refund flag
				$model->stripeAddPayout($post, 1);
			}
			else
			{
				return true;
			}
		}

		return true;
	}
}
