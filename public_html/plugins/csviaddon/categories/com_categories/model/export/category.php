<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaCategory
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace categories\com_categories\model\export;

defined('_JEXEC') or die;

/**
 * Export Joomla Categories.
 *
 * @package     CSVI
 * @subpackage  JoomlaCategory
 * @since       6.0
 */
class Category extends \CsviModelExports
{
	/**
	 * The custom fields that from other extensions.
	 *
	 * @var    array
	 * @since  6.5.0
	 */
	private $pluginfieldsExport = array();

	/**
	 * List of available custom fields
	 *
	 * @var    array
	 * @since  7.2.0
	 */
	private $customFields = array();

	/**
	 * Export the data.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	protected function exportBody()
	{
		if (parent::exportBody())
		{
			// Get some basic data
			$this->loadPluginFields();
			$this->loadCustomFields();

			// Build something fancy to only get the fieldnames the user wants
			$userfields = array();
			$exportfields = $this->fields->getFields();
			$userfields[] = $this->db->quoteName('c.id');

			foreach ($exportfields as $field)
			{
				switch ($field->field_name)
				{
					case 'category_path':
						$userfields[] = $this->db->quoteName('c.path');
						break;
					case 'meta_author':
					case 'meta_robots':
						$userfields[] = $this->db->quoteName('c.metadata');
						break;
					case 'category_layout':
					case 'image':
						$userfields[] = $this->db->quoteName('c.params');
						break;
					case 'custom':
						break;
					default:
						// Do not include custom fields into the query
						if (!in_array($field->field_name, $this->pluginfieldsExport)
							&& !in_array($field->field_name, $this->customFields))
						{
							$userfields[] = $this->db->quoteName($field->field_name);
						}
						break;
				}
			}

			// Build the query
			$userfields = array_unique($userfields);
			$query = $this->db->getQuery(true);
			$query->select(implode(",\n", $userfields));
			$query->from($this->db->quoteName('#__categories', 'c'));

			// Make sure the ID is always greater than 0 as we don't want to export the root
			$query->where('asset_id > 0');

			// Filter by published state
			$publish_state = $this->template->get('publish_state');

			if ($publish_state != '' && ($publish_state == 1 || $publish_state == 0))
			{
				$query->where($this->db->quoteName('e.published') . ' = ' . (int) $publish_state);
			}

			// Add a limit if user wants us to
			$limits = $this->getExportLimit();

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

						// Process the field
						switch ($fieldname)
						{
							case 'category_path':
								$fieldvalue = $record->path;
								break;
							case 'meta_author':
							case 'meta_robots':
								$metadata = json_decode($record->metadata);

								if (isset($metadata->$fieldname))
								{
									$fieldvalue = $metadata->$fieldname;
								}
								break;
							case 'category_layout':
							case 'image':
								$params = json_decode($record->params);

								if (isset($params->$fieldname))
								{
									$fieldvalue = $params->$fieldname;
								}
								break;
							default:
								if (in_array($fieldname, $this->pluginfieldsExport))
								{
									$fieldvalue = '';

									// Get value from content plugin
									$dispatcher = new \RantaiPluginDispatcher;
									$dispatcher->importPlugins('csviext', $this->db);
									$result = $dispatcher->trigger(
										'onExportContent',
										array(
											'extension' => 'joomla',
											'operation' => 'category',
											'id' => $record->id,
											'fieldname' => $fieldname,
											'log' => $this->log
										)
									);

									if (isset($result[0]))
									{
										$fieldvalue = $result[0];
									}
								}

								if (in_array($fieldname, $this->customFields))
								{
									$fieldvalue = '';
									$query->clear()
										->select($this->db->quoteName('id'))
										->from($this->db->quoteName('#__fields'))
										->where($this->db->quoteName('name') . '  = ' . $this->db->quote($fieldname));
									$this->db->setQuery($query);
									$fieldId = $this->db->loadResult();
									$itemId = $record->id;

									$query->clear()
										->select($this->db->quoteName('value'))
										->from($this->db->quoteName('#__fields_values'))
										->where($this->db->quoteName('field_id') . ' = ' . (int) $fieldId)
										->where($this->db->quoteName('item_id') . ' = ' . (int) $itemId);
									$this->db->setQuery($query);
									$fieldResult = $this->db->loadObjectList();

									// Check if the custom field is a multiple image list
									if ($this->fields->checkCustomFieldType($fieldname, 'imagelist'))
									{
										$fieldArray = array();

										foreach ($fieldResult as $result)
										{
											$fieldArray[] = $result->value;
										}

										$fieldvalue = implode('|', $fieldArray);

									}
									else
									{
										if (!empty($fieldResult))
										{
											$fieldvalue = $fieldResult[0]->value;
										}
									}


									if ($fieldvalue && $this->fields->checkCustomFieldType($fieldname, 'calendar'))
									{
										$fieldvalue = $this->fields->getDateFormat($fieldname, $fieldvalue, $field->column_header);
									}
								}

								break;
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
				$this->addExportContent('COM_CSVI_NO_DATA_FOUND');

				// Output the contents
				$this->writeOutput();
			}
		}
	}

	/**
	 * Get a list of plugin fields that can be used as available field.
	 *
	 * @return  void.
	 *
	 * @since   6.5.0
	 */
	private function loadPluginFields()
	{
		$dispatcher = new \RantaiPluginDispatcher;
		$dispatcher->importPlugins('csviext', $this->db);
		$result = $dispatcher->trigger(
			'getAttributes',
			array(
				'extension' => 'joomla',
				'operation' => 'category',
				'log' => $this->log
			)
		);

		if (is_array($result) && !empty($result))
		{
			$this->pluginfieldsExport = array_merge($this->pluginfieldsExport, $result[0]);
		}
	}

	/**
	 * Get a list of custom fields that can be used as available field.
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 *
	 * @throws  \Exception
	 */
	private function loadCustomFields()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('name'))
			->from($this->db->quoteName('#__fields'))
			->where($this->db->quoteName('state') . ' = 1')
			->where($this->db->quoteName('context') . ' = ' . $this->db->quote('com_content.categories'));
		$this->db->setQuery($query);
		$results = $this->db->loadObjectList();

		foreach ($results as $result)
		{
			$this->customFields[] = $result->name;
		}

		$this->log->add('Load the Joomla custom fields for categories');
	}
}
