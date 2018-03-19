<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaMenu
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - [year] RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace menus\com_menus\model\import;

defined('_JEXEC') or die;

/**
 * Menus import.
 *
 * @package     CSVI
 * @subpackage  JoomlaMenu
 * @since       6.3.0
 */
class Menu extends \RantaiImportEngine
{
	/**
	 * Menu table
	 *
	 * @var    \MenusTableMenu
	 * @since  6.3.0
	 */
	private $menu;

	/**
	 * CSVI fields
	 *
	 * @var    \CsviHelperImportFields
	 * @since  6.0
	 */
	protected $fields;

	/**
	 * The addon helper
	 *
	 * @var    \Com_MenusHelperCom_Menus
	 * @since  6.3.0
	 */
	protected $helper;

	/**
	 * List of core fields
	 *
	 * @var    array
	 * @since  6.5.0
	 */
	private $corefields = array();

	/**
	 * List of custom fields being imported
	 *
	 * @var    array
	 * @since  3.5.0
	 */
	private $customfields = array();

	/**
	 * Start the menu import process.
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   6.3.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
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
					case 'access':
						$this->setState('access', $this->getAccessLevel($value));
						break;
					case 'template_style':
						$this->setState('template_style_id', $this->getTemplateStyleId($value));
						break;
					case 'menuordering':
						switch ($value)
						{
							case -1:
							case -2:
								$this->setState('menuordering', $value);
								break;
							default:
								$this->setState('menuordering', $this->getMenuOrdering($value));
								break;
						}
						break;
					case 'show_page_heading':
						switch ($value)
						{
							case 'n':
							case 'no':
							case 'N':
							case 'NO':
							case '0':
								$value = 0;
								break;
							case 'y':
							case 'yes':
							case 'Y':
							case 'YES':
							case '1':
								$value = 1;
								break;
							default:
								$value = '';
								break;
						}

						$this->setState($name, $value);
						break;
					case 'secure':
						switch (strtolower($value))
						{
							case 'n':
							case 'no':
							case '0':
								$value = -1;
								break;
							case 'y':
							case 'yes':
							case '1':
								$value = 1;
								break;
							default:
								$value = 0;
								break;
						}

						$this->setState($name, $value);
						break;
					case 'published':
						switch (strtolower($value))
						{
							case '-2':
								$value = -2;
								break;
							case 'y':
							case 'yes':
							case '1':
								$value = 1;
								break;
							default:
								$value = 0;
								break;
						}

						$this->setState($name, $value);
						break;
					case 'menu_text':
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
						if (!in_array($name, $this->corefields, true))
						{
							$this->customfields[] = $name;
						}

						$this->setState($name, $value);
						break;
				}
			}
		}

		/**
		 * Required fields
		 * type
		 * path
		 * menutype
		 */

		// There must be an id or path
		if ($this->getState('id', false)
			|| ($this->getState('path', false) && $this->getState('type', false) && $this->getState('menutype', false)))
		{
			$this->loaded = true;

			if (!$this->getState('id', false))
			{
				$this->setState('id', $this->helper->getMenuId($this->getState('path'), $this->getState('type', false)));
			}

			// Load the current menu data
			if (!$this->template->get('overwrite_existing_data') && $this->menu->load($this->getState('id', 0)))
			{
				$this->log->add(\JText::sprintf('COM_CSVI_DATA_EXISTS_MENU_TYPE', $this->getState('path', ''), $this->getState('type', '')));
				$this->loaded = false;
			}
		}
		else
		{
			// We must have the required fields otherwise menu cannot be created
			$this->loaded = false;

			$this->log->addStats('skipped', \JText::_('COM_CSVI_MISSING_REQUIRED_FIELDS_MENU_TYPE'));
		}

		return true;
	}

	/**
	 * Process a record.
	 *
	 * @return  bool  Returns true if all is OK | Returns false if no path or menu ID can be found.
	 *
	 * @since   6.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 * @throws  \UnexpectedValueException
	 */
	public function getProcessRecord()
	{
		if ($this->loaded)
		{
			if (!$this->getState('id', false) && $this->template->get('ignore_non_exist'))
			{
				// Do nothing for new menus when user chooses to ignore new menus
				$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('path', '')));
			}
			else
			{
				$this->log->add('Process path:' . $this->getState('path'), false);
				$this->log->add('Process type:' . $this->getState('type'), false);

				// Get the component ID and do some cleaning where needed
				switch ($this->getState('type'))
				{
					case 'component':
						$componentId = $this->getComponentId($this->getState('component'));

						// Clean the link
						$link = $this->getState('link', '');
						$linkPosition = stripos($link, 'index.php');

						if ($linkPosition > 0)
						{
							$this->setState('link', substr($link, $linkPosition));
						}
						break;
					case 'url':
						$componentId = 0;

						// Make sure the links start with http
						$link = $this->getState('link', '');

						if (0 !== strpos($link, 'http'))
						{
							$this->setState('link', 'http://' . $link);
						}
						break;
					default:
						$componentId = 0;
						break;
				}

				// Check and set the menu type
				$this->setState('menutype', $this->getMenuType($this->getState('menutype')));

				if (!$this->getState('id', false))
				{
					$paths    = explode('/', $this->getState('path'));
					$path     = '';
					$type     = $this->getState('type');
					$parentId = false;
					$pathkeys = array_keys($paths);
					$lastkey  = array_pop($pathkeys);

					foreach ($paths as $key => $menu)
					{
						if ($key > 0)
						{
							$path .= '/' . $menu;
						}
						else
						{
							$path = $menu;
						}

						// Check if the path exists
						$pathId = $this->helper->getMenuId($path, $type);

						// Menu doesn't exist
						if (!$pathId)
						{
							// Clean the table
							$this->menu->reset();

							// Bind the data
							$data                      = array();
							$data['path']              = $path;
							$data['type']              = $type;
							$data['alias']             = $this->getState('alias');
							$data['published']         = (!$this->getState('published', false)) ? 0 : $this->getState('published');
							$data['access']            = (!$this->getState('access', false)) ? 1 : $this->getState('access');
							$data['params']            = $this->getState('params', '{}');
							$data['language']          = (!$this->getState('language', false)) ? '*' : $this->getState('language');
							$data['link']              = $this->getState('link', '');
							$data['client_id']         = $this->getState('client_id', 0);
							$data['component_id']      = $componentId;
							$data['menutype']          = $this->getState('menutype', 'mainmenu');
							$data['browserNav']        = (!$this->getState('browserNav', false)) ? 0 : $this->getState('browserNav');
							$data['template_style_id'] = (!$this->getState('template_style_id', false)) ? 0 : $this->getState('template_style_id');
							$data['note']              = $this->getState('note', null);
							$data['menuordering']      = 0;

							if ($parentId)
							{
								$data['parent_id'] = $parentId;
							}
							else
							{
								$data['parent_id'] = 1;
							}

							if ($lastkey === $key)
							{
								$data['title'] = (!$this->getState('title', false)) ? $menu : $this->getState('title');
								$data['note'] = $this->getState('note');
								$data['description'] = $this->getState('description');
							}
							else
							{
								$data['title'] = $menu;
							}

							// Set the menu location
							$this->menu->setLocation($data['parent_id'], 'last-child');

							// Bind the data
							$this->menu->bind($data);

							try
							{
								$this->menu->checkMenu($this->date);

								if ($this->menu->storeMenu($this->date, $this->userId))
								{
									$this->menu->rebuildPath($this->menu->get('id'));
									$this->menu->rebuild($this->menu->get('id'), $this->menu->lft, $this->menu->level, $this->menu->get('path'));
									$parentId = $this->menu->get('id');
									$this->log->add('Joomla menu updated', false);
									$this->log->addStats('Updated', \JText::_('COM_CSVI_ADD_MENU'));
								}
							}
							catch (\Exception $e)
							{
								$this->log->add('Cannot add Joomla menu. Error: ' . $e->getMessage(), false);
								$this->log->addStats('incorrect', $e->getMessage());

								return false;
							}
						}
						else
						{
							$parentId = $pathId;
						}
					}
				}
				else
				{
					// Check if we use a given menu id
					if ($this->template->get('keepmenuid', false))
					{
						$menuId = $this->menu->checkId($this->getState('id'), $this->date, $this->getState('alias', $this->getState('path')));
						$this->setState('id', $menuId);
					}

					// Menu already exist, just update it
					$this->menu->load($this->getState('id'));

					// Remove the alias, so it can be created again
					$this->menu->alias = null;

					// Prepare the data
					$data                      = array();
					$data['alias']             = $this->getState('alias', null);
					$data['published']         = $this->getState('published', $this->menu->get('published'));
					$data['access']            = $this->getState('access', null);
					$data['language']          = $this->getState('language', $this->menu->get('language'));
					$data['link']              = $this->getState('link', null);
					$data['client_id']         = $this->getState('client_id', null);
					$data['component_id']      = $componentId;
					$data['path']              = $this->getState('path', null);
					$data['title']             = $this->getState('title', null);
					$data['menutype']          = $this->getState('menutype', null);
					$data['home']              = $this->getState('home', $this->menu->get('home', 0));
					$data['browserNav']        = $this->getState('browserNav', null);
					$data['template_style_id'] = $this->getState('template_style_id', null);
					$data['note']              = $this->getState('note', null);
					$data['type']              = $this->getState('type', null);

					// Process the access level
					$this->getAccessLevel($this->getState('access'));

					// Process the parameters
					$this->setParameters();
					$data['params'] = $this->getState('params', null);

					$menuOrdering = $this->getState('menuordering', null, 'int');

					// If first is chosen make the item the first child of the selected parent.
					if ($menuOrdering === -1)
					{
						$this->menu->setLocation($menuOrdering, 'first-child');
					}
					// If last is chosen make it the last child of the selected parent.
					elseif ($menuOrdering === -2)
					{
						$this->menu->setLocation($menuOrdering, 'last-child');
					}
					// Don't try to put an item after itself. All other ones put after the selected item.
					// $data['id'] is empty means it's a save as copy
					elseif ($menuOrdering && $this->menu->get('id') !== $menuOrdering)
					{
						$this->menu->setLocation($menuOrdering, 'after');
					}

					// Bind the data
					$this->menu->bind($data);

					try
					{
						$this->menu->checkMenu($this->date);
						$this->menu->storeMenu($this->date, $this->userId);
						$this->log->add('Joomla menu updated', false);
						$this->log->addStats('Updated', \JText::_('COM_CSVI_UPDATE_MENU'));
					}
					catch (\Exception $e)
					{
						$this->log->add('Cannot add Joomla menu. Error: ' . $e->getMessage(), false);
						$this->log->addStats('incorrect', $e->getMessage());

						return false;
					}
				}
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Load the necessary tables.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function loadTables()
	{
		\JTable::addIncludePath(JPATH_PLUGINS . '/csviaddon/menus/com_menus/table');
		$this->menu = \JTable::getInstance('Menu', 'MenusTable');

		// Inject the template into the table, needed for transliteration
		$this->menu->setTemplate($this->template);
		$this->menu->setLogger($this->log);

		// Set the corefields, needed to be able to distinguish component fields
		$this->corefields = array(
			'id',
			'menutype',
			'title',
			'alias',
			'note',
			'path',
			'link',
			'type',
			'published',
			'parent_id',
			'level',
			'component_id',
			'checked_out',
			'checked_out_time',
			'browserNav',
			'access',
			'img',
			'template_style_id',
			'params',
			'lft',
			'rgt',
			'home',
			'language',
			'client_id',
			'template_style',
			'menuordering',
			'menu-anchor_title',
			'menu-anchor_css',
			'menu_image',
			'menu_text',
			'page_title',
			'show_page_heading',
			'pageclass_sfx',
			'menu-meta_description',
			'menu-meta_keywords',
			'robots',
			'secure',
			'page_heading',
		);
	}

	/**
	 * Clear the loaded tables.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function clearTables()
	{
		$this->menu->reset();
	}

	/**
	 * Rebuild the menu left and right values after the import is complete.
	 *
	 * @return  void
	 *
	 * @since   7.2.0
	 */
	public function onAfterStart()
	{
		// Rebuild is only needed when we are keeping the menu ID
		if ($this->template->get('keepmenuid', false))
		{
			$this->menu->rebuild(1);
		}
	}

	/**
	 * Get the component ID.
	 *
	 * @param   string  $component  The name of the component to get the ID for.
	 *
	 * @return  int  The ID of the component.
	 *
	 * @since   6.3.0
	 *
	 * @throws  \RuntimeException
	 */
	private function getComponentId($component)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('extension_id'))
			->from($this->db->quoteName('#__extensions'))
			->where($this->db->quoteName('name') . ' = ' . $this->db->quote($component))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'));
		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Get template style ID.
	 *
	 * @param   string  $templateStyle  The name of the template style.
	 *
	 * @return  int  The ID of the template style.
	 *
	 * @since   6.5.0
	 *
	 * @throws  \RuntimeException
	 */
	private function getTemplateStyleId($templateStyle)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__template_styles'))
			->where($this->db->quoteName('title') . ' = ' . $this->db->quote($templateStyle));
		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Get the ID of the menu item to precede the imported menu item.
	 *
	 * @param   string  $menuordering  The location of the menu item
	 *
	 * @return  int  The ID of the menu item.
	 *
	 * @since   6.5.0
	 *
	 * @throws  \RuntimeException
	 */
	private function getMenuOrdering($menuordering)
	{
		list($type, $alias) = explode('/', $menuordering);

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__menu'))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote($type))
			->where($this->db->quoteName('alias') . ' = ' . $this->db->quote($alias));
		$this->db->setQuery($query);

		$id = $this->db->loadResult();

		if (!$id)
		{
			$id = -2;
		}

		return $id;
	}

	/**
	 * Set the parameter fields.
	 *
	 * @return  void.
	 *
	 * @since   6.5.0
	 */
	private function setParameters()
	{
		// Check for attributes
		if (!$this->getState('params', false))
		{
			$attributeFields = array
			(
				'menu-anchor_title',
				'menu-anchor_css',
				'menu_image',
				'menu_text',
				'page_title',
				'show_page_heading',
				'page_heading',
				'pageclass_sfx',
				'menu-meta_description',
				'menu-meta_keywords',
				'robots',
				'secure',
			);

			// Merge the custom fields the user is importing
			$attributeFields = array_merge($attributeFields, $this->customfields);

			// Load the current attributes
			$parameters = json_decode($this->menu->get('params'));

			if (!is_object($parameters))
			{
				$parameters = new \stdClass;
			}

			foreach ($attributeFields as $field)
			{
				if ($this->getState($field, false))
				{
					if ($this->$field === '*')
					{
						$parameters->$field = '';
					}
					else
					{
						$parameters->$field = $this->getState($field, '');
					}
				}
			}

			// Store the new attributes
			$this->setState('params', json_encode($parameters));
		}
	}

	/**
	 * Get the access level for the menu item.
	 *
	 * @param   string  $name  The name of the access level.
	 *
	 * @return  int  The ID of the access level.
	 *
	 * @since   6.5.0
	 *
	 * @throws  \RuntimeException
	 */
	private function getAccessLevel($name)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__usergroups'))
			->where($this->db->quoteName('title') . ' = ' . $this->db->quote($name));
		$this->db->setQuery($query);

		$id = $this->db->loadResult();

		if (!$id)
		{
			$id = 1;
		}

		return $id;
	}

	/**
	 * Get the menu location for the menu item.
	 *
	 * @param   string  $name  The name of the menu location.
	 *
	 * @return  string  The menutype of the menu location.
	 *
	 * @since   6.5.0
	 *
	 * @throws  \RuntimeException
	 */
	private function getMenuType($name)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(array('menutype', 'id')))
			->from($this->db->quoteName('#__menu_types'))
			->where(
				$this->db->quoteName('title') . ' = ' . $this->db->quote($name) . ' OR' .
				$this->db->quoteName('menutype') . ' = ' . $this->db->quote($name)
			);
		$this->db->setQuery($query);
		$menuTypeRow = $this->db->loadObject();
		$menuType = $menuTypeRow->menutype;
		$menuId = $menuTypeRow->id;
		$clientId = $this->getState('client_id', 0);

		// No menu type found, let's create it
		if (!$menuType)
		{
			$quotedName = $this->db->quote($name);

			$query->clear()
				->insert($this->db->quoteName('#__menu_types'))
				->columns(
					$this->db->quoteName(
						array(
							'menutype',
							'title',
							'description',
							'client_id'
						)
					)
				)
				->values($quotedName . ', ' . $quotedName . ', ' . $quotedName . ', ' . $this->db->quote($clientId));
			$this->db->setQuery($query)->execute();
			$this->log->add('Adding menu type ' . $name . ' because it was not found');

			$menuType = $name;
		}
		else
		{
			$query->clear()
				->update($this->db->quoteName('#__menu_types'))
				->set($this->db->quoteName('client_id') . ' = ' . (int) $clientId)
				->where($this->db->quoteName('id') . ' = ' . (int) $menuId);
			$this->db->setQuery($query)->execute();
			$this->log->add('Updated menu type ' . $name . ' client_id field');
		}

		return $menuType;
	}
}
