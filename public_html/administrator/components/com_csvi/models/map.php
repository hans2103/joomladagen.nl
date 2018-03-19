<?php
/**
 * @package     CSVI
 * @subpackage  Fieldmapper
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Field mapper model.
 *
 * @package     CSVI
 * @subpackage  Fieldmapper
 * @since       6.6.0
 */
class CsviModelMap extends JModelAdmin
{
	/**
	 * Holds the database driver
	 *
	 * @var    JDatabaseDriver
	 * @since  6.6.0
	 */
	private $db;

	/**
	 * Holds the input object
	 *
	 * @var    JInput
	 * @since  6.6.0
	 */
	private $input;

	/**
	 * Construct the class.
	 *
	 * @since   6.6.0
	 *
	 * @throws  Exception
	 */
	public function __construct()
	{
		parent::__construct();

		// Load the basics
		$this->db    = $this->getDbo();
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
	 *
	 * @throws  Exception
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_csvi.map', 'map', array('control' => 'jform', 'load_data' => $loadData));

		if ($form === false)
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
		$data = JFactory::getApplication()->getUserState('com_csvi.edit.map.data', array());

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

		if ($item->csvi_map_id)
		{
			// Load the options
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('action') . ',' . $this->db->quoteName('component') . ',' . $this->db->quoteName('operation'))
				->from($this->db->quoteName('#__csvi_maps'))
				->where($this->db->quoteName('csvi_map_id') . '=' . (int) $item->csvi_map_id);
			$this->db->setQuery($query);
			$item->options = $this->db->loadObject();

			// Load the header fields to match
			$query->clear()
				->select($this->db->quoteName('csvheader') . ',' . $this->db->quoteName('templateheader'))
				->from($this->db->quoteName('#__csvi_mapheaders'))
				->where($this->db->quoteName('map_id') . '=' . (int) $item->csvi_map_id);
			$this->db->setQuery($query);
			$item->headers = $this->db->loadObjectList();
		}

		return $item;
	}

	/**
	 * Method to save the form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   6.6.0
	 *
	 * @throws  Exception
	 */
	public function save($data)
	{
		$data = $this->input->get('jform', array(), 'array');

		// Get the uploaded file
		$file = $this->input->files->get('jform', array(), 'array');

		if (parent::save($data))
		{
			// Get the last insert Id
			$data['csvi_map_id'] = $this->getState($this->getName() . '.id');

			// Let's see if the user uploaded a file to get new columns
			if (!empty($file['mapfile']['name']))
			{
				// Save the file
				$this->processFile($data, $file);
			}
			// Store any mapped fields
			else
			{
				$this->processHeader((int) $data['csvi_map_id']);
			}
		}

		return true;
	}

	/**
	 * Process an uploaded file with headers.
	 *
	 * @param   array  $data  The map data.
	 * @param   array  $file  The posted file.
	 *
	 * @return  bool  True if file is processed | False if file is not processed.
	 *
	 * @since   5.8
	 *
	 * @throws  Exception
	 */
	private function processFile($data, $file)
	{
		// Get the file details
		$upload = array();
		$upload['name'] = $file['mapfile']['name'];
		$upload['type'] = $file['mapfile']['type'];
		$upload['tmp_name'] = $file['mapfile']['tmp_name'];
		$upload['error'] = $file['mapfile']['error'];

		if (!$upload['error'])
		{
			// Move the temporary file
			if (is_uploaded_file($upload['tmp_name']))
			{
				// Get some basic info
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');
				$folder = CSVIPATH_TMP . '/' . time();
				$upload_parts = pathinfo($upload['name']);

				// Create the temp folder
				if (JFolder::create($folder))
				{
					// Move the uploaded file to its temp location
					if (JFile::upload($upload['tmp_name'], $folder . '/' . $upload['name']))
					{
						if (array_key_exists('extension', $upload_parts))
						{
							// Set the file class name to import because that can read the file.
							$fileclass = 'CsviHelperFileImport';

							// Load the extension specific class
							switch (strtolower($upload_parts['extension']))
							{
								case 'xml':
									$fileclass .= 'Xml';
									break;
								case 'xls':
									$fileclass .= 'Xls';
									break;
								case 'ods':
									$fileclass .= 'Ods';
									break;
								default:
									// Treat any unknown type as CSV
									$fileclass .= 'Csv';
									break;
							}

							$csvihelper = new CsviHelperCsvi;
							$settings = new CsviHelperSettings($this->db);
							$log = new CsviHelperLog($settings, $this->db);
							$template = new CsviHelperTemplate(0);
							$template->set('source', 'fromserver');
							$template->set('local_csv_file', $folder . '/' . $upload['name']);
							$template->set('auto_detect_delimiters', $data['auto_detect_delimiters']);
							$template->set('field_delimiter', $data['field_delimiter']);
							$template->set('text_enclosure', $data['text_enclosure']);

							// Auto detect line-endings to also support Mac line-endings
							if ($data['im_mac'])
							{
								ini_set('auto_detect_line_endings', true);
							}

							// Get the file handler
							$file = new $fileclass($template, $log, $csvihelper, $this->input);

							// Set the fields
							$fields = new CsviHelperImportfields($template, $log, $this->db);
							$file->setFields($fields);

							// Validate and process the file
							$file->setFilename($folder . '/' . $upload['name']);
							$file->processFile(true);

							// Get the header
							if ($header = $file->loadColumnHeaders())
							{
								if (is_array($header))
								{
									// Load the table
									$headerTable = JTable::getInstance('Mapheaders', 'Table', array('dbo' => $this->db));

									// Remove existing entries
									$query = $this->db->getQuery(true)
										->delete($this->db->quoteName('#__csvi_mapheaders'))
										->where($this->db->quoteName('map_id') . ' = ' . (int) $data['csvi_map_id']);
									$this->db->setQuery($query);
									$this->db->execute();

									// Store the headers
									$map = array();
									$map['id'] = $data['id'] ?: 0;
									$map['map_id'] = $data['csvi_map_id'];

									foreach ($header as $name)
									{
										$map['csvheader'] = $name;

										// Store the data
										$headerTable->save($map);
										$headerTable->reset();
									}
								}
								else
								{
									return false;
								}
							}
							else
							{
								return false;
							}
						}
						else
						{
							return false;
						}
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Process the header mappings.
	 *
	 * @param   int  $id  The map id.
	 *
	 * @return  void.
	 *
	 * @since   5.8
	 *
	 * @throws  RuntimeException
	 */
	private function processHeader($id)
	{
		foreach ($this->input->get('templateheader', array(), 'array') as $csvheader => $templateheader)
		{
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__csvi_mapheaders'))
				->set($this->db->quoteName('templateheader') . ' = ' . $this->db->quote($templateheader))
				->where($this->db->quoteName('map_id') . ' = ' . (int) $id)
				->where($this->db->quoteName('csvheader') . ' = ' . $this->db->quote($csvheader));
			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   6.6.0
	 *
	 * @throws  RuntimeException
	 */
	public function delete(&$pks)
	{
		if (parent::delete($pks))
		{
			// Remove all map headers IDs
			foreach ($pks as $pk)
			{
				$query = $this->db->getQuery(true)
					->delete($this->db->quoteName('#__csvi_mapheaders'))
					->where($this->db->quoteName('map_id') . ' = ' . (int) $pk);
				$this->db->setQuery($query)->execute();
			}

			return true;
		}

		return false;
	}
}
