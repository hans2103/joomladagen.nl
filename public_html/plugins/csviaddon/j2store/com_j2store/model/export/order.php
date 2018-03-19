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
 * Export J2Store order export.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Order extends \CsviModelExports
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
			$userFields   = array();
			$userFields[] = $this->db->quoteName('j2store_orders.order_id');

			foreach ($exportFields as $field)
			{
				switch ($field->field_name)
				{
					case 'j2store_order_id':
					case 'order_id':
					case 'parent_id':
					case 'cart_id':
					case 'invoice_prefix':
					case 'invoice_number':
					case 'token':
					case 'user_id':
					case 'user_email':
					case 'order_total':
					case 'order_subtotal':
					case 'order_subtotal_ex_tax':
					case 'order_tax':
					case 'order_shipping':
					case 'order_shipping_tax':
					case 'order_discount':
					case 'order_discount_tax':
					case 'order_credit':
					case 'order_refund':
					case 'order_surcharge':
					case 'order_fees':
					case 'orderpayment_type':
					case 'transaction_id':
					case 'transaction_status':
					case 'transaction_details':
					case 'currency_id':
					case 'currency_code':
					case 'currency_value':
					case 'ip_address':
					case 'is_shippable':
					case 'is_including_tax':
					case 'customer_note':
					case 'customer_language':
					case 'customer_group':
					case 'order_state_id':
					case 'order_state':
					case 'order_params':
					case 'created_on':
					case 'created_by':
					case 'modified_on':
					case 'modified_by':
						$userFields[] = $this->db->quoteName('j2store_orders.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_orders.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_orders.' . $field->field_name);
						}
						break;
					case 'j2store_orderhistory_id':
					case 'notify_customer':
					case 'comment':
					case 'params':
						$userFields[] = $this->db->quoteName('j2store_orderhistories.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_orderhistories.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_orderhistories.' . $field->field_name);
						}
						break;
					case 'j2store_orderinfo_id':
					case 'billing_company':
					case 'billing_last_name':
					case 'billing_first_name':
					case 'billing_middle_name':
					case 'billing_phone_1':
					case 'billing_phone_2':
					case 'billing_fax':
					case 'billing_address_1':
					case 'billing_address_2':
					case 'billing_city':
					case 'billing_zone_name':
					case 'billing_country_name':
					case 'billing_zone_id':
					case 'billing_country_id':
					case 'billing_zip':
					case 'billing_tax_numbe':
					case 'shipping_company':
					case 'shipping_last_name':
					case 'shipping_first_name':
					case 'shipping_middle_name':
					case 'shipping_phone_1':
					case 'shipping_phone_2':
					case 'shipping_fax':
					case 'shipping_address_1':
					case 'shipping_address_2':
					case 'shipping_city':
					case 'shipping_zip':
					case 'shipping_zone_name':
					case 'shipping_country_name':
					case 'shipping_zone_id':
					case 'shipping_country_id':
					case 'shipping_id':
					case 'shipping_tax_number':
					case 'all_billing':
					case 'all_shipping':
					case 'all_payment':
						$userFields[] = $this->db->quoteName('j2store_orderinfos.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_orderinfos.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_orderinfos.' . $field->field_name);
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
			$query->from($this->db->quoteName('#__j2store_orders', 'j2store_orders'));
			$query->leftJoin(
				$this->db->quoteName('#__j2store_orderinfos', 'j2store_orderinfos') . ' ON ' .
				$this->db->quoteName('j2store_orderinfos.order_id') . ' = ' . $this->db->quoteName('j2store_orders.order_id')
			);
			$query->leftJoin(
				$this->db->quoteName('#__j2store_orderhistories', 'j2store_orderhistories') . ' ON ' .
				$this->db->quoteName('j2store_orderhistories.order_id') . ' = ' . $this->db->quoteName('j2store_orders.order_id')
			);

			// Filter by order number start
			$ordernostart = $this->template->get('ordernostart', 0, 'int');

			if ($ordernostart > 0)
			{
				$query->where($this->db->quoteName('j2store_orders.j2store_order_id') . ' >= ' . (int) $ordernostart);
			}

			// Filter by order number end
			$ordernoend = $this->template->get('ordernoend', 0, 'int');

			if ($ordernoend > 0)
			{
				$query->where($this->db->quoteName('j2store_orders.j2store_order_id') . ' <= ' . (int) $ordernoend);
			}

			// Filter by list of order numbers
			$orderlist = $this->template->get('orderlist');

			if ($orderlist)
			{
				$query->where($this->db->quoteName('j2store_orders.j2store_order_id') . ' IN (' . $orderlist . ')');
			}

			// Check for a pre-defined date
			$daterange = $this->template->get('orderdaterange', '');

			if ($daterange !== '')
			{
				$jdate       = \JFactory::getDate('now', 'UTC');
				$currentDate = $this->db->quote($jdate->format('Y-m-d'));

				switch ($daterange)
				{
					case 'lastrun':
						if (substr($this->template->getLastrun(), 0, 4) != '0000')
						{
							$query->where($this->db->quoteName('j2store_orders.created_on') . ' > ' . $this->db->quote($this->template->getLastrun()));
						}
						break;
					case 'yesterday':
						$query->where(
							'DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') = DATE_SUB(' . $currentDate . ', INTERVAL 1 DAY)');
						break;
					case 'thisweek':
						// Get the current day of the week
						$dayofweek = $jdate->__get('dayofweek');
						$offset = $dayofweek - 1;
						$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= DATE_SUB(' . $currentDate . ', INTERVAL ' . $offset . ' DAY)');
						$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') <= ' . $currentDate);
						break;
					case 'lastweek':
						// Get the current day of the week
						$dayofweek = $jdate->__get('dayofweek');
						$offset = $dayofweek + 6;
						$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= DATE_SUB(' . $currentDate . ', INTERVAL ' . $offset . ' DAY)');
						$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') <= DATE_SUB(' . $currentDate . ', INTERVAL ' . $dayofweek . ' DAY)');
						break;
					case 'thismonth':
						// Get the current day of the week
						$dayofmonth = $jdate->__get('day');
						$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= DATE_SUB(' . $currentDate . ', INTERVAL ' . $dayofmonth . ' DAY)');
						$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') <= ' . $currentDate);
						break;
					case 'lastmonth':
						// Get the current day of the week
						$dayofmonth = $jdate->__get('day');
						$month = date('n');
						$year = date('y');

						if ($month > 1)
						{
							$month--;
						}
						else
						{
							$month = 12;
							$year--;
						}

						$daysinmonth = date('t', mktime(0, 0, 0, $month, 25, $year));
						$offset = ($daysinmonth + $dayofmonth) - 1;

						$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= DATE_SUB(' . $currentDate . ', INTERVAL ' . $offset . ' DAY)');
						$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') <= DATE_SUB(' . $currentDate . ', INTERVAL ' . $dayofmonth . ' DAY)');
						break;
					case 'thisquarter':
						// Find out which quarter we are in
						$month = $jdate->__get('month');
						$year = date('Y');
						$quarter = ceil($month / 3);

						switch ($quarter)
						{
							case '1':
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= ' . $this->db->quote($year . '-01-01'));
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') < ' . $this->db->quote($year . '-04-01'));
								break;
							case '2':
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= ' . $this->db->quote($year . '-04-01'));
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') < ' . $this->db->quote($year . '-07-01'));
								break;
							case '3':
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= ' . $this->db->quote($year . '-07-01'));
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') < ' . $this->db->quote($year . '-10-01'));
								break;
							case '4':
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= ' . $this->db->quote($year . '-10-01'));
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') < ' . $this->db->quote(($year++) . '-01-01'));
								break;
						}
						break;
					case 'lastquarter':
						// Find out which quarter we are in
						$month = $jdate->__get('month');
						$year = date('Y');
						$quarter = ceil($month / 3);

						if ($quarter == 1)
						{
							$quarter = 4;
							$year--;
						}
						else
						{
							$quarter--;
						}

						switch ($quarter)
						{
							case '1':
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= ' . $this->db->quote($year . '-01-01'));
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') < ' . $this->db->quote($year . '-04-01'));
								break;
							case '2':
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= ' . $this->db->quote($year . '-04-01'));
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') < ' . $this->db->quote($year . '-07-01'));
								break;
							case '3':
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= ' . $this->db->quote($year . '-07-01'));
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') < ' . $this->db->quote($year . '-10-01'));
								break;
							case '4':
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= ' . $this->db->quote($year . '-10-01'));
								$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') < ' . $this->db->quote(($year++) . '-01-01'));
								break;
						}
						break;
					case 'thisyear':
						$year = date('Y');
						$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= ' . $this->db->quote($year . '-01-01'));
						$year++;
						$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') < ' . $this->db->quote($year . '-01-01'));
						break;
					case 'lastyear':
						$year = date('Y');
						$year--;
						$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') >= ' . $this->db->quote($year . '-01-01'));
						$year++;
						$query->where('DATE(' . $this->db->quoteName('j2store_orders.created_on') . ') < ' . $this->db->quote($year . '-01-01'));
						break;
				}
			}
			else
			{
				// Filter by order date start
				$orderdatestart = $this->template->get('orderdatestart', false);

				if ($orderdatestart)
				{
					$orderdate = \JFactory::getDate($orderdatestart);
					$query->where($this->db->quoteName('j2store_orders') . '.' . $this->db->quoteName('created_on') . ' >= ' . $this->db->quote($orderdate->toSql()));
				}

				// Filter by order date end
				$orderdateend = $this->template->get('orderdateend', false);

				if ($orderdateend)
				{
					$orderdate = \JFactory::getDate($orderdateend);
					$query->where($this->db->quoteName('j2store_orders') . '.' . $this->db->quoteName('created_on') . ' <= ' . $this->db->quote($orderdate->toSql()));
				}

				// Filter by order modified date start
				$ordermdatestart = $this->template->get('ordermdatestart', false);

				if ($ordermdatestart)
				{
					$ordermdate = \JFactory::getDate($ordermdatestart);
					$query->where($this->db->quoteName('j2store_orders') . '.' . $this->db->quoteName('modified_on') . ' >= ' . $this->db->quote($ordermdate->toSql()));
				}

				// Filter by order modified date end
				$ordermdateend = $this->template->get('ordermdateend', false);

				if ($ordermdateend)
				{
					$ordermdate = \JFactory::getDate($ordermdateend);
					$query->where($this->db->quoteName('j2store_orders') . '.' . $this->db->quoteName('modified_on') . ' <= ' . $this->db->quote($ordermdate->toSql()));
				}
			}

			// Filter by order status
			$orderstatus = $this->template->get('orderstatus', false);

			if ($orderstatus && $orderstatus[0] !== '')
			{
				$query->where($this->db->quoteName('j2store_orders.order_state_id') . ' IN (\'' . implode("','", $orderstatus) . '\')');
			}

			// Filter by order price start
			$pricestart = $this->template->get('orderpricestart', false, 'float');

			if ($pricestart)
			{
				$query->where($this->db->quoteName('j2store_orders.order_total') . ' >= ' . $pricestart);
			}

			// Filter by order price end
			$priceend = $this->template->get('orderpriceend', false, 'float');

			if ($priceend)
			{
				$query->where($this->db->quoteName('j2store_orders.order_total') . ' <= ' . $priceend);
			}

			// Filter by order currency
			$ordercurrency = $this->template->get('ordercurrency', false);

			if ($ordercurrency && $ordercurrency[0] !== '')
			{
				$query->where($this->db->quoteName('j2store_orders.currency_id') . ' IN (\'' . implode("','", $ordercurrency) . '\')');
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
				// Check if we need to split the orderline
				$splitLine = $this->getTemplate()->get('splitorderline', 'jform');

				if ($splitLine)
				{
					// Set the order ID
					$orderId = 0;
				}

				foreach ($records as $record)
				{
					$emptyLine = false;

					if ($splitLine)
					{
						// Set the order ID
						if ($orderId == 0)
						{
							$orderId = $record->order_id;
							$emptyLine = false;
						}
					}

					$this->log->incrementLinenumber();

					foreach ($exportFields as $field)
					{
						$fieldName = $field->field_name;

						// Set the field value
						if (isset($record->$fieldName))
						{
							$fieldValue = $record->$fieldName;
						}
						else
						{
							$fieldValue = '';
						}

						// Process the field
						switch ($fieldName)
						{
							case 'created_on':
							case 'modified_on':
								$fieldValue = $this->fields->getDateFormat($fieldName, $record->$fieldName, $field->column_header);
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
							case 'order_refund':
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
								if ($fieldValue)
								{
									$fieldValue = number_format(
										$fieldValue,
										$this->template->get('export_price_format_decimal', 2, 'int'),
										$this->template->get('export_price_format_decsep'),
										$this->template->get('export_price_format_thousep')
									);
								}
								break;
							case 'custom':
								$fieldValue = $field->default_value;
								break;
							default:
								break;
						}

						// Store the field value
						$this->fields->set($field->csvi_templatefield_id, $fieldValue);
					}

					// Output the data
					$this->addExportFields();

					if ($splitLine)
					{
						// Keep track of the order ID
						if ($record->order_id != $orderId)
						{
							$orderId   = $record->order_id;
							$emptyLine = true;
						}
					}

					// Output the contents
					$this->writeOutput($emptyLine);
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
