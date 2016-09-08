<?php

/****************************************************************
 * @version			3.2.0											
 * @package			ticketmaster									
 * @copyright		Copyright © 2009 - All rights reserved.			
 * @license			GNU/GPL											
 * @author			Robert Dam										
 * @author mail		info@rd-media.org								
 * @website			http://www.rd-media.org							
 ***************************************************************/

## Direct access is not allowed.
defined('_JEXEC') or die();


/*

## Include the payment API class to do several actions after a payment: 

$path_include = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'payment.api.class.php';
include_once( $path_include );

## Check if the class exsists:

if (!class_exists('paymentAPI')) {
    exit("Class is not available to process transactions.");
}					

## Start the API to process everything.
## The API needs to be feeded by the ordercode that is used everywhere in Ticketmaster!

$payment = new paymentAPI( (int)$this->ordercode ); 		

## Example to call a function inside the class:
## Every function has the ordercode present as you have initiated the class with the ordercode above.

## Some functions can have more variables available by sending them. 
## More information about what can be sent is described per function:
## Example of function that requires more varibales:

$transactions = $payment->checkTempTransactionAmount($intTrxId);

## Example of function that doesn't need extra varibales:

$payment->updateOrder();

*/

class paymentAPI{					


	function __construct($eid){  
		
		## Setting the $eid as var
		$this->ordercode = $eid; 
		
	 }  

	 
	## inserting the temporary transaction to the temporary transaction tbale.
	public function insertTempTransaction($userid, $transid) {

		## Including the table transaction to store it.
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'tables');
		$row = JTable::getInstance('TempTransaction', 'Table');	
	
		## Prepare the row.
		$row->transaction_number = $transid;
		$row->userid 			 = (int)$userid;
		$row->ordercode			 = (int)$this->ordercode;
		$row->processed			 = '0';
		
		## Store data
		$transaction = $row->store();	

		if($transaction == true){
			return true;
		}else{
			return false;
		}

	}
	
	## update the temporary transaction to "processed"
	## Needs to feeded with the unique transaction ID
	## This transaction ID has been entered by yourself with the saveTransaction() functionality.
	public function updateTempTransaction($transid, $state=1) {
		
		$db      = JFactory::getDBO();
				
		$query = 'UPDATE #__ticketmaster_transactions_temp SET processed = '.(int)$state.' WHERE transaction_number = "'.$transid.'"';
		
		## Do the query now	
		$db->setQuery( $query );			

		## When query goes wrong.. Show message with error.
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}
		
		return true;			
		
	}	
	
	## Check the count of the temporary transactions
	## Every transaction maybe entered once. It will return false if there are more than 0 transactions.
	public function checkTempTransactionAmount($intTrxId) {

		## Connect DB:
		$db = JFactory::getDBO();
		
		## Doing the query to get the count of transactions for this one.
		$sql = 'SELECT * FROM #__ticketmaster_transactions_temp WHERE transaction_number = "'.$intTrxId.'" ';
		
		$db->setQuery($sql);
		$temp_transaction = $db->loadObjectList();
		
		$transactions = count($temp_transaction);
		
		return $transactions;
	
	}	
	
	
	## Check the count of the temporary transactions
	## Every transaction maybe entered once. It will return false if there are more than 0 transactions.
	public function getTempTransactionResult($intTrxId) {

		## Connect DB:
		$db = JFactory::getDBO();
		
		## Doing the query to get the count of transactions for this one.
		$sql = 'SELECT * FROM #__ticketmaster_transactions_temp WHERE transaction_number = "'.$intTrxId.'" ';
		
		$db->setQuery($sql);
		$temp_transaction = $db->loadObject();
		
		return $temp_transaction;
	
	}		
	
	## save the transaction details to the transaction table
	## transaction ID 	= VARCHAR(60) --> MUST be sanitized by yourself! 
	## amount 			= DOUBLE --> 0.90 / 12.34 are valid doubles
	## type 			= VARCHAR(50) --> type transaction information (EG: PayPal, IDEAL, Sofort) 
	## details 			= TEXT (may have unlimited transaction and must be sanitized by yourself!)
	public function saveTransaction($transid, $userid, $details, $amount, $type) {
	
		## Including the table transaction to store it.
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'tables');
		$row =& JTable::getInstance('transaction', 'Table');	
		
		## Prepare the row.
		$row->transid = $transid;
		$row->userid = (int)$userid;
		$row->details = $details;
		$row->amount = $amount;
		$row->type = $type;
		$row->orderid = (int)$this->ordercode;
		
		## Store data
		$storing_payment = $row->store();	
		
		if($storing_payment == true){
			return true;
		}else{
			return false;
		}
			
	
	}
	
	## update the order details in the ticketbox, feeded by the ordercode.
	public function updateOrder() {
		
		$db = JFactory::getDBO();
		
		## This query will update the order table and setting this to paid and confirmed:
		$query = 'UPDATE #__ticketmaster_orders SET paid = 1, published = 1
				  WHERE ordercode = "'.(int)$this->ordercode.'" ';						

		## Execute the query:
		$db->setQuery( $query );
		
		## When query goes wrong.. Show message with error.
		if (!$db->query()) {
			return false;
		}
		
		return true;
	
	}					
	
	## Create the tickets for this client, feeded by the ordercode.
	public function createTickets() {
		
		$db = JFactory::getDBO();
		
		## Now get the orders to create the tickets.
		$query = 'SELECT * FROM #__ticketmaster_orders WHERE ordercode = '.(int)$this->ordercode.'';

		## Do the query now	
		$db->setQuery($query);
		$data = $db->loadObjectList();

		## Loop through the items to create the tickets:
		for ($i = 0, $n = count($data); $i < $n; $i++ ){
			
			$row  = $data[$i];
				
			## Include the confirmation class to sent the tickets. 
			$path 		= JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'createtickets.class.php';
			$override 	= JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'override'.DS.'createtickets.class.php';
			
			## Check if the override is there.
			if (file_exists($override)) {
				## Yes, now we use it.
				require_once($override);
			} else {
				## No, use the standard
				require_once($path);
			}	
			
			if(isset($row->orderid)) {  
				
				## Create the tickets for this order:
				$creator = new ticketcreator( (int)$row->orderid );  
				$creator->doPDF();
			
			}  				
			
		}
		
		return true;	
	
	}	
	
	## sending the tickets to the customer feeded by the ordercode
	public function sendTickets() {

		## Include the confirmation class to sent the tickets. 
		$path_include = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'sendonpayment.class.php';
		include_once( $path_include );
		
		## Sending the ticket immediatly to the client.
		$creator = new sendonpayment( (int)$this->ordercode );  
		$creator->send();	
		
		return true;
	
	}	
	
	## clear the session for Ticketmaster
	public function clearSession() {

		## Removing the session, it's not needed anymore.
		$session = JFactory::getSession();
		$session->clear('ordercode');
		$session->clear('coupon');	
		
		return true;
	
	}	
	
	## send the confirmation to the client.
	public function sendConfirmation() {
	
		$path_include = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'confirmation.php';
		include_once( $path_include );
		
		$sendconfirmation = new confirmation( (int)$this->ordercode );  
		$sendconfirmation->doConfirm();
		$sendconfirmation->doSend();	
		
		return true;
	
	}			
	
}	