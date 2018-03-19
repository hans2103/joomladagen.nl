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
 * Maintenance model.
 *
 * @package     CSVI
 * @subpackage  Maintenance
 * @since       6.0
 */
class CsviModelMaintenance extends CsviModelDefault
{
	/**
	 * Load a maintenance addon.
	 *
	 * @param   string  $component  The name of the component being processed.
	 * @param   bool    $isCli      Set if we are running CLI mode.
	 *
	 * @return  mixed	addon class if found.
	 *
	 * @throws  CsviException
	 *
	 * @since   6.0
	 */
	private function loadAddon($component, $isCli = false)
	{
		if ($component)
		{
			$extension = substr($component, 4);

			if (file_exists(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component . '/model/maintenance.php'))
			{
				require_once JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component . '/model/maintenance.php';
				$classname = $component . 'Maintenance';
				$addon = new $classname($this->db, $this->log, $this->csvihelper, $isCli);

				// Load the language files, always load com_csvi
				$this->csvihelper->loadLanguage('com_csvi');
				$this->csvihelper->loadLanguage($component);

				return $addon;
			}

			throw new CsviException(JText::sprintf('COM_CSVI_ADDON_MAINTENANCE_NOT_FOUND', $component), 511);
		}

		throw new CsviException(JText::sprintf('COM_CSVI_ADDON_MAINTENANCE_NO_COMPONENT'), 518);
	}

	/**
	 * Run a maintenance operation.
	 *
	 * @param   string  $component  The name of the component being processed.
	 * @param   string  $operation  The name of the operation being performed.
	 * @param   mixed   $key        A counter that can be used by methods to keep track of status.
	 * @param   bool    $isCli      Set if we are running CLI mode.
	 *
	 * @return  array	List of result parameters.
	 *
	 * @throws  CsviException
	 *
	 * @since   6.0
	 */
	public function runOperation($component, $operation, $key, $isCli = false)
	{
		// Load the addon
		if ($addon = $this->loadAddon($component, $isCli))
		{
			// Check if the operation exists
			if (!method_exists($addon, $operation))
			{
				throw new CsviException(JText::sprintf('COM_CSVI_OPERATION_NOT_EXIST', $component, $operation), 408);
			}

			$this->log->setActive(true);

			// Prepare the operation
			$this->prepareOperation($component, $operation);

			// Fire an onBefore
			$onBefore = 'onBefore' . $operation;

			if (method_exists($addon, $onBefore))
			{
				$addon->$onBefore($this->input);
			}

			// Execute the operation
			$addon->$operation($this->input, $key);

			// Fire an onAfter
			$onAfter = 'onAfter' . $operation;
			$options = array();

			if (method_exists($addon, $onAfter))
			{
				$options = $addon->$onAfter();

				if (!$options)
				{
					throw new CsviException(JText::_('COM_CSVI_MAINTENANCE_ONAFTER_EXECUTION_ERROR'));
				}
				else
				{
					if (array_key_exists('cancel', $options))
					{
						return $options;
					}
				}
			}

			// Collect the results
			$results = array();

			// Get the run ID
			$results['run_id'] = $this->log->getLogId();

			// Get the information to show
			$results['info'] = array_key_exists('info', $options) ? $options['info'] : '';

			// Set if the process should continue
			$results['continue'] = array_key_exists('continue', $options) ? $options['continue'] : false;

			// If no need to continue, set the end date
			if (!$results['continue'])
			{
				$this->finishOperation($results['run_id']);
			}
			else
			{
				$this->updateRecords($results['run_id']);
			}

			// Set if the process has been cancelled
			$results['cancel'] = array_key_exists('cancel', $options) ? $options['cancel'] : false;

			// Set the key
			$results['key'] = array_key_exists('key', $options) ? $options['key'] : 0;

			// Set the key
			$results['downloadfile'] = array_key_exists('downloadfile', $options) ? $options['downloadfile'] : '';

			return $results;
		}

		throw new CsviException(JText::sprintf('COM_CSVI_ADDON_NOT_FOUND', $component));
	}

	/**
	 * Prepare maintenance.
	 *
	 * @param   string  $component  The name of the component being processed.
	 * @param   string  $operation  The name of the operation being performed.
	 *
	 * @return  void.
	 *
	 * @since   3.3
	 */
	private function prepareOperation($component, $operation)
	{
		// Start the log
		$this->log->setLogId($this->input->get('run_id', 0, 'int'));
		$this->log->setAddon($component);
		$this->log->setAction('Maintenance');
		$this->log->setActionType($operation . '_LABEL');

		$this->log->initialise();
	}

	/**
	 * Maintenance operation is cancelled.
	 *
	 * @param   int  $csvi_log_id  The ID of the log entry
	 *
	 * @return  void.
	 *
	 * @throws  Exception
	 *
	 * @since   6.0
	 */
	public function cancel($csvi_log_id)
	{
		// Clean the session
		$session = JFactory::getSession();
		$session->set('form', serialize('0'), 'com_csvi');

		// Load the log details
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('addon'))
			->from($this->db->quoteName('#__csvi_logs'))
			->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $csvi_log_id);
		$component = $this->db->setQuery($query)->loadResult();

		// Load the addon
		if ($addon = $this->loadAddon($component))
		{
			// Check if the operation exists
			if (method_exists($addon, 'cancelOperation'))
			{
				// Execute the operation
				if (!$addon->cancelOperation())
				{
					// Finish the operation
					$this->cancelOperation($csvi_log_id);

					throw new Exception(JText::_('COM_CSVI_MAINTENANCE_EXECUTION_ERROR'));
				}

				// Finish the operation
				$this->cancelOperation($csvi_log_id);
			}
		}
	}

	/**
	 * Get a list of available components that have maintenance options.
	 *
	 * @return  array  Returns an array of components.
	 *
	 * @since   6.0
	 */
	public function getComponents()
	{
		// Load the components
		$components = $this->csvihelper->getComponents();

		// Check if there are any maintenance options available
		foreach ($components as $key => $component)
		{
			$extension = substr($component->value, 4);

			if (!file_exists(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component->value . '/model/maintenance.php'))
			{
				unset($components[$key]);
			}
			else
			{
				require_once JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $component->value . '/model/maintenance.php';
				$classname = $component->value . 'Maintenance';
				$maintenance = new $classname($this->db, $this->log, $this->csvihelper);

				if (!method_exists($maintenance, 'getOperations'))
				{
					unset($components[$key]);
				}
			}
		}

		$options = JHtml::_('select.option', '', JText::_('COM_CSVI_MAKE_CHOICE'), 'value', 'text', true);
		array_unshift($components, $options);

		return $components;
	}

	/**
	 * Get operations for a selected component.
	 *
	 * @param   string  $component  The name of the component to process
	 *
	 * @return  array  Returns an array of options.
	 *
	 * @since   6.0
	 */
	public function getOperations($component)
	{
		// Check for maintenance options of the addon
		if ($addon = $this->loadAddon($component))
		{
			// Load the operations
			if (method_exists($addon, 'getOperations'))
			{
				return $addon->getOperations();
			}
			else
			{
				return array('' => JText::_('COM_CSVI_NO_OPTIONS_FOUND'));
			}
		}
		else
		{
			return array('' => JText::_('COM_CSVI_NO_OPTIONS_FOUND'));
		}
	}

	/**
	 * Get options for a selected component operation.
	 *
	 * @param   string  $component  The name of the component being processed
	 * @param   string  $operation  The name of the operation being loaded
	 *
	 * @return  array  Returns an array with options.
	 *
	 * @since   6.0
	 */
	public function getOptions($component, $operation)
	{
		// Check for maintenance options of the addon
		if ($addon = $this->loadAddon($component))
		{
			// Load the operations
			if (method_exists($addon, 'getOptions'))
			{
				return array('options' => $addon->getOptions($operation));
			}
			else
			{
				return array('' => JText::_('COM_CSVI_NO_OPTIONS_FOUND'));
			}
		}
		else
		{
			return array('' => JText::_('COM_CSVI_NO_OPTIONS_FOUND'));
		}
	}

	/**
	 * Load the language of a selected component.
	 *
	 * @param   string  $component  The component to load the language for
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function loadLanguage($component)
	{
		// Load the language files
		$this->language->loadAddonLanguage($component);
	}

	/**
	 * Store uploaded files in the CSVI temp folder for future use.
	 *
	 * @param   array  $files  The array of uploaded files
	 *
	 * @return  array  An array with cleaned up file information.
	 *
	 * @since   6.0
	 */
	public function storeUploadedFiles($files)
	{
		jimport('joomla.filesystem.file');

		foreach ($files as $key => $file)
		{
			if (is_uploaded_file($file['tmp_name']))
			{
				if (JFile::upload($file['tmp_name'], CSVIPATH_TMP . '/' . $file['name'], false, true))
				{
					$files[$key]['tmp_name'] = CSVIPATH_TMP . '/' . $file['name'];
				}
			}
		}

		return $files;
	}

	/**
	 * Update the number of records
	 *
	 * @param   int  $csvi_log_id  The ID of the import process
	 *
	 * @return  void.
	 *
	 * @since   6.7.0
	 */
	private function updateRecords($csvi_log_id)
	{
		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__csvi_logs'))
			->set($this->db->quoteName('records') . ' = ' . (int) $this->log->getLinenumber())
			->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $csvi_log_id);
		$this->db->setQuery($query)
			->execute();
	}

	/**
	 * Handle the end of the import.
	 *
	 * @param   int  $csvi_log_id  The ID of the import process
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	private function finishOperation($csvi_log_id)
	{
		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__csvi_logs'))
			->set($this->db->quoteName('records') . ' = ' . (int) $this->log->getLinenumber())
			->set($this->db->quoteName('end') . ' = ' . $this->db->quote(JFactory::getDate()->toSql()))
			->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $csvi_log_id);
		$this->db->setQuery($query)
			->execute();
	}

	/**
	 * Cancel a running maintenance operation.
	 *
	 * @param   int  $csvi_log_id  The ID of the log entry
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	private function cancelOperation($csvi_log_id)
	{
		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__csvi_logs'))
			->set($this->db->quoteName('end') . ' = ' . $this->db->quote(JFactory::getDate()->toSql()))
			->set($this->db->quoteName('run_cancelled') . ' = 1')
			->where($this->db->quoteName('csvi_log_id') . ' = ' . (int) $csvi_log_id);
		$this->db->setQuery($query)->execute();
	}

	/**
	 * Check available fields if needs to be updated
	 *
	 * @return  void.
	 *
	 * @since   7.0
	 */
	public function checkAvailableFields()
	{
		// Get all the components installed
		$extensions = $this->csvihelper->getComponents();

		// Store the available fields result in cache
		$cache = JFactory::getCache('com_csvi', '');
		$cache->setCaching(true);
		$cache->setLifeTime(86400);
		$cacheId = md5('com_csvi_availablefields');
		$showMessage = $cache->get($cacheId);

		if (!$showMessage)
		{
			foreach ($extensions as $key => $extension)
			{
				$component = $extension->value;
				$parent    = substr($component, 4);

				if (JComponentHelper::isInstalled($component))
				{
					/** @var CsviModelAvailablefields $availableFieldsModel */
					$availableFieldsModel = JModelLegacy::getInstance('Availablefields', 'CsviModel', array('ignore_request' => true));

					// Set a default filters
					$availableFieldsModel->setState('filter.component', $component);
					$availableFieldsModel->setState('filter.idfields', 1);
					$totalFields = count($availableFieldsModel->getItems());

					if (file_exists(JPATH_PLUGINS . '/csviaddon/' . $parent . '/' . $component . '/model/maintenance.php'))
					{
						require_once JPATH_PLUGINS . '/csviaddon/' . $parent . '/' . $component . '/model/maintenance.php';

						$classname   = $component . 'Maintenance';
						$maintenance = new $classname($this->db, $this->log, $this->csvihelper);

						if (method_exists($maintenance, 'availableFieldsThresholdLimit'))
						{
							$thresholdLimit = $maintenance->availableFieldsThresholdLimit();

							if ($totalFields < $thresholdLimit)
							{
								$showMessage = JText::sprintf('COM_CSVI_UPDATE_AVAILABLE_FIELDS', $component);
								$cache->store($showMessage, $cacheId);
								break;
							}
						}
					}
				}
			}
		}

		if ($showMessage)
		{
			JFactory::getApplication()->enqueueMessage($showMessage, 'error');
		}
	}

	/**
	 * Check addon version is same as extension version
	 *
	 * @return  void.
	 *
	 * @since   7.1.0
	 */
	public function checkAddonVersion()
	{
		// Store the addon version result in cache
		$cache = JFactory::getCache('com_csvi', '');
		$cache->setCaching(true);
		$cache->setLifeTime(86400);
		$cacheId = md5('com_csvi_addonversion');
		$showMessage = $cache->get($cacheId);

		// Get the latest version of CSVI extension
		/** @var CsviModelAbout $aboutUsModel */
		$aboutUsModel     = JModelLegacy::getInstance('About', 'CsviModel', array('ignore_request' => true));
		$extensionVersion = substr($aboutUsModel->getSchemaVersion(), 0, 3);

		if (!$showMessage)
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('manifest_cache'))
				->from($this->db->quoteName('#__extensions'))
				->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
				->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('csviaddon'));
			$this->db->setQuery($query);
			$addons = $this->db->loadRowList();

			foreach ($addons as $addon)
			{
				$manifestCache = json_decode($addon[0], true);
				$addonVersion  = substr($manifestCache['version'], 0, 3);

				if (version_compare($addonVersion, $extensionVersion, 'eq') === false)
				{
					$showMessage = JText::_('COM_CSVI_ADDON_OUTDATED');
					$cache->store($showMessage, $cacheId);
					break;
				}
			}
		}

		if ($showMessage)
		{
			JFactory::getApplication()->enqueueMessage($showMessage, 'error');
		}
	}

	/**
	 * Check if addons are installed
	 *
	 * @return  void.
	 *
	 * @since   7.3.0
	 */
	public function checkInstalledAddons()
	{
		if ($this->settings->get('addoninstallcheck', true))
		{
			$installedPlugins = array();
			$addons           = $this->standardAddons();
			$query            = $this->db->getQuery(true)
				->select($this->db->quoteName('element'))
				->from($this->db->quoteName('#__extensions'))
				->where($this->db->quoteName('enabled') . ' = 1')
				->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'));
			$this->db->setQuery($query);
			$installedComponents = $this->db->loadRowList();

			foreach (JPluginHelper::getPlugin('csviaddon') as $plugin)
			{
				$installedPlugins[] = $plugin->name;
			}

			foreach ($installedComponents as $component)
			{
				$componentName = str_replace('com_', '', $component[0]);

				if (in_array($componentName, $addons) && !in_array($componentName, $installedPlugins))
				{
					$showMessage = JText::sprintf('COM_CSVI_NO_ADDON_INSTALLED', JText::_('COM_CSVI_ADDON_' . $componentName));
					JFactory::getApplication()->enqueueMessage($showMessage, 'warning');
					break;
				}
			}
		}
	}

	/**
	 * Return the standard addons for CSVI
	 *
	 * @return  array.
	 *
	 * @since   7.3.0
	 */
	private function standardAddons()
	{
		return array ('virtuemart','hikashop', 'k2', 'form2content', 'rsform',
			'productbuilder', 'fastseller', 'awocoupon', 'customfilters', 'j2store');
	}
}

