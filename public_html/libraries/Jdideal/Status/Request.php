<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

namespace Jdideal\Status;

use Jdideal\Gateway;
use Jdideal\Psp;

defined('_JEXEC') or die;

/**
 * Handle the status requests.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class Request
{
	/**
	 * Global configuration settings
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  4.0
	 */
	private $config;

	/**
	 * JD iDEAL Gateway global settings
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  4.0
	 */
	private $params;

	/**
	 * The Joomla Mailer class
	 *
	 * @var    \JMail
	 * @since  4.0
	 */
	private $mail;

	/**
	 * The email address of the site sending the email
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $from;

	/**
	 * The name of the site sending the email
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $fromName;

	/**
	 * The URL of the site to create URLs in the emails
	 *
	 * @var    string
	 * @since  4.0
	 */
	private $siteUrl;

	/**
	 * Process the status request.
	 *
	 * @return  array  The transaction data.
	 *
	 * @since   3.0
	 *
	 * @throws  \Exception
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 */
	public function process()
	{
		// Get the helper
		$jdideal    = new Gateway;
		$jinput     = \JFactory::getApplication()->input;
		$status     = false;
		$errorLevel = '';
		$redirect   = false;

		/** @var Psp\Ems $notifier */
		$notifier = $jdideal->loadNotifier($jdideal->psp, $jinput);

		if (!$notifier)
		{
			throw new \RuntimeException(\JText::sprintf('COM_JDIDEALGATEWAY_CANNOT_LOAD_NOTIFIER', $jdideal->psp));
		}

		try
		{
			// Get the log ID
			$logId = $notifier->getLogId();
		}
		catch (\Exception $e)
		{
			$this->writeErrorLog($e->getMessage());

			throw new \RuntimeException($e->getMessage(), $e->getCode());
		}

		// Store some data for debugging
		$jdideal->log('Originating IP: ' . $_SERVER['REMOTE_ADDR'], $logId);

		if (array_key_exists('HTTP_USER_AGENT', $_SERVER))
		{
			$jdideal->log('User agent: ' . $_SERVER['HTTP_USER_AGENT'], $logId);
		}

		$jdideal->log('Query string: ' . $_SERVER['QUERY_STRING'], $logId);

		$user = \JFactory::getUser();

		// Determine if the payment provider is calling
		$isCustomer = $notifier->isCustomer();

		if ($isCustomer)
		{
			$jdideal->log('User ' . $user->get('username', 'Guest') . ' is calling.', $logId);
		}
		else
		{
			$jdideal->log('Payment provider calling', $logId);
		}

		// Load the payment details from the database
		$transactionDetails = $jdideal->getDetails($logId);

		if (!is_object($transactionDetails))
		{
			$jdideal->log('No transaction details have been found.', $logId);

			throw new \RuntimeException(\JText::sprintf('COM_JDIDEALGATEWAY_NO_LOGDETAILS_FOUND', $logId));
		}

		// Check if the payment has already been checked and only if the payment provider is calling
		if (!$transactionDetails->processed || !$isCustomer)
		{
			// Load the config
			$this->config = \JFactory::getConfig();
			$this->params = \JComponentHelper::getParams('com_jdidealgateway');
			$this->mail   = \JFactory::getMailer();

			// Load the mail settings
			$this->from     = $this->config->get('mailfrom');
			$this->fromName = $this->config->get('fromname');

			// Get the live site URL
			$this->siteUrl = $jdideal->getUrl();

			// Build the result data
			$resultData            = array();
			$resultData['isValid'] = true;
			$resultData['message'] = '';

			// Get the transaction ID
			$transactionID = $notifier->getTransactionId();

			// Validate the status request
			$transactionStatus = $notifier->transactionStatus($jdideal, $logId);
			$status            = $transactionStatus['isOK'];

			// Load the Addon class
			try
			{
				/** @var \JdidealAddon $extensionAddon */
				$extensionAddon = $jdideal->getAddon($transactionDetails->origin);
			}
			catch (\Exception $e)
			{
				$jdideal->log($e->getMessage(), $logId);

				// Inform the admin of the missing file
				$this->mail->clearAddresses();
				$body = \JText::sprintf(
					'COM_JDIDEALGATEWAY_ADDON_FILE_MISSING_DESC',
					JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/addons/' . strtolower($transactionDetails->origin) . '.php',
					$transactionDetails->origin
				);
				$this->mail->sendMail(
					$this->from,
					$this->fromName,
					$this->from,
					\JText::sprintf('COM_JDIDEALGATEWAY_ADDON_FILE_MISSING', $transactionDetails->origin),
					$body,
					false
				);

				throw new \RuntimeException($e->getMessage());
			}

			// Build the order data
			$orderData                 = array();
			$orderData['order_number'] = $transactionDetails->order_number;
			$orderData['order_id']     = $transactionDetails->order_id;

			// Get the additional order data from the addon
			try
			{
				$orderData = $extensionAddon->getOrderInformation($transactionDetails->order_id, $orderData);
			}
			catch (\Exception $e)
			{
				$jdideal->log('Caught an error: ' . $e->getMessage(), $logId);
			}

			if (!$transactionStatus['isOK'])
			{
				// Set the result is false
				$resultData['isValid'] = false;
				$errorLevel            = 'error';

				// Status Request failed
				$message = \JText::_('COM_JDIDEALGATEWAY_STATUS_COULD_NOT_BE_RETRIEVED');

				// Log the error message
				if (array_key_exists('error_message', $transactionStatus))
				{
					$jdideal->log($transactionStatus['error_message'], $logId);
					$message .= \JText::sprintf('COM_JDIDEALGATEWAY_IDEAL_ERROR_MESSAGE', $transactionStatus['error_message']);
				}

				$resultData['message'] = $message;

				// Store the result
				$jdideal->status('OPEN', $logId);

				$this->emailResultNOK($jdideal, $transactionDetails, $orderData);
			}
			else
			{
				// Get the payment status
				$responseStatus = $transactionStatus['suggestedAction'];

				if (!$responseStatus)
				{
					$jdideal->log('No response status has been received for transaction ID ' . $transactionID, $logId);

					throw new \InvalidArgumentException(\JText::_('COM_JDIDEALGATEWAY_NO_RESPONSESTATUS'));
				}

				$jdideal->log('Payment has ' . $responseStatus . ' status', $logId);
				$jdideal->log('Current order status ' . $orderData['order_status'], $logId);

				// Store the result
				$jdideal->status($responseStatus, $logId);

				// Log the error message received from the payment provider
				if (array_key_exists('error_message', $transactionStatus))
				{
					$jdideal->log($transactionStatus['error_message'], $logId);
				}

				// Verify the order pending status matches the status to update an order
				if ($orderData['order_status'] !== $jdideal->get('pendingStatus', 'P'))
				{
					// Status doesn't match, inform the administrator if needed
					if ($this->params->get('status_mismatch'))
					{
						$options = array();
						$options['type'] = 'status_mismatch';
						$options['expected_status'] = $jdideal->get('pendingStatus');
						$options['found_status'] = $orderData['order_status'];

						if (array_key_exists('order_number', $orderData))
						{
							$options['order_number'] = $orderData['order_number'];
						}

						$options['status'] = $responseStatus;
						$jdideal->informAdmin($options);
					}

					$jdideal->log('Order has status ' . $orderData['order_status'] . ' but according to the settings it needs status '
						. $jdideal->get('pendingStatus') . ' to be able to update the order', $logId
					);

					throw new \RuntimeException(
						\JText::sprintf(
							'COM_JDIDEALGATEWAY_ORDER_STATUS_DOESNT_MATCH_PENDING',
							$orderData['order_status'],
							$jdideal->get('pendingStatus')
						)
					);
				}

				// Set the order comment
				$orderData['order_comment'] = \JText::_('COM_JDIDEALGATEWAY_TRANSACTION_ID') . ' ' . $transactionID;

				// Process the response
				switch (strtoupper($responseStatus))
				{
					case 'SUCCESS':
						// Check if the current order status matches the order status the order must have to be able to update it
						$orderData['order_status'] = $jdideal->get('verifiedStatus');
						break;
					case 'CANCELLED':
						$orderData['order_status'] = $jdideal->get('cancelledStatus');
						break;
					case 'FAILURE':
						$orderData['order_status'] = $jdideal->get('failedStatus');
						break;
					default:
						$orderData['order_status'] = $jdideal->get('openStatus');
						break;
				}

				// Update the order status
				$orderStatusName = $extensionAddon->getOrderStatusName($orderData);
				$orderLink       = $extensionAddon->getOrderLink($orderData['order_id'], $orderData['order_number']);

				// Send out the emails if needed
				try
				{
					$this->emailCustomerChangeStatus($jdideal, $responseStatus, $orderData, $orderStatusName, $orderLink);
				}
				catch (\Exception $e)
				{
					$jdideal->log($e->getMessage(), $logId);
				}

				try
				{
					$this->emailAdminOrderPayment($jdideal, $transactionDetails, $transactionStatus, $orderData, $orderStatusName, $transactionID);
				}
				catch (\Exception $e)
				{
					$jdideal->log($this->getMessage(), $logId);
				}
			}

			// Build the notify URL for the extension
			if ($resultData['isValid'])
			{
				$redirect = $transactionDetails->notify_url ?: $transactionDetails->return_url;
			}
			else
			{
				$redirect = $transactionDetails->cancel_url ?: $transactionDetails->return_url;
			}

			// Complete the redirect URL
			if (strpos($redirect, '?') !== false)
			{
				$redirect .= '&transactionId=' . $transactionID;
			}
			else
			{
				$redirect .= '?transactionId=' . $transactionID;
			}

			// Call the extension to update the status if we are the payment provider calling
			$jdideal->log('Notifying extension on URL: ' . $redirect, $logId);

			// Load the HTTP driver
			try
			{
				$http = \JHttpFactory::getHttp(null, array('curl', 'stream'));

				// Call the URL
				$httpResponse = $http->get($redirect);

				$jdideal->log('Received HTTP status ' . $httpResponse->code, $logId);

				if ($httpResponse->code === 500)
				{
					$jdideal->log($httpResponse->body, $logId);
				}

				// Get the new transaction details as they may have changed
				$transactionDetails = $jdideal->getDetails($transactionDetails->id, 'id', true);
			}
			catch (\Exception $e)
			{
				$jdideal->log('Caught an error notifying extension: ' . $e->getMessage(), $logId);
			}

			if ($isCustomer)
			{
				$redirect = $this->getCustomerRedirect($jdideal, $transactionDetails);
			}

			// Trigger the callback
			$extensionAddon->callBack(array_merge($resultData, (array) $transactionDetails));
		}

		if (!$redirect)
		{
			// Get the redirect URL
			$redirect = $this->getCustomerRedirect($jdideal, $transactionDetails);

			$jdideal->log('Redirecting customer to: ' . $redirect, $logId);
		}

		// Build the return data
		$return               = array();
		$return['isCustomer'] = $isCustomer;
		$return['url']        = $redirect;
		$return['message']    = '';
		$return['level']      = $errorLevel;
		$return['status']     = $status ? 'OK' : 'NOK';

		return $return;
	}

	/**
	 * Check who is knocking at the door.
	 *
	 * @return  bool  True if it is the PSP otherwise false if it is the customer.
	 *
	 * @since   4.0
	 *
	 * @throws  \Exception
	 */
	public function whoIsCalling()
	{
		// Get the helper
		$jdideal = new Gateway;
		$jinput = \JFactory::getApplication()->input;

		$notifier = $jdideal->loadNotifier($jdideal->psp, $jinput);
		$callback = false;

		if ($notifier)
		{
			$callback = $notifier->isCustomer();
		}

		return $callback;
	}

	/**
	 * Send the email for a not OK status.
	 *
	 * @param   Gateway  $jdideal    The JdidealGateway class.
	 * @param   object   $details    An array with transaction details.
	 * @param   array    $orderData  An array with result status.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	private function emailResultNOK(Gateway $jdideal, $details, $orderData)
	{
		if ($this->params->get('admin_payment_failed'))
		{
			// Inform the admin
			// Get the body from the database if available
			$mail_tpl = $jdideal->getMailBody('admin_payment_failed');

			if ($mail_tpl)
			{
				$find = array();
				$find[] = '{ORDERNR}';
				$find[] = '{ORDERID}';
				$find[] = '{BEDRAG}';
				$find[] = '{USER_EMAIL}';
				$replace = array();
				$replace[] = $orderData['order_number'];
				$replace[] = $orderData['order_id'];
				$replace[] = number_format($details->amount, 2, ',', '.');
				$replace[] = $orderData['user_email'];
				$body = str_ireplace($find, $replace, $mail_tpl->body);
				$subject = str_ireplace($find, $replace, $mail_tpl->subject);
				$html = true;
			}
			else
			{
				$subject = sprintf(
					\JText::_('COM_JDIDEALGATEWAY_MAIL_PAYMENT_STATUS'),
					$this->from,
					\JText::_('COM_JDIDEALGATEWAY_MAIL_STATUS_REQUEST_FAILED'),
					$orderData['order_number']
				);
				$body = \JText::_('COM_JDIDEALGATEWAY_MAIL_INTRO') . "\n\n";
				$body .= sprintf(
						\JText::_('COM_JDIDEALGATEWAY_MAIL_IDEAL_RESULT'), strtoupper(\JText::_('COM_JDIDEALGATEWAY_MAIL_STATUS_REQUEST_FAILED'))
					) . "\n";
				$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_TRANSACTION_FOR'), $this->from, $this->siteUrl) . "\n";
				$body .= "-----------------------------------------------------------\n";
				$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_EMAIL_CUSTOMER'), $orderData['user_email']) . "\n";
				$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_ORDER_ID'), $orderData['order_number']) . "\n";
				$html = false;
			}

			$recipients = explode(',', $this->params->get('jdidealgateway_emailto'));

			if ($recipients)
			{
				foreach ($recipients as $recipient)
				{
					$this->mail->clearAddresses();
					$this->mail->sendMail($this->from, $this->fromName, $recipient, $subject, $body, $html);
				}
			}
		}
	}

	/**
	 * Send the customer change status email.
	 *
	 * @param   Gateway  $jdideal          The JdidealGateway class.
	 * @param   string   $result           The payment result.
	 * @param   array    $orderData        An array with result status.
	 * @param   string   $orderStatusName  The order status name.
	 * @param   string   $orderLink        The link to the order.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	private function emailCustomerChangeStatus(Gateway $jdideal, $result, $orderData, $orderStatusName, $orderLink)
	{
		if ($this->params->get('customer_change_status'))
		{
			$recipient = $orderData['user_email'];
			$mail_tpl = $jdideal->getMailBody('customer_change_status');

			if ($mail_tpl)
			{
				$subject = $mail_tpl->subject;
				$find = array();
				$find[] = '{ORDERNR}';
				$find[] = '{ORDERID}';
				$find[] = '{STATUS_NAME}';
				$find[] = '{ORDER_LINK}';
				$replace = array();
				$replace[] = $orderData['order_number'];
				$replace[] = $orderData['order_id'];
				$replace[] = $orderStatusName;
				$replace[] = $orderLink ? \JRoute::_($this->siteUrl . $orderLink) : '';
				$body = str_ireplace($find, $replace, $mail_tpl->body);
				$html = true;
			}
			else
			{
				$subject = sprintf(
					\JText::_('COM_JDIDEALGATEWAY_MAIL_PAYMENT_STATUS'), $this->fromName, ucfirst(\JText::_($result)), $orderData['order_number']
				);
				$body = \JText::_('COM_JDIDEALGATEWAY_MAIL_INTRO') . "\n\n";
				$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_STATUS_CHANGED'), $orderData['order_number']) . "\n";
				$body .= "-------------------------------------------------------------------------------------------------------\n";
				$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_NEW_STATUS'), $orderStatusName) . "\n";
				$body .= "-------------------------------------------------------------------------------------------------------\n";
				$body .= "\n\n";

				if ($orderLink)
				{
					$body .= \JText::_('COM_JDIDEALGATEWAY_MAIL_CLICK_BROWSER_LINK') . "\n";
					$body .= $this->siteUrl . $orderLink . "\n";
					$body .= "\n\n";
					$body .= "-------------------------------------------------------------------------------------------------------\n";
				}

				$body .= $this->fromName . "\n";
				$body .= $this->siteUrl . "\n";
				$body .= $this->from;
				$html = false;
			}

			$this->mail->clearAddresses();
			$this->mail->sendMail($this->from, $this->fromName, $recipient, $subject, $body, $html);
		}
	}

	/**
	 * Send the administrator change status email.
	 *
	 * @param   Gateway  $jdideal          The JdidealGateway class.
	 * @param   object   $details          An array with transaction details.
	 * @param   array    $result           An array with result data.
	 * @param   array    $orderData        An array with result status.
	 * @param   string   $orderStatusName  The order status name.
	 * @param   string   $transactionID    The link to the order.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	private function emailAdminOrderPayment(Gateway $jdideal, $details, $result, $orderData, $orderStatusName, $transactionID)
	{
		if ($this->params->get('admin_order_payment'))
		{
			// Get the body from the database if available
			$mail_tpl = $jdideal->getMailBody('admin_order_payment');

			if ($mail_tpl)
			{
				$find = array();
				$find[] = '{ORDERNR}';
				$find[] = '{ORDERID}';
				$find[] = '{BEDRAG}';
				$find[] = '{STATUS}';
				$find[] = '{STATUS_NAME}';
				$find[] = '{TRANSACTION_ID}';
				$find[] = '{USER_EMAIL}';
				$find[] = '{CONSUMERACCOUNT}';
				$find[] = '{CONSUMERIBAN}';
				$find[] = '{CONSUMERBIC}';
				$find[] = '{CONSUMERNAME}';
				$find[] = '{CONSUMERCITY}';
				$find[] = '{CARD}';
				$replace = array();
				$replace[] = $orderData['order_number'];
				$replace[] = $orderData['order_id'];
				$replace[] = number_format($details->amount, 2, ',', '.');
				$replace[] = \JText::_(strtoupper($result['suggestedAction']));
				$replace[] = \JText::_($orderStatusName);
				$replace[] = $transactionID;
				$replace[] = $orderData['user_email'];

				if (!empty($result['consumer']))
				{
					$replace[] = array_key_exists('consumerconsumerAccount', $result['consumer']) ? $result['consumer']['consumerAccount'] : '';
					$replace[] = array_key_exists('consumerconsumerIban', $result['consumer']) ? $result['consumer']['consumerIban'] : '';
					$replace[] = array_key_exists('consumerconsumerBic', $result['consumer']) ? $result['consumer']['consumerBic'] : '';
					$replace[] = array_key_exists('consumerconsumerName', $result['consumer']) ? $result['consumer']['consumerName'] : '';
					$replace[] = array_key_exists('consumerconsumerCity', $result['consumer']) ? $result['consumer']['consumerCity'] : '';
				}
				else
				{
					$replace[] = '';
					$replace[] = '';
					$replace[] = '';
					$replace[] = '';
					$replace[] = '';
				}

				$replace[] = $result['card'] ?: '';
				$body = str_ireplace($find, $replace, $mail_tpl->body);
				$subject = str_ireplace($find, $replace, $mail_tpl->subject);
				$html = true;
			}
			else
			{
				$subject = sprintf(
					\JText::_('COM_JDIDEALGATEWAY_MAIL_PAYMENT_STATUS'), $this->fromName, ucfirst(\JText::_($result['suggestedAction'])), $orderData['order_number']
				);
				$body = \JText::_('COM_JDIDEALGATEWAY_MAIL_INTRO') . "\n\n";
				$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_IDEAL_RESULT'), ucfirst(\JText::_($result['suggestedAction']))) . "\n";
				$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_TRANSACTION_FOR'), $this->fromName, $this->siteUrl) . "\n";
				$body .= "-----------------------------------------------------------\n";
				$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_TRANSACTION_ID'), $transactionID) . "\n";
				$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_EMAIL_CUSTOMER'), $orderData['user_email']) . "\n";

				if (!empty($result['consumer']))
				{
					if (!empty($result['consumer']['consumerAccount']))
					{
						$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_ACCOUNT_CUSTOMER'), $result['consumer']['consumerAccount']) . "\n";
					}

					if (!empty($result['consumer']['consumerIban']))
					{
						$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_IBAN_CUSTOMER'), $result['consumer']['consumerIban']) . "\n";
					}

					if (!empty($result['consumer']['consumerBic']))
					{
						$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_BIC_CUSTOMER'), $result['consumer']['consumerBic']) . "\n";
					}

					if (!empty($result['consumer']['consumerName']))
					{
						$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_NAME_CUSTOMER'), $result['consumer']['consumerName']) . "\n";
					}

					if (!empty($result['consumer']['consumerCity']))
					{
						$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_CITY_CUSTOMER'), $result['consumer']['consumerCity']) . "\n";
					}
				}

				$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_ORDER_ID'), $orderData['order_number']) . "\n";
				$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_ORDER_STATUS'), $orderStatusName) . "\n";

				if ($result['card'])
				{
					$body .= sprintf(\JText::_('COM_JDIDEALGATEWAY_MAIL_ORDER_CARD'), $result['card']) . "\n";
				}

				$html = false;
			}

			// Load list of users to inform
			$recipients = explode(',', $this->params->get('jdidealgateway_emailto'));

			if ($recipients)
			{
				foreach ($recipients as $recipient)
				{
					$this->mail->clearAddresses();
					$this->mail->sendMail($this->from, $this->fromName, $recipient, $subject, $body, $html);
				}
			}
		}
	}

	/**
	 * Build the return URL for the customer.
	 *
	 * @param   Gateway  $jdideal             The JdidealGateway class.
	 * @param   object   $transactionDetails  The details of the transaction.
	 *
	 * @return  string  The URL to send the customer to.
	 *
	 * @since   4.0
	 */
	private function getCustomerRedirect(Gateway $jdideal, $transactionDetails)
	{
		// Transaction already processed, build the return URL for the extension
		$redirect = $transactionDetails->cancel_url ?: $transactionDetails->return_url;

		if ($jdideal->isValid($transactionDetails->result))
		{
			$redirect = $transactionDetails->return_url;
		}

		// Complete the redirect URL
		if (strpos($redirect, '?') !== false)
		{
			$redirect .= '&transactionId=' . $transactionDetails->trans;
		}
		else
		{
			$redirect .= '?transactionId=' . $transactionDetails->trans;
		}

		return $redirect;
	}

	/**
	 * Write information to the error log.
	 *
	 * @param   string  $message  The exception message to write to the log
	 *
	 * @return  void
	 *
	 * @since   4.6.0
	 */
	public function writeErrorLog($message)
	{
		jimport('joomla.log.log');

		\JLog::addLogger(
			array(
				'text_file' => 'com_jdidealgateway.errors.php'
			),
			\JLog::ERROR,
			array('com_jdidealgateway')
		);

		$message = $message . "\r\n"
			. 'Request method: ' . $_SERVER['REQUEST_METHOD'] . "\r\n"
			. 'Originating IP: ' . $_SERVER['REMOTE_ADDR'] . "\r\n";

		$input = \JFactory::getApplication()->input;

		// Check for POST variables
		$getArray = $input->get->getArray();
		$get      = array();

		foreach ($getArray as $name => $value)
		{
			$get[] = $name . '=' . $input->get->get($name);
		}

		$message .= 'GET variables: ' . implode('&', $get) . "\r\n";

		$postArray = $input->post->getArray();
		$post      = array();

		foreach ($postArray as $name => $value)
		{
			$post[] = $name . '=' . $input->post->get($name);
		}

		$message .= 'POST variables: ' . implode('&', $post) . "\r\n";


		if (array_key_exists('HTTP_USER_AGENT', $_SERVER))
		{
			$message .= 'User agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
		}

		\JLog::add($message, \JLog::ERROR, 'com_jdidealgateway');
	}
}
