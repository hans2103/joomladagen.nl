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
 * Speakers model.
 *
 * @package     Conference
 * @since       1.0
 */
class ConferenceModelSpeakers extends JModelList
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
				'conference_speaker_id', 'speakers.conference_speaker_id',
				'ordering', 'speakers.ordering',
				'enabled', 'speakers.enabled',
				'title', 'speakers.title',
				'modified_on', 'speakers.modified_on',
				'events.title',
				'conference_event_id', 'events.conference_event_id',
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
	protected function populateState($ordering = 'speakers.title', $direction = 'ASC')
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
			->from($db->quoteName('#__conference_speakers', 'speakers'))
			->select(
				$db->quoteName(
					array(
						'speakers.conference_speaker_id',
						'speakers.user_id',
						'speakers.enabled',
						'speakers.title',
						'speakers.bio',
						'speakers.image',
						'speakers.facebook',
						'speakers.twitter',
						'speakers.googleplus',
						'speakers.linkedin',
						'speakers.website',
						'speakers.speakernotes',
						'speakers.locked_by',
						'speakers.modified_on',
						'speakers.conference_event_id',
					)
				)
			)
			->select($db->quoteName('events.title', 'event'))
			->join('LEFT OUTER', $db->quoteName('#__conference_events', 'events')
				. ' ON ' .
				$db->quoteName('events.conference_event_id') . ' = ' .
				$db->quoteName('speakers.conference_event_id')
			);

		// Filter by search field
		$search = $this->getState('filter.search');

		if ($search)
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('speakers.conference_speaker_id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$query->where($db->quoteName('speakers.title') . ' LIKE ' . $db->quote('%' . $search . '%'));
			}
		}

		// Filter by enabled
		$enabled = $this->getState('filter.enabled');

		if ('' !== $enabled && null !== $enabled)
		{
			$query->where($db->quoteName('speakers.enabled') . ' = ' . (int) $enabled);
		}

		// Filter by event
		$event = $this->getState('filter.conference_event_id');

		if ('' !== $event && null !== $event)
		{
			$query->where(
				'('.
				'('.$db->quoteName('speakers.conference_event_id') . ' = ' . $db->quote($event).') OR'.
				'('.$db->quoteName('speakers.conference_event_id') . ' LIKE ' . $db->quote('%,' . $event . ',%') . ') OR' .
				'('.$db->quoteName('speakers.conference_event_id') . ' LIKE ' . $db->quote($event . ',%').') OR' .
				'('.$db->quoteName('speakers.conference_event_id') . ' LIKE ' . $db->quote('%,' . $event) . ')' .
				')'
			);
		}

		// Add the list ordering clause.
		$query->order(
			$db->quoteName(
				$db->escape(
					$this->getState('list.ordering', 'speakers.title')
				)
			)
			. ' ' . $db->escape($this->getState('list.direction', 'ASC'))
		);

		return $query;
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.0.0
	 *
	 * @throws  RuntimeException
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (!is_array($items))
		{
			return $items;
		}

		$db           = $this->getDbo();
		$sessionQuery = $db->getQuery(true)
			->select('COUNT(' . $db->quoteName('conference_session_id') . ')')
			->from($db->quoteName('#__conference_sessions'));
		$eventQuery = $db->getQuery(true)
			->select($db->quoteName(array('conference_event_id', 'title')))
			->from($db->quoteName('#__conference_events'))
			->order($db->quoteName('title') . ' DESC');

		// Get the sessions for each item
		foreach ($items as $index => $item)
		{
			// Load the sessions
			$sessionQuery->clear('where')
				->where($db->quoteName('conference_speaker_id') . ' = ' . (int) $item->conference_speaker_id)
				->where($db->quoteName('enabled') . ' = 1');
			$db->setQuery($sessionQuery);

			$item->sessions = $db->loadResult();

			// Load the events
			$item->events = array();

			if ($item->conference_event_id)
			{
				$eventQuery->clear('where')
					->where($db->quoteName('conference_event_id') . ' IN (' . $item->conference_event_id . ')');
				$db->setQuery($eventQuery);
				$item->events = $db->loadObjectList();
			}

			$items[$index] = $item;
		}

		return $items;
	}
}
