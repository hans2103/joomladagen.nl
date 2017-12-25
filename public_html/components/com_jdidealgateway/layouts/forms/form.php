<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

use Jdideal\Gateway;
use Jdideal\Psp\Advanced;
use Jdideal\Psp\Buckaroo;
use Jdideal\Psp\Easy;
use Jdideal\Psp\Ems;
use Jdideal\Psp\Internetkassa;
use Jdideal\Psp\Kassacompleet;
use Jdideal\Psp\Lite;
use Jdideal\Psp\Mollie;
use Jdideal\Psp\Omnikassa;
use Jdideal\Psp\Onlinekassa;
use Jdideal\Psp\Sisow;
use Jdideal\Psp\Targetpay;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * The display data can contain the following parameters
 *
 * amount         The price to be paid
 * order_number   The order number
 * order_id       The order ID
 * origin         The name of the extension calling this form
 * return_url     The URL to send any status to
 * notify_url     The URL to send the notification to
 * cancel_url     The URL to send a cancelled status to
 * email          The customers email
 * payment_method The selected payment method to use
 * profileAlias   The profile alias to use
 * custom_html    Extra text to show on the payment form
 * silent         Set to true to send the customer directly to the payment provider and hide the form
 * banks          The value of the selected bank in case of an iDEAL payment
 */

// Load the language file
$language = JFactory::getLanguage();
$language->load('com_jdidealgateway', JPATH_SITE . '/components/com_jdidealgateway', 'en-GB', true);
$language->load('com_jdidealgateway', JPATH_SITE . '/components/com_jdidealgateway', $language->getDefault(), true);
$language->load('com_jdidealgateway', JPATH_SITE . '/components/com_jdidealgateway', null, true);

// Data is stored in an array called $displayData, let's put it in a regular array
/** @var array $displayData */
$data = (array) $displayData['data'];

// Turn it into an object
$data = ArrayHelper::toObject($data);

// Check if we have a profile alias, otherwise we set one
if (!isset($data->profileAlias))
{
	$data->profileAlias = null;
}

// Set the autoloader, we may come from a place that never heard about us
JLoader::registerNamespace('Jdideal', JPATH_LIBRARIES);

// Load the basics
$jinput = JFactory::getApplication()->input;
$jdideal = new Gateway;

// Load the profile if needed
if ('' !== $data->profileAlias && null !== $data->profileAlias)
{
	$jdideal->loadConfiguration($data->profileAlias);
}

// Load the component parameters
$params = JComponentHelper::getParams('com_jdidealgateway');

// Check if ideal is configured
if ($jdideal->psp)
{
	// Check if data is filled
	if (is_object($data) && isset($data->order_id) && '' !== $data->order_id)
	{
		// Fix the amount in case it is in the format of 1,234.56
		$clean        = str_replace(",", ".", $data->amount);
		$lastpos      = strrpos($clean, '.');
		$data->amount = str_replace('.', '', substr($clean, 0, $lastpos)) . substr($clean, $lastpos);

		// Check if the amount has a maximum of 2 digits
		$data->amount = round($data->amount, 2);

		// Check if the order number is not empty
		if ('' === $data->order_number || null === $data->order_number)
		{
			$data->order_number = $data->order_id;
		}

		// Check if the quantity is set
		if (!isset($data->quantity))
		{
			$data->quantity = 1;
		}

		// Check if custom HTML is set
		if (!isset($data->custom_html))
		{
			$data->custom_html = '';
		}

		// Check if need to go silent
		if (!isset($data->silent))
		{
			$data->silent = false;
		}

		// Check if have a bank
		if (!isset($data->banks))
		{
			$data->banks = '';
		}

		// Check if we have a notify URL
		if (!isset($data->notify_url))
		{
			$data->notify_url = '';
		}

		// Check if we have a cancel URL
		if (!isset($data->cancel_url))
		{
			$data->cancel_url = '';
		}

		// Check if we have a return URL
		if (!isset($data->return_url))
		{
			$data->return_url = '';
		}

		// Check if we have an email address
		if (!isset($data->email))
		{
			$data->email = '';
		}

		// Set the root URL
		$root = $jdideal->getUrl();

		// Store the information in the log table
		$db    = JFactory::getDbo();
		$date = JHtml::_('date', 'now', 'Y-m-d H:i:s', false);
		$query = $db->getQuery(true);
		$query->insert('#__jdidealgateway_logs')
			->columns(
				$db->quoteName(
					array(
						'profile_id',
						'order_id',
						'order_number',
						'quantity',
						'amount',
						'origin',
						'return_url',
						'cancel_url',
						'notify_url',
						'date_added',
					)
				)
			)
			->values(
				$db->quote($jdideal->getProfileId()) . ', ' .
				$db->quote($data->order_id) . ', ' .
				$db->quote($data->order_number) . ', ' .
				$db->quote($data->quantity) . ', ' .
				$db->quote($data->amount) . ', ' .
				$db->quote($data->origin) . ', ' .
				$db->quote($data->return_url) . ', ' .
				$db->quote($data->cancel_url) . ', ' .
				$db->quote($data->notify_url) . ', ' .
				$db->quote($date)
			);
		$db->setQuery($query)->execute();
		$data->logid = $db->insertid();

		// Check if we have an empty order number, if so use our own log ID
		if (!$data->order_number)
		{
			$data->order_number = $data->logid;
		}

		// Send the email to the manager if set
		if ($params->get('inform_email', true))
		{
			$config = JFactory::getConfig();
			$from = $config->get('mailfrom');
			$fromname = $config->get('fromname');
			$mail = JFactory::getMailer();

			// Construct the body
			$mail_tpl = $jdideal->getMailBody('admin_inform_email');

			if ($mail_tpl)
			{
				$find = array();
				$find[] = '{BEDRAG}';
				$find[] = '{ORDERNR}';
				$find[] = '{ORDERID}';
				$replace = array();
				$replace[] = number_format($data->amount, 2, ',', '.');
				$replace[] = $data->order_number;
				$replace[] = $data->order_id;
				$body = str_ireplace($find, $replace, $mail_tpl->body);

				// Construct the subject
				$subject = str_ireplace($find, $replace, $mail_tpl->subject);

				// Send the e-mail
				$emailtos = explode(',', $params->get('jdidealgateway_emailto'));

				if (!empty($emailtos))
				{
					foreach ($emailtos as $email)
					{
						$mail->clearAddresses();
						$mail->sendMail($from, $fromname, $email, $subject, $body, true);
					}
				}
			}
		}

		// Load the form based on active iDEAL
		switch ($jdideal->psp)
		{
			case 'advanced':
				$psp = new Advanced($jinput);
				$output = $psp->getForm($jdideal, $data);

				$layout = new JLayoutFile('forms.psp', null, array('component' => 'com_jdidealgateway'));
				echo $layout->render(array('jdideal' => $jdideal, 'data' => $data, 'root' => $root, 'output' => $output));
				break;
			case 'rabo-omnikassa':
				$psp = new Omnikassa($jinput);
				$data = $psp->getForm($jdideal, $data);

				// Get the URL
				$url = $psp->getLiveUrl();

				if ($jdideal->get('testmode') === '1')
				{
					$url = $psp->getTestUrl();
				}

				$layout = new JLayoutFile('forms.rabo-omnikassa', null, array('component' => 'com_jdidealgateway'));
				echo $layout->render(array('jdideal' => $jdideal, 'data' => $data, 'root' => $root, 'url' => $url));
				break;
			case 'mollie':
				$psp = new Mollie($jinput);
				$output = $psp->getForm($jdideal, $data);

				$layout = new JLayoutFile('forms.psp', null, array('component' => 'com_jdidealgateway'));
				echo $layout->render(array('jdideal' => $jdideal, 'data' => $data, 'root' => $root, 'output' => $output));
				break;
			case 'targetpay':
				$psp = new Targetpay($jinput);
				$output = $psp->getForm($jdideal, $data);

				$layout = new JLayoutFile('forms.' . $output->file, null, array('component' => 'com_jdidealgateway'));
				echo $layout->render(array('jdideal' => $jdideal, 'data' => $data, 'root' => $root, 'output' => $output));
				break;
			case 'sisow':
				$psp = new Sisow($jinput);
				$output = $psp->getForm($jdideal, $data);

				$layout = new JLayoutFile('forms.psp', null, array('component' => 'com_jdidealgateway'));
				echo $layout->render(array('jdideal' => $jdideal, 'data' => $data, 'root' => $root, 'output' => $output));
				break;
			case 'buckaroo':
				$psp = new Buckaroo($jinput);
				$output = $psp->getForm($jdideal, $data);

				$layout = new JLayoutFile('forms.psp', null, array('component' => 'com_jdidealgateway'));
				echo $layout->render(array('jdideal' => $jdideal, 'data' => $data, 'root' => $root, 'output' => $output));
				break;
			case 'rabo-lite':
			case 'ing-lite':
				$psp = new Lite($jinput);
				$data = $psp->getForm($jdideal, $data);

				// Get the URL
				$url = $psp->getLiveUrl();

				if ($jdideal->get('testmode') === '1')
				{
					$url = $psp->getTestUrl();
				}

				$layout = new JLayoutFile('forms.lite', null, array('component' => 'com_jdidealgateway'));
				echo $layout->render(array('jdideal' => $jdideal, 'data' => $data, 'root' => $root, 'url' => $url));
				break;
			case 'abn-internetkassa':
			case 'ogone':
				$psp = new Internetkassa($jinput);
				$data = $psp->getForm($jdideal, $data);

				// Get the URL
				$url = $psp->getLiveUrl($jdideal->psp);

				if ($jdideal->get('testmode') === '1')
				{
					$url = $psp->getTestUrl($jdideal->psp);
				}

				$layout = new JLayoutFile('forms.internetkassa', null, array('component' => 'com_jdidealgateway'));
				echo $layout->render(array('jdideal' => $jdideal, 'data' => $data, 'root' => $root, 'url' => $url));
				break;
			case 'abn-lite':
				$psp = new Easy($jinput);
				$data = $psp->getForm($jdideal, $data);

				// Get the URL
				$url = $psp->getLiveUrl();

				if ($jdideal->get('testmode') === '1')
				{
					$url = $psp->getTestUrl();
				}

				$layout = new JLayoutFile('forms.abn-lite', null, array('component' => 'com_jdidealgateway'));
				echo $layout->render(array('jdideal' => $jdideal, 'data' => $data, 'root' => $root, 'url' => $url));
				break;
			case 'kassacompleet':
				$psp = new Kassacompleet($jinput);
				$output = $psp->getForm($jdideal, $data);

				$layout = new JLayoutFile('forms.psp', null, array('component' => 'com_jdidealgateway'));
				echo $layout->render(array('jdideal' => $jdideal, 'data' => $data, 'root' => $root, 'output' => $output));
				break;
			case 'ems':
				$psp = new Ems($jinput);
				$output = $psp->getForm($jdideal, $data);

				// Get the URL
				$url = $psp->getLiveUrl();

				if ((int) $jdideal->get('testmode') === 1)
				{
					$url = $psp->getTestUrl();
				}

				$layout = new JLayoutFile('forms.ems', null, array('component' => 'com_jdidealgateway'));
				echo $layout->render(array('jdideal' => $jdideal, 'data' => $data, 'root' => $root, 'url' => $url));
				break;
			case 'onlinekassa':
				$psp = new Onlinekassa($jinput);
				$output = $psp->getForm($jdideal, $data);

				$layout = new JLayoutFile('forms.psp', null, array('component' => 'com_jdidealgateway'));
				echo $layout->render(array('jdideal' => $jdideal, 'data' => $data, 'root' => $root, 'output' => $output));
				break;
		}
	}
	else
	{
		if (!is_object($data))
		{
			echo JText::sprintf('COM_JDIDEALGATEWAY_DATA_NOT_OBJECT', gettype($data));
		}
		elseif (!isset($data->order_id))
		{
			echo JText::_('COM_JDIDEALGATEWAY_DATA_HAS_NO_ORDER_ID');
		}
		elseif ('' === $data->order_id)
		{
			echo JText::_('COM_JDIDEALGATEWAY_DATA_HAS_EMPTY_ORDER_ID');
		}
		else
		{
			echo JText::_('COM_JDIDEALGATEWAY_DATA_FAILURE');
		}
	}
}
else
{
	echo JText::_('COM_JDIDEALGATEWAY_NO_IDEAL_CONFIGURED');
}
