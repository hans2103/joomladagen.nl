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
class CsviControllerImport extends JControllerLegacy
{
	/**
	 * Load the import page and start the import.
	 *
	 * @return  boolean  Always returns true.
	 *
	 * @throws  CsviException
	 *
	 * @since   6.0
	 */
	public function start()
	{
		// Get the template ID
		$runId = $this->input->getInt('runId', false);

		if ($runId)
		{
			// Load the model
			/** @var CsviModelImports $model */
			$model = $this->getModel('Imports', 'CsviModel');

			// Prepare for import
			$model->initialiseImport($runId);

			// Make the template available
			$view = $this->getView('Import', 'html');
			$view->set('template', $model->getTemplate());
		}
		else
		{
			throw new CsviException(JText::_('COM_CSVI_NO_RUNID_FOUND'));
		}

		return $view->display();
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
		$model = $this->getModel('Imports', 'CsviModel');
		$model->setEndTimestamp($this->input->getInt('csvi_process_id', 0));

		// Redirect back to the import page
		$this->setRedirect('index.php?option=com_csvi&view=imports', JText::_('COM_CSVI_IMPORT_CANCELED'), 'notice');
		$this->redirect();
	}
}
