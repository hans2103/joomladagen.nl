<?php
/**
 * @package     CSVI
 * @subpackage  CSVI
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace csvi\com_csvi\model\export;

defined('_JEXEC') or die;

/**
 * Export custom tables.
 *
 * @package     CSVI
 * @subpackage  CSVI
 * @since       6.0
 */
class Custom extends \CsviModelExports
{
	/**
	 * Export the data.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 *
	 * @throws  \CsviException
	 */
	protected function exportBody()
	{
		if (parent::exportBody())
		{
			// Build something fancy to only get the fieldnames the user wants
			$userfields = array();
			$exportfields = $this->fields->getFields();
			$customTables = $this->template->get('custom_table');

			if (!isset($customTables->custom_table0->table))
			{
				// There is no from table, we can't continue
				throw new \CsviException(\JText::_('COM_CSVI_CUSTOM_EXPORT_NO_TABLENAME_SET'));
			}

			// Get the primary table
			$primaryTable = $customTables->custom_table0->table;

			// Group by fields
			$groupbyfields = json_decode($this->template->get('groupbyfields', '', 'string'));
			$groupFieldNames = array();

			for ($i = 0; $i <= count($groupbyfields); $i++)
			{
				if (isset($groupbyfields->groupby_table_name[$i]))
				{
					$groupFieldNames[] = '#__' . $groupbyfields->groupby_table_name[$i] . '.' . $groupbyfields->groupby_field_name[$i];
				}
			}

			$groupby = array();

			// Sort selected fields
			$sortfields = json_decode($this->template->get('sortfields', '', 'string'));
			$sortby = array();

			$sortFieldNames = array();

			for ($j = 0; $j <= count($sortfields); $j++)
			{
				if (isset($sortfields->sortby_table_name[$j]))
				{
					$sortFieldNames[] = '#__' . $sortfields->sortby_table_name[$j] . '.' . $sortfields->sortby_field_name[$j];
				}
			}

			foreach ($exportfields as $field)
			{
				switch ($field->field_name)
				{
					// Man made fields, do not export them
					case 'custom':
						break;
					default:
						$tableName = $field->table_name;

						if ($tableName)
						{
							$userfields[] = $this->db->quoteName('#__' . $tableName . '.' . $field->field_name);

							if ($groupFieldNames)
							{
								$groupby = $groupFieldNames;
							}

							if ($sortFieldNames)
							{
								$sortby = $sortFieldNames;
							}
						}
						else
						{
							$userfields[] = $this->db->quoteName($field->field_name);

							if (array_key_exists($field->field_name, $groupFieldNames))
							{
								$groupby[] = $this->db->quoteName($field->field_name);
							}

							if (array_key_exists($field->field_name, $sortFieldNames))
							{
								$sortby[] = $this->db->quoteName($field->field_name);
							}
						}
						break;
				}
			}

			$userfields = array_unique($userfields);
			$query = $this->db->getQuery(true);
			$query->select(implode(",\n", $userfields));

			$query->from($this->db->quoteName("#__" . $primaryTable));

			for ($i = 1; $i <= count((array) $customTables); $i++)
			{
				$keyName = 'custom_table' . $i;

				if (isset($customTables->$keyName->table))
				{
					$joinType = $customTables->$keyName->jointype;
					$query->join(
						$joinType,
						$this->db->quoteName('#__' . $customTables->$keyName->table)
						. ' ON ' . $this->db->quoteName('#__' . $customTables->$keyName->table . '.' . $customTables->$keyName->field) .
						' = ' . $this->db->quoteName('#__' . $customTables->$keyName->jointable . '.' . $customTables->$keyName->joinfield)
					);
				}
			}

			// Group the fields
			$groupby = array_unique($groupby);

			if (!empty($groupby))
			{
				$query->group($groupby);
			}

			// Sort set fields
			$sortby = array_unique($sortby);

			if (!empty($sortby))
			{
				$query->order($sortby);
			}

			// Add export limits
			$limits = $this->getExportLimit();

			try
			{
				// Execute the query
				$this->db->setQuery($query, $limits['offset'], $limits['limit']);
				$records = $this->db->getIterator();
				$this->log->add('Export query' . $query->__toString(), false);

				// Check if there are any records
				$logcount = $this->db->getNumRows();

				if ($logcount > 0)
				{
					foreach ($records as $record)
					{
						$this->log->incrementLinenumber();

					foreach ($exportfields as $field)
					{
						$fieldname = $field->field_name;

							// Set the field value
							if (isset($record->$fieldname))
							{
								$fieldvalue = $record->$fieldname;
							}
							else
							{
								$fieldvalue = '';
							}

							// Store the field value
							$this->fields->set($field->csvi_templatefield_id, $fieldvalue);
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
			catch (Exception $e)
			{
				$this->log->add('Error: ' . $e->getMessage(), false);
				$this->log->addStats('incorrect', $e->getMessage());
			}
		}
	}
}
