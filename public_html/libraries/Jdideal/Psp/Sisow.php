<?php
/**
 * @package     JDiDEAL
 * @subpackage  Sisow
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
 * Sisow processor.
 *
 * @package     JDiDEAL
 * @subpackage  Sisow
 * @since       2.12
 */
class Sisow
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
	 * Array with return data from Sisow
	 *
	 * @var    array
	 * @since  4.0
	 */
	private $data;

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
		$this->data['transaction_id'] = $jinput->get('trxid');

		// Set if this is the customer
		$notify                   = $jinput->get('notify', false) ? false : true;
		$callback                 = $jinput->get('callback', false) ? false : true;
		$this->data['isCustomer'] = $notify && $callback;
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
			'ideal'       => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_IDEAL'),
			'overboeking' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANKTRANSFER'),
			'sofort'      => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_SOFORT'),
			'mistercash'  => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANCONTACT'),
			'paypalec'    => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYPAL'),
			'visa'        => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_VISA'),
			'mastercard'  => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MASTERCARD'),
			'maestro'     => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MAESTRO'),
			'vpay'        => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_VPAY'),
			'webshop'     => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_WEBSHOP'),
			'podium'      => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PODIUM'),
			'bunq'        => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BUNQ'),
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
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 */
	public function getForm(Gateway $jdideal, $data)
	{
		// Load the Sisow class to get the banks
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Sisow/Sisow.php';

		// Instantiate Sisow
		/** @var \Sisow $sisow */
		$sisow = new \Sisow($jdideal->get('merchant_id'), $jdideal->get('merchant_key'), $jdideal->get('shop_id', 0));

		// Get the payment method, plugin overrides component
		if (isset($data->payment_method) && $data->payment_method)
		{
			$selected[0] = strtolower($data->payment_method);
		}
		else
		{
			$selected = $jdideal->get('payment');
		}

		// Create the list of possible payment methods
		$options = array();
		$banks = '';

		foreach ($selected as $key => $name)
		{
			switch ($name)
			{
				case 'overboeking':
					$options[] = \JHtml::_('select.option', 'overboeking', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANKTRANSFER'));
					break;
				case 'sofort':
					$options[] = \JHtml::_('select.option', 'sofort', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_SOFORT'));
					break;
				case 'mistercash':
					$options[] = \JHtml::_('select.option', 'mistercash', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANCONTACT'));
					break;
				case 'paypalec':
					$options[] = \JHtml::_('select.option', 'paypalec', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYPAL'));
					break;
				case 'visa':
					$options[] = \JHtml::_('select.option', 'visa', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_VISA'));
					break;
				case 'mastercard':
					$options[] = \JHtml::_('select.option', 'mastercard', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MASTERCARD'));
					break;
				case 'maestro':
					$options[] = \JHtml::_('select.option', 'maestro', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MAESTRO'));
					break;
				case 'vpay':
					$options[] = \JHtml::_('select.option', 'vpay', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_VPAY'));
					break;
				case 'webshop':
					$options[] = \JHtml::_('select.option', 'webshop', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_WEBSHOP'));
					break;
				case 'podium':
					$options[] = \JHtml::_('select.option', 'podium', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PODIUM'));
					break;
				case 'bunq':
					$options[] = \JHtml::_('select.option', 'bunq', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BUNQ'));
					break;
				default:
				case 'ideal':
					$options[] = \JHtml::_('select.option', 'ideal', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_IDEAL'));

					// Check if testmode is enabled
					$testmode = $jdideal->get('testmode') ? true : false;

					// Load the banks
					$banks = $sisow->DirectoryRequest($testmode);
					break;
			}
		}

		$output = array();
		$output['payments'] = $options;
		$output['banks'] = $banks;

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

		if ($this->data['transaction_id'])
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('id'))
				->from($this->db->quoteName('#__jdidealgateway_logs'))
				->where($this->db->quoteName('trans') . ' = ' . $this->db->quote($this->data['transaction_id']));
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
		if (!array_key_exists('transaction_id', $this->data))
		{
			throw new \RuntimeException(\JText::_('COM_JDIDEALGATEWAY_NO_TRANSACTIONID_FOUND'));
		}

		// Get the transaction ID
		return $this->data['transaction_id'];
	}

	/**
	 * Send payment to Sisow.
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

		// Load the Sisow class
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Sisow/Sisow.php';
		$sisow = new \Sisow($jdideal->get('merchant_id'), $jdideal->get('merchant_key'), $jdideal->get('shop_id', 0));

		// Load the stored data
		$details = $jdideal->getDetails($logId);

		if (!is_object($details))
		{
			throw new \RuntimeException(\JText::sprintf('COM_JDIDEALGATEWAY_NO_TRANSACTION_DETAILS', 'Sisow', $logId));
		}

		// Load the addon
		$addon = $jdideal->getAddon($details->origin);

		// Replace some predefined values
		$find = array();
		$find[] = '{KLANTNR}';
		$find[] = '{ORDERNR}';
		$replace = array();
		$user = \JFactory::getUser();
		$replace[] = $user->id;
		$replace[] = $details->order_number;
		$description = substr(str_ireplace($find, $replace, $jdideal->get('description')), 0, 32);

		// Set the parameters
		$sisow->payment = $this->jinput->get('payment', 'ideal');
		$sisow->issuerId = $this->jinput->get('banks');
		$sisow->purchaseId = substr($details->order_number, 0, 16);
		$sisow->amount = $details->amount;
		$sisow->description = $description;
		$sisow->entranceCode = $details->id;
		$sisow->returnUrl = $jdideal->getUrl() . 'cli/notify.php';
		$sisow->cancelUrl = $jdideal->getUrl() . 'cli/notify.php';
		$sisow->callbackUrl = $jdideal->getUrl() . 'cli/notify.php';
		$sisow->notifyUrl = $jdideal->getUrl() . 'cli/notify.php';
		$sisow->testmode = $jdideal->get('merchant_id') ? true : false;
		$jdideal->log('Callback URL: ' . $sisow->callbackUrl, $logId);
		$arg = array();

		if ($sisow->payment === 'overboeking')
		{
			// Set the status to transfer as it works different than other payment options
			$jdideal->status('TRANSFER', $logId);

			// Load the customer details
			$customer = $addon->getCustomerInformation($details->order_id);

			if ($customer)
			{
				// Billing details
				$arg['billing_firstname'] = $customer['billing']->firstname ?: '';
				$arg['billing_lastname'] = $customer['billing']->lastname ?: '';
				$arg['billing_mail'] = $customer['billing']->email ?: '';
				$arg['billing_company'] = $customer['billing']->company ?: '';
				$arg['billing_address1'] = $customer['billing']->address1 ?: '';
				$arg['billing_address2'] = $customer['billing']->address2 ?: '';
				$arg['billing_zip'] = $customer['billing']->zip ?: '';
				$arg['billing_city'] = $customer['billing']->city ?: '';
				$arg['billing_country'] = $customer['billing']->country ?: '';
				$arg['billing_countrycode'] = $customer['billing']->countrycode ?: '';
				$arg['billing_phone'] = $customer['billing']->phone1 ?: '';

				// Shipping details
				$arg['shipping_firstname'] = $customer['shipping']->firstname ?: '';
				$arg['shipping_lastname'] = $customer['shipping']->lastname ?: '';
				$arg['shipping_mail'] = $customer['shipping']->email ?: '';
				$arg['shipping_company'] = $customer['shipping']->company ?: '';
				$arg['shipping_address1'] = $customer['shipping']->address1 ?: '';
				$arg['shipping_address2'] = $customer['shipping']->address2 ?: '';
				$arg['shipping_zip'] = $customer['shipping']->zip ?: '';
				$arg['shipping_city'] = $customer['shipping']->city ?: '';
				$arg['shipping_country'] = $customer['shipping']->country ?: '';
				$arg['shipping_countrycode'] = $customer['shipping']->countrycode ?: '';
				$arg['shipping_phone'] = $customer['shipping']->phone ?: '';

				// Other details
				$arg['amount'] = $customer['billing']->amount ? round($customer['billing']->amount * 100.0, 0) : 0;
				$arg['tax'] = $customer['billing']->tax ? round($customer['billing']->tax * 100.0, 0) : 0;
				$arg['currency'] = $customer['currency'] ?: '';
			}
		}

		// Initiate the transaction
		if ($sisow->TransactionRequest($arg) === 0)
		{
			// Set the transaction ID
			$jdideal->setTrans($sisow->trxId, $logId);

			// Store the payment type
			$jdideal->setTransactionDetails($sisow->payment, 0, $logId);

			// Check where to send the customer
			switch ($sisow->payment)
			{
				case 'overboeking':
					if (0 === count($arg))
					{
						// No customer details, can't continue
						$jdideal->log('No customer details found, bank transfer is not possible', $logId);
						$jdideal->log('Return URL: ' . $sisow->cancelUrl . '?ec=' . $logId . '&trxid=' . $sisow->trxId, $logId);
						$app->redirect($sisow->cancelUrl . '?ec=' . $logId . '&trxid=' . $sisow->trxId);
					}
					else
					{
						// Send the customer to the success page
						$jdideal->log('No customer details found, bank transfer is not possible', $logId);
						$jdideal->log('Return URL: ' . $sisow->notifyUrl . '?ec=' . $logId . '&trxid=' . $sisow->trxId, $logId);
						$app->redirect($sisow->notifyUrl . '?ec=' . $logId . '&trxid=' . $sisow->trxId);
					}
					break;
				default:
					// Send the customer to the bank
					$jdideal->log('Sending customer to URL: ' . $sisow->issuerUrl, $logId);
					$app->redirect($sisow->issuerUrl);
					break;
			}
		}
		else
		{
			$jdideal->log($sisow->errorMessage, $logId);
			$redirect = $details->cancel_url === '' ? $details->return_url : $details->cancel_url;
			$jdideal->log('Redirect to: ' . $redirect, $logId);
			$app->redirect($redirect, $sisow->errorMessage, 'error');
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
	 * @param   Gateway  $jdideal  An instance of JdIdealGateway.
	 * @param   int      $logId    The ID of the transaction log.
	 *
	 * @return  array  Array of transaction details.
	 *
	 * @since   2.13
	 *
	 * @throws  \RuntimeException
	 */
	public function transactionStatus(Gateway $jdideal, $logId)
	{
		// Load the Sisow class
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Sisow/Sisow.php';
		$sisow = new \Sisow($jdideal->get('merchant_id'), $jdideal->get('merchant_key'), $jdideal->get('shop_id', 0));

		$details = $jdideal->getDetails($logId);
		$transactionID = $details->trans;
		$status['transactionID'] = $transactionID;

		// Check the transaction status
		$result = $sisow->StatusRequest($transactionID);

		if ($result === 0)
		{
			$status['card'] = $details->card;
			$jdideal->log('Received status: ' . $sisow->status, $logId);

			if ($status['card'] === 'overboeking' && strtolower($sisow->status) === 'pending')
			{
				$status['isOK'] = true;
				$status['error_message'] = '';
				$status['suggestedAction'] = 'TRANSFER';
				$status['consumer'] = array();
			}
			else
			{
				// Check if the XML is valid
				$sha1 = sha1(
					$sisow->trxId .
					$sisow->status .
					($sisow->amount * 100) .
					$sisow->purchaseId .
					$sisow->entranceCode .
					$sisow->consumerAccount .
					$jdideal->get('merchant_id') .
					$jdideal->get('merchant_key')
				);

				if ($sha1 === $sisow->sha1)
				{
					// Log some details
					$jdideal->log('Status: ' . $sisow->status, $logId);
					$jdideal->log('Timestamp: ' . $sisow->timeStamp, $logId);
					$jdideal->log('Amount: ' . $sisow->amount, $logId);
					$jdideal->log('Consumer account: ' . $sisow->consumerAccount, $logId);
					$jdideal->log('Consumer name: ' . $sisow->consumerName, $logId);
					$jdideal->log('Consumer city: ' . $sisow->consumerCity, $logId);
					$jdideal->log('Purchase ID: ' . $sisow->purchaseId, $logId);
					$jdideal->log('Description: ' . $sisow->description, $logId);
					$jdideal->log('Entrance code: ' . $sisow->entranceCode, $logId);
					$jdideal->log('Document ID: ' . $sisow->documentId, $logId);
					$jdideal->log('Document URL: ' . $sisow->documentUrl, $logId);

					$jdideal->setTransactionDetails($status['card'], 1, $logId);

					$status['isOK'] = true;
					$status['error_message'] = '';

					switch (strtoupper($sisow->status))
					{
						case 'EXPIRED':
							$status['suggestedAction'] = 'FAILURE';
							break;
						default:
							$status['suggestedAction'] = $sisow->status;
							break;
					}

					// Consumer data
					$status['consumer'] = array();
					$status['consumer']['consumerAccount'] = $sisow->consumerAccount;
					$status['consumer']['consumerName'] = $sisow->consumerName;
					$status['consumer']['consumerCity'] = $sisow->consumerCity;
				}
				else
				{
					$jdideal->log(\JText::_('COM_JDIDEAGATEWAY_SHA_VERIFY_FAILED'), $logId);
					$status['isOK'] = false;
					$status['error_message'] = \JText::_('COM_JDIDEAGATEWAY_SHA_VERIFY_FAILED');
					$status['suggestedAction'] = 'CANCELLED';
					$status['consumer'] = array();
				}
			}
		}
		// Transaction ID is missing
		elseif ($result === -4)
		{
			$jdideal->log('Error code: ' . $sisow->errorCode, $logId);
			$jdideal->log('Error message: ' . $sisow->errorMessage, $logId);

			$status['isOK'] = false;
			$status['error_message'] = $sisow->errorMessage;
		}
		// Any other error code
		else
		{
			$jdideal->log('Error code: ' . $sisow->errorCode, $logId);
			$jdideal->log('Error message: ' . $sisow->errorMessage, $logId);
			$jdideal->log('Result: ' . $result, $logId);

			$status['isOK'] = true;
			$status['error_message'] = $sisow->errorMessage;
			$status['suggestedAction'] = 'CANCELLED';
			$status['consumer'] = array();
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
		return $this->data['isCustomer'];
	}

	/**
	 * Create the options to show on a checkout page.
	 *
	 * @param   Gateway  $jdideal        An instance of JdidealGateway.
	 * @param   string   $paymentMethod  The name of the chosen payment method.
	 *
	 * @return  array  List of select options.
	 *
	 * @since   4.1.0
	 *
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 */
	public function getCheckoutOptions(Gateway $jdideal, $paymentMethod)
	{
		// Load the Sisow class to get the banks
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Sisow/Sisow.php';

		// Instantiate Sisow
		/** @var \Sisow $sisow */
		$sisow = new \Sisow($jdideal->get('merchant_id'), $jdideal->get('merchant_key'), $jdideal->get('shop_id', 0));

		// Check if testmode is enabled
		$testMode = $jdideal->get('testmode') ? true : false;

		$banks = null;

		switch (strtolower($paymentMethod))
		{
			case 'ideal':
				// Load the banks
				$banks = $sisow->DirectoryRequest($testMode);
				break;
		}

		return $banks;
	}
}
