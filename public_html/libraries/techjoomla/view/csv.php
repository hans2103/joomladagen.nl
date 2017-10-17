<?php
/**
 * @version    SVN: <svn_id>
 * @package    Techjoomla.Libraries
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');
jimport('joomla.application.component.model');
jimport('techjoomla.tjcsv.csv');

/**
 * TjCsv
 *
 * @package     Techjoomla.Libraries
 * @subpackage  TjCsv
 * @since       1.0
 */
class TjExportCsv extends JViewLegacy
{
	/**
	 *  seperator specifies the field separator, default value is comma(,) .
	 *
	 * @var  boolean
	 */
	protected $seperator = ',';

	/**
	 *  enclosure specifies the field enclosure character, default value is " .
	 *
	 * @var  boolean
	 */
	protected $enclosure = '"';

	/**
	 * The filename of the downloaded CSV file.
	 *
	 * @var  string
	 */
	protected $fileName = null;

	/**
	 * Function get the limit start and total records count for CSV export
	 *
	 * @param   STRING  $tpl  file name if empty then default set component name view name date and rand number
	 *
	 * @return  jexit
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$input      = $app->input;
		$limitStart = $input->json->get('limit_start');
		$returnFileName = $input->json->get('file_name');

		$this->fileName = $this->fileName ? JFile::stripExt($this->fileName) : substr($input->get('option'), 4) . "_" .
		$input->get('view') . "_" . date("Y-m-d_H-i-s", time());
		$this->fileName .= '_' . rand() . '.' . 'csv';

		$model = JModelLegacy::getInstance($input->get('view'), substr($input->get('option'), 4) . 'Model');
		$app->setUserState($input->get('option') . '.' . $input->get('view') . '.limitstart', $limitStart);
		$model->setState("list.limit", $model->getState('list.limit'));
		$data = $model->getItems();

		$TjCsv = new TjCsv;
		$TjCsv->limitStart  = $limitStart;
		$TjCsv->recordCnt   = $model->gettotal();
		$TjCsv->seperator   = $this->seperator;
		$TjCsv->enclosure   = $this->enclosure;
		$TjCsv->csvFilename = $returnFileName ? $returnFileName : $this->fileName;
		$returnData = $TjCsv->CsvExport($data);

		echo json_encode($returnData);
		jexit();
	}
}
