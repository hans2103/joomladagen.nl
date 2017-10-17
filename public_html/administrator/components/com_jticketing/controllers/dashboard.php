<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class jticketingControllerDashboard extends JControllerLegacy
{
	function __construct() 	{
		
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add' , 'edit' );
	}

	
	
	
function SetsessionForGraph()
	{
		$periodicorderscount='';
	 	$fromDate =  $_GET['fromDate'];
	 	$toDate =  $_GET['toDate'];
		$periodicorderscount=0;
		
		$session =& JFactory::getSession();
		$session->set('jticketing_from_date', $fromDate);
		$session->set('jticketing_end_date', $toDate);
		
		$model = $this->getModel('dashboard');		
		$statsforpie=$model->statsforpie();
		$periodicorderscount=$model->getperiodicorderscount();
		$session->set('statsforpie', $statsforpie);
		$session->set('periodicorderscount', $periodicorderscount);
		
		header('Content-type: application/json');
	  	echo (json_encode(array("statsforpie"=>$statsforpie,
	  	
	  				)));
	  
		jexit();
	}
	
	function makechart()
	{
		$month_array_name = array(JText::_('SA_JAN'),JText::_('SA_FEB'),JText::_('SA_MAR'),JText::_('SA_APR'),JText::_('SA_MAY'),JText::_('SA_JUN'),JText::_('SA_JUL'),JText::_('SA_AUG'),JText::_('SA_SEP'),JText::_('SA_OCT'),JText::_('SA_NOV'),JText::_('SA_DEC')) ;
		$session =& JFactory::getSession();
		$jticketing_from_date='';
		$jticketing_end_date='';
		$statsforbar='';
		$jticketing_from_date= $session->get('jticketing_from_date', '');
		$jticketing_end_date=$session->get('jticketing_end_date', '');
		$total_days = (strtotime($jticketing_end_date) - strtotime($jticketing_from_date)) / (60 * 60 * 24);	
		$total_days=$total_days+1;
		$statsforbar = $session->get('statsforbar','');
		$statsforpie = $session->get('statsforpie','');
		$periodicorderscount=$session->get('periodicorderscount');
		$imprs=0;
		$clicks=0;
		
		
		$emptylinechart=0;
		$barchart='';
		$fromDate= $session->get('jticketing_from_date', '');
		$toDate=$session->get('jticketing_end_date', '');
	 
		$dateMonthYearArr = array();
		$fromDateSTR = strtotime($fromDate);
		$toDateSTR = strtotime($toDate);
		$pending_orders=$confirmed_orders=$refund_orders=0;
			if(empty($statsforpie))
			{
				$barchart=JText::_('NO_STATS');
				$emptylinechart=1;
			}
			else
			{
			  	if(!empty($statsforpie['P']))
				{
					
						$pending_orders= $statsforpie['P'];
				}
			  	
				if(!empty($statsforpie['C']))
				{
					
						$confirmed_orders= $statsforpie['C'];
				}
				if(!empty($statsforpie['D']))
				{
					
						$denied_orders= $statsforpie['D'];
				}					
				if(!empty($statsforpie['RF']))
				{
					
						$refund_orders= $statsforpie['RF'];
				}		  
			}
			
		header('Content-type: application/json');
		  	echo (json_encode(array("pending_orders"=>$pending_orders,
		  						"confirmed_orders"=>$confirmed_orders,
		  						"refund_orders"=>$refund_orders,
		  						"periodicorderscount"=>$periodicorderscount,
		  						"emptylinechart"=>$emptylinechart
		  						)));
		  	
		  	jexit();
	}
}
