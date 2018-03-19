<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaModules
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace modules\com_modules\model\import;

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Modules import.
 *
 * @package     CSVI
 * @subpackage  JoomlaModule
 * @since       7.4.0
 */
class Modules extends \RantaiImportEngine
{
	/**
	 * Module table.
	 *
	 * @var    \Com_ModulesHelperCom_Modules
	 * @since  7.4.0
	 */
	private $module = null;

	/**
	 * The Joomla Module helper
	 *
	 * @var    \Com_ModulesHelperCom_Modules
	 * @since  7.4.0
	 */
	protected $helper = null;

	/**
	 * Start the product import process.
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   7.4.0
	 */
	public function getStart()
	{
		// Process data
		foreach ($this->fields->getData() as $fields)
		{
			foreach ($fields as $name => $details)
			{
				$value = $details->value;

				switch ($name)
				{
					case 'published':
						switch (strtolower($value))
						{
							case 'n':
							case 'no':
							case '0':
								$value = 0;
								break;
							default:
								$value = 1;
								break;
						}

						$this->setState($name, $value);
						break;
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		$this->loaded = true;

		// There must be an id or alias and catid or category_path
		if ($this->getState('id', false) || ($this->getState('title', false) && $this->getState('module', false)))
		{
			if (!$this->getState('id', false))
			{
				$this->setState('id', $this->helper->getModuleId($this->getState('title', false), $this->getState('module', false)));
			}

			// Load the current content data
			if ($this->module->load($this->getState('id', 0)))
			{
				if (!$this->template->get('overwrite_existing_data'))
				{
					$this->log->add('Module is not updated because the option overwrite existing data is set to No');
					$this->loaded = false;
				}
			}
		}
		else
		{
			$this->loaded = false;
			$this->log->addStats('skipped', \JText::_('COM_CSVI_NO_TITLE_MODULE_FIELDS_FOUND'));
		}

		return true;
	}

	/**
	 * Process a record.
	 *
	 * @return  bool  Returns true if all is OK | Returns false otherwise.
	 *
	 * @since   7.4.0
	 */
	public function getProcessRecord()
	{
		if ($this->loaded)
		{
			if (!$this->getState('id', false) && $this->template->get('ignore_non_exist'))
			{
				$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('id', '')));

				return false;
			}

			// Check if module
			if ($this->getState('module_delete', 'N') === 'Y')
			{
				$this->deleteModule();
			}
			else
			{
				if ($this->getState('menu_alias', false))
				{
					$menus       = explode('|', $this->getState('menu_alias', false));
					$menuIdArray = array();

					foreach ($menus as $menu)
					{
						$menuIdArray[] = $this->helper->getMenuId($menu);
					}

					$menuIds = implode('|', $menuIdArray);
					$this->setState('menuid', $menuIds);
				}

				if (!$this->getState('access', false))
				{
					$this->module->access = 1;
				}

				if (!$this->getState('language', false))
				{
					$this->module->language = '*';
				}

				// Data must be in an array
				$data = ArrayHelper::fromObject($this->state);

				$this->module->bind($data);
				$this->module->check();

				// Check if we use a given order id
				if ($this->template->get('keepid'))
				{
					$this->module->checkId();
				}

				try
				{
					$this->module->store();
					$moduleId = $this->module->id;
					$this->processMenu($moduleId);

					$this->log->addStats('Processed', \JText::_('COM_CSVI_JOOMLA_MODULE_PROCESSED'));
				}
				catch (\Exception $e)
				{
					$this->log->add('Cannot add Joomla Module. Error: ' . $e->getMessage(), false);
					$this->log->addStats('incorrect', $e->getMessage());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Delete a module detail
	 *
	 * @return  void.
	 *
	 * @since   7.4.0
	 */
	private function deleteModule()
	{
		if ($this->getState('id', false))
		{
			$query = $this->db->getquery(true)
				->delete($this->db->quotename('#__modules'))
				->where($this->db->quotename('id') . ' = ' . (int) $this->getState('id'));
			$this->db->setquery($query);
			$this->log->add('Module deleted');
			$this->db->execute();

			$query->clear()
				->delete($this->db->quoteName('#__modules_menu'))
				->where($this->db->quoteName('moduleid') . ' = ' . (int) $this->getState('id'));
			$this->log->add('Module menus deleted');
			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Save the menu details
	 *
	 * @param   int  $moduleId  The module id.
	 *
	 * @return  void.
	 *
	 * @since   7.4.0
	 *
	 */
	private function processMenu($moduleId)
	{
		$query = $this->db->getQuery(true);

		if (!$this->getState('id', 0))
		{
			if (!$this->getState('menuid', false))
			{
				$query->values((int) $moduleId . ', 0');
			}
			else
			{
				$menuIds = explode('|', $this->getState('menuid', false));

				foreach ($menuIds as $menuId)
				{
					$query->values((int) $moduleId . ',' . (int) $menuId);
				}
			}

			$query->insert($this->db->quoteName('#__modules_menu'))
				->columns($this->db->quoteName(array('moduleid', 'menuid')));
			$this->db->setQuery($query);
			$this->log->add('Insert the menus for new module');
			$this->db->execute();
		}
		else
		{
			if ($this->template->get('processmenu', false) && $this->getState('menuid', false))
			{
				$query->clear()
					->delete($this->db->quoteName('#__modules_menu'))
					->where($this->db->quoteName('moduleid') . ' = ' . (int) $moduleId);
				$this->db->setQuery($query)->execute();
				$this->log->add('Delete previous menu entries to make new inserts');

				$menuIds = explode('|', $this->getState('menuid', false));

				foreach ($menuIds as $menuId)
				{
					$query->values((int) $moduleId . ',' . (int) $menuId);
				}

				$query->insert($this->db->quoteName('#__modules_menu'))
					->columns($this->db->quoteName(array('moduleid', 'menuid')));
				$this->db->setQuery($query);
				$this->log->add('Update the menus assignment for module');
				$this->db->execute();
			}
		}
	}

	/**
	 * Load the necessary tables.
	 *
	 * @return  void.
	 *
	 * @since   7.4.0
	 *
	 * @throws  \CsviException
	 * @throws  \RuntimeException
	 */
	public function loadTables()
	{
		$this->module = $this->getTable('Modules');
	}

	/**
	 * Clear the loaded tables.
	 *
	 * @return  void.
	 *
	 * @since   7.4.0
	 */
	public function clearTables()
	{
		$this->module->reset();
	}
}
