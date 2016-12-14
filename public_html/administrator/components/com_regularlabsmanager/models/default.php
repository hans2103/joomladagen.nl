<?php
/**
 * @package         Regular Labs Extension Manager
 * @version         6.1.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2016 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Default Model
 */
class RegularLabsManagerModelDefault extends JModelList
{
	/**
	 * Get the extensions data
	 */
	public function getItems($ids = array())
	{
		$rows = $this->getItemsByXML();

		if (empty($rows))
		{
			return array();
		}

		$items = array();

		foreach ($rows as $row)
		{
			$item = $this->initItem();
			if (!isset($row['name']))
			{
				continue;
			}

			$item->name = $row['name'];
			$item->id   = isset($row['id']) ? $row['id'] : preg_replace('#[^a-z\-]#', '', str_replace('?', '-', strtolower($item->name)));

			if (!empty($ids) && !in_array($item->id, $ids))
			{
				continue;
			}

			$item->alias   = isset($row['alias']) ? $row['alias'] : $item->id;
			$item->element = isset($row['element']) ? $row['element'] : $item->alias;
			$item->types   = array();

			$types = array();

			if (isset($row['type']))
			{
				$types = explode(',', $row['type']);
			}

			$this->checkInstalled($item, $types);

			if (isset($row['old']) && $row['old'] && !$item->installed)
			{
				continue;
			}

			$item->error = isset($row['error']) ? $row['error'] : '';

			$items[$item->id] = $item;
		}

		return $items;
	}

	function storeKey()
	{
		$key = JFactory::getApplication()->input->get('key');

		$data = JComponentHelper::getComponent('com_regularlabsmanager');
		$data = json_decode(json_encode($data), true);

		if (is_null($data))
		{
			$data = array();
		}

		$data['params']['key'] = $key;

		$table = JTable::getInstance('extension');
		// Load the previous Data
		if (!$table->load($data['id']))
		{
			throw new RuntimeException($table->getError());
		}

		unset($data['id']);

		// Bind the data.
		if (!$table->bind($data))
		{
			throw new RuntimeException($table->getError());
		}

		// Check the data.
		if (!$table->check())
		{
			throw new RuntimeException($table->getError());
		}

		// Store the data.
		if (!$table->store())
		{
			throw new RuntimeException($table->getError());
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update('#__update_sites')
			->set($db->quoteName('extra_query') . ' = ' . $db->quote(''))
			->where($db->quoteName('location') . ' LIKE ' . $db->quote('http://download.regularlabs.com%'));
		$db->setQuery($query);
		$db->execute();

		if ($key)
		{
			$query->clear()
				->update('#__update_sites')
				->set($db->quoteName('extra_query') . ' = ' . $db->quote('k=' . $key))
				->where($db->quoteName('location') . ' LIKE ' . $db->quote('http://download.regularlabs.com%'))
				->where($db->quoteName('location') . ' LIKE ' . $db->quote('%&pro=1%'));
			$db->setQuery($query);
			$db->execute();
		}

		JFactory::getCache()->clean('_system');
	}

	/**
	 * Return an object list with items from the xml file
	 */
	function getItemsByXML()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			//return $this->cache[$store];
		}

		$items = array();

		jimport('joomla.filesystem.file');
		if (!JFile::exists(JPATH_COMPONENT . '/extensions.xml'))
		{
			return $items;
		}

		$file = JFile::read(JPATH_COMPONENT . '/extensions.xml');

		if (!$file)
		{
			return $items;
		}

		$xml_parser = xml_parser_create();
		xml_parse_into_struct($xml_parser, $file, $fields);
		xml_parser_free($xml_parser);

		foreach ($fields as $field)
		{
			if ($field['tag'] != 'EXTENSION'
				|| !isset($field['attributes'])
			)
			{
				continue;
			}

			$item = array();
			foreach ($field['attributes'] as $val => $key)
			{
				$item[strtolower($val)] = $key;
			}
			$items[] = $item;
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	/**
	 * Return an empty extension item object
	 */
	function initItem()
	{
		$item            = new stdClass;
		$item->id        = 0;
		$item->name      = '';
		$item->alias     = '';
		$item->element   = '';
		$item->installed = '';
		$item->version   = '';
		$item->pro       = 0;
		$item->haspro    = 1;
		$item->types     = array();
		$item->missing   = array();

		return $item;
	}

	/**
	 * Return an empty type object
	 */
	function initType()
	{
		$item       = new stdClass;
		$item->id   = 0;
		$item->type = '';
		$item->link = '';

		return $item;
	}

	/**
	 * Return an empty extension item
	 */
	function checkInstalled(&$item, $types = array())
	{
		jimport('joomla.filesystem.file');

		$file = '';
		$xml  = '';

		foreach ($types as $type)
		{
			$el       = $this->initType();
			$el->type = $type;
			list($xml, $client_id) = $this->getXML($type, $item->element);
			if (!$xml)
			{
				switch ($item->element)
				{
					case 'tabs':
						list($xml, $client_id) = $this->getXML($type, 'tabber');
						break;
					case 'sliders':
						list($xml, $client_id) = $this->getXML($type, 'slider');
						break;
				}
			}

			$el->client_id = $client_id;
			$el->link      = $this->getURL($type, $item->element, $item->name, $client_id);
			if (!$xml)
			{
				$item->missing[] = $type;
			}
			else
			{
				$el->id = $this->getID($type, $item->element);
				if (!$file)
				{
					$file = $xml;
				}
			}
			$item->types[$type] = $el;
		}

		if (!$file)
		{
			$item->missing = array();
		}
		else
		{
			$xml = JApplicationHelper::parseXMLInstallFile($file);
			if ($xml && isset($xml['version']))
			{
				// Fix wrong version numbers
				$xml['version'] = str_replace(
					array('4.4.0PROFREE', '4.4.0PROPRO', 'FREEFREE', 'FREEPRO', 'PROFREE', 'PROPRO'),
					array('1.0.1FREE', '1.0.1PRO', 'FREE', 'PRO', 'FREE', 'PRO'),
					$xml['version']
				);

				$item->installed = 1;
				$item->version   = str_replace(array('FREE', 'PRO'), '', $xml['version']);

				if (stripos($xml['version'], 'PRO') !== false)
				{
					$item->pro = 1;
				}

				if (!$item->version)
				{
					$item->version = '0.0.0';
				}
			}
		}
	}

	/**
	 * Get the extension url
	 */
	function getXML($type, $element)
	{
		$client_id = 1;
		$xml       = '';
		switch ($type)
		{
			case 'com':
				if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_' . $element . '/' . $element . '.xml'))
				{
					$xml = JPATH_ADMINISTRATOR . '/components/com_' . $element . '/' . $element . '.xml';
				}
				else if (JFile::exists(JPATH_SITE . '/components/com_' . $element . '/' . $element . '.xml'))
				{
					$client_id = 0;
					$xml       = JPATH_SITE . '/components/com_' . $element . '/' . $element . '.xml';
				}
				else if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_' . $element . '/com_' . $element . '.xml'))
				{
					$xml = JPATH_ADMINISTRATOR . '/components/com_' . $element . '/com_' . $element . '.xml';
				}
				else if (JFile::exists(JPATH_SITE . '/components/com_' . $element . '/com_' . $element . '.xml'))
				{
					$client_id = 0;
					$xml       = JPATH_SITE . '/components/com_' . $element . '/com_' . $element . '.xml';
				}
				break;
			case 'plg_system':
				if (JFile::exists(JPATH_PLUGINS . '/system/' . $element . '/' . $element . '.xml'))
				{
					$xml = JPATH_PLUGINS . '/system/' . $element . '/' . $element . '.xml';
				}
				else if (JFile::exists(JPATH_PLUGINS . '/system/' . $element . '.xml'))
				{
					$xml = JPATH_PLUGINS . '/system/' . $element . '.xml';
				}
				break;
			case 'plg_editors-xtd':
				if (JFile::exists(JPATH_PLUGINS . '/editors-xtd/' . $element . '/' . $element . '.xml'))
				{
					$xml = JPATH_PLUGINS . '/editors-xtd/' . $element . '/' . $element . '.xml';
				}
				else if (JFile::exists(JPATH_PLUGINS . '/editors-xtd/' . $element . '.xml'))
				{
					$xml = JPATH_PLUGINS . '/editors-xtd/' . $element . '.xml';
				}
				break;
			case 'mod':
				if (JFile::exists(JPATH_ADMINISTRATOR . '/modules/mod_' . $element . '/' . $element . '.xml'))
				{
					$xml = JPATH_ADMINISTRATOR . '/modules/mod_' . $element . '/' . $element . '.xml';
				}
				else if (JFile::exists(JPATH_SITE . '/modules/mod_' . $element . '/' . $element . '.xml'))
				{
					$client_id = 0;
					$xml       = JPATH_SITE . '/modules/mod_' . $element . '/' . $element . '.xml';
				}
				else if (JFile::exists(JPATH_ADMINISTRATOR . '/modules/mod_' . $element . '/mod_' . $element . '.xml'))
				{
					$xml = JPATH_ADMINISTRATOR . '/modules/mod_' . $element . '/mod_' . $element . '.xml';
				}
				else if (JFile::exists(JPATH_SITE . '/modules/mod_' . $element . '/mod_' . $element . '.xml'))
				{
					$client_id = 0;
					$xml       = JPATH_SITE . '/modules/mod_' . $element . '/mod_' . $element . '.xml';
				}
				break;
		}

		return array($xml, $client_id);
	}

	/**
	 * Get the extension url
	 */
	function getURL($type, $element, $name, $client_id = 1)
	{
		list($type, $folder) = explode('_', $type . '_');

		$link = '';
		switch ($type)
		{
			case 'com';
				RLFunctions::loadLanguage('com_' . $element . '.sys', '', true);
				$link = 'option=com_' . $element;
				break;
			case 'mod';
				RLFunctions::loadLanguage('mod_' . $element . '.sys', '', true);
				$link = 'option=com_modules&filter_client_id=' . $client_id . '&filter_module=mod_' . $element . '&filter_search=';
				break;
			case 'plg';
				RLFunctions::loadLanguage('plg_'. $folder . '_' . $element . '.sys', '', true);
				$name = JText::_($name);
				$name = preg_replace('#^(.*?)\?.*$#', '\1', $name);
				$link = 'option=com_plugins&filter_folder=' . $folder . '&filter_search=' . $name;
				break;
		}

		return $link;
	}

	/**
	 * Get the extension id
	 */
	function getID($type, $element)
	{
		$db = JFactory::getDbo();

		list($type, $folder) = explode('_', $type . '_');

		$query = $db->getQuery(true)
			->from('#__extensions as e')
			->select('e.extension_id');

		switch ($type)
		{
			case 'com';
				$query->where('e.type = ' . $db->quote('component'))
					->where('e.element = ' . $db->quote('com_' . $element));
				break;
			case 'mod';
				$query->where('e.type = ' . $db->quote('module'))
					->where('e.element = ' . $db->quote('mod_' . $element));
				break;
			case 'plg';
				$query->where('e.type = ' . $db->quote('plugin'))
					->where('e.element = ' . $db->quote($element))
					->where('e.folder = ' . $db->quote($folder));
				break;
		}

		$db->setQuery($query);

		return $db->loadResult();
	}
}
