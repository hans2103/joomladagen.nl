<?php
/**
 * @version    SVN:
 * @package    Com_Jticketing
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright  2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Jticketing records.
 *
 * @since  1.6
 */
class JticketingModelAttendeeCoreFields extends JModelList
{
/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'ordering', 'a.`ordering`',
				'state', 'a.`state`',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Create the base select statement.
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_attendee_fields', 'a'));
		$query->where($db->quoteName('core') . ' = 1 ');

		return $query;
	}

	/**
	 * Gets user id for a particular vendor id
	 *
	 * @param   integer  $event_id  id for the event
	 * 
	 * @return integer   $db        attendee fields for that event
	 *
	 * @since	1.6
	 */
	public function getAttendeeFields($event_id)
	{
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__jticketing_attendee_fields'));
		$query->where($db->quoteName('eventid') . ' = ' . $db->quote($event_id));
		$db->setQuery($query);

		return $db->loadAssocList();
	}
}
