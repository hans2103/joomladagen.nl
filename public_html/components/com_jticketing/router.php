<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
/**
 * Class JTicketingRouter
 *
 * @since  3.3
 */
class JTicketingRouter extends JComponentRouterBase
{
	private  $views = array(
						'events','event','eventform','order',
						'orders','mytickets','mypayouts',
						'calendar','attendee_list','allticketsales','venues','venueform'
						);

	private  $specialViews = array('events', 'event', 'eventform','venues','venueform');

	private  $viewsNeedingEventId = array('event', 'eventform', 'order','venues','venueform');

	private  $viewsNeedingTmpl = array('');

	/**
	 * Build the route for the com_content component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return   array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since  1.5
	 */
	public function build(&$query)
	{
		$segments = array();

		// Get a menu item based on Itemid or currently active
		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		$params = JComponentHelper::getParams('com_jticketing');
		$db = JFactory::getDbo();

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $menu->getActive();
			$menuItemGiven = false;
		}
		else
		{
			$menuItem = $menu->getItem($query['Itemid']);

			$menuItemGiven = true;
		}

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_jticketing')
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		// Check if view is set.
		if (isset($query['view']))
		{
			$view = $query['view'];
		}
		else
		{
			// We need to have a view in the query or it is an invalid URL
			return $segments;
		}

		// Add the view only for normal views, for special its just the slug
		if (isset($query['view']) && !in_array($query['view'], $this->specialViews))
		{
			$segments[] = $query['view'];
			unset($query['view']);
		}

		// Handle the special views
		if ($view == 'events')
		{
			if (!empty($query['filter_events_cat']))
			{
				$catId = (int) $query['filter_events_cat'];

				if ($catId)
				{
					$category = JTable::getInstance('Category', 'JTable', array('dbo', $db));
					$category->load(array('id' => $catId, 'extension' => 'com_jticketing'));
					$segments[] = $category->alias;
					unset($query['filter_events_cat']);
					unset($query['view']);
				}
				else
				{
					$segments[] = '';
					unset($query['filter_events_cat']);
					unset($query['view']);
				}
			}

			unset($query['view']);
			unset($query['layout']);
		}

		if ($view == 'event')
		{
			if (isset($query['id']))
			{
				$eventTable = $this->_getEventRow($query['id'], 'id');
				$segments[] = $eventTable->alias;
				unset($query['id']);
				unset($query['view']);
			}
		}

		if ($view == 'eventform')
		{
			if (isset($query['id']))
			{
				$eventTable = $this->_getEventRow($query['id'], 'id');
				$segments[] = 'edit';
				$segments[] = $eventTable->alias;
				unset($query['id']);
				unset($query['view']);
			}
		}

		if ($view == 'venueform')
		{
			if (isset($query['id']))
			{
				$venueTable = $this->_getVenueRow($query['id'], 'id');
				$segments[] = 'edit';
				$segments[] = $venueTable->alias;
				unset($query['id']);
				unset($query['view']);
				unset($query['layout']);
			}
		}

		if ($view == 'attendee_list')
		{
			unset($query['event']);
		}

		if ($view == 'orders')
		{
			if (isset($query['orderid']))
			{
				$segments[] = $query['layout'];
				$segments[] = $query['orderid'];
				unset($query['orderid']);
				unset($query['layout']);
				unset($query['processor']);

				if (isset($query['sendmail']))
				{
					unset($query['sendmail']);
				}

				if (isset($query['email']))
				{
					$segments[] = $query['email'];
					unset($query['email']);
				}
			}
		}

		if (in_array($view, $this->viewsNeedingEventId) && isset($query['eventid']))
		{
			$eventTable = $this->_getEventRow($query['eventid'], 'id');

			if (!empty($eventTable->alias))
			{
				$segments[] = $eventTable->alias;
			}
			else
			{
				$segments[] = $eventTable->id;
			}

			unset($query['eventid']);
			unset($query['layout']);
		}

		// End Handle normal views
		if (in_array($view, $this->viewsNeedingTmpl))
		{
			unset($query['tmpl']);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since  1.5
	 */
	public function parse(&$segments)
	{
		$item = $this->menu->getActive();
		$vars = array();
		$db = JFactory::getDbo();

		// Count route segments
		$count = count($segments);

		if ($count == 1)
		{
			$categoryTable = JTable::getInstance('Category', 'JTable', array('dbo', $db));
			$categoryTable->load(array('alias' => $segments[0], 'extension' => 'com_jticketing'));

			if ($categoryTable->id)
			{
				$vars['view'] = 'events';
				$vars['filter_events_cat'] = $categoryTable->id;
			}
			elseif ($eventTableId = $this->_getEventRow($segments[0])->id)
			{
				$vars['view'] = 'event';
				$vars['id'] = $eventTableId;
			}
			elseif (in_array($segments[0], $this->views))
			{
				$vars['view'] = $segments[0];
			}
			else
			{
				$vars['view'] = 'event';
				$vars['id'] = 0;
			}
		}
		else
		{
			$vars['view'] = $segments[0];

			switch ($segments[0])
			{
				case 'orders':
				if (isset($segments[1]))
				{
					$vars['layout'] = $segments[1];
					$vars['orderid'] = $segments[2];

					if (isset($segments[3]))
					{
						$vars['email'] = $segments[3];
					}
				}
				break;

				default:
				if (in_array($segments[0], $this->viewsNeedingEventId))
				{
					$eventTable = $this->_getEventRow($segments[1]);
					$vars['eventid'] = $eventTable->id;
				}
				else
				{
					$venueTable = $this->_getVenueRow($segments[1]);
					$vars['view'] = 'venueform';
					$vars['id'] = $venueTable->id;
				}
			}

			if ($count = 2)
			{
				if ($segments[0] == 'edit' && $eventTableId = $this->_getEventRow($segments[1])->id)
				{
					$vars['view'] = 'eventform';
					$vars['id'] = $eventTableId;
				}
			}

			if (in_array($segments[0], $this->viewsNeedingTmpl))
			{
				$vars['tmpl'] = 'component';
			}
		}

		return $vars;
	}

	/**
	 * Get a event row based on alias or id
	 *
	 * @param   mixed   $event  The id or alias of the event to be loaded
	 * @param   string  $input  The field to match to load the event
	 *
	 * @return  object  The event JTable object
	 */
	private function _getEventRow($event, $input = 'alias')
	{
		$com_params = JComponentHelper::getParams('com_jticketing');
		$integration = $com_params->get('integration');

		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		if ($integration == 1)
		{
			$query->select($db->quoteName(array('xref.id', 'xref.eventid')));
			$query->from($db->quoteName('#__jticketing_integration_xref', 'xref'));
			$query->select($db->quoteName(array('comm.id', 'comm.title')));
			$query->join('LEFT', $db->quoteName('#__community_events', 'comm')
			. ' ON (' . $db->quoteName('xref.eventid') . ' = ' . $db->quoteName('comm.id') . ')');
			$query->where($db->quoteName('comm.id') . ' = ' . $db->quote($event));
			$db->setQuery($query);
			$events = $db->loadObject();

			$obj = new stdClass;
			$obj->id = $events->eventid;

			return $obj;
		}
		elseif ($integration == 2)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
			$table = JTable::getInstance('Event', 'JticketingTable', array('dbo', $db));
			$table->load(array($input => $event));

			return $table;
		}
		elseif ($integration == 3)
		{
			$query->select($db->quoteName(array('xref.id', 'xref.eventid')));
			$query->from($db->quoteName('#__jticketing_integration_xref', 'xref'));
			$query->select($db->quoteName(array('jevent.evdet_id', 'jevent.summary')));
			$query->join('LEFT', $db->quoteName('#__jevents_vevdetail', 'jevent')
			. ' ON (' . $db->quoteName('xref.eventid') . ' = ' . $db->quoteName('jevent.evdet_id') . ')');
			$query->where($db->quoteName('jevent.evdet_id') . ' = ' . $db->quote($event));
			$db->setQuery($query);
			$events = $db->loadObject();

			$obj = new stdClass;
			$obj->id = $events->eventid;

			return $obj;
		}
		elseif ($integration == 4)
		{
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_easysocial/tables');
			$table = JTable::getInstance('Cluster', 'SocialTable', array('dbo', $db));
			$table->load(array($input => $event));

			return $table;
		}
	}

	/**
	 * Get a venue row based on alias or id
	 *
	 * @param   mixed   $venue  The id or alias of the event to be loaded
	 * @param   string  $input  The field to match to load the event
	 *
	 * @return  object  The event JTable object
	 */
	private function _getVenueRow($venue, $input = 'alias')
	{
		$db    = JFactory::getDBO();
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');
		$table = JTable::getInstance('Venue', 'JticketingTable', array('dbo', $db));
		$table->load(array($input => $venue));

		return $table;
	}
}
