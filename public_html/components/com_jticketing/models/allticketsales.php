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



class jticketingModelallticketsales extends JModelLegacy
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
		$this->jticketingmainhelper=new jticketingmainhelper();

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
		$filter_order='';
		$filter_order_Dir='';
		$filter_type=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order','goal_amount','cmd');
		$filter_order_Dir=$mainframe->getUserStateFromRequest('filter_order_Dir','filter_order_Dir','desc','word');

		if($filter_type=='eticketscount')
		{
			$this->_data=$this->jticketingmainhelper->multi_d_sort($this->_data,$filter_type,$filter_order_Dir);
		}
		return $this->_data;

	}

	function _buildQuery()
	{

		global $mainframe, $option;  //
		$input=JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$where=$this->_buildContentWhere();
		$xrefid=$this->_buildContentXrefid();
		$user=JFactory::getUser();
		$db=JFactory::getDBO();  //
		$integration=$this->jticketingmainhelper->getIntegration();
		$query=$this->jticketingmainhelper->getSalesDataSite($xrefid,$user->id,$where);
		$query.=" GROUP BY a.event_details_id ";
		$filter_order=''; //
		$filter_order_Dir=''; //
		$qry1='';
		$filter_order=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order','title','cmd');  //
		$filter_order_Dir=$mainframe->getUserStateFromRequest($option.'filter_order_Dir','filter_order_Dir','desc','word');//

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
						case 'title':$query.=" ORDER BY event.$filter_order $filter_order_Dir";
						break;

						case 'cdate':$query.=" ORDER BY a.$filter_order $filter_order_Dir";
						break;

					}
				}
			}
		}
		return $query;

	}

	function _buildContentWhere()
	{

			global $mainframe, $option;
			$integration=$this->jticketingmainhelper->getIntegration();

			$input=JFactory::getApplication()->input;
			$post=$input->post;

			$mainframe = JFactory::getApplication();


			$xrefid='';
			$option =$input->get('option');
			$eventid=$search_event = $mainframe->getUserStateFromRequest($option.'search_event', 'search_event', '', 'string' );
			$where="";
			if($eventid)
			{
				$xrefid=$this->jticketingmainhelper->getEventrefid($eventid);
				if($xrefid)
				{
					$where[]=" AND a.event_details_id={$xrefid}";
				}
				return $where1=(count($where)?''. implode(' AND ',$where ):'');
			}
			else
			return '';

	}


	function _buildContentXrefid()
	{


			$integration=$this->jticketingmainhelper->getIntegration();

			$input=JFactory::getApplication()->input;
			$post=$input->post;
			global $mainframe, $option;
			$mainframe = JFactory::getApplication();
			$xrefid='';
			$option =$input->get('option');

			$eventid=$search_event = $mainframe->getUserStateFromRequest( $option.'search_event', 'search_event', '', 'string' );
			if(!$eventid)
			{
				$eventid = $input->get('event','','INT');
				if($eventid)
				$xrefid=$this->jticketingmainhelper->getEventrefid($eventid);

				return $xrefid;
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

		$query=$this->jticketingmainhelper->getEventName($eventid);
		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadResult();

		return $this->_data;
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
