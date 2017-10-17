<?php

/**
 * @version    SVN: <svn_id>
 * @package    Com_Tjlms
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of courses
 *
 * @since  1.0.0
 */
class JFormFieldVenue extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'venue';

	/**
	 * Fiedd to decide if options are being loaded externally and from xml
	 *
	 * @var		integer
	 * @since	2.2
	 */
	protected $loadExternally = 0;

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 *
	 * @since   11.4
	 */
	protected function getInput()
	{
		$db = JFactory::getDbo();
		$eventId = "";
		$post = JFactory::getApplication()->input;
		$eventId		= $post->get('id', '', 'INT');
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('*');
		$query->from('`#__jticketing_events` AS l');
		$query->order($db->escape('l.title ASC'));

		if (!empty($eventId))
		{
			$query->where('id = ' . $eventId);
		}

		$db->setQuery($query);
		$allUsers = $db->loadObject();
		$selectedvenueType = $allUsers->venue;
		$array_venue['venue'] = $allUsers->venue;
		$array_venue['event_online'] = $allUsers->online_events;
		$array_venue['created_by'] = $allUsers->created_by;

		require_once JPATH_COMPONENT . '/models/event.php';
		$JticketingModelEvent = new JticketingModelEvent;
		$result = $JticketingModelEvent->getAvailableVenue($array_venue);

		$options = array();

			foreach ($result as $u)
			{
				$options[] = JHtml::_('select.option', $u->id, $u->name);
			}

			return JHtml::_('select.genericlist', $options, $u->name, 'class="inputbox"  size="5"', 'value', 'text', $selectedvenueType);
	}
}
