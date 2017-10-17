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

class jticketingControllerattendees extends JControllerLegacy
{

	function __construct()
	{
		parent::__construct();
		
	}
	function csvexport(){
		$com_params=JComponentHelper::getParams('com_jticketing');
		$currency = $com_params->get('currency');
		
		$model=$this->getModel('attendees');
		$model_results=$model->getData();

		$db =JFactory::getDBO();
		$query = "SELECT d.ad_id, d.ad_title, d.ad_payment_type, d.ad_creator,d.ad_startdate, d.ad_enddate, i.processor, i.ad_credits_qty, i.cdate, i.ad_amount,i.status,i.id FROM #__ad_data AS d RIGHT JOIN #__ad_payment_info AS i ON d.ad_id = i.ad_id";
		$db->setQuery($query);
		$results = $db->loadObjectList();
		$csvData = null;
        $csvData.= "Attender_Name,Bought_On,Ticket_Type,Ticket_Rate,Number_of_tickets_bought,Total_Amount_(A-B)";
        $csvData .= "\n";
        $filename = "Jt_attendees_".date("Y-m-d_H-i",time());
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: csv" . date("Y-m") .".csv");
        header("Content-disposition: filename=".$filename.".csv");

				$totalnooftickets=$totalprice=$totalcommission=$totalearn=0;
				
        foreach($model_results as $result ){

						$totalnooftickets=$totalnooftickets+$result->ticketcount;
						$totalprice=$totalprice+$result->amount;
						$totalearn=$totalearn+$result->totalamount;

							$csvData .= '"'.$result->name.'"'.',';
							$csvData .= '"'.(JVERSION<"1.6.0" ? JHtml::_( 'date', $result->cdate, '%Y/%m/%d') : JHtml::_( 'date', $result->cdate, "Y-m-d")). '"'.',';
							$csvData .=	'"'.$result->ticket_type_title.'"'.',';
							$csvData .= '"'.$result->amount. ' '.$currency.'"'.',';
							$csvData .= '"'.$result->ticketcount.'"'.',';
							$csvData .=	'"'.$result->totalamount.$currency.'"';
		
        	
				$csvData .= "\n";
        	
        }
				$csvData .= '" "," "," ","'.JText::_('TOTAL').'","'.number_format($totalnooftickets, 2, '.', '').'","'.number_format($totalprice, 2, '.', '').$currency.'"';
				$csvData .= "\n";
		print $csvData;
	exit();
	}
}	
