<?php
/**
 * @package     CSVI
 * @subpackage  Controller
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Import source controller.
 *
 * @package     CSVI
 * @subpackage  Controller
 * @since       6.0
 */
class CsviControllerImportsource extends JControllerLegacy
{
	/**
	 * Show the form.
	 *
	 * @return  import details.
	 *
	 * @since   6.0
	 */
	public function source()
	{
		// Get the template ID
		$runId = $this->input->getInt('runId', false);

		if ($runId)
		{
			// Load the model
			/** @var CsviModelImportsources $model */
			$model = $this->getModel('Importsources', 'CsviModel');

			// Initialise the import
			try
			{
				$model->initialiseImport($runId);

				// Push the template into the view
				$view = $this->getView('Importsource', 'html');
				$view->set('template', $model->getTemplate());
				$view->display();
			}
			catch (Exception $e)
			{
				// We don't have valid data, return to the import page
				$this->setRedirect('index.php?option=com_csvi&view=imports', $e->getMessage(), 'error');
				$this->redirect();
			}
		}
	}

	/**
	 * Prepare for preview.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function preview()
	{
		// Get the template ID
		$runId = $this->input->getInt('runId', false);

		// Check if we have a valid template ID
		if ($runId)
		{
			try
			{
				// Get the model
				/** @var CsviModelImportsources $model */
				$model = $this->getModel('Importsources', 'CsviModel');

				// Initialise the model
				$model->initialiseImport($runId);

				// Process the file
				$model->initialiseFile();

				// Redirect to the preview page
				$this->setRedirect('index.php?option=com_csvi&task=importpreview.preview&runId=' . $runId);
				$this->redirect();
			}
			catch (Exception $e)
			{
				$this->setRedirect('index.php?option=com_csvi&view=imports', $e->getMessage(), 'error')->redirect();
			}
		}
		else
		{
			// We don't have valid data, return to the import page
			$this->setRedirect('index.php?option=com_csvi&view=imports', JText::_('COM_CSVI_IMPORTPREVIEW_NO_CSVIHELPERTEMPLATE_FOUND'), 'error')->redirect();
		}
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
		// Redirect back to the import page
		$this->setRedirect('index.php?option=com_csvi&view=imports', JText::_('COM_CSVI_IMPORT_CANCELED'), 'notice');
		$this->redirect();
	}
}
