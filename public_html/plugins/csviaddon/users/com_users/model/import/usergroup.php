<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaUser
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace users\com_users\model\import;

defined('_JEXEC') or die;

/**
 * Joomla User group import.
 *
 * @package     CSVI
 * @subpackage  JoomlaUser
 * @since       6.5.0
 */
class Usergroup extends \RantaiImportEngine
{
	/**
	 * User table
	 *
	 * @var    \UsersTableUsergroup
	 * @since  6.0
	 */
	private $usergroupTable = null;

	/**
	 * The addon helper
	 *
	 * @var    \Com_UsersHelperCom_Users
	 * @since  6.5.0
	 */
	protected $helper = null;

	/**
	 * Start the product import process.
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   6.0
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
					case 'parent_name':
						$this->setState('parent_id', $this->getParentId($value));
						break;
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		// There must be a title
		if ($this->getState('title', false))
		{
			$this->loaded = true;

			if (!$this->getState('id', false))
			{
				$this->setState('id', $this->helper->getUserGroupId());
			}

			// Load the current content data
			if ($this->usergroupTable->load($this->getState('id')))
			{
				if (!$this->template->get('overwrite_existing_data'))
				{
					$this->log->add(\JText::sprintf('COM_CSVI_DATA_EXISTS_PRODUCT_SKU', $this->getState('title', '')));
					$this->loaded = false;
				}
			}
		}
		else
		{
			$this->loaded = false;

			$this->log->addStats('skipped', \JText::_('COM_CSVI_MISSING_REQUIRED_FIELDS'));
		}

		return true;
	}

	/**
	 * Process a record.
	 *
	 * @return  bool  Returns true if all is OK | Returns false if no product SKU or product ID can be found.
	 *
	 * @since   6.0
	 */
	public function getProcessRecord()
	{
		if ($this->loaded)
		{
			$usergroup_delete = $this->getState('usergroup_delete', 'N');
			$id = $this->getState('id', false);

			// User wants to delete the product
			if ($id && $usergroup_delete == 'Y')
			{
				$this->usergroupTable->deleteUsergroup($id);
			}
			elseif (!$this->getState('id', false) && $this->template->get('ignore_non_exist'))
			{
				// Do nothing for new users when user chooses to ignore new users
				$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('title', '')));
			}
			else
			{
				// Bind the data
				$this->usergroupTable->bind($this->state);

				// Store the product
				if (!$this->usergroupTable->storeUsergroup())
				{
					return false;
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
		$this->usergroupTable = $this->getTable('Usergroup');
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
		$this->usergroupTable->reset();
	}

	/**
	 * Load the ID of the parent group.
	 *
	 * @param   string  $name  The name of the parent group.
	 *
	 * @return  int  The ID of the parent group.
	 *
	 * @since   6.5.0
	 */
	private function getParentId($name)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('id'))
			->from($this->db->quoteName('#__usergroups'))
			->where($this->db->quoteName('title') . ' = ' . $this->db->quote($name));
		$this->db->setQuery($query);

		$id = $this->db->loadResult();

		if (!$id)
		{
			$id = 0;
		}

		return $id;
	}
}
