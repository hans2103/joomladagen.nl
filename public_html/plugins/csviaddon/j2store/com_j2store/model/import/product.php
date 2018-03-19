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
class Product extends \RantaiImportEngine
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
	 * Product table
	 *
	 * @var    \J2StoreTableProduct
	 * @since  7.3.0
	 */
	private $productTable;

	/**
	 * Joomla content table
	 *
	 * @var    \J2StoreTableContent
	 * @since  7.3.0
	 */
	private $contentTable;

	/**
	 * Variant table
	 *
	 * @var    \J2StoreTableContent
	 * @since  7.3.0
	 */
	private $variantTable;

	/**
	 * Product image table
	 *
	 * @var    \J2StoreTableProductimage
	 * @since  7.3.0
	 */
	private $imageTable;

	/**
	 * The image helper
	 *
	 * @var    \CsviHelperImage
	 * @since  7.3.0
	 */
	protected $imageHelper;

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
					case 'manufacturer_name':
						$this->setState('manufacturer_id', $this->helper->getManufacturerId($value));
						break;
					case 'vendor_user_email':
						$this->setState('vendor_id', $this->helper->getVendorId($value));
						break;
					case 'category_path':
						$categoryId = $this->helper->getCategoryId($value);
						$this->setState('catid', $categoryId);
						break;
					case 'taxprofile_name':
						$this->setState('taxprofile_id', $this->helper->getTaxProfileId($value));
						break;
					case 'enabled':
					case 'is_master':
					case 'manage_stock':
					case 'quantity_restriction':
					case 'shipping':
					case 'visibility':
						switch (strtolower($value))
						{
							case 'n':
							case 'no':
							case '0':
								$value = 0;
								break;
							default:
								$value = 1;
								break;
						}

						$this->setState($name, $value);
						break;
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		$field = $selectField = 'j2store_product_id';
		$table = '#__j2store_products';

		if ($this->template->get('update_based_on', 'sku'))
		{
			$field = $this->template->get('update_based_on', 'sku');

			$productFields = $this->productTable->getFields();
			$variantFields = $this->variantTable->getFields();

			if ($this->getState($field, false))
			{
				if (isset($productFields[$field]))
				{
					$this->productTable->setKeyName($field);
					$this->variantTable->setKeyName('product_id');
					$table = '#__j2store_products';
					$selectField = "j2store_product_id";
				}
				elseif (isset($variantFields[$field]))
				{
					$this->productTable->setKeyName('j2store_product_id');
					$this->variantTable->setKeyName($field);
					$table = '#__j2store_variants';
					$selectField = "product_id";
				}
			}
		}

		// If no category is found set to default
		if (!$this->getState('catid', false))
		{
			$this->setState('catid', 2);
		}

		// If no alias set use the title field
		$requiredField = $this->getState($field, false);

		if (!$requiredField)
		{
			$this->loaded = false;
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_NO_REQUIRED_FIELD_FOUND', $field));
		}
		else
		{
			$this->recordIdentity = $this->getState($field, '');
			$this->loaded = true;

			if (!$this->getState('j2store_product_id', false))
			{
				$this->setState('j2store_product_id', $this->helper->getProductId($field, $this->getState($field, ''), $table, $selectField));
			}

			if (!$this->getState('title', false) && !$this->getState('j2store_product_id', false))
			{
				$this->setState('title', $this->getState($field, false));
			}

			if (!$this->getState('alias', false) && $this->getState('title', false))
			{
				$alias = \JFilterOutput::stringURLSafe($this->getState('title', false));
				$this->setState('alias', $alias);
			}

			if ($this->getState('alias', false) && $this->getState('catid', false))
			{
				$this->setState('id', $this->helper->getContentId($this->getState('alias', false), $this->getState('catid', false)));
			}

			$alias = $this->getState('alias', false);

			if (!$this->template->get('products_existing_content', false) && $alias && !$this->getState('j2store_product_id', false))
			{
				if (!$this->contentTable->checkAlias($alias))
				{
					$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_PRODUCT_ALIAS_EXISTS', $alias));

					$this->loaded = false;
				}
			}

			if ($this->productTable->load($this->getState($field, false)) || $this->variantTable->load($this->getState($field, false)))
			{
				if (!$this->template->get('overwrite_existing_data'))
				{
					$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_PRODUCT_SKU', $this->getState($field, '')));
					$this->loaded = false;
				}
				else
				{
					$this->setState('j2store_variant_id', $this->variantTable->get('j2store_variant_id', 0));
					$this->setState('j2store_product_id', $this->getState('j2store_product_id', 0));
					$this->setState('id', $this->getState('id', 0));
				}
			}
			else
			{
				$productId = $this->getState('j2store_product_id', false);

				if (!$this->getState($field, false) && empty($productId))
				{
					$this->log->addStats('incorrect', 'COM_CSVI_DEBUG_NO_SKU');
					$this->log->add(\JText::_('COM_CSVI_DEBUG_NO_SKU'));

					$this->loaded = false;
				}
				else
				{
					// Product is not found so we need to reset to the primary key field
					$this->productTable->setKeyName('j2store_product_id');
					$this->variantTable->setKeyName('j2store_variant_id');
					$this->contentTable->setKeyName('id');
					$this->log->add(\JText::sprintf('COM_CSVI_DEBUG_PROCESS_SKU', $this->recordIdentity), false);
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

		if (!$this->getState('j2store_product_id', false) && $this->template->get('ignore_non_exist'))
		{
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->recordIdentity));
		}
		else
		{
			$this->productTable->load($this->getState('j2store_product_id', false));

			if (!$this->getState('alias', false) && $this->getState('title', false))
			{
				$alias = \JFilterOutput::stringURLSafe($this->getState('title', false));
				$this->setState('alias', $alias);
			}

			if (!$this->getState('product_source_id', false))
			{
				$this->setState('product_source_id', $this->helper->getProductSourceId($this->getState('j2store_product_id', false)));
			}

			// First step is to save the product as a Joomla article
			$this->storeJoomlaContent();
			$this->log->add('Store product as Joomla content', false);

			if (!$this->getState('product_source', false))
			{
				$this->productTable->product_source = 'com_content';
			}

			if (!$this->getState('product_type', false))
			{
				$this->productTable->product_type = 'simple';
			}

			// Set the params for a product
			$this->setParams();

			if (!$this->getState('j2store_product_id', false))
			{
				if (!$this->getState('created_by'))
				{
					$this->productTable->created_by = $this->userId;
				}

				if (!$this->getState('created_on'))
				{
					$this->productTable->created_on = $this->date->toSql();
				}

				if (!$this->getState('taxprofile_id'))
				{
					$this->productTable->taxprofile_id = 0;
				}

				if (!$this->getState('vendor_id'))
				{
					$this->productTable->vendor_id = 0;
				}

				if (!$this->getState('manufacturer_id'))
				{
					$this->productTable->manufacturer_id = 0;
				}

				if (!$this->getState('params'))
				{
					$params = '{"product_css_class":""}';

					if ($this->getState('product_type', false) === 'downloadable')
					{
						$params = '{"product_css_class":"","download_limit":"","download_expiry":""}';
					}

					$this->productTable->params = $params;
					$this->setState('params', $params);
				}
			}
			else
			{
				if (!$this->getState('modified_on', false))
				{
					$this->productTable->modified_on = $this->date->toSql();
				}

				if (!$this->getState('modified_by'))
				{
					$this->productTable->modified_by = $this->userId;
				}
			}

			if ($this->getState('up_sells', false))
			{
				$newUpSellProductIds = array();
				$relatedUpSellProducts = explode('|', $this->getState('up_sells', false));

				foreach ($relatedUpSellProducts as $product)
				{
					$productId = $this->helper->getProductId('sku', $product);
					$newUpSellProductIds[] = $productId;
				}

				$this->setState('up_sells', implode(',', $newUpSellProductIds));
				$this->log->add('Product up_sells added');
			}

			if ($this->getState('cross_sells', false))
			{
				$newCrossSellProductIds = array();
				$relatedCrossSellProducts = explode('|', $this->getState('cross_sells', false));

				foreach ($relatedCrossSellProducts as $crossSellProduct)
				{
					$crossProductId = $this->helper->getProductId('sku', $crossSellProduct);
					$newCrossSellProductIds[] = $crossProductId;
				}

				$this->setState('cross_sells', implode(',', $newCrossSellProductIds));
				$this->log->add('Product cross_sells added');
			}

			// Set the default value
			$this->productTable->enabled = 1;

			if (strtoupper($this->getState('product_delete')) === 'Y')
			{
				if (!$this->getState('j2store_product_id', false))
				{
					$this->log->addStats('incorrect', 'COM_CSVI_NO_PRODUCT_FOUND_TO_DELETE');

					return false;
				}

				$this->deleteProduct();
			}
			else
			{
				// Data must be in an array
				$productData = ArrayHelper::fromObject($this->state);

				$this->productTable->bind($productData);
				$this->productTable->check();

				try
				{
					$this->productTable->save($productData);
					$this->log->add('Product added successfully', false);
					$this->setState('product_id', $this->productTable->j2store_product_id);
					$this->storeProductVariant();
					$this->storeProductQuantity();
					$this->storeProductImages();
				}
				catch (\Exception $e)
				{
					$this->log->add('Cannot add product. Error: ' . $e->getMessage(), false);
					$this->log->addStats('incorrect', $e->getMessage());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Set product params
	 *
	 * @return  void.
	 *
	 * @since   7.3.0
	 */
	private function setParams()
	{
		if (!$this->getState('params'))
		{
			$paramsFields = array('product_css_class');

			if ($this->getState('product_type', 'simple') === 'downloadable')
			{
				$paramsFields = array('product_css_class', 'download_limit', 'download_expiry');
			}

			$params = json_decode($this->productTable->params);

			if (!is_object($params))
			{
				$params = new \stdClass;
			}

			foreach ($paramsFields as $field)
			{
				$params->$field = $this->getState($field, '');

				if (!$this->getState($field, false))
				{
					$params->$field = '';
				}
			}

			$this->setState('params', json_encode($params));
			$this->productTable->params = json_encode($params);
		}
	}

	/**
	 * Delete product
	 *
	 * @return  void.
	 *
	 * @since   7.3.0
	 */
	private function deleteProduct()
	{
		$variantId = $this->helper->getVariantId($this->getState('sku', false));
		$this->productTable->delete($this->getState('j2store_product_id', false));
		$this->contentTable->delete($this->getState('product_source_id', false));
		$this->variantTable->delete($variantId);

		$query = $this->db->getQuery(true)
			->delete($this->db->quoteName('#__j2store_productimages'))
			->where($this->db->quoteName('product_id') . '=' . (int) $this->getState('j2store_product_id', false));
		$this->db->setQuery($query)->execute();
		$this->log->add('Deleted product images');
		$this->log->addStats('delete', 'COM_CSVI_TABLE_J2STORETABLEPRODUCTIMAGE_DELETED');

		$query->clear()
			->delete($this->db->quoteName('#__j2store_productquantities'))
			->where($this->db->quoteName('variant_id') . '=' . (int) $variantId);
		$this->db->setQuery($query)->execute();
		$this->log->add('Deleted product quantity', false);
		$this->log->addStats('delete', 'COM_CSVI_PRODUCT_QUANTITY_DELETE');

		$query->clear()
			->delete($this->db->quoteName('#__j2store_productfiles'))
			->where($this->db->quoteName('product_id') . '=' . (int) $this->getState('j2store_product_id', false));
		$this->db->setQuery($query)->execute();
		$this->log->add('Deleted product files', false);
		$this->log->addStats('delete', 'COM_CSVI_PRODUCT_FILES_DELETE');

		$query->clear()
			->delete($this->db->quoteName('#__j2store_product_filters'))
			->where($this->db->quoteName('product_id') . '=' . (int) $this->getState('j2store_product_id', false));
		$this->db->setQuery($query)->execute();
		$this->log->add('Deleted product filters', false);
		$this->log->addStats('delete', 'COM_CSVI_PRODUCT_FILTERS_DELETE');

		$query->clear()
			->delete($this->db->quoteName('#__j2store_product_options'))
			->where($this->db->quoteName('product_id') . '=' . (int) $this->getState('j2store_product_id', false));
		$this->db->setQuery($query)->execute();
		$this->log->add('Deleted product options', false);
		$this->log->addStats('delete', 'COM_CSVI_PRODUCT_OPTIONS_DELETE');

		$query->clear()
			->delete($this->db->quoteName('#__j2store_productprice_index'))
			->where($this->db->quoteName('product_id') . '=' . (int) $this->getState('j2store_product_id', false));
		$this->db->setQuery($query)->execute();
		$this->log->add('Deleted product price index', false);
		$this->log->addStats('delete', 'COM_CSVI_PRODUCT_PRICEINDEX_DELETE');

		$query->clear()
			->delete($this->db->quoteName('#__j2store_product_variant_optionvalues'))
			->where($this->db->quoteName('variant_id') . '=' . (int) $variantId);
		$this->db->setQuery($query)->execute();
		$this->log->add('Deleted product variant option values', false);
		$this->log->addStats('delete', 'COM_CSVI_PRODUCT_VARIANT_OPTION_VALUES_DELETE');
	}

	/**
	 * Store product as a Joomla article
	 *
	 * @return  void.
	 *
	 * @since   7.3.0
	 */
	private function storeJoomlaContent()
	{
		$this->contentTable->load($this->getState('id', false));

		if (!$this->getState('catid', false))
		{
			$this->contentTable->catid = 2;
		}

		if (!$this->getState('enabled', false))
		{
			$this->contentTable->state = 1;
		}

		if (!$this->getState('modified_on', false))
		{
			$this->contentTable->modified = $this->date->toSql();
		}

		if (!$this->getState('modified_by'))
		{
			$this->contentTable->modified_by = $this->userId;
		}

		if (!$this->getState('j2store_product_id', false))
		{
			if (!$this->getState('created_by'))
			{
				$this->contentTable->created_by = $this->userId;
			}

			if (!$this->getState('created_on'))
			{
				$this->contentTable->created = $this->date->toSql();
			}
		}

		// Data must be in an array
		$data = ArrayHelper::fromObject($this->state);
		$this->contentTable->bind($data);
		$this->contentTable->check();

		try
		{
			$this->contentTable->store();
			$this->setState('product_source_id', $this->contentTable->id);

			if ($this->template->get('products_existing_content', false) && !$this->contentTable->checkAlias($this->getState('alias', false)))
			{
				$this->log->add('Product added and linked to existing Joomla content', false);
			}
			else
			{
				$this->log->add('Added product as a Joomla article', false);
			}
		}
		catch (\Exception $e)
		{
			$this->log->add('Cannot add product as Joomla content. Error: ' . $e->getMessage(), false);
			$this->log->addStats('incorrect', $e->getMessage());
		}
	}

	/**
	 * Store product as a variant
	 *
	 * @return  void.
	 *
	 * @since   7.3.0
	 */
	private function storeProductVariant()
	{
		$this->variantTable->load($this->getState('j2store_variant_id', false));

		if (!$this->getState('variant_id', false))
		{
			if (!$this->getState('created_by'))
			{
				$this->variantTable->created_by = $this->userId;
			}

			if (!$this->getState('created_on'))
			{
				$this->variantTable->created_on = $this->date->toSql();
			}

			if (!$this->getState('upc'))
			{
				$this->variantTable->upc = '';
			}

			if ($this->getState('params'))
			{
				$this->variantTable->params = $this->getState('params');
			}

			$arrayDefaultFields = array('length', 'width', 'height', 'length_class_id', 'weight', 'weight_class_id', 'manage_stock', 'min_sale_qty',
				'use_store_config_min_sale_qty', 'max_sale_qty', 'use_store_config_max_sale_qty', 'notify_qty', 'use_store_config_notify_qty',
				'availability');

			foreach ($arrayDefaultFields as $defaultFields)
			{
				$this->variantTable->$defaultFields = 0;

				if ($this->getState($defaultFields, false))
				{
					$this->variantTable->$defaultFields = $this->getState($defaultFields, false);
				}
			}
		}
		else
		{
			if (!$this->getState('modified_on', false))
			{
				$this->variantTable->modified_on = $this->date->toSql();
			}

			if (!$this->getState('modified_by'))
			{
				$this->variantTable->modified_by = $this->userId;
			}
		}

		if (!$this->getState('pricing_calculator', false))
		{
			$this->variantTable->pricing_calculator = 'standard';
		}

		// Set the default value
		$this->variantTable->is_master = 1;

		$variantData = ArrayHelper::fromObject($this->state);
		$this->variantTable->bind($variantData);
		$this->variantTable->check();

		try
		{
			$this->variantTable->save($variantData);
			$this->log->add('Product variant processed', false);
			$this->setState('variant_id', $this->variantTable->j2store_variant_id);
		}
		catch (\Exception $e)
		{
			$this->log->add('Cannot add product as variant. Error: ' . $e->getMessage(), false);
			$this->log->addStats('incorrect', $e->getMessage());
		}
	}

	/**
	 * Store product quantity
	 *
	 * @return  true if everything Ok false otherwise.
	 *
	 * @since   7.3.0
	 */
	private function storeProductQuantity()
	{
		$productQuantity = $this->getState('quantity', 0);
		$variantId       = $this->getState('variant_id', false);

		if ($variantId === false)
		{
			return false;
		}

		$query = $this->db->getQuery(true)
			->delete($this->db->quoteName('#__j2store_productquantities'))
			->where($this->db->quoteName('variant_id') . '=' . (int) $variantId);
		$this->db->setQuery($query)->execute();
		$this->log->add('Delete variant quantity before inserting new values');

		$query->clear()
			->insert($this->db->quoteName('#__j2store_productquantities'))
			->columns($this->db->quoteName(array('variant_id', 'quantity')))
			->values((int) $variantId . ',' . (int) $productQuantity);
		$this->db->setQuery($query)->execute();
		$this->log->add('Insert the variant quantity');

		return true;
	}

	/**
	 * Process media files.
	 *
	 * @return  bool Returns true on OK | False on failure.
	 *
	 * @since   7.3.0
	 *
	 * @throws  \Exception
	 */
	private function storeProductImages()
	{
		$mainImage              = $this->getState('main_image', false);
		$generateImage          = $this->template->get('auto_generate_image_name', false);
		$productFullImageOutput = '';

		if ($mainImage || $generateImage)
		{
			if ($generateImage)
			{
				$productFullImageOutput = $this->createImageName();
			}

			// Image handling
			$this->imageHelper = new \CsviHelperImage($this->template, $this->log, $this->csvihelper);
			$thumbImage        = $this->getState('thumb_image', false);
			$productId         = $this->getState('product_id', false);
			$max_width         = $this->template->get('resize_max_width', 1024);
			$max_height        = $this->template->get('resize_max_height', 768);
			$imgPath           = $this->template->get('file_location_product_files', false);

			// Delete existing product images to do a fresh insert
			$query = $this->db->getQuery(true)
				->delete($this->db->quoteName('#__j2store_productimages'))
				->where($this->db->quoteName('product_id') . '=' . $productId);
			$this->db->setQuery($query)->execute();
			$this->log->add('Delete product images');

			if ($mainImage)
			{
				$imagePaths = $this->getImageOriginalFullPath($mainImage);
				$original   = $imagePaths['original'];
				$fullPath   = $imagePaths['fullpath'];

				if ($this->template->get('process_image', false))
				{
					if ($generateImage)
					{
						$fileDetails = $this->imageHelper->processImage($original, $fullPath, $productFullImageOutput);
					}
					else
					{
						$fileDetails = $this->imageHelper->processImage($original, $fullPath);
					}
				}
				else
				{
					$fileDetails['exists']      = true;
					$fileDetails['isimage']     = $this->imageHelper->isImage(JPATH_SITE . '/' . $mainImage);
					$fileDetails['name']        = $mainImage;
					$fileDetails['output_name'] = basename($mainImage);

					if (file_exists(JPATH_SITE . '/' . $mainImage))
					{
						$fileDetails['mime_type'] = $this->imageHelper->findMimeType($mainImage);
					}
					else
					{
						$fileDetails['mime_type'] = '';
					}

					$fileDetails['output_path'] = $fullPath;
				}

				if ($fileDetails['exists'])
				{
					$media                = array();
					$media['product_id']  = $productId;
					$media['main_image']  = (empty($fileDetails['output_path'])) ? $fileDetails['output_name'] : $fileDetails['output_path'] . $fileDetails['output_name'];
					$media['thumb_image'] = '';

					if (($fileDetails['isimage']) && $this->template->get('thumb_create', false))
					{
						if ($thumbImage)
						{
							// Check if the image contains the image path
							$dirname = dirname($thumbImage);

							if (strpos($imgPath . 'thumbs/', $dirname) !== false)
							{
								// Collect rest of folder path if it is more than image default path
								$imageLeftPath = str_replace($imgPath, '', $dirname . '/');
								$imageThumb    = basename($thumbImage);

								if ($imageLeftPath)
								{
									$thumbImage = $imageLeftPath . $imageThumb;
								}
							}
						}

						$thumbSizes = getimagesize(JPATH_SITE . '/' . $media['main_image']);

						if (empty($thumbImage) || $generateImage)
						{
							// Get the subfolder structure
							$thumbPath  = str_ireplace($imgPath, '', $fullPath);
							$thumbImage = $thumbPath . 'thumbs/' . basename($media['main_image']);
						}
						else
						{
							// Check if we are not overwriting any large images
							$thumbPathParts = pathinfo($thumbImage);

							if ($thumbPathParts['dirname'] === '.')
							{
								$this->log->addStats('incorrect', 'COM_CSVI_THUMB_OVERWRITE_FULL');
								$thumbImage = false;
							}
						}

						if ($thumbImage && ($thumbSizes[0] < $max_width || $thumbSizes[1] < $max_height))
						{
							$media['thumb_image'] = $this->imageHelper->createThumbnail($media['main_image'], $imgPath, $thumbImage);
						}
						else
						{
							$this->log->addStats('incorrect', \JText::sprintf('COM_CSVI_THUMB_TOO_BIG', $max_width, $max_height, $thumbSizes[0], $thumbSizes[1]));
							$this->log->add('Thumbnail is bigger than maximums set', false);
							$media['thumb_image'] = '';
						}
					}
					else
					{
						$media['thumb_image'] = empty($thumbImage) ? $media['main_image'] : $thumbImage;

						if (0 === strpos($media['thumb_image'], 'http') && null !== $thumbImage)
						{
							$media['thumb_image'] = $thumbImage;
						}
					}
				}
				else
				{
					$media['thumb_image'] = '';
				}

				// Check for additional images
				$additionalImages          = explode('|', $this->getState('additional_images', false));
				$additionalImagesNew       = new \stdClass;
				$additionalFullImageOutput = '';

				if (!$this->getState('additional_images', false))
				{
					$media['additional_images'] = '[""]';
				}
				else
				{
					foreach ($additionalImages as $key => $additionalImage)
					{
						$additionalImage = trim($additionalImage);

						if (count($additionalImages) === 1)
						{
							$imgCounter = 1;
						}
						else
						{
							$imgCounter = $key + 1;
						}

						if ($generateImage)
						{
							$additionalFullImageOutput = $this->createImageName($imgCounter);
						}

						$additionalImagePaths  = $this->getImageOriginalFullPath($additionalImage);
						$additionalOriginal    = $additionalImagePaths['original'];
						$additionalFullPath    = $additionalImagePaths['fullpath'];
						$additionalFileDetails = array();

						if ($this->template->get('process_image', false))
						{
							if ($generateImage)
							{
								$additionalFileDetails = $this->imageHelper->processImage($additionalOriginal, $additionalFullPath, $additionalFullImageOutput);
							}
							else
							{
								$additionalFileDetails = $this->imageHelper->processImage($additionalOriginal, $additionalFullPath);
							}
						}
						else
						{
							$additionalFileDetails['exists']      = true;
							$additionalFileDetails['isimage']     = $this->imageHelper->isImage(JPATH_SITE . '/' . $additionalImage);
							$additionalFileDetails['name']        = $additionalImage;
							$additionalFileDetails['output_name'] = basename($additionalImage);

							if (file_exists(JPATH_SITE . '/' . $additionalImage))
							{
								$additionalFileDetails['mime_type'] = $this->imageHelper->findMimeType($additionalImage);
							}
							else
							{
								$additionalFileDetails['mime_type'] = '';
							}

							$additionalFileDetails['output_path'] = $fullPath;
						}

						if ($additionalFileDetails['exists'])
						{
							if ($this->template->get('full_watermark', 'image') && $fileDetails['isimage'])
							{
								$this->imageHelper->addWatermark(JPATH_SITE . '/' . $additionalImage);
							}

							$additionalImagesNew->$key = (empty($additionalFileDetails['output_path'])) ? $additionalFileDetails['output_name'] : $additionalFileDetails['output_path'] . $additionalFileDetails['output_name'];
						}
					}

					$media['additional_images'] = json_encode($additionalImagesNew);
				}

				$this->imageTable->load($productId);

				// Bind the media data
				$this->imageTable->bind($media);

				// Check if the media image already exists
				$this->imageTable->check();

				try
				{
					$this->imageTable->store();

					$this->log->add('Image added for the product', false);

					if ($this->template->get('full_watermark', 'image') && $fileDetails['isimage'])
					{
						$this->imageHelper->addWatermark(JPATH_SITE . '/' . $media['main_image']);
					}
				}
				catch (\Exception $e)
				{
					$this->log->add('Cannot add product image. Error: ' . $e->getMessage(), false);
					$this->log->addStats('incorrect', $e->getMessage());

					return false;
				}

				$this->imageTable->reset();
			}
		}

		return true;
	}

	/**
	 * Create original and fullpath of image
	 *
	 * Check if the user wants to have CSVI J2Store create the image names if so
	 * create the image names without path.
	 *
	 * @param   int  $imageName  The name of the image
	 *
	 * @return  array  The path of the image.
	 *
	 * @since   7.3.0
	 */
	private function getImageOriginalFullPath($imageName)
	{
		$image   = array();
		$imgPath = $this->template->get('file_location_product_files', false);
		$imgPath = str_replace('\\', '/', $imgPath);

		if (substr($imgPath, -1) !== '/')
		{
			$imgPath .= '/';
		}

		if ($this->imageHelper->isRemote($imageName))
		{
			$image['original'] = $imageName;
			$image['fullpath'] = $imgPath;
		}
		else
		{
			$imageName = str_replace('\\', '/', $imageName);

			// Check if the image contains the image path
			$dirname = dirname($imageName) . '/';

			if (strpos($dirname, $imgPath) !== false && $imgPath !== '/')
			{
				// Collect rest of folder path if it is more than image default path
				$imageLeftPath = str_replace($imgPath, '', $dirname . '/');
				$imageName     = basename($imageName);

				if ($imageLeftPath)
				{
					$imageName = $imageLeftPath . $imageName;
				}
			}

			$image['original'] = $imgPath . $imageName;

			// Get subfolders
			$pathParts         = pathinfo($image['original']);
			$image['fullpath'] = $pathParts['dirname'] . '/';
		}

		return $image;
	}

	/**
	 * Create image name.
	 *
	 * Check if the user wants to have CSVI J2Store create the image names if so
	 * create the image names without path.
	 *
	 * @param   int  $ordering  The number to apply to a generated image name.
	 *
	 * @return  string  The name of the image.
	 *
	 * @since   7.3.0
	 */
	private function createImageName($ordering = 0)
	{
		$this->log->add('Generate image name', false);

		// Create extension
		$ext = $this->template->get('autogenerateext');

		// Check if the user wants to convert the images to a different type
		switch ($this->template->get('type_generate_image_name'))
		{
			case 'product_sku':
				$this->log->add('Create name from product SKU', false);

				if (!$this->getState('sku', false))
				{
					$this->log->addStats('error', 'COM_CSVI_CANNOT_FIND_PRODUCT_SKU');

					return false;
				}

				$name = $this->getState('sku');
				break;
			case 'product_name':
				$this->log->add('Create name from product name', false);

				if (!$this->getState('title', false))
				{
					$this->log->addStats('error', 'COM_CSVI_CANNOT_FIND_PRODUCT_NAME');

					return false;
				}

				$name = str_replace(' ', '_', $this->getState('title', false));
				break;
			case 'product_id':
				$this->log->add('Create name from product ID', false);

				if (!$this->getState('j2store_product_id'))
				{
					$this->log->addStats('error', 'COM_CSVI_CANNOT_FIND_PRODUCT_ID');

					return false;
				}

				$name = $this->getState('j2store_product_id');
				break;
			case 'random':
				$this->log->add('Create a random name', false);
				$name = mt_rand();
				break;
			default:
				$this->log->addStats('error', 'COM_CSVI_CANNOT_FIND_PRODUCT_SKU');

				return false;
				break;
		}

		// Build the new name
		$imageName = $name . '.' . $ext;

		if ($ordering > 0)
		{
			$imageName = $name . '_' . $ordering . '.' . $ext;
		}

		$this->log->add('Created image name: ' . $imageName, false);

		// Check if the user is supplying image data
		if (!$this->getState('main_image', false))
		{
			$this->setState('main_image', $imageName);
		}

		return $imageName;
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
		$this->productTable = $this->getTable('Product');
		$this->contentTable = $this->getTable('Content');
		$this->variantTable = $this->getTable('Variant');
		$this->imageTable   = $this->getTable('ProductImage');
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
		$this->productTable->reset();
		$this->contentTable->reset();
		$this->variantTable->reset();
		$this->imageTable->reset();
	}
}
