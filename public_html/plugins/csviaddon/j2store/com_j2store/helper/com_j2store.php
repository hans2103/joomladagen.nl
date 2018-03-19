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

defined('_JEXEC') or die;

/**
 * J2Store helper.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Com_J2StoreHelperCom_J2Store
{
	/**
	 * Template helper
	 *
	 * @var    CsviHelperTemplate
	 * @since  7.3.0
	 */
	protected $template = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  7.3.0
	 */
	protected $log = null;

	/**
	 * Fields helper
	 *
	 * @var    CsviHelperFields
	 * @since  7.3.0
	 */
	protected $fields = null;

	/**
	 * Database connector
	 *
	 * @var    JDatabase
	 * @since  7.3.0
	 */
	protected $db = null;

	/**
	 * Constructor.
	 *
	 * @param   CsviHelperTemplate  $template  An instance of CsviHelperTemplate.
	 * @param   CsviHelperLog       $log       An instance of CsviHelperLog.
	 * @param   CsviHelperFields    $fields    An instance of CsviHelperFields.
	 * @param   JDatabaseDriver     $db        Database connector.
	 *
	 * @since   7.3.0
	 */
	public function __construct(
		CsviHelperTemplate $template,
		CsviHelperLog $log,
		CsviHelperFields $fields,
		JDatabaseDriver $db)
	{
		$this->template = $template;
		$this->log      = $log;
		$this->fields   = $fields;
		$this->db       = $db;
	}

	/**
	 * Get the category ID based on it's path.
	 *
	 * @param   string  $category_path  The path of the category
	 *
	 * @return  int  The ID of the category.
	 *
	 * @since   7.3.0
	 */
	public function getCategoryId($category_path)
	{
		if (!$category_path)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__categories'))
			->where($this->db->quoteName('extension') . ' = ' . $this->db->quote('com_content'))
			->where($this->db->quoteName('path') . ' = ' . $this->db->quote($category_path));
		$this->db->setQuery($query);
		$catid = $this->db->loadResult();
		$this->log->add('Found category id for path ' . $category_path);

		if (!$catid)
		{
			$catid = 2;
			$this->log->add('No category id found so storing in default category');
		}

		return $catid;
	}

	/**
	 * Get the manufacturer ID based on email or name.
	 *
	 * @param   string  $fieldValue  The value of the field to check the manufacturer
	 *
	 * @return  int  The ID of the manufacturer.
	 *
	 * @since   7.3.0
	 */
	public function getManufacturerId($fieldValue)
	{
		if (!$fieldValue)
		{
			return false;
		}

		$manufacturerId = 0;
		$addressId      = $this->getAddressId('company', $fieldValue);

		if ($addressId)
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('j2store_manufacturer_id'))
				->from($this->db->quoteName('#__j2store_manufacturers'))
				->where($this->db->quoteName('address_id') . ' = ' . $this->db->quote($addressId));
			$this->db->setQuery($query);
			$manufacturerId = $this->db->loadResult();
			$this->log->add('Found manufacturer id  ' . $fieldValue);
		}

		return $manufacturerId;
	}

	/**
	 * Get the address ID based on company or vendor name.
	 *
	 * @param   string  $field       The field to check the manufacturer or vendor
	 * @param   string  $fieldValue  The value of the field to check the manufacturer or vendor
	 *
	 * @return  int  The ID of the manufacturer or vendor.
	 *
	 * @since   7.3.0
	 */
	private function getAddressId($field, $fieldValue)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_address_id'))
			->from($this->db->quoteName('#__j2store_addresses'))
			->where($this->db->quoteName($field) . ' = ' . $this->db->quote($fieldValue));
		$this->db->setQuery($query);
		$addressId = $this->db->loadResult();

		return $addressId;
	}

	/**
	 * Get the product source id from Joomla content.
	 *
	 * @param   string  $productId  The id of the product
	 *
	 * @return  int  The ID of the Joomla content.
	 *
	 * @since   7.3.0
	 */
	public function getProductSourceId($productId)
	{
		if (!$productId)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('product_source_id'))
			->from($this->db->quoteName('#__j2store_products'))
			->where($this->db->quoteName('j2store_product_id') . ' = ' . (int) $productId);
		$this->db->setQuery($query);
		$this->log->add('Found the product source ID');

		return $this->db->loadResult();
	}

	/**
	 * Get the Tax profile id from name
	 *
	 * @param   string  $profileName  The tax profile name
	 *
	 * @return  int  The ID of the tax profile.
	 *
	 * @since   7.3.0
	 */
	public function getTaxProfileId($profileName)
	{
		if (!$profileName)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_taxprofile_id'))
			->from($this->db->quoteName('#__j2store_taxprofiles'))
			->where($this->db->quoteName('taxprofile_name') . ' = ' . $this->db->quote($profileName));
		$this->db->setQuery($query);
		$this->log->add('Found the tax profile ID');

		return $this->db->loadResult();
	}

	/**
	 * Get the product ID.
	 *
	 * @param   string  $field        The field name
	 * @param   string  $fieldValue   The value of the field
	 * @param   string  $table        The table name
	 * @param   string  $selectField  The select field
	 *
	 * @return  int  The ID of the J2Store product.
	 *
	 * @since   7.3.0
	 */
	public function getProductId($field, $fieldValue, $table = '#__j2store_variants', $selectField = 'product_id')
	{
		if (!$field || !$fieldValue)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName($selectField))
			->from($this->db->quoteName($table))
			->where($this->db->quoteName($field) . ' = ' . $this->db->quote($fieldValue));
		$this->db->setQuery($query);
		$productId = $this->db->loadResult();
		$this->log->add('Find the product id for ' . $field . ' ' . $fieldValue, false);

		if (!$productId)
		{
			$this->log->add('No product id found for ' . $field . ' ' . $fieldValue);
		}

		return $productId;
	}

	/**
	 * Get the user group id, this is necessary for updating existing user groups.
	 *
	 * @param   string  $name  The name of the user group
	 *
	 * @return  mixed  ID of the user if found | False otherwise.
	 *
	 * @since   7.3.0
	 */
	public function getUserGroupId($name)
	{
		if (!$name)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__usergroups'))
			->where($this->db->quoteName('title') . '  = ' . $this->db->quote($name));
		$this->db->setQuery($query);
		$this->log->add('Found the user group ID');

		return $this->db->loadResult();
	}

	/**
	 * Get the vendor id from user email
	 *
	 * @param   string  $email  The email of the vendor
	 *
	 * @return  mixed  ID of the user if found | False otherwise.
	 *
	 * @since   7.3.0
	 */
	public function getVendorId($email)
	{
		if (!$email)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_vendor_id'))
			->from($this->db->quoteName('#__j2store_vendors'))
			->leftJoin(
				$this->db->quoteName('#__users') . ' ON ' .
				$this->db->quoteName('#__users.id') . ' = ' . $this->db->quoteName('#__j2store_vendors.j2store_user_id')
			)
			->where($this->db->quoteName('#__users.email') . ' = ' . $this->db->quote($email));
		$this->db->setQuery($query);
		$this->log->add('Found vendor id for email ' . $email);

		return $this->db->loadResult();
	}

	/**
	 * Get the variant ID.
	 *
	 * @param   string  $productSku  The SKU of the product
	 *
	 * @return  int  The ID of the variant.
	 *
	 * @since   7.3.0
	 */
	public function getVariantId($productSku)
	{
		if (!$productSku)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_variant_id'))
			->from($this->db->quoteName('#__j2store_variants'))
			->where($this->db->quoteName('sku') . ' = ' . $this->db->quote($productSku));
		$this->db->setQuery($query);
		$variantId = $this->db->loadResult();

		return $variantId;
	}

	/**
	 * Get the order id from order number
	 *
	 * @param   string  $orderNumber  The order number
	 *
	 * @return  int  The ID of the order.
	 *
	 * @since   7.3.0
	 */
	public function getOrderId($orderNumber)
	{
		if (!$orderNumber)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_order_id'))
			->from($this->db->quoteName('#__j2store_orders'))
			->where($this->db->quoteName('order_id') . ' = ' . $this->db->quote($orderNumber));
		$this->db->setQuery($query);
		$this->log->add('Found the order ID');

		return $this->db->loadResult();
	}

	/**
	 * Get the order id from order number
	 *
	 * @param   string  $orderId  The order number
	 * @param   string  $itemSku  The product sku
	 *
	 * @return  int  The ID of the order item.
	 *
	 * @since   7.3.0
	 */
	public function getOrderItemId($orderId, $itemSku)
	{
		if (!$orderId)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_orderitem_id'))
			->from($this->db->quoteName('#__j2store_orderitems'))
			->where($this->db->quoteName('orderitem_sku') . ' = ' . $this->db->quote($itemSku))
			->where($this->db->quoteName('order_id') . ' = ' . $this->db->quote($orderId));
		$this->db->setQuery($query);
		$this->log->add('Looking for order ID');

		return $this->db->loadResult();
	}

	/**
	 * Get the user id from email
	 *
	 * @param   string  $email  The user email
	 *
	 * @return  int  The ID of the user.
	 *
	 * @since   7.3.0
	 */
	public function getUserId($email)
	{
		if (!$email)
		{
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('id'));
		$query->from($this->db->quoteName('#__users'));
		$query->where($this->db->quoteName('email') . ' = ' . $this->db->quote($email));
		$this->db->setQuery($query);
		$this->log->add('Found user id from email');

		return $this->db->loadResult();
	}

	/**
	 * Unpublish products before import.
	 *
	 * @param   CsviHelperTemplate  $template  An instance of CsviHelperTemplate
	 * @param   CsviHelperLog       $log       An instance of CsviHelperLog
	 * @param   JDatabase           $db        JDatabase class
	 *
	 * @return  void
	 *
	 * @since   7.3.0
	 *
	 * @throws  RuntimeException
	 */
	public function unpublishBeforeImport(CsviHelperTemplate $template, CsviHelperLog $log, JDatabase $db)
	{
		if ($this->template->get('unpublish_before_import', 0))
		{
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__j2store_products'))
				->set($this->db->quoteName('enabled') . ' = 0');
			$this->db->setQuery($query);

			if (!$this->db->execute())
			{
				$log->add('Cannot unpublish products before import');
			}

			$log->add('Unpublishing products before import');
		}
	}

	/**
	 * Check if product is of downloadble type
	 *
	 * @param   string  $sku  The SKU of product
	 *
	 * @return  boolean True if downloadable false otherwise
	 *
	 * @since   7.3.0
	 */
	public function checkDownloadableProduct($sku)
	{
		if (!$sku)
		{
			return false;
		}

		$productId = $this->getProductId('sku', $sku);

		if (!$productId)
		{
			$this->log->add('No product found with given SKU ' . $sku);
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('product_type'))
			->from($this->db->quoteName('#__j2store_products'))
			->where($this->db->quoteName('j2store_product_id') . ' = ' . (int) $productId);
		$this->db->setQuery($query);
		$this->log->add('Found the product type');
		$result = $this->db->loadResult();

		if ($result === 'downloadable')
		{
			return true;
		}

		return false;
	}

	/**
	 * Get the article ID
	 *
	 * @param   string  $alias  The article alias
	 * @param   string  $catId  The id of the category
	 *
	 * @return  int  The ID article.
	 *
	 * @since   7.3.0
	 */
	public function getContentId($alias, $catId)
	{
		if (!$alias || !$catId)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__content'))
			->where($this->db->quoteName('alias') . ' = ' . $this->db->quote($alias))
			->where($this->db->quoteName('catid') . ' = ' . (int) $catId);
		$this->db->setQuery($query);
		$this->log->add('Find the Joomla content ID');

		return $this->db->loadResult();
	}

	/**
	 * Get the order state id from name
	 *
	 * @param   string  $name  The name of the order state
	 *
	 * @return  mixed  ID of the user if found | False otherwise.
	 *
	 * @since   7.3.0
	 */
	public function getOrderStateId($name)
	{
		if (!$name)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__j2store_orderstatuses'))
			->where($this->db->quoteName('orderstatus_name') . '  = ' . $this->db->quote($name));
		$this->db->setQuery($query);
		$this->log->add('Found the order state id');

		return $this->db->loadResult();
	}

	/**
	 * Get the currency id from code
	 *
	 * @param   string  $code  The currency code
	 *
	 * @return  int  The ID of the currency.
	 *
	 * @since   7.3.0
	 */
	public function getCurrencyId($code)
	{
		if (!$code)
		{
			return false;
		}

		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('j2store_currency_id'));
		$query->from($this->db->quoteName('#__j2store_currencies'));
		$query->where($this->db->quoteName('currency_code') . ' = ' . $this->db->quote($code));
		$this->db->setQuery($query);
		$this->log->add('COM_CSVI_DEBUG_RETRIEVE_CURRENCY_ID');

		return $this->db->loadResult();
	}

	/**
	 * Get the zone id from name
	 *
	 * @param   string  $name  The zone name
	 *
	 * @return  int  The ID of the zone.
	 *
	 * @since   7.3.0
	 */
	public function getZoneId($name)
	{
		if (!$name)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_zone_id'))
			->from($this->db->quoteName('#__j2store_zones'))
			->where($this->db->quoteName('zone_name') . ' = ' . $this->db->quote($name));
		$this->db->setQuery($query);
		$this->log->add('Found zone id from zone name');

		return $this->db->loadResult();
	}

	/**
	 * Get the country id from name
	 *
	 * @param   string $name  The country name
	 * @param   string $field The field name
	 *
	 * @return  int  The ID of the country.
	 *
	 * @since   7.3.0
	 */
	public function getCountryId($name, $field = 'country_name')
	{
		if (!$name)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_country_id'))
			->from($this->db->quoteName('#__j2store_countries'))
			->where($this->db->quoteName($field) . ' = ' . $this->db->quote($name));
		$this->db->setQuery($query);
		$this->log->add('Get country id from country name');

		return $this->db->loadResult();
	}

	/**
	 * Get the product type
	 *
	 * @param   string  $productId  The product id
	 *
	 * @return  int  The ID of the order item.
	 *
	 * @since   7.3.0
	 */
	public function getProductType($productId)
	{
		if (!$productId)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('product_type'))
			->from($this->db->quoteName('#__j2store_products'))
			->where($this->db->quoteName('j2store_product_id') . ' = ' . $this->db->quote($productId));
		$this->db->setQuery($query);
		$this->log->add('Found the product type');

		return $this->db->loadResult();
	}

	/**
	 * Get the product name
	 *
	 * @param   string  $productId  The product id
	 *
	 * @return  int  The name of the product.
	 *
	 * @since   7.3.0
	 */
	public function getProductName($productId)
	{
		if (!$productId)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('title'))
			->from($this->db->quoteName('#__content'))
			->leftJoin(
				$this->db->quoteName('#__j2store_products') . ' ON ' .
				$this->db->quoteName('#__content.id') . ' = ' . $this->db->quoteName('#__j2store_products.product_source_id')
			)
			->where($this->db->quoteName('#__j2store_products.j2store_product_id') . ' = ' . $this->db->quote($productId));
		$this->db->setQuery($query);
		$this->log->add('Found the product name');

		return $this->db->loadResult();
	}

	/**
	 * Check if the order id is valid
	 *
	 * @param   string  $orderId  The order number
	 *
	 * @return  bool  True if order id is valid false otherwise
	 *
	 * @since   7.3.0
	 */
	public function checkOrderId($orderId)
	{
		if (!$orderId)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_order_id'))
			->from($this->db->quoteName('#__j2store_orders'))
			->where($this->db->quoteName('order_id') . ' = ' . $this->db->quote($orderId));
		$this->db->setQuery($query);
		$this->log->add('Checking the the order ID');
		$result = $this->db->loadResult();

		if ($result)
		{
			return true;
		}

		return false;
	}

	/**
	 * Get the list of manufacturers
	 *
	 * @return  array manufacturers list
	 *
	 * @since   7.5.0
	 */
	public function getManufacturerList()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_manufacturer_id') . ' AS value')
			->select($this->db->quoteName('company') . ' AS text')
			->from($this->db->quoteName('#__j2store_manufacturers'))
			->leftJoin(
				$this->db->quoteName('#__j2store_addresses')
				. ' ON ' . $this->db->quoteName('#__j2store_addresses.j2store_address_id') . ' = ' . $this->db->quoteName('#__j2store_manufacturers.address_id')
			)
			->where('#__j2store_manufacturers.enabled = 1');
		$this->db->setQuery($query);
		$manufacturers = $this->db->loadObjectList();

		return $manufacturers;
	}

	/**
	 * Get the zone id for the country
	 *
	 * @param   int    $countryId The country id
	 * @param   string $zoneCode  The zone code
	 * @param   string $zoneName  The zone name
	 *
	 * @return  int  The ID of the zone.
	 *
	 * @since   7.5.0
	 */
	public function getCountryZoneId($countryId, $zoneCode, $zoneName)
	{
		if (!$countryId && (!$zoneCode || !$zoneName))
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_zone_id'))
			->from($this->db->quoteName('#__j2store_zones'))
			->where($this->db->quoteName('country_id') . ' = ' . (int) $countryId);

		if ($zoneCode)
		{
			$query->where($this->db->quoteName('zone_code') . ' = ' . $this->db->quote($zoneCode));
		}

		if ($zoneName)
		{
			$query->where($this->db->quoteName('zone_name') . ' = ' . $this->db->quote($zoneName));
		}

		$this->db->setQuery($query);
		$this->log->add('Found zone id from zone name or zone code and country id');

		return $this->db->loadResult();
	}

	/**
	 * Get geo zone id
	 *
	 * @param   string $geoName The geo name
	 *
	 * @return  bool  True if order id is valid false otherwise
	 *
	 * @since   7.3.0
	 */
	public function getGeoZoneId($geoName)
	{
		if (!$geoName)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('j2store_geozone_id'))
			->from($this->db->quoteName('#__j2store_geozones'))
			->where($this->db->quoteName('geozone_name') . ' = ' . $this->db->quote($geoName));
		$this->db->setQuery($query);
		$this->log->add('Get geo zone id');

		return $this->db->loadResult();
	}
}
