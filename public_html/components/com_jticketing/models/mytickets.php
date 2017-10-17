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
class JticketingModelmytickets extends JModelLegacy
{
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$input     = JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$option    = $input->get('option');

		// Get pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');

		// Get pagination request variables
		$limitstart = $input->get('limitstart', '0', 'INT');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		$this->jticketingmainhelper = new jticketingmainhelper;
		$this->user                 = JFactory::getUser();
	}

	/**
	 * Method to get data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getData()
	{
		if (empty($this->_data))
		{
			$query       = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	/**
	 * Method to build query
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function _buildQuery()
	{
		$where = $this->_buildContentWhere();

		if ($where)
		{
			$query = $this->jticketingmainhelper->getMyticketDataSite($where);

			return $query;
		}
	}

	/**
	 * Method to build where content
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function _buildContentWhere()
	{
		$integration = $this->jticketingmainhelper->getintegration();
		$user        = JFactory::getUser();
		$input       = JFactory::getApplication()->input;
		$post        = $input->post;
		$eventid     = $input->get('jticketing_eventid', '', 'INT');

		if (!$eventid)
		{
			$eventid = $input->get('event');
		}

		$mainframe         = JFactory::getApplication();
		$xrefid            = '';
		$option            = $input->get('option');
		$where             = array();
		$jticketing_userid = $input->get('jticketing_userid', '', 'INT');

		if ($jticketing_userid)
		{
			$where[] = " AND  a.user_id=" . $jticketing_userid;
		}
		else
		{
			$where[] = " AND a.user_id=" . $user->id;
		}

		$search_order = $mainframe->getUserStateFromRequest($option . 'search_order', 'search_order', '', 'string');

		if ($search_order)
		{
			$where[] = "  e.order_id=" . $search_order;
		}

		return $where1 = (count($where) ? '' . implode(' AND ', $where) : '');
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
			$query        = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to get pagination
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
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
		$eventid   = $input->get('event', '', 'INT');
		$query     = $this->jticketingmainhelper->getEventName($eventid);
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadResult();

		return $this->_data;
	}

	/**
	 * Method to get event details
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
		$eventid = $input->get('event', '', 'INT');
		$query = "SELECT title FROM #__community_events
			  WHERE id = {$eventid}";
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadResult();

		return $this->_data;
	}

	/**
	 * Method to get order info by buyer
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getOrderByBuyer()
	{
		$query = "SELECT id,order_id
			FROM #__jticketing_order
			WHERE user_id=" . $this->user->id;
		$this->_db->setQuery($query);

		return $data = $this->_db->loadObjectlist();
	}
}
