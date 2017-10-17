<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');

JLoader::import('route', JPATH_SITE . '/components/com_jticketing/helpers');

/**
 * common helper class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingCommonHelper
{
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$com_params  = JComponentHelper::getParams('com_jticketing');
		$this->integration = $com_params->get('integration');
		$this->jticketingmainhelper = new jticketingmainhelper;
		$this->JTRouteHelper = new JTRouteHelper;
	}

	/**
	 * Get Event xref id
	 *
	 * @param   integer  $eventId  eventId
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventIntegXrefId($eventId)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		if (!empty($eventId))
		{
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__jticketing_integration_xref'));
			$query->where($db->quoteName('eventid') . ' = ' . $db->quote($eventId));

			if ($this->integration == 1)
			{
				$query->where($db->quoteName('source') . ' = ' . $db->quote('com_community'));
			}
			elseif ($this->integration == 2)
			{
				$query->where($db->quoteName('source') . ' = ' . $db->quote('com_jticketing'));
			}
			elseif ($this->integration == 3)
			{
				$query->where($db->quoteName('source') . ' = ' . $db->quote('com_jevents'));
			}
			elseif ($this->integration == 4)
			{
				$query->where($db->quoteName('source') . ' = ' . $db->quote('com_easysocial'));
			}

			$db->setQuery($query);

			return $eventXrefId = $db->loadResult();
		}

		return;
	}

	/**
	 * Method to get event vendor
	 *
	 * @param   int  $eventId  event id from all event related tables for example for jomsocial pass jomsocial's event id
	 *
	 * @return  int  vendor id
	 */
	public function getEventVendor($eventId)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		if (empty($eventId))
		{
			return;
		}

		if ($this->integration == 1)
		{
			$source = 'com_community';
		}
		elseif ($this->integration == 2)
		{
			$source = 'com_jticketing';
		}
		elseif ($this->integration == 3)
		{
			$source = 'com_jevents';
		}
		elseif ($this->integration == 4)
		{
			$source = 'com_easysocial';
		}

		$query->select($db->quoteName(array('vendor_id')));
		$query->from($db->quoteName('#__jticketing_integration_xref'));
		$query->where($db->quoteName('source') . ' = ' . $db->quote($source));
		$query->where($db->quoteName('eventid') . ' = ' . $db->quote($eventId));
		$db->setQuery($query);
		$eventVendor = $db->loadObject();

		return $eventVendor;
	}

	/**
	 * Get layout html
	 *
	 * @param   string  $viewName       name of view
	 * @param   string  $layout         layout of view
	 * @param   string  $searchTmpPath  site/admin template
	 * @param   string  $useViewpath    site/admin view
	 *
	 * @return  [type]                  description
	 */
	public function getViewPath($viewName, $layout = "", $searchTmpPath = 'SITE', $useViewpath = 'SITE')
	{
		$searchTmpPath = ($searchTmpPath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
		$useViewpath   = ($useViewpath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
		$app           = JFactory::getApplication();

		if (!empty($layout))
		{
			$layoutName = $layout . '.php';
		}
		else
		{
			$layoutName = "default.php";
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

		$override = $searchTmpPath . '/templates/' . $defTemplate . '/html/com_jticketing/' . $viewName . '/' . $layoutName;

		if (JFile::exists($override))
		{
			return $view = $override;
		}
		else
		{
			return $view = $useViewpath . '/components/com_jticketing/views/' . $viewName . '/tmpl/' . $layoutName;
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
	 * Function to create free ticket
	 *
	 * @param   integer  $userID   userID
	 * @param   integer  $orderID  orderID
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createFreeTicket($userID, $orderID)
	{
		$com_params  = JComponentHelper::getParams('com_jticketing');
		$order = $this->jticketingmainhelper->getorderinfo($orderID);
		$email_options        = $com_params->get('email_options', '');
		JLoader::register('JticketingMailHelper', JPATH_SITE . '/components/com_jticketing/helpers/mail.php');

		// If free ticket then confirm automatically and redirect to Invoice View.
		if ($order['order_info']['0']->amount == 0)
		{
			$input = JFactory::getApplication()->input;
			$confirmOrder = array();
			$confirmOrder['buyer_email']    = '';
			$confirmOrder['status']         = 'C';
			$confirmOrder['processor']      = "Free_ticket";
			$confirmOrder['transaction_id'] = "";
			$confirmOrder['raw_data']       = "";
			$paymentHelper = JPATH_ROOT . '/components/com_jticketing/models/payment.php';

			if (!class_exists('jticketingModelpayment'))
			{
				JLoader::register('jticketingModelpayment', $paymentHelper);
				JLoader::load('jticketingModelpayment');
			}

			$jticketingModelpayment = new jticketingModelpayment;
			$jticketingModelpayment->updatesales($confirmOrder, $orderID);

			$guestEmail = '';

			// For Guest user attach email
			if (!$order['order_info']['0']->user_id)
			{
				isset($order['order_info']['0']->user_email) ? $order['order_info']['0']->user_email : $order['order_info']['0']->user_email = '';
				$guestEmail = "&email=" . md5($order['order_info']['0']->user_email);
			}

			$Itemid = $this->jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=orders');
			$orderIdWithPrefix = $order['order_info']['0']->orderid_with_prefix;
			$redUrl   = "index.php?option=com_jticketing&view=orders&sendmail=1&layout=order&orderid=";
			$redUrl .= $orderIdWithPrefix . "&processor=Free_ticket&Itemid=" . $Itemid . $guestEmail;
			$invoiceUrl = $this->JTRouteHelper->JTRoute($redUrl);

			$eventDetails = $this->jticketingmainhelper->getticketDetails($order['eventinfo']->id, $order['items']['0']->order_items_id);
			$socialIntegration = $com_params->get('integrate_with', 'none');
			$streamBuyTicket = $com_params->get('streamBuyTicket', 0);
			$order_id                   = $order['order_info']['0']->id;

			if (!$eventDetails->online_events)
			{
				// Send Ticket Email.
				if (in_array('ticket_email', $email_options))
				{
					if (!$eventDetails->online_events)
					{
						$email = JticketingMailHelper::sendmailnotify($order_id, 'afterordermail');
					}
				}
			}

			if ($socialIntegration != 'none')
			{
				// Add in activity.
				if ($streamBuyTicket == 1 and !empty($userID))
				{
					$jteventHelper = new jteventHelper;
					$libClass    = $jteventHelper->getJticketSocialLibObj();
					$action      = 'streamBuyTicket';
					$eventLink   = '<a class="" href="' . $order['eventinfo']->event_url . '">' . $order['eventinfo']->summary . '</a>';
					$originalMsg = JText::sprintf('COM_JTICKETING_PURCHASED_TICKET', $eventLink);
					$libClass->pushActivity($userID, $actType = '', $actSubtype = '', $originalMsg, $actLink = '', $title = '', $actAccess = 0);
				}
			}

			if ($this->integration == 2)
			{
				// If online event create user on adobe site and register for this event
				if ($eventDetails->online_events == 1)
				{
					JLoader::import('components.com_jticketing.models.venueform', JPATH_SITE);
					$venueModel = JModelLegacy::getInstance('VenueForm', 'JticketingModel');
					$venueDetail = $venueModel->getItem($eventDetails->venue);
					$eventParams = json_decode($eventDetails->jt_params, true);
					$venueParams = $venueDetail->params;
					$jtParams = new stdClass;

					$enrollUser = JFactory::getUser($userID);
					$jtParams->user_id  = $userID;
					$jtParams->name     = $enrollUser->name;
					$jtParams->email    = $enrollUser->email;
					$jtParams->password = $this->jticketingmainhelper->rand_str(8);
					$jtParams->meeting_url = $eventParams['event_url'];
					$jtParams->api_username = $venueParams['api_username'];
					$jtParams->api_password = $venueParams['api_password'];
					$jtParams->host_url = $venueParams['host_url'];
					$jtParams->sco_id = $eventParams['event_sco_id'];

					// TRIGGER After create event
					$dispatcher = JDispatcher::getInstance();
					JPluginHelper::importPlugin('tjevents');
					$result = $dispatcher->trigger('tj_inviteUsers', array($jtParams));
					$email  = JticketingMailHelper::onlineEventNotify($jtParams, $order['eventinfo']);
				}
			}

			return $invoiceUrl;
		}
	}

	/**
	 * Get all jtext for javascript
	 *
	 * @return   void
	 *
	 * @since   1.0
	 */
	public static function getLanguageConstant()
	{
		$params = JComponentHelper::getParams('com_jticketing');
		$mediaSize = $params->get('jticketing_media_size', '15');

		// For venue valiation
		JText::script('COM_JTICKETING_INVALID_FIELD');
		JText::script('COM_JTICKETING_ONLINE_EVENTS_PROVIDER');
		JText::script('COM_JTICKETING_FORM_LBL_VENUE_ADDRESS');
		JText::script('COM_JTICKETING_VENUE_FORM_ADDRESS_FILED');
		JText::script('COM_JTICKETING_VENUE_FORM_ONLINE_PROVIDER');
		JText::script('JT_TICKET_BOOKING_ID_VALIDATION');

		// Event counter
		JText::script('JT_EVENT_COUNTER_STARTS_IN_DAYS');
		JText::script('JT_EVENT_COUNTER_STARTS_IN_TIME');
		JText::script('JT_EVENT_COUNTER_ENDS_IN_DAYS');
		JText::script('JT_EVENT_COUNTER_ENDS_IN_TIME');
		JText::script('JT_EVENT_COUNTER_EXPIRE');

		// Billing Form validation
		JText::script('COM_JTICKETING_CHECK_SPECIAL_CHARS');
		JText::script('COM_JTICKETING_ENTER_NO_OF_TICKETS');
		JText::script('COM_JTICKETING_FILL_ALL_REQUIRED_FIELDS');
		JText::script('COM_JTICKETING_ACCEPT_TERMS_AND_CONDITIONS');

		// Gallary Upload File validation
		JText::sprintf('COM_TJMEDIA_VALIDATE_MEDIA_SIZE', $mediaSize, 'MB', array('script' => true));

		// Move from main.php helper
		JText::script('COM_JTICKETING_SAVE_AND_CLOSE');
		JText::script('COM_JTICKETING_ADDRESS_NOT_FOUND');
		JText::script('COM_JTICKETING_LONG_LAT_VAL');
		JText::script('COM_JTICKETING_CONFIRM_TO_DELETE');
		JText::script('COM_JTICKETING_NUMBER_OF_TICKETS');
		JText::script('ENTER_COP_COD');
		JText::script('COP_EXISTS');
		JText::script('ENTER_LESS_COUNT_ERROR');
		JText::script('COM_JTICKETING_ENTER_NUMERICS');
		JText::script('COM_JTICKETING_ENTER_AMOUNT_GR_ZERO');
		JText::script('COM_JTICKETING_ENTER_AMOUNT_INT');
		JText::script('COM_JTICKETING_MEETING_BUTTON');
		JText::script('COM_JT_MEETING_ACCESS');
		JText::script('COM_JTICKETING_EVENT_FINISHED');
		JText::script('JGLOBAL_CONFIRM_DELETE');
		JText::script('COM_TJMEDIA_VALIDATE_YOUTUBE_URL');
		JText::script('COM_JTICKETING_EVENT_GALLERY_VIDEOS');
		JText::script('COM_JTICKETING_EVENT_GALLERY_IMAGES');
		JText::script('COM_JTICKETING_EMPTY_DESCRIPTION_ERROR');
		JText::script('COM_JTICKETING_INVALID_FIELD');
		JText::script('COM_JTICKETING_FORM_LBL_EVENT_DATE_ERROR');
		JText::script('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_DATE_ERROR');
		JText::script('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_EVENT_END_ERROR');
		JText::script('COM_JTICKETING_CUSTOM_LOCATION');
		JText::script('COM_JTICKETING_NO_VENUE_ERROR_MSG');
		JText::script('COM_JTICKETING_EVENTS_ENTER_MEETING_SITE_POPUPS');
		JText::script('COM_JTICKETING_NO_ONLINE_VENUE_ERROR');
		JText::script('UNLIM_SEATS');
		JText::script('COM_JTICKETING_VALIDATE_CAPTCHA');

		// Event Detail page Gallery
		JText::script('COM_JTICKETING_GALLERY_VIDEO_TEXT');
		JText::script('COM_JTICKETING_GALLERY_IMAGE_TEXT');
		JText::script('COM_JTICKETING_EVENT_VIDEOS');
	}

	/**
	 * Check if the logged in user is a vendor
	 *
	 * @return   mixed
	 *
	 * @since   2.0
	 */
	public static function checkVendor()
	{
		$user_id = jFactory::getuser()->id;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('vendor_id'));
		$query->from($db->quoteName('#__tjvendors_vendors'));
		$query->where($db->quoteName('user_id') . ' = ' . $user_id);
		$db->setQuery($query);
		$vendor = $db->loadResult();

		if (!$vendor)
		{
			return false;
		}
		else
		{
			return $vendor;
		}
	}
}
