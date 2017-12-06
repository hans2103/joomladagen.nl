<?php
/*
 * @package		Paypal Standard Plugin for J2Store
* @subpackage	J2Store
* @author    	Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
* @copyright	Copyright (c) 2014 Weblogicx India Ltd. All rights reserved.
* @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
* --------------------------------------------------------------------------------
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/payment.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/prices.php');
require_once (JPATH_SITE.'/components/com_j2store/helpers/utilities.php');
class plgJ2StorePayment_paypal extends J2StorePaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element    = 'payment_paypal';
	var $_isLog      = false;
	var $_j2version = null;
	var $username = null;
	var $password = null;
	var $signature = null;

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
		$this->_j2version = $this->getVersion();
		if($this->params->get('debug', 0)) {
			$this->_isLog = true;
		}

		$mode = $this->params->get ( 'sandbox', 0 );
		if($mode){
			$this->username = $this->params->get('sandbox_api_username','');
			$this->password = $this->params->get('sandbox_api_password','');
			$this->signature = $this->params->get('sandbox_api_signature','');
		} else {
			$this->username = $this->params->get('api_username','');
			$this->password = $this->params->get('api_password','');
			$this->signature = $this->params->get('api_signature','');
		}
	}

	/**
	 * @param $order     object    Order table object
	 */

	function onJ2StoreGetPaymentOptions($element, $order)
	{
		// Check if this is the right plugin
		if (!$this->_isMe($element))
		{
			return null;
		}

		$status = true;
		// if this payment method should be available for this order, return true
		// if not, return false.
		// by default, all enabled payment methods are valid, so return true here,
		// but plugins may override this
		if( version_compare( $this->_j2version, '2.6.7', 'ge' ) ) {
			//$order = JTable::getInstance('Orders', 'Table');
			$order->setAddress();
			$address = $order->getBillingAddress();
			$geozone_id = $this->params->get('geozone_id', '0');
			//get the geozones
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('gz.*,gzr.*')->from('#__j2store_geozones AS gz')
			->innerJoin('#__j2store_geozonerules AS gzr ON gzr.geozone_id = gz.geozone_id')
			->where('gz.geozone_id='.$geozone_id )
			->where('gzr.country_id='.$db->q($address['country_id']).' AND (gzr.zone_id=0 OR gzr.zone_id='.$db->q($address['zone_id']).')');
			$db->setQuery($query);
			$grows = $db->loadObjectList();

			if (!$geozone_id ) {
				$status = true;
			} elseif ($grows) {
				$status = true;
			} else {
				$status = false;
			}
		}

		return $status;
	}

	function _beforePayment($order) {
		//get surcharge if any
		$surcharge = 0;

		$surcharge_percent = $this->params->get('surcharge_percent', 0);
		$surcharge_fixed = $this->params->get('surcharge_fixed', 0);
		if((float) $surcharge_percent > 0 || (float) $surcharge_fixed > 0) {

			//percentage
			if((float) $surcharge_percent > 0) {
				$surcharge += ($order->order_total * (float) $surcharge_percent) / 100;
			}

			if((float) $surcharge_fixed > 0) {
				$surcharge += (float) $surcharge_fixed;
			}
			//make sure it is formated to 2 decimals

			$order->order_surcharge = round($surcharge, 2);
			$order->calculateTotals();
		}

	}

	/**
	 * @param $data     array       form post data
	 * @return string   HTML to display
	 */
	function _prePayment( $data )
	{
		// get component params
		$params = JComponentHelper::getParams('com_j2store');

		// prepare the payment form

		$vars = new JObject();
		$vars->order_id = $data['order_id'];
		$vars->orderpayment_id = $data['orderpayment_id'];
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		$order = JTable::getInstance('Orders', 'Table');
		$order->load($data['orderpayment_id']);
		$currency_values= $this->getCurrency($order);

		$vars->currency_code =$currency_values['currency_code'];
		$vars->orderpayment_amount = $this->getAmount($order->orderpayment_amount, $currency_values['currency_code'], $currency_values['currency_value'], $currency_values['convert']);

		$vars->orderpayment_type = $this->_element;

		$vars->cart_session_id = JFactory::getSession()->getId();

		$vars->display_name = $this->params->get('display_name', 'PAYMENT_PAYPAL');
		$vars->onbeforepayment_text = $this->params->get('onbeforepayment', '');
		$vars->button_text = $this->params->get('button_text', 'J2STORE_PLACE_ORDER');


		$items = $order->getItems();

		$products = array();
		foreach ($items as $item)
		{
			$desc = $item->orderitem_name;

			//sku
			if (!empty($item->orderitem_sku))
			{
				$desc .= ' | '.JText::_('J2STORE_SKU').': '.$item->orderitem_sku;
			}

			//productoptions
			if (!empty($item->orderitem_attribute_names)) {
				//first convert from JSON to array
				if( version_compare( $this->_j2version, '2.5.0', 'lt' ) ) {
					$desc .=' | '.$item->orderitem_attribute_names;
				} else {
					$registry = new JRegistry;
					$registry->loadString(stripslashes($item->orderitem_attribute_names), 'JSON');
					$product_options = $registry->toObject();
					if(count($product_options) >0 ) {
						foreach ($product_options as $option) {
							$desc .=' | '.$option->name.':'.$option->value;
						}
					}
				}
			}
			$desc = str_replace("'", '', $desc);
			$products[]= array(
					'name'		=>html_entity_decode($desc,ENT_QUOTES, 'UTF-8'),
					'number' 	=>$item->product_id,
					'quantity'	=>$item->orderitem_quantity,
					'price'		=> $this->getAmount($item->orderitem_final_price / $item->orderitem_quantity, $currency_values['currency_code'], $currency_values['currency_value'], $currency_values['convert'])
			);

			$item->_description = $desc;
		}

		$handling_cost = $order->order_shipping + $order->order_shipping_tax + $order->order_surcharge;
		$handling_cart = $this->getAmount($handling_cost, $currency_values['currency_code'], $currency_values['currency_value'], $currency_values['convert']);
		if($handling_cart > 0) {
			$products[]= array(
					'name'		=> JText::_('J2STORE_CART_SHIPPING_AND_HANDLING'),
					'quantity'	=> 1,
					'price'		=> $handling_cart
			);
		}	

		$vars->products = $products;
		$vars->tax_cart = $this->getAmount($order->order_tax, $currency_values['currency_code'], $currency_values['currency_value'], $currency_values['convert']);

		$vars->discount_amount_cart = $this->getAmount($order->order_discount, $currency_values['currency_code'], $currency_values['currency_value'], $currency_values['convert']);

		$vars->order = $order;
		$vars->orderitems = $items;

		// set payment plugin variables
		if($this->params->get('sandbox', 0)) {
			$vars->merchant_email = trim($this->_getParam( 'sandbox_merchant_email' ));
		}else {
			$vars->merchant_email = trim($this->_getParam( 'merchant_email' ));
		}


		$rootURL = rtrim(JURI::base(),'/');
		$subpathURL = JURI::base(true);
		if(!empty($subpathURL) && ($subpathURL != '/')) {
			$rootURL = substr($rootURL, 0, -1 * strlen($subpathURL));
		}


		$vars->post_url = $this->_getPostUrl();
		$vars->return_url = $rootURL.JRoute::_("index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=display_message");
		$vars->cancel_url = $rootURL.JRoute::_("index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=cancel");
//		$vars->notify_url = $rootURL.JRoute::_("index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=process&tmpl=component");
		//$vars->notify_url = rtrim(JURI::root(),'/')."/plugins/j2store/".$this->_element."/".$this->_element."/tmpl/notify.php";
		//$vars->currency_code = $this->_getParam( 'currency', 'USD' );
		$notify_type = $this->params->get('notify_url_type','default');
		if($notify_type == 'alternative'){
			$vars->notify_url = rtrim(JURI::root(),'/')."/plugins/j2store/".$this->_element."/".$this->_element."/tmpl/notify.php";
		}elseif($notify_type == 'default'){
			$vars->notify_url = JURI::root()."index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=process&tmpl=component";
		}else{
			$vars->notify_url = JURI::root()."index.php?option=com_j2store&view=callback&method=".$this->_element."&paction=process";
		}

		// set variables for user info
		$vars->first_name   = $data['orderinfo']['billing_first_name'];
		$vars->last_name    = $data['orderinfo']['billing_last_name'];
		$vars->email        = $data['orderinfo']['user_email'];
		$vars->address_1    = $data['orderinfo']['billing_address_1'];
		$vars->address_2    = $data['orderinfo']['billing_address_2'];
		$vars->city         = $data['orderinfo']['billing_city'];
		$vars->country      = $this->getCountryById($data['orderinfo']['billing_country_id'])->country_isocode_2;//$data['orderinfo']['billing_country_name'];
		$vars->region       = $data['orderinfo']['billing_zone_name'];
		$vars->postal_code  = $data['orderinfo']['billing_zip'];

		if(isset($order->invoice_number) && $order->invoice_number > 0) {
			$invoice_number = $order->invoice_prefix.$order->invoice_number;
		}else {
			$invoice_number = $order->id;
		}
		$vars->invoice = $invoice_number;

		$html = $this->_getLayout('prepayment', $vars);
		return $html;
	}

	/**
	 * Processes the payment form
	 * and returns HTML to be displayed to the user
	 * generally with a success/failed message
	 *
	 * @param $data     array       form post data
	 * @return string   HTML to display
	 */
	function _postPayment( $data )
	{
		// Process the payment
		$paction = JRequest::getVar('paction');

		$vars = new JObject();

		switch ($paction)
		{
			case "display_message":
				$session = JFactory::getSession();
				$session->set('j2store_cart', array());
				$vars->message = JText::_($this->params->get('onafterpayment', ''));
				$html = $this->_getLayout('message', $vars);
				$html .= $this->_displayArticle();

				break;
			case "process":
				$vars->message = $this->_process();
				$html = $this->_getLayout('message', $vars);
				echo $html; // TODO Remove this
				$app = JFactory::getApplication();
				$app->close();
				break;
			case "cancel":
				$vars->message = JText::_($this->params->get('oncancelpayment', ''));
				$html = $this->_getLayout('message', $vars);
				break;
			default:
				$vars->message = JText::_($this->params->get('onerrorpayment', ''));
				$html = $this->_getLayout('message', $vars);
				break;
		}

		return $html;
	}

	/**
	 * Prepares variables for the payment form
	 *
	 * @return unknown_type
	 */
	function _renderForm( $data )
	{
		$user = JFactory::getUser();
		$vars = new JObject();
		$vars->onselection_text = $this->params->get('onselection', '');
		$html = $this->_getLayout('form', $vars);

		return $html;
	}

	/************************************
	 * Note to 3pd:
	*
	* The methods between here
	* and the next comment block are
	* specific to this payment plugin
	*
	************************************/

	/**
	 * Gets the Paypal gateway URL
	 *
	 * @param boolean $full
	 * @return string
	 * @access protected
	 */
	function _getPostUrl($full = true)
	{
		$url = $this->params->get('sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';

		if ($full)
		{
			$url = 'https://' . $url . '/cgi-bin/webscr';
		}

		return $url;
	}


	/**
	 * Gets the value for the Paypal variable
	 *
	 * @param string $name
	 * @return string
	 * @access protected
	 */
	function _getParam( $name, $default='' )
	{
		$return = $this->params->get($name, $default);

		$sandbox_param = "sandbox_$name";
		$sb_value = $this->params->get($sandbox_param);
		if ($this->params->get('sandbox') && !empty($sb_value))
		{
			$return = $this->params->get($sandbox_param, $default);
		}

		return $return;
	}

	function getIPNurl(){
		$url = $this->params->get('sandbox',0) ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr' : 'https://ipnpb.paypal.com/cgi-bin/webscr';
		return $url;
	}

	/**
	 * Validates the IPN data
	 *
	 * @param array $data
	 * @return string Empty string if data is valid and an error message otherwise
	 * @access protected
	 */
	function _validateIPN( $data )
	{
		$secure_post = $this->params->get( 'secure_post', '0' );
		$paypal_url = $this->getIPNurl();

		$request = 'cmd=_notify-validate';

		foreach ($data as $key => $value) {
			$request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		}

		$curl = curl_init($paypal_url);

		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$response = curl_exec($curl);

		if (!$response) {
			$this->_log('CURL failed ' . curl_error($curl) . '(' . curl_errno($curl) . ')');
		}

		$this->_log('IPN Validation REQUEST: ' . $request);
		$this->_log('IPN Validation RESPONSE: ' . $response);

		if ((strcmp($response, 'VERIFIED') == 0 || strcmp($response, 'UNVERIFIED') == 0)) {
			return '';
		}elseif (strcmp ($response, 'INVALID') == 0) {
			return JText::_('J2STORE_PAYPAL_ERROR_IPN_VALIDATION');
		}
		return '';
	}

	/**
	 *
	 * @return HTML
	 */
	function _process()
	{
		$app = JFactory::getApplication();
		$data = $app->input->getArray($_REQUEST);
		$transaction_details = $this->_getFormattedTransactionDetails($data);
		$this->_log($transaction_details, 'IPN Response from Paypal');

		$error = '';

		$validate_ipn = $this->params->get('validate_ipn', 1);
		if($validate_ipn) {
			// prepare some data
			
	
			$custom = $data['custom'];
			$custom_array = explode('|', $custom);
	
			$order_id  = $custom_array[0];
	
			// load the orderpayment record and set some values
			JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/tables' );
			$orderpayment = JTable::getInstance('Orders', 'Table');
	
			$orderpayment->load(array('order_id'=>$order_id));
			if(!empty($order_id) && ($orderpayment->order_id == $order_id) ) {
				// validate the IPN info
				$errorV = $this->_validateIPN($data);
				if (!empty($errorV))
				{
					//ipn api validation
					if(!$this->checkStatusOfPaypal($data)){
						// ipn Validation failed
						$data['ipn_validation_results'] = $errorV;
					}
				}
	
			}
		}	

		$data['transaction_details'] = $this->_getFormattedTransactionDetails( $data );

		// process the payment based on its type
		if ( !empty($data['txn_type']) )
		{
			$payment_error = '';

			if ($data['txn_type'] == 'cart') {
				// Payment received for multiple items; source is Express Checkout or the PayPal Shopping Cart.
				$payment_error = $this->_processSale( $data, $error );
			}
			else {
				// other methods not supported right now
				$payment_error = JText::_( "J2STORE_PAYPAL_ERROR_INVALID_TRANSACTION_TYPE" ).": ".$data['txn_type'];
			}

			if ($payment_error) {
				// it seems like an error has occurred during the payment process
				$error .= $error ? "\n" . $payment_error : $payment_error;
			}
		}

		if ($error) {
			// send an emails to site's administrators with error messages
			$this->_sendErrorEmails($error, $data['transaction_details']);
			return $error;
		}


		// if here, all went well
		$error = 'processed';
		return $error;
	}

	function _getTransactionUrl()
	{
		$url = $this->params->get('sandbox',0) ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
		return $url;
	}

	public function checkStatusOfPaypal($data){
		if(empty( $data ['txn_id'] ) || empty( $this->username ) || empty( $this->password ) || empty( $this->signature )){
			return false;
		}

		$transactionId = $data ['txn_id'];
		$payment_url = $this->_getTransactionUrl();
		$targetURL = new JUri($payment_url);
		$targetURL->setVar('USER', $this->username);
		$targetURL->setVar('PWD', $this->password);
		$targetURL->setVar('SIGNATURE', $this->signature);
		$targetURL->setVar('METHOD', 'GetTransactionDetails');
		$targetURL->setVar('VERSION', '106.0');
		$targetURL->setVar('TRANSACTIONID', $transactionId);

		// Set up the request through cURL
		$ch = curl_init($targetURL->toString());
		$userAgent = "J2Store/".$this->_j2version;
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		//  curl_setopt($ch, CURLOPT_CAINFO, JPATH_LIBRARIES . '/fof30/Download/Adapter/cacert.pem');
		//  curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		// Force the use of TLS (therefore SSLv3 is not used, mitigating POODLE; see https://github.com/paypal/merchant-sdk-php)
		//    curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
		// This forces the use of TLS 1.x
		//  curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);

		$rawResponse = curl_exec($ch);
		curl_close($ch);

		// We get a long query string as a response. We need to decode it to a hash array
		$fakeURI    = new JUri('http://localhost/index.php?' . $rawResponse);
		$transaction = $fakeURI->getVar('TRANSACTIONID', '');
		$payment_status = $fakeURI->getVar('PAYMENTSTATUS', '');
		$status = false;

		if($transaction == $transactionId && in_array(strtoupper($payment_status),array('PENDING','COMPLETED'))){
			$status = true;
		}
		return $status;
	}

	/**
	 * Processes the sale payment
	 *
	 * @param array $data IPN data
	 * @return boolean Did the IPN Validate?
	 * @access protected
	 */
	function _processSale( $data, $ipnValidationFailed='' )
	{
		/*
		 * validate the payment data
		*/
		$errors = array();

		if (!empty($ipnValidationFailed))
		{
			$errors[] = $ipnValidationFailed;
		}

		if($this->params->get('sandbox', 0)) {
			$merchant_email = trim($this->_getParam( 'sandbox_merchant_email' ));
		}else {
			$merchant_email = trim($this->_getParam( 'merchant_email' ));
		}
		// is the recipient correct?
		if (empty($data['receiver_email']) || JString::strtolower($data['receiver_email']) != JString::strtolower(trim($merchant_email))) {
			$errors[] = JText::_('J2STORE_PAYPAL_MESSAGE_RECEIVER_INVALID');
		}

		$custom = $data['custom'];
		$custom_array = explode('|', $custom);

		$order_id  = $custom_array[0];
		//array 1 has the cart session key
		if(isset($custom_array[1])) {
			$cart_session_id = $custom_array[1];
		} else {
			$cart_session_id = '';
		}


		// load the orderpayment record and set some values
		JTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/tables' );
		$orderpayment = JTable::getInstance('Orders', 'Table');

		$orderpayment->load( array('order_id'=>$order_id));
		if(!empty($order_id) && ($orderpayment->order_id == $order_id ) ) {
			$orderpayment->transaction_details  = $data['transaction_details'];
			$orderpayment->transaction_id       = $data['txn_id'];
			$orderpayment->transaction_status   = $data['payment_status'];

			// check the stored amount against the payment amount

				// check the payment status
				if (empty($data['payment_status']) || ($data['payment_status'] != 'Completed' && $data['payment_status'] != 'Pending')) {
					$errors[] = JText::sprintf('J2STORE_PAYPAL_MESSAGE_STATUS_INVALID', @$data['payment_status']);
				}

				//set a default status to it
				//$orderpayment->order_state = JText::_('J2STORE_PENDING'); // PENDING
				//$orderpayment->order_state_id = 4; // PENDING

				// set the order's new status
				if (count($errors))
				{
					// if an error occurred
					$orderpayment->order_state = JText::_('J2STORE_FAILED'); // FAILED
					$orderpayment->order_state_id = 3; //FAILED
				}
				elseif (@$data['payment_status'] == 'Pending')
				{
					// if the transaction has the "pending" status,
					$orderpayment->order_state = JText::_('J2STORE_PENDING'); // PENDING
					$orderpayment->order_state_id = 4; //PENDING

				}
				else
				{
					$orderpayment->order_state = trim(JText::_('J2STORE_CONFIRMED')); // CONFIRMED
					$orderpayment->order_state_id = 1; //CONFIRMED
					$orderpayment->paypal_status = @$data['payment_status']; // Paypal's original status
					JLoader::register( 'J2StoreHelperCart', JPATH_SITE.'/components/com_j2store/helpers/cart.php');
					// remove items from cart
					// J2StoreHelperCart::removeOrderItems( $orderpayment->id );
					if(!empty($cart_session_id)) {
						//load session with id
						$options = array('id'=>$cart_session_id);
						$session = JFactory::getSession($options);
						$session->set('j2store_cart', array());

					}

				}

				// save the orderpayment
				if($orderpayment->store())
				{
					if($orderpayment->order_state_id == 1 || $orderpayment->order_state_id == 4 ) {
						// let us inform the user that the payment is successful
						require_once (JPATH_SITE.'/components/com_j2store/helpers/orders.php');
						J2StoreOrdersHelper::sendUserEmail($orderpayment->user_id, $orderpayment->order_id, $data['payment_status'], $orderpayment->order_state, $orderpayment->order_state_id);
					}

				} else {
					$errors[] = $orderpayment->getError();
				}
					
			}

			return count($errors) ? implode("\n", $errors) : '';
		}

		/**
		 * Formatts the payment data for storing
		 *
		 * @param array $data
		 * @return string
		 */
		function _getFormattedTransactionDetails( $data )
		{
			$separator = "\n";
			$formatted = array();

			foreach ($data as $key => $value)
			{
				if ($key != 'view' && $key != 'layout')
				{
					$formatted[] = $key . ' = ' . $value;
				}
			}

			return count($formatted) ? implode("\n", $formatted) : '';
		}

		/**
		 * Simple logger
		 *
		 * @param string $text
		 * @param string $type
		 * @return void
		 */
		function _log($text, $type = 'message')
		{
			if ($this->_isLog) {
				$file = JPATH_ROOT . "/cache/{$this->_element}.log";
				$date = JFactory::getDate();

				$f = fopen($file, 'a');
				fwrite($f, "\n\n" . $date->format('Y-m-d H:i:s'));
				fwrite($f, "\n" . $type . ': ' . $text);
				fclose($f);
			}
		}

		/**
		 * Sends error messages to site administrators
		 *
		 * @param string $message
		 * @param string $paymentData
		 * @return boolean
		 * @access protected
		 */
		function _sendErrorEmails($message, $paymentData)
		{
			$mainframe = JFactory::getApplication();

			// grab config settings for sender name and email
			$config     = JComponentHelper::getParams('com_j2store');
			$mailfrom   = $config->get( 'emails_defaultemail', $mainframe->getCfg('mailfrom') );
			$fromname   = $config->get( 'emails_defaultname', $mainframe->getCfg('fromname') );
			$sitename   = $config->get( 'sitename', $mainframe->getCfg('sitename') );
			$siteurl    = $config->get( 'siteurl', JURI::root() );

			$recipients = $this->_getAdmins();
			$mailer = JFactory::getMailer();

			$subject = JText::sprintf('J2STORE_PAYPAL_EMAIL_PAYMENT_NOT_VALIDATED_SUBJECT', $sitename);

			foreach ($recipients as $recipient)
			{
				$mailer = JFactory::getMailer();
				$mailer->addRecipient( $recipient->email );

				$mailer->setSubject( $subject );
				$mailer->setBody( JText::sprintf('J2STORE_PAYPAL_EMAIL_PAYMENT_FAILED_BODY', $recipient->name, $sitename, $siteurl, $message, $paymentData) );
				$mailer->setSender(array( $mailfrom, $fromname ));
				$sent = $mailer->send();
			}

			return true;
		}

		/**
		 * Gets admins data
		 *
		 * @return array|boolean
		 * @access protected
		 */
		function _getAdmins()
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('u.name,u.email');
			$query->from('#__users AS u');
			$query->join('LEFT', '#__user_usergroup_map AS ug ON u.id=ug.user_id');
			$query->where('u.sendEmail = 1');
			$query->where('ug.group_id = 8');

			$db->setQuery($query);
			$admins = $db->loadObjectList();
			if ($error = $db->getErrorMsg()) {
				JError::raiseError(500, $error);
				return false;
			}

			return $admins;
		}

		function getCurrency($order) {
			$results = array();
			$convert = false;
			$params = JComponentHelper::getParams('com_j2store');

			if( version_compare( $this->_j2version, '2.6.7', 'lt' ) ) {
				$currency_code = $params->get('currency_code', 'USD');
				$currency_value = 1;
			} else {

				include_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/base.php');
				$currencyObject = J2StoreFactory::getCurrencyObject();

				$currency_code = $order->currency_code;
				$currency_value = $order->currency_value;

				//accepted currencies
				$currencies = $this->getAcceptedCurrencies();
				if(!in_array($order->currency_code, $currencies)) {
					$default_currency = 'USD';
					if($currencyObject->has($default_currency)) {
						$currencyObject->set($default_currency);
						$currency_code = $default_currency;
						$currency_value = $currencyObject->getValue($default_currency);
						$convert = true;
					}
				}
			}
			$results['currency_code'] = $currency_code;
			$results['currency_value'] = $currency_value;
			$results['convert'] = $convert;

			return $results;
		}

		function getAmount($value, $currency_code, $currency_value, $convert=false) {

			if( version_compare( $this->_j2version, '2.6.7', 'lt' ) ) {
				return J2StoreUtilities::number( $value, array( 'thousands'=>'', 'num_decimals'=>'2', 'decimal'=>'.') );
			} else {
				include_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/base.php');
				$currencyObject = J2StoreFactory::getCurrencyObject();
				$amount = $currencyObject->format($value, $currency_code, $currency_value, false);
				return $amount;
			}

		}

		function getVersion() {

			if(is_null($this->_j2version)) {
				$xmlfile = JPATH_ADMINISTRATOR.'/components/com_j2store/manifest.xml';
				$xml = JFactory::getXML($xmlfile);
				$this->_j2version=(string)$xml->version;
			}
			return $this->_j2version;
		}

		function getAcceptedCurrencies() {
			$currencies = array(
					'AUD',
					'BRL',
					'CAD',
					'CZK',
					'DKK',
					'EUR',
					'HKD',
					'HUF',
					'ILS',
					'JPY',
					'MYR',
					'MXN',
					'NOK',
					'NZD',
					'PHP',
					'PLN',
					'GBP',
					'RUB',
					'SGD',
					'SEK',
					'CHF',
					'TWD',
					'THB',
					'TRY',
					'USD'
			);
			return $currencies;
		}

		function getOrderPaymentID($order_id) {

			$db = JFactory::getDbo();
			$query = $db->getQuery(true)->select('id')->from('#__j2store_orders')->where('order_id='.$db->q($order_id));
			$db->setQuery($query);
			return $db->loadResult();

		}
		public function clean_title($text){
			$text =  str_replace ( '"','' ,  $text);
			$text =  str_replace ( "'",'' ,  $text);
			return $text;
		}

		function getCountryById($country_id) {

			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__j2store_countries')->where('country_id='.$db->q($country_id));
			$db->setQuery($query);
			return $db->loadObject();
		}

	}

	/* TYPICAL RESPONSE FROM PAYPAL INCLUDES:
	 * mc_gross=49.99
	* &protection_eligibility=Eligible
	* &address_status=confirmed
	* &payer_id=Q5HTJ93G8FQKC
	* &tax=0.00
	* &address_street=10101+Some+Street
	* &payment_date=12%3A13%3A19+Dec+05%2C+2008+PST
	* &payment_status=Completed
	* &charset=windows-1252
	* &address_zip=11259
	* &first_name=John
	* &mc_fee=1.75
	* &address_country_code=US
	* &address_name=John+Doe
	* &custom=some+custom+value
	* &payer_status=verified
	* &business=receiver%40domain.com
	* &address_country=United+States
	* &address_city=Some+City
	* &quantity=1
	* &payer_email=sender%40emaildomain.com
	* &txn_id=3JK16594EX581780W
	* &payment_type=instant
	* &payer_business_name=John+Doe
	* &last_name=Doe
	* &address_state=CA
	* &receiver_email=receiver%40domain.com
	* &payment_fee=1.75
	* &receiver_id=YG9UDRP6DE45G
	* &txn_type=web_accept
	* &item_name=Name+of+item
	* &mc_currency=USD
	* &item_number=Number+of+Item
	* &residence_country=US
	* &handling_amount=0.00
	* &transaction_subject=Subject+of+Transaction
	* &payment_gross=49.99
	* &shipping=0.00
	* &=
	*/

	/**
	 * VALID PAYMENT_STATUS VALUES returned from Paypal
	*
	* Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was reversed have been returned to you.
	* Completed: The payment has been completed, and the funds have been added successfully to your account balance.
	* Created: A German ELV payment is made using Express Checkout.
	* Denied: You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the pending_reason variable or the Fraud_Management_Filters_x variable.
	* Expired: This authorization has expired and cannot be captured.
	* Failed: The payment has failed. This happens only if the payment was made from your customer�s bank account.
	* Pending: The payment is pending. See pending_reason for more information.
	* Refunded: You refunded the payment.
	* Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
	* Processed: A payment has been accepted.
	* Voided: This authorization has been voided.
	*/
