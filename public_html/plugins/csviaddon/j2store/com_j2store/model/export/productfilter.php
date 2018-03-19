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
 * Export J2Store product filter fields.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.5.0
 */
class Productfilter extends \CsviModelExports
{
	/**
	 * Export the data.
	 *
	 * @return  void.
	 *
	 * @since   7.5.0
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
			$userFields[] = $this->db->quoteName('#__j2store_product_filters.product_id');

			foreach ($exportFields as $field)
			{
				switch ($field->field_name)
				{
					case 'sku':
					case 'product_id':
						$userFields[] = $this->db->quoteName('#__j2store_product_filters.product_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('#__j2store_product_filters.product_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('#__j2store_product_filters.product_id');
						}
						break;
					case 'filter_id':
						$userFields[] = $this->db->quoteName('#__j2store_product_filters.filter_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('#__j2store_product_filters.filter_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('#__j2store_product_filters.filter_id');
						}
						break;
					case 'filter_name':
					case 'filter_group_name':
						$userFields[] = $this->db->quoteName('#__j2store_product_filters.filter_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('#__j2store_product_filters.filter_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('#__j2store_product_filters.filter_id');
						}

						$groupBy[] = $this->db->quoteName('#__j2store_product_filters.product_id');

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
			$query->from($this->db->quoteName('#__j2store_product_filters'));

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
						$fieldName  = $field->field_name;
						$fieldValue = '';

						// Set the field value
						if (isset($record->$fieldName))
						{
							$fieldValue = $record->$fieldName;
						}

						// Process the field
						switch ($fieldName)
						{
							case 'sku':
								$query->clear()
									->select($this->db->quoteName('sku'))
									->from($this->db->quoteName('#__j2store_variants'))
									->where($this->db->quoteName('product_id') . ' = ' . (int) $record->product_id);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
								break;
							case 'filter_name':
							case 'filter_group_name':
								$query->clear()
									->select($this->db->quoteName('filter_id'))
									->from($this->db->quoteName('#__j2store_product_filters'))
									->where($this->db->quoteName('product_id') . ' = ' . (int) $record->product_id);
								$this->db->setQuery($query);
								$filterIds = $this->db->loadObjectList();

								$filterNames = array();

								foreach ($filterIds as $filterId)
								{
									$query->clear()
										->select($this->db->quoteName('#__j2store_filters.filter_name'))
										->select($this->db->quoteName('#__j2store_filtergroups.group_name'))
										->from($this->db->quoteName('#__j2store_filters'))
										->leftJoin(
											$this->db->quoteName('#__j2store_filtergroups')
											. ' ON ' . $this->db->quoteName('#__j2store_filters.group_id') . ' = ' .
											$this->db->quoteName('#__j2store_filtergroups.j2store_filtergroup_id')
										)
										->where($this->db->quoteName('#__j2store_filters.j2store_filter_id') . ' = ' . (int) $filterId->filter_id);
									$this->db->setQuery($query);
									$filterNames[] = $this->db->loadObject();
								}

								$groupArray = array();

								foreach ($filterNames as $names)
								{
									if (!isset($groupArray[$names->group_name]))
									{
										$groupArray[$names->group_name]   = array();
										$groupArray[$names->group_name][] = $names->filter_name;
									}
									else
									{
										$groupArray[$names->group_name][] = $names->filter_name;
									}
								}

								if ($fieldName === 'filter_group_name')
								{
									$fieldValue = implode('|', array_keys($groupArray));
								}

								if ($fieldName === 'filter_name')
								{
									$filterValues = array();
									$keys         = array_keys($groupArray);

									foreach ($keys as $key)
									{
										$filterValues[] = implode('#', $groupArray[$key]);
									}

									$fieldValue = implode('|', $filterValues);
								}
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
