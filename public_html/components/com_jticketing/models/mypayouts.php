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
 * Model for mypayout to show payout dat to event admin
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelmypayouts extends JModelLegacy
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
		$input      = JFactory::getApplication()->input;
		$mainframe  = JFactory::getApplication();
		$option     = $input->get('option');

		// Get pagination request variables
		$limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $input->get('limitstart', '0', 'INT');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		$this->jticketingmainhelper = new jticketingmainhelper;
		$this->user                 = JFactory::getUser();
	}

	/**
	 * Get data for a payout
	 *
	 * @return  object  $this->_data  payout data
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
	 * Bulid query
	 *
	 * @return  string  $query  query
	 *
	 * @since   1.0
	 */
	public function _buildQuery()
	{
		$user = JFactory::getUser();

		if ($user)
		{
			global $mainframe, $option;
			$mainframe = JFactory::getApplication();
			$db        = JFactory::getDBO();
			$where = '';
			$query = $this->jticketingmainhelper->getMypayoutData($user->id);
			$query .= $where;
			$filter_order     = '';
			$filter_order_Dir = '';
			$qry1             = '';
			$filter_order     = $mainframe->getUserStateFromRequest($option . 'filter_order', 'filter_order', 'title', 'cmd');
			$filter_order_Dir = $mainframe->getUserStateFromRequest($option . 'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

			if ($filter_order)
			{
				$qry1 = "SHOW COLUMNS FROM #__jticketing_ticket_payouts";

				if ($qry1)
				{
					$db->setQuery($qry1);
					$exists1 = $db->loadobjectlist();

					foreach ($exists1 as $key1 => $value1)
					{
						$allowed_fields[] = $value1->Field;
					}

					if (in_array($filter_order, $allowed_fields))
					{
						$query .= " ORDER BY $filter_order $filter_order_Dir";
					}
				}
			}

			return $query;
		}
		else
		{
			return false;
		}
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
			$query        = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * get pagination
	 *
	 * @return  object  $this->_pagination  pagination values
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
}
