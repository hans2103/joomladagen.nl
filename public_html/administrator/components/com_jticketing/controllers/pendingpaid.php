<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
	
// no direct access
	defined('_JEXEC') or die('Restricted access'); 
require_once( JPATH_COMPONENT.DS.'controller.php' );

jimport('joomla.application.component.controller');

class jticketingControllerpendingpaid extends JControllerLegacy
{

	function __construct()
	{
		parent::__construct();
		
	}
	
	function save()
	{
		$input=JFactory::getApplication()->input;
		$task=$input->get('task');
 		switch ($task) 
		{
			case 'cancel':
			$this->setRedirect( 'index.php?option=com_jticketing');
		}	
	}
	
	function cancel()
	{
			$input=JFactory::getApplication()->input;
			$task=$input->get('task');
			switch ($task) 
			{
				case 'cancel':
				$this->setRedirect( 'index.php?option=com_jticketing');
			}	
	}
	
	function csvexport(){
		
		$jticketingmainhelper = new jticketingmainhelper();
		$jticketingfrontendhelper=new jticketingfrontendhelper();
		$model=$this->getModel('pendingpaid');

		$com_params=JComponentHelper::getParams('com_jticketing');
		$currency = $com_params->get('currency');
		$Data 	=$model->getData();
		foreach($Data as &$data)
		{
			
			$data->pendingcount=$model->pendingcount($data->eventid);
			$data->confirmcount=$model->confirmcount($data->eventid);
		}
	
		
		$csvData = null;
		$csvData_arr[]=JText::_('COM_JTICKETING_EVENT_NAME');
		$csvData_arr[]=JText::_('COM_JTICKETING_NUMBER_OF_SEATS');
		$csvData_arr[]=JText::_('COM_JTICKETING_FULLY_PAID_SEATS');
		$csvData_arr[]=JText::_('COM_JTICKETING_PENDING_SEATS');
		

        $filename = "Jt_attendees_".date("Y-m-d_H-i",time());

		header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: csv" . date("Y-m") .".csv");
        header("Content-disposition: filename=".$filename.".csv");
		$totalnooftickets=$totalprice=$totalcommission=$totalearn=0;
		
		$csvData.=implode(';',$csvData_arr);
		$csvData .= "\n";
		echo $csvData;

		/*'P'=>JText::_('JT_PSTATUS_PENDING'),
'C'=>JText::_('JT_PSTATUS_COMPLETED'),
		'D'=>JText::_('JT_PSTATUS_DECLINED'),
		'E'=>JText::_('JT_PSTATUS_FAILED'),
		'UR'=>JText::_('JT_PSTATUS_UNDERREVIW'),
		'RF'=>JText::_('JT_PSTATUS_REFUNDED'),
		'CRV'=>JText::_('JT_PSTATUS_CANCEL_REVERSED'),
		'RV'=>JText::_('JT_PSTATUS_REVERSED'),
);*/
		
        $csvData='';

       
       
		
        foreach($Data as $data ){
			
			$phone=$email='';
			if(!$data->confirmcount)
			$data->confirmcount=0;
			if(!$data->pendingcount)
			$data->pendingcount=0;
			$csvData=$doc_submitted=$checkin='';
			$csvData_arr1=array();
			
			$csvData_arr1[]= ucfirst($data->title);
			$csvData_arr1[]=$data->pendingcount+$data->confirmcount;
			$csvData_arr1[]= $data->confirmcount;
			$csvData_arr1[]= $data->pendingcount;
			$csvData=implode(';',$csvData_arr1);

			echo	$csvData."\n";

        }
        
        	echo	$csvData."\n";	
			jexit();
	}
	
	
	
}	
