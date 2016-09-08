<?php
## no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
## Import library dependencies
jimport('joomla.plugin.plugin');
 
class plgRDmediaMollie extends JPlugin
{
/**
 * Constructor
 *
 * For php4 compatability we must not use the __constructor as a constructor for
 * plugins because func_get_args ( void ) returns a copy of all passed arguments
 * NOT references.  This causes problems with cross-referencing necessary for the
 * observer design pattern.
 */
 function plgRDMediaMollie( $subject , $params ) {
 
    parent::__construct( $subject , $params );
	
	// Load user_profile plugin language
	$lang = JFactory::getLanguage();
	$lang->load('plg_rdmedia_mollie', JPATH_ADMINISTRATOR);
 
	## load plugin params info
 	$plugin = JPluginHelper::getPlugin('rdmedia', 'mollie');
 	$this->api_key 			 = $this->params->def( 'api_key', 1 );
	$this->success_tpl 		 = $this->params->def( 'success_tpl', 1 );
	$this->failure_tpl 		 = $this->params->def( 'failure_tpl', 1 );
	$this->description 		 = $this->params->def( 'description', 'Ordercode:' );	
	$this->show_methods 	 = $this->params->def( 'show_methods', 0 );	
	$this->methods 			 = $this->params->def( 'methods' );
	$this->send_confirmation = $this->params->def( 'send_confirmation', 0 );	
	$this->pending_tpl 		 = $this->params->def( 'pending_tpl', 0 );	

	## Including required paths to calculator.
	$path_include = JPATH_SITE.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'helpers'.DS.'get.amount.php';
	include_once( $path_include );

	## Getting the global DB session
	$session = JFactory::getSession();
	## Gettig the orderid if there is one.
	$this->ordercode = $session->get('ordercode');
	
	## Getting the amounts for this order.
	$amount      = _getAmount($this->ordercode, 1);
	$fees		 = _getFees($this->ordercode); 
	
	## Prepare for Mollie.
	$this->amount = $amount;
	$this->fees = $fees;

	## Return URL after payment has been done.
	$this->return_url  = JURI::root().'index.php?option=com_ticketmaster&view=transaction&payment_type=mollie'; 
	$this->notify_url = JURI::root().'index.php?option=com_ticketmaster&controller=ipn&task=ipnProcessor&plg=mollie';
	
 }
 
/**
 * Plugin method with the same name as the event will be called automatically.
*/


	 function display()
	 {
		$app = JFactory::getApplication();
		$db  = JFactory::getDBO();
		
		require_once dirname(__FILE__) . "/Mollie/API/Autoloader.php";
		
		/*
		 * Initialize the Mollie API library with your API key.
		 *
		 * See: https://www.mollie.nl/beheer/account/profielen/
		 */
		$mollie = new Mollie_API_Client;
		$mollie->setApiKey($this->api_key);
		
		## IF THE MOLLIE FORM HAS BEEN SUBMITTED -- PERFORM THIS ACTION ##
		
		$jinput = JFactory::getApplication()->input;
		

		## SHOWING THE FORM TO THE CUSTOMER ##
		$path_include = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'payment.api.class.php';		
		$curr_path = dirname(__FILE__).'/Mollie/payment.api.class.php';
		
		## We want to be sure that the API is there :)
		if(!file_exists($path_include)){
			jimport('joomla.filesystem.file');
			## Copy the file there:
			JFile::copy($curr_path, $path_include);
		}
		
		## include the API file:
		include($path_include);
		
		if($jinput->get('redirect_mollie', '0', 'int') == 1){
			
			$protocol = isset($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) !== 0 ? "https" : "http";
			$hostname = $_SERVER['HTTP_HOST'];
			$path     = dirname(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']);
			
			## Force total of the order in this format: 
			$ordertotal = number_format($this->amount, 2, '.', ''); 
			$method = $jinput->get('method', '', 'word');
			$issuer = $jinput->get('issuer', '', 'RAW');

			## if chosen ideal and issuer is not empty:
			if ( $jinput->get('method', '', 'RAW') == 'ideal' && $jinput->get('issuer', '', 'RAW') != '' ){

				 $payment = $mollie->payments->create(array(
					"amount"       => $ordertotal,
					"method"	   => $method, 
					"issuer"	   => $issuer, 
					"description"  => $this->description.' '.$this->ordercode,
					"redirectUrl"  => $this->return_url.'&order='.md5($this->ordercode),
					"webhookUrl"   => $this->notify_url,
					"metadata"     => array(
					   "order_id" => $this->ordercode,
					),
				));
			
			## method has been chosen (but no issuer for ideal --> customer choose issuer at mollie.nl) 
			}else if( $jinput->get('method', '', 'RAW') != '' ){
			
				 $payment = $mollie->payments->create(array(
					"amount"       => $ordertotal,
					"method"	   => $method, 
					"description"  => $this->description.' '.$this->ordercode,
					"redirectUrl"  => $this->return_url.'&order='.md5($this->ordercode),
					"webhookUrl"   => $this->notify_url,
					"metadata"     => array(
					   "order_id" => $this->ordercode,
					),
				));
			
			##no method has been chosen at all:
			}else{
				
				 $payment = $mollie->payments->create(array(
					"amount"       => $ordertotal,
					"description"  => $this->description.' '.$this->ordercode,
					"redirectUrl"  => $this->return_url.'&order='.md5($this->ordercode),
					"webhookUrl"   => $this->notify_url,
					"metadata"     => array(
					   "order_id" => $this->ordercode,
					),
				));

			}	
			
			## Stop if the class is not present:
			if (!class_exists('paymentAPI')) {
			    exit("Class is not available to process transactions.");
			}			
			
			## Start the API to process everything.
			$newPayment = new paymentAPI( (int)$this->ordercode ); 		
			$transactions = $newPayment->checkTempTransactionAmount(md5($this->ordercode));
			
			## If there are no transactions, insert now.
			if($transactions == 0) {	
				
				## Get the user object:
				$user =  JFactory::getUser();
				
				## Let the API insert a new payment to the temp transaction table:
				$temp_transaction = $newPayment->insertTempTransaction($user->id, md5($this->ordercode));									
				
				## If there is no new created temporary transaction, quit here.
				if(!$temp_transaction){
					exit( JText::_( 'PLG_TICKETMSTER_MOLLIE_ERROR_1000' ) );
				}			   
							   
			}	
			
			## Redirect to the bank in question:
			header("Location: " . $payment->getPaymentUrl());
	
		}
		


		
		$issuers = $mollie->issuers->all();	
		$ideal_check = array_search('ideal', $this->methods);
		
		echo '<img src="plugins/rdmedia/rdmtargetpay/rdmedia_targetpay/images/plg-targetpay-vertical-ideal.png" />';
        
        echo '<form action = "index.php" method="POST" name="adminForm" id="adminForm">';

		if($this->show_methods == 1 ){
		
			echo '<fieldset style="margin-top:15px;">';
	        
			echo	'<select name="method" class="input" style="width:100%;">';
			
			echo 		'<option value="">'.JText::_( 'PLG_TICKETMSTER_MOLLIE_IDEAL_CHOOSE_METHOD' ).'</option>';
						
					foreach ($this->methods as $method){
						echo '<option value=" '.htmlspecialchars($method).' ">'.htmlspecialchars(ucfirst($method)).'</option>';
					}						
			
			echo	'</select>';
		
		}
		
		if($this->show_methods == 1 && in_array('ideal', $this->methods) ){

			
			echo '<p style="text-align:center; font-weight:bold;">'.JText::_( 'PLG_TICKETMSTER_MOLLIE_IDEAL_CHOOSE_BANK' ).'</p>';
	        
			echo	'<select name="issuer" class="input" style="width:100%;">';
			
			echo 		'<option value="">'.JText::_( 'PLG_TICKETMSTER_MOLLIE_IDEAL_CHOOSE_ISSUER' ).'</option>';
						
					foreach ($issuers as $issuer){
						if ($issuer->method == Mollie_API_Object_Method::IDEAL){
							echo '<option value=" '.htmlspecialchars($issuer->id).' ">'.htmlspecialchars($issuer->name).'</option>';
						}
					}						
			
			echo	'</select>';
		
		}
		
        
		echo    '<input type="hidden" name="option" value="com_ticketmaster" />';
		echo    '<input type="hidden" name="view" value="payment" />';
		echo    '<input type="hidden" name="redirect_mollie" value="1" />';	                 
	    echo    '<button class="btn btn-block btn-success" type="submit" style="margin-top:10px;">'.JText::_( 'PLG_TICKETMSTER_MOLLIE_MAKE_PAYMENT' ).'</button>'; 

		echo '</form>';
		
		return true;
	 }
	 

	 function mollie(){
		 
		$jinput = JFactory::getApplication()->input;
		$tmp_transaction_id = $jinput->getString('order', '');
		
		if($tmp_transaction_id == ''){
			exit('No tmp transaction has been sent back -- looks more like an attack');
		}
		
		## Connect DB:
		$db = JFactory::getDBO();
		
		## Doing the query to get the count of transactions for this one.
		$sql = 'SELECT * FROM #__ticketmaster_transactions_temp WHERE transaction_number = "'.$tmp_transaction_id.'" ';
		
		$db->setQuery($sql);
		$temp_transaction = $db->loadObject();		
		
		## Include the confirmation class to sent the tickets. 
		$path_include = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'payment.api.class.php';		
		include_once( $path_include );
		
		## Stop if the class is not present:
		if (!class_exists('paymentAPI')) {
			exit("Class is not available to process transactions.");
		}			
		
		## Start the API to process everything.
		$newPayment = new paymentAPI( (int)$temp_transaction->ordercode ); 			 
		## Clearing the session:
		$newPayment->clearSession(); 
		
		$db = JFactory::getDBO();
		## Getting the client information, must be available in the email:
		$sql = 'SELECT * FROM #__ticketmaster_clients WHERE userid = "'.$temp_transaction->userid.'" ';
		
		$db->setQuery($sql);
		$user = $db->loadObject();	
		
		## Unknow state:
		if($temp_transaction->processed == 5){
			$message_id	= $this->failure_tpl;
			$error_msg	= JText::_( 'PLG_TICKETMSTER_MOLLIE_PAYMENT_STATE_UNKNOWN' );
		## Pending Payment	
		}else if($temp_transaction->processed == 4){
			$message_id	= $this->pending_tpl;
			$error_msg	= JText::_( 'PLG_TICKETMSTER_MOLLIE_PAYMENT_PENDING' );		
		## Open Payment	
		}else if($temp_transaction->processed == 3){
			$message_id	= $this->pending_tpl;
			$error_msg	= JText::_( 'PLG_TICKETMSTER_MOLLIE_PAYMENT_OPEN' );			
		## Paid amount is not correctly	
		}else if($temp_transaction->processed == 2){
			$message_id	= $this->failure_tpl;
			$error_msg	= JText::_( 'PLG_TICKETMSTER_MOLLIE_PAYMENT_WRONG_AMOUNT' );			
		## Payment has been completed:	
		}else if($temp_transaction->processed == 1){
			$message_id	= $this->success_tpl;
		## Unknown	
		}else{
			$message_id	= $this->failure_tpl;
			$error_msg	= JText::_( 'PLG_TICKETMSTER_MOLLIE_PAYMENT_STATE_UNKNOWN' );			
		}

		## Getting the desired info from the configuration table
		$sql = "SELECT * FROM #__ticketmaster_emails WHERE emailid = ".(int)$message_id."";
		
		$db->setQuery($sql);
		$config = $db->loadObject();		
					
		$message = str_replace('%%FIRSTNAME%%', utf8_decode($user->firstname), $config->mailbody);
		$message = str_replace('%%LASTNAME%%', utf8_decode($user->name), $message);
		$message = str_replace('%%ADDRESS1%%', utf8_decode($user->address), $message);
		$message = str_replace('%%ADDRESS2%%', utf8_decode($user->address2), $message);
		$message = str_replace('%%ZIPCODE%%', utf8_decode($user->zipcode), $message);
		$message = str_replace('%%CITY%%', utf8_decode($user->city), $message);				
		
		$message = str_replace('%%TRANSACTIONID%%', $temp_transaction->errormessage, $message);
		$message = str_replace('%%ORDERCODE%%', $temp_transaction->ordercode, $message);	
		$message = str_replace('%%ERROR%%', $error_msg, $message);	
		
		## Imaport mail functions:
		jimport( 'joomla.mail.mail' );
							
		## Set the sender of the email:
		$sender[0] = $config->from_email;
		$sender[1] = $config->from_name;					
		## Compile mailer function:			
		$obj = JFactory::getMailer();
		$obj->setSender( $sender );
		$obj->isHTML( true );
		$obj->setBody ( $message );				
		$obj->addRecipient($user->email);
		## Send blind copy to site admin?
		if ($config->receive_bcc == 1){
			if ($config->reply_to_email != ''){
				$obj->addBCC($obj->reply_to_email);
			}	
		}					
		## Add reply to and subject:					
		$obj->addReplyTo($config->reply_to_email);
		$obj->setSubject($config->mailsubject);
		
		if ($mail->published == 1){						
			
			$sent = $obj->Send();						
		}	
		
		echo $message;	
		 
	 }
	 
	 
	 function IPNProcessPayment($response){
				
		## Include the confirmation class to sent the tickets. 
		$path_include = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'payment.api.class.php';		
		include_once( $path_include );
		
		## Include the mollie API
		require_once dirname(__FILE__) . "/Mollie/API/Autoloader.php";

		$mollie = new Mollie_API_Client;
		$mollie->setApiKey($this->api_key);
		
		$payment  = $mollie->payments->get($response['id']);
		$order_id = $payment->metadata->order_id;	
		
		## add all information to a string:
		$payment_details = http_build_query($payment);	
		
		## Including required paths to calculator.
		$path_include = JPATH_SITE.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'helpers'.DS.'get.amount.php';
		include_once( $path_include );										
		
		## Stop if the class is not present:
		if (!class_exists('paymentAPI')) {
			exit("Class is not available to process transactions.");
		}			
		
		## Start the API to process everything.
		$newPayment = new paymentAPI( (int)$order_id ); 		
		$tmpTransaction = $newPayment->getTempTransactionResult( md5($order_id) );

		if(!$tmpTransaction){
			exit('No temporary transaction in the database.');
		}
		
		## PAYMENT SUCCESFULL 
		if ($payment->isPaid() == true) {

			## Getting the amounts for this order.
			$amount      = _getAmount( (int)$order_id  , 1);	
			
			$netto_price = number_format($amount, 2, '.', '');
			$paid_price = $payment->amount;			
			
			if($netto_price == $paid_price){
				
				## Update the order state in the order table:
				$payment_state = $newPayment->updateOrder();	
				
				## Insert transaction details:
				$store_payment = $newPayment->saveTransaction($order_id, $tmpTransaction->userid, $payment_details, $paid_price, ucfirst($payment->method));

				## if state is true, create the tickets:
				if($payment_state == true){
					$ticket_creator = $newPayment->createTickets();
				}
				
				## set temporary payment to 1 (paid order)
				$newPayment->updateTempTransaction( md5($order_id) , '1', $response['id']) ;
				
				## if send confirmation is on:
				if($this->send_confirmation == 1){
					$newPayment->sendConfirmation();
				}
				
				## if tickets has been created:
				if($ticket_creator == true){
					$newPayment->sendTickets();
				}
				
				## Clearing the session:
				$newPayment->clearSession();
				exit();	
				
			}else{
				
				## set temporary payment to 1 (paid order)
				$newPayment->updateTempTransaction( md5($order_id) , '2');
				exit();	
			
			}
		
		## Payment is still open
		}elseif ($payment->isOpen() == true) {

			## set temporary payment to 1 (payment is still open)
			$newPayment->updateTempTransaction( md5($order_id) , '3') ;
			exit();				
		
		## Payment is pending: (mostly Bitcoins)
		}elseif ($payment->isPending() == true) {

			## set temporary payment to 1 (payment is pending)
			$newPayment->updateTempTransaction( md5($order_id) , '4') ;
			exit();				
		
		## Something is not OK :)
		}else{
			
			## set temporary payment to 5 (cancelled payment)
			$newPayment->updateTempTransaction( md5($order_id) , '5') ;	
			exit();					
		
		}
		
		## set temporary payment to 0 (payment is returning without state)
		$newPayment->updateTempTransaction( md5($order_id) , '0') ;
		exit();		
	}
}
?>
