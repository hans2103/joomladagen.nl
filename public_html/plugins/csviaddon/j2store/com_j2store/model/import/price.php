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
 * Product import.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Price extends \RantaiImportEngine
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
	 * Product price table
	 *
	 * @var    \J2StoreTableProductPrice
	 * @since  7.3.0
	 */
	private $productPriceTable;

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
					case 'customer_group_name':
						$groupId = $this->helper->getUserGroupId($value);
						$this->setState('customer_group_id', $groupId);
						break;
					case 'price':
					case 'price_new':
						$this->setState($name, $this->toPeriod($value));
						break;
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		$sku = $this->getState('sku', false);

		if (!$sku)
		{
			$this->loaded = false;
			$this->log->addStats('skipped', 'COM_CSVI_NO_PRODUCT_SKU_FOUND');
		}
		else
		{
			$this->loaded = true;

			if (!$this->getState('j2store_productprice_id', false))
			{
				$this->setState('variant_id', $this->helper->getVariantId($sku));
			}

			if ($this->productPriceTable->load($this->getState('j2store_productprice_id', 0)))
			{
				if (!$this->template->get('overwrite_existing_data'))
				{
					$this->log->add(\JText::sprintf('COM_FIELDS_WARNING_OVERWRITING_SET_TO_NO', $this->getState('sku')), false);
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
		if (!$this->loaded)
		{
			return false;
		}

		if (!$this->getState('j2store_productprice_id', false) && $this->template->get('ignore_non_exist'))
		{
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('sku', '')));
		}
		else
		{
			if (!$this->getState('variant_id', false))
			{
				$this->log->add('Product not found with SKU ' . $this->getState('sku'));
				$this->log->AddStats('skipped', \JText::sprintf('COM_CSVI_NO_PRODUCT_ID_FOUND', $this->getState('sku')));
			}
			elseif (!$this->getState('customer_group_id', false))
			{
				$this->log->add('No user group name found');
				$this->log->AddStats('skipped', 'COM_CSVI_NO_USER_GROUP_FOUND');
			}
			else
			{
				$this->productPriceTable->bind($this->state);

				$this->productPriceTable->check();

				if ($this->productPriceTable->get('j2store_productprice_id'))
				{
					$this->setState('j2store_productprice_id', $this->productPriceTable->get('j2store_productprice_id'));
				}

				$this->productPriceTable->load($this->productPriceTable->get('j2store_productprice_id'));

				if (!$this->getState('j2store_productprice_id', false))
				{
					if (!$this->getState('date_from', false))
					{
						$this->productPriceTable->date_from = '0000-00-00 00:00:00';
					}

					if (!$this->getState('date_to', false))
					{
						$this->productPriceTable->date_to = '0000-00-00 00:00:00';
					}

					if (!$this->getState('quantity_from', false))
					{
						$this->productPriceTable->quantity_from = 0;
					}
				}

				if ($this->getState('price_new', false))
				{
					$this->productPriceTable->price = $this->getState('price_new', false);
				}

				if ($this->getState('customer_group_name_new', false))
				{
					$this->productPriceTable->customer_group_id = $this->helper->getUserGroupId($this->getState('customer_group_name_new', false));
				}

				if (strtoupper($this->getState('price_delete')) === 'Y')
				{
					if ($this->productPriceTable->get('j2store_productprice_id'))
					{
						$this->productPriceTable->delete($this->productPriceTable->get('j2store_productprice_id'));
					}
					else
					{
						$this->log->addStats('incorrect', 'COM_CSVI_PRICE_NOT_DELETED_NO_ID');
					}
				}
				else
				{
					try
					{
						$this->productPriceTable->store();
						$this->log->add('Product prices added successfully', false);
					}
					catch (\Exception $e)
					{
						$this->log->add('Cannot add product price. Error: ' . $e->getMessage(), false);
						$this->log->addStats('incorrect', $e->getMessage());

						return false;
					}
				}
			}
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
		$this->productPriceTable = $this->getTable('ProductPrice');
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
		$this->productPriceTable->reset();
	}
}
