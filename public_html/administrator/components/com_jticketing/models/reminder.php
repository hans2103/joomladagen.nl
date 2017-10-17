<?php
/**
 * @version    SVN: <svn_id>
 * @package    JGive
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * jticketing Reminder Model
 *
 * @since  1.0
 */
class JticketingModelReminder extends JModelAdmin
{
	protected $text_prefix = 'COM_JTICKETING';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getTable($type = 'Reminder', $prefix = 'JticketingTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.reminder', 'reminder', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 *
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_jticketing.edit.reminder.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return	mixed  object on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   integer  $table  The id of the primary key.
	 *
	 * @return	mixed  object on success, false on failure.
	 *
	 * @since	1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '')
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__jticketing_reminder_types');
				$max = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	public function save($data)
	{
		$jteventHelper = new jteventHelper;
		$table = $this->getTable();
		$user = JFactory::getUser();
		$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('reminder.id');

		if ($id)
		{
			// Check the user can edit this item.
			$authorised = $user->authorise('core.edit', 'com_jticketing') || $authorised = $user->authorise('core.edit.own', 'com_jticketing');

			// The user cannot edit the state of the item.
			if ($user->authorise('core.edit.state', 'com_jticketing') !== true && $state == 1)
			{
				$data['com_jticketing'] = 0;
			}
		}
		else
		{
			// Check the user can create new items in this section.
			$authorised = $user->authorise('core.create', 'com_jticketing');

			// The user cannot edit the state of the item.
			if ($user->authorise('core.edit.state', 'com_jticketing') !== true && $state == 1)
			{
				$data['com_jticketing'] = 0;
			}
		}

		if ($authorised !== true)
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$table = $this->getTable();

		// Bind data
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Validate
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Attempt to save data
		if (parent::save($data))
		{
		}

		$id = (int) $this->getState($this->getName() . '.id');


		//$jteventHelper->updateReminderQueue();



		return $id;

	}

	/**
	 * To return a reminder days
	 *
	 * @param   integer  $days  reminder days
	 *
	 * @return  integer on success
	 *
	 * @since  1.7
	 */
	public function getDays($days)
	{
		$db = JFactory::getDBO();

		$qry = "SELECT `id` FROM #__jticketing_reminder_types WHERE state=1 AND `days` = " . $db->quote($db->escape(trim($days)));
		$db->setQuery($qry);
		$exists = $db->loadResult();

		if ($exists)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * To check for reminder days
	 *
	 * @param   integer  $days  reminder days
	 * @param   integer  $rid   The id of table is passed
	 *
	 * @return  integer on success
	 *
	 * @since  1.7
	 */
	public function getSelectDays($days,$rid)
	{
		$db = JFactory::getDBO();

		$qry = "SELECT `days` FROM #__jticketing_reminder_types WHERE id <>'{$rid}' AND `days` = " . $days;

		$db->setQuery($qry);
		$exists = $db->loadResult();


		if ($exists)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}
}
