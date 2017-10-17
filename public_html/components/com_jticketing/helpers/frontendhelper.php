<?php
/**
 * @version    SVN: <svn_id>
 * @package    JGive
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
ini_set('memory_limit', '1000M');
jimport('joomla.filesystem.file');
jimport('joomla.html.html');
jimport('joomla.html.parameter');
jimport('joomla.utilities.date');
require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/venue.php';

/**
 * jticketingfrontendhelper
 *
 * @since  1.0
 */
class Jticketingfrontendhelper
{
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$path = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $path);
			JLoader::load('jticketingmainhelper');
		}

		$this->jticketingmainhelper = new jticketingmainhelper;
		$db                         = JFactory::getDBO();
	}

	/**
	 * Render booking HTML
	 *
	 * @param   int     $eventid     id of event
	 * @param   int     $userid      userid
	 * @param   object  $eventdata   eventdata
	 * @param   object  $dataFormat  return data into html or array format
	 *
	 * @return  booking HTML
	 *
	 * @since   1.0
	 */
	public function renderBookingHTML($eventid, $userid = '', $eventdata = '', $dataFormat = '')
	{
		$document        = JFactory::getDocument();
		$renderer        = $document->loadRenderer('modules');
		$input           = JFactory::getApplication()->input;
		$app             = JFactory::getApplication();
		$this->jt_params = $app->getParams('com_jticketing');
		$showbook        = $this->jticketingmainhelper->showbuybutton($eventid, $userid);

		$return['startdate'] = $eventdata->startdate;
		$return['enddate'] = $eventdata->enddate;
		$return['isboughtEvent'] = $isboughtEvent   = $this->jticketingmainhelper->isEventbought($eventid, $userid);

		$return['isPaidEvent'] = $isPaidEvent   = $this->jticketingmainhelper->isPaidEvent($eventid);
		$com_params      = JComponentHelper::getParams('com_jticketing');
		$integration     = $com_params->get('integration');
		$buyItemId = 0;

		if (empty($userid))
		{
			$userid = JFactory::getUser()->id;
		}

		$user = JFactory::getUser($userid);

		$this->enable_self_enrollment      = $this->jt_params->get('enable_self_enrollment', '0', 'INT');
		$this->supress_buy_button          = $this->jt_params->get('supress_buy_button', '', 'INT');
		$this->accesslevels_for_enrollment = $this->jt_params->get('accesslevels_for_enrollment');
		$groups                            = $user->getAuthorisedViewLevels();
		$guest                             = $user->get('guest');
		$allowAccessLevelEnrollment        = 0;
		$this->buyTicketItemId             = 0;

		if (!empty($this->accesslevels_for_enrollment))
		{
			// Check access levels for enrollment
			foreach ($groups as $group)
			{
				if (is_array($this->accesslevels_for_enrollment))
				{
					if (in_array($group, $this->accesslevels_for_enrollment, true))
					{
						$allowAccessLevelEnrollment = 1;
						break;
					}
				}
			}
		}

		if ($showbook)
		{
			$enroll = 0;

			$userAuthorisedEnroll = $user->authorise('core.enroll', 'com_jticketing.event.' . $eventid);

			// Show enroll button if - Quick book is set, self enrolment permission is set and ticket is not bought
			if ($this->enable_self_enrollment == 1 && $userAuthorisedEnroll == '1')
			{
				$enroll = 1;

				if (!$isboughtEvent)
				{
					$itemid = JFactory::getApplication()->input->get('Itemid');
					$enrollTicketLink = JRoute::_('index.php?option=com_jticketing&task=order.createOrderAPI&eventid=' . $eventid . '&Itemid=' . $itemid, false);
					$return['enrol_link'] = $enrollTicketLink;
					$return['enrol_button'] = "<a href=" . $enrollTicketLink . " class='btn  btn-default btn-lg btn-success com_jt_enroll com_jticketing_button'>";
					$return['enrol_button'] .= JText::_('COM_JTICKETING_ENROLL_BUTTON') . "</a>";
				}
			}

			// Get the allow_buy_guest value if it's enable then display MEETING button for guest user
			$allowBuyGuest = $app->getParams('com_jticketing')->get('allow_buy_guest', '', 'INT');

			if ($integration == 2)
			{
				if ($eventdata->online_events == 1 && $eventdata->created_by == $userid)
				{
					$return['isboughtEvent'] = 1;
				}

				if ($allowBuyGuest == 1 && $eventdata->online_events == 1 && $guest == 1)
				{
					$guestMeetingLink = 'index.php?option=com_jticketing&view=order&layout=default_online&eventid=';
					$guestMeetingLink = JRoute::_($guestMeetingLink . $eventid . '&Itemid=' . $buyItemId, false);
					$return['guest_meeting_link'] = $guestMeetingLink;
					$return['guest_meeting_btn'] = "<div class='tj-adobeconnect'>";
					$return['guest_meeting_btn'] .= "<a href=" . $guestMeetingLink . " class='btn btn-success btn-lg adobe-enter-btn com_jticketing_button'>";
					$return['guest_meeting_btn'] .= JText::_('COM_JTICKETING_MEETING_BUTTON') . "</a></div>";
				}
			}

			require_once JPATH_SITE . "/components/com_jticketing/helpers/route.php";
			$JTRouteHelper = new JTRouteHelper;
			$buyLink = 'index.php?option=com_jticketing&view=order&layout=default&eventid=' . $eventid;
			$buyTicketLink = $JTRouteHelper->JTRoute($buyLink);

			if ($integration == 2)
			{
				if (((empty($isboughtEvent) && $eventdata->online_events == 1 && $eventdata->created_by != $userid)
					|| $eventdata->online_events == 0) && $enroll == 0)
				{
					$return['buy_button_link'] = $buyTicketLink;
					$return['buy_button'] = "<a	href=" . $buyTicketLink . " class='btn  btn-default   btn-lg btn-success com_jt_book com_jticketing_button'>";
					$return['buy_button'] .= JText::_('COM_JTICKETING_BUY_BUTTON') . "</a>";
				}
			}
			elseif ($enroll == 0)
			{
				$return['buy_button_link'] = $buyTicketLink;
				$return['buy_button'] = "<a	href=" . $buyTicketLink . " class='btn  btn-default btn-lg btn-success com_jt_book com_jticketing_button'>";
				$return['buy_button'] .= JText::_('COM_JTICKETING_BUY_BUTTON') . "</a>";
			}
		}

		$return['details_button_link'] = $eventdata->event_url;
		$return['details_button'] = "<a	href=" . $eventdata->event_url . " class='btn  btn-default btn-lg btn-primary com_jticketing_button'>"
		. JText::_('COM_JTICKETING_DETAILS') . "</a>";

		return $return;
	}

	/**
	 * This function is used to get client name from backend selected integration
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function loadHelperClasses()
	{
		$path                             = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';
		$jticketingfrontendhelper         = JPATH_ROOT . '/components/com_jticketing/helpers/frontendhelper.php';
		$JTicketingIntegrationsHelperPath = JPATH_ROOT . '/components/com_jticketing/helpers/integrations.php';
		$helperPath                       = JPATH_SITE . '/components/com_jticketing/helpers/event.php';
		$mediaHelperPath                  = JPATH_SITE . '/components/com_jticketing/helpers/media.php';
		$field_manager_path               = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $path);
			JLoader::load('jticketingmainhelper');
		}

		if (!class_exists('jticketingfrontendhelper'))
		{
			JLoader::register('jticketingfrontendhelper', $jticketingfrontendhelper);
			JLoader::load('jticketingfrontendhelper');
		}

		if (!class_exists('JTicketingIntegrationsHelper'))
		{
			JLoader::register('JTicketingIntegrationsHelper', $JTicketingIntegrationsHelperPath);
			JLoader::load('JTicketingIntegrationsHelper');
		}

		if (!class_exists('jteventHelper'))
		{
			JLoader::register('jteventHelper', $helperPath);
			JLoader::load('jteventHelper');
		}

		if (file_exists($field_manager_path))
		{
			if (!class_exists('TjfieldsHelper'))
			{
				JLoader::register('TjfieldsHelper', $field_manager_path);
				JLoader::load('TjfieldsHelper');
			}
		}

		if (!class_exists('jticketingMediaHelper'))
		{
			JLoader::register('jticketingMediaHelper', $mediaHelperPath);
			JLoader::load('jticketingMediaHelper');
		}
	}

	/**
	 * This is function is used to get rsvp button
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function showrsvp()
	{
		$this->loadHelperClasses();
		$jtickeketing_component_enabled = JComponentHelper::isEnabled('com_jticketing', true);
		$jtickeketing_module_enabled = JModuleHelper::isEnabled('mod_jticketing_buy', true);
		$event_link = $event->getPermalink();

		if ($jtickeketing_component_enabled and $jtickeketing_module_enabled)
		{
			$com_params      = JComponentHelper::getParams('com_jticketing');
			$integration     = $com_params->get('integration');
			$jticketingmainhelper = new jticketingmainhelper;
			$eventid = $event->id;
			$showbuybutton = $jticketingmainhelper->showbuybutton($eventid);
			$isEventbought = $jticketingmainhelper->isEventbought($eventid);

			if (JFile::exists(JPATH_ROOT . '/components/com_jticketing/jticketing.php'))
			{
				if ($showbuybutton and empty($isEventbought))
				{
					$show_rsvp = 0;
					$lang      = JFactory::getLanguage();
					$extension = 'mod_jticketing_buy';
					$base_dir  = JPATH_SITE;
					$lang->load($extension, $base_dir);

					return 0;
				}
			}
		}

		return 1;
	}

	/**
	 * This function is used to get client name from backend selected integration
	 *
	 * @param   int  $integration  backend selected integration
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getClientName($integration)
	{
		if ($integration == 1)
		{
			$client = 'com_community';
		}

		if ($integration == 2)
		{
			$client = 'com_jticketing';
		}

		if ($integration == 3)
		{
			$client = 'com_jevents';
		}

		if ($integration == 4)
		{
			$client = 'com_easysocial';
		}

		return $client;
	}

	/**
	 * This is function is used to get all fields like universal fields(from tjfields),core fields(in JTicketing)
	 *
	 * @param   object  $eventid      format of output
	 * @param   object  $fieldnames   fieldnames
	 * @param   object  $reultFormat  eventid
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getAllfields($eventid = '', $fieldnames = '*', $reultFormat = "object")
	{
		$db      = JFactory::getDBO();
		$where   = array();
		$where[] = " WHERE state=1 ";

		// Find core fields which comes while installing JTIcketing.
		$where_core = implode(" AND ", $where);
		$query      = "SELECT " . $fieldnames . "
		 FROM #__jticketing_attendee_fields " . $where_core . "
		 AND core=1 order by ordering";
		$db->setQuery($query);
		$fields['core_fields'] = $db->loadObjectlist();

		if ($reultFormat == 'array')
		{
			$fields['core_fields'] = $db->loadAssocList();
		}

		// Find  fields which are created using field manager.
		if (file_exists(JPATH_ROOT . '/components/com_tjfields/helpers/tjfields.php'))
		{
			$filedHelperPath = JPATH_ROOT . DS . 'components' . DS . 'com_tjfields' . DS . 'helpers' . DS . 'tjfields.php';

			if (!class_exists('TjfieldsHelper'))
			{
				JLoader::register('TjfieldsHelper', $filedHelperPath);
				JLoader::load('TjfieldsHelper');
			}

			$TjfieldsHelper                      = new TjfieldsHelper;
			$fields['universal_attendee_fields'] = $TjfieldsHelper->getUniversalFields('com_jticketing.ticket');

			if ($fields['universal_attendee_fields'])
			{
				foreach ($fields['universal_attendee_fields'] AS $key => &$val)
				{
					$val->default_selected_option = $TjfieldsHelper->getOptions($val->id);

					// Set as universal fields, this is important
					$val->is_universal = 1;
				}
			}
		}

		if (!$eventid)
		{
			return $fields;
		}

		if ($eventid)
		{
			$intxrefidevid = $this->jticketingmainhelper->getEventrefid($eventid);

			// If no integration id found, return
			if (!$intxrefidevid)
			{
				return $fields;
			}

			$where[] = " eventid=$intxrefidevid ";
		}

		$where_custom = implode(" AND ", $where);
		$query        = "SELECT " . $fieldnames . "
		 FROM #__jticketing_attendee_fields " . $where_custom . "
		 AND core<>1 ORDER BY ordering";
		$db->setQuery($query);
		$fields['attendee_fields'] = $db->loadObjectlist();

		if ($reultFormat == 'array')
		{
			$fields['attendee_fields'] = $db->loadAssocList();
		}

		return $fields;
	}

	/**
	 * This is function is used to store event details in JTicketing
	 *
	 * @param   object  $objpassed   format of output
	 * @param   object  $source      com_jticketing/com_jevents
	 * @param   object  $postparams  post data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function saveEvent($objpassed, $source, $postparams = '')
	{
		$db  = JFactory::getDBO();
		$obj = $objpassed->eventdata;

		if (!isset($objpassed->eventid))
		{
			if (!$db->insertObject('#__jticketing_events', $obj, 'id'))
			{
				echo $db->stderr();

				return false;
			}

			$event_id = $db->insertid();

			// Saving the event id into integration table
			$obj->event_id      = $event_id;
			$obj->saving_method = 'save';
		}
		else
		{
			$event_id = $objpassed->eventid;
			$obj->id  = $objpassed->eventid;

			if (!$db->updateObject('#__jticketing_events', $obj, 'id'))
			{
			}

			$obj->event_id      = $objpassed->eventid;
			$obj->saving_method = 'edit';
		}

		$obj->eventid     = $obj->event_id;
		$obj->paypalemail = $objpassed->paypalemail;
		$integration_id   = $this->saveIntegrationDetails($obj, $source);
		$file_field       = "event_image";
		$file_error       = $_FILES[$file_field]['error'];

		if (!$file_error == 4)
		{
			// Upload event image
			$uploadSuccess = $this->uploadImage($integration_id);
		}

		// This is Event integration ID which is used in all reference tables of JTicketing AND other products
		$obj->event_integrationid  = $integration_id;
		$com_params                = JComponentHelper::getParams('com_jticketing');
		$obj->currency             = $com_params->get('currency');
		$obj->event_integration_id = $integration_id;

		// Trigger plugins OnAfterJTEventUpdate
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$result = $dispatcher->trigger('OnAfterJTEventUpdate', array($obj, $postparams));

		if (!$result)
		{
		}

		return $event_id;
	}

	/**
	 * This is function is used to delete ticket types
	 *
	 * @param   int  $delete_ids  id of jticketing_types table
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function deleteTicket($delete_ids)
	{
		$db = JFactory::getDBO();

		foreach ($delete_ids as $key => $value)
		{
			$query = 'DELETE FROM #__jticketing_types
			 WHERE id="' . $value . '"';
			$db->setQuery($query);

			if (!$db->execute())
			{
				echo $db->stderr();

				return false;
			}
		}
	}

	/**
	 * This is function is used to delete ticket types
	 *
	 * @param   int    $post     post data
	 * @param   int    $eventid  eventid
	 * @param   array  $data     added by komal for csv import
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */

	public function createTicketTypeObj($post, $eventid, $data = array())
	{
		$db            = JFactory::getDBO();
		$jteventHelper = new jteventHelper;
		$obj           = new stdClass;

		// Added by komal for csv import

		if (isset($data['isImportCsv']))
		{
			$ids             = $post['ticket_type_id'];
			$title           = array('Free');
			$desc            = array('Free');
			$price           = array('0');
			$available       = array('');
			$access          = array('1');
			$state           = array('1');
			$count           = array('1');
			$dep             = array('');
			$unlimited_seats = array('1');
		}
		else
		{
			$ids             = $post->get('ticket_type_id', '', 'ARRAY');
			$title           = $post->get('ticket_type_title', '', 'ARRAY');
			$desc            = $post->get('ticket_type_desc', '', 'ARRAY');
			$price           = $post->get('ticket_type_price', '', 'ARRAY');
			$available       = $post->get('ticket_type_available', '', 'ARRAY');
			$access          = $post->get('ticket_type_access', '', 'ARRAY');
			$state           = $post->get('ticket_type_state', '', 'ARRAY');
			$count           = $post->get('ticket_type_count', '', 'ARRAY');
			$dep             = $post->get('ticket_type_deposit_price', '', 'ARRAY');
			$unlimited_seats = $post->get('ticket_type_unlimited_seats', '', 'ARRAY');
		}

		for ($i = 0; $i < count($ids); $i++)
		{
			$TicketTypesobj                  = new stdClass;
			$TicketTypesobj->id              = $ids[$i];
			$TicketTypesobj->count           = $available[$i];
			$TicketTypesobj->access          = $access[$i];
			$TicketTypesobj->state           = $state[$i];
			$TicketTypesobj->unlimited_seats = $unlimited_seats[$i];

			if ($available[$i] != 0)
			{
				$TicketTypesobj->available = $available[$i];
			}

			// Fix available seats while updating Events
			if ($TicketTypesobj->id)
			{
				$where = "	 id=" . $TicketTypesobj->id;
				$query = "SELECT id,count,available,access FROM #__jticketing_types
							WHERE  " . $where;
				$db->setQuery($query);
				$detailspresent = $db->loadObject();

				if (!empty($detailspresent))
				{
					$current_available_seats   = 0;
					$TicketTypesobj->available = $current_available_seats = $available[$i];

					// Fix available value in table if count increases OR decrerases manually
					// $TicketTypesobj->available =$jteventHelper->fixavailableSeats($current_available_seats, $detailspresent, $eventid);
				}
			}

			$TicketTypesobj->eventid     = $eventid;
			$TicketTypesobj->title       = $title[$i];
			$TicketTypesobj->desc        = $desc[$i];
			$TicketTypesobj->price       = $price[$i];
			$TicketTypesobj->deposit_fee = $dep[$i];
			$TicketTypesobj->access      = $access[$i];

			$this->createTicketTypes($TicketTypesobj, 'com_jticketing');
		}
	}

	/**
	 * This is function is used to get event data
	 *
	 * @param   int  $eventid  event id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEvent($eventid)
	{
		$db = JFactory::getDBO();
		JLoader::import('event', JPATH_ADMINISTRATOR . '/components/com_jticketing/models');
		$model  = new jticketingModelEvent;
		$result = $model->getEvent($eventid);

		return $result;
	}

	/**
	 * This is function is used to get event ca
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventcat()
	{
		$db = JFactory::getDBO();
		JLoader::import('event', JPATH_ADMINISTRATOR . '/components/com_jticketing/models');
		$model  = new jticketingModelEvent;
		$result = $model->getEventsCats();

		return $result;
	}

	/**
	 * This is function is used to delete ticket types
	 *
	 * @param   int  $price       price
	 * @param   int  $curr        currency format
	 * @param   int  $formatting  formatting 1
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getFromattedPrice($price, $curr = null, $formatting = 1)
	{
		$db                         = JFactory::getDBO();
		$params                     = JComponentHelper::getParams('com_jticketing');
		$curr_sym                   = $params->get("currency_symbol");
		$curr_nam                   = $params->get("currency");
		$currency_display_format    = $params->get('currency_display_format', "{CURRENCY_SYMBOL} {AMOUNT} ");
		$price                      = intval(str_replace(',', '', $price));
		$price                      = number_format($price, 2);
		$currency_display_formatstr = str_replace('{AMOUNT}', $price, $currency_display_format);
		$currency_display_formatstr = str_replace('{CURRENCY_SYMBOL}', $curr_sym, $currency_display_formatstr);
		$currency_display_formatstr = str_replace('{CURRENCY}', $curr_nam, $currency_display_formatstr);
		$html                       = $currency_display_formatstr;

		return $html;
	}

	/**
	 * This is function is used to delete ticket types
	 *
	 * @param   int  $eventid  eventid
	 * @param   int  $client   client name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getIntegrationID($eventid, $client)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT id FROM #__jticketing_integration_xref WHERE source LIKE '" . $client . "' AND eventid=" . $eventid;
		$db->setQuery($query);

		return $rows = $db->loadResult();
	}

	/**
	 * This is function is used to delete ticket types
	 *
	 * @param   int  $integration_id  integration_id
	 * @param   int  $type_id         client name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getTicketTypes($integration_id, $type_id = '')
	{
		$db    = JFactory::getDBO();
		$query = "SELECT * FROM #__jticketing_types WHERE  eventid='" . (int) $integration_id . "'";
		$db->setQuery($query);

		return $rows = $db->loadObjectlist();
	}

	/**
	 * This is function is used to get custom fields for event
	 *
	 * @param   int  $integration_id  integration_id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventCustomFields($integration_id)
	{
		$db          = JFactory::getDBO();
		$tickettypes = $this->getTicketTypes($integration_id);
		$query       = "SELECT * FROM #__jticketing_field_values AS field_value
		LEFT JOIN #__jticketing_fields  as field ON field.id=field_value.field_id
		WHERE  event_id='" . $integration_id . "'";

		if ($tickettypes)
		{
			if ($tickettypes[0]->id)
			{
				$query .= " AND ticket_type_id=" . $tickettypes[0]->id;
			}
		}

		$db->setQuery($query);
		$rows        = $db->loadObjectlist();
		$fieldsarray = new StdClass;

		foreach ($rows as $row)
		{
			$fieldname               = $row->field_title;
			$fielvalue               = $row->field_value;
			$fieldsarray->$fieldname = $fielvalue;
		}

		return $fieldsarray;
	}

	/**
	 * This is function is used to convert time
	 *
	 * @param   int  $passedtime  time to format
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getTime($passedtime)
	{
		$db        = JFactory::getDBO();
		$time_hour = $passedtime['hour'];
		$time_min  = $passedtime['min'];
		$time_ampm = $passedtime['ampm'];

		if (($time_ampm == 'PM') && ($time_hour < 12))
		{
			$time_hour = $time_hour + 12;
		}
		elseif (($time_ampm == 'AM') && ($time_hour == 12))
		{
			$time_hour = $time_hour - 12;
		}

		return $time_final = $time_hour . ":" . $time_min;
	}

	/**
	 * This is function is used to get city from country
	 *
	 * @param   int  $country  country id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getCity($country)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT c.city_id, c.city
		FROM #__tj_city AS c
		LEFT JOIN #__tj_country AS con
		ON c.country_code=con.country_code
		WHERE con.country_id=" . $country . "
		ORDER BY c.city";
		$db->setQuery($query);
		$rows = $db->loadAssocList();

		return $rows;
	}

	/**
	 * This is used to create order object from data
	 *
	 * @param   int  $data  data passed
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createOrderObject($data)
	{
		$db = JFactory::getDBO();

		if (!$data['integraton_id'])
		{
			$data['integraton_id'] = $this->getIntegrationID($data['eventid'], $data['client']);
		}

		$res                   = new StdClass;
		$res->event_details_id = $data['integraton_id'];

		if (isset($data['name']))
		{
			$res->name = $data['name'];
		}

		if (isset($data['email']))
		{
			$res->email = $data['email'];
		}

		if (isset($data['user_id']))
		{
			$res->user_id = $data['user_id'];
		}

		$res->coupon_code             = $data['coupon_code'];
		$res->coupon_discount         = $data['coupon_discount'];
		$res->coupon_discount_details = $data['coupon_discount_details'];
		$res->order_tax               = $data['order_tax'];
		$res->order_tax_details       = $data['order_tax_details'];
		$res->cdate                   = date("Y-m-d H:i:s");
		$res->mdate                   = date("Y-m-d H:i:s");

		if (isset($data['processor']))
		{
			$res->processor = $data['processor'];
		}

		if (isset($data['customer_note']))
		{
			$res->customer_note = $data['customer_note'];
		}

		$res->ticketscount = $data['no_of_tickets'];

		if (!$data['parent_order_id'])
		{
			$res->parent_order_id = 0;
		}
		else
		{
			$res->parent_order_id = $data['parent_order_id'];
		}

		$res->status = 'P';

		// This is calculated amount
		$res->original_amount = $data['original_amt'];
		$res->amount          = $data['amount'];
		$res->fee             = $data['fee'];
		$res->ip_address      = $_SERVER["REMOTE_ADDR"];

		return $res;
	}

	/**
	 * This is used to create ticket types
	 *
	 * @param   object  $tickettypesobj  tickettypesobj
	 * @param   string  $client          com_jticketing
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createTicketTypes($tickettypesobj, $client = 'com_jticketing')
	{
		$db = JFactory::getDBO();

		if (!$tickettypesobj->id && $tickettypesobj->title && $tickettypesobj->eventid)
		{
			// Insert object
			if (!$db->insertObject('#__jticketing_types', $tickettypesobj, 'id'))
			{
				echo $db->stderr();

				return false;
			}

			$tickettypeid = $db->insertid();
		}
		else
		{
			$db->updateObject('#__jticketing_types', $tickettypesobj, 'id');
		}

		return $tickettypeid;
	}

	/**
	 * This is used to create order items
	 *
	 * @param   int  $orderdata  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createOrderItems($orderdata)
	{
		$db                = JFactory::getDBO();
		$order_items_array = $updated_ticket_field_value_id = array();
		$tid               = 0;

		// Delete order items That are removed
		if (array_key_exists("order_id", $orderdata))
		{
			if ($orderdata['order_id'])
			{
				$current_order_items = array();

				foreach ($orderdata['attendee_field'] as $attkeyold => $fieldsold)
				{
					// If order items id present update it
					if ($fieldsold['order_items_id'])
					{
						$current_order_items[] = $fieldsold['order_items_id'];
					}
				}

				$sql = "SELECT id FROM #__jticketing_order_items WHERE order_id=" . $orderdata['order_id'];
				$db->setQuery($sql);
				$ids = $db->loadColumn();

				if (count($ids) > count($current_order_items))
				{
					$diff    = array_diff($ids, $current_order_items);
					$diffids = implode("','", $diff);
					$query   = "DELETE FROM #__jticketing_order_items	WHERE id IN ('" . $diffids . "')";
					$db->setQuery($query);

					if (!$db->execute())
					{
					}

					$query = "DELETE FROM #__jticketing_ticket_field_values	WHERE id IN ('" . $diffids . "')";
					$db->setQuery($query);

					if (!$db->execute())
					{
					}
				}
			}
		}

		// Delete order items That are removed
		if (array_key_exists("attendee_field", $orderdata))
		{
			foreach ($orderdata['attendee_field'] as $attkey => $fields)
			{
				$res           = new StdClass;
				$res->id       = '';
				$res->owner_id = JFactory::getUser()->id;

				if ($fields['attendee_id'])
				{
					$attendee_id = $res->id = $fields['attendee_id'];
				}
				else
				{
					if (!$db->insertObject('#__jticketing_attendees', $res, 'id'))
					{
						echo $db->stderr();

						return false;
					}

					// Firstly create User Entry Field
					$attendee_id = $db->insertid();
				}

				$res               = new StdClass;
				$res->id           = '';
				$res->type_id      = $orderdata['all_event_data']['ticket_types']['0']->id;
				$res->ticketcount  = 1;
				$res->ticket_price = $orderdata['all_event_data']['ticket_types']['0']->price;

				if ($orderdata['order_id'])
				{
					$order_id = $res->order_id = $orderdata['order_id'];
				}
				else
				{
					$order_id = $res->order_id = $orderdata['inserted_orderid'];
				}

				$res->amount_paid = $orderdata['all_event_data']['ticket_types']['0']->deposit_fee + $fields['extra_amount'];
				$res->name        = $fields['name'];
				$res->email       = $fields['email'];

				// Insurance fees any extra fees
				$res->attribute_amount = $fields['attribute_amount'];
				$res->payment_status   = 'P';
				$res->attendee_id      = $attendee_id;

				// If order items id present update it
				if ($fields['order_items_id'])
				{
					$current_order_items[] = $fields['order_items_id'];
					$res->id               = $fields['order_items_id'];
					$insert_order_items_id = $fields['order_items_id'];

					if (!$db->updateObject('#__jticketing_order_items', $res, 'id'))
					{
					}
				}
				else
				{
					if (!$db->insertObject('#__jticketing_order_items', $res, 'id'))
					{
						echo $db->stderr();

						return false;
					}

					$insert_order_items_id = $db->insertid();
				}

				$order_items_array[] = $insert_order_items_id;

				// Save Custom user Entry Fields
				foreach ($fields as $key => $field)
				{
					$db->setQuery('SELECT id FROM `#__jticketing_attendee_fields` WHERE name LIKE  "' . $key . '"');
					$field_id = $db->loadResult();

					if ($field_id)
					{
						$row             = new stdClass;
						$row->id         = '';
						$field_id_exists = 0;
						$db->setQuery('SELECT id FROM `#__jticketing_attendee_field_values` WHERE attendee_id="' . $attendee_id . '" AND field_id=' . $field_id);
						$field_id_exists  = $db->loadResult();
						$row->field_id    = $field_id;
						$row->attendee_id = $attendee_id;
						$row->field_value = $field;

						if ($field_id_exists)
						{
							$row->id = $field_id_exists;

							if (!$db->updateObject('#__jticketing_attendee_field_values', $row, 'id'))
							{
							}
						}
						else
						{
							if (!$db->insertObject('#__jticketing_attendee_field_values', $row, 'id'))
							{
							}
						}
					}

					// Saving Ticket type fields
					if (!$field_id)
					{
						$db->setQuery('SELECT id FROM `#__jticketing_ticket_fields` WHERE name LIKE  "' . $key . '"');
						$field_id = $db->loadResult();

						if ($field_id)
						{
							$field_id_exists = 0;
							$qry             = 'SELECT id FROM `#__jticketing_ticket_field_values`
							WHERE order_items_id="' . $insert_order_items_id . '" AND field_id=' . $field_id;
							$db->setQuery($qry);
							$field_id_exists                       = $db->loadResult();
							$resdt                                 = new stdClass;
							$resdt->id                             = $field_id_exists;
							$ticket_field_ids[$tid]['field_id']    = $resdt->field_id = $field_id;
							$ticket_field_ids[$tid]['attendee_id'] = $resdt->attendee_id = $attendee_id;
							$tid++;
							$resdt->order_items_id = $insert_order_items_id;
							$resdt->field_value    = $field;

							if ($field_id_exists)
							{
								$updated_ticket_field_value_id[] = $resdt->id;

								if (!$db->updateObject('#__jticketing_ticket_field_values', $resdt, 'id'))
								{
								}
							}
							elseif ($db->insertObject('#__jticketing_ticket_field_values', $resdt, 'id'))
							{
								$updated_ticket_field_value_id[] = $db->insertid();
							}
						}
					}
				}
			}
		}

		// Delete records that are removed
		if (!empty($updated_ticket_field_value_id))
		{
			$otemsstr = implode("','", $order_items_array);
			$ids      = $diff = array();
			$sql      = "SELECT id FROM #__jticketing_ticket_field_values WHERE order_items_id IN ('" . $otemsstr . "')";
			$db->setQuery($sql);
			$ids  = $db->loadColumn();
			$diff = array_diff($ids, $updated_ticket_field_value_id);

			if ($diff)
			{
				$diffids = implode("','", $diff);
				$query   = "DELETE FROM #__jticketing_ticket_field_values	WHERE id IN ('" . $diffids . "')";
				$db->setQuery($query);

				if (!$db->execute())
				{
				}
			}
		}

		// Update Total Values in main order
		if (!empty($order_id))
		{
			$sql = "SELECT sum(attribute_amount) as total_attribute_amount,sum(ticket_price) as total_ticket_price,
			sum(amount_paid) as total_amount_paid  FROM #__jticketing_order_items WHERE order_id=" . $order_id;
			$db->setQuery($sql);
			$totaldata                = $db->loadObjectlist();
			$total_original_amt       = $totaldata[0]->total_ticket_price + $totaldata[0]->total_attribute_amount;
			$total_paid_amt           = $totaldata[0]->total_amount_paid + $totaldata[0]->total_attribute_amount;
			$res_tot                  = new StdClass;
			$res_tot->id              = $order_id;
			$res_tot->original_amount = $total_original_amt;
			$res_tot->amount          = $total_paid_amt;
			$db->updateObject('#__jticketing_order', $res_tot, 'id');
		}
	}

	/**
	 * This is used to createMainOrder
	 *
	 * @param   int  $orderdata  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createMainOrder($orderdata)
	{
		$db  = JFactory::getDBO();
		$res = $this->createOrderObject($orderdata);

		// Update order if orde_id present
		if (isset($orderdata['order_id']))
		{
			$res->id = $orderdata['order_id'];
			$db->updateObject('#__jticketing_order', $res, 'id');
			$insert_order_id = $orderdata['order_id'];
		}
		else
		{
			// Store Order to Jticketing Table
			$lang      = JFactory::getLanguage();
			$extension = 'com_jticketing';
			$base_dir  = JPATH_ROOT;
			$lang->load($extension, $base_dir);
			$com_params     = JComponentHelper::getParams('com_jticketing');
			$integration    = $com_params->get('integration');
			$guest_reg_id   = $com_params->get('guest_reg_id');
			$auto_fix_seats = $com_params->get('auto_fix_seats');
			$currency       = $com_params->get('currency');
			$order_prefix   = $com_params->get('order_prefix');
			$separator      = $com_params->get('separator');
			$random_orderid = $com_params->get('random_orderid');
			$padding_count  = $com_params->get('padding_count');

			// Lets make a random char for this order take order prefix set by admin
			$order_prefix = (string) $order_prefix;

			// String length should not be more than 5
			$order_prefix = substr($order_prefix, 0, 5);

			// Take separator set by admin
			$separator     = (string) $separator;
			$res->order_id = $order_prefix . $separator;

			// Check if we have to add random number to order id
			$use_random_orderid = (int) $random_orderid;

			if ($use_random_orderid)
			{
				$random_numer = $this->_random(5);
				$res->order_id .= $random_numer . $separator;

				// Order_id_column_field_length - prefix_length - no_of_underscores - length_of_random number
				$len = (23 - 5 - 2 - 5);
			}
			else
			{
				/* This length shud be such that it matches the column lenth of primary key
				It is used to add pading
				order_id_column_field_length - prefix_length - no_of_underscores*/
				$len = (23 - 5 - 2);
			}

			if (!$db->insertObject('#__jticketing_order', $res, 'id'))
			{
				echo $db->stderr();

				return false;
			}

			$insert_order_id = $orders_key = $sticketid = $db->insertid();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('order_id')));
			$query->from($db->quoteName('#__jticketing_order'));
			$query->where($db->quoteName('id') . " = " . $db->quote($orders_key));
			$db->setQuery($query);
			$order_id      = (string) $db->loadResult();
			$maxlen        = 23 - strlen($order_id) - strlen($orders_key);
			$padding_count = (int) $padding_count;

			// Use padding length set by admin only if it is les than allowed(calculate) length
			if ($padding_count > $maxlen)
			{
				$padding_count = $maxlen;
			}

			if (strlen((string) $orders_key) <= $len)
			{
				$append = '';

				for ($z = 0; $z < $padding_count; $z++)
				{
					$append .= '0';
				}

				$append = $append . $orders_key;
			}

			$resd     = new stdClass;
			$resd->id = $orders_key;
			$order_id = $resd->order_id = $order_id . $append;

			if (!$db->updateObject('#__jticketing_order', $resd, 'id'))
			{
			}
		}

		return $insert_order_id;
	}

	/**
	 * This is used to createMainOrder
	 *
	 * @param   int  $orderdata  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createOrder($orderdata)
	{
		$db                            = JFactory::getDBO();
		$insert_order_id               = $this->createMainOrder($orderdata);
		$orderdata['inserted_orderid'] = $insert_order_id;
		$this->createOrderItems($orderdata);

		return $insert_order_id;
	}

	/**
	 * This is used to get order data
	 *
	 * @param   int  $orderid  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getOrderData($orderid)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT * FROM `#__jticketing_order` WHERE `id`='" . $orderid . "'";
		$db->setQuery($query);
		$details = $db->loadObject();

		return $details;
	}

	/**
	 * This is used to get order
	 *
	 * @param   int  $orderid  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getOrder($orderid)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM #__jticketing_order WHERE id=' . $orderid);
		$orderdata['orderdata'] = $db->loadObjectlist();
		$db->setQuery('SELECT * FROM #__jticketing_order_items WHERE order_id=' . $orderid);
		$orderdata['orderitems'] = $db->loadObjectlist();

		return $orderdata;
	}

	/**
	 * This is used to get order items
	 *
	 * @param   int  $orderid  orderid
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getOrderItems($orderid)
	{
		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM #__jticketing_order_items WHERE order_id=' . $orderid);
		$orderdata = $db->loadObjectlist();

		return $orderdata;
	}

	/**
	 * This is used to get order items
	 *
	 * @param   int     $orderid    orderid
	 * @param   object  $orderdata  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updateOrder($orderid, $orderdata = '')
	{
		$db = JFactory::getDBO();

		if (!$orderdata)
		{
			$orderdata = $this->getOrder();
		}

		$resd                          = new stdClass;
		$resd->id                      = $orderdata['orderdata']->id;
		$resd->status                  = $orderdata['orderdata']->status;
		$resd->parent_order_id         = $orderdata['orderdata']->parent_order_id;
		$resd->amount                  = $orderdata['orderdata']->amount;
		$resd->coupon_discount         = $orderdata['orderdata']->coupon_discount;
		$resd->coupon_discount_details = $orderdata['orderdata']->coupon_discount_details;

		if (!$db->updateObject('#__jticketing_order', $resd, 'id'))
		{
		}

		$this->updateOrderItems($orderdata);
	}

	/**
	 * This is used to get custom user entry fields
	 *
	 * @param   int  $params  params
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getUserEntryField($params = '')
	{
		$db          = JFactory::getDBO();
		$where_attnd = $details = $where = '';

		if (isset($params['field_id']))
		{
			$where .= " AND  atnfld_value.field_id=" . $params['field_id'];
		}

		if (isset($params['user_id']))
		{
			$where .= "  AND  attnds.owner_id=" . $params['user_id'];
		}

		if (isset($params['attendee_id']))
		{
			$where_attnd = " WHERE id=" . $params['attendee_id'];
		}

		$query = "SELECT id FROM #__jticketing_attendees AS attnds " . $where_attnd;
		$db->setQuery($query);
		$attendee_ids = $db->loadObjectlist();

		foreach ($attendee_ids AS $attendee_id)
		{
			$result = '';
			$query  = "SELECT fieldstable.name,atnfld_value.field_value,atnfld_value.field_id
			FROM #__jticketing_attendees AS attnds INNER JOIN #__jticketing_attendee_field_values AS atnfld_value
			ON attnds.id=atnfld_value.attendee_id INNER JOIN  #__jticketing_attendee_fields AS fieldstable
			ON fieldstable.id=atnfld_value.field_id WHERE field_source='com_jticketing'
			AND  attnds.id=" . $attendee_id->id . $where;
			$db->setQuery($query);
			$result = $db->loadObjectlist();

			if ($result)
			{
				$details[$attendee_id->id] = $result;
			}
		}

		return $details;
	}

	/**
	 * This is used to get custom user entry fields from tjfields
	 *
	 * @param   int  $params  params
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getUniversalUserEntryField($params = '')
	{
		$db                 = JFactory::getDBO();
		$field_manager_path = JPATH_SITE . "/components/com_tjfields/helpers/tjfields.php";

		if (file_exists($field_manager_path))
		{
			$TjfieldsHelper          = new TjfieldsHelper;
			$universalAttendeeFields = $TjfieldsHelper->getUniversalFields('com_jticketing.ticket');
			$where_attnd             = $details = $where = '';

			if (isset($params['field_id']))
			{
				$where .= " AND  atnfld_value.field_id=" . $params['field_id'];
			}

			if (isset($params['user_id']))
			{
				$where .= "  AND  attnds.owner_id=" . $params['user_id'];
			}

			if (isset($params['attendee_id']))
			{
				$where_attnd = " WHERE id=" . $params['attendee_id'];
			}

			$query = "SELECT id FROM #__jticketing_attendees AS attnds " . $where_attnd;
			$db->setQuery($query);
			$attendee_ids = $db->loadObjectlist();
			$result       = '';

			if ($universalAttendeeFields)
			{
				foreach ($attendee_ids AS $attendee_id)
				{
					$i = 0;

					foreach ($universalAttendeeFields AS $field)
					{
						$query = "SELECT atnfld_value.field_value FROM #__jticketing_attendees AS attnds
						INNER JOIN #__jticketing_attendee_field_values AS atnfld_value
						ON attnds.id=atnfld_value.attendee_id
						WHERE field_source='com_tjfields.com_jticketing.ticket' AND atnfld_value.field_id=" . $field->id . "
						AND  attnds.id=" . $attendee_id->id . $where;
						$db->setQuery($query);
						$resultobj = $db->loadObject();

						if (!empty($resultobj))
						{
							$result[$i]           = $resultobj;
							$result[$i]->name     = $field->name;
							$result[$i]->field_id = $field->id;
							$i++;
						}
					}

					if (!empty($result))
					{
						$details[$attendee_id->id] = $result;
					}
				}
			}

			if (!empty($details))
			{
				return $details;
			}
		}
	}

	/**
	 * This is used to get custom user entry fields from tjfields
	 *
	 * @param   int  $params  params
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getTicketField($params = '')
	{
		$db    = JFactory::getDBO();
		$query = "SELECT field_id,name,field_value FROM #__jticketing_ticket_field_values AS otems
		INNER JOIN #__jticketing_ticket_fields AS fields ON fields.id=otems.field_id
		 WHERE order_items_id=" . $params['order_items_id'];
		$db->setQuery($query);
		$result = $db->loadObjectlist();

		return $result;
	}

	/**
	 * This is used to update orderdata
	 *
	 * @param   int  $orderdata  orderdata
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function updateOrderItems($orderdata)
	{
		$db = JFactory::getDBO();

		foreach ($orderdata['order_items'] as $key => $value)
		{
		}
	}

	/**
	 * This is used to get random no
	 *
	 * @param   int  $length  length
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function _random($length = 5)
	{
		$db     = JFactory::getDBO();
		$salt   = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len    = strlen($salt);
		$random = '';
		$stat   = @stat(__FILE__);

		if (empty($stat) || !is_array($stat))
		{
			$stat = array(
				php_uname()
			);
		}

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i++)
		{
			$random .= $salt[mt_rand(0, $len - 1)];
		}

		return $random;
	}

	/**
	 * This is used to get booking details
	 *
	 * @param   int  $id  order id of event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getbookingDetails($id)
	{
		$db    = JFactory::getDBO();
		$user  = JFactory::getUser();
		$query = "SELECT sum(oi.`ticket_price`+ oi.`attribute_amount`) as ticket_price ,
		event_details_id,sum(oi.`amount_paid`+oi.`attribute_amount`) as paid_amount ,oi.payment_status,t.title
		FROM `#__jticketing_order_items` as oi , `#__jticketing_order` as o, `#__jticketing_types` as t
		WHERE  oi. `order_id`= o.id AND  o.event_details_id = t.eventid
		AND oi. `order_id`='" . $id . "' AND (oi.`payment_status`='C' OR oi.`payment_status`='DP')";
		$db->setQuery($query);
		$details       = $db->loadObjectList();
		$camper_amount = $this->getCamperDetails($id);
		$data          = array(
			'order' => $details,
			'order_item' => $camper_amount
		);

		return $data;
	}

	/**
	 * This is used to get event name
	 *
	 * @param   int  $id  order id of event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getEventName($id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT DISTINCT (e.title) FROM `#__jticketing_order_items` as oi,
		`#__jticketing_events_xref` as x,  `#__jticketing_events`as e WHERE oi. `type_id`=x.id
		and x.eventid = e.id AND oi.type_id = '" . $id . "'";
		$db->setQuery($query);
		$details = $db->loadResult();

		return $details;
	}

	/**
	 * This is used to get event name
	 *
	 * @param   int  $teid  order id of event
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getCancellationfee($teid)
	{
		$db = JFactory::getDBO();
		$q  = "SELECT DISTINCT(x.eventid) FROM `#__jticketing_order_items` as oi  ,
		`#__jticketing_events_xref` as x WHERE  oi.type_id = x.id and oi.id ='" . $teid . "'";
		$db->setQuery($q);
		$eid = $db->loadResult();
		$q   = "SELECT `field_value` FROM `#__jticketing_field_values` WHERE
		`event_id`='" . $eid . "'and `field_id`='3'";
		$db->setQuery($q);
		$details = $db->loadResult();

		return $details;
	}

	/**
	 * This is used to get event name
	 *
	 * @param   int  $id  get refund data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getRefundData($id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT * FROM `#__jticketing_order_items` WHERE `id`='" . $id . "'";
		$db->setQuery($query);
		$details    = $db->loadObject();
		$refund_amt = $this->getCancellationfee($id);
		$data       = array(
			'order_item' => $details,
			'refund_fee' => $refund_amt
		);

		return $data;
	}

	/**
	 * This is used to get transfer fee
	 *
	 * @param   int  $teid  order id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getTransferfee($teid)
	{
		$db = JFactory::getDBO();
		$q  = "SELECT DISTINCT(x.eventid) FROM `#__jticketing_order_items` as oi  ,
		`#__jticketing_events_xref` as x WHERE  oi.type_id = x.id and oi.id ='" . $teid . "'";
		$db->setQuery($q);
		$eid = $db->loadResult();
		$q   = "SELECT `field_value` FROM `#__jticketing_field_values` WHERE
		`event_id`='" . $eid . "'and `field_id`='2'";
		$db->setQuery($q);
		$details = $db->loadResult();

		return $details;
	}

	/**
	 * This is used to get transfer data
	 *
	 * @param   int  $id  order id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getTransferData($id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT * FROM `#__jticketing_order_items` WHERE `id`='" . $id . "'";
		$db->setQuery($query);
		$details               = $db->loadObject();
		$refund_amt            = $this->getTransferfee($id);
		$details->price        = '';
		$details->transfer_fee = $refund_amt;
		$details               = array(
			$details
		);

		return $details;
	}

	/**
	 * This is used to check if late fee applied
	 *
	 * @param   date  $booking_end_date  order id
	 * @param   date  $event_start_date  order id
	 *
	 * @return  boolean  1 or 0
	 *
	 * @since   1.0
	 */
	public function isLatefeeApply($booking_end_date, $event_start_date)
	{
		$db                = JFactory::getDBO();
		$current_timestamp = time();
		$booking_end_date  = strtotime($booking_end_date);
		$startdate         = date('Y-m-d', strtotime($event_start_date));
		$event_startdate   = $startdate . " 23:59:59";
		$event_start_time  = strtotime($event_startdate);

		if ($current_timestamp > $booking_end_date and $current_timestamp <= $event_start_time)
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * This is used to check if late fee applied
	 *
	 * @param   string  $source   com_jticketing/com_jevents
	 * @param   int     $eventid  com_jticketing/com_jevents
	 *
	 * @return  boolean  1 or 0
	 *
	 * @since   1.0
	 */
	public function getXreftableID($source, $eventid)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$columnArray = array('eventid','id');
		$query->select($db->quoteName($columnArray));
		$query->from($db->quoteName('#__jticketing_integration_xref'));
		$query->where($db->quoteName('source') . ' = ' . $db->quote($source));
		$query->where($db->quoteName('eventid') . ' = ' . $db->quote($eventid));
		$db->setQuery($query);
		$rows = $db->loadObject();

		return $rows;
	}

	/**
	 * Load Assets which are require for quick2cart.
	 *
	 * @return  null.
	 *
	 * @since   12.2
	 */
	public static function loadjticketingAssetFiles()
	{
		$qtcParams = JComponentHelper::getParams('com_jticketing');

		// Define wrapper class
		if (!defined('JTICKETING_WRAPPER_CLASS'))
		{
			$wrapperClass   = "jticketing-wrapper";
			$currentBSViews = $qtcParams->get('currentBSViews', "bs3");

			if (version_compare(JVERSION, '3.0', 'lt'))
			{
				if ($currentBSViews == "bs3")
				{
					$wrapperClass = " jticketing-wrapper techjoomla-bootstrap ";
				}
				else
				{
					$wrapperClass = " jticketing-wrapper techjoomla-bootstrap ";
				}
			}
			else
			{
				$wrapperClass = " jticketing-wrapper ";

				if ($currentBSViews == "bs3")
				{
					$wrapperClass = " jticketing-wrapper tjBs3 ";
				}
				else
				{
					$wrapperClass = " jticketing-wrapper ";
				}
			}

			define('JTICKETING_WRAPPER_CLASS', $wrapperClass);
		}

		// Load js assets
		jimport('joomla.filesystem.file');
		$tjStrapperPath = JPATH_SITE . '/media/techjoomla_strapper/tjstrapper.php';

		if (JFile::exists($tjStrapperPath))
		{
			require_once $tjStrapperPath;
			TjStrapper::loadTjAssets('com_jticketing');
		}

		// According to component option load boostrap3 css file and chagne the wrapper
	}

	/**
	 * This function is used to load javascripts in component
	 *
	 * @param   string  &$jsFilesArray  com_jticketing/com_jevents
	 *
	 * @return  boolean  1 or 0
	 *
	 * @since   1.0
	 */
	public function getJticketingJsFiles(&$jsFilesArray)
	{
		$db       = JFactory::getDBO();
		$input    = JFactory::getApplication()->input;
		$option   = $input->get('option', '');
		$view     = $input->get('view', '');
		$app      = JFactory::getApplication();
		$document = JFactory::getDocument();

		// Load css files
		$comparams      = JComponentHelper::getParams('com_jticketing');
		$load_bootstrap = $comparams->get('load_bootstrap');

		// Load bootstrap.min.js before loading other files
		if (!$app->isAdmin())
		{
			if ($load_bootstrap)
			{
				$document->addStyleSheet(JUri::root(true) . '/media/techjoomla_strapper/bs3/css/bootstrap.min.css');
			}

			// Get plugin 'relatedarticles' of type 'content'
			$plugin = JPluginHelper::getPlugin('system', 'plug_sys_jticketing');

			if ($plugin)
			{
				// Get plugin params
				$pluginParams = new JRegistry($plugin->params);
				$load         = $pluginParams->get('loadBS3js');
			}

			if (!empty($load))
			{
				$jsFilesArray[] = 'media/techjoomla_strapper/bs3/js/bootstrap.min.js';
			}
		}

		if ($option == "com_jticketing")
		{
			// Load the view specific js
			switch ($view)
			{
				case "order":
					$jsFilesArray[] = 'components/com_jticketing/assets/js/fuelux2.3loader.min.js';
					$jsFilesArray[] = 'media/com_jticketing/js/steps.js';
					$jsFilesArray[] = 'media/com_jticketing/vendors/js/masonry.pkgd.min.js';
					break;
			}
		}

		return $jsFilesArray;
	}

	/**
	 * This is used to get venue name
	 *
	 * @param   int  $id  order id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getVenue($id)
	{
		$jticketingModelVenueForm = JModelLegacy::getInstance('VenueForm', 'JticketingModel');
		$venueName = $jticketingModelVenueForm->getItem($id);

		return $venueName;
	}

	/**
	 * Get global attendee fields
	 *
	 * @return  object $db
	 *
	 * @since   2.0
	 */
	public function getGlobalAtendeeFields()
	{
		$db = JFactory::getDbo();
		$query1 = $db->getQuery(true);
		$query2 = $db->getQuery(true);
		$columnArray = array('id', 'label', 'type');
		$query2->select($columnArray);
		$query2->from($db->quoteName('#__jticketing_attendee_fields'));
		$query2->where($db->quoteName('core') . " = 1");
		$query2->where($db->quoteName('state') . " = 1");
		$query1->select($columnArray);
		$query1->from($db->quoteName('#__tjfields_fields'));
		$query1->where($db->quoteName('client') . " = " . $db->quote('com_jticketing.ticket'));
		$query1->union($query2);
		$db->setQuery($query1);

		return $db->loadObjectList();
	}

	/**
	 * get ticket types of the event
	 *
	 * @param   integer  $xrefId  id for the event in integration table
	 *
	 * @return integer   $db        ticket types ids
	 *
	 * @since  2.0
	 */
	public function getTicketTypeFields($xrefId)
	{
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__jticketing_types'));
		$query->where($db->quoteName('eventid') . ' = ' . $db->quote($xrefId));
		$db->setQuery($query);

		return $db->loadAssocList();
	}

	/**
	 * This is used to generate custom fields
	 *
	 * @param   int  $fieldset  be it attendee fields or ticket types
	 *
	 * @param   int  $event_id  event id in case of edit event
	 *
	 * @param   int  $source    for multiple integrations
	 *
	 * @return  $html
	 *
	 * @since   2.0
	 */
	public function generateCustomFieldHtml($fieldset, $event_id, $source)
	{
		if ($source == "com_community")
		{
			$form_path = JPATH_ADMINISTRATOR . '/components/com_jticketing/models/forms/jomsocial/eventjs.xml';
		}
		else
		{
			$form_path = JPATH_ADMINISTRATOR . '/components/com_jticketing/models/forms/integration/eventint.xml';
		}

		$form = JForm::getInstance('', $form_path);

		if (!empty($event_id))
		{
			$xrefId = $this->getXreftableID($source, $event_id);
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models');
			$jticketingTickettypesModel = JModelLegacy::getInstance('Tickettypes', 'JticketingModel');
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables', 'Tickettypes');
			$ticketData = $jticketingTickettypesModel->getTicketTypeFields($xrefId->id);
			$attendeeFieldModel = JModelLegacy::getInstance('Attendeefields', 'JTicketingModel');
			$attendeeCoreFieldModel = JModelLegacy::getInstance('Attendeecorefields', 'JTicketingModel');
			$attendeeData = $attendeeCoreFieldModel->getAttendeeFields($xrefId->id);
			$customFieldData = array();

			foreach ($ticketData as $key => $ticket)
			{
				$ticketFields["tickettypes" . $key] = $jticketingTickettypesModel->getItem($ticket['id']);
			}

			$customTicketFieldData[] = array("tickettypes" => $ticketFields);

			if (!empty($attendeeData))
			{
				foreach ($attendeeData as $key => $attendee)
				{
					$attendeeFields["attendeefields" . $key] = (array) $attendeeFieldModel->getItem($attendee['id']);
				}

				$customAttendeeFieldData[] = array("attendeefields" => $attendeeFields);
				$form->bind($customAttendeeFieldData);
			}

			$form->bind($customTicketFieldData);
		}

		$fieldSet = $form->getFieldset($fieldset);
		$html = array();

		foreach ($fieldSet as $field)
		{
			$html[] = $field->renderField();
		}

		return implode('', $html);
	}

	/**
	 * This is used get custom field types
	 *
	 * @param   int  $fieldType  be it attendee fields or ticket types
	 *
	 * @param   int  $event_id   event id in case of edit event
	 *
	 * @param   int  $source     for multiple integrations
	 *
	 * @return  customFields
	 *
	 * @since   2.0
	 */
	public function getCustomFieldTypes($fieldType, $event_id, $source)
	{
		if ($fieldType == "attendeeFields")
		{
			$customFields = $this->generateCustomFieldHtml('attendeefields', $event_id, $source);
		}
		else
		{
			$customFields = $this->generateCustomFieldHtml('ticket_types', $event_id, $source);
		}

		return $customFields;
	}
}
