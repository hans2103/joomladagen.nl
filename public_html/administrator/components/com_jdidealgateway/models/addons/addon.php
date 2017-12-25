<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

/**
 * JD iDEAL Gateway Abstract Addon class.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
abstract class JdidealAddon
{
	/**
	 * JDatabase connector
	 *
	 * @var    JDatabaseDriver
	 * @since  2.0
	 */
	protected $db = null;

	/**
	 * Construct the class.
	 *
	 * @since   2.0
	 */
	public function __construct()
	{
		$this->db = JFactory::getDbo();
	}

	/**
	 * Get the order information.
	 *
	 * @param   int    $order_id  The order ID the request is for.
	 * @param   array  $data      The transaction data.
	 *
	 * @return  array	Order details.
	 *
	 * @since   2.0
	 */
	abstract public function getOrderInformation($order_id, $data);

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
	 */
	abstract public function getCustomerInformation($order_id);

	/**
	 * Translate the order status from the component status to an JD iDEAL Gateway status.
	 *
	 * @param   string  $order_status  The code of the order status
	 *
	 * @return  string  the JD iDEAL order status code.
	 *
	 * @since   2.0
	 */
	abstract public function translateOrderStatus($order_status);

	/**
	 * Get the order status name.
	 *
	 * @param   array  $data  Array with order information
	 *
	 * @return  string  The name of the new order status.
	 *
	 * @since   2.0
	 */
	abstract public function getOrderStatusName($data);

	/**
	 * Get the component link.
	 *
	 * @return  string  The URL to the component.
	 *
	 * @since   2.0
	 */
	abstract public function getComponentLink();

	/**
	 * Get the administrator order link.
	 *
	 * @param   string  $order_id  The order ID for the link.
	 *
	 * @return  string  The URL to the order details.
	 *
	 * @since   2.0
	 */
	abstract public function getAdminOrderLink($order_id);

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
	abstract public function getOrderLink($order_id, $order_number);

	/**
	 * Set the callback to go back to the component.
	 *
	 * @param   array  $data  The data from the processor
	 *
	 * @return  array  The order data.
	 *
	 * @since   2.0
	 */
	abstract public function callBack($data);
}
