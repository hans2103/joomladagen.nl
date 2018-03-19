<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaContacts
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace contact\com_contact\model\import;

defined('_JEXEC') or die;

/**
 * Fields import.
 *
 * @package     CSVI
 * @subpackage  JoomlaContacts
 * @since       7.2.0
 */
class Contact extends \RantaiImportEngine
{
	/**
	 * CSVI fields
	 *
	 * @var    \CsviHelperImportFields
	 * @since  7.2.0
	 */
	protected $fields;

	/**
	 * The addon helper
	 *
	 * @var    \Com_ContactHelperCom_Contact
	 * @since  7.2.0
	 */
	protected $helper;

	/**
	 * Fields table
	 *
	 * @var    \ContactTableContact
	 * @since  7.2.0
	 */
	private $contactsTable;

	/**
	 * List of available custom fields
	 *
	 * @var    array
	 * @since  7.2.0
	 */
	private $customFields = '';

	/**
	 * Run this before we start.
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 */
	public function onBeforeStart()
	{
		// Load the tables that will contain the data
		$this->loadCustomFields();
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
		$this->customFields = $this->db->loadObjectList();

		$this->log->add('Load the Joomla custom fields for contacts');
	}

	/**
	 * Start the menu import process.
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   7.2.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 */
	public function getStart()
	{
		// Process data
		foreach ($this->fields->getData() as $fields)
		{
			foreach ($fields as $name => $details)
			{
				$value = $details->value;

				switch ($name)
				{
					case 'email':
						$this->setState('user_id', $this->helper->getUserId($value));
						break;
					case 'published':
						switch (strtolower($value))
						{
							case '-2':
								$value = -2;
								break;
							case 'y':
							case 'yes':
							case '1':
								$value = 1;
								break;
							default:
								$value = 0;
								break;
						}

						$this->setState($name, $value);
						break;
					case 'show_contact_category':
					case 'show_contact_list':
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
					case 'show_articles':
					case 'show_profile':
					case 'show_links':
					case 'allow_vcard':
					case 'show_email_form':
					case 'show_email_copy':
					case 'validate_session':
					case 'custom_reply':
						switch (strtolower($value))
						{
							case 'n':
							case 'no':
							case '0':
								$value = 0;
								break;
							case 'y':
							case 'yes':
							case '1':
								$value = 1;
								break;
							default:
								$value = '';
								break;
						}

						$this->setState($name, $value);
						break;
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		// If no alias set use the title field
		$alias = $this->getState('alias', false);
		$name  = $this->getState('name', false);

		// Create alias from name field
		if ($name && !$alias)
		{
			$alias = \JFilterOutput::stringURLSafe($name);
			$this->setState('alias', $alias);
		}

		if (!$alias && !$name)
		{
			$this->loaded = false;
			$this->log->addStats('skipped', \JText::_('COM_CSVI_NO_NAME_ALIAS_FIELDS_FOUND'));
		}
		else
		{
			$this->loaded = true;

			if (!$this->getState('id', false))
			{
				$this->setState('id', $this->helper->getContactId($this->getState('alias', '')));
			}

			if ($this->contactsTable->load($this->getState('id', 0)))
			{
				if (!$this->template->get('overwrite_existing_data'))
				{
					$this->log->add(\JText::sprintf('COM_CONTACTS_WARNING_OVERWRITING_SET_TO_NO', $this->getState('alias')), false);
					$this->loaded = false;
				}
			}
		}

		return true;
	}

	/**
	 * Process a record.
	 *
	 * @return  bool  Returns true if all is OK | Returns false if no path or menu ID can be found.
	 *
	 * @since   7.2.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 */
	public function getProcessRecord()
	{
		if (!$this->loaded)
		{
			return false;
		}

		if (!$this->getState('id', false) && $this->template->get('ignore_non_exist'))
		{
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('alias', '')));
		}
		else
		{
			// We have the category ID, lets see if it should be deleted
			if ($this->getState('contact_delete', 'N') === 'Y')
			{
				$this->deleteContact();
			}
			else
			{
				$this->processParams();
				$this->processMetaData();

				if (!$this->getState('id', false))
				{
					$this->contactsTable->created    = $this->date->toSql();
					$this->contactsTable->created_by = $this->userId;

					if (!$this->getState('language', false))
					{
						$this->setState('language', '*');
						$this->contactsTable->language = '*';
					}
				}

				$this->contactsTable->modified    = $this->date->toSql();
				$this->contactsTable->modified_by = $this->userId;

				if ($this->getState('category_path', false))
				{
					$categoryId = $this->helper->getCategoryId($this->getState('category_path', false), $this->getState('alias', false));
					$this->setState('catid', $categoryId);
				}

				if (!$this->getState('access', false))
				{
					$this->contactsTable->access = 1;
				}

				if (!$this->getState('catid', false))
				{
					$this->contactsTable->catid = 4;
				}

				$this->contactsTable->bind($this->state);

				try
				{
					$this->contactsTable->save($this->state);
					$this->log->add('Contact added successfully', false);
					$this->processCustomFields($this->contactsTable->id);

					if ($this->getState('associations', false))
					{
						$this->processAssociations($this->contactsTable->id);
					}
				}
				catch (\Exception $e)
				{
					$this->log->add('Cannot add contact. Error: ' . $e->getMessage(), false);
					$this->log->addStats('incorrect', \JText::sprintf('COM_CSVI_TABLE_CONTACTTABLE_ERROR', $e->getMessage()));

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Process associated contacts
	 *
	 * @param   int  $contactId  Id of the contact
	 *
	 * @return  bool True if all ok False otherwise.
	 *
	 * @since   7.2.0
	 */
	private function processAssociations($contactId)
	{
		if ($this->getState('language', false) === '*')
		{
			$this->log->add('Association cannot be added if language set to all');

			return false;
		}

		$associations                                     = array();
		$associatedContacts                               = explode('|', $this->getState('associations', false));
		$associationsContext                              = 'com_contact.item';
		$associations[$this->getState('language', false)] = $contactId;

		foreach ($associatedContacts as $contact)
		{
			$associatedDetails   = explode('#', $contact);
			$languageTags        = explode('-', $associatedDetails[0]);
			$languageTags[1]     = strtoupper($languageTags[1]);
			$language            = implode('-', $languageTags);
			$contactAlias        = $associatedDetails[1];
			$associatedContactId = $this->helper->getContactId($contactAlias);

			if ($associatedContactId)
			{
				$associations[$language] = (int) $associatedContactId;
			}
			else
			{
				$this->log->add('No associated contact id found with alias  ' . $contactAlias);
			}
		}

		$associations = array_unique($associations);

		if ((count($associations)) > 1)
		{
			$key   = md5(json_encode($associations));
			$query = $this->db->getQuery(true)
				->insert('#__associations');

			foreach ($associations as $arrayVal => $id)
			{
				$this->deleteAssociation($id, $associationsContext);
				$query->values(((int) $id) . ',' . $this->db->quote($associationsContext) . ',' . $this->db->quote($key));
			}

			$this->db->setQuery($query)->execute();
			$this->log->add('Associations added for contact');
		}

		return true;
	}

	/**
	 * Delete a association
	 *
	 * @param   int  $id       Id of the contact
	 * @param   int  $context  Context of com_contact
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 */
	private function deleteAssociation($id, $context)
	{
		if ($id)
		{
			$query = $this->db->getquery(true)
				->delete($this->db->quotename('#__associations'))
				->where($this->db->quotename('id') . ' = ' . (int) $id)
				->where($this->db->quotename('context') . ' = ' . $this->db->quote($context));
			$this->db->setquery($query);
			$this->log->add('Association contact deleted');
			$this->db->execute();
		}
	}

	/**
	 * Delete a contact detail
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 */
	private function deleteContact()
	{
		if ($this->getState('id', false))
		{
			$query = $this->db->getquery(true)
				->delete($this->db->quotename('#__contact_details'))
				->where($this->db->quotename('id') . ' = ' . (int) $this->getState('id'));
			$this->db->setquery($query);
			$this->log->add('Contact deleted');
			$this->db->execute();

			// Delete the related association
			$this->deleteAssociation($this->getState('id'), 'com_contact.item');
		}
	}

	/**
	 * Process params field.
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 */
	private function processParams()
	{
		if (!$this->getState('params', false))
		{
			$paramsFields = array
			(
				'show_contact_category',
				'show_contact_list',
				'presentation_style',
				'show_tags',
				'show_info',
				'show_name',
				'show_position',
				'show_email',
				'show_street_address',
				'show_suburb',
				'show_state',
				'show_postcode',
				'show_country',
				'show_telephone',
				'show_mobile',
				'show_fax',
				'show_webpage',
				'show_image',
				'show_misc',
				'allow_vcard',
				'show_articles',
				'articles_display_num',
				'show_profile',
				'show_links',
				'linka_name',
				'linka',
				'linkb_name',
				'linkb',
				'linkc_name',
				'linkc',
				'linkd_name',
				'linkd',
				'linke_name',
				'linke',
				'contact_layout',
				'show_email_form',
				'show_email_copy',
				'banned_email',
				'banned_subject',
				'banned_text',
				'validate_session',
				'custom_reply',
				'redirect'
			);

			$params = json_decode($this->contactsTable->params);

			if (!is_object($params))
			{
				$params = new \stdClass;
			}

			foreach ($paramsFields as $field)
			{
				$fieldValue = $this->getState($field, '');

				if (isset($fieldValue))
				{
					$params->$field = $this->getState($field, '');
				}
			}

			// Store the new params
			$this->setState('params', json_encode($params));
		}
	}

	/**
	 * Process the meta data.
	 *
	 * @return  void.
	 *
	 * @since   7.0
	 */
	private function processMetaData()
	{
		if (!$this->getState('metadata', false))
		{
			$metadataFields = array
			(
				'robots',
				'rights'
			);

			// Load the current images
			$metadata = json_decode($this->contactsTable->metadata);

			if (!is_object($metadata))
			{
				$metadata = new \stdClass;
			}

			foreach ($metadataFields as $field)
			{
				$metadata->$field = $this->getState($field, '');
			}

			// Store the new metadata
			$this->setState('metadata', json_encode($metadata));
		}
	}

	/**
	 * Update custom fields data.
	 *
	 * @param   int  $id  Id of the contact
	 *
	 * @return  bool Returns true if all is OK | Returns false otherwise
	 *
	 * @since   7.2.0
	 */
	private function processCustomFields($id)
	{
		if (count($this->customFields) === 0)
		{
			$this->log->add('No custom fields found for contacts', false);

			return false;
		}

		foreach ($this->customFields as $field)
		{
			$fieldName = $field->name;

			if ($this->getState($fieldName, ''))
			{
				$query = $this->db->getQuery(true);
				$query->select($this->db->quoteName('id'))
					->from($this->db->quoteName('#__fields'))
					->where($this->db->quoteName('name') . '  = ' . $this->db->quote($fieldName));
				$this->db->setQuery($query);
				$fieldId = $this->db->loadResult();

				if ($this->getState('catid', false) && $fieldId)
				{
					$query->clear()
						->select(
							$this->db->quoteName(
								array(
									'field_id',
									'category_id'
								)
							)
						)
						->from($this->db->quoteName('#__fields_categories'))
						->where($this->db->quoteName('field_id') . ' = ' . (int) $fieldId)
						->where($this->db->quoteName('category_id') . ' = ' . (int) $this->getState('catid', false));
					$this->db->setQuery($query);
					$result = $this->db->loadResult();

					if (!$result)
					{
						$this->log->add('Custom field ' . $fieldName . ' is not assigned to given category');
					}
				}

				if ($fieldId && $id)
				{
					$importValues = explode('|', $this->getState($fieldName, ''));
					$query->clear()
						->delete($this->db->quoteName('#__fields_values'))
						->where($this->db->quoteName('field_id') . ' = ' . (int) $fieldId)
						->where($this->db->quoteName('item_id') . ' = ' . (int) $id);
					$this->db->setQuery($query)->execute();
					$this->log->add('Removed existing custom field values');

					$query->clear()
						->insert($this->db->quoteName('#__fields_values'))
						->columns($this->db->quoteName(array('field_id', 'item_id', 'value')));

					foreach ($importValues as $fieldValues)
					{
						$query->values(
							(int) $fieldId . ',' .
							(int) $id . ',' .
							$this->db->quote($fieldValues)
						);
					}

					$this->db->setQuery($query)->execute();
					$this->log->add('Custom field values added for contacts');
				}
			}
		}

		return true;
	}

	/**
	 * Load the necessary tables.
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 */
	public function loadTables()
	{
		$this->contactsTable = $this->getTable('Contact');
	}

	/**
	 * Clear the loaded tables.
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 */
	public function clearTables()
	{
		$this->contactsTable->reset();
	}
}
