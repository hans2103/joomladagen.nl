<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

require_once JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/addons/addon.php';

/**
 * Addon for the JD IDEAL Gateway payment form.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class AddonJdidealgateway extends JdidealAddon
{
	/**
	 * Returns the order information in an array
	 *
	 * order_total  = The amount of the order
	 * order_status = The translated order status
	 * user_email   = The email address of the customer
	 *
	 * @param   int    $order_id  The order ID the request is for.
	 * @param   array  $data      The transaction data.
	 *
	 * @return  array	Order details.
	 *
	 * @since   2.0
	 *
	 * @throws  \RuntimeException
	 */
	public function getOrderInformation($order_id, $data)
	{
		$query = $this->db->getQuery(true);
		$query->select(
			array(
				$this->db->quoteName('user_email'),
				$this->db->quoteName('amount'),
				$this->db->quoteName('status')
			)
		)
			->from($this->db->quoteName('#__jdidealgateway_pays'))
			->where($this->db->quoteName('id') . ' = ' . (int) $order_id);
		$this->db->setQuery($query);
		$order = $this->db->loadObject();

		if (!$order)
		{
			$data['order_status'] = false;

			return $this->callBack($data);
		}

		$data['order_total'] = $order->amount;
		$data['order_status'] = $this->translateOrderStatus($order->status);
		$data['user_email'] = $order->user_email;

		// Return the data
		return $data;
	}

	/**
	 * Get the customer information
	 *
	 * Array of required information:
	 *
	 * Shipping:
	 * firstname
	 * lastname
	 * company
	 * address1
	 * address2
	 * city
	 * zip
	 * countrycode (2 letters)
	 * country (name)
	 * phone
	 * email
	 *
	 * Billing:
	 * firstname
	 * lastname
	 * company
	 * address1
	 * address2
	 * city
	 * zip
	 * countrycode (2 letters)
	 * country (name)
	 * phone
	 * email
	 *
	 * Pricing:
	 * amount
	 * tax (tax amount)
	 * currency (3 letters)
	 *
	 * @param   int  $order_id  The order ID the request is for
	 *
	 * @return  array  Customer details.
	 *
	 * @since   2.0
	 *
	 * @throws  \RuntimeException
	 */
	public function getCustomerInformation($order_id)
	{
		// Collect the data
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('user_email', 'email'))
			->select($this->db->quoteName('amount'))
			->from($this->db->quoteName('#__jdidealgateway_pays'))
			->where($this->db->quoteName('id') . ' = ' . (int) $order_id);
		$this->db->setQuery($query);

		$data['billing'] = $this->db->loadObject();

		return $data;
	}

	/**
	 * Translate the order status from the component status to an JD iDEAL Gateway status.
	 *
	 * @param   string  $order_status  The code of the order status
	 *
	 * @return  string  the JD iDEAL order status code.
	 *
	 * @since   2.0
	 */
	public function translateOrderStatus($order_status)
	{
		return $order_status ?: 'P';
	}

	/**
	 * Get the order status name.
	 *
	 * @param   array  $data  Array with order information
	 *
	 * @return  string  The name of the new order status.
	 *
	 * @since   2.0
	 */
	public function getOrderStatusName($data)
	{
		switch ($data['order_status'])
		{
			case 'C':
				$orderStatusName = JText::_('COM_JDIDEALGATEWAY_STATUS_SUCCESS');
				break;
			case 'X':
				$orderStatusName = JText::_('COM_JDIDEALGATEWAY_STATUS_CANCELLED');
				break;
			case 'P':
				$orderStatusName = JText::_('COM_JDIDEALGATEWAY_STATUS_PENDING');
				break;
			default:
				$orderStatusName = JText::_('COM_JDIDEALGATEWAY_STATUS_UNKNOWN');
				break;
		}

		return $orderStatusName;
	}

	/**
	 * Get the component link.
	 *
	 * @return  string  The URL to the component.
	 *
	 * @since   2.0
	 */
	public function getComponentLink()
	{
		return 'index.php?option=com_jdidealgateway';
	}

	/**
	 * Get the administrator order link.
	 *
	 * @param   string  $order_id  The order ID for the link.
	 *
	 * @return  string  The URL to the order details.
	 *
	 * @since   2.0
	 */
	public function getAdminOrderLink($order_id)
	{
		return 'index.php?option=com_jdidealgateway&view=pays&filter[search]=' . $order_id;
	}

	/**
	 * Get the order link.
	 *
	 * @param   string  $order_id      The order ID for the link
	 * @param   string  $order_number  The order number for the link
	 *
	 * @return  string  The URL to the order details.
	 *
	 * @since   2.0
	 */
	public function getOrderLink($order_id, $order_number)
	{
		return false;
	}

	/**
	 * Set the callback to go back to the component.
	 *
	 * @param   array  $data  The data from the processor
	 *
	 * @return  array  The order data.
	 *
	 * @since   2.0
	 */
	public function callBack($data)
	{
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('jdideal');
		$dispatcher->trigger('onPaymentComplete', array($data));

		return $data;
	}
}
