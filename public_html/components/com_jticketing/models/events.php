<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Model for getting event list
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelEvents extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since    1.6
	 */
	public function __construct($config = array())
	{
		require_once JPATH_SITE . "/components/com_jticketing/helpers/frontendhelper.php";
		$this->objFrontendhelper = new Jticketingfrontendhelper;

		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'title', 'events.title',
				'state', 'events.state',
				'created', 'created_on',
				'startdate', 'startdate',
				'enddate', 'enddate',
				'location', 'location',
				'category', 'catid',
				'booking_start_date','booking_start_date',
				'booking_end_date','booking_end_date'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto populate state
	 *
	 * @param   object  $ordering   ordering of list
	 * @param   object  $direction  direction of list
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// List state information
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);
		$limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);
		$this->setState('list.start', $limitstart);

		// Load the parameters. Merge Global and Menu Item params into new object
		$params     = $app->getParams();
		$menuParams = new JRegistry;

		if ($menu = $app->getMenu()->getActive())
		{
			$menuParams->loadString($menu->params);
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);

		if (empty($ordering))
		{
			$ordering = $mergedParams->get('default_sort_by_option');
		}

		if (empty($direction))
		{
			$direction = $mergedParams->get('filter_order_Dir');
		}

		$this->setState('filter_order', $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', ''));
		$this->setState('filter_order_Dir', $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'asc'));
		$this->setState('filter_creator', $app->getUserStateFromRequest($this->context . '.filter_creator', 'filter_creator', 0));
		$this->setState('filter_location', $app->getUserStateFromRequest($this->context . '.filter_location', 'filter_location', '', 'string'));
		$this->setState('filter_start_date', $app->getUserStateFromRequest($this->context . '.filter_start_date', 'filter_start_date', '', 'string'));
		$this->setState('filter_end_date', $app->getUserStateFromRequest($this->context . '.filter_end_date', 'filter_end_date', '', 'string'));
		$this->setState('online_events', $app->getUserStateFromRequest($this->context . '.online_events', 'online_events', '', 'string'));
		$this->setState('filter_events_cat', $app->getUserStateFromRequest($this->context . '.filter_events_cat', 'filter_events_cat', '', 'string'));
		$this->setState('filter_booking_start_date', $app->getUserStateFromRequest(
		$this->context . '.filter_booking_start_date', 'filter_booking_start_date', '', 'string'
		)
		);
		$this->setState('filter_booking_end_date', $app->getUserStateFromRequest(
		$this->context . '.filter_booking_end_date', 'filter_booking_end_date', '', 'string'
		)
		);
		$this->setState('layout', $app->getUserStateFromRequest($this->context . '.layout', 'layout', 'default', 'string'));

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 *
	 * @since	1.6
	 */
	public function getListQuery()
	{
		// Create a new query object.
		$db     = $this->getDbo();
		$app    = JFactory::getApplication();
		$query  = $db->getQuery(true);
		$userid = $app->input->get('jt_user_id', '');

		if (!empty($userid))
		{
			$user = JFactory::getUser($userid);
		}
		else
		{
			$user = JFactory::getUser();
		}

		$allowedViewLevels = JAccess::getAuthorisedViewLevels($user->id);
		$implodedViewLevels = implode('","', $allowedViewLevels);

		$jticketingmainhelper = new Jticketingmainhelper;
		$integration = $jticketingmainhelper->getIntegration();

		$events_to_show = $this->getState('events_to_show');

		if (!isset($events_to_show))
		{
			$events_to_show = $app->getParams()->get('events_to_show');
			$this->setState('events_to_show', $events_to_show);
		}

		$layout            = $this->getState('layout');
		$this->params      = $app->getParams('com_jticketing');
		$catid = "";
		$ordering          = $this->getState('filter_order');
		$order_dir         = $this->getState('filter_order_Dir');
		$creator           = $this->getState('filter_creator');
		$location          = $this->getState('filter_location');
		$filter_start_date = $this->getState('filter_start_date');
		$filter_end_date   = $this->getState('filter_end_date');
		$events_to_show    = $this->getState('events_to_show');
		$online_events     = $this->getState('online_events');
		$catid             = $this->getState('filter_events_cat');
		$filter_booking_start_date = $this->getState('filter_booking_start_date');
		$filter_booking_end_date = $this->getState('filter_booking_end_date');

		// Filter by search in title
		$search = $this->getState('filter.search');
		$search = (!empty($search)) ? $search : $app->input->get('search', '', 'STRING');

		if ($integration == 2)
		{
			// Select the required fields from the table.
			$query->select($this->getState('list.select', 'events.*'));
			$query->from('`#__jticketing_events` AS events');

			$query->select('v.name, v.online_provider, v.address, v.country, v.state_id, v.city, v.zipcode, v.params');
			$query->join('LEFT', '#__jticketing_venues AS v ON v.id=events.venue');

			// Join over the venue table
			$query->select('con.country AS coutryName');
			$query->join('LEFT', '#__tj_country AS con ON con.id=v.country');

			// Join over the venue table
			$query->select('r.region');
			$query->join('LEFT', '#__tj_region AS r ON r.id=v.state_id');

			// Join over the category 'catid'.
			$query->select(' c.title AS category');
			$query->join('LEFT', '#__categories AS c ON c.id = events.catid');

			// Join over the created by field 'created_by',
			$query->select(' u.name AS created_by_name');
			$query->join('LEFT', '#__users AS u ON u.id = events.created_by');

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$query->where('events.id = ' . (int) substr($search, 3));
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$query->where('( events.title LIKE ' . $search . ' OR events.long_description LIKE ' . $search . ' )');
				}
			}

			if ($layout == 'my')
			{
				$query->where("events.state<>-2");
				$query->where("events.created_by=" . $user->id . "");
			}
			elseif ($creator)
			{
				if ($creator)
				{
					$query->where("events.created_by='" . $creator . "'");
				}
			}
			else
			{
				$query->where("events.state=1");
			}

			// For location filter
			if ($location != '')
			{
				if ( ! filter_var($location, FILTER_VALIDATE_INT) )
				{
					$query->where($db->quoteName('events.location') . ' LIKE ' . $db->quote($location));
				}
				else
				{
					$query->where($db->quoteName('events.venue') . ' = ' . $db->quote($location));
				}
			}
			// For event type filter
			if ($online_events == '1' || $online_events == '0')
			{
				$query->where('events.online_events = ' . "'" . $online_events . "'");
			}

			// Filtering catid
			if ($catid)
			{
				$query->where("events.catid = '" . $catid . "'");
			}

			switch ($events_to_show)
			{
				case 'featured':
					$query->where("events.featured = 1");
					break;
				case '0':
					$query->where("events.enddate >= CURDATE() ");
					break;
				case '-1':
					$query->where("events.enddate <= CURDATE() ");
					break;
				default:
					break;
			}

			$dateField_start = "startdate";
			$dateField_end = "enddate";

			if (!empty($filter_start_date) and !empty($filter_end_date))
			{
				$query->where($dateField_start . 'BETWEEN' . $db->quote($filter_start_date) . ' AND ' . $db->quote($filter_end_date));
			}
			elseif (!empty($filter_start_date))
			{
				$query->where($dateField_start . ' >= ' . $db->quote($filter_start_date));
			}
			elseif (!empty($filter_end_date))
			{
				$query->where($dateField_end . ' <= ' . $db->quote($filter_end_date));
			}

			$bookingdateField_start = "booking_start_date";
			$bookingdateField_end = "booking_end_date";

			if (!empty($filter_booking_start_date) and !empty($filter_booking_end_date))
			{
				$query->where($bookingdateField_start . 'BETWEEN' . $db->quote($filter_booking_start_date) . ' AND ' . $db->quote($filter_booking_end_date));
			}
			elseif (!empty($filter_booking_start_date))
			{
				$query->where($bookingdateField_start . ' >= ' . $db->quote($filter_booking_start_date));
			}
			elseif (!empty($filter_booking_end_date))
			{
				$query->where($bookingdateField_end . ' <= ' . $db->quote($filter_booking_end_date));
			}

			// Get events with repect to access level
			$query->where('events.access IN ("' . $implodedViewLevels . '")');

			// Call TjfieldsHelper for the search filter module.
			$TjfieldsHelperPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

			if (!class_exists('TjfieldsHelper'))
			{
				JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
				JLoader::load('TjfieldsHelper');
			}

			$TjfieldsHelper = new TjfieldsHelper;
			$tjfieldItem_ids = $TjfieldsHelper->getFilterResults();
			$jinput = $app->input;
			$client = $jinput->get('client', '', 'string');

			if (!empty($client))
			{
				if ($tjfieldItem_ids != '-2')
				{
					$query->where(" events.id IN (" . $tjfieldItem_ids . ") ");
				}
			}

			// For ordering filter
			$filter_order     = $app->getUserStateFromRequest('com_jticketing' . 'events.filter_order', 'filter_order', 'startdate', 'cmd');
			$filter_order_Dir = $app->getUserStateFromRequest('com_jticketing' . 'events.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

			if (empty($filter_order))
			{
				$filter_order  		= 'startdate';
				$filter_order_Dir = 'desc';
			}

			$query->order($this->getState('list.ordering', $filter_order) . ' ' . $this->getState('list.direction', $filter_order_Dir));
		}
		elseif ($integration == 4)
		{
			// Filter by search in title
			$search = $this->getState('filter.search');
			$search = (!empty($search)) ? $search : $app->input->get('search', '', 'STRING');

			// Select the required fields from the table.
			$query->select($this->getState('list.select', 'events.*,events.address AS location,events.description	AS short_description'));
			$query->from('`#__social_clusters` AS events');

			// Join over the cluster id
			$query->select(' eventdet.start AS startdate,eventdet.end AS enddate');
			$query->join('INNER', '#__social_events_meta AS eventdet ON eventdet.cluster_id = events.id');

			// Join over the category 'catid'.
			$query->select(' c.title AS category');
			$query->join('INNER', '#__social_clusters_categories AS c ON c.id = events.category_id');

			// Join over the created by field 'creator_uid',
			$query->select(' u.name AS creator_uid_name');
			$query->join('INNER', '#__users AS u ON u.id = events.creator_uid');

			// Join over the cluster id
			$query->select(' event_images.square AS image');
			$query->join('LEFT', '#__social_avatars AS event_images ON event_images.uid = events.id');

			if (!empty($search))
			{
				if (stripos($search, 'id:') === 0)
				{
					$query->where('events.id = ' . (int) substr($search, 3));
				}
				else
				{
					$search = $db->Quote('%' . $db->escape($search, true) . '%');
					$query->where('( events.title LIKE ' . $search . ' )');
				}
			}

			if ($layout == 'my')
			{
				$query->where("events.state<>-2");
				$query->where("events.creator_uid=" . $user->id . "");
			}
			elseif ($creator)
			{
				if ($creator)
				{
					$query->where("events.creator_uid = '" . $creator . "'");
				}
			}
			else
			{
				$query->where("events.state=1");
			}

			// For location filter
			if ($location != '')
			{
				$query->where("( events.address LIKE '%{$location}%' )");
			}

			// For event type filter
			if ($online_events == '1' || $online_events == '0')
			{
				$query->where('events.online_events = ' . "'" . $online_events . "'");
			}

			// Filtering catid
			if ($catid)
			{
				$query->where("events.category_id = '" . $catid . "'");
			}

			switch ($events_to_show)
			{
				case 'featured':
					$query->where("events.featured = 1");
					break;
				case '0':
					$query->where("eventdet.end >= CURDATE() ");
					break;
				case '-1':
					$query->where("eventdet.end <= CURDATE() ");
					break;
				default:
					break;
			}

			// For ordering filter
			$filter_order     = $app->getUserStateFromRequest('com_jticketing' . 'events.filter_order', 'filter_order', 'created', 'cmd');
			$filter_order_Dir = $app->getUserStateFromRequest('com_jticketing' . 'events.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
			$query->group('events.id');

			if (empty($filter_order))
			{
				$filter_order  		= 'start';
				$filter_order_Dir = 'desc';
			}

			$query->order($this->getState('list.startdate', $filter_order) . ' ' . $this->getState('list.direction', $filter_order_Dir));
		}

		return $query;
	}

	/**
	 * This is used to get venue name
	 *
	 * @param   int  $id  order id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getVenueparams($id)
	{
		$db = JFactory::getDBO();
		$sql = "SELECT params FROM #__jticketing_venues WHERE #__jticketing_venues.id =" . $id;
		$db->setQuery($sql);
		$venueName = $db->loadResult();

		return $venueName;
	}

	/**
	 * Get ordering option
	 *
	 * @return  array  options array
	 *
	 * @since   1.0
	 */
	public function getOrderingOptions()
	{
		$mainframe = JFactory::getApplication();
		$default_sort_options = $mainframe->getParams()->get('default_sort_by_option');
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration = $jticketingmainhelper->getIntegration();

		if ($mainframe->isAdmin())
		{
			$filter_order = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', 'created', 'string');
		}
		else
		{
			$filter_order = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order',  $default_sort_options, 'string');
		}

		$this->setState('filter_order', $filter_order);
		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('COM_JTK_FILTER_SELECT_OREDERING'));
		$options[] = JHtml::_('select.option', 'title', JText::_('COM_JTK_TITLE'));
		$options[] = JHtml::_('select.option', 'created', JText::_('COM_JTK_CREATED'));
		$options[] = JHtml::_('select.option', 'startdate', JText::_('COM_JTK_START_DATE'));
		$options[] = JHtml::_('select.option', 'enddate', JText::_('COM_JTK_END_DATE'));

		// If Native integration then only below options
		if ($integration == '2')
		{
			$options[] = JHtml::_('select.option', 'modified', JText::_('COM_JTK_MODIFIED'));
			$options[] = JHtml::_('select.option', 'booking_start_date', JText::_('COM_JTK_BOOK_SDATE'));
			$options[] = JHtml::_('select.option', 'booking_end_date', JText::_('COM_JTK_BOOK_EDATE'));
		}

		return $options;
	}

	/**
	 * Get direction option
	 *
	 * @return  array  options array
	 *
	 * @since   1.0
	 */
	public function getOrderingDirectionOptions()
	{
		$mainframe = JFactory::getApplication();
		$filter_order_Dir = $mainframe->getParams()->get('filter_order_Dir');

		if ($mainframe->isAdmin())
		{
			$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', 'desc', 'string');
		}
		else
		{
			$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', $filter_order_Dir, 'string');
		}

		$this->setState('filter_order_Dir', $filter_order_Dir);
		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('COM_JTK_FILTER_SELECT_OREDERING_DIRECTION'));
		$options[] = JHtml::_('select.option', 'asc', JText::_('COM_JTK_ASCENDING'));
		$options[] = JHtml::_('select.option', 'desc', JText::_('COM_JTK_DESCENDING'));

		return $options;
	}

	/**
	 * Get creator option
	 *
	 * @return  array  options array
	 *
	 * @since   1.0
	 */
	public function getCreator()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration = $jticketingmainhelper->getIntegration();

		if ($integration == 2)
		{
			$query->select('DISTINCT(created_by) AS creator');
			$query->from('`#__jticketing_events` AS events');
		}

		if ($integration == 4)
		{
			$query->select('DISTINCT(creator_uid) AS creator');
			$query->from('`#__social_clusters` AS events');
		}

		$query->where("events.state = 1");
		$db->setQuery($query);
		$creator   = $db->loadColumn();
		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('COM_JTK_FILTER_CREATOR'));

		foreach ($creator as $val)
		{
			$uname     = JFactory::getUser($val)->username;
			$options[] = JHtml::_('select.option', $val, $uname);
		}

		return $options;
	}

	/**
	 * Get venue location option
	 *
	 * @return  array  options array
	 *
	 * @since   2.0
	 */
	public function getVenueLocations()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT(address) AS address, id as id');
		$query->from('`#__jticketing_venues` AS venues');
		$query->where($db->quoteName('venues.state') . ' = ' . $db->quote('1'));
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * Get location option
	 *
	 * @return  array  options array
	 *
	 * @since   1.0
	 */
	public function getLocation()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$jticketingmainhelper = new Jticketingmainhelper;
		$integration = $jticketingmainhelper->getIntegration();

		if ($integration == 2)
		{
			$query->select('DISTINCT(location)');
			$query->from($db->quoteName('#__jticketing_events'));
		}

		if ($integration == 4)
		{
			$query->select($db->quoteName('DISTINCT(address)'));
			$query->from($db->quoteName('#__social_clusters'));
		}

		$query->where($db->quoteName('state') . ' = ' . $db->quote('1'));
		$query->where($db->quoteName('venue') . ' = ' . $db->quote('0'));
		$db->setQuery($query);

		$location = $db->loadColumn();
		$venueLocations = $this->getVenueLocations();
		$options  = array();
		$options[] = JHtml::_('select.option', '', JText::_('COM_JTK_FILTER_LOCATION'));

		foreach ($location as $val)
		{
			$options[] = JHtml::_('select.option', $val, $val);
		}

		foreach ($venueLocations as $val)
		{
			$options[] = JHtml::_('select.option', $val['id'], $val['address']);
		}

		return $options;
	}

	/**
	 * Method to get a list of courses.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$eventDetails = $this->getTJEventDetails($item->id);

				if (array_key_exists('buy_button', $eventDetails))
				{
					$item->buy_link = $eventDetails['buy_button_link'];
					$item->buy_button = $eventDetails['buy_button'];
				}

				if (array_key_exists('enrol_button', $eventDetails))
				{
					$item->enrol_link = $eventDetails['enrol_link'];
					$item->enrol_button = $eventDetails['enrol_button'];
				}

				$item->isboughtEvent = $eventDetails['isboughtEvent'];

				/* Get Event Ticket type price here*/
				$jticketingmainhelper = new jticketingmainhelper;
				$getTicketTypes       = $jticketingmainhelper->getEventDetails($item->id);

				for ($i = 0; $i < count($getTicketTypes); $i++)
				{
					$item->availableSeats = $getTicketTypes[$i]->available;
					$item->availableCount = $getTicketTypes[$i]->count;
					$item->unlimitedSeats = $getTicketTypes[$i]->unlimited_seats;
				}

				if (count($getTicketTypes) == 1)
				{
					foreach ($getTicketTypes as $ticketInfo)
					{
						$item->eventPriceMaxValue = $ticketInfo->price;
						$item->eventPriceMinValue = $ticketInfo->price;
					}
				}
				else
				{
					$maxTicketPrice = -9999999;
					$minTicketPrice = 9999999;

					foreach ($getTicketTypes as $ticketInfo)
					{
						if ($ticketInfo->price > $maxTicketPrice)
						{
							$maxTicketPrice = $ticketInfo->price;
						}

						if ($ticketInfo->price < $minTicketPrice)
						{
							$minTicketPrice = $ticketInfo->price;
						}
					}

					$item->eventPriceMaxValue = $maxTicketPrice;
					$item->eventPriceMinValue = $minTicketPrice;
				}

				$eventBookingStartdate = JFactory::getDate($item->booking_start_date)->Format(JText::_('Y-m-d H:i:s'));
				$eventBookingEndDate = JFactory::getDate($item->booking_end_date)->Format(JText::_('Y-m-d H:i:s'));
				$curr_date = JFactory::getDate()->Format(JText::_('Y-m-d H:i:s'));

				if ($eventBookingEndDate < $curr_date)
				{
					// Booking date is closed
					$item->bookingStatus = -1;
				}
				elseif ($eventBookingStartdate > $curr_date)
				{
					// Booking not started
					$item->bookingStatus = 1;
				}
				else
				{
					// Booking is started
					$item->bookingStatus = 0;
				}

				$com_params     = JComponentHelper::getParams('com_jticketing');
				$integration    = $com_params->get('integration');

				if ($integration == '2')
				{
					if (empty($item->location) && $item->venue != '0')
					{
						$venueDetails = $this->objFrontendhelper->getVenue($item->venue);

						if (isset($venueDetails->online_provider) && $venueDetails->online_provider == 'plug_tjevents_adobeconnect')
						{
							$item->location = 'Adobe-' . $venueDetails->name;
						}
						else
						{
							$address = $item->address;
							$item->location = $venueDetails->name . ' - ' . $address;
						}
					}
				}

				$modelMediaXref = JModelLegacy::getInstance('MediaXref', 'JTicketingModel');
				$modelMedia = JModelLegacy::getInstance('Media', 'JTicketingModel');
				$mediaGallery = $modelMediaXref->getEventMedia($item->id, 'com_jticketing.event', 1);

				if ($mediaGallery)
				{
					$galleryFiles = array();

					foreach ($mediaGallery as $mediaXref)
					{
						$galleryFiles[] = $modelMedia->getItem($mediaXref->media_id);
					}

					$item->gallery = $galleryFiles;
				}

				$eventMainImage = $modelMediaXref->getEventMedia($item->id, 'com_jticketing.event', 0);

				if (isset($eventMainImage[0]->media_id))
				{
					$item->image = $modelMedia->getItem($eventMainImage[0]->media_id);
				}
			}
		}

		return $items;
	}

	/**
	 * Method to check buy button display or not.
	 *
	 * @param   integer  $eventID     event id.
	 *
	 * @param   integer  $dataFormat  dataFormat.
	 *
	 * @return  mixed  An array of events details, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function getTJEventDetails($eventID, $dataFormat = '')
	{
		require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";
		$jticketingmainhelper = new jticketingmainhelper;
		$userID = JFactory::getUser()->id;
		$eventdata = $jticketingmainhelper->getAllEventDetails($eventID);
		$returnData = $this->objFrontendhelper->renderBookingHTML($eventID, $userID, $eventdata, $dataFormat);

		return $returnData;
	}
}
