<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaModule
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace modules\com_modules\model\export;

defined('_JEXEC') or die;

/**
 * Export Joomla Modules.
 *
 * @package     CSVI
 * @subpackage  JoomlaModule
 * @since       7.4.0
 */
class Modules extends \CsviModelExports
{
	/**
	 * Export the data.
	 *
	 * @return  void.
	 *
	 * @since   7.4.0
	 *
	 * @throws  \CsviException
	 * @throws  \RuntimeException
	 */
	protected function exportBody()
	{
		if (parent::exportBody())
		{
			// Build something fancy to only get the fieldnames the user wants
			$userFields   = array();
			$exportFields = $this->fields->getFields();

			// Group by fields
			$groupByFields = json_decode($this->template->get('groupbyfields', '', 'string'));
			$groupBy       = array();

			if (isset($groupbyFields->name))
			{
				$groupByFields = array_flip($groupByFields->name);
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

			$userFields[] = $this->db->quoteName('modules.id');

			foreach ($exportFields as $field)
			{
				switch ($field->field_name)
				{
					case 'access':
					case 'checked_out':
					case 'checked_out_time':
					case 'content':
					case 'language':
					case 'module':
					case 'note':
					case 'ordering':
					case 'params':
					case 'position':
					case 'published':
					case 'publish_down':
					case 'publish_up':
					case 'showtitle':
					case 'title':
					case 'id':
					case 'asset_id':
					case 'client_id':
						$userFields[] = $this->db->quoteName('modules.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('modules.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('modules.' . $field->field_name);
						}
						break;
					case 'menuid':
					case 'moduleid':
						$userFields[] = $this->db->quoteName('modules_menu.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('modules_menu.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('modules_menu.' . $field->field_name);
						}
						break;
					case 'menu_alias':
						$userFields[] = $this->db->quoteName('modules_menu.menuid');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('modules_menu.menuid');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('modules_menu.menuid');
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
			$query->from($this->db->quoteName('#__modules', 'modules'))
				->leftJoin(
					$this->db->quoteName('#__modules_menu', 'modules_menu')
					. ' ON ' . $this->db->quoteName('modules.id') . ' = ' . $this->db->quoteName('modules_menu.menuid')
				);

			// Filter by published state
			$publishState = $this->template->get('publish_state');

			if ($publishState != '' && ($publishState == 1 || $publishState == 0))
			{
				$query->where($this->db->quoteName('modules.published') . ' = ' . (int) $publishState);
			}

			// Filter by language
			$language = $this->template->get('content_language');

			if ($language != '*')
			{
				$query->where($this->db->quoteName('modules.language') . ' = ' . $this->db->quote($language));
			}

			// Group the fields
			$groupBy = array_unique($groupBy);

			if (!empty($groupBy))
			{
				$query->group($groupBy);
			}

			// Sort set fields
			$sortBy = array_unique($sortBy);

			if (!empty($sortBy))
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
			$logcount = $this->db->getNumRows();

			if ($logcount > 0)
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
							case 'access':
							case 'checked_out':
							case 'content':
							case 'language':
							case 'module':
							case 'note':
							case 'ordering':
							case 'params':
							case 'position':
							case 'published':
							case 'showtitle':
							case 'title':
							case 'id':
							case 'client_id':
								$fieldValue = '';

								if ($record->$fieldName)
								{
									$fieldValue = $record->$fieldName;
								}
								break;
							case 'menuid':
							case 'menu_alias':
								$fieldValue = '';
								$moduleId = $record->id;

								if ($moduleId)
								{
									$query->clear()
										->select(array($this->db->quoteName('menuid')))
										->from($this->db->quoteName('#__modules_menu'))
										->where($this->db->quoteName('moduleid') . ' = ' . (int) $moduleId);
									$this->db->setQuery($query);
									$menuIds   = $this->db->loadAssocList();
									$menuArray = array();

									foreach ($menuIds as $key => $menuId)
									{
										$menuArray[] = $menuId['menuid'];
									}

									$fieldValue = implode('|', $menuArray);

									if ($fieldName === 'menu_alias')
									{
										$aliasArray = array();

										foreach ($menuArray as $menu)
										{
											$query->clear()
												->select(array($this->db->quoteName('alias')))
												->from($this->db->quoteName('#__menu'))
												->where($this->db->quoteName('id') . ' = ' . (int) $menu);
											$this->db->setQuery($query);
											$menuAlias    = $this->db->loadResult();
											$aliasArray[] = $menuAlias;
											$fieldValue   = implode('|', $aliasArray);
										}
									}
								}

								break;
							case 'moduleid':
								$fieldValue = $record->id;
								break;
							case 'checked_out_time':
							case 'publish_down':
							case 'publish_up':
								$fieldValue = $this->fields->getDateFormat($fieldName, $record->$fieldName);
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
