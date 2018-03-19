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


/**
 * Product files import.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Productfile extends \RantaiImportEngine
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
	 * @var    \J2StoreTableProductFile
	 * @since  7.3.0
	 */
	private $productFileTable;

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

			if (!$this->helper->checkDownloadableProduct($sku))
			{
				$this->loaded = false;
				$this->log->addStats('skipped', 'COM_CSVI_PRODUCT_NOT_DOWNLOADBLE_TYPE');
			}

			if (!$this->getState('j2store_productprice_id', false))
			{
				$this->setState('product_id', $this->helper->getProductId('sku', $sku));
			}

			if ($this->productFileTable->load($this->getState('j2store_productfile_id', 0)))
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

		if (!$this->getState('j2store_productfile_id', false) && $this->template->get('ignore_non_exist'))
		{
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('sku', '')));
		}
		else
		{
			if (!$this->getState('product_id', false))
			{
				$this->log->add('Product not found with SKU ' . $this->getState('sku'));
				$this->log->AddStats('skipped', \JText::sprintf('COM_CSVI_NO_PRODUCT_ID_FOUND', $this->getState('sku')));
			}
			else
			{
				if (!$this->template->get('append_files', 0))
				{
					$query = $this->db->getQuery(true)
						->delete($this->db->quoteName('#__j2store_productfiles'))
						->where($this->db->quoteName('product_id') . '=' . (int) $this->getState('product_id', false));
					$this->db->setQuery($query)->execute();
					$this->log->add('Deleted existing product files as append files set to no');
				}

				$this->productFileTable->bind($this->state);

				$this->productFileTable->check();

				if (!$this->getState('j2store_productfile_id', false))
				{
					if (!$this->getState('download_total', false))
					{
						$this->productFileTable->download_total = 0;
					}
				}

				try
				{
					$this->productFileTable->store();
					$this->log->add('Product files added successfully', false);
				}
				catch (\Exception $e)
				{
					$this->log->add('Cannot add product files. Error: ' . $e->getMessage(), false);
					$this->log->addStats('incorrect', $e->getMessage());

					return false;
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
		$this->productFileTable = $this->getTable('ProductFile');
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
		$this->productFileTable->reset();
	}
}
