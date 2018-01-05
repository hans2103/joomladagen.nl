<?php
/**
 * @package     JDiDEAL
 * @subpackage  Omnikassa
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
 * EMS processor.
 *
 * @package     JDiDEAL
 * @subpackage  EMS
 * @since       4.2.0
 */
class Ems
{
	/**
	 * Database driver
	 *
	 * @var    \JDatabaseDriver
	 * @since  4.0
	 */
	private $db;

	/**
	 * Array with return data from the Rabobank
	 *
	 * @var    array
	 * @since  4.0
	 */
	private $data = array();

	/**
	 * The live URL
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $liveUrl;

	/**
	 * The test URL
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $testUrl;

	/**
	 * Set if the customer or PSP is calling
	 *
	 * @var    bool
	 * @since  4.0
	 */
	private $isCustomer = false;

	/**
	 * List of return fields
	 *
	 * @var    array
	 * @since  4.2.0
	 */
	private $returnFields = array(
		'approval_code',
		'oid',
		'refnumber',
		'status',
		'txndate_processed',
		'tdate',
		'fail_reason',
		'response_hash',
		'processor_response_code',
		'fail_rc',
		'terminal_id',
		'ccbin',
		'cccountry',
		'ccbrand',
		'response_code_3dsecure',
		'redirectURL',
		'fail_reason_details',
		'invalid_cardholder_data',
	);

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
		$this->data['transactionId'] = $jinput->get('oid');
		$this->data['logId']         = $jinput->getInt('logId');

		// Set the URLs
		$this->liveUrl = 'https://www.ipg-online.com/connect/gateway/processing';
		$this->testUrl = 'https://test.ipg-online.com/connect/gateway/processing';

		// Set who is calling
		$this->isCustomer = $jinput->get('customer', 1);
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
			'M'          => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MASTERCARD'),
			'V'          => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_VISA'),
			'A'          => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_AMEX'),
			'C'          => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_DINERSCLUB'),
			'J'          => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_JCB'),
			'ideal'      => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_IDEAL'),
			'klarna'     => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_KLARNA'),
			'MA'         => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MAESTRO'),
			'maestroUK'  => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MAESTROUK'),
			'masterpass' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MASTERPASS'),
			'paypal'     => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYPAL'),
			'sofort'     => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_SOFORT'),
		);
	}

	/**
	 * Return the live URL.
	 *
	 * @return  string  The live URL.
	 *
	 * @since   4.0
	 */
	public function getLiveUrl()
	{
		return $this->liveUrl;
	}

	/**
	 * Return the test URL.
	 *
	 * @return  string  The test URL.
	 *
	 * @since   4.0
	 */
	public function getTestUrl()
	{
		return $this->testUrl;
	}

	/**
	 * Prepare data for the form.
	 *
	 * @param   Gateway  $jdideal  An instance of JdidealGateway.
	 * @param   object   $data     An object with transaction information.
	 *
	 * @return  object  The data for the form.
	 *
	 * @since   2.13
	 *
	 * @throws  \RuntimeException
	 */
	public function getForm(Gateway $jdideal, $data)
	{
		// Get the store name
		$data->storeName = $jdideal->get('storeName');

		// Load the EMS class to get the values
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Ems/Ems.php';

		// Instantiate EMS
		/** @var \Ems $ems */
		$ems = new \Ems($data->storeName, $jdideal->get('sharedSecret'));

		// Get the amount
		$data->amount = number_format($data->amount, 2, '.', '');

		// Get the timezone
		$data->timezone = $jdideal->get('timezone', 'Europe/Amsterdam');

		// Get the date and time of transaction
		$data->transactionDateTime = \JHtml::_('date', $jdideal->get('date_added'), 'Y:m:d-H:i:s', $data->timezone);

		// Get the currency
		$data->currency = $jdideal->get('currency', 978);

		// Create the payment hash storename + txndatetime + chargetotal + currency + sharedsecret
		$data->hash = $ems->createHash($data->transactionDateTime, $data->amount, $data->currency);

		// Set the notify URL
		$root = $jdideal->getUrl();
		$data->notifyurl = $root . 'cli/notify.php';

		// Get the payment method, plugin overrides component
		$data->paymentMethod = $jdideal->get('payment');

		$jdideal->log(\JText::sprintf('COM_JDIDEAL_SELECTED_CARD', $data->paymentMethod), $data->logid);

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
		if (!array_key_exists('logId', $this->data))
		{
			throw new \RuntimeException(\JText::_('COM_JDIDEALGATEWAY_NO_LOGID_FOUND'));
		}

		// Get the transaction ID
		return $this->data['logId'];
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
	 */
	public function transactionStatus(Gateway $jdideal, $logId)
	{
		// Store the transaction reference
		if ($this->data['transactionId'])
		{
			$jdideal->setTrans($this->data['transactionId'], $logId);
		}

		// Log the received data
		foreach ($_POST as $name => $value)
		{
			$jdideal->log($name . ':' . $value, $logId);
		}

		$status = array();
		$status['isOK'] = true;

		// Get the transaction details
		$details = $jdideal->getDetails($logId);

		// Add the card and processed status to the log
		$brand = $this->jinput->getString('ccbrand', false);

		if ($brand)
		{
			$status['card'] = $brand;
		}

		// Get the customer info, not available
		$status['consumer'] = array();

		// Load the EMS class to get the values
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Ems/Ems.php';

		// Instantiate EMS
		/** @var \Ems $ems */
		$ems = new \Ems($jdideal->get('storeName'), $jdideal->get('sharedSecret'));

		// Create the seal
		$approvalCode = $this->jinput->getString('approval_code');
		$amount       = $this->jinput->getString('chargetotal');
		$currency     = $jdideal->get('currency', 978);
		$dateAdded    = \JHtml::_('date', $details->date_added, 'Y:m:d-H:i:s', $jdideal->get('timezone', 'Europe/Amsterdam'));

		$seal = $ems->validateHash($approvalCode, $amount, $currency, $dateAdded);

		// Check the seal
		if ($seal !== $this->jinput->get('notification_hash'))
		{
			$jdideal->log(\JText::_('COM_JDIDEAGATEWAY_SHA_VERIFY_FAILED'), $logId);
			$status['isOK'] = false;
			$status['error_message'] = \JText::_('COM_JDIDEAGATEWAY_SHA_VERIFY_FAILED');
			$status['suggestedAction'] = 'CANCELLED';

			return $status;
		}

		$jdideal->setTransactionDetails($brand, 1, $logId);

		// Check the payment status
		$approvalCode = $this->jinput->get('approval_code');

		switch ($approvalCode[0])
		{
			case 'Y':
				$status['suggestedAction'] = 'SUCCESS';
				break;
			case '?':
				$status['suggestedAction'] = 'OPEN';
				break;
			default:
				$status['suggestedAction'] = 'FAILURE';
				$status['error_message'] = $this->jinput->getString('fail_reason');
				break;
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
