<?php
/**
 * @package     CSVI
 * @subpackage  Logdetails
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Log details model.
 *
 * @package     CSVI
 * @subpackage  Logdetails
 * @since       6.0
 */
class CsviModelLogdetails extends JModelList
{
	/**
	 * The database class
	 *
	 * @var    JDatabaseDriver
	 * @since  6.0
	 */
	protected $db;

	/**
	 * Construct the class.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   6.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.action', 'action',
				'a.result', 'result',
			);
		}

		// Load the basics
		$this->db = JFactory::getDbo();

		parent::__construct($config);
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
	protected function populateState($ordering = 'a.csvi_logdetail_id', $direction = 'ASC')
	{
		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery  The query to execute.
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 */
	protected function getListQuery()
	{
		// Get the Run ID
		$run_id = JFactory::getApplication()->input->getInt('run_id', 0);

		// Get the parent query
		$query = $this->db->getQuery(true)
			->from($this->db->quoteName('#__csvi_logdetails', 'a'))
			->leftJoin(
				$this->db->quoteName('#__csvi_logs', 'l')
				. ' ON ' . $this->db->quoteName('l.csvi_log_id') . ' = ' . $this->db->quoteName('a.csvi_log_id')
			)
			->select(
				$this->db->quoteName(
					array(
						'a.csvi_logdetail_id',
						'a.csvi_log_id',
						'a.line',
						'a.description',
						'a.result',
						'a.status',
						'a.area',
					)
				)
			)
			->where($this->db->quoteName('l.csvi_log_id') . ' = ' . (int) $run_id);

		// Filter by search
		$search = $this->getState('filter.search');

		if ($search)
		{
			$query->where($this->db->quoteName('a.description') . ' LIKE ' . $this->db->quote('%' . $search . '%'));
		}

		// Filter by action
		$action = $this->getState('filter.action');

		if ($action)
		{
			$query->where($this->db->quoteName('status') . ' = ' . $this->db->quote($action));
		}

		// Filter by action
		$result = $this->getState('filter.result');

		if ($result)
		{
			$query->where($this->db->quoteName('result') . ' = ' . $this->db->quote($result));
		}

		return $query;
	}

	/**
	 * Load the statistics for displaying.
	 *
	 * @param   int     $runId  The log ID.
	 *
	 * @return  object  Object of result
	 *
	 * @since   3.0
	 *
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 */
	public function getStats($runId)
	{
		$details  = new stdClass;
		$language = new CsviHelperLanguage;

		if ($runId)
		{
			jimport('joomla.filesystem.file');

			// Add the run ID
			$details->run_id = $runId;

			// Get the total number of records
			$query = $this->db->getQuery(true)
				->select(
					array(
						$this->db->quoteName('start'),
						$this->db->quoteName('end'),
						$this->db->quoteName('addon'),
						$this->db->quoteName('action'),
						$this->db->quoteName('action_type'),
						$this->db->quoteName('template_name'),
						$this->db->quoteName('records'),
						$this->db->quoteName('file_name'),
						$this->db->quoteName('run_cancelled')
					)
				)
				->from($this->db->quoteName('#__csvi_logs'))
				->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $runId);
			$this->db->setQuery($query);
			$details = $this->db->loadObject();

			// Load the addon language
			$language->loadAddonLanguage($details->addon);

			// Get the status area results
			$query->clear()
				->select('COUNT(' . $this->db->quoteName('status') . ') AS ' . $this->db->quoteName('total'))
				->select($this->db->quoteName('status'))
				->select($this->db->quoteName('area'))
				->from($this->db->quoteName('#__csvi_logdetails'))
				->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $runId)
				->group($this->db->quoteName(array('status', 'area')))
				->order($this->db->quoteName('area'));
			$this->db->setQuery($query);
			$details->resultstats = $this->db->loadObjectList();

			// Get some status results
			$query->clear()
				->select('COUNT(' . $this->db->quoteName('status') . ') AS ' . $this->db->quoteName('total'))
				->select($this->db->quoteName('status'))
				->select($this->db->quoteName('result'))
				->from($this->db->quoteName('#__csvi_logdetails'))
				->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $runId)
				->order($this->db->quoteName('csvi_logdetail_id'));
			$this->db->setQuery($query);
			$results = $this->db->loadObjectList('status');
			$details->result = array();

			foreach ($results as $status => $result)
			{
				if ($status)
				{
					$details->result[$status] = $result;
				}
			}

			// Check if there is a debug log file
			$logfile = CSVIPATH_DEBUG . '/com_csvi.log.' . $runId . '.php';

			if (JFile::exists($logfile))
			{
				$attribs = 'class="modal" onclick="" rel="{handler: \'iframe\', size: {x: 950, y: 500}}"';
				$details->debug = JHtml::_(
					'link',
					JRoute::_('index.php?option=com_csvi&view=logs&layout=logreader&tmpl=component&run_id=' . $runId),
					JText::_('COM_CSVI_SHOW_LOG'),
					$attribs
				);
				$details->debug .= ' | ';
				$details->debug .= JHtml::_(
					'link',
					JRoute::_('index.php?option=com_csvi&view=logs&layout=logreader&tmpl=component&run_id=' . $runId),
					JText::_('COM_CSVI_OPEN_LOG'),
					'target="_new"'
				);
				$details->debug .= ' | ';
				$details->debug .= JHtml::_(
					'link',
					JRoute::_('index.php?option=com_csvi&task=logs.downloadDebug&run_id=' . $runId),
					JText::_('COM_CSVI_DOWNLOAD_LOG')
				);
			}
			else
			{
				$details->debug = JText::_('COM_CSVI_NO_DEBUG_LOG_FOUND');
			}
		}

		return $details;
	}
}
