<?php
/**
 * @package     CSVI
 * @subpackage  About
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * About model.
 *
 * @package     CSVI
 * @subpackage  About
 * @since       6.0
 */
class CsviModelAbout extends JModelList
{
	/**
	 * Check folder permissions.
	 *
	 * @return  array  Folders and their permissions.
	 *
	 * @since   2.3.10
	 */
	public function getFolderCheck()
	{
		jimport('joomla.filesystem.folder');
		$config = JFactory::getConfig();
		$tmp_path = JPath::clean($config->get('tmp_path'), '/');
		$folders = array();

		// Check the tmp/ path
		@touch($tmp_path . '/about.txt');
		$folders[$tmp_path] = is_writable($tmp_path . '/about.txt');
		@unlink($tmp_path . '/about.txt');

		// Check the tmp/com_csvi path
		@touch(CSVIPATH_TMP . '/about.txt');
		$folders[CSVIPATH_TMP] = is_writable(CSVIPATH_TMP . '/about.txt');
		@unlink(CSVIPATH_TMP . '/about.txt');

		// Check the tmp/com_csvi/export path
		@touch(CSVIPATH_TMP . '/export/about.txt');
		$folders[CSVIPATH_TMP . '/export'] = is_writable(CSVIPATH_TMP . '/export/about.txt');
		@unlink(CSVIPATH_TMP . '/export/about.txt');

		// Check the log path
		@touch(CSVIPATH_DEBUG . '/about.txt');
		$folders[CSVIPATH_DEBUG] = is_writable(CSVIPATH_DEBUG . '/about.txt');
		@unlink(CSVIPATH_DEBUG . '/about.txt');

		return $folders;
	}

	/**
	 * Create missing folders.
	 *
	 * @return  array  Result and result text for folder fix operation.
	 *
	 * @since   3.0
	 */
	public function fixFolder()
	{
		$jinput = JFactory::getApplication()->input;
		jimport('joomla.filesystem.folder');
		$result = false;

		// Get the folder name
		$folder = $jinput->get('folder', '', 'string');

		// Check if the folder exists
		if (!is_dir($folder))
		{
			// Try to create the folder
			JFolder::create($folder);

			// Check if the folder exists now
			if (!is_dir($folder))
			{
				$result = array(
					'result' => 'false',
					'resultText' => JText::sprintf('COM_CSVI_ABOUT_FOLDER_CANNOT_CREATE', $folder));
			}
		}

		if (!$result)
		{
			// Check if the folder is writable
			@touch($folder . '/about.txt');

			if (!is_writable($folder . '/about.txt'))
			{
				$result = array(
					'result' => 'false',
					'resultText' => JText::sprintf('COM_CSVI_ABOUT_FOLDER_CANNOT_WRITE', $folder)
				);

				if (!@chmod($folder, '0755'))
				{
					$result = array(
						'result' => 'false',
						'resultText' => JText::sprintf('COM_CSVI_ABOUT_FOLDER_CANNOT_MAKE_WRITABLE', $folder)
					);
				}
			}

			// Remove the test file
			@unlink($folder . '/about.txt');

			if (!$result)
			{
				$result = array('result' => 'true');
			}
		}

		return $result;
	}

	/**
	 * Get database changeset.
	 *
	 * @return  JSchemaChangeset  A JSchemaChangeset class.
	 *
	 * @since   5.6
	 */
	public function getChangeSet()
	{
		$folder = JPATH_ADMINISTRATOR . '/components/com_csvi/sql/updates/';
		$changeSet = JSchemaChangeset::getInstance(JFactory::getDbo(), $folder);

		return $changeSet;
	}

	/**
	 * Get version from #__schemas table.
	 *
	 * @return  mixed  The return value from the query, or null if the query fails.
	 *
	 * @since   5.6
	 */
	public function getSchemaVersion()
	{
		$db = JFactory::getDbo();
		$version = false;

		// Get the extension id first
		$query = $db->getQuery(true);
		$query->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'))
			->where($db->quoteName('element') . ' = ' . $db->quote('com_csvi'));
		$db->setQuery($query);
		$eid = $db->loadResult();

		if ($eid)
		{
			// Check if there is a version in the schemas table
			$query->clear()
				->select($db->quoteName('version_id'))
				->from($db->quoteName('#__schemas'))
				->where($db->quoteName('extension_id') . ' = ' . (int) $eid);
			$db->setQuery($query);
			$version = $db->loadResult();
		}

		return $version;
	}

	/**
	 * Fix database inconsistencies.
	 *
	 * @return  bool  Returns true.
	 *
	 * @since   5.7
	 */
	public function fix()
	{
		$changeSet = $this->getChangeSet();
		$changeSet->fix();

		return true;
	}

	/**
	 * Fix a messed up menu tree.
	 *
	 * @return  bool  Returns true.
	 *
	 * @since   6.5.7
	 *
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 */
	public function fixMenu()
	{
		$db = JFactory::getDbo();

		// Get the extension ID
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'))
			->where($db->quoteName('element') . ' = ' . $db->quote('com_csvi'));
		$db->setQuery($query);

		$extensionId = $db->loadResult();

		if (!$extensionId)
		{
			// No extension entry found but we exist as we have been summoned
			$columns = array(
				$db->quoteName('name'),
				$db->quoteName('type'),
				$db->quoteName('element'),
				$db->quoteName('folder'),
				$db->quoteName('client_id'),
				$db->quoteName('enabled'),
				$db->quoteName('access'),
				$db->quoteName('protected'),
				$db->quoteName('manifest_cache'),
				$db->quoteName('params'),
			);

			$values = $db->quote('csvi') . ', '
				. $db->quote('component') . ', '
				. $db->quote('com_csvi') . ', '
				. $db->quote('') . ', '
				. (int) 0 . ', '
				. (int) 1 . ', '
				. (int) 1 . ', '
				. (int) 0 . ', '
				. $db->quote('{"name":"CSVI","type":"component","creationDate":"0","author":"RolandD Cyber Produksi","copyright":"","authorEmail":"contact@csvimproved.com","authorUrl":"https:\/\/csvimproved.com\/","version":"0","description":"COM_CSVI_XML_DESCRIPTION","group":""}') . ', '
				. $db->quote('{}');

			$query->clear()
				->insert($db->quoteName('#__extensions'))
				->columns($columns)
				->values($values);
			$db->setQuery($query);

			try
			{
				$db->execute();

				$extensionId = $db->insertid();
			}
			catch (Exception $e)
			{
				throw new RuntimeException(JText::sprintf('COM_CSVI_FIX_MENU_CANNOT_CREATE_EXTENSION_ENTRY', $e->getMessage()));
			}
		}

		// Delete all the main menu entries
		$query->clear()
			->delete($db->quoteName('#__menu'))
			->where($db->quoteName('menutype') . ' = ' . $db->quote('main'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'))
			->where($db->quoteName('path') . ' LIKE ' . $db->quote('com_csvi%'));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			throw new RuntimeException(JText::sprintf('COM_CSVI_FIX_MENU_CANNOT_REMOVE_MENU_ENTRIES', $e->getMessage()));
		}

		// Create the menu entries
		$entries = array(
			'com_csvi' => array(
				'title' => 'COM_CSVI',
				'path' => 'com_csvi',
				'link' => 'index.php?option=com_csvi',
				'level' => 1,
			),
			'com_csvi_dashboard' => array(
				'title' => 'COM_CSVI_DASHBOARD',
				'path' => 'com_csvi/com_csvi_dashboard',
				'link' => 'index.php?option=com_csvi',
				'level' => 2,
			),
			'com_csvi_imports' => array(
				'title' => 'COM_CSVI_IMPORTS',
				'path' => 'com_csvi/com_csvi_imports',
				'link' => 'index.php?option=com_csvi&view=imports',
				'level' => 2,
			),
			'com_csvi_exports' => array(
				'title' => 'COM_CSVI_EXPORTS',
				'path' => 'com_csvi/com_csvi_exports',
				'link' => 'index.php?option=com_csvi&view=exports',
				'level' => 2,
			),
			'com_csvi_templates' => array(
				'title' => 'COM_CSVI_TEMPLATES',
				'path' => 'com_csvi/com_csvi_templates',
				'link' => 'index.php?option=com_csvi&view=templates',
				'level' => 2,
			),
			'com_csvi_maintenance' => array(
				'title' => 'COM_CSVI_MAINTENANCE',
				'path' => 'com_csvi/com_csvi_maintenance',
				'link' => 'index.php?option=com_csvi&view=maintenance',
				'level' => 2,
			),
			'com_csvi_logs' => array(
				'title' => 'COM_CSVI_LOGS',
				'path' => 'com_csvi/com_csvi_logs',
				'link' => 'index.php?option=com_csvi&view=logs',
				'level' => 2,
			),
			'com_csvi_about' => array(
				'title' => 'COM_CSVI_ABOUT',
				'path' => 'com_csvi/com_csvi_about',
				'link' => 'index.php?option=com_csvi&view=about',
				'level' => 2,
			),
		);
		$menuTable = new JTableMenu($db);
		$parentId = 1;
		$menu = array(
			'menutype' => 'main',
			'type' => 'component',
			'published' => 0,
			'access' => 1,
			'img' => 'class:component',
			'home' => 0,
			'component_id' => $extensionId,
			'client_id' => 1,
		);

		foreach ($entries as $alias => $entry)
		{
			$menu['title'] = $entry['title'];
			$menu['alias'] = $alias;
			$menu['path'] = $entry['path'];
			$menu['link'] = $entry['link'];
			$menu['parent_id'] = $parentId;

			$menuTable->setLocation($parentId, 'last-child');

			$menuTable->save($menu);

			if ($parentId === 1)
			{
				$parentId = $menuTable->get('id');
			}

			// Reset the table for the next entry
			$menuTable->reset();
			$menuTable->set('id', null);
		}

		return true;
	}
}
