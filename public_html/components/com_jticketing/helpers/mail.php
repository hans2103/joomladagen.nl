<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
jimport('joomla.user.helper');
jimport('joomla.html.html');
jimport('joomla.html.parameter');
jimport('joomla.utilities.date');
jimport('techjoomla.tjnotifications.tjnotifications');
jimport('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models');

use Dompdf\Dompdf;

/**
 * mail helper class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingMailHelper
{
	/**
	 * Method to mail pdf to buyer(also populates data)
	 *
	 * @param   array  $ticketid  id of jticketing_order table
	 * @param   int    $type      after order completed or beforeorder
	 *
	 * @return  void
	 */
	public static function sendmailnotify($ticketid, $type = '')
	{
		$com_params           = JComponentHelper::getParams('com_jticketing');
		$mail_to              = $com_params->get('mail_to');
		$replytoemail         = $com_params->get('reply_to');
		$onlyInvoiceToCreator = $com_params->get('only_invoice_to_event_creator');
		$jticketingmainhelper = new Jticketingmainhelper;
		$where                = '';
		$db                   = JFactory::getDBO();
		$buyer                = JFactory::getUser();
		$app                  = JFactory::getApplication();
		$mailfrom             = $app->getCfg('mailfrom');
		$fromname             = $app->getCfg('fromname');
		$sitename             = $app->getCfg('sitename');
		$email = '';

		if (isset($replytoemail))
		{
			$replytoemail = explode(",", $replytoemail);
		}

		$integration          = $jticketingmainhelper->getIntegration();
		$source               = $jticketingmainhelper->getSourceName($integration);
		$event_integration_id = $jticketingmainhelper->getEventID_from_OrderID($ticketid);
		$eventid              = $jticketingmainhelper->getEventID_FROM_INTEGRATIONID($event_integration_id, $source);
		$orderitems           = $jticketingmainhelper->getOrderItemsID($ticketid);
		$eventpathmodel       = JPATH_SITE . '/components/com_jticketing/models/event.php';

		if (!class_exists('JticketingModelEvent'))
		{
			JLoader::register('JticketingModelEvent', $eventpathmodel);
			JLoader::load('JticketingModelEvent');
		}

		// Get ticket fields of ticket
		foreach ($orderitems AS $orderitem)
		{
			$row = $jticketingmainhelper->getticketDetails($eventid, $orderitem->order_items_id);

			if (!$app->isSite())
			{
				if (!empty($row->email))
				{
					$email = $row->email;
				}
			}
			else
			{
				if (!$buyer->id)
				{
					if (!empty($row->email))
					{
						$email = $row->email;
					}
				}
				else
				{
					$email = $buyer->email;
				}
			}

			$creator_id = $jticketingmainhelper->getEventCreator($row->eid);

			if ($creator_id == '')
			{
				$creator_id = $row->creator;
			}

			$db = JFactory::getDBO();
			$query = "SELECT email FROM #__users WHERE id = " . $creator_id;
			$db->setQuery($query);
			$event_creator_mail = $db->loadResult();

			$link        = $jticketingmainhelper->getEventlink($row->eid);
			$data        = array();
			$data        = $jticketingmainhelper->afterordermail($row, $ticketid, $link);
			$billingdata = $jticketingmainhelper->getbillingdata($ticketid);

			$buyeremail  = $billingdata->user_email;

			if (empty($buyeremail))
			{
				$buyeremail = $row->email;
			}

			// Chk whom to send email
			if (in_array('site_admin', $mail_to))
			{
				$toemail['site_admin'] = trim($mailfrom);
			}

			if (in_array('event_creator', $mail_to) && $onlyInvoiceToCreator == '0')
			{
				$toemail['event_creator'] = trim($event_creator_mail);
			}

			if (in_array('event_buyer', $mail_to))
			{
				$toemail['event_buyer'] = trim($buyeremail);
			}

			$toemail = array_unique($toemail);

			// Load other libraries since we are generating PDF there are some issues after that
			// Add config for from address
			$headers       = '';
			$subject       = $data['subject'];
			$message       = $data['message'];
			$dispatcher    = JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');
			$dispatcher->trigger('jt_OnBeforeTicketEmail', array($toemail, $data['subject'], $data['message']));

			$client = "com_jticketing";
			$key    = "e-tickets";

			$recipients = '';

			$row->site = $sitename;
			$model       = JModelAdmin::getInstance('EventForm', 'JticketingModel');
			$eventDetails = $model->getItem($row->eid);
			$eventDetails->link = $link;

			$replacements         = new stdClass;
			$replacements->event  = $eventDetails;
			$replacements->ticket = $row;

			if (!empty($row->customfields_ticket))
			{
				$replacements->buyer  = $row->customfields_ticket;
			}

			$options = new JRegistry;
			$options->set('subject', $row);

			$options->set('guestEmails', $toemail);
			$options->set('from', $mailfrom);
			$options->set('fromname', $fromname);

			if (!empty($data['pdf']))
			{
				$options->set('attachment', $data['pdf']);
				$result = Tjnotifications::send($client, $key, $recipients, $replacements, $options);

				// Delete unwanted pdf and ics files stored in tmp folder
				if ($result)
				{
					$jticketingmainhelper->deleteunwantedfiles($data);
				}
			}
			else
			{
				$result = Tjnotifications::send($client, $key, $recipients, $replacements, $options);
			}
		}

		// Update mailsent flag if email sent
		if ($result == 1)
		{
			$obj                    = new StdClass;
			$obj->id                = $ticketid;

			if (in_array('event_buyer', $mail_to))
			{
				$obj->ticket_email_sent = 1;
			}
			else
			{
				$obj->ticket_email_sent = 0;
			}

			if ($db->updateObject('#__jticketing_order', $obj, 'id'))
			{
			}
		}

		return $result;
	}

	/**
	 * Method to sendMail
	 *
	 * @param   string  $from     from
	 * @param   string  $fromnm   fromname
	 * @param   string  $recept   recipient
	 * @param   html    $subject  subject
	 * @param   string  $body     body
	 * @param   string  $mode     mode
	 * @param   string  $cc       cc
	 * @param   string  $bcc      bcc
	 * @param   string  $attach   attachment
	 * @param   string  $repto    repto
	 * @param   string  $rpnm     replytoname
	 * @param   string  $headr    headers
	 *
	 * @return  boolean  true/false
	 */
	public static function sendMail($from, $fromnm, $recept, $subject, $body,
		$mode, $cc = '', $bcc = '', $attach = '', $repto = '', $rpnm = '', $headr = '')
	{
		// Get a JMail instance
		try
		{
			$mail = JFactory::getMailer(true);
			$mail->setSender(array($from, $fromnm));
			$mail->setSubject($subject);
			$mail->setBody($body);

			// Are we sending the email as HTML?
			if ($mode)
			{
				$mail->IsHTML(true);
			}

			if (!empty($cc))
			{
				$mail->addCC($cc);
			}

			if (!empty($recept))
			{
				$mail->addRecipient($recept);
			}

			if (!empty($bcc))
			{
				$mail->addBCC($bcc);
			}

			if (!empty($attach))
			{
				$mail->addAttachment($attach);
			}

			// Take care of reply email addresses
			if (is_array($repto))
			{
				$numReplyTo = count($repto);

				for ($i = 0; $i < $numReplyTo; $i++)
				{
					if (version_compare(JVERSION, '3.0', 'ge'))
					{
						$mail->addReplyTo($repto[$i], $rpnm[$i]);
					}
					else
					{
						$mail->addReplyTo(array($repto[$i], $rpnm[$i]));
					}
				}
			}
			elseif (!empty($repto))
			{
				if (version_compare(JVERSION, '3.0', 'ge'))
				{
					$mail->addReplyTo($repto, $rpnm);
				}
				else
				{
					$mail->addReplyTo(array($repto, $rpnm));
				}
			}

			if ($mail->Send())
			{
				return 1;
			}

			return 0;
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			JFactory::getApplication()->enqueueMessage($msg, 'error');
		}
	}

	/**
	 * Order status Email change
	 *
	 * @param   integer  $order_id  order_id for successed
	 * @param   string   $status    step no
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function sendOrderStatusEmail($order_id, $status)
	{
		$input                = JFactory::getApplication()->input;
		$post                 = $input->post;
		$ticketid             = $post->get('ticketid', '', 'STRING');
		$jticketingmainhelper = new jticketingmainhelper;
		$statusdet            = $jticketingmainhelper->getPaymentStatus($status);
		$com_params           = JComponentHelper::getParams('com_jticketing');
		$mail_to              = $com_params->get('mail_to');
		$orderinfo            = $jticketingmainhelper->getorderinfo($order_id);
		$buyer                = JFactory::getUser();
		$app                  = JFactory::getApplication();
		$mailfrom             = $app->getCfg('mailfrom');
		$fromname             = $app->getCfg('fromname');
		$sitename             = $app->getCfg('sitename');

		// Chk whom to send email
		if (in_array('site_admin', $mail_to))
		{
			$toemail[] = trim($mailfrom);
		}

		if (in_array('event_creator', $mail_to))
		{
			if (!empty($orderinfo['eventinfo']->email))
			{
				$toemail[] = trim($orderinfo['eventinfo']->email);
			}
			elseif (!empty($orderinfo['eventinfo']->creator))
			{
				$toemail[] = trim($orderinfo['eventinfo']->creator);
			}
		}

		if (in_array('event_buyer', $mail_to))
		{
			if (!empty($orderinfo['order_info']['0']->user_email))
			{
				$toemail[] = trim($orderinfo['order_info']['0']->user_email);
			}
		}

		$toemail_bcc = '';
		$dispatcher  = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$resp = $dispatcher->trigger('jt_OnBeforeOrderStatusChangeEmail', array($orderinfo));

		if (!empty($resp['0']))
		{
			$toemail_bcc = $resp['0'];
		}

		$client = "com_jticketing";
		$key    = "any_order_status";

		$recipients = '';

		$replacements                            = new stdClass;
		$orderinfo['order_info']['0']->newStatus = $statusdet;
		$replacements->order                     = $orderinfo['order_info']['0'];

		$orderinfo['eventinfo']->site = $sitename;
		$replacements->event          = $orderinfo['eventinfo'];

		$options = new JRegistry;
		$options->set('subject', $orderinfo['eventinfo']);
		$options->set('cc', $toemail_bcc);
		$options->set('guestEmails', $toemail);
		$options->set('from', $mailfrom);
		$options->set('fromname', $fromname);

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);
	}

	/**
	 * Send invoice email after payment
	 *
	 * @param   string  $id  id in jticketing_order
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function sendInvoiceEmail($id)
	{
		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$TjGeoHelper          = new TjGeoHelper;
		$db                   = JFactory::getDBO();
		$com_params           = JComponentHelper::getParams('com_jticketing');
		$mail_to              = $com_params->get('mail_to');
		$jticketingmainhelper = new jticketingmainhelper;
		$orderItemid          = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=orders');
		$jinput               = JFactory::getApplication()->input;
		$order                = $jticketingmainhelper->getorderinfo($id);
		$order_currency       = $com_params->get('currency');

		$paymentStatus['P']   = JText::_('JT_PSTATUS_PENDING');
		$paymentStatus['C']   = JText::_('JT_PSTATUS_COMPLETED');
		$paymentStatus['D']   = JText::_('JT_PSTATUS_DECLINED');
		$paymentStatus['E']   = JText::_('JT_PSTATUS_FAILED');
		$paymentStatus['UR']  = JText::_('JT_PSTATUS_UNDERREVIW');
		$paymentStatus['RF']  = JText::_('JT_PSTATUS_REFUNDED');
		$paymentStatus['CRV'] = JText::_('JT_PSTATUS_CANCEL_REVERSED');
		$paymentStatus['RV']  = JText::_('JT_PSTATUS_REVERSED');

		if (isset($order['eventinfo']->created_by))
		{
			$order['eventinfo']->created_by = trim($order['eventinfo']->created_by);
			$order['eventinfo']->created_by = (int) ($order['eventinfo']->created_by);
			$query = "SELECT email
				FROM #__users where id=" . $order['eventinfo']->created_by;
			$db->setQuery($query);
			$creator_email = $db->loadResult();
		}

		$app                  = JFactory::getApplication();
		$sitename             = $app->getCfg('sitename');
		$eventtitle           = "";
		$mailfrom             = $app->getCfg('mailfrom');
		$fromname             = $app->getCfg('fromname');
		$eventinfo            = $order['eventinfo'];

		if (isset($order['eventinfo']->title))
		{
			$eventtitle = $order['eventinfo']->title;
		}

		$orderinfo        = $order['order_info'];
		$orderitems       = $order['items'];
		$orders_site      = 1;
		$orders_email     = 1;
		$order_authorized = 1;

		if (in_array('event_buyer', $mail_to))
		{
			if ($orderinfo[0]->address_type == 'BT')
			{
				$uemail = $billemail[] = $orderinfo[0]->user_email;
			}
			elseif ($orderinfo[1]->address_type == 'BT')
			{
				$uemail = $billemail[] = $orderinfo[1]->user_email;
			}
		}

		$guest_email = '';

		// Add invoice link with the html
		if (!$orderinfo[0]->user_id && $com_params->get('allow_buy_guest'))
		{
			$guest_email = "&email=" . md5($uemail);
		}

		if (!JFactory::getUser()->id && $com_params->get('guest'))
		{
			$jinput->set('email', md5($billemail));
		}

		if (in_array('event_creator', $mail_to) and isset($creator_email))
		{
			$billemail[] = $creator_email;
		}

		if (in_array('site_admin', $mail_to))
		{
			$billemail[] = $mailfrom;
		}

		$billemail        = array_unique($billemail);
		$currenturl_base  = "index.php?option=com_jticketing&view=orders&layout=order";
		$currenturl_base .= $guest_email . "&orderid=" . $orderinfo[0]->orderid_with_prefix;
		$currenturl_base .= "&processor=" . $orderinfo[0]->processor . "&Itemid=" . $orderItemid;
		$currentUrl       = JUri::root() . substr(JRoute::_($currenturl_base, false), strlen(JUri::base(true)) + 1);

		if ($app->isAdmin())
		{
			$app        = JApplication::getInstance('site');
			$router     = $app->getRouter();
			$uri        = $router->build($currentUrl);
			$currentUrl = $uri->toString();
		}

		if (isset($orderinfo[0]->coupon_code))
		{
			$coupon_code             = trim($orderinfo[0]->coupon_code);
			$total_amount_after_disc = $orderinfo[0]->original_amount;

			if ($orderinfo[0]->coupon_discount > 0)
			{
				$total_amount_after_disc = $total_amount_after_disc - $orderinfo[0]->coupon_discount;
			}
		}

		if (isset($orderinfo[0]->order_tax) and $orderinfo[0]->order_tax > 0)
		{
			$tax_json                 = $orderinfo[0]->order_tax_details;
			$tax_arr                  = json_decode($tax_json, true);
			$orderinfo[0]->taxPercent = $tax_arr['percent'];
		}

		$client     = "com_jticketing";
		$key        = "payment";
		$recipients = '';

		$replacements = new stdclass;
		$orderinfo[0]->newStatus  = $paymentStatus[$orderinfo[0]->status];
		$orderinfo[0]->amountDisc = $jticketingmainhelper->getFromattedPrice(number_format(($total_amount_after_disc), 2), $order_currency);
		$orderinfo[0]->taxAmount  = $jticketingmainhelper->getFromattedPrice(number_format(($orderinfo[0]->order_tax), 2), $order_currency);
		$orderinfo[0]->state      = $TjGeoHelper->getRegionNameFromId($orderinfo[0]->state_code);
		$orderinfo[0]->country    = $TjGeoHelper->getCountryNameFromId($orderinfo[0]->country_code);
		$replacements->order      = $orderinfo[0];

		$orderitems[0]->currencyPrice = $jticketingmainhelper->getFromattedPrice(number_format(($orderitems[0]->price), 2), $order_currency);
		$totalprice                   = $orderitems[0]->ticketcount * $orderitems[0]->price;
		$orderitems[0]->totalPrice    = $jticketingmainhelper->getFromattedPrice(number_format(($totalprice), 2), $order_currency);
		$replacements->ticket         = $orderitems[0];

		$eventinfo->site     = $sitename;
		$replacements->event = $eventinfo;

		$options = new JRegistry;
		$options->set('subject', $eventinfo);
		$options->set('guestEmails', $billemail);
		$options->set('from', $mailfrom);
		$options->set('fromname', $fromname);

		Tjnotifications::send($client, $key, $recipients, $replacements, $options);
	}

	/**
	 * Send invoice email after payment
	 *
	 * @param   string  $venueParams   id in jticketing_order
	 * @param   string  $eventDetails  id in jticketing_order
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function onlineEventNotify($venueParams, $eventDetails)
	{
		$com_params           = JComponentHelper::getParams('com_jticketing');
		$jticketingmainhelper = new Jticketingmainhelper;
		$where                = '';
		$db                   = JFactory::getDBO();
		$buyer                = JFactory::getUser();
		$app                  = JFactory::getApplication();
		$mailfrom             = $app->getCfg('mailfrom');
		$fromname             = $app->getCfg('fromname');
		$sitename             = $app->getCfg('sitename');
		$email = '';

		$client = "com_jticketing";
		$key    = "e-ticketsOnlineEvent";

		$recipients = '';

		$eventDetails->site = $sitename;

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('tjevents');
		$userCredential = $dispatcher->trigger('getUserAPIData', array($venueParams->user_id));
		$userData       = json_decode($userCredential['0']['token'], true);

		// JSON Decode
		$email       = $userData['email'];
		$password    = base64_decode($userData['password']);
		$hostUrl     = preg_replace('{/$}', '', $venueParams->host_url);

		$userDetails = new stdClass;
		$userDetails = $venueParams;

		$userDetails->adobeEmail    = $email;
		$userDetails->adobePassword = $password;
		$userDetails->url           = $hostUrl . $venueParams->meeting_url;

		$replacements        = new stdClass;
		$replacements->event = $eventDetails;
		$replacements->user  = $userDetails;

		$options = new JRegistry;
		$options->set('subject', $eventDetails);
		$options->set('guestEmails', array($venueParams->email));
		$options->set('from', $mailfrom);
		$options->set('fromname', $fromname);

		$result = Tjnotifications::send($client, $key, $recipients, $replacements, $options);
	}

	/**
	 * Send checkin email after checkin
	 *
	 * @param   ARRAY  $data  checkin array
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function checkInMail($data)
	{
		$app                  = JFactory::getApplication();
		$mailfrom             = $app->getCfg('mailfrom');
		$fromname             = $app->getCfg('fromname');
		$sitename             = $app->getCfg('sitename');
		$email = '';

		$client = "com_jticketing";
		$key    = "checkin";
		$recipients = '';
		$eventDetails->site = $sitename;

		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		$eventModel = JModelLegacy::getInstance('Event', 'JticketingModel');

		$checkInDetails = (object) $data;
		$eventDetails = new stdClass;
		$eventDetails = $eventModel->getItem($data['eventid']);

		$replacements = new stdClass;
		$replacements->event = $eventDetails;
		$replacements->checkin  = $checkInDetails;

		$options = new JRegistry;
		$options->set('subject', $eventDetails);
		$options->set('guestEmails', array($data['attendee_email']));
		$options->set('from', $mailfrom);
		$options->set('fromname', $fromname);

		$result = Tjnotifications::send($client, $key, $recipients, $replacements, $options);
	}
}
