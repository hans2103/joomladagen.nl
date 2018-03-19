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

namespace csvi\com_csvi\model\import;

defined('_JEXEC') or die;

/**
 * Custom import.
 *
 * @package     CSVI
 * @subpackage  CSVI
 * @since       6.0
 */
class Custom extends \RantaiImportEngine
{
	/**
	 * Custom table
	 *
	 * @var    \CsviTableCustomtable
	 * @since  6.0
	 */
	private $customTable = null;

	/**
	 * The primary key field
	 *
	 * @var    array
	 * @since  6.0
	 */
	private $pk = null;

	/**
	 * Set if the row already exists in the database
	 *
	 * @var    boolean
	 * @since  7.4
	 */
	private $rowExists = false;

	/**
	 * Run this before we start.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function onBeforeStart()
	{
		// Delete all rows before running a new import
		if ($this->template->get('delete_table_records', false))
		{
			$this->db->truncateTable('#__' . $this->template->get('custom_table', false));
			$this->log->add('Custom table data removed');
		}
	}

	/**
	 * Start the product import process.
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   6.0
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
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		// Find the primary key
		$this->pk = $this->customTable->getKeyName(true);

		if (!$this->pk)
		{
			$this->log->addStats('skipped', \JText::_('COM_CSVI_CUSTOM_NO_PRIMARY_KEY_AND_NO_FIELDS_SET'));

			return false;
		}

		$this->loaded = true;
		$this->log->add('Processed record based on fields: ' . implode(',', $this->pk), false);
		$this->customTable->bind($this->state);

		if ($this->rowExists = $this->customTable->checkIfRowExists())
		{
			if (!$this->template->get('overwrite_existing_data'))
			{
				// Get the primary key values
				$values = array();

				foreach ($this->pk as $pk)
				{
					$value = $this->getState($pk, '');

					$values[] = $pk . ' => ' . $value;
				}

				$this->log->add('Record ' . implode(',', $values) . ' not updated because the option overwrite existing data is set to No');
				$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_CUSTOM', implode(',', $values)));
				$this->loaded = false;
			}
			else
			{
				foreach ($this->pk as $pk)
				{
					if ($currentValue = $this->customTable->get($pk, 0))
					{
						$this->setState($pk, $currentValue);
					}
				}
			}
		}

		return true;
	}

	/**
	 * Process a record.
	 *
	 * @return  bool  Returns true if all is OK | Returns false if no product SKU or product ID can be found.
	 *
	 * @since   6.0
	 */
	public function getProcessRecord()
	{
		if (!$this->loaded)
		{
			return false;
		}

		if ($this->getState('record_delete', 'N') === 'Y')
		{
			$conditions = array();

			foreach ($this->pk as $pk)
			{
				$fieldValue = $this->getState($pk, '');
				$conditions[] = $this->db->quoteName($pk) . ' = ' . $this->db->quote($fieldValue);
			}

			$query = $this->db->getQuery(true)
				->delete($this->db->quoteName('#__' . $this->template->get('custom_table')))
				->where(implode( ' and ', $conditions));
			$this->db->setQuery($query)->execute();
			$this->log->addStats('delete', 'COM_CSVI_TABLE_CSVITABLECUSTOMTABLE_DELETED');
		}
		else
		{
			// Get the primary key values
			$values = array();

			foreach ($this->pk as $pk)
			{
				$value = $this->getState($pk, '');

				$values[] = $pk . ' => ' . $value;
			}

			if (!$this->rowExists && $this->template->get('ignore_non_exist'))
			{
				// Do nothing for new records when user chooses to ignore new products
				$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', implode(',', $values)));
				$this->log->add('Skipping record because user wants to ignore non existing items');

				return false;
			}

			try
			{
				if ($this->rowExists)
				{
					$this->db->updateObject($this->customTable->getTableName(), $this->state, $this->pk);
					$this->log->add('Query');
					$this->log->addStats('updated', 'COM_CSVI_TABLE_CUSTOM_UPDATED', 'Custom');
				}
				else
				{
					$this->db->insertObject($this->customTable->getTableName(), $this->state);
					$this->log->add('Query');
					$this->log->addStats('added', 'COM_CSVI_TABLE_CUSTOM_ADDED', 'Custom');
				}

				$this->customTable->reset();
			}
			catch (\Exception $e)
			{
				$this->log->add('Cannot add custom table data. Error: ' . $e->getMessage(), false);
				$this->log->addStats('incorrect', $e->getMessage());

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
	 * @since   6.0
	 */
	public function loadTables()
	{
		$this->customTable = $this->getTable('CustomTable');
	}

	/**
	 * Clear the loaded tables.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function clearTables()
	{
		$this->customTable->reset();
	}
}
