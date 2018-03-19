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
 * FieldsGroups import.
 *
 * @package     CSVI
 * @subpackage  JoomlaFieldsGroups
 * @since       7.2.0
 */
class FieldGroups extends \RantaiImportEngine
{
	/**
	 * Groups table
	 *
	 * @var    \FieldsTableFields
	 * @since  7.2.0
	 */
	private $fieldGroupTable;

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
	 * Start the groups import process.
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
							default:
								$value = 'com_content.article';
								break;
						}

						$this->setState('context', $value);
						break;
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		// If no alias set use the title field
		$title = $this->getState('title', false);

		if (!$title)
		{
			$this->loaded = false;
			$this->log->addStats('skipped', \JText::_('COM_CSVI_NO_TITLE_FIELDSGROUPS_FOUND'));
		}
		else
		{
			$this->loaded = true;

			if (!$this->getState('id', false))
			{
				$this->setState('id', $this->helper->getGroupId($this->getState('title', '')));
			}

			if ($this->fieldGroupTable->load($this->getState('id', 0)))
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
			if (!$this->getState('id', false))
			{
				$this->fieldGroupTable->created = $this->date->toSql();
				$this->fieldGroupTable->created_by = $this->userId;
			}

			$this->fieldGroupTable->modified = $this->date->toSql();
			$this->fieldGroupTable->modified_by = $this->userId;

			if (!$this->getState('context', false))
			{
				$this->fieldGroupTable->context = 'com_content.article';
			}

			if (!$this->getState('access', false))
			{
				$this->fieldGroupTable->access = 1;
			}

			if (!$this->getState('language', false))
			{
				$this->fieldGroupTable->language = '*';
			}

			$this->fieldGroupTable->bind($this->state);

			try
			{
				$this->fieldGroupTable->check();
				$this->fieldGroupTable->store();
				$this->log->add('Field groups added successfully', false);
			}
			catch (\Exception $e)
			{
				$this->log->add('Cannot add field groups. Error: ' . $e->getMessage(), false);
				$this->log->addStats('incorrect', \JText::sprintf('COM_CSVI_TABLE_FIELDSGROUPSTABLE_ERROR', $e->getMessage()));

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
		$this->fieldGroupTable = $this->getTable('FieldGroups');

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
		$this->fieldGroupTable->reset();
	}
}
