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

/**
 * Model for mypayout to show payout
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
		$option     = JRequest::getCmd('option');

		// Get pagination request variables
		$limit      = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $input->get('limitstart', '0', 'INT');

		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
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
		$jticketingmainhelper = new jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();
		$query                = $jticketingmainhelper->getMypayoutData();
		global $mainframe, $option;
		$mainframe        = JFactory::getApplication();
		$db               = JFactory::getDBO();
		$input            = JFactory::getApplication()->input;
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

				if ($filter_order == 'cdate' AND $integration == 3)
				{
					$filter_order = 'date';
				}

				if (in_array($filter_order, $allowed_fields))
				{
					$query .= " ORDER BY a.$filter_order $filter_order_Dir";
				}
			}
		}

		return $query;
	}

	/**
	 * get  payout data for edit view
	 *
	 * @return  object  single payout data
	 *
	 * @since   1.0
	 */
	public function getPayoutFormData()
	{
		$jticketingmainhelper = new jticketingmainhelper;
		$query                = $jticketingmainhelper->getPayeeDetails();
		$this->_db->setQuery($query);
		$payouts = $this->_db->loadObjectList();

		return $payouts;
	}

	/**
	 * get Single payout data
	 *
	 * @return  object  single payout data
	 *
	 * @since   1.0
	 */
	public function getSinglePayoutData()
	{
		$payout_id = JRequest::getInt('payout_id', '');
		$db = JFactory::getDBO();
		$query = "SELECT id,user_id,payee_name,transction_id,date,payee_id,amount,status
		FROM #__jticketing_ticket_payouts
		WHERE id=" . $payout_id;
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
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
		$input = JFactory::getApplication()->input;
		$mainframe            = JFactory::getApplication();
		$option               = $input->get('option');
		$eventid              = $input->get('event', '', 'INT');
		$jticketingmainhelper = new jticketingmainhelper;
		$query                = $jticketingmainhelper->getEventName($eventid);
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadResult();

		return $this->_data;
	}

	/**
	 * Edit payout data
	 *
	 * @return  object  single payout data
	 *
	 * @since   1.0
	 */
	public function editPayout()
	{
		$post               = JRequest::get('post');
		$obj                = new stdClass;
		$obj->id            = $post['edit_id'];
		$obj->user_id       = $post['user_id'];
		$obj->payee_name    = $post['payee_name'];
		$obj->payee_id      = $post['paypal_email'];
		$obj->transction_id = $post['transaction_id'];
		$obj->amount        = $post['amount'];
		$obj->date          = $post['payout_date'];
		$obj->status        = $post['status'];

		// Insert object
		if (!$this->_db->updateObject('#__jticketing_ticket_payouts', $obj, 'id'))
		{
			echo $this->_db->stderr();

			return false;
		}

		return true;
	}

	/**
	 * Save payout data
	 *
	 * @param   array  $post  post data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function savePayout($post = '')
	{
		if (empty($post))
		{
			$post = JRequest::get('post');
		}

		$obj                = new stdClass;
		$obj->id            = '';
		$obj->user_id       = $post['user_id'];
		$obj->payee_name    = $post['payee_name'];
		$obj->payee_id      = $post['paypal_email'];
		$obj->transction_id = $post['transaction_id'];
		$obj->amount        = $post['amount'];
		$obj->date          = $post['payout_date'];
		$obj->status        = $post['status'];
		$obj->ip_address    = $_SERVER['REMOTE_ADDR'];

		// Insert object
		if (!$this->_db->insertObject('#__jticketing_ticket_payouts', $obj, 'id'))
		{
			echo $this->_db->stderr();

			return false;
		}

		return true;
	}

	/**
	 * Delete payout data
	 *
	 * @param   int  $payeeid  Id of the payee to delete
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function delete_payout($payeeid)
	{
		$db = JFactory::getDBO();
		$id = implode(',', $payeeid);

		$delete_payout = "delete from #__jticketing_ticket_payouts where id IN ( $id )";
		$db->setQuery($delete_payout);
		$confrim = $db->Query();

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
	 * Delete payout data
	 *
	 * @param   object  $items  items to publish
	 * @param   int     $state  state
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setItemState($items, $state)
	{
		$db = JFactory::getDBO();

		if (is_array($items))
		{
			$row = $this->getTable();

			foreach ($items as $id)
			{
				$db    = JFactory::getDBO();
				$query = "UPDATE  #__jticketing_ticket_payouts SET status=$state where id=" . $id;
				$db->setQuery($query);

				if (!$db->query())
				{
					$this->setError($this->_db->getErrorMsg());

					return false;
				}
			}
		}
	}
}
