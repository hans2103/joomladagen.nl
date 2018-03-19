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
 * Export J2Store order items fields.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Orderitem extends \CsviModelExports
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
					case 'orderitem_taxprofile_name':
						$userFields[] = $this->db->quoteName('j2store_orderitems.orderitem_taxprofile_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_orderitems.orderitem_taxprofile_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_orderitems.orderitem_taxprofile_id');
						}
						break;
					case 'sku':
						$userFields[] = $this->db->quoteName('j2store_orderitems.variant_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_orderitems.variant_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_orderitems.variant_id');
						}
						break;
					case 'thumb_image':
					case 'shipping':
						$userFields[] = $this->db->quoteName('j2store_orderitems.orderitem_params');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_orderitems.orderitem_params');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_orderitems.orderitem_params');
						}
						break;
					case 'j2store_orderitem_id':
					case 'order_id':
					case 'orderitem_type':
					case 'cart_id':
					case 'cartitem_id':
					case 'product_id':
					case 'product_type':
					case 'variant_id':
					case 'vendor_id':
					case 'orderitem_sku':
					case 'orderitem_name':
					case 'orderitem_attributes':
					case 'orderitem_quantity':
					case 'orderitem_taxprofile_id':
					case 'orderitem_per_item_tax':
					case 'orderitem_tax':
					case 'orderitem_discount':
					case 'orderitem_discount_tax':
					case 'orderitem_price':
					case 'orderitem_option_price':
					case 'orderitem_finalprice':
					case 'orderitem_finalprice_with_tax':
					case 'orderitem_finalprice_without_tax':
					case 'orderitem_params':
					case 'created_on':
					case 'created_by':
					case 'orderitem_weight':
					case 'orderitem_weight_total':
						$userFields[] = $this->db->quoteName('j2store_orderitems.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_orderitems.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_orderitems.' . $field->field_name);
						}
						break;
					case 'vendor_user_email':
						$userFields[] = $this->db->quoteName('j2store_orderitems.vendor_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_orderitems.vendor_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_orderitems.vendor_id');
						}
						break;
					case 'j2store_orderitemattribute_id':
					case 'orderitem_id':
					case 'productattributeoption_id':
					case 'productattributeoptionvalue_id':
					case 'orderitemattribute_name':
					case 'orderitemattribute_value':
					case 'orderitemattribute_prefix':
					case 'orderitemattribute_price':
					case 'orderitemattribute_code':
					case 'orderitemattribute_type':
						$userFields[] = $this->db->quoteName('j2store_orderitemattributes.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_orderitemattributes.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_orderitemattributes.' . $field->field_name);
						}
						break;
					case 'name':
						$userFields[] = $this->db->quoteName('j2store_orderitems.created_by');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_orderitems.created_by');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_orderitems.created_by');
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
			$query->from($this->db->quoteName('#__j2store_orderitems', 'j2store_orderitems'));
			$query->leftJoin(
				$this->db->quoteName('#__j2store_orderitemattributes', 'j2store_orderitemattributes') . ' ON ' .
				$this->db->quoteName('j2store_orderitemattributes.orderitem_id') . ' = ' . $this->db->quoteName('j2store_orderitems.j2store_orderitem_id')
				);
			$query->leftJoin(
				$this->db->quoteName('#__j2store_orders', 'j2store_orders') . ' ON ' .
				$this->db->quoteName('j2store_orders.order_id') . ' = ' . $this->db->quoteName('j2store_orderitems.order_id')
			);

			// Filter by order status
			$orderstatus = $this->template->get('orderstatus', false);

			if ($orderstatus && $orderstatus[0] !== '')
			{
				$query->where($this->db->quoteName('j2store_orders.order_state_id') . ' IN (\'' . implode("','", $orderstatus) . '\')');
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
							case 'orderitem_taxprofile_name':
								$query->clear()
									->select($this->db->quoteName('taxprofile_name'))
									->from($this->db->quoteName('#__j2store_taxprofiles'))
									->where($this->db->quoteName('j2store_taxprofile_id') . ' = ' . (int) $record->orderitem_taxprofile_id);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
								break;
							case 'sku':
								$query->clear()
									->select($this->db->quoteName('sku'))
									->from($this->db->quoteName('#__j2store_variants'))
									->where($this->db->quoteName('j2store_variant_id') . ' = ' . (int) $record->variant_id);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
								break;
							case 'thumb_image':
								$itemParams = json_decode($record->orderitem_params, true);
								$fieldValue = $itemParams['thumb_image'];
								break;
							case 'shipping':
								$itemParams = json_decode($record->orderitem_params, true);
								$fieldValue = $itemParams['shipping'];
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
							case 'name':
								$query->clear()
									->select($this->db->quoteName('name'))
									->from($this->db->quoteName('#__users'))
									->where($this->db->quoteName('id') . ' = ' . (int) $record->created_by);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
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
}
