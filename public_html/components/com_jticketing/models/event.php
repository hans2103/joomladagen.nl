<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

/**
 * Model for getting event list
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelEvent extends JModelForm
{
	private $item = '';
	/**
	 * Method to populate state
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('com_jticketing');

		// Load state from the request userState on edit or from the passed variable on default
		if (JFactory::getApplication()->input->get('layout') == 'edit')
		{
			$id = JFactory::getApplication()->getUserState('com_jticketing.edit.event.id');
		}
		else
		{
			$id = JFactory::getApplication()->input->get('id');
			JFactory::getApplication()->setUserState('com_jticketing.edit.event.id', $id);
		}

		$this->setState('event.id', $id);

		// Load the parameters.
		$params       = $app->getParams();
		$params_array = $params->toArray();

		if (isset($params_array['item_id']))
		{
			$this->setState('event.id', $params_array['item_id']);
		}

		$this->setState('params', $params);
	}

	/**
	 * Method to get an ojbect.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed	Object on success, false on failure.
	 */
	public function &getData($id = null)
	{
		if (empty($this->item))
		{
			$this->item = false;

			if (empty($id))
			{
				$id = $this->getState('event.id');
			}

			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'eventform');
			$jtickeitngModelEventFrom = JModelLegacy::getInstance('eventform', 'JticketingModel');
			$eventData = $jtickeitngModelEventFrom->getItem($id);
		}

		if (!empty($eventData))
		{
			if ($eventData->venue == "0")
			{
				$eventData->event_address = $eventData->location;
			}
			else
			{
				$jticketingfrontendhelper = new jticketingfrontendhelper;
				$eventData->event_address = $jticketingfrontendhelper->getVenue($eventData->venue)->address;
			}
		}

		/*Event Book button condition checked here*/
		$jticketingEventsModel = JModelLegacy::getInstance('Events', 'JticketingModel');
		$eventBookButtonDetails = $jticketingEventsModel->getTJEventDetails($eventData->id);

		if (array_key_exists('buy_button', $eventBookButtonDetails))
		{
			$eventData->buy_link = $eventBookButtonDetails['buy_button_link'];
			$eventData->buy_button = $eventBookButtonDetails['buy_button'];
		}

		if (array_key_exists('enrol_button', $eventBookButtonDetails))
		{
			$eventData->enrol_link = $eventBookButtonDetails['enrol_link'];
			$eventData->enrol_button = $eventBookButtonDetails['enrol_button'];
		}

		$eventData->isboughtEvent = $eventBookButtonDetails['isboughtEvent'];

		// Get event organizer information
		JLoader::import('integrations', JPATH_SITE . '/components/com_jticketing/helpers');
		$jTicketingIntegrationsHelper  = new JTicketingIntegrationsHelper;
		$eventData->organizerAvatar = $jTicketingIntegrationsHelper->getUserAvatar($eventData->created_by);
		$eventData->organizerProfileUrl = $jTicketingIntegrationsHelper->getUserProfileUrl($eventData->created_by);

		$eventBookingStartdate = JFactory::getDate($eventData->booking_start_date)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_BOOK_BTN'));
		$eventBookingEndDate = JFactory::getDate($eventData->booking_end_date)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_BOOK_BTN'));

		$eventStartdate = JFactory::getDate($eventData->startdate)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_BOOK_BTN'));
		$eventEndDate = JFactory::getDate($eventData->enddate)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_BOOK_BTN'));
		$curr_date = JFactory::getDate()->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_BOOK_BTN'));

		if (count($eventData->tickettypes) == 1)
		{
			foreach ($eventData->tickettypes as $ticketInfo)
			{
				$eventData->eventPriceMaxValue = $ticketInfo->price;
				$eventData->eventPriceMinValue = $ticketInfo->price;
			}
		}
		else
		{
			// Added this for findind the event max ticket price and min ticket price.
			// For e.g If My event have 3 tickets and price is $100, $80, $120
			$maxTicketPrice = -9999999;
			$minTicketPrice = 9999999;

			foreach ($eventData->tickettypes as $ticketInfo)
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

			$eventData->eventPriceMaxValue = $maxTicketPrice;
			$eventData->eventPriceMinValue = $minTicketPrice;
		}

		if ($eventBookingEndDate < $curr_date)
		{
			// Booking date is closed
			$eventData->eventBookStatus = -1;
		}
		elseif ($eventBookingStartdate > $curr_date)
		{
			// Booking not started
			$eventData->eventBookStatus = 1;
		}
		else
		{
			// Booking is started
			$eventData->eventBookStatus = 0;
		}

		if ($eventEndDate < $curr_date)
		{
			// Event end date is closed
			$eventData->eventStatus = -1;
		}
		elseif ($eventStartdate > $curr_date)
		{
			// Event not started
			$eventData->eventStatus = 1;
		}
		else
		{
			// Event is started
			$eventData->eventStatus = 0;
		}

		if ($eventData->id)
		{
			$limit = 4;
			JLoader::import('event', JPATH_SITE . '/components/com_jticketing/helpers');
			$jteventHelper  = new JteventHelper;
			$eventAttendeeInfo = $jteventHelper->getEventAttendeeInfo($eventData->id, 0, $limit);
			$eventData->eventAttendeeInfo = $eventAttendeeInfo;

			$eventAttendeeInfoCount = $jteventHelper->getEventAttendeeInfo($eventData->id);
			$eventData->eventAttendeeCount = count($eventAttendeeInfoCount);
		}

		return $eventData;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getTable($type = 'Event', $prefix = 'JticketingTable', $config = array())
	{
		$this->addTablePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to check in an item.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return	boolean  True on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function checkin($id = null)
	{
		// Get the id.
		$id = (!empty($id)) ? $id : (int) $this->getState('event.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Attempt to check the row in.
			if (method_exists($table, 'checkin'))
			{
				if (!$table->checkin($id))
				{
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to check out an item.
	 *
	 * @param   integer  $id  The id of the row to check out.
	 *
	 * @return	boolean  True on success, false on failure.
	 *
	 * @since	1.6
	 */
	public function checkout($id = null)
	{
		// Get the user id.
		$id = (!empty($id)) ? $id : (int) $this->getState('event.id');

		if ($id)
		{
			// Initialise the table
			$table = $this->getTable();

			// Get the current user object.
			$user = JFactory::getUser();

			// Attempt to check the row out.
			if (method_exists($table, 'checkout'))
			{
				if (!$table->checkout($user->get('id'), $id))
				{
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_jticketing.event', 'event', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 *
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		$data = $this->getData();

		return $data;
	}

	/**
	 * Method to save form
	 *
	 * @param   string  $data  An optional array of data for the form to interogate.
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function save($data)
	{
		$jteventHelper = new jteventHelper;
		$dispatcher    = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$result = $dispatcher->trigger('jt_OnBeforeEventCreate', array($data));
		$id     = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('event.id');
		$state  = (!empty($data['state'])) ? 1 : 0;
		$user   = JFactory::getUser();

		if ($id)
		{
			// Check the user can edit this item
			$authorised = $user->authorise('core.edit', 'com_jticketing') || $authorised = $user->authorise('core.edit.own', 'com_jticketing');

			// The user cannot edit the state of the item.
			if ($user->authorise('core.edit.state', 'com_jticketing') !== true && $state == 1)
			{
				$data['state'] = 0;
			}
		}
		else
		{
			// Check the user can create new items in this section
			$authorised = $user->authorise('core.create', 'com_jticketing');

			// The user cannot edit the state of the item.
			if ($user->authorise('core.edit.state', 'com_jticketing') !== true && $state == 1)
			{
				$data['state'] = 0;
			}
		}

		if ($authorised !== true)
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$table = $this->getTable();

		if ($table->save($data) === true)
		{
			return $id;
		}
		else
		{
			return false;
		}

		$socialintegration = $com_params->get('integrate_with', 'none');
		$streamAddEvent    = $com_params->get('streamAddEvent', 0);

		if ($socialintegration != 'none')
		{
			$user     = JFactory::getUser();
			$libclass = $jteventHelper->getJticketSocialLibObj();

			// Add in activity.
			if ($streamAddEvent)
			{
				$action      = 'addevent';
				$link = JUri::root() . substr(JRoute::_('index.php?option=com_jticketing&view=event&id=' . $id), strlen(JUri::base(true)) + 1);
				$eventLink   = '<a class="" href="' . $link . '">' . $data['title'] . '</a>';
				$originalMsg = JText::sprintf('COM_JTICKETING_ACTIVITY_ADD_EVENT', $eventLink);
				$libclass->pushActivity($user->id, $act_type = '', $act_subtype = '', $originalMsg, $act_link = '', $title = '', $act_access = 0);
			}
		}

		// TRIGGER After create event
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$result = $dispatcher->trigger('jt_OnAfterEventCreate', array($data));
	}

	/**
	 * Method to delete event
	 *
	 * @param   string  $data  post data
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function delete($data)
	{
		$id = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('event.id');

		if (JFactory::getUser()->authorise('core.delete', 'com_jticketing') !== true)
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$table = $this->getTable();

		if ($table->delete($data['id']) === true)
		{
			return $id;
		}
		else
		{
			return false;
		}

		// TRIGGER After create event
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$result = $dispatcher->trigger('jt_OnAfterDeleteEvent', array($data, $id));

		return true;
	}

	/**
	 * Method to get category name
	 *
	 * @param   string  $id  id of category
	 *
	 * @return	string  category name
	 *
	 * @since   1.0
	 */
	public function getCategoryName($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		if (isset($id))
		{
			$query->select('title')->from('#__categories')->where('id = ' . $id);
			$db->setQuery($query);
		}

		return $db->loadObject();
	}

	/**
	 * Method to get the form for extra fields.This form file will be created by field manager.
	 *
	 * @param   array  $id  An optional array of data for the form to interogate.
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function getDataExtra($id = null)
	{
		if (empty($id))
		{
			$id = $this->getState('event.id');
		}

		if (empty($id))
		{
			return false;
		}

		$TjfieldsHelperPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$tjFieldsHelper = new TjfieldsHelper;
		$data               = array();
		$data['client']     = 'com_jticketing.event';
		$data['content_id'] = $id;
		$extra_fields_data = $tjFieldsHelper->FetchDatavalue($data);

		return $extra_fields_data;
	}

	/**
	 * Method to get ticket types
	 *
	 * @param   array  $id  An optional array of data for the form to interogate.
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function GetTicketTypes($id = null)
	{
		if (empty($id))
		{
			$id = $this->getState('event.id');
		}

		if (empty($id))
		{
			return false;
		}

		$jticketingmainhelper = new jticketingmainhelper;
		$GetTicketTypes       = $jticketingmainhelper->getEventDetails($id);

		return $GetTicketTypes;
	}

	/**
	 * Render booking HTML
	 *
	 * @param   int  $eventid  id of event
	 * @param   int  $userid   userid
	 *
	 * @return  booking HTML
	 *
	 * @since   1.0
	 */
	public function renderBookingHTML($eventid,$userid='')
	{
		$path = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';

		if (!class_exists('Jticketingfrontendhelper'))
		{
			JLoader::register('Jticketingfrontendhelper', $path);
			JLoader::load('Jticketingfrontendhelper');
		}

		$path = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';

		if (!class_exists('Jticketingmainhelper'))
		{
			JLoader::register('Jticketingmainhelper', $path);
			JLoader::load('Jticketingmainhelper');
		}

		$Jticketingfrontendhelper = new Jticketingfrontendhelper;
		$Jticketingmainhelper = new Jticketingmainhelper;
		$eventdata = $Jticketingmainhelper->getAllEventDetails($eventid);
		$HTML = $Jticketingfrontendhelper->renderBookingHTML($eventid, $userid, $eventdata);

		$HTML['online_html'] = '';

		if ($eventdata->online_events)
		{
			if ($HTML['isboughtEvent'] || $eventdata->created_by == $userid)
			{
				$params = json_decode($eventdata->jt_params, "true");

				// TRIGGER After create event
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('tjevents');
				$result = $dispatcher->trigger('generateMeeting_HTML', array($params,$eventdata));

				if (!empty($result['0']))
				{
					$HTML['online_html'] = $result['0'];
				}
			}
		}

		return $HTML;
	}

	/**
	 * Get video data - Added by Nidhi
	 *
	 * @param   integer  $vid   Video id
	 * @param   string   $type  Video provider e.g youtube, vimeo, upload
	 *
	 * @return   Object   video data
	 *
	 * since 1.7
	 */
	public function getVideoData($vid, $type)
	{
		if (!empty($vid) && !empty($type))
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select("*");
			$query->from($db->quoteName("#__jticketing_media_files"));
			$query->where($db->quoteName('id') . ' = ' . $vid);
			$db->setQuery($query);
			$results = $db->loadObject();

			return $results;
		}
	}

	/**
	 * This is add google event
	 *
	 * @param   int  $eventDetails  event details
	 *
	 * @return  $url
	 *
	 * @since   1.0
	 */
	public function addGoogleEvent($eventDetails)
	{
		$eventName = $eventDetails->title;
		$formattedEventName = str_replace(' ', '+', $eventName);
		$startDateTime = new DateTime($eventDetails->startdate);
		$googleStartDate = $startDateTime->format(DateTime::ISO8601);
		$removeExtraStart = substr($googleStartDate, 0, -4);
		$formattedStartDate = preg_replace("/[^ \w]+/", "", $removeExtraStart) . "Z";
		$endDateTime = new DateTime($eventDetails->enddate);
		$googleEndDate = $endDateTime->format(DateTime::ISO8601);
		$removeExtraEnd = substr($googleEndDate, 0, -4);
		$formattedEndDate = preg_replace("/[^ \w]+/", "", $removeExtraEnd) . "Z";
		$description = strip_tags($eventDetails->long_description);
		$location = $eventDetails->location;
		$url = "https://calendar.google.com/calendar/render?action=TEMPLATE&text=" .
		$formattedEventName . "&dates=" . $formattedStartDate . "/" . $formattedEndDate . "&details=" .
		$description . "&location=" . $location . "&pli=1&sf=true&output=xml#eventpage_6";

		return $url;
	}

	/**
	 * Method to get Graph Data
	 *
	 * @param   Integer  $durationVal  This show the duration for graph data
	 * @param   Integer  $eventId      This show the event id or user id
	 *
	 * @return  Json data
	 *
	 * @since  2.0
	 */
	public function getEventGarphData($durationVal, $eventId)
	{
		if ($durationVal == 0)
		{
			$graphDuration = 7;
		}
		elseif ($durationVal == 1)
		{
			$graphDuration = 30;
		}

		$todate = JFactory::getDate(date('Y-m-d'))->Format(JText::_('Y-m-d'));

		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$query = $db->getQuery(true);

		if ($durationVal == 0 || $durationVal == 1)
		{
			$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - ' . $graphDuration . ' days'));

			$query->select('SUM(o.amount) AS order_amount');
			$query->select('DATE(o.cdate) AS cdate');
			$query->select('COUNT(o.id) AS orders_count');
			$query->from($db->qn('#__jticketing_order', 'o'));
			$query->join('LEFT', $db->qn('#__jticketing_integration_xref', 'e') . ' ON (' . $db->qn('e.eventid') . ' = ' .
			$db->qn('o.event_details_id') . ')');
			$query->where($db->qn('e.eventid') . ' = ' . $db->quote($eventId) . ' AND ' . $db->qn('o.status') . ' = ' . $db->quote('C'));
			$query->where('DATE(' . $db->qn('o.cdate') . ')' . ' >= ' . $db->quote($backdate) . ' AND ' . 'DATE(' .
			$db->qn('o.cdate') . ')' . ' <= ' . $db->quote($todate)
			);
			$query->group('DATE(' . $db->qn('o.cdate') . ')');
			$query->order($db->qn('o.cdate') . 'DESC');

			$db->setQuery($query);
			$results = $db->loadObjectList();
		}
		elseif ($durationVal == 2)
		{
			$curdate    = date('Y-m-d');
			$back_year  = date('Y') - 1;
			$back_month = date('m') + 1;
			$backdate   = $back_year . '-' . $back_month . '-' . '01';

			$curdate    = date('Y-m-d');
			$back_year  = date('Y') - 1;
			$back_month = date('m') + 1;
			$backdate   = $back_year . '-' . $back_month . '-' . '01';

			$query->select('SUM(o.amount) AS order_amount');
			$query->select('MONTH(o.cdate) AS month_name');
			$query->select('YEAR(o.cdate) AS year_name');
			$query->select('COUNT(o.id) AS orders_count');
			$query->from($db->qn('#__jticketing_order', 'o'));
			$query->join('LEFT', $db->qn('#__jticketing_integration_xref', 'e') . ' ON (' . $db->qn('e.eventid') . ' = ' .
			$db->qn('o.event_details_id') . ')');
			$query->where($db->qn('e.eventid') . ' = ' . $db->quote($eventId) . ' AND ' . $db->qn('o.status') . ' = ' . $db->quote('C'));
			$query->where('DATE(' . $db->qn('o.cdate') . ')' . ' >= ' . $db->quote($backdate) . ' AND ' . 'DATE(' .
			$db->qn('o.cdate') . ')' . ' <= ' . $db->quote($todate)
			);

			$query->group($db->quote('year_name'));
			$query->group('month_name');
			$query->order($db->quote('YEAR( o.cdate )') . 'DESC');
			$query->order($db->quote('MONTH( o.cdate )') . 'DESC');

			$db->setQuery($query);
			$results = $db->loadObjectList();
		}

		return $results;
	}

	/**
	 * Methode viewMoreAttendee
	 *
	 * @param   integer  $eventId           event id
	 * @param   integer  $jticketing_index  jticketing_index
	 *
	 * @return   Object  Donor data
	 *
	 * since 1.7
	 */
	public function viewMoreAttendee($eventId, $jticketing_index)
	{
		JLoader::import('event', JPATH_SITE . '/components/com_jticketing/helpers');
		$jteventHelper  = new JteventHelper;
		$eventAttendeeInfo = $jteventHelper->getEventAttendeeInfo($eventId, $jticketing_index - 1, 10);

		$html = "";

		JLoader::import('main', JPATH_SITE . '/components/com_jticketing/helpers');
		$jticketingmainhelper  = new Jticketingmainhelper;
		$eventAttendee_html_view = $jticketingmainhelper->getViewpath('event', 'default_attendeelist');

		foreach ($eventAttendeeInfo as $this->eventAttendeeInfo)
		{
			ob_start();
			include $eventAttendee_html_view;
			$html .= ob_get_contents();
			ob_end_clean();

			$jticketing_index ++;
		}

		$result = array();
		$result['jticketing_index'] = $jticketing_index;
		$result['records']     = $html;

		return $result;
	}
}
