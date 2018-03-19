<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaFields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace fields\com_fields\model\import;

defined('_JEXEC') or die;

/**
 * Fields import.
 *
 * @package     CSVI
 * @subpackage  JoomlaFields
 * @since       7.2.0
 */
class Fields extends \RantaiImportEngine
{
	/**
	 * Fields table
	 *
	 * @var    \FieldsTableFields
	 * @since  7.2.0
	 */
	private $fieldTable;

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
	 * @var    \Com_FieldsHelperCom_Fields
	 * @since  7.2.0
	 */
	protected $helper;

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
					case 'group_name':
						$this->setState('group_id', $this->helper->getGroupId($value));
						break;
					case 'category_id':
					case 'category_path':
						$categoryIds = array();

						if (strlen(trim($value)) > 0)
						{
							if (strpos($value, '|') > 0)
							{
								$categories = explode("|", $value);

								foreach ($categories as $category)
								{
									if (!is_numeric($category))
									{
										$categoryId = $this->helper->getCategoryId($category);

										if ($categoryId > 0)
										{
											$categoryIds[] = $categoryId;
										}
									}
									else
									{
										$categoryIds[] = $category;
									}
								}
							}
							else
							{
								$categoryId = $this->helper->getCategoryId($value);

								if ($categoryId > 0)
								{
									$categoryIds[] = $categoryId;
								}
							}

							$this->setState('category_ids', $categoryIds);
						}

						break;
					case 'state':
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
					case 'showlabel':
						switch (strtolower($value))
						{
							case 'n':
							case 'no':
							case '0':
								$value = 0;
								break;
							default:
								$value = 1;
								break;
						}

						$this->setState($name, $value);
						break;
					case 'show_on':
						switch (strtolower($value))
						{
							case 'site':
								$value = 1;
								break;
							case 'administrator':
								$value = 2;
								break;
							default:
								$value = '';
								break;
						}

						$this->setState('show_on', $value);
						break;
					case 'display':
						switch (strtolower($value))
						{
							case 'after title':
								$value = 1;
								break;
							case 'before display':
								$value = 2;
								break;
							case 'after display':
								$value = 3;
								break;
							default:
								$value = 0;
								break;
						}

						$this->setState('display', $value);
						break;
					case 'filter':
						switch (strtolower($value))
						{
							case 'text':
								$value = 'JComponentHelper::filterText';
								break;
							case 'alpha numeric':
								$value = 'alnum';
								break;
							case 'safe html':
								$value = 'safehtml';
								break;
							case 'telephone':
								$value = 'tel';
								break;
							case 'integer':
								$value = 'integer';
								break;
							case 'float':
								$value = 'float';
								break;
							case 'raw':
								$value = 'raw';
								break;
							default:
								$value = 0;
								break;
						}

						$this->setState('filter', $value);
						break;
					case 'context_type':
						switch (strtolower($value))
						{
							case 'category':
							case 'categories':
								$value = 'com_content.categories';
								break;
							case 'user':
								$value = 'com_users.user';
								break;
							case 'contact':
								$value = 'com_contact.contact';
								break;
							default:
								$value = 'com_content.article';
								break;
						}

						$this->setState('context', $value);
						break;
					case 'category_delete':
						switch (strtolower($value))
						{
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
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		// If no alias set use the title field
		$title = $this->getState('title', false);
		$name = $this->getState('name', false);

		// Create alias from title field
		if (!$name && $title)
		{
			$name = \JFilterOutput::stringURLSafe($title);
			$this->setState('name', $name);
		}

		if (!$title && !$name)
		{
			$this->loaded = false;
			$this->log->addStats('skipped', \JText::_('COM_CSVI_NO_TITLE_ALIAS_FIELDS_FOUND'));
		}
		else
		{
			$this->loaded = true;

			if (!$this->getState('id', false))
			{
				$this->setState('id', $this->helper->getFieldsId($this->getState('name', '')));
			}

			if ($this->fieldTable->load($this->getState('id', 0)))
			{
				if (!$this->template->get('overwrite_existing_data'))
				{
					$this->log->add(\JText::sprintf('COM_FIELDS_WARNING_OVERWRITING_SET_TO_NO', $this->getState('title')), false);
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
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('name', '')));
		}
		else
		{
			$this->processFieldParams();

			$this->processParams();

			// Check if we have a title
			if (!$this->fieldTable->title)
			{
				$this->fieldTable->title = $this->getState('name');
			}

			if (!$this->getState('id', false))
			{
				$this->fieldTable->created_time = $this->date->toSql();
				$this->fieldTable->created_user_id = $this->userId;
			}

			$this->fieldTable->modified_time = $this->date->toSql();

			if (!$this->getState('context', false))
			{
				$this->fieldTable->context = 'com_content.article';
			}

			if (!$this->getState('label', false))
			{
				$this->fieldTable->label = $this->getState('title');
			}

			if (!$this->getState('access', false))
			{
				$this->fieldTable->access = 1;
			}

			if (!$this->getState('language', false))
			{
				$this->fieldTable->language = '*';
			}

			try
			{
				$this->fieldTable->save($this->state);

				// Delete categories if needed
				if ($this->getState('category_delete', false))
				{
					$this->deleteFieldCategories($this->fieldTable->id);
				}

				$this->processCategories($this->fieldTable->id);

				$this->log->add('Field added successfully', false);
			}
			catch (\Exception $e)
			{
				$this->log->add('Cannot add field. Error: ' . $e->getMessage(), false);
				$this->log->addStats('incorrect', \JText::sprintf('COM_CSVI_TABLE_FIELDSTABLE_ERROR', $e->getMessage()));

				return false;
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
		$this->fieldTable = $this->getTable('Fields');

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
		$this->fieldTable->reset();
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
				'hint',
				'render_class',
				'class',
				'showlabel',
				'show_on',
				'display'
			);

			$params = json_decode($this->fieldTable->params);

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
	 * Process field params.
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 */
	private function processFieldParams()
	{
		if (!$this->getState('fieldparams', false))
		{
			$fields = array
			(
				'filter',
				'maxlength'
			);

			$fieldParams = json_decode($this->fieldTable->fieldparams);

			if (!is_object($fieldParams))
			{
				$fieldParams = new \stdClass;
			}

			foreach ($fields as $field)
			{
				$fieldValue = $this->getState($field, '');

				if (isset($fieldValue))
				{
					$fieldParams->$field = $this->getState($field, '');
				}
			}

			// Store the new params
			$this->setState('fieldparams', json_encode($fieldParams));
		}
	}

	/**
	 * Process the categories a field is assigned to.
	 *
	 * @param   int  $fieldId  The ID of the field the categories belong to.
	 *
	 * @return  void
	 *
	 * @since   7.2.0
	 */
	private function processCategories($fieldId)
	{
		if (!$fieldId || !$this->getState('category_ids', false))
		{
			return;
		}

		if (!$this->getState('category_delete', false))
		{
			$this->deleteFieldCategories($fieldId);
		}

		if ($this->getState('category_ids', false))
		{
			$categoryIds = array_unique($this->getState('category_ids', false));
			$query = $this->db->getQuery(true)
				->insert($this->db->quoteName('#__fields_categories'))
				->columns(
					$this->db->quoteName(
						array(
							'field_id',
							'category_id'
						)
					)
				);

			foreach ($categoryIds as $catId)
			{
				if ($catId && $fieldId)
				{
					$query->values((int) $fieldId . ',' . (int) $catId);
				}
			}

			$this->db->setQuery($query);
			$this->log->add('Insert the field category id');
			$this->db->execute();
		}
	}

	/**
	 * Delete the categories a field is assigned to.
	 *
	 * @param   int  $fieldId  The ID of the field the categories belong to.
	 *
	 * @return  void
	 *
	 * @since   7.2.0
	 */
	private function deleteFieldCategories($fieldId)
	{
		if (!$fieldId)
		{
			return;
		}

		$query = $this->db->getQuery(true)
			->delete($this->db->quoteName('#__fields_categories'))
			->where($this->db->quoteName('field_id') . ' = ' . (int) $fieldId);

		$this->db->setQuery($query)->execute();
		$this->log->add('Delete previous field categories to make new inserts');
	}
}
