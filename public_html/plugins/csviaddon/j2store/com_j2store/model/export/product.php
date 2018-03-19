<?php
/**
 * @package     CSVI
 * @subpackage  J2Store
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace j2store\com_j2store\model\export;

defined('_JEXEC') or die;

/**
 * Export J2Store product fields.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Product extends \CsviModelExports
{
	/**
	 * Export the data.
	 *
	 * @return  void.
	 *
	 * @since   7.3.0
	 */
	protected function exportBody()
	{
		if (parent::exportBody())
		{
			// Build something fancy to only get the fieldnames the user wants
			$exportFields = $this->fields->getFields();

			// Group by fields
			$groupFields = json_decode($this->template->get('groupbyfields', '', 'string'));
			$groupBy     = array();

			if (isset($groupFields->name))
			{
				$groupByFields = array_flip($groupFields->name);
			}
			else
			{
				$groupByFields = array();
			}

			// Sort selected fields
			$sortFields = json_decode($this->template->get('sortfields', '', 'string'));
			$sortBy     = array();

			if (isset($sortFields->name))
			{
				$sortByFields = array_flip($sortFields->name);
			}
			else
			{
				$sortByFields = array();
			}

			// Fields which are needed for getting contents
			$userFields = array();

			foreach ($exportFields as $field)
			{
				switch ($field->field_name)
				{
					case 'title':
					case 'alias':
						$userFields[] = $this->db->quoteName('content.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('content.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('content.' . $field->field_name);
						}
						break;
					case 'category_path':
						$userFields[] = $this->db->quoteName('content.catid');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('content.catid');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('content.cat_id');
						}
						break;
					case 'quantity':
						$userFields[] = $this->db->quoteName('j2store_productquantities.quantity');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_productquantities.quantity');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_productquantities.quantity');
						}
						break;
					case 'manufacturer_name':
						$userFields[] = $this->db->quoteName('j2store_products.manufacturer_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_products.manufacturer_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_products.manufacturer_id');
						}
						break;
					case 'taxprofile_name':
						$userFields[] = $this->db->quoteName('j2store_products.taxprofile_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_products.taxprofile_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_products.taxprofile_id');
						}
						break;
					case 'product_css_class':
					case 'download_limit':
					case 'download_expiry':
						$userFields[] = $this->db->quoteName('j2store_products.params');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_products.params');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_products.params');
						}
						break;
					case 'vendor_user_email':
						$userFields[] = $this->db->quoteName('j2store_products.vendor_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_products.vendor_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_products.vendor_id');
						}
						break;
					case 'price':
					case 'product_id':
					case 'is_master':
					case 'sku':
					case 'upc':
					case 'pricing_calculator':
					case 'shipping':
					case 'length':
					case 'width':
					case 'height':
					case 'length_class_id':
					case 'weight':
					case 'weight_class_id':
					case 'manage_stock':
					case 'quantity_restriction':
					case 'min_out_qty':
					case 'use_store_config_min_out_qty':
					case 'min_sale_qty':
					case 'use_store_config_min_sale_qty':
					case 'max_sale_qty':
					case 'use_store_config_max_sale_qty':
					case 'notify_qty':
					case 'use_store_config_notify_qty':
					case 'availability':
					case 'sold':
					case 'allow_backorder':
					case 'isdefault_variant':
						$userFields[] = $this->db->quoteName('j2store_variants.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_variants.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_variants.' . $field->field_name);
						}
						break;
					case 'visibility':
					case 'j2store_product_id':
					case 'product_source':
					case 'product_source_id':
					case 'product_type':
					case 'taxprofile_id':
					case 'manufacturer_id':
					case 'vendor_id':
					case 'has_options':
					case 'addtocart_text':
					case 'enabled':
					case 'plugins':
					case 'params':
					case 'created_on':
					case 'created_by':
					case 'modified_on':
					case 'modified_by':
					case 'up_sells':
					case 'cross_sells':
					case 'productfilter_ids':
						$userFields[] = $this->db->quoteName('j2store_products.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_products.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_products.' . $field->field_name);
						}
						break;
					case 'main_image':
					case 'thumb_image':
					case 'additional_image':
						$userFields[] = $this->db->quoteName('j2store_productimages.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_productimages.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_productimages.' . $field->field_name);
						}
						break;
					case 'product_file_display_name':
					case 'product_file_save_name':
					case 'download_total':
						$userFields[] = $this->db->quoteName('j2store_productfiles.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_productfiles.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_productfiles.' . $field->field_name);
						}
						break;
					case 'custom':
						break;
					default:
						$userFields[] = $this->db->quoteName($field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName($field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName($field->field_name);
						}

						break;
				}
			}

			// Build the query
			$userFields = array_unique($userFields);
			$query      = $this->db->getQuery(true);
			$query->select(implode(",\n", $userFields));
			$query->from($this->db->quoteName('#__content', 'content'));
			$query->rightJoin(
				$this->db->quoteName('#__j2store_products', 'j2store_products') . ' ON ' .
				$this->db->quoteName('j2store_products.product_source_id') . ' = ' . $this->db->quoteName('content.id')
			);
			$query->leftJoin(
				$this->db->quoteName('#__j2store_variants', 'j2store_variants') . ' ON ' .
				$this->db->quoteName('j2store_variants.product_id') . ' = ' . $this->db->quoteName('j2store_products.j2store_product_id')
			);
			$query->leftJoin(
				$this->db->quoteName('#__j2store_productquantities', 'j2store_productquantities') . ' ON ' .
				$this->db->quoteName('j2store_productquantities.variant_id') . ' = ' . $this->db->quoteName('j2store_variants.j2store_variant_id')
			);
			$query->leftJoin(
				$this->db->quoteName('#__j2store_productimages', 'j2store_productimages') . ' ON ' .
				$this->db->quoteName('j2store_productimages.product_id') . ' = ' . $this->db->quoteName('j2store_products.j2store_product_id')
			);
			$query->leftJoin(
				$this->db->quoteName('#__j2store_productfiles', 'j2store_productfiles') . ' ON ' .
				$this->db->quoteName('j2store_productfiles.product_id') . ' = ' . $this->db->quoteName('j2store_products.j2store_product_id')
			);

			// Filter by published state
			$published = $this->template->get('publish_state');

			if ($published !== '' && ($published == 1 || $published == 0))
			{
				$query->where('j2store_products.enabled = ' . (int) $published);
			}

			// Filter by product type
			$productType = $this->template->get('product_type');

			if ($productType !== '')
			{
				$query->where('j2store_products.product_type = ' . $this->db->quote($productType));
			}

			// Filter by product visibility
			$visibility = $this->template->get('visibility');

			if ($visibility !== '' && ($visibility == 1 || $visibility == 0))
			{
				$query->where('j2store_products.visibility = ' . (int) $visibility);
			}

			// Filter by manufacturer
			$manufacturer = $this->template->get('manufacturers');

			if ($manufacturer && 'none' !== $manufacturer[0] && 0 !== count($manufacturer))
			{
				$query->where('j2store_products.manufacturer_id = ' . (int) $manufacturer);
			}

			// Filter by tax profile
			$taxProfile = $this->template->get('taxprofile');

			if ($taxProfile && 'none' !== $taxProfile[0] && 0 !== count($taxProfile))
			{
				$query->where('j2store_products.taxprofile_id = ' . (int) $taxProfile);
			}

			// Include/exclude by product SKU
			$inclproductskufilter = $this->template->get('incl_productskufilter');

			// Filter by product SKU
			$productskufilter = $this->template->get('productskufilter');

			if ($productskufilter)
			{
				$productskufilter .= ',';

				if (strpos($productskufilter, ','))
				{
					$skus     = explode(',', $productskufilter);
					$wildcard = '';
					$normal   = array();

					foreach ($skus as $sku)
					{
						if (!empty($sku))
						{
							if (strpos($sku, '%'))
							{
								// Check if filter is for include or exclude of product SKU
								if ($inclproductskufilter)
								{
									$wildcard .= $this->db->quoteName('j2store_variants.sku') . ' LIKE ' . $this->db->quote($sku) . ' OR ';
								}
								else
								{
									$wildcard .= $this->db->quoteName('j2store_variants.sku') . ' NOT LIKE ' . $this->db->quote($sku) . ' OR ';
								}
							}
							else
							{
								$normal[] = $this->db->quote($sku);
							}
						}
					}

					if (substr($wildcard, -3) === 'OR ')
					{
						$wildcard = substr($wildcard, 0, -4);
					}

					// If sku filter is include look for matching records, else exclude matching records
					if ($inclproductskufilter)
					{
						if (!empty($wildcard) && !empty($normal))
						{
							$query->where('(' . $wildcard . ' OR ' . $this->db->quoteName('j2store_variants.sku') . ' IN (' . implode(',', $normal) . '))');
						}
						elseif (!empty($wildcard))
						{
							$query->where('(' . $wildcard . ')');
						}
						elseif (!empty($normal))
						{
							$query->where('(' . $this->db->quoteName('j2store_variants.sku') . ' IN (' . implode(',', $normal) . '))');
						}
					}
					else
					{
						if (!empty($wildcard) && !empty($normal))
						{
							$query->where(
								'(' . $wildcard . ' OR ' . $this->db->quoteName('j2store_variants.sku') . ' NOT IN (' . implode(',', $normal) . '))'
							);
						}
						elseif (!empty($wildcard))
						{
							$query->where('(' . $wildcard . ')');
						}
						elseif (!empty($normal))
						{
							$query->where('(' . $this->db->quoteName('j2store_variants.sku') . ' NOT IN (' . implode(',', $normal) . '))');
						}
					}
				}
			}

			// Group the fields
			$groupBy = array_unique($groupBy);

			if (0 !== count($groupBy))
			{
				$query->group($groupBy);
			}

			// Sort set fields
			$sortBy = array_unique($sortBy);

			if (0 !== count($sortBy))
			{
				$query->order($sortBy);
			}

			// Add export limits
			$limits = $this->getExportLimit();

			// Execute the query
			$this->db->setQuery($query, $limits['offset'], $limits['limit']);
			$records = $this->db->getIterator();
			$this->log->add('Export query' . $query->__toString(), false);

			// Check if there are any records
			$logCount = $this->db->getNumRows();

			if ($logCount > 0)
			{
				foreach ($records as $record)
				{
					$this->log->incrementLinenumber();

					foreach ($exportFields as $field)
					{
						$fieldName = $field->field_name;
						$fieldValue = '';

						// Set the field value
						if (isset($record->$fieldName))
						{
							$fieldValue = $record->$fieldName;
						}
						
						// Process the field
						switch ($fieldName)
						{
							case 'manufacturer_name':
								$query->clear()
									->select($this->db->quoteName('company'))
									->from($this->db->quoteName('#__j2store_addresses'))
									->leftJoin(
										$this->db->quoteName('#__j2store_manufacturers') . ' ON ' .
										$this->db->quoteName('#__j2store_manufacturers.address_id') . ' = ' . $this->db->quoteName('#__j2store_addresses.j2store_address_id')
									)
									->where($this->db->quoteName('#__j2store_manufacturers.j2store_manufacturer_id') . ' = ' . (int) $record->manufacturer_id);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
								break;
							case 'taxprofile_name':
								$query->clear()
									->select($this->db->quoteName('taxprofile_name'))
									->from($this->db->quoteName('#__j2store_taxprofiles'))
									->where($this->db->quoteName('j2store_taxprofile_id') . ' = ' . (int) $record->taxprofile_id);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
								break;
							case 'vendor_user_email':
								$query->clear()
									->select($this->db->quoteName('email'))
									->from($this->db->quoteName('#__users'))
									->leftJoin(
										$this->db->quoteName('#__j2store_vendors') . ' ON ' .
										$this->db->quoteName('#__j2store_vendors.j2store_user_id') . ' = ' . $this->db->quoteName('#__users.id')
									)
									->where($this->db->quoteName('#__j2store_vendors.j2store_vendor_id') . ' = ' . (int) $record->vendor_id);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
								break;
							case 'category_path':
								$query->clear()
									->select($this->db->quoteName('path'))
									->from($this->db->quoteName('#__categories'))
									->where($this->db->quoteName('id') . ' = ' . (int) $record->catid);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
								break;
							case 'product_css_class':
							case 'download_limit':
							case 'download_expiry':
								$fieldValue = '';
								$params     = json_decode($record->params);

								if (isset($params->$fieldName))
								{
									$fieldValue = $params->$fieldName;
								}
								break;
							case 'additional_images':
								$fieldValue = '';
								$newImage   = array();

								if ($record->additional_images)
								{
									$images = json_decode($record->additional_images);

									foreach ($images as $image)
									{
										$newImage[] = $image;
									}

									$fieldValue = implode('|', $newImage);
								}
								break;
							case 'up_sells':
								$fieldValue = '';
								$upsellProductSKUs   = array();

								if ($record->up_sells)
								{
									$upsellProducts = explode(',', $record->up_sells);

									foreach ($upsellProducts as $upsellProduct)
									{
										$query = $this->db->getQuery(true)
											->select($this->db->quoteName('sku'))
											->from($this->db->quoteName('#__j2store_variants'))
											->where($this->db->quoteName('product_id') . ' = ' . (int) $upsellProduct);
										$this->db->setQuery($query);
										$upsellProductSKUs[] = $this->db->loadResult();
									}

									$fieldValue = implode('|', $upsellProductSKUs);
								}
								break;
							case 'cross_sells':
								$fieldValue = '';
								$crosssellProductSKUs   = array();

								if ($record->cross_sells)
								{
									$crosssellProducts = explode(',', $record->cross_sells);

									foreach ($crosssellProducts as $crosssellProduct)
									{
										$query = $this->db->getQuery(true)
											->select($this->db->quoteName('sku'))
											->from($this->db->quoteName('#__j2store_variants'))
											->where($this->db->quoteName('product_id') . ' = ' . (int) $crosssellProduct);
										$this->db->setQuery($query);
										$crosssellProductSKUs[] = $this->db->loadResult();
									}

									$fieldValue = implode('|', $crosssellProductSKUs);
								}
								break;
							case 'created_on':
							case 'modified_on':
								$fieldValue = $this->fields->getDateFormat($fieldName, $record->$fieldName, $field->column_header);
								break;
							case 'price':
								$fieldValue = $this->formatNumber($record->price);
								break;
							default:
								break;
						}

						// Store the field value
						$this->fields->set($field->csvi_templatefield_id, $fieldValue);
					}

					// Output the data
					$this->addExportFields();

					// Output the contents
					$this->writeOutput();
				}
			}
			else
			{
				$this->addExportContent(\JText::_('COM_CSVI_NO_DATA_FOUND'));

				// Output the contents
				$this->writeOutput();
			}
		}
	}

	/**
	 * Format a value to a number.
	 *
	 * @param   float  $fieldValue  The value to format as number.
	 *
	 * @return  string  The formatted number.
	 *
	 * @since   7.3.0
	 */
	private function formatNumber($fieldValue)
	{
		return number_format(
			$fieldValue,
			$this->template->get('export_price_format_decimal', 2, 'int'),
			$this->template->get('export_price_format_decsep'),
			$this->template->get('export_price_format_thousep')
		);
	}
}
