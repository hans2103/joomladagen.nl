<?php
/**
 * @package     CSVI
 * @subpackage  Maintenance
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Maintenance controller.
 *
 * @package     CSVI
 * @subpackage  Maintenance
 * @since       6.0
 */
class CsviControllerMaintenance extends JControllerLegacy
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModel
	 *
	 * @since   6.6.0
	 */
	public function getModel($name = 'Maintenance', $prefix = 'CsviModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Single record read. The id set in the request is passed to the model and
	 * then the item layout is used to render the result.
	 *
	 * @return  bool
	 *
	 * @since   6.0
	 */
	public function read()
	{
		/** @var CsviModelMaintenance $model */
		$model = $this->getModel();

		// Load the view
		$view = $this->getView('Maintenance', 'html');
		$view->setModel($model, true);

		// Load the component and operation
		$component = $this->input->get('component');

		// Need to store form options in the session otherwise they won't be available later
		/** @var JSession $session */
		$session = JFactory::getSession();
		$session->set('form', serialize($this->input->get('form', array(), 'array')), 'com_csvi');

		// Check if there is a file uploaded
		$files = $this->input->files->get('form', array(), 'raw');

		if (!empty($files))
		{
			$files = $model->storeUploadedFiles($files);

			$session->set('files', serialize($files), 'com_csvi');
		}

		// Load the language of the addon
		$model->loadLanguage($component);

		// Set the layout
		$view->setLayout('run');

		// Display
		return $view->display();
	}

	/**
	 * Cancel the maintenance operation.
	 *
	 * @return  void.
	 *
	 * @since   3.3
	 */
	public function cancelOperation()
	{
		// Load the component
		$csvi_log_id = $this->input->get('run_id');

		$model = $this->getModel();
		$model->cancel($csvi_log_id);

		// Redirect back to the maintenance page
		$this->setRedirect('index.php?option=com_csvi&view=maintenance', JText::_('COM_CSVI_MAINTENANCE_CANCELED'), 'notice');
	}
}
