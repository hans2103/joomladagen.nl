<?php
## no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
## Import library dependencies
jimport('joomla.plugin.plugin');
 
class plgRDmediaFreeCheckout extends JPlugin
{
/**
 * Constructor
 *
 * For php4 compatability we must not use the __constructor as a constructor for
 * plugins because func_get_args ( void ) returns a copy of all passed arguments
 * NOT references.  This causes problems with cross-referencing necessary for the
 * observer design pattern.
 */
	function plgRDMediaFreeCheckout( &$subject, $params ) 
	{
	
		parent::__construct( $subject, $params );
		
		## Loading language:	
		$lang = JFactory::getLanguage();
		$lang->load('plg_rdmedia_paybycheck', JPATH_ADMINISTRATOR);		
		
		## load plugin params info
		$plugin			=& JPluginHelper::getPlugin('rdmedia', 'freecheckout');		
		
		$this->infobox = $this->params->def( 'infobox', '0' );
		$this->email_tpl = $this->params->def( 'email_tpl', '1' );
		$this->success_tpl = $this->params->def( 'success_tpl', '1' );	
		$this->layout = $this->params->def( 'layout', '1' );
		$this->j3buttontext = $this->params->def( 'j3buttontext', 'Complete Order' );
		
		## Including required paths to calculator.
		$path_include = JPATH_SITE.DS.'components'.DS.'com_ticketmaster'.DS.'assets'.DS.'helpers'.DS.'get.amount.php';
		include_once( $path_include );
		
		## Getting the global DB session
		$session =& JFactory::getSession();
		## Gettig the orderid if there is one.
		$this->ordercode = $session->get('ordercode');
		
		## Getting the amounts for this order.
		$amount      = _getAmount($this->ordercode);
		$fees		 = _getFees($this->ordercode); 
		
		## Prepare for Mollie.
		$this->amount = $amount*100;
		$this->fees = $fees;
		
		## Return URL after payment has been done.
		$this->report_url  = 'index.php?option=com_ticketmaster&view=transaction&payment_type=freecheckout';
	
	}

	 function display()
	 {
		$app = &JFactory::getApplication();
		
		## Loading the CSS file for ideal plugin.
		$document = &JFactory::getDocument();
		$document->addStyleSheet( JURI::root(true).'/plugins/rdmedia/freecheckout/rdmedia_freecheckout/css/freecheckout.css' );	

		## Making sure PayPal getting the right amount (23.00)
		$ordertotal = number_format($this->amount, 2, '.', '');		

		## Check the amount, if higher then 0.00 then show the plugin data.	
		if ($ordertotal == '0.00' || $ordertotal < 0) {
			
			if($this->layout != 1){		

				## Form to show to client.
				echo '<form action = "'.$this->report_url.'" method="POST" name="adminForm" id="adminForm">';	
	
				echo '<div id="paybox_links">';	
				
				echo '<div id="paybox_banks">';
				echo 	$this->infobox;
				echo '</div>';						
				
				echo 	'<div id="paybox_button">';
				echo		'<input type="submit" name="submit" class="freecheckout_button" value="" />';
				echo 	'</div>';				
		
				echo 	'</div>';
					
				echo '</form>';	
			
			}else{
				
				echo '<form action = "'.$this->report_url.'" method="POST" name="adminForm" id="adminForm">';	
				echo 	'<div class="alert alert-success">'.$this->infobox.'</div>';			
				echo    '<button class="btn btn-block btn-success" style="margin-top: 8px;" type="submit">'.$this->j3buttontext.'</button>';
				echo '</form>';
				
			}
				
		}
		
		return true;
	}
	
	function freecheckout()
	{

		## Getting the global DB session
		$session =& JFactory::getSession();
		## Gettig the orderid if there is one.
		$ordercode = $session->get('ordercode');
		
		$db = JFactory::getDBO();
		
		## Getting the desired info from the configuration table
		$sql = "SELECT * FROM #__ticketmaster_emails WHERE emailid = ".(int)$this->success_tpl."";
		$db->setQuery($sql);
		$config = $db->loadObject();
		
		## Getting the desired info from the configuration table
		$sql = "SELECT userid, email, u.name 
				FROM #__ticketmaster_orders AS o , #__users AS u
				WHERE ordercode = ".(int)$this->ordercode."
				AND o.userid = u.id";
				
		$db->setQuery($sql, 0, 1);
		$user = $db->loadObject();
		
		##### SHOWING A MESSAGE IN SCREEN ######
		
		echo '<h2 class="contentheading">'.$config->mailsubject.'</h2>';
		$message = str_replace('%%ORDERCODE%%', $ordercode, $config->mailbody);
		$message = str_replace('%%TOTAL%%', $this->amount/100, $message);
		$message = str_replace('%%NAME%%', $user->name, $message);
		echo $message;
		
		##### SENDING THE MESSAGE NOW ########		

		## Getting the desired info from the configuration table
		$sql = "SELECT * FROM #__ticketmaster_emails WHERE emailid = ".(int)$this->email_tpl."";
		$db->setQuery($sql);
		$msg = $db->loadObject();
		
		$message = str_replace('%%ORDERCODE%%', $this->ordercode, $msg->mailbody);
		$message = str_replace('%%TOTAL%%', $this->amount/100, $message);
		$message = str_replace('%%NAME%%', $user->name, $message);
		
		## Imaport mail functions:
		jimport( 'joomla.mail.mail' );
			
						## Set the sender of the email:
		$sender[0] = $msg->from_email;
		$sender[1] = $msg->from_name;
		## Compile mailer function:
		$obj = JFactory::getMailer();
		$obj->setSender( $sender );
		$obj->isHTML( true );
		$obj->setBody ( $message );
		$obj->addRecipient($user->email);
		## Send blind copy to site admin?
		if ($config->receive_bcc == 1){
			if ($config->reply_to_email != ''){
				$obj->addRecipient($msg->bcc_email);
			}
		}
		## Add reply to and subject:
		$obj->addReplyTo($msg->from_email);
		$obj->setSubject($msg->mailsubject);
		
		if ($msg->published == 1){
			
			$sent = $obj->Send();
		}		


		$query = 'UPDATE #__ticketmaster_orders'
			. ' SET paid = 1, published = 1'
			. ' WHERE ordercode = '.(int)$this->ordercode.'';
		
		## Do the query now	
		$db->setQuery( $query );
		
		## When query goes wrong.. Show message with error.
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		$query = 'SELECT * FROM #__ticketmaster_orders WHERE ordercode = '.(int)$this->ordercode.'';

		## Do the query now	
		$db->setQuery($query);
		$data = $db->loadObjectList();
		
		$k = 0;
		for ($i = 0, $n = count($data); $i < $n; $i++ ){
			
		$row  = &$data[$i];
			
		## Include the confirmation class to sent the tickets. 
		$path = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'createtickets.class.php';
		$override = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'override'.DS.'createtickets.class.php';
		
		## Check if the override is there.
		if (file_exists($override)) {
			## Yes, now we use it.
			require_once($override);
		} else {
			## No, use the standard
			require_once($path);
		}	
			
			if(isset($row->orderid)) {  
			
				$creator = new ticketcreator( (int)$row->orderid );  
				$creator->doPDF();
			
			}  				
			
			$k=1 - $k;
			
		}	

		## Include the confirmation class to sent the tickets. 
		$path_include = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ticketmaster'.DS.'classes'.DS.'sendonpayment.class.php';
		include_once( $path_include );
		
		## Sending the ticket immediatly to the client.
		$creator = new sendonpayment( (int)$this->ordercode );  
		$creator->send();
		
		## Removing the session, it's not needed anymore.
		$session = JFactory::getSession();
		$session->clear('ordercode');	
		$session->clear('coupon');



	}

}	
?>
