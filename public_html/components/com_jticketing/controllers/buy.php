<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_COMPONENT . DS . 'controller.php';

jimport('joomla.application.component.controller');

/**
 * JTicketing
 *
 * @since  1.6
 */
class JticketingControllerbuy extends jticketingController
{
	/**
	 * Constructor
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function __construct()
	{
		parent::__construct();

		// Initialise the session object
		$this->session = JFactory::getSession();

		// Language
		$language = JFactory::getLanguage();

		// Set the base directory for the language
		$base_dir = JPATH_SITE;

		// Load the language. IMPORTANT Becase we use ajax to load cart
		$language->load('com_jticketing', $base_dir, $language->getTag(), true);
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
		$model   = $this->getModel('buy');
		$state   = $model->getuserState($country);
		echo json_encode($state);
		jexit();
	}

	/**
	 * Get formatBillingData
	 *
	 * @param   string  $billinginfo  billinginfo
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function formatBillingData($billinginfo)
	{
		$billinginfo->firstname    = $billinginfo->firstname;
		$billinginfo->lastname     = $billinginfo->lastname;
		$billinginfo->user_email   = $billinginfo->user_email;
		$billinginfo->address_type = 'Billing';
		$billinginfo->vat_number   = $billinginfo->vat_number;
		$billinginfo->tax_exempt   = $billinginfo->tax_exempt;
		$billinginfo->country      = $billinginfo->country_code;
		$billinginfo->city         = $billinginfo->city;
		$billinginfo->state        = $billinginfo->state_code;
		$billinginfo->address1     = $billinginfo->address;
		$billinginfo->address2     = '';
		$billinginfo->zipcode      = $billinginfo->zipcode;
		$billinginfo->phone        = $billinginfo->phone;

		return $billinginfo;
	}

	/**
	 * Get getorderHTML details
	 *
	 * @param   string  $order_id  order_id
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function getorderHTML($order_id)
	{
		$jticketingmainhelper = new jticketingmainhelper;
		$jticketingModelbuy   = new jticketingModelbuy;
		$order                = $jticketingmainhelper->getOrderInfo($order_id);
		JLoader::import('buy', JPATH_SITE . DS . 'components' . DS . 'com_jticketing' . DS . 'models');

		$this->billinfo = $jticketingModelbuy->getuserdata($order_id);

		if (!empty($order))
		{
			$this->order_authorized = $jticketingmainhelper->getorderAuthorization($order["order_info"][0]->user_id);
			$this->orderinfo        = $order['order_info'];
			$this->orderitems       = $order['items'];
		}
		else
		{
			$this->noOrderDetails = 1;
		}

		$app = JFactory::getApplication();

		$jticketingmainhelper = new jticketingmainhelper;
		$view                 = $jticketingmainhelper->getViewpath('orders', 'order');
		ob_start();

		include $view;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Get concept details from api for given conceptId
	 *
	 * @param   string  $order_id      order_id
	 * @param   string  $order_status  order_status
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function getEventMemberid($order_id, $order_status)
	{
		$db = &JFactory::getDBO();

		if ($order_status == 'P' OR $order_status == 'RF')
		{
			$query = "SELECT user_id
				FROM #__jticketing_order where id=" . $order_id . "  AND status NOT LIKE 'C' ";
		}
		else
		{
			$query = "SELECT user_id
			FROM #__jticketing_order where id=" . $order_id . "  AND status='C'";
		}

		$db->setQuery($query);

		return $user_id = $db->loadResult();
	}

	/**
	 * Get applytax details
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function applytax()
	{
		$input          = JFactory::getApplication()->input;

		// Set Required Sessions
		$post           = $input->post;
		$total_calc_amt = $input->get('total_calc_amt', '', 'STRING');
		$dispatcher     = JDispatcher::getInstance();

		// @TODO:need to check plugim type..
		JPluginHelper::importPlugin('jticketingtax');

		// Call the plugin and get the result
		$taxresults = $dispatcher->trigger('addTax', array($total_calc_amt));
		echo json_encode($taxresults['0']);
		jexit();
	}

	/**
	 * Get buytickets details
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function buytickets()
	{
		$input = JFactory::getApplication()->input;

		// Set Required Sessions
		$session = JFactory::getSession();
		$model   = $this->getModel('buy');

		$model->clearSession();

		$post  = $input->post;
		$model = $this->getModel('buy');

		$model->setSessionEventid($post);

		// End Set Required Sessions
		$url   = JUri::base() . substr(JRoute::_('index.php?option=com_jticketing&view=buy'), strlen(JUri::base(true)) + 1);
		$this->setRedirect($url);
	}

	/**
	 * Get editorder details
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function editorder()
	{
		$session              = JFactory::getSession();
		$jticketingmainhelper = new jticketingmainhelper;
		$user                 = JFactory::getUser();
		$input                = JFactory::getApplication()->input;
		$post                 = $input->post;
		$order_id             = $post->get('order_id');
		$isorderauthorised    = $jticketingmainhelper->getorderAuthorization($user->id);
		$JT_orderid           = $session->get('JT_orderid');

		if ($user->id)
		{
			if (!$isorderauthorised)
			{
				$data['success'] = 0;
				$data['message'] = JText::_('COM_JTICKETING_ORDER_AUTHORISATION_FAILED');
				echo json_encode($data);
				jexit();
			}
		}

		if ($order_id != $JT_orderid)
		{
			$data['success'] = 0;
			$data['message'] = JText::_('COM_JTICKETING_ORDER_AUTHORISATION_FAILED');
			echo json_encode($data);
			jexit();
		}

		$id      = $input->get('cid');
		$model   = $this->getModel('buy');
		$eventid = $session->get('JT_eventid');

		$com_params          = JComponentHelper::getParams('com_jticketing');
		$checkout_mehtod_buy = $com_params->get('checkout_mehtod_buy');
		$res                 = $model->editorder();
		$orderid             = $session->get('JT_orderid');

		if ($res)
		{
			$payment_plg       = $session->get('payment_plg');
			$itemid            = $input->get('Itemid', 0);
			$message           = "";
			$data['msg']       = JText::_('COM_JTICKETING_ORDER_UPDATED_SUCCESS');
			$data['success']   = 1;
			$data['order_id']  = $orderid;
			$data['orderHTML'] = $this->getorderHTML($orderid);
		}
		else
		{
			$data['msg']      = JText::_('COM_JTICKETING_ORDER_UPDATED_FAILED');
			$data['success']  = 0;
			$data['order_id'] = $orderid;
		}

		echo json_encode($data);
		jexit();
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
		$redirect_url = JRoute::_('index.php?option=com_jticketing&view=buy');
		$input        = JFactory::getApplication()->input;
		$post         = $input->post;
		$id           = $input->get('cid');
		$session      = JFactory::getSession();
		$model        = $this->getModel('buy');
		$session      = JFactory::getSession();
		$eventid      = $session->get('JT_eventid');
		$com_params   = JComponentHelper::getParams('com_jticketing');
		$res          = $model->store();

		$checkout_mehtod_buy = $com_params->get('checkout_mehtod_buy');

		if ($session->get('JT_orderid'))
		{
			$payment_plg = $session->get('payment_plg');
			$itemid      = $input->get('Itemid', 0);
			$orderid     = $session->get('JT_orderid');

			$data['success_msg'] = JText::_('COM_JTICKETING_ORDER_CREATED_SUCCESS');
			$data['success']     = 1;
			$data['order_id']    = $orderid;
			$data['orderHTML']   = $this->getorderHTML($orderid);
		}
		else
		{
			$data['success_msg'] = JText::_('COM_JTICKETING_ORDER_CREATED_FAILED');
			$data['success'] = 0;
			$data['redirect_uri'] = $redirect_url;

			echo json_encode($data);
			jexit();
		}

		echo json_encode($data);
		jexit();
	}

	/**
	 * Get getcoupon
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function getcoupon()
	{
		$user   = JFactory::getUser();
		$db     = JFactory::getDBO();
		$input  = JFactory::getApplication()->input;
		$c_code = $input->get('coupon_code');
		$count  = '';
		$model  = $this->getModel('buy');
		$count  = $model->getcoupon($c_code);

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
	 * Function chkmail
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function chkmail()
	{
		$jinput = JFactory::getApplication()->input;
		$email  = $jinput->get('email', '', 'STRING');
		$model  = $this->getModel('buy');
		$status = $model->checkuserExistJoomla($email);
		$e[]    = $status;

		if ($status == 1)
		{
			$e[] = JText::_('COM_JTICKETING_MAIL_EXISTS');
		}

		echo json_encode($e);
		jexit();
	}

	/**
	 * Function login_validate
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function login_validate()
	{
		$input   = JFactory::getApplication()->input;
		$eventid = $input->get('eventid', '', 'STRING');
		$app     = JFactory::getApplication();
		$user    = JFactory::getUser();

		$redirect_url = JRoute::_('index.php?option=com_jticketing&view=buy&layout=checkout');

		$json = array();

		if ($user->id)
		{
			$json['redirect'] = $redirect_url;
		}

		if (!$json)
		{
			$userpath = JPATH_SITE . DS . 'components' . DS . 'com_jticketing' . DS . 'helpers' . DS . 'user.php';

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

		$json['redirect'] = $redirect_url;
		echo json_encode($json);
		$app->close();
	}

	/**
	 * Function CreateOrder_step_selectTicket
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function CreateOrder_step_selectTicket()
	{
		$session = JFactory::getSession();
		$input   = JFactory::getApplication()->input;
		$data    = $input->post;
		$model   = $this->getModel('buy');
		$res     = $model->createOrder('step_selectTicket');
	}

	/**
	 * Function save_step_selectTicket
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function save_step_selectTicket()
	{
		$session = JFactory::getSession();
		$model   = $this->getModel('buy');
		$res     = $model->createOrder('', 'step_selectTicket');
	}

	/**
	 * Function save_step_selectAttendee
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function save_step_selectAttendee()
	{
		$session = JFactory::getSession();
		$model   = $this->getModel('buy');
		$res     = $model->createOrder('', 'save_step_selectAttendee');
	}

	/**
	 * Function save_step_billinginfo
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function save_step_billinginfo()
	{
		$session = JFactory::getSession();
		$model   = $this->getModel('buy');
		$res     = $model->createOrder('', 'save_step_billinginfo');
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

	/**
	 * Call from Ajax
	 *
	 * @return  void
	 *
	 * @since   1.7
	 */
	public function getUpdatedBillingInfo()
	{
		$model = $this->getModel('buy');
		$res   = $model->getUpdatedBillingInfo();
	}

	/**
	 * Get checkGeustForOnlineEvent
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function checkGeustForOnlineEvent()
	{
		$redirect = JRoute::_('index.php?option=com_jticketing&view=buy&layout=default_online', false);
		$this->setRedirect($redirect, $msg);
	}

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
}
