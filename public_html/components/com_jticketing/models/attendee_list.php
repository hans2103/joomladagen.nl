<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die(';)');
jimport('joomla.application.component.model');
jimport('joomla.database.table.user');

/**
 * Class for Jticketing Attendee List Model
 *
 * @package  JTicketing
 * @since    1.5
 */
class JticketingModelattendee_List extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'ordertbl.`id`',
				'order_id', 'ordertbl.`order_id`',
				'status', 'ordertbl.`status`',
				'name', 'ordertbl.`name`',
				'email', 'ordertbl.`email`',
			);
		}

		$this->jticketingmainhelper = new jticketingmainhelper;
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get pagination request variables
		$limit      = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'));
		$limitstart = $app->getUserStateFromRequest('limitstart', 'limitstart', 0);

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$filterOrder     = $app->getUserStateFromRequest($this->context . 'filter_order', 'filter_order', 'title', 'cmd');
		$this->setState('filter_order', $filterOrder);

		$filterOrderDir = $app->getUserStateFromRequest($this->context . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$this->setState('filter_order_Dir', $filterOrderDir);

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		if ($app->isAdmin())
		{
			$searchEvent = $app->getUserStateFromRequest($this->context . '.filter.events', 'filter_event', '', 'string');
			$this->setState('filter.events', $searchEvent);

			$searchPaymentStatusList = $app->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', '', 'string');
			$this->setState('filter.status', $searchPaymentStatusList);
		}
		else
		{
			$searchEvent = $app->getUserStateFromRequest($this->context . '.search_event_list', 'search_event_list', '', 'string');
			$this->setState('search_event_list', $searchEvent);

			$searchPaymentStatusList = $app->getUserStateFromRequest($this->context . '.search_paymentStatuslist', 'search_paymentStatuslist', '', 'string');
			$this->setState('search_paymentStatuslist', $searchPaymentStatusList);
		}

		// List state information.
		parent::populateState();
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
		$app = JFactory::getApplication();
		$layout = $app->get('layout', '', 'STRING');
		$user = JFactory::getUser();
		$integration = $this->jticketingmainhelper->getIntegration();

		// Create a new query object.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(
				array(
					'o.customer_note, o.amount as amount, check.checkin, o.order_id as order_id,
					o.email as buyeremail, i.eventid as evid, o.cdate, e.attendee_id, o.id, o.status,
					o.name, o.event_details_id, o.user_id, e.type_id, e.id AS order_items_id, e.ticketcount AS ticketcount,
					f.title AS ticket_type_title, f.price AS amount, (f.price * e.ticketcount) AS totalamount,
					user.firstname, user.lastname'
					)
				);
		$query->from($db->qn('#__jticketing_order', 'o'));
		$query->join('LEFT', $db->qn('#__jticketing_order_items', 'e') . 'ON (' . $db->qn('o.id') . ' = ' . $db->qn('e.order_id') . ')');
		$query->join('LEFT', $db->qn('#__jticketing_integration_xref', 'i') . 'ON (' . $db->qn('i.id') . ' = ' . $db->qn('o.event_details_id') . ')');
		$query->join('LEFT', $db->qn('#__jticketing_checkindetails', 'check') . 'ON (' . $db->qn('check.ticketid') . ' = ' . $db->qn('e.id') . ')');
		$query->join('INNER', $db->qn('#__jticketing_types', 'f') . 'ON (' . $db->qn('f.id') . ' = ' . $db->qn('e.type_id') . ')');
		$query->join('INNER', $db->qn('#__jticketing_users', 'user') . 'ON (' . $db->qn('o.id') . ' = ' . $db->qn('user.order_id') . ')');

		if ($integration == 1)
		{
			$query->select(array('comm.location, comm.title, comm.avatar as image'));
			$query->join('LEFT', $db->qn('#__community_events', 'comm') . 'ON (' . $db->qn('comm.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_community"));
		}
		elseif ($integration == 2)
		{
			$query->select(array('b.title, b.image, b.short_description, b.location'));
			$query->join('LEFT', $db->qn('#__jticketing_events', 'b') . 'ON (' . $db->qn('b.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_jticketing"));
		}
		elseif ($integration == 3)
		{
			$query->select(array('evntstbl.location as location, evntstbl.summary AS title'));
			$query->join('LEFT', $db->qn('#__jevents_vevdetail', 'evntstbl') . 'ON (' . $db->qn('evntstbl.evdet_id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->join('LEFT', $db->qn('#__jevents_vevent', 'ev') . 'ON (' . $db->qn('evntstbl.evdet_id') . ' = ' . $db->qn('ev.ev_id') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_jevents"));
		}
		elseif ($integration == 4)
		{
			$query->select(array('evntstbl.address as location, evntstbl.title AS title'));
			$query->join('LEFT', $db->qn('#__social_clusters', 'evntstbl') . 'ON (' . $db->qn('evntstbl.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->join('LEFT', $db->qn('#__social_events_meta', 'ev') . 'ON (' . $db->qn('evntstbl.id') . ' = ' . $db->qn('ev.cluster_id') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_easysocial"));
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if ($app->isAdmin())
		{
			$searchEvent = $this->getState('filter.events');
			$searchPaymentStatusList = $this->getState('filter.status');
		}
		else
		{
			$searchEvent = $this->getState('search_event_list');
			$searchPaymentStatusList = $this->getState('search_paymentStatuslist');
		}

		$filterOrder = $this->getState('filter_order');
		$filterOrderDir = $this->getState('filter_order_Dir');

		if (!empty($searchEvent))
		{
			$eventid = JString::strtolower($searchEvent);
		}

		if (empty($eventid))
		{
			$eventid = JFactory::getApplication()->input->post->get('event');
		}

		// IF Event Filter Selected
		if ($eventid)
		{
			$eventRefID = $this->jticketingmainhelper->getEventrefid($eventid);

			if ($eventRefID)
			{
				$query->where($db->quoteName('o.event_details_id') . ' = ' . $db->quote($eventRefID));
			}
		}
		else
		{
			if ($app->isSite())
			{
				$eventList = $this->jticketingmainhelper->geteventnamesByCreator($user->id);

				if (!empty($eventList))
				{
					$intXrefEventIdArray = array();

					foreach ($eventList as $key => $event)
					{
						$intXrefEventId = '';
						$intXrefEventId = $this->jticketingmainhelper->getEventrefid($event->id);

						if ($intXrefEventId)
						{
							$intXrefEventIdArray[] = $intXrefEventId;
						}
					}

					$intXrefEventIdArray = implode(',', $intXrefEventIdArray);
					$query->where('o.event_details_id IN (' . $intXrefEventIdArray . ')');
				}
			}
		}

		if ($searchPaymentStatusList)
		{
			$query->where($db->quoteName('o.status') . 'LIKE' . $db->quote($searchPaymentStatusList));
		}

		$search = trim($search);

		if ($search)
		{
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('(o.name LIKE ' . $search . ' OR o.email LIKE ' . $search . ')');
		}

		if ($app->isSite())
		{
			if ($integration == 1)
			{
				$query->where($db->quoteName('comm.creator') . ' = ' . $db->quote($user->id));
			}

			if ($integration == 2)
			{
				$query->where($db->quoteName('b.created_by') . ' = ' . $db->quote($user->id));
			}

			if ($integration == 3)
			{
				$query->where($db->quoteName('ev.created_by') . ' = ' . $db->quote($user->id));
			}

			if ($integration == 4)
			{
				$query->where($db->quoteName('evntstbl.creator_uid') . ' = ' . $db->quote($user->id));
			}
		}

		$query->group($db->quoteName('order_items_id'));

		if (!empty($filterOrder))
		{
			$db = JFactory::getDBO();
			$columnInfo = $db->getTableColumns('#__jticketing_order');

			foreach ($columnInfo as $key => $value)
			{
				$allowedFields[] = $key;
			}

			if (in_array($filterOrder, $allowedFields))
			{
				$query->order('o.' . $filterOrder . ' ' . $filterOrderDir);
			}
		}

	return $query;
	}

	/**
	 * Method to get an array of data items
	 *
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Method to get data
	 *
	 * @param   array  $uselimit  use limit or not
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function getData($uselimit = 1)
	{
		if (empty($this->_data))
		{
			$query       = $this->getListQuery();

			if ($uselimit == 1)
			{
				$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			}
			else
			{
				$this->_data = $this->_getList($query);
			}
		}

		return $this->_data;
	}

	/**
	 * Method to get total records
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getTotal()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_total))
		{
			$query        = $this->getListQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to get eventname
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventName()
	{
		$input     = JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$option    = $input->get('option');
		$eventid   = $input->get('event_list', '', 'INT');
		$query     = $this->jticketingmainhelper->getEventName($eventid);
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadResult();

		return $this->_data;
	}

	/**
	 * Method to get customer name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getCustomerNote()
	{
		$input       = JFactory::getApplication()->input;
		$attendee_id = $input->get('attendee_id', '', 'INT');

		if (!$attendee_id)
		{
			return false;
		}

		$query = "SELECT o.customer_note
		FROM #__jticketing_order as o
		LEFT JOIN #__jticketing_order_items as oi ON oi.order_id = o.id
		WHERE oi.attendee_id=" . $attendee_id;
		$this->_db->setQuery($query);

		return $this->_db->loadResult();
	}

	/**
	 * Method to get
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventid()
	{
		$jticketingmainhelper = new jticketingmainhelper;
		$query                = $jticketingmainhelper->getSalesDataAdmin('', '', $where);
	}

	/**
	 * Method to get
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function Eventdetails()
	{
		$input     = JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$option    = $input->get('option');
		$eventid   = $input->get('event', '', 'INT');
		$query     = "SELECT title FROM #__community_events
			  WHERE id = {$eventid}";
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadResult();

		return $this->_data;
	}

	/**
	 * Email to selected attendees or Adds antries to jticketing queue which will be used later
	 *
	 * @param   ARRAY   $cid             array of emails
	 * @param   string  $subject         subject of email
	 * @param   string  $message         message of email
	 * @param   string  $attachmentPath  Attachment path
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function emailtoSelected($cid, $subject, $message, $attachmentPath = '')
	{
		$db = JFactory::getDBO();
		$jticketingmainhelper = new jticketingmainhelper;
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		$path                 = JPATH_ROOT . '/components/com_jticketing/models/orders.php';
		$com_params           = JComponentHelper::getParams('com_jticketing');
		$replytoemail         = $com_params->get('reply_to');
		$where       = '';
		$app       	= JFactory::getApplication();
		$mailfrom  	= $app->getCfg('mailfrom');
		$fromname  	= $app->getCfg('fromname');
		$sitename  	= $app->getCfg('sitename');
		$mainframe 	= JFactory::getApplication();

		if (isset($replytoemail))
		{
			$replytoemail = explode(",", $replytoemail);
		}

		if (!class_exists('JticketingModelorders'))
		{
			JLoader::register('JticketingModelorders', $path);
			JLoader::load('JticketingModelorders');
		}

		$JticketingModelorders = new JticketingModelorders;

		if (is_array($cid))
		{
			foreach ($cid AS $email)
			{
				// If order is deleted dont send reminder
				if (!$email)
				{
					continue;
				}

				$result = JticketingMailHelper::sendMail($mailfrom, $fromname, $email, $subject, $message, $html = 1, '', '', '', $replytoemail);
			}
		}
	}

	/**
	 * Email to selected attendees or Adds antries to jticketing queue which will be used later
	 *
	 * @param   ARRAY  $order_items  order_items
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getAttendeeEmail($order_items)
	{
		$db                       = JFactory::getDBO();
		$email_array = array();

		foreach ($order_items AS $order_item_id)
		{
			$query = "SELECT order_id from #__jticketing_order_items where id=" . $order_item_id;
			$db->setQuery($query);
			$order_id = $db->loadResult($query);

			if (!$order_id)
			{
				continue;
			}

			$query = "SELECT email from #__jticketing_order where  id=" . $order_id;
			$db->setQuery($query);
			$email = $db->loadResult($query);

			if ($email)
			{
				$email_array[] = $email;
			}
		}

		return array_unique($email_array);
	}

	/**
	 * Method to getEvents forpurchase
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventsforpurchase()
	{
		$path = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $path);
			JLoader::load('jticketingmainhelper');
		}

		$jticketingmainhelper = new jticketingmainhelper;
		$jinput        = JFactory::getApplication()->input;
		$catId         = $jinput->get('category_id', '', 'integer');
		$location      = $jinput->post->get('location', '', 'string');
		$buyer_id      = $jinput->post->get('buyer_id', '', 'INT');

		if (!empty($catId))
		{
			$select_options = "";
			$db                       = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('te.id AS eventId, te.title , te.startdate');
			$query->from('`#__jticketing_events` AS te');
			$query->where('te.catid = ' . $db->Quote($catId));
			$query->where('te.state = 1');

			if ($location)
			{
				$location = $db->Quote('%' . $db->escape($location, true) . '%');
				$query->where('( te.location LIKE ' . $location . ' )');
			}

			$query->group('te.id');
			$db->setQuery($query);
			$eventList = $db->loadObjectList();
			$listv = '';
			$date_format = JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM');
			$i = 0;
			$result = array();

			foreach ($eventList as $value)
			{
				if ($value)
				{
					$sel = '';
					$showEvent = 0;
					$showEvent = $jticketingmainhelper->showbuybutton($value->eventId);

					if ($showEvent)
					{
							$result[$i]['eventid'] = $value->eventId;
							$result[$i]['title'] = $value->title . ' (' . JHtml::date($value->startdate, $date_format, false) . ') ';
							$i++;
					}
				}
			}

			if (!empty($result))
			{
				return $result;
			}
		}
	}

	/**
	 * Method to change ticket assignment
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function changeTicketAssignment()
	{
		$jinput        = JFactory::getApplication()->input;
		$search_event_assignee         = $jinput->get('search_event_assignee', '', 'integer');
		$search_ticket_assignee         = $jinput->get('search_ticket_assignee', '', 'integer');
		$order_items_id   = $jinput->get('order_items_id', '', 'integer');
		$order_id         = $jinput->get('order_id', '', 'integer');

		if (!empty($search_event_assignee) and !empty($search_ticket_assignee) and !empty($order_items_id))
		{
			if (!empty($order_id))
			{
				$this->changeOrderticket($search_event_assignee, $search_ticket_assignee, $order_items_id, $order_id);
			}
		}
	}

	/**
	 * Method to change ticket assignment
	 *
	 * @param   array  $search_event_assignee   event id
	 * @param   array  $search_ticket_assignee  search_ticket_assignee
	 * @param   array  $order_items_id          order_items_id
	 * @param   array  $order_id                order_id
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function changeOrderticket($search_event_assignee,$search_ticket_assignee, $order_items_id, $order_id)
	{
		$jticketingmainhelper = new jticketingmainhelper;
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Get integration xref id from eventid
		$intxrefidevid = $jticketingmainhelper->getEventrefid($search_event_assignee);
		$query = "SELECT o.evento.fee,o.amount,o.customer_note,o.processor,o.order_id
				FROM #__jticketing_order  as o
				where o.id=" . $order_id;
		$db->setQuery($query);
		$orderdata = $db->loadObject($query);

		// Change Integration xref in main order
		$order               = $db->loadObjectlist();
		$res                 = new stdClass;
		$res->id             = $order_id;
		$res->mdate          = date("Y-m-d H:i:s");
		$res->extra          = $orderdata->extra;

		if (!$db->updateObject('#__jticketing_order', $res, 'id'))
		{
			return false;
		}
	}

	/**
	 * Method to get ticket types
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getTicketTypes()
	{
		$jticketingmainhelper = new jticketingmainhelper;
		$jinput        = JFactory::getApplication()->input;
		$eventid         = $jinput->get('eventid', '', 'integer');
		jimport('joomla.utilities.arrayhelper');
		$result_arr = array();

		if ($eventid)
		{
			$results = $jticketingmainhelper->getTicketTypes($eventid);

			foreach ($results AS $result)
			{
				$result_arr[] = JArrayHelper::fromObject($result, true);
			}
		}

		return $result_arr;
	}

	/**
	 * Method to cancel ticket
	 *
	 * @param   INT  $order_id  order ID for event
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function cancelTicket($order_id='')
	{
		$jinput = JFactory::getApplication()->input;

		if (!$order_id)
		{
			$order_id = $jinput->post->get('order_id', 'null', 'INT');
		}

		$db = JFactory::getDbo();

		if ($order_id)
		{
			$query = "DELETE FROM #__jticketing_queue WHERE order_id={$order_id}";
			$db->setQuery($query);
			$db->query();

			$query = "SELECT id from #__jticketing_order_items WHERE order_id={$order_id}";
			$db->setQuery($query);
			$order_items    = $db->loadObjectlist();

			if ($order_items)
			{
				foreach ($order_items AS $oitems)
				{
					// Delete From Checkin Details Table
					$query = "DELETE FROM #__jticketing_checkindetails	WHERE ticketid=" . $oitems->id;
					$db->setQuery($query);
					$db->execute();
				}
			}
		}

		if ($order_id && $status)
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/controllers/orders.php';
			$JticketingControllerorders = new JticketingControllerorders;
			$JticketingControllerorders->save();
		}
	}
}
