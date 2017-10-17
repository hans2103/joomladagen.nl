<?php
/*<?php
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
 * Sessions model.
 *
 * @package     Conference
 * @since       1.0
 */
class ConferenceModelSessions extends JModelList
{
	/**
	 * Construct the class.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'conference_session_id', 'sessions.conference_session_id',
				'enabled', 'sessions.enabled',
				'listview', 'sessions.listview',
				'title', 'sessions.title',
				'conference_day_id', 'sessions.conference_day_id',
				'conference_speaker_id', 'sessions.conference_speaker_id',
				'conference_level_id', 'sessions.conference_level_id',
				'conference_room_id', 'sessions.conference_room_id',
				'conference_event_id', 'sessions.conference_event_id',
				'modified_on', 'sessions.modified_on',
				'start_time', 'slots.start_time',
				'events.title',
				'sessions.description',
				'sessions.slides',
				'sessions.video',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function populateState($ordering = 'slots.start_time', $direction = 'DESC')
	{
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Add the list state to the store id.
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');
		$id .= ':' . $this->getState('filter.enabled');
		$id .= ':' . $this->getState('filter.status');
		$id .= ':' . $this->getState('filter.conference_level_id');
		$id .= ':' . $this->getState('filter.conference_room_id');
		$id .= ':' . $this->getState('filter.conference_day_id');
		$id .= ':' . $this->getState('filter.conference_slot_id');
		$id .= ':' . $this->getState('filter.conference_event_id');

		return md5($this->context . ':' . $id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery  The query to execute.
	 *
	 * @since   1.0.0
	 *
	 * @throws  RuntimeException
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();

		// Get the parent query
		$query = $db->getQuery(true)
			->from($db->quoteName('#__conference_sessions', 'sessions'))
			->select(
				$db->quoteName(
					array(
						'sessions.conference_session_id',
						'sessions.ordering',
						'sessions.title',
						'sessions.listview',
						'sessions.enabled',
						'sessions.description',
						'sessions.slides',
						'sessions.video',
						'sessions.modified_on',
						'sessions.locked_by',
						'sessions.conference_speaker_id',
						'levels.conference_level_id',
						'rooms.conference_room_id',
						'slots.conference_slot_id',
					)
				)
			)
			->select(
				$db->quoteName(
					array(
						'levels.title',
						'levels.label',
						'rooms.title',
						'days.title',
						'slots.start_time',
						'slots.end_time',
						'slots.conference_day_id',
						'speakers.image',
						'speakers.user_id',
						'events.title',
						'events.conference_event_id'
					)
					, array(
						'level',
						'level_label',
						'room',
						'day',
						'start_time',
						'end_time',
						'day_id',
						'speakerimage',
						'user_id',
						'event',
						'conference_event_id'
					)
				)
			)
			->join('LEFT OUTER', $db->quoteName('#__conference_speakers', 'speakers')
				. ' ON ' .
				$db->quoteName('speakers.conference_speaker_id') . ' = ' .
				$db->quoteName('sessions.conference_speaker_id')
			)
			->join('LEFT OUTER', $db->quoteName('#__conference_levels', 'levels')
				. ' ON ' .
				$db->quoteName('levels.conference_level_id') . ' = ' .
				$db->quoteName('sessions.conference_level_id')
			)
			->join('LEFT OUTER', $db->quoteName('#__conference_rooms', 'rooms')
				. ' ON ' .
				$db->quoteName('rooms.conference_room_id') . ' = ' .
				$db->quoteName('sessions.conference_room_id')
			)
			->join('LEFT OUTER', $db->quoteName('#__conference_slots', 'slots')
				. ' ON ' .
				$db->quoteName('slots.conference_slot_id') . ' = ' .
				$db->quoteName('sessions.conference_slot_id')
			)
			->join('LEFT OUTER', $db->quoteName('#__conference_days', 'days')
				. ' ON ' .
				$db->quoteName('days.conference_day_id') . ' = ' .
				$db->quoteName('slots.conference_day_id')
			)
			->join('LEFT OUTER', $db->quoteName('#__conference_events', 'events')
				. ' ON ' .
				$db->quoteName('events.conference_event_id') . ' = ' .
				$db->quoteName('days.conference_event_id')
			);

		// Filter by search field
		$search = $this->getState('filter.search');

		if ($search)
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('sessions.conference_session_id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$query->where($db->quoteName('sessions.title') . ' LIKE ' . $db->quote('%' . $search . '%'));
			}
		}

		// Filter by enabled
		$enabled = $this->getState('filter.enabled');

		if ('' !== $enabled && null !== $enabled)
		{
			$query->where($db->quoteName('sessions.enabled') . ' = ' . (int) $enabled);
		}

		// Filter by status
		$status = $this->getState('filter.status');

		if ('' !== $status && null !== $status)
		{
			$query->where($db->quoteName('sessions.status') . ' = ' . (int) $status);
		}

		// Filter by speaker
		$speaker = $this->getState('filter.conference_speaker_id');

		if (is_array($speaker) && !empty($speaker[0]))
		{
			$query->where($db->quoteName('sessions.conference_speaker_id') . ' IN (' . implode(',', $speaker) . ')');
		}

		// Filter by level
		$level = $this->getState('filter.conference_level_id');

		if ('' !== $level && null !== $level)
		{
			$query->where($db->quoteName('sessions.conference_level_id') . ' = ' . (int) $level);
		}

		// Filter by room
		$room = $this->getState('filter.conference_room_id');

		if ('' !== $room && null !== $room)
		{
			$query->where($db->quoteName('sessions.conference_room_id') . ' = ' . (int) $room);
		}

		// Filter by day
		$day = $this->getState('filter.conference_day_id');

		if ('' !== $day && null !== $day)
		{
			$query->where($db->quoteName('slots.conference_day_id') . ' = ' . (int) $day);
		}

		// Filter by slot
		$slot = $this->getState('filter.conference_slot_id');

		if ('' !== $slot && null !== $slot)
		{
			$query->where($db->quoteName('sessions.conference_slot_id') . ' = ' . (int) $slot);
		}

		// Filter by event
		$event = $this->getState('filter.conference_event_id');

		if ('' !== $event && null !== $event)
		{
			$query->where($db->quoteName('events.conference_event_id') . ' = ' . (int) $event);
		}

		// Add the list ordering clause
		$listOrder = $this->getState('list.ordering', 'slots.start_time');

		switch ($listOrder)
		{
			case 'sessions.description':
				$sortOrder = 'LENGTH(' . $db->quoteName('sessions.description') . ')';
				break;
			case 'sessions.slides':
				$sortOrder = 'LENGTH(' . $db->quoteName('sessions.slides') . ')';
				break;
			case 'sessions.video':
				$sortOrder = 'LENGTH(' . $db->quoteName('sessions.video') . ')';
				break;
			case 'slots.start_time':
				$sortOrder = $db->quoteName('days.date') . ' ' . $db->escape($this->getState('list.direction', 'DESC')) . ', ' .
				$db->quoteName(
					$db->escape(
						$this->getState('list.ordering', 'slots.start_time')
					)
				);
				break;
			default:
				$sortOrder = $db->quoteName(
					$db->escape(
						$this->getState('list.ordering', 'slots.start_time')
					)
				);
				break;
		}

		if ($sortOrder)
		{
			$query->order(
				$sortOrder . ' ' . $db->escape($this->getState('list.direction', 'DESC'))
			);
		}

		return $query;
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		// Get the items
		$items = parent::getItems();

		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('conference_speaker_id', 'title')))
			->from($db->quoteName('#__conference_speakers'));

		// Load the speakers for each session
		foreach ($items as $key => $item)
		{
			$item->speakers = array();

			if ($item->conference_speaker_id)
			{
				$query->clear('where')
					->where($db->quoteName('conference_speaker_id') . ' IN (' . $item->conference_speaker_id . ')');
				$db->setQuery($query);
				$item->speakers = $db->loadObjectList();
			}

			$items[$key] = $item;
		}

		return $items;
	}
}
