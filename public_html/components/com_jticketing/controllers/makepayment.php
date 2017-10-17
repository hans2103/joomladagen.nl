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

class jticketingControllermakepayment extends JControllerLegacy
{
	
 	function setOrder()
	{
		JLoader::import('frontendhelper', JPATH_SITE.DS.'components'.DS.'com_jticketing'.DS.'helpers');
		$mainHelper =  new jticketingfrontendhelper();
		$id = JRequest::getInt('id');
		$target_data = $mainHelper->getbookingDetails($id );
		$postdata 	= JRequest::get( 'post' );
		$total = '';
		foreach($postdata as $key=>$value)
		{
			foreach($target_data['order_item'] as $data)
			{
				//echo $data->id;
				if($data->id == $key )
				{
					$total +=$value;
					$data->price =$value;
					
				} 
			}
		}
		$target_data['order'][0]->totalprice = $total;
		$target_data['order'][0]->parent_id = $id;
		/*
		 * Array
(
    [order] => Array
        (
            [0] => stdClass Object
                (
                    [id] => 12
                    [name] => Super User
                    [order_id] => JT-00012
                    [order_amount] => 500.00
                    [original_amount] => 1100.00
                    [status] => P
                )

        )

    [order_item] => Array
        (
            [0] => stdClass Object
                (
                    [id] => 8
                    [order_id] => 12
                    [type_id] => 5
                    [ticket_price] => 600.00
                    [total] => 300.00
                    [name] => Sachin
                    [payment_status] => P
                    [price] => 12
                )

            [1] => stdClass Object
                (
                    [id] => 9
                    [order_id] => 12
                    [type_id] => 5
                    [ticket_price] => 500.00
                    [total] => 200.00
                    [name] => Sachin
                    [payment_status] => P
                    [price] => 13
                )

        )

)
		 * 
		 */
		//echo"<pre>";print_r($target_data);
		//die();
		$model = $this->getModel('makepayment');
		
		$val =$model->saveBalance($target_data);
		$redirect=JUri::base().'index.php?option=com_jticketing&view=allticketsales&layout=pay&id='.$val;	
		$this->setRedirect( $redirect);
		
		
	}
	function cancel()
	{
		$redirect=JUri::base().'index.php/component/jticketing/?view=allticketsales&layout=myevent&Itemid=232';	
		$this->setRedirect( $redirect);
	}
}
