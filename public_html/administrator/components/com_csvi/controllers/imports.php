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
 * Import controller.
 *
 * @package     CSVI
 * @subpackage  Import
 * @since       6.0
 */
class CsviControllerImports extends JControllerLegacy
{
	/**
	 * Handle the template selection.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function selectSource()
	{
		$template_id = $this->input->getInt('csvi_template_id', false);

		// Prepare the template
		/** @var CsviModelImports $model */
		$model = $this->getModel('Imports', 'CsviModel');
		$model->initialise($template_id);

		// Prepare the logger
		$model->initialiseLog();

		// Prepare the import run
		$csvi_log_id = $model->initialiseRun();

		// Redirect to the template view
		$this->setRedirect('index.php?option=com_csvi&task=importsource.source&runId=' . $csvi_log_id);
		$this->redirect();
	}

	/**
	 * Cancel the import and return to the import page.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function cancel()
	{
		// Set the end timestamp
		/** @var CsviModelImports $model */
		$model = $this->getThisModel();
		$model->setEndTimestamp($this->input->getInt('csvi_process_id', 0));

		// Redirect back to the import page
		$this->setRedirect('index.php?option=com_csvi&view=imports', JText::_('COM_CSVI_IMPORT_CANCELED'), 'notice');
		$this->redirect();
	}
}
