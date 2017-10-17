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
 * Speaker table.
 *
 * @package     Conference
 * @since       1.0
 */
class TableSpeaker extends JTable
{
	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver  $db  A database connector object.
	 *
	 * @since   1.0.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__conference_speakers', 'conference_speaker_id', $db);

		$this->setColumnAlias('published', 'enabled');
	}

	/**
	 * Method to perform sanity checks on the Table instance properties to ensure they are safe to store in the database.
	 *
	 * Child classes should override this method to make sure the data they are storing in the database is safe and as expected before storage.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @since   1.0.0
	 */
	public function check()
	{
		$result = true;

		// Make sure assigned events really exists and normalize the list
		if (!empty($this->conference_event_id))
		{
			if (is_array($this->conference_event_id))
			{
				$eventIds = $this->conference_event_id;
			}
			else
			{
				$eventIds = explode(',', $this->conference_event_id);
			}

			if (empty($eventIds))
			{
				$this->conference_event_id = '';
			}
			else
			{
				$events = array();
				$db       = $this->getDbo();
				$query = $db->getQuery(true)
					->select($db->quoteName('conference_event_id'))
					->from($db->quoteName('#__conference_events'));

				foreach ($eventIds as $eventId)
				{
					$query->clear('where')
						->where($db->quoteName('conference_event_id') . ' = ' . (int) $eventId);

					$conferenceEventId = $db->loadResult();

					if ($conferenceEventId > 0)
					{
						$events[] = $eventId;
					}
				}

				$this->conference_event_id = implode(',', $events);
			}
		}

		// Make sure we have a slug
		if (trim($this->slug) == '')
		{
			$this->slug = $this->title;
		}

		$this->slug = JApplicationHelper::stringURLSafe($this->slug, $this->language);

		// Get the user
		$user = JFactory::getUser();

		if ($this->conference_speaker_id === 0)
		{
			$this->created_by = $user->id;
			$this->created_on = JFactory::getDate()->toSql();
		}
		else
		{
			$this->modified_by = $user->id;
			$this->modified_on = JFactory::getDate()->toSql();
		}

		return $result;
	}
}