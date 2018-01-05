<?php
/**
 * @package     JDiDEAL
 * @subpackage  Kassacompleet
 *
 * @author      Roland Dalmulder <contact@jdideal.nl>
 * @copyright   Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://jdideal.nl
 */

namespace Jdideal\Psp;

use Jdideal\Gateway;

defined('_JEXEC') or die;

/**
 * Kassa Compleet processor.
 *
 * @package     JDiDEAL
 * @subpackage  Kassacompleet
 *
 * @link        https://s3-eu-west-1.amazonaws.com/wl1-apidocs/api.kassacompleet.nl/index.html
 * @link        https://github.com/gingerpayments/ginger-php
 *
 * @since       4.0
 */
class Kassacompleet
{
	/**
	 * Database driver
	 *
	 * @var    \JDatabaseDriver
	 * @since  4.0
	 */
	private $db;

	/**
	 * Input processor
	 *
	 * @var    \JInput
	 * @since  4.0
	 */
	private $jinput;

	/**
	 * Array with return data from Kassacompleet
	 *
	 * @var    array
	 * @since  4.0
	 */
	private $data;

	/**
	 * Set if the customer or PSP is calling
	 *
	 * @var    bool
	 * @since  4.0
	 */
	private $isCustomer = false;

	/**
	 * Construct the payment reference.
	 *
	 * @param   \Jinput  $jinput  The input object.
	 *
	 * @since   4.0
	 */
	public function __construct(\JInput $jinput)
	{
		// Set the input
		$this->jinput = $jinput;

		// Set the database
		$this->db = \JFactory::getDbo();

		// Put the return data in an array, data is constructed as name=value
		$this->data['transactionId'] = $jinput->get('order_id');

		// Set who is calling
		$this->isCustomer = $jinput->get('output', '') === 'customer';
	}

	/**
	 * Returns a list of available payment methods.
	 *
	 * @return  array  List of available payment methods.
	 *
	 * @since   3.0
	 */
	public function getAvailablePaymentMethods()
	{
		return array(
			'ideal' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_IDEAL'),
			'credit-card' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_CREDITCARD'),
			'paypal' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYPAL'),
			'bank-transfer' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANKTRANSFER'),
			'rembours' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_CASHONDELIVERY'),
		);
	}

	/**
	 * Prepare data for the form.
	 *
	 * @param   Gateway  $jdideal  An instance of JdidealGateway.
	 * @param   object   $data     An object with transaction information.
	 *
	 * @return  array  The data for the form.
	 *
	 * @since   2.13
	 *
	 * @throws   \RuntimeException
	 * @throws   \InvalidArgumentException
	 */
	public function getForm(Gateway $jdideal, $data)
	{
		// Load the form options
		$output = array();

		// Get the payment method, plugin overrides component
		if (isset($data->payment_method) && $data->payment_method)
		{
			$selected = array();
			$selected[] = strtolower($data->payment_method);
		}
		else
		{
			$selected = $jdideal->get('payment', array('all'));

			// If there is no choice made, set the value empty
			if ($selected[0] === 'all')
			{
				$selected = array_flip($this->getAvailablePaymentMethods());
			}
		}

		// Process the selected payment methods
		foreach ($selected as $name)
		{
			switch ($name)
			{
				case 'credit-card':
					$output['payments'][] = \JHtml::_('select.option', 'credit-card', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_CREDITCARD'));
					break;
				case 'paypal':
					$output['payments'][] = \JHtml::_('select.option', 'paypal', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYPAL'));
					break;
				case 'bank-transfer':
					$output['payments'][] = \JHtml::_('select.option', 'bank-transfer', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANKTRANSFER'));
					break;
				case 'rembours':
					$output['payments'][] = \JHtml::_('select.option', 'rembours', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_CASHONDELIVERY'));
					break;
				case 'ideal':
					$output['payments'][] = \JHtml::_('select.option', 'ideal', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_IDEAL'));

					// Load the Kassa Compleet class to get the banks
					require_once JPATH_LIBRARIES . '/Jdideal/Psp/Kassacompleet/Kassacompleet.php';

					// Instantiate Kassa Compleet
					/** @var \Kassacompleet $kassacompleet */
					$kassacompleet = new \Kassacompleet;
					$kassacompleet->setApiKey($jdideal->get('apiKey'));

					// Load the banks
					$output['banks'] = $kassacompleet->getBanks();
					break;
				default:
					$output['payments'][] = '';
					break;
			}
		}

		$output['redirect'] = $jdideal->get('redirect', 'wait');

		return $output;
	}

	/**
	 * Get the log ID.
	 *
	 * @return  int  The ID of the log.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function getLogId()
	{
		$logId = false;

		if ($this->data['transactionId'])
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__jdidealgateway_logs'))
				->where($this->db->quoteName('trans') . ' = ' . $this->db->quote($this->data['transactionId']));
			$this->db->setQuery($query);

			$logId = $this->db->loadResult();
		}

		if (!$logId)
		{
			throw new \RuntimeException(\JText::_('COM_JDIDEALGATEWAY_NO_LOGID_FOUND'));
		}

		return $logId;
	}

	/**
	 * Get the transaction ID.
	 *
	 * @return  int  The ID of the transaction.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function getTransactionId()
	{
		if (!array_key_exists('transactionId', $this->data))
		{
			throw new \RuntimeException(\JText::_('COM_JDIDEALGATEWAY_NO_TRANSACTIONID_FOUND'));
		}

		// Get the transaction ID
		return $this->data['transactionId'];
	}

	/**
	 * Send payment to Kassacompleet.
	 *
	 * @param   Gateway  $jdideal  An instance of \Jdideal\Gateway.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *
	 * @throws  \Exception
	 * @throws  \RuntimeException
	 */
	public function sendPayment(Gateway $jdideal)
	{
		$app = \JFactory::getApplication();
		$logId = $this->jinput->get('logid', 0, 'int');

		// Load the Kassacompleet class
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Kassacompleet/Kassacompleet.php';
		$kassacompleet = new \Kassacompleet;
		$kassacompleet->setApiKey($jdideal->get('apiKey'));

		// Load the stored data
		$details = $jdideal->getDetails($logId);

		if (!is_object($details))
		{
			throw new \RuntimeException(\JText::sprintf('COM_JDIDEALGATEWAY_NO_TRANSACTION_DETAILS', 'Kassacompleet', $logId));
		}

		$notify_url = \JUri::root() . 'cli/notify.php?output=customer';

		try
		{
			// Replace some predefined values
			$find        = array();
			$find[]      = '{KLANTNR}';
			$find[]      = '{ORDERNR}';
			$replace     = array();
			$user        = \JFactory::getUser();
			$replace[]   = $user->id;
			$replace[]   = $details->order_number;
			$description = substr(str_ireplace($find, $replace, $jdideal->get('description')), 0, 32);

			// Load the chosen payment method
			$paymentMethod = $this->jinput->get('payment');
			$jdideal->log(\JText::sprintf('COM_JDIDEAL_SELECTED_CARD', $paymentMethod), $logId);

			// Create the payload
			$payload                                                     = new \stdClass;
			$payload->currency                                           = 'EUR';
			$payload->amount                                             = $details->amount * 100;
			$payload->merchant_order_id                                  = $details->order_number;
			$payload->description                                        = $description;
			$payload->return_url                                         = $notify_url;
			$payload->transactions                                       = array();
			$payload->transactions[0]->payment_method                    = $paymentMethod;
			$payload->transactions[0]->payment_method_details->issuer_id = $this->jinput->get('banks');

			// Create the order at ING
			$response = $kassacompleet->create($payload);

			// Store the transaction ID
			$jdideal->setTrans($response->order_id, $logId);

			// Get the payment URL
			$paymentUrl = $kassacompleet->getPaymentUrl();

			// Store the response in the log
			foreach ($response as $name => $value)
			{
				if (is_string($value))
				{
					$jdideal->log($name . ': ' . $value, $logId);
				}
			}

			// Check if we need to send the customer to the bank
			if ($response->status === 'new' && $paymentUrl)
			{
				// Send the customer to the bank if needed
				$jdideal->log('Send customer to URL: ' . $paymentUrl, $logId);
				$app->redirect($paymentUrl);
			}

			// No need for redirect e.g. bank transfer, go straight to the notify URL
			$app->redirect($notify_url . '&transactionId=' . $response->order_id);

		}
		catch (\RuntimeException $e)
		{
			$jdideal->log('The payment could not be created.', $logId);
			$jdideal->log('Error: ' . $e->getMessage(), $logId);
			$jdideal->log('Notify URL: ' . $notify_url, $logId);

			throw new \RuntimeException($e->getMessage());
		}
	}

	/**
	 * Check the transaction status.
	 *
	 * isOK            = Set if the validation is OK
	 * card            = The payment method used by the customer
	 * suggestedAction = The result of the transaction
	 * error_message   = An error message in case there is an error with the transaction
	 * consumer        = Array with info about the customer
	 *
	 * @param   Gateway  $jdideal  An instance of JdidealGateway.
	 * @param   int      $logId    The ID of the transaction log.
	 *
	 * @return  array  Array of transaction details.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function transactionStatus(Gateway $jdideal, $logId)
	{
		$status = array();
		$status['isOK'] = false;

		// Load the Kassa Compleet class
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Kassacompleet/Kassacompleet.php';
		$kassacompleet = new \Kassacompleet;
		$kassacompleet->setApiKey($jdideal->get('apiKey'));

		// Get the transaction ID
		$transactionId = $this->getTransactionId();

		// Get the order status
		$payment = $kassacompleet->orderStatus($transactionId);

		$status['isOK'] = true;
		$status['card'] = $payment->payment_method;

		$jdideal->log('Received payment status: ' . $payment->status, $logId);

		if ($payment->status !== 'Success' && $status['card'] === 'bank-transfer')
		{
			$status['isOK'] = true;
			$status['error_message'] = '';
			$status['suggestedAction'] = 'TRANSFER';
			$status['reference'] = $payment->payment_method_details->reference;
			$status['consumer'] = array();

			$jdideal->setTransactionDetails($status['card'], 0, $logId, $payment->payment_method_details->reference);

			$jdideal->log('Payment reference: ' . $payment->payment_method_details->reference, $logId);
		}
		else
		{
			switch (strtolower($payment->status))
			{
				case 'cancelled':
				case 'refunded':
				case 'charged_back':
					$status['suggestedAction'] = 'CANCELLED';
					break;
				case 'fail':
				case 'expired':
					$status['suggestedAction'] = 'FAILURE';
					break;
				case 'success':
				case 'completed':
					$status['suggestedAction'] = 'SUCCESS';
					break;
				case 'processing':
				default:
					$status['suggestedAction'] = 'OPEN';
					break;
			}

			$jdideal->setTransactionDetails($status['card'], 0, $logId);

			// Get the customer info
			$status['consumer'] = (array) $payment->payment_method_details;

			if (empty($status['consumer']))
			{
				$status['consumer']['consumerAccount'] = '';
				$status['consumer']['consumerName'] = '';
				$status['consumer']['consumerCity'] = '';
			}
		}

		return $status;
	}

	/**
	 * Check who is knocking at the door.
	 *
	 * @return  bool  True if it is the customer | False if it is the PSP.
	 *
	 * @since   4.0
	 */
	public function isCustomer()
	{
		return $this->isCustomer;
	}
}
