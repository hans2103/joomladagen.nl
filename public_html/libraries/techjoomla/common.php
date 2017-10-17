<?php
/**
 * @version     SVN: <svn_id>
 * @package     Techjoomla.Libraries
 * @subpackage  JSocial
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

/**
 * Helper class for common functions in JSocial library
 *
 * @package     Techjoomla.Libraries
 * @subpackage  JSocial
 * @since       1.0.4
 */
class TechjoomlaCommon
{
	/**
	 * Get itemid for given link
	 *
	 * @param   string   $link          link
	 * @param   integer  $skipIfNoMenu  Decide to use Itemid from $input
	 *
	 * @return  item id
	 *
	 * @since  3.0
	 */
	public static function getItemId($link, $skipIfNoMenu = 0)
	{
		$itemid    = 0;
		$mainframe = JFactory::getApplication();
		$input     = JFactory::getApplication()->input;

		if ($mainframe->issite())
		{
			$JSite = new JSite;
			$menu  = $JSite->getMenu();
			$items = $menu->getItems('link', $link);

			if (isset($items[0]))
			{
				$itemid = $items[0]->id;
			}
		}

		if (!$itemid)
		{
			$db = JFactory::getDbo();

			if (JVERSION >= '3.0')
			{
				$query = "SELECT id FROM #__menu
				 WHERE link LIKE '%" . $link . "%'
				 AND published =1
				 LIMIT 1";
			}
			else
			{
				$query = "SELECT id FROM " . $db->quoteName('#__menu') . "
				 WHERE link LIKE '%" . $link . "%'
				 AND published =1
				 ORDER BY ordering
				 LIMIT 1";
			}

			$db->setQuery($query);
			$itemid = $db->loadResult();
		}

		if (!$itemid)
		{
			if ($skipIfNoMenu)
			{
				$itemid = 0;
			}
			else
			{
				$itemid  = $input->get->get('Itemid', '0', 'INT');
			}
		}

		return $itemid;
	}

	/**
	 * This function get the view path
	 *
	 * @param   STRING  $component      Component name
	 * @param   STRING  $viewname       View name
	 * @param   STRING  $layout         Layout
	 * @param   STRING  $searchTmpPath  Site
	 * @param   STRING  $useViewpath    Site
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	public function getViewpath($component, $viewname, $layout = 'default', $searchTmpPath = 'SITE', $useViewpath = 'SITE')
	{
		$app = JFactory::getApplication();

		$searchTmpPath = ($searchTmpPath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
		$useViewpath   = ($useViewpath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;

		$layoutname = $layout . '.php';

		$override = $searchTmpPath . '/' . 'templates' . '/' . $app->getTemplate() . '/' . 'html' . '/' . $component . '/' . $viewname . '/' . $layoutname;

		if (JFile::exists($override))
		{
			return $view = $override;
		}
		else
		{
			return $view = $useViewpath . '/' . 'components' . '/' . $component . '/' . 'views' . '/' . $viewname . '/' . 'tmpl' . '/' . $layoutname;
		}
	}

	/**
	 * Sort given array with the provided column and provided order
	 *
	 * @param   ARRAY   $array   array of data
	 * @param   STRING  $column  column name
	 * @param   STRING  $order   order in which array has to be sort
	 *
	 * @return  ARRAY
	 *
	 * @since   1.0
	 */
	public function multi_d_sort($array, $column, $order)
	{
		if (isset($array) && count($array))
		{
			foreach ($array as $key => $row)
			{
				$orderby[$key] = $row->$column;
			}

			if ($order == 'asc')
			{
				array_multisort($orderby, SORT_ASC, $array);
			}
			else
			{
				array_multisort($orderby, SORT_DESC, $array);
			}
		}

		return $array;
	}

	/**
	 * Get all the dates converted to utc
	 *
	 * @param   date  $date  date of lesson
	 *
	 * @return   date in utc format
	 *
	 * @since   1.0
	 */
	public function getDateInUtc($date)
	{
		// Change date in UTC
		$user   = JFactory::getUser();
		$config = JFactory::getConfig();
		$offset = $user->getParam('timezone', $config->get('offset'));

		if (!empty($date) && $date != '0000-00-00 00:00:00')
		{
			$udate = JFactory::getDate($date, $offset);
			$date = $udate->toSQL();
		}

		return $date;
	}

	/**
	 * Get all the dates converted to utc
	 *
	 * @param   date    $date                 date of lesson
	 * @param   INT     $getOnlyDate          Flag used to get only date or datetime
	 * @param   string  $date_formatwithtime  Date format with time
	 * @param   string  $date_format          Date format
	 *
	 * @return   date in utc format
	 *
	 * @since   1.0
	 */
	public function getDateInLocal($date, $getOnlyDate = 0, $date_formatwithtime = 'Y-m-d H:i:s', $date_format='Y-m-d')
	{
		if (!empty($date) && $date != '0000-00-00 00:00:00')
		{
			if ($getOnlyDate == 0)
			{
				// Create JDate object set to now in the users timezone.
				$date = JHtml::date($date, $date_formatwithtime, true);
			}
			else
			{
				$date = JHtml::date($date, $date_format, true);
			}
		}

		return $date;
	}

	/**
	 * Function extact the uploaded zip
	 *
	 * @param   String  $extractdir  Directory where you want to extract the zip
	 * @param   String  $archive     Absolute Path of the zip file
	 *
	 * @return  true if successful
	 *
	 * @since 1.0.0
	 */
	public function extractCourse($extractdir, $archive)
	{
			$archive = JPath::clean($archive);
			$extractdir = JPath::clean($extractdir);

			if (JArchive::extract($archive, $extractdir))
			{
				return true;
			}
			else
			{
				return false;
			}
	}

	/**
	 * Returns last 12 months from current month
	 *
	 * @return  array of last 12 months from current date
	 *
	 * @since   1.0.0
	 */
	public function getLastTwelveMonths()
	{
		// Get current date
		$currentDate      = date('Y-m-d');

		// Get one year back date
		$lastYearDate = date('Y-m-d', strtotime(date("Y-m-d", time()) . " - 365 day"));

		// Convert dates to UNIX timestamp
		$lastYearTime = strtotime($lastYearDate);
		$CurrentDateTime = strtotime($currentDate);
		$tmp = date('mY', $CurrentDateTime);
		$year = date('Y', $lastYearTime);

		// $months[] = array("month" => date('F', $lastYearTime), "year" => date('Y', $lastYearTime));

		while ($lastYearTime < $CurrentDateTime)
		{
			// Array to store months with 30 and 31 days
			$month31 = array(1,3,5,7,8,10,12);
			$month30 = array(4,6,9,11);

			$month = date('m', $lastYearTime);

			if (array_search($month, $month31))
			{
				$lastYearTime = strtotime(date('Y-m-d', $lastYearTime) . ' +31 days');
			}
			elseif (array_search($month, $month30))
			{
				$lastYearTime = strtotime(date('Y-m-d', $lastYearTime) . ' +30 days');
			}
			else
			{
				if ( ((0 == $year % 4) && (0 != $year % 100)) || (0 == $year % 400))
				{
					$lastYearTime = strtotime(date('Y-m-d', $lastYearTime) . ' +29 days');
				}
				else
				{
					$lastYearTime = strtotime(date('Y-m-d', $lastYearTime) . ' +28 days');
				}
			}

			if (date('mY', $lastYearTime) != $tmp && ($lastYearTime < $CurrentDateTime))
			{
				$months[] = array(
					"month" => date('F', $lastYearTime),
					"year" => date('Y', $lastYearTime)
				);
			}
		}

		$months[] = array("month" => date('F', $CurrentDateTime),"year" => date('Y', $CurrentDateTime));

		return $months;
	}

	/**
	 * If you want to use outsource javascript, and wants to use in multiple modules or plugins
	 *
	 * @param   string  $script  script name
	 *
	 * @return  Boolean
	 *
	 * @since  1.6
	 */
	public static function loadScriptOnce($script)
	{
		$doc = JFactory::getDocument();
		$flg = 0;

		foreach ($doc->_scripts as $name => $ar)
		{
			if ($name == $script)
			{
				$flg = 1;
			}
		}

		if ($flg == 0)
		{
			$doc->addScript($script);
		}
	}

	/**
	 * Function to return extension version
	 *
	 * @param   string  $extensionType  Extension Type
	 *
	 * @param   string  $extensionName  Extesion Name
	 *
	 * @return  Float
	 *
	 * @since  1.6
	 */
	public function getExtensionVersion($extensionType, $extensionName)
	{
		if (empty($extensionName) || empty($extensionType))
		{
			return false;
		}

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('name', 'manifest_cache')))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('type') . ' = ' . $db->quote($extensionType))
				->where($db->quoteName('name') . ' = ' . $db->quote($extensionName));

		$db->setQuery($query);

		$extension = $db->loadObject();

		$decode = json_decode($extension->manifest_cache);
		$version = $decode->version;

		return $version;
	}
}
