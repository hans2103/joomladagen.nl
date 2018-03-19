<?php
/**
 * @package     CSVI
 * @subpackage  Export
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Export file View
 */
class CsviViewExportfile extends JViewLegacy {

	/**
	 * Export file display method
	 *
	 * @copyright
	 * @author 		RolandD
	 * @todo
	 * @see			CsviModelExportfile::getProcessData()
	 * @access 		public
	 * @param
	 * @return
	 * @since 		4.0
	 */
	public function display($tpl = null) {
		$jinput = JFactory::getApplication()->input;
		// Process the export data
		$result = $this->get('ProcessData');

		if (!$jinput->get('cron', false, 'bool')) {
			// Load the results
			$logresult = $this->get('Stats', 'log');
			$this->assignRef('logresult', $logresult);

			// Load the run ID
			$jinput = JFactory::getApplication()->input;
			$csvilog = $jinput->get('csvilog', null, null);
			$this->assignRef('run_id', $csvilog->getLogId());
		}

		// Display it all
		parent::display($tpl);
	}
}
