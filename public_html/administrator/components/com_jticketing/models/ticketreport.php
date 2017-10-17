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



class jticketingModelTicketreport extends JModelLegacy
{
	var $_total = null;
	var $_pagination = null;
	function __construct()
  {
 	parent::__construct();
 
	global $mainframe, $option;
 $mainframe = JFactory::getApplication();
		 $input=JFactory::getApplication()->input;
		$option = $input->get('option');
	// Get pagination request variables
	$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart=$input->get('limitstart','0','INT');
 
	// In case limit has been changed, adjust it
	$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
 
	$this->setState('limit', $limit);
	$this->setState('limitstart', $limitstart);
  }

 
    function getData()
    {
		$user =JFactory::getUser();
		 $query = "SELECT events.id, 
					count( events.id ) AS ncnt, 
					events.startdate, 
					events.enddate,
					events.title, 
					events.creator, 
					ticket.ticket_price, 
					sum(ticket.ticket_price) AS nprice, 
					sum(ticket.fee) AS nfee, 
					eventdetails.eventid 
					FROM #__community_events AS events 
					LEFT JOIN #__jticketing_events_xref AS eventdetails 
					ON events.id = eventdetails.eventid 
					LEFT JOIN #__ticket_sales AS ticket 
					ON eventdetails.eventid = ticket.event_details_id
					WHERE  ticket.transaction_id IS NOT NULL AND  ticket.status='C'	 
					GROUP BY events.id";
			
		$this->_db->setQuery($query);
		$result = $this->_db->loadobjectlist();
		if($this->getState('limit') > 0) {
				$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit')); 

			} else {
				$this->_data = $result;
			}
			
		$query = "SELECT sum(amount) AS nsum FROM #__jticketing_ticket_payouts WHERE  status='1' AND user_id ='".$user->id."' GROUP BY user_id";
			
		$this->_db->setQuery($query);
		$sumresult = $this->_db->loadResult();	
		$this->_data['0']->sum=$sumresult;
	  return $this->_data;
	}
	
	function getTotal()
  {
  	$query = "SELECT events.id, 
					count( events.id ) AS ncnt, 
					events.startdate, 
					events.enddate,
					events.title, 
					events.creator, 
					ticket.ticket_price, 
					sum(ticket.ticket_price) AS nprice, 
					sum(ticket.fee) AS nfee, 
					eventdetails.eventid 
					FROM #__community_events AS events 
					LEFT JOIN #__jticketing_events_xref AS eventdetails 
					ON events.id = eventdetails.eventid 
					LEFT JOIN #__ticket_sales AS ticket 
					ON eventdetails.eventid = ticket.event_details_id		
					WHERE  ticket.transaction_id IS NOT NULL AND  ticket.status='C'	 		 
					GROUP BY events.id";
	 	if($this->getState('limit') > 0) {
				$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit')); 

			} else {
				$this->_data = $result;
			}
 		return $this->_total;
  }

function getFilter()
  {
  		$input=JFactory::getApplication()->input;
		$post=$input->post;
		$mainframe = JFactory::getApplication();
		$option = $input->get('option');
		$eventid = $input->get('event','','INT');
  		$user = JFactory::getUser();
  		
  		$query = "SELECT id,title FROM #__community_events 
				  WHERE creator = {$user->id}";

		$this->_db->setQuery($query);
		$this->_data = $this->_db->loadObjectList();
		
		$options = array();
		$options[] = JHtml::_('select.option','0','Select Event');
		
		$std_opt = 'class="inputbox" onchange="if(this.value != 0)document.adminForm.submit();"';
   		$list = JHtml::_('select.genericlist',$options, 'selevent', $std_opt, 'value', 'text', $post->get('selevent'));
		return $list;		  
  }
 function getPagination()
  {
 	if (empty($this->_pagination)) {
 	    jimport('joomla.html.pagination');
 	    $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
 	}
 	return $this->_pagination;
  }
	 
}
 
