<?php
/**
 * @package     CSVI
 * @subpackage  Model
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Logs model.
 *
 * @package     CSVI
 * @subpackage  Model
 * @since       6.0
 */
class CsviModelLogs extends JModelList
{
	/**
	 * The database class
	 *
	 * @var    JDatabase
	 * @since  6.0
	 */
	protected $db;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  6.0
	 */
	protected $log;

	/**
	 * CSVI settings
	 *
	 * @var    CsviHelperSettings
	 * @since  6.0
	 */
	protected $settings;

	/**
	 * The CSVI Language helper
	 *
	 * @var    CsviHelperLanguage
	 * @since  6.0
	 */
	protected $language;

	/**
	 * An instance of JInput
	 *
	 * @var    JInput
	 * @since  6.6.0
	 */
	private $input;

	/**
	 * Public class constructor
	 *
	 * @param   array  $config  The configuration array
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'actiontypes', 'l.actiontypes',
				'template_name', 'l.template_name',
				'csvi_log_id', 'l.csvi_log_id',
				'start', 'l.start',
				'end', 'l.end',
				'action_type', 'l.action_type',
				'action', 'l.action',
				'records', 'l.records',
				'file_name', 'l.file_name',
				'run_cancelled', 'l.run_cancelled',
				'name', 'u.name',

			);
		}

		// Initialise some values
		$this->db = JFactory::getDbo();
		$this->settings = new CsviHelperSettings($this->db);
		$this->log = new CsviHelperLog($this->settings, $this->db);
		$this->language = new CsviHelperLanguage;
		$this->input = JFactory::getApplication()->input;

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  object the query to execute
	 *
	 * @since   4.0
	 *
	 * @throws  RuntimeException
	 */
	protected function getListQuery()
	{
		$query = $this->db->getQuery(true)
			->select(
				$this->db->quoteName(
					array(
						'csvi_log_id',
						'start',
						'end',
						'addon',
						'action',
						'action_type',
						'template_name',
						'records',
						'file_name',
						'run_cancelled'
					)
				)
			)
			->from($this->db->quoteName('#__csvi_logs', 'l'))
			->select($this->db->quoteName('u.name', 'runuser'))
			->leftJoin(
				$this->db->quoteName('#__users', 'u') . ' ON ' . $this->db->quoteName('u.id') . ' = ' . $this->db->quoteName('l.userid')
			);

		$actiontype = $this->getState('filter.actiontypes');

		if ($actiontype)
		{
			$query->where($this->db->quoteName('action') . ' = ' . $this->db->quote($actiontype));
		}

		// Add the list ordering clause.
		$query->order(
			$this->db->quoteName(
				$this->db->escape(
					$this->getState('list.ordering', 'l.start')
				)
			)
			. ' ' . $this->db->escape($this->getState('list.direction', 'DESC'))
		);

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   6.6.0
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Load the needed languages
		$loaded = array();

		foreach ($items as $item)
		{
			if ($item->addon && !in_array($item->addon, $loaded, true))
			{
				$loaded[] = $item->addon;
				$this->language->loadAddonLanguage($item->addon);
			}
		}

		return $items;
	}

	/**
	 * Store the log results
	 *
	 * @return  void
	 *
	 * @since   3.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 * @throws  UnexpectedValueException
	 */
	public function storeLogResults()
	{
		$logresult = $this->log->getStats();
		$details = array();
		$logcount = array();

		// Get the number of lines processed based on type
		switch ($logresult['action'])
		{
			case 'import':
				$logcount['import'] = $this->input->getInt('recordsprocessed', 0);
				break;
			case 'export':
				$logcount['export'] = $this->input->getInt('logcount', 0);
				break;
			case 'maintenance':
				$logcount['maintenance'] = $this->input->getInt('linesprocessed', 0);
				break;
		}

		// Get the database connector
		$logTable = $this->getTable('Log');

		// Check for an existing run ID
		$logId = $this->log->getLogId();

		if (!$logId)
		{
			// Get user ID
			$my = JFactory::getUser();
			$details['userid'] = $my->id;

			// Create GMT timestamp
			jimport('joomla.utilities.date');
			$jnow = new JDate(time());
			$details['logstamp'] = $jnow->toSql();

			// Set the addon the import/export is for
			$details['addon'] = $logresult['addon'];

			// Set action if it is import or export
			$details['action'] = $logresult['action'];

			// Type of action
			$details['action_type'] = $logresult['action_type'];

			// Name of template used
			$details['template_name'] = $logresult['action_template'];

			// Get the number of records
			$details['records'] = $logcount[$logresult['action']];

			// Get the import filename
			$details['file_name'] = $this->log->getFilename();

			// Bind the data
			if (!$logTable->bind($details))
			{
				throw new RuntimeException(JText::_('COM_CSVI_CANNOT_BIND_LOG_DATA', 0));
			}

			// Check the data
			if (!$logTable->check())
			{
				throw new RuntimeException(JText::_('COM_CSVI_CANNOT_CHECK_LOG_DATA', 0));
			}

			// Store the data
			if (!$logTable->store())
			{
				throw new RuntimeException(JText::_('COM_CSVI_CANNOT_STORE_LOG_DATA', 0));
			}
			else
			{
				$logId = $logTable->csvi_log_id;
				$logTable->reset();
			}
		}
		else
		{
			$logTable->load($logId);

			if (array_key_exists('action', $logresult) && isset($logcount[$logresult['action']]))
			{
				$logTable->records += $logcount[$logresult['action']];
			}
			else
			{
				$logTable->records = 0;
			}

			$logTable->store();
		}

		// Store the log details
		if (is_array($logresult) && 0 !== count($logresult))
		{
			$query = $this->db->getQuery(true)
				->insert($this->db->quoteName('#__csvi_logdetails'))
				->columns(
					array(
						$this->db->quoteName('csvi_logdetail_id') . ',' .
						$this->db->quoteName('log_id') . ',' .
						$this->db->quoteName('line') . ',' .
						$this->db->quoteName('description') . ',' .
						$this->db->quoteName('result') . ',' .
						$this->db->quoteName('status'))
					);
			$row = 0;

			foreach ($logresult as $linenr => $result)
			{
				if (is_int($linenr))
				{
					$row++;

					foreach ($result['status'] as $status => $stat)
					{
						$query->values(
								$this->db->quote('0') . ', ' .
								$logId . ',' .
								$linenr . ',' .
								$this->db->quote(trim($stat['message'])) . ',' .
								$this->db->quote($stat['result']) . ',' .
								$this->db->quote($status)
						);
					}

					// Loop in increments of 100
					if ($row === 100)
					{
						$this->db->setQuery($query);
						$this->db->execute();
						$query->clear('values');
						$row = 0;
					}
				}
			}

			// Execute the final query
			if ($row > 0)
			{
				$this->db->setQuery($query);
				$this->db->execute();
			}

			// Clean up the statistics
			$this->log->cleanStats();
		}
	}

	/**
	 * Delete 1 or more selected log entries
	 *
	 * @return  array Array with the results of the deletion
	 *
	 * @since   3.0
	 *
	 * @throws  RuntimeException
	 */
	public function delete()
	{
		jimport('joomla.filesystem.file');
		$cids                 = $this->input->get('cid', array(), 'array');
		$file_not_found       = 0;
		$file_deleted         = 0;
		$file_not_deleted     = 0;
		$log_del              = 0;
		$log_del_error        = 0;
		$log_detail_del       = 0;
		$log_detail_del_error = 0;

		// Make it an array
		if (!is_array($cids))
		{
			$cids = array((int) $cids);
		}

		foreach ($cids as $csvi_log_id)
		{
			$filename = CSVIPATH_DEBUG . '/com_csvi.log.' . $csvi_log_id . '.php';

			if (file_exists($filename))
			{
				if (JFile::delete($filename))
				{
					$file_deleted++;
				}
				else
				{
					$file_not_deleted++;
				}
			}
			else
			{
				$file_not_found++;
			}

			// Delete the log entry
			$query = $this->db->getQuery(true)
				->delete($this->db->quoteName('#__csvi_logs'))
				->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $csvi_log_id);
			$this->db->setQuery($query);

			if (!$this->db->execute())
			{
				$log_del_error++;
			}
			else
			{
				$log_del++;
			}

			// Delete the log details
			$query = $this->db->getQuery(true)
				->delete($this->db->quoteName('#__csvi_logdetails'))
				->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $csvi_log_id);
			$this->db->setQuery($query);

			if (!$this->db->execute())
			{
				$log_detail_del_error++;
			}
			else
			{
				$log_detail_del++;
			}
		}

		// Set the results
		$results = array();

		if ($file_not_found > 0)
		{
			$results['ok'][] = JText::plural('COM_CSVI_DELETE_LOGS_FILE_NOT_FOUND', $file_not_found);
		}

		if ($file_deleted > 0)
		{
			$results['ok'][] = JText::plural('COM_CSVI_DELETE_LOGS_FILE', $file_deleted);
		}

		if ($file_not_deleted > 0)
		{
			$results['nok'][] = JText::plural('COM_CSVI_CANNOT_DELETE_LOGS_FILE', $file_not_deleted);
		}

		if ($log_del > 0)
		{
			$results['ok'][] = JText::plural('COM_CSVI_DELETE_LOGS_DATA', $log_del);
		}

		if ($log_del_error > 0)
		{
			$results['nok'][] = JText::plural('COM_CSVI_CANNOT_DELETE_LOGS_DATA', $log_del_error);
		}

		if ($log_detail_del > 0)
		{
			$results['ok'][] = JText::plural('COM_CSVI_DELETE_LOGS_DETAILS_DATA', $log_detail_del);
		}

		if ($log_detail_del_error > 0)
		{
			$results['nok'][] = JText::plural('COM_CSVI_CANNOT_DELETE_LOGS_DETAILS_DATA', $log_detail_del_error);
		}

		return $results;
	}

	/**
	 * Delete all log entries
	 *
	 * @return  array Array of results
	 *
	 * @since   3.0
	 *
	 * @throws  RuntimeException
	 */
	public function deleteAll()
	{
		$results = array();

		// Empty the log table
		$q = 'TRUNCATE ' . $this->db->quoteName('#__csvi_logs');
		$this->db->setQuery($q);

		if ($this->db->execute())
		{
			// Optimize the table
			$q = 'OPTIMIZE TABLE ' . $this->db->quoteName('#__csvi_logs');
			$this->db->setQuery($q)->execute();

			$results['ok'][] = JText::_('COM_CSVI_DELETE_LOG_DATA_ALL_OK');
		}
		else
		{
			$results['nok'][] = JText::_('COM_CSVI_DELETE_LOG_DATA_ALL_NOK');
		}

		// Empty the log details table
		$q = 'TRUNCATE ' . $this->db->quoteName('#__csvi_logdetails');
		$this->db->setQuery($q);

		if ($this->db->execute())
		{
			// Optimize the table
			$q = 'OPTIMIZE TABLE ' . $this->db->quoteName('#__csvi_logdetails');
			$this->db->setQuery($q)->execute();

			$results['ok'][] = JText::_('COM_CSVI_DELETE_LOG_DATA_DETAILS_ALL_OK');
		}
		else
		{
			$results['nok'][] = JText::_('COM_CSVI_DELETE_LOG_DATA_DETAILS_ALL_NOK');
		}

		return $results;
	}

	/**
	 * Load the statistics.
	 *
	 * @return  array  Array of objects with statistics information.
	 *
	 * @since   6.0
	 *
	 * @throws  RuntimeException
	 */
	public function getStatsMessage()
	{
		$run_id = $this->input->get('run_id', false, 'int');

		if (!$run_id)
		{
			/* Try to get it from the cid */
			$cids = $this->input->get('cid', array(), 'array');

			if (is_array($cids) && array_key_exists('0', $cids))
			{
				$run_id = $cids[0];
			}
			else
			{
				return false;
			}
		}

		$details = array();

		if ($run_id)
		{
			$query = $this->db->getQuery(true)
				->select(
						$this->db->quoteName('line') . ',' .
						$this->db->quoteName('description') . ',' .
						$this->db->quoteName('status') . ',' .
						$this->db->quoteName('log_id') . ',' .
						$this->db->quoteName('result')
					)
				->from($this->db->quoteName('#__csvi_logdetails', 'd'))
				->innerJoin($this->db->quoteName('#__csvi_logs', 'l') . ' ON ' . $this->db->quoteName('d.log_id') . ' = ' . $this->db->quoteName('l.csvi_log_id'))
				->where($this->db->quoteName('l.run_id') . ' = ' . $this->db->quote($run_id))
				->order($this->db->quoteName('line'));
			$this->db->setQuery($query);
			$details = $this->db->loadObjectList();
		}

		return $details;
	}

	/**
	 * Download a debug report.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 */
	public function downloadDebug()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.archive');
		$run_id = $this->input->get('run_id', 0, 'int');
		$filepath = CSVIPATH_DEBUG . '/';
		$filename = 'com_csvi.log.' . $run_id . '.';
		$filesize = filesize($filepath . $filename . 'php');

		// Check for the size of the logfile
		if ($filesize < 512000)
		{
			$zip = JArchive::getAdapter('zip');
			$files = array();
			$files[] = array(
					'name' => $filename . 'php',
					'time' => filemtime($filepath . $filename . 'php'),
					'data' => file_get_contents($filepath . $filename . 'php')
			);

			$zip->create($filepath . $filename . 'zip', $files);
			$outputext = 'zip';
		}
		else
		{
			copy($filepath . $filename . 'php', $filepath . $filename . 'txt');
			$outputext = 'txt';
		}

		if (preg_match('/Opera[\s|\/]([^\s]+)/i', $_SERVER['HTTP_USER_AGENT']))
		{
			$UserBrowser = "Opera";
		}
		elseif (preg_match('/MSIE\s([^\s|;]+)/i', $_SERVER['HTTP_USER_AGENT']))
		{
			$UserBrowser = "IE";
		}
		else
		{
			$UserBrowser = '';
		}

		$mime_type = ($UserBrowser === 'IE' || $UserBrowser === 'Opera') ? 'application/octetstream' : 'application/octet-stream';

		// Clean the buffer
		while (@ob_end_clean())
		{
		}

		header('Content-Type: ' . $mime_type);
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');

		// Filesize gets corrupted because of server compression
		if ($outputext === 'txt')
		{
			header('Content-Length: ' . $filesize);
		}

		if ($UserBrowser === 'IE')
		{
			header('Content-Disposition: inline; filename="' . $filename . $outputext . '"');
			header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		}
		else
		{
			header('Content-Disposition: attachment; filename="' . $filename . $outputext . '"');
			header('Pragma: no-cache');
		}

		// Send the file
		readfile($filepath . $filename . $outputext);
		JFile::delete($filepath . $filename . $outputext);

		// Flush the buffer
		flush();

		// Close the transmission
		JFactory::getApplication()->close();
	}

	/**
	 * Reads a log file and displays its results.
	 *
	 * @return  array  List of log lines.
	 *
	 * @since   2.3.11
	 */
	public function getLogfile()
	{
		$run_id = $this->input->getInt('run_id', 0);
		$log = array();

		if ($run_id > 0)
		{
			$logfile = CSVIPATH_DEBUG . '/com_csvi.log.' . $run_id . '.php';

			if (file_exists($logfile))
			{
				$loglines = file($logfile);

				foreach ($loglines as $key => $line)
				{
					switch ($key)
					{
						case '0':
							// This is an empty line
						case '1':
							// This is the protection line
							break;
						case '2':
							// Get the date
							if (strstr($line, ':'))
							{
								list($text, $value) = explode(': ', $line);
							}
							else
							{
								$value = '';
							}

							$log['date'] = $value;
							break;
						case '3':
							// Get the Joomla version
							if (strstr($line, ':'))
							{
								list($text, $value) = explode(': ', $line);
							}
							else
							{
								$value = '';
							}

							$log['joomla'] = $value;
							break;
						case '4':
							// This is an empty line
							break;
						case '5':
							// Get the fields
							if (strstr($line, ':'))
							{
								list($text, $value) = explode(': ', $line);
								$fields = preg_split("/\t/", $value);

								foreach ($fields as $field)
								{
									$log['fields'][] = $field;
								}
							}
							else
							{
								$log['fields'] = array();
							}
							break;
						default:
							// The actual log lines
							$log['entries'][] = preg_split("/\t/", $line);
							break;
					}
				}
			}
		}

		return $log;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   6.6.0
	 */
	protected function populateState($ordering = 'l.start', $direction = 'DESC')
	{
		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   12.2
	 */
	protected function getStoreId($id = '')
	{
		// Add the list state to the store id.
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.actiontypes');

		return md5($this->context . ':' . $id);
	}
}
