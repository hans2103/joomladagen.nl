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
 * Joomla User access level import.
 *
 * @package     CSVI
 * @subpackage  JoomlaAccesslevel
 * @since       7.1.0
 */
class Accesslevel extends \RantaiImportEngine
{
	/**
	 * View levels table
	 *
	 * @var    \UsersTableAccesslevel
	 * @since  7.1.0
	 */
	private $accesslevelTable = null;

	/**
	 * The addon helper
	 *
	 * @var    \Com_UsersHelperCom_Users
	 * @since  7.1.0
	 */
	protected $helper = null;

	/**
	 * Start the product import process.
	 *
	 * @return  bool  True on success | false on failure.
	 *
	 * @since   7.1.0
	 */
	public function getStart()
	{
		// Process data
		foreach ($this->fields->getData() as $fields)
		{
			foreach ($fields as $name => $details)
			{
				$value = $details->value;

				// Check if the field needs extra treatment
				switch ($name)
				{
					case 'title':
						$this->setState('id', $this->helper->getAccessLevelId($value));
						$this->setState($name, $value);
						break;
					default:
						$this->setState($name, $value);
						break;
				}
			}
		}

		$this->loaded = true;

		// There must be a title
		if (!$this->getState('title', false))
		{
			$this->loaded = false;
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_MISSING_REQUIRED_FIELDS', 'title'));
		}

		// Load the current content data
		if ($this->accesslevelTable->load($this->getState('id')))
		{
			if (!$this->template->get('overwrite_existing_data'))
			{
				$this->log->add(\JText::sprintf('COM_CSVI_DATA_EXISTS_TITLE', $this->getState('title', '')));
				$this->loaded = false;
			}
		}

		return true;
	}

	/**
	 * Process a record.
	 *
	 * @return  bool  Returns true if all is OK | Returns false if no product SKU or product ID can be found.
	 *
	 * @since   7.1.0
	 */
	public function getProcessRecord()
	{
		if (!$this->loaded)
		{
			return false;
		}

		if (!$this->getState('id', false) && $this->template->get('ignore_non_exist'))
		{
			// Do nothing for new users when user chooses to ignore new users
			$this->log->addStats('skipped', \JText::sprintf('COM_CSVI_DATA_EXISTS_IGNORE_NEW', $this->getState('title', '')));
		}
		else
		{
			if ($this->getState('usergroup_name', false))
			{
				$userGroupNames = explode('|', $this->getState('usergroup_name', false));
				$groupIdArray   = array();

				foreach ($userGroupNames as $name)
				{
					$groupId = $this->helper->getAccessLevelGroupId($name, 'id', 'title');

					if (!$groupId)
					{
						$this->log->add('No user group found with name' . $name);
						$this->log->addStats('incorrect', \JText::sprintf('COM_CSVI_NO_USER_GROUP_FOUND', $name));

						return false;
					}

					$groupIdArray[] = $groupId;
				}

				if ($groupIdArray)
				{
					$rules = '[' . implode(',', $groupIdArray) . ']';
					$this->setState('rules', $rules);
				}
			}

			// Bind the data
			$this->accesslevelTable->bind($this->state);

			try
			{
				$this->accesslevelTable->check();
				$this->accesslevelTable->store();
				$this->log->add('Access levels added');
			}
			catch (\Exception $e)
			{
				$this->log->add('Cannot add access levels. Error: ' . $e->getMessage(), false);
				$this->log->addStats('incorrect', \JText::_('COM_CSVI_TABLE_ACESSLEVEL_ERROR'));

				return false;
			}
		}

		return true;
	}

	/**
	 * Load the necessary tables.
	 *
	 * @return  void.
	 *
	 * @since   7.1.0
	 */
	public function loadTables()
	{
		$this->accesslevelTable = $this->getTable('Accesslevel');
	}

	/**
	 * Clear the loaded tables.
	 *
	 * @return  void.
	 *
	 * @since   7.1.0
	 */
	public function clearTables()
	{
		$this->accesslevelTable->reset();
	}
}
