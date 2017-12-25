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
 * Sisow processing class.
 *
 * @package  JDiDEAL
 * @since    4.0
 */
class Sisow
{
	/**
	 * Array of issuer banks.
	 *
	 * @var    array
	 * @since  4.0
	 */
	protected static $issuers;

	/**
	 * Timestamp when the issuer list was last checked.
	 *
	 * @var    int
	 * @since  4.0
	 */
	protected static $lastcheck;

	/**
	 * The XML response received from Sisow.
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $response;

	/**
	 * The merchant ID.
	 *
	 * @var    int
	 * @since  4.0
	 */
	private $merchantId;

	/**
	 * The merchant key.
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $merchantKey;

	/**
	 * The unique shop ID.
	 *
	 * @var    int
	 * @since  4.0
	 */
	private $shopId;

	/**
	 * The payment method chosen by the customer.
	 *
	 * Can be left empty for iDEAL.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $payment;

	/**
	 * The bank code used for specifying the iDEAL bank.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $issuerId;

	/**
	 * Unique 16 alphanumeric value.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $purchaseId;

	/**
	 * A unique maximum 40 alphanumeric value.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $entranceCode;

	/**
	 * A description of maximum 32 alphanumeric value.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $description;

	/**
	 * The amount to be paid. Minimum value is 0.45.
	 *
	 * @var    float
	 * @since  4.0
	 */
	public $amount;

	/**
	 * Set the test mode
	 *
	 * @var    bool
	 * @since  4.0
	 */
	public $testmode;

	/**
	 * The notification URL.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $notifyUrl;

	/**
	 * The return URL.
	 *
	 * This field is mandatory.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $returnUrl;

	/**
	 * The cancellation URL.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $cancelUrl;

	/**
	 * The callback URL.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $callbackUrl;

	/**
	 * The status of the payment.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $status;

	/**
	 * The timestamp of the payment.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $timeStamp;

	/**
	 * The customer account number.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $consumerAccount;

	/**
	 * The customer name.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $consumerName;

	/**
	 * The customer city.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $consumerCity;

	/**
	 * The invoice number
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $invoiceNo;

	/**
	 * The document ID.
	 *
	 * @var    int
	 * @since  4.0
	 */
	public $documentId;

	/**
	 * The document URL.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $documentUrl;

	/**
	 * The transaction ID
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $trxId;

	/**
	 * The URL to the bank to pay with iDEAL.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $issuerUrl;

	/**
	 * The received SHA1 value to validate against.
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $sha1;

	/**
	 * The error code
	 *
	 * @var    int
	 * @since  4.0
	 */
	public $errorCode;

	/**
	 * The error message
	 *
	 * @var    string
	 * @since  4.0
	 */
	public $errorMessage;

	/**
	 * The constructor.
	 *
	 * @param   int     $merchantid   The merchant ID.
	 * @param   string  $merchantkey  The merchant key.
	 * @param   int     $shopid       The shop ID.
	 *
	 * @since   4.0
	 */
	public function __construct($merchantid, $merchantkey, $shopid = 0)
	{
		$this->merchantId = $merchantid;
		$this->merchantKey = $merchantkey;
		$this->shopId = $shopid;
	}

	/**
	 * Get the error code and message.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 */
	private function error()
	{
		$this->errorCode = $this->parse('errorcode');
		$this->errorMessage = urldecode($this->parse('errormessage'));
	}

	/**
	 * Parse the answer received form Sisow.
	 *
	 * @param   string  $search  The tag to search for.
	 * @param   string  $xml     The XML string.
	 *
	 * @return  string  The received answer.
	 *
	 * @since   4.0
	 */
	private function parse($search, $xml = '')
	{
		if ($xml === '')
		{
			$xml = $this->response;
		}

		if (($start = strpos($xml, '<' . $search . '>')) === false)
		{
			return false;
		}

		$start += strlen($search) + 2;

		if (($end = strpos($xml, '</' . $search . '>', $start)) === false)
		{
			return false;
		}

		return substr($xml, $start, $end - $start);
	}

	/**
	 * Get a response for a Sisow request.
	 *
	 * @param   string  $method    The method to call.
	 * @param   array   $keyvalue  The parameters to send.
	 *
	 * @return  string  The response received.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function send($method, array $keyvalue = array())
	{
		// Get the transporter
		$http = JHttpFactory::getHttp(null, array('curl', 'stream'));

		// Send request
		$url = 'https://www.sisow.nl/Sisow/iDeal/RestHandler.ashx/' . $method;
		$strResponse = $http->post($url, $keyvalue);

		$this->response = $strResponse->body;

		if (!$this->response)
		{
			return false;
		}

		return true;
	}

	/**
	 * Get a list of banks available for iDEAL.
	 *
	 * @return  int  A code representing state.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	private function getDirectory()
	{
		$diff = 24 * 60 * 60;

		if (self::$lastcheck)
		{
			$diff = time() - self::$lastcheck;
		}

		if ($diff < 24 * 60 * 60)
		{
			return 0;
		}

		if (!$this->send('DirectoryRequest'))
		{
			return -1;
		}

		$search = $this->parse('directory');

		if (!$search)
		{
			$this->error();

			return -2;
		}

		self::$issuers = array();
		$iss = explode('<issuer>', str_replace('</issuer>', '', $search));

		foreach ($iss as $k => $v)
		{
			$issuerid = $this->parse('issuerid', $v);
			$issuername = $this->parse('issuername', $v);

			if ($issuerid && $issuername)
			{
				self::$issuers[$issuerid] = $issuername;
			}
		}

		// Update the last time we checked
		self::$lastcheck = time();

		return 0;
	}

	/**
	 * Retrieve a list of iDEAL banks.
	 *
	 *  0 : If all is OK
	 * -x : If directory request returns a negative value
	 *
	 * @param   bool  $test  Set if the test mode is used.
	 *
	 * @return  array  The list of banks.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 */
	public function DirectoryRequest($test = false)
	{
		$banks = array();

		if ($test === true)
		{
			$banks['Nederland']['items'][] = \JHtml::_('select.option', '99', 'Sisow Bank (test)');

			return $banks;
		}

		$ex = $this->getDirectory();

		if ($ex < 0)
		{
			return array();
		}

		foreach (self::$issuers as $k => $v)
		{
			$banks['Nederland']['items'][] = \JHtml::_('select.option', $k, $v);
		}

		return $banks;
	}

	/**
	 * Send a transaction request to Sisow.
	 *
	 * @param   array  $keyvalue  An array of values to send to Sisow.
	 *
	 *  -1 : No Merchant ID
	 *  -2 : No Merchant Key
	 *  -3 : No Shop ID
	 *  -4 : No Purchase ID
	 *  -5 : Amount lower than 45 cents
	 *  -6 : No Description
	 *  -7 : No Return URL
	 *  -8 : No issuer ID or payment method
	 *  -9 : Transaction request failed
	 * -10 : No issuer URL
	 *
	 * @return  int  The status value.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function TransactionRequest(array $keyvalue = array())
	{
		$this->trxId = $this->issuerUrl = '';

		if (!$this->merchantId)
		{
			$this->errorMessage = 'No Merchant ID';

			return -1;
		}

		if (!$this->merchantKey)
		{
			$this->errorMessage = 'No Merchant Key';

			return -2;
		}

		if (null === $this->shopId)
		{
			$this->errorMessage = 'No Shop ID';

			return -3;
		}

		if (!$this->purchaseId)
		{
			$this->errorMessage = 'No purchaseid';

			return -4;
		}

		if ($this->amount < 0.45)
		{
			$this->errorMessage = 'Amount < 0.45';

			return -5;
		}

		if (!$this->description)
		{
			$this->errorMessage = 'No description';

			return -6;
		}

		if (!$this->returnUrl)
		{
			$this->errorMessage = 'No returnurl';

			return -7;
		}

		if (!$this->issuerId && !$this->payment)
		{
			$this->errorMessage = 'No issuerid or no payment method';

			return -8;
		}

		if (!$this->entranceCode)
		{
			$this->entranceCode = $this->purchaseId;
		}

		$pars = array();
		$pars['shopid'] = $this->shopId;
		$pars['merchantid'] = $this->merchantId;
		$pars['payment'] = $this->payment;
		$pars['issuerid'] = $this->issuerId;
		$pars['purchaseid'] = $this->purchaseId;
		$pars['amount'] = round($this->amount * 100);
		$pars['description'] = $this->description;
		$pars['entrancecode'] = $this->entranceCode;
		$pars['returnurl'] = $this->returnUrl;
		$pars['cancelurl'] = $this->cancelUrl;
		$pars['callbackurl'] = $this->callbackUrl;
		$pars['notifyurl'] = $this->notifyUrl;
		$pars['sha1'] = sha1($this->purchaseId . $this->entranceCode . round($this->amount * 100) . $this->shopId . $this->merchantId . $this->merchantKey);

		if ($keyvalue)
		{
			foreach ($keyvalue as $k => $v)
			{
				$pars[$k] = $v;
			}
		}

		if (!$this->send('TransactionRequest', $pars))
		{
			return -9;
		}

		$this->trxId = $this->parse('trxid');
		$this->issuerUrl = urldecode($this->parse('issuerurl'));
		$this->invoiceNo = $this->parse('invoiceno');
		$this->documentId = $this->parse('documentid');
		$this->documentUrl = $this->parse('documenturl');

		if (!$this->issuerUrl)
		{
			$this->error();

			return -10;
		}

		return 0;
	}

	/**
	 * Check the payment status.
	 *
	 *  0 : All is good
	 * -1 : Merchant ID is missing
	 * -2 : Merchant Key is missing
	 * -3 : Shop ID is missing
	 * -4 : Transaction ID is missing
	 * -5 : Status request failed
	 * -6 : No status received
	 *
	 * @param   string  $trxid  The transaction ID.
	 *
	 * @return  int  The status of the request.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function StatusRequest($trxid = '')
	{
		if ($trxid === '')
		{
			$trxid = $this->trxId;
		}

		if (!$this->merchantId)
		{
			return -1;
		}

		if (!$this->merchantKey)
		{
			return -2;
		}

		if (null === $this->shopId)
		{
			return -3;
		}

		if (!$trxid)
		{
			return -4;
		}

		$this->trxId = $trxid;
		$pars = array();
		$pars['merchantid'] = $this->merchantId;
		$pars['shopid'] = $this->shopId;
		$pars['trxid'] = $this->trxId;
		$pars['sha1'] = sha1($this->trxId . $this->shopId . $this->merchantId . $this->merchantKey);

		// Validate the status request with Sisow
		if (!$this->send('StatusRequest', $pars))
		{
			return -5;
		}

		$this->status = $this->parse('status');

		if (!$this->status)
		{
			$this->error();

			return -6;
		}

		// Get the values from the request
		$this->timeStamp = $this->parse('timestamp');
		$this->amount = $this->parse('amount') / 100.0;
		$this->consumerAccount = $this->parse('consumeraccount');
		$this->consumerName = $this->parse('consumername');
		$this->consumerCity = $this->parse('consumercity');
		$this->purchaseId = $this->parse('purchaseid');
		$this->description = $this->parse('description');
		$this->entranceCode = $this->parse('entrancecode');
		$this->sha1 = $this->parse('sha1');

		return 0;
	}

	/**
	 * Refund request.
	 *
	 * @param   string  $trxid  The transaction ID.
	 *
	 * @return  int  Document ID.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function RefundRequest($trxid)
	{
		$pars = array();
		$pars['merchantid'] = $this->merchantId;
		$pars['shopid'] = $this->shopId;
		$pars['trxid'] = $trxid;
		$pars['sha1'] = sha1($trxid . $this->shopId . $this->merchantId . $this->merchantKey);

		if (!$this->send('RefundRequest', $pars))
		{
			return -1;
		}

		$this->documentId = $this->parse('refundid');

		if (!$this->documentId)
		{
			$this->error();

			return -2;
		}

		return $this->documentId;
	}

	/**
	 * Invoice request.
	 *
	 * @param   string  $trxid     Transaction ID.
	 * @param   array   $keyvalue  An array of values to send to Sisow.
	 *
	 * @return  int  Status ID.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function InvoiceRequest($trxid, array $keyvalue = array())
	{
		$pars = array();
		$pars['merchantid'] = $this->merchantId;
		$pars['shopid'] = $this->shopId;
		$pars['trxid'] = $trxid;
		$pars['sha1'] = sha1($trxid . $this->shopId . $this->merchantId . $this->merchantKey);

		if ($keyvalue)
		{
			foreach ($keyvalue as $k => $v)
			{
				$pars[$k] = $v;
			}
		}

		if (!$this->send('InvoiceRequest', $pars))
		{
			return -1;
		}

		$this->invoiceNo = $this->parse('invoiceno');

		if (!$this->invoiceNo)
		{
			$this->error();

			return -2;
		}

		$this->documentId = $this->parse('documentid');

		return 0;
	}

	/**
	 * Cancel reservation request.
	 *
	 * @param   string  $trxid  The transaction ID.
	 *
	 * @return  int  Status code.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function CancelReservationRequest($trxid)
	{
		$pars = array();
		$pars['merchantid'] = $this->merchantId;
		$pars['shopid'] = $this->shopId;
		$pars['trxid'] = $trxid;
		$pars['sha1'] = sha1($trxid . $this->shopId . $this->merchantId . $this->merchantKey);

		if (!$this->send('CancelReservationRequest', $pars))
		{
			return -1;
		}

		return 0;
	}

	// CreditInvoiceRequest
	/**
	 * Credit invoice request.
	 *
	 * @param   string  $trxid     The transaction ID.
	 * @param   array   $keyvalue  An array of values to send to Sisow.
	 *
	 * @return  int  Status code.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function CreditInvoiceRequest($trxid, array $keyvalue = array())
	{
		$pars = array();
		$pars['merchantid'] = $this->merchantId;
		$pars['shopid'] = $this->shopId;
		$pars['trxid'] = $trxid;
		$pars['sha1'] = sha1($trxid . $this->shopId . $this->merchantId . $this->merchantKey);

		if ($keyvalue)
		{
			foreach ($keyvalue as $k => $v)
			{
				$pars[$k] = $v;
			}
		}

		if (!$this->send('CreditInvoiceRequest', $pars))
		{
			return -1;
		}

		$this->invoiceNo = $this->parse('invoiceno');

		if (!$this->invoiceNo)
		{
			$this->error();

			return -2;
		}

		$this->documentId = $this->parse('documentid');

		return 0;
	}
}
