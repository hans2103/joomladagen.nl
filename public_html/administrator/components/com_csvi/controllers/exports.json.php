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

/**
 * Export Controller.
 *
 * @package     CSVI
 * @subpackage  Export
 * @since       6.0
 */
class CsviControllerExports extends JControllerLegacy
{
	/**
	 * Export the requested data.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	public function export()
	{
		// Get the run ID
		$runId = $this->input->getInt('runId', false);

		// Get the model
		/** @var CsviModelExports $model */
		$model = $this->getModel('Exports', 'CsviModel');

		try
		{
			if (!$runId)
			{
				throw new CsviException(JText::_('COM_CSVI_NO_VALID_RUNID_FOUND'), 506);
			}

			// Load the template
			$templateId = $model->getTemplateId($runId);

			if (!$templateId)
			{
				throw new CsviException(JText::_('COM_CSVI_NO_TEMPLATEID_FOUND'), 509);
			}

			$model->loadTemplate($templateId);

			// Load the template
			$template = $model->getTemplate();

			// Get the component and operation
			$component     = $template->get('component');
			$extension     = substr($component, 4);
			$operation     = $template->get('operation');
			$override      = $template->get('override');
			$adminTemplate = JFactory::getApplication()->getTemplate();

			// If the addon is not installed show message to install it
			if (!file_exists(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component))
			{
				throw new CsviException(JText::sprintf('COM_CSVI_NO_ADDON_INSTALLED', $component));
			}

			if (!$component && !$operation)
			{
				throw new CsviException(JText::_('COM_CSVI_EXPORT_NO_COMPONENT_NO_OPERATION'), 514);
			}

			// Set the classname
			$classname = '\\' . $extension . '\\' . $component . '\\model\export\\' . $operation;

			if ($override)
			{
				if (file_exists(JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' . $component . '/model/export/' . $override . '.php'))
				{
					JLoader::registerPrefix(ucfirst($component), JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' . $component);
					$classname = ucwords($component) . 'ModelExport' . ucwords($override);
				}
				else
				{
					JLoader::registerNamespace($extension, JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/');
					$classname = '\\' . $extension . '\\' . $component . '\\model\export\\' . $override;
				}

				if (!class_exists($classname))
				{
					$classname = '\\' . $extension . '\\' . $component . '\\model\export\\' . $override;
				}
			}

			// Setup the component autoloader
			JLoader::registerNamespace($extension, JPATH_PLUGINS . '/csviaddon/');

			// This loader is still needed for other classes than the export
			JLoader::registerPrefix(ucfirst($component), JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component);

			/** @var CsviModelExports $routine */
			$routine = new $classname;

			// Prepare for export
			$routine->initialiseExport($runId);
			$routine->onBeforeExport($component);

			if ($override)
			{
				// Set the override for the operation model if exists
				$overridefile = JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' . $component . '/model/export/' . $override . '.php';

				if (file_exists($overridefile))
				{
					$this->addModelPath(JPATH_ADMINISTRATOR . '/templates/' . $adminTemplate . '/html/com_csvi/' . $component . 'model/export');
				}
				else
				{
					$this->addModelPath(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component . '/model/export');
				}
			}

			// Start the export
			try
			{
				$routine->runExport();
				$result['process'] = false;
				$result['records'] = $routine->getRecords();
				$result['downloadurl'] = $routine->getDownloadUrl();
				$returnUrl = JUri::root() . 'administrator/index.php?option=com_csvi&view=exports';
				$result['url'] = JUri::root() . 'administrator/index.php?option=com_csvi&view=logdetails&run_id=' . $routine->getLogId() . '&return=' . base64_encode($returnUrl);

				// Output the results in JSON
				echo json_encode($result);

				JFactory::getApplication()->close();
			}
			catch (Exception $e)
			{
				// Finalize the export
				$routine->setEndTimestamp($runId);

				// Enqueue the message
				$helper = new CsviHelperCsvi;
				$helper->enqueueMessage($e->getMessage(), 'error');

				// Send the user to the log details
				$result['process'] = false;
				$result['url'] = JUri::root() . 'administrator/index.php?option=com_csvi&view=logdetails&run_id=' . $routine->getLogId();

				// Output the results in JSON
				echo json_encode($result);
			}
		}
		catch (Exception $e)
		{
			// Finalize the export
			$model->setEndTimestamp($runId);

			// Enqueue the message
			$helper = new CsviHelperCsvi;
			$helper->enqueueMessage($e->getMessage(), 'error');

			// Send the user to the log details
			$result['process'] = false;
			$result['url'] = JUri::root() . 'administrator/index.php?option=com_csvi&view=logs';

			// Output the results in JSON
			echo json_encode($result);
		}
	}

	/**
	 * Retrieve different kinds of data in JSON format.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *
	 * @throws  Exception
	 */
	public function getData()
	{
		$component = $this->input->getCmd('component', 'com_csvi');
		$extension = substr($component, 4);
		$function  = $this->input->getCmd('function', '');
		$filter    = $this->input->getCmd('filter', '');
		$db        = JFactory::getDbo();
		$result    = array();

		// Setup the auto loader
		JLoader::registerPrefix(ucfirst($component), JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component);

		// Load the addon helper
		$addon = ucfirst($component) . 'Helper' . ucfirst($component) . '_Json';

		$helper = new $addon($db);

		if (method_exists($helper, $function))
		{
			$result = $helper->$function($filter);
		}

		echo json_encode($result);
		jexit();
	}

	/**
	 * Load the available sites for XML or HTML export.
	 *
	 * @return  string  JSON encoded string of a select list.
	 *
	 * @since   4.0
	 */
	public function loadSites()
	{
		/** @var CsviModelExports $model */
		$model = $this->getModel('Exports', 'CsviModel');
		$sites = $model->getExportSites($this->input->get('exportsite'));

		echo json_encode($sites);

		jexit();
	}
}
