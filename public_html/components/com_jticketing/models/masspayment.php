<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die(';)');

jimport('joomla.application.component.model');
jimport('joomla.database.table.user');


/**
 * Model for post processing payment
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelmasspayment extends JModelLegacy
{
	/**
	 * function to add log
	 *
	 * @param   string  $message  message to log
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addlog($message)
	{
		jimport('joomla.error.log');
		$options = "{DATE}\t{TIME}\t{USER}\t{DESC}";
		$logfilename = 'jticketing_masspayment.log';
		$path = JPATH_SITE . '/logs/';
		JLog::addLogger(array('text_file' => $logfilename, 'text_entry_format' => $options, 'text_file_path' => $path), JLog::INFO, "jticketing");

		$logEntry       = new JLogEntry('Transaction added', JLog::INFO, "jticketing");
		$logEntry->user = "";
		$logEntry->desc = $message;
		JLog::add($logEntry);
	}

	/**
	 * function to perform masspayment
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function performmasspay()
	{
		$com_params           = JComponentHelper::getParams('com_jticketing');
		$integration          = $com_params->get('integration');
		$siteadmin_comm_per   = $com_params->get('siteadmin_comm_per');
		$paypal               = $com_params->get('paypal');
		$gateways             = $com_params->get('gateways');
		$handle_transactions  = $com_params->get('handle_transactions');
		$apiuser              = $com_params->get('apiuser');
		$apipass              = $com_params->get('apipass');
		$apisign              = $com_params->get('apisign');
		$private_key_cronjob  = $com_params->get('private_key_cronjob');
		$currency             = $com_params->get('currency');
		$min_val_masspay      = $com_params->get('min_val_masspay');
		$msg                  = "<table border=\"0\" width=\"100%\">
				<tr>
					<th align=\"center\">" . JText::_('OWNER') . "</th>
					<th align=\"center\">" . JText::_('PAYOUT_AMT') . "</th>
					<th align=\"center\">" . JText::_('PAYOUT_STATUS') . "</th>
				</tr>";
		$jticketingmainhelper = new jticketingmainhelper;
		$db                   = JFactory::getDBO();
		$query                = $jticketingmainhelper->getPayeeDetails();
		$db->setQuery($query);
		$this->getPayoutFormData = $db->loadObjectList();
		$event_owners            = $this->getAllEventCreator();

		foreach ($this->getPayoutFormData as $payout)
		{
			$totalpaidamount = $jticketingmainhelper->getTotalPaidOutAmount($payout->creator);
			$total_originalamount = $payout->total_originalamount;
			$total_commission = $payout->total_commission;
			$total_need_to_pay = (float) $total_originalamount - (float) $total_commission;
			$event_owners_payout[$payout->creator]['totalpaidamount']    = (float) $totalpaidamount;
			$event_owners_payout[$payout->creator]['amount_earned']      = (float) $total_originalamount - (float) $payout->total_commission;
			$event_owners_payout[$payout->creator]['amount_need_to_pay'] = (float) $total_need_to_pay - (float) $totalpaidamount;
			$event_owners_payout[$payout->creator]['totalcommission']    = (float) $total_commission;
		}

		$nvpStr = "";
		$k      = 0;
		$this->addlog('------------------New Masspayment Data-----------');
		$masspay_enabled = 0;

		if ($siteadmin_comm_per > 0 and in_array('paypal', $gateways))
		{
			$masspay_enabled = 1;
		}

		if ($masspay_enabled == 1 and $handle_transactions == 0 and $apiuser != '' and $apipass != '' and $apisign != '' and $private_key_cronjob != '')
		{
			$arrayuniqid = $eventnamearr = $confirm = $arrayuserid = $reason = $payvaluestr = $arraypayval = "";

			for ($i = 0, $n = count($event_owners); $i < $n; $i++)
			{
				$paypal_email = '';
				$payvalue     = 0;
				$this->addlog('Owner Id Being Processed' . $event_owners[$i]->creator);

				if ($event_owners_payout[$event_owners[$i]->creator]['amount_need_to_pay'] <= 0)
				{
					$this->addlog('Amount is less than zero-' . $payvalue . ' so skip payment');
					continue;
				}

				$payee_email = $this->geEventOwneremail($event_owners[$i]->creator);
				$payee_email = trim($payee_email);

				if (empty($payee_email))
				{
					echo 'Paypal Email not Found so skip payment';
					$this->addlog('Paypal Email not Found so skip payment');
					continue;
				}

				$pusers   = JFactory::getuser($event_owners[$i]->creator);

				// Total amount earned
				$paytotal = $event_owners_payout[$event_owners[$i]->creator]['amount_earned'];

				// Total Payouts done till now
				$sumresult = $event_owners_payout[$event_owners[$i]->creator]['totalpaidamount'];

				// Total Amount need to pay now
				$payvalue = $event_owners_payout[$event_owners[$i]->creator]['amount_need_to_pay'];

				$this->addlog('Amount earned: ' . $paytotal . ' Amount paid: ' . $sumresult . ' Balance: ' . $payvalue);

				// Check minimum value for masspayment
				if ($payvalue < $min_val_masspay)
				{
					$comment = JText::sprintf('MIN_AMT_MASSPAY_ERROR', $min_val_masspay);
					$this->addlog($comment);
					continue;
				}

				// Event owner emailid
				$payvaluestr .= $payvalue . "&";
				$arraypayval[$k] = $payvalue;
				$this->addlog('Net Amount Paid-' . $payvalue);
				$paydata['creator']    = $event_owners[$i]->creator;
				$paydata['amount']     = $payvalue;
				$paydata['status']     = '0';
				$paydata['payee_name'] = $pusers->name;
				$paydata['payee_id']   = $payee_email;
				$paydata['type']       = 'event';

				// Insert Payout Data
				$insertid        = $this->insertPayoutData($paydata);
				$arrayuserid[$k] = $event_owners[$i]->creator;
				$receiverEmail   = urlencode($payee_email);
				$amount          = urlencode($payvalue);
				$uniqid          = urlencode($insertid);
				$arrayuniqid[$k] = $uniqid;
				$app      = JFactory::getApplication();
				$sitename = $app->getCfg('sitename');
				$note = JText::sprintf('MASSPAY_NOTE', date('Y-m-d H:i:s'), $sitename);
				$note = urlencode($note);
				$nvpStr .= "&L_EMAIL$k=$receiverEmail&L_Amt$k=$amount&L_NOTE$k=$note&L_UNIQUEID$k=$uniqid";
				$k++;
			}

			$this->addlog('Paypal Request string-' . $nvpStr);
			$httpParsedResponseAr = $this->PPHttpPost('MassPay', $nvpStr);
			$keys = array_keys($httpParsedResponseAr), array_values($httpParsedResponseAr);
			$new_array = array_map(create_function('$key, $value', 'return $key."=".$value." & ";'), $keys);
			$Responsestr = implode($new_array);
			$this->addlog('Paypal Response string-' . $Responsestr);

			if ("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
			{
				for ($l = 0; $l < count($arrayuniqid); $l++)
				{
					$obj                = new stdClass;
					$obj->id            = $arrayuniqid[$l];
					$obj->transction_id = $httpParsedResponseAr["CORRELATIONID"];
					$obj->status        = '1';
					$resp = $this->updatePayoutData($obj);

					if ($httpParsedResponseAr["ACK"] == "FAILURE")
					{
						$reason .= urldecode($httpParsedResponseAr['L_SHORTMESSAGE' . $l]) . "&";
					}
					else
					{
						$reason .= '--';
					}
				}
			}

			if (!empty($arrayuserid))
			{
				for ($j = 0; $j < count($arrayuserid); $j++)
				{
					$name = JFactory::getUser($arrayuserid[$j])->name;
					$msg .= "<tr>
								<td align=\"center\">{$name}</td>
								<td align=\"center\">{$arraypayval[$j]}</td>
								<td align=\"center\">" . strtoupper($httpParsedResponseAr["ACK"]) . "</td>";
					$msg .= "<td>" . urldecode(strtoupper($httpParsedResponseAr["L_LONGMESSAGE0"])) . "</td>";
					$msg .= "</tr>";
				}

				$msg .= "</table>";
			}
			else
			{
				$msg = "<table><tr><td>" . JText::_('NO_USERS_PROCESS') . "</td></tr></table>";
			}
		}
		else
		{
			$this->addlog('------------------No Masspayment enabled-----------');
			$msg = "<table><tr><td>" . JText::_('No Masspayment enabled') . "</td></tr></table>";
		}

		return $msg;
	}

	/**
	 * Method to post data
	 *
	 * @param   string  $methodName_  methodName_
	 * @param   string  $nvpStr_      coupon_code
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function PPHttpPost($methodName_, $nvpStr_)
	{
		$api = $this->getApiDetails();

		if (!$api)
		{
			echo JTEXT::_('JT_MASS_PAY_ERR');
			die;
		}

		$API_UserName  = $api['apiuser'];
		$API_Password  = $api['apipass'];
		$API_Signature = $api['apisign'];
		$API_Endpoint  = $api['apiend'];
		$version       = $api['apiv'];

		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);

		// Set the API operation, version, and API signature in the request.
		$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";

		// Set the request as a POST FIELD for curl.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

		// Get response from the server.
		$httpResponse = curl_exec($ch);

		if (!$httpResponse)
		{
			echo "line no 61 file payment.php";
			exit("$methodName_ failed: " . curl_error($ch) . '(' . curl_errno($ch) . ')');
		}

		// Extract the response details.
		$httpResponseAr = explode("&", $httpResponse);
		$httpParsedResponseAr = array();

		foreach ($httpResponseAr as $i => $value)
		{
			$tmpAr = explode("=", $value);

			if (sizeof($tmpAr) > 1)
			{
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}

		if ((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr))
		{
			exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
		}

		return $httpParsedResponseAr;
	}

	/**
	 * Method to get masspayment account info
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getApiDetails()
	{
		$com_params = JComponentHelper::getParams('com_jticketing');
		$sandbox    = $com_params->get('sandbox');
		$apiend = 'https://api-3t.paypal.com/nvp';

		if ($sandbox == 1)
		{
			$apiend = 'https://api-3t.sandbox.paypal.com/nvp';
		}

		$masspay_config = array(
			'apiuser' => $com_params->get('apiuser'),
			'apipass' => $com_params->get('apipass'),
			'apisign' => $com_params->get('apisign'),
			'apiend' => $apiend,
			'apiv' => $com_params->get('apiv')
		);
		$var            = $masspay_config;

		return $var;
	}

	/**
	 * Insert payout data
	 *
	 * @param   array  $paydata  post data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function insertPayoutData($paydata)
	{
		$db              = JFactory::getDBO();
		$res             = new stdClass;
		$res->id         = '';
		$res->user_id    = $paydata['creator'];
		$res->date       = date("Y-m-d H:i:s");
		$res->amount     = $paydata['amount'];
		$res->status     = $paydata['status'];
		$res->payee_name = $paydata['payee_name'];
		$res->payee_id   = $paydata['payee_id'];
		$res->ip_address = $_SERVER["REMOTE_ADDR"];
		$res->type       = $paydata['type'];

		if (!$db->insertObject('#__jticketing_ticket_payouts', $res, 'id'))
		{
			echo $db->stderr();

			return false;
		}

		return $db->insertid();
	}

	/**
	 * Update payout data
	 *
	 * @param   object  $obj  post data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updatePayoutData($obj)
	{
		$db = JFactory::getDBO();

		if (!$db->updateObject('#__jticketing_ticket_payouts', $obj, 'id'))
		{
			echo $db->stderr();

			return false;
		}

		return true;
	}

	/**
	 * Get all event creator
	 *
	 * @return  object  list of event creator
	 *
	 * @since   1.0
	 */
	public function getAllEventCreator()
	{
		$db                   = JFactory::getDBO();
		$jticketingmainhelper = new jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$source = 'com_community';
		}
		elseif ($integration == 2)
		{
			$source = 'com_jticketing';
		}
		elseif ($integration == 3)
		{
			$source = 'com_jevents';
		}
		elseif ($integration == 4)
		{
			$source = 'com_easysocial';
		}

		$query = "SELECT  xref.userid AS creator,xref.paypal_email AS paypal_email FROM #__jticketing_order AS ordertable ,
		 #__jticketing_integration_xref AS xref WHERE xref.id=ordertable.event_details_id
		AND ordertable.status = 'C' AND  xref.source='" . $source . "' GROUP BY xref.userid";
		$db->setQuery($query);
		$eventowner = $db->loadObjectList();

		return $eventowner;
	}

	/**
	 * Get event owner email
	 *
	 * @param   int  $creator  user id of event owner
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function geEventOwneremail($creator)
	{
		$db                   = JFactory::getDBO();
		$jticketingmainhelper = new jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$source = 'com_community';
		}
		elseif ($integration == 2)
		{
			$source = 'com_jticketing';
		}
		elseif ($integration == 3)
		{
			$source = 'com_jevents';
		}
		elseif ($integration == 4)
		{
			$source = 'com_easysocial';
		}

		$query = "SELECT  max(id) AS id,xref.userid AS creator,xref.paypal_email AS paypal_email FROM
		#__jticketing_integration_xref AS xref	WHERE  paypal_email<>'' AND  xref.userid=" . $creator . " AND xref.source='" . $source . "'";
		$db->setQuery($query);
		$paypalemail = $db->loadObject();

		return $paypalemail->paypal_email;
	}
}
