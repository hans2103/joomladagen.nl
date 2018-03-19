<?php
/**
 * @package     CSVI
 * @subpackage  Joomla field groups
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace fields\com_fields\model\export;

defined('_JEXEC') or die;

/**
 * Export Joomla field groups.
 *
 * @package     CSVI
 * @subpackage  Field groups
 * @since       7.2.0
 */
class FieldGroups extends \CsviModelExports
{
	/**
	 * Export the data.
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 */
	protected function exportBody()
	{
		if (parent::exportBody())
		{
			// Build something fancy to only get the fieldnames the user wants
			$exportFields = $this->fields->getFields();

			// Group by fields
			$groupFields = json_decode($this->template->get('groupbyfields', '', 'string'));
			$groupBy       = array();

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
					case 'context_type':
						$userFields[] = $this->db->quoteName('fg.context');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('fg.context');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('fg.context');
						}
						break;
					case 'title':
					case 'id':
					case 'note':
					case 'asset_id':
					case 'context':
					case 'description':
					case 'state':
					case 'ordering':
					case 'checked_out':
					case 'checked_out_time':
					case 'language':
					case 'created':
					case 'created_by':
					case 'modified':
					case 'modified_by':
					case 'access':
						$userFields[] = $this->db->quoteName('fg.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('fg.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('fg.' . $field->field_name);
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
			$query->from($this->db->quoteName('#__fields_groups', 'fg'));

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
							case 'context_type':
								$context = explode('.', $record->context);
								$fieldValue = $context[1];
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
