<?php
// no direct access
defined( '_JEXEC' ) or die( ';)' );

jimport( 'joomla.application.component.model' );
jimport( 'joomla.database.table.user' );



class jticketingModelattendees extends JModelLegacy
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
		return $this->_data;

	}
	function _buildQuery()
	{
		$where=$this->_buildContentWhere();
		$jticketingmainhelper = new jticketingmainhelper();
		$query = $jticketingmainhelper->getAttendeesData();

		if($where)
		{
			$query.=$where;
		}
			$query.=" GROUP BY order_items_id";


		// FOR ORDER

		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$db=JFactory::getDBO();
		$filter_order='';
		$filter_order_Dir='';
		$qry1='';
		$filter_order=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order','title','cmd');
		$filter_order_Dir=$mainframe->getUserStateFromRequest($option.'filter_order_Dir','filter_order_Dir','desc','word');

		if($filter_order)
		{
			$qry1="SHOW COLUMNS FROM #__jticketing_order";

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
						$query.=" ORDER BY a.$filter_order $filter_order_Dir";
				}
			}
		}
		return $query;
	}

	function _buildContentWhere()
	{

		global $mainframe, $option;
		$input=JFactory::getApplication()->input;
		$jticketingmainhelper = new jticketingmainhelper();
		$integration = $jticketingmainhelper->getIntegration();

		$post=$input->post;
		$mainframe = JFactory::getApplication();
		$option = $input->get('option');
		$user = JFactory::getUser();
		$eventid = $input->get('event','','INT');
		$order_id = $input->get('order_id','','STRING');
		$id = $jticketingmainhelper->getIDFromOrderID($order_id);
		if(empty($id))
		$id=$order_id;

		$search_event = $mainframe->getUserStateFromRequest( $option.'search_event', 'search_event', '', 'string' );

		if(!empty($search_event)){
		  $eventid = JString::strtolower( $search_event);
			}
		$where="";
		if($eventid)
		{
			$intxrefidevid = $jticketingmainhelper->getEventrefid($eventid);


			$where.="  AND a.event_details_id = {$intxrefidevid}  ";
			if($id)
			$where.=" AND a.id={$id} ";

			return $where;
		}
		else return '';

	}

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
}
