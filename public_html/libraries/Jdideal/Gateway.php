<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

namespace Jdideal;

use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * JD iDEAL Gateway helper.
 *
 * @package     JDiDEAL
 * @subpackage  Core
 * @since       3.0
 */
class Gateway
{
	/**
	 * The selected payment processor
	 *
	 * @var    string
	 * @since  3.0
	 */
	public $psp = '';

	/**
	 * The ID of the selected payment processor
	 *
	 * @var    int
	 * @since  4.0
	 */
	public $profileId;

	/**
	 * The configuration settings.
	 *
	 * @var    Registry
	 * @since  3.0
	 */
	private $configuration = array();

	/**
	 * The transaction details
	 *
	 * @var    array
	 * @since  3.0
	 */
	private $logDetails = array();

	/**
	 * JDatabase handler.
	 *
	 * @var    \JDatabaseDriver
	 * @since  3.0
	 */
	private $db;

	/**
	 * Construct the helper.
	 *
	 * @param   string  $profileAlias  The name of the profile to use
	 *
	 * @since   3.0
	 *
	 * @throws  \Exception
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 */
	public function __construct($profileAlias = null)
	{
		// Set the database handler
		$this->db = \JFactory::getDbo();

		// Load the language
		$lang = \JFactory::getLanguage();
		$lang->load('com_jdidealgateway', JPATH_ADMINISTRATOR . '/components/com_jdidealgateway');
		$lang->load('com_jdidealgateway', JPATH_SITE . '/components/com_jdidealgateway');

		// Find the profile alias, needed to load the configuration
		if ($profileAlias === null)
		{
			$profileAlias = $this->findProfileAlias();
		}

		// Load the configuration
		$this->loadConfiguration($profileAlias);
	}

	/**
	 * Find the log ID for any given payment provider.
	 *
	 * @return  string  The profile alias to load.
	 *
	 * @since   4.0
	 *
	 * @throws  \Exception
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 */
	private function findProfileAlias()
	{
		// Initialise the alias
		$alias = false;
		$transactionId = 0;

		// Check how many profiles there are
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('alias'))
			->from($this->db->quoteName('#__jdidealgateway_profiles'))
			->order($this->db->quoteName('ordering'));
		$this->db->setQuery($query);
		$profiles = $this->db->loadColumn();

		if (array_key_exists(0, $profiles))
		{
			$alias = $profiles[0];
		}

		if (count($profiles) > 1)
		{
			$jinput = \JFactory::getApplication()->input;

			// List of possible transaction IDs
			$transactionKeys = array(
				'trxid',
				'transaction_id',
				'transactionId',
				'PAYID',
				'order_id',
			);

			// Check for known keys
			foreach ($transactionKeys as $key)
			{
				$transactionId = $jinput->get($key, 0);

				if (0 !== $transactionId)
				{
					// Found a transaction ID
					break;
				}
			}

			// Check if we found a key
			if (0 === $transactionId)
			{
				// No key found yet but we may be using Rabobank Omnikassa, this requires some more work
				$returnData = $jinput->get('Data', '', 'string');

				if ($returnData)
				{
					$dataArray = explode('|', $returnData);

					foreach ($dataArray as $pair)
					{
						list($name, $value) = explode('=', $pair);

						if ($name === 'transactionReference')
						{
							$transactionId = $value;
						}
					}
				}
			}

			// Check again if we found a key
			$logId = false;

			if (0 !== $transactionId)
			{
				// We found a transaction ID, let's get the log ID for it
				$query = $this->db->getQuery(true)
					->select($this->db->quoteName('id'))
					->from($this->db->quoteName('#__jdidealgateway_logs'))
					->where($this->db->quoteName('trans') . ' = ' . $this->db->quote($transactionId));
				$this->db->setQuery($query);

				$logId = $this->db->loadResult();
			}

			// If we don't have a log ID try to find one from known keys
			if (!$logId)
			{
				$logKeys = array(
					'ec',
					'add_logid',
					'logid',
					'COMPLUS',
					'id',
				);

				foreach ($logKeys as $key)
				{
					$logId = $jinput->get($key, 0);

					if (0 !== $logId)
					{
						// Found a log ID
						break;
					}
				}
			}

			if (!$logId)
			{
				// Can't find any reference, take the first available profile
				$alias = $profiles[0];
			}
			else
			{
				$query->clear()
					->select($this->db->quoteName('p.alias'))
					->from($this->db->quoteName('#__jdidealgateway_logs', 'l'))
					->leftJoin(
						$this->db->quoteName('#__jdidealgateway_profiles', 'p')
						. ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('l.profile_id')
					)
					->where($this->db->quoteName('l.id') . ' = ' . (int) $logId);
				$this->db->setQuery($query);
				$alias = $this->db->loadResult();
			}
		}

		if (!$alias)
		{
			throw new \InvalidArgumentException(\JText::_('COM_JDIDEALGATEWAY_NO_ALIAS_FOUND'));
		}

		return $alias;

	}

	/**
	 * Load the JD iDEAL configuration.
	 *
	 * @param   string  $profileAlias  The alias of the profile to load.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *
	 * @throws  \RuntimeException
	 */
	public function loadConfiguration($profileAlias = null)
	{
		$query = $this->db->getQuery(true)
			->select(
				$this->db->quoteName(
					array(
						'psp',
						'paymentInfo'
					)
				)
			)
			->select($this->db->quoteName('id', 'profile_id'))
			->from($this->db->quoteName('#__jdidealgateway_profiles'))
			->order($this->db->quoteName('ordering'));

		// See if we need to load a specific alias
		if (null !== $profileAlias)
		{
			$query->where($this->db->quoteName('alias') . ' = ' . $this->db->quote($profileAlias));
		}

		$this->db->setQuery($query, 0, 1);

		$config = $this->db->loadObject();

		if ($config)
		{
			$this->configuration = new Registry($config->paymentInfo);
			$this->psp = $config->psp;
			$this->profileId = $config->profile_id;
		}
		else
		{
			$this->configuration = new Registry;
		}
	}

	/**
	 * Get a configuration value.
	 *
	 * @param   string  $name     Name of the configuration value to get.
	 * @param   mixed   $default  The default value if nothing is found.
	 *
	 * @return  mixed  The requested value.
	 *
	 * @since   3.0
	 */
	public function get($name, $default='')
	{
		return $this->configuration->get($name, $default);
	}

	/**
	 * Set a configuration value.
	 *
	 * @param   string  $name   The name of the parameter
	 * @param   mixed   $value  The value of the parameter
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	public function set($name, $value)
	{
		$this->configuration->set($name, $value);
	}

	/**
	 * Send an email to the administrator.
	 *
	 * @param   array  $options  The options to use in the mail.
	 *
	 * @return  bool  True if mail has been sent | False if mail has not been sent.
	 *
	 * @since   2.2
	 *
	 * @throws  \RuntimeException
	 */
	public function informAdmin($options)
	{
		$config = \JFactory::getConfig();
		$from = $config->get('mailfrom');
		$fromname = $config->get('fromname');
		$subject = false;
		$body = false;
		$html = false;

		// Something is up, inform the administrator
		switch ($options['type'])
		{
			case 'status_mismatch':
				$mail_tpl = $this->getMailBody('admin_status_mismatch');

				if ($mail_tpl)
				{
					$find = array();
					$find[] = '{ORDERNR}';
					$find[] = '{EXPECTED_STATUS}';
					$find[] = '{FOUND_STATUS}';
					$find[] = '{STATUS}';
					$find[] = '{HTTP_HOST}';
					$find[] = '{QUERY_STRING}';
					$find[] = '{REMOTE_ADDRESS}';
					$find[] = '{SCRIPT_FILENAME}';
					$find[] = '{REQUEST_TIME}';
					$replace = array();
					$replace[] = $options['order_number'];
					$replace[] = $options['expected_status'];
					$replace[] = $options['found_status'];
					$replace[] = $options['status'];
					$replace[] = $_SERVER['HTTP_HOST'];
					$replace[] = $_SERVER['QUERY_STRING'];
					$replace[] = $_SERVER['REMOTE_ADDR'];
					$replace[] = $_SERVER['SCRIPT_FILENAME'];
					$replace[] = $_SERVER['REQUEST_TIME'];
					$body = str_ireplace($find, $replace, $mail_tpl->body);
					$subject = $mail_tpl->subject;
					$html = true;
				}
				else
				{
					$subject = \JText::sprintf('COM_JDIDEALGATEWAY_STATUS_MISMATCH', $options['order_number']);
					$body = "********************\n";
					$body .= '* ' . \JText::_('COM_JDIDEALGATEWAY_STATUS_REPORT') . "\n";
					$body .= "********************\n";
					$body .= \JText::sprintf('COM_JDIDEALGATEWAY_EXPECTED_STATUS', $options['expected_status']) . "\n";
					$body .= \JText::sprintf('COM_JDIDEALGATEWAY_FOUND_STATUS', $options['found_status']) . "\n";
					$body .= \JText::sprintf('COM_JDIDEALGATEWAY_ORDER_NUMBER', $options['order_number']) . "\n";
					$body .= \JText::sprintf('COM_JDIDEALGATEWAY_STATUS', $options['status']) . "\n";
					$body .= \JText::sprintf('COM_JDIDEALGATEWAY_HTTP_HOST', $_SERVER['HTTP_HOST']) . "\n";
					$body .= \JText::sprintf('COM_JDIDEALGATEWAY_QUERY_STRING', $_SERVER['QUERY_STRING']) . "\n";
					$body .= \JText::sprintf('COM_JDIDEALGATEWAY_REMOTE_ADDRESS', $_SERVER['REMOTE_ADDR']) . "\n";
					$body .= \JText::sprintf('COM_JDIDEALGATEWAY_SCRIPT_FILENAME', $_SERVER['SCRIPT_FILENAME']) . "\n";
					$body .= \JText::sprintf('COM_JDIDEALGATEWAY_REQUEST_TIME', date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])) . "\n";
					$html = false;
				}
				break;
		}

		if (!$subject && $body && $html)
		{
			return false;
		}

		// Send the e-mail
		$mail = \JFactory::getMailer();

		return $mail->sendMail($from, $fromname, $from, $subject, $body, $html);
	}

	/**
	 * Logs the message to the database.
	 *
	 * @param   string  $message  The message to log.
	 * @param   int     $logId    The ID of the transaction to add the log message to.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *
	 * @throws  \RuntimeException
	 */
	public function log($message, $logId)
	{
		if ($logId)
		{
			// Prefix message with timestamp
			$message = '[' . date('Y-m-d H:i:s', time()) . '] ' . $message;

			$query = $this->db->getQuery(true);
			$message .= "\r\n\r\n";
			$query->update($this->db->quoteName('#__jdidealgateway_logs'))
				->set($this->db->quoteName('history') . ' = CONCAT(' . $this->db->quoteName('history') . ', ' . $this->db->quote($message) . ')')
				->where($this->db->quoteName('id') . ' = ' . (int) $logId);
			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Store the transaction details.
	 *
	 * @param   string  $card              The type of payment method that has been used.
	 * @param   int     $processed         Set if the payment has been processed.
	 * @param   int     $logId             The ID of the transaction to add the details to.
	 * @param   string  $paymentReference  The overboeking reference.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function setTransactionDetails($card, $processed, $logId, $paymentReference = null)
	{
		$this->log('Set transaction details', $logId);
		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__jdidealgateway_logs'))
			->set($this->db->quoteName('card') . ' = ' . $this->db->quote($card))
			->set($this->db->quoteName('processed') . ' = ' . $processed)
			->where($this->db->quoteName('id') . ' = ' . (int) $logId);

		if ($paymentReference)
		{
			$query->set($this->db->quoteName('paymentReference') . ' = ' . $this->db->quote($paymentReference));
		}

		$this->db->setQuery($query)->execute();
	}

	/**
	 * Store the payment ID.
	 *
	 * @param   string  $paymentId  The ID of the payment.
	 * @param   int     $logId      The ID of the transaction to add the details to.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function setPaymentId($paymentId, $logId)
	{
		if ($logId)
		{
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__jdidealgateway_logs'))
				->set($this->db->quoteName('paymentId') . ' = ' . $this->db->quote($paymentId))
				->where($this->db->quoteName('id') . ' = ' . (int) $logId);

			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Update the transaction status.
	 *
	 * @param   string  $result  The transaction result.
	 * @param   int     $logId   The ID of the transaction to add the status to.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *
	 * @throws  \RuntimeException
	 */
	public function status($result, $logId)
	{
		if ($logId)
		{
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__jdidealgateway_logs'))
				->set($this->db->quoteName('result') . ' = ' . $this->db->quote(strtoupper($result)))
				->where($this->db->quoteName('id') . ' = ' . (int) $logId);
			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Update the transaction reference.
	 *
	 * @param   string  $trans  The transaction reference.
	 * @param   int     $logId  The ID of the transaction to add the status to.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *
	 * @throws  \RuntimeException
	 */
	public function setTrans($trans, $logId)
	{
		if ($logId)
		{
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__jdidealgateway_logs'))
				->set($this->db->quoteName('trans') . ' = ' . $this->db->quote($trans))
				->where($this->db->quoteName('id') . ' = ' . (int) $logId);
			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Retrieve transaction reference.
	 *
	 * @param   int  $logId  The ID of the transaction to add the status to.
	 *
	 * @return  string  The transaction reference.
	 *
	 * @since   3.0
	 *
	 * @throws  \RuntimeException
	 */
	public function getTrans($logId)
	{
		$result = '';

		if ($logId)
		{
			$query = $this->db->getQuery(true)
				->select($this->db->quoteName('trans'))
				->from($this->db->quoteName('#__jdidealgateway_logs'))
				->where($this->db->quoteName('id') . ' = ' . (int) $logId);
			$this->db->setQuery($query);

			$result = $this->db->loadResult();
		}

		return $result;
	}

	/**
	 * Update the processed status.
	 *
	 * @param   int  $processed  The new processed status.
	 * @param   int  $logId      The ID of the transaction to add the status to.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function setProcessed($processed, $logId)
	{
		if ($logId)
		{
			$query = $this->db->getQuery(true)
				->update($this->db->quoteName('#__jdidealgateway_logs'))
				->set($this->db->quoteName('processed') . ' = ' . (int) $processed)
				->where($this->db->quoteName('id') . ' = ' . (int) $logId);
			$this->db->setQuery($query)->execute();
		}
	}

	/**
	 * Load transaction details.
	 *
	 * @param   mixed   $logId   The id to search a transaction on.
	 * @param   string  $column  An alternative column to select on.
	 * @param   bool    $force   Force to reload the details.
	 * @param   string  $origin  The origin of the transaction.
	 *
	 * @return 	object	The transaction details
	 *
	 * @since 	2.7
	 *
	 * @throws  \RuntimeException
	 */
	public function getDetails($logId, $column = 'id', $force = false, $origin = '')
	{
		$identifier = $column . '.' . $logId;

		if ($force || !array_key_exists($identifier, $this->logDetails))
		{
			$query = $this->db->getQuery(true)
				->select(
					array(
						$this->db->quoteName('id'),
						$this->db->quoteName('profile_id'),
						$this->db->quoteName('quantity'),
						$this->db->quoteName('amount'),
						$this->db->quoteName('trans'),
						$this->db->quoteName('cancel_url'),
						$this->db->quoteName('return_url'),
						$this->db->quoteName('notify_url'),
						$this->db->quoteName('origin'),
						$this->db->quoteName('order_id'),
						$this->db->quoteName('order_number'),
						$this->db->quoteName('result'),
						$this->db->quoteName('card'),
						$this->db->quoteName('processed'),
						$this->db->quoteName('paymentReference'),
						$this->db->quoteName('paymentId'),
						$this->db->quoteName('date_added'),
					)
				)
				->from($this->db->quoteName('#__jdidealgateway_logs'))
				->where($this->db->quoteName($column) . ' = ' . $this->db->quote($logId));

			// Check if we need to verify origin
			if ($origin)
			{
				$query->where($this->db->quoteName('origin') . ' = ' . $this->db->quote($origin));
			}

			$this->db->setQuery($query);
			$this->logDetails[$column . '.' . $logId] = $this->db->loadObject();
		}

		return $this->logDetails[$identifier];
	}

	/**
	 * Load result message.
	 *
	 * @param   int  $logId  The ID of the log entry.
	 *
	 * @return  string  The message with replacements done.
	 *
	 * @since   2.0
	 *
	 * @throws  \Exception
	 * @throws  \RuntimeException
	 * @throws  \InvalidArgumentException
	 */
	public function getMessage($logId)
	{
		$messageType = 0;
		$text        = false;
		$articleId   = false;

		// Load the logged details
		$details = $this->getDetails($logId);

		// Load the message details
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('message_type', 'messageType'))
			->select($this->db->quoteName('message_text_id', 'articleId'))
			->select($this->db->quoteName('message_text', 'text'))
			->from($this->db->quoteName('#__jdidealgateway_messages'))
			->where($this->db->quoteName('orderstatus') . ' = ' . $this->db->quote($details->result))
			->where($this->db->quoteName('profile_id') . ' = ' . (int) $details->profile_id)
			->where(
				$this->db->quoteName('language') . ' IN (' . $this->db->quote(\JFactory::getLanguage()->getTag()) . ', ' . $this->db->quote('*') . ')'
			);

		$this->db->setQuery($query);
		$messageDetails = $this->db->loadAssoc();

		if (null === $messageDetails)
		{
			return '';
		}

		// Get the message details into the needed variables
		extract($messageDetails, EXTR_OVERWRITE);

		// Check if an article has been selected
		if (!$articleId)
		{
			$messageType = 0;
		}

		switch ($messageType)
		{
			case '2':
				return '';
				break;
			case '1':
				try
				{
					// Load the content route helper for the plugins
					require_once JPATH_SITE . '/components/com_content/helpers/route.php';

					// Load the article model
					require_once JPATH_SITE . '/components/com_content/models/article.php';
					$model = new \ContentModelArticle;
					$item = $model->getItem($articleId);

					$dispatcher	= \JEventDispatcher::getInstance();
					$params = \JFactory::getApplication()->getParams('com_content');
					$user = \JFactory::getUser();

					// Check the view access to the article (the model has already computed the values).
					if ($item->params->get('access-view') !== true && ($item->params->get('show_noauth') !== true && $user->get('guest')))
					{
						return '';
					}

					// Set the text
					$item->text = $item->introtext;

					if ($item->params->get('show_intro', 1) === 1)
					{
						$item->text = $item->introtext . ' ' . $item->fulltext;
					}
					elseif ($item->fulltext)
					{
						$item->text = $item->fulltext;
					}

					\JPluginHelper::importPlugin('content');
					$dispatcher->trigger('onContentPrepare', array ('com_content.article', &$item, &$params));

					// Set the message text
					$msg = $item->text;
				}
				catch (\Exception $e)
				{
					$this->log($e->getMessage(), $logId);
				}
				break;
			default:
				$msg = $text;
				break;
		}

		// Load the Addon class
		try
		{
			$addon = $this->getAddon($details->origin);
		}
		catch (\Exception $e)
		{
			$this->log($e->getMessage(), $logId);

			return false;
		}

		$find = array();
		$find[] = '{BEDRAG}';
		$find[] = '{STATUS}';
		$find[] = '{ORDERNR}';
		$find[] = '{ORDERLINK}';
		$find[] = '{OVERBOEKING_REFERENTIE}';
		$replace = array();
		$replace[] = number_format($details->amount, 2, ',', '');
		$replace[] = strtolower(\JText::_('COM_JDIDEALGATEWAY_STATUS_' . $details->result));
		$replace[] = $details->order_number;
		$link = $addon->getOrderLink($details->order_id, $details->order_number);
		$replace[] = \JHtml::_('link', $link, $link);
		$replace[] = $details->paymentReference;

		return str_ireplace($find, $replace, $msg);
	}

	/**
	 * Get the new order status.
	 *
	 * @param   string  $status  The status name.
	 *
	 * @return  string  The new status code.
	 *
	 * @since   2.0
	 */
	public function getStatusCode($status)
	{
		switch (strtolower($status))
		{
			case 'success':
			case 'authorized':
				$newstatus = $this->get('verifiedStatus');
				break;
			case 'cancel':
			case 'cancelled':
				$newstatus = $this->get('cancelledStatus');
				break;
			case 'failure':
				$newstatus = $this->get('failedStatus');
				break;
			default:
			case 'expired':
			case 'open':
			case 'not_authorized':
				$newstatus = $this->get('openStatus');
				break;
		}

		return $newstatus;
	}

	/**
	 * Check if the transaction is valid.
	 *
	 * @param   string  $status  The result of the transaction.
	 *
	 * @return  bool  True if transaction is valid | False if transaction is invalid.
	 *
	 * @since   2.0
	 */
	public function isValid($status)
	{
		switch (strtolower($status))
		{
			case 'success':
			case 'confirmed':
			case 'authorized':
			case 'transfer':
				return true;
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * Load an e-mail message.
	 *
	 * @param   string  $trigger  The name of the email trigger.
	 *
	 * @return  mixed  Mail message object if found | False if no mail message has been found.
	 *
	 * @since   2.8
	 *
	 * @throws  \RuntimeException
	 */
	public function getMailBody($trigger)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('subject') . ',' . $this->db->quoteName('body'))
			->from($this->db->quoteName('#__jdidealgateway_emails'))
			->where($this->db->quoteName('trigger') . ' = ' . $this->db->quote($trigger));
		$this->db->setQuery($query);

		if ($this->db->execute())
		{
			$mail = $this->db->loadObject();

			if ($mail)
			{
				return $mail;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get the root URL.
	 *
	 * @return  string  The URL to the site.
	 *
	 * @since   2.9.5
	 */
	public function getUrl()
	{
		$uri  = \JUri::getInstance();
		$ssl  = $uri->isSSL();
		$root = \JUri::root();

		// Check if the root already has https
		if ($ssl && strpos($root, 'https') === false)
		{
			$root = 'https' . substr($root, 4);
		}

		// Remove the cli when ran through the notify script
		if (substr($root, -4) === 'cli/')
		{
			$root = substr($root, 0, -4);
		}

		return $root;
	}

	/**
	 * Load the addon.
	 *
	 * @param   string  $origin  The extension to get the addon for.
	 *
	 * @return  \JdidealAddon  An instance of the addon.
	 *
	 * @since   4.0
	 *
	 * @throws  \RuntimeException
	 */
	public function getAddon($origin)
	{
		// Load the Addon class
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/addons/' . strtolower($origin) . '.php'))
		{
			throw new \RuntimeException(\JText::sprintf('COM_JDIDEALGATEWAY_CANNOT_LOAD_ADDON', strtolower($origin)));
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/addons/' . strtolower($origin) . '.php';
		/** @var \JdidealAddon $classname */
		$classname = 'Addon' . $origin;

		return new $classname;
	}

	/**
	 * Get the ID of the selected payment provider.
	 *
	 * @return  int  The ID of the payment provider.
	 *
	 * @since   4.0
	 */
	public function getProfileId()
	{
		return $this->profileId;
	}

	/**
	 * Get the alias of the selected payment provider.
	 *
	 * @param   int  profileId  The ID of the profile to get the alias for.
	 *
	 * @return  string  The alias of the payment provider.
	 *
	 * @since   4.0
	 */
	public function getProfileAlias($profileId)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('alias'))
			->from($this->db->quoteName('#__jdidealgateway_profiles'))
			->where($this->db->quoteName('id') . ' = ' . (int) $profileId);
		$this->db->setQuery($query);

		return $this->db->loadResult();
	}

	/**
	 * Load the PSP notifier.
	 *
	 * @param   string   $psp     The name of the payment provider to load.
	 * @param   \JInput  $jinput  The JInput object.
	 *
	 * @return  mixed  The notifier object if found | False if not found.
	 *
	 * @since   4.0
	 *
	 * @throws  \Exception
	 */
	public function loadNotifier($psp, \JInput $jinput)
	{
		$notifier = false;

		// Get the active payment provider class
		switch ($psp)
		{
			case 'advanced':
				$notifier = new Psp\Advanced($jinput);
				break;
			case 'buckaroo':
				$notifier = new Psp\Buckaroo($jinput);
				break;
			case 'ems':
				$notifier = new Psp\Ems($jinput);
				break;
			case 'ing-lite':
				$notifier = new Psp\Lite($jinput);
				break;
			case 'kassacompleet':
				$notifier = new Psp\Kassacompleet($jinput);
				break;
			case 'mollie':
				$notifier = new Psp\Mollie($jinput);
				break;
			case 'onlinekassa':
				$notifier = new Psp\Onlinekassa($jinput);
				break;
			case 'rabo-omnikassa':
				$notifier = new Psp\Omnikassa($jinput);
				break;
			case 'targetpay':
				$notifier = new Psp\Targetpay($jinput);
				break;
			case 'abn-internetkassa':
			case 'ogone':
				$notifier = new Psp\Internetkassa($jinput);
				break;
			case 'sisow':
				$notifier = new Psp\Sisow($jinput);
				break;
		}

		return $notifier;
	}
}
