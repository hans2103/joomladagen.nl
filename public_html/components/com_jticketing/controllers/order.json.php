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
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		$this->JticketingCommonHelper = new JticketingCommonHelper;
		$this->JTRouteHelper = new JTRouteHelper;
	}

	/**
	 * Function loadState
	 *
	 * @return null|object
	 */
	public function loadState()
	{
		$db      = JFactory::getDBO();
		$jinput  = JFactory::getApplication()->input;
		$country = $jinput->get('country', '', 'STRING');
		$model   = $this->getModel('order');
		$regionList   = $model->getRegionList($country);
		echo json_encode($regionList);
		jexit();
	}

	/**
	 * Get applyTax details
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function applyTax()
	{
		$app        = JFactory::getApplication();
		$input      = $app->input;
		$taxDetails = array();

		$jticketingMainHelper = new jticketingmainhelper;

		// Set Required Sessions
		$post           = $input->post;
		$totalCalAmt    = $input->get('total_calc_amt', '', 'STRING');
		$dispatcher     = JDispatcher::getInstance();

		// @TODO:need to check plugim type..
		JPluginHelper::importPlugin('jticketingtax');

		// Call the plugin and get the result
		$taxResults        = $dispatcher->trigger('addTax', array($totalCalAmt));
		$taxAmount         = $taxResults[0]->taxvalue;
		$netAmountAfterTax = $taxAmount + $totalCalAmt;

		// Get rounded ammounts for further calculations
		$roundedTaxAmount         = $jticketingMainHelper->getRoundedPrice($taxAmount);
		$roundedNetAmountAfterTax = $jticketingMainHelper->getRoundedPrice($netAmountAfterTax);

		// Get formatted output to display directly
		$formattedTaxAmount         = $jticketingMainHelper->getFormattedPrice($taxAmount);
		$formattedNetAmountAfterTax = $jticketingMainHelper->getFormattedPrice($netAmountAfterTax);

		// Collect datat in single array for json
		$taxDetails['roundedTaxAmount']           = $roundedTaxAmount;
		$taxDetails['roundedNetAmountAfterTax']   = $roundedNetAmountAfterTax;
		$taxDetails['formattedTaxAmount']         = $formattedTaxAmount;
		$taxDetails['formattedNetAmountAfterTax'] = $formattedNetAmountAfterTax;

		echo new JResponseJson($taxDetails);
	}

	/**
	 * Function save save values into table
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function save()
	{
		$user       = JFactory::getUser();
		$input      = JFactory::getApplication()->input;
		$step       = $input->get('step');
		$session = JFactory::getSession();
		$com_params = JComponentHelper::getParams('com_jticketing');

		if ($step == 'selectTicket')
		{
			$eventData = array();

			$post = $input->post;
			$eventData['eventid']              = $post->get('eventid', '', 'INT');
			$eventData['type_ticketcount']     = $post->get('type_ticketcount', '', 'ARRAY');
			$eventData['type_id']              = $post->get('type_id', '', 'ARRAY');
			$eventData['coupon_code']          = $post->get('coupon_code', '', 'STRING');
			$eventData['event_integraton_id']  = $post->get('event_integraton_id', '', 'INT');
			$eventData['user_id']    = $user->id;
			$eventData['name']  = $user->name;
			$eventData['email'] = $user->email;

			$model   = $this->getModel('order');
			$orderID  = $model->save($eventData);

			if (!empty($orderID))
			{
				$JT_orderdata[$eventData['event_integraton_id']] = $orderID;
				$session->set('JT_orderdata', $JT_orderdata);
				$session->set('JT_orderid', $orderID);
				$orderInfo = array();
				$orderInfo['success']  = 1;
				$orderInfo['order_id'] = $orderID;
				$orderInfo['message']  = JText::_('COM_JTICKETING_ORDER_CREATED_SUCCESS');

				// Render attendee info fields.
				$eventData['order_id'] = $orderID;
				$orderItems = $model->getAttendeeInfoFields($eventData);

				$this->orderitems = $orderItems['orderitems'];
				$this->fields     = $orderItems['fields'];
				$ticketTypeArr    = $orderItems['ticketTypeArr'];

				// To get orderitems data
				$model      = $this->getModel('orderitem');
				$orderItems = $model->getOrderItems($orderID);

				if ($orderItems = $model->getOrderItems($orderID))
				{
					foreach ($orderItems as $key => $val)
					{
						$orderInfo[$key]['order_items_id'] = $val->id;
						$orderInfo[$key]['ticket_type'] = $val->type_id;
					}
				}

				// Check if attendee information is enabled.
				$attendeeInfo = $com_params->get('collect_attendee_info_checkout');

				if ($attendeeInfo == 1)
				{
					$billPath = $this->JticketingCommonHelper->getViewPath('order', 'default_attendee_data');
					ob_start();
					include $billPath;
					$html = ob_get_contents();
					ob_end_clean();

					$orderInfo['attendee_html'] = $html;
				}

				$model     = $this->getModel('order');
				$orderData = $model->getItem($orderID);

				if ($orderData->amount == '0' && !empty($user->id) && $attendeeInfo != 1)
				{
					$model      = $this->getModel('user');
					$model->addRegisterdUserIntoJtuser($user->id, $orderID);

					JLoader::import('components.com_jticketing.helpers.common', JPATH_SITE);
					$JticketingCommonHelper = new JticketingCommonHelper;
					$orderInfo['redirect_invoice_view'] = $JticketingCommonHelper->createFreeTicket($user->id, $orderID);
				}

				echo json_encode($orderInfo);
				jexit();
			}
			else
			{
				return false;
			}
		}
		elseif($step == 'selectAttendee')
		{
			$post          = $input->post;
			$attendeeField = $post->get('attendee_field', '', 'ARRAY');
			$model         = $this->getModel('attendees');
			$model->setState('user_id', $user->id);

			$result        = $model->save($attendeeField);
			$orderID = $session->get('JT_orderid');

			if (!empty($orderID))
			{
				$model     = $this->getModel('order');
				$orderData = $model->getItem($orderID);

				if ($orderData->amount == '0' && !empty($user->id))
				{
					$model      = $this->getModel('user');
					$model->addRegisterdUserIntoJtuser($user->id, $orderID);

					JLoader::import('components.com_jticketing.helpers.common', JPATH_SITE);
					$JticketingCommonHelper = new JticketingCommonHelper;
					$attendeeInfo['redirect_invoice_view'] = $JticketingCommonHelper->createFreeTicket($user->id, $orderID);

					echo json_encode($attendeeInfo);
				}

				jexit();
			}
		}
		elseif($step == 'billinginfo')
		{
			$post       = $input->post;
			$orderID    = $session->get('JT_orderid');

			$billingData = array();
			$billingData             = $post->get('bill', '', 'ARRAY');
			$billingData['comment']  = $post->get('jt_comment', '', 'STRING');
			$billingData['order_id'] = $orderID;
			$billingData['user_id']  = $user->id;

			// Handle guest checkout and on-the-fly registration.
			$checkoutMethod = $post->get('account_jt', '', 'STRING');
			$billingData['checkout_method'] = $checkoutMethod;

			$model   = $this->getModel('user');
			$result     = $model->save($billingData);

			if (!empty($orderID))
			{
				$selectedGateways = $com_params->get('gateways');
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('payment');

				if (!is_array($selectedGateways) )
				{
					$gatewayParam[] = $selectedGateways;
				}
				else
				{
					$gatewayParam = $selectedGateways;
				}

				if (!empty($gatewayParam))
				{
					$gateways = $dispatcher->trigger('onTP_GetInfo', array($gatewayParam));
				}

				$this->gateways = $gateways;

				$model     = $this->getModel('order');
				$orderData = $model->getItem($orderID);
				$userInfo = array();

				if ($orderData->amount == '0' || !empty($user->id))
				{
					JLoader::import('components.com_jticketing.helpers.common', JPATH_SITE);
					$JticketingCommonHelper = new JticketingCommonHelper;
					$userInfo['redirect_invoice_view'] = $JticketingCommonHelper->createFreeTicket($user->id, $orderID);
				}

				$billPath = $this->JticketingCommonHelper->getViewPath('order', 'default_payment');
				ob_start();
				include $billPath;
				$html = ob_get_contents();
				ob_end_clean();
				$userInfo['payment_html'] = $html;
				$userInfo['success']  = 1;
				$userInfo['order_id'] = $orderID;
				$userInfo['message']  = JText::_('COM_JTICKETING_BILLING_DATA_SAVE_SUCCESS');

				echo json_encode($userInfo);
				jexit();
			}
		}
	}

	/**
	 * Get getcoupon
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function getCoupon()
	{
		$user   = JFactory::getUser();
		$db     = JFactory::getDBO();
		$input  = JFactory::getApplication()->input;
		$couponCode = $input->get('coupon_code');
		$count  = '';

		$model  = $this->getModel('order');
		$count  = $model->getCoupon($couponCode);

		if ($count)
		{
			$c[] = array(
				"value" => $count[0]->value,
				"val_type" => $count[0]->val_type
			);
		}
		else
		{
			$c[] = array("error" => 1);
		}

		echo json_encode($c);
		jexit();
	}

	/**
	 * Function loginValidate
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function loginValidate()
	{
		$input   = JFactory::getApplication()->input;
		$eventid = $input->get('eventid', '', 'STRING');
		$app     = JFactory::getApplication();
		$user    = JFactory::getUser();

		$redirectUrl = JRoute::_('index.php?option=com_jticketing&view=order&layout=checkout');

		$json = array();

		if ($user->id)
		{
			$json['redirect'] = $redirectUrl;
		}

		if (!$json)
		{
			$userpath = JPATH_SITE . '/components/com_jticketing/helpers/user.php';

			if (!class_exists('JticketingHelperUser'))
			{
				JLoader::register('JticketingHelperUser', $userpath);
				JLoader::load('JticketingHelperUser');
			}

			$userHelper = new JticketingHelperUser;

			// Now login the user
			if (!$userHelper->login(array('username' => $app->input->getString('email'), 'password' => $app->input->getString('password'))))
			{
				$json['error']['warning'] = JText::_('JTICKETING_CHECKOUT_ERROR_LOGIN');
			}
		}

		$userID = JFactory::getUser()->id;
		$session = JFactory::getSession();
		$orderId = $session->get('JT_orderid');

		if ($orderId)
		{
			$orderInfo = array();
			$orderInfo['user_id'] = $userID;

			// Update the order details.
			$model = $this->getModel('order');
			$model->updateOrderDetails($orderId, $orderInfo);
		}

		$this->jticketingmainhelper = new jticketingmainhelper;
		$order = $this->jticketingmainhelper->getorderinfo($orderId);

		// If free ticket then confirm automatically and redirect to Invoice View.
		if ($order['order_info']['0']->amount == 0 && !empty($userID))
		{
			$model = $this->getModel('user');
			$model->addRegisterdUserIntoJtuser($userID, $orderId);
			JLoader::import('components.com_jticketing.helpers.common', JPATH_SITE);
			$JticketingCommonHelper = new JticketingCommonHelper;
			$json['redirect_invoice_view'] = $JticketingCommonHelper->createFreeTicket($userID, $orderId);
		}

		$json['redirect'] = $redirectUrl;
		echo json_encode($json);
		$app->close();
	}

	/**
	 * Call from Ajax
	 *
	 * @return  void
	 *
	 * @since   1.7
	 */
	public function getUpdatedBillingInfo()
	{
		$model = $this->getModel('user');
		$res   = $model->getUpdatedBillingInfo();
	}

	/**
	 * Function selectAttendee
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function selectAttendee()
	{
		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$input                    = JFactory::getApplication()->input;
		$attendee_id              = $input->get('attendee_id', '', 'INT');
		$app                      = JFactory::getApplication();
		$user                     = JFactory::getUser();
		$params                   = array();
		$params['owner_id']       = $attendee_id;
		$params['attendee_id']    = $attendee_id;
		$details                  = $jticketingfrontendhelper->getUserEntryField($params);
		$json                     = array();

		if (!empty($details))
		{
			foreach ($details as $detail)
			{
				foreach ($detail as $field)
				{
					$json[$field->name] = $field->field_value;
				}
			}
		}

		echo json_encode($json);
		$app->close();
	}

	/**
	 * Function checkUserEmailId
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function checkUserEmailId()
	{
		$jinput = JFactory::getApplication()->input;
		$email  = $jinput->get('email', '', 'STRING');
		$model  = $this->getModel('order');
		$status = $model->checkuserExistJoomla($email);
		$e[]    = $status;

		if ($status == 1)
		{
			$e[] = JText::_('COM_JTICKETING_MAIL_EXISTS');
		}

		echo json_encode($e);
		jexit();
	}

	// @TODO:Add this in booking ticket email

	/**
	 * Get verifyBookingID
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function verifyBookingID()
	{
		$post                 = JFactory::getApplication()->input;
		$book_id              = $post->get('book_id', '', 'STRING');
		$jticketingmainhelper = new jticketingmainhelper;
		$order                = $jticketingmainhelper->verifyBookingID($book_id);

		echo json_encode($order);
		jexit();
	}

	/**
	 * Get total Ammount
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function getTotalAmount()
	{
		$app        = JFactory::getApplication();
		$input      = $app->input;
		$result     = array();
		$amount     = $input->get('amt', '', 'STRING');
		$totalPrice = $input->get('totalPrice', '', 'STRING');

		$jticketingMainHelper = new jticketingmainhelper;

		// Get all amount calulation rounded and formatted
		$roundedAmount   = $jticketingMainHelper->getRoundedPrice($amount);
		$formattedAmount = $jticketingMainHelper->getFormattedPrice($amount);

		// Get total price of current ticket type
		$roundedTotalPrice   = $jticketingMainHelper->getRoundedPrice($totalPrice);
		$formattedTotalPrice = $jticketingMainHelper->getFormattedPrice($totalPrice);

		$result['roundedTotalPrice']   = $roundedTotalPrice;
		$result['formattedTotalPrice'] = $formattedTotalPrice;
		$result['rounded_amount']      = $roundedAmount;
		$result['formatted_amount']    = $formattedAmount;

		echo new JResponseJson($result);
	}
}
