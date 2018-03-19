<?php
/**
 * @package     CSVI
 * @subpackage  Imports
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Import model class.
 *
 * @package     CSVI
 * @subpackage  Imports
 * @since       6.0
 */
abstract class RantaiImportEngine
{
	/**
	 * A state object
	 *
	 * @var    object
	 * @since  6.0
	 */
	protected $state;

	/**
	 * JDatabase handler
	 *
	 * @var    JDatabaseDriver
	 * @since  6.0
	 */
	protected $db = null;

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
	 * CSVI helper
	 *
	 * @var    CsviHelperCsvi
	 * @since  6.0
	 */
	protected $csvihelper = null;

	/**
	 * CSVI fields
	 *
	 * @var    CsviHelperFields
	 * @since  6.0
	 */
	protected $fields = null;

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
	 * The Joomla date class
	 *
	 * @var    JDate
	 * @since  6.0
	 */
	protected $date = null;

	/**
	 * The Joomla user ID
	 *
	 * @var    int
	 * @since  6.0
	 */
	protected $userId = 0;

	/**
	 * Set if the product is loaded
	 *
	 * @var    bool
	 * @since  6.0
	 */
	protected  $loaded = true;

	/**
	 * Record identifier
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $recordIdentity = null;

	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver     $db            An instance of JDatabaseDriver.
	 * @param   CsviHelperTemplate  $template      An instance of CsviHelperTemplate.
	 * @param   CsviHelperLog       $log           An instance of CsviHelperLog.
	 * @param   CsviHelperCsvi      $csvihelper    An instance of CsviHelperCsvi.
	 * @param   CsviHelperFields    $fields        An instance of CsviHelperFields.
	 * @param   object              $helper        The component helper.
	 * @param   object              $helperconfig  The component config helper.
	 * @param   int                 $userId        The ID of the user running the import.
	 *
	 * @since   3.4
	 */
	public function __construct(
		JDatabaseDriver $db,
		CsviHelperTemplate $template,
		CsviHelperLog $log,
		CsviHelperCsvi $csvihelper,
		CsviHelperFields $fields,
		$helper,
		$helperconfig,
		$userId)
	{
		// Set the dependencies
		$this->db = $db;
		$this->template = $template;
		$this->log = $log;
		$this->csvihelper = $csvihelper;
		$this->fields = $fields;
		$this->helper = $helper;
		$this->helperconfig = $helperconfig;

		// Load the date helper
		$this->date = JFactory::getDate();

		// Load the user helper
		$this->userId = $userId;

		// Set the state object
		$this->state = new JObject;

		// Set the Joomla version
		if (file_exists(JPATH_LIBRARIES . '/cms/version/version.php'))
		{
			require_once JPATH_LIBRARIES . '/cms/version/version.php';
		}
		else
		{
			require_once JPATH_LIBRARIES . '/src/Version.php';
		}
	}

	/**
	 * Here starts the processing.
	 *
	 * @return  bool  Returns true on success | False on failure.
	 *
	 * @since   3.0
	 */
	abstract public function getStart();

	/**
	 * Process each record and store it in the database.
	 *
	 * @return  bool  Returns true if all is OK | Returns false if no product SKU or product ID can be found.
	 *
	 * @since   3.0
	 */
	abstract public function getProcessRecord();

	/**
	 * Method to set model state variables
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 *
	 * @since   6.0
	 */
	public function setState($property, $value = null)
	{
		return $this->state->set($property, $value);
	}

	/**
	 * Magic getter; allows to use the name of model state keys as properties
	 *
	 * @param   string  $name  The name of the variable to get
	 *
	 * @return  mixed  The value of the variable
	 *
	 * @since   6.0
	 */
	public function __get($name)
	{
		return $this->getState($name);
	}

	/**
	 * Get a filtered state variable
	 *
	 * @param   string  $key          The name of the state variable
	 * @param   mixed   $default      The default value to use
	 * @param   string  $filter_type  Filter type
	 *
	 * @return  mixed  The variable value
	 *
	 * @since   6.0
	 */
	protected function getState($key = null, $default = null, $filter_type = 'raw')
	{
		if (strlen($key) === 0)
		{
			return null;
		}

		$value = $this->state->get($key, $default);

		if (strtoupper($filter_type) == 'RAW')
		{
			return $value;
		}
		else
		{
			JLoader::import('joomla.filter.filterinput');
			$filter = new JFilterInput;

			return $filter->clean($value, $filter_type);
		}
	}

	/**
	 * Empty the state completely.
	 *
	 * @return  bool  Always returns true.
	 *
	 * @since   6.0
	 */
	public function clearState()
	{
		$this->state = new JObject;

		return true;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @throws  CsviException
	 *
	 * @since   6.0
	 */
	public function getTable($name = '', $prefix = null, $options = array())
	{
		// Check if we have a template
		if ($this->template)
		{
			// Get the component name
			$component = $this->template->get('component');
			$extension = substr($component, 4);

			// Set the prefix if needed
			if (empty($prefix))
			{
				$prefix = ucfirst(str_replace('com_', '', $component)) . 'Table';
			}

			// Set the table path
			$options['tablepath'] = JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component . '/table';

			// Add extras to the options
			$options['template']     = $this->template;
			$options['log']          = $this->log;
			$options['helper']       = $this->helper;
			$options['helperconfig'] = $this->helperconfig;

			// Load the table file
			require_once JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component . '/table/' . strtolower($name) . '.php';

			// Create the table name
			$tblName = $prefix . $name;

			// Instantiate the table
			try
			{
				return new $tblName('', '', $this->db, $options);
			}
			catch (Exception $e)
			{
				throw new CsviException(JText::sprintf('COM_CSVI_NO_TABLE_LOADED', $tblName, $e->getMessage()), 513);
			}
		}
		else
		{
			throw new CsviException(JText::_('COM_CSVI_NO_TEMPLATE_LOADED'), 501);
		}
	}

	/**
	 * Converts a price to correct delimiters for database storage.
	 *
	 * @param   string  $value  The value to modify.
	 *
	 * @return  string  The modified price.
	 *
	 * @since   6.0
	 */
	protected function toPeriod($value)
	{
		$clean = str_replace(",", ".", $value);
		$lastpos = strrpos($clean, '.');

		return str_replace('.', '', substr($clean, 0, $lastpos)) . substr($clean, $lastpos);
	}

	/**
	 * Format a datetime format.
	 *
	 * Format of the date is day/month/year or day-month-year.
	 *
	 * @param   string  $date  The date to convert
	 * @param   string  $type  The type of date/time to return
	 * @param   string  $tz    Which timezone to use, joomla or user
	 *
	 * @return  mixed  A timestamp based on the supplied type.
	 *
	 * @since   6.0
	 */
	protected function convertDate($date, $type='sql', $tz='joomla')
	{
		// Check if we have a null date
		if ($date == '0000-00-00 00:00:00' || $date == '0000-00-00')
		{
			return $date;
		}
		else
		{
			$new_date = preg_replace('/-|\./', '/', $date);
			$date_parts = explode(' ', $new_date);
			$date_part = '';
			$time_part = '';

			if (isset($date_parts[0]))
			{
				$date_part = $date_parts[0];
			}

			if (isset($date_parts[1]))
			{
				$time_part = $date_parts[1];
			}

			$date_parts = explode('/', $date_part);
			$time_parts = explode(':', $time_part);

			switch ($type)
			{
				case 'date':
					// Check if the date starts with a year or not
					if (strlen($date_parts[0]) == 4)
					{
						$old_date = mktime(0, 0, 0, $date_parts[1], $date_parts[2], $date_parts[0]);
					}
					elseif (
						(count($date_parts) == 3) &&
						($date_parts[0] > 0 && $date_parts[0] < 32 && $date_parts[1] > 0 && $date_parts[1] < 13 && (strlen($date_parts[2]) == 4  || strlen($date_parts[2]) == 2))
					)
					{
						// Check if the year is only 2 digits, if so add 20 to it.
						if (strlen($date_parts[2]) == 2)
						{
							$date_parts[2] = '20' . $date_parts[2];
						}

						$old_date = mktime(0, 0, 0, $date_parts[1], $date_parts[0], $date_parts[2]);
					}
					else
					{
						$old_date = 0;
					}
					break;
				case 'time':
					if ((count($time_parts) == 3) && ($time_parts[0] > 0 && $time_parts[0] < 24 && $time_parts[1] > 0 && $time_parts[1] < 61))
					{
						$old_date = mktime($time_parts[0], $time_parts[1], $time_parts[2], $date_parts[1], $date_parts[0], $date_parts[2]);
					}
					else
					{
						$old_date = 0;
					}
					break;
				default:
					// Check if the date starts with a year or not
					if (strlen($date_parts[0]) == 4)
					{
						$old_date = mktime(0, 0, 0, $date_parts[1], $date_parts[2], $date_parts[0]);
					}
					elseif (
						(count($date_parts) == 3) &&
						($date_parts[0] > 0 && $date_parts[0] < 32 && $date_parts[1] > 0 && $date_parts[1] < 13 && (strlen($date_parts[2]) == 4 || strlen($date_parts[2]) == 2))
					)
					{
						// Check if the year is only 2 digits, if so add 20 to it.
						if (strlen($date_parts[2]) == 2)
						{
							$date_parts[2] = '20' . $date_parts[2];
						}

						if ((count($time_parts) == 3) && ($time_parts[0] > 0 && $time_parts[0] < 24 && $time_parts[1] > 0 && $time_parts[1] < 61))
						{
							$old_date = mktime($time_parts[0], $time_parts[1], $time_parts[2], $date_parts[1], $date_parts[0], $date_parts[2]);
						}
						else
						{
							$old_date = mktime(0, 0, 0, $date_parts[1], $date_parts[0], $date_parts[2]);
						}
					}
					else
					{
						$old_date = 0;
					}
					break;
			}

			// Convert the old date
			switch ($tz)
			{
				case 'user':
					switch ($type)
					{
						case 'date':
							$new_date = date('Y-m-d', $old_date);
							break;
						case 'time':
							$new_date = date('H:i:s', $old_date);
							break;
						case 'unix':
							$new_date = $old_date;
							break;
						default:
							$new_date = date('Y-m-d H:i:s', $old_date);
							break;
					}
					break;
				case 'joomla':
					$date = JFactory::getDate($old_date);

					switch ($type)
					{
						case 'date':
							$new_date = $date->format('Y-m-d', false, false);
							break;
						case 'time':
							$new_date = $date->format('H:i:s', false, false);
							break;
						case 'unix':
							if ($old_date)
							{
								$new_date = $date->toUnix();
							}
							else
							{
								$new_date = '0';
							}
							break;
						default:
							if ($old_date)
							{
								$new_date = $date->toSql();
							}
							else
							{
								$new_date = '0000-00-00 00:00:00';
							}
							break;
					}
					break;
			}

			return $new_date;
		}
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
	 * Clean a price so it is only a price.
	 *
	 * @param   string  $price  The price to clean
	 *
	 * @return  float  The cleaned up price.
	 *
	 * @since   6.0
	 */
	protected function cleanPrice($price)
	{
		$filter = new JFilterInput;

		return $filter->clean($this->toPeriod($price), 'float');
	}
}
