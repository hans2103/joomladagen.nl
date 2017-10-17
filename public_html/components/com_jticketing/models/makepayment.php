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

jimport('joomla.application.component.model');
jimport('joomla.database.table.user');

/**
 * JTicketing
 *
 * @since  1.6
 */
class JticketingModelmakepayment extends JModelLegacy
{
	/**
	 * Get getorderHTML details
	 *
	 * @param   INT  $target_data  order_id
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function saveBalance($target_data)
	{
		$order_data_id = $this->createOrder($target_data['order'][0]);

		if ($target_data['order_item'])
		{
			$order_item_data = $this->createOrderItems($target_data['order_item'], $order_data_id);
		}
		else
		{
			$order_item_data = $this->createTranOrderItems($target_data['tran_item'], $order_data_id);
			$this->updateOrder($target_data['update_order'][0]);
		}

		return $order_data_id;
	}

	/**
	 * Get getorderHTML details
	 *
	 * @param   INT  $eventid  order_id
	 * @param   INT  $client   order_id
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function getIntegrationID($eventid,$client)
	{
		$query = "SELECT id FROM #__jticketing_integration_xref WHERE source LIKE '" . $client . "' AND eventid=" . $eventid;
		$this->_db->setQuery($query);

		return $rows = $this->_db->loadResult();
	}

	/**
	 * Get getorderHTML details
	 *
	 * @param   INT  $data  order_id
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function createOrder($data)
	{
		$user = JFactory::getUser();

		if (!$data->id)
		{
			$status = $data->status;
		}
		else
		{
			$status = 'P';
		}

		if ($data->user_id)
		{
			$user_id = $data->user_id;
		}
		else
		{
			$user_id = $user->id;
		}

		$res = new StdClass;
		$res->event_details_id = $data->event_details_id;
		$res->name = JFactory::getUser($user_id)->name;
		$res->email = JFactory::getUser($user_id)->email;
		$res->user_id = $user_id;
		$res->coupon_code = '';
		$res->cdate = date("Y-m-d H:i:s");
		$res->mdate = date("Y-m-d H:i:s");
		$res->processor = 'jrob_authorizenet';
		$res->customer_note = 'authorizenet';
		$res->ticketscount = $data->ticketscount;

		$res->parent_order_id = $data->parent_id;
		$res->status = $status;

		// This is calculated amount
		$res->original_amount = $data->original_amount;

		// This is paid amount actually
		$res->order_amount = $data->totalprice;
		$res->amount = $data->totalprice;
		$res->fee = '';
		$res->ip_address = $_SERVER["REMOTE_ADDR"];
		$db = JFactory::getDBO();
		$com_params = JComponentHelper::getParams('com_jticketing');
		$order_prefix = $com_params->get('order_prefix');
		$separator = $com_params->get('separator');
		$random_orderid = $com_params->get('random_orderid');
		$padding_count = $com_params->get('padding_count');

		// Lets make a random char for this order
		// Take order prefix set by admin
		$order_prefix = (string) $order_prefix;

		// String length should not be more than 5
		$order_prefix = substr($order_prefix, 0, 5);

		// Take separator set by admin
		$separator = (string) $separator;
		$res->order_id = $order_prefix . $separator;

		// Check if we have to add random number to order id
		$use_random_orderid = (int) $random_orderid;

		if ($use_random_orderid)
		{
			$random_numer = $this->_random(5);
			$res->order_id .= $random_numer . $separator;

			// This length shud be such that it matches the column lenth of primary key
			// It is used to add pading
			$len = (23 - 5 - 2 - 5);

			// Order_id_column_field_length - prefix_length - no_of_underscores - length_of_random number
		}
		else
		{
			// This length shud be such that it matches the column lenth of primary key
			// It is used to add pading
			$len = (23 - 5 - 2);

			// Order_id_column_field_length - prefix_length - no_of_underscores
		}
		/*##############################################################*/

		if (!$db->insertObject('#__jticketing_order', $res, 'id'))
		{
				echo $db->stderr();

				return false;
		}

		$insert_order_id = $orders_key = $sticketid = $db->insertid();

		$db->setQuery('SELECT order_id FROM #__jticketing_order WHERE id=' . $orders_key);
		$order_id = (string) $db->loadResult();
		$maxlen = 23 - strlen($order_id) - strlen($orders_key);
		$padding_count = (int) $padding_count;

		if ($padding_count > $maxlen)
		{
			$padding_count = $maxlen;
		}

		if (strlen((string) $orders_key) <= $len)
		{
			$append = '';

			for ($z = 0;$z < $padding_count;$z++)
			{
				$append .= '0';
			}

			$append = $append . $orders_key;
		}

		$resd     = new stdClass;
		$resd->id = $orders_key;
		$order_id = $resd->order_id = $order_id . $append;

		if (!$db->updateObject('#__jticketing_order', $resd, 'id'))
		{
		}

		$this->setSession($insert_order_id);
		$this->billingaddr($user->id, $data, $insert_order_id);
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		JticketingMailHelper::sendmailnotify($data->parent_id, 'afterorderemail');

		return $insert_order_id;
	}

	/**
	 * Get getorderHTML details
	 *
	 * @param   INT  $uid              order_id
	 * @param   INT  $data1            order_id
	 * @param   INT  $insert_order_id  order_id
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function billingaddr($uid,$data1,$insert_order_id)
	{
		$db 	= JFactory::getDBO();
		$query = 'SELECT * FROM #__jticketing_users WHERE user_id =' . $uid;

		$db->setQuery($query);
		$data = $db->loadObject();

		$db->setQuery('SELECT order_id FROM #__jticketing_users WHERE order_id=' . $insert_order_id);
		$order_id = (string) $db->loadResult();

		if ($order_id)
		{
			$query = "DELETE FROM #__jticketing_users	WHERE order_id=" . $insert_order_id;
			$db->setQuery($query);

			if (!$db->execute())
			{
			}
		}

		$row                  = new stdClass;
		$row->user_id         = $uid;
		$row->user_email      = $data->user_email;
		$row->address_type    = 'BT';
		$row->firstname       = $data->firstname;
		$row->lastname        = $data->lastname;
		$row->country_code    = $data->country_code;
		$row->vat_number      = $data->vat_number;
		$row->address         = $data->address;
		$row->city            = $data->city;
		$row->state_code      = $data->state_code;
		$row->zipcode         = $data->zipcode;
		$row->phone           = $data->phone;
		$row->approved        = '1';
		$row->order_id        = $insert_order_id;

		if (!$this->_db->insertObject('#__jticketing_users', $row, 'id'))
		{
				echo $this->_db->stderr();

				return false;
		}

		$params = JComponentHelper::getParams('com_jticketing');

		return $row->user_id;
	}

	/**
	 * Get getorderHTML details
	 *
	 * @param   INT  $sticketid  order_id
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function setSession($sticketid)
	{
		$session = JFactory::getSession();
		$session->set('sticketid', $sticketid);
		$session->set('JT_orderid', $sticketid);
	}

	/**
	 * Get getorderHTML details
	 *
	 * @param   INT  $orderdatas     order_id
	 * @param   INT  $order_data_id  order_id
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function createOrderItems($orderdatas,$order_data_id)
	{
		foreach ($orderdatas as $orderdata)
		{
			if ($orderdata->payment_status)
			{
				$status = $orderdata->payment_status;
			}
			else
			{
				$status = 'P';
			}

			$db                = JFactory::getDBO();
			$res               = new StdClass;
			$res->id           = $orderdata->id;
			$res->order_id     = $order_data_id;
			$res->type_id      = $orderdata->type_id;
			$res->ticketcount  = 1;
			$res->ticket_price = $orderdata->ticket_price;
			$res->amount_paid  = $orderdata->price;
			$res->name         = $orderdata->name;
			$res->email        = $orderdata->email;
			$res->attribute_amount = '';
			$res->payment_status = 'P';

				if (!$db->insertObject('#__jticketing_balance_order_items', $res, 'id'))
				{
					echo $db->stderr();

					return false;
				}
		}

			$insert_order_id = $db->insertid();

			return true;
	}

	/**
	 * Get getorderHTML details
	 *
	 * @param   INT  $orderdatas     order_id
	 * @param   INT  $order_data_id  order_id
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function createTranOrderItems($orderdatas ,$order_data_id)
	{
		foreach ($orderdatas as $orderdata)
		{
			if ($orderdata->payment_status)
			{
				$status = $orderdata->payment_status;
			}
			else
			{
				$status = 'P';
			}

			$db            = JFactory::getDBO();
			$res           = new StdClass;
			$res->id       = '';
			$res->order_id = $order_data_id;
			$res->type_id  = $orderdata->type_id;
			$res->ticketcount = 1;
			$res->ticket_price = $orderdata->ticket_price;
			$res->amount_paid = $orderdata->price;
			$res->name     = $orderdata->name;
			$res->email    = $orderdata->email;
			$res->attribute_amount = '';
			$res->payment_status = $status;

				if (!$db->insertObject('#__jticketing_order_items', $res, 'id'))
				{
					echo $db->stderr();

					return false;
				}
		}

		$insert_order_id = $db->insertid();

		return true;
	}

	/**
	 * Get getorderHTML details
	 *
	 * @param   INT  $length  order_id
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function _random($length = 5)
	{
		$salt = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$random = '';

		$stat = @stat(__FILE__);

		if (empty($stat) || !is_array($stat))
		{
			$stat = array(php_uname());
		}

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i ++)
		{
			$random .= $salt[mt_rand(0, $len - 1)];
		}

		return $random;
	}

	/**
	 * Get getorderHTML details
	 *
	 * @param   string  $data  order_id
	 *
	 * @return  array
	 *
	 * @since  1.0.0
	 */
	public function updateOrder($data)
	{
		$db                    = JFactory::getDBO();
		$resd                  = new stdClass;
		$resd->id              = $data->id;
		$resd->order_amount    = $data->order_amount;
		$resd->original_amount = $data->original_amount;
		$resd->amount          = $data->amount;
		$resd->ticketscount    = $data->ticketscount;

		if ($data->ticketscount == 0)
		{
			$resd->status = "T";
		}
		else
		{
			$resd->status = $data->status;
		}

		if (!$db->updateObject('#__jticketing_order', $resd, 'id'))
		{
		}
	}
}
