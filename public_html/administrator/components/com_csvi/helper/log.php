<?php
/**
 * @package     CSVI
 * @subpackage  Helper
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Helper class for logging.
 *
 * @package     CSVI
 * @subpackage  Helper
 * @since       6.0
 */
class CsviHelperLog
{
	/**
	 * Contains the current line number
	 *
	 * @var    int
	 * @since  6.0
	 */
	private $linenumber = 0;

	/**
	 * Contains the database log ID
	 *
	 * @var    int
	 * @since  6.0
	 */
	private $logid = 0;

	/**
	 * The name of the logfile
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $logfile = '';

	/**
	 * The path to where the logfile is stored.
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $logpath = '';

	/**
	 * The name of the file being imported/exported
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $filename = '';

	/**
	 * The status if debug info is to be collected
	 *
	 * @var    bool
	 * @since  6.0
	 */
	private $active = false;

	/**
	 * The log messages
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $logmessage = '';

	/**
	 * The log statistics
	 *
	 * @var    array
	 * @since  6.0
	 */
	private $stats = array();

	/**
	 * The maximum number of logs to keep
	 *
	 * @var    int
	 * @since  6.0
	 */
	private $log_max = 25;

	/**
	 * The database helper
	 *
	 * @var    JDatabaseDriver
	 * @since  6.0
	 */
	private $db = null;

	/**
	 * Addon used
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $addon = null;

	/**
	 * Action performed
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $action = null;

	/**
	 * Action type executed
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $actionType = null;

	/**
	 * Template name
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $templateName = null;

	/**
	 * Constructor
	 *
	 * @param   CsviHelperSettings  $settings  An instance of CsviHelperSettings
	 * @param   JDatabaseDriver     $db        Joomla database connector
	 *
	 * @since   3.4
	 */
	public function __construct(CsviHelperSettings $settings, JDatabaseDriver $db)
	{
		// Initialise the settings
		$this->log_max = $settings->get('log_max', 25);

		$this->db = $db;
	}

	/**
	 * Get the active status of the logger.
	 *
	 * @return  bool  True if logging is turned on | False if logging is turned off.
	 *
	 * @since   6.6.3
	 */
	public function isActive()
	{
		return $this->active;
	}

	/**
	 * Enable the logger
	 *
	 * @param   bool  $value  Set the logging on or off
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function setActive($value)
	{
		$this->active = $value;
	}

	/**
	 * Set the addon the log is for.
	 *
	 * @param   string  $addon  The name of the addon
	 *
	 * @return  void
	 *
	 * @since   6.0
	 */
	public function setAddon($addon)
	{
		$this->addon = strtolower($addon);
	}

	/**
	 * Set the action the log is for.
	 *
	 * @param   string  $action  The type of action being taken
	 *
	 * @return  void
	 *
	 * @since   6.0
	 */
	public function setAction($action)
	{
		$this->action = strtolower($action);
	}

	/**
	 * Set the type of action the log is for.
	 *
	 * @param   string  $action  The action type being performed
	 *
	 * @return  void
	 *
	 * @since   6.0
	 */
	public function setActionType($action)
	{
		$this->actionType = strtolower($action);
	}

	/**
	 * Set the name of the template.
	 *
	 * @param   string  $template_name  The name of the chosen template
	 *
	 * @return  void
	 *
	 * @since   6.0
	 */
	public function setTemplateName($template_name)
	{
		$this->templateName = $template_name;
	}

	/**
	 * Initialise the log.
	 *
	 * @return  void
	 *
	 * @since   6.0
	 */
	public function initialise()
	{
		// Make sure the table can be found
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_csvi/tables');

		// Store the log entry
		$table = JTable::getInstance('Log', 'Table');
		$data = array(
			'csvi_log_id'   => $this->logid,
			'userid'        => JFactory::getUser()->get('id', 0),
			'start'         => JFactory::getDate(time())->toSql(),
			'addon'         => $this->addon,
			'action'        => $this->action,
			'action_type'   => $this->actionType,
			'template_name' => $this->templateName
		);

		$table->save($data);

		// Get the log ID
		$this->logid = $table->get('csvi_log_id');

		// Load the current settings
		$table->load();

		// Get the line number
		$this->setLinenumber($table->records);

		// Clean out any old logs
		$this->cleanUpLogs();
	}

	/**
	 * Clean up old log entries
	 *
	 * @return 		void
	 *
	 * @since 		3.0
	 */
	private function cleanUpLogs()
	{
		// Load the settings
		$jinput = JFactory::getApplication()->input;

		// Check if there are any logs to remove
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('csvi_log_id'))
			->from($this->db->quoteName('#__csvi_logs'))
			->order($this->db->quoteName('csvi_log_id'));
		$this->db->setQuery($query);
		$dblogs = $this->db->loadColumn();
		$this->add(sprintf('Clean up old logs. Found %s logs and threshold is %s logs', count($dblogs), $this->log_max), false);

		if (count($dblogs) > $this->log_max)
		{
			$jinput->set('cid', array_slice($dblogs, 0, (count($dblogs) - $this->log_max)));

			// Load the log model
			require_once JPATH_ADMINISTRATOR . '/components/com_csvi/models/logs.php';
			$log_model = new CsviModelLogs;
			$log_model->delete();
		}
	}

	/**
	 * Invoke the Joomla logger
	 *
	 * @param   string  $comment  The comment to log
	 * @param   int     $linenr   The linenumber concerned
	 * @param   string  $action   The type of action
	 *
	 * @return 	 void
	 *
	 * @since 	 3.0
	 */
	private function simpleLog($comment, $linenr, $action)
	{
		// Include the library dependancies
		jimport('joomla.log.log');

		// Set the logfile
		$this->getLogName();

		// Create the instance of the log file in case we use it later
		$options = array(
				'text_entry_format' => "{DATE}\t{TIME}\t{LINE_NR}\t{ACTION}\t{COMMENT}",
				'text_file' => $this->logfile,
				'text_file_path' => $this->logpath
		);
		JLog::addLogger($options, 30719, 'csvidebug');

		$entry = new JLogEntry($comment);
		$entry->comment = $comment;
		$entry->line_nr = $linenr;
		$entry->action = $action;
		$entry->category = 'csvidebug';

		JLog::add($entry);
	}

	/**
	 * Return the name of the logfile
	 *
	 * @return 		string	The name of the logfile
	 *
	 * @since 		3.0
	 */
	private function getLogName()
	{
		$this->logfile = 'com_csvi.log.' . $this->getLogId() . '.php';
		$this->logpath = CSVIPATH_DEBUG;

		return $this->logpath . '/' . $this->logfile;
	}

	/**
	 * Set the current line number
	 *
	 * @param   int  $linenumber  The current linenumber
	 *
	 * @return  bool  true
	 *
	 * @since   3.0
	 */
	public function setLinenumber($linenumber)
	{
		$this->linenumber = $linenumber;

		return true;
	}

	/**
	 * Get the current line number
	 *
	 * @return  int  The linenumber
	 *
	 * @since   6.0
	 */
	public function getLinenumber()
	{
		return $this->linenumber;
	}

	/**
	 * Increment the line number
	 *
	 * @return  bool  true
	 *
	 * @since   6.0
	 */
	public function incrementLinenumber()
	{
		$this->linenumber++;

		return true;
	}

	/**
	 * Decrement the line number
	 *
	 * @return  bool  true
	 *
	 * @since   6.0
	 */
	public function decrementLinenumber()
	{
		$this->linenumber--;

		return true;
	}

	/**
	 * Set the import/export ID
	 *
	 * @param   int  $csvi_log_id  The ID to set
	 *
	 * @return  int  the import/export ID
	 *
	 * @since   3.0
	 */
	public function setLogId($csvi_log_id)
	{
		if ($csvi_log_id)
		{
			// Set the log ID
			$this->logid = $csvi_log_id;

			// Load the last linenumber
			$query = $this->db->getQuery(true)
				->select(array($this->db->quoteName('records')))
				->from($this->db->quoteName('#__csvi_logs'))
				->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $csvi_log_id);
			$this->db->setQuery($query);
			$records = $this->db->loadResult();

			$this->setLinenumber($records);
		}
	}

	/**
	 * Get the database ID.
	 *
	 * @return  int  The database ID.
	 *
	 * @since   3.0
	 */
	public function getLogId()
	{
		if (empty($this->logid))
		{
			$this->initialise();
		}

		return $this->logid;
	}

	/**
	 * Set the filename of the file used for import/export
	 *
	 * @param   string  $filename  the full path and filename of the import/export file
	 *
	 * @return  void
	 *
	 * @since   6.0
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;

		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__csvi_logs'))
			->set($this->db->quoteName('file_name') . ' = ' . $this->db->quote($filename))
			->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $this->logid);
		$this->db->setQuery($query)->execute();
	}

	/**
	 * Get the import filename
	 *
	 * @return  string  the full path and filename of the logfile
	 *
	 * @since   6.0
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * Adds a message to the log file.
	 *
	 * @param   string  $message  Message to add to the debug log.
	 * @param   bool    $sql      If true adds the sql statement.
	 * @param   string  $action   The kind of action to qualify the message for.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function add($message, $sql=true, $action='DEBUG')
	{
		// Check if we should add the log line
		if ($this->active)
		{
			// Store the message in the log file
			$this->simpleLog($message, $this->linenumber, '[' . strtoupper($action) . ']');

			if ($sql)
			{
				if (strpos($message, 'SQL='))
				{
					$qmsg = $message;
					$qaction = 'SQL ERROR';
				}
				else
				{
					$qmsg = str_replace('#__', $this->db->getPrefix(), $this->db->getQuery());
					$qaction = 'QUERY';
				}

				$qmsg = str_replace(array("\r\n", "\n", "\r", "\t"), ' ', $qmsg);
				$this->simpleLog($qmsg, $this->linenumber, '[' . $qaction . ']');
			}
		}
	}

	/**
	 * Adds a message to the statistics stack.
	 *
	 * <p>
	 * Types:
	 * --> Products
	 * updated
	 * deleted
	 * added
	 * skipped
	 * incorrect
	 * --> DB tables
	 * empty
	 * --> Fields
	 * nosupport
	 * --> No files found multiple images
	 * nofiles
	 * --> General information
	 * information
	 * </p>
	 *
	 * @param   string  $action   Type of message.
	 * @param   string  $message  Message to add to the stack.
	 * @param   string  $area     The area the message concerns.
	 *
	 * @return  void
	 *
	 * @since   6.0
	 *
	 */
	public function addStats($action, $message, $area = null)
	{
		if ($this->active)
		{
			// Check the area
			if (is_null($area))
			{
				$trace = debug_backtrace();
				$caller = $trace[1];

				if (isset($caller['class']))
				{
					// Get only the last part of the namespace
					$space = explode('\\', $caller['class']);
					$area = end($space);
				}
			}

			// Set the result
			$success = array('updated', 'deleted', 'added', 'empty', 'processed');
			$failure = array('incorrect', 'nosupport');
			$notice  = array('information', 'nofiles', 'skipped');

			if (in_array($action, $success))
			{
				$result = JText::_('COM_CSVI_STATUS_SUCCESS');
			}
			elseif (in_array($action, $failure))
			{
				$result = JText::_('COM_CSVI_STATUS_FAILURE');
			}
			elseif (in_array($action, $notice))
			{
				$result = JText::_('COM_CSVI_STATUS_NOTICE');
			}
			else
			{
				$result = '';
			}

			// Store the message in the database
			$query = $this->db->getQuery(true)
				->insert($this->db->quoteName('#__csvi_logdetails'))
				->columns(
					array(
						$this->db->quoteName('csvi_log_id'),
						$this->db->quoteName('line'),
						$this->db->quoteName('description'),
						$this->db->quoteName('result'),
						$this->db->quoteName('status'),
						$this->db->quoteName('area')
					)
				)
				->values(
					(int) $this->logid . ',' .
					(int) $this->linenumber . ',' .
					$this->db->quote(JText::_($message)) . ',' .
					$this->db->quote($result) . ',' .
					$this->db->quote(JText::_('COM_CSVI_STATUS_' . $action)) . ',' .
					$this->db->quote(JText::_('COM_CSVI_AREA_' . $area))
				);

			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Retrieves the log message.
	 *
	 * @return  string  The log message
	 *
	 * @since   6.0
	 */
	public function getLogMessage()
	{
		return $this->logmessage;
	}

	/**
	 * Retrieves the statistics array.
	 *
	 * @return  array  Array with statistic details
	 *
	 * @since   6.0
	 */
	public function getStats()
	{
		return $this->stats;
	}

	/**
	 * Clean the statistics array.
	 *
	 * @return  void
	 *
	 * @since   6.0
	 */
	public function cleanStats()
	{
		$runstats['addon'] = $this->stats['addon'];
		$runstats['action'] = $this->stats['action'];
		$runstats['action_type'] = $this->stats['action_type'];
		$runstats['action_template'] = $this->stats['action_template'];
		$this->stats = array();
		$this->stats = $runstats;
	}
}
