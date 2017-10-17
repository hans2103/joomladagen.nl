<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
	defined('_JEXEC') or die('Restricted access');
require_once( JPATH_COMPONENT.'/controller.php' );
require_once( JPATH_COMPONENT.'/helpers/main.php' );


jimport('joomla.application.component.controller');

class jticketingControllerjticketingapi extends JControllerLegacy
{

	function __construct()
	{
		parent::__construct();

	}

	function Geteventdetails()
	{
		header('Content-type: application/json');
		$document =& JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		$input  = JFactory::getApplication()->input->get();
		$eventid = $input->get('eventid','','INT');

		if(empty($eventid))
        {
			$obj->success = 0;
			$obj->message ='Please select valid event';

		}

		$eventdata=jticketingmainhelper::GetUserEventsAPI('',$eventid);
		foreach($eventdata as &$eventdata)
		{
			$eventdata->avatar=JUri::base().$eventdata->avatar;
			$eventdata->totaltickets=jticketingmainhelper::GetTicketcount($eventid);
		}

        $obj = new stdClass();
        if($eventdata)
		{
			$obj->success = "1";
			$obj->data = $eventdata;
        }
		else
		{
			$obj->success = "0";
			$obj->data = "Invalid event id";
		}

		echo json_encode(	$obj );
		jexit();
	}

	function checkin()
	{
		header('Content-type: application/json');
		$document =& JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		$input  = JFactory::getApplication()->input->get();
		$ticketidstr = $input->get('ticketidstr','','STRING');


        if(empty($ticketidstr))
        {
			$obj->success = 0;
			$obj->message ='Please select valid ticket';
			echo json_encode(	$obj );
			jexit();

		}
		$ticketidarr=explode('-',$ticketidstr);
		$oid=$ticketidarr[0];
		$orderitemsid=$ticketidarr[1];
		$obj = new stdClass();
		$jticketingmainhelper = new jticketingmainhelper();
		$result= $jticketingmainhelper->GetActualTicketidAPI($oid,$orderitemsid,'');
		$originalticketid=JText::_("TICKET_PREFIX").$ticketidstr;
		$attendernm=$result[0]->name;
		if(empty($result))
		{
			$obj->success = 0;
			$obj->message ='Invalid ticket';
			echo json_encode(	$obj );
			jexit();
		}
		else
		{
			$checkindone= $jticketingmainhelper->GetCheckinStatusAPI($orderitemsid);
			if($checkindone)
			{
				 $obj->success = 0;
				 $obj->message ='Duplicate check in for '.$attendernm;
				 echo json_encode(	$obj );
				jexit();
			}
			else
			{
				$obj->success = $jticketingmainhelper->DoCheckinAPI($orderitemsid,$result);

				if($obj->success)
				{


					$app =JFactory::getApplication();
					$mailfrom = $app->getCfg('mailfrom');
					$fromname = $app->getCfg('fromname');
					$sitename = $app->getCfg('sitename');
					$message=jticketingControllerjticketingapi::getMessage();
					$message_subject=JText::_("CHECKIN_MESSAGE");
					$eventnm= $jticketingmainhelper->getEventTitle($oid);
					$message_subject= stripslashes($message_subject);
					$message_subject	= str_replace("[EVENTNAME]", $eventnm,$message_subject);

					$message_body	= stripslashes($message);
					$message_body	= str_replace("[EVENTNAME]", $eventnm,$message_body);
					$message_body	= str_replace("[NAME]", $attendernm,$message_body);
					$message_body	= str_replace("[TICKETID]", $originalticketid,$message_body);
					$result=JFactory::getMailer()->sendMail($fromname,$mailfrom,$result[0]->email,"You've checked in to Joomla Day India 2013!",$message_body,$html=1,null,null,'');

				}

				$obj->message ='Ticket check in success for '.$attendernm;
				echo json_encode(	$obj );
				jexit();
			}
		}


	}


	function Getuserevents(){
		header('Content-type: application/json');
		$document =& JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		$input  = JFactory::getApplication()->input->get();
		$userid = $input->get('userid','','STRING');

		if(empty($userid))
		{
			$obj->success = 0;
			$obj->message ='Please select valid user';
			echo json_encode(	$obj );
			jexit();
		}
		$jticketingmainhelper = new jticketingmainhelper();
		$eventdata= $jticketingmainhelper->GetUserEventsAPI($userid,'');
		$obj = new stdClass();
		if($eventdata)
		{
			$obj->success = "1";
			$obj->data = $eventdata;
		}
		else
		{
			$obj->success = "0";
			$obj->data = "Invalid user or no data for this user";
		}
			echo json_encode(	$obj );
			jexit();
	}

	function Getticketlist(){
		header('Content-type: application/json');
		$document =& JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		$lang =& JFactory::getLanguage();
		$extension = 'com_jticketing';
		$base_dir = JPATH_SITE;

		$lang->load($extension, $base_dir);
		$input  = JFactory::getApplication()->input->get();
		$eventid = $input->get('eventid', 0, 'STRING');
		$var = $input->get('attendtype', 'all', 'STRING');

		$jticketingmainhelper = new jticketingmainhelper();
		$results= $jticketingmainhelper->GetOrderitemsAPI($eventid,$var);

        if($eventid)
		{
			$data = array();
			foreach($results as &$orderitem)
			{
				$obj = new stdClass();
				$obj->checkin = $jticketingmainhelper->GetCheckinStatusAPI($orderitem->order_items_id);
				$obj->ticketid = $orderitem->oid.'-'.$orderitem->order_items_id;
				$obj->attendee_nm =$orderitem->name;
				$obj->ticket_type_title =$orderitem->ticket_type_title;
				$obj->event_title = $orderitem->event_title;
				$obj->ticket_prefix=JText::_("TICKET_PREFIX");

				if($var=="all")
				{
					$data[] = $obj;
				}

				if($var=="attended" && $obj->checkin==1)
				{
					$data[] = $obj;
				}
				else if($var=="notattended" && $obj->checkin==0)
				{
					$data[] = $obj;
				}

				$i++;
			}

		    $fobj = new stdClass();
            $fobj->total = count($data);
			$fobj->data = $data;

        }
		else
		{
			$fobj->success = "0";
			$fobj->data = "Invalid event id";
		}
			echo json_encode(	$fobj );
			jexit();

	}

	function authoriseattendeelist()
	{
		header('Content-type: application/json');
		$document =& JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		$input  = JFactory::getApplication()->input->get();
		$useremail = $input->get('useremail', 0, 'STRING');
		$useremail=trim($useremail);
		$var = $input->get('attendtype', 'all', 'STRING');
		$ticketidstr = $input->get('ticketid', '', 'STRING');


		if(empty($ticketidstr) OR empty($useremail))
		{
			$obj->success = 0;
			$obj->message ='Please select valid ticket or enter valid email ';
			echo json_encode(	$obj );
			jexit();
		}
		$ticketidarr=explode('-',$ticketidstr);
		$oid=$ticketidarr[0];
		$orderitemsid=$ticketidarr[1];
		$obj = new stdClass();
		$jticketingmainhelper = new jticketingmainhelper();
		$result= $jticketingmainhelper->GetActualTicketidAPI($oid,$orderitemsid,$useremail);
		if(empty($result))
		{
			$obj->success = 0;
			$obj->message ='Invalid ticket';
		}
		else
		{


				$obj->success = $jticketingmainhelper->Addtoattendeelist($orderitemsid,$result);
				if($obj->success)
				$obj->message ='Authorisation for Attendeelist Successful ';
				else
				$obj->message ='You are already authorised for attendeelist to this event ';



		}

		echo json_encode(	$obj );
		jexit();

	}

	function getAttendeeList()
	{
		header('Content-type: application/json');
		$document =& JFactory::getDocument();
		$document->setMimeEncoding('application/json');
		$lang =& JFactory::getLanguage();
		$extension = 'com_jticketing';
		$base_dir = JPATH_SITE;
		$lang->load($extension, $base_dir);
		$input  = JFactory::getApplication()->input->get();
		$useremail = $input->get('useremail', 0, 'STRING');
		$useremail=trim($useremail);
		$var = $input->get('attendtype', 'all', 'STRING');
		$ticketidstr = $input->get('ticketid', '', 'STRING');

		if(empty($eventid) OR empty($var))
		{
			$obj->success = 0;
			$obj->message ='Please select valid eventid or attendtype() ';
			echo json_encode(	$obj );
			jexit();
		}

		$jticketingmainhelper = new jticketingmainhelper();
		$results= $jticketingmainhelper->GetOrderitemsAPI($eventid,$var);

        if($eventid)
		{
			$data = array();

			foreach($results as &$orderitem)
			{
				$obj = new stdClass();
				$obj->checkin = $jticketingmainhelper->GetCheckinStatusAPI($orderitem->order_items_id);
				$obj->ticketid = $orderitem->oid.'-'.$orderitem->order_items_id;
				$obj->attendee_nm =$orderitem->name;
				$obj->ticket_type_title =$orderitem->ticket_type_title;
				$obj->event_title = $orderitem->event_title;
				$obj->ticket_prefix=JText::_("TICKET_PREFIX");

				if($var=="attended" && $obj->checkin==1)
				{
					$data[] = $obj;
				}
				else if($var=="notattended" && $obj->checkin==0)
				{
					$data[] = $obj;
				}
				$i++;
			}

		    $fobj = new stdClass();
            $fobj->total = count($data);
			$fobj->data = $data;

        }
		else
		{
			$fobj->success = "0";
			$fobj->data = "Invalid event id";
		}
			echo json_encode(	$fobj );
			jexit();


	}
	function getMessage(){
		return $message=JText::_("CHECKIN_MESSAGE");

	}




}
