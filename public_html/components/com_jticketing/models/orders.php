<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');
jimport('joomla.database.table.user');
jimport('joomla.application.component.modellist');

/**
 * Model for order for creating order and process order
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelorders extends JModelList
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
				'id', 'o.`id`',
				'order_id', 'o.`order_id`',
				'event_details_id', 'o.`event_details_id`',
				'status', 'o.`status`',
			);
		}

		$this->jticketingmainhelper = new jticketingmainhelper;

		$jTOrderHelper = JPATH_ROOT . '/components/com_jticketing/helpers/order.php';

		if (!class_exists('JticketingOrdersHelper'))
		{
			JLoader::register('JticketingOrdersHelper', $jTOrderHelper);
			JLoader::load('JticketingOrdersHelper');
		}

		$this->jTOrderHelper = new JticketingOrdersHelper;
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

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// Get pagination request variables
		$limit      = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'));
		$limitstart = $app->getUserStateFromRequest('limitstart', 'limitstart', 0);

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		if ($app->isAdmin())
		{
			$eventId = $app->getUserStateFromRequest($this->context . '.filter.events', 'filter_event', '', 'string');
			$this->setState('filter.events', $eventId);

			$searchPaymentStatus = $app->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', '', 'string');
			$this->setState('filter.status', $searchPaymentStatus);
		}
		else
		{
			$eventId = $app->getUserStateFromRequest($this->context . '.search_event', 'search_event', '', 'string');
			$this->setState('search_event', $eventId);

			$searchPaymentStatus = $app->getUserStateFromRequest($this->context . '.search_paymentStatus', 'search_paymentStatus', '', 'string');
			$this->setState('search_paymentStatus', $searchPaymentStatus);
		}

		$filterOrder     = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'title', 'cmd');
		$this->setState('filter_order', $filterOrder);

		$filterOrderDir = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$this->setState('filter_order_Dir', $filterOrderDir);

		// List state information.
		parent::populateState('o.order_id', 'asc');
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
		$layout               = $app->get('layout', '', 'STRING');
		$user                 = JFactory::getUser();
		$integration = $this->jticketingmainhelper->getIntegration();

		// Create a new query object.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(
			array(
			'o.transaction_id as transaction_id,o.order_tax,o.coupon_discount,
			o.order_id as order_id,i.eventid AS eventid, o.id,o.name,o.processor,o.cdate,o.amount,
			o.fee,o.status,o.ticketscount,o.original_amount,o.event_details_id,i.eventid
			as evid,o.amount as paid_amount,o.coupon_code,user.firstname,user.lastname'
			)
			);
		$query->from($db->qn('#__jticketing_order', 'o'));
		$query->join('INNER', $db->qn('#__jticketing_integration_xref', 'i') . 'ON (' . $db->qn('o.event_details_id') . ' = ' . $db->qn('i.id') . ')');
		$query->join('INNER', $db->qn('#__jticketing_users', 'user') . 'ON (' . $db->qn('o.id') . ' = ' . $db->qn('user.order_id') . ')');

		if ($integration == 1)
		{
			$query->select('comm.title');
			$query->join('LEFT', $db->qn('#__community_events', 'comm') . 'ON (' . $db->qn('comm.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_community"));
		}
		elseif ($integration == 2)
		{
			$query->select('event.title');
			$query->join('LEFT', $db->qn('#__jticketing_events', 'event') . 'ON (' . $db->qn('event.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_jticketing"));
		}
		elseif ($integration == 3)
		{
			$query->select('je.summary AS title');
			$query->join('LEFT', $db->qn('#__jevents_vevdetail', 'je') . 'ON (' . $db->qn('je.evdet_id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_jevents"));
		}
		elseif ($integration == 4)
		{
			$query->select('es.title');
			$query->join('LEFT', $db->qn('#__social_clusters', 'es') . 'ON (' . $db->qn('es.id') . ' = ' . $db->qn('i.eventid') . ')');
			$query->where($db->qn('i.source') . ' = ' . $db->quote("com_easysocial"));
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if ($app->isAdmin())
		{
			$eventId = $this->getState('filter.events');
			$searchPaymentStatus = $this->getState('filter.status');
		}
		else
		{
			$eventId = $this->getState('search_event');
			$searchPaymentStatus = $this->getState('search_paymentStatus');
		}

		$filterOrder = $this->getState('filter_order');
		$filterOrderDir = $this->getState('filter_order_Dir');

		if (!empty($search))
		{
			if (stripos($search, 'o.id:') === 0)
			{
				$query->where('o.order_id = ' . (int) substr($search, 4));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( o.order_id LIKE ' . $search . ' )');
			}
		}

		if ($user and $layout == 'my')
		{
			$query->where('(user.user_id=' . $user->id . ')');
		}

		// If all orders view show only events that are created by that user
		if ($user and $layout == 'default')
		{
			$query->where('(i.userid=' . $user->id . ')');
		}

		$integration = $this->jticketingmainhelper->getIntegration();
		$source = $this->jticketingmainhelper->getSourceName($integration);

		if ($source)
		{
			$query->where('(i.source=' . $db->quote($source) . ')');
		}

		if (!empty($eventId))
		{
			$intXrefeventID = $this->jticketingmainhelper->getEventrefid($eventId);
			$query->where('(o.event_details_id=' . $intXrefeventID . ')');
		}
		else
		{
			// If layout = my find events for which user has made orders
			if ($layout == 'my')
			{
				$eventList = $this->jticketingmainhelper->geteventnamesBybuyer($user->id);
			}
			elseif ($layout == 'default')
			{
				// If layout = default find all events which are created by that user
				$eventList = $this->jticketingmainhelper->geteventnamesByCreator($user->id);
			}

			if (!empty($eventList))
			{
				$eventIntegId = array();

				foreach ($eventList as $key => $event)
				{
					if (isset($event->integrid))
					{
						$eventIntegId[] = $event->integrid;
					}
				}

				if (!empty($eventIntegId))
				{
					$eventIntegId = implode("','", $eventIntegId);
					$query->where($db->quoteName('o.event_details_id') . ' IN (' . $eventIntegId . ')');
				}
			}
		}

		if (!empty($searchPaymentStatus))
		{
			$paymentStatus = JString::strtoupper($searchPaymentStatus);
			$paymentStatus = $db->Quote('%' . $db->escape($paymentStatus, true) . '%');
			$query->where('( o.status LIKE ' . $paymentStatus . ' )');
		}

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
	 * Get data for a order
	 *
	 * @return  object  $this->_data  payout data
	 *
	 * @since   1.0
	 */
	public function getData()
	{
		if (empty($orderData))
		{
			$query = $this->getListQuery();
			$orderData = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $orderData;
	}

	/**
	 * get total count
	 *
	 * @return  int  $this->_total  total count
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
	 * get eventname
	 *
	 * @return  object  $this->_data  event data
	 *
	 * @since   1.0
	 */
	public function getEventName()
	{
		$input     = JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$option    = $input->get('option');
		$eventid   = $input->get('event', '', 'INT');
		$query     = $this->jticketingmainhelper->getEventName($eventid);
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadResult();

		return $this->_data;
	}

	/**
	 * Get Event details
	 *
	 * @return  object  $this->_data  event data
	 *
	 * @since   1.0
	 */
	public function Eventdetails()
	{
		$input     = JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$option    = $input->get('option');
		$eventid   = $input->get('event', '', 'INT');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('title'));
		$query->from($db->quoteName('#__community_events'));
		$query->where('id = ' . $eventid);
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadResult();

		return $this->_data;
	}

	/**
	 * Store order
	 *
	 * @param   integer  $post  post data for order
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function store($post)
	{
		$integration = $this->jticketingmainhelper->getIntegration();

		$db                  = JFactory::getDBO();
		$res                 = new stdClass;
		$res->id             = $post->get('order_id');
		$res->mdate          = date("Y-m-d H:i:s");
		$res->transaction_id = md5($post->get('order_id'));
		$res->payee_id       = $post->get('buyer_email');
		$res->status         = trim($post->get('pstatus'));
		$res->processor      = $post->get('processor');

		if (!$db->updateObject('#__jticketing_order', $res, 'id'))
		{
			return false;
		}

		if ($post->get('pstatus') == 'C')
		{
			if ($integration == 1)
			{
				$query = "SELECT i.id, i.eventid
				FROM #__jticketing_integration_xref AS i
				LEFT JOIN  #__jticketing_order AS o ON o.event_details_id = i.id
				WHERE o.id =" . $post->get('order_id');
				$db->setQuery($query);
				$eventid = $db->loadObjectlist();
			}

			if ($integration == 1)
			{
				$query = "SELECT type_id,count(ticketcount) as ticketcounts
					FROM #__jticketing_order_items where order_id=" . $post->get('order_id') . " GROUP BY type_id";
			}
			elseif ($integration = 2)
			{
				$query = "SELECT type_id,count(ticketcount) as ticketcounts
					FROM #__jticketing_order_items where order_id=" . $post->get('order_id') . " GROUP BY type_id";
			}

			$db->setQuery($query);
			$orderDetails = $db->loadObjectlist();

			foreach ($orderDetails as $orderDetail)
			{
				$typeData = '';
				$resType  = new stdClass;

				if ($integration == 1)
				{
					$query = "SELECT count
					FROM #__jticketing_types where id=" . $orderDetail->type_id;
				}
				elseif ($integration == 2)
				{
					$query = "SELECT count
					FROM #__jticketing_types where id=" . $orderDetail->type_id;
				}

				$db->setQuery($query);
				$typeData       = $db->loadResult();
				$resType->id    = $orderDetail->type_id;
				$resType->count = $typeData - $orderDetail->ticketcounts;

				if ($integration == 1)
				{
					$db->updateObject('#__community_events', $resType, 'id');
				}
				elseif ($integration == 2)
				{
					$db->updateObject('#__jticketing_types', $resType, 'id');
				}
			}
		}
	}

	/**
	 * Delete order
	 *
	 * @param   integer  $orderid  id of jticketing_order table to delete
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function delete($orderid)
	{
		$db = JFactory::getDBO();
		$id = implode(',', $orderid);

		for ($i = 0; $i < 2; $i++)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('o.id,type_id,SUM(i.ticketcount) as cnt, o.status');
			$query->from('#__jticketing_order_items as i');
			$query->join('INNER', '#__jticketing_order as o ON o.id=i.order_id');
			$query->where('i.order_id IN (' . $id . ')');

			if ($i == 0)
			{
				$query->where('(o.status LIKE "C" )');
			}
			else
			{
				$query->where('(o.status NOT LIKE "C" )');
			}

			$data = $query->group('i.type_id');
			$db->setQuery($data);
			$result  = $db->loadobjectlist();
			$confrim = 0;

			foreach ($result as $tempResult)
			{
				// Update the Type ticeket count. When Deleting Confirmed order increase count in types table
				if ($i == 0)
				{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->update('#__jticketing_types SET count = count+' . $tempResult->cnt);
					$query->where('id=' . $tempResult->type_id);
					$db->setQuery($query);
					$confrim = $db->execute();
				}
			}

			// When Deleting Confirmed order decresing the confromcount in community_events table & deleting the community_events_members confirmed users
			if ($i == 0 && (!empty($result)))
			{
				$confrim = $this->jticketingmainhelper->unJoinMembers($id);
			}
		}

		// Delete the order from order table
		$db = JFactory::getDbo();
		$deleteOrder = $db->getQuery(true);
		$deleteOrder->delete($db->quoteName('#__jticketing_order'));
		$deleteOrder->where('id IN (' . $id . ')');
		$db->setQuery($deleteOrder);
		$confrim = $db->execute();

		// Delete the order from order table
		$db = JFactory::getDbo();
		$deleteOrder = $db->getQuery(true);
		$deleteOrder->delete($db->quoteName('#__jticketing_queue'));
		$deleteOrder->where('order_id IN (' . $id . ')');
		$db->setQuery($deleteOrder);
		$confrim = $db->execute();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__jticketing_order_items'));
		$query->where('order_id IN (' . $id . ')');
		$db->setQuery($query);
		$orderItems = $db->loadObjectlist();

		if ($orderItems)
		{
			foreach ($orderItems AS $oitems)
			{
				// Delete From Checkin Details Table
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__jticketing_checkindetails'));
				$query->where('ticketid = ' . $oitems->id);
				$db->setQuery($query);
				$db->execute();
			}
		}

		// Delete the order item
		$db = JFactory::getDbo();
		$deleteOrderItem = $db->getQuery(true);
		$deleteOrderItem->delete($db->quoteName('#__jticketing_order_items'));
		$deleteOrderItem->where('order_id IN (' . $id . ')');
		$db->setQuery($deleteOrderItem);
		$confrim = $db->execute();

		if ($confrim)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Decrease ticket available seats
	 *
	 * @param   integer  $order_id  id of jticketing_order table
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function eventsTypesCountDecrease($order_id)
	{
		$db   = JFactory::getDBO();
		$data = $this->jticketingmainhelper->getOrder_ticketcount($order_id, 0);
		$db->setQuery($data);
		$result  = $db->loadobjectlist();
		$confrim = 0;

		foreach ($result as $tempResult)
		{
			// Update the Type ticeket count
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update('#__jticketing_types SET count = count-' . $tempResult->cnt);
			$query->where('id=' . $tempResult->type_id);
			$db->setQuery($query);
			$confrim = $db->execute();
		}
	}

	/**
	 * Increase ticket available seats
	 *
	 * @param   integer  $order_id  id of jticketing_order table
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function eventsTypesCountIncrease($order_id)
	{
		$db          = JFactory::getDBO();
		$integration = $this->jticketingmainhelper->getIntegration();
		$data        = $this->jticketingmainhelper->getOrder_ticketcount($order_id, 0);
		$db->setQuery($data);
		$result  = $db->loadobjectlist();
		$confrim = 0;

		foreach ($result as $tempResult)
		{
			// Update the Type ticeket count
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update('#__jticketing_types SET count = count+' . $tempResult->cnt);
			$query->where('id=' . $tempResult->type_id);
			$db->setQuery($query);
			$confrim = $db->execute();
		}

		if ($integration == 1)
		{
			$confrim = $this->jticketingmainhelper->unJoinMembers($order_id);
		}
	}

	/**
	 * Get order status based on order id
	 *
	 * @param   integer  $order_id  id of jticketing_order table
	 *
	 * @return  string order status like C,P
	 *
	 * @since   1.0
	 */
	public function getOrderStatus($order_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('status'));
		$query->from($db->quoteName('#__jticketing_order'));
		$query->where('id=' . $order_id);
		$db->setQuery($query);

		return $result = $db->loadResult($query);
	}

	/**
	 * Update order status based on order id
	 *
	 * @param   integer  $orderId    id of jticketing_order table
	 * @param   string   $status     status to change
	 * @param   string   $sendEmail  sendEmail on status change
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updateOrderStatus($orderId, $status, $sendEmail =null)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__jticketing_order'));
		$query->set($db->quoteName('status') . ' = ' . $db->quote($status));
		$query->where($db->quoteName('id') . ' = ' . $db->quote($orderId));
		$db->setQuery($query);
		$db->execute();

		$input   = JFactory::getApplication()->input;
		$post    = $input->post;
		$comment = $post->get('comment', '', 'STRING');
		$orderItemsId = $post->get('order_items_id');

		if (isset($orderItemsId) && isset($comment))
		{
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__jticketing_order_items'));
			$query->set($db->quoteName('comment') . ' = ' . $db->quote($comment));
			$query->where($db->quoteName('id') . ' = ' . $db->quote($orderItemsId));
			$db->setQuery($query);
			$db->execute();
		}

		// Change sent column ub backend queue
		if ($status != 'C')
		{
			$this->jTOrderHelper->addEntry($orderId, $status);
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__jticketing_queue'));
			$query->set($db->quoteName('sent') . ' = 4');
			$query->where($db->quoteName('order_id') . ' = ' . $db->quote($orderId));
			$query->where($db->quoteName('sent') . "IN ('0','3')");
			$db->setQuery($query);
			$db->execute();

			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__jticketing_order_items'));
			$query->where($db->quoteName('order_id') . ' = ' . $db->quote($orderId));
			$db->setQuery($query);
			$orderItems    = $db->loadObjectlist();

			if ($orderItems)
			{
				foreach ($orderItems AS $oitems)
				{
					// Delete From Checkin Details Table
					$query = $db->getQuery(true);
					$query->delete($db->quoteName('#__jticketing_checkindetails'));
					$query->where($db->quoteName('ticketid') . ' = ' . $db->quote($oitems->id));
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
		else
		{
			$this->jTOrderHelper->addEntry($orderId, $status);
			$query = $db->getQuery(true);
			$query->update($db->quoteName('#__jticketing_queue'));
			$query->set($db->quoteName('sent') . ' = 0');
			$query->where($db->quoteName('order_id') . ' = ' . $db->quote($orderId));
			$query->where($db->quoteName('sent') . '= 4');
			$db->setQuery($query);
			$db->execute();
		}

		$comParams = JComponentHelper::getParams('com_jticketing');
		$emailOptions = $comParams->get('email_options');

		// Send order status change Email
		if ($emailOptions)
		{
			if (in_array('order_status_change_email', $emailOptions))
			{
				JticketingMailHelper::sendOrderStatusEmail($orderId, $status);
			}
		}

		// Trigger After Process Payment
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$result = $dispatcher->trigger('jt_OnAfterProcessPayment', array($post, $orderId, $pg_plugin = ''));
	}

	/**
	 * Send remiders to client
	 *
	 * @param   int  $plug_call  this is 1 if function called from jticketing system plugin
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendReminder($plug_call = 0)
	{
		$JticketingMainHelper = new Jticketingmainhelper;
		$comParams           = JComponentHelper::getParams('com_jticketing');
		jimport('joomla.filesystem.file');
		$db                  = JFactory::getDBO();
		$pkeyForReminder   = $comParams->get("pkey_for_reminder");
		$sendAutoReminders = $comParams->get("send_auto_reminders");

		if ($sendAutoReminders != 1)
		{
			return false;
		}

		$input            = JFactory::getApplication()->input;
		$privateKeyInUrl = $input->get('pkey', '', 'STRING');
		$returnMsg       = array();

		if ($pkeyForReminder != $privateKeyInUrl)
		{
			echo "You are Not authorized To send mails";

			return;
		}
		else
		{
			if ($plug_call == 0)
			{
				echo "*****************************<br />";
				echo "Sending Reminders <br />";
				echo "----------------------------- <br />";
			}

			$batchSizeReminders = $comParams->get("batch_size_reminders");
			$enbBatch            = $comParams->get("enb_batch");

			// Send  manual emails(which are added to queue from backend attendee list view)
			$returnMsg[] = $this->sendManualEmail($enbBatch, $batchSizeReminders);

			// Send normal reminder emails so no need to pass flag
			$returnMsg[] = $this->sendEmailReminder($enbBatch, $batchSizeReminders);
			$returnMsg[] = $this->sendSMSReminder($enbBatch, $batchSizeReminders);

			// Add entries to log files
			jimport('joomla.log.log');
			$tableMsg = '';
			$tableMsg .= "<table>";

			if (empty($returnMsg['0']) and empty($returnMsg['1']))
			{
				if ($plug_call == 0)
				{
					echo "===No records found==";
				}

				return;
			}

			foreach ($returnMsg as $msgs)
			{
				foreach ($msgs AS $msg)
				{
					// $tableMsg .= "<tr><td align=\"center\"></td>";
					$tableMsg .= "<td>" . $msg["msg"] . "</td>";
					JLog::addLogger(array('text_file' => 'com_jticketing.reminder.php'), JLog::ALL, $msg["msg"]);
					$tableMsg .= "</tr>";
				}
			}

			if ($plug_call == 0)
			{
				$tableMsg .= "</table>";
				echo $tableMsg;
			}
		}
	}

	/**
	 * Send SMS Reminders
	 *
	 * @param   string   $enbBatch            enable batch or not
	 * @param   integer  $batchSizeReminders  status to change
	 * @param   integer  $sent_delayed        if message is delayed give it preference
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendManualEmail($enbBatch, $batchSizeReminders, $sent_delayed = 0)
	{
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');
		$JticketingMainHelper = new Jticketingmainhelper;
		$comParams           = JComponentHelper::getParams('com_jticketing');
		$db                   = JFactory::getDBO();
		$returnMsg           = array();
		$query                = "select *
		from #__jticketing_queue l
		WHERE sent=" . $sent_delayed . "  AND reminder_type='manual_email' AND  DATE(NOW()) = DATE(`date_to_sent`)
		order by date_to_sent desc";

		if ($enbBatch == '1')
		{
			$query .= " LIMIT {$batchSizeReminders}";
		}

		$db->setQuery($query);
		$reminderIds = $db->loadObjectList();

		if (empty($reminderIds))
		{
			return array();
		}

		$i = 0;

		foreach ($reminderIds AS $reminder)
		{
			if (!empty($reminder->content) and !empty($reminder->email))
			{
				$query = "";

				// Find event start date
				$query = "SELECT event_details_id from #__jticketing_order where  status='C' AND id=" . $reminder->order_id;
				$db->setQuery($query);
				$eventIntegrationId = $db->loadResult($query);

				// If order is deleted dont send reminder
				if (!$eventIntegrationId)
				{
					continue;
				}

				$query = "";
				$query = "SELECT eventid from #__jticketing_integration_xref where id=$eventIntegrationId";
				$db->setQuery($query);
				$eventid = $db->loadResult($query);

				// If order is deleted dont send reminder
				if (!$eventid)
				{
					continue;
				}

				$eventDetails = $JticketingMainHelper->getAllEventDetails($eventid);

				// If event is deleted dont send reminder
				if (!$eventDetails)
				{
					continue;
				}

				// If event unpublished dont send reminder
				if ($eventDetails->event_state != 1)
				{
					continue;
				}

				$todayDate = JFactory::getDate();
				$email      = JticketingMailHelper::sendMail($mailfrom, $fromname, $reminder->email, $reminder->subject, $reminder->content, $mode = 1);

				if ($email == 1)
				{
					$returnMsg['success'] = 1;
					$obj                   = new StdClass;
					$obj->id               = $reminder->id;
					$obj->sent             = 1;
					$obj->sent_date        = date("Y-m-d H:i:s");

					if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
					{
						$returnMsg[$i]['success'] = 0;
						$returnMsg[$i]['msg']     = "Database error";

						return $returnMsg;
					}

					$returnMsg[$i]['success'] = 1;
					$returnMsg[$i]['msg']     = "Successfully Sent to " . $reminder->email;
					$i++;
				}
				else
				{
					// If email not sent set it as delayed
					$obj            = new StdClass;
					$obj->id        = $reminder->id;
					$obj->sent      = 0;
					$obj->sent_date = date("Y-m-d H:i:s");

					if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
					{
						$returnMsg[$i]['success'] = 0;
						$returnMsg[$i]['msg']     = "Database error";

						return $returnMsg;
					}

					$returnMsg[$i]['success'] = 0;
					$returnMsg[$i]['msg']     = "Failed to sent" . $reminder->email;
					$i++;
				}
			}
		}
	}

	/**
	 * Send Email Reminders
	 *
	 * @param   string   $enbBatch            enable batch or not
	 * @param   integer  $batchSizeReminders  size of batch
	 * @param   integer  $sent_delayed        if message is delayed give it preference
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendEmailReminder($enbBatch, $batchSizeReminders, $sent_delayed = 0)
	{
		$db                   = JFactory::getDBO();
		$JticketingMainHelper = new Jticketingmainhelper;
		$jtEventHelper        = new jteventHelper;
		$returnMsg           = array();
		$app                  = JFactory::getApplication();
		$mailer               = JFactory::getMailer();
		$mailfrom             = $app->getCfg('mailfrom');
		$fromname             = $app->getCfg('fromname');
		$sitename             = $app->getCfg('sitename');
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

		// Delete all entries in queue which are not sent
		$query              = "";
		$input              = JFactory::getApplication()->input;
		$doNotAddPending = $input->get('do_not_add_pending');
		$debug              = $input->get('jt_debug');

		if (empty($doNotAddPending))
		{
			$jtEventHelper->addPendingEntriestoQueue();
		}

		$query = "";
		$query = "select queue.*,remtypes.replytoemail
		from #__jticketing_queue AS queue ,#__jticketing_reminder_types AS remtypes
		WHERE queue.reminder_type_id=remtypes.id
		AND  remtypes.state=1 AND queue.sent=" . $sent_delayed . "
		AND queue.reminder_type='email' AND  DATE(NOW()) = DATE(`date_to_sent`)
		order by date_to_sent desc";

		if ($enbBatch == '1')
		{
			$query .= " LIMIT {$batchSizeReminders}";
		}

		$db->setQuery($query);
		$reminderIds = $db->loadObjectList();

		if ($debug)
		{
			print_r($reminderIds);
		}

		if (empty($reminderIds))
		{
			return array();
		}

		$i = 0;

		foreach ($reminderIds AS $reminder)
		{
			// Find all reminder data
			$query                   = "";
			$reminder->reminder_type = trim($reminder->reminder_type);
			$reminder->content       = trim($reminder->content);

			if ($reminder->reminder_type == "email" and !empty($reminder->content) and !empty($reminder->email))
			{
				$query = "";

				// Find event start date
				$db    = JFactory::getDBO();
				$query = "SELECT event_details_id from #__jticketing_order where  status='C' AND id=" . $reminder->order_id;
				$db->setQuery($query);
				$eventIntegrationId = $db->loadResult($query);

				// If order is deleted dont send reminder
				if (!$eventIntegrationId)
				{
					continue;
				}

				$query = "";
				$query = "SELECT eventid from #__jticketing_integration_xref where id=$eventIntegrationId";
				$db->setQuery($query);
				$eventid = $db->loadResult($query);

				// If order is deleted dont send reminder
				if (!$eventid)
				{
					continue;
				}

				$eventDetails = $JticketingMainHelper->getAllEventDetails($eventid);

				// If event is deleted dont send reminder
				if (!$eventDetails)
				{
					continue;
				}

				// If event unpublished dont send reminder
				if ($eventDetails->event_state != 1)
				{
					continue;
				}

				$todayDate = date('Y-m-d');

				// Check if date has not passed
				if (strtotime($eventDetails->startdate) < strtotime($todayDate))
				{
					$obj            = new StdClass;
					$obj->id        = $reminder->id;
					$obj->sent      = 2;
					$obj->sent_date = date("Y-m-d H:i:s");

					if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
					{
						$returnMsg[$i]['success'] = 0;
						$returnMsg[$i]['msg']     = "Database error";

						return $returnMsg;
					}

					continue;
				}

				$toEmailBcc = '';
				$dispatcher  = JDispatcher::getInstance();
				JPluginHelper::importPlugin('system');
				$resp = $dispatcher->trigger('jt_OnBeforeReminderEmail', array($reminder->email));

				if (!empty($resp['0']))
				{
					$toEmailBcc = $resp['0'];
				}

				// $email = $JticketingMainHelper->jt_sendmail($reminder->email, $reminder->subject, );
				// $email  = $mailer->sendMail($mailFrom, $fromName, $reminder->email, $reminder->subject, $reminder->content, $mode = 1, $toemail_bcc);

				if ($toEmailBcc)
				{
					$bccStr = explode(",", $toEmailBcc);
				}
				else
				{
					$bccStr = '';
				}

				$sub   = $reminder->subject;

				$email = JticketingMailHelper::sendMail(
				$mailfrom, $fromname, $reminder->email, $sub, $reminder->content,
				$mode = 1, $bcc_str, '', '', $reminder->replytoemail,
				$reminder->replytoemail, ''
				);

				if ($email == 1)
				{
					$returnMsg['success'] = 1;
					$obj                   = new StdClass;
					$obj->id               = $reminder->id;
					$obj->sent             = 1;
					$obj->sent_date        = date("Y-m-d H:i:s");

					if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
					{
						$returnMsg[$i]['success'] = 0;
						$returnMsg[$i]['msg']     = "Database error";

						return $returnMsg;
					}

					$returnMsg[$i]['success'] = 1;
					$returnMsg[$i]['msg']     = "Successfully Sent to " . $reminder->email;

					// Set flag as 1 if date_to_sent is less than above reminder of same order ID
					$query = "";
					$query = "SELECT id from #__jticketing_queue where sent=0
							AND order_id=" . $reminder->order_id . " AND reminder_type='email'
							AND date_to_sent<='" . $reminder->date_to_sent . "'";
					$db->setQuery($query);
					$queueIDS = $db->loadobjectlist();

					if (isset($queueIDS))
					{
						foreach ($queueIDS AS $queueID)
						{
							$obj            = new StdClass;
							$obj->id        = $queueID->id;
							$obj->sent      = 2;
							$obj->sent_date = date("Y-m-d H:i:s");

							if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
							{
								$returnMsg[$i]['success'] = 0;
								$returnMsg[$i]['msg']     = "Database error";
							}
						}
					}

					$i++;
				}
				else
				{
					// If email not sent set it as delayed
					$obj            = new StdClass;
					$obj->id        = $reminder->id;
					$obj->sent      = 3;
					$obj->sent_date = date("Y-m-d H:i:s");

					if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
					{
						$returnMsg[$i]['success'] = 0;
						$returnMsg[$i]['msg']     = "Database error";

						return $returnMsg;
					}

					$returnMsg[$i]['success'] = 0;
					$returnMsg[$i]['msg']     = "Failed to sent" . $reminder->email;
					$i++;
				}
			}
		}

		return $returnMsg;
	}

	/**
	 * Send SMS Reminders
	 *
	 * @param   string   $enbBatch            enable batch or not
	 * @param   integer  $batchSizeReminders  status to change
	 * @param   integer  $sent_delayed        if message is delayed give it preference
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function sendSMSReminder($enbBatch, $batchSizeReminders, $sent_delayed = 0)
	{
		$db                   = JFactory::getDBO();
		$JticketingMainHelper = new Jticketingmainhelper;
		$jtEventHelper        = new jteventHelper;
		$returnMsg           = array();

		/* Find only latest reminders of sms type for that event suppose 3 reminders of week,day and month
		 * and if email reminder not sent previously or error occured for monthly,weekly then only send one day reminder
		 */

		$input              = JFactory::getApplication()->input;
		$doNotAddPending = $input->get('do_not_add_pending');

		if (empty($doNotAddPending))
		{
			// $jtEventHelper->addPendingEntriestoQueue();
		}

		$query = "";
		$query = "select queue.*,remtypes.replytoemail
		from #__jticketing_queue AS queue ,#__jticketing_reminder_types AS remtypes
		WHERE queue.reminder_type_id=remtypes.id
		AND  remtypes.state=1 AND queue.sent=" . $sent_delayed . "
		AND queue.reminder_type='sms' AND  DATE(NOW()) = DATE(`date_to_sent`)
		order by date_to_sent desc";

		if ($enbBatch == '1')
		{
			$query .= " LIMIT {$batchSizeReminders}";
		}

		$db->setQuery($query);
		$reminderIds = $db->loadObjectList();

		if (empty($reminderIds))
		{
			return array();
		}

		$i = 0;

		foreach ($reminderIds AS $reminder)
		{
			/*Find all reminder data
			$query = "";
			$query = "SELECT * from #__jticketing_queue where  id=" . $reminder_id->id;
			$db->setQuery($query);
			$reminders = $db->loadObjectList();*/

			// Foreach ($reminders AS $reminder)
			{
				$reminder->content = trim($reminder->content);

				if ($reminder->reminder_type == 'sms' and !empty($reminder->content) and !empty($reminder->mobile_no))
				{
					// Find event start date
					$query = "";
					$query = "SELECT event_details_id from #__jticketing_order where
				status='C' AND id=" . $reminder->order_id;
					$db->setQuery($query);
					$eventIntegrationId = $db->loadResult($query);

					// If order is deleted dont send reminder
					if (!$eventIntegrationId)
					{
						continue;
					}

					$query = "";
					$query = "SELECT eventid from #__jticketing_integration_xref where id=" . $eventIntegrationId;
					$db->setQuery($query);
					$eventid = $db->loadResult($query);

					// If order is deleted dont send reminder
					if (!$eventid)
					{
						continue;
					}

					$eventDetails = $JticketingMainHelper->getAllEventDetails($eventid);

					// If event is deleted dont send reminder
					if (!$eventDetails)
					{
						continue;
					}

					// If event is unpublished do not send reminder
					if ($eventDetails->event_state != 1)
					{
						continue;
					}

					$today_date = JFactory::getDate();

					// Check if date has not passed
					if (strtotime($eventDetails->startdate) < strtotime($today_date))
					{
						$obj            = new StdClass;
						$obj->id        = $reminder->id;
						$obj->sent      = 2;
						$obj->sent_date = date("Y-m-d H:i:s");

						if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
						{
							$returnMsg[$i]['success'] = 0;
							$returnMsg[$i]['msg']     = "Database error";

							return $returnMsg;
						}

						continue;
					}

					$vars            = new StdClass;
					$vars->mobile_no = trim($reminder->mobile_no);
					$dispatcher      = JDispatcher::getInstance();
					$params          = JComponentHelper::getParams('com_jticketing');

					$smsGateways = $params->get('smsgateways');
					JPluginHelper::importPlugin('tjsms', $smsGateways);
					$res = $dispatcher->trigger($smsGateways . 'send_message', array($reminder->content,$vars));

					if (!empty($res[0]))
					{
						$response = $res[0];
					}

					if (!empty($response))
					{
						$obj            = new StdClass;
						$obj->id        = $reminder->id;
						$obj->sent      = 1;
						$obj->sent_date = date("Y-m-d H:i:s");

						if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
						{
							$returnMsg[$i]['success'] = 0;
							$returnMsg[$i]['msg']     = "Database error";

							return $returnMsg;
						}

						// Set flag as 1 if date_to_sent is less than above reminder of same order ID
						$query = "";
						$query = "SELECT id from #__jticketing_queue where sent=0
						AND order_id=" . $reminder->order_id . "
						AND reminder_type='email' AND date_to_sent<='" . $reminder->date_to_sent . "'";
						$db->setQuery($query);
						$queueIDS = $db->loadobjectlist();

						if (isset($queueIDS))
						{
							foreach ($queueIDS AS $queueID)
							{
								$obj            = new StdClass;
								$obj->id        = $queueID->id;
								$obj->sent      = 2;
								$obj->sent_date = date("Y-m-d H:i:s");

								if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
								{
									$returnMsg[$i]['success'] = 0;
									$returnMsg[$i]['msg']     = "Database error";
								}
							}
						}

						$returnMsg[$i]['success'] = 1;
						$returnMsg[$i]['msg']     = "Successfully Sent to " . $vars->mobile_no;
						$i++;
					}
					else
					{
						// If sms not sent set it as delayed
						$obj            = new StdClass;
						$obj->id        = $reminder->id;
						$obj->sent      = 0;
						$obj->sent_date = date("Y-m-d H:i:s");

						if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
						{
							$returnMsg[$i]['success'] = 0;
							$returnMsg[$i]['msg']     = "Database error";

							return $returnMsg;
						}
					}
				}
			}
		}

		return $returnMsg;
	}

	/**
	 * function to add data to queue
	 *
	 * @param   object  $reminder  data of reminder
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addtoQueue($reminder)
	{
		$db                 = JFactory::getDBO();
		$obj                = new StdClass;
		$obj->sent          = $reminder->sent;
		$obj->reminder_type = $reminder->reminder_type;
		$obj->date_to_sent  = $reminder->date_to_sent;
		$obj->subject       = $reminder->subject;
		$obj->content       = $reminder->content;
		$obj->email         = $reminder->email;
		$obj->order_id      = $reminder->order_id;

		if (!empty($reminder->id))
		{
			$obj->id = $reminder->id;

			if (!$db->updateObject('#__jticketing_queue', $obj, 'id'))
			{
			}
		}
		else
		{
			if (!$db->insertObject('#__jticketing_queue', $obj, 'id'))
			{
			}
		}
	}
}
