<?php
/**
 * @package     CSVI
 * @subpackage  Table
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * CSVI default table.
 *
 * @package     CSVI
 * @subpackage  Table
 * @since       6.0
 */
class CsviTableDefault extends JTable
{
	/**
	 * Template helper
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	protected $template = null;

	/**
	 * Logger helper
	 *
	 * @var    CsviHelperLog
	 * @since  6.0
	 */
	protected $log = null;

	/**
	 * Database connector
	 *
	 * @var    JDatabaseDriver
	 * @since  6.0
	 */
	protected $db = null;

	/**
	 * The addon helper
	 *
	 * @var    object
	 * @since  6.0
	 */
	protected $helper = null;

	/**
	 * The addon config helper
	 *
	 * @var    object
	 * @since  6.0
	 */
	protected $helperconfig = null;

	/**
	 * Table constructor.
	 *
	 * @param   string     $table   Name of the database table to model.
	 * @param   string     $key     Name of the primary key field in the table.
	 * @param   JDatabase  &$db     Database driver
	 * @param   array      $config  The configuration parameters array
	 *
	 * @since   4.0
	 */
	public function __construct($table, $key, &$db, $config = array())
	{
		$this->db = $db;

		if (isset($config['template']))
		{
			$this->template = $config['template'];
		}

		if (isset($config['log']))
		{
			$this->log = $config['log'];
		}

		if (isset($config['helper']))
		{
			$this->helper = $config['helper'];
		}

		if (isset($config['helperconfig']))
		{
			$this->helperconfig = $config['helperconfig'];
		}

		parent::__construct($table, $key, $db);
	}

	/**
	 * Set an alternative primary key field to use.
	 *
	 * @param   string  $value  The name of the field to use
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function setKeyName($value)
	{
		$this->_tbl_keys = array($value);
	}

	/**
	 * Override of the load method to log queries.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 * set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found or on error (internal error state set in that case).
	 *
	 * @since   6.0
	 */
	public function load($keys = null, $reset = true)
	{
		try
		{
			if (!is_null($this->log))
			{
				$this->log->add('Load a row', false);
			}

			$result = parent::load($keys, $reset);

			return $result;
		}
		catch (Exception $e)
		{
			if (!is_null($this->log))
			{
				$this->log->add('Row cannot be loaded. Error: ' . $e->getMessage(), false);
			}

			return false;
		}
	}

	/**
	 * Override of the store method to log queries.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   6.0
	 */
	public function store($updateNulls = false)
	{
		$area = $this->getArea();

		if (!is_null($this->log))
		{
			$this->log->add('Query for ' . $area);
		}

		$result = parent::store($updateNulls);

		if (!is_null($this->log))
		{
			$this->log->add('Executed store', true);
		}

		if (!$result)
		{
			$this->log->addStats('incorrect', JText::sprintf('COM_CSVI_NOT_ADDED', $this->getError()), $area);
		}
		else
		{
			if ($this->queryResult() == 'UPDATE')
			{
				$this->log->addStats('updated', 'COM_CSVI_TABLE_' . get_called_class() . '_UPDATED', $area);
			}
			elseif ($this->queryResult() == 'INSERT')
			{
				$this->log->addStats('added', 'COM_CSVI_TABLE_' . get_called_class() . '_ADDED', $area);
			}
			else
			{
				$this->log->addStats('processed', 'COM_CSVI_TABLE_' . get_called_class() . '_PROCESSED', $area);
			}
		}

		return $result;
	}

	/**
	 * Method to delete a row from the database table by primary key value.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link	http://docs.joomla.org/JTable/delete
	 * @since   11.1
	 */
	public function delete($pk = null)
	{
		$result = parent::delete($pk);

		$area = $this->getArea();

		if (!$result)
		{
			$this->log->add('Row not deleted. Error: ' . $this->getError());
		}
		else
		{
			$this->log->add('Delete row query');
			$this->log->addStats('deleted', JText::_('COM_CSVI_TABLE_' . get_called_class() . '_DELETED'), $area);
		}

		return $result;
	}

	/**
	 * Return the type of query INSERT / UPDATE.
	 *
	 * @return  string  The name of the type of query performed.
	 *
	 * @since   3.0
	 */
	protected function queryResult()
	{
		return trim(substr($this->db->getQuery(), 0, strpos($this->db->getQuery(), ' ')));
	}

	/**
	 * Get the area the query is for.
	 *
	 * @return  string  The name of the area.
	 *
	 * @since   6.0
	 */
	protected function getArea()
	{
		// Get the area
		$className = get_called_class();

		return substr($className, (strpos($className, 'Table') + 5));
	}
}
