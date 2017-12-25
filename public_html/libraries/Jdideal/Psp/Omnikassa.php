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
 * Omnikassa processor.
 *
 * @package     JDiDEAL
 * @subpackage  Omnikassa
 * @since       2.12
 */
class Omnikassa
{
	/**
	 * Database driver
	 *
	 * @var    \JDatabaseDriver
	 * @since  4.0
	 */
	private $db;

	/**
	 * The returned data string
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $returnData;

	/**
	 * Array with return data from the Rabobank
	 *
	 * @var    array
	 * @since  4.0
	 */
	private $data = array();

	/**
	 * The signing seal
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $seal;

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
	 * Contruct the payment reference.
	 *
	 * @param   \Jinput  $jinput  The input object.
	 *
	 * @since   4.0
	 */
	public function __construct(\JInput $jinput)
	{
		// Put the return data in an array, data is constructed as name=value
		$this->returnData = $jinput->post->get('Data', '', 'raw');

		if ($this->returnData)
		{
			$dataArray = explode('|', $this->returnData);

			foreach ($dataArray as $pair)
			{
				list($name, $value) = explode('=', $pair);

				$this->data[$name] = $value;
			}
		}

		// Set the seal
		$this->seal = $jinput->post->get('Seal', false);

		// Set the database
		$this->db = \JFactory::getDbo();

		// Set the URLs
		$this->liveUrl = 'https://payment-webinit.omnikassa.rabobank.nl/paymentServlet';
		$this->testUrl = 'https://payment-webinit.simu.omnikassa.rabobank.nl/paymentServlet';

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
			'IDEAL'      => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_IDEAL'),
			'VISA'       => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_VISA'),
			'MASTERCARD' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MASTERCARD'),
			'MAESTRO'    => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MAESTRO'),
			'BCMC'       => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_BANCONTACT'),
			'VPAY'       => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_VPAY'),
			'MINITIX'    => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_MINITIX'),
			'INCASSO'    => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_INCASSO'),
			'ACCEPTGIRO' => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_ACCEPTGIRO'),
			'REMBOURS'   => \JText::_('COM_JDIDEALGATWAY_PAYMENT_METHOD_CASHONDELIVERY'),
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
		// Store the transaction reference
		$data->trans = time() . 'R' . $data->logid;
		$jdideal->setTrans($data->trans, $data->logid);

		// Get the payment method, plugin overrides component
		if (isset($data->payment_method) && $data->payment_method)
		{
			$data->pmlist = strtoupper($data->payment_method);
		}
		else
		{
			$payment = $jdideal->get('paymentMeanBrandList');
			$data->pmlist = '';

			if (is_array($payment))
			{
				$data->pmlist = implode(',', $payment);
			}

			// Check if all is selected
			if ($data->pmlist === 'all')
			{
				$data->pmlist = '';
			}
		}

		$jdideal->log(\JText::sprintf('COM_JDIDEAL_SELECTED_CARD', $data->pmlist), $data->logid);

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
		// Convert the strings to a real array
		$logId = false;

		if (array_key_exists('transactionReference', $this->data))
		{
			$items = explode('R', $this->data['transactionReference']);

			if (array_key_exists('1', $items))
			{
				$logId = $items[1];
			}
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
		if (!array_key_exists('transactionReference', $this->data))
		{
			throw new \RuntimeException(\JText::_('COM_JDIDEALGATEWAY_NO_TRANSACTIONID_FOUND'));
		}

		// Get the transaction ID
		return $this->data['transactionReference'];
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
		// Log the received data
		foreach ($this->data as $name => $value)
		{
			// Add the details to the log
			if ($name === 'responseCode')
			{
				$jdideal->log($name . ':' . \JText::_('COM_JDIDEALGATEWAY_RABO_OMNIKASSA_RESULT_' . $value), $logId);
			}

			$jdideal->log($name . ':' . $value, $logId);
		}

		// Add the card and processed status to the log
		$brand = '';

		if (array_key_exists('paymentMeanBrand', $this->data))
		{
			$brand = $this->data['paymentMeanBrand'];
		}

		$status = array();
		$status['isOK'] = true;
		$status['card'] = $brand;

		// Get the customer info, not available
		$status['consumer'] = array();

		// Create the seal
		$seal = hash('sha256', utf8_encode($this->returnData . $jdideal->get('password')));

		// Check the seal
		if ($seal !== $this->seal)
		{
			$jdideal->log(\JText::_('COM_JDIDEAGATEWAY_SHA_VERIFY_FAILED'), $logId);
			$status['isOK'] = false;
			$status['error_message'] = \JText::_('COM_JDIDEAGATEWAY_SHA_VERIFY_FAILED');
			$status['suggestedAction'] = 'CANCELLED';

			return $status;
		}

		$jdideal->setTransactionDetails($brand, 1, $logId);

		switch ($brand)
		{
			case 'IDEAL':
			case 'VISA':
			case 'MASTERCARD':
			case 'MAESTRO':
			case 'MINITIX':
			case 'VPAY':
			case 'BCMC':
				switch ($this->data['responseCode'])
				{
					case '00':
						$status['suggestedAction'] = 'SUCCESS';
						break;
					case '17':
						$status['suggestedAction'] = 'CANCELLED';
						$status['error_message'] = \JText::_('COM_JDIDEALGATEWAY_RABO_OMNIKASSA_RESULT_17');
						break;
					case '60':
						$status['suggestedAction'] = 'OPEN';
						break;
					default:
						$status['suggestedAction'] = 'FAILURE';
						$status['error_message'] = \JText::_('COM_JDIDEALGATEWAY_RABO_OMNIKASSA_RESULT_' . $this->data['responseCode']);
						break;
				}
				break;
			case 'INCASSO':
			case 'ACCEPTGIRO':
			case 'REMBOURS':
				switch ($this->data['responseCode'])
				{
					case '00':
						$status['suggestedAction'] = 'SUCCESS';
						break;
					case '17':
						$status['suggestedAction'] = 'CANCELLED';
						$status['error_message'] = \JText::_('COM_JDIDEALGATEWAY_RABO_OMNIKASSA_RESULT_17');
						break;
					case '60':
						$status['suggestedAction'] = 'OPEN';
						break;
					default:
						$status['suggestedAction'] = 'TRANSFER';
						break;
				}
				break;
			default:
				$status['suggestedAction'] = 'OPEN';
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
