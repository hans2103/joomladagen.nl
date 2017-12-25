<?php
/**
 * @package     JDiDEAL
 * @subpackage  Omnikassa
 *
 * @author      Roland Dalmulder <contact@jdideal.nl>
 * @copyright   Copyright (C) 2017 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://jdideal.nl
 */

namespace Jdideal\Psp;

use Jdideal\Gateway;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\endpoint\Endpoint;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\Money;
use	nl\rabobank\gict\payments_savings\omnikassa_sdk\model\PaymentBrandForce;
use	nl\rabobank\gict\payments_savings\omnikassa_sdk\model\request\MerchantOrder;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\response\PaymentCompletedResponse;
use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\signing\SigningKey;
use	nl\rabobank\gict\payments_savings\omnikassa_sdk\connector\TokenProvider;

// Load the OnlineKassa class
require_once JPATH_LIBRARIES . '/Jdideal/Psp/Onlinekassa/vendor/autoload.php';

defined('_JEXEC') or die;

/**
 * Omnikassa processor.
 *
 * @package     JDiDEAL
 * @subpackage  Onlinekassa
 * @link        https://github.com/opensdks/omnikassa2-sdk
 * @since       4.8.0
 */
class Onlinekassa
{
	/**
	 * Database driver
	 *
	 * @var    \JDatabaseDriver
	 * @since  4.8.0
	 */
	private $db;

	/**
	 * Input processor
	 *
	 * @var    \JInput
	 * @since  4.8.0
	 */
	private $jinput;

	/**
	 * The live URL to send the requests to
	 *
	 * @var    string
	 * @since  4.8.0
	 */
	private $liveUrl;

	/**
	 * The test URL
	 *
	 * @var    string
	 * @since  4.8.0
	 */
	private $testUrl;

	/**
	 * Array with return data from the Rabobank
	 *
	 * @var    array
	 * @since  4.8.0
	 */
	private $data = array();

	/**
	 * Set if the customer or PSP is calling
	 *
	 * @var    bool
	 * @since  4.8.0
	 */
	private $isCustomer = false;

	/**
	 * Contruct the payment reference.
	 *
	 * @param   \Jinput  $jinput  The input object.
	 *
	 * @since   4.8.0
	 */
	public function __construct(\JInput $jinput)
	{
		// Set the input
		$this->jinput = $jinput;

		// Set the database
		$this->db = \JFactory::getDbo();

		// Set the URLs
		$this->liveUrl = 'https://betalen.rabobank.nl/omnikassa-api/';
		$this->testUrl = 'https://betalen.rabobank.nl/omnikassa-api-sandbox/';

		// Put the return data in an array, data is constructed as name=value
		$this->data['transaction_id'] = $jinput->get('transaction_id');

		// Set who is calling
		$this->isCustomer = true;
	}

	/**
	 * Returns a list of available payment methods.
	 *
	 * @return  array  List of available payment methods.
	 *
	 * @since   4.8.0
	 */
	public function getAvailablePaymentMethods()
	{
		return array(
			'IDEAL'      => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_IDEAL'),
			'VISA'       => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_VISA'),
			'MASTERCARD' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MASTERCARD'),
			'MAESTRO'    => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MAESTRO'),
			'BANCONTACT' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANCONTACT'),
			'VPAY'       => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_VPAY'),
			'PAYPAL'     => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYPAL'),
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
	 * @since   4.8.0
	 *
	 * @throws  \RuntimeException
	 */
	public function getForm(Gateway $jdideal, $data)
	{
		// Load the form options
		$options = array();

		// Get the payment method, plugin overrides component
		if (isset($data->payment_method) && $data->payment_method)
		{
			$selected   = array();
			$selected[] = strtolower($data->payment_method);
		}
		else
		{
			$selected = $jdideal->get('paymentMeanBrandList', array('ideal'));

			// If there is no choice made, set the value empty
			if ($selected[0] === 'all')
			{
				$selected[0] = '';
			}
		}

		// Process the selected payment methods
		foreach ($selected as $name)
		{
			switch (strtolower($name))
			{
				case 'ideal':
					$options[] = \JHtml::_('select.option', 'ideal', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_IDEAL'));
					break;
				case 'paypal':
					$options[] = \JHtml::_('select.option', 'paypal', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_PAYPAL'));
					break;
				case 'mastercard':
					$options[] = \JHtml::_('select.option', 'mastercard', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MASTERCARD'));
					break;
				case 'visa':
					$options[] = \JHtml::_('select.option', 'visa', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_VISA'));
					break;
				case 'bancontact':
					$options[] = \JHtml::_('select.option', 'bancontact', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANCONTACT'));
					break;
				case 'maestro':
					$options[] = \JHtml::_('select.option', 'maestro', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MAESTRO'));
					break;
				case 'v_pay':
					$options[] = \JHtml::_('select.option', 'v_pay', \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_VPAY'));
					break;
			}
		}

		$output             = array();
		$output['payments'] = $options;
		$output['redirect'] = $jdideal->get('redirect', 'wait');

		$jdideal->log(\JText::sprintf('COM_JDIDEAL_SELECTED_CARD', $selected[0]), $data->logid);

		return $output;
	}

	/**
	 * Get the log ID.
	 *
	 * @return  int  The ID of the log.
	 *
	 * @since   4.8.0
	 *
	 * @throws  \RuntimeException
	 */
	public function getLogId()
	{
		$logId = 0;

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
	 * @since   4.8.0
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
	 * Send payment to Rabobank.
	 *
	 * @param   Gateway  $jdideal  An instance of \Jdideal\Gateway.
	 *
	 * @return  void.
	 *
	 * @since   4.8.0
	 */
	public function sendPayment(Gateway $jdideal)
	{
		$app   = \JFactory::getApplication();
		$logId = $this->jinput->get('logid', 0, 'int');

		// Load the stored data
		$details = $jdideal->getDetails($logId);

		if (!is_object($details))
		{
			throw new \RuntimeException(\JText::sprintf('COM_JDIDEALGATEWAY_NO_TRANSACTION_DETAILS', 'Onlinekassa', $logId));
		}

		$trans = time();
		$jdideal->setTrans($trans, $logId);
		$notify_url = \JUri::root() . 'cli/notify.php?transaction_id=' . $trans;

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
			$description = substr(str_ireplace($find, $replace, $jdideal->get('description')), 0, 100);

			// Get the amount
			$amount = Money::fromDecimal($jdideal->get('currency'), $details->amount);

			// Get the language
			$language = $jdideal->get('customerLanguage');

			// Get the chosen payment method
			$paymentMethod = strtoupper($this->jinput->get('payment'));

			// Store the chosen payment method
			$jdideal->setTransactionDetails($paymentMethod, 0, $logId);

			// Set if payment method should be forced
			$paymentBrandForce = PaymentBrandForce::FORCE_ONCE;

			/**
			 * If we have no payment brand, we must set the force to null otherwise you get the error
			 * Client error response [url] https://betalen.rabobank.nl/omnikassa-api-sandbox/order/server/api/order [status code] 422 [reason phrase] Undefined
			 *
			 * This cannot be tested with other payment methods than iDEAL because sandbox only has iDEAL
			 */
			if (empty($paymentMethod))
			{
				$paymentBrandForce = null;
			}

			$order = new MerchantOrder(
				$logId,
				$description,
				null,
				$amount,
				null,
				$language,
				$notify_url,
				$paymentMethod,
				$paymentBrandForce
			);

			// Prepare for sending
			$signingKey            = new SigningKey(base64_decode($jdideal->get('signingKey')));
			$inMemoryTokenProvider = new InMemoryTokenProvider($jdideal->get('apiKey'));
			$endpoint              = Endpoint::createInstance($this->getUrl($jdideal), $signingKey, $inMemoryTokenProvider);
			$redirectUrl           = $endpoint->announceMerchantOrder($order);

			// Add some info to the log
			$jdideal->log('Send customer to URL: ' . $redirectUrl, $logId);

			// Send the customer to the bank
			$app->redirect($redirectUrl);
		}
		catch (\Exception $e)
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
	 * @since   4.8.0
	 *
	 * @throws  \RuntimeException
	 */
	public function transactionStatus(Gateway $jdideal, $logId)
	{
		// Log the received data
		$orderId       = $this->jinput->get('order_id');
		$paymentStatus = $this->jinput->get('status');
		$signature     = $this->jinput->get('signature');

		// Log the received data
		$jdideal->log('Order ID:' . $orderId, $logId);
		$jdideal->log('Status:' . $paymentStatus, $logId);
		$jdideal->log('Signature:' . $signature, $logId);

		// Get the status information
		$status         = array();
		$status['isOK'] = true;
		$status['card'] = '';

		// Get the customer info, not available
		$status['consumer'] = array();

		// Store the payment ID, needed for retrieving order status at a later time
		$jdideal->setPaymentId('transaction_id=' . $this->getTransactionId() . '&order_id=' . $orderId . '&status=' . $paymentStatus . '&signature=' . $signature, $logId);

		// Verify the payment
		$signingKey               = new SigningKey(base64_decode($jdideal->get('signingKey')));
		$paymentCompletedResponse = PaymentCompletedResponse::createInstance($orderId, $paymentStatus, $signature, $signingKey);

		// Check if payment completed
		if (!$paymentCompletedResponse)
		{
			throw new \RuntimeException('The payment completed response was invalid.');
		}

		// Get the payment result
		$validatedStatus = $paymentCompletedResponse->getStatus();

		// Store the details
		$details = $jdideal->getDetails($logId);
		$jdideal->setTransactionDetails($details->card, 1, $logId);

		switch ($validatedStatus)
		{
			case 'COMPLETED':
				$status['suggestedAction'] = 'SUCCESS';
				break;
			case 'CANCELLED':
				$status['suggestedAction'] = 'CANCELLED';
				break;
			case 'IN_PROGRESS':
			default:
				$status['suggestedAction'] = 'OPEN';
				break;
		}

		return $status;
	}

	/**
	 * Get the URL to send the request to.
	 *
	 * @param   Gateway  $jdideal  An instance of JdidealGateway.
	 *
	 * @return  string  The URL to call.
	 *
	 * @since   4.8.0
	 */
	private function getUrl(Gateway $jdideal)
	{
		if ($jdideal->get('testmode', 1))
		{
			return $this->testUrl;
		}
		else
		{
			return $this->liveUrl;
		}
	}

	/**
	 * Check who is knocking at the door.
	 *
	 * @return  bool  True if it is the customer | False if it is the PSP.
	 *
	 * @since   4.8.0
	 */
	public function isCustomer()
	{
		return $this->isCustomer;
	}
}

/**
 * In memory token provider.
 *
 * @package  JDiDEAL
 * @since    4.8.0
 */
class InMemoryTokenProvider extends TokenProvider
{
	private $map = array();

	/**
	 * Construct the in memory token provider with the given refresh token.
	 *
	 * @param   string  $refreshToken  The refresh token used to retrieve the
	 *                                 access tokens with.
	 *
	 * @since   4.8.0
	 */
	public function __construct($refreshToken)
	{
		$this->setValue('REFRESH_TOKEN', $refreshToken);
	}

	/**
	 * Retrieve the value for the given key.
	 *
	 * @param   string  $key  The key to get the value for
	 *
	 * @return string Value of the given key or null if it does not exists.
	 *
	 * @since   4.8.0
	 */
	protected function getValue($key)
	{
		return array_key_exists($key, $this->map) ? $this->map[$key] :
			null;
	}

	/**
	 * Store the value by the given key.
	 *
	 * @param   string  $key    They key to store
	 * @param   string  $value  The value to store
	 *
	 * @return  void
	 *
	 * @since   4.8.0
	 */
	protected function setValue($key, $value)
	{
		$this->map[$key] = $value;
	}

	/**
	 * Optional functionality to flush your systems.
	 * It is called after storing all the values of the access token and
	 * can be used for example to clean caches or reload changes from the
	 * database.
	 *
	 * @return  void
	 *
	 * @since   4.8.0
	 */
	protected function flush()
	{
	}
}
