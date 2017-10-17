<?php
/**
 * @package     Ekcontent
 * @subpackage  com_ekcontent
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/database.php';

/**
 * Jlike Manage Model
 *
 * @since  1.6
 */
class TjModelDatabase extends InstallerModelDatabase
{
	protected $extension_name = '';

	/**
	 * Gets the changeset object.
	 *
	 * @return  JSchemaChangeset
	 */
	public function getItems()
	{
		if ($this->extension_name)
		{
			$folder = JPATH_ADMINISTRATOR . '/components/' . $this->extension_name . '/sql/updates/';

			try
			{
				$changeSet = JSchemaChangeset::getInstance($this->getDbo(), $folder);
			}
			catch (RuntimeException $e)
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

				return false;
			}

			return $changeSet;
		}
	}

	/**
	 * + Techjoomla - Dummy override
	 * Fix schema version if wrong.
	 *
	 * @param   JSchemaChangeSet  $changeSet  Schema change set.
	 *
	 * @return   mixed  string schema version if success, false if fail.
	 */
	public function fixSchemaVersion($changeSet)
	{
		// We don't want to update anything related to core Joomla after db upgrade fix
		$schema = $this->getSchemaVersion();

		return $schema;
	}
}
