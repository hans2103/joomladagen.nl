<?php
/**
 * @package     CSVI
 * @subpackage  J2Store
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace j2store\com_j2store\model\import;

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Order import.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Order extends \RantaiImportEngine
{
	/**
	 * CSVI fields
	 *
	 * @var    \CsviHelperImportFields
	 * @since  7.3.0
	 */
	protected $fields;

	/**
	 * The addon helper
	 *
	 * @var    \Com_J2StoreHelperCom_J2Store
	 * @since  7.3.0
	 */
	protected $helper;

	/**
	 * Order table
	 *
	 * @var    \J2StoreTableOrder
	 * @since  7.3.0
	 */
	private $orderTable;

	/**
	 * Order infos table
	 *
	 * @var    \J2StoreTableOrderinfos
	 * @since  7.3.0
	 */
	private $orderInfosTable;

	/**
	 * Order history table
	 *
	 * @var    \J2StoreTableOrderHistory
	 * @since  7.3.0
	 */
	private $orderHistoryTable;

	/**
	 * Start the menu import process.
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   7.3.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 */
	public function getStart()
	{
		// Process data
		foreach ($this->fields->getData() as $fields)
		{
			foreach ($fields as $name => $details)
			{
				$value = $details->value;

				switch ($name)
				{
					case 'sku':
						$this->setState('product_id', $this->helper->getProductId($name, $value));
						break;
					case 'order_state':
						$this->setState('order_state_id', $this->helper->getOrderStateId($value));
						break;
					case 'order_total':
					case 'order_subtotal':
					case 'order_subtotal_ex_tax':
					case 'order_tax':
					case 'order_shipping':
					case 'order_shipping_tax':
					case 'order_discount':
					case 'order_discount_tax':
					case 'order_credit':
					case 'order_surcharge':
					case 'order_fees':
					case 'orderitem_per_item_tax':
					case 'orderitem_tax':
					case 'orderitem_discount':
					case 'orderitem_discount_tax':
					case 'orderitem_price':
					case 'orderitem_option_price':
					case 'orderitem_finalprice':
					case 'orderitem_finalprice_with_tax':
					case 'orderitem_finalprice_without_tax':
						$this->setState($name, $this->cleanPrice($value));
						break;
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		$orderId = $this->getState('order_id', false);

		if ($orderId)
		{
			if (!$this->getState('j2store_order_id', false) && $orderId)
			{
				$query = $this->db->getQuery(true);
				$query->select($this->db->quoteName('j2store_order_id'));
				$query->from($this->db->quoteName('#__j2store_orders'));
				$query->where($this->db->quoteName('order_id') . ' = ' . $this->db->quote($orderId));
				$this->db->setQuery($query);
				$j2storeOrderId = $this->db->loadResult();

				if (!$j2storeOrderId)
				{
					$this->log->add('Not a valid order id', true);
					$this->log->AddStats('incorrect', \JText::sprintf('COM_CSVI_NOT_PROCESS_ORDER_ID', $orderId));

					return false;
				}

				$this->setState('j2store_order_id', $j2storeOrderId);
				$this->log->add('COM_CSVI_DEBUG_LOAD_ORDER_ID', true);
			}

			if (!$this->getState('j2store_order_id', false))
			{
				$this->setState('j2store_order_id', $this->helper->getOrderId($orderId));
			}

			if ($this->orderTable->load($this->getState('j2store_order_id', 0)))
			{
				if (!$this->template->get('overwrite_existing_data'))
				{
					$this->log->add(\JText::sprintf('COM_FIELDS_WARNING_OVERWRITING_SET_TO_NO', $this->getState('order_id')), false);
					$this->loaded = false;
				}
			}
		}

		return true;
	}

	/**
	 * Process a record.
	 *
	 * @return  bool  Returns true if all is OK | Returns false if no path or menu ID can be found.
	 *
	 * @since   7.3.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 */
	public function getProcessRecord()
	{
		if (!$this->getState('j2store_order_id', false) && $this->template->get('ignore_non_exist'))
		{
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('j2store_order_id', '')));
		}
		else
		{
			// Load the order user details
			$userId = $this->getState('user_id', false);
			$email  = $this->getState('user_email', false);

			if (!$userId && $email)
			{
				$userId = $this->helper->getUserId($email);
				$this->setState('user_id', $userId);
			}

			$this->orderTable->load($this->getState('j2store_order_id', false));

			if (!$this->getState('user_id', false))
			{
				$this->log->add('Cannot process order. There is no user ID found', false);
				$this->log->AddStats('incorrect', 'COM_CSVI_NOT_PROCESS_ORDER_USER');

				return false;
			}

			if (!$this->getState('order_state_id', false))
			{
				$this->setState('order_state_id', 5);
			}

			if (!$this->getState('currency_value', false))
			{
				$this->setState('currency_value', 1);
			}

			if ($this->getState('currency_code', false) && !$this->getState('currency_id', false))
			{
				$this->setState('currency_id', $this->helper->getCurrencyId($this->getState('currency_code', false)));
			}

			if ($this->getState('customer_group', false))
			{
				$groupIds = array();
				$userGroup = explode('|', $this->getState('customer_group', false));

				foreach ($userGroup as $group)
				{
					if ($group)
					{
						$groupIds[] = $this->helper->getUserGroupId($group);
					}
				}

				$this->setState('customer_group', implode(',', $groupIds));
			}

			if (!$this->getState('customer_language', false))
			{
				$lang = \JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
				$this->setState('customer_language', $lang);
			}

			// Bind order data
			$this->orderTable->bind($this->state);

			// Check if we use a given order id
			if ($this->template->get('keepid') || !$this->getState('j2store_order_id', false))
			{
				$this->orderTable->check();

				if (!$this->getState('created_on'))
				{
					$this->orderTable->created_on = $this->date->toSql();
				}

				if (!$this->getState('created_by'))
				{
					$this->orderTable->created_by = $this->userId;
				}
			}
			else
			{
				$this->orderTable->modified_on = $this->date->toSql();
				$this->orderTable->modified_by = $this->userId;
			}

			try
			{
				$this->orderTable->store();
				$this->log->add('Order added successfully', false);
				$this->setState('j2store_order_id', $this->orderTable->j2store_order_id);

				if (!$this->getState('order_id', false) || !$this->getState('j2store_order_id', false))
				{
					$orderNumber = (string) time() . $this->orderTable->j2store_order_id;
					$this->setState('order_id', $orderNumber);
					$orderData                     = array();
					$orderData['order_id']         = $orderNumber;
					$orderData['j2store_order_id'] = $this->orderTable->j2store_order_id;
					$orderData['token'] = \JApplicationHelper::getHash($orderNumber);
					$this->orderTable->bind($orderData);
					$this->orderTable->check();
					$this->orderTable->store();
					$this->log->add('Order number updated successfully', false);
				}

				$this->storeOrderInfos();
				$this->storeOrderHistory();
			}
			catch (\Exception $e)
			{
				$this->log->add('Cannot add order. Error: ' . $e->getMessage(), false);
				$this->log->addStats('incorrect', $e->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Set order infos
	 *
	 * @return  true if all ok false otherwise.
	 *
	 * @since   7.3.0
	 */
	private function storeOrderInfos()
	{
		// Store the user info
		if (!$this->getState('j2store_orderinfo_id', false) && $this->getState('order_id', false))
		{
			// Check if there is the requested address in the database
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('j2store_orderinfo_id'))
				->from($this->db->quoteName('#__j2store_orderinfos'))
				->where($this->db->quoteName('order_id') . ' = ' . (int) $this->getState('order_id'));
			$this->db->setQuery($query);
			$this->setState('j2store_orderinfo_id', $this->db->loadResult());
			$this->log->add('Load the order info', false);
		}

		if ($this->getState('billing_zone_name', false) && !$this->getState('billing_zone_id', false))
		{
			$this->setState('billing_zone_id', $this->helper->getZoneId($this->getState('billing_zone_name', false)));
			$this->log->add('Load the billing zone id', false);
		}

		if ($this->getState('billing_country_name', false) && !$this->getState('billing_country_id', false))
		{
			$this->setState('billing_country_id', $this->helper->getCountryId($this->getState('billing_country_name', false)));
			$this->log->add('Load the billing country id', false);
		}

		if ($this->getState('shipping_zone_name', false) && !$this->getState('shipping_zone_id', false))
		{
			$this->setState('shipping_zone_id', $this->helper->getZoneId($this->getState('shipping_zone_name', false)));
			$this->log->add('Load the shipping zone id', false);
		}

		if ($this->getState('shipping_country_name', false) && !$this->getState('shipping_country_id', false))
		{
			$this->setState('shipping_country_id', $this->helper->getCountryId($this->getState('shipping_country_name', false)));
			$this->log->add('Load the shipping country id', false);
		}

		if (!$this->getState('all_billing', false))
		{
			$defaultAllBilling['email'] = array('label' => 'J2STORE_EMAIL', 'value' => $this->getState('user_email', false) );
			$this->setState('all_billing', json_encode($defaultAllBilling));
		}

		if (!$this->getState('all_shipping', false))
		{
			$this->setState('all_shipping', '{}');
		}

		if (!$this->getState('all_payment', false))
		{
			$this->setState('all_payment', '{}');
		}

		// Load the order info
		if ($this->getState('j2store_orderinfo_id', false))
		{
			$this->orderInfosTable->load($this->getState('j2store_orderinfo_id'));
			$this->log->add('Load the order info');
		}

		$this->orderInfosTable->bind($this->state);

		try
		{
			$this->orderInfosTable->check();
			$this->orderInfosTable->store();
			$this->log->add('Order infos added successfully', false);
		}
		catch (\Exception $e)
		{
			$this->log->add('Cannot add order infos. Error: ' . $e->getMessage(), false);
			$this->log->addStats('incorrect', $e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Save order history
	 *
	 * @return  true if all ok false otherwise..
	 *
	 * @since   7.3.0
	 */
	private function storeOrderHistory()
	{
		// Store the user info
		if (!$this->getState('j2store_orderhistory_id', false) && $this->getState('order_id', false))
		{
			// Check if there is the requested address in the database
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('j2store_orderhistory_id'))
				->from($this->db->quoteName('#__j2store_orderhistories'))
				->where($this->db->quoteName('order_id') . ' = ' . (int) $this->getState('order_id'));
			$this->db->setQuery($query);
			$this->setState('j2store_orderhistory_id', $this->db->loadResult());
			$this->log->add('Load the order history id', false);
		}

		// Load the order info
		if ($this->getState('j2store_orderhistory_id', false))
		{
			$this->orderHistoryTable->load($this->getState('j2store_orderhistory_id'));
			$this->log->add('Load the order history details');
		}

		if (!$this->getState('j2store_orderhistory_id', false))
		{
			$this->orderHistoryTable->created_on = $this->date->toSql();
			$this->orderHistoryTable->created_by = $this->userId;
		}

		if (!$this->getState('notify_customer', false))
		{
			$this->setState('notify_customer', 0);
		}

		if (!$this->getState('order_state_id', false))
		{
			$this->setState('order_state_id', 5);
		}

		// Comments
		$this->orderHistoryTable->comment = $this->getState('comment', 'Order created');

		$this->orderHistoryTable->bind($this->state);

		try
		{
			$this->orderHistoryTable->store();
			$this->log->add('Order history details added successfully', false);
		}
		catch (\Exception $e)
		{
			$this->log->add('Cannot add order history. Error: ' . $e->getMessage(), false);
			$this->log->addStats('incorrect', $e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Load the necessary tables.
	 *
	 * @return  void.
	 *
	 * @since   7.3.0
	 */
	public function loadTables()
	{
		$this->orderTable = $this->getTable('Order');
		$this->orderInfosTable = $this->getTable('Orderinfos');
		$this->orderHistoryTable = $this->getTable('OrderHistory');
	}

	/**
	 * Clear the loaded tables.
	 *
	 * @return  void.
	 *
	 * @since   7.3.0
	 */
	public function clearTables()
	{
		$this->orderTable->reset();
		$this->orderInfosTable->reset();
		$this->orderHistoryTable->reset();
	}
}
