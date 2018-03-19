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
 * The CsviFields class handles the import field operations.
 *
 * @package     CSVI
 * @subpackage  Helper.Fields
 * @since       6.0
 */
final class CsviHelperImportfields extends CsviHelperFields
{
	/**
	 * Holds the ICEcat data
	 *
	 * @var    array
	 * @since  6.0
	 */
	private $icecatData = null;

	/**
	 * Adds a field array to the fields list.
	 *
	 * @param   object  $data  The field data to add
	 *
	 * @return  bool  Returns true if field is added | False otherwise
	 *
	 * @since   4.6
	 */
	public function add($data)
	{
		// Check if the field name is supported
		if (!in_array($data->field_name, $this->supportedFields))
		{
			// Make the field a skip field as it is unsupported
			$data->field_name = 'skip';
		}

		// Add a used field to track usage
		$data->used = false;

		// Set the field name for collection
		$field_name = $data->field_name;

		// Check if we have a skip, combine or custom field
		if (in_array($data->field_name, array('skip', 'combine', 'custom')))
		{
			$field_name = $data->field_name . count($this->fields);
		}

		// Check if the name exists
		if (!isset($this->fields[$field_name]))
		{
			$this->fields[$field_name] = array();
		}

		/**
		 * Add the data in a 2-dimensional array
		 *
		 * field_name is the available field supported by CSVI
		 * xml_node is the name of the field in the import file. This is needed for XML files, for other files this is
		 * the same as the field_name.
		 */
		$this->fields[$field_name][$data->xml_node] = $data;

		return true;
	}

	/**
	 * Load the details of a field.
	 *
	 * @param   string  $name     The name of the field to retrieve.
	 * @param   string  $default  The default value to use if needed.
	 *
	 * @return  string  Value if field is found | null if field is not found.
	 *
	 * @since   5.0
	 */

	public function get($name, $default=null, $counter = 0)
	{
		// Check if we are going to take special fields
		if ($name == 'features')
		{
			if (isset($this->icecatData['features']))
			{
				return $this->icecatData['features'];
			}
			else
			{
				return null;
			}
		}
		else
		{
			$fieldcounter = array();

			// Check if the field exists
			foreach ($this->fields as $group)
			{
				foreach ($group as $fieldname => $field)
				{
					$checkfield = $field->xml_node;

					if (!in_array($field->field_name, array('skip', 'combine', 'custom')))
					{
						$checkfield = $field->field_name;
					}

					if (!isset($fieldcounter[$checkfield]))
					{
						$fieldcounter[$checkfield] = 0;
					}

					$fieldcounter[$checkfield]++;

					// See if the name matches the field
					if ($name == $checkfield && ($counter <= 1 || $fieldcounter[$field->xml_node] == $counter))
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

			return null;
		}
	}

	/**
	 * Set a value on a field.
	 *
	 * @param   string  $key      The name of the field to add the data to.
	 * @param   string  $value    The data to add to the field.
	 * @param   int     $counter  Counter to specify which field should be updated in case of duplicate fields.
	 *
	 * @return  mixed  Returns true if field is added | Null if nothing is added.
	 *
	 * @since   4.6
	 */
	public function set($key, $value, $counter = 0)
	{
		$fieldcounter = array();

		foreach ($this->fields as $name => $fields)
		{
			foreach ($fields as $field => $details)
			{
				if (!isset($fieldcounter[$details->xml_node]))
				{
					$fieldcounter[$details->xml_node] = 0;
				}

				$fieldcounter[$details->xml_node]++;

				if ($details->xml_node == $key && ($counter < 2 || $fieldcounter[$details->xml_node] == $counter))
				{
					// Set the value
					if (strlen($value) == 0)
					{
						$value = $details->default_value;
					}

					$this->fields[$name][$field]->value = $value;

					// Return as we are done
					return true;
				}
			}
		}

		return null;
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
		foreach ($this->fields as $name => $fields)
		{
			foreach ($fields as $key => $field)
			{
				$this->fields[$name][$key]->used = false;
				$this->fields[$name][$key]->value = null;
			}
		}
	}

	/**
	 * Check if the fieldname exists.
	 *
	 * @param   string  $name  The name of the field to check
	 *
	 * @return  bool  true if the fieldname is found | false if the fieldname is not found.
	 *
	 * @since   4.6
	 */
	public function valid($name)
	{
		foreach ($this->fields as $fields)
		{
			if (array_key_exists($name, $fields))
			{
				return true;
			}
		}

		// Nothing found
		return false;
	}

	/**
	 * Add the fields we are processing.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @throws  CsviException
	 *
	 * @since   6.0
	 */
	public function setupFields()
	{
		$nosupport = array();
		$this->log->add('Load the fields to process', false);

		if ($this->template->get('use_column_headers'))
		{
			// Load the column headers as the user wants to use the column headers for configuration
			$columnheaders = $this->file->loadColumnHeaders();

			// The user has column headers in the file
			if ($columnheaders)
			{
				foreach ($columnheaders as $order => $name)
				{
					$data = new JObject;

					// Trim the name in case the name contains any preceding or trailing spaces
					$name = trim($name);

					// Check if the field name is supported
					if (in_array($name, $this->supportedFields))
					{
						$this->log->add('Setup field: ' . $name, false);
						$data->field_name = $name;
						$data->xml_node = $name;
						$data->source_field = null;
						$data->default_value = null;
						$data->supported = 1;
						$data->order = $order;
						$data->value = null;
					}
					else
					{
						// Check if the user has any field that is not supported
						if (strlen($name) == 0)
						{
							$name = JText::_('COM_CSVI_FIELD_EMPTY');
						}

						// Field is not supported, let's skip it
						$data->field_name = $name;
						$data->xml_node = $name;
						$data->default_value = null;
						$data->supported = 0;
						$data->order = $order;
						$data->value = null;

						// Collect the field names to report them
						$nosupport[] = $name;
					}

					// Add the field to the field handler
					$this->add($data);
				}

				if (!empty($nosupport))
				{
					// Ensure the error message matches the file type
					switch ($this->file->getExtension())
					{
						case 'xml':
							$this->log->addStats('nosupport', implode(',', $nosupport) . JText::_('COM_CSVI_FIELD_NOT_INCLUDED'));
							break;
						default:
							$this->log->addStats('nosupport', JText::sprintf('COM_CSVI_NO_SUPPORT', '<ul><li>' . implode('</li><li>', $nosupport) . '</li></ul>'));
							break;
					}

					$this->log->addStats('information', 'COM_CSVI_UNSUPPORTED_FIELDS');
				}

				$this->log->add('Use the file for configuration', false);
			}
			else
			{
				throw new CsviException(JText::_('COM_CSVI_NO_COLUMN_HEADERS_FOUND'), 406);
			}

			// Add extra fields if needed
			if ($this->template->get('add_extra_fields', false))
			{
				$fields = $this->loadTemplateFields();

				if (!empty($fields))
				{
					foreach ($fields as $fid => $field)
					{
						// Get the name of the field
						$name = $field->field_name;

						// Check if we are handling a combine field
						if ($field->field_name == 'combine')
						{
							$name .= $fid;
						}

						// Set the XML node
						$xml_node = $field->xml_node;

						// Check if there is a XML node set
						if (empty($field->xml_node))
						{
							$xml_node = $name;
						}

						// Collect the data
						$data = new JObject;
						$data->field_name = $name;
						$data->xml_node = $xml_node;
						$data->source_field = $field->source_field;
						$data->default_value = $field->default_value;
						$data->supported = true;
						$data->order = $field->ordering;

						// Add the field to field class
						$this->add($data);

						$this->log->add('Field name: ' . $name, false);
						$this->log->add('Use database for configuration', false);
					}
				}
			}
		}
		else
		{
			// Load the fields to process
			$headers = $this->loadTemplateFields();

			if (!empty($headers))
			{
				// Add the rules and convert combine fields
				foreach ($headers as $field)
				{
					// Setup the XML fields, we mimic them for none XML files
					if (empty($field->xml_node))
					{
						$field->xml_node = empty($field->source_field) ? $field->field_name : $field->source_field;
					}

					// Convert combine fields to be able to have multiple per import
					if ($field->field_name == 'combine')
					{
						$field->field_name .= $field->csvi_templatefield_id;
					}

					// Load the associated rules for the field
					$query = $this->db->getQuery(true)
						->select($this->db->quoteName('csvi_rule_id'))
						->from($this->db->quoteName('#__csvi_templatefields_rules'))
						->where($this->db->quoteName('csvi_templatefield_id') . ' = ' . (int) $field->csvi_templatefield_id)
						->order($this->db->quoteName('csvi_templatefields_rule_id'));
					$this->db->setQuery($query);
					$field->rules = $this->db->loadColumn();

					// Convert to the field to an JObject
					$data = new JObject($field);

					// Set the field as supported
					$data->supported = 1;

					// Add the field to the field helper
					$this->add($data);

					$this->log->add('Field name: ' . $field->field_name, false);
				}
			}
			else
			{
				$this->log->addStats('incorrect', 'COM_CSVI_NO_COLUMN_HEADERS_FOUND');

				throw new CsviException(JText::_('COM_CSVI_NO_COLUMN_HEADERS_FOUND'), 500);
			}
		}

		return true;
	}

	/**
	 * Load fields assigned to a template.
	 *
	 * @return  array  List of fields linked to the template.
	 *
	 * @since   6.0
	 */
	private function loadTemplateFields()
	{
		// Use the fields assigned to the template
		$query = $this->db->getQuery(true)
		->select(
			array(
				$this->db->quoteName('f.csvi_templatefield_id'),
				$this->db->quoteName('f.field_name'),
				$this->db->quoteName('f.source_field'),
				$this->db->quoteName('f.xml_node'),
				$this->db->quoteName('f.default_value'),
				$this->db->quoteName('f.sort'),
				$this->db->quoteName('f.ordering')
			)
		)
		->from($this->db->quoteName('#__csvi_templatefields', 'f'))
		->where($this->db->quoteName('f.csvi_template_id') . ' = ' . (int) $this->template->getId())
		->order($this->db->quoteName('f.ordering'));
		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}

	/**
	 * Prepare the fields for import.
	 *
	 * @return  bool  Always returns true.
	 *
	 * @since   4.6
	 */
	public function prepareData()
	{
		// Validate the fields
		foreach ($this->fields as $name => $fields)
		{
			foreach ($fields as $field => $details)
			{
				if ($name !== 'skip' && $details->supported)
				{
					$datafield = $this->validateInput($details);

					if ($datafield !== false)
					{
						// Set the new value
						$this->fields[$name][$field]->value = $datafield;
					}
				}
			}
		}

		// Run the rules
		$this->runRules();

		return true;
	}

	/**
	 * Get a list of field names being processed.
	 *
	 * @param   bool  $headers  Set if the headers should be returned
	 *
	 * @return  array  The list of processed field names.
	 *
	 * @throws  Exception
	 *
	 * @since   5.0
	 */
	public function getFieldnames($headers = false)
	{
		$fields = array();

		foreach ($this->fields as $data)
		{
			// Get the first entry
			$field = reset($data);

			if (is_object($field))
			{
				if ($field->supported)
				{
					if ($headers)
					{
						if (!in_array($field->field_name, array('skip','combine')))
						{
							$fields[] = empty($field->source_field) ? $field->field_name : $field->source_field;
						}
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
	 * Get all field names regardless of their status.
	 *
	 * @return  array  The list of all field names in the template.
	 *
	 * @since   6.0
	 */
	public function getAllFieldnames()
	{
		$fields = array();

		$counter = 0;

		foreach ($this->fields as $data)
		{
			// Get the first entry
			$field = reset($data);

			if (is_object($field))
			{
				$fields[$counter++] = $field->xml_node;
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
		// See the file models/importsources _retrieveConfigFields
		foreach ($this->fields as $group)
		{
			foreach ($group as $field)
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
								'plugin' => $rule->plugin,
								'settings' => json_decode($rule->plugin_params),
								'field' => $field,
								'fields' => $this,
							)
						);
					}
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
		// Remove the skip and combine fields
		$data = array();

		if (!$this->getProcessRecord())
		{
			$this->log->add(JText::_('COM_CSVI_SKIPPED_RECORD_RULE_SETTINGS'), false);
			$this->log->addStats('skipped', JText::_('COM_CSVI_SKIPPED_RECORD_RULE_SETTINGS'));

			return $data;
		}

		foreach ($this->fields as $name => $field)
		{
			$new = array();

			foreach ($field as $fieldname => $fielddata)
			{
				if (isset($fielddata->field_name) && $fielddata->supported)
				{
					if (!in_array($fielddata->field_name, array('skip', 'combine')))
					{
						$new[$name]  = clone $fielddata;
						$data[$name] = $new;
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Checks if the field has a value, if not check if the user wants us to
	 * use the default value.
	 *
	 * @param   JObject  $details  The list of field details
	 *
	 * @return  true  returns validated value | return false if the column count does not match
	 *
	 * @since   6.0
	 */
	private function validateInput(JObject $details)
	{
		$fieldname = $details->xml_node;

		// Check if the field is published
		if ($details->supported)
		{
			// Check if the user wants ICEcat data
			if ($this->template->get('use_icecat', false, 'bool')
				&& !empty($this->icecatData)
				&& (array_key_exists($fieldname, $this->icecatData))
			)
			{
				$this->log->add(JText::sprintf('COM_CSVI_USE_ICECAT_FIELD', $fieldname), false);
				$newvalue = $this->icecatData[$fieldname];
			}
			else
			{
				if (isset($details->value) && strlen($details->value) > 0)
				{
					// Check if the field has a value
					$this->log->add(JText::sprintf('COM_CSVI_USE_FIELD_VALUE', $fieldname), false);
					$newvalue = trim($details->value);
				}
				else
				{
					// Field has no value, check if we can use default value
					$this->log->add(JText::sprintf('COM_CSVI_USE_DEFAULT_VALUE', $fieldname), false);
					$newvalue = $details->default_value;
				}
			}

			return $newvalue;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Set the ICEcat data.
	 *
	 * @param   array  $icecatData  An array with data retrieved from ICEcat.
	 *
	 * @return  void.
	 *
	 * @since   6.5.0
	 */
	public function setIcecatData($icecatData)
	{
		$this->icecatData = $icecatData;
	}

	/**
	 * unset the ICEcat data.
	 *
	 * @return  void.
	 *
	 * @since   6.5.6
	 */
	public function clearIcecatData()
	{
		$this->icecatData = array();
	}

	/**
	 * Update a given field with the given value.
	 *
	 * @param   object $field The field to update.
	 * @param   mixed $value The value to add to the field.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.5.2
	 */
	public function updateField($field, $value)
	{
		$this->set($field->xml_node, $value);

		return true;
	}

	/**
	 * Get a single field object.
	 *
	 * @param   string  $name  The name of the field to find.
	 *
	 * @return  object  The requested field.
	 *
	 * @since   6.5.2
	 */
	public function getField($name)
	{
		// Check if the field exists
		foreach ($this->fields as $group)
		{
			foreach ($group as $fieldname => $field)
			{
				$checkfield = $field->xml_node;

				if (!in_array($field->field_name, array('skip', 'combine', 'custom')))
				{
					$checkfield = $field->field_name;
				}

				// See if the name matches the field
				if ($name == $checkfield)
				{
					return $field;
				}
			}
		}

		return false;
	}

	/**
	 * Check the missing required fields
	 *
	 * @param   array  $requiredFields  An array of required fields
	 *
	 * @return  string Message is fields are missing.
	 *
	 * @since   7.1.0
	 */
	public function checkRequiredFields($requiredFields)
	{
		$importFields = $this->getFieldnames();
		$missingFields = array();
		$message = '';

		foreach ($requiredFields as $field)
		{
			if (strpos($field, '~') !== false)
			{
				$orFields = explode('~', $field);

				if (count(array_intersect($orFields, $importFields)) === 0)
				{
					$missingFields[] = $field;
				}
			}
			else
			{
				if (!in_array($field, $importFields))
				{
					$missingFields[] = $field;
				}
			}
		}

		// Check the collected missing required fields and put it all in message
		if (!empty($missingFields))
		{
			$fieldsAppend = '';

			foreach ($missingFields as $missingField)
			{
				if (strpos($missingField, '~') !== false)
				{
					$missingField = str_replace('~', ' or ', $missingField);
				}

				$fieldsAppend .= ' - ' . $missingField . '<br>';
			}

			$message = JText::sprintf('COM_CSVI_REQUIRED_FIELDS_MISSING', $fieldsAppend);
		}

		return $message;
	}
}
