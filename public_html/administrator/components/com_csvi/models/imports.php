<?php
/**
 * @package     CSVI
 * @subpackage  Import
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Import file Model.
 *
 * @package     CSVI
 * @subpackage  Import
 * @since       6.0
 */
class CsviModelImports extends CsviModelDefault
{
	/**
	 * Initialise the needed classes, it all starts with the template ID
	 *
	 * @param   int  $template_id  The ID of the template to load
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function initialise($template_id)
	{
		// Check the temporary folder
		$this->checkTmpFolder();

		// Load the language files
		$this->loadLanguageFiles();

		// Load the template
		$this->loadTemplate($template_id);
	}

	/**
	 * Set the log basics.
	 *
	 * @return  bool  Always returns true.
	 *
	 * @since   6.0
	 */
	public function initialiseLog()
	{
		$this->log->setAction('import');

		return parent::initialiseLog();
	}

	/**
	 * Set the log basics.
	 *
	 * @param   int  $runId  The ID of the import run
	 *
	 * @return  bool  Always returns true.
	 *
	 * @since   6.0
	 */
	public function initialiseImport($runId)
	{
		parent::initialiseImport($runId);

		$this->initialiseLog();
	}

	/**
	 * Returns a list of items
	 *
	 * @param   boolean  $overrideLimits  Should I override set limits?
	 * @param   string   $group           The group by clause
	 *
	 * @return  array  of items
	 *
	 * @since   6.0
	 */
	public function &getItemList($overrideLimits = false, $group = '')
	{
		$this->list = array();

		return $this->list;
	}

	/**
	 * Get the number of all items.
	 *
	 * This is always 0 as we don't have a traditional list.
	 *
	 * @return  integer  The number of records.
	 *
	 * @since   6.0
	 */
	public function getTotal()
	{
		$this->total = 0;

		return $this->total;
	}

	/**
	 * Set the end timestamp.
	 *
	 * @param   int  $csvi_process_id  The ID of the import process
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function setEndTimestamp($csvi_process_id)
	{
		if ($csvi_process_id > 0)
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('csvi_log_id'))
				->from($this->db->quoteName('#__csvi_processes'))
				->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $csvi_process_id);
			$this->db->setQuery($query);
			$csvi_log_id = $this->db->loadResult();

			$date = new JDate(date('Y-m-d H:i:s', time()));
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__csvi_logs'))
				->set($this->db->quoteName('end') . ' = ' . $this->db->quote($date->toSql()))
				->set($this->db->quoteName('run_cancelled') . ' = 1')
				->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $csvi_log_id);
			$this->db->setQuery($query)->execute();

			$query = $this->db->getQuery(true)
				->delete($this->db->quoteName('#__csvi_processes'))
				->where($this->db->quoteName('csvi_process_id') . ' = ' . (int) $csvi_process_id);
			$this->db->setQuery($query)->execute();
		}
	}
}
