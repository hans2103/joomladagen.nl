<?php
/**
 * @package     JDiDEAL
 * @subpackage  Advanced
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
 * iDEAL Advanced processor.
 *
 * @package     JDiDEAL
 * @subpackage  Advanced
 * @since       2.13
 */
class Advanced
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
		$this->data['transactionId'] = $jinput->get('trxid');
		$this->data['logId']         = $jinput->get('ec');
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
			'ideal' => 'iDEAL'
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
	 * @throws  \Exception
	 * @throws  \ErrorException
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 */
	public function getForm(Gateway $jdideal, $data)
	{
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Advanced/iDEALConnector.php';

		$iDEALConnector = new \iDEALConnector($jdideal);
		/** @var \DirectoryResponse $response */
		$response = $iDEALConnector->GetIssuerList($jdideal);
		$issuerList = array();

		if ($response->IsResponseError())
		{
			/** @var \ErrorResponse $response */
			$errorCode = $response->getErrorCode();
			$errorMsg = $response->getErrorMessage();
			$consumerMessage = $response->getConsumerMessage() ?: $response->getErrorMessage();

			$jdideal->log('Errorcode: ' . $errorCode, $data->logid);
			$jdideal->log('Error Message: ' . $errorMsg, $data->logid);

			throw new \ErrorException($consumerMessage);
		}
		else
		{
			// Get the countries
			/** @var \CountryEntry $countries */
			foreach ($response->getCountries() as $countryNames => $country)
			{
				$issuerList[$countryNames] = array();

				// Get the issuers
				/** @var \IssuerEntry $issuer */
				foreach ($country->getIssuers() as $issuer)
				{
					$issuerList[$countryNames]['items'][] = \JHtml::_('select.option', $issuer->getIssuerID(), $issuer->getIssuerName());
				}
			}
		}

		$output = array();
		$output['banks'] = $issuerList;

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
		if (!array_key_exists('logId', $this->data) || empty($this->data['logId']))
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
	 * Send payment to iDEAL Advanced.
	 *
	 * @param   Gateway  $jdideal  An instance of JdIdealGateway.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *
	 * @throws  \Exception
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 */
	public function sendPayment(Gateway $jdideal)
	{
		$app = \JFactory::getApplication();

		// Check if we have a selected bank
		$issuerID = $this->jinput->get('banks', false);

		// Load the stored data
		$logId = $this->jinput->get('logid');
		$details = $jdideal->getDetails($logId);

		if ($issuerID && is_object($details))
		{
			require_once JPATH_LIBRARIES . '/Jdideal/Psp/Advanced/iDEALConnector.php';
			require_once JPATH_LIBRARIES . '/Jdideal/Psp/Advanced/ConfigurationManager.php';
			$config = \ConfigurationManager::getInstance($jdideal);

			// Create Transaction Request
			$iDEALConnector = new \iDEALConnector($jdideal);

			// Set parameters for TransactionRequest
			$result = null;

			// Check if the order number meets the iDEAL requirement
			$pattern = '/[a-zA-Z0-9]+/';
			preg_match($pattern, $details->order_number, $matches);

			if ($matches[0] === $details->order_number)
			{
				$purchaseId = substr($details->order_number, 0, 16);
			}
			else
			{
				$purchaseId = substr($details->order_id, 0, 16);
			}

			// Clean out any invalid characters
			$pattern = '/[.]+/';
			$purchaseId = preg_replace($pattern, '', $purchaseId);

			$amount = str_ireplace(',', '.', $details->amount);

			// Replace some predefined values
			$find = array();
			$find[] = '{KLANTNR}';
			$find[] = '{ORDERNR}';
			$replace = array();
			$user = \JFactory::getUser();
			$replace[] = $user->id;
			$replace[] = $details->order_number;
			$description = substr(str_ireplace($find, $replace, $jdideal->get('IDEAL_DESCRIPTION')), 0, 32);
			$entranceCode = substr($details->id, 0, 40);
			$expirationPeriod = $config->GetConfiguration('EXPIRATIONPERIOD', false, $result);
			$merchantReturnUrl = $config->GetConfiguration('MERCHANTRETURNURL', false, $result);

			/** @var \AcquirerTransactionResponse $response */
			$response = $iDEALConnector->RequestTransaction(
				$issuerID,
				$purchaseId,
				$amount,
				$description,
				$entranceCode,
				$jdideal,
				$expirationPeriod,
				$merchantReturnUrl
			);

			if ($response->IsResponseError())
			{
				// Log the errors
				$jdideal->log(\JText::_('COM_JDIDEALGATEWAY_TRANSACTION_REQUEST_FAILED'), $logId);
				/** @var \ErrorResponse $response */
				$jdideal->log($response->getErrorCode(), $logId);
				$jdideal->log($response->getErrorMessage(), $logId);

				// Check for a consumer message
				$consumerMsg = $response->getConsumerMessage() ?: $response->getErrorMessage();

				// Transaction Request failed, inform the customer
				$url = $details->cancel_url ?: $details->return_url;

				if ($url)
				{
					$app->redirect($url . '&order_id=' . $details->order_id, \JText::sprintf('COM_JDIDEALGATEWAY_IDEAL_ERROR_MESSAGE', $consumerMsg), 'error');
				}
				else
				{
					throw new \RuntimeException(\JText::sprintf('COM_JDIDEALGATEWAY_IDEAL_ERROR_MESSAGE', $consumerMsg));
				}
			}
			else
			{
				$jdideal->setTrans($response->getTransactionID(), $logId);
				$issuerAuthenticationURL = $response->getIssuerAuthenticationURL();

				$jdideal->log('Send customer to URL: ' . $issuerAuthenticationURL, $logId);

				// Send the customer to the bank
				$app->redirect($issuerAuthenticationURL);
			}
		}
		else
		{
			// Find appropriate URL to redirect to
			$app = \JFactory::getApplication();
			$app->redirect('index.php', \JText::_('COM_JDIDEALGATEWAY_NO_VALID_BANK_CHOICE'), 'error');
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
	 * @throws  \Exception
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 */
	public function transactionStatus(Gateway $jdideal, $logId)
	{
		$transactionId = $this->getTransactionId();

		include JPATH_LIBRARIES . '/Jdideal/Psp/Advanced/iDEALConnector.php';
		$statusRequest = new \iDEALConnector($jdideal);
		/** @var \AcquirerStatusResponse $result */
		$result = $statusRequest->RequestTransactionStatus($transactionId, $jdideal);

		$status = array();
		$status['transactionID'] = $transactionId;

		if (!$result)
		{
			$jdideal->log(\JText::_('COM_JDIDEALGATEWAY_NO_VALID_RESPONSE'), $logId);
			$status['isOK'] = true;
			$status['isAuthenticated'] = false;
			$status['error_message'] = \JText::_('COM_JDIDEALGATEWAY_NO_VALID_RESPONSE');
			$status['suggestedAction'] = 'CANCELLED';
			$status['consumer'] = array();
		}
		else
		{
			if ($result->IsResponseError())
			{
				/** @var \ErrorResponse $result */
				$status['error_message'] = $result->getConsumerMessage();
				$jdideal->log($result->getErrorCode(), $logId);
				$jdideal->log($result->getErrorMessage(), $logId);
			}
			else
			{
				$status['isOK'] = true;
				$status['card'] = '';

				// Check the result
				switch ($result->getStatus())
				{
					case '1':
						$status['suggestedAction'] = 'SUCCESS';
						$status['error_message'] = '';
						break;
					case '2':
						$status['suggestedAction'] = 'CANCELLED';
						$status['error_message'] = \JText::_('COM_JDIDEALGATEWAY_PAYMENT_CANCELLED');
						break;
					case '3':
						$status['suggestedAction'] = 'OPEN';
						$status['error_message'] = \JText::_('COM_JDIDEALGATEWAY_PAYMENT_EXPIRED');
						break;
					case '4':
						$status['suggestedAction'] = 'FAILURE';
						$status['error_message'] = \JText::_('COM_JDIDEALGATEWAY_PAYMENT_FAILURE');
						break;
					case '5':
						$status['suggestedAction'] = 'OPEN';
						$status['error_message'] = \JText::_('COM_JDIDEALGATEWAY_PAYMENT_OPEN');
						break;
				}

				// Get the customer details
				$status['consumer']['consumerAccount'] = '';
				$status['consumer']['consumerIban'] = $result->getConsumerIBAN();
				$status['consumer']['consumerBic'] = $result->getConsumerBIC();
				$status['consumer']['consumerName'] = $result->getConsumerName();
				$status['consumer']['consumerCity'] = '';

				$jdideal->setTransactionDetails('iDEAL', 1, $logId);
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
		return true;
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
	 * @throws \Exception
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 */
	public function getCheckoutOptions(Gateway $jdideal, $paymentMethod)
	{
		require_once JPATH_LIBRARIES . '/Jdideal/Psp/Advanced/iDEALConnector.php';

		$iDEALConnector = new \iDEALConnector($jdideal);
		/** @var \DirectoryResponse $response */
		$response = $iDEALConnector->GetIssuerList($jdideal);

		$banks = null;

		// Get the countries
		/** @var \CountryEntry $countries */
		foreach ($response->getCountries() as $countryNames => $country)
		{
			$banks[$countryNames] = array();

			// Get the issuers
			/** @var \IssuerEntry $issuer */
			foreach ($country->getIssuers() as $issuer)
			{
				$banks[$countryNames]['items'][] = \JHtml::_('select.option', $issuer->getIssuerID(), $issuer->getIssuerName());
			}
		}

		return $banks;
	}
}
