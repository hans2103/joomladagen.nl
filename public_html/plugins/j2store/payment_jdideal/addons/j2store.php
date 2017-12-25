<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/addons/addon.php';

/**
 * J2Store addon.
 *
 * @package  JDiDEAL
 * @since    2.13
 */
class AddonJ2store extends JdidealAddon
{
	/**
	 * Collect the order information.
	 *
	 * @param   int    $order_id  The ID to get the order data for
	 * @param   array  $data      Array with payment info
	 *
	 * @return  array  Array with order information.
	 *
	 * @since   2.13
	 *
	 * @throws  RuntimeException
	 */
	public function getOrderInformation($order_id, $data)
	{
		$query = $this->db->getQuery(true)
			->select(
				$this->db->quoteName(
					array(
						'o.order_id',
						'o.order_total',
						'o.user_email',
						'o.order_state_id'
					)
				)
			)
			->from($this->db->quoteName('#__j2store_orders', 'o'))
			->where($this->db->quoteName('o.j2store_order_id') . ' = ' . $this->db->quote($order_id));
		$this->db->setQuery($query);
		$order = $this->db->loadObject();

		if (!$order)
		{
			$data['order_status'] = false;

			return $this->callBack($data);
		}

		$data['order_id'] = $order->order_id;
		$data['order_number'] = $order->order_id;
		$data['order_total'] = $order->order_total;
		$data['order_status'] = $this->translateOrderStatus($order->order_state_id);
		$data['user_email'] = $order->user_email;

		// Return the data
		return $data;
	}

	/**
	 * Collect customer information.
	 *
	 * @param   int  $order_id  The ID of the order to get the info from.
	 *
	 * @return  array  The array of customer information.
	 *
	 * @since   2.13
	 */
	public function getCustomerInformation($order_id)
	{
		// Collect the data
		$data = array();

		return $data;
	}

	/**
	 * Translate component order status to JD iDEAL status.
	 *
	 * @param   string  $order_status  The component order status.
	 *
	 * @return  string  The JD iDEAL order status.
	 *
	 * @since   2.13
	 */
	public function translateOrderStatus($order_status)
	{
		switch ($order_status)
		{
			case '4':
				return 'P';
				break;
			case '1':
				return 'C';
				break;
			case '3':
				return 'X';
				break;
			default:
				return 'P';
				break;
		}
	}

	/**
	 * Get the component order status name.
	 *
	 * @param   string  $data  The name of the JD iDEAL order status.
	 *
	 * @return  string  The name of the component order status.
	 *
	 * @since   2.13
	 */
	public function getOrderStatusName($data)
	{
		$value = 'P';

		switch ($data['order_status'])
		{
			case 'C':
				$value = JText::_('COM_JDIDEALGATEWAY_STATUS_CONFIRMED');
				break;
			case 'P':
				$value = JText::_('COM_JDIDEALGATEWAY_STATUS_PENDING');
				break;
			case 'X':
				$value = JText::_('COM_JDIDEALGATEWAY_STATUS_CANCELLED');
				break;
		}

		return $value;
	}

	/**
	 * Get the link to the component.
	 *
	 * @return  string  The link to the component.
	 *
	 * @since   2.13
	 */
	public function getComponentLink()
	{
		return 'index.php?option=com_j2store';
	}

	/**
	 * Get the link to the order on the administrator side.
	 *
	 * @param   int  $order_id  The order ID to get the link for.
	 *
	 * @return  string  The link to the order.
	 *
	 * @since   2.13
	 */
	public function getAdminOrderLink($order_id)
	{
		return 'index.php?option=com_j2store&view=order&id=' . $order_id;
	}

	/**
	 * Get the link to the order on the frontend.
	 *
	 * @param   string  $order_id      The order ID for the link
	 * @param   string  $order_number  The order number for the link
	 *
	 * @return  string  The link to the order.
	 *
	 * @since   2.13
	 */
	public function getOrderLink($order_id, $order_number)
	{
		return 'index.php?option=com_j2store&view=myprofile&task=vieworder&order_id=' . $order_number;
	}

	/**
	 * Perform the callback for any additional tasks to be done.
	 *
	 * @param   array  $data  An array of order data.
	 *
	 * @return  array  An array of order data.
	 *
	 * @since   2.13
	 */
	public function callBack($data)
	{
		return $data;
	}
}
