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
 * Order item import.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Orderitem extends \RantaiImportEngine
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
	 * Order item table
	 *
	 * @var    \J2StoreTableOrderitem
	 * @since  7.3.0
	 */
	private $orderItemTable;

	/**
	 * Order item attribute table
	 *
	 * @var    \J2StoreTableOrderitemattribute
	 * @since  7.3.0
	 */
	private $orderItemAttributeTable;

	/**
	 * Product id
	 *
	 * @var    int
	 * @since  7.3.0
	 */
	private $productId;

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
					case 'orderitem_taxprofile_name':
						$taxProfileId = $this->helper->getTaxProfileId($value);
						$this->setState('orderitem_taxprofile_id', $taxProfileId);
						break;
					case 'sku':
						$this->setState('orderitem_sku', $value);
						break;
					case 'vendor_user_email':
						$this->setState('vendor_id', $this->helper->getVendorId($value));
						break;
					case 'orderitem_per_item_tax':
					case 'orderitem_tax':
					case 'orderitem_discount':
					case 'orderitem_discount_tax':
					case 'orderitem_price':
					case 'orderitem_option_price':
					case 'orderitem_finalprice':
					case 'orderitem_finalprice_with_tax':
					case 'orderitem_finalprice_without_tax':
					case 'orderitemattribute_price':
						$this->setState($name, $this->cleanPrice($value));
						break;
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		$requiredFields = array('order_id', 'orderitem_sku');

		if (!$this->getState('order_id', false) || !$this->getState('orderitem_sku', false))
		{
			$this->loaded = false;
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_MISSING_ORDERITEMS_REQUIRED_FIELDS', implode(' or ', $requiredFields)));
		}
		else
		{
			$checkOrderId = $this->helper->checkOrderId($this->getState('order_id', false));
			$this->productId = $this->helper->getProductId('sku', $this->getState('orderitem_sku', false), '#__j2store_variants', 'product_id');

			if (!$checkOrderId)
			{
				$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_NO_VALID_ORDER_ID', $this->getState('order_id', false)));
				$this->loaded = false;
			}
			elseif (!$this->productId)
			{
				$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_NO_VALID_PRODUCT_SKU', $this->getState('orderitem_sku', false)));
				$this->loaded = false;
			}
			else
			{
				$this->loaded = true;

				if (!$this->getState('j2store_orderitem_id', false))
				{
					$this->setState(
						'j2store_orderitem_id',
						$this->helper->getOrderItemId($this->getState('order_id', false), $this->getState('orderitem_sku', false))
					);
				}

				if ($this->orderItemTable->load($this->getState('j2store_orderitem_id', 0)))
				{
					if (!$this->template->get('overwrite_existing_data'))
					{
						$this->log->add(\JText::sprintf('COM_FIELDS_WARNING_OVERWRITING_SET_TO_NO', $this->getState('order_id')), false);
						$this->loaded = false;
					}
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
		if (!$this->loaded)
		{
			return false;
		}

		if (!$this->getState('j2store_orderitem_id', false) && $this->template->get('ignore_non_exist'))
		{
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('order_id', '')));
		}
		else
		{
			If (!$this->getState('variant_id', false) && $this->getState('orderitem_sku', false))
			{
				$this->setState('variant_id', $this->helper->getVariantId($this->getState('orderitem_sku', false)));
			}

			If (!$this->getState('product_id', false))
			{
				$this->setState('product_id', $this->productId);
			}

			if (!$this->getState('j2store_orderitem_id', false))
			{
				If (!$this->getState('orderitem_type', false))
				{
					$this->setState('orderitem_type', 'normal');
				}

				If (!$this->getState('orderitem_quantity', false))
				{
					$this->setState('orderitem_quantity', 1);
				}

				If (!$this->getState('orderitem_weight', false))
				{
					$this->setState('orderitem_weight', 0);
				}

				If (!$this->getState('orderitem_weight_total', false))
				{
					$this->setState('orderitem_weight_total', 0);
				}
			}

			If (!$this->getState('product_type', false))
			{
				$this->setState('product_type', $this->helper->getProductType($this->productId));
			}

			If (!$this->getState('orderitem_name', false))
			{
				$this->setState('orderitem_name', $this->helper->getProductName($this->productId));
			}

			If (!$this->getState('orderitem_params', false))
			{
				$params = array();

				$params['thumb_image'] = '';

				if ($this->getState('thumb_image', false))
				{
					$params['thumb_image'] = $this->getState('thumb_image', false);
				}

				$params['shipping'] = '0';

				if ($this->getState('shipping', false))
				{
					$params['shipping'] = $this->getState('shipping', false);
				}

				$this->setState('orderitem_params', json_encode($params));
			}

			if (!$this->getState('j2store_orderitem_id', false) && !$this->getState('created_on'))
			{
				$this->orderItemTable->created_on = $this->date->toSql();
				$this->orderItemTable->created_by = $this->userId;
			}

			$this->orderItemTable->bind($this->state);
			$this->orderItemTable->check();

			try
			{
				$this->orderItemTable->store();
				$this->log->add('Order items added successfully', false);
				$this->setState('orderitem_id', $this->orderItemTable->j2store_orderitem_id);
				$this->addOrderItemAttributes();
			}
			catch (\Exception $e)
			{
				$this->log->add('Cannot add order items. Error: ' . $e->getMessage(), false);
				$this->log->addStats('incorrect', $e->getMessage());

				return false;
			}
		}

		return true;
	}

	/**
	 * Add order item attributes
	 *
	 * @return  mixed False if query gets no result void otherwise.
	 *
	 * @since   7.3.0
	 */
	private function addOrderItemAttributes()
	{
		$optionId        = $optionValueId = '';
		$productOptionId = $productOptionValueId = '';
		$attribute       = array();

		if ($this->getState('orderitemattribute_name', false))
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('j2store_option_id'))
				->from($this->db->quoteName('#__j2store_options'))
				->where($this->db->quoteName('option_name') . ' = ' . $this->db->quote($this->getState('orderitemattribute_name', false)));
			$this->db->setQuery($query);
			$optionId = $this->db->loadResult();
			$this->setState('option_id', $optionId);
			$this->log->add('Query to find option id from attribute name');

			if ($optionId)
			{
				$query->clear()
					->select($this->db->quoteName('j2store_productoption_id'))
					->from($this->db->quoteName('#__j2store_product_options'))
					->where($this->db->quoteName('product_id') . ' = ' . (int) $this->productId)
					->where($this->db->quoteName('option_id') . ' = ' . (int) $optionId);
				$this->db->setQuery($query);
				$productOptionId = $this->db->loadResult();
				$this->setState('productattributeoption_id', $productOptionId);
				$this->log->add('Query to find product option id from option id and product id');
			}

			$attribute[] = $this->getState('orderitemattribute_name', false);
		}

		if ($optionId && $this->getState('orderitemattribute_value', false))
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('j2store_optionvalue_id'))
				->from($this->db->quoteName('#__j2store_optionvalues'))
				->where($this->db->quoteName('option_id') . ' = ' . (int) $this->getState('option_id', false))
				->where($this->db->quoteName('optionvalue_name') . ' = ' . $this->db->quote($this->getState('orderitemattribute_value', false)));
			$this->db->setQuery($query);
			$optionValueId = $this->db->loadResult();
			$this->log->add('Query to find option value id');
			$this->setState('optionvalue_id', $optionValueId);

			if ($optionValueId)
			{
				$query->clear()
					->select($this->db->quoteName('j2store_product_optionvalue_id'))
					->from($this->db->quoteName('#__j2store_product_optionvalues'))
					->where($this->db->quoteName('productoption_id') . ' = ' . (int) $this->getState('productattributeoption_id', false))
					->where($this->db->quoteName('optionvalue_id') . ' = ' . (int) $optionValueId);
				$this->db->setQuery($query);
				$productOptionValueId = $this->db->loadResult();
				$this->setState('productattributeoptionvalue_id', $productOptionValueId);
				$this->log->add('Query to find product option value id', false);
			}

			$attribute[] = $this->getState('orderitemattribute_value', false);
		}

		if (!$optionId || !$optionValueId)
		{
			$this->log->addStats('Error', \JText::sprintf('COM_CSVI_COM_J2STORE_ORDERITEM_NOT_VALID_ATTRIBUTE', implode(',', $attribute)));
			$this->log->add('The given attributes ' . implode(',', $attribute) . ' are not valid', false);

			return false;
		}

		if (!$productOptionId || !$productOptionValueId)
		{
			$this->log->addStats('Error', \JText::sprintf('COM_CSVI_COM_J2STORE_ORDERITEM_NO_PRODUCT_ATTRIBUTE', implode(',', $attribute), $this->getState('orderitem_sku', false)));
			$this->log->add('The given attributes ' . implode(',', $attribute) . ' are not linked to the product', false);

			return false;
		}

		$this->orderItemAttributeTable->load(
			array(
				'orderitem_id' => $this->getState('orderitem_id', false),
				'productattributeoption_id' => $this->getState('productattributeoption_id', false),
				'productattributeoptionvalue_id' => $this->getState('productattributeoptionvalue_id', false))
		);
		$data = ArrayHelper::fromObject($this->state);
		$this->orderItemAttributeTable->bind($data);
		$this->orderItemAttributeTable->check();

		try
		{
			$this->orderItemAttributeTable->store();
			$this->log->add('Added order item attributes', false);
		}
		catch (\Exception $e)
		{
			$this->log->add('Cannot add order item attributes. Error: ' . $e->getMessage(), false);
			$this->log->addStats('incorrect', $e->getMessage());
		}

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
		$this->orderItemTable = $this->getTable('Orderitem');
		$this->orderItemAttributeTable = $this->getTable('Orderitemattribute');
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
		$this->orderItemTable->reset();
		$this->orderItemAttributeTable->reset();
	}
}
