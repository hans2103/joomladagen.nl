<?php
/**
 * @package     CSVI
 * @subpackage  Helper.Fields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * The CsviFields class handles the export field operations.
 *
 * @package     CSVI
 * @subpackage  Helper.Fields
 * @since       6.0
 */
final class CsviHelperExportfields extends CsviHelperFields
{
	/**
	 * Adds a field array to the fields list.
	 *
	 * @param   JObject $data The field data to add
	 *
	 * @return  bool  Returns true if field is added | False otherwise
	 *
	 * @since   4.6
	 */
	public function add(JObject $data)
	{
		// Check if the field name is supported
		if (!in_array($data->field_name, $this->supportedFields))
		{
			// Make the field a skip field as it is unsupported
			$this->log->add('Found field ' . $data->field_name . ' but this field is not supported. Ignoring this field.');
		}
		else
		{
			/**
			 * Add the data in a 2-dimensional array
			 */
			$this->fields[$data->csvi_templatefield_id] = $data;
		}

		return true;
	}

	/**
	 * Load the details of a field.
	 *
	 * @param   string $name    The name of the field to retrieve.
	 * @param   string $default The default value to use if needed.
	 *
	 * @return  string  Value if field is found | null if field is not found.
	 *
	 * @since   5.0
	 */

	public function get($name, $default = null)
	{
		// Check if the field exists
		foreach ($this->fields as $field)
		{
			$checkField             = $field->xml_node;
			$checkColumnHeaderField = '';

			if ($field->field_name)
			{
				$checkField = $field->field_name;
			}

			if ($field->column_header)
			{
				$checkColumnHeaderField = $field->column_header;
			}

			// See if the name matches the field
			if ($name == $checkField || $name == $checkColumnHeaderField)
			{
				if (strlen($field->value) == 0)
				{
					return $default;
				}
				else
				{
					return $field->value;
				}
			}
		}
	}

	/**
	 * Set a value on a field.
	 *
	 * @param   string $csvi_templatefield_id The name of the field to add the data to.
	 * @param   string $value                 The data to add to the field.
	 *
	 * @return  mixed  Returns true if field is added | Null if nothing is added.
	 *
	 * @since   4.6
	 */
	public function set($csvi_templatefield_id, $value)
	{
		if (isset($this->fields[$csvi_templatefield_id]))
		{
			$this->fields[$csvi_templatefield_id]->value = $value;
		}

		// Return as we are done
		return true;
	}

	/**
	 * Resets the values.
	 *
	 * @return  void.
	 *
	 * @since   4.6
	 */
	public function reset()
	{
		foreach ($this->fields as $csvi_templatefield_id => $field)
		{
			$this->fields[$csvi_templatefield_id]->used  = false;
			$this->fields[$csvi_templatefield_id]->value = null;
		}
	}

	/**
	 * Get a list of field names being processed.
	 *
	 * @return  array  The list of processed field names.
	 *
	 * @throws  Exception
	 *
	 * @since   5.0
	 */
	public function getFieldnames()
	{
		$fields = array();

		foreach ($this->fields as $field)
		{
			if (is_object($field))
			{
				if ($field->enabled)
				{
					if (!empty($field->column_header))
					{
						$fields[] = $field->column_header;
					}
					else
					{
						$fields[] = $field->field_name;
					}
				}
			}
		}

		return $fields;
	}

	/**
	 * Run the associated rules before export/import.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function runRules()
	{
		// Load the plugin helper
		$dispatcher = new RantaiPluginDispatcher;
		$dispatcher->importPlugins('csvirules', $this->db);

		// Run through the fields and see which plugins need to be applied
		foreach ($this->fields as $field)
		{
			if (!empty($field->rules))
			{
				// Load the plugin details
				$query = $this->db->getQuery(true)
					->select(
						array(
							$this->db->quoteName('plugin'),
							$this->db->quoteName('plugin_params')
						)
					)
					->from($this->db->quoteName('#__csvi_rules'))
					->where($this->db->quoteName('csvi_rule_id') . ' IN (' . implode(',', $field->rules) . ')')
					->order($this->db->quoteName('ordering'));
				$this->db->setQuery($query);
				$rules = $this->db->loadObjectList();

				foreach ($rules as $rule)
				{
					$dispatcher->trigger('runRule',
						array(
							'plugin'   => $rule->plugin,
							'settings' => json_decode($rule->plugin_params),
							'field'    => $field,
							'fields'   => $this,
						)
					);
				}
			}
		}
	}

	/**
	 * Get the data to process by the model.
	 *
	 * @return  array  List of fields and their data.
	 *
	 * @since   4.6
	 */
	public function getData()
	{
		$data = array();

		if (!$this->getProcessRecord())
		{
			$this->log->add(JText::_('COM_CSVI_EXPORT_SKIPPED_RECORD_RULE_SETTINGS'), false);
			$this->log->addStats('skipped', JText::_('COM_CSVI_EXPORT_SKIPPED_RECORD_RULE_SETTINGS'));

			return $data;
		}

		foreach ($this->fields as $csvi_templatefield_id => $field)
		{
			$new = array();

			if (isset($field->field_name) && $field->enabled)
			{
				$new[$field->field_name]             = clone $field;
				$data[$field->csvi_templatefield_id] = $new;
			}
		}

		return $data;
	}

	/**
	 * Update a given field with the given value.
	 *
	 * @param   object $field The field to update.
	 * @param   mixed  $value The value to add to the field.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.5.2
	 */
	public function updateField($field, $value)
	{
		$this->set($field->csvi_templatefield_id, $value);

		return true;
	}

	/**
	 * Get a single field object.
	 *
	 * @param   string $name The name of the field to find.
	 *
	 * @return  object  The requested field.
	 *
	 * @since   6.5.2
	 */
	public function getField($name)
	{
		// Check if the field exists
		foreach ($this->fields as $field)
		{
			$checkField = $field->xml_node;

			if ($field->column_header)
			{
				$checkField = $field->column_header;
			}

			// See if the name matches the field
			if ($name === $checkField || $name === $field->field_name)
			{
				return $field;
			}
		}

		return false;
	}

	/**
	 * Get date format of field if set
	 *
	 * @param   string $fieldName    The name of the field
	 * @param   string $fieldValue   The value of the field
	 * @param   string $columnHeader The name of the column header
	 *
	 * @return  string  The value of field with date format applied
	 *
	 * @since   7.3.0
	 */
	public function getDateFormat($fieldName, $fieldValue, $columnHeader = '')
	{
		if ($fieldValue === $this->db->getNullDate())
		{
			return '';
		}

		if ($columnHeader)
		{
			$fieldName = $columnHeader;
		}

		$fieldDetails = $this->getField($fieldName);
		$finalValue   = JHtml::_('date', $fieldValue, $this->template->get('export_date_format'));

		if ($fieldDetails->field_date_format)
		{
			$finalValue = JHtml::_('date', $fieldValue, $fieldDetails->field_date_format);
		}

		return $finalValue;
	}

	/**
	 * Get if the given value is a date
	 *
	 * @param   string $fieldName The field name
	 * @param   string $fieldType The field type
	 *
	 * @return  bool True if date value false otherwise.
	 *
	 * @since   7.3.0
	 *
	 * @throws  \Exception
	 */
	public function checkCustomFieldType($fieldName, $fieldType)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('type'))
			->from($this->db->quoteName('#__fields'))
			->where($this->db->quoteName('name') . ' = ' . $this->db->quote($fieldName));

		$this->db->setQuery($query);
		$type = $this->db->loadResult();

		if ($type === $fieldType)
		{
			return true;
		}

		return false;

	}
}
