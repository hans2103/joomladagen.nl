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
use Jdideal\Psp\Kassacompleet;
use Jdideal\Psp\Mollie;
use Jdideal\Psp\Onlinekassa;
use Jdideal\Psp\Sisow;
use Jdideal\Psp\Targetpay;

defined('_JEXEC') or die;

/**
 * JD iDEAL Controller.
 *
 * @package  JDiDEAL
 * @since    3.0
 */
class JdidealgatewayControllerCheckIdeal extends JControllerLegacy
{
	/**
	 * Process the transaction request and send the customer to the bank
	 *
	 * Internetkassa goes directly to the bank.
	 *
	 * @return  mixed  Void if customer is redirected | False if an error occurred.
	 *
	 * @since   3.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 * @throws  Mollie_API_Exception
	 */
	public function send()
	{
		// Load the helper
		$jdideal = new Gateway;
		$jinput = JFactory::getApplication()->input;

		switch ($jdideal->psp)
		{
			case 'advanced':
				/** @var Advanced $notifier */
				$notifier = new Advanced($jinput);
				$notifier->sendPayment($jdideal);
				break;
			case 'mollie':
				/** @var Mollie $notifier */
				$notifier = new Mollie($jinput);
				$notifier->sendPayment($jdideal);
				break;
			case 'targetpay':
				/** @var Targetpay $notifier */
				$notifier = new Targetpay($jinput);
				$notifier->sendPayment($jdideal);
				break;
			case 'sisow':
				/** @var Sisow $notifier */
				$notifier = new Sisow($jinput);
				$notifier->sendPayment($jdideal);
				break;
			case 'buckaroo':
				/** @var Buckaroo $notifier */
				$notifier = new Buckaroo($jinput);
				$notifier->sendPayment($jdideal);
				break;
			case 'kassacompleet':
				/** @var Kassacompleet $notifier */
				$notifier = new Kassacompleet($jinput);
				$notifier->sendPayment($jdideal);
				break;
			case 'onlinekassa':
				/** @var Onlinekassa $notifier */
				$notifier = new Onlinekassa($jinput);
				$notifier->sendPayment($jdideal);
				break;
		}

		// End the output
		JFactory::getApplication()->close();
	}
}
