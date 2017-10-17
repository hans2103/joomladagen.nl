<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

/**
 * JlikeController
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JlikeController extends JControllerLegacy
{
	/**
	 * Migrate like
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController This object to support chaining..
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT . '/helpers/jlike.php';

		$view = JFactory::getApplication()->input->getCmd('view', 'dashboard');
		JFactory::getApplication()->input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}

	/**
	 * Apply
	 *
	 * @return  return null
	 */
	public function apply()
	{
		$model = $this->getModel('buttonset');

		if ($model->store())
		{
			$msg = JText::_('COM_JLIKE_SAVED');
		}
		else
		{
			$msg = JText::_('COM_JLIKE_ERROR_IN_SAVING');
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$link = 'index.php?option=com_jlike&view=buttonset';
		$this->setRedirect($link, $msg);
	}

	/**
	 * Add
	 *
	 * @return  return null
	 */
	public function add()
	{
		$db       = JFactory::getDBO();
		$files    = JRequest::get('FILES');
		$file     = array_pop($files);
		$filename = $file['name'];
		$arr      = explode('.', $file['name']);
		$ext      = array_pop($arr);

		$imgPath     = JPATH_COMPONENT_SITE . DS . 'assets' . DS . 'images' . DS . 'buttonset' . DS;
		$destination = $imgPath . $filename;

		if (!move_uploaded_file($file['tmp_name'], $destination))
		{
			$msg = JText::_('Error: Cannot move uploaded file');
			$this->setRedirect('index.php?option=com_jlike', $msg);
		}

		$db = JFactory::getDBO();
		$db->setQuery("INSERT INTO `#__jlike` set `published`=0,`title`='{$filename}';");
		$db->query();
		$id = $db->insertid();

		if (!rename($destination, $imgPath . $filename))
		{
			$msg = JText::_('Error: Cannot rename uploaded file');
			$this->setRedirect('index.php?option=com_jlike', $msg);
		}

		$this->setRedirect('index.php?option=com_jlike&view=buttonset', JText::_('COM_JLIKE_FILE_UPLOAD_SUCCESS'));
	}

	/**
	 * Cancel
	 *
	 * @return  return null
	 */
	public function cancel()
	{
		$msg = JText::_('Operation Cancelled');
		$this->setRedirect('index.php?option=com_jlike', $msg);
	}

	/**
	 * Getversion AJax call
	 *
	 * @return  return null
	 */
	public function getVersion()
	{
		echo $recdata = file_get_contents('http://techjoomla.com/vc/index.php?key=abcd1234&product=jlike');
		jexit();
	}

	/**
	 * Migrate like
	 *
	 * @return  return null
	 */
	public function migrateLikes()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		include_once JPATH_SITE . DS . 'components' . DS . 'com_jlike' . DS . 'helpers' . DS . 'migration.php';
		$jlikemigrateobj = new comjlikeMigrateHelper;
		$result          = $jlikemigrateobj->migrateLikes();

		if ($result)
		{
			echo json_encode(1);
		}
		else
		{
			echo json_encode(-1);
		}

		jexit();
	}

	/**
	 * Save
	 *
	 * @return  return null
	 */
	public function save()
	{
		$model = $this->getModel('element_config');

		if ($model->store())
		{
			$msg = JText::_('COM_JLIKE_DATA_SAVED');
		}
		else
		{
			$msg = JText::_('COM_JLIKE_DATA_SAVED_ERROR');
		}

		$this->setRedirect('index.php?option=com_jlike&view=element_config', $msg);
	}

	/**
	 * Get Item and status related data for csv emport
	 *
	 * @since   2.2
	 * @return  list.
	 */
	public function csvExportStatusdetails()
	{
		$comjlikeHelper = new comjlikeHelper;

		$model         = $this->getModel("contents");
		$CSVData       = $model->csvExportStatusdetails();

		if (!empty($CSVData) )
		{
			$status_code = $CSVData[0]->statusCount;

			$filename      = JText::_('COM_JLIKE_STATUS_REPORTS') . date("Y-m-d_h:i:s") . '_' . $status_code;
			$params        = JComponentHelper::getParams('com_jlike');
			$currency      = $params->get('currency_symbol');
			$csvData       = null;

			// $csvData.= "Item_id;Product Name;Store Name;Store Id;Sales Count;Amount;Created By;";
			$headColumn    = array();
			$headColumn[0] = JText::_('COM_JLIKE_STATUS_EXPORT_ID');
			$headColumn[1] = JText::_('COM_JLIKE_STATUS_EXPORT_TITLE');
			$headColumn[2] = JText::_('COM_JLIKE_STATUS_EXPORT_URL');
			$headColumn[3] = JText::_('COM_JLIKE_STATUS_EXPORT_CAT_ID');
			$headColumn[4] = JText::_('COM_JLIKE_STATUS_EXPORT_CATE_NAME');
			$headColumn[5] = JText::_('COM_JLIKE_STATUS_EXPORT_STATUSCOUNT');

			/*$headColumn[6] = JText::_('COM_JLIKE_STATUS_EXPORT_ELEMENT_ID');
			$headColumn[7] = JText::_('COM_JLIKE_STATUS_EXPORT_ELEMENT');
			*/

			$csvData .= implode(";", $headColumn);
			$csvData .= "\n";
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: csv" . date("Y-m-d") . ".csv");
			header("Content-disposition: filename=" . $filename . ".csv");

			foreach ($CSVData as $data)
			{
				$csvrow    = array();
				$csvrow[0] = '"' . $data->id . '"';
				$csvrow[1] = '"' . $data->title . '"';
				$csvrow[2] = '"' . $data->url . '"';
				$csvrow[3] = '"' . $data->category_id . '"';

				// Get category name
				$csvrow[4] = '';

				if (!empty($data->category_id))
				{
					$csvrow[4] = '"' . $comjlikeHelper->getZooCatName($data->category_id) . '"';
				}

				$csvrow[5] = '"' . $data->statusCount . '"';

				/*
				$csvrow[6] = '"' . $data->element_id . '"';
				$csvrow[7] = '"' . $data->element . '"';
				*/

				$csvData .= implode(";", $csvrow);
				$csvData .= "\n";
			}

			ob_clean();
			echo $csvData . "\n";
			jexit();
		}

		$link = JURI::base() . substr(JRoute::_('index.php?option=com_jlike&view=contents', false), strlen(JURI::base(true)) + 1);
		$this->setRedirect($link);
	}

	/**
	 * Get Item and status related data for csv emport
	 *
	 * @since   2.2
	 * @return  list.
	 */
	public function csvExportAllRatings()
	{
		$comjlikeHelper = new comjlikeHelper;

		$model         = $this->getModel("ratings");
		$CSVData       = $model->csvExportAllRatings();

		if (!empty($CSVData) )
		{
			$allStatusList = (array) $comjlikeHelper->getAllStatus();
			$status_code = $CSVData[0]->statusCount;

			$filename      = JText::_('COM_JLIKE_STATUS_RATES_PLANS_REPORTS') . date("Y-m-d_h:i:s") . '_' . $status_code;
			$params        = JComponentHelper::getParams('com_jlike');
			$currency      = $params->get('currency_symbol');
			$csvData       = null;

			// $csvData.= "Item_id;Product Name;Store Name;Store Id;Sales Count;Amount;Created By;";
			$headColumn    = array();
			$headColumn[0] = JText::_('COM_JLIKE_STATUS_RATES_EXPORT_ID');
			$headColumn[1] = JText::_('COM_JLIKE_STATUS_RATES_EXPORT_TITLE');
			$headColumn[2] = JText::_('COM_JLIKE_STATUS_RATES_EXPORT_URL');
			$headColumn[3] = JText::_('COM_JLIKE_STATUS_RATES_EXPORT_CAT_ID');
			$headColumn[4] = JText::_('COM_JLIKE_STATUS_RATES_EXPORT_CATE_NAME');
			$headColumn[5] = JText::_('COM_JLIKE_STATUS_RATES_EXPORT_STATUS_TEXT');
			$headColumn[6] = JText::_('COM_JLIKE_STATUS_RATES_EXPORT_STATUS_ID');
			$headColumn[7] = JText::_('COM_JLIKE_STATUS_RATES_EXPORT_USER_ID');

			/*$headColumn[6] = JText::_('COM_JLIKE_STATUS_RATES_EXPORT_ELEMENT_ID');
			$headColumn[7] = JText::_('COM_JLIKE_STATUS_RATES_EXPORT_ELEMENT');
			*/

			$csvData .= implode(";", $headColumn);
			$csvData .= "\n";
			header("Content-type: application/vnd.ms-excel");
			header("Content-disposition: csv" . date("Y-m-d") . ".csv");
			header("Content-disposition: filename=" . $filename . ".csv");

			foreach ($CSVData as $data)
			{
				$csvrow    = array();
				$csvrow[0] = '"' . $data->id . '"';
				$csvrow[1] = '"' . $data->title . '"';
				$csvrow[2] = '"' . $data->url . '"';
				$csvrow[3] = '"' . $data->category_id . '"';

				// Get category name
				$csvrow[4] = '';

				if (!empty($data->category_id))
				{
					$csvrow[4] = '"' . $comjlikeHelper->getZooCatName($data->category_id) . '"';
				}

				$csvrow[5] = '"' . $data->status_id . '"';

				if (!empty($allStatusList[$data->status_id]))
				{
					$currentStatusCode = $allStatusList[$data->status_id]->status_code;
					$csvrow[5] = '"' . JText::_($currentStatusCode) . '"';
				}

				$csvrow[6] = '"' . $data->status_id . '"';
				$csvrow[7] = '"' . $data->user_id . '"';
				$csvData .= implode(";", $csvrow);
				$csvData .= "\n";
			}

			ob_clean();
			echo $csvData . "\n";
			jexit();
		}

		$link = JURI::base() . substr(JRoute::_('index.php?option=com_jlike&view=contents', false), strlen(JURI::base(true)) + 1);
		$this->setRedirect($link);
	}
}
