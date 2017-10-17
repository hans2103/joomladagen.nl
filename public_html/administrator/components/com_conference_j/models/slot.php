<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://joomladagen.nl
 */

defined('_JEXEC') or die;

/**
 * Slot model.
 *
 * @package     Conference
 * @since       1.0
 */
class ConferenceModelSlot extends JModelAdmin
{
	/**
	 * Get the form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success | False on failure.
	 *
	 * @since   4.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_conference.slot', 'slot', array('control' => 'jform', 'load_data' => $loadData));

		if (0 === count($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The data for the form..
	 *
	 * @since   1.0
	 *
	 * @throws  Exception
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_conference.edit.slot.data', array());

		if (0 === count($data))
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
	 * @return  JObject|boolean  Object on success, false on failure.
	 *
	 * @since   1.0
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		// Get the user
		$user = JFactory::getUser();

		// Date conversion based on timezone
		$date = JFactory::getDate($item->start_time, 'UTC');
		$date->setTimezone($user->getTimezone());
		$item->start_time = $date->format('H:i', true);

		$date = JFactory::getDate($item->end_time, 'UTC');
		$date->setTimezone($user->getTimezone());
		$item->end_time = $date->format('H:i', true);

		return $item;
	}


	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		// Get the user
		$user = JFactory::getUser();

		// Date conversion based on timezone
		$date = JFactory::getDate($data['start_time'], $user->getTimezone());
		$data['start_time'] = $date->format('H:i');

		$date = JFactory::getDate($data['end_time'], $user->getTimezone());
		$data['end_time'] = $date->format('H:i');

		if ($data['conference_slot_id'] === 0)
		{
			$data['created_by'] = $user->id;
			$data['created_on'] = JFactory::getDate()->toSql();
		}
		else
		{
			$data['modified_by'] = $user->id;
			$data['modified_on'] = JFactory::getDate()->toSql();
		}

		return parent::save($data);
	}
}
