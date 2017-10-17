<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Techjoomla
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
class JlikeModelDatabase extends InstallerModelDatabase
{
	/**
	 * Gets the changeset object.
	 *
	 * @return  JSchemaChangeset
	 */
	public function getItems()
	{
		$folder = JPATH_ADMINISTRATOR . '/components/com_jlike/sql/updates/';

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

	/**
	 * Add the entry in the reminder table for default reminder
	 *
	 * @return  void
	 */
	public function setDefaultReminder()
	{
		/*If certificate.php exists, do not oevrride it*/
		$reminder_file = JPATH_ADMINISTRATOR . '/components/com_jlike/reminder.php';

		$db = JFactory::getDbo();

		// Create default category on installation if not exists
		$sql = $db->getQuery(true)->select(1)
		->from($db->quoteName('#__jlike_reminders'))
		->setLimit(1);

		$db->setQuery($sql);
		$reminder_rows = $db->loadResult();

		if (!$reminder_rows)
		{
			include $reminder_file;

			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_tjlms/tables');
			$row = JTable::getInstance('reminder', 'JlikeTable', array());
			$row->created_by = JFactory::getUser()->id;
			$row->title = 'Default reminder';
			$row->email_template = $reminder['email_template'];
			$row->checked_out = JFactory::getUser()->id;
			$row->checked_out_time = JFactory::getDate()->toSql();
			$row->state = '1';
			$row->created_by = JFactory::getUser()->id;
			$row->modified_by = JFactory::getUser()->id;
			$row->days_before = '5';
			$row->content_type = 'com_tjlms.course';
			$row->subject = 'Reminder for {content_title}';

			$row->store();

			$reminder_id = $row->id;

			return 1;
		}
	}
}
