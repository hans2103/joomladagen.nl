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
 * Export J2Store Geo zone rule fields.
 *
 * @package     CSVI
 * @subpackage  J2Store
 * @since       7.5.0
 */
class Geozonerule extends \CsviModelExports
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
			$userFields = array();

			foreach ($exportFields as $field)
			{
				switch ($field->field_name)
				{
					case 'geozone_name':
					case 'j2store_geozone_id':
					case 'enabled':
						$userFields[] = $this->db->quoteName('j2store_geozones.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_geozones.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_geozones.' . $field->field_name);
						}
						break;
					case 'j2store_geozonerule_id':
					case 'geozone_id':
					case 'country_id':
					case 'zone_id':
						$userFields[] = $this->db->quoteName('j2store_geozonerules.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_geozonerules.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_geozonerules.' . $field->field_name);
						}
						break;
					case 'country_name':
					case 'country_isocode_2':
					case 'country_isocode_3':
					case 'j2store_country_id':
					case 'ordering':
						$userFields[] = $this->db->quoteName('j2store_geozonerules.country_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_geozonerules.country_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_geozonerules.country_id');
						}
						break;
					case 'zone_code':
					case 'zone_name':
						$userFields[] = $this->db->quoteName('j2store_geozonerules.zone_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('j2store_geozonerules.zone_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('j2store_geozonerules.zone_id');
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
			$query->from($this->db->quoteName('#__j2store_geozonerules', 'j2store_geozonerules'));
			$query->leftJoin(
				$this->db->quoteName('#__j2store_geozones', 'j2store_geozones') . ' ON ' .
				$this->db->quoteName('j2store_geozones.j2store_geozone_id') . ' = ' . $this->db->quoteName('j2store_geozonerules.geozone_id')
			);
			$query->leftJoin(
				$this->db->quoteName('#__j2store_countries', 'j2store_countries') . ' ON ' .
				$this->db->quoteName('j2store_countries.j2store_country_id') . ' = ' . $this->db->quoteName('j2store_geozonerules.country_id')
			);
			$query->leftJoin(
				$this->db->quoteName('#__j2store_zones', 'j2store_zones') . ' ON ' .
				$this->db->quoteName('j2store_zones.j2store_zone_id') . ' = ' . $this->db->quoteName('j2store_geozonerules.zone_id')
			);

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
							case 'country_name':
							case 'country_isocode_2':
							case 'country_isocode_3':
								$query->clear()
									->select($this->db->quoteName($fieldName))
									->from($this->db->quoteName('#__j2store_countries'))
									->where($this->db->quoteName('j2store_country_id') . ' = ' . (int) $record->country_id);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
								break;
							case 'zone_code':
							case 'zone_name':
								$query->clear()
									->select($this->db->quoteName($fieldName))
									->from($this->db->quoteName('#__j2store_zones'))
									->where($this->db->quoteName('j2store_zone_id') . ' = ' . (int) $record->zone_id);
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
