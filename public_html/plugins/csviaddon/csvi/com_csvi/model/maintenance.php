<?php
/**
 * @package     CSVI
 * @subpackage  Maintenance
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Performs CSVI specific maintenance operations.
 *
 * @package     CSVI
 * @subpackage  Maintenace
 * @since       6.0
 */
class Com_CsviMaintenance
{
	/**
	 * Database connector
	 *
	 * @var    JDatabaseDriver
	 * @since  6.0
	 */
	private $db = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  6.0
	 */
	private $log = null;

	/**
	 * CSVI Helper.
	 *
	 * @var    CsviHelperCsvi
	 * @since  6.0
	 */
	private $csvihelper = null;

	/**
	 * Key tracker
	 *
	 * @var    int
	 * @since  6.0
	 */
	private $key = 0;

	/**
	 * Total restored templates
	 *
	 * @var    int
	 * @since  7.2.1
	 */
	private $totalRestoredTemplates = 0;

	/**
	 * Hold the message to show on a JSON run
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $message = '';

	/**
	 * Set if we are running in CLI mode
	 *
	 * @var    bool
	 * @since  6.0
	 */
	private $isCli = false;

	/**
	 * Set download file
	 *
	 * @var    bool
	 * @since  6.6.0
	 */
	private $downloadfile = '';

	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver  $db          Joomla Database connector
	 * @param   CsviHelperLog    $log         An instance of CsviHelperLog
	 * @param   CsviHelperCsvi   $csvihelper  An instance of CsviHelperCsvi
	 * @param   bool             $isCli       Set if we are running CLI mode
	 *
	 * @since   6.0
	 */
	public function __construct(JDatabaseDriver $db, CsviHelperLog $log, CsviHelperCsvi $csvihelper, $isCli = false)
	{
		// Load the database class
		$this->db         = $db;
		$this->log        = $log;
		$this->csvihelper = $csvihelper;
		$this->isCli      = $isCli;
	}

	/**
	 * Load a number of maintenance tasks.
	 *
	 * @return  array  The list of available tasks.
	 *
	 * @since   6.0
	 */
	public function getOperations()
	{
		return
			array('options' =>
				array(
					''                      => JText::_('COM_CSVI_MAKE_CHOICE'),
					'loadpatch'             => JText::_('COM_CSVI_PATCH_FILE_LABEL'),
					'updateavailablefields' => JText::_('COM_CSVI_UPDATEAVAILABLEFIELDS_LABEL'),
					'cleantemp'             => JText::_('COM_CSVI_CLEANTEMP_LABEL'),
					'icecatindex'           => JText::_('COM_CSVI_ICECATINDEX_LABEL'),
					'backuptemplates'       => JText::_('COM_CSVI_BACKUPTEMPLATES_LABEL'),
					'restoretemplates'      => JText::_('COM_CSVI_RESTORETEMPLATES_LABEL'),
					'exampletemplates'      => JText::_('COM_CSVI_EXAMPLETEMPLATES_LABEL'),
					'optimizetables'        => JText::_('COM_CSVI_OPTIMIZETABLES_LABEL'),
					'deletetables'          => JText::_('COM_CSVI_DELETETABLES_LABEL'),
				)
			);
	}

	/**
	 * Load the options for a selected operation.
	 *
	 * @param   string  $operation  The operation to get the options for
	 *
	 * @return  string	the options for a selected operation.
	 *
	 * @since   6.0
	 */
	public function getOptions($operation)
	{
		$layoutPath = JPATH_PLUGINS . '/csviaddon/csvi/com_csvi/layouts';

		switch ($operation)
		{
			case 'loadpatch':
			case 'updateavailablefields':
			case 'cleantemp':
			case 'backuptemplates':
			case 'restoretemplates':
			case 'icecatindex':
			case 'optimizetables':
			case 'exampletemplates':
				$layout = new JLayoutFile('maintenance.' . $operation, $layoutPath);

				return $layout->render();
				break;
			case 'deletetables':
				$layout = new JLayoutFile('csvi.modal');

				return $layout->render(
					array(
						'modal-header'  => JText::_('COM_CSVI_' . $operation . '_LABEL'),
						'modal-body'    => JText::_('COM_CSVI_CONFIRM_TABLES_DELETE'),
						'cancel-button' => true
					)
				);
				break;
			default:
				return '';
				break;
		}
	}

	/**
	 * Optimize all database tables.
	 *
	 * @param   JInput  $input  The input model
	 * @param   mixed   $key    A reference used by the method.
	 *
	 * @return  bool  Always returns true.
	 *
	 * @since   6.0
	 */
	public function optimizeTables(JInput $input, $key)
	{
		// Get the list of tables to optimize
		$tables = $this->db->getTableList();

		if ($this->isCli)
		{
			foreach ($tables as $table)
			{
				// Increment log linecounter
				$this->log->incrementLinenumber();

				$this->optimizeTable($table);
			}
		}
		else
		{
			if (isset($tables[$key]))
			{
				// Increment log linecounter
				$this->log->incrementLinenumber();

				if ($this->optimizeTable($tables[$key]))
				{
					$this->message = JText::sprintf('COM_CSVI_TABLE_HAS_BEEN_OPTIMIZED', $tables[$key]);
				}
				else
				{
					$this->message = JText::sprintf('COM_CSVI_TABLE_HAS_NOT_BEEN_OPTIMIZED', $tables[$key]);
				}

				// Set the key for post processing
				$key++;
				$this->key = $key;
			}
			else
			{
				$this->key = false;
			}
		}

		return true;
	}

	/**
	 * Optimize a table.
	 *
	 * @param   string  $table  The name of the table to optimize
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.0
	 */
	private function optimizeTable($table)
	{
		// Build the query
		$q = 'OPTIMIZE TABLE ' . $this->db->quoteName($table);
		$this->db->setQuery($q);

		// Execute query
		if ($this->db->execute())
		{
			$this->log->addStats('information', JText::sprintf('COM_CSVI_TABLE_HAS_BEEN_OPTIMIZED', $table), 'MAINTENANCE');

			return true;
		}
		else
		{
			$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_TABLE_HAS_NOT_BEEN_OPTIMIZED', $table), 'MAINTENANCE');

			return false;
		}
	}

	/**
	 * Post processing optimize tables.
	 *
	 * @return  array  Settings for continuing.
	 *
	 * @since   6.0
	 */
	public function onAfteroptimizeTables()
	{
		if ($this->key)
		{
			// Return data
			$results = array();
			$results['continue'] = true;
			$results['key'] = $this->key;
		}
		else
		{
			$results['continue'] = false;
		}

		$results['info'] = $this->message;

		return $results;
	}

	/**
	 * Update available fields.
	 *
	 * @param   JInput  $input  The input model
	 * @param   mixed   $key    A reference used by the method.
	 *
	 * @return  bool  True on success, false on failure.
	 *
	 * @since   3.3
	 *
	 * @throws  \RuntimeException
	 */
	public function updateAvailableFields(JInput $input, $key)
	{
		$result = false;

		// Check if we need to prepare the available fields
		if ($key === 0)
		{
			$this->prepareAvailableFields();
		}

		if (CSVI_CLI)
		{
			$continue = true;

			while ($continue)
			{
				$result = $this->indexAvailableFields();
				$continue = $this->key;
			}
		}
		else
		{
			$result = $this->indexAvailableFields();
		}

		return $result;
	}

	/**
	 * Prepare for available fields importing.
	 *
	 * 1. Set all tables to be indexed
	 * 2. Empty the available fields table
	 * 3. Import the extra availablefields sql file
	 * 4. Find what tables need to be imported and store them in the session.
	 *
	 * @return  void.
	 *
	 * @since   3.5
	 *
	 * @throws  \RuntimeException
	 */
	private function prepareAvailableFields()
	{
		// Set all tables to be indexed
		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__csvi_availabletables'))
			->set($this->db->quoteName('indexed') . ' = 0');
		$this->db->setQuery($query)->execute();

		// Drop the available fields first
		try
		{
			// Delete the table
			$this->db->dropTable('#__csvi_availablefields');
			$this->log->addStats('delete', 'COM_CSVI_AVAILABLE_FIELDS_TABLE_DELETED', 'availablefields');

			// Create table again so index is proper
			$this->createAvailableFieldsTable();

			// Index the custom tables used in CSVI import/export
			$this->indexCustomTables();

			// Do component specific updates
			$override = new stdClass;
			$override->value = 'custom';
			$components = $this->csvihelper->getComponents();
			$components[] = $override;
			jimport('joomla.filesystem.file');

			foreach ($components as $component)
			{
				if (JComponentHelper::isInstalled($component->value))
				{
					$extension = substr($component->value, 4);

					// Load any component specific file
					if ($component->value
						&& $component->value !== 'com_csvi'
						&& $component->value !== 'custom'
						&& file_exists(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component->value . '/model/maintenance.php'))
					{
						require_once JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component->value . '/model/maintenance.php';
						$extensionClassname = ucfirst($component->value) . 'Maintenance';
						$extensionModel     = new $extensionClassname($this->db, $this->log, $this->csvihelper);

						if (method_exists($extensionModel, 'updateAvailableFields'))
						{
							$extensionModel->updateAvailableFields();
						}
					}

					// Process all extra available fields
					$filename = JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component->value . '/install/availablefields.sql';

					if (JFile::exists($filename))
					{
						// Check if the component is installed
						$ext_id = true;

						if (0 === strpos($component->value, 'com_'))
						{
							$query = $this->db->getQuery(true)
								->select($this->db->quoteName('extension_id'))
								->from($this->db->quoteName('#__extensions'))
								->where($this->db->quoteName('element') . ' = ' . $this->db->quote($component->value));
							$this->db->setQuery($query);
							$ext_id = $this->db->loadResult();
						}

						if ($ext_id)
						{
							// Increment line number
							$this->log->incrementLinenumber();

							$queries = JDatabaseDriver::splitSql(file_get_contents($filename));

							// Get the admin template name
							$adminTemplate = JFactory::getApplication()->getTemplate();

							// Check if there are any override files for custom available fields
							$overrideFilename = JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' . $component->value . '/install/override.sql';

							if (JFile::exists($overrideFilename))
							{
								$overrideQueries = JDatabaseDriver::splitSql(file_get_contents($overrideFilename));

								if (!empty($overrideQueries))
								{
									$queries = array_merge($queries, $overrideQueries);
								}
							}

							foreach ($queries as $step => $splitQuery)
							{
								// Clean the string of any trailing whitespace
								$splitQuery = trim($splitQuery);

								if ($splitQuery)
								{
									$this->db->setQuery($splitQuery);

									if ($this->db->execute())
									{
										$this->log->addStats(
											'added',
											JText::sprintf('COM_CSVI_CUSTOM_AVAILABLE_FIELDS_HAVE_BEEN_ADDED', JText::_('COM_CSVI_' . $component->value), $step + 1),
											$component->value . '_CUSTOM'
										);
									}
									else
									{
										$this->log->add(
											'incorrect',
											JText::sprintf('COM_CSVI_CUSTOM_AVAILABLE_FIELDS_HAVE_NOT_BEEN_ADDED', JText::_('COM_CSVI_' . $component->value), $step + 1, $splitQuery),
											$component->value . '_CUSTOM'
										);
									}
								}
							}

							// Execute any specific available fields that are not in an SQL file
							if (file_exists(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component->value . '/model/maintenance.php'))
							{
								require_once JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component->value . '/model/maintenance.php';
								$classname = $component->value . 'Maintenance';
								$addon     = new $classname($this->db, $this->log, $this->csvihelper);

								if (method_exists($addon, 'customAvailableFields'))
								{
									$addon->customAvailableFields();
								}
							}
						}
					}
				}
			}

			// Increment line number
			$this->log->decrementLinenumber();
		}
		catch (Exception $e)
		{
			$this->log->addStats('error', $e->getMessage(), 'availablefields');

			throw new CsviException($e->getMessage(), $e->getCode());
		}
	}

	/**
	 * Process the custom tables for import/export.
	 *
	 * @return  void.
	 *
	 * @since   6.5.6
	 *
	 * @throws  \RuntimeException
	 */
	private function indexCustomTables()
	{
		// Add the custom fields for each specific custom table in use
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('template_table'))
			->from($this->db->quoteName('#__csvi_availabletables'))
			->where($this->db->quoteName('task_name') . ' = ' . $this->db->quote('custom'))
			->where($this->db->quoteName('component') . ' = ' . $this->db->quote('com_csvi'))
			->where($this->db->quoteName('action') . ' = ' . $this->db->quote('import'));
		$this->db->setQuery($query);

		$importTables = $this->db->loadColumn();

		$query->clear('where')
			->where($this->db->quoteName('task_name') . ' = ' . $this->db->quote('custom'))
			->where($this->db->quoteName('component') . ' = ' . $this->db->quote('com_csvi'))
			->where($this->db->quoteName('action') . ' = ' . $this->db->quote('export'));
		$this->db->setQuery($query);

		$exportTables = $this->db->loadColumn();

		$query = 'INSERT IGNORE INTO ' . $this->db->quoteName('#__csvi_availablefields')
			. '(' . $this->db->quoteName('csvi_name') . ', '
			. $this->db->quoteName('component_name') . ', '
			. $this->db->quoteName('component_table') . ', '
			. $this->db->quoteName('component') . ', '
			. $this->db->quoteName('action') . ')';

		$customFields = array();

		foreach ($importTables as $importTable)
		{
			// Add the custom available fields for each import table
			$customFields[] = '(' . $this->db->quote('skip') . ', '
				. $this->db->quote('skip') . ', '
				. $this->db->quote($importTable) . ', '
				. $this->db->quote('com_csvi') . ', '
				. $this->db->quote('import') . ')';

			$customFields[] = '(' . $this->db->quote('combine') . ', '
				. $this->db->quote('combine') . ', '
				. $this->db->quote($importTable) . ', '
				. $this->db->quote('com_csvi') . ', '
				. $this->db->quote('import') . ')';
		}

		foreach ($exportTables as $exportTable)
		{
			// Add the custom available fields for each export table
			$customFields[] = '(' . $this->db->quote('custom') . ', '
				. $this->db->quote('custom') . ', '
				. $this->db->quote($exportTable) . ', '
				. $this->db->quote('com_csvi') . ', '
				. $this->db->quote('export') . ')';
		}

		if (0 !== count($customFields))
		{
			$query .= ' VALUES ' . implode(', ', $customFields);

			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Import the available fields in steps.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   3.5
	 *
	 * @throws  \RuntimeException
	 */
	private function indexAvailableFields()
	{
		// Load the session data
		$lines = $this->log->getLinenumber();
		$lines++;

		// Set the line number
		$this->log->setLinenumber($lines);

		$query = $this->db->getQuery(true);
		$query->select(
			$this->db->quoteName('csvi_availabletable_id') . ',' .
			$this->db->quoteName('template_table') . ',' .
			$this->db->quoteName('component') . ',' .
			$this->db->quoteName('action')
		)
			->from($this->db->quoteName('#__csvi_availabletables'))
			->where($this->db->quoteName('indexed') . ' = 0')
			->where($this->db->quoteName('enabled') . ' = 1')
			->group($this->db->quoteName('template_table'));
		$this->db->setQuery($query, 0, 1);
		$table = $this->db->loadObject();

		if (is_object($table))
		{
			// Set the key that we started
			$this->key = 1;

			// Check if the table exists
			$tables = $this->db->getTableList();

			if (in_array($this->db->getPrefix() . $table->template_table, $tables, true))
			{
				// Increment line number
				$this->log->incrementLinenumber();

				$this->indexTable($table);
			}
			else
			{
				$this->message = $table->template_table . ' not an available table';
			}

			// Set the table to indexed
			$query = $this->db->getQuery(true);
			$query->update($this->db->quoteName('#__csvi_availabletables'))
				->set($this->db->quoteName('indexed') . ' = 1')
				->where($this->db->quoteName('csvi_availabletable_id') . ' = ' . (int) $table->csvi_availabletable_id);
			$this->db->setQuery($query);
			$this->db->execute();
		}
		else
		{
			$this->key = false;
		}

		return true;
	}

	/**
	 * Creates an array of custom database fields the user can use for import/export.
	 *
	 * @param   string  $table    The table name to get the fields for
	 * @param   bool    $addname  Add the table name to the list of fields
	 *
	 * @return  array  List of custom database fields.
	 *
	 * @since   3.0
	 */
	private function dbFields($table, $addname=false)
	{
		$customfields = array();
		$q = 'SHOW COLUMNS FROM ' . $this->db->quoteName('#__' . $table);
		$this->db->setQuery($q);
		$fields = $this->db->loadObjectList();

		if (count($fields) > 0)
		{
			foreach ($fields as $field)
			{
				if ($addname)
				{
					$customfields[$field->Field] = $table;
				}
				else
				{
					$customfields[$field->Field] = null;
				}
			}
		}

		return $customfields;
	}

	/**
	 * Index a single table.
	 *
	 * @param   object   $table        The table to index
	 * @param   boolean  $showMessage  Show availablefields updated message
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 *
	 * @throws  \RuntimeException
	 */
	public function indexTable($table, $showMessage = true)
	{
		// Get the primary key for the table
		$primaryKey = $this->csvihelper->getPrimaryKey($table->template_table);

		// Load the language
		$language = new CsviHelperLanguage;
		$language->loadAddonLanguage($table->component, false);

		$fields = $this->dbFields($table->template_table, true);

		if (is_array($fields))
		{
			// Process all fields
			foreach ($fields as $name => $value)
			{
				// Check if the field is a primary field
				$primary = 0;

				if ($primaryKey === $name)
				{
					$primary = 1;
				}

				if ($name)
				{
					$q = 'INSERT IGNORE INTO ' . $this->db->quoteName('#__csvi_availablefields') . ' VALUES ('
						. '0,'
						. $this->db->quote($name) . ','
						. $this->db->quote($name) . ','
						. $this->db->quote($value) . ','
						. $this->db->quote($table->component) . ','
						. $this->db->quote($table->action) . ','
						. $this->db->quote($primary) . ')';
					$this->db->setQuery($q);

					try
					{
						$this->db->execute();
					}
					catch (Exception $e)
					{
						$this->log->addStats('error', 'COM_CSVI_AVAILABLE_FIELDS_HAVE_NOT_BEEN_ADDED', 'maintenance_index_availablefields');
						$this->message = $e->getMessage();
					}
				}
			}

			if ($showMessage)
			{
				$this->log->addStats(
					'added',
					JText::sprintf('COM_CSVI_AVAILABLE_FIELDS_HAVE_BEEN_ADDED', $table->template_table),
					'maintenance_index_availablefields'
				);
				$this->message = JText::sprintf('COM_CSVI_AVAILABLE_FIELDS_HAVE_BEEN_ADDED', $table->template_table);
			}
		}
	}

	/**
	 * This is called after the available fields have been updated for post-processing.
	 *
	 * @return  array  Results of the update.
	 *
	 * @since   6.0
	 */
	public function onAfterUpdateAvailableFields()
	{
		// Clean the cache
		$cache = JFactory::getCache('com_csvi', '');
		$cache->clean('com_csvi');

		if ($this->key)
		{
			// Return data
			$results = array();
			$results['continue'] = true;
			$results['key'] = $this->key;
		}
		else
		{
			$results['continue'] = false;
		}

		$results['info'] = $this->message;

		return $results;
	}

	/**
	 * Load a patch provided by the forum.
	 *
	 * @param   JInput  $input  The JInput class.
	 *
	 * @return  bool  True on success, false on failure.
	 *
	 * @throws   CsviException
	 * @throws   RuntimeException
	 *
	 * @since   5.6
	 */
	public function loadPatch(JInput $input)
	{
		// Load the necessary libraries
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.archive');

		clearstatcache();

		// Get the uploaded details
		$upload = $input->get('patch_file', '', 'array');

		// Check if the file upload has an error
		if (empty($upload) || !array_key_exists('error', $upload))
		{
			$this->log->addStats('incorrect', 'COM_CSVI_NO_UPLOADED_FILE_PROVIDED', 'maintenance');

			throw new CsviException(JText::_('COM_CSVI_NO_UPLOADED_FILE_PROVIDED'));
		}
		elseif ($upload['error'] == 0)
		{
			// Get some basic info
			$folder = CSVIPATH_TMP . '/patch/' . time();

			// Create the temp folder
			if (JFolder::create($folder))
			{
				// Move the uploaded file to its temp location
				if (JFile::copy($upload['tmp_name'], $folder . '/' . $upload['name']))
				{
					// Remove the temporary file
					JFile::delete($upload['tmp_name']);

					// Unpack the archive
					if (JArchive::extract($folder . '/' . $upload['name'], $folder))
					{
						// File is unpacked, remove the zip file so it won't get processed
						JFile::delete($folder . '/' . $upload['name']);

						// File is unpacked, let's process the folder
						if ($this->processFolder($folder, $folder))
						{
							// All good remove tempory folder
							JFolder::delete($folder);

							return true;
						}
					}
					else
					{
						$this->log->addStats('incorrect', 'COM_CSVI_CANNOT_UNPACK_UPLOADED_FILE', 'maintenance');

						throw new RuntimeException(JText::_('COM_CSVI_CANNOT_UNPACK_UPLOADED_FILE'));
					}
				}
			}
			else
			{
				$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_CANNOT_CREATE_UNPACK_FOLDER', $folder), 'maintenance');

				throw new RuntimeException(JText::sprintf('COM_CSVI_CANNOT_CREATE_UNPACK_FOLDER', $folder));
			}
		}
		else
		{
			// There was a problem uploading the file
			switch ($upload['error'])
			{
				case '1':
					$this->log->addStats('incorrect', 'COM_CSVI_THE_UPLOADED_FILE_EXCEEDS_THE_MAXIMUM_UPLOADED_FILE_SIZE', 'maintenance');
					break;
				case '2':
					$this->log->addStats('incorrect', 'COM_CSVI_THE_UPLOADED_FILE_EXCEEDS_THE_MAXIMUM_UPLOADED_FILE_SIZE', 'maintenance');
					break;
				case '3':
					$this->log->addStats('incorrect', 'COM_CSVI_THE_UPLOADED_FILE_WAS_ONLY_PARTIALLY_UPLOADED', 'maintenance');
					break;
				case '4':
					$this->log->addStats('incorrect', 'COM_CSVI_NO_FILE_WAS_UPLOADED', 'maintenance');
					break;
				case '6':
					$this->log->addStats('incorrect', 'COM_CSVI_MISSING_A_TEMPORARY_FOLDER', 'maintenance');
					break;
				case '7':
					$this->log->addStats('incorrect', 'COM_CSVI_FAILED_TO_WRITE_FILE_TO_DISK', 'maintenance');
					break;
				case '8':
					$this->log->addStats('incorrect', 'COM_CSVI_FILE_UPLOAD_STOPPED_BY_EXTENSION', 'maintenance');
					break;
				default:
					$this->log->addStats('incorrect', 'COM_CSVI_THERE_WAS_A_PROBLEM_UPLOADING_THE_FILE', 'maintenance');
					break;
			}

			throw new RuntimeException(JText::_('COM_CSVI_PATH_UPLOAD_ERROR'));
		}

		return true;
	}

	/**
	 * Walk through a folder to process all found files.
	 *
	 * @param   string  $folder  The name of the folder to process
	 * @param   string  $base    The base folder
	 *
	 * @return  bool  True on success, false on failure.
	 *
	 * @since   5.6
	 */
	private function processFolder($folder, $base = null)
	{
		$foundfiles = scandir($folder);

		foreach ($foundfiles as $ffkey => $ffname)
		{
			$src = $folder . '/' . $ffname;

			// Check if it is a folder
			if (is_dir($src))
			{
				switch ($ffname)
				{
					case '.':
					case '..':
						break;
					default:
						$this->processFolder($src, $base);
						break;
				}
			}
			else
			{
				// Create the destination name
				$destFile = str_ireplace($base, JPATH_SITE, $folder) . '/' . $ffname;

				// Check if the destination file exists
				if (file_exists($destFile))
				{
					JFile::move($destFile, $destFile . '.' . date('Ymd-His'));
				}

				// Copy the file to the destination location
				if (JFile::copy($src, $destFile))
				{
					$this->log->addStats('added', JText::sprintf('COM_CSVI_COPY_PATCHFILE', $src, $destFile), 'maintenance');
				}
				else
				{
					$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_CANT_COPY_PATCHFILE', $src, $destFile), 'maintenance');
				}
			}
		}

		return true;
	}

	/**
	 * Clean the CSVI cache.
	 *
	 * @return  bool  Always returns true.
	 *
	 * @since   3.0
	 */
	public function cleanTemp()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$folder = CSVIPATH_TMP;

		if (JFolder::exists($folder))
		{
			// Delete all import files left behind in the folder
			JFile::delete(JFolder::files($folder, '.', false, true));

			// Delete all import folders left behind in the folder
			$folders = JFolder::folders($folder, '.', true, true, array('debug', 'export'));

			if (!empty($folders))
			{
				foreach ($folders as $path)
				{
					JFolder::delete($path);
				}
			}

			// Empty the export folder
			JFile::delete(JFolder::files($folder . '/export', '.', false, true));

			// Load the files
			if (JFolder::exists(CSVIPATH_DEBUG))
			{
				$files = JFolder::files(CSVIPATH_DEBUG, 'com_csvi', false, true);

				if ($files)
				{
					// Set all directory separators in the same direction
					foreach ($files as &$file)
					{
						$file = str_replace('\\', '/', $file);
					}

					// Remove any debug logs that are still there but not in the database
					$query = $this->db->getQuery(true)
						->select(
							'CONCAT('
							. $this->db->quote(CSVIPATH_DEBUG . '/com_csvi.log.')
							. ', '
							. $this->db->quoteName('csvi_log_id')
							. ', '
							. $this->db->quote('.php') . ') AS ' . $this->db->quoteName('filename')
						)
						->from($this->db->quoteName('#__csvi_logs'))
						->order($this->db->quoteName('csvi_log_id'));
					$this->db->setQuery($query);
					$ids = $this->db->loadColumn();

					if (!is_array($ids))
					{
						$ids = (array) $ids;
					}

					// Delete all obsolete files
					JFile::delete(array_diff($files, $ids));
				}
			}

			$this->log->addStats('delete', JText::sprintf('COM_CSVI_TEMP_CLEANED', $folder, CSVIPATH_DEBUG, $folder . '/export'), 'maintenance');
		}
		else
		{
			$this->log->addStats('information', JText::sprintf('COM_CSVI_TEMP_PATH_NOT_FOUND'), 'maintenance');
		}

		return true;
	}

	/**
	 * Backup selected templates.
	 *
	 * @param   JInput  $input  JInput object
	 *
	 * @return  bool  Always true.
	 *
	 * @since   3.0
	 *
	 * @throws  CsviException
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function backupTemplates(JInput $input)
	{
		$lineNumber = 1;
		$ids        = $input->get('templates', array(), 'array');

		if (!$ids)
		{
			throw new CsviException(JText::_('COM_CSVI_NO_TEMPLATES_SELECTED'));
		}

		$xml               = new DOMDocument;
		$xml->formatOutput = true;
		$csvi_element      = $xml->createElement('csvi');

		/** @var CsviModelTemplates $templateModel */
		$templateModel = JModelLegacy::getInstance('Templates', 'CsviModel', array('ignore_request' => true));
		$templates     = $templateModel->getItems();
		/** @var array $ignoreFields */
		$ignoreFields = array('ftpusername', 'ftppass', 'urlusername', 'urlpass', 'secret');

		foreach ($templates as $template)
		{
			$template = (array) $template;

			if (in_array($template['csvi_template_id'], $ids, true))
			{
				// Create the template node
				/** @var DOMElement $xml_template */
				$xml_template = $xml->createElement('template');

				// Add the settings
				/** @var DOMElement $template_settings */
				$template_settings = $xml->createElement('settings');
				$settings = json_decode($template['settings']);

				foreach ($settings as $name => $value)
				{
					if (in_array($name, $ignoreFields, true))
					{
						$value = '';
					}

					/** @var DOMElement $ruleElement */
					$ruleElement = $xml->createElement($name);

					if ($name === 'custom_table')
					{
						$notObject = false;

						if (is_object($value))
						{
							foreach ($value as $customName => $customValue)
							{
								if (is_object($customValue))
								{
									foreach ($customValue as $customLevelKey => $customLevelName)
									{
										$element_param = $xml->createElement($customLevelKey);
										$element_param->appendChild($xml->createCDATASection($customLevelName));
										$ruleElement->appendChild($element_param);
									}
								}
								else
								{
									$notObject = true;
								}
							}
						}
						else
						{
							$notObject = true;
						}

						if ($notObject)
						{
							$element_param = $xml->createElement($name);
							$element_param->appendChild($xml->createCDATASection($value));
							$ruleElement->appendChild($element_param);
						}
					}
					else
					{
						if (is_array($value))
						{
							foreach ($value as $key => $subValue)
							{
								/** @var DOMElement $subElement */
								$subElement = $xml->createElement('option');
								$subElement->appendChild($xml->createCDATASection($subValue));
								$ruleElement->appendChild($subElement);
							}
						}
						else
						{
							// Convert 1/0 to yes/no so 0 wont become empty
							switch ($value)
							{
								case '0':
									$val = 'no';
									break;
								case '1':
									$val = 'yes';
									break;
								default:
									$val = $value;
									break;
							}

							$ruleElement->appendChild($xml->createCDATASection($val));
						}
					}

					$template_settings->appendChild($ruleElement);
				}

				// Add the settings to the XML
				$xml_template->appendChild($template_settings);

				// Array of fields to export
				/** @var array $nodes */
				$nodes = array(
					'template_name',
					'advanced',
					'action',
					'frontend',
					'secret',
					'log',
					'lastrun',
					'enabled',
					'ordering',
				);

				// Add all the template options
				/** @var string $ruleNode */
				foreach ($nodes as $ruleNode)
				{
					if (in_array($ruleNode, $ignoreFields, true))
					{
						$template[$ruleNode] = '';
					}

					$ruleElement = $xml->createElement($ruleNode);
					$ruleElement->appendChild($xml->createCDATASection($template[$ruleNode]));
					$xml_template->appendChild($ruleElement);
				}

				// Add the fields for this template
				/** @var CsviModelTemplatefields $fieldsModel */
				$fieldsModel = JModelLegacy::getInstance('Templatefields', 'CsviModel', array('ignore_request' => true));
				$fieldsModel->setState('filter.csvi_template_id', $template['csvi_template_id']);
				$fields = $fieldsModel->getItems();

				if (count($fields) > 0)
				{
					$nodes = array(
						'field_name',
						'xml_node',
						'table_name',
						'column_header',
						'default_value',
						'enabled',
						'sort',
						'cdata',
						'ordering',
					);

					$template_fields = $xml->createElement('fields');

					foreach ($fields as $field)
					{
						$template_field = $xml->createElement('field');

						foreach ($nodes as $node)
						{
							if (isset($field->$node))
							{
								$fieldElement = $xml->createElement($node);
								$fieldElement->appendChild($xml->createCDATASection($field->$node));
								$template_field->appendChild($fieldElement);
							}
						}

						// Add the template field rules
						$query = $this->db->getQuery(true)
							->select(
								$this->db->quoteName(
									array(
										'name',
										'action',
										'ordering',
										'plugin',
										'plugin_params',
									)
								)
							)
							->from($this->db->quoteName('#__csvi_rules', 'r'))
							->leftJoin(
								$this->db->quoteName('#__csvi_templatefields_rules', 't')
								. ' ON ' . $this->db->quoteName('t.csvi_rule_id') . ' = ' . $this->db->quoteName('r.csvi_rule_id')
							)
							->where($this->db->quoteName('t.csvi_templatefield_id') . ' = ' . (int) $field->csvi_templatefield_id);
						$this->db->setQuery($query);

						$rules = $this->db->loadObjectList();

						if (count($rules) > 0)
						{
							$ruleNodes = array(
								'name',
								'action',
								'ordering',
								'plugin',
								'plugin_params',
							);

							$fieldRules = $xml->createElement('fieldrules');

							foreach ($rules as $rule)
							{
								$fieldRule = $xml->createElement('rule');

								foreach ($ruleNodes as $ruleNode)
								{
									$ruleElement = $xml->createElement($ruleNode);

									if ($ruleNode === 'plugin_params')
									{
										$params = json_decode($rule->$ruleNode);

										if (is_object($params))
										{
											foreach ($params as $name => $value)
											{
												// Check if it is a multi replace plugin params
												if (is_object($value))
												{
													foreach ($value as $replaceLevelKey => $replaceLevelName)
													{
														if (is_object($replaceLevelName))
														{
															foreach ($replaceLevelName as $replaceKey => $replaceName)
															{
																$element_param = $xml->createElement($replaceKey);
																$element_param->appendChild($xml->createCDATASection($replaceName));
																$ruleElement->appendChild($element_param);
															}
														}
													}
												}
												else
												{
													$element_param = $xml->createElement($name);
													$element_param->appendChild($xml->createCDATASection($value));
													$ruleElement->appendChild($element_param);
												}
											}
										}
									}
									else
									{
										$ruleElement->appendChild($xml->createCDATASection($rule->$ruleNode));
									}

									$fieldRule->appendChild($ruleElement);
								}

								$fieldRules->appendChild($fieldRule);
							}

							$template_field->appendChild($fieldRules);
						}

						$template_fields->appendChild($template_field);
					}

					// Add the fields to the XML
					$xml_template->appendChild($template_fields);
				}

				// Add the template to the XML
				$this->log->setLinenumber($lineNumber++);
				$csvi_element->appendChild($xml_template);
			}
		}

		$xml->appendChild($csvi_element);

		$location = $input->get('exportto', 'todownload', 'string');

		// Create the backup file
		$filePath = JPATH_SITE . $input->get('backup_location', '/tmp/com_csvi', 'string');
		$domain   = JUri::getInstance()->toString(array('host'));
		$filename = 'csvi_templates_' . $domain . '_' . date('Ymd', time()) . '.xml';
		$file = JPath::clean($filePath . '/' . $filename, '/');
		$xml->save($file);
		$this->downloadfile = JUri::root()
			. 'administrator/index.php?option=com_csvi&task=exports.downloadfile&tmpl=component&file='
			. base64_encode($file);

		// If user needs to download the file
		if ($location !== 'todownload')
		{
			$this->downloadfile = '';
			$this->log->addStats('information', JText::sprintf('COM_CSVI_BACKUP_TEMPLATE_PATH', $file), 'maintenance');
		}

		// Store the log count
		$lineNumber--;
		$input->set('logcount', $lineNumber);

		return true;
	}



	/**
	 * Restore templates.
	 *
	 * @param   JInput  $input     JInput object
	 * @param   mixed   $key       A reference used by the method.
	 * @param   string  $filename  A local filename to use for import
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   3.0
	 *
	 * @throws  CsviException
	 */
	public function restoreTemplates(JInput $input, $key, $filename = '')
	{
		$linenumber = 0;
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$userExistingRules = $input->get('useexistingrules', 1);
		$enableDebugLog = $input->get('enablelog', 0);

		if (empty($filename))
		{
			$upload = $input->get('restore_file', null, 'array');

			// Check if the file upload has an error
			if (null === $upload)
			{
				$this->log->addStats('incorrect', JText::_('COM_CSVI_NO_UPLOADED_FILE_PROVIDED'), 'maintenance');

				throw new CsviException(JText::_('COM_CSVI_NO_UPLOADED_FILE_PROVIDED'));
			}

			$filename = $upload['tmp_name'];
		}

		$doc = new DOMDocument;
		$doc->load(realpath($filename));
		$data = $this->domnodeToArray($doc->documentElement);

		$file = basename($filename);

		// Check if it is a multi-dimensional array
		if (!isset($data['template'][0]))
		{
			// Make the array multi-dimensional
			$newtemplate = array();
			$newtemplate['template'][0] = $data['template'];
			$data = $newtemplate;
		}

		// Load the necessary tables
		/** @var TableTemplate $templateTable */
		$templateTable           = JTable::getInstance('Template', 'Table');
		/** @var TableTemplatefield $fieldTable */
		$fieldTable              = JTable::getInstance('Templatefield', 'Table');
		$ruleTable               = JTable::getInstance('Rule', 'Table');
		$templatefieldrulesTable = JTable::getInstance('Templatefields_rules', 'Table');

		foreach ($data as $templates)
		{
			foreach ($templates as $template)
			{
				// Store the template
				$templateTable->reset();
				$templateTable->set('csvi_template_id', null);
				$templateTable->set('template_name', $template['template_name']);
				$templateTable->set('advanced', $template['advanced']);
				$templateTable->set('action', $template['action']);
				$templateTable->set('frontend', $template['frontend']);
				$templateTable->set('secret', $template['secret']);
				$templateTable->set('log', ($enableDebugLog) ? $enableDebugLog : $template['log']);
				$templateTable->set('lastrun', $template['lastrun']);
				$templateTable->set('enabled', $template['enabled']);
				$templateTable->set('ordering', $template['ordering']);

				// Reformat the settings
				foreach ($template['settings'] as $name => $setting)
				{
					$component = $template['settings']['component'];

					if (!$this->checkComponentInstall($component))
					{
						throw new RuntimeException(JText::sprintf('COM_CSVI_COMPONENT_NOT_INSTALLED', $component));
					}

					// Convert back yes/no to template readable form 1/0
					switch ($template['settings'][$name])
					{
						case 'no':
							$val = 0;
							break;
						case 'yes':
							$val = 1;
							break;
						default:
							$val = $template['settings'][$name];
							break;
					}

					$template['settings'][$name] = $val;

					// Check if it is a custom table to update available fields
					if ($name === 'custom_table' && $template['settings'][$name])
					{
						if (isset($template['settings'][$name]['table']))
						{
							$customTableValues = $template['settings'][$name];
							unset($template['settings'][$name]['table']);
							unset($template['settings'][$name]['field']);
							unset($template['settings'][$name]['joinfield']);
							unset($template['settings'][$name]['jointable']);
							unset($template['settings'][$name]['jointype']);
							unset($template['settings'][$name]['csvi_template_id']);

							foreach ($customTableValues as $key => $customTable)
							{
								foreach ($customTable as $count => $tableValue)
								{
									$template['settings'][$name]['custom_table' . $count][$key] = $tableValue;
								}
							}
						}

						$template['settings']['custom_table'] = $template['settings'][$name];
						$this->saveCustomTableAvailableFields($customTableValues['table'], $template['action']);
					}

					if (is_array($setting) && isset($setting['option']))
					{
						// Make sure the option is an array
						$setting['option'] = (array) $setting['option'];

						$template['settings'][$name] = $setting['option'];
					}
				}

				$templateTable->set('settings', json_encode($template['settings']));
				$templateTable->store();

				if (array_key_exists('fields', $template))
				{
					// Store the fields
					$fields = $template['fields']['field'];

					if (!array_key_exists(0, $template['fields']['field']))
					{
						$fields = array(0 => $template['fields']['field']);
					}

					// Get the template ID
					$templateId = $templateTable->get('csvi_template_id');

					foreach ($fields as $field)
					{
						$fieldTable->set('csvi_template_id', $templateId);
						$fieldTable->save($field);

						// Store any field related rules
						if (isset($field['fieldrules']))
						{
							foreach ($field['fieldrules'] as $rules)
							{
								if (isset($rules['name']))
								{
									$rules = array($rules);
								}

								foreach ($rules as $rule)
								{
									$newArray = array();

									if (isset($rule['plugin_params']['operation']))
									{
										$finalReplaceArray = array();
										$replaceArray = array();

										if (is_array($rule['plugin_params']['operation']))
										{
											for ($i = 0; $i < count($rule['plugin_params']['operation']); $i++)
											{
												foreach ($rule['plugin_params'] as $paramKey => $paramValue)
												{
													$replaceArray[$paramKey] = $paramValue[$i];
												}

												$finalReplaceArray['replacements' . $i] = $replaceArray;
											}
										}
										else
										{
											foreach ($rule['plugin_params'] as $paramKey => $paramValue)
											{
												$replaceArray[$paramKey] = $paramValue;
											}

											$finalReplaceArray['replacements0'] = $replaceArray;
										}

										$newArray['replacements'] = $finalReplaceArray;
										$rule['plugin_params']    = $newArray;
									}

									$ruleId = 0;

									// Check if existing rules has to be used
									if ($userExistingRules)
									{
										$query = $this->db->getQuery(true)
											->select($this->db->quoteName('csvi_rule_id'))
											->from($this->db->quoteName('#__csvi_rules'))
											->where($this->db->quoteName('name') . '=' . $this->db->quote($rule['name']));
										$this->db->setQuery($query);
										$ruleId = $this->db->loadResult();
									}

									if (!$ruleId)
									{
										$ruledata = array(
											'name'                  => $rule['name'],
											'action'                => $rule['action'],
											'ordering'              => $rule['ordering'],
											'plugin'                => $rule['plugin'],
											'plugin_params'         => json_encode($rule['plugin_params']),
											'csvi_templatefield_id' => $fieldTable->get('csvi_templatefield_id')
										);

										// Save the rule
										$ruleTable->save($ruledata);
										$ruleId = $ruleTable->get('csvi_rule_id');
									}

									// Save the relation
									$templatefieldrulesTable->set('csvi_templatefield_id', $fieldTable->get('csvi_templatefield_id'));
									$templatefieldrulesTable->set('csvi_rule_id', $ruleId);
									$templatefieldrulesTable->store();

									// Reset the relation table
									$templatefieldrulesTable->reset();
									$templatefieldrulesTable->csvi_templatefields_rule_id = null;

									// Reset the rule table
									$ruleTable->reset();
									$ruleTable->csvi_rule_id = null;
								}
							}
						}

						$fieldTable->reset();
						$fieldTable->csvi_templatefield_id = null;
					}
				}

				// Increment the number of templates processed
				$this->log->setLinenumber(++$linenumber);
			}
		}

		$totalTemplates = $this->totalRestoredTemplates + $linenumber;
		$this->totalRestoredTemplates = $totalTemplates;

		// Set the name of the file restore to logs display
		$this->log->setFilename($file);

		// Store the log count
		$totalTemplates--;
		$input->set('logcount', $totalTemplates);

		return true;
	}

	/**
	 * Check if the component is installed.
	 *
	 * @param   string  $component  Component name to check if installed
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   7.4.0
	 *
	 */
	private function checkComponentInstall($component)
	{
		$query = $this->db->getQuery(true);
		$query->select($this->db->quoteName('extension_id'))
			->from($this->db->quoteName('#__extensions'))
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote($component))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'));
		$this->db->setQuery($query);
		$extensionId = $this->db->loadResult();

		if (!$extensionId)
		{
			return false;
		}

		return true;
	}

	/**
	 * Turn the XML file into an associative array.
	 *
	 * @param   DOMElement  $node  The tree to turn into an array.
	 *
	 * @return  array  The  XML layout as associative array.
	 *
	 * @see     https://github.com/gaarf/XML-string-to-PHP-array
	 *
	 * @since   6.0
	 */
	private function domnodeToArray($node)
	{
		$output = array();

		switch ($node->nodeType)
		{
			case XML_CDATA_SECTION_NODE:
			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;
			case XML_ELEMENT_NODE:
				for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++)
				{
					$child = $node->childNodes->item($i);
					$v = $this->domnodeToArray($child);

					if (isset($child->tagName))
					{
						$t = $child->tagName;

						if (!isset($output[$t]))
						{
							$output[$t] = array();
						}

						if (empty($v))
						{
							$v = '';
						}

						$output[$t][] = $v;
					}
					elseif ($v)
					{
						$output = (string) $v;
					}
				}

				if (is_array($output))
				{
					if ($node->attributes->length)
					{
						$a = array();

						foreach ($node->attributes as $attrName => $attrNode)
						{
							$a[$attrName] = (string) $attrNode->value;
						}

						$output['@attributes'] = $a;
					}

					foreach ($output as $t => $v)
					{
						if (is_array($v) && count($v) == 1 && $t != '@attributes')
						{
							$output[$t] = $v[0];
						}
					}
				}

				break;
		}

		return $output;
	}

	/**
	 * Prepare the ICEcat index files for loading.
	 *
	 * @param   JInput  $input  The input model
	 *
	 * @return  void
	 *
	 * @since   6.0
	 *
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 */
	public function onBeforeIcecatIndex(JInput $input)
	{
		$session              = JFactory::getSession();
		$settings             = new CsviHelperSettings($this->db);
		$username             = $settings->get('ice_username', false);
		$password             = $settings->get('ice_password', false);
		$icecat_gzip          = $input->get('icecat_gzip', true, 'bool');
		$loadIndex            = $input->get('icecat_index', false, 'bool');
		$loadSupplier         = $input->get('icecat_supplier', false, 'bool');
		$key                  = $input->get('key', 0);
		$loadRemoteIndex      = false;
		$loadRemoteSupplier   = false;
		$icecat_index_file    = '';
		$icecat_supplier_file = '';

		// Check if we have a username and password
		if ($username && $password)
		{
			// Only download the files at the start of indexing
			if ((int) $key !== 0)
			{
				return;
			}

			// Joomla includes
			jimport('joomla.filesystem.folder');
			jimport('joomla.filesystem.file');
			jimport('joomla.filesystem.archive');

			// Check if the files are stored on the server
			$location = $input->get('icecatlocation', CSVIPATH_TMP, 'string');

			if ($loadIndex)
			{
				if (JFile::exists($location . '/icecat_index'))
				{
					$icecat_index_file = $location . '/icecat_index';
				}
				elseif (JFile::exists($location . '/icecat_index.gzip'))
				{
					$icecat_index_file = $location . '/icecat_index.gzip';
				}
				elseif (JFile::exists($location . '/icecat_index.zip'))
				{
					$icecat_index_file = $location . '/icecat_index.zip';
				}
				else
				{
					$icecat_index_file = $location . '/icecat_index';
					$loadRemoteIndex   = true;
				}
			}

			if ($loadSupplier)
			{
				if (JFile::exists($location . '/icecat_supplier'))
				{
					$icecat_supplier_file = $location . '/icecat_supplier';
				}
				elseif (JFile::exists($location . '/icecat_supplier.gzip'))
				{
					$icecat_supplier_file = $location . '/icecat_supplier.gzip';
				}
				elseif (JFile::exists($location . '/icecat_supplier.zip'))
				{
					$icecat_supplier_file = $location . '/icecat_supplier.zip';
				}
				else
				{
					$icecat_supplier_file = $location . '/icecat_supplier';
					$loadRemoteSupplier   = true;
				}
			}

			// Load the remote files if needed
			if ($loadRemoteIndex || $loadRemoteSupplier)
			{
				$gzip = '';

				// Context for retrieving files
				if ($icecat_gzip)
				{
					$gzip = "Accept-Encoding: gzip\r\n";
				}

				$context = stream_context_create(
					array(
						'http' => array(
							'header'  => "Authorization: Basic " . base64_encode($username . ':' . $password) . "\r\n" . $gzip
						)
					)
				);

				if ($loadIndex && $loadRemoteIndex)
				{
					// ICEcat index file
					$icecat_url = $settings->get('ice_index', 'https://data.icecat.biz/export/freexml.int/INT/files.index.csv');

					if ($icecat_gzip)
					{
						$icecat_index_file .= '.gzip';
					}

					$fp_url   = fopen($icecat_url, 'r', false, $context);
					$fp_local = fopen($icecat_index_file, 'w+');

					while ($content = fread($fp_url, 1024536))
					{
						fwrite($fp_local, $content);
					}

					fclose($fp_url);
					fclose($fp_local);
				}

				if ($loadSupplier && $loadRemoteSupplier)
				{
					// Load the manufacturer data
					$icecat_mf = $settings->get('ice_supplier', 'https://data.icecat.biz/export/freexml.int/INT/supplier_mapping.xml');

					if ($icecat_gzip)
					{
						$icecat_supplier_file .= '.gzip';
					}

					$fp_url = fopen($icecat_mf, 'r', false, $context);
					$fp_local = fopen($icecat_supplier_file, 'w+');

					while ($content = fread($fp_url, 1024536))
					{
						fwrite($fp_local, $content);
					}

					fclose($fp_url);
					fclose($fp_local);
				}
			}

			// Check if we need to unpack the files
			if ($loadIndex)
			{
				if (substr($icecat_index_file, -3) == 'zip')
				{
					if (!$this->unpackIcecat($icecat_index_file, CSVIPATH_TMP))
					{
						$this->log->addStats('incorrect', 'COM_CSVI_ICECAT_INDEX_NOT_UNPACKED', 'maintenance');

						throw new RuntimeException(JText::_('COM_CSVI_ICECAT_INDEX_NOT_UNPACKED'));
					}
				}

				$session->set('icecat_index_file', serialize($icecat_index_file), 'com_csvi');
			}

			if ($loadSupplier)
			{
				if (substr($icecat_supplier_file, -3) == 'zip')
				{
					if (!$this->unpackIcecat($icecat_supplier_file, CSVIPATH_TMP))
					{
						$this->log->addStats('incorrect', 'COM_CSVI_ICECAT_SUPPLIER_NOT_UNPACKED', 'maintenance');

						throw new RuntimeException(JText::_('COM_CSVI_ICECAT_SUPPLIER_NOT_UNPACKED'));
					}
					else
					{
						$icecat_supplier_file = CSVIPATH_TMP . '/icecat_supplier';
					}
				}

				$session->set('icecat_supplier_file', serialize($icecat_supplier_file), 'com_csvi');
			}
		}
		else
		{
			$this->log->addStats('incorrect', 'COM_CSVI_ICECAT_NO_USER_PASS', 'maintenance');

			throw new InvalidArgumentException(JText::_('COM_CSVI_ICECAT_NO_USER_PASS'));
		}
	}

	/**
	 * Load the ICEcat indexes.
	 *
	 * @param   JInput  $input  The input model
	 * @param   mixed   $key    A reference used by the method.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.0
	 */
	public function icecatIndex(JInput $input, $key)
	{
		$result = false;

		if ($key > 0)
		{
			$result = $this->indexIcecat($input, $key);
		}
		else
		{
			$linenumber = $this->log->getLinenumber();

			// Load the index files
			$session              = JFactory::getSession();
			$icecat_index_file    = unserialize($session->get('icecat_index_file', '', 'com_csvi'));
			$icecat_supplier_file = unserialize($session->get('icecat_supplier_file', '', 'com_csvi'));

			// Should we load the index file in 1 go
			$loadtype = $input->get('loadtype', true, 'bool');

			// Check which files to load
			$load_index     = $input->get('icecat_index', false, 'boolean');
			$load_supplier  = $input->get('icecat_supplier', false, 'boolean');
			$icecat_records = $input->get('icecat_records', 1000, 'int');
			$session->set('icecat_records', serialize($icecat_records), 'com_csvi');
			$icecat_wait = $input->get('icecat_wait', 5, 'int');
			$session->set('icecat_wait', serialize($icecat_wait), 'com_csvi');

			// First load the supplier file, it is small and easy to do
			if ($linenumber == 0 && $load_supplier && $icecat_supplier_file)
			{
				// Add the line number
				$this->log->setLinenumber(++$linenumber);

				// Empty the supplier table
				$this->db->truncateTable('#__csvi_icecat_suppliers');

				// Reset the supplier file
				$xmlstr        = file_get_contents($icecat_supplier_file);
				$xml           = new SimpleXMLElement($xmlstr);
				$supplier_data = array();

				foreach ($xml->SupplierMappings->children() as $mapping)
				{
					foreach ($mapping->attributes() as $attr_name => $attr_value)
					{
						switch ($attr_name)
						{
							case 'supplier_id':
								$supplier_id = $attr_value;
								break;
							case 'name':
								$supplier_data[] = '(' . $this->db->quote($supplier_id) . ',' . $this->db->quote($attr_value) . ')';
								break;
						}
					}

					foreach ($mapping->children() as $symbol)
					{
						$supplier_data[] = '(' . $this->db->quote($supplier_id) . ',' . $this->db->quote($symbol) . ')';
					}
				}

				$q = 'INSERT IGNORE INTO ' . $this->db->quoteName('#__csvi_icecat_suppliers') . ' VALUES ' . implode(',', $supplier_data);
				$this->db->setQuery($q);

				try
				{
					$this->db->execute();
					$input->set('linesprocessed', $this->db->getAffectedRows());
					$this->log->addStats('added', 'COM_CSVI_ICECAT_SUPPLIERS_LOADED');
				}
				catch (Exception $e)
				{
					$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_ICECAT_SUPPLIERS_NOT_LOADED', $e->getMessage()));
				}
			}

			if (!$loadtype && $load_index)
			{
				if ($icecat_index_file)
				{
					// Empty the index table
					$this->db->truncateTable('#__csvi_icecat_index');

					// Load the files using INFILE
					$q = "LOAD DATA LOCAL INFILE " . $this->db->quote($icecat_index_file) . "
						INTO TABLE " . $this->db->quoteName('#__csvi_icecat_index') . "
						FIELDS TERMINATED BY '\t' ENCLOSED BY '\"'
						IGNORE 1 LINES";
					$this->db->setQuery($q);

					// Add the line number
					$this->log->setLinenumber(++$linenumber);

					try
					{
						$result = $this->db->execute();
						$input->set('linesprocessed', $input->get('linesprocessed') + $this->db->getAffectedRows());
						$this->log->addStats('added', 'COM_CSVI_ICECAT_INDEX_LOADED');
					}
					catch (Exception $e)
					{
						$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_ICECAT_INDEX_NOT_LOADED', $e->getMessage()));
					}
				}
				else
				{
					$this->log->addStats('incorrect', 'COM_CSVI_ICECAT_INDEX_FILE_NOT_FOUND');
				}
			}
			else
			{
				// Load the files in 1 go using cron
				if (CSVI_CLI)
				{
					$continue = true;

					while ($continue)
					{
						$result = $this->indexIcecat($input, $key);
						$continue = $input->get('continue');
					}
				}
				// Load the files in steps using gui
				else
				{
					if ($key == 0)
					{
						// Empty the index table
						$this->db->truncateTable('#__csvi_icecat_index');
					}

					$result = $this->indexIcecat($input, $key);
				}
			}
		}

		return $result;
	}

	/**
	 * Post processing index ICEcat.
	 *
	 * @return  array  Settings for continuing.
	 *
	 * @since   6.0
	 */
	public function onAftericecatIndex()
	{
		$results['continue'] = false;

		if ($this->key)
		{
			// Return data
			$results = array();
			$results['continue'] = true;
			$results['key'] = $this->key;
		}

		$results['info'] = $this->message;

		return $results;
	}

	/**
	 * Unpack the ICEcat index files.
	 *
	 * @param   string  $archivename  The full path and name of the file to extract
	 * @param   string  $extractdir   The folder to copy the extracted file to
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   3.0
	 */
	private function unpackIcecat($archivename, $extractdir)
	{
		$adapter = JArchive::getAdapter('gzip');

		if (file_exists($archivename) && $adapter)
		{
			$config   = JFactory::getConfig();
			$tmpfname = $config->get('tmp_path') . '/' . uniqid('gzip');

			try
			{
				$adapter->extract($archivename, $tmpfname);
			}
			catch (Exception $e)
			{
				@unlink($tmpfname);

				return false;
			}

			$path = JPath::clean($extractdir);
			JFolder::create($path);
			JFile::copy($tmpfname, $path . '/' . JFile::stripExt(basename(strtolower($archivename))));
			@unlink($tmpfname);
		}

		return true;
	}

	/**
	 * Load the ICEcat index in batches.
	 *
	 * @param   JInput  $input  The input model
	 * @param   mixed   $key    A reference used by the method.
	 *
	 * @return  array  Status list.
	 *
	 * @since   3.3
	 */
	private function indexIcecat(JInput $input, $key)
	{
		$linenumber = $this->log->getLinenumber();

		// Session init
		$session           = JFactory::getSession();
		$icecat_index_file = unserialize($session->get('icecat_index_file', '', 'com_csvi'));
		$records           = unserialize($session->get('icecat_records', '', 'com_csvi'));
		$wait              = unserialize($session->get('icecat_wait', 5, 'com_csvi'));
		$finished          = false;
		$continue          = true;
		$result            = array(
			'cancel' => false,
			'process' => true,
			'continue' => false,
		);

		if ($icecat_index_file)
		{
			// Sleep to please the server
			sleep($wait);

			// Load the records line by line
			$query = $this->db->getQuery(true)
				->insert($this->db->quoteName('#__csvi_icecat_index'))
				->columns(
					$this->db->quoteName('path') . ','
					. $this->db->quoteName('product_id') . ','
					. $this->db->quoteName('updated') . ','
					. $this->db->quoteName('quality') . ','
					. $this->db->quoteName('supplier_id') . ','
					. $this->db->quoteName('prod_id') . ','
					. $this->db->quoteName('catid') . ','
					. $this->db->quoteName('m_prod_id') . ','
					. $this->db->quoteName('ean_upc') . ','
					. $this->db->quoteName('on_market') . ','
					. $this->db->quoteName('country_market') . ','
					. $this->db->quoteName('model_name') . ','
					. $this->db->quoteName('product_view') . ','
					. $this->db->quoteName('high_pic') . ','
					. $this->db->quoteName('high_pic_size') . ','
					. $this->db->quoteName('high_pic_width') . ','
					. $this->db->quoteName('high_pic_height') . ','
					. $this->db->quoteName('m_supplier_id') . ','
					. $this->db->quoteName('m_supplier_name') . ','
					. $this->db->quoteName('ean_upc_is_approved') . ','
					. $this->db->quoteName('Limited') . ','
					. $this->db->quoteName('Date_Added')
				);

			if (($handle = fopen($icecat_index_file, "r")) !== false)
			{
				// Position pointers
				$row = 0;

				// Position file pointer
				fseek($handle, $key);

				// Start processing
				while ($continue)
				{
					if ($row < $records)
					{
						$data = fgetcsv($handle, 4096, "\t");

						if ($data)
						{
							$row++;
							$lines = array();

							foreach ($data as $item)
							{
								if (empty($item))
								{
									$lines[] = 'NULL';
								}
								else
								{
									$lines[] = $this->db->quote($item);
								}
							}

							$query->values(implode(',', $lines));
						}
						else
						{
							$finished = true;
							$continue = false;
						}
					}
					else
					{
						$continue = false;
					}
				}

				// Store the data
				$this->db->setQuery($query);

				if ($this->db->execute())
				{
					$this->log->setLinenumber(++$linenumber);
					$this->log->addStats('added', 'COM_CSVI_ICECAT_INDEX_LOADED');

					// Store for future use
					if (!$finished)
					{
						$this->key = ftell($handle);
						$this->message = JText::sprintf('COM_CSVI_PROCESS_LINES', $row);
					}
					else
					{
						$this->log->addStats('added', 'COM_CSVI_ICECAT_INDEX_LOADED');

						// Clear the session
						$session->clear('icecat_index_file', 'com_csvi');
						$session->clear('icecat_supplier_file', 'com_csvi');
						$session->clear('icecat_records', 'com_csvi');
						$session->clear('icecat_wait', 'com_csvi');
						$session->clear('form', 'com_csvi');
					}

					$result['continue'] = true;
				}
				else
				{
					$result['continue'] = false;
				}

				fclose($handle);
			}
		}

		return $result;
	}

	/**
	 * Clean up because the user has cancelled the operation.
	 *
	 * @return  bool  Returns true.
	 *
	 * @since   6.0
	 */
	public function cancelOperation()
	{
		// Clean the session
		$session = JFactory::getSession();

		$session->clear('icecat_index_file', 'com_csvi');
		$session->clear('icecat_supplier_file', 'com_csvi');

		return true;
	}

	/**
	 * Delete the CSVI tables.
	 *
	 * @return  bool  Returns true.
	 *
	 * @since   6.0
	 */
	public function deleteTables()
	{
		$tables = array(
			'csvi_availablefields',
			'csvi_availabletables',
			'csvi_currency',
			'csvi_icecat_index',
			'csvi_icecat_suppliers',
			'csvi_logdetails',
			'csvi_logs',
			'csvi_mapheaders',
			'csvi_maps',
			'csvi_processed',
			'csvi_related_categories',
			'csvi_related_products',
			'csvi_rules',
			'csvi_sefurls',
			'csvi_processes',
			'csvi_settings',
			'csvi_tasks',
			'csvi_templatefields',
			'csvi_templatefields_rules',
			'csvi_templates',
			'csvi_templates_rules',
			'csvi_template_fields_combine',
			'csvi_template_fields_replacement'
		);

		foreach ($tables as $tablename)
		{
			$this->db->dropTable($this->db->getPrefix() . $tablename);
		}

		return true;
	}

	/**
	 * Post process table deletion.
	 *
	 * @return  array  Option to cancel any further execution.
	 *
	 * @since   6.0
	 */
	public function onAfterDeleteTables()
	{
		// Store the message to show
		$this->csvihelper->enqueueMessage(JText::_('COM_CSVI_ALL_TABLES_DELETED'));

		// Since we have no tables left and user plans to uninstall, we need to redirect to the extension manager
		$cancel = array('url' => 'index.php?option=com_installer&view=manage&filter[search]=csvi&filter[type]=package');
		JFactory::getApplication()->input->set('canceloptions', $cancel);

		return array('cancel' => true);
	}

	/**
	 * Install any available example template.
	 *
	 * @param   JInput  $input  The input model
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   6.4.0
	 */
	public function exampleTemplates(JInput $input)
	{
		// Get a list of example templates to install
		$components = $this->csvihelper->getComponents();
		jimport('joomla.filesystem.file');
		$selectedComponents = $input->get('addons', '', 'array');

		foreach ($components as $component)
		{
			if (in_array($component->value, $selectedComponents))
			{
				// Process all extra available fields
				$extension = substr($component->value, 4);
				$filename  = JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component->value . '/install/templates.xml';

				if (JFile::exists($filename))
				{
					// Check if the component is installed
					if (substr($component->value, 0, 4) == 'com_')
					{
						$query = $this->db->getQuery(true)
							->select($this->db->quoteName('extension_id'))
							->from($this->db->quoteName('#__extensions'))
							->where($this->db->quoteName('element') . ' = ' . $this->db->quote($component->value));
						$this->db->setQuery($query);
						$ext_id = $this->db->loadResult();
					}
					else
					{
						$ext_id = true;
					}

					if ($ext_id)
					{
						$this->log->add('Processing template file ' . $filename);

						// Install the templates
						if ($this->restoreTemplates($input, 0, $filename))
						{
							$this->log->addStats('added', JText::sprintf('COM_CSVI_ADDED_EXAMPLE_TEMPLATEFILE', JText::_('COM_CSVI_' . $component->value)));
						}
					}
				}
			}
		}

		$this->log->setLinenumber($this->totalRestoredTemplates);
	}

	/**
	 * Create available fields table.
	 *
	 * @return  bool  True if table has been created.
	 *
	 * @since   6.5.0
	 *
	 * @throws  CsviException
	 */
	private function createAvailableFieldsTable()
	{
		$query = 'CREATE TABLE IF NOT EXISTS ' . $this->db->quoteName('#__csvi_availablefields') . ' ('
			. $this->db->quoteName('csvi_availablefield_id') . ' INT(11) NOT NULL AUTO_INCREMENT,'
			. $this->db->quoteName('csvi_name') . ' VARCHAR(255) NOT NULL,'
			. $this->db->quoteName('component_name') . ' VARCHAR(55) NOT NULL,'
			. $this->db->quoteName('component_table') . ' VARCHAR(55) NOT NULL,'
			. $this->db->quoteName('component') . ' VARCHAR(55) NOT NULL,'
			. $this->db->quoteName('action') . ' VARCHAR(6) NOT NULL,'
			. $this->db->quoteName('isprimary') . ' TINYINT(1) NOT NULL DEFAULT \'0\',
					PRIMARY KEY (' . $this->db->quoteName('csvi_availablefield_id') . '),
					UNIQUE INDEX ' . $this->db->quoteName('component_name_table') . ' ('
			. $this->db->quoteName('component_name') . ', '
			. $this->db->quoteName('component_table') . ', '
			. $this->db->quoteName('component') . ', '
			. $this->db->quoteName('action') . ')
				  ) CHARSET=utf8 COMMENT=\'Available fields for CSVI\'';
		$this->db->setQuery($query);

		try
		{
			$this->db->execute();
			$this->log->addStats('created', 'COM_CSVI_AVAILABLE_FIELDS_TABLE_CREATED', 'availablefields');
		}
		catch (Exception $e)
		{
			throw new CsviException($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * Post processing of backup templates.
	 *
	 * @return  array  Settings for continuing.
	 *
	 * @since   6.6.0
	 */
	public function onAfterBackupTemplates()
	{
		return array('downloadfile' => $this->downloadfile);
	}

	/**
	 * Threshold available fields for extension
	 *
	 * @return  int Hardcoded available fields
	 *
	 * @since   7.0
	 */
	public function availableFieldsThresholdLimit()
	{
		return 0;
	}

	/**
	 * Update the available fields of custom table
	 *
	 * @param   String  $tables  The custom table name
	 * @param   String  $action  Action
	 *
	 * @return  void
	 *
	 * @since   7.1.0
	 */

	public function saveCustomTableAvailableFields($tables, $action)
	{
		if (!is_array($tables))
		{
			$tables = (array) $tables;
		}

		foreach ($tables as $table)
		{
			// Check if the table is already listed
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('csvi_availabletable_id'))
				->from($this->db->quoteName('#__csvi_availabletables'))
				->where($this->db->quoteName('template_table') . ' = ' . $this->db->quote($table))
				->where($this->db->quoteName('component') . ' = ' . $this->db->quote('com_csvi'))
				->where($this->db->quoteName('action') . ' = ' . $this->db->quote($action));

			$this->db->setQuery($query);
			$csviAvailableTableId = $this->db->loadResult();

			// Add the table to the available fields table if needed
			if (!$csviAvailableTableId)
			{
				$query->clear()
					->insert($this->db->quoteName('#__csvi_availabletables'))
					->columns(
						$this->db->quoteName('task_name') . ',' .
						$this->db->quoteName('template_table') . ',' .
						$this->db->quoteName('component') . ',' .
						$this->db->quoteName('action') . ',' .
						$this->db->quoteName('enabled')
					)
					->values(
						$this->db->quote('custom') . ',' .
						$this->db->quote($table) . ',' .
						$this->db->quote('com_csvi') . ',' .
						$this->db->quote($action) . ',' .
						$this->db->quote('1')
					);
				$this->db->setQuery($query)->execute();
			}

			$customTable                 = new stdClass;
			$customTable->template_table = $table;
			$customTable->component      = 'com_csvi';
			$customTable->action         = $action;
			$this->indexTable($customTable, false);
		}
	}
}
