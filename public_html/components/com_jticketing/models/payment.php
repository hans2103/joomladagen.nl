<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die(';)');
jimport('joomla.application.component.model');
jimport('joomla.database.table.user');
jimport('techjoomla.tjnotifications.tjnotifications');
require_once JPATH_ADMINISTRATOR . '/components/com_tjvendors/helpers/tjvendors.php';

/**
 * Model for post processing payment
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */

class JticketingModelpayment extends JModelLegacy
{
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();

		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$jTOrderHelper = JPATH_ROOT . '/components/com_jticketing/helpers/order.php';

		if (!class_exists('JticketingOrdersHelper'))
		{
			JLoader::register('JticketingOrdersHelper', $jTOrderHelper);
			JLoader::load('JticketingOrdersHelper');
		}

		$this->TjGeoHelper = new TjGeoHelper;
		$this->jTOrderHelper = new JticketingOrdersHelper;
		$this->_db         = JFactory::getDBO();

		// Load jlike main helper to call api function for assigndetails and other
		$path = JPATH_SITE . '/components/com_jlike/helpers/main.php';
		$this->ComjlikeMainHelper = "";

		if (JFile::exists($path))
		{
			if (!class_exists('ComjlikeMainHelper'))
			{
				JLoader::register('ComjlikeMainHelper', $path);
				JLoader::load('ComjlikeMainHelper');
			}

			$this->ComjlikeMainHelper = new ComjlikeMainHelper;
		}

		// Load jlike model to call api function for assigndetails and other
		$path = JPATH_SITE . '/components/com_jlike/models/recommendations.php';
		$this->JlikeModelRecommendations = "";

		if (JFile::exists($path))
		{
			if (!class_exists('JlikeModelRecommendations'))
			{
				JLoader::register('JlikeModelRecommendations', $path);
				JLoader::load('JlikeModelRecommendations');
			}

			$this->JlikeModelRecommendations = new JlikeModelRecommendations;
		}

		// Load jlike admin model content form to call api to get content id
		$path = JPATH_SITE . '/administrator/components/com_jlike/models/contentform.php';

		$this->JlikeModelContentForm = "";

		if (JFile::exists($path))
		{
			if (!class_exists('JlikeModelContentForm'))
			{
				JLoader::register('JlikeModelContentForm', $path);
				JLoader::load('JlikeModelContentForm');
			}

			$this->JlikeModelContentForm = new JlikeModelContentForm;
		}
	}

	/**
	 * Gives payment html from plugin
	 *
	 * @param   string   $pg_plugin  name of plugin like paypal
	 * @param   integer  $oid        id of jticketing_order
	 *
	 * @return  html  payment html
	 *
	 * @since   1.0
	 */
	public function confirmpayment($pg_plugin, $oid)
	{
		$post = JRequest::get('post');
		$vars = $this->getPaymentVars($pg_plugin, $oid);

		if (!empty($post) && !empty($vars))
		{
			if (!empty($result))
			{
				$vars = $result[0];
			}

			JPluginHelper::importPlugin('payment', $pg_plugin);
			$dispatcher = JDispatcher::getInstance();

			if (isset($vars->is_recurring) and $vars->is_recurring == 1)
			{
				$result = $dispatcher->trigger('onTP_ProcessSubmitRecurring', array($post, $vars));
			}
			else
			{
				$result = $dispatcher->trigger('onTP_ProcessSubmit', array($post, $vars));
			}
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_('SOME_ERROR_OCCURRED'), 'error');
		}
	}

	/**
	 * Gives vars to be used in plugin by parsing them in plugin structure
	 *
	 * @param   string   $pg_plugin  name of plugin like paypal
	 * @param   integer  $orderid    id of jticketing_order
	 *
	 * @return  html  payment html
	 *
	 * @since   1.0
	 */
	public function getPaymentVars($pg_plugin, $orderid)
	{
		$db                   = JFactory::getDBO();
		$jticketingmainhelper = new jticketingmainhelper;
		$params               = JComponentHelper::getParams('com_jticketing');
		$siteadmin_comm_per   = $params->get('siteadmin_comm_per');
		$gateways             = $params->get('gateways');
		$handle_transactions  = $params->get('handle_transactions');
		$session              = JFactory::getSession();
		$orderItemid          = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=orders');
		$chkoutItemid         = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=order');

		// Append prefix and order_id
		$pass_data      = $this->getdetails($orderid);
		$vars           = new stdClass;
		$vars->order_id = $pass_data->order_id;
		$vars->user_id  = $pass_data->user_id;

		if (isset($pass_data->firstname))
		{
			$vars->user_firstname = $pass_data->firstname;
		}

		if (isset($pass_data->lastname))
		{
			$vars->user_lastname = $pass_data->lastname;
		}

		if (isset($pass_data->address))
		{
			$vars->user_address = $pass_data->address;
		}

		if (isset($pass_data->user_email))
		{
			$vars->user_email = $pass_data->user_email;
		}

		if (isset($pass_data->user_city))
		{
			$vars->user_city = $pass_data->user_city;
		}

		if (isset($pass_data->user_zip))
		{
			$vars->user_zip = $pass_data->user_zip;
		}

		if (isset($pass_data->phone))
		{
			$vars->phone = $pass_data->phone;
		}

		$guest_email = '';

		if (!$pass_data->user_id && $params->get('allow_buy_guest'))
		{
			$guest_email = "&email=" . md5($pass_data->user_email);
		}

		$vars->item_name = $pass_data->order_item_name;
		$submiturl       = "index.php?option=com_jticketing&task=payment.confirmpayment&processor={$pg_plugin}";
		$vars->submiturl = JRoute::_($submiturl, false);
		$return_url      = "index.php?option=com_jticketing&view=orders&layout=order";
		$return_url .= $guest_email . "&orderid=" . $pass_data->order_id . "&processor={$pg_plugin}&Itemid=" . $orderItemid;
		$vars->return        = JURI::root() . substr(JRoute::_($return_url, false), strlen(JURI::base(true)) + 1);
		$cancel_return       = "index.php?option=com_jticketing&view=order&layout=cancel&processor={$pg_plugin}&Itemid=" . $chkoutItemid;
		$vars->cancel_return = JURI::root() . substr(JRoute::_($cancel_return, false), strlen(JURI::base(true)) + 1);
		$url                 = JURI::root() . "index.php?option=com_jticketing&task=payment.processpayment" . $guest_email;
		$url .= "&processor=" . $pg_plugin . "&order_id=" . $pass_data->order_id;
		$vars->url           = JRoute::_($url, false);
		$vars->notify_url = JURI::root() . "index.php?option=com_jticketing&task=payment.notify&order_id="
. $pass_data->order_id . $guest_email . "&processor=" . $pg_plugin;

		$vars->currency_code = $pass_data->currency;
		$vars->comment       = $pass_data->customer_note;
		$vars->amount        = $pass_data->order_amt;

		if ($pass_data->fee > 0)
		{
			$session->set("JT_fee", $pass_data->fee);
		}

		$res     = new stdClass;
		$res->id = $orderid;

		if ($pass_data->processor == 'paypal' and $handle_transactions == 1)
		{
			$vars->business = $this->getEventownerEmail($orderid);
			$res->fee       = 0;
		}
		else
		{
			$res->fee = $session->get("JT_fee");
		}

		if (!$db->updateObject('#__jticketing_order', $res, 'id'))
		{
		}

		$vars->userInfo = $this->userInfo($orderid);

		if (isset($vars->userInfo->country))
		{
			$vars->country_code = $vars->userInfo->country;
		}

		if (isset($vars->userInfo->state))
		{
			$vars->state_code = $vars->userInfo->state;
		}

		// For Adpative payment
		$jticketingModelpayment     = new jticketingModelpayment;
		$vars->adaptiveReceiverList = $jticketingModelpayment->getReceiverList($vars, $pg_plugin, $orderid);

		// Get event owner: For stripe
		$eventid     = $jticketingmainhelper->getEventID_from_OrderID($orderid);
		$vars->owner = $jticketingmainhelper->getEventCreator($eventid);
		$vars->bootstrapVersion = $params->get("currentBSViews");

		// Get commision amount
		$vars->commision = $jticketingmainhelper->getTransactionFee($orderid);
		$vars->client    = "jticketing";

		return $vars;
	}

	/**
	 * Get billing details
	 *
	 * @param   integer  $orderid  id of jticketing_order table
	 *
	 * @return  array  $billDetails  billing details
	 *
	 * @since   1.0
	 */
	public function userInfo($orderid)
	{
		$user  = JFactory::getUser();
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select(
		$db->quoteName(
			array('user_id',
			'user_email', 'firstname', 'lastname', 'country_code',
			'state_code', 'address', 'city', 'phone', 'zipcode')
			)
		);
		$query->from($db->quoteName('#__jticketing_users'));
		$query->where($db->quoteName('order_id') . " = " . $db->quote($orderid));
		$db->setQuery($query);
		$billDetails = $db->loadAssoc();

		// Make address in 2 lines
		if (isset($billDetails['country_code']))
		{
			$billDetails['country_code'] = $this->TjGeoHelper->getCountryNameFromId($billDetails['country_code']);
		}

		if (isset($billDetails['state_code']))
		{
			$billDetails['state_code'] = $this->TjGeoHelper->getRegionNameFromId($billDetails['state_code']);
		}

		$billDetails['add_line2'] = '';
		$remove_character         = array(
			"\n",
			"\r\n",
			"\r"
		);

		$billDetails['add_line1'] = str_replace($remove_character, ' ', $billDetails['address']);

		return $billDetails;
	}

	/**
	 * Changes gateway html when clicked on checkout
	 *
	 * @return  array  $billDetails  biling infor of the payee
	 *
	 * @since   1.0
	 */
	public function changegateway()
	{
		JLoader::import('payment', JPATH_SITE . DS . 'components' . DS . 'com_jticketing' . DS . 'models');
		$db              = JFactory::getDBO();
		$jinput          = JFactory::getApplication()->input;
		$model           = new jticketingModelpayment;
		$selectedGateway = $jinput->get('gateways', '');
		$order_id        = $jinput->get('order_id', '');
		$return          = '';

		if (!empty($selectedGateway) && !empty($order_id))
		{
			$model->updateOrderGateway($selectedGateway, $order_id);
			$payhtml = $model->getHTML($order_id);
		}

		echo $payhtml[0];
		jexit();
	}

	/**
	 * Get payment gateway html from plugin
	 *
	 * @param   integer  $order_id  id of jticketing_order table
	 *
	 * @return  html
	 *
	 * @since   1.0
	 */
	public function getHTML($order_id)
	{
		$pass_data = $this->getdetails($order_id);
		$vars      = $this->getPaymentVars($pass_data->processor, $order_id);
		JPluginHelper::importPlugin('payment', $pass_data->processor);
		$dispatcher = JDispatcher::getInstance();
		$html       = $dispatcher->trigger('onTP_GetHTML', array($vars));

		return $html;
	}

	/**
	 * Get payment gateway html from plugin
	 *
	 * @param   integer  $pg_plugin  id of jticketing_order table
	 *
	 * @param   integer  $order_id   id of jticketing_order table
	 *
	 * @param   integer  $order      id of jticketing_order table
	 *
	 * @return  html
	 *
	 * @since   1.0
	 */
	public function getHTMLS($pg_plugin, $order_id, $order)
	{
		$pass_data = $this->getdetails($order_id);
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update("#__jticketing_order AS JTO");
		$query->set("JTO.processor = '" . $pg_plugin . "', JTO.status = 'P'");
		$query->where("JTO.order_id= '" . $order . "'");
		$db->setQuery($query);
		$result = $db->execute();

		$vars      = $this->getPaymentVars($pass_data->processor, $order_id);
		JPluginHelper::importPlugin('payment', $pg_plugin);
		$dispatcher = JDispatcher::getInstance();
		$html       = $dispatcher->trigger('onTP_GetHTML', array($vars));

		return $html;
	}

	/**
	 * Get all order details based on id
	 *
	 * @param   integer  $tid  id of jticketing_order table
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getdetails($tid)
	{
		$params = JComponentHelper::getParams('com_jticketing');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('firstname','user_email','phone','user_id')));
		$query->from($db->quoteName('#__jticketing_users'));
		$query->where($db->quoteName('order_id') . " = " . $db->quote($tid));
		$query->where($db->quoteName('address_type') . " = 'BT'");
		$db->setQuery($query);
		$orderdetails = $db->loadObjectlist();

		$query1 = $db->getQuery(true);
		$query1->select($db->quoteName(array('fee','amount','customer_note','processor','order_id')));
		$query1->from($db->quoteName('#__jticketing_order'));
		$query1->where($db->quoteName('id') . " = " . $db->quote($tid));
		$db->setQuery($query1);
		$orderamt = $db->loadObjectlist();
		$orderdetails['0']->order_id = $orderamt[0]->order_id;

		$query2 = $db->getQuery(true);
		$query2->select($db->quoteName(array('i.type_id', 't.title', 't.price'), array(null, 'order_item_name', null)));
		$query2->select('sum(' . $db->quoteName('i.ticketcount') . ') as ticketcount');
		$query2->from($db->quoteName('#__jticketing_order_items', 'i'));
		$query2->join('LEFT', $db->quoteName('#__jticketing_types', 't') . ' ON (' . $db->quoteName('t.id') . ' = ' . $db->quoteName('i.type_id') . ')');
		$query2->where($db->quoteName('i.order_id') . " = " . $db->quote($tid));
		$query2->group($db->quoteName('i.type_id'));

		$db->setQuery($query2);
		$orderlist['items'] = $db->loadObjectlist();
		$itemarr            = array();

		foreach ($orderlist['items'] as $item)
		{
			$itemarr[] = $item->order_item_name;
		}

		if ($itemarr[0])
		{
			$itemstring = implode('\n', $itemarr);
		}

		$orderdetails['0']->order_item_name = $itemstring;
		$orderdetails['0']->processor       = $orderamt[0]->processor;
		$orderdetails['0']->order_amt       = $orderamt[0]->amount;
		$orderdetails['0']->fee             = $orderamt[0]->fee;
		$orderdetails['0']->currency        = $params->get('currency');
		$orderdetails['0']->customer_note   = preg_replace('/\<br(\s*)?\/?\>/i', " ", $orderamt[0]->customer_note);

		return $orderdetails['0'];
	}

	/**
	 * Post Processing for order
	 *
	 * @param   string  $post       post data
	 * @param   string  $pg_plugin  payment gateway name
	 * @param   int     $order_id   id of jticketing_order table
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function processpayment($post, $pg_plugin, $order_id)
	{
		// Trigger Before process Paymemt
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$result      = $dispatcher->trigger('jt_OnBeforeProcessPayment', array($post, $order_id, $pg_plugin));
		$session     = JFactory::getSession();
		$com_params  = JComponentHelper::getParams('com_jticketing');
		$currency    = $com_params->get('currency');
		$jinput      = JFactory::getApplication()->input;
		$id          = $order_id;
		$return_resp = array();

		// Authorise Post Data
		if (!empty($post['plugin_payment_method']) && $post['plugin_payment_method'] == 'onsite')
		{
			$plugin_payment_method = $post['plugin_payment_method'];
		}

		$dispatcher     = JDispatcher::getInstance();
		$post['client'] = 'jticketing';
		JPluginHelper::importPlugin('payment', $pg_plugin);
		$vars = $this->getPaymentVars($pg_plugin, $order_id);

		try
		{
			$data = $dispatcher->trigger('onTP_Processpayment', array($post, $vars));
		}
		catch (Exception $e)
		{
			http_response_code(400);
			echo new JResponseJson($e);
		}

		$data = $data[0];

		if ($data)
		{
			try
			{
				// $res  = @$this->storelog($pg_plugin, $data);

				$jticketingmainhelper = new jticketingmainhelper;
				$orderItemid          = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=orders');
				$chkoutItemid         = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=order');

				// Get order id
				if (empty($order_id))
				{
					// Here we get order_id in Format JT_JKJKJK_0012
					$order_id = $data['order_id'];
				}

				// Get order_id in format 12
				$id = $jticketingmainhelper->getIDFromOrderID($order_id);

				// Start for guest checkout
				$query = "SELECT ou.user_id,ou.user_email
				FROM #__jticketing_users as ou
				WHERE ou.address_type='BT' AND ou.order_id=" . $id;
				$this->_db->setQuery($query);
				$user_detail = $this->_db->loadObject();
				$params      = JComponentHelper::getParams('com_jticketing');
				$guest_email = "";

				if (!$user_detail->user_id && $params->get('allow_buy_guest'))
				{
					$guest_email = "&email=" . md5($user_detail->user_email);
				}

				$data['processor'] = $pg_plugin;
				$data['status']    = trim($data['status']);
				$query             = "SELECT o.amount
						FROM #__jticketing_order  as o
						where o.id=" . $id;
				$this->_db->setQuery($query);
				$order_amount          = $this->_db->loadResult();
				$return_resp['status'] = '0';

				if ($order_amount == 0)
				{
					$data['order_id']       = $id;
					$data['total_paid_amt'] = 0;
					$data['processor']      = $pg_plugin;
					$data['status']         = 'C';
				}

				if (($data['status'] == 'C' && $order_amount == $data['total_paid_amt']) or ($data['status'] == 'C' && $order_amount == 0))
				{
					$data['status']        = 'C';
					$return_resp['status'] = '1';
				}
				elseif ($order_amount != $data['total_paid_amt'] and $data['processor'] != 'adaptive_paypal')
				{
					$data['status']        = 'E';
					$return_resp['status'] = '0';
				}
				elseif (empty($data['status']))
				{
					$data['status']        = 'P';
					$return_resp['status'] = '0';
				}

				if ($data['status'] != 'C' && !empty($data['error']))
				{
					$return_resp['msg'] = $data['error']['code'] . " " . $data['error']['desc'];
				}

				$this->updateOrder($id, $user_detail->user_id, $data, $return_resp);

				// Clear order session
				$session->set('JT_orderid', '');
				$session->set('JT_fee', '');
				$return = "index.php?option=com_jticketing&view=orders&layout=order";
				$return .= $guest_email . "&orderid=" . ($order_id) . "&processor={$pg_plugin}&Itemid=" . $orderItemid;
				$return_resp['return'] = JUri::root() . substr(JRoute::_($return, false), strlen(JUri::base(true)) + 1);

				// Trigger After Process Payment
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('system');
				$result = $dispatcher->trigger('jt_OnAfterProcessPayment', array($data, $order_id, $pg_plugin));
			}
			catch (Exception $e)
			{
				throw new Exception($e->getMessage());
			}
		}
		else
		{
			$return_resp['msg'] = JText::_('COM_JTICKETING_ORDER_ERROR');
		}

		return $return_resp;
	}

	/**
	 * Check if order is already processed
	 *
	 * @param   string  $transaction_id  transaction_id for order
	 * @param   string  $order_id        id of jticketing_order
	 *
	 * @return  int  1 or 0
	 *
	 * @since   1.0
	 */
	public function Dataprocessed($transaction_id, $order_id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT event_details_id
				FROM #__jticketing_order where id=" . $order_id . " AND transaction_id='" . $transaction_id . "' AND status='C'";
		$db->setQuery($query);
		$eventdata = $db->loadResult();

		if (!empty($eventdata))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Update order its status,seats and other data
	 *
	 * @param   string  $id           id for jticketing_order
	 * @param   string  $userid       userid of payee
	 * @param   array   $data         data of jticketing_order
	 * @param   string  $return_resp  return_resp
	 *
	 * @return  int  1 or 0
	 *
	 * @since   1.0
	 */
	public function updateOrder($id, $userid, $data, $return_resp)
	{
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		$processed = 0;

		// Imp Check if Data is processed and Status is already Completed
		$processed = $this->Dataprocessed($data['transaction_id'], $id);

		if ($processed == 1)
		{
			$return_resp['status'] = '1';

			return $return_resp;
		}

		if ($data['status'] == 'C' and $processed != 1)
		{
			$this->updateOrderEvent($id, $data, $userid);
			$return_resp['status'] = '1';
		}
		elseif (!empty($data['status']))
		{
			$this->updateStatus($data);
		}

		$com_params           = JComponentHelper::getParams('com_jticketing');
		$email_options        = $com_params->get('email_options', '');

		if (in_array('order_email', $email_options) and $data['status'] != 'C')
		{
			JticketingMailHelper::sendInvoiceEmail($id);
		}

		return $return_resp;
	}

	/**
	 * Update order and send invoice and add payout entry
	 *
	 * @param   string  $id         id for jticketing_order
	 * @param   string  $data       userid of payee
	 * @param   string  $member_id  payee id
	 *
	 * @return  int  1 or 0
	 *
	 * @since   1.0
	 */
	public function updateOrderEvent($id, $data, $member_id)
	{
		$com_params           = JComponentHelper::getParams('com_jticketing');
		$socialintegration    = $com_params->get('integrate_with', 'none');
		$streamBuyTicket      = $com_params->get('streamBuyTicket', 0);
		$email_options        = $com_params->get('email_options', '');
		$handle_transactions        = $com_params->get('handle_transactions', 0);
		$user                 = JFactory::getUser();
		$jticketingmainhelper = new jticketingmainhelper;
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$jteventHelper        = new jteventHelper;
		$orderinfo   = $jticketingmainhelper->getorderinfo($id);

		// Add Payout Entry
		$this->jTOrderHelper->addEntry($id, $data['status']);

		if ($socialintegration != 'none')
		{
			// Add in activity.
			if ($streamBuyTicket == 1 and !empty($user->id))
			{
				$libclass    = $jteventHelper->getJticketSocialLibObj();
				$action      = 'streamBuyTicket';
				$eventLink   = '<a class="" href="' . $orderinfo['eventinfo']->event_url . '">' . $orderinfo['eventinfo']->summary . '</a>';
				$originalMsg = JText::sprintf('COM_JTICKETING_PURCHASED_TICKET', $eventLink);
				$libclass->pushActivity($user->id, $act_type = '', $act_subtype = '', $originalMsg, $act_link = '', $title = '', $act_access = 0);
			}
		}

		$this->updatesales($data, $id);
		$member_id   = $this->getEventMemberid($id, $data['status']);
		$eventupdate = $this->eventupdate($id, $member_id);

		// Send Ticket Email.
		if (in_array('ticket_email', $email_options))
		{
			if (!$orderinfo['eventinfo']->online_events)
			{
				$email = JticketingMailHelper::sendmailnotify($id, 'afterordermail');
			}
		}

		// Send Invoice Email.
		if (in_array('order_email', $email_options))
		{
			JticketingMailHelper::sendInvoiceEmail($id);
		}

		// Add entries to reminder queue to send reminder for Event
		$reminderData             = $orderinfo['eventinfo'];
		$jticketingModelpayment     = new jticketingModelpayment;
		$eventupdate                = $jticketingModelpayment->addtoReminderQueue($reminderData, $orderinfo['order_info'][0]->user_id);

		// Add entries to JLike TODO table to send reminder for Event
		$integration = $com_params->get('integration');
		$eventType = $orderinfo['eventinfo']->online_events;

		if ($integration == 2 && $eventType == 1)
		{
			$meeting_url = json_decode($orderinfo['eventinfo']->jt_params);
			$venueDetails               = $jticketingfrontendhelper->getVenue($orderinfo['eventinfo']->venue);
			$orderDetails               = $jticketingmainhelper->getOrderDetail($order_id);
			$randomPassword             = $jticketingmainhelper->rand_str(8);
			$venueParams                = json_decode($venueDetails->params);
			$venueParams->user_id  = $orderDetails->user_id;
			$venueParams->name     = $orderDetails->name;
			$venueParams->email    = $orderDetails->email;
			$venueParams->password = $randomPassword;
			$venueParams->meeting_url = json_decode($meeting_url->event_url);

			if ($eventType == '1')
			{
				// TRIGGER After create event
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('tjevents');
				$result = $dispatcher->trigger('tj_inviteUsers', array($venueParams));

				if (in_array('ticket_email', $email_options))
				{
					$email  = JticketingMailHelper::onlineEventNotify($id, $venueParams, $order['eventinfo']);
				}
			}
		}

		// Add payout entry
		$payout_id = $this->addPayoutEntry($id, $data['transaction_id'], $data['status'], $data['processor']);
	}

	/**
	 * Get payout ID and status from payout table
	 *
	 * @param   string  $transactionID  id for jticketing_order
	 * @param   string  $userid         userid of payee
	 *
	 * @return  array  payout array
	 *
	 * @since   1.0
	 */
	public function getPayoutId($transactionID, $userid)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT `id`,`status`
		FROM `#__jticketing_ticket_payouts`
		WHERE `transction_id`='" . $transactionID . "' AND `user_id`=" . $userid;
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	/**
	 * Update payout entry
	 *
	 * @param   string  $order_id   id for jticketing_order
	 * @param   string  $txnid      txnid
	 * @param   array   $status     status
	 * @param   string  $pg_plugin  name of plugin
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addPayoutEntry($order_id, $txnid, $status, $pg_plugin)
	{
		$plugin           = JPluginHelper::getPlugin('payment', $pg_plugin);
		$params              = JComponentHelper::getParams('com_jticketing');
		$handle_transactions = $params->get('handle_transactions');

		if ($pg_plugin == 'adaptive_paypal' || ($pg_plugin == 'paypal' && $handle_transactions == 1))
		{
			// Lets set the paypal email if admin is not handling transactions
			$jticketingmainhelper     = new jticketingmainhelper;
			$adaptiveDetails          = $jticketingmainhelper->getorderEventInfo($order_id);

			foreach ($adaptiveDetails as $userReport)
			{
				$com_params = JComponentHelper::getParams('com_jticketing');
				$currency = $com_params->get('currency');
				$vendor_id = $this->vendorCheck($userReport['owner']);
				$newPayoutData = new stdClass;
				$newPayoutData->debit = $userReport['commissonCutPrice'];
				$payableAmount = TjvendorsHelpersTjvendors::getTotalAmount($vendor_id, $currency, 'com_jticketing');
				$newPayoutData->total = $payableAmount['total'] - $newPayoutData->debit;
				$newPayoutData->transaction_time = JFactory::getDate()->toSql();
				$newPayoutData->client = 'com_jticketing';
				$newPayoutData->currency = $currency;
				$transactionClient = "Jticketing";
				$newPayoutData->transaction_id = $transactionClient . '-' . $currency . '-' . $vendor_id . '-';
				$newPayoutData->id = '';
				$newPayoutData->vendor_id = $vendor_id;
				$newPayoutData->status = 1;
				$newPayoutData->credit = '0.00';

				if ($pg_plugin == 'paypal')
				{
					$customerNote = "Payment done directly to the vendor";
				}
				else
				{
					$customerNote = "Payment handled by adaptive paypal";
				}

				$params = array("customer_note" => $customerNote, "entry_status" => "debit_payout");
				$newPayoutData->params = json_encode($params);

				// Insert the object into the user profile table.
				$result = JFactory::getDbo()->insertObject('#__tjvendors_passbook', $newPayoutData);

				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('max(' . $db->quotename('id') . ')');
				$query->from($db->quoteName('#__tjvendors_passbook'));
				$db->setQuery($query);
				$payout_id = $db->loadResult();

				$payout_update = new stdClass;

				// Must be a valid primary key value.
				$payout_update->id = $payout_id;
				$payout_update->transaction_id = $newPayoutData->transaction_id . $payout_update->id;

				// Update their details in the users table using id as the primary key.
				$result = JFactory::getDbo()->updateObject('#__tjvendors_passbook', $payout_update, 'id');
			}
		}
	}

	/**
	 * check if user is a vendor
	 *
	 * @param   integer  $user_id  order's id
	 *
	 * @return  mixed
	 *
	 * @since   2.0
	 */
	public function vendorCheck($user_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('vendor_id'));
		$query->from($db->quoteName('#__tjvendors_vendors'));
		$query->where($db->quoteName('user_id') . ' = ' . $user_id);
		$db->setQuery($query);
		$vendor = $db->loadResult();

		if (!$vendor)
		{
			return false;
		}
		else
		{
			return $vendor;
		}
	}

	/**
	 * Get userid of payee from order
	 *
	 * @param   string  $order_id      order_id in jticketing_order
	 * @param   string  $order_status  order_status like C and P
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventMemberid($order_id, $order_status)
	{
		$db = JFactory::getDBO();

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
	 * Store login data
	 *
	 * @param   string  $name  name of plugin
	 * @param   string  $data  data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function storelog($name, $data)
	{
		$data1              = array();
		$data1['raw_data']  = $data['raw_data'];
		$data1['JT_CLIENT'] = "com_jticketing";
		$dispatcher         = JDispatcher::getInstance();
		JPluginHelper::importPlugin('payment', $name);
		$data = $dispatcher->trigger('onTP_Storelog', array($data1));
	}

	/**
	 * Update status
	 *
	 * @param   string  $data  data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updateStatus($data)
	{
		$db                  = JFactory::getDBO();
		$res                 = new stdClass;
		$res->id             = $data['order_id'];
		$res->mdate          = date("Y-m-d H:i:s");
		$res->transaction_id = $data['transaction_id'];
		$res->status         = $data['status'];
		$res->extra          = json_encode($data['raw_data']);

		if (!$db->updateObject('#__jticketing_order', $res, 'id'))
		{
		}

		if ($res->status == 'C')
		{
			$query = "SELECT type_id,count(ticketcount) as ticketcounts
					FROM #__jticketing_order_items where order_id=" . $data['order_id'] . " GROUP BY type_id";
			$db->setQuery($query);
			$orderdetails = $db->loadObjectlist();

			foreach ($orderdetails as $orderdetail)
			{
				$typedata = '';
				$restype  = new stdClass;
				$query    = "SELECT count
					FROM #__jticketing_types where id=" . $orderdetail->type_id;
				$db->setQuery($query);
				$typedata       = $db->loadResult();
				$restype->id    = $orderdetail->type_id;
				$restype->count = $typedata + $orderdetail->ticketcounts;
				$db->updateObject('#__jticketing_types', $restype, 'id');
			}
		}
	}

	/**
	 * Update sales data
	 *
	 * @param   array  $data  data
	 * @param   int    $id    order id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updatesales($data, $id)
	{
		$db                  = JFactory::getDBO();
		$res                 = new stdClass;
		$res->id             = $id;
		$res->mdate          = date("Y-m-d H:i:s");
		$res->transaction_id = $data['transaction_id'];
		$res->payee_id       = $data['buyer_email'];
		$res->status         = $data['status'];
		$res->processor      = $data['processor'];
		$res->extra          = json_encode($data['raw_data']);

		if (!$db->updateObject('#__jticketing_order', $res, 'id'))
		{
			return false;
		}

		$query = "SELECT type_id,count(ticketcount) as ticketcounts
				FROM #__jticketing_order_items where order_id=" . $id . " GROUP BY type_id";
		$db->setQuery($query);
		$orderdetails = $db->loadObjectlist();

		foreach ($orderdetails as $orderdetail)
		{
			$typedata = '';
			$restype  = new stdClass;
			$query    = "SELECT count
				FROM #__jticketing_types where id=" . $orderdetail->type_id;
			$db->setQuery($query);
			$typedata       = $db->loadResult();
			$restype->id    = $orderdetail->type_id;
			$restype->count = $typedata - $orderdetail->ticketcounts;
			$db->updateObject('#__jticketing_types', $restype, 'id');
		}
	}

	/**
	 * Update event data for jomsocial and other integration for event members
	 *
	 * @param   array  $order_id   id of jticketing_order table
	 * @param   int    $member_id  user id of payee
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function eventupdate($order_id, $member_id)
	{
		$com_params             = JComponentHelper::getParams('com_jticketing');
		$integration            = $com_params->get('integration');
		$siteadmin_comm_per     = $com_params->get('siteadmin_comm_per');
		$guest_reg_id           = $com_params->get('guest_reg_id');
		$auto_fix_seats         = $com_params->get('auto_fix_seats');
		$currency               = $com_params->get('currency');
		$affect_js_native_seats = $com_params->get('affect_js_native_seats');
		$jticketingmainhelper   = new jticketingmainhelper;

		if ($affect_js_native_seats != '1')
		{
			return;
		}

		$integration = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$db    = JFactory::getDBO();
			$user  = JFactory::getUser();
			$query = "SELECT event_details_id,amount,ticketscount
		   		        FROM #__jticketing_order where id=" . $order_id . " AND status='C'";
			$db->setQuery($query);
			$eventdata = $db->loadObject();
			$query     = "SELECT eventid
		   		        FROM #__jticketing_integration_xref where id=" . $eventdata->event_details_id . "  AND source='com_community'";
			$db->setQuery($query);
			$eventid = $db->loadResult();
			$qry     = "SELECT confirmedcount FROM #__community_events
					WHERE id=" . $eventid;
			$db->setQuery($qry);
			$cnt                 = $db->loadResult();
			$arr                 = new stdClass;
			$arr->id             = $eventid;
			$arr->confirmedcount = $cnt + $eventdata->ticketscount;
			$db->updateObject('#__community_events', $arr, 'id');

			// Added for bug fix for bug  #12043
			$qry = "SELECT id FROM #__community_events_members
					  WHERE eventid=" . $eventdata->event_details_id . "
					  AND memberid=" . $member_id . "
					  AND status=0";
			$db->setQuery($qry);

			$invited_row_id = $db->loadResult();

			if ($invited_row_id)
			{
				$qry = "UPDATE `#__community_events_members` SET `status`=1
					WHERE `id`=" . $invited_row_id;
				$db->setQuery($qry);
				$db->execute($qry);
				$eventdata->ticketscount--;
			}

			if ($eventdata->ticketscount)
			{
				for ($i = 0; $i < $eventdata->ticketscount; $i++)
				{
					// Get site admin id
					if (!$member_id)
					{
						if (!empty($guest_reg_id))
						{
							$member_id = $guest_reg_id;
						}
					}

					if (!$member_id)
					{
						continue;
					}

					$qry = "SELECT id FROM #__community_events_members
					  WHERE eventid=" . $eventid . "
					 AND memberid=" . $member_id . "
					 AND status=1";
					$db->setQuery($qry);
					$member_already_present = $db->loadResult();

					if ($member_already_present)
					{
						continue;
					}

					$dat             = new stdClass;
					$dat->id         = '';
					$dat->eventid    = $eventid;
					$dat->memberid   = $member_id;
					$dat->status     = 1;
					$dat->permission = 3;
					$dat->invited_by = 0;
					$dat->approval   = 0;
					$dat->created    = date("Y-m-d H:i:s");

					if (!$db->insertObject('#__community_events_members', $dat, 'id'))
					{
						echo $db->stderr();
					}
				}
			}

			// For bug fix for bug  #12043
			if ($auto_fix_seats == '1')
			{
				$this->fixDB($eventid);
			}
		}

		return true;
	}

	/**
	 * Update event data for jomsocial and other integration for event members
	 *
	 * @param   array  $order_id  id of jticketing_order table
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventownerEmail($order_id)
	{
		$db = JFactory::getDBO();

		// Retrieve XrefID
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('event_details_id')));
		$query->from($db->quoteName('#__jticketing_order'));
		$query->where($db->quoteName('id') . " = " . $db->quote($order_id));
		$db->setQuery($query);
		$eventid = $db->loadResult();

		$query1 = $db->getQuery(true);
		$query1->select($db->quoteName(array('vendor_id')));
		$query1->from($db->quoteName('#__jticketing_integration_xref'));
		$query1->where($db->quoteName('id') . " = " . $db->quote($eventid));
		$db->setQuery($query1);
		$vendor_id = $db->loadResult();

		$query2 = $db->getQuery(true);
		$query2->select($db->quoteName(array('params')));
		$query2->from($db->quoteName('#__vendor_client_xref'));
		$query2->where($db->quoteName('vendor_id') . " = " . $db->quote($vendor_id));
		$db->setQuery($query2);
		$paypalemail = json_decode($db->loadResult());

		if (!empty($paypalemail))
		{
			return $paypalemail->payment_email_id;
		}
	}

	/**
	 * Get all event data for jomsocial based on creator
	 *
	 * @param   int  $creator  creator of event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventData($creator)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT events.id,events.creator, sum(ticket.amount) AS nprice, sum(ticket.fee) AS nfee
							  FROM #__community_events AS events
					          LEFT JOIN #__jticketing_events_xref AS eventdetails
							  ON events.id = eventdetails.eventid
							  LEFT JOIN #__jticketing_order AS ticket
							  ON eventdetails.eventid = ticket.event_details_id
							  WHERE ticket.status = 'C'
							  AND events.creator ='" . $creator . "' GROUP BY events.creator";
		$db->setQuery($query);
		$rows = $db->loadObject();

		return $rows;
	}

	/**
	 * Fix database member count values for jomsocial
	 *
	 * @param   int  $eventid  eventid
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function fixDB($eventid)
	{
		// Remove inconsitent entries from community event members table
		$db = JFactory::getDBO();

		// Load list of all user who bought tickets for event
		$qry = "SELECT id, user_id,ticketscount
		FROM `#__jticketing_order`
		WHERE event_details_id=" . $eventid;
		$db->setQuery($qry);
		$orders = $db->loadObjectList();

		// Calculate ticket count for all users who bought tickets
		$members = array();

		foreach ($orders as $o)
		{
			if (!array_key_exists($o->user_id, $members))
			{
				$members[$o->user_id]['ticketscount'] = $o->ticketscount;
			}
			else
			{
				$members[$o->user_id]['ticketscount'] = $members[$o->user_id]['ticketscount'] + $o->ticketscount;
			}
		}

		$todelete = array();

		foreach ($members as $key => $val)
		{
			// Get all event members of type mebmer(permission=3)
			$qry = "SELECT id,status,invited_by
			FROM `#__community_events_members`
			WHERE eventid=" . $eventid . " AND permission=3 AND memberid=" . $key;
			$db->setQuery($qry);
			$mb = $db->loadObjectList();

			// If count of a members attendance is greater than no. of tickets he bought
			if (count($mb) > $val['ticketscount'])
			{
				$cnt = 0;

				foreach ($mb as $m)
				{
					$cnt++;

					// If count of a members attendance is greater than no. of tickets he bought
					if ($cnt > $val['ticketscount'])
					{
						$todelete[] = $m->id;
					}
				}
			}
		}

		// Delete all rows calculated above
		if (isset($todelete))
		{
			if (count($todelete) > 0)
			{
				$todel = implode($todelete, ',');
				$qry   = "DELETE FROM `#__community_events_members`
					WHERE id IN(" . $todel . ")";
				$db->setQuery($qry);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Update selected gateway in database
	 *
	 * @param   string  $selectedGateway  gateway that is selected
	 * @param   int     $order_id         order_id of the order which is selected
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updateOrderGateway($selectedGateway, $order_id)
	{
		$db             = JFactory::getDBO();
		$row            = new stdClass;
		$row->id        = $order_id;
		$row->processor = $selectedGateway;

		if (!$this->_db->updateObject('#__jticketing_order', $row, 'id'))
		{
			echo $this->_db->stderr();

			return 0;
		}

		return 1;
	}

	/**
	 * Get data for adaptive payment
	 *
	 * @param   string  $vars       vars for adaptive payment
	 * @param   int     $pg_plugin  payment gateway name
	 * @param   int     $orderid    orderid of the order which is selected
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getReceiverList($vars, $pg_plugin, $orderid)
	{
		// GET BUSINESS EMAIL
		$plugin           = JPluginHelper::getPlugin('payment', $pg_plugin);
		$pluginParams     = json_decode($plugin->params);
		$businessPayEmial = "";

		if (property_exists($pluginParams, 'business'))
		{
			$businessPayEmial = trim($pluginParams->business);
		}
		else
		{
			return array();
		}

		$params              = JComponentHelper::getParams('com_jticketing');
		$handle_transactions = $params->get('handle_transactions', 0);

		if ($pg_plugin == 'adaptive_paypal')
		{
			// Lets set the paypal email if admin is not handling transactions
			$adaptiveDetails      = array();
			$jticketingmainhelper = new jticketingmainhelper;
			$adaptiveDetails      = $jticketingmainhelper->getorderEventInfo($orderid);

			$params = JComponentHelper::getParams('com_jticketing');

			/*
			if($handle_transactions==0){
			$commission_per = $params->get("siteadmin_comm_per",0);
			$adminCommisson_per =  (($vars->amount * $commission_per)/100 );
			$commission_flat = $params->get("siteadmin_comm_flat",0);
			$adminCommisson=$adminCommisson_per+$commission_flat;
			}*/

			// GET BUSINESS EMAIL
			$plugin           = JPluginHelper::getPlugin('payment', $pg_plugin);
			$pluginParams     = json_decode($plugin->params);
			$businessPayEmial = "";

			if (property_exists($pluginParams, 'business'))
			{
				$businessPayEmial = trim($pluginParams->business);
			}

			$receiverList                = array();
			$receiverList[0]             = array();
			$tamount                     = 0;
			$receiverList[0]['receiver'] = $businessPayEmial;
			$receiverList[0]['amount']   = $adaptiveDetails['0']['commission'];
			$receiverList[0]['primary']  = false;

			if (!empty($adaptiveDetails[$businessPayEmial]))
			{
				// Primary account
				unset($adaptiveDetails[$businessPayEmial]);
			}
			else
			{
				// $tamount = $tamount + $receiverList[0]['amount'];
			}

			// Add other receivers
			$index = 1;

			foreach ($adaptiveDetails as $detail)
			{
				$adaptiveEmail = json_decode($detail['paypal_detail']);
				$receiverList[$index]['receiver'] = $adaptiveEmail->payment_email_id;

				// Changed above 2 lines by sagar to make event owner as primary receiver
				$receiverList[$index]['amount']  = $vars->amount;
				$receiverList[$index]['primary'] = true;
				$index++;
			}

			return $receiverList;
		}
	}

	/**
	 * Get data for stripe payment
	 *
	 * @param   string  $data    data for stripe payment
	 * @param   int     $refund  refund 1 or 0
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function stripeAddPayout($data, $refund = 0)
	{
		$db             = JFactory::getDBO();
		$transaction_id = $data['data']['object']['charge'];

		if (!$transaction_id)
		{
			return;
		}

		// Get Event Owner ID
		$query = $db->getQuery(true);

		// Select campaign owner id
		$query->select($db->quoteName(array('e.userid', 'o.original_amount')));
		$query->from($db->quoteName('#__jticketing_integration_xref', 'e'));
		$query->join('INNER', $db->quoteName('#__jticketing_order', 'o') .
		' ON (' . $db->quoteName('e.id') . ' = ' . $db->quoteName('o.event_details_id') . ')');
		$query->where($db->quoteName('o.transaction_id') . ' = ' . "'" . $db->quote($transaction_id) . "'");

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		// Load the result
		$orderObject = $db->loadObject();

		// Get Payout ID
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id')));
		$query->from($db->quoteName('#__jticketing_ticket_payouts', 'p'));
		$query->where($db->quoteName('p.transction_id') . ' = ' . "'" . $db->quote($transaction_id) . "'");
		$db->setQuery($query);

		// Get payout ID
		$payout_id = $db->loadResult();

		// Add Payout
		$res = new stdClass;

		if ($payout_id)
		{
			$res->id = $payout_id;
		}
		else
		{
			$res->id = '';
		}

		$res->user_id       = $orderObject->userid;
		$res->payee_name    = JFactory::getUser($orderObject->userid)->name;
		$res->date          = date("Y-m-d H:i:s");
		$res->transction_id = $transaction_id;

		// If fee is refunded then means total amount is paid to campaign promoter
		if ($refund == 1)
		{
			$res->amount = $orderObject->original_amount;
		}
		else
		{
			$res->amount = $orderObject->original_amount - ($data['data']['object']['amount'] / 100);
		}

		$res->status     = 1;
		$res->ip_address = '';
		$res->type       = 'stripe';

		if ($res->id)
		{
			if (!$db->updateObject('#__jticketing_ticket_payouts', $res, 'id'))
			{
			}
		}
		else
		{
			if (!$db->insertObject('#__jticketing_ticket_payouts', $res, 'id'))
			{
			}
		}

		return true;
	}

	/**
	 * Add data to reminder queue
	 *
	 * @param   array  $reminderData  Reminder data
	 * @param   array  $user          User data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addtoReminderQueue($reminderData, $user)
	{
		$data['start_date']   = $reminderData->startdate;
		$data['due_date']   = $reminderData->enddate;
		$data['type']					= 'assign';
		$data['todo_id']				= '';
		$data['recommend_friends']		= array($user);

		// Set the plugin details
		$plg_name   = 'jlike_events';
		$plg_type   = 'content';
		$element    = 'com_jticketing.event';

		// @TODO Snehal Get xref ID of the event
		$element_id = $reminderData->id;
		$options = array('element' => $element, 'element_id' => $element_id, 'plg_name' => $plg_name, 'plg_type' => $plg_type);

		if (!empty($data))
		{
			$res       = $this->ComjlikeMainHelper->updateTodos($data, $options);

			return $res;
		}
	}
}
