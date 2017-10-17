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
 * Levels model.
 *
 * @package     Conference
 * @since       1.0
 */
class ConferenceModelLevels extends JModelList
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
				'ordering', 'levels.ordering',
				'conference_level_id', 'levels.conference_level_id',
				'title', 'levels.title',
				'enabled', 'levels.enabled',
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
	protected function populateState($ordering = 'levels.ordering', $direction = 'DESC')
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
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.enabled');

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
			->from($db->quoteName('#__conference_levels', 'levels'))
			->select(
				$db->quoteName(
					array(
						'conference_level_id',
						'title',
						'label',
						'enabled',
						'ordering',
						'locked_by',
					)
				)
			);

		// Filter by search field
		$search = $this->getState('filter.search');

		if ($search)
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('levels.conference_level_id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$query->where($db->quoteName('levels.title') . ' LIKE ' . $db->quote('%' . $search . '%'));
			}
		}

		// Filter by enabled
		$enabled = $this->getState('filter.enabled');

		if ('' !== $enabled && null !== $enabled)
		{
			$query->where($db->quoteName('levels.enabled') . ' = ' . (int) $enabled);
		}

		// Add the list ordering clause.
		$query->order(
			$db->quoteName(
				$db->escape(
					$this->getState('list.ordering', 'levels.ordering')
				)
			)
			. ' ' . $db->escape($this->getState('list.direction', 'DESC'))
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

		/** @var ConferenceModelEvents $model */
		$model = JModelLegacy::getInstance('Events', 'ConferenceModel');

		// Get the sessions for each item
		foreach ($items as $index => $item)
		{
			$item->sessions = $model->getSessionCount($item->conference_level_id, 'level');
			$items[$index] = $item;
		}

		return $items;
	}
}
