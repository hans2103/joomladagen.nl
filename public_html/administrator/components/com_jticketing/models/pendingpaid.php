<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
	defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model' );
jimport( 'joomla.database.table.user' );


class jticketingModelpendingpaid extends JModelLegacy
{

	function __construct()
  {

		parent::__construct();
		global $mainframe, $option;
		$input=JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$option = $input->get('option');
		 //Get pagination request variables
		$limit =$mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart=$input->get('limitstart','0','INT');
		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

  }


  function getData()
    {
		if(empty($this->_data))
		{
			$query=$this->_buildQuery();
			$this->_data=$this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));

		}

		global $mainframe,$option;
		$mainframe=JFactory::getApplication();
		$filter_order_Dir='';
		$filter_type='';
		$filter_type=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order','goal_amount','cmd');
		$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jomgive.filter_order_Dir','filter_order_Dir','desc','word');
		$jticketingmainhelper = new jticketingmainhelper();
		if($filter_type=='eticketscount')
		{

			$this->_data = $jticketingmainhelper->multi_d_sort($this->_data,$filter_type,$filter_order_Dir);
		}
		return $this->_data;
	}

	function _buildQuery()
	{
		global $mainframe, $option;
		$input=JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$db=JFactory::getDBO();
		$jticketingmainhelper = new jticketingmainhelper();

		$integration = $jticketingmainhelper->getIntegration();
		$where=$this->_buildContentWhere();

		if($integration==1)
		{
			 $query = "SELECT a.order_id as order_id,sum(order_tax)as eorder_tax,sum(original_amount)as eoriginal_amount,sum(coupon_discount)as ecoupon_discount,  sum(amount)as eamount,sum(a.fee) as ecommission,sum(a.ticketscount) as eticketscount,b.id AS evid,a.*,b.title,b.thumb
			FROM #__jticketing_order AS a , #__community_events AS b,#__jticketing_integration_xref as integr
			WHERE a.event_details_id = integr.id
			AND  a.status='C' AND integr.eventid=b.id AND integr.source='com_community'
				".$where;
		}
		else if($integration==2)
		{

			$query = "SELECT  count(oitems.id) AS soldtickets,oitems.*,events.title,types.count,types.eventid FROM #__jticketing_order_items AS
			oitems INNER JOIN #__jticketing_order as ordera ON  ordera.id=oitems.order_id
			INNER JOIN #__jticketing_types  AS types ON  oitems.type_id=types.id
			INNER JOIN #__jticketing_events AS events ON  types.eventid=events.id
			WHERE oitems.payment_status IN('C','DP')".$where." GROUP BY ordera.event_details_id";//die;
		}
		else if($integration==3)
		{

			$query = "SELECT a.order_id as order_id,sum(order_tax)as eorder_tax,sum(original_amount)as eoriginal_amount,sum(coupon_discount)as ecoupon_discount,SUM( a.amount ) AS eamount, SUM( a.fee ) AS ecommission, SUM( a.ticketscount ) AS eticketscount, b.evdet_id AS evid, a.* , b.summary as title
			FROM #__jticketing_order AS a
			LEFT JOIN #__jticketing_integration_xref AS i ON a.event_details_id = i.id
			LEFT JOIN #__jevents_vevdetail AS b ON b.evdet_id = i.eventid
			WHERE a.status =  'C' AND i.eventid=b.evdet_id AND i.source='com_jevents'".$where;
		}


		$filter_order='';
		$filter_order_Dir='';
		$qry1='';
		$filter_order=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order','title','cmd');
		$filter_order_Dir=$mainframe->getUserStateFromRequest($option.'filter_order_Dir','filter_order_Dir','desc','word');
		//@ TO DO Ordering for only
		if($filter_order)
		{
			if($filter_order=='cdate')
					 $qry1="SHOW COLUMNS FROM #__jticketing_order";
			else if($filter_order=='title')
			{
				switch($integration)
				{
					case 1:$qry1="SHOW COLUMNS FROM #__community_events";
					break;

					case 2:$qry1="SHOW COLUMNS FROM #__jticketing_events";
					break;

					case 3:$qry1="SHOW COLUMNS FROM #__jevents_vevdetail";
					$filter_order='summary';
					break;
				}
			}

			if($qry1)
			{
				$db->setQuery($qry1);
				$exists1=$db->loadobjectlist();
				foreach($exists1 as $key1=>$value1)
				{
					$allowed_fields[]=$value1->Field;
				}
				if(in_array($filter_order,$allowed_fields))
				{
					switch($filter_order)
					{
						case 'title':
						$query.=" ORDER BY events.$filter_order $filter_order_Dir";
						break;

						case 'cdate':$query.=" ORDER BY events.$filter_order $filter_order_Dir";
						break;
					}
				}
			}
		}

		return $query;
	}

	function _buildContentWhere()
	{
		$jticketingmainhelper = new jticketingmainhelper();
		 $input=JFactory::getApplication()->input;
    	global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$option =$input->get('option');
		$eventid = $input->get('event','','INT');
		$search_event = $mainframe->getUserStateFromRequest( $option.'search_event', 'search_event', '', 'string' );

		$where="";

		if($search_event!=0){
			  $eventid = JString::strtolower( $search_event);
			  $xrefid = $jticketingmainhelper->getEventrefid($eventid);
			  $where[]=" AND ordera.event_details_id={$xrefid}";
			  return $where1=(count($where)?''. implode(' AND ',$where ):'');
		}
		else
		return '';


	}


	//B
	function getTotal()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_total))
		{
		$query = $this->_buildQuery();
		$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	//B
	function getPagination()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}



  function getEventName()
  {
		$input=JFactory::getApplication()->input;

		$mainframe = JFactory::getApplication();
		$option = $input->get('option');
		$eventid = $input->get('event','','INT');

  		$jticketingmainhelper = new jticketingmainhelper();
		$query = $jticketingmainhelper->getEventName($eventid);

		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadResult();

		return $this->_data;
  }

  function getEventid()
  {
 		$jticketingmainhelper = new jticketingmainhelper();
		$query = $jticketingmainhelper->getSalesDataAdmin('','',$where);
  }

  function Eventdetails()
  {
		$input=JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$option = $input->get('option');

		$eventid = $input->get('event','','INT');

  	$query = "SELECT title FROM #__community_events
			  WHERE id = {$eventid}";
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadResult();

	return $this->_data;

  }

  function pendingcount($eventid){
		$query = "SELECT  count(oitems.id) AS soldtickets FROM #__jticketing_order_items AS
		oitems INNER JOIN #__jticketing_order as ordera ON  ordera.id=oitems.order_id
		INNER JOIN #__jticketing_types  AS types ON  oitems.type_id=types.id
		INNER JOIN #__jticketing_events AS events ON  types.eventid=events.id
		WHERE oitems.payment_status IN('DP') AND ordera.event_details_id=".$eventid." GROUP BY ordera.event_details_id";//die;
		$this->_db->setQuery($query);
		return $data = $this->_db->loadResult();

  }

   function confirmcount($eventid){
	   $query = "SELECT  count(oitems.id) AS soldtickets FROM #__jticketing_order_items AS
			oitems INNER JOIN #__jticketing_order as ordera ON  ordera.id=oitems.order_id
			INNER JOIN #__jticketing_types  AS types ON  oitems.type_id=types.id
			INNER JOIN #__jticketing_events AS events ON  types.eventid=events.id
			WHERE oitems.payment_status IN('C') AND ordera.event_details_id=".$eventid." GROUP BY ordera.event_details_id";//die;

	  $this->_db->setQuery($query);
		return $data = $this->_db->loadResult();
  }


}
