<?php
/**
 * @package     JDiDEAL
 * @subpackage  Targetpay
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
 * TargetPay processor.
 *
 * @package     JDiDEAL
 * @subpackage  Targetpay
 * @since       2.12
 */
class Targetpay
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
	 * Array with return data from the Rabobank
	 *
	 * @var    array
	 * @since  4.0
	 */
	private $data = array();

	/**
	 * Contruct the payment reference.
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
		$this->data['isCustomer'] = $jinput->get('output') === 'customer';
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
			'mistercash'  => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANCONTACT'),
			'sofort'      => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_SOFORT'),
			'paysafecard' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYSAFECARD'),
			'premiumsms'  => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PREMIUMSMS'),
		);
	}

	/**
	 * Prepare data for the form.
	 *
	 * @param   Gateway  $jdideal  An instance of JdIdealgateway.
	 * @param   object   $data     An object with transaction information.
	 *
	 * @return  array  The data for the form.
	 *
	 * @since   2.13
	 *
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 */
	public function getForm(Gateway $jdideal, $data)
	{
		// Load the TargetPay class to get the banks
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Targetpay/Targetpay.php';
		$banks = '';

		// Get the payment method, plugin overrides component
		if (isset($data->payment_method) && $data->payment_method)
		{
			$selected[0] = strtolower($data->payment_method);
		}
		else
		{
			$selected = $jdideal->get('payment');
		}

		$options = array();
		$data->file = 'targetpay';

		foreach ($selected as $key => $name)
		{
			switch ($name)
			{
				case 'ideal':
					$options[] = \JHtml::_('select.option', 'ideal', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_IDEAL'));
					$selected[$key] = 'ideal';
					$targetPay = new \TargetPay($jdideal->get('rtlo'));
					$banks = $targetPay->DirectoryRequest();
					$data->redirect = 'wait';
					break;
				case 'mistercash':
					$options[] = \JHtml::_('select.option', 'mistercash', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MISTERCASH'));
					$data->redirect = $jdideal->get('redirect', 'wait');
					break;
				case 'sofort':
					$options[] = \JHtml::_('select.option', 'sofort', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_SOFORT'));
					$data->redirect = $jdideal->get('redirect', 'wait');
					break;
				case 'paysafecard':
					$options[] = \JHtml::_('select.option', 'paysafecard', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYSAFECARD'));
					$data->redirect = $jdideal->get('redirect', 'wait');
					break;
				case 'premiumsms':
					$data->file = 'premiumsms';
					break;
			}
		}

		if ($data->file === 'premiumsms')
		{
			// Load the SMS handler
			require_once JPATH_LIBRARIES . '/Jdideal/Psp/Targetpay/Targetpaysms.php';
			$targetPay = new \TargetPaySms($jdideal->get('rtlo'));

			// Start the payment
			$country = $jdideal->get('sms_country');

			if (($country === '31' && $data->amount > 6) || ($country === '32' && $data->amount > 2))
			{
				throw new \InvalidArgumentException(\JText::_('COM_JDIDEALGATEWAY_PREMIUMSMS_VALUE_TOO_HIGH'));
			}
			else
			{
				// Amount in euros
				$targetPay->setAmount($data->amount);
				$targetPay->setCountry($country);
				$codes = $targetPay->startPayment();

				if (!is_array($codes))
				{
					throw new \RuntimeException($targetPay->errorMessage);
				}

				$data->numbertosms = $codes[0];
				$data->keyword = $codes[1];
				$data->costs = trim(substr($codes[2], 1));

				// Generate a transaction ID
				$data->trxid = uniqid('', true);
				$jdideal->setTrans($data->trxid, $data->logid);

				$data->file = 'premiumsms';
			}
		}
		else
		{
			// Create the list of possible payment methods
			if (count($options) > 1)
			{
				$payments = \JHtml::_('select.genericlist', $options, 'payment', 'onchange="showBanks(this);";');
				$data->redirect = 'wait';
			}
			else
			{
				$payments = '<input type="hidden" name="payment" value="' . $selected[0] . '">';
			}

			$data->output['payments'] = $payments;

			if ($banks)
			{
				$data->output['banks'] = $banks;
			}
		}

		return $data;
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
	 * Send payment to Target Pay.
	 *
	 * @param   Gateway  $jdideal  An instance of Gateway.
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

		// Load the stored data
		$details = $jdideal->getDetails($logId);

		if (!is_object($details))
		{
			$jdideal->log('No details found for this transaction', $logId);

			throw new \RuntimeException(\JText::sprintf('COM_JDIDEALGATEWAY_NO_TRANSACTION_DETAILS', 'Targetpay', $logId));
		}

		// Replace some predefined values
		$find = array();
		$find[] = '{KLANTNR}';
		$find[] = '{ORDERNR}';
		$replace = array();
		$user = \JFactory::getUser();
		$replace[] = $user->id;
		$replace[] = $details->order_number;
		$description = substr(str_ireplace($find, $replace, $jdideal->get('description')), 0, 32);

		// Load the Target Pay class
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Targetpay/Targetpay.php';

		/** @var \TargetPay $targetPay */
		$targetPay = new \TargetPay($jdideal->get('rtlo'));

		// Build parameter string
		$targetPay->bank        = $this->jinput->get('bank', '');
		$targetPay->description = $description;
		$targetPay->amount      = $details->amount;
		$targetPay->country     = $jdideal->get('country');
		$targetPay->language    = $jdideal->get('lang');
		$targetPay->type        = $jdideal->get('type');
		$targetPay->returnurl   = \JUri::root() . 'cli/notify.php?output=customer';
		$targetPay->reporturl   = \JUri::root() . 'cli/notify.php';
		$targetPay->testmode    = $jdideal->get('testmode', 0);

		// Add the card to the log
		$payment = $this->jinput->get('payment');
		$jdideal->setTransactionDetails($payment, 0, $logId);

		// Initiate the transaction
		if ($targetPay->TransactionRequest($payment) === 0)
		{
			// Set the transaction ID
			$jdideal->setTrans($targetPay->trxId, $logId);

			// Add some info to the log
			$jdideal->log('Send customer to URL: ' . $targetPay->issuerUrl, $logId);

			// Send the customer to the bank
			$app->redirect($targetPay->issuerUrl);
		}
		else
		{
			$jdideal->log($targetPay->errorMessage, $logId);
			$redirect = $details->cancel_url ?: $details->return_url;
			$jdideal->log('Redirect to: ' . $redirect, $logId);
			$app->redirect($redirect, $targetPay->errorMessage, 'error');
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
	 * @since   2.13
	 *
	 * @throws  \Exception
	 * @throws  \RuntimeException
	 */
	public function transactionStatus(Gateway $jdideal, $logId)
	{
		// Log the received data
		foreach ($this->data as $name => $value)
		{
			$jdideal->log($name . ':' . $value, $logId);
		}

		// Load the Target Pay class
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Targetpay/Targetpay.php';

		// Get the selected payment
		$selected = $jdideal->get('payment');

		if (in_array('premiumsms', $selected, true))
		{
			$jdideal->log('Processing Premium SMS', $logId);

			// Load the Targetpay SMS class
			require_once JPATH_LIBRARIES . '/Jdideal/Psp/Targetpay/Targetpaysms.php';

			// Set the values
			$targetPay = new \TargetPaySms($jdideal->get('rtlo'));
			$targetPay->setTestmode($jdideal->get('testmode', 'false'));
			$keyword = $this->jinput->getCmd('keyword');
			$shortcode = $this->jinput->getCmd('shortcode');
			$paycode = $this->jinput->getInt('paycode');

			// Start the payment
			$country = $jdideal->get('sms_country');
			$targetPay->setCountry($country);
			$result = $targetPay->validatePayment($keyword, $paycode, $shortcode);
			$jdideal->setTransactionDetails('premiumsms', 1, $logId);
		}
		else
		{
			$details = $jdideal->getDetails($logId);
			$jdideal->log('Processing ' . $details->card, $logId);

			$targetPay = new \TargetPay($jdideal->get('rtlo'));
			$targetPay->setTestmode($jdideal->get('testmode', 'false'));
			$result = $targetPay->checkPayment($details->card, $this->data['transaction_id']);
			$jdideal->setTransactionDetails($details->card, 1, $logId);
		}

		// Check the status of the payment
		if ($result)
		{
			$status['isOK'] = true;
			$status['error_message'] = '';
			$status['suggestedAction'] = 'SUCCESS';
		}
		else
		{
			$jdideal->log($targetPay->errorMessage, $logId);

			// Check if it is an open or cancelled status
			$code = strtoupper(substr($targetPay->errorMessage, 0, 6));

			switch ($code)
			{
				case 'TP0010':
					$status['suggestedAction'] = 'OPEN';
					break;
				case 'TP0012':
				case 'TP0013':
					$status['suggestedAction'] = 'FAILURE';
					break;
				default:
					$status['suggestedAction'] = 'CANCELLED';
					break;
			}

			$status['isOK'] = true;
			$status['error_message'] = $targetPay->errorMessage;
		}

		$status['card'] = '';

		// Get the customer info
		$status['consumer'] = array();

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
}
