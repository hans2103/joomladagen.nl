<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
	defined('_JEXEC') or die('Restricted access');
ini_set('memory_limit', '1000M');
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.html.html' );
jimport( 'joomla.html.parameter' );
jimport( 'joomla.utilities.date');

class jticketingintegrationhelper
{

	function __construct()
    {
			$this->jticketingmainhelper=new jticketingmainhelper();


   	}
	/*
	* This event is triggered when compaign is created
	Inputs-
	$varscompaigdetails(title,location,summary,description,creator,startdate,enddate,available,avatar,thumb);
	$varsgivebackdetails(title,desc,price,vailable);

	Outputs-

	jgive_jticketing_integration_compaigns Table structure
	eventid   int(10)
	compaignid  int(10)
	*
    jgive_jticketing_integration_compaigns
	* tickettypeid
	*  givebackid
	*/

   	function createEvent($compaigndetails,$CampaignGivebacks){


		$db=JFactory::getDBO();
		$user=JFactory::getUser();
		$userid=$user->id;
		//save campaign details
		$obj = new stdClass();		// object for events table
		$obj->id='';

		$objl = new stdClass();		// object foe integration table
		$objl->id='';

		$objtype = new stdClass();		// Object for type table
		$objtype->id='';

		$objeventxref = new stdClass();		// Object for type table
		$objeventxref->id='';

		$obj->creator=$compaigndetails->creator_id;
		$obj->title=$compaigndetails->title;
		$obj->summary=$compaigndetails->short_description;
		$obj->description=$compaigndetails->long_description;
		$obj->location=$compaigndetails->country.','.$compaigndetails->state.','.$compaigndetails->city;
		$obj->startdate=$compaigndetails->start_date;
		$obj->enddate=$compaigndetails->end_date;

		if($CampaignGivebacks['0'])
		{
			$count=0;
			foreach($CampaignGivebacks['0'] as $giveback){
			$count++;
			}
		}
		$obj->available=$count;
		$obj->published=1;
		$obj->avatar=$compaigndetails->creator_id;
		if($compaigndetails->imgpath)
		{
			$obj->thumb=$compaigndetails->imgpath;
			$obj->avatar=$compaigndetails->imgpath;
		}

		if(!$db->insertObject( '#__jticketing_events',$obj,'id'))
		{

			echo $db->stderr();
			//return false;
		}

		$eventdetails->eventid=$evid=$db->insertid();


		//saving the event id into integration table
		$objl->eventid=$evid;
		$objl->source='com_jticketing';
		if(!$db->insertObject( '#__jticketing_integration_xref',$objl,'id')){

			echo $db->stderr();
			//return false;
		}

		$eventdetails->integrationid=$db->insertid();	// foreign key event_xref table


		return $eventdetails;

	}


	function updateEvent($cid,$compaigndetails,$CampaignGivebacks){
		//Get Integration Details From table
		$integr_data=$this->getEventIntegrationDetails($cid,$compaigndetails,$CampaignGivebacks);
		$db=JFactory::getDBO();
		$user=JFactory::getUser();
		$userid=$user->id;
		//save campaign details
		$obj = new stdClass();
		// object for events table

		$objl = new stdClass();		// object foe integration table
		$objl->id='';

		$objtype = new stdClass();		// Object for type table
		$objtype->id='';

		$objeventxref = new stdClass();		// Object for type table
		$objeventxref->id='';

		$obj->creator=$compaigndetails->creator_id;
		$obj->title=$compaigndetails->title;
		$obj->summary=$compaigndetails->short_description;
		$obj->description=$compaigndetails->long_description;
		$obj->location=$compaigndetails->country.','.$compaigndetails->state.','.$compaigndetails->city;
		$obj->startdate=$compaigndetails->start_date;
		$obj->enddate=$compaigndetails->end_date;

		if($CampaignGivebacks['0'])
		{
			$count=0;
			foreach($CampaignGivebacks['0'] as $giveback){
			$count++;
			}
		}
		$obj->available=$count;
		$obj->published=1;
		$obj->avatar=$compaigndetails->creator_id;
		if($compaigndetails->imgpath)
		{
			$obj->thumb=$compaigndetails->imgpath;
			$obj->avatar=$compaigndetails->imgpath;
		}

		if(isset($integr_data->compaignsxref['0']->event_integration_id)){ //update existing data
			$eventinfo->integrationid=$integr_data->compaignsxref['0']->event_integration_id;
			$eventid=$this->getEventidFromintegrationID($integr_data->compaignsxref['0']->event_integration_id);
			$obj->id=$eventid;
			if(!$db->updateObject( '#__jticketing_events',$obj,'id'))
			{

			}

			$this->updateticketTypes($compaigndetails,$CampaignGivebacks,$eventinfo);

		}
		else if(!empty($CampaignGivebacks))
		{
			if(!$db->insertObject( '#__jticketing_events',$obj,'id'))//insert new data
			{

			}

			$eventdetails->eventid=$db->insertid();

			//saving the event id into integration table
			$objl->id='';
			$objl->eventid=$eventdetails->eventid;
			$objl->source='com_jticketing';
			if(!$db->insertObject( '#__jticketing_integration_xref',$objl,'id')){
				echo $db->stderr();
			}

			$eventinfo->integrationid=$db->insertid();	// foreign key event_xref table

			//Create new ticket types
			$this->createticketTypes($compaigndetails,$CampaignGivebacks,$eventinfo);


		}
		$eventdetails->eventid=$evid=$eventid;
		return $eventdetails;

	}

	function deleteEvent(){




	}

	function getEventidFromintegrationID($intgrid)
	{
		$db=JFactory::getDBO();
		$query="SELECT *
		FROM `#__jticketing_integration_xref` AS c
		WHERE c.id=".$intgrid;
		$db->setQuery($query);
		return $eventid=$db->loadResult();
	}

	function getEventIntegrationDetails($cid,$compaigndetails,$CampaignGivebacks)
	{
		$db=JFactory::getDBO();
		$query="SELECT *
		FROM `#__jg_jticketing_compaignsxref` AS c
		WHERE c.compaign_id=".$cid;
		$db->setQuery($query);
		$result->compaignsxref=$db->loadObjectList();
		return $result;


	}






	//Insert order items table of Jticketing
	function insertoOrderItems_jticketing($oid,$typeid)
	{
			$db 	= JFactory::getDBO();
			$resdetails = new stdClass();
			$resdetails->id='';
			$resdetails->order_id=$oid;
			$resdetails->ticketcount=1;
			$resdetails->type_id=$typeid;

			if (!$db->insertObject( '#__jticketing_order_items', $resdetails, 'id' )) {
				echo $db->stderr();

			}
	}

	//Insert to billing table of Jticketing
	function billingaddr_jticketing($uid,$data,$insert_order_id){

		$db 	= JFactory::getDBO();
		$db->setQuery('SELECT order_id FROM #__jticketing_users WHERE order_id='.$insert_order_id);
		$order_id=(string)$db->loadResult();
		if($order_id)
		{
			$query = "DELETE FROM #__jticketing_users	WHERE order_id=".$insert_order_id;
			$db->setQuery( $query );
			if (!$db->execute()) {

			}

		}

		$row = new stdClass;
		$row->user_id=$data->user_id;
		$row->user_email=$data->email;
		$row->address_type='BT';
		$row->firstname=$data->first_name;
		$row->lastname=$data->last_name;
		$row->country_code=$data->country;
		if(!empty($data->vat_num))
		$row->vat_number=$data['vat_num'];
		$row->address=$data->address;
		$row->city=$data->city;
		$row->state_code=$data->state;
		$row->zipcode=$data->zip;
		$row->phone=$data->phone;
		$row->approved='1';
		$row->order_id=$insert_order_id;
		if(!$db->insertObject('#__jticketing_users', $row, 'id'))
			{
				echo $this->_db->stderr();
				return false;
			}
	}


}
