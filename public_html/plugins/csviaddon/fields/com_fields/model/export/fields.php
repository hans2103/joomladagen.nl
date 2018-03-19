<?php
/**
 * @package     CSVI
 * @subpackage  Form2Content
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace fields\com_fields\model\export;

defined('_JEXEC') or die;

/**
 * Export Joomla fields.
 *
 * @package     CSVI
 * @subpackage  Joomla fields
 * @since       7.2.0
 */
class Fields extends \CsviModelExports
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
					case 'category_path':
						$userFields[] = $this->db->quoteName('fc.category_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('fc.category_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('fc.category_id');
						}
						break;
					case 'group_name':
						$userFields[] = $this->db->quoteName('f.group_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('f.group_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('f.group_id');
						}
						break;
					case 'context_type':
						$userFields[] = $this->db->quoteName('f.context');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('f.context');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('f.context');
						}
						break;
					case 'group_id':
					case 'title':
					case 'name':
					case 'label':
					case 'default_value':
					case 'type':
					case 'id':
					case 'note':
					case 'asset_id':
					case 'context':
					case 'description':
					case 'state':
					case 'required':
					case 'ordering':
					case 'checked_out':
					case 'checked_out_time':
					case 'language':
					case 'created_time':
					case 'created_user_id':
					case 'modified_time':
					case 'modified_by':
					case 'access':
						$userFields[] = $this->db->quoteName('f.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('f.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('f.' . $field->field_name);
						}
						break;
					case 'hint':
					case 'render_class':
					case 'class':
					case 'showlabel':
					case 'show_on':
					case 'display':
						$userFields[] = $this->db->quoteName('f.params');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('f.params');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('f.params');
						}
						break;
					case 'filter':
					case 'maxlength':
						$userFields[] = $this->db->quoteName('f.fieldparams');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('f.fieldparams');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('f.fieldparams');
						}
						break;
					case 'field_id':
					case 'category_id':
						$userFields[] = $this->db->quoteName('fc.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('fc.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('fc.' . $field->field_name);
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
			$query->from($this->db->quoteName('#__fields', 'f'));
			$query->leftJoin(
				$this->db->quoteName('#__fields_categories', 'fc') . ' ON ' .
				$this->db->quoteName('fc.field_id') . ' = ' . $this->db->quoteName('f.id')
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

					// Clean some settings
					$params  = '';
					$fieldParams = '';

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
							case 'group_name':
								$query->clear()
									->select($this->db->quoteName('title'))
									->from($this->db->quoteName('#__fields_groups'))
									->where($this->db->quoteName('id') . ' = ' . (int) $record->group_id);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
								break;
							case 'category_path':
								$query->clear()
									->select($this->db->quoteName('path'))
									->from($this->db->quoteName('#__categories'))
									->where($this->db->quoteName('id') . ' = ' . (int) $record->category_id);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
								break;
							case 'context_type':
								$context = explode('.', $record->context);
								$fieldValue = $context[1];
								break;
							case 'hint':
							case 'render_class':
							case 'class':
							case 'showlabel':
							case 'show_on':
							case 'display':
								if (empty($params))
								{
									$params = json_decode($record->params);
								}

								if (isset($params->$fieldName))
								{
									$fieldValue = $params->$fieldName;
								}
								break;
							case 'filter':
							case 'maxlength':
								if (empty($fieldParams))
								{
									$fieldParams = json_decode($record->fieldparams);
								}

								if (isset($fieldParams->$fieldName))
								{
									$fieldValue = $fieldParams->$fieldName;
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
