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
 * Export J2Store product price fields.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.3.0
 */
class Price extends \CsviModelExports
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
					case 'customer_group_name':
						$userFields[] = $this->db->quoteName('#__j2store_product_prices.customer_group_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('#__j2store_product_prices.customer_group_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('#__j2store_product_prices.customer_group_id');
						}
						break;
					case 'sku':
						$userFields[] = $this->db->quoteName('#__j2store_product_prices.variant_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('#__j2store_product_prices.variant_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('#__j2store_product_prices.variant_id');
						}
						break;
					case 'variant_id':
					case 'quantity_from':
					case 'quantity_to':
					case 'date_from':
					case 'date_to':
					case 'customer_group_id':
					case 'price':
					case 'params':
						$userFields[] = $this->db->quoteName('#__j2store_product_prices.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('#__j2store_product_prices.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('#__j2store_product_prices.' . $field->field_name);
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
			$query->from($this->db->quoteName('#__j2store_product_prices'));

			// Filter by customer group
			$customerGroup = $this->template->get('customer_group');

			if ($customerGroup && 'none' !== $customerGroup[0] && 0 !== count($customerGroup))
			{
				$query->where('customer_group_id = ' . (int) $customerGroup);
			}

			$quantityFrom = $this->template->get('quantity_start', null);

			if ($quantityFrom >= 0 && $quantityFrom !== null)
			{
				$query->where($this->db->quoteName('quantity_from') . ' >= ' . $this->db->quote($quantityFrom));
			}

			// Filter on price from
			$priceoperator = $this->template->get('priceoperator', 'gt');
			$pricefrom     = $this->template->get('pricefrom', false);
			$priceto       = $this->template->get('priceto', 0, 'float');

			if ($pricefrom !== false)
			{
				switch ($priceoperator)
				{
					case 'gt':
						$query->where(
							'ROUND('
							. $this->db->quoteName('price') . ', '
							. $this->template->get('export_price_format_decimal', 5, 'int') . ') > ' . $pricefrom
						);
						break;
					case 'eq':
						$query->where(
							'ROUND('
							. $this->db->quoteName('price') . ', '
							. $this->template->get('export_price_format_decimal', 5, 'int') . ') = ' . $pricefrom
						);
						break;
					case 'lt':
						$query->where(
							'ROUND('
							. $this->db->quoteName('price') . ', '
							. $this->template->get('export_price_format_decimal', 5, 'int') . ') < ' . $pricefrom
						);
						break;
					case 'bt':
						$query->where(
							'ROUND('
							. $this->db->quoteName('price') . ', '
							. $this->template->get('export_price_format_decimal', 5, 'int') . ') BETWEEN ' . $pricefrom . ' AND ' . $priceto
						);
						break;
				}
			}

			$dateStart = $this->template->get('date_from', false);

			if ($dateStart)
			{
				$startDate = \JFactory::getDate($dateStart)->format('Y-m-d');
				$query->where($this->db->quoteName('date_from') . ' >= ' . $this->db->quote($startDate));
			}

			$dateEnd = $this->template->get('date_to');

			if ($dateEnd)
			{
				$endDate = \JFactory::getDate($dateEnd)->format('Y-m-d');
				$query->where($this->db->quoteName('date_to') . ' <= ' . $this->db->quote($endDate));
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
							case 'customer_group_name':
								$query->clear()
									->select($this->db->quoteName('title'))
									->from($this->db->quoteName('#__usergroups'))
									->where($this->db->quoteName('id') . ' = ' . (int) $record->customer_group_id);
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
							case 'price':
								$fieldValue = $this->formatNumber($record->price);
								break;
							case 'date_from':
							case 'date_to':
								$fieldValue = $this->fields->getDateFormat($fieldName, $record->$fieldName, $field->column_header);
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
