<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

namespace Jdideal\Psp;

use Jdideal\Gateway;

defined('_JEXEC') or die;

/**
 * Mollie processor.
 *
 * @package     JDiDEAL
 * @subpackage  Mollie
 * @since       2.12
 */
class Mollie
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
	 * Array with return data from Mollie
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
		$this->data['id'] = $jinput->get('id', false);
		$this->data['transaction_id'] = $jinput->get('transaction_id');

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
			'ideal'        => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_IDEAL'),
			'creditcard'   => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_CREDITCARD'),
			'mistercash'   => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANCONTACT'),
			'sofort'       => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_SOFORT'),
			'paypal'       => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYPAL'),
			'paysafecard'  => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYSAFECARD'),
			'banktransfer' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANKTRANSFER'),
			'bitcoin'      => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BITCOIN'),
			'belfius'      => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BELFIUS'),
			'kbc'          => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_KBC'),
			'podiumkaart'  => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PODIUM'),
			'giftcard'     => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_GIFTCARD'),
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
		$options = array();
		$cards   = false;
		$banks   = false;

		// Get the payment method, plugin overrides component
		if (isset($data->payment_method) && $data->payment_method)
		{
			$selected = array();
			$selected[] = strtolower($data->payment_method);
		}
		else
		{
			$selected = $jdideal->get('payment', array('ideal'));

			// If there is no choice made, set the value empty
			if ($selected[0] === 'all')
			{
				$selected[0] = '';
			}
		}

		// Process the selected payment methods
		foreach ($selected as $name)
		{
			switch ($name)
			{
				case 'creditcard':
					$options[] = \JHtml::_('select.option', 'creditcard', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_CREDITCARD'));
					break;
				case 'mistercash':
					$options[] = \JHtml::_('select.option', 'mistercash', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANCONTACT'));
					break;
				case 'paypal':
					$options[] = \JHtml::_('select.option', 'paypal', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYPAL'));
					break;
				case 'paysafecard':
					$options[] = \JHtml::_('select.option', 'paysafecard', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYSAFECARD'));
					break;
				case 'banktransfer':
					$options[] = \JHtml::_('select.option', 'banktransfer', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANKTRANSFER'));
					break;
				case 'sofort':
					$options[] = \JHtml::_('select.option', 'sofort', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_SOFORT'));
					break;
				case 'bitcoin':
					$options[] = \JHtml::_('select.option', 'bitcoin', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BITCOIN'));
					break;
				case 'belfius':
					$options[] = \JHtml::_('select.option', 'belfius', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BELFIUS'));
					break;
				case 'kbc':
					$options[] = \JHtml::_('select.option', 'kbc', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_KBC'));
					break;
				case 'podiumcadeaukaart':
					$options[] = \JHtml::_('select.option', 'podiumkaart', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PODIUM'));
					break;
				case 'giftcard':
					$options[] = \JHtml::_('select.option', 'giftcard', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_GIFTCARD'));
					$cards = array();
					$cards[] = \JHtml::_('select.option', '', \JText::_('COM_JDIDEALGATEWAY_SELECT_GIFTCARD'));
					$cards[] = \JHtml::_('select.option', 'fashioncheque', \JText::_('COM_JDIDEALGATEWAY_PAYMENT_GIFTCARD_FASHIONCHEQUE'));
					$cards[] = \JHtml::_('select.option', 'nationaleentertainmentcard', \JText::_('COM_JDIDEALGATEWAY_PAYMENT_GIFTCARD_NATIONALEENTERTAINMENTCARD'));
					$cards[] = \JHtml::_('select.option', 'podiumcadeaukaart', \JText::_('COM_JDIDEALGATEWAY_PAYMENT_GIFTCARD_PODIUMCADEAUKAART'));
					$cards[] = \JHtml::_('select.option', 'vvvgiftcard', \JText::_('COM_JDIDEALGATEWAY_PAYMENT_GIFTCARD_VVVGIFTCARD'));
					$cards[] = \JHtml::_('select.option', 'webshopgiftcard', \JText::_('COM_JDIDEALGATEWAY_PAYMENT_GIFTCARD_WEBSHOPGIFTCARD'));
					$cards[] = \JHtml::_('select.option', 'yourgift', \JText::_('COM_JDIDEALGATEWAY_PAYMENT_GIFTCARD_YOURGIFT'));
					break;
				case 'ideal':
					$options[] = \JHtml::_('select.option', 'ideal', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_IDEAL'));

					// Load the Mollie class
					require_once JPATH_LIBRARIES . '/Jdideal/Psp/Mollie/Mollie/API/Autoloader.php';
					$mollie = new \Mollie_API_Client;
					$mollie->setApiKey($jdideal->get('profile_key'));
					$methods = $mollie->methods->get('ideal', array('include' => 'issuers'));

					foreach ($methods->issuers as $issuer)
					{
						$banks['Nederland']['items'][] = \JHtml::_('select.option', $issuer->id, $issuer->name);
					}
					break;
				default:
					$options[] = '';
					break;
			}
		}

		$output = array();
		$output['payments'] = $options;
		$output['redirect'] = $jdideal->get('redirect', 'wait');

		if ($cards)
		{
			$output['cards'] = $cards;
		}

		if ($banks)
		{
			$output['banks'] = $banks;
		}

		$jdideal->log(\JText::sprintf('COM_JDIDEAL_SELECTED_CARD', $selected[0]), $data->logid);

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
	 * Send payment to Mollie.
	 *
	 * @param   Gateway  $jdideal  An instance of \Jdideal\Gateway.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *
	 * @throws  \Exception
	 * @throws  \RuntimeException
	 * @throws  \Mollie_API_Exception
	 */
	public function sendPayment(Gateway $jdideal)
	{
		$app = \JFactory::getApplication();
		$logId = $this->jinput->get('logid', 0, 'int');

		// Load the Mollie class
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Mollie/Mollie/API/Autoloader.php';
		$mollie = new \Mollie_API_Client;
		$mollie->setApiKey($jdideal->get('profile_key'));

		// Load the stored data
		$details = $jdideal->getDetails($logId);

		if (!is_object($details))
		{
			throw new \RuntimeException(\JText::sprintf('COM_JDIDEALGATEWAY_NO_TRANSACTION_DETAILS', 'Mollie', $logId));
		}

		$trans = time();
		$jdideal->setTrans($trans, $logId);
		$notify_url = \JUri::root() . 'cli/notify.php?transaction_id=' . $trans;

		// Replace some predefined values
		$find        = array();
		$find[]      = '{KLANTNR}';
		$find[]      = '{ORDERNR}';
		$replace     = array();
		$user        = \JFactory::getUser();
		$replace[]   = $user->id;
		$replace[]   = $details->order_number;
		$description = substr(str_ireplace($find, $replace, $jdideal->get('description')), 0, 32);

		try
		{
			// Load the chosen payment method
			$paymentMethod = $this->jinput->get('payment');

			switch ($paymentMethod)
			{
				case 'ideal':
					$issuerID = $this->jinput->get('banks', '');
					break;
				case 'giftcard':
					$issuerID = $this->jinput->get('cards', '');
					break;
				default:
					$issuerID = '';
					break;
			}

			// Store the chosen payment method
			$jdideal->setTransactionDetails($paymentMethod, 0, $logId);

			// Build the metadata to send to Mollie
			$metadata = array(
				'order_id' => $details->order_number
			);

			// Load the addon
			$addon = $jdideal->getAddon($details->origin);

			// Need customer information for the banktransfer
			if ($paymentMethod === 'banktransfer')
			{
				// Set the status to transfer as it works different than other payment options
				$jdideal->status('TRANSFER', $logId);

				// Load the customer details
				$customer = $addon->getCustomerInformation($details->order_id);

				if ($customer)
				{
					$metadata['billingEmail'] = $customer['billing']->email;

					$jdideal->log('Email: ' . $customer['billing']->email, $logId);
				}
			}

			$payment = $mollie->payments->create(
				array(
					'amount'      => $details->amount,
					'description' => $description,
					'method'      => $paymentMethod,
					'redirectUrl' => $notify_url . '&output=customer',
					'webhookUrl'  => $notify_url,
					'metadata'    => $metadata,
					'issuer'      => $issuerID,
				)
			);

			// Add some info to the log
			$jdideal->log('Send customer to URL: ' . $payment->getPaymentUrl(), $logId);

			// Send the customer to the bank
			$app->redirect($payment->getPaymentUrl());
		}
		catch (\Mollie_API_Exception $e)
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
	 * @since   2.13
	 *
	 * @throws  \RuntimeException
	 * @throws  \Mollie_API_Exception
	 */
	public function transactionStatus(Gateway $jdideal, $logId)
	{
		// Log the received data
		foreach ($this->data as $name => $value)
		{
			$jdideal->log($name . ':' . $value, $logId);
		}

		$status = array();
		$status['isOK'] = false;

		// Check if we have a banktransfer, in that case status is OK
		$details = $jdideal->getDetails($logId);

		if ($details->card === 'banktransfer')
		{
			$status['isOK'] = true;
			$status['suggestedAction'] = 'TRANSFER';
		}

		if (array_key_exists('id', $this->data) && $this->data['id'])
		{
			// Store the payment ID, needed for retrieving order status at a later time
			$jdideal->setPaymentId($this->data['id'], $logId);

			// Load the Mollie class
			require_once JPATH_LIBRARIES . '/Jdideal/Psp/Mollie/Mollie/API/Autoloader.php';
			$mollie = new \Mollie_API_Client;
			$mollie->setApiKey($jdideal->get('profile_key'));

			$payment = $mollie->payments->get($this->data['id']);

			$status['isOK'] = true;
			$status['card'] = $payment->method;

			$jdideal->log('Received payment status: ' . $payment->status, $logId);
			$jdideal->log('Received card: ' . $payment->method, $logId);

			if ($status['card'] === 'banktransfer' && !in_array($payment->status, array('paid', 'cancelled'), true))
			{
				$status['isOK'] = true;
				$status['error_message'] = '';
				$status['suggestedAction'] = 'TRANSFER';
				$status['consumer'] = array();

				$jdideal->setTransactionDetails($status['card'], 0, $logId);
			}
			else
			{
				switch ($payment->status)
				{
					case 'open':
					case 'pending':
					case 'paidout':
						$status['suggestedAction'] = 'OPEN';
						break;
					case 'cancelled':
					case 'refunded':
					case 'charged_back':
						$status['suggestedAction'] = 'CANCELLED';
						break;
					case 'fail':
					case 'expired':
						$status['suggestedAction'] = 'FAILURE';
						break;
					case 'paid':
						$status['suggestedAction'] = 'SUCCESS';
						break;
				}

				$jdideal->setTransactionDetails($status['card'], 1, $logId);

				// Get the customer info
				$status['consumer'] = (array) $payment->details;

				if (empty($status['consumer']))
				{
					$status['consumer']['consumerAccount'] = '';
					$status['consumer']['consumerName'] = '';
					$status['consumer']['consumerCity'] = '';
				}
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
