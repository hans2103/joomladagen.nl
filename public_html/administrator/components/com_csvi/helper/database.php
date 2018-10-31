<?php
/**
 * @package     CSVI
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Sanity checking for database
 *
 * @package  CSVI
 * @since    7.5.0
 */
class CsviHelperDatabase
{
	/**
	 * Holds the JDatabaseDriver
	 *
	 * @var    JDatabaseDriver
	 * @since  7.5.0
	 */
	private $db;

	/**
	 * List of tables in the database
	 *
	 * @var    array
	 * @since  7.5.0
	 */
	private $tables = array();

	/**
	 * List of queries to execute
	 *
	 * @var    array
	 * @since  7.5.0
	 */
	private $queries = array();

	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver  $db  An instance of JDatabaseDriver
	 *
	 * @since   4.6
	 */
	public function __construct(JDatabaseDriver $db)
	{
		$this->db = $db;
	}

	/**
	 * Process the database XML definition file.
	 *
	 * @param   string  $source  The full path to the database XML file to process
	 *
	 * @return  void
	 *
	 * @throws  InvalidArgumentException
	 *
	 * @since   7.5.0
	 */
	public function process($source)
	{
		if (!file_exists($source))
		{
			throw new InvalidArgumentException(JText::_('COM_CSVI_NOT_VALID_DATABASE_SOURCE'));
		}

		/** @var object $xml */
		$xml = new SimpleXMLElement($source, 0, true);

		// Process the table fields
		foreach ($xml->tables->table as $table)
		{
			// Get the table name
			$tableName = (string) $table->name;

			// Collect all the fields
			$fields      = ((array) $table->fields);
			$tableFields = $fields['field'];

			// Check if the table exists
			if (!$this->tableExists($tableName))
			{
				$indexes = array();

				// Process the indexes
				if (isset($table->indexes))
				{
					$indexes = ((array) $table->indexes);
				}

				$this->createTable($table, $tableFields, $indexes);
			}
			else
			{
				// Check the existing fields
				$this->checkFields($tableName, $tableFields);

				// Process the indexes
				if (isset($table->indexes))
				{
					$indexes = ((array) $table->indexes);
					$this->checkIndexes($tableName, $indexes);
				}
			}
		}

		// Run the queries if there are any
		if ($this->queries)
		{
			$this->runQueries();
		}
	}

	/**
	 * Check if a given table name exists.
	 *
	 * @param   string  $tableName  The name of the table to check
	 *
	 * @return  boolean  True if table exists | false otherwise.
	 *
	 * @since   7.5.0
	 */
	private function tableExists($tableName)
	{
		if (!$this->tables)
		{
			$this->tables = $this->db->getTableList();
		}

		return in_array($this->db->getPrefix() . $tableName, $this->tables);
	}

	/**
	 * Get the create table statement.
	 *
	 * @param   object  $table        The table to create
	 * @param   array   $tableFields  A list of fields that must exist
	 * @param   array   $indexes      The list of indexes to add
	 *
	 * @return  void
	 *
	 * @since   7.5.0
	 */
	private function createTable($table, $tableFields, $indexes)
	{
		$db          = $this->db;
		$tableName   = (string) $table->name;
		$createTable = 'CREATE TABLE IF NOT EXISTS ' . $db->quoteName('#__' . $tableName) . ' (';
		$fields      = array();
		$primaryKey  = '';

		foreach ($tableFields as $index => $tableField)
		{
			$fieldName     = (string) $tableField->name;
			$fieldType     = (string) $tableField->type;
			$fieldNull     = (string) $tableField->null;
			$fieldDefault  = ($tableField->default) ? 'DEFAULT ' . ((string) $tableField->default === 'NULL' ? 'NULL' : $db->quote($tableField->default)) : '';
			$nullValue     = 'NULL';
			$autoIncrement = (bool) $tableField->autoIncrement ? 'AUTO_INCREMENT' : '';

			// Check if the NULL setting is correct
			if ($fieldNull === 'NO')
			{
				$nullValue = 'NOT NULL';
			}

			// Check if the field is unsigned
			if (strtolower((string) $tableField->unsigned) === 'true')
			{
				$fieldType .= ' UNSIGNED';
			}

			// Check if the primary key needs to be set
			if ((bool) $tableField->primaryKey)
			{
				$primaryKey = 'PRIMARY KEY (' . $db->quoteName($fieldName) . ')';
			}

			// Check if we have an auto-increment set, if so we cannot have a default value
			if ($autoIncrement)
			{
				$fieldDefault = '';
			}

			$fields[] = $db->quoteName($fieldName) . ' ' . $fieldType . ' ' . $nullValue . ' ' . $fieldDefault . ' ' . $autoIncrement;
		}

		$createTable .= implode(',', $fields);

		// Check if there is primary key
		if ($primaryKey)
		{
			$createTable .= ", \r\n" . $primaryKey;
		}

		// Add the indexes
		$indexQueries = array();

		foreach ($indexes as $key => $index)
		{
			// Check if the indexes is an array
			if (is_object($index))
			{
				$index = (array) array($index);
			}

			foreach ($index as $item)
			{
				$indexName   = (string) $item->name;
				$indexFields = (array) $item->fields;
				$indexType   = (string) $item->type;

				if (!$indexFields)
				{
					throw new InvalidArgumentException(JText::sprintf('COM_CSVI_MISSING_INDEX_FIELDS', $indexName, $tableName));
				}

				$query  = strtoupper($indexType) . ' INDEX ' . $db->quoteName($indexName) . ' (';
				$fields = array();

				// Check if there is only one field
				if (isset($indexFields['field']->name))
				{
					$fieldName = $db->quoteName((string) $indexFields['field']->name);

					// Check if there is a length
					if (isset($indexFields['field']->length))
					{
						$fieldName .= '(' . (string) $indexFields['field']->length . ')';
					}

					$fields[] = $fieldName;
				}
				else
				{
					foreach ($indexFields['field'] as $indexField)
					{
						$fields[] = $db->quoteName((string) $indexField->name);
					}
				}

				$query .= implode(',', $fields) . ')';

				$indexQueries[] = $query;
			}
		}

		if ($indexQueries)
		{
			$createTable .= ",\r\n" . implode(',', $indexQueries);
		}

		$createTable .= ') CHARSET=utf8';

		// Check if there is a comment
		$comment = (string) $table->comment;

		if ($comment)
		{
			$createTable .= ' COMMENT=' . $db->quote($comment);
		}

		// Close the query
		$createTable .= ';';

		// Add the query to the query list
		$this->queries[] = $createTable;
	}

	/**
	 * Check the fields in a given table.
	 *
	 * @param   string  $tableName    The name of the table to check
	 * @param   array   $tableFields  A list of fields that must exist
	 *
	 * @return  void
	 *
	 * @since   7.5.0
	 */
	private function checkFields($tableName, $tableFields)
	{
		$db              = $this->db;
		$existingFields  = $this->db->getTableColumns($db->getPrefix() . $tableName, false);
		$existingIndexes = $this->getIndexes($db->getTableKeys($db->getPrefix() . $tableName));

		// Get the primary key fields from the database
		$primaryKeyFields = $existingIndexes['PRIMARY'];

		foreach ($tableFields as $index => $tableField)
		{
			$addType       = null;
			$fieldName     = (string) $tableField->name;
			$fieldType     = (string) $tableField->type;
			$fieldNull     = (string) $tableField->null;
			$fieldDefault  = '';
			$comment       = '';
			$nullValue     = '';
			$autoIncrement = (bool) $tableField->autoIncrement ? 'AUTO_INCREMENT' : '';
			$fieldExists   = array_key_exists($fieldName, $existingFields);

			// Check if the NULL setting is correct
			if (!$fieldExists || strtolower($fieldNull) !== strtolower($existingFields[$fieldName]->Null))
			{
				$nullValue = 'NULL';

				if ($fieldNull === 'NO')
				{
					$nullValue = 'NOT NULL';
				}
			}

			if (!$fieldExists || strtolower($tableField->default) !== strtolower($existingFields[$fieldName]->Default))
			{
				$fieldDefault  = 'DEFAULT ' . $db->quote($tableField->default);

				if ((string) $tableField->default === 'NULL')
				{
					$fieldDefault  = 'DEFAULT NULL';
				}
			}

			// Check if the field is unsigned
			if (strtolower((string) $tableField->unsigned) === 'true')
			{
				$fieldType .= ' UNSIGNED';
			}

			// Check if the primary key needs to be set
			if ((bool) $tableField->primaryKey)
			{
				// Set the primary key if there is no primary key defined yet
				$setPrimaryKey = !array_key_exists('PRIMARY', $existingIndexes);

				// Check if the primary key field is the same as the existing one, if not we drop the existing primary key
				if (!in_array($fieldName, $primaryKeyFields))
				{
					// Drop the existing primary key as there is a new sherrif in town
					foreach ($primaryKeyFields as $primaryKeyField)
					{
						$this->queries[] = 'ALTER TABLE ' . $db->quoteName($db->getPrefix() . $tableName)
							. ' ALTER ' . $db->quoteName($primaryKeyField) . ' DROP DEFAULT;';
						$this->queries[] = 'ALTER TABLE ' . $db->quoteName($db->getPrefix() . $tableName)
							. ' CHANGE COLUMN ' . $db->quoteName($primaryKeyField) . ' ' . $db->quoteName($primaryKeyField)
							. $existingFields[$primaryKeyField]->Type
							. ($existingFields[$primaryKeyField]->Null === 'NO' ? ' NOT NULL' : 'NULL')
							. ' FIRST, DROP PRIMARY KEY';
					}

					// Set the new primary key
					$setPrimaryKey = true;
				}

				if ($setPrimaryKey)
				{
					$nullValue = 'NOT NULL PRIMARY KEY';
				}
			}

			// Check if the comment needs to be updated
			if (!$fieldExists || strtolower((string) $tableField->comment) !== strtolower($existingFields[$fieldName]->Comment))
			{
				$comment = ' COMMENT ' . $db->quote((string) $tableField->comment);
			}

			// Check if we have an auto-increment set, if so we cannot have a default value
			if ($autoIncrement)
			{
				$fieldDefault = '';
			}

			// Check if the field exists
			if ($fieldExists)
			{
				// Check if the field type is correct
				if ($fieldDefault || strtolower($fieldType) !== strtolower($existingFields[$fieldName]->Type))
				{
					// Modify the table field
					$addType = 'MODIFY';
				}
			}
			else
			{
				// Create the field as it doesn't exist
				$addType = 'ADD';
			}

			if ($addType)
			{
				// Create the query
				$this->queries[] = 'ALTER TABLE '
					. $db->quoteName($db->getPrefix() . $tableName) . ' '
					. $addType . ' '
					. $db->quoteName($fieldName) . ' '
					. $fieldType . ' '
					. $nullValue . ' '
					. $fieldDefault . ' '
					. $autoIncrement . ' '
					. $comment . ';';
			}
		}
	}

	/**
	 * Check the indexes in a given table.
	 *
	 * @param   string  $tableName  The name of the table to check
	 * @param   array   $indexes    A list of indexes that must exist
	 *
	 * @return  void
	 *
	 * @since   7.5.0
	 */
	private function checkIndexes($tableName, $indexes)
	{
		$db              = $this->db;
		$existingIndexes = $this->getIndexes($db->getTableKeys($db->getPrefix() . $tableName));

		// Remove the existing indexes
		foreach ($existingIndexes as $indexName => $existingIndex)
		{
			if (strtoupper($indexName) !== 'PRIMARY')
			{
				array_unshift($this->queries, 'ALTER TABLE ' . $db->quoteName($db->getPrefix() . $tableName) . ' DROP INDEX ' . $db->quoteName($indexName) . ';');
			}
		}

		// Add the new indexes
		foreach ($indexes as $key => $index)
		{
			// Check if the indexes is an array
			if (is_object($index))
			{
				$index = (array) array($index);
			}

			foreach ($index as $item)
			{
				$indexName   = (string) $item->name;
				$indexFields = (array) $item->fields;
				$indexType   = (string) $item->type;

				if (!$indexFields)
				{
					throw new InvalidArgumentException(JText::sprintf('COM_CSVI_MISSING_INDEX_FIELDS', $indexName, $tableName));
				}

				$query  = 'ALTER TABLE ' . $db->quoteName($db->getPrefix() . $tableName) . ' ADD ' . $indexType . ' INDEX ' . $db->quoteName($indexName) . ' (';
				$fields = array();

				// Check if there is only one field
				if (isset($indexFields['field']->name))
				{
					$fieldName = $db->quoteName((string) $indexFields['field']->name);

					// Check if there is a length
					if (isset($indexFields['field']->length))
					{
						$fieldName .= '(' . (string) $indexFields['field']->length . ')';
					}

					$fields[] = $fieldName;
				}
				else
				{
					foreach ($indexFields['field'] as $indexField)
					{
						$fieldName = $db->quoteName((string) $indexField->name);

						// Check if there is a length
						if (isset($indexField->length))
						{
							$fieldName .= '(' . (string) $indexField->length . ')';
						}

						$fields[] = $fieldName;
					}
				}

				$query .= implode(',', $fields) . ');';

				$this->queries[] = $query;
			}
		}
	}

	/**
	 * Get the list of indexes.
	 *
	 * @param   array  $indexes  The indexes to rework
	 *
	 * @return  array  List of indexes.
	 *
	 * @since   7.5.0
	 */
	private function getIndexes($indexes)
	{
		$cleanIndexes = array();

		foreach ($indexes as $key => $index)
		{
			$cleanIndexes[$index->Key_name][$index->Seq_in_index] = $index->Column_name;
		}

		return $cleanIndexes;
	}

	/**
	 * Run the queries.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 *
	 * @since   7.5.0
	 */
	private function runQueries()
	{
		if (!$this->queries)
		{
			return;
		}

		$db = $this->db;

		foreach ($this->queries as $index => $query)
		{
			try
			{
				$db->setQuery($query)->execute();
			}
			catch (Exception $exception)
			{
				throw new RuntimeException(JText::sprintf('COM_CSVI_FAILED_QUERY', $query, $exception->getMessage()));
			}
		}
	}
}
