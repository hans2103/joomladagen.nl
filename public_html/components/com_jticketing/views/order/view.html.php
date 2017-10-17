<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.html.parameter');

/**
 * View for checkout
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewOrder extends JViewLegacy
{
	/**
	 * Method to display events
	 *
	 * @param   object  $tpl  tpl
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$document  = JFactory::getDocument();
		$user      = JFactory::getUser();
		$input     = JFactory::getApplication()->input;
		$mainframe = JFactory::getApplication();
		$session   = JFactory::getSession();
		$params     = $mainframe->getParams('com_jticketing');
		$integration = $params->get('integration');

		// Native Event Manager.
		if ($integration < 1)
		{
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		jimport('joomla.html.parameter');
		$this->eventid = $eventid = $input->get('eventid', '', 'INT');

		if (!$eventid)
		{
			$eventid = $session->get('JT_eventid');
		}

		$session->set('JT_eventid', $eventid);
		$this->jticketingfrontendhelper = new jticketingfrontendhelper;
		$this->jticketingmainhelper     = new jticketingmainhelper;
		$this->JticketingCommonHelper     = new JticketingCommonHelper;
		$layout                         = $input->get('layout', '', 'GET');

		if (!$eventid)
		{
			echo $this->logoutmessage = JText::_("USER_LOGOUT");

			return;
		}

		$this->logoutmessage_orderid = JText::_("JT_SESSION_EXPIRED_ORDERID");
		$document->addStyleSheet(JUri::root() . 'components/com_jticketing/assets/css/jticketing_steps.css');
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('payment');
		$com_params         = JComponentHelper::getParams('com_jticketing');
		$gatewaysconfig     = $com_params->get('gateways');
		$allow_buy_guest    = $com_params->get('allow_buy_guest');
		$allow_buy_guestreg = $com_params->get('allow_buy_guestreg');
		$this->integration        = $com_params->get('integration');
		$this->tnc          = $com_params->get('tnc');
		$this->article      = $com_params->get('article');
		$this->default_country_mobile_code      = $com_params->get('default_country_mobile_code');

		// Check if captcha is enabled
		$this->captch_enabled = 0;
		$enable_captcha      = $com_params->get('enable_captcha');
		$plugin = JPluginHelper::getPlugin('captcha', 'recaptcha');

		if ($enable_captcha and $plugin)
		{
				$params = new JRegistry($plugin->params);
				$sitekey = $params->get('public_key', '');

				if ($sitekey)
				{
					JPluginHelper::importPlugin('captcha');
					$dispatcher = JDispatcher::getInstance();
					$res = $dispatcher->trigger('onInit', 'recaptcha');
					$this->captch_enabled = 1;
				}
		}

		// If guest are not allowed to buy redirect to login page
		if (!$allow_buy_guest and !($user->id))
		{
			$itemid = $input->get('Itemid');
			$uri    = 'index.php?option=com_jticketing&view=order&eventid=' . $eventid . '&Itemid=' . $itemid;
			$url    = base64_encode($uri);
			$mainframe->redirect(JRoute::_('index.php?option=com_users&return=' . $url, false), $msg);
		}

		$this->clientname    = $this->jticketingfrontendhelper->getClientName($this->integration);
		$checkout_mehtod_buy = $com_params->get('checkout_mehtod_buy');
		$Data                = $this->get('Data');
		$this->EventData     = $this->get('Eventdata');
		$pagination          = $this->get('Pagination');
		$Itemid              = $input->get('Itemid');

		if ($user->id)
		{
			JLoader::import('components.com_jticketing.models.user', JPATH_SITE);
			$eventModel = JModelLegacy::getInstance('User', 'JticketingModel');
			$userdata = $eventModel->getUserData();
			$this->userdata = $userdata;
		}

		$gateways = array();

		if (!empty($gatewaysconfig))
		{
			$gateways = $dispatcher->trigger('onTP_GetInfo', array($gatewaysconfig));
		}

		foreach ($gateways as $gateway)
		{
			if (!empty($gateway->id))
			{
				if (empty($gateway->name))
				{
					$gateway->name = $gateway->id;
				}

				$newgateways[] = $gateway;
			}
		}

		$country                   = $this->get('Country');
		$this->country             = $country;
		$this->gateways            = $newgateways;
		$this->allow_buy_guest     = $allow_buy_guest;
		$this->allow_buy_guestreg  = $allow_buy_guestreg;
		$this->checkout_mehtod_buy = $checkout_mehtod_buy;
		$this->Itemid              = $Itemid;
		$this->items               = $Data;
		$this->pagination          = $pagination;
		$this->showbuybutton       = $this->jticketingmainhelper->showbuybutton($eventid);
		$eventcreator              = $this->jticketingmainhelper->getEventCreator($eventid);

		// If Event Owners not allowed to buy ticket return false
		if (isset($user->id) and ($user->id == $eventcreator) and !$com_params->get('eventowner_buy'))
		{
			echo JText::_('COM_JTICKETING_EVENT_OWNER_CANT_BUY');

			return;
		}

		$this->eventtypedata = $this->jticketingmainhelper->getEventDetails($eventid);

		if (!empty($this->eventtypedata['0']->max_limit_crossed))
		{
			echo JText::_('COM_JTICKETING_LIMIT_CROSSED');

			return;
		}

		$this->alleventdata   = $this->jticketingmainhelper->getAllEventDetails($eventid);
		$this->eventtickets   = $this->jticketingmainhelper->getEventInfo($eventid);
		$this->fields         = $this->jticketingfrontendhelper->getAllfields($eventid);
		$this->timezonestring = $this->jticketingmainhelper->getTimezoneString($eventid);

		// Check ticket price for free event
		foreach ($this->eventtypedata as $this->ticketType)
		{
			if ($this->ticketType->price > '0')
			{
				$this->ticketType->price;
				break;
			}
		}

		if ($this->timezonestring['startdate'] == $this->timezonestring['enddate'])
		{
			$this->dateToShow = '<i class="fa fa-calendar" aria-hidden="true"></i> ' . $this->timezonestring['startdate'];
		}
		else
		{
			$this->dateToShow = '<i class="fa fa-calendar" aria-hidden="true"></i> ' . $this->timezonestring['startdate']
			. ' - ' . $this->timezonestring['enddate'];
		}

		if (!empty($this->timezonestring['eventshowtimezone']))
		{
			$this->dateToShow .= '<br/>' . $this->timezonestring['eventshowtimezone'];
		}

		// This is ID which we will store all realated order tables.
		$event_integration_id = $this->event_integraton_id = $this->jticketingfrontendhelper->getIntegrationID($eventid, $this->clientname);
		$session->set('JT_orderid', '');

		// Check if session Data exist and set orderid and orderdata
		$JT_orderid = $session->get('JT_orderid');

		if (!empty($JT_orderid))
		{
			// Clear fee in session
			$session->set('JT_fee', '');
			$orderdata = $this->jticketingmainhelper->getorderinfo($JT_orderid);

			// Check if orderid is of this event only
			if (!empty($orderdata['order_info']))
			{
				if ($orderdata['order_info']['0']->event_integration_id == $event_integration_id)
				{
					if ($orderdata['order_info']['0']->status == 'P')
					{
						$orderdata['order_id'] = $JT_orderid;
						$this->orderdata       = $orderdata;

						if (isset($this->orderdata['items']))
						{
							$ticket_type_count = array();

							foreach ($this->orderdata['items'] AS $key => $otems)
							{
								$ticket_type_count[$otems->type_id] = $otems->ticketcount;
								$ticket_type_count[$otems->type_id] = $otems->ticketcount;
							}

							$this->orderdata['ticket_type_count'] = $ticket_type_count;
						}
					}
				}
			}
		}
		else
		{
			$session->set('JT_orderid', '');
		}

		$this->jticketing_params = $com_params = JComponentHelper::getParams('com_jticketing');

		// Filling user info according to community
		$profile_import               = $com_params->get('profile_import');
		$JTicketingIntegrationsHelper = new JTicketingIntegrationsHelper;
		$cdata['userbill']            = '';

		if ($profile_import)
		{
			$cdata = $JTicketingIntegrationsHelper->profileImport();
		}

		// Use profile data if more than 2 fields present in BT address.
		$this->userbill = (isset($this->userdata['BT']) && (count((array) $this->userdata['BT']) >= 2)) ? $this->userdata['BT'] : $cdata['userbill'];

		$this->integration = $com_params->get('integration');

		// If Jomsocial set Header Toolbar.
		if ($this->integration == 1)
		{
			$this->jsheader = $this->jticketingmainhelper->getJSheader();
			$this->jsfooter = $this->jticketingmainhelper->getJSfooter();
		}

		if (isset($user->id))
		{
			$this->billing_data = $this->jticketingmainhelper->getbillingdata('', $user->id);
		}

		$this->siteadmin_comm_per              = $com_params->get('siteadmin_comm_per');
		$this->article                         = $com_params->get('article');
		$this->currency                        = $com_params->get('currency');
		$this->default_country                 = $com_params->get('default_country');
		$this->currency_code                   = $com_params->get('currency_code');
		$this->allow_buy_guestreg              = $com_params->get('allow_buy_guestreg');
		$this->allow_taxation                  = $com_params->get('allow_taxation');
		$this->enable_bill_vat                 = $com_params->get('enable_bill_vat');
		$this->currency_symbol                 = $com_params->get('currency_symbol');
		$this->tnc                             = $com_params->get('tnc');
		$this->max_noticket_peruserperpurchase = $com_params->get('max_noticket_peruserperpurchase');
		$this->collect_attendee_info_checkout  = $com_params->get('collect_attendee_info_checkout');
		$plugin                                = JPluginHelper::getPlugin('jticketingtax', 'jticketing_tax_default');

		if ($plugin)
		{
			$pluginParams = new JRegistry;
			$pluginParams->loadString($plugin->params);
			$this->tax_per = $pluginParams->get('tax_per', '0');
		}

		// Show or hide billing information field(s).
		$showSelectedFields = $com_params->get('show_selected_fields');

		if ($showSelectedFields == 1)
		{
			$billingInfoFields = $com_params->get('billing_info_field');

			if (isset($billingInfoFields))
			{
				foreach ($billingInfoFields as $field)
				{
					switch ($field)
					{
						case 'address':
							$this->address_config = 1;
						break;

						case 'country':
							$this->country_config = 1;
						break;

						case 'state':
							$this->state_config = 1;
						break;

						case 'city':
							$this->city_config = 1;
						break;

						case 'zip':
							$this->zip_config = 1;
						break;

						case 'customer_note':
							$this->customer_note_config = 1;
						break;
					}
				}
			}
		}

		$this->params    = $com_params;

		$this->user      = JFactory::getUser();
		parent::display($tpl);
	}
}
