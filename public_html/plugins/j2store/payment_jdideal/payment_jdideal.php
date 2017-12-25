<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

use Jdideal\Gateway;

defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/payment.php';

/**
 * J2Store payment plugin.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class plgJ2StorePayment_jdideal extends J2StorePaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	public $_element    = 'payment_jdideal';

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An array that holds the plugin configuration.
	 *
	 * @since 1.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage('plg_j2store_jdideal', JPATH_ADMINISTRATOR);
	}

	/**
	 * Prepares the payment form
	 * and returns HTML Form to be displayed to the user
	 * generally will have a message saying, 'confirm entries, then click complete order'
	 *
	 * @param   array  $data  Form post data
	 *
	 * @return  string   HTML to display
	 *
	 * @since   2.0
	 *
	 * @throws  Exception
	 */
	public function _prePayment($data)
	{
		// Create the URLs
		$jinput = JFactory::getApplication()->input;
		$item_id = $jinput->get('Itemid');

		$uri = JUri::getInstance(JRoute::_(JUri::root() . 'index.php?option=com_j2store&view=checkout'));
		$uri->setVar('order_id', $data['order_id']);
		$uri->setVar('orderpayment_id', $data['orderpayment_id']);
		$uri->setVar('orderpayment_type', $this->_element);
		$uri->setVar('task', 'confirmPayment');
		$uri->setVar('Itemid', $item_id);
		$return_url = $uri->toString();

		// Set some needed data
		$data = array(
				'amount'		=> $data['orderpayment_amount'],
				'order_number'	=> $data['order_id'],
				'order_id'		=> $data['orderpayment_id'],
				'origin'		=> 'j2store',
				'return_url' 	=> $return_url,
				'notify_url'	=> '',
				'cancel_url'	=> '',
				'email'	        => '',
				'payment_method' => '',
				'profileAlias'  => $this->params->get('profileAlias'),
				'custom_html'   => '',
				'silent'        => false,
		);

		// Build the form
		ob_start();

		// Include the payment form
		$layout = new JLayoutFile('forms.form', null, array('component' => 'com_jdidealgateway'));
		echo $layout->render(array('data' => $data));
		$html = ob_get_contents();

		ob_end_clean();

		// Set the form to be processed
		return $html;
	}

	/**
	 * Processes the payment form
	 * and returns HTML to be displayed to the user
	 * generally with a success/failed message
	 *
	 * @param   array  $data  Form post data.
	 *
	 * @return string   HTML to display
	 *
	 * @since  2.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 */
	public function _postPayment($data)
	{
		$jinput = JFactory::getApplication()->input;

		// Load the helper
		JLoader::registerNamespace('Jdideal', JPATH_LIBRARIES);
		$jdideal = new Gateway;
		$vars = new stdClass;

		$trans = $jinput->get('transactionId');
		$details = $jdideal->getDetails($trans, 'trans', false, 'j2store');
		$vars->message = $jdideal->getMessage($details->id);

		// Show the result
		switch ($jdideal->getStatusCode($details->result))
		{
			case 'C':
				$state = 1;
				break;
			case 'X':
				$state = 6;
				break;
			default:
				$state = 4;
				break;
		}

		// Load the orderpayment record and set some values
		F0FTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_j2store/tables');
		$orderpayment = F0FTable::getInstance('Order', 'J2StoreTable');

		$orderpayment->load(array('j2store_order_id' => $details->order_id));
		$orderpayment->transaction_id = $trans;
		$orderpayment->transaction_status = ucfirst($details->result);
		$orderpayment->order_state = ucfirst($details->result);

		// Save the orderpayment
		if ($state == 1)
		{
			$orderpayment->payment_complete();
		}
		else
		{
			$orderpayment->update_status($state, true);
		}

		if ($orderpayment->store())
		{
			$orderpayment->empty_cart();
		}
		else
		{
			$vars->message = $orderpayment->getError();
		}

		$html = $this->_getLayout('postpayment', $vars);

		return $html;
	}

	/**
	 * Prepares variables and
	 * Renders the form for collecting payment info
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return string  Output to show on selection
	 *
	 * @since  2.0
	 */
	public function _renderForm($data)
	{
		$vars = new JObject;
		$vars->onselection_text = $this->params->get('onselection', '');
		$html = $this->_getLayout('form', $vars);

		return $html;
	}

	/**
	 * Verifies that all the required form fields are completed
	 * if any fail verification, set
	 * $object->error = true
	 * $object->message .= '<li>x item failed verification</li>'
	 *
	 * @param   array  $submitted_values  Post data.
	 *
	 * @return JObject  An empty JObject
	 *
	 * @since  2.0
	 */
	public function _verifyForm($submitted_values)
	{
		$object = new JObject;
		$object->error = false;
		$object->message = '';

		return $object;
	}

	/**
	 * Generates a dropdown list of valid payment methods.
	 *
	 * @param   string  $field    Name of the field.
	 * @param   string  $default  Default value of the field.
	 * @param   string  $options  Optional options.
	 *
	 * @return  string  An empty string.
	 */
	public function _paymentMethods($field='jdideal_payment_method', $default='', $options='')
	{
		return '';
	}
}
