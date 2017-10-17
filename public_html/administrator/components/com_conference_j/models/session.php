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
 * Session model.
 *
 * @package     Conference
 * @since       1.0
 */
class ConferenceModelSession extends JModelAdmin
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
		$form = $this->loadForm('com_conference.session', 'session', array('control' => 'jform', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_conference.edit.session.data', array());

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
	 * @return  \JObject|boolean  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);
		$db   = $this->getDbo();

		// Get the speakers if available
		if ($item->conference_speaker_id)
		{
			$query = $db->getQuery(true)
				->select(
					$db->quoteName(
						array(
							'conference_speaker_id',
							'title'
						),
						array(
							'value',
							'text'
						)
					)
				)
				->from($db->quoteName('#__conference_speakers'))
				->where($db->quoteName('conference_speaker_id') . ' IN (' . $item->conference_speaker_id . ')');
			$db->setQuery($query);
			$item->conference_speaker_id = $db->loadObjectList();
		}

		return $item;
	}
}
