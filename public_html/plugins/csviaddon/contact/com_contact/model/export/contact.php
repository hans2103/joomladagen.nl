<?php
/**
 * @package     CSVI
 * @subpackage  Contacts
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace contact\com_contact\model\export;

defined('_JEXEC') or die;

/**
 * Export Joomla contacts.
 *
 * @package     CSVI
 * @subpackage  Joomla contacts
 * @since       7.2.0
 */
class Contact extends \CsviModelExports
{
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
	 * @since   7.2.0
	 */
	protected function exportBody()
	{
		if (parent::exportBody())
		{
			$this->loadCustomFields();

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
			$userFields[] = $this->db->quoteName('contact_details.id');

			foreach ($exportFields as $field)
			{
				switch ($field->field_name)
				{
					case 'category_path':
						$userFields[] = $this->db->quoteName('contact_details.catid');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('contact_details.catid');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('contact_details.catid');
						}
						break;
					case 'email':
						$userFields[] = $this->db->quoteName('contact_details.user_id');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('contact_details.user_id');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('contact_details.user_id');
						}
						break;
					case 'id':
					case 'alias':
					case 'name':
					case 'con_position':
					case 'address':
					case 'suburb':
					case 'state':
					case 'country':
					case 'postcode':
					case 'telephone':
					case 'fax':
					case 'misc':
					case 'image':
					case 'email_to':
					case 'default_con':
					case 'published':
					case 'checked_out':
					case 'checked_out_time':
					case 'ordering':
					case 'params':
					case 'user_id':
					case 'catid':
					case 'access':
					case 'mobile':
					case 'webpage':
					case 'sortname1':
					case 'sortname2':
					case 'sortname3':
					case 'language':
					case 'created':
					case 'created_by':
					case 'created_by_alias':
					case 'modified':
					case 'modified_by':
					case 'metakey':
					case 'metadesc':
					case 'metadata':
					case 'featured':
					case 'xreference':
					case 'publish_up':
					case 'publish_down':
					case 'version':
					case 'hits':
						$userFields[] = $this->db->quoteName('contact_details.' . $field->field_name);

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('contact_details.' . $field->field_name);
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('contact_details.' . $field->field_name);
						}
						break;
					case 'show_contact_category':
					case 'show_contact_list':
					case 'presentation_style':
					case 'show_tags':
					case 'show_info':
					case 'show_name':
					case 'show_position':
					case 'show_email':
					case 'show_street_address':
					case 'show_suburb':
					case 'show_state':
					case 'show_postcode':
					case 'show_country':
					case 'show_telephone':
					case 'show_mobile':
					case 'show_fax':
					case 'show_webpage':
					case 'show_image':
					case 'show_misc':
					case 'allow_vcard':
					case 'show_articles':
					case 'articles_display_num':
					case 'show_profile':
					case 'show_links':
					case 'linka_name':
					case 'linka':
					case 'linkb_name':
					case 'linkb':
					case 'linkc_name':
					case 'linkc':
					case 'linkd_name':
					case 'linkd':
					case 'linke_name':
					case 'linke':
					case 'contact_layout':
					case 'show_email_form':
					case 'show_email_copy':
					case 'banned_email':
					case 'banned_subject':
					case 'banned_text':
					case 'validate_session':
					case 'custom_reply':
					case 'redirect':
						$userFields[] = $this->db->quoteName('contact_details.params');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('contact_details.params');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('contact_details.params');
						}
						break;
					case 'robots':
					case 'rights':
						$userFields[] = $this->db->quoteName('contact_details.metadata');

						if (array_key_exists($field->field_name, $groupByFields))
						{
							$groupBy[] = $this->db->quoteName('contact_details.metadata');
						}

						if (array_key_exists($field->field_name, $sortByFields))
						{
							$sortBy[] = $this->db->quoteName('contact_details.metadata');
						}
						break;
					case 'custom':
						break;
					default:
						if (!in_array($field->field_name, $this->customFields))
						{
							$userFields[] = $this->db->quoteName($field->field_name);

							if (array_key_exists($field->field_name, $groupByFields))
							{
								$groupBy[] = $this->db->quoteName($field->field_name);
							}

							if (array_key_exists($field->field_name, $sortByFields))
							{
								$sortBy[] = $this->db->quoteName($field->field_name);
							}
						}

						break;
				}
			}

			// Build the query
			$userFields = array_unique($userFields);
			$query      = $this->db->getQuery(true);
			$query->select(implode(",\n", $userFields));
			$query->from($this->db->quoteName('#__contact_details', 'contact_details'));
			$query->leftJoin(
				$this->db->quoteName('#__users', 'user') . ' ON ' .
				$this->db->quoteName('contact_details.user_id') . ' = ' . $this->db->quoteName('user.id')
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
					$metaData = '';

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
							case 'email':
								$query->clear()
									->select($this->db->quoteName('email'))
									->from($this->db->quoteName('#__users'))
									->where($this->db->quoteName('id') . ' = ' . (int) $record->user_id);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
								break;
							case 'category_path':
								$query->clear()
									->select($this->db->quoteName('path'))
									->from($this->db->quoteName('#__categories'))
									->where($this->db->quoteName('id') . ' = ' . (int) $record->catid);
								$this->db->setQuery($query);
								$fieldValue = $this->db->loadResult();
								break;
							case 'show_contact_category':
							case 'show_contact_list':
							case 'presentation_style':
							case 'show_tags':
							case 'show_info':
							case 'show_name':
							case 'show_position':
							case 'show_email':
							case 'show_street_address':
							case 'show_suburb':
							case 'show_state':
							case 'show_postcode':
							case 'show_country':
							case 'show_telephone':
							case 'show_mobile':
							case 'show_fax':
							case 'show_webpage':
							case 'show_image':
							case 'show_misc':
							case 'allow_vcard':
							case 'show_articles':
							case 'articles_display_num':
							case 'show_profile':
							case 'show_links':
							case 'linka_name':
							case 'linka':
							case 'linkb_name':
							case 'linkb':
							case 'linkc_name':
							case 'linkc':
							case 'linkd_name':
							case 'linkd':
							case 'linke_name':
							case 'linke':
							case 'contact_layout':
							case 'show_email_form':
							case 'show_email_copy':
							case 'banned_email':
							case 'banned_subject':
							case 'banned_text':
							case 'validate_session':
							case 'custom_reply':
							case 'redirect':
								if (empty($params))
								{
									$params = json_decode($record->params);
								}

								if (isset($params->$fieldName))
								{
									$fieldValue = $params->$fieldName;
								}
								break;
							case 'robots':
							case 'rights':
								if (empty($metaData))
								{
									$metaData = json_decode($record->metadata);
								}

								if (isset($metaData->$fieldName))
								{
									$fieldValue = $metaData->$fieldName;
								}
								break;
							default:
								if (in_array($fieldName, $this->customFields))
								{
									$query->clear()
										->select($this->db->quoteName('id'))
										->from($this->db->quoteName('#__fields'))
										->where($this->db->quoteName('name') . '  = ' . $this->db->quote($fieldName));
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
									if ($this->fields->checkCustomFieldType($fieldName, 'imagelist'))
									{
										$fieldArray = array();

										foreach ($fieldResult as $result)
										{
											$fieldArray[] = $result->value;
										}

										$fieldValue = implode('|', $fieldArray);

									}
									else
									{
										if (!empty($fieldResult))
										{
											$fieldValue = $fieldResult[0]->value;
										}
									}
									
									if ($fieldValue && $this->fields->checkCustomFieldType($fieldName, 'calendar'))
									{
										$fieldValue = $this->fields->getDateFormat($fieldName, $fieldValue, $field->column_header);
									}
								}

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
			->where($this->db->quoteName('context') . ' = ' . $this->db->quote('com_contact.contact'));
		$this->db->setQuery($query);
		$results = $this->db->loadObjectList();

		foreach ($results as $result)
		{
			$this->customFields[] = $result->name;
		}

		$this->log->add('Load the Joomla custom fields for contact');
	}
}
