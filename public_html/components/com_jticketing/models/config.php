<?php
/**
* @package		Broadcast
* @copyright	Copyright © 2012 - All rights reserved.
* @license		GNU/GPL
* @author		TechJoomla
* @author mail	extensions@techjoomla.com
* @website		http://techjoomla.com
*/
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class BroadcastModelconfig extends JModelLegacy
{

	function getlist()
	{
		$qry = "SELECT distinct(app) FROM #__community_activities WHERE app<>'' order by app";
		$this->_db->setQuery($qry);
		$list = $this->_db->loadObjectList();
		return $list;	
	}
	
	function getsubscribedlist()
	{
		$user 	= JFactory::getUser();
		$qry 	= "SELECT broadcast_activity_config,broadcast_rss  FROM #__broadcast_config  WHERE  user_id  = {$user->id} ";
		$this->_db->setQuery($qry);
	 	$sub_list 	= $this->_db->loadObject();		 	
	 	return $sub_list;
	}
	function renderHTML_other()
	{
		$dispatcher =JDispatcher::getInstance();
		JPluginHelper::importPlugin('techjoomlaAPI');
		$grt_response = $dispatcher->trigger('get_otherAccountData');
		return $grt_response['0'];	
			
	}

	function save()
	{	
		//require(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_broadcast'.DS.'config'.DS.'config.php');
		$params=JComponentHelper::getParams('com_broadcast');
		$integration=$params->get('integration');
		$user = JFactory::getUser();
		$row = new stdClass;
		$data = JRequest::get('post');
		$row->user_id = $user->id;
				
		if(!$user->id)		
			return false;
			
		if($data['broadcast_activity'])
		$broadcast_activity = implode('|', $data['broadcast_activity']); 
		else
		$broadcast_activity ='';
		
		
		$i=0;


			
		foreach($data['rss_title'] as $key=>$title){
			$rss[$i]['title']=$title;
			$rss[$i]['link']=$data['rss_link'][$key];
			$i++;
		
		}

				
		$row->broadcast_activity_config = $broadcast_activity;
		$row->broadcast_rss = json_encode($rss);
		$qry = "SELECT user_id FROM #__broadcast_config WHERE  user_id = {$user->id}";
		$this->_db->setQuery($qry);
	 	$userexists = $this->_db->loadResult();		
	 	if($userexists)
	 	{
			if(!$this->_db->updateObject('#__broadcast_config', $row, 'user_id'))
			{
				echo $this->_db->stderr();
				return false;
			}
		} 
		else
		{
			if(!$this->_db->insertObject('#__broadcast_config', $row, 'user_id'))
			{
				echo $this->_db->stderr();
				return false;
			}		
		}
		return true;
	}
	function checkparamexist()
	{
		$qry = "SELECT config FROM #__broadcast_config WHERE  user_id = {$user->id}";
		$this->_db->setQuery($qry);
	 	$config = $this->_db->loadResult();
	 	$configarray=json_decode($config,true)		;
	}	
	
	function saveotheraccounts()
	{
	
	$session =& JFactory::getSession();
	
		$user=JFactory::getUser();
		$dataids = JRequest::get('post');
		
		$otherdataArr=$session->get("API_otherAccountData");

		$i=0;

		foreach($otherdataArr['data'] as $key=>$otherdata)
		{

			foreach($otherdata as $singledata)
			{

					$singledataids[$i]['key']=$key;
					$singledataids[$i]['id']=$singledata['id'];
					$singledataids[$i]['data']=$singledata;

					$i++;
				}
			
		}
		
		

				
				
		foreach($dataids as $dts){
			foreach($dts as  $dt){
			foreach($singledataids as  $singledataid){
					if($singledataid['id']==$dt){
					$finaldata['paramsdata'][$singledataid['key']][]=$singledataid;
												
					}

					
			}
		}
		}


		$row->user_id=$user->id;
		$row->params = json_encode($finaldata);
		
	 	if($this->_db->updateObject('#__broadcast_config', $row, 'user_id'))
			{
				echo $this->_db->stderr();
				return false;
			}
	
				return 1;
	
	}


}
