<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

/**
 * Kassa Compleet processing class.
 *
 * @package  JDiDEAL
 * @since    4.0
 */
class Kassacompleet
{
	/**
	 * API URL
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $apiUrl = 'https://api.kassacompleet.nl/v1';

	/**
	 * API key
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $apiKey;

	/**
	 * The response received from ING.
	 *
	 * @var    JHttpResponse
	 * @since  4.0
	 */
	private $response;

	/**
	 * Set the API key.
	 *
	 * @param   string  $apiKey  The API key
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 */
	public function setApiKey($apiKey)
	{
		$this->apiKey = $apiKey;
	}

	/**
	 * Get a list of issuers.
	 *
	 * @return  array  List of available issuers.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function getBanks()
	{
		try
		{
			// Get the list of banks
			$this->send('ideal/issuers', 'get');

			// Process the list
			$answer = json_decode($this->response->body);

			// Check if we have an error
			if (isset($answer->error))
			{
				throw new RuntimeException($answer->error->value);
			}

			// Reorganize the banks
			$banks = array();

			foreach ($answer as $index => $item)
			{
				$banks[$item->list_type]['items'][$item->id] = $item->name;
			}

			// Return the banks
			return $banks;
		}
		catch (Exception $e)
		{
			throw new RuntimeException($e->getMessage());
		}
	}

	/**
	 * Create a payment request.
	 *
	 * @param   stdClass  $data  The payment data.
	 *
	 * @return  object  The response details.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function create($data)
	{
		// Send the request to ING
		$this->send('orders', 'post', $data);

		// Decode the response
		$response = json_decode($this->response->body);

		// Check the status
		if ($response->status === 'error')
		{
			throw new RuntimeException($response->transactions[0]->reason);
		}

		// Get the payment URL
		return $response->transactions[0];
	}

	/**
	 * Retrieve the URL to send the customer to to complete the payment.
	 *
	 * @return  string  The URL to send the customer to.
	 *
	 * @since   4.0
	 */
	public function getPaymentUrl()
	{
		// Decode the response
		$response = json_decode($this->response->body);

		$paymentUrl = '';

		if (array_key_exists(0, $response->transactions))
		{
			$paymentUrl = $response->transactions[0]->payment_url;
		}

		return $paymentUrl;
	}

	/**
	 * Get the order status.
	 *
	 * An empty transaction ID will retrieve all orders.
	 *
	 * @param   string  $transactionId  The transaction ID of the payment.
	 *
	 * @return  object  List of order details.
	 *
	 * @since   4.0
	 *
	 * @throws  RuntimeException
	 */
	public function orderStatus($transactionId)
	{
		$this->send('orders/' . $transactionId, 'get');

		// Decode the response
		$response = json_decode($this->response->body);

		// Check the status
		if ($response->status === 'error')
		{
			$reason = JText::_('COM_JDIDEALGATEWAY_KASSACOMPLEET_STATUS_ERROR');

			if (isset($response->transactions[0]->reason))
			{
				$reason = $response->transactions[0]->reason;
			}

			throw new RuntimeException($reason);
		}

		// Get the payment URL
		return $response->transactions[0];
	}

	/**
	 * Get a response for an ING request.
	 *
	 * @param   string    $endpoint  The endpoint to call.
	 * @param   string    $method    The method to send the data. Options for example GET, POST
	 * @param   stdClass  $keyValue  The parameters to send.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function send($endpoint, $method, stdClass $keyValue = null)
	{
		// Encode the API key
		$password = base64_encode($this->apiKey . ':');

		// Build the payload
		$payload = array();
		$headers = array();

		if ($method === 'get')
		{
			$payload['Authorization'] = 'Basic ' . $password;
		}
		else
		{
			// JSON encode the payload
			$payload = json_encode($keyValue);

			// Build the header if needed
			$headers['Authorization'] = 'Basic ' . $password;
			$headers['Content-Type'] = 'application/json';
		}

		// Get the transporter
		$http = JHttpFactory::getHttp(null, array('curl', 'stream'));

		// Send request
		$this->response = $http->$method($this->apiUrl . '/' . $endpoint . '/', $payload, $headers);
	}
}
