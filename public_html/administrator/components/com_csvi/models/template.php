<?php
/**
 * @package     CSVI
 * @subpackage  Template
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use phpseclib\Net\SFTP;

defined('_JEXEC') or die;

/**
 * Template model.
 *
 * @package     CSVI
 * @subpackage  Template
 * @since       6.0
 */
class CsviModelTemplate extends JModelAdmin
{
	/**
	 * Holds the database driver
	 *
	 * @var    JDatabaseDriver
	 * @since  6.0
	 */
	protected $db;

	/**
	 * Holds the input class
	 *
	 * @var    JInput
	 * @since  6.6.0
	 */
	protected $input;

	/**
	 * Holds the template settings
	 *
	 * @var    array
	 * @since  6.0
	 */
	protected $options;

	/**
	 * Construct the class.
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 */
	public function __construct()
	{
		parent::__construct();

		// Load the basics
		$this->db = $this->getDbo();
		$this->input = JFactory::getApplication()->input;
	}

	/**
	 * Get the form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success | False on failure.
	 *
	 * @since   4.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Add our own form path
		JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_csvi/views/template/tmpl/');

		// Get the form.
		$form = $this->loadForm('com_csvi.template', 'operations', array('control' => 'jform', 'load_data' => $loadData));

		if (0 === count($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The data for the form..
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_csvi.edit.template.data', array());

		if (0 === count($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   6.6.0
	 *
	 * @throws  Exception
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		// Get the template information
		$item->options    = new Registry(json_decode($item->settings, true));
		$templateArray    = json_decode(json_encode($item), true);
		$templateSettings = json_decode(ArrayHelper::getValue($templateArray, 'settings'), true);

		if ($templateSettings && array_key_exists('custom_table', $templateSettings))
		{
			$item->count_tables = count($templateSettings['custom_table']);
		}

		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The filtered form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   3.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 * @throws  UnexpectedValueException
	 */
	public function save($data)
	{
		// Get the complete posted data
		$fullData = $this->input->post->getArray(
			array(
				'template_name'    => 'string',
				'enabled'          => 'int',
				'ordering'         => 'int',
				'log'              => 'int',
				'frontend'         => 'int',
				'csvi_template_id' => 'int',
				'secret'           => 'raw',
				'advanced'         => 'int',
			)
		);

		// Set the filtered data
		$fullData['jform'] = $data;

		// Load the table
		$table = $this->getTable('Template');
		$table->load($fullData['csvi_template_id']);

		$query = $this->db->getQuery(true);

		// Prepare the settings
		if (array_key_exists('jform', $fullData))
		{
			// Check if we are in the wizard, if so, we must preload the already stored settings
			if ($this->input->getInt('step', 0))
			{
				$query->clear()
					->select(
						$this->db->quoteName(
							array(
								'settings',
								'action',
							)
						)
					)
					->from($this->db->quoteName('#__csvi_templates'))
					->where($this->db->quoteName('csvi_template_id') . ' = ' . (int) $table->get('csvi_template_id'));
				$this->db->setQuery($query);
				$templateSettings = $this->db->loadObject();

				$fullData['jform']  = array_merge((array) json_decode($templateSettings->settings), $fullData['jform']);
				$fullData['action'] = $templateSettings->action;
			}

			// Clear the FTP details if it is not set as location
			if ((array_key_exists('source', $fullData['jform']) && $fullData['jform']['source'] !== 'fromftp')
				|| (array_key_exists('exportto', $fullData['jform']) && !in_array('toftp', $fullData['jform']['exportto'], true)))
			{
				$fullData['jform']['ftpusername'] = '';
				$fullData['jform']['ftppass']     = '';
			}

			// Clear the URL details if it is not set as location
			if (array_key_exists('source', $fullData['jform']) && $fullData['jform']['source'] !== 'fromurl')
			{
				$fullData['jform']['urlusername'] = '';
				$fullData['jform']['urlpass']     = '';
			}

			// Remove any trailing slash in export to server path
			if (array_key_exists('localpath', $fullData['jform']))
			{
				$localPath = $fullData['jform']['localpath'];

				if (substr($localPath, -1) === '/')
				{
					$localPath = substr($fullData['jform']['localpath'], 0, -1);
				}

				$fullData['jform']['localpath'] = $localPath;
			}

			$fullData['settings'] = json_encode($fullData['jform']);
			$fullData['action']   = $fullData['jform']['action'];
		}

		// Store the table to the custom available fields if needed
		if (array_key_exists('custom_table', $fullData['jform']) && $fullData['jform']['custom_table']['custom_table0'])
		{
			$customTables = array();

			if ($fullData['jform']['import_based_on'])
			{
				$field_name = $fullData['jform']['import_based_on'];
				$importBasedkeys = explode(',', $field_name);
				$customTable = $this->db->getPrefix() . $fullData['jform']['custom_table'];
				$columns = $this->db->getTableColumns($customTable);

				try
				{
					foreach ($importBasedkeys as $importField)
					{
						$importField = trim($importField);

						if (!isset($columns[$importField]))
						{
							$this->setError(JText::sprintf('COM_CSVI_NO_IMPORT_FIELD_FOUND', $importField));

							return false;
						}
					}
				}
				catch (Exception $e)
				{
					throw new CsviException($e->getMessage(), $e->getCode());
				}
			}


			// Load the helpers
			$csvihelper = new CsviHelperCsvi;
			$settings   = new CsviHelperSettings($this->db);
			$log        = new CsviHelperLog($settings, $this->db);

			// For export multiple tables is possible so modify array in readable format
			if ($fullData['action'] === 'export')
			{
				for ($i = 0; $i < count($fullData['jform']['custom_table']) + 1; $i++)
				{
					if (isset($fullData['jform']['custom_table']['custom_table' . $i]))
					{
						$customTables[] = $fullData['jform']['custom_table']['custom_table' . $i]['table'];

						if ($fullData['jform']['custom_table']['custom_table' . $i]['jointable'])
						{
							$customTables[] = $fullData['jform']['custom_table']['custom_table' . $i]['jointable'];
						}
					}
				}

				array_unique($customTables);
				$fullData['jform']['custom_table']['custom_table0'] = array_merge(
					$fullData['jform']['custom_table']['custom_table0'],
					array('jointable' => '', 'joinfield' => '', 'field' => '', 'jointype' => '')
				);
				$fullData['settings'] = json_encode($fullData['jform']);
			}
			else
			{
				$customTables[] = $fullData['jform']['custom_table'];

				// If user changes export template to import or vice versa then take the default first table
				if (isset($fullData['jform']['custom_table']['custom_table0']['table']))
				{
					$customTables = array();
					$customTables[] = $fullData['jform']['custom_table']['custom_table0']['table'];
				}
			}

			// Index the custom table and add available fields table
			require_once JPATH_PLUGINS . '/csviaddon/csvi/com_csvi/model/maintenance.php';
			$maintenanceModel = new Com_CsviMaintenance($this->db, $log, $csvihelper);
			$maintenanceModel->saveCustomTableAvailableFields($customTables, $fullData['action']);
		}

		// Check if the chosen table is the same as the one already stored, if not, we need to remove the template fields
		$customTablesArray = array();
		$settings          = json_decode($table->get('settings'));

		if (isset($settings->custom_table))
		{
			$customTablesArray = json_decode(json_encode($settings->custom_table), true);
		}

		if (isset($settings->custom_table, $fullData['jform']['custom_table'])
			&& array_diff($customTablesArray, $fullData['jform']['custom_table']))
		{
			// Remove all associated fields
			$query->clear()
				->delete($this->db->quoteName('#__csvi_templatefields'))
				->where($this->db->quoteName('csvi_template_id') . ' = ' . (int) $table->get('csvi_template_id'));
			$this->db->setQuery($query)->execute();
		}

		return parent::save($fullData);
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		return $data;

		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception)
		{
			$this->setError($return->getMessage());

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$this->setError($message);
			}

			return false;
		}

		// Tags B/C break at 3.1.2
		if (isset($data['metadata']['tags']) && !isset($data['tags']))
		{
			$data['tags'] = $data['metadata']['tags'];
		}

		return $data;
	}

	/**
	 * Delete a template
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  Return false to raise an error, true otherwise
	 *
	 * @throws  RuntimeException
	 *
	 * @since   3.0
	 */
	public function delete(&$pks)
	{
		if (parent::delete($pks))
		{
			foreach ($pks as $pk)
			{
				// Delete the template field rules
				$query = $this->db->getQuery(true)
					->select($this->db->quoteName('csvi_templatefield_id'))
					->from($this->db->quoteName('#__csvi_templatefields'))
					->where($this->db->quoteName('csvi_template_id') . ' = ' . (int) $pk);
				$this->db->setQuery($query);
				$fieldIds = $this->db->loadColumn();

				if ($fieldIds)
				{
					// Delete the rules
					$query->clear()
						->delete($this->db->quoteName('#__csvi_templatefields_rules'))
						->where($this->db->quoteName('csvi_templatefield_id') . ' IN (' . implode(',', $fieldIds) . ')');
					$this->db->setQuery($query)->execute();
				}

				// Delete the template fields
				$query->clear()
					->delete($this->db->quoteName('#__csvi_templatefields'))
					->where($this->db->quoteName('csvi_template_id') . ' = ' . (int) $pk);
				$this->db->setQuery($query)->execute();
			}
		}

		return true;
	}

	/**
	 * Test the FTP details.
	 *
	 * @return  bool  True if connection works | Fails if connection fails.
	 *
	 * @since   4.3.2
	 *
	 * @throws  CsviException
	 * @throws  InvalidArgumentException
	 */
	public function testFtp()
	{
		$ftphost     = $this->input->get('ftphost', '', 'string');
		$ftpport     = $this->input->get('ftpport');
		$ftpusername = $this->input->get('ftpusername', '', 'string');
		$ftppass     = $this->input->get('ftppass', '', 'string');
		$ftproot     = $this->input->get('ftproot', '', 'string');
		$ftpfile     = $this->input->get('ftpfile', '', 'string');
		$action      = $this->input->get('action');
		$sftp		 = $this->input->getInt('sftp', 0);

		if ($sftp)
		{
			return $this->testSftp();
		}

		// Set up the ftp connection
		jimport('joomla.client.ftp');
		$ftp = JClientFtp::getInstance($ftphost, $ftpport, array(), $ftpusername, $ftppass);

		try
		{
			// Try to login again because Joomla! doesn't let us know when the username and/or password is wrong
			if ($ftp->isConnected())
			{
				// See if we can change folder
				if ($ftp->chdir($ftproot))
				{
					$result = true;

					if ($action === 'import' && $ftpfile)
					{
						// Check if the file exists
						$files = $ftp->listNames();

						if (!is_array($files))
						{
							throw new CsviException(JText::sprintf('COM_CSVI_FTP_NO_FILES_FOUND', $ftp->pwd()));
						}

						if (!in_array($ftpfile, $files, true))
						{
							throw new CsviException(JText::sprintf('COM_CSVI_FTP_FILE_NOT_FOUND', $ftpfile, $ftp->pwd()));
						}
					}
				}
				else
				{
					throw new CsviException(JText::sprintf('COM_CSVI_FTP_FOLDER_NOT_FOUND', $ftproot));
				}
			}
			else
			{
				throw new InvalidArgumentException(JText::_('COM_CSVI_FTP_CREDENTIALS_INVALID'));
			}

			// Close up
			$ftp->quit();
		}
		catch (Exception $e)
		{
			// Close up
			$ftp->quit();

			throw new CsviException($e->getMessage(), $e->getCode());
		}

		return $result;
	}

	/**
	 * Test the FTP details.
	 *
	 * @return  bool  True if connection works | Fails if connection fails.
	 *
	 * @since   4.3.2
	 *
	 * @throws  CsviException
	 * @throws  InvalidArgumentException
	 */
	public function testSftp()
	{
		$ftphost     = $this->input->get('ftphost', '', 'string');
		$ftpport     = $this->input->get('ftpport');
		$ftpusername = $this->input->get('ftpusername', '', 'string');
		$ftppass     = $this->input->get('ftppass', '', 'string');
		$ftproot     = $this->input->get('ftproot', '', 'string');
		$ftpfile     = $this->input->get('ftpfile', '', 'string');
		$action      = $this->input->get('action');

		try
		{
			$sftp = new SFTP($ftphost, $ftpport);

			if (!$sftp->login($ftpusername, $ftppass))
			{
				throw new InvalidArgumentException(JText::_('COM_CSVI_FTP_CREDENTIALS_INVALID'));
			}

			// See if we can change folder
			if ($sftp->chdir($ftproot))
			{
				$result = true;

				if ($action === 'import' && $ftpfile)
				{
					// Check if the file exists
					$files = $sftp->nlist();

					if (!is_array($files))
					{
						throw new CsviException(JText::sprintf('COM_CSVI_FTP_NO_FILES_FOUND', $sftp->pwd()));
					}

					if (!in_array($ftpfile, $files, true))
					{
						throw new CsviException(JText::sprintf('COM_CSVI_FTP_FILE_NOT_FOUND', $ftpfile, $sftp->pwd()));
					}
				}
			}
			else
			{
				throw new CsviException(JText::sprintf('COM_CSVI_FTP_FOLDER_NOT_FOUND', $ftproot));
			}
		}
		catch (Exception $e)
		{
			throw new CsviException($e->getMessage(), $e->getCode());
		}

		return $result;
	}


	/**
	 * Test if the URL exists.
	 *
	 * @return  bool  True if URL exists | Fails otherwise.
	 *
	 * @since   6.5.0
	 *
	 * @throws  CsviException
	 */
	public function testURL()
	{
		$testurl            = $this->input->get('testurl', '', 'string');
		$testuser           = $this->input->get('testuser', '', 'string');
		$testuserfield      = $this->input->get('testuserfield', '', 'string');
		$testpass           = $this->input->get('testpass', '', 'string');
		$testpassfield      = $this->input->get('testpassfield', '', 'string');
		$testmethod         = $this->input->get('testmethod', 'GET', 'string');
		$testcredentialtype = $this->input->get('testcredentialtype', 'htaccess', 'string');
		$csvihelper    = new CsviHelperCsvi;

		if (!$csvihelper->fileExistsRemote($testurl, $testuser, $testpass, $testmethod, $testuserfield, $testpassfield, $testcredentialtype))
		{
			throw new CsviException(JText::_('COM_CSVI_URL_TEST_NO_SUCCESS'));
		}

		return true;
	}

	/**
	 * Test if the server path is valid.
	 *
	 * @return  bool  True if URL exists | Fails otherwise.
	 *
	 * @since   6.5.0
	 *
	 * @throws  CsviException
	 * @throws  UnexpectedValueException
	 */
	public function testPath()
	{
		jimport('joomla.filesystem.folder');

		$testPath = $this->input->get('testpath', '', 'string');

		$file = JPath::clean($testPath, '/');

		// If the given path is a folder or file, check if its valid
		if (!JFolder::exists($file) && (!JFile::exists($file)))
		{
			throw new CsviException(JText::_('COM_CSVI_PATH_TEST_NO_SUCCESS'));
		}

		return true;
	}

	/**
	 * Copy one ore more templates to a new one.
	 *
	 * @param   array  $templateIds  The IDs of the template(s) to copy.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @throws  CsviException
	 * @throws  RuntimeException
	 *
	 * @since   6.0
	 */
	public function createCopy($templateIds)
	{
		if (!is_array($templateIds))
		{
			$templateIds = (array) $templateIds;
		}

		$table = $this->getTable();

		foreach ($templateIds as $templateId)
		{
			$table->load($templateId);
			$table->set('csvi_template_id', 0);
			$table->set('lastrun', $this->db->getNullDate());
			$table->set('template_name', $table->get('template_name') . ' copy');

			if ($table->store())
			{
				// Copy also the template fields
				$query = $this->db->getQuery(true)
					->select($this->db->quoteName('csvi_templatefield_id'))
					->from($this->db->quoteName('#__csvi_templatefields'))
					->where($this->db->quoteName('csvi_template_id') . ' = ' . (int) $templateId);
				$this->db->setQuery($query);
				$fieldIds = $this->db->loadColumn();

				$ftable = $this->getTable('Templatefield');

				foreach ($fieldIds as $fieldId)
				{
					$ftable->load($fieldId);
					$ftable->set('csvi_templatefield_id', 0);
					$ftable->set('csvi_template_id', $table->get('csvi_template_id'));
					$ftable->store();

					// Copy the template field rules
					$query->clear()
						->select($ftable->get('csvi_templatefield_id'))
						->select($this->db->quoteName('csvi_rule_id'))
						->from($this->db->quoteName('#__csvi_templatefields_rules'))
						->where($this->db->quoteName('csvi_templatefield_id') . ' = ' . (int) $fieldId);
					$this->db->setQuery($query);
					$templatefieldruleIds = $this->db->loadAssocList();

					if (count($templatefieldruleIds) > 0)
					{
						$query->clear()
							->insert($this->db->quoteName('#__csvi_templatefields_rules'))
							->columns(
								$this->db->quoteName(
									array(
										'csvi_templatefield_id',
										'csvi_rule_id'
									)
								)
							);

						foreach ($templatefieldruleIds as $rule)
						{
							$query->values(implode(',', $rule));
						}

						$this->db->setQuery($query)->execute();
					}
				}
			}
			else
			{
				throw new CsviException(JText::sprintf('COM_CSVI_CANNOT_COPY_TEMPLATE', $table->getError()));
			}
		}

		return true;
	}

	/**
	 * Test the database connection details.
	 *
	 * @return  bool  True if connection works | Fails if connection fails.
	 *
	 * @since   6.7.0
	 *
	 * @throws  CsviException
	 * @throws  InvalidArgumentException
	 */
	public function testDbConnection()
	{
		$details              = array();
		$details['user']      = $this->input->get('dbusername', '', 'string');
		$details['password']  = $this->input->get('dbpassword', '', 'string');
		$details['database']  = $this->input->get('dbname', '', 'string');
		$portNo               = $this->input->get('dbportno');
		$hostName             = $this->input->get('dburl', '', 'string');
		$tableName            = $this->input->get('dbtable', '', 'string');
		$action               = $this->input->get('action', '', 'string');
		$createDatabase       = false;
		$createTable          = false;

		if ($action != 'import')
		{
			$createTable = true;
		}

		if ((strpos($hostName, ':') === false) && $portNo)
		{
			$hostName = $hostName . ':' . $portNo;
		}

		$details['host'] = $hostName;
		$database        = JDatabaseDriver::getInstance($details);

		try
		{
			$database->connect();
			$database->connected();

			if ($createTable)
			{
				// Create table if not exists if connected to database
				$query = "CREATE TABLE IF NOT EXISTS" . $database->quoteName($tableName) . "  (
			" . $database->quoteName('id') . " int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (" . $database->quoteName('id') . ")) CHARSET=utf8";
				$database->setQuery($query)->execute();
			}
		}
		catch (Exception $e)
		{
			if ($e->getMessage() === 'Could not connect to database.' && $createTable)
			{
				$createDatabase = true;
			}
			else
			{
				throw new CsviException(JText::sprintf('COM_CSVI_DBCONNECTION_TEST_NO_SUCCESS', $e->getMessage()));
			}
		}

		// If there is no database, create one
		if ($createDatabase)
		{
			// Create database
			$options          = new stdClass;
			$options->db_name = $details['database'];
			$options->db_user = $details['user'];
			$database->createDatabase($options);

			if ($database->select($details['database']))
			{
				// Create table if not exists
				$query = "CREATE TABLE IF NOT EXISTS" . $database->quoteName($tableName) . "  (
				" . $database->quoteName('id') . " int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (" . $database->quoteName('id') . ")) CHARSET=utf8";

				try
				{
					$database->setQuery($query);
					$database->execute();
				}
				catch (Exception $e)
				{
					throw new CsviException(JText::sprintf('COM_CSVI_TABLE_CREATE_NOT_SUCCESS', $e->getMessage()));
				}
			}
		}

		$database->disconnect();

		return true;
	}

	/**
	 * Get custom table fields columns
	 *
	 * @param   string  $table  Table name
	 *
	 * @return  array $columns The fields names.
	 *
	 * @since   7.2.0
	 *
	 * @throws  Exception
	 * @throws  CsviException
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 */
	public function getTableColumns($table)
	{
		$columns = array();
		$tableName = $this->db->getPrefix() . $table;
		$tableColumns = $this->db->getTableColumns($tableName);

		foreach ($tableColumns as $keyField => $field)
		{
			if ($field === 'int' || $field === 'int unsigned')
			{
				$columns[$keyField] = $keyField;
			}
		}

		return array('columns' => $columns);
	}
}
