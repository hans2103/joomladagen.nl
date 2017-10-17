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

$com_params         = JComponentHelper::getParams('com_jticketing');
$pdf_attach_in_mail = $com_params->get('pdf_attach_in_mail');

jimport('joomla.user.helper');
jimport('joomla.html.html');
jimport('joomla.html.parameter');
jimport('joomla.utilities.date');
jimport('techjoomla.tjnotifications.tjnotifications');
JLoader::import('time', JPATH_SITE . '/components/com_jticketing/helpers');

use Dompdf\Dompdf;

/**
 * main helper class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class Jticketingmainhelper
{
	/**
	 * Get layout html
	 *
	 * @param   string  $remoteFile  name of view
	 * @param   string  $localFile   layout of view
	 *
	 * @return  void
	 */
	public function saveFileFromTheWeb($remoteFile, $localFile)
	{
		$ch      = curl_init();
		$timeout = 0;
		curl_setopt($ch, CURLOPT_URL, $remoteFile);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		$image = curl_exec($ch);
		curl_close($ch);
		$f = fopen($localFile, 'w');
		fwrite($f, $image);
		fclose($f);
	}

	/**
	 * Get layout html
	 *
	 * @param   string  $viewname       name of view
	 * @param   string  $layout         layout of view
	 * @param   string  $searchTmpPath  site/admin template
	 * @param   string  $useViewpath    site/admin view
	 *
	 * @return  [type]                  description
	 */
	public function getViewpath($viewname, $layout = "", $searchTmpPath = 'SITE', $useViewpath = 'SITE')
	{
		$searchTmpPath = ($searchTmpPath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
		$useViewpath   = ($useViewpath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
		$app           = JFactory::getApplication();

		if (!empty($layout))
		{
			$layoutname = $layout . '.php';
		}
		else
		{
			$layoutname = "default.php";
		}

		// Get templates from override folder

		if ($searchTmpPath == JPATH_SITE)
		{
			$defTemplate = $this->getSiteDefaultTemplate(0);
		}
		else
		{
			$defTemplate = $this->getSiteDefaultTemplate(0);
		}

		// @TODO GET TEMPLATE MANUALLY as  $app->getTemplate() is not working
		// $searchTmpPath . '/templates/' . $app->getTemplate() . '/html/com_jticketing/' . $viewname . '/' . $layoutname;
		$overide_basepath = $override = $searchTmpPath . '/templates/' . $defTemplate . '/html/com_jticketing/' . $viewname . '/' . $layoutname;

		if (JFile::exists($override))
		{
			return $view = $override;
		}
		else
		{
			return $view = $useViewpath . '/components/com_jticketing/views/' . $viewname . '/tmpl/' . $layoutname;
		}
	}

	/**
	 * Get sites/administrator default template
	 *
	 * @param   mixed  $client  0 for site and 1 for admin template
	 *
	 * @return  json
	 *
	 * @since   1.5
	 */
	public function getSiteDefaultTemplate($client = 0)
	{
		try
		{
			$db = JFactory::getDBO();

			// Get current status for Unset previous template from being default
			// For front end => client_id=0
			$query = $db->getQuery(true)->select('template')->from($db->quoteName('#__template_styles'))->where('client_id=' . $client)->where('home=1');
			$db->setQuery($query);

			return $db->loadResult();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return '';
		}
	}

	/**
	 * Send mail to receipeints
	 *
	 * @param   string  $recipient       description
	 * @param   string  $subject         subject
	 * @param   string  $body            body
	 * @param   string  $bcc_string      bcc_string
	 * @param   string  $single          singlemail
	 * @param   string  $attachmentPath  attachmentPath
	 * @param   string  $from            from
	 * @param   string  $fromname        fromname
	 * @param   string  $mode            mode
	 *
	 * @return  [type]                  description
	 */
	public function jt_sendmail($recipient, $subject, $body, $bcc_string = '', $single = 1, $attachmentPath = "", $from = "", $fromname = "", $mode = 1)
	{
		jimport('joomla.utilities.utility');
		$mainframe = JFactory::getApplication();

		if (!$from)
		{
			$from = $mainframe->getCfg('mailfrom');
		}

		if (!$fromname)
		{
			$fromname = $mainframe->getCfg('fromname');
		}

		$recipient = trim($recipient);
		$cc        = null;
		$bcc       = array();

		if ($single == 1)
		{
			if (isset($bcc_string))
			{
				$bcc = explode(',', $bcc_string);
			}
		}

		$attachment = null;

		if (!empty($attachmentPath))
		{
			$attachment = $attachmentPath;
		}

		$replyto     = null;
		$replytoname = null;

		return JFactory::getMailer()->sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
	}

	/**
	 * Get access levels
	 *
	 * @param   array  $groups  groups
	 *
	 * @return  void
	 */
	public function getAccessLevels($groups = array())
	{
		$db = JFactory::getDBO();

		$query = "SELECT title,id FROM #__viewlevels";

		if (!empty($groups))
		{
			$groups = implode("','", $groups);
			$query .= " WHERE id IN('" . $groups . "')";
		}

		$db->setQuery($query);
		$accesslevels = $db->loadObjectList();

		return $accesslevels;
	}

	/**
	 * Get predefined payment status
	 *
	 * @return  array  $payment_statuses  payment status array
	 *
	 * @since   1.0
	 */
	public function getPaymentStatusArray()
	{
		$payment_statuses = array(
			'P' => JText::_('JT_PSTATUS_PENDING'),
			'C' => JText::_('JT_PSTATUS_COMPLETED'),
			'D' => JText::_('JT_PSTATUS_DECLINED'),
			'DP' => JText::_('JT_PSTATUS_DEPOSIT_PAID'),
			'E' => JText::_('JT_PSTATUS_FAILED'),
			'UR' => JText::_('JT_PSTATUS_UNDERREVIW'),
			'RF' => JText::_('JT_PSTATUS_REFUNDED'),
			'CRV' => JText::_('JT_PSTATUS_CANCEL_REVERSED'),
			'RV' => JText::_('JT_PSTATUS_REVERSED'),
			'T' => JText::_('JT_PSTATUS_TRANSFER')

		);

		return $payment_statuses;
	}

	/**
	 * Get actual payment status
	 *
	 * @param   string  $pstatus  payment status like P/S
	 *
	 * @return  array  $payment_statuses  payment status array
	 *
	 * @since   1.0
	 */
	public function getPaymentStatus($pstatus)
	{
		$payment_statuses = array(
			'P' => JText::_('JT_PSTATUS_PENDING'),
			'C' => JText::_('JT_PSTATUS_COMPLETED'),
			'D' => JText::_('JT_PSTATUS_DECLINED'),
			'DP' => JText::_('JT_PSTATUS_DEPOSIT_PAID'),
			'E' => JText::_('JT_PSTATUS_FAILED'),
			'UR' => JText::_('JT_PSTATUS_UNDERREVIW'),
			'RF' => JText::_('JT_PSTATUS_REFUNDED'),
			'CRV' => JText::_('JT_PSTATUS_CANCEL_REVERSED'),
			'RV' => JText::_('JT_PSTATUS_REVERSED')
		);

		return $payment_statuses[$pstatus];
	}

	/**
	 * Get currency symbol pass like USD and returns $
	 *
	 * @param   string  $currency  description
	 *
	 * @return  string  symbol $
	 */
	public function getCurrencySymbol($currency = '')
	{
		$params   = JComponentHelper::getParams('com_jticketing');
		$curr_sym = $params->get('currency_symbol');

		if (empty($curr_sym))
		{
			$curr_sym = $params->get('currency');
		}

		return $curr_sym;
	}

	/**
	 * Get formatted price
	 *
	 * @param   float   $price  price
	 * @param   string  $curr   curr
	 *
	 * @return  html    formated like 3$ or $3
	 */
	public function getFromattedPrice($price, $curr = null)
	{
		$curr_sym                   = $this->getCurrencySymbol();
		$params                     = JComponentHelper::getParams('com_jticketing');
		$currency_display_format    = $params->get('currency_display_format');
		$currency_display_formatstr = '';
		$currency_display_formatstr = str_replace('{AMOUNT}', "&nbsp;" . $price, $currency_display_format);
		$currency_display_formatstr = str_replace('{CURRENCY_SYMBOL}', "&nbsp;" . $curr_sym, $currency_display_formatstr);
		$html                       = '';
		$html                       = "<span>" . $currency_display_formatstr . "</span>";

		return $html;
	}

	/**
	 * Validates if order is for same user
	 *
	 * @param   int  $orderuser  price
	 *
	 * @return  int  1 or 0
	 */
	public function getorderAuthorization($orderuser)
	{
		$user = JFactory::getUser();

		if ($user->id == $orderuser)
		{
			return 1;
		}

		return 0;
	}

	/**
	 * Validates to show buy button in module or anywhere in jticketing
	 *
	 * @param   int  $eventid  eventid passed in url of page(#_community_event,#_jevent)
	 * @param   int  $userid   userid  id of user logged in or which needs to buy ticket
	 *
	 * @return  string  1 or 0
	 */
	public function showbuybutton($eventid, $userid = '')
	{
		$eachtypecntzero = 0;
		$eachtypeavl     = 0;

		if (isset($userid))
		{
			$user = $userid;
		}
		else
		{
			$user = JFactory::getUser()->id;
		}

		$ticketdata  = $this->getEventDetails($eventid);
		$eventdata   = $this->getAllEventDetails($eventid);
		$evxref_id   = $this->getEventrefid($eventid);

		$integration = $this->getIntegration();
		$currentTime = strtotime(JFactory::getDate('now', 'UTC', true));
		$event_end_time = strtotime($eventdata->enddate);

		// If integration is native consider booking start and booking end date
		if ($integration == 2)
		{
			$booking_end_time   = strtotime($eventdata->booking_end_date);
			$booking_start_time = strtotime($eventdata->booking_start_date);
		}

		// If integration is Native consider booking start and Booking end date
		if ($integration == 2)
		{
			// If event booking date is passed or current date is less than booking start date do not show buy button.
			if ($booking_end_time < $currentTime or $booking_start_time > $currentTime or $event_end_time < $currentTime)
			{
				return 0;
			}
		}
		elseif ($integration == 3)
		{
			// If event endtime has passed, return.
			if ($event_end_time < $currentTime)
			{
				return 0;
			}
		}
		else
		{
			if (!empty($eventdata->enddate))
			{
				if ($eventdata->enddate == '0000-00-00')
				{
					$eventdata->enddate = $eventdata->startdate;
				}

				$eventtime = strtotime($eventdata->enddate);

				// If event is expired do not show buy button
				if ($eventtime < $currentTime or $event_end_time < $currentTime)
				{
					return 0;
				}
			}
		}

		$paideventype = 0;

		if (!empty($ticketdata))
		{
			$checkiflimitcrossed_for_ticket = 0;

			foreach ($ticketdata as $type)
			{
				if ((float) $type->price > 0)
				{
					$paideventype = 1;
				}

				// If for Unlimited Seats Show buy button by default
				if ($type->unlimited_seats == 1 and $type->hide_ticket_type != 1)
				{
					return 1;
				}

				$eachtypeavl = $type->available;

				if (isset($type->count) and $type->count > 0 and $type->hide_ticket_type != 1)
				{
					// If there are still tickets available
					$eachtypecntzero = 1;
					break;
				}
				else
				{
					$eachtypecntzero = 0;
				}

				if ($type->available <= 0)
				{
					$eachtype_available = 0;
				}
				else
				{
					$eachtype_available = 1;
				}
			}
		}

		// IF EVENT IS FREE AND ALL TICKETS SOLD
		if ($eachtypecntzero == 1)
		{
			return 1;
		}

		if ($paideventype != 1 and $eachtypecntzero == 0 and $eachtypeavl > 0)
		{
			return 0;
		}
	}

	/**
	 * Check if ticket limit crossed
	 *
	 * @return  int  1 or 0
	 *
	 * @since   1.0
	 */
	public function checkiflimitcrossed_for_ticket()
	{
		if ($type->max_limit_ticket > 0)
		{
			$db    = JFactory::getDBO();
			$query = "SELECT id FROM #__jticketing_order WHERE status LIKE 'C' AND user_id=" . $user->id;
			$db->setQuery($query);
			$order_ids = $db->loadObjectList();

			foreach ($order_ids AS $order)
			{
				$query = "SELECT id FROM #__jticketing_order WHERE  user_id=" . $user->id;
				$db->setQuery($query);
				$order_ids = $db->loadObjectList();
			}
		}
	}

	/**
	 * Get item id of menu
	 *
	 * @param   string  $link          link
	 * @param   string  $skipIfNoMenu  skipIfNoMenu
	 *
	 * @return  object  $country  Details
	 *
	 * @since   1.0
	 */
	public function getItemId($link, $skipIfNoMenu = 0)
	{
		$itemid    = 0;
		parse_str($link, $parsedLinked);
		$layout = '';

		if (isset($parsedLinked['layout']))
		{
			$layout = $parsedLinked['layout'];
		}

		$JSite = new JSite;
		$menu  = $JSite->getMenu();
		$jinput = JFactory::getApplication();

		if ($jinput->issite())
		{
			$items = $menu->getItems('link', $link);

			if (isset($items[0]))
			{
				$itemid = $items[0]->id;
			}
		}

		if (isset($parsedLinked['view']))
		{
			if (($parsedLinked['view'] == 'order') || $parsedLinked['view'] == 'event')
			{
				// Get the itemid of the menu which is pointed to events URL
				$eventsUrl = 'index.php?option=com_jticketing&view=events&layout=default';
				$menuItem = $menu->getItems('link', $eventsUrl, true);

				if ($menuItem)
				{
					$itemid = $menuItem->id;
				}
			}

			if ($layout == 'order')
			{
				// Get the itemid of the menu which is pointed to orders URL
				$ordersUrl = 'index.php?option=com_jticketing&view=orders&layout=default';
				$menuItem = $menu->getItems('link', $ordersUrl, true);

				if ($menuItem)
				{
					$itemid = $menuItem->id;
				}
			}
		}

		if (!$itemid)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'));
			$query->from($db->quoteName('#__menu'));
			$query->where($db->quoteName('link') . ' LIKE ' . $db->Quote('%' . $link . '%'));
			$query->where($db->quoteName('published') . '=' . $db->Quote(1));
			$query->setLimit(1);
			$db->setQuery($query);
			$itemid = $db->loadResult();
		}

		if (!$itemid)
		{
			if ($skipIfNoMenu)
			{
				$itemid = 0;
			}
			else
			{
				$input = $jinput->input;
				$itemid = $input->get('Itemid', '0', 'INT');
			}
		}

		return $itemid;
	}

	/**
	 * Get integration in backend JTicketing option
	 *
	 * @return  object  $country  Details
	 *
	 * @since   1.0
	 */
	public function getIntegration()
	{
		$com_params = JComponentHelper::getParams('com_jticketing');

		return $integration = $com_params->get('integration');
	}

	/**
	 * Gives xref id from jticketing_integration table
	 *
	 * @param   int  $eventid  event id of of the jomsocial,jevents,easysocial
	 *
	 * @return  int  id of xref table
	 *
	 * @since   1.0
	 */
	public function getEventrefid($eventid)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$db                   = JFactory::getDBO();
		$integration          = $jticketingmainhelper->getIntegration();

		if (!$integration)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage('Please set ticketing integration first', 'warning');
			$app->redirect(JRoute::_('index.php?option=com_jticketing&view=events', false));
		}

		if (!empty($eventid))
		{
			if ($integration == 1)
			{
				$source = "com_community";
			}
			elseif ($integration == 2)
			{
				$source = "com_jticketing";
			}
			elseif ($integration == 3)
			{
				$source = "com_jevents";
			}
			elseif ($integration == 4)
			{
				$source = "com_easysocial";
			}

			$query = $db->getQuery(true);
			$query->select($db->quoteName('id'));
			$query->from($db->quoteName('#__jticketing_integration_xref'));
			$query->where($db->quoteName('eventid') . ' = ' . $db->quote($eventid));
			$query->where($db->quoteName('source') . ' = ' . $db->quote($source));
			$db->setQuery($query);

			return $evxref_id = $db->loadResult();
		}

		return;
	}

	/**
	 * Gives paypal email of event owner
	 *
	 * @param   int  $order_id  order_id
	 *
	 * @return  string  $email  paypal email of event owner
	 *
	 * @since   1.0
	 */
	public function getEventownerEmail($order_id)
	{
		$db                   = JFactory::getDBO();
		$jticketingmainhelper = new Jticketingmainhelper;

		$integration = $jticketingmainhelper->getIntegration();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('event_details_id')));
		$query->from($db->quoteName('#__jticketing_order'));
		$query->where($db->quoteName('id') . ' = ' . $db->quote($order_id));
		$db->setQuery($query);
		$eventid   = $db->loadResult();
		$evxref_id = $jticketingmainhelper->getEventrefid($eventid);

		if (!$evxref_id)
		{
			return '';
		}

		$query1 = $db->getQuery(true);
		$query1->select($db->quoteName(array('paypal_email')));
		$query1->from($db->quoteName('#__jticketing_integration_xref'));
		$query1->where($db->quoteName('eventid') . ' = ' . $db->quote($evxref_id));
		$db->setQuery($query1);
		$email = $db->loadResult();

		return $email;
	}

	/**
	 * Gives ticket html to print
	 *
	 * @param   string  $data        data passed
	 * @param   int     $usesession  usesession
	 *
	 * @return  string  $email  paypal email of event owner
	 *
	 * @since   1.0
	 */
	public function getticketHTML($data, $usesession = 0)
	{
		$com_params     = JComponentHelper::getParams('com_jticketing');
		$qr_code_width  = $com_params->get('qr_code_width', 80);
		$qr_code_height = $com_params->get('qr_code_height', 80);

		// Trigger Before showing pdf html
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$data1 = $dispatcher->trigger('jt_OnBeforeHTMLShow', array($data));

		if (!empty($data1['0']))
		{
			$data = $data1['0'];
		}

		if (!empty($data->send_reminder) and $data->send_reminder == '1')
		{
			$emails_config = $data->email_config;
		}
		elseif (isset($data->send_html_only) and $data->send_html_only == 1)
		{
			// Trigger Before using backend global template or any other template
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');
			$data1 = $dispatcher->trigger('jt_OnBeforeEmailTemplateReplace', array($data));

			if (!empty($data1[0]))
			{
				// Define email template array as per template file
				$emails_config = array(
				'message_body' => $data1[0]);
			}
			else
			{
				require JPATH_ADMINISTRATOR . '/components/com_jticketing/email_template.php';
			}
		}
		else
		{
			require JPATH_ADMINISTRATOR . '/components/com_jticketing/config.php';
		}

		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();
		$document             = JFactory::getDocument();
		$session              = JFactory::getSession();
		$ticketprice          = 0;
		$nofotickets          = 0;
		$totalprice           = 0;
		$location             = '';

		if ($usesession == 1)
		{
			$data->ticketprice = $session->get('eventticketprice');
			$data->nofotickets = $session->get('tickets');
			$data->totalprice  = $session->get('totalprice');
		}

		// If no event avatar found
		if (empty($data->avatar) && empty($data->cover))
		{
			if ($integration == 1)
			{
				$eventpicture = 'components/com_community/assets/event.png';
			}
			elseif ($integration == 2)
			{
				$eventpicture = 'media/com_jticketing/images/default-event-image.png';
			}
			elseif ($integration == 3)
			{
				$eventpicture = 'media/com_jticketing/images/default-event-image.png';
			}
			elseif ($integration == 4)
			{
				$eventpicture = '/media/com_easysocial/defaults/avatars/event/square.png';
			}
		}
		else
		{
			if ($integration == 1)
			{
				if (isset($data->avatar) || isset($data->cover))
				{
					if (!empty($data->avatar))
					{
						$eventpicture = $data->avatar;
					}
					else
					{
						$eventpicture = $data->cover;
					}
				}
			}
			elseif ($integration == 2)
			{
				$eventpicture = $data->avatar;
			}
			elseif ($integration == 3)
			{
				$eventpicture = $data->avatar;
			}
			elseif ($integration == 4)
			{
				$eventpicture = $data->avatar;
			}
		}

		$description = '';
		$title       = '';

		if (!empty($data->title))
		{
			$title = ucfirst($data->title);
		}
		elseif ($data->summary)
		{
			$title = ucfirst($data->summary);
		}

		if (!empty($data->long_description))
		{
			$description = $data->long_description;
		}
		elseif (!empty($data->description))
		{
			$description = $data->description;
		}

		if ($integration == 2)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('v.country', 'v.state_id', 'v.online_provider', 'v.name', 'v.city', 'v.address', 'v.zipcode')));
			$query->from($db->quoteName('#__jticketing_venues', 'v'));
			$query->where($db->quoteName('v.id') . ' = ' . $db->quote($data->venue));

			// Join over the venue table
			$query->select($db->quoteName(array('con.country'), array('coutryName')));
			$query->join('LEFT', $db->quoteName('#__tj_country', 'con') . ' ON (' . $db->quoteName('con.id') . ' = ' . $db->quoteName('v.country') . ')');

			// Join over the venue table
			$query->select($db->quoteName(array('r.region')));
			$query->join('LEFT', $db->quoteName('#__tj_region', 'r') . ' ON (' . $db->quoteName('r.id') . ' = ' . $db->quoteName('v.state_id') . ')');

			$db->setQuery($query);
			$location_data   = $db->loadObject();

			if ($data->online_events == "1")
			{
				if ($location_data->online_provider == "plug_tjevents_adobeconnect")
				{
					$location = JText::_('COM_JTICKETING_ADOBECONNECT_PLG_NAME') . " - " . $location_data->name;
				}
			}
			elseif($data->online_events == "0")
			{
				if ($data->venue != "0")
				{
					$name = $location_data->name . " : " . JText::_('COM_JTICKETING_BILLIN_ADDR') . "- " . $location_data->address;
					$city = ", " . $location_data->city . ", " . $location_data->region . ", " . $location_data->coutryName;
					$zipcode = ", " . JText::_('COM_JTICKETING_FORM_LBL_VENUE_ZIPCODE') . " - " . $location_data->zipcode;
					$location = $name . $city . $zipcode;
				}
				else
				{
					$location = $data->location;
				}
			}
			else
			{
				echo "-";
			}
		}
		else
		{
			$location = $data->location;
		}

		$bookdate = JHtml::_('date', $data->cdate, 'Y-m-d');
		$bookdate = str_replace('00:00:00', '', $bookdate);

		if ($data->attendee_id)
		{
			$field_array = array(
				'first_name',
				'last_name'
			);

			// Get Attendee Details
			$attendee_details = $this->getAttendees_details($data->attendee_id, $field_array);
		}

		$return       = $jticketingmainhelper->getTimezoneString($data->eid);
		$ev_startdate = $return['startdate'];

		$ev_enddate = $return['enddate'];

		if (!empty($return['eventshowtimezone']))
		{
			$datetoshow .= '<br/>' . $return['eventshowtimezone'];
		}

		$msg['msg_body'] = $emails_config['message_body'];

		if (empty($data->eventnm))
		{
			$eventnm = $title;
		}
		else
		{
			$eventnm = $data->eventnm;
		}

		if (isset($attendee_details['first_name']))
		{
			$buyername = implode(" ", $attendee_details);
		}
		else
		{
			$db = JFactory::getDBO();

			$collect_attendee_info_checkout = $com_params->get('collect_attendee_info_checkout');

			// If collect attendee info is set  to no in backend then take first and last name from billing info.
			if (!$collect_attendee_info_checkout and $data->id)
			{
				$query = "SELECT firstname,lastname FROM #__jticketing_users WHERE order_id=" . $data->id;
				$db->setQuery($query);
				$attname   = $db->loadObject();
				$buyername = $attname->firstname . ' ' . $attname->lastname;
			}
			else
			{
				$buyername = $data->name;
			}
		}

		// Create QR Image Using Google Graphs
		$qrstring                 = "Booking ID:" . JText::_("TICKET_PREFIX") . $data->id . '-' . $data->order_items_id;
		$qrstring                 = urlencode($qrstring);
		$qr_url                   = "http://chart.apis.google.com/chart?cht=qr&chs=";
		$qrimage                  = $qr_url . $qr_code_width . "x" . $qr_code_height . "&chl=" . $qrstring . "&chld=H|0";
		$qr_path_url              = $qrimage;
		$msg['booking_date']      = $bookdate;
		$msg['event_image']       = '<img src="' . $eventpicture . '" width="250px" height="150px" border="0">';
		$msg['event_name']        = $eventnm;
		$msg['event_start_date']  = $ev_startdate;
		$msg['event_start_time']  = $return['start_time'];
		$msg['event_end_date']    = $ev_enddate;
		$msg['event_description'] = $description;
		$msg['event_location']    = $location;
		$msg['ticket_id']         = JText::_("TICKET_PREFIX") . $data->id . '-' . $data->order_items_id;

		if (isset($data->ticketprice))
		{
			$msg['ticket_price'] = $data->ticketprice;
		}

		$msg['no_of_attendees']   = $data->nofotickets;
		$msg['total_price']       = $data->totalprice;
		$msg['qr_code']           = '<img src="' . $qr_path_url . '" class="qrimg" >';
		$msg['buyer_name']        = $buyername;
		$msg['ticket_type_title'] = $data->ticket_type_title;
		$msg['ticket_type_desc']  = $data->ticket_type_desc;
		$msg['event_id']          = $data->eid;
		$msg['ticket_buyer_name'] = $data->name;

		if (isset($data->customfields_ticket))
		{
			$msg['customfields_ticket'] = $data->customfields_ticket;
		}

		if (isset($data->customfields_event))
		{
			$msg['customfields_event'] = $data->customfields_event;
		}

		// 	Get Event Url
		$link                  = $jticketingmainhelper->getEventlink($data->eid);
		$msg['event_url']      = "<a href=" . $link . ">" . JText::_("COM_JTICKETING_CLICK_TO_ATTEND") . "</a>";
		$event_url_ical        = JUri::base() . "index.php?option=com_jticketing&view=event&format=ical&id=" . $data->eid;
		$msg['event_url_ical'] = "<a href=" . $event_url_ical . ">Export to Ical</a>";
		$html                  = $jticketingmainhelper->tagreplace($msg);
		$cssdata               = "";

		if (!empty($data->send_reminder) and $data->send_reminder == '1' and $data->reminder_type == 'email')
		{
			// $cssdata .= $data->css_file;
			// $html = $jticketingmainhelper->getEmorgify($html, $cssdata);
		}
		else
		{
			$cssfile = JPATH_ROOT . "/components/com_jticketing/assets/css/email.css";
			$cssdata .= file_get_contents($cssfile);
			$html = $jticketingmainhelper->getEmorgify($html, $cssdata);
		}

		return $html;
	}

	/**
	 * Get attendee details for order
	 *
	 * @param   string  $attnd_id     attendee id
	 * @param   string  $field_array  name of fields to return
	 *
	 * @return  object  attendee data of name and valuie
	 */
	public function getAttendees_details($attnd_id, $field_array = '')
	{
		$db = JFactory::getDBO();

		if (!empty($field_array))
		{
			$field_array_str = implode("','", $field_array);
		}

		$query = "SELECT ufv.field_value,ufields.name as fieldnm,ufields.id as fieldid
				FROM #__jticketing_attendee_field_values as ufv INNER
				JOIN  #__jticketing_attendee_fields as  ufields
				ON ufields.id=ufv.field_id
				WHERE ufv.attendee_id=" . $attnd_id . "
				AND ufv.field_source='com_jticketing'";

		if ($field_array_str)
		{
			$where = " AND  ufields.name IN('" . $field_array_str . "')";
		}

		$query .= $where;
		$db->setQuery($query);
		$attendee_data = $db->loadObjectList();
		$attdata       = array();

		if (!empty($attendee_data))
		{
			foreach ($attendee_data as $key => $data)
			{
				$attdata[$data->fieldnm] = $data->field_value;
			}

			return $attdata;
		}
	}

	/**
	 * Get all order Information
	 *
	 * @param   integer  $orderid  orderid
	 *
	 * @return  array  list of all orders with order items and event details
	 *
	 * @since   1.0
	 */
	public function getorderinfo($orderid = '0')
	{
		$db     = JFactory::getDBO();
		$user   = JFactory::getUser();
		$jinput = JFactory::getApplication()->input;

		if ($orderid)
		{
			$query = "SELECT `parent_order_id` FROM `#__jticketing_order` WHERE `id`=" . $orderid;
			$db->setQuery($query);
			$parent_order_id = $db->loadResult();

			if ($parent_order_id > 0)
			{
				$orderid = $parent_order_id;
			}
		}

		if (empty($orderid))
		{
			return 0;
		}

		// In result id field  belongs to order table not user table(due to same column name in both table last column is selected)
		$query = "SELECT u.* ,o.processor,o.event_details_id AS event_integration_id,
				o.original_amount,o.order_id as orderid_with_prefix,o.amount,o.fee,o.coupon_discount,
				o.coupon_discount_details,o.coupon_code , o.user_id, o.transaction_id,o.ip_address,o.cdate,
				o.payee_id,o.status,o.id,o.order_tax,o.order_tax_details,o.amount,o.customer_note
				FROM #__jticketing_order as o  JOIN #__jticketing_users as u ON o.id = u.order_id";

		if ($orderid)
		{
			$query .= " WHERE o.id=" . $orderid;
		}

		$query .= " order by u.id DESC";
		$db->setQuery($query);
		$order_result = $db->loadObjectlist();

		// Change for backward compatiblity for user info not saving order id against it Check only order table
		if (empty($order_result))
		{
			$query = "SELECT o.id as order_id,o.order_id as orderid_with_prefix,
			o.event_details_id AS event_integration_id,o.processor,o.original_amount,
			o.amount,o.fee,o.coupon_discount,o.coupon_discount_details,o.coupon_code ,o.user_id,
			o.transaction_id,o.ip_address,o.cdate, o.payee_id,o.status,o.id,o.order_tax,o.order_tax_details,
			o.amount,o.customer_note	FROM #__jticketing_order as o 	 WHERE  o.id=" . $orderid . "";
			$db->setQuery($query);
			$order_result = $db->loadObjectlist();
		}

		if (empty($order_result))
		{
			return;
		}

		$orderlist['order_info'] = $order_result;
		$orderlist['items']      = $this->getOrderItems($orderid);

		// Get Event Information For that order
		if (isset($orderlist['order_info']['0']->event_integration_id))
		{
			$query = "SELECT eventid FROM #__jticketing_integration_xref WHERE id=" . $orderlist['order_info']['0']->event_integration_id;
			$db->setQuery($query);
			$eventid = $db->loadResult();

			if (isset($eventid))
			{
				$orderlist['eventinfo'] = $this->getAllEventDetails($eventid);
			}
		}

		return $orderlist;
	}

	/**
	 * Get all order Information
	 *
	 * @param   integer  $orderid  orderid
	 *
	 * @return  array  list of all orders with order items and event details
	 *
	 * @since   1.0
	 */
	public function getOrderItems($orderid)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT i.id as order_items_id,i.type_id,t.title as order_item_name,
		sum(i.ticketcount) as ticketcount,i.id AS order_item_id,
		 t.price FROM #__jticketing_order_items as i JOIN
		 #__jticketing_types as t ON t.id=i.type_id
		 WHERE i.order_id=" . $orderid . " GROUP BY t.title";
		$db->setQuery($query);

		return $db->loadObjectlist();
	}

	/**
	 * Get all order Information
	 *
	 * @param   integer  $orderid  orderid
	 *
	 * @return  array  list of all orders with order items and event details
	 *
	 * @since   1.0
	 */
	public function getOrderItemsID($orderid)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT i.id AS order_items_id,attendee_id
		  FROM #__jticketing_order_items AS i	 WHERE i.order_id=" . $orderid . "";
		$db->setQuery($query);

		return $db->loadObjectlist();
	}

	/**
	 * Clears all session data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function clearSession()
	{
		$session = JFactory::getSession();
		$session->set('sticketid', '');
		$session->set('JT_orderid', '');
	}

	/**
	 * Get Event link
	 *
	 * @param   int  $eventid  description
	 *
	 * @return  string  link of event
	 */
	public function getEventlink($eventid)
	{
		$jticketingmainhelper = new Jticketingmainhelper;

		$db          = JFactory::getDBO();
		$integration = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			require_once JPATH_SITE . '/components/com_community/libraries/core.php';
			$link = "index.php?option=com_community&view=events";
			$link .= "&task=viewevent&eventid=" . $eventid;

			return $link = JUri::root() . substr(CRoute::_($link), strlen(JUri::base(true)) + 1);
		}
		elseif ($integration == 2)
		{
			$link = "index.php?option=com_jticketing&view=event&id=" . $eventid;
		}
		elseif ($integration == 3)
		{
			$link = "index.php?option=com_jevents&task=icalrepeat.detail&evid=" . $eventid;
		}
		elseif ($integration == 4)
		{
			$link = "index.php?option=com_easysocial&view=events&layout=item&id=" . $eventid;
		}

		$itemid = $this->getItemId($link);
		$link   = JUri::root() . substr(JRoute::_($link . '&Itemid=' . $itemid), strlen(JUri::base(true)) + 1);

		return $link;
	}

	/**
	 * Method to replace the tags in the message body
	 *
	 * @param   array  $msg   replacement data for tags
	 * @param   int    $flag  int
	 *
	 * @return  void
	 */
	public function tagreplace($msg, $flag = ' ')
	{
		$com_params                     = JComponentHelper::getParams('com_jticketing');
		$session                        = JFactory::getSession();
		$message_body                   = stripslashes($msg['msg_body']);
		$collect_attendee_info_checkout = $com_params->get('collect_attendee_info_checkout');

		if (isset($collect_attendee_info_checkout))
		{
			if (!empty($msg['customfields_ticket']->first_name))
			{
				$message_body = str_replace("[NAME]", $msg['customfields_ticket']->first_name . " " . $msg['customfields_ticket']->last_name, $message_body);
			}
			else
			{
				$message_body = str_replace("[NAME]", $msg['buyer_name'], $message_body);
			}
		}
		else
		{
			$message_body = str_replace("[NAME]", $msg['buyer_name'], $message_body);
		}

		$message_body = str_replace("[EVENT_URL]", $msg['event_url'], $message_body);
		$message_body = str_replace("[TICKET_ID]", $msg['ticket_id'], $message_body);
		$message_body = str_replace("[BOOKING_DATE]", $msg['booking_date'], $message_body);
		$message_body = str_replace("[BUYER_NAME]", $msg['ticket_buyer_name'], $message_body);
		$message_body = str_replace("[EVENT_IMAGE]", $msg['event_image'], $message_body);
		$message_body = str_replace("[EVENT_NAME]", $msg['event_name'], $message_body);
		$message_body = str_replace("[ST_DATE]", $msg['event_start_date'], $message_body);
		$message_body = str_replace("[ST_TIME]", $msg['event_start_time'], $message_body);
		$message_body = str_replace("[EN_DATE]", $msg['event_end_date'], $message_body);
		$message_body = str_replace("[EVENT_LOCATION]", $msg['event_location'], $message_body);

		if (!empty($msg['ticket_price']))
		{
			$message_body = str_replace("[TICKET_PRICE]", $msg['ticket_price'], $message_body);
		}

		$message_body = str_replace("[NO_OF_ATTENDEES]", $msg['no_of_attendees'], $message_body);
		$message_body = str_replace("[TOTAL_PRICE]", $msg['total_price'], $message_body);
		$message_body = str_replace("[EVENT_DESCRIPTION]", $msg['event_description'], $message_body);
		$message_body = str_replace("[QR_CODE]", $msg['qr_code'], $message_body);
		$message_body = str_replace("[TICKET_TYPE]", $msg['ticket_type_title'], $message_body);
		$message_body = str_replace("[TICKET_TYPE_DESCRIPTION]", $msg['ticket_type_desc'], $message_body);
		$message_body = str_replace("[EXPORT_ICAL_LINK]", $msg['event_url_ical'], $message_body);

		// Replace custom fields in JTicketing. This will replace custom fields for event and ticket as required
		$message_body = $this->replacecustomfields($msg, $message_body);

		// We will replace this field 2 times to support field in fields
		$message_body = $this->replacecustomfields($msg, $message_body);

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$data1 = $dispatcher->trigger('jt_OnTagReplace', array($msg,$message_body));

		if (!empty($data1['0']))
		{
			$message_body = $data1['0'];
		}

		return $message_body;
	}

	/**
	 * Get field id and type
	 *
	 * @param   int  $msg           msg to send
	 * @param   int  $message_body  msg to send
	 *
	 * @return  void
	 */
	public function replacecustomfields($msg, $message_body)
	{
		if (!empty($msg['customfields_ticket']))
		{
			foreach ($msg['customfields_ticket'] as $label_ticket => $value_ticket)
			{
				$message_body = str_replace("[" . $label_ticket . "]", $value_ticket, $message_body);
			}
		}

		if (!empty($msg['customfields_event']))
		{
			foreach ($msg['customfields_event'] as $label_event => $value_event)
			{
				$message_body = str_replace("[" . $label_event . "]", $value_event, $message_body);
			}
		}

		return $message_body;
	}

	/**
	 * Get field id and type
	 *
	 * @param   int  $fname  name of field
	 *
	 * @return  void
	 */
	public function getFieldData($fname = '')
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'type,name', 'label', 'format')));
		$query->from($db->quoteName('#__tjfields_fields'));

		if ($fname)
		{
			$query->where($db->quoteName('name') . ' = ' . $db->quote($fname));
		}

		$db->setQuery($query);
		$field_data = $db->loadObject();

		return $field_data;
	}

	/**
	 * Get field value from tjfields component
	 *
	 * @param   int  $field_id    field_id
	 * @param   int  $content_id  id of the field to store
	 * @param   int  $client      jticketing
	 *
	 * @return  void
	 */
	public function getFieldValue($field_id = '', $content_id = '', $client = '')
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('value FROM #__tjfields_fields_value');
		$query->where('content_id=' . $content_id . ' AND field_id="' . $field_id . '" ' . ' AND client="' . $client . '" ' . $query_user_string);
		$db->setQuery($query);

		return $field_data_value = $db->loadResult();
	}

	/**
	 * Get All event details
	 *
	 * @param   int  $eventid  id of event
	 *
	 * @return  void
	 */
	public function getAllEventDetails($eventid)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$db                   = JFactory::getDBO();

		if (!$eventid)
		{
			return;
		}

		$integration = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = $db->getQuery(true);
			$query->select('*,published AS event_state');
			$query->from($db->quoteName('#__community_events'));
			$query->where($db->quoteName('id') . ' = ' . $db->quote($eventid));
		}
		elseif ($integration == 2)
		{
			$query = $db->getQuery(true);
			$query->select('e.*,e.state AS event_state,e.short_description AS summary,
			e.long_description AS description,e.image AS avatar, u.email');
			$query->from($db->quoteName('#__jticketing_events', 'e'));
			$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('e.created_by') . ')');
			$query->where($db->quoteName('e.id') . ' = ' . $db->quote($eventid));
		}
		elseif ($integration == 3)
		{
			$isEnabledJeventLocation = JPluginHelper::isEnabled('jevents', 'jevlocations');

			if (!empty($isEnabledJeventLocation))
			{
				$query = $db->getQuery(true);
				$query->select('event.evdet_id AS id, event.*, event.state AS event_state,
				event.summary as title, DATE(event.dtstart) AS startdate, DATE(event.dtend) AS enddate');
				$query->from('#__jevents_vevdetail as event');
				$query->where($db->quoteName('evdet_id') . ' = ' . $eventid);
				$query->select('loc.title');
				$query->join('LEFT', '#__jev_locations AS loc ON loc.loc_id = event.location');
			}
			else
			{
				$query = $db->getQuery(true);
				$query->select('event.evdet_id AS id, event.*, event.state AS event_state,
				event.summary as title, DATE(event.dtstart) AS startdate, DATE(event.dtend) AS enddate');
				$query->from('#__jevents_vevdetail as event');
				$query->where($db->quoteName('evdet_id') . ' = ' . $eventid);
			}
		}
		elseif ($integration == 4)
		{
			$query = $db->getQuery(true);
			$query->select('event.address AS location,event.title AS summary,event.state AS event_state,
			event.description,event.creator_uid AS created_by,event.title, type.available as total_ticket,
			type.title as type,type.count as ticket,type.count as count,type.price,
			 DATE((event_det.start ) ) as startdate,DATE((event_det.end)) as enddate,event.id');
			$query->from($db->quoteName('#__social_clusters', 'event'));
			$query->from($db->quoteName('#__social_events_meta', 'event_det'));
			$query->from($db->quoteName('#__jticketing_types', 'type'));
			$query->from($db->quoteName('#__jticketing_integration_xref', 'integr_xref'));
			$query->where($db->quoteName('integr_xref.eventid') . ' = ' . $db->quoteName('event.id'));
			$query->where($db->quoteName('event.id') . ' = ' . $db->quoteName('event_det.cluster_id'));
			$query->where($db->quoteName('integr_xref.source') . ' = ' . $db->quote('com_easysocial'));
			$query->where($db->quoteName('integr_xref.id') . ' = ' . $db->quoteName('type.eventid'));
			$query->where($db->quoteName('event.id') . ' = ' . $db->quote($eventid));
		}

		$db->setQuery($query);
		$eventdata = new stdClass;
		$eventdata = $db->loadObject();

		if ($integration == 3)
		{
			$isEnabledJeventLocation = JPluginHelper::isEnabled('jevents', 'jevlocations');

			if (isset($eventdata->dtstart))
			{
				$eventdata->startdate = date('Y-m-d H:i:s', $eventdata->dtstart);
			}

			if (isset($eventdata->dtend))
			{
				$eventdata->enddate = date('Y-m-d H:i:s', $eventdata->dtend);
			}

			if (!empty($isEnabledJeventLocation))
			{
				if (!empty($eventdata->title))
				{
					$eventdata->location = $eventdata->title;
				}
			}
		}

		if ($integration == 4)
		{
			require_once JPATH_ROOT . '/administrator/components/com_easysocial/includes/foundry.php';
			$event = new StdClass;

			if (!empty($eventid))
			{
				$event = new stdClass;
				$event = FD::event($eventid);

				if (!empty($event))
				{
					$eventdata->startdate = $event->getEventStart()->format('Y-m-d H:i:s', true);
					$eventdata->enddate   = $event->getEventEnd()->format('Y-m-d H:i:s', true);

					if (!empty($event))
					{
						$eventdata->avatar = $event->getAvatar();
					}
				}
			}
		}

		if (!empty($eventid))
		{
			$eventdata->event_url = $jticketingmainhelper->getEventlink($eventid);
		}

		if ($integration == 2)
		{
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'media');
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'mediaxref');
			$modelMediaXref = JModelLegacy::getInstance('MediaXref', 'JTicketingModel');
			$modelMedia = JModelLegacy::getInstance('Media', 'JTicketingModel');

			$eventMainImage = $modelMediaXref->getEventMedia($eventdata->id, 'com_jticketing.event', 0);

			if (isset($eventMainImage[0]->media_id))
			{
				$mediaData = $modelMedia->getItem($eventMainImage[0]->media_id);
				$eventdata->avatar = $mediaData->media;
			}
		}

		return $eventdata;
	}

	/**
	 * Method to get event details in order
	 *
	 * @param   int  $eventid  event id
	 *
	 * @return  void
	 */
	public function getEventDetails($eventid)
	{
		$db                   = JFactory::getDBO();
		$jticketingmainhelper = new Jticketingmainhelper;
		$userid               = JFactory::getUser()->id;
		$integration          = $jticketingmainhelper->getIntegration();
		$evxref_id            = $jticketingmainhelper->getEventrefid($eventid);

		if (!$evxref_id)
		{
			return '';
		}

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__jticketing_types'));
		$query->where($db->quoteName('eventid') . '=' . $db->quote($evxref_id));
		$query->where('(' . $db->quoteName('available') . '> 0 OR' . $db->quoteName('unlimited_seats') . '= 1)');
		$db->setQuery($query);
		$eventdata = $db->loadObjectList();

		foreach ($eventdata as $type)
		{
			$hide_ticket_type       = 0;
			$type->hide_ticket_type = 0;

			if ($userid and $type->max_limit_ticket and $evxref_id)
			{
				$query = $db->getQuery(true);
				$query->select($db->quoteName('id'));
				$query->from($db->quoteName('#__jticketing_order'));
				$query->where($db->quoteName('event_details_id') . '=' . $db->quote($evxref_id));
				$query->where($db->quoteName('user_id') . '=' . $db->quote($userid));
				$query->where($db->quoteName('status') . '=' . $db->quote('C'));
				$query->where($db->quoteName('event_details_id') . '=' . $db->quote($evxref_id));
				$db->setQuery($query);
				$orderids = $db->loadObjectlist();

				if (!empty($orderids))
				{
					$order_items_cnt = 0;

					foreach ($orderids AS $order)
					{
						$query = $db->getQuery(true);
						$query->select('COUNT(id) as cnt');
						$query->from($db->qn('#__jticketing_order_items', 'o'));
						$query->where($db->qn('o.order_id') . ' = ' . $db->quote($order->id));
						$db->setQuery($query);
						$order_items_cnt_db = $db->loadResult();

						if ($order_items_cnt_db)
						{
							$order_items_cnt += $order_items_cnt_db;
						}
					}

					$type->no_of_purchased_by_me = $order_items_cnt;

					if ($type->no_of_purchased_by_me >= $type->max_limit_ticket)
					{
						$type->hide_ticket_type = 1;
						$hide_ticket_type++;
					}
				}
			}

			if ($type->state != 1)
			{
				$type->hide_ticket_type = 1;
			}
		}

		if (isset($hide_ticket_type) and $hide_ticket_type == count($eventdata) and $hide_ticket_type > 0)
		{
			$eventdata[0]->max_limit_crossed = 1;
		}

		$user   = JFactory::getUser();
		$groups = $user->getAuthorisedViewLevels();
		$guest  = $user->get('guest');
		$input  = JFactory::getApplication()->input;

		for ($i = 0; $i < count($eventdata); $i++)
		{
			$query = "SELECT v.id FROM #__viewlevels as v, #__jticketing_types as t WHERE t.access = '{$eventdata[$i]->access}'
			 AND eventid = {$evxref_id} AND v.id = t.access";
			$db->setQuery($query);
			$id = $db->loadResult();

			if (!in_array($id, $groups))
			{
				if ($guest && $id == 5)
				{
				}
				else
				{
					$eventdata[$i]->hide_ticket_type = 1;
					unset($eventdata[$i]->title);
					unset($eventdata[$i]->deposit_fee);
					unset($eventdata[$i]->count);
				}
			}
		}

		return $eventdata;
	}

	/**
	 * Method to get event creator
	 *
	 * @param   int  $eventid  event id from all event related tables for example for jomsocial pass jomsocial's event id
	 *
	 * @return  int  creator id
	 */
	public function getEventCreator($eventid)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$db                   = JFactory::getDBO();

		if (!$eventid)
		{
			return;
		}

		$integration = $jticketingmainhelper->getIntegration();
		$query = $db->getQuery(true);

		if ($integration == 1)
		{
			$query->select(array('creator'));
			$query->from($db->quoteName('#__community_events'));
			$query->where($db->quoteName('id') . " = " . $db->quote($eventid));
		}
		elseif ($integration == 2)
		{
			$query->select(array('created_by'));
			$query->from($db->quoteName('#__jticketing_events'));
			$query->where($db->quoteName('id') . " = " . $db->quote($eventid));
		}
		elseif ($integration == 3)
		{
			$query->select(array('created_by'));
			$query->from($db->quoteName('#__jevents_vevent'));
			$query->where($db->quoteName('detail_id') . " = " . $db->quote($eventid));
		}
		elseif ($integration == 4)
		{
			$query->select(array('creator_uid'));
			$query->from($db->quoteName('#__social_clusters'));
			$query->where($db->quoteName('id') . " = " . $db->quote($eventid));
		}

		$db->setQuery($query);
		$eventowner = $db->loadResult();

		return $eventowner;
	}

	/**
	 * Method to get event title based on order id
	 *
	 * @param   int  $ticketid  order id
	 *
	 * @return  string  Event title
	 */
	public function getEventTitle($ticketid)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$db                   = JFactory::getDBO();
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT a.title
			FROM #__community_events AS a, #__jticketing_order AS b,#__jticketing_integration_xref as c
			WHERE b.event_details_id=c.id
			AND a.id=c.eventid
			AND b.id=" . $ticketid . " AND c.source='com_community'";
		}
		elseif ($integration == 2)
		{
			$query = "SELECT a.title
			FROM #__jticketing_events AS a, #__jticketing_order AS b,#__jticketing_integration_xref as c
			WHERE c.id=b.event_details_id
			AND a.id=c.eventid
			AND b.id=" . $ticketid . " AND c.source='com_jticketing'";
		}
		elseif ($integration == 3)
		{
			$query = "SELECT event.summary,event.summary as title
			FROM #__jevents_vevdetail AS event, #__jticketing_order AS b,#__jticketing_integration_xref as c
			WHERE c.id=b.event_details_id
			AND event.evdet_id=c.eventid
			AND b.id=" . $ticketid . " AND c.source='com_jevents'";
		}
		elseif ($integration == 4)
		{
			$query = "SELECT a.title
			FROM #__social_clusters AS a, #__jticketing_order AS b, #__jticketing_integration_xref as c
			WHERE c.id=b.event_details_id
			AND a.id=c.eventid
			AND b.id=" . $ticketid . " AND c.source='com_easysocial'";
		}

		$db->setQuery($query);

		return $eventtitle = $db->loadResult();
	}

	/**
	 * Method to delete ticket types
	 *
	 * @param   int  $ticket_type_id  ticket type id to delete
	 * @param   int  $xrefid          xrefid from jticketing_integration_xref table
	 *
	 * @return  void
	 */
	public function DeleteTickettypes($ticket_type_id, $xrefid)
	{
		$db                = JFactory::getDBO();
		$ticket_type_idarr = (array) $ticket_type_id;
		$query             = "SELECT id FROM #__jticketing_types WHERE eventid=" . $xrefid;
		$db->setQuery($query);
		$type_ids = $db->loadColumn();
		$diff     = array_diff($type_ids, $ticket_type_idarr);
		$diffids  = implode("','", $diff);
		$query    = "DELETE FROM #__jticketing_types	WHERE id IN ('" . $diffids . "') AND eventid=" . $xrefid;
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}
	}

	/**
	 * Method to get event custom info
	 *
	 * @param   array  $ticketid  order id of data for tags
	 * @param   int    $field     int
	 *
	 * @return  void
	 */
	public function getEventcustominfo($ticketid, $field)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$db                   = JFactory::getDBO();

		if (!$ticketid)
		{
			return '';
		}

		$integration = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT " . $field . "
			FROM #__community_events AS a, #__jticketing_order AS b,#__jticketing_integration_xref as c
			WHERE c.id=b.event_details_id
			AND c.id=" . $ticketid . " AND c.source='com_community'";
		}
		elseif ($integration == 2)
		{
			$query = "SELECT " . $field . "
			FROM #__jticketing_events AS a, #__jticketing_order AS b,#__jticketing_integration_xref as c
			WHERE c.id=b.event_details_id
			AND c.id=" . $ticketid . " AND c.source='com_jticketing'";
		}
		elseif ($integration == 3)
		{
			if ($field == 'title')
			{
				$field = 'summary as title';
			}

			$query = "SELECT " . $field . "
			FROM #__jevents_vevdetail AS a, #__jticketing_order AS b,#__jticketing_integration_xref as c
			WHERE c.id=b.event_details_id
			AND c.id=" . $ticketid . " AND c.source='com_jevents'";
		}
		elseif ($integration == 4)
		{
			$query = "SELECT " . $field . "
			FROM #__social_clusters AS a, #__jticketing_order AS b,#__jticketing_integration_xref as c
			WHERE c.id=b.event_details_id
			AND c.id=" . $ticketid . " AND c.source='com_easysocial'";
		}

		$db->setQuery($query);
		$eventowner = $db->loadResult();

		return $eventowner;
	}

	/**
	 * Method to get Event info
	 *
	 * @param   int  $eventid  event id for jomsocial,jevents,easysocial
	 *
	 * @return  void
	 */
	public function getEventInfo($eventid)
	{
		$jticketingmainhelper = new Jticketingmainhelper;

		if (!$eventid)
		{
			return '';
		}

		$db                   = JFactory::getDBO();
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = $db->getQuery(true);
			$query->select('title,ticket,eve.startdate,eve.enddate');
			$query->from($db->quoteName('#__jticketing_integration_xref', 'integr_xref'));
			$query->from($db->quoteName('#__community_events', 'eve'));
			$query->where($db->quoteName('integr_xref.eventid') . ' = ' . $db->quoteName('eve.id'));
			$query->where($db->quoteName('eve.id') . ' = ' . $db->quote($eventid));
			$query->where($db->quoteName('integr_xref.source') . ' = ' . $db->quote('com_community'));
		}
		elseif ($integration == 2)
		{
			$query = $db->getQuery(true);
			$query->select('title,eve.startdate,eve.enddate');
			$query->from($db->quoteName('#__jticketing_integration_xref', 'integr_xref'));
			$query->from($db->quoteName('#__jticketing_events', 'eve'));
			$query->where($db->quoteName('integr_xref.eventid') . ' = ' . $db->quoteName('eve.id'));
			$query->where($db->quoteName('eve.id') . ' = ' . $db->quote($eventid));
			$query->where($db->quoteName('integr_xref.source') . ' = ' . $db->quote('com_jticketing'));
		}
		elseif ($integration == 3)
		{
			$query = $db->getQuery(true);
			$query->select('event.summary as title,type.available as total_ticket,type.title as type,
			type.count as ticket,type.count as count,type.price,
			DATE( FROM_UNIXTIME( event.dtstart ) ) as startdate,DATE(FROM_UNIXTIME(event.dtend)) as enddate');
			$query->from($db->quoteName('#__jevents_vevdetail', 'event'));
			$query->from($db->quoteName('#__jticketing_types', 'type'));
			$query->from($db->quoteName('#__jticketing_integration_xref', 'integr_xref'));
			$query->where($db->quoteName('integr_xref.eventid') . ' = ' . $db->quoteName('event.evdet_id'));
			$query->where($db->quoteName('integr_xref.id') . ' = ' . $db->quoteName('type.eventid'));
			$query->where($db->quoteName('event.evdet_id') . ' = ' . $db->quote($eventid));
		}
		elseif ($integration == 4)
		{
			$query = $db->getQuery(true);
			$query->select('event.title, type.available as total_ticket,type.title as type,
			type.count as ticket,type.count as count,type.price, DATE((event_det.start ) ) as startdate,
			DATE((event_det.end)) as enddate');
			$query->from($db->quoteName('#__social_clusters', 'event'));
			$query->from($db->quoteName('#__social_events_meta', 'event_det'));
			$query->from($db->quoteName('#__jticketing_types', 'type'));
			$query->from($db->quoteName('#__jticketing_integration_xref', 'integr_xref'));
			$query->where($db->quoteName('integr_xref.eventid') . ' = ' . $db->quoteName('event.id'));
			$query->where($db->quoteName('event.id') . ' = ' . $db->quoteName('event_det.cluster_id'));
			$query->where($db->quoteName('integr_xref.source') . ' = ' . $db->quote('com_easysocial'));
			$query->where($db->quoteName('integr_xref.id') . ' = ' . $db->quoteName('type.eventid'));
			$query->where($db->quoteName('event.id') . ' = ' . $db->quote($eventid));
		}

		$db->setQuery($query);
		$eventtitle = $db->loadObjectlist();

		return $eventtitle;
	}

	/**
	 * Method to get available tickets
	 *
	 * @param   int  $eventid  event id for jomsocial,jevents,easysocial
	 *
	 * @return  void
	 */
	public function getAvailableTickets($eventid)
	{
		$jticketingmainhelper = new Jticketingmainhelper;

		if (!$eventid)
		{
			return '';
		}

		$evxref_id = $jticketingmainhelper->getEventrefid($eventid);

		if (!$evxref_id)
		{
			return -1;
		}

		$db          = JFactory::getDBO();
		$integration = $jticketingmainhelper->getIntegration();

		if ($integration == 3 or $integration == 2)
		{
			$query = $db->getQuery(true);
			$query->select('sum(' . $db->quoteName('count') . ')');
			$query->from($db->quoteName('#__jticketing_types'));
			$query->where($db->quoteName('eventid') . " = " . $db->quote($evxref_id));
			$db->setquery($query);
			$available_tickets = $db->loadResult($query);

			if ($available_tickets)
			{
				return $available_tickets;
			}

			return 0;
		}

		$query = $db->getQuery(true);
		$query->select('sum(' . $db->quoteName('ticketscount') . ') As ticketcnt');
		$query->from($db->quoteName('#__jticketing_order', 'a'));
		$query->where($db->quoteName('a.transaction_id') . ' IS NOT NULL');
		$query->where($db->quoteName('a.status') . ' LIKE' . $db->quote('C'));
		$query->where($db->quoteName('a.event_details_id') . " = " . $db->quote($evxref_id));

		$db->setQuery($query);
		$soldtickets = $db->loadResult();

		if ($integration == 1)
		{
			$query = $db->getQuery(true);
			$query->select($db->quoteName('ticket'));
			$query->from($db->quoteName('#__community_events', 'b'));
			$query->where($db->quoteName('b.id') . " = " . $db->quote($eventid));
		}

		$totalticket = $db->loadResult();

		if ($totalticket == 0)
		{
			return -1;
		}
		else
		{
			$AvailableTickets = $totalticket - $soldtickets;
		}

		if ($AvailableTickets >= 1)
		{
			if ($integration == 1)
			{
				return $AvailableTickets - 1;
			}
			elseif ($integration == 2)
			{
				return $AvailableTickets;
			}
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Method to get all ticket details
	 *
	 * @param   int  $eventid   integration_xref id
	 * @param   int  $ticketid  order id of event
	 *
	 * @return  object  $result  all ticket details
	 */
	public function getticketDetails($eventid = '', $ticketid = '')
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();
		$db                   = JFactory::getDBO();
		$evxref_id            = $jticketingmainhelper->getEventrefid($eventid);
		$com_params     = JComponentHelper::getParams('com_jticketing');
		$qr_code_width  = $com_params->get('qr_code_width', 80);
		$qr_code_height = $com_params->get('qr_code_height', 80);

		if (!$evxref_id)
		{
			return '';
		}

		$query = "SELECT ordertble.cdate AS cdate,orderitem.order_id As id,ordertble.name AS name,ordertble.user_id,ordertble.amount AS amount,
		ticket_types.price,ticket_types.price AS totalamount,
		ticket_types.title AS ticket_type_title,ticket_types.count AS ticket_type_count,
		ticket_types.desc AS ticket_type_desc,orderitem.type_id AS type_id,orderitem.ticketcount AS ticketscount,
		ordertble.status AS STATUS,orderitem.type_id,orderitem.attendee_id AS attendee_id,orderitem.id AS order_items_id
		FROM #__jticketing_order AS ordertble,#__jticketing_order_items AS orderitem,#__jticketing_types AS ticket_types
		WHERE ticket_types.eventid=ordertble.event_details_id AND ticket_types.id=orderitem.type_id
		AND orderitem.order_id=ordertble.id AND orderitem.id=" . $ticketid . " AND ordertble.event_details_id=" . $evxref_id;

		$db->setQuery($query);
		$result      = $db->loadObject();
		$result->eid = $eventid;
		$eventdata   = $this->getAllEventDetails($eventid);

		if (empty($eventdata))
		{
			return;
		}

		$result->location  = $eventdata->location;
		$result->startdate = $eventdata->startdate;
		$result->enddate   = $eventdata->enddate;

		if (isset($eventdata->booking_start_date))
		{
			$result->booking_start_date = $eventdata->booking_start_date;
		}

		if (isset($eventdata->venue))
		{
			$result->venue = $eventdata->venue;
		}

		if (isset($eventdata->online_events))
		{
			$result->online_events = $eventdata->online_events;
		}

		if (isset($eventdata->jt_params))
		{
			$result->jt_params = $eventdata->jt_params;
		}

		if (isset($eventdata->booking_end_date))
		{
			$result->booking_end_date = $eventdata->booking_end_date;
		}

		$result->title   = $eventdata->title;
		$result->eventnm = $eventdata->title;

		if (isset($eventdata->created_by))
		{
			$result->creator = $eventdata->created_by;
		}

		$result->summary     = $eventdata->summary;
		$result->description = $eventdata->description;

		if (isset($eventdata->avatar) || isset($eventdata->cover))
		{
			if (!empty($eventdata->avatar))
			{
				$result->avatar = $eventdata->avatar;
			}
			else
			{
				isset($eventdata->cover) ? $eventdata->cover : $eventdata->cover = '';
				$result->cover = $eventdata->cover;
			}
		}

		// Custom Field Ticket
		if (!empty($result->attendee_id))
		{
			$extraFieldslabel = $this->extraFieldslabel($evxref_id, $result->attendee_id);
			$ticketfields     = array();
			$j                = 0;
			$ticketfields     = new StdClass;

			foreach ($extraFieldslabel as $efl)
			{
				foreach ($efl->attendee_value as $key_attendee => $eflav)
				{
					$name = '';

					if ($result->attendee_id == $key_attendee)
					{
						$name                = $efl->name;
						$ticketfields->$name = $eflav->field_value;
						$j++;
						$i = 1;
						break;
					}
				}
			}
		}

		if (!empty($ticketfields))
		{
			$result->customfields_ticket = $ticketfields;
		}

		$eventpathmodel = JPATH_SITE . '/components/com_jticketing/models/event.php';

		if (!class_exists('JticketingModelEvent'))
		{
			JLoader::register('JticketingModelEvent', $eventpathmodel);
			JLoader::load('JticketingModelEvent');
		}

		$JticketingModelEvent = new JticketingModelEvent;
		$extradataobj         = $JticketingModelEvent->getDataExtra($eventid);
		$event                = new Stdclass;
		$event->extradata     = new Stdclass;

		if (!empty($extradataobj))
		{
			// Customfields Event
			foreach ($extradataobj AS $extrda)
			{
				if (!empty($extrda->name) and !empty($extrda->value))
				{
					$nm                    = $extrda->name;
					$event->extradata->$nm = $extrda->value;
				}
			}

			$result->customfields_events = $event->extradata;
		}

		if ($integration == 2)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('v.country', 'v.state_id', 'v.online_provider', 'v.name', 'v.city', 'v.address', 'v.zipcode')));
			$query->from($db->quoteName('#__jticketing_venues', 'v'));
			$query->where($db->quoteName('v.id') . ' = ' . $db->quote($result->venue));

			// Join over the venue table
			$query->select($db->quoteName(array('con.country'), array('coutryName')));
			$query->join('LEFT', $db->quoteName('#__tj_country', 'con') . ' ON (' . $db->quoteName('con.id') . ' = ' . $db->quoteName('v.country') . ')');

			// Join over the venue table
			$query->select($db->quoteName(array('r.region')));
			$query->join('LEFT', $db->quoteName('#__tj_region', 'r') . ' ON (' . $db->quoteName('r.id') . ' = ' . $db->quoteName('v.state_id') . ')');

			$db->setQuery($query);
			$location_data   = $db->loadObject();

			if ($result->online_events == "1")
			{
				if ($location_data->online_provider == "plug_tjevents_adobeconnect")
				{
					$location = JText::_('COM_JTICKETING_ADOBECONNECT_PLG_NAME') . " - " . $location_data->name;
				}
			}
			elseif($result->online_events == "0")
			{
				if ($result->venue != "0")
				{
					$name = $location_data->name . " : " . JText::_('COM_JTICKETING_BILLIN_ADDR') . "- " . $location_data->address;
					$city = ", " . $location_data->city . ", " . $location_data->region . ", " . $location_data->coutryName;
					$zipcode = ", " . JText::_('COM_JTICKETING_FORM_LBL_VENUE_ZIPCODE') . " - " . $location_data->zipcode;
					$result->location = $name . $city . $zipcode;
				}
				else
				{
					$result->location = $result->location;
				}
			}
			else
			{
				echo "-";
			}
		}
		else
		{
			$result->location = $result->location;
		}

		$qrstring                 = "Booking ID:" . JText::_("TICKET_PREFIX") . $result->id . '-' . $result->order_items_id;
		$result->ticket_id                 = urlencode($qrstring);
		$qr_url                   = "http://chart.apis.google.com/chart?cht=qr&chs=";
		$qrimage                  = $qr_url . $qr_code_width . "x" . $qr_code_height . "&chl=" . $qrstring . "&chld=H|0";
		$result->qr_url              = $qrimage;

		return $result;
	}

	/**
	 * Method to get event ticket types
	 *
	 * @param   int  $eventid  integration_xref id
	 *
	 * @return  array  $result  all ticket details
	 */
	public function getEvent_ticketTypes($eventid)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$evxref_id            = $jticketingmainhelper->getEventrefid($eventid);

		if (!$evxref_id)
		{
			return '';
		}

		$db    = JFactory::getDBO();
		$query = "select max(price) as price	from  #__jticketing_types  where eventid=" . $evxref_id;
		$db->setQuery($query);
		$price = $db->loadResult();
		$query = "select *	from  #__jticketing_types  where eventid=" . $evxref_id . " AND price=" . $price;
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Method to get event name
	 *
	 * @param   int  $eventid  event id of jomsocial,jevents,easysocial
	 *
	 * @return  void
	 */
	public function getEventName($eventid)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();
		$input                = JFactory::getApplication()->input;
		$mainframe            = JFactory::getApplication();
		$option               = $input->get('option');
		$eventid              = $input->get('event', '', 'INT');

		if ($integration == 1)
		{
			$query = "SELECT title FROM #__community_events
			  WHERE id = {$eventid}";
		}
		elseif ($integration == 2)
		{
			$query = "SELECT title FROM #__jticketing_events
			  WHERE id = {$eventid}";
		}
		elseif ($integration == 4)
		{
			$query = "SELECT title FROM #__social_clusters
			  WHERE id = {$eventid}";
		}

		return $query;
	}

	/**
	 * Method to get all events by buyer
	 *
	 * @param   int  $userid  user id
	 * @param   int  $params  array of parameters
	 *
	 * @return  void
	 */
	public function geteventnamesBybuyer($userid, $params = array())
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT events.id AS id,events.title AS title,integr_xref.id as integrid,
			ticket.event_details_id as event_details_id,events.startdate as startdate,events.enddate as enddate
			FROM #__jticketing_order AS ticket, #__community_events AS events,#__jticketing_integration_xref AS integr_xref
			WHERE integr_xref.id = ticket.event_details_id
			AND integr_xref.eventid = events.id
			AND integr_xref.source='com_community'";
		}
		elseif ($integration == 2)
		{
			$query = "SELECT events.id AS id,events.title AS title,integr_xref.id as integrid,
			ticket.event_details_id as event_details_id,events.startdate as startdate,
			events.enddate as enddate,events.short_description,events.long_description
			FROM #__jticketing_order AS ticket, #__jticketing_events AS events,#__jticketing_integration_xref AS integr_xref
			WHERE integr_xref.id = ticket.event_details_id
			AND integr_xref.eventid = events.id
			AND integr_xref.source='com_jticketing'";
		}
		elseif ($integration == 3)
		{
			$query = "SELECT events.evdet_id AS id,events.summary AS title,
			integr_xref.id as integrid,ticket.event_details_id as event_details_id
			FROM #__jticketing_order AS ticket, #__jevents_vevdetail AS events,#__jticketing_integration_xref AS integr_xref
			WHERE integr_xref.id = ticket.event_details_id
			AND integr_xref.eventid =events.evdet_id
			AND integr_xref.source='com_jevents'";
		}
		elseif ($integration == 4)
		{
			$query = "SELECT events.id AS id,events.title,ticket.status,ticket.event_details_id as event_details_id
			FROM #__jticketing_order AS ticket, #__social_clusters AS events,#__jticketing_integration_xref  AS integr_xref
			WHERE integr_xref.id = ticket.event_details_id
			AND integr_xref.eventid = events.id
			AND integr_xref.source='com_easysocial'
			";
		}

		if ($userid)
		{
			$query .= " AND ticket.user_id='" . $userid . "'";
		}

		if (!empty($params['only_completed_orders']))
		{
			$query .= " AND ticket.status='C'";
		}

		if ($integration == 3)
		{
			$query .= " GROUP BY events.evdet_id";
		}
		else
		{
			$query .= " GROUP BY events.id";
		}

		if (!empty($params['no_of_events']))
		{
			$query .= " LIMIT 0," . $params['no_of_events'];
		}

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$results = $db->loadObjectlist();

		if (!empty($results))
		{
			foreach ($results AS $result)
			{
				$query = "SELECT checkin FROM #__jticketing_checkindetails  WHERE
				eventid=" . $result->event_details_id;
				$db->setQuery($query);
				$checkin = $db->loadResult();

				$result->checkin = 0;

				if ($result->checkin)
				{
					$result->checkin = 1;
				}
			}
		}

		return $results;
	}

	/**
	 * Method to get all events created by user
	 *
	 * @param   int  $user  user id
	 *
	 * @return  void
	 */
	public function geteventnamesByCreator($user = '')
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT events.id AS id,integr_xref.id as integrid,events.title AS title,ticket.status
			FROM #__jticketing_order AS ticket INNER JOIN  #__jticketing_integration_xref  AS integr_xref
			ON integr_xref.id = ticket.event_details_id
			INNER JOIN #__community_events AS events
			ON integr_xref.eventid = events.id
			AND integr_xref.source='com_community'";
		}
		elseif ($integration == 2)
		{
			$query = "SELECT events.id AS id,events.title AS title,ticket.status,events.short_description AS short_description,events.startdate AS start_date,
			events.enddate AS end_date
			FROM #__jticketing_order AS ticket, #__jticketing_events AS events,#__jticketing_integration_xref
			AS integr_xref
			WHERE integr_xref.id = ticket.event_details_id
			AND integr_xref.eventid = events.id
			AND integr_xref.source='com_jticketing'";
		}
		elseif ($integration == 3)
		{
			$query = "SELECT events.evdet_id AS id,events.summary AS title,ticket.status
			FROM #__jticketing_order AS ticket, #__jevents_vevdetail AS events,#__jticketing_integration_xref  AS integr_xref,
			#__jevents_vevent as vevent
			WHERE integr_xref.id = ticket.event_details_id
			AND integr_xref.eventid = events.evdet_id
			AND integr_xref.source='com_jevents'
			AND events.evdet_id=vevent.detail_id
			";
		}
		elseif ($integration == 4)
		{
			$query = "SELECT events.id AS id,events.title,ticket.status
			FROM #__jticketing_order AS ticket, #__social_clusters AS events,#__jticketing_integration_xref  AS integr_xref
			WHERE integr_xref.id = ticket.event_details_id
			AND integr_xref.eventid = events.id
			AND integr_xref.source='com_easysocial'
			";
		}

		if ($user)
		{
			$user = JFactory::getUser();

			if ($integration == 3)
			{
				$query .= " AND vevent.created_by ='" . $user->id . "'";
			}
			elseif ($integration == 2)
			{
				$query .= " AND events.created_by ='" . $user->id . "'";
			}
			elseif ($integration == 4)
			{
				$query .= " AND events.creator_uid='" . $user->id . "'";
			}
			else
			{
				$query .= " AND events.creator='" . $user->id . "'";
			}
		}

		if ($integration == 3)
		{
			$query .= " GROUP BY events.evdet_id";
		}
		else
		{
			$query .= " GROUP BY events.id";
		}

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$result = $db->loadObjectlist();

		return $result;
	}

	/**
	 * Method to get all events
	 *
	 * @param   array  $params  user id
	 *
	 * @return  void
	 */
	public function getEvents($params = array())
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT id,title,startdate,enddate,catid AS category_id	FROM  #__community_events AS events
			WHERE published=1";

			if ($params['category_id'])
			{
				$query .= " AND events.catid=" . $params['category_id'];
			}
		}
		elseif ($integration == 2)
		{
			$user               = JFactory::getUser();
			$allowedViewLevels  = JAccess::getAuthorisedViewLevels($user->id);
			$implodedViewLevels = implode('","', $allowedViewLevels);
			$query              = "SELECT id,title,startdate,enddate,catid AS category_id
			FROM	#__jticketing_events AS events WHERE state=1";

			if (!empty($params['category_id']))
			{
				$query .= " AND events.catid=" . $params['category_id'];
			}

			$query .= ' AND access IN ("' . $implodedViewLevels . '")';
		}
		elseif ($integration == 3)
		{
			$query = "SELECT events.evdet_id AS id,summary AS title,eventmain.catid AS category_id,
			DATE( FROM_UNIXTIME( events.dtstart ) ) as startdate,
			DATE(FROM_UNIXTIME(events.dtend)) as enddate
			FROM  #__jevents_vevdetail AS events,#__jevents_vevent AS eventmain WHERE events.state=1 AND
			events.evdet_id=eventmain.detail_id";

			if ($params['category_id'])
			{
				$query .= " AND eventmain.catid=" . $params['category_id'];
			}
		}
		elseif ($integration == 4)
		{
			require_once JPATH_ROOT . '/administrator/components/com_easysocial/includes/foundry.php';
			$query = "SELECT events.id AS id,events.title,eventdet.start AS startdate,eventdet.end AS enddate,events.category_id
			FROM #__social_clusters AS events,#__social_events_meta as eventdet
			WHERE events.state=1 AND events.cluster_type LIKE 'event' AND events.id=eventdet.cluster_id";

			if ($params['category_id'])
			{
				$query .= " AND events.category_id=" . $params['category_id'];
			}
		}

		$query .= " order by startdate";
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$result = $db->loadAssocList();

		foreach ($result as $k => $v)
		{
			if ($integration == 4)
			{
				$event = new StdClass;

				if (!empty($eventid))
				{
					$event = new stdClass;
					$event = FD::event($v['id']);

					if (!empty($event))
					{
						$result[$k]['startdate'] = $event->getEventStart()->format('Y-m-d H:i:s', true);
						$result[$k]['enddate'] = $event->getEventEnd()->format('Y-m-d H:i:s', true);

						if (!empty($event))
						{
							$eventdata->avatar = $event->getAvatar();
						}
					}
				}
			}

			$result[$k]['startdate'] = JHtml::date($result[$k]['startdate'], JText::_('Y-m-d H:i:s'), true);
			$result[$k]['enddate'] = JHtml::date($result[$k]['enddate'], JText::_('Y-m-d H:i:s'), true);
			$result[$k]['url'] = $jticketingmainhelper->getEventlink($v['id']);
		}

		return $result;
	}

	/**
	 * Method to get event id from integration id
	 *
	 * @param   int  $event_integration_id  xref id
	 * @param   int  $source                source like jomsocial
	 *
	 * @return  int  $eventid event id
	 */
	public function getEventID_FROM_INTEGRATIONID($event_integration_id, $source = '')
	{
		$query = "select eventid FROM  #__jticketing_integration_xref  WHERE id=" . $event_integration_id;

		// Source like jticketing/easysocial
		if ($source)
		{
			$query .= " AND source LIKE '" . $source . "'";
		}

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$eventid = $db->loadResult();

		return $eventid;
	}

	/**
	 * Method to get event id from integration id
	 *
	 * @param   int  $integration  xref id
	 *
	 * @return  int  $eventid event id
	 */
	public function getSourceName($integration)
	{
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

		return $source;
	}

	/**
	 * Method to delete unwanted files
	 *
	 * @param   array  $data  filename containing data
	 *
	 * @return  void
	 */
	public function deleteUnwantedfiles($data)
	{
		$tmp_path = JPATH_SITE . '/components/com_jticketing/helpers/dompdf/tmp/';

		if (!empty($data['pdf']))
		{
			if (is_array($data['pdf']))
			{
				foreach ($data['pdf'] as $pdf)
				{
					if (file_exists($pdf))
					{
						unlink($pdf);
					}
				}
			}
			else
			{
				if (file_exists($data['pdf']))
				{
					unlink($data['pdf']);
				}
			}
		}

		if (!empty($data['qr_code']))
		{
			if (is_array($data['qr_code']))
			{
				foreach ($data['qr_code'] as $pdf)
				{
					if (file_exists($tmp_path . $pdf))
					{
						unlink($tmp_path . $pdf);
					}
				}

				foreach ($data['ics_file'] as $ics_file)
				{
					if (file_exists($tmp_path . $ics_file))
					{
						unlink($tmp_path . $ics_file);
					}
				}
			}
			else
			{
				if (file_exists($tmp_path . $data['qr_code']))
				{
					unlink($tmp_path . $data['qr_code']);
				}
			}
		}
	}

	/**
	 * Method to get all billing data based on order id and userid
	 *
	 * @param   int  $order_id  id of jticketing_order table
	 * @param   int  $userid    userid
	 *
	 * @return  object  $row  billing data
	 */
	public function getbillingdata($order_id = '', $userid = '')
	{
		$where_str = '';
		$where     = array();

		if ($userid)
		{
			$where[] = " user_id=" . $userid;
		}

		if ($order_id)
		{
			$where[] = " order_id=" . $order_id;
		}

		if (!empty($where))
		{
			$where_str = " WHERE " . implode(' AND ', $where);
		}

		$db    = JFactory::getDBO();
		$query = "SELECT * FROM #__jticketing_users " . $where_str;
		$db->setQuery($query);

		return $row = $db->loadObject();
	}

	/**
	 * Method to email after order gets completed
	 *
	 * @param   object  $row       id of jticketing_order table
	 * @param   int     $ticketid  userid
	 * @param   int     $link      whether to pass the link in ticket
	 *
	 * @return  void
	 */
	public function afterordermail($row, $ticketid, $link)
	{
		$com_params           = JComponentHelper::getParams('com_jticketing');
		$send_ics_file        = 1;
		$pdf_attach_in_mail   = $com_params->get('pdf_attach_in_mail');
		$jticketingmainhelper = new Jticketingmainhelper;
		$where                = '';
		$db                   = JFactory::getDBO();
		$integration          = $jticketingmainhelper->getIntegration();
		$buyer                = JFactory::getUser($row->user_id);
		$return               = $jticketingmainhelper->getTimezoneString($row->eid);
		$datetoshow           = $return['startdate'] . '-' . $return['enddate'];
		$qr_code_width  = $com_params->get('qr_code_width', 80);
		$qr_code_height = $com_params->get('qr_code_height', 80);

		if (!empty($return['eventshowtimezone']))
		{
			$datetoshow .= '<br/>' . $return['eventshowtimezone'];
		}

		$app             = JFactory::getApplication();
		$sitename        = $app->getCfg('sitename');
		$data['subject'] = JText::sprintf('TICKET_SUBJECT', $sitename, ucfirst($row->title));
		$cssdata         = "";
		$cssfile         = JPATH_SITE . "/components/com_jticketing/assets/css/email.css";
		$cssdata .= file_get_contents($cssfile);
		$row->title   = $row->eventnm;
		$jj           = 0;
		$message_body = '';
		$pdflink      = '';
		$ticket_html  = array();

		if ($integration == 4)
		{
			require_once JPATH_ROOT . '/administrator/components/com_easysocial/includes/foundry.php';
			$event  = FD::event($row->eid);
			$avatar = str_replace(JUri::root(), '', $event->getAvatar());
		}

		if ($integration == 4)
		{
			$row->avatar = $avatar;
		}

		if ($integration == 2)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('v.country', 'v.state_id', 'v.online_provider', 'v.name', 'v.city', 'v.address', 'v.zipcode')));
			$query->from($db->quoteName('#__jticketing_venues', 'v'));
			$query->where($db->quoteName('v.id') . ' = ' . $db->quote($row->venue));

			// Join over the venue table
			$query->select($db->quoteName(array('con.country'), array('coutryName')));
			$query->join('LEFT', $db->quoteName('#__tj_country', 'con') . ' ON (' . $db->quoteName('con.id') . ' = ' . $db->quoteName('v.country') . ')');

			// Join over the venue table
			$query->select($db->quoteName(array('r.region')));
			$query->join('LEFT', $db->quoteName('#__tj_region', 'r') . ' ON (' . $db->quoteName('r.id') . ' = ' . $db->quoteName('v.state_id') . ')');

			$db->setQuery($query);
			$location_data   = $db->loadObject();

			if ($row->online_events == "1")
			{
				if ($location_data->online_provider == "plug_tjevents_adobeconnect")
				{
					$location = JText::_('COM_JTICKETING_ADOBECONNECT_PLG_NAME') . " - " . $location_data->name;
				}
			}
			elseif($row->online_events == "0")
			{
				if ($row->venue != "0")
				{
					$name = $location_data->name . " : " . JText::_('COM_JTICKETING_BILLIN_ADDR') . "- " . $location_data->address;
					$city = ", " . $location_data->city . ", " . $location_data->region . ", " . $location_data->coutryName;
					$zipcode = ", " . JText::_('COM_JTICKETING_FORM_LBL_VENUE_ZIPCODE') . " - " . $location_data->zipcode;
					$row->location = $name . $city . $zipcode;
				}
				else
				{
					$row->location = $row->location;
				}
			}
			else
			{
				echo "-";
			}
		}
		else
		{
			$row->location = $row->location;
		}

		$qrstring                 = JText::_("TICKET_PREFIX") . $row->id . '-' . $row->order_items_id;
		$qrstring                 = urlencode($qrstring);
		$qr_url                   = "http://chart.apis.google.com/chart?cht=qr&chs=";
		$qrimage                  = $qr_url . $qr_code_width . "x" . $qr_code_height . "&chl=" . $qrstring . "&chld=H|0";
		$qr_path_url              = $qrimage;

		// Generate random no and attach it to PDF name
		$random_no              = JUserHelper::genRandomPassword(3);
		$pdfname1               = 'Ticket_' . $row->eventnm . '_' . $random_no . '_' . $ticketid . "_" . $row->order_items_id . ".pdf";
		$pdfname                = JPATH_SITE . "/tmp/" . $pdfname1;
		$row->useforemail       = 1;
		$row->nofotickets       = 1;
		$row->totalprice        = $row->totalamount;
		$row->ticketprice       = $row->amount;
		$row->attendee_id       = $row->attendee_id;
		$row->order_items_id    = $row->order_items_id;
		$row->ticket_type_title = $row->ticket_type_title;
		$row->send_html_only    = 0;
		$message_body_pdf       = '';
		$message_body_pdf       = $jticketingmainhelper->getticketHTML($row, $usesession = 0);
		$event                  = $row;
		ob_start();
		require JPATH_SITE . '/components/com_jticketing/views/event/tmpl/default_ical.php';
		$output = ob_get_contents();
		$output = strtr($output, array("\r\n" => "\r\n", "\r" => "\r\n", "\n" => "\r\n"));
		ob_end_clean();

		$file    = str_replace(" ", "_", $row->eventnm);
		$file    = str_replace("/", "", $row->eventnm);
		$icsnm   = $file . '_' . $random_no . '_' . ".ics";
		$icsname = JPATH_SITE . "/libraries/techjoomla/dompdf/tmp/" . $icsnm;

		if (file_exists($icsname))
		{
			$file = fopen($icsname, "w");

			if ($file)
			{
				fwrite($file, $output);
				fclose($file);
				$data['pdf']['ics_file']      = $icsname;
			}
		}

		if ($pdf_attach_in_mail == 1)
		{
			if ($message_body_pdf)
			{
				$data['pdf']['0'] = $jticketingmainhelper->generatepdf($message_body_pdf, $pdfname, $download = 0);
			}
		}

		$row->send_html_only = 1;
		$message_body        = $jticketingmainhelper->getticketHTML($row, $usesession = 0);
		$buyer               = JFactory::getUser($row->user_id);

		if (!$row->user_id)
		{
			$buyrname = $row->name;
		}
		else
		{
			$buyrname = $buyer->name;
		}

		$data['message'] = $message_body;

		return $data;
	}

	/**
	 * Method to email before order gets completed
	 *
	 * @param   object  $row       order data
	 * @param   int     $ticketid  userid
	 * @param   int     $link      whether to pass the link in ticket
	 *
	 * @return  void
	 */
	public function beforeordermail($row, $ticketid, $link)
	{
	}

	/**
	 * Method to apply css to html
	 *
	 * @param   array   $prev     replacement data for tags
	 * @param   string  $cssdata  int
	 *
	 * @return  string  html with css applied
	 */
	public function getEmorgify($prev, $cssdata)
	{
		if (version_compare(phpversion(), '5.4', '>='))
		{
			require_once JPATH_SITE . "/components/com_jticketing/helpers/emogrifier.php";
		}
		else
		{
			require_once JPATH_SITE . "/components/com_jticketing/helpers/emogrifier_old.php";
		}

		if (!class_exists('Emogrifier'))
		{
			JLoader::register('Emogrifier', $path);
			JLoader::load('Emogrifier');
		}

		// Condition to check if mbstring is enabled
		if (!function_exists('mb_convert_encoding'))
		{
			echo JText::_("MB_EXT");

			return $prev;
		}

		$emogr      = new Emogrifier($prev, $cssdata);
		$emorg_data = $emogr->emogrify();

		return $emorg_data;
	}

	/**
	 * Method to get amount to pay for event owner
	 *
	 * @param   int  $userid  user id of event owner
	 *
	 * @return  void
	 */
	public function getsubtotalamount($userid = '')
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$db                   = JFactory::getDBO();
		$integration          = $jticketingmainhelper->getIntegration();
		$where                = '';
		$user                 = JFactory::getUser();
		$app                  = JFactory::getApplication();
		$admin                = $app->isAdmin();
		$userid               = $user->id;

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

		if ($admin)
		{
			$query = "SELECT *
				FROM #__jticketing_order as o
				INNER JOIN  #__users as u ON o.user_id=u.id
				INNER JOIN #__jticketing_integration_xref as xref ON xref.id=o.event_details_id
				WHERE o.status='C'";
		}
		else
		{
			if ($userid)
			{
				$query = "SELECT *
				FROM #__jticketing_order as o
				INNER JOIN  #__users as u ON o.user_id=u.id
				INNER JOIN #__jticketing_integration_xref as xref ON xref.id=o.event_details_id
				WHERE o.status='C'
				AND xref.userid=$userid";
			}
		}

		$query .= " AND xref.source='" . $source . "'";
		$db->setQuery($query);
		$totalearn = 0;
		$result    = $db->loadObjectlist();

		if (!empty($result))
		{
			$dataamt = 0;

			foreach ($result as $data)
			{
				$totalearn += $data->original_amount - $data->coupon_discount - $data->fee;
			}

			return $totalearn;
		}
	}

	/**
	 * Method to get amount already paid for event owner
	 *
	 * @param   int  $userid  user id of event owner
	 *
	 * @return  void
	 */
	public function getpaidamount($userid = '')
	{
		$where = '';
		$db    = JFactory::getDBO();

		if ($userid)
		{
			$where = "AND	user_id={$userid}	";
		}

		$query = "SELECT user_id,payee_name,transction_id,date,payee_id,amount
		FROM #__jticketing_ticket_payouts  	WHERE  status =1	" . $where;
		$db->setQuery($query);
		$totalearn = 0;
		$result    = $db->loadObjectlist();
		$totalpaid = 0;

		if (!empty($result))
		{
			foreach ($result as $data)
			{
				$totalpaid = $totalpaid + $data->amount;
			}
		}

		return $totalpaid;
	}

	/**
	 * Method to include jomsocial script files
	 *
	 * @return  void
	 */
	public function includeJomsocailscripts()
	{
	}

	/**
	 * Method to generate pdf from html
	 *
	 * @param   array   $html      replacement data for tags
	 * @param   string  $pdffile   name of pdf file
	 * @param   string  $download  int
	 *
	 * @return  string  html with css applied
	 */
	public function generatepdf($html, $pdffile, $download = 0)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		require_once  JPATH_SITE . "/libraries/techjoomla/dompdf/autoload.inc.php";

		if (JVERSION < '2.5.0')
		{
			foreach (JFolder::files($classpath) as $file)
			{
				JLoader::register(JFile::stripExt($file), $classpath . DS . $file);
			}
		}
		else
		{
			if (isset($funcs))
			{
				// Import the library loader if necessary.
				if (!class_exists('JLoader'))
				{
					require_once JPATH_PLATFORM . '/loader.php';
				}

				class_exists('JLoader') or die('pdf generation failed');

				// Setup the autoloaders.
				JLoader::setup();

				// Import the cms loader if necessary.
				if (version_compare(JVERSION, '2.5.6', 'le'))
				{
					if (!class_exists('JCmsLoader'))
					{
						require_once JPATH_PLATFORM . '/cms/cmsloader.php';
						JCmsLoader::setup();
					}
				}
				else
				{
					if (!class_exists('JLoader'))
					{
						require_once JPATH_PLATFORM . '/cms.php';
						require_once JPATH_PLATFORM . '/loader.php';
						JLoader::setup();
					}
				}
			}

			require_once JPATH_PLATFORM . '/loader.php';
		}

		$html = "<html><head><style>body { font-family: DejaVu Sans }</style>" . "<body>" . $html . "</body>" . "</head></html>";

		if (get_magic_quotes_gpc())
		{
			$html = stripslashes($html);
		}

		$title = 'event';
		$dompdf = new Dompdf;
		$dompdf->loadHtml($html);
		$dompdf->render();
		$output = $dompdf->output();
		file_put_contents($pdffile, $output);

		if ($download == 1)
		{
			header('Content-Description: File Transfer');
			header('Cache-Control: public');
			header('Content-Type: ' . $type);
			header("Content-Transfer-Encoding: binary");
			header('Content-Disposition: attachment; filename=' . basename($pdffile));
			header('Content-Length: ' . filesize($pdffile));
			ob_clean();
			flush();
			readfile($pdffile);
			jexit();
		}

		return $pdffile;
	}

	/**
	 * Method to chk if paid event or not
	 *
	 * @param   int  $eventid  eventid to chk if paid or not
	 *
	 * @return  int  id of event
	 */
	public function isPaidEvent($eventid)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$xrefid               = $jticketingmainhelper->getEventrefid($eventid);
		$db                   = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__jticketing_types'));
		$query->where($db->quoteName('eventid') . " = " . $db->quote($eventid));
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Method to chk if is free or not
	 *
	 * @param   int  $eventid  eventid to chk if paid or not
	 *
	 * @return  int  id of event
	 */
	public function isFreeEvent($eventid)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__jticketing_types'));
		$query->where($db->quoteName('eventid') . " = " . $db->quote($eventid));
		$query->where($db->quoteName('price') . " >0 ");
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * Method to chk if event ticket has already bought or not
	 *
	 * @param   int  $eventid  eventid
	 * @param   int  $userid   userid
	 *
	 * @return  id of
	 */
	public function isEventbought($eventid, $userid)
	{
		$db = JFactory::getDbo();
		$jticketingmainhelper = new Jticketingmainhelper;
		$xrefid               = $jticketingmainhelper->getEventrefid($eventid);

		if (!empty($userid))
		{
			$condition = $db->quoteName('user_id') . " = " . $db->quote($userid);
		}
		else
		{
			return 0;
		}

		if (empty($xrefid))
		{
			return 0;
		}

		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__jticketing_order'));
		$query->where($db->quoteName('status') . " = " . $db->quote('C'));
		$query->where($db->quoteName('event_details_id') . " = " . $db->quote($xrefid));
		$query->where($condition);
		$db->setQuery($query);
		$result = $db->loadResult();

		if ($result)
		{
			return 1;
		}
	}

	/**
	 * Method to replace the tags in the message body
	 *
	 * @param   int  $eventid  replacement data for tags
	 *
	 * @return  void
	 */
	public function getTimezoneString($eventid)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();
		$config               = JFactory::getConfig();
		$eventdata           = $jticketingmainhelper->getAllEventDetails($eventid);
		$date_format_to_show = JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM');
		$com_params = JComponentHelper::getParams('com_jticketing');
		$dateFormat = $com_params->get('date_format_show');
		$startDate  = JFactory::getDate($eventdata->startdate)->Format($dateFormat);
		$endDate    = JFactory::getDate($eventdata->enddate)->Format($dateFormat);
		$startTime = JFactory::getDate($eventdata->startdate)->Format(JText::_('COM_JTICKETING_TIME_FORMAT_AM_PM'));

		if ($eventdata->startdate == "0000-00-00")
		{
			$eventr['startdate'] = JText::_('COM_JTICKETING_NO_DATE');
		}
		else
		{
			$eventr['startdate'] = $startDate;
		}

		if ($eventdata->enddate == "0000-00-00")
		{
			$eventr['enddate'] = JText::_('COM_JTICKETING_NO_DATE');
		}
		else
		{
			$eventr['enddate'] = $endDate;
		}

		$eventr['start_time'] = $startTime;

		if (!empty($eventdata->offset))
		{
			$eventtimezone = Jtick_TimeHelper::getTimezone($eventdata->offset);
		}
		else
		{
			$config = JFactory::getConfig();

			if (JVERSION >= 3.0)
			{
				$offset = $config->get('config.offset');
			}

			if ($offset)
			{
				$eventtimezone = Jtick_TimeHelper::getTimezone($offset);
			}
		}

		if ($offset)
		{
			$eventr['eventshowtimezone'] = $eventtimezone;
		}

		return $eventr;
	}

	/**
	 * Method to apply coupon to transaction
	 *
	 * @param   int  $c_code  coupon code from jticketing_coupon table
	 *
	 * @return  object  $coupon
	 */
	public function getcoupon($c_code)
	{
		$user  = JFactory::getUser();
		$db    = JFactory::getDBO();
		$query = "SELECT value, val_type	FROM #__jticketing_coupon
		WHERE (from_date <= CURDATE() <= exp_date	OR from_date = '0000-00-00 00:00:00')
		AND (max_use >= (SELECT COUNT( api.coupon_code )	FROM #__jticketing_order AS api
		WHERE api.coupon_code = " . $db->quote($db->escape($c_code)) . " )	OR max_use =0)
		AND (max_per_user >= (SELECT COUNT( api.coupon_code)	FROM #__jticketing_order AS api
		WHERE api.coupon_code = " . $db->quote($db->escape($c_code)) . "
		AND api.user_id =" . $user->id . ")	OR max_per_user =0)	AND state =1
		AND code=" . $db->quote($db->escape($c_code));
		$db->setQuery($query);
		$coupon = $db->loadObjectList();

		return $coupon;
	}

	/**
	 * Method to get jomsocial toolbar header
	 *
	 * @return  html
	 */
	public function getJSheader()
	{
		$com_params      = JComponentHelper::getParams('com_jticketing');
		$show_js_toolbar = $com_params->get('show_js_toolbar');
		$html            = '';

		// Newly added for JS toolbar inclusion
		if (file_exists(JPATH_SITE . '/components/com_community'))
		{
			// Load the local XML file first to get the local version
			$xml       = JFactory::getXML(JPATH_ROOT . '/administrator/components/com_community/community.xml');
			$jsversion = (float) $xml->version;

			if ($jsversion >= 2.4 and $show_js_toolbar == 1)
			{
				require_once JPATH_SITE . '/components/com_community/libraries/core.php';
				require_once JPATH_ROOT . '/components/com_community/libraries/toolbar.php';
				$toolbar = CFactory::getToolbar();
				$tool    = CToolbarLibrary::getInstance();
				$html    = '<div id="community-wrap">';
				$html .= $tool->getHTML();
			}
		}

		return $html;
	}

	/**
	 * Method to get jomsocial toolbar footer
	 *
	 * @return  html
	 */
	public function getJSfooter()
	{
		$com_params      = JComponentHelper::getParams('com_jticketing');
		$show_js_toolbar = $com_params->get('show_js_toolbar');
		$html            = '';

		if (file_exists(JPATH_SITE . DS . 'components' . DS . 'com_community'))
		{
			// Load the local XML file first to get the local version
			$xml       = JFactory::getXML(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_community' . DS . 'community.xml');
			$jsversion = (float) $xml->version;

			if ($jsversion >= 2.4 and $show_js_toolbar == 1)
			{
				$html = '</div>';
			}
		}

		return $html;
	}

	/**
	 * Method to get backend sales report query
	 *
	 * @param   int  $creator  user id of
	 * @param   int  $eventid  eventid
	 * @param   int  $where    condition
	 *
	 * @return  string $query
	 */
	public function getSalesDataAdmin($creator = '', $eventid = '', $where = '')
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT a.order_id as order_id,sum(order_tax)as eorder_tax,sum(original_amount)as eoriginal_amount,
			sum(coupon_discount)as ecoupon_discount,  sum(amount)as eamount,sum(a.fee) as ecommission,
			sum(a.ticketscount) as eticketscount,b.id AS evid,a.*,b.title,b.thumb
			FROM #__jticketing_order AS a , #__community_events AS b,#__jticketing_integration_xref as integr
			WHERE a.event_details_id = integr.id
			AND  a.status='C' AND integr.eventid=b.id AND integr.source='com_community'
				" . $where;
		}
		elseif ($integration == 2)
		{
			$query = "SELECT a.order_id as order_id,sum(order_tax)as eorder_tax,sum(original_amount)as eoriginal_amount,
			sum(coupon_discount)as ecoupon_discount,SUM( a.amount ) AS eamount, SUM( a.fee ) AS ecommission,
			SUM( a.ticketscount ) AS eticketscount, b.id AS evid,b.image AS thumb, a.* , b.title
			FROM #__jticketing_order AS a
			LEFT JOIN #__jticketing_integration_xref AS i ON a.event_details_id = i.id
			LEFT JOIN #__jticketing_events AS b ON b.id = i.eventid
			WHERE a.status =  'C' AND i.eventid=b.id AND i.source='com_jticketing'" . $where;
		}
		elseif ($integration == 3)
		{
			$query = "SELECT a.order_id as order_id,sum(order_tax)as eorder_tax,sum(original_amount)as eoriginal_amount,
			sum(coupon_discount)as ecoupon_discount,SUM( a.amount ) AS eamount, SUM( a.fee ) AS ecommission,
			SUM( a.ticketscount ) AS eticketscount, b.evdet_id AS evid, a.* , b.summary as title
			FROM #__jticketing_order AS a	LEFT JOIN #__jticketing_integration_xref AS i ON a.event_details_id = i.id
			LEFT JOIN #__jevents_vevdetail AS b ON b.evdet_id = i.eventid
			WHERE a.status =  'C' AND i.eventid=b.evdet_id AND i.source='com_jevents'" . $where;
		}
		elseif ($integration == 4)
		{
			$query = "SELECT a.order_id as order_id,sum(order_tax)as eorder_tax,
			sum(original_amount)as eoriginal_amount,sum(coupon_discount)as ecoupon_discount,SUM( a.amount ) AS eamount,
			SUM( a.fee ) AS ecommission, SUM( a.ticketscount ) AS eticketscount, b.id AS evid, a.* , b.title as title
			FROM #__jticketing_order AS a	LEFT JOIN #__jticketing_integration_xref AS i ON a.event_details_id = i.id
			LEFT JOIN #__social_clusters AS b ON b.id = i.eventid
			WHERE a.status =  'C' AND i.eventid=b.id AND i.source='com_easysocial'" . $where;
		}

		return $query;
	}

	/**
	 * Method to get frontend sales report query
	 *
	 * @param   int  $xrefid   id of integration xref table
	 * @param   int  $creator  user id of event owner
	 * @param   int  $where    condition
	 *
	 * @return  string $query
	 */
	public function getSalesDataSite($xrefid, $creator, $where)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT a.order_id as order_id,SUM( a.amount ) AS eamount, SUM( a.fee ) AS ecommission,
			SUM( a.ticketscount ) AS eticketscount, event.id AS evid,event.thumb, a.* ,
			 event.title	FROM #__jticketing_order AS a
			LEFT JOIN #__jticketing_integration_xref AS i ON a.event_details_id = i.id
			LEFT JOIN #__community_events AS event ON event.id = i.eventid
			WHERE a.status IN('C','DP')  AND event.creator='" . $creator . "' AND i.source='com_community'" . $where;
		}
		elseif ($integration == 2)
		{
			$query = "SELECT a.order_id as order_id,SUM( a.amount ) AS eamount, SUM( a.fee ) AS ecommission,
			SUM( a.ticketscount ) AS eticketscount, event.id AS evid,event.image as thumb, a.* ,
			event.title	FROM #__jticketing_order AS a
			LEFT JOIN #__jticketing_integration_xref AS i ON a.event_details_id = i.id
			LEFT JOIN #__jticketing_events AS event ON event.id = i.eventid
			WHERE a.status IN('C','DP')  AND event.created_by='" . $creator . "' AND i.source='com_jticketing'" . $where;
		}
		elseif ($integration == 3)
		{
			$query = "SELECT a.order_id as order_id,SUM( a.amount ) AS eamount, SUM( a.fee ) AS ecommission,
			SUM( a.ticketscount ) AS eticketscount, event.evdet_id AS evid, a.* , event.summary as title
			FROM #__jticketing_order AS a	LEFT JOIN #__jticketing_integration_xref AS i ON a.event_details_id = i.id
			LEFT JOIN #__jevents_vevdetail AS event ON event.evdet_id = i.eventid
			LEFT JOIN #__jevents_vevent as vevent ON event.evdet_id=vevent.detail_id
			WHERE a.status IN('C','DP')  AND vevent.created_by='" . $creator . "' AND i.source='com_jevents'" . $where;
		}
		elseif ($integration == 4)
		{
			$query = "SELECT a.order_id as order_id,SUM( a.amount ) AS eamount, SUM( a.fee ) AS ecommission,
			SUM( a.ticketscount ) AS eticketscount, event.id AS evid, a.* , event.title as title
			FROM #__jticketing_order AS a	LEFT JOIN #__jticketing_integration_xref AS i ON a.event_details_id = i.id
			LEFT JOIN #__social_clusters AS event ON event.id = i.eventid
			LEFT JOIN #__social_events_meta as event_det ON event.id=event_det.cluster_id
			WHERE a.status IN('C','DP')  AND event.creator_uid='" . $creator . "' AND i.source='com_easysocial'" . $where;
		}

		return $query;
	}

	/**
	 * Method to get frontend myticket report query
	 *
	 * @param   int  $where  condition
	 *
	 * @return  string $query
	 */
	public function getMyticketDataSite($where)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT a.order_id as order_id,a.status AS
			STATUS , i.eventid AS eventid, e.id AS order_items_id, b.id AS evid, b.startdate,a.email, b.enddate,
			a.cdate, a.id, a.name, a.id AS orderid, a.event_details_id, a.user_id, b.title, b.thumb, b.location, e.type_id,
			e.ticketcount AS ticketscount, f.title AS ticket_type_title, f.price AS price,
			(f.price * e.ticketcount) AS totalamount FROM #__jticketing_order AS a, #__community_events AS b,
			 #__jticketing_order_items AS e, #__jticketing_types AS f,#__jticketing_integration_xref AS i
			WHERE a.event_details_id = i.id	AND e.order_id = a.id	AND i.source = 'com_community'
			AND e.type_id = f.id AND b.id = i.eventid	" . $where;
		}
		elseif ($integration == 2)
		{
			$query = "SELECT a.order_id as order_id,a.status AS
			STATUS , i.eventid AS eventid, e.id AS order_items_id, b.id AS evid,a.email, b.startdate, b.enddate, +
			a.cdate, a.id, a.name, a.id AS orderid, a.event_details_id, a.user_id, b.title,
			b.image as thumb, b.location, e.type_id, e.ticketcount AS ticketscount,
			f.title AS ticket_type_title, f.price AS price,(	f.price * e.ticketcount	) AS totalamount
			FROM #__jticketing_order AS a,#__jticketing_events AS b,  #__jticketing_order_items AS e,
			#__jticketing_types AS f, #__jticketing_integration_xref AS i	WHERE a.event_details_id = i.id
			AND e.order_id = a.id	AND i.source = 'com_jticketing'		AND e.type_id = f.id
			AND b.id = i.eventid
			" . $where;
		}
		elseif ($integration == 3)
		{
			$query = "SELECT a.order_id as order_id,a.status AS
			STATUS , i.eventid AS eventid, e.id AS order_items_id, b.evdet_id AS evid,a.email,
			DATE(FROM_UNIXTIME(b.dtstart)) as startdate, DATE(FROM_UNIXTIME(b.dtend))as enddate, a.cdate,
			a.id, a.name, a.id AS orderid, a.event_details_id, a.user_id, b.summary,b.summary as title,
			b.location, e.type_id, e.ticketcount AS ticketscount, f.title AS ticket_type_title, f.price AS price,
			(f.price * e.ticketcount) AS totalamount	FROM #__jticketing_order AS a, #__jevents_vevdetail AS b,
			#__jticketing_order_items AS e, #__jticketing_types AS f, #__jticketing_integration_xref AS i
			WHERE a.event_details_id = i.id	AND e.order_id = a.id	AND i.source = 'com_jevents'
			AND e.type_id = f.id AND b.evdet_id = i.eventid
			" . $where;
		}
		elseif ($integration == 4)
		{
			$query = "SELECT a.order_id as order_id,a.status AS
			STATUS , i.eventid AS eventid, e.id AS order_items_id, b.id AS evid,a.email, DATE(event_det.start) as startdate,
			DATE(event_det.end)as enddate, a.cdate, a.id, a.name, a.id AS orderid, a.event_details_id, a.user_id,
			b.title as title, b.address as location, e.type_id, e.ticketcount AS ticketscount,
			f.title AS ticket_type_title, f.price AS price, (f.price * e.ticketcount) AS totalamount
			FROM #__jticketing_order AS a, #__social_clusters AS b, #__social_events_meta AS event_det,
			#__jticketing_order_items AS e, #__jticketing_types AS f, #__jticketing_integration_xref AS i
			WHERE a.event_details_id = i.id		AND e.order_id = a.id	AND i.source = 'com_easysocial'			AND e.type_id = f.id
			AND b.id = i.eventid	AND b.id = event_det.cluster_id		" . $where;
		}

		return $query;
	}

	/**
	 * Method to get order data
	 *
	 * @param   string  $where  condition
	 *
	 * @return  void
	 */
	public function getOrderData($where)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT o.transaction_id as transaction_id, o.order_tax,o.coupon_discount,
			o.order_id as order_id,i.eventid AS eventid, o.id,o.name,o.processor,o.cdate,o.amount,
			o.fee,o.status,o.ticketscount,o.original_amount,o.event_details_id,i.eventid
			as evid,o.amount as paid_amount,o.coupon_code,user.firstname,user.lastname FROM
			#__jticketing_order as o
			INNER JOIN #__jticketing_integration_xref as i ON o.event_details_id=i.id
			INNER JOIN #__jticketing_users as user ON o.id=user.order_id" . $where . " ";
		}
		elseif ($integration == 2)
		{
			$query = "SELECT o.transaction_id as transaction_id,o.order_tax,o.coupon_discount,
			o.order_id as order_id,i.eventid AS eventid, o.id,o.name,o.processor,o.cdate,o.amount,
			o.fee,o.status,o.ticketscount,o.original_amount,o.event_details_id,i.eventid
			as evid,o.amount as paid_amount,o.coupon_code,user.firstname,user.lastname FROM
			#__jticketing_order as o
			INNER JOIN #__jticketing_integration_xref as i ON o.event_details_id=i.id
			INNER JOIN #__jticketing_users as user ON o.id=user.order_id " . $where . " ";
		}
		elseif ($integration == 3)
		{
			$query = "SELECT o.transaction_id as transaction_id,o.order_tax,o.coupon_discount,o.order_id as order_id,
			i.eventid AS eventid, o.id,o.name,o.processor,o.cdate,o.amount,o.fee,o.status,o.ticketscount,
			o.original_amount,o.event_details_id,i.eventid
			as evid,o.amount as paid_amount,o.coupon_code,user.firstname, user.lastname FROM
			#__jticketing_order as o
			INNER JOIN #__jticketing_integration_xref as i ON o.event_details_id=i.id
			INNER JOIN #__jticketing_users as user ON o.id=user.order_id" . $where . " ";
		}
		elseif ($integration == 4)
		{
			$query = "SELECT o.transaction_id as transaction_id,o.order_tax,o.coupon_discount,
			o.order_id as order_id,i.eventid AS eventid, o.id,o.name,o.processor,o.cdate,o.amount,o.fee,o.status,
			o.ticketscount,o.original_amount,o.event_details_id,i.eventid
			as evid,o.amount as paid_amount,o.coupon_code,user.firstname, user.lastname FROM
			#__jticketing_order as o
			INNER JOIN #__jticketing_integration_xref as i ON o.event_details_id=i.id
			INNER JOIN #__jticketing_users as user ON o.id=user.order_id" . $where . " ";
		}

		return $query;
	}

	/**
	 * Method to get attendees data
	 *
	 * @param   string  $where  condition
	 *
	 * @return  void
	 */
	public function getAttendeesData($where = '')
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT ordertbl.customer_note,ordertbl.amount as amount,b.location,
			checkindetails.checkin,ordertbl.order_id as order_id,ordertbl.email as buyeremail,i.eventid as evid,
			ordertbl.cdate,e.attendee_id, ordertbl.id, ordertbl.status, ordertbl.name, ordertbl.event_details_id,
			ordertbl.user_id, b.title, b.avatar as image, e.type_id, e.id AS order_items_id,e.ticketcount AS ticketcount,
			 f.title AS ticket_type_title, f.price AS amount, (f.price * e.ticketcount) AS totalamount,user.firstname,user.lastname
			FROM	#__jticketing_order AS ordertbl
			LEFT JOIN #__jticketing_order_items AS e On ordertbl.id = e.order_id
			LEFT JOIN #__jticketing_integration_xref AS i ON i.id = ordertbl.event_details_id
			LEFT JOIN #__jticketing_checkindetails As checkindetails on checkindetails.ticketid=e.id
			LEFT JOIN #__community_events AS b ON b.id = i.eventid
			INNER JOIN #__jticketing_types AS f ON f.id = e.type_id
			INNER JOIN #__jticketing_users AS user ON ordertbl.id=user.order_id
			WHERE	i.source='com_community'	" . $where;
		}
		elseif ($integration == 2)
		{
			$query = "SELECT ordertbl.customer_note,ordertbl.amount as amount,checkindetails.checkin,
			ordertbl.order_id as order_id,ordertbl.email as buyeremail,i.eventid as evid,ordertbl.cdate,e.attendee_id,
			ordertbl.id, ordertbl.status, ordertbl.name, ordertbl.event_details_id, ordertbl.user_id, b.title, b.image,b.short_description,b.location,
			e.type_id, e.id AS order_items_id,e.ticketcount AS ticketcount, f.title AS ticket_type_title, f.price AS amount,
			 (f.price * e.ticketcount) AS totalamount,user.firstname,user.lastname	FROM	#__jticketing_order AS ordertbl
			LEFT JOIN #__jticketing_order_items AS e On ordertbl.id = e.order_id
			LEFT JOIN #__jticketing_integration_xref AS i ON i.id = ordertbl.event_details_id
			LEFT JOIN #__jticketing_checkindetails As checkindetails on checkindetails.ticketid=e.id
			LEFT JOIN #__jticketing_events AS b ON b.id = i.eventid
			INNER JOIN #__jticketing_types AS f ON f.id = e.type_id
			INNER JOIN #__jticketing_users AS user ON ordertbl.id=user.order_id
			WHERE	i.source='com_jticketing'" . $where;
		}
		elseif ($integration == 3)
		{
			$query = "SELECT ordertbl.customer_note,evntstbl.location as location,checkindetails.checkin,
			 ordertbl.amount as amount,ordertbl.order_id as order_id,ordertbl.email as buyeremail,i.eventid as evid,
			 ordertbl.cdate,e.attendee_id, ordertbl.id, ordertbl.status, ordertbl.name, ordertbl.event_details_id,
			 ordertbl.user_id, evntstbl.summary AS title,  e.type_id, e.id AS order_items_id,e.ticketcount AS ticketcount,
			 f.title AS ticket_type_title, f.price AS amount, (f.price * e.ticketcount) AS totalamount,user.firstname,user.lastname
			FROM #__jticketing_order AS ordertbl
			LEFT JOIN #__jticketing_order_items AS e On ordertbl.id = e.order_id
			LEFT JOIN #__jticketing_integration_xref AS i ON i.id = ordertbl.event_details_id
			LEFT JOIN #__jticketing_checkindetails As checkindetails on checkindetails.ticketid=e.id
			INNER JOIN #__jticketing_types AS f ON f.id = e.type_id
			LEFT JOIN #__jevents_vevdetail AS evntstbl ON evntstbl.evdet_id=i.eventid
			LEFT JOIN #__jevents_vevent AS ev ON evntstbl.evdet_id=ev.ev_id
			INNER JOIN #__jticketing_users AS user ON ordertbl.id=user.order_id
			WHERE i.source='com_jevents'
			" . $where;
		}
		elseif ($integration == 4)
		{
			$query = "SELECT ordertbl.customer_note,evntstbl.address as location,checkindetails.checkin,
			ordertbl.amount as amount,ordertbl.order_id as order_id,ordertbl.email as buyeremail,i.eventid as evid,
			ordertbl.cdate,e.attendee_id, ordertbl.id, ordertbl.status, ordertbl.name,
			ordertbl.event_details_id, ordertbl.user_id,evntstbl.address AS location,
			evntstbl.title AS title,  e.type_id, e.id AS order_items_id,e.ticketcount AS ticketcount, f.title AS ticket_type_title,
			f.price AS amount, (f.price * e.ticketcount) AS totalamount,user.firstname,user.lastname	FROM #__jticketing_order AS ordertbl
			LEFT JOIN #__jticketing_order_items AS e On ordertbl.id = e.order_id
			LEFT JOIN #__jticketing_integration_xref AS i ON i.id = ordertbl.event_details_id
			LEFT JOIN #__jticketing_checkindetails As checkindetails on checkindetails.ticketid=e.id
			LEFT JOIN #__jticketing_types AS f ON f.id = e.type_id
			LEFT JOIN #__social_clusters AS evntstbl ON evntstbl.id=i.eventid
			LEFT JOIN #__social_events_meta AS ev ON evntstbl.id=ev.cluster_id
			INNER JOIN #__jticketing_users AS user ON ordertbl.id=user.order_id
			WHERE i.source='com_easysocial'" . $where;
		}

		return $query;
	}

	/**
	 * Method to get payout data
	 *
	 * @param   int     $userid  id of the user
	 *
	 * @param   string  $search  name of the payee
	 *
	 * @return  void
	 */
	public function getMypayoutData($userid = '',$search = '')
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT a.id,a.user_id,a.payee_name,a.transction_id,a.date,a.payee_id,a.amount,b.thumb
					,a.status as published FROM #__jticketing_ticket_payouts AS a,#__community_users AS b
					WHERE a.user_id = b.userid ";

			if ($search)
			{
				$query .= " AND a.payee_name LIKE '%$search%'";
			}
		}

		if ($integration == 2)
		{
			$query = "SELECT a.id,a.user_id, a.payee_name, a.transction_id, a.date, a.payee_id, a.amount
				,a.status as published  FROM #__jticketing_ticket_payouts AS a, #__users AS b
				WHERE a.user_id = b.id ";

			if ($search)
			{
				$query .= " AND a.payee_name LIKE '%{$search}%'";
			}
		}

		if ($integration == 3)
		{
			$query = "SELECT a.id,a.user_id, a.payee_name, a.transction_id, a.date, a.payee_id, a.amount,a.status as published
					FROM #__jticketing_ticket_payouts AS a, #__jevents_vevent as vevent
					WHERE a.user_id = vevent.created_by";

			if ($search)
			{
				$query .= "AND a.payee_name LIKE '%$search%'";
			}
		}

		if ($integration == 4)
		{
			$query = "SELECT  vevent.creator_uid AS creator, a.transction_id,a.id,a.user_id, a.payee_name,o.coupon_discount AS total_coupon_discount,
			o.fee AS total_commission,o.original_amount AS total_originalamount, a.date,
			a.payee_id, a.amount,a.status as published	FROM #__jticketing_ticket_payouts AS a,
			#__social_clusters as vevent,#__jticketing_order AS o WHERE a.user_id = vevent.creator_uid";

			if ($search)
			{
				$query .= " AND a.payee_name LIKE '%$search%'";
			}
		}

		if ($userid)
		{
			$query .= " AND a.user_id=" . $userid;
			$query .= " AND a.status=1";
		}

		$query .= " GROUP BY  a.id";

		return $query;
	}

	/**
	 * Method to get jevents repetion id
	 *
	 * @param   int  $eventid  id of the event
	 *
	 * @return  void
	 */
	public function getJEventrepitationid($eventid)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$db                   = JFactory::getDBO();
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 3)
		{
			$query = "SELECT rep.rp_id FROM #__jevents_repetition as rep
			INNER JOIN #__jevents_vevent as vevent ON vevent.ev_id=rep.eventid
			AND rep.eventdetail_id= $eventid";
			$db->setQuery($query);
			$rp_id = $db->loadResult();
		}

		return $rp_id;
	}

	/**
	 * Method to get back button on order page
	 *
	 * @param   int  $eventid  replacement data for tags
	 * @param   int  $Itemid   itemid to pass for menu
	 *
	 * @return  void
	 */
	public function BackButtonSite($eventid, $Itemid = '')
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$session              = JFactory::getSession();
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$backbutton = JRoute::_('index.php?option=com_community&view=events&task=viewevent');
		}
		elseif ($integration == 2)
		{
			$backbutton = JRoute::_('index.php?option=com_jticketing&view=event&eventid=' . $eventid);
		}
		elseif ($integration == 3)
		{
			$rp_id      = $jticketingmainhelper->getJEventrepitationid($eventid);
			$backbutton = JRoute::_('index.php?option=com_jevents&task=icalrepeat.detail&evid=' . $rp_id);
		}
		elseif ($integration == 4)
		{
			$backbutton = JRoute::_('index.php?option=com_easysocial&view=events&id=' . $eventid);
		}

		$itemid     = $this->getItemId($backbutton);
		$backbutton = JUri::root() . substr(JRoute::_($backbutton . '&Itemid=' . $itemid), strlen(JUri::base(true)) + 1);

		return $backbutton;
	}

	/**
	 * Method to sort array
	 *
	 * @param   array  $array   to sort
	 * @param   int    $column  column name
	 * @param   int    $order   asc or desc
	 *
	 * @return  array
	 */
	public function multi_d_sort($array, $column, $order)
	{
		foreach ($array as $key => $row)
		{
			$orderby[$key] = $row->$column;
		}

		if ($order == 'asc')
		{
			array_multisort($orderby, SORT_ASC, $array);
		}
		else
		{
			array_multisort($orderby, SORT_DESC, $array);
		}

		return $array;
	}

	/**
	 * Method to get query for  total ticket counts
	 *
	 * @param   int  $id         order id
	 * @param   int  $confirmed  1 or 0
	 *
	 * @return  string  $query_order_status;
	 */
	public function getOrder_ticketcount($id, $confirmed)
	{
		$jticketingmainhelper = new Jticketingmainhelper;

		if ($confirmed == 0)
		{
			$where = " AND o.status LIKE 'C'";
		}
		else
		{
			$where = " AND o.status NOT LIKE 'C'";
		}

		$integration        = $jticketingmainhelper->getIntegration();
		$query_order_status = "SELECT o.id,type_id,SUM(i.ticketcount) as cnt, o.status
		FROM `#__jticketing_order_items` as i		INNER JOIN #__jticketing_order as o ON o.id=i.order_id
		where i.order_id IN ( $id )" . $where . " GROUP BY i.type_id";

		return $query_order_status;
	}

	/**
	 * Method to get all event details based on order id
	 *
	 * @param   string  $where  condition
	 *
	 * @return  object  event details object
	 */
	public function getallEventDetailsByOrder($where)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$db                   = JFactory::getDBO();
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT a.status AS STATUS , i.eventid AS eventid, e.id AS order_items_id, b.id AS evid,
			b.startdate,a.email, b.enddate, a.cdate, a.id, a.name, a.id AS orderid, a.event_details_id, a.user_id,
			b.title, b.thumb as image, b.location, e.type_id, e.ticketcount AS ticketscount, f.title AS ticket_type_title,
			f.price AS price, (f.price * e.ticketcount ) AS totalamount
			FROM	#__jticketing_order AS a,#__community_events AS b,	#__jticketing_order_items AS e,
			#__jticketing_types AS f,#__jticketing_integration_xref AS i
			WHERE 	a.event_details_id = i.id	AND e.order_id = a.id	AND i.source = 'com_community'
			AND e.type_id = f.id	AND b.id = i.eventid" . $where;
		}
		elseif ($integration == 2)
		{
			$query = "SELECT a.status AS
					STATUS , i.eventid AS eventid, e.id AS order_items_id, b.id AS evid,a.email, b.startdate,
					b.enddate,b.image AS avatar,b.image, a.cdate, a.id, a.name, a.id AS orderid,
					a.event_details_id, a.user_id, b.title,b.location, e.type_id, e.ticketcount AS ticketscount,
					f.title AS ticket_type_title, f.price AS price, (	f.price * e.ticketcount	) AS totalamount
					FROM	#__jticketing_order AS a,
					#__jticketing_events AS b,
					#__jticketing_order_items AS e,
					#__jticketing_types AS f,
					#__jticketing_integration_xref AS i
					WHERE 	a.event_details_id = i.id
					AND e.order_id = a.id
					AND i.source = 'com_jticketing'
					AND e.type_id = f.id
					AND b.id = i.eventid
					" . $where;
		}
		elseif ($integration == 3)
		{
			$query = "SELECT a.status AS
				STATUS , i.eventid AS eventid, e.id AS order_items_id, b.evdet_id AS evid,a.email,
				DATE(FROM_UNIXTIME(b.dtstart))as startdate, DATE(FROM_UNIXTIME(b.dtend)) as enddate, a.cdate, a.id, a.name,
				a.id AS orderid, a.event_details_id, a.user_id, b.summary as title, b.location, e.type_id,
				e.ticketcount AS ticketscount, f.title AS ticket_type_title,
				f.price AS price, (f.price * e.ticketcount	) AS totalamount
				FROM 	#__jticketing_order AS a,
				#__jevents_vevdetail AS b,
				#__jticketing_order_items AS e,
				#__jticketing_types AS f,
				#__jticketing_integration_xref AS i
				WHERE 	a.event_details_id = i.id
				AND e.order_id = a.id
				AND i.source = 'com_jevents'
				AND e.type_id = f.id
				AND b.evdet_id = i.eventid
				" . $where;
		}
		elseif ($integration == 4)
		{
			$query = "SELECT a.status AS
			STATUS , i.eventid AS eventid, e.id AS order_items_id, event.id AS evid,a.email,
			DATE(event_det.start) as startdate, DATE(event_det.end) as enddate, a.cdate, a.id, a.name,
			a.id AS orderid, a.event_details_id, a.user_id, event.title as title, event.address as location,
			e.type_id, e.ticketcount AS ticketscount, f.title AS ticket_type_title, f.price AS price,
			(f.price * e.ticketcount	) AS totalamount
			FROM 	#__jticketing_order AS a,
			#__social_clusters AS event,#__social_events_meta AS event_det,
			#__jticketing_order_items AS e,
			#__jticketing_types AS f,
			#__jticketing_integration_xref AS i
			WHERE 	a.event_details_id = i.id
			AND e.order_id = a.id
			AND i.source = 'com_easysocial'
			AND e.type_id = f.id
			AND event.id = i.eventid
			" . $where;
		}

		$db->setQuery($query);
		$orderdetails = $db->loadObjectlist();

		return $orderdetails;
	}

	/**
	 * Method to decrease jomsocial seats if order status changed from confirmed topending
	 *
	 * @param   int  $order_id  order id ofjticketing_order table
	 *
	 * @return  void
	 */
	public function unJoinMembers($order_id)
	{
		$com_params             = JComponentHelper::getParams('com_jticketing');
		$affect_js_native_seats = $com_params->get('affect_js_native_seats');

		if ($affect_js_native_seats != '1')
		{
			return;
		}

		$db    = JFactory::getDBO();
		$user  = JFactory::getUser();
		$query = "SELECT a.id as orderid,a.user_id,a.event_details_id,a.amount,a.ticketscount,
		i.eventid as event_id	FROM #__jticketing_order AS a,#__jticketing_order_items AS b,
		#__jticketing_integration_xref as i
		where a.id IN(" . $order_id . ") AND a.id=b.order_id AND a.event_details_id=i.id AND a.status='C' GROUP BY a.id";

		$db->setQuery($query);
		$eventdata = $db->loadObjectList();

		if (!empty($eventdata))
		{
			foreach ($eventdata as $row)
			{
				$qry = "SELECT confirmedcount FROM #__community_events
						WHERE id=" . $row->event_id;
				$db->setQuery($qry);
				$cnt                 = $db->loadResult();
				$arr                 = new stdClass;
				$arr->id             = $row->event_id;
				$arr->confirmedcount = $cnt - $row->ticketscount;
				$db->updateObject('#__community_events', $arr, 'id');
				$query = 'DELETE FROM #__community_events_members	WHERE memberid=' . $row->user_id . '
				AND eventid=' . $row->event_id . " LIMIT " . $row->ticketscount;
				$db->setQuery($query);

				if (!$db->execute())
				{
					$this->setError($db->getErrorMsg());

					return false;
				}
			}
		}
	}

	/**
	 * Method to get total paid amount
	 *
	 * @param   int  $userid  event owner id
	 *
	 * @return  void
	 */
	public function getTotalPaidOutAmount($userid = 0)
	{
		$db    = JFactory::getDBO();
		$where = '';

		if ($userid)
		{
			$where = " AND user_id=" . $userid;
		}

		$query = "SELECT user_id,payee_name,transction_id,date,payee_id,amount
		FROM #__jticketing_ticket_payouts
		WHERE status=1 " . $where;
		$db->setQuery($query);
		$totalearn = 0;
		$result    = $db->loadObjectlist();
		$totalpaid = 0;

		if (!empty($result))
		{
			foreach ($result as $data)
			{
				$totalpaid = $totalpaid + $data->amount;
			}
		}

		return $totalpaid;
	}

	/**
	 * Method to get event details id from repetion id(Used for JEvents)
	 *
	 * @param   int  $rp_id  repetation id
	 *
	 * @return  array  event details
	 */
	public function getEventDetailsid($rp_id)
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$db                   = JFactory::getDBO();
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 3)
		{
			$query = $db->getQuery(true);
			$query->select($db->quoteName('rep.eventdetail_id'));
			$query->from($db->quoteName('#__jevents_repetition', 'rep'));
			$query->join('INNER', $db->quoteName('#__jevents_vevent', 'vevent') .
			' ON (' . $db->quoteName('vevent.ev_id') . ' = ' . $db->quoteName('rep.eventid') . ')');
			$query->where($db->quoteName('rep.rp_id') . ' = ' . $db->quote($rp_id));
			$db->setQuery($query);

			return $db->loadResult();
		}
	}

	/**
	 * Method to get payee details id from event creator
	 *
	 * @param   int  $eventcreator  repetation id
	 *
	 * @return  array  event details
	 */
	public function getPayeeDetails($eventcreator = '')
	{
		$eventtable           = $where = '';
		$jticketingmainhelper = new Jticketingmainhelper;

		if ($eventcreator)
		{
			$where = " AND creator=" . $eventcreator;
		}

		$integration = $jticketingmainhelper->getIntegration();
		$eventdata   = " e.id AS eventid , e.creator ";
		$inerjoin    = "  ";

		if ($integration == 1)
		{
			$query = "SELECT e.id AS eventid,u.username, e.creator, i.paypal_email, COUNT( o.id ) AS order_count,
			SUM( o.original_amount ) AS total_originalamount,SUM( o.amount ) AS total_amount,
			SUM( o.coupon_discount ) AS total_coupon_discount, SUM( o.fee ) AS total_commission
			FROM  #__community_events  AS e
			INNER JOIN `#__jticketing_integration_xref` as i  ON i.eventid = e.id
			INNER JOIN `#__jticketing_order` AS o ON  i.id=o.event_details_id
			INNER JOIN `#__users` AS u ON u.id = e.creator
			AND o.status = 'C'
			" . $where . "
			GROUP BY e.creator";
		}

		if ($integration == 2)
		{
			if ($eventcreator)
			{
				$where = " AND e.created_by=" . $eventcreator;
			}

			$eventdata = " e.id AS eventid , e.created_by ";
			$query     = "SELECT e.id AS eventid,u.username, e.created_by as creator, i.paypal_email,
			COUNT( o.id ) AS order_count, SUM( o.original_amount ) AS total_originalamount,
			SUM( o.amount ) AS total_amount,SUM( o.coupon_discount ) AS total_coupon_discount,
			SUM( o.fee ) AS total_commission	FROM #__jticketing_events AS e
			INNER JOIN `#__jticketing_integration_xref` as i  ON i.eventid = e.id
			INNER JOIN `#__jticketing_order` AS o ON  i.id=o.event_details_id
			INNER JOIN `#__users` AS u ON u.id = e.created_by
			AND o.status = 'C'
			" . $where . "	GROUP BY e.created_by";
		}

		if ($integration == 3)
		{
			$where = '';

			if ($eventcreator)
			{
				$where = " AND vevent.created_by=" . $eventcreator;
			}

			$query = "SELECT e.evdet_id AS  eventid ,vevent.created_by as creator ,u.username , i.paypal_email, COUNT( o.id ) AS order_count,
			SUM( o.original_amount ) AS total_originalamount,SUM( o.coupon_discount ) AS total_coupon_discount,
			SUM( o.amount ) AS total_amount, SUM( o.fee ) AS total_commission	FROM #__jevents_vevdetail AS e
			INNER JOIN `#__jticketing_integration_xref` AS i ON  e.evdet_id =i.eventid
			INNER JOIN `#__jticketing_order` AS o ON  i.id=o.event_details_id
			INNER JOIN `#__jevents_vevent` AS vevent ON  vevent.detail_id = e.evdet_id
			INNER JOIN `#__users` AS u ON  u.id = vevent.created_by
			AND o.status = 'C'
			AND i.source='com_jevents'
			" . $where . "	GROUP BY vevent.created_by";
		}

		if ($integration == 4)
		{
			$where = '';

			if ($eventcreator)
			{
				$where = " AND event.creator_uid=" . $eventcreator;
			}

			$query = "SELECT event.id AS  eventid ,event.creator_uid as creator ,u.username ,
			i.paypal_email, COUNT( o.id ) AS order_count,SUM( o.original_amount ) AS total_originalamount,
			SUM( o.coupon_discount ) AS total_coupon_discount, SUM( o.amount ) AS total_amount,
			SUM( o.fee ) AS total_commission FROM #__social_clusters AS event
			INNER JOIN `#__jticketing_integration_xref` AS i ON  event.id =i.eventid
			INNER JOIN `#__jticketing_order` AS o ON  i.id=o.event_details_id
			INNER JOIN `#__users` AS u ON  u.id = event.creator_uid
			AND o.status = 'C'	AND i.source='com_easysocial'	" . $where . "	GROUP BY event.creator_uid";
		}

		return $query;
	}

	/**
	 * Method to calculate amount to be paid to event creator
	 *
	 * @param   array  $eventcreator  id of the event creator
	 *
	 * @return  void
	 */
	public function getAmounttobepaid_toEventcreator($eventcreator = '')
	{
		$where                = $eventtable = '';
		$jticketingmainhelper = new Jticketingmainhelper;

		if ($eventcreator)
		{
			$where = " AND creator=" . $eventcreator;
		}

		$integration = $jticketingmainhelper->getIntegration();
		$eventdata   = " e.id AS eventid , e.creator ";

		if ($integration == 2)
		{
			$inerjoin = "  ";
		}

		if ($integration == 1)
		{
			$eventtable = " #__community_events ";
		}
		elseif ($integration == 2)
		{
			$eventtable = " #__jticketing_events ";
		}

		if (($integration == 1))
		{
			$query = "SELECT e.id AS eventid,u.username, e.creator, i.paypal_email,
			COUNT( o.id ) AS order_count,SUM( o.amount ) AS total_amount, SUM( o.original_amount ) AS total_originalamount,
			SUM( o.coupon_discount ) AS total_coupon_discount, SUM( o.fee ) AS total_commission
			FROM $eventtable AS e INNER JOIN `#__jticketing_integration_xref` as i  ON i.eventid = e.id
			INNER JOIN `#__jticketing_order` AS o ON  i.id=o.event_details_id
			INNER JOIN `#__users` AS u ON u.id = e.creator
			AND o.status = 'C'
			" . $where . "
			GROUP BY e.creator";
		}

		if (($integration == 2))
		{
			if ($eventcreator)
			{
				$where = " AND created_by=" . $eventcreator;
			}

			$eventdata = " e.id AS eventid , e.created_by ";
			$query     = "SELECT e.id AS eventid,u.username, e.created_by AS creator,
			i.paypal_email, COUNT( o.id ) AS order_count,SUM( o.amount ) AS total_amount,
			SUM( o.original_amount ) AS total_originalamount,SUM( o.coupon_discount ) AS total_coupon_discount,
			SUM( o.fee ) AS total_commission	FROM $eventtable AS e
			INNER JOIN `#__jticketing_integration_xref` as i  ON i.eventid = e.id
			INNER JOIN `#__jticketing_order` AS o ON  i.id=o.event_details_id
			INNER JOIN `#__users` AS u ON u.id = e.created_by
			AND o.status = 'C'
			" . $where . "
			GROUP BY e.created_by";
		}

		if ($integration == 3)
		{
			$where = '';

			if ($eventcreator)
			{
				$where = " AND vevent.created_by=" . $eventcreator;
			}

			$query = "SELECT e.evdet_id AS  eventid ,vevent.created_by as creator ,u.username ,
			i.paypal_email,SUM( o.amount ) AS total_amount, COUNT( o.id ) AS order_count,
			SUM( o.original_amount ) AS total_originalamount,SUM( o.coupon_discount ) AS total_coupon_discount,
			SUM( o.amount ) AS total_amount, SUM( o.fee ) AS total_commission
			FROM #__jevents_vevdetail AS e	INNER JOIN `#__jticketing_integration_xref` AS i ON  e.evdet_id =i.eventid
			INNER JOIN `#__jticketing_order` AS o ON  i.id=o.event_details_id
			INNER JOIN `#__jevents_vevent` AS vevent ON  vevent.detail_id = e.evdet_id
			INNER JOIN `#__users` AS u ON  u.id = vevent.created_by
			AND o.status = 'C'
			AND i.source='com_jevents'
			" . $where . "
			GROUP BY vevent.created_by";
		}

		if ($integration == 4)
		{
			$where = '';

			if ($eventcreator)
			{
				$where = " AND event.creator_uid=" . $eventcreator;
			}

			$query = "SELECT event.id AS  eventid ,event.creator_uid as creator ,u.username ,
			i.paypal_email,SUM( o.amount ) AS total_amount, COUNT( o.id ) AS order_count,
			SUM( o.original_amount ) AS total_originalamount,SUM( o.coupon_discount ) AS total_coupon_discount,
			SUM( o.amount ) AS total_amount, SUM( o.fee ) AS total_commission	FROM #__social_clusters AS event
			INNER JOIN `#__jticketing_integration_xref` AS i ON  event.id =i.eventid
			INNER JOIN `#__jticketing_order` AS o ON  i.id=o.event_details_id
			INNER JOIN `#__users` AS u ON  u.id = event.creator_uid
			AND o.status = 'C'	AND i.source='com_easysocial'	" . $where . "	GROUP BY event.creator_uid";
		}

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$payouts = $db->loadObjectList();

		if ($payouts)
		{
			$amt = (float) ($payouts[0]->total_originalamount - $payouts[0]->total_coupon_discount - $payouts[0]->total_commission);
		}

		if (isset($amt))
		{
			if ($amt > 0)
			{
				return $amt;
			}
			else
			{
				return 0;
			}
		}
	}

	/**
	 * Method to get id from orderId in jticketing_order table
	 *
	 * @param   int  $orderId  orderId id like JT_111sflsf
	 *
	 * @return  void
	 */
	public function getIDFromOrderID($orderId)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__jticketing_order'));
		$query->where($db->quoteName('order_id') . ' = ' . $db->quote($orderId));
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Method to get order_id from id in jticketing_order table
	 *
	 * @param   int  $id  id of the jticketing_order table
	 *
	 * @return  void
	 */
	public function getORDERIDFromID($id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT order_id From #__jticketing_order WHERE id='" . $id . "'";
		$db->setQuery($query);

		return $result = $db->loadResult();
	}

	/**
	 * Method to get order items
	 *
	 * @param   int    $eventid  eventid of the
	 * @param   array  $var      array of order items
	 *
	 * @return  object  order items object
	 */
	public function GetOrderitemsAPI($eventid, $var = array())
	{
		$db                   = JFactory::getDBO();
		$jticketingmainhelper = new Jticketingmainhelper;
		$where                = '';
		$integration          = $jticketingmainhelper->getIntegration();

		if ($eventid)
		{
			$intxrefidevid = $jticketingmainhelper->getEventrefid($eventid);
			$where .= " AND a.event_details_id = {$intxrefidevid}";
		}

		$where .= " AND a.status LIKE 'C'";

		if ($var['tickettypeid'])
		{
			$where .= " AND e.type_id=" . $var['tickettypeid'];
		}

		if ($integration == 1)
		{
			$query = "SELECT e.attendee_id as attendee_id,e.type_id as tickettypeid,a.name,a.email as email,
			a.order_id as order_id,i.eventid as evid,a.cdate, a.id as oid, a.status, a.event_details_id, a.user_id,
			 b.title as event_title, b.thumb,b.startdate,b.enddate,e.type_id, e.id AS order_items_id,
			e.ticketcount AS ticketcount,f.title AS ticket_type_title,
			f.price AS amount, (f.price * e.ticketcount) AS totalamount FROM #__jticketing_order AS a,
			#__community_events AS b,#__jticketing_order_items AS e, #__jticketing_types AS f,
			#__jticketing_integration_xref AS i	WHERE a.event_details_id = i.id
			AND i.source='com_community' AND e.order_id = a.id	AND e.type_id = f.id";
		}
		elseif ($integration == 2)
		{
			$query = "SELECT e.attendee_id as attendee_id,e.type_id as tickettypeid,a.order_id as order_id,i.eventid as evid,
			a.cdate,  a.id as oid, a.status, a.name,a.email as email,b.startdate,b.enddate, a.event_details_id, a.user_id, b.title as event_title,
			b.image AS thumb, e.type_id, e.id AS order_items_id,e.ticketcount AS ticketcount, f.title AS ticket_type_title,
			f.price AS amount, (f.price * e.ticketcount) AS totalamount FROM #__jticketing_order AS a,
			#__jticketing_events AS b, #__jticketing_order_items AS e,	#__jticketing_types AS f,
			#__jticketing_integration_xref AS i	WHERE a.event_details_id = i.id		AND i.source='com_jticketing'
			AND e.order_id = a.id AND e.type_id = f.id";
		}
		elseif ($integration == 3)
		{
			$query = "SELECT e.attendee_id as attendee_id,e.type_id as tickettypeid,a.order_id as order_id,i.eventid as evid,
			a.cdate,  a.id as oid, a.status, a.name,a.email as email, a.event_details_id, a.user_id, b.summary as event_title,  e.type_id,
			 e.id AS order_items_id,e.ticketcount AS ticketcount, f.title AS ticket_type_title, f.price AS amount,
			(f.price * e.ticketcount) AS totalamount FROM #__jticketing_order AS a,
			#__jevents_vevdetail AS b,  #__jticketing_order_items AS e, #__jticketing_types AS f,
			#__jticketing_integration_xref AS i	WHERE a.event_details_id = i.id
			AND i.source='com_jevents'		AND e.order_id = a.id		AND e.type_id = f.id";
		}
		elseif ($integration == 4)
		{
			$query = "SELECT e.attendee_id as attendee_id,e.type_id as tickettypeid,a.order_id as order_id,i.eventid as evid,
			a.cdate,  a.id as oid, a.status, a.name,a.email as email, a.event_details_id, a.user_id, b.title as event_title,
			e.type_id, e.id AS order_items_id,e.ticketcount AS ticketcount, f.title AS ticket_type_title, f.price AS amount,
			(f.price * e.ticketcount) AS totalamount FROM #__jticketing_order AS a,#__social_clusters AS b,
			#__jticketing_order_items AS e, #__jticketing_types AS f,#__jticketing_integration_xref AS i
			WHERE a.event_details_id = i.id	AND i.eventid =b.id	AND i.source='com_easysocial'	AND e.order_id = a.id
			AND e.type_id = f.id";
		}

		if ($where)
		{
			$query .= $where;
		}

		$query .= " GROUP BY order_items_id";
		$query .= " ORDER BY a.name";
		$db->setQuery($query);

		return $result = $db->loadObjectlist();
	}

	/**
	 * Method to check if event is multiple day
	 *
	 * @param   int  $eventdata  eventdata
	 *
	 * @return  void
	 */
	public function isMultidayEvent($eventdata)
	{
		// Vishal - if empty return 0
		if (empty($eventdata))
		{
			return 0;
		}

		$starttimestamp = strtotime($eventdata->startdate);
		$endtimestamp   = strtotime($eventdata->enddate);
		$currenttime    = time();
		$diff           = $endtimestamp - $starttimestamp;
		$multipleday    = (($diff) / (60 * 60 * 24)) % 365;

		if ($multipleday >= 1 and $currenttime < $endtimestamp)
		{
			return 1;
		}

		return 0;
	}

	/**
	 * Method to get checkin details from order items id
	 *
	 * @param   int  $ticketid  order items id jticketin_order_items table
	 * @param   int  $eventid   eventid        eventid
	 *
	 * @return  void
	 */
	public function GetCheckinStatusAPI($ticketid, $eventid = '')
	{
		$db = JFactory::getDBO();

		if (isset($eventid))
		{
			$eventdata   = $this->getAllEventDetails($eventid);

			$multipleday = $this->isMultidayEvent($eventdata);

			if ($multipleday == 1)
			{
				// Return 0;
			}
		}

		$query = "SELECT checkin FROM #__jticketing_checkindetails WHERE ticketid=" . $ticketid;
		$db->setQuery($query);
		$result = $db->loadResult();

		if (!empty($result))
		{
			return 1;
		}

		return 0;
	}

	/**
	 * Method to replace the tags in the message body
	 *
	 * @param   array  $oid           order id
	 * @param   int    $orderitemsid  order item id
	 * @param   int    $useremail     email of buyer
	 *
	 * @return  void
	 */
	public function GetActualTicketidAPI($oid, $orderitemsid, $useremail = '')
	{
		$db    = JFactory::getDBO();
		$where = "";

		if ($useremail)
		{
			$where = " AND a.email LIKE '" . $useremail . "'";
		}

		$query = "SELECT b.attendee_id AS attendee_id,b.id as order_items_id,b.type_id as type_id, a.name as name ,
		a.user_id as user_id,a.email as email,a.event_details_id as eventid FROM #__jticketing_order AS a,
		#__jticketing_order_items AS b WHERE a.id=b.order_id AND a.id=$oid AND b.id=$orderitemsid
		AND a.status LIKE 'C' " . $where;
		$db->setQuery($query);

		return $result = $db->loadObjectlist();
	}

	/**
	 * Method to mark ticket as checkin
	 *
	 * @param   int  $orderitemsid  order items id
	 * @param   int  $result        result
	 *
	 * @return  void
	 */
	public function DoCheckinAPI($orderitemsid, $result)
	{
		$db                      = JFactory::getDBO();
		$restype                 = new stdClass;
		$restype->ticketid       = $orderitemsid;
		$restype->eventid        = $result[0]->eventid;
		$restype->attendee_name  = $result[0]->name;
		$restype->attendee_email = $result[0]->email;
		$restype->attendee_id    = $result[0]->user_id;
		$restype->checkintime    = date('Y-m-d H:i:s');
		$restype->checkin        = 1;

		if (!$db->insertObject('#__jticketing_checkindetails', $restype, 'ticketid'))
		{
			return 0;
		}

		// Now update integration xref count
		$query = "SELECT a.event_details_id FROM #__jticketing_order AS a,#__jticketing_order_items AS b
		WHERE a.id=b.order_id AND  b.id=$orderitemsid AND a.status LIKE 'C'";
		$db->setQuery($query);
		$result = $db->loadResult();

		if ($result)
		{
			$query = "UPDATE #__jticketing_integration_xref SET checkin=checkin+1 WHERE  id=" . $result;
			$db->setQuery($query);
			$db->execute();
		}

		return 1;
	}

	/**
	 * Method to add ticket in attendee list
	 *
	 * @param   int  $orderitemsid  replacement data for tags
	 * @param   int  $result        result
	 *
	 * @return  void
	 */
	public function Addtoattendeelist($orderitemsid, $result)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT ticketid FROM #__jticketing_atteendeelist WHERE user_email LIKE '" . $result[0]->email . "'";
		$db->setQuery($query);
		$present = $db->loadResult();

		if (!$present)
		{
			$restype             = new stdClass;
			$restype->ticketid   = $orderitemsid;
			$restype->eventid    = $result[0]->eventid;
			$restype->user_email = $result[0]->email;
			$restype->user_id    = $result[0]->user_id;
			$restype->user_name  = $result[0]->name;

			if (!$db->insertObject('#__jticketing_atteendeelist', $restype, 'ticketid'))
			{
				return 0;
			}
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Method to get unpaid events
	 *
	 * @param   int     $eventid   eventid
	 * @param   array   $userid    userid of event creator
	 * @param   array   $eveidarr  int
	 * @param   string  $search    search name of event
	 *
	 * @return  void
	 */
	public function GetUser_unpaidEventsAPI($eventid, $userid = '', $eveidarr = '', $search = '')
	{
		//  $today = JFactory::getDate()->format("Y-m-d");
		$today                = JHtml::date('', 'Y-m-d', true);
		$where1               = array();
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if (!empty($eveidarr))
		{
			$eveidstr = implode(",", $eveidarr);
			$eveidstr = "" . $eveidstr . "";

			if ($integration == 3)
			{
				$where1[] = "   events.evdet_id NOT IN (" . $eveidstr . ")";
			}
			else
			{
				$where1[] = "   events.id NOT IN (" . $eveidstr . ")";
			}
		}

		if ($userid)
		{
			$user = JFactory::getUser();

			if ($integration == 1)
			{
				$where1[] = "  events.creator='" . $userid . "'";
				$where1[] = "  events.title LIKE '%$search%'";
			}
			elseif ($integration == 2)
			{
				$where1[] = "  events.created_by='" . $userid . "'";
				$where1[] = "  events.state=1";
				$where1[] = "  events.title LIKE '%$search%'";
			}
			elseif ($integration == 3)
			{
				$where1[] = "  vevent.created_by ='" . $userid . "'";
				$where1[] = "  events.summary LIKE '%$search%'";
			}
			elseif ($integration == 4)
			{
				$where1[] = " events.creator_uid='" . $userid . "'";
				$where1[] = "  events.title LIKE '%$search%'";
			}
		}

		if ($eventid)
		{
			if ($integration == 3)
			{
				$where1[] = "   events.evdet_id=" . $eventid;
			}
			else
			{
				$where1[] = "   events.id=" . $eventid;
			}
		}

		if ($where1)
		{
			$where = " WHERE " . implode(' AND ', $where1);
		}

		if ($integration == 1)
		{
			$query = "SELECT events.id AS id,events.creator,events.title AS title,events.startdate as startdate,
			events.short_description as description,events.location as location,
			events.booking_start_date AS book_start_date,events.booking_end_date AS book_end_date,
			events.enddate as enddate,avatar FROM #__community_events AS events" . $where;
		}
		elseif ($integration == 2)
		{
			$query = "SELECT events.id AS id,events.title AS title,events.startdate as startdate,
			events.short_description as description,events.location as location,
			events.booking_start_date AS book_start_date,events.booking_end_date AS book_end_date,events.enddate as enddate,
			image as avatar FROM #__jticketing_events AS events" . $where;
		}
		elseif ($integration == 3)
		{
			$query = "SELECT events.evdet_id AS id,events.summary AS title,
			DATE( FROM_UNIXTIME(events.dtstart ) ) as startdate,DATE(FROM_UNIXTIME(events.dtend)) as enddate
			FROM  #__jevents_vevdetail AS events LEFT JOIN #__jevents_vevent as vevent ON events.evdet_id=vevent.detail_id
			" . $where;
		}
		elseif ($integration == 4)
		{
			$query = "SELECT events.id AS id,events.title AS title,events_det.start  AS startdate,events_det.end as enddate
			FROM  #__social_clusters AS events
			LEFT JOIN #__social_events_meta as events_det ON events.id=events_det.cluster_id
			" . $where;
		}

		if ($integration == 3)
		{
			$query .= " AND DATE( FROM_UNIXTIME(events.dtend ) ) > NOW() ";
		}
		elseif ($integration == 4)
		{
			$query .= " AND DATE( events_det.start) >= '" . $today . "' ";
		}
		else
		{
			$query .= " AND DATE( events.enddate) >= '" . $today . "' ";
		}

		$query .= " ORDER BY startdate,title asc";

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$result = $db->loadObjectlist();

		if ($integration == 2)
		{
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'media');
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'mediaxref');
			$modelMediaXref = JModelLegacy::getInstance('MediaXref', 'JTicketingModel');
			$modelMedia = JModelLegacy::getInstance('Media', 'JTicketingModel');

			foreach ($result AS $key => $res)
			{
				$eventMainImage = $modelMediaXref->getEventMedia($res->id, 'com_jticketing.event', 0);

				if (isset($eventMainImage[0]->media_id))
				{
					$mediaData = $modelMedia->getItem($eventMainImage[0]->media_id);
					$result[$key]->avatar = $mediaData->media;
				}
			}
		}

		return $result;
	}

	/**
	 * Method to get  sold tickets
	 *
	 * @param   int  $XrefId  xref id of jticketing_xref table
	 *
	 * @return  void
	 */
	public function GetSoldTickets($XrefId)
	{
		$db = JFactory::getDBO();

		if (!$XrefId)
		{
			return 0;
		}

		$query = "SELECT order_id FROM  #__jticketing_order WHERE event_details_id=" . $XrefId . " AND status LIKE 'C'";
		$db->setQuery($query);
		$result = $db->loadObjectList();
		$count  = 0;

		if (!empty($result))
		{
			foreach ($result AS $order)
			{
				$query = "SELECT count(order_id) FROM  #__jticketing_order_items WHERE order_id=" . $order->id;
				$db->setQuery($query);
				$result_cnt = $db->loadResult();

				if ($result_cnt)
				{
					$count = $count + $result_cnt;
				}
			}
		}

		return $count;
	}

	/**
	 * Method to get  sold tickets
	 *
	 * @param   int  $tickettypeid  tickettypeid
	 *
	 * @return  void
	 */
	public function GetTicketTypessold($tickettypeid)
	{
		$db = JFactory::getDBO();

		if (!$tickettypeid)
		{
			return 0;
		}

		$query = "SELECT eventid FROM  #__jticketing_types WHERE id=" . $tickettypeid;
		$db->setQuery($query);
		$eventid = $db->loadResult();

		$query = "SELECT id FROM  #__jticketing_order WHERE event_details_id=" . $eventid . " AND status LIKE 'C'";
		$db->setQuery($query);
		$result = $db->loadObjectList();
		$count  = 0;

		if (!empty($result))
		{
			foreach ($result AS $order)
			{
				$query = "SELECT count(order_id) FROM  #__jticketing_order_items WHERE order_id=" . $order->id . " AND type_id=" . $tickettypeid;
				$db->setQuery($query);
				$result_cnt = $db->loadResult();

				if ($result_cnt)
				{
					$count = $count + $result_cnt;
				}
			}
		}

		return $count;
	}

	/**
	 * Method to get all events
	 *
	 * @param   int     $userid   user id
	 * @param   array   $eventid  eventid of event creator
	 * @param   string  $search   name of event
	 *
	 * @return  void
	 */
	public function GetUserEventsAPI($userid = '', $eventid = '', $search = '')
	{
		// $today = JFactory::getDate()->format("Y-m-d");
		$today                = JHtml::date('', 'Y-m-d', true);
		$jticketingmainhelper = new Jticketingmainhelper;
		$integration          = $jticketingmainhelper->getIntegration();

		if ($integration == 1)
		{
			$query = "SELECT events.id AS id,integr_xref.id as integrid,integr_xref.checkin as checkin,
			count(otems.id) as soldtickets,events.title AS title,ticket.status,events.startdate as startdate,
			events.enddate as enddate,avatar
			FROM #__jticketing_order AS ticket INNER JOIN #__jticketing_order_items AS otems ON
			otems.order_id=ticket.id LEFT JOIN  #__jticketing_integration_xref  AS integr_xref
			ON integr_xref.id = ticket.event_details_id	JOIN #__community_events AS events
			ON integr_xref.eventid = events.id
			AND integr_xref.source='com_community' AND ticket.status LIKE 'C'";
		}
		elseif ($integration == 2)
		{
			$query = "SELECT events.id AS id,events.short_description AS description,events.location AS location,events.booking_start_date AS book_start_date,
			events.booking_end_date AS book_end_date,integr_xref.id as integrid,
			integr_xref.checkin as checkin,count(otems.id) as soldtickets,
			events.title AS title,ticket.status,events.startdate as startdate,
			events.enddate as enddate,image as avatar
			FROM #__jticketing_order AS ticket INNER JOIN #__jticketing_order_items AS otems ON
			otems.order_id=ticket.id
			LEFT JOIN  #__jticketing_integration_xref  AS integr_xref
			ON integr_xref.id = ticket.event_details_id
			JOIN #__jticketing_events AS events
			ON integr_xref.eventid = events.id
			AND integr_xref.source='com_jticketing' AND events.state=1 AND ticket.status LIKE 'C' WHERE events.title LIKE '%$search%'";
		}
		elseif ($integration == 3)
		{
			$query = "SELECT events.evdet_id AS id,events.summary AS title,events.description AS description,events.location AS location,
			ticket.status,integr_xref.checkin as checkin,
			count(otems.id) as soldtickets,DATE( FROM_UNIXTIME(events.dtstart ) ) as startdate,
			DATE(FROM_UNIXTIME(events.dtend)) as enddate,DATE( FROM_UNIXTIME(events.dtstart ) ) as book_start_date,
			DATE(FROM_UNIXTIME(events.dtend)) as book_end_date
			FROM #__jticketing_order AS ticket INNER JOIN #__jticketing_order_items AS otems ON
			otems.order_id=ticket.id	LEFT JOIN  #__jticketing_integration_xref  AS integr_xref
			ON integr_xref.id = ticket.event_details_id
			JOIN #__jevents_vevdetail AS events		ON integr_xref.eventid = events.evdet_id
			INNER JOIN #__jevents_vevent as eventmain	ON events.evdet_id = eventmain.detail_id
			AND ticket.status LIKE 'C' WHERE events.summary LIKE '%$search%'";
		}
		elseif ($integration == 4)
		{
			$query = "SELECT events.id AS id,events.title AS title,ticket.status,integr_xref.checkin as checkin,
			count(otems.id) as soldtickets,event_det.start AS startdate,event_det.end AS enddate
			FROM #__jticketing_order AS ticket INNER JOIN #__jticketing_order_items AS otems ON
			otems.order_id=ticket.id LEFT JOIN  #__jticketing_integration_xref  AS integr_xref
			ON integr_xref.id = ticket.event_details_id	JOIN #__social_clusters AS events
			ON integr_xref.eventid = events.id	INNER JOIN #__social_events_meta as event_det
			ON events.id = event_det.cluster_id	AND ticket.status LIKE 'C'";
			$query .= " AND DATE( event_det.start ) >= '" . $today . "' ";
		}

		if ($userid)
		{
			$user = JFactory::getUser();

			if ($integration == 1)
			{
				$query .= " AND events.creator='" . $userid . "'";
			}
			elseif ($integration == 2)
			{
				$query .= " AND events.created_by='" . $userid . "'";
			}
			elseif ($integration == 3)
			{
				$query .= " AND eventmain.created_by ='" . $userid . "'";
			}
			elseif ($integration == 4)
			{
				$query .= " AND events.creator_uid ='" . $userid . "'";
			}
		}

		if ($eventid)
		{
			$intxrefidevid = $jticketingmainhelper->getEventrefid($eventid);
			$query .= "  AND ticket.event_details_id = {$intxrefidevid}";
		}

		if ($integration == 3)
		{
			$query .= " AND DATE( FROM_UNIXTIME(events.dtstart) ) >= '" . $today . "'";
		}
		elseif ($integration == 1 or $integration == 2)
		{
			$query .= " AND DATE(events.enddate) >= '" . $today . "'";
		}

		if ($integration == 3)
		{
			$query .= " GROUP BY eventmain.detail_id";
		}
		else
		{
			$query .= " GROUP BY events.id";
		}

		$query .= " ORDER BY startdate asc";
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$results = $db->loadObjectlist();

		if ($integration == 4)
		{
			require_once JPATH_ROOT . '/administrator/components/com_easysocial/includes/foundry.php';

			foreach ($results AS $key => $result)
			{
				$event                 = FD::event($result->id);
				$results[$key]->avatar = str_replace(JUri::root(), '', $event->getAvatar());
			}
		}

		if ($integration == 2)
		{
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'media');
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'mediaxref');
			$modelMediaXref = JModelLegacy::getInstance('MediaXref', 'JTicketingModel');
			$modelMedia = JModelLegacy::getInstance('Media', 'JTicketingModel');

			foreach ($results AS $key => $result)
			{
				$eventMainImage = $modelMediaXref->getEventMedia($result->id, 'com_jticketing.event', 0);

				if (isset($eventMainImage[0]->media_id))
				{
					$mediaData = $modelMedia->getItem($eventMainImage[0]->media_id);
					$results[$key]->avatar = $mediaData->media;
				}
			}
		}

		return $results;
	}

	/**
	 * Method to get ticket count
	 *
	 * @param   int  $eventid  eventid to get ticket
	 *
	 * @return  void
	 */
	public function GetTicketcount($eventid)
	{
		$db                   = JFactory::getDBO();
		$jticketingmainhelper = new Jticketingmainhelper;
		$intxrefidevid        = $jticketingmainhelper->getEventrefid($eventid);

		if (!$intxrefidevid)
		{
			return;
		}

		$query = "SELECT  sum(available) as totaltickets FROM #__jticketing_types WHERE eventid=" . $intxrefidevid;
		$db->setQuery($query);
		$result = $db->loadResult();
		$query = "SELECT  count(id) FROM #__jticketing_types WHERE eventid=" . $intxrefidevid;
		$db->setQuery($query);
		$count = $db->loadResult();

		if ($result == 0 and $count >= 1)
		{
			return JText::_('COM_JTICKETING_UNLIMITED_SEATS');
		}

		return $result;
	}

	/**
	 * Method to get if checkin or not
	 *
	 * @param   int     $eventId         eventId
	 * @param   string  $attendee_email  attendee email
	 *
	 * @return  void
	 */
	public function getEventAttendStatus($eventId, $attendee_email)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery('true');
		$query->select('cd.checkin');
		$query->from('`#__jticketing_checkindetails` AS cd');
		$query->where('cd.attendee_email = ' . $db->quote($attendee_email));
		$query->where('cd.eventid = ' . (int) $eventId);
		$query->where('cd.checkin = 1');
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to get ticket types
	 *
	 * @param   array   $eventid  eventid
	 * @param   int     $typeid   typeid
	 * @param   string  $search   ticket type name
	 *
	 * @return  void
	 */
	public function getTicketTypes($eventid, $typeid = '', $search = '')
	{
		$jticketingmainhelper = new Jticketingmainhelper;
		$intxrefidevid        = $where1 = array();
		$userid               = JFactory::getUser()->id;
		$where                = array();

		if ($eventid)
		{
			$intxrefidevid = $jticketingmainhelper->getEventrefid($eventid);

			if (!$intxrefidevid)
			{
				return false;
			}

			$where1[] = " eventid=" . $intxrefidevid;
			$where1[] = "  title LIKE '%$search%'";
		}

		if ($typeid)
		{
			$where1[] = " id=" . $typeid;
		}

		if ($where1)
		{
			$where = " WHERE " . implode(' AND ', $where1);
		}

		$db    = JFactory::getDBO();
		$query = "SELECT *
		 FROM  #__jticketing_types
		 " . $where;
		$db->setQuery($query);
		$result           = $db->loadObjectlist();
		$hide_ticket_type = 0;

		foreach ($result as $type)
		{
			$type->hide_ticket_type = 0;

			if ($userid and $type->max_limit_ticket and $intxrefidevid)
			{
				$query = "SELECT  id FROM #__jticketing_order WHERE event_details_id = {$intxrefidevid} AND status='C'
				AND title LIKE '%$search%' AND user_id=" . $userid;
				$db->setQuery($query);
				$orderids = $db->loadObjectlist();

				if (!empty($orderids))
				{
					$order_items_cnt = 0;

					foreach ($orderids AS $order)
					{
						$query = "SELECT  count(id) as cnt FROM #__jticketing_order_items WHERE order_id=" . $order->id;
						$db->setQuery($query);
						$order_items_cnt_db = $db->loadResult();

						if ($order_items_cnt_db)
						{
							$order_items_cnt += $order_items_cnt_db;
						}
					}

					$type->no_of_purchased_by_me = $order_items_cnt;

					if ($type->no_of_purchased_by_me >= $type->max_limit_ticket)
					{
						$type->hide_ticket_type = 1;
						$hide_ticket_type++;
					}
				}
			}
		}

		// If limit is crossed
		if (isset($hide_ticket_type) and $hide_ticket_type == count($result) and $hide_ticket_type > 0)
		{
			$result[0]->max_limit_crossed = 1;
		}

		return $result;
	}

	/**
	 * Method to get count of ticket types that are checkin
	 *
	 * @param   int  $typeid  ticket type id
	 *
	 * @return  void
	 */
	public function GetTicketTypescheckin($typeid)
	{
		$where1[] = " checkin=1";
		$where1[] = " orders.status  LIKE 'C'";

		if ($typeid)
		{
			$where1[] = " tickettypes.id=" . $typeid;
		}

		if ($where1)
		{
			$where = " WHERE " . implode(' AND ', $where1);
		}

		$db    = JFactory::getDBO();
		$query = "SELECT  count(checkin) FROM #__jticketing_checkindetails as checkindet
		INNER JOIN #__jticketing_order_items as orderitem ON orderitem.id=checkindet.ticketid
		INNER JOIN #__jticketing_types as tickettypes ON tickettypes.id=orderitem.type_id
		INNER JOIN #__jticketing_order  as orders ON orders.id=orderitem.order_id" . $where;
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Method to get local time
	 *
	 * @param   array  $userid  user id of buyer
	 * @param   int    $date    date
	 *
	 * @return  void
	 */
	public function getLocaletime($userid, $date)
	{
		$config = JFactory::getConfig();
		$JUser  = JFactory::getUser($userid);

		if ($date == "0000-00-00")
		{
			return "-";
		}

		$JDate_start = JFactory::getDate($date);

		if (JVERSION >= 3.0)
		{
			$offset = $config->get('offset');
		}
		else
		{
			$offset = $config->getValue('config.offset');
		}

		$date_format_to_show = JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM');

		return JHtml::_('date', $JDate_start, $date_format_to_show, $offset);
	}

	/**
	 * Method to get event information from order id
	 *
	 * @param   int  $order_id  order id to get info
	 *
	 * @return  void
	 */
	public function getorderEventInfo($order_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$arrayColumns = array('o.original_amount-o.coupon_discount-o.fee as commissonCutPrice,
		o.fee AS commission,
		ex.vendor_id as vendor,
		vc.params as paypal_detail');
		$query->select($arrayColumns);
		$query->from($db->quoteName('#__jticketing_order', 'o'));
		$query->join('LEFT', $db->quoteName('#__jticketing_integration_xref', 'ex') .
		' ON (' . $db->quoteName('o.event_details_id') . ' = ' . $db->quoteName('ex.eventid') . ')');
		$query->join('LEFT', $db->quoteName('#__vendor_client_xref', 'vc') .
		' ON (' . $db->quoteName('ex.vendor_id') . ' = ' . $db->quoteName('vc.vendor_id') . ')');
		$query->where($db->quoteName('o.id') . ' = ' . $db->quote($order_id));
		$db->setQuery($query);
		$result = $db->loadAssoclist();

		return $result;
	}

	/**
	 * Method to
	 *
	 * @param   string  $path       path
	 * @param   string  $classname  name of class to load
	 *
	 * @return  void
	 */
	public function loadJTClass($path, $classname)
	{
		if (!class_exists($classname))
		{
			JLoader::register($classname, $path);
			JLoader::load($classname);
		}

		return new $classname;
	}

	/**
	 * Method to get username
	 *
	 * @param   int  $userid  userid
	 *
	 * @return  void
	 */
	public function getUserName($userid)
	{
		$db    = JFactory::getDBO();
		$query = 'SELECT `username` FROM `#__users` WHERE `id`=' . $userid;
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method to get extra fields label used in csv
	 *
	 * @param   int  $event_id     event id of the event to export to csv
	 * @param   int  $attendee_id  attendee id to to export to csv
	 *
	 * @return  void
	 */
	public function extraFieldslabel($event_id, $attendee_id = '')
	{
		$labelArrayJT = array();
		$db           = JFactory::getDbo();
		$query        = $db->getQuery(true);
		$query->select('f.id,f.label,f.name FROM #__jticketing_attendee_fields as f');
		$query->where('f.eventid=' . $event_id . ' OR f.eventid=0');
		$db->setQuery($query);
		$AttendeefieldsJticketing = $db->loadObjectlist();

		// Put label in array created
		foreach ($AttendeefieldsJticketing as $afj)
		{
			$afj->source    = 'com_jticketing';
			$labelArrayJT[] = $afj;
		}

		$TjfieldsHelperPath = JPATH_ROOT . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('TjfieldsHelper'))
		{
			JLoader::register('TjfieldsHelper', $TjfieldsHelperPath);
			JLoader::load('TjfieldsHelper');
		}

		$TjfieldsHelper   = new TjfieldsHelper;
		$AttendeefieldsFM = $TjfieldsHelper->getUniversalFields('com_jticketing.ticket');

		if ($AttendeefieldsFM)
		{
			foreach ($AttendeefieldsFM as $FMFields)
			{
				$obj            = new stdclass;
				$obj->id        = $FMFields->id;
				$obj->name      = $FMFields->name;
				$obj->label     = $FMFields->label;
				$obj->type      = $FMFields->type;
				$obj->source    = 'com_tjfields.com_jticketing.ticket';
				$labelArrayJT[] = $obj;
			}
		}

		$attendee_id_query = '';

		if ($attendee_id)
		{
			$attendee_id_query = "AND f.attendee_id=" . $attendee_id;
		}

		foreach ($labelArrayJT as $lab)
		{
			$query = $db->getQuery(true);
			$query->select('f.field_value,f.attendee_id,f.field_source FROM #__jticketing_attendee_field_values as f');
			$query->where('f.field_source="' . $lab->source . '" AND f.field_id=' . $lab->id . ' ' . $attendee_id_query);
			$db->setQuery($query);
			$attendee_f_value = $db->loadobjectlist('attendee_id');

			foreach ($attendee_f_value as $af)
			{
				if ($af->field_source == 'com_tjfields.com_jticketing.ticket')
				{
					if ($lab->type == 'radio' || $lab->type == 'multi_select' || $lab->type == 'single_select')
					{
						$op_name            = '';
						$option_value       = explode('|', $af->field_value);
						$option_value_array = json_encode($option_value);
						$option_name        = $TjfieldsHelper->getOptions($lab->id, $option_value_array);

						foreach ($option_name as $on)
						{
							$op_name .= $on->options . " ";
						}

						$af->field_value = $op_name;
					}
				}
				else
				{
					$af->field_value = str_replace('|', ' ', $af->field_value);
				}
			}

			$lab->attendee_value = $attendee_f_value;
		}

		return $labelArrayJT;
	}

	/**
	 * Method to get event id from order id
	 *
	 * @param   int  $order_id  order id
	 *
	 * @return  void
	 */
	public function getEventID_from_OrderID($order_id)
	{
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('event_details_id')));
		$query->from($db->quoteName('#__jticketing_order', 'o'));
		$query->where($db->quoteName('o.id') . " = " . $db->quote($order_id));

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		return $event_details_id = $db->loadResult();
	}

	/**
	 * Method to get transaction fee
	 *
	 * @param   int  $order_id  order id
	 *
	 * @return  void
	 */
	public function getTransactionFee($order_id)
	{
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('fee')));
		$query->from($db->quoteName('#__jticketing_order', 'o'));
		$query->where($db->quoteName('o.id') . " = " . $db->quote($order_id));

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		// Load the result
		return $fee = $db->loadResult();
	}

	/**
	 * Wrapper to JRoute to handle itemid We need to try and capture the correct itemid for different view
	 *
	 * @param   string   $url    Absolute or Relative URI to Joomla resource.
	 * @param   boolean  $xhtml  Replace & by &amp; for XML compliance.
	 * @param   integer  $ssl    Secure state for the resolved URI.
	 *
	 * @return  url with itemif
	 *
	 * @since  1.0
	 */
	public function JTRoute($url, $xhtml = true, $ssl = null)
	{
		static $eventsItemid = array();

		$mainframe = JFactory::getApplication();
		$jinput = $mainframe->input;

		if (empty($eventsItemid[$url]))
		{
			$eventsItemid[$url] = self::getItemId($url);
		}

		$pos = strpos($url, '#');

		if ($pos === false)
		{
			if (isset($eventsItemid[$url]))
			{
				if (strpos($url, 'Itemid=') === false && strpos($url, 'com_jticketing') !== false)
				{
					$url .= '&Itemid=' . $eventsItemid[$url];
				}
			}
		}
		else
		{
			if (isset($eventsItemid[$url]))
			{
				$url = str_ireplace('#', '&Itemid=' . $eventsItemid[$view] . '#', $url);
			}
		}

		$routedUrl = JRoute::_($url, $xhtml, $ssl);
		$routedUrl = htmlspecialchars_decode($routedUrl);

		return $routedUrl;
	}

	/**
	 * Method to verify booking id
	 *
	 * @param   int  $book_id  book id
	 *
	 * @return   void
	 */
	public function verifyBookingID($book_id)
	{
		if (empty($book_id))
		{
			$array = array();
			$array['success'] = false;

			return $array;
		}

		$prefix = substr($book_id, 9);
		$order = explode("-", $prefix);

		if (empty($order['0']) || empty($order['1']))
		{
			$array = array();
			$array['success'] = false;

			return $array;
		}

		try
		{
			$db = JFactory::getDbo();

			$query = $db->getQuery(true);
			$query->select('o.order_id');
			$query->from($db->quoteName('#__jticketing_order_items', 'o'));
			$query->where($db->quoteName('o.id') . ' = "' . $order['1'] . '"AND' . $db->quoteName('o.order_id') . ' = ' . $order['0']);
			$query->select('od.event_details_id');
			$query->join('LEFT', '#__jticketing_order AS od ON od.id = o.order_id');

			$query->select('a.venue, a.jt_params');
			$query->join('LEFT', '#__jticketing_events AS a ON a.id=od.event_details_id');

			$query->select('v.params');
			$query->join('LEFT', '#__jticketing_venues AS v ON v.id=a.venue');

			$db->setQuery($query);
			$result = $db->loadObject();

			if (!empty($result))
			{
				$params = json_decode($result->params);
				$jt_params = json_decode($result->jt_params);

				$array_url = array();
				$array_url['host_url'] = $params->host_url . $jt_params->event_url;
				$array_url['success'] = true;

				return $array_url;
			}
			else
			{
				$array = array();
				$array['success'] = false;

				return $array;
			}
		}
		catch (Exception $e)
		{
			$e->getMessage();
		}
	}

	/**
	 * Get order detail
	 *
	 * @param   integer  $order_id  order id
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function getOrderDetail($order_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('o.*');
		$query->from($db->quoteName('#__jticketing_order', 'o'));
		$query->where($db->quoteName('o.id') . ' = "' . $order_id . '"');
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 * Generate random no
	 *
	 * @param   integer  $length  length for field
	 * @param   string   $chars   Allowed characters
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
	{
		// Length of character list
		$chars_length = (strlen($chars) - 1);

		// Start our string
		$string = $chars{rand(0, $chars_length)};

		// Generate random string
		for ($i = 1; $i < $length; $i = strlen($string))
		{
			// Grab a random character from our list
			$r = $chars{rand(0, $chars_length)};

			// Make sure the same two characters don't appear next to each other
			if ($r != $string{$i - 1})
			{
				$string .= $r;
			}
		}

		// Return the string
		return $string;
	}
}
