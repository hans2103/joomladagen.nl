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

		// Load the component and operation
		$component = $this->input->get('component');
		$operation = strtolower($this->input->get('operation'));
		$subTask   = strtolower($this->input->get('subtask'));
		$options   = array();

		switch ($subTask)
		{
			case 'options':
				$options = $model->getOptions($component, $operation);
				break;
			case 'operations':
				$options = $model->getOperations($component);
				break;
		}

		echo new JResponseJson($options);

		JFactory::getApplication()->close();
	}

	/**
	 * Run a maintenance operation.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 *
	 * @throws  \Exception
	 */
	public function runOperation()
	{
		// Check for request forgeries.
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		// Set the output file
		/** @var CsviModelMaintenance $model */
		$model = $this->getModel();

		// Load the component and operation
		$component = $this->input->get('component', false);
		$operation = strtolower($this->input->get('operation', false));
		$key       = $this->input->getInt('key', 0);

		$result = array('process' => false);

		if ($component && $operation)
		{
			// Load the form options
			/** @var JSession $session */
			$session = JFactory::getSession();
			$form = unserialize($session->get('form', null, 'com_csvi'));

			// Store the form values individually, so we can filter and apply default values
			if (is_array($form))
			{
				foreach ($form as $name => $value)
				{
					$this->input->set($name, $value);
				}
			}

			// Clean the session
			$session->clear('form', 'com_csvi');

			// Store the files
			$files = unserialize($session->get('files', false, 'com_csvi'));

			if ($files)
			{
				foreach ($files as $name => $value)
				{
					$this->input->set($name, $value);
				}

				// Clean the session
				$session->clear('files', 'com_csvi');
			}

			try
			{
				// Get the result from the operation
				$result = $model->runOperation($component, $operation, $key);

				if (!$result['cancel'])
				{
					$result['process'] = true;

					if (!$result['continue'])
					{
						$result['process'] = false;

						$returnUrl = '&return=' . base64_encode(JUri::root() . 'administrator/index.php?option=com_csvi&view=maintenance');

						if ($operation === 'updateavailablefields')
						{
							$returnUrl = '&return=' . base64_encode(JUri::root() . 'administrator/index.php?option=com_csvi&view=availablefields');
						}

						// Set the forward URL
						$result['url'] = JUri::root() . 'administrator/index.php?option=com_csvi&view=logdetails&run_id=' . $result['run_id'] . $returnUrl;
					}
				}
				else
				{
					/**
					 * Check for any cancellation settings
					 * This array takes 4 options
					 * - url: Where to send the user to
					 * - msg: The message to show to the user
					 */
					$jinput = JFactory::getApplication()->input;
					$canceloptions = $jinput->get('canceloptions', array(), 'array');

					if (!empty($canceloptions))
					{
						// Set the redirect options
						$result['url'] = $canceloptions['url'];
						$result['run_id'] = 0;
					}
				}
			}
			catch (Exception $e)
			{
				// Store the message for rendering
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				$csvihelper = new CsviHelperCsvi;
				$csvihelper->enqueueMessage($e->getMessage(), 'error');
				$result['url'] = JUri::root() . 'administrator/index.php?option=com_csvi&view=maintenance';
				$result['run_id'] = 0;
			}
		}

		echo new JResponseJson($result);

		JFactory::getApplication()->close();
	}
}
