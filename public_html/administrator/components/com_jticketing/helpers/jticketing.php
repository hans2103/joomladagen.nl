<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Class for showing toolbar in backend jticketing toolbar
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingHelper
{
	/**
	 * function for showing toolbar in backend jticketing toolbar
	 *
	 * @param   integer  $vName  name of view for which to add menu
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function addSubmenu($vName = '')
	{
		$params               = JComponentHelper::getParams('com_jticketing');
		$integration          = $params->get('integration', '', 'INT');
		$input                = JFactory::getapplication()->input;
		$vName                = $input->get('view', '', 'STRING');
		$client               = $input->get('client', '', 'STRING');
		$extension			  = $input->get('extension', '', 'STRING');
		$client_ticket_fields = $client_event_fields = $client_ticket_groups = $client_event_groups = 0;

		if ($client == 'com_jticketing.ticket' && $vName == 'fields')
		{
			$client_ticket_fields = 1;
		}
		elseif ($client == 'com_jticketing.event' && $vName == 'fields')
		{
			$client_event_fields = 1;
		}
		elseif ($client == 'com_jticketing.ticket' && $vName == 'groups')
		{
			$client_ticket_groups = 1;
		}
		elseif ($client == 'com_jticketing.event' && $vName == 'groups')
		{
			$client_event_groups = 1;
		}

		// Define view paths
		$events_view = 'index.php?option=com_jticketing&view=events';
		$categories_view = 'index.php?option=com_categories&view=categories&extension=com_jticketing';
		$sales_view = 'index.php?option=com_jticketing&view=allticketsales';
		$orders_view = 'index.php?option=com_jticketing&view=orders';
		$attendee_list_view = 'index.php?option=com_jticketing&view=attendee_list';
		$email_config_view = 'index.php?option=com_jticketing&view=email_config';
		$notification_templates_view = 'index.php?option=com_tjnotifications&extension=com_jticketing';
		$catimpexp = 'index.php?option=com_jticketing&view=catimpexp';
		$reminder_view = 'index.php?option=com_jlike&view=reminders&extension=com_jticketing';
		$coupon_view = 'index.php?option=com_jticketing&view=coupons';
		$event_field_view = 'index.php?option=com_tjfields&view=fields&client=com_jticketing.event';
		$event_field_group_view = 'index.php?option=com_tjfields&view=groups&client=com_jticketing.event';
		$attendee_field_view = 'index.php?option=com_tjfields&view=fields&client=com_jticketing.ticket';
		$attendee_field_group_view = 'index.php?option=com_tjfields&view=groups&client=com_jticketing.ticket';
		$venues = 'index.php?option=com_jticketing&view=venues';
		$venues_categories = 'index.php?option=com_categories&view=categories&extension=com_jticketing.venues';
		$vendor_view = 'index.php?option=com_tjvendors&view=vendors&client=com_jticketing';
		$ticket_attendee_view = 'index.php?option=com_jticketing&view=attendeecorefields&client=com_jticketing';
		$country_view = 'index.php?option=com_tjfields&view=countries&client=com_jticketing';
		$regions_view = 'index.php?option=com_tjfields&view=regions&client=com_jticketing';

			JHtmlSidebar::addEntry(JText::_('JT_CP'), 'index.php?option=com_jticketing&view=cp', $vName == 'cp');

			JHtmlSidebar::addEntry(
					JText::_('COM_JTICKETING_TITLE_VENUES_CATS'),
					$venues_categories,
					$vName == 'categories' && $extension == 'com_jticketing.venues');
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_TITLE_VENUES'), $venues, $vName == 'venues');

			// Showing Native event and event category menus
			if ($integration == 2)
			{
				JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_TITLE_EVENTS'), $events_view, $vName == 'events');
				JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_SUBMENU_CATEGORIES'), $categories_view, $vName == 'categories' && $extension == 'com_jticketing');
				JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_TITLE_CATIMPORTEXPORT'), $catimpexp, $vName == 'catimpexp');
			}

			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_TICKET_SALES_REPORT'), $sales_view, $vName == 'allticketsales');
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_ORDERS'), $orders_view, $vName == 'orders');
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_ATTENDEE_LIST'), $attendee_list_view, $vName == 'attendee_list');

			if ($vName == 'categories')
			{
				JToolBarHelper::title('Jticketing: Categories (Events)');
			}

			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_EMAIL_CONFIG'), $email_config_view, $vName == 'email_config');
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_EMAIL_TEMPLATE'), $notification_templates_view, $vName == 'notifications');
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_REMINDER_TYPES'), $reminder_view, $vName == 'reminders');
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_COUPONS'), $coupon_view, $vName == 'coupons');
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_EVENT_FIELD_MENU'), $event_field_view, $client_event_fields == 1);
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_EVENT_GROUP_MENU'), $event_field_group_view, $client_event_groups == 1);
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_ATTENDEE_FIELD_MENU'), $attendee_field_view, $client_ticket_fields == 1);
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_ATTENDEE_FIELDS_GROUP'), $attendee_field_group_view, $client_ticket_groups == 1);
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_CORE_ATTENDEE_FIELDS'), $ticket_attendee_view, $vName == "attendeecorefields");
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_VENDORS'), $vendor_view, $vName == "vendors");
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_COUNTRIES'), $country_view, $vName == "countries");
			JHtmlSidebar::addEntry(JText::_('COM_JTICKETING_REGIONS'), $regions_view, $vName == "regions");
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 *
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user      = JFactory::getUser();
		$result    = new JObject;
		$assetName = 'com_jticketing';
		$actions   = array(
						'core.admin',
						'core.manage',
						'core.create',
						'core.edit',
						'core.edit.own',
						'core.edit.state',
						'core.delete'
		);

		foreach ($actions as $action)
		{
						$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/** Get all jtext for javascript
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
		JText::script('COM_TJMEDIA_VALIDATE_YOUTUBE_URL');
		JText::script('JGLOBAL_CONFIRM_DELETE');
		JText::script('COM_JTICKETING_ORDER_DELETE_CONF');
		JText::script('COM_JTICKETING_FORM_LBL_VENUE_TITLE');
		JText::script('COM_JTICKETING_FORM_LBL_EVENT_DESCRIPTION');
		JText::script('COM_JTICKETING_CUSTOM_LOCATION');
		JText::script('COM_JTICKETING_FORM_LBL_EVENT_DESCRIPTION');
		JText::sprintf('COM_TJMEDIA_VALIDATE_MEDIA_SIZE', $mediaSize, 'MB', array('script' => true));
		JText::script('COM_JTICKETING_EMPTY_DESCRIPTION_ERROR');
		JText::script('COM_JTICKETING_FORM_LBL_EVENT_DATE_ERROR');
		JText::script('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_DATE_ERROR');
		JText::script('COM_JTICKETING_FORM_LBL_EVENT_BOOKING_EVENT_END_ERROR');
		JText::script('COM_JTICKETING_ENTER_NUMERICS');
		JText::script('JGLOBAL_VALIDATION_FORM_FAILED');
		JText::script('COM_JTICKETING_MIN_AMT_SHOULD_GREATER_MSG');
		JText::script('COM_JTICKETING_DUPLICATE_COUPON');
		JText::script('COM_JTICKETING_DATE_START_ERROR_MSG');
		JText::script('COM_JTICKETING_DATE_END_ERROR_MSG');
		JText::script('COM_JTICKETING_DATE_ERROR_MSG');
		JText::script('COM_JTICKETING_NO_VENUE_ERROR_MSG');
		JText::script('COM_JTICKETING_NO_ONLINE_VENUE_ERROR');
		JText::script('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG1');
		JText::script('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG2');
		JText::script('COM_JTICKETING_VENDOR_FORM_LINK');
		JText::script('COM_JTICKETING_VALIDATE_ROUNDED_PRICE');
	}
}
