<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');
$tjFieldsPath = JPATH_SITE . '/components/com_tjfields/filterFields.php';

if (JFile::exists($tjFieldsPath))
{
	require_once $tjFieldsPath;
}

require_once JPATH_SITE . '/components/com_jticketing/models/attendeefields.php';
require_once JPATH_SITE . '/components/com_jticketing/models/tickettypes.php';
require_once JPATH_SITE . '/components/com_jticketing/models/integrationxref.php';

/**
 * model for showing order
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */

class JticketingModelEventForm extends JModelAdmin
{
	use TjfieldsFilterField;
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Event', $prefix = 'JTicketingTable', $config = array())
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			return JTable::getInstance($type, $prefix, $config);
		}
		else
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

			return JTable::getInstance($type, $prefix, $config);
		}
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Check admin and load admin form in case of admin event form
		if ($app->isAdmin())
		{
			// Get the form.
			$form = $this->loadForm('com_jticketing.event', 'event', array('control' => 'jform', 'load_data' => $loadData));
		}
		else
		{
			// Get the form.
			$form = $this->loadForm('com_jticketing.eventform', 'eventform', array('control' => 'jform', 'load_data' => $loadData));
		}

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_jticketing.edit.event.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$com_params    = JComponentHelper::getParams('com_jticketing');
		$this->collect_attendee_info_checkout = $com_params->get('collect_attendee_info_checkout');

		if ($item = parent::getItem($pk))
		{
			if (!empty($item->id))
			{
				$xrefId = $jticketingfrontendhelper->getXreftableID('com_jticketing', $item->id);
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'integrationxref');
				$JTIcketingModelIntegrationXref = JModelLegacy::getInstance('Integrationxref', 'JTicketingModel');

				if (!empty($xrefId))
				{
					$integrationData = $JTIcketingModelIntegrationXref->getItem($xrefId->id);

					if (!empty($integrationData->vendor_id))
					{
						$item->vendor_id = $integrationData->vendor_id;
					}
				}

				$ticketTypes = array();
				$attendeeFields = array();
				$db = JFactory::getDbo();

				// Load ticket types data
				$query = $db->getQuery(true);
				$query->select('*');
				$query->from($db->quoteName('#__jticketing_types'));

				if (!empty($xrefId))
				{
					$query->where($db->quoteName('eventid') . " = " . $db->quote($xrefId->id));
				}

				$db->setQuery($query);
				$ticketTypes = $db->loadObjectList();

				$item->tickettypes = $ticketTypes;

				if ($this->collect_attendee_info_checkout == 1)
				{
					// Load attendee fields data
					$query1 = $db->getQuery(true);
					$query1->select('*');
					$query1->from($db->quoteName('#__jticketing_attendee_fields'));

					if (!empty($xrefId))
					{
						$query1->where($db->quoteName('eventid') . " = " . $db->quote($xrefId->id));
					}

					$query1->where($db->quoteName('core') . " != " . $db->quote('1'));

					$db->setQuery($query1);
					$item->attendeefields = $db->loadObjectList();
				}

				if ($item->id)
				{
					$modelMediaXref = JModelLegacy::getInstance('MediaXref', 'JTicketingModel');
					$modelMedia = JModelLegacy::getInstance('Media', 'JTicketingModel');
					$mediaGallery = $modelMediaXref->getEventMedia($item->id, 'com_jticketing.event', 1);

					if ($mediaGallery)
					{
						$galleryFiles = array();

						foreach ($mediaGallery as $mediaXref)
						{
							$galleryFiles[] = $modelMedia->getItem($mediaXref->media_id);
						}

						$item->gallery = $galleryFiles;
					}

					$eventMainImage = $modelMediaXref->getEventMedia($item->id, 'com_jticketing.event', 0);

					if (!empty($eventMainImage))
					{
						$item->image = $modelMedia->getItem($eventMainImage[0]->media_id);
					}
				}
			}
		}

		return $item;
	}

	/**
	 * Method to save an event data.
	 *
	 * @param   array  $data  data
	 *
	 * @return  void
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$com_params    = JComponentHelper::getParams('com_jticketing');
		$enforceVendor = $com_params->get('enforce_vendor');
		$jticketingfrontendhelper = new jticketingfrontendhelper;
		$this->collect_attendee_info_checkout = $com_params->get('collect_attendee_info_checkout');
		$xrefData = array();

		$siteCall = $app->isSite();

		if ($enforceVendor && $siteCall)
		{
			$user_id = JFactory::getUser()->id;
			$data['created_by'] = $user_id;
			$xrefData['vendor_id'] = $data['vendor_id'];
		}
		else
		{
			$tJvendorsHelper = new TjvendorsHelpersTjvendors;
			$getVendorId = $tJvendorsHelper->getVendorId($data['created_by']);

			if (empty($getVendorId))
			{
				$vendorData['vendor_client'] = "com_jticketing";
				$vendorData['user_id'] = $data['created_by'];
				$vendorData['vendor_title'] = $data['userName'];
				$vendorData['state'] = "1";
				$vendorData['params'] = null;
				$xrefData['vendor_id'] = $tJvendorsHelper->addVendor($vendorData);
			}
			else
			{
				$xrefData['vendor_id'] = $getVendorId;
			}
		}

		$date = JFactory::getDate();

		if ($data['id'])
		{
			$data['modified'] = $date->toSql(true);
		}
		else
		{
			$data['created'] = $date->toSql(true);
		}

		if ($data['online_events'] == 1)
		{
			$eventData = $this->createOnlineEvent($data);

			if ($eventData != 1)
			{
				$data['jt_params'] = $eventData;
			}

			if ($eventData === false)
			{
				return false;
			}
		}

		// TRIGGER Before Event Create
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$dispatcher->trigger('jt_OnBeforeEventCreate', array($data));
		$table = $this->getTable();

		if ($data['id'] != 0)
		{
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
			$jticketingEventformModel = JModelLegacy::getInstance('Eventform', 'JticketingModel');
			$oldEventData = $jticketingEventformModel->getItem($data['id']);
		}

		// Bind data
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		if (parent::save($data))
		{
			$id = (int) $this->getState($this->getName() . '.id');

			if (!empty($data['id']))
			{
				$extra_field_data = array();
				$extra_field_data['content_id'] = $data['id'];
				$extra_field_data['client'] = 'com_jticketing.event';
				$extra_field_data['fieldsvalue'] = $data['extra_jform_data'];

				// Save extra fields data.
				$this->saveExtraFields($extra_field_data, $data['id'], $data['created_by']);
			}

			if ($id)
			{
				$xrefData['eventid'] = $id;
				$xrefData['source'] = "com_jticketing";
				$xrefData['userid'] = $data['created_by'];
				$xrefId = $jticketingfrontendhelper->getXreftableID('com_jticketing', $id);

				if (empty($xrefId))
				{
					JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'integrationxref');
					$JTIcketingModelIntegrationXref = JModelLegacy::getInstance('Integrationxref', 'JTicketingModel');
					$JTIcketingModelIntegrationXref->save($xrefData);
				}

				$xrefId = $jticketingfrontendhelper->getXreftableID('com_jticketing', $id);
				$ticketTypes = $data['tickettypes'];
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'tickettypes');
				$ticketTypesModel = JModelLegacy::getInstance('Tickettypes', 'JticketingModel');
				$tickets = array();
				$ticketFieldIds = $jticketingfrontendhelper->getTicketTypeFields($xrefId->id);

				if ($ticketFieldIds)
				{
					foreach ($ticketFieldIds as $ticketId)
					{
						$deleteTicketField = $ticketTypesModel->delete($ticketId['id']);
					}
				}

				foreach ($ticketTypes as $ticketType)
				{
					$tickets['id'] = '';
					$tickets['count'] = $ticketType['available'];

					$tickets['title'] = $ticketType['title'];
					$tickets['desc'] = $ticketType['desc'];
					$tickets['unlimited_seats'] = $ticketType['unlimited_seats'];
					$tickets['available'] = $ticketType['available'];
					$tickets['state'] = $ticketType['state'];
					$tickets['price'] = $ticketType['price'];

					$tickets['access'] = $ticketType['access'];
					$tickets['eventid'] = $xrefId->id;
					$ticketTypesModel->save($tickets);
				}

					$attendeeFieldModel = JModelLegacy::getInstance('Attendeefields', 'JTicketingModel');
					$attendeeCoreFieldModel = JModelLegacy::getInstance('Attendeecorefields', 'JTicketingModel');
					$attendeeFieldIds = $attendeeCoreFieldModel->getAttendeeFields($xrefId->id);

					if ($attendeeFieldIds)
					{
						foreach ($attendeeFieldIds as $attendeeId)
						{
							$deleteAttendeeField = $attendeeFieldModel->delete($attendeeId['id']);
						}
					}

				if ($this->collect_attendee_info_checkout == 1)
				{
					// Save Attendee fields
					$attendeeFields = $data['attendeefields'];
					$attendeeFieldsModel = JModelLegacy::getInstance('Attendeefields', 'JticketingModel');

					foreach ($attendeeFields as $attendeeField)
					{
						$attendeeData['id'] = '';

						$attendeeData['label'] = $attendeeField['label'];
						$attendeeData['type'] = $attendeeField['type'];
						$attendeeData['default_selected_option'] = $attendeeField['default_selected_option'];
						$attendeeData['required'] = $attendeeField['required'];
						$attendeeData['eventid'] = $xrefId->id;
						$attendeeData['state'] = 1;

						if (!empty($attendeeData['label']))
						{
							$string = strtolower($attendeeData['label']);
							$string = str_replace(' ', '_', $string);
							$attendeeData['name'] = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
							$return = $attendeeFieldsModel->save($attendeeData);
						}
					}
				}

				if (isset($data['image']['new_image']))
				{
					if ($this->saveMedia($data['image']['new_image'], 0, $id))
					{
						$mediaxrefTbl = $this->getTable('mediaxref');
						$mediaxrefTbl->load(array('media_id' => (int) $data['image']['old_image']));

						if ($mediaxrefTbl->id)
						{
							$modelMediaXref = JModelLegacy::getInstance('MediaXref', 'JTicketingModel');
							$modelMediaXref->delete($mediaxrefTbl->id);
						}
					}
				}

				if (isset($data['gallery_file']['media']))
				{
					$this->saveMedia($data['gallery_file']['media'], 1, $id);
				}
			}

			$data['eventId'] = $id;

			if ($data['id'] != 0)
			{
				$data['eventOldData'] = $oldEventData;
			}

			$dispatcher->trigger('jt_OnAfterEventCreate', array($data));

			return $id;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to get book venue
	 *
	 * @param   array  $event  An array to get the item values
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function getvenuehtml($event)
	{
		$array_venue = array();
		$db = JFactory::getDbo();
		$selectedvenue = $event->venue;

		$array_venue['venue'] = $selectedvenue;
		$array_venue['start_dt_timestamp'] = $event->startdate;
		$array_venue['end_dt_timestamp'] = $event->enddate;
		$array_venue['event_online'] = $event->online_events;
		$array_venue['created_by'] = $event->created_by;

		$getvenue = $this->getAvailableVenue($array_venue);
		$options = array();

		if (!empty($getvenue))
		{
			foreach ($getvenue as $u)
			{
				$options[] = JHtml::_('select.option', $u->id, $u->name);
			}
		}
		else
		{
			$u = new stdClass;
			$u->name = '';
		}

		return JHtml::_('select.genericlist', $options, $u->name, 'class="inputbox"  size="5"', 'value', 'text', $selectedvenue);
	}

	/**
	 * Method to get book venue
	 *
	 * @param   array  $array_venue  An array to get the booked values
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function getAvailableVenue($array_venue)
	{
		$db = JFactory::getDbo();
		$jinput = JFactory::getApplication()->input;
		$eventid = $jinput->get('id', '', 'STRING');

		$venue = $array_venue['venue'];
		$array_venue['start_dt_timestamp'];
		$array_venue['end_dt_timestamp'];
		$array_venue['event_online'];
		$created_by = $array_venue['created_by'];
		$query = $db->getQuery(true);

		if (!empty($eventid))
		{
		$query = "SELECT  v.id,v.name,v.created_by,v.privacy,v.state FROM   #__jticketing_venues AS v
			WHERE NOT EXISTS(SELECT NULL FROM #__jticketing_events AS ed WHERE ed.venue = v.id AND(('"
		. $array_venue["start_dt_timestamp"] . "' BETWEEN UNIX_TIMESTAMP(ed.startdate) AND UNIX_TIMESTAMP(ed.enddate)) OR ('"
		. $array_venue["end_dt_timestamp"] . "' BETWEEN UNIX_TIMESTAMP(ed.startdate) AND UNIX_TIMESTAMP(ed.enddate)) OR ('"
		. $array_venue["start_dt_timestamp"] . "' <= UNIX_TIMESTAMP(ed.startdate) AND '"
		. $array_venue["end_dt_timestamp"] . "' >= UNIX_TIMESTAMP(ed.enddate))
		 )) AND v.online = "
		. $array_venue['event_online'] . " AND v.state = 1 AND (v.created_by ='"
		. $created_by . "' OR v.privacy = 1) AND v.id != '" . $eventid . "'";
		}
		else
		{
			$query = "SELECT  v.id,v.name,v.created_by,v.privacy,v.state FROM   #__jticketing_venues AS v
			WHERE NOT EXISTS(SELECT NULL FROM #__jticketing_events AS ed WHERE ed.venue = v.id AND(('"
		. $array_venue["start_dt_timestamp"] . "' BETWEEN UNIX_TIMESTAMP(ed.startdate) AND UNIX_TIMESTAMP(ed.enddate)) OR ('"
		. $array_venue["end_dt_timestamp"] . "' BETWEEN UNIX_TIMESTAMP(ed.startdate) AND UNIX_TIMESTAMP(ed.enddate)) OR ('"
		. $array_venue["start_dt_timestamp"] . "' <= UNIX_TIMESTAMP(ed.startdate) AND '"
		. $array_venue["end_dt_timestamp"] . "' >= UNIX_TIMESTAMP(ed.enddate))
		 )) AND v.online = " . $array_venue['event_online'] . " AND v.state = 1 AND (v.created_by ='" . $created_by . "' OR v.privacy = 1)";
		}

		$db->setQuery($query);
		$events = $db->loadObjectList();

		return $events;
	}

	/**
	 * Method to get venue list depending on the vendor chosen.
	 *
	 * @param   string  $eventData  event data
	 *
	 * @return   array result
	 *
	 * @since    1.6
	 */
	public function getVenueList($eventData)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('venue');
		$query->from('#__jticketing_events');
		$query->where($db->quoteName('startdate') . ' BETWEEN ' . $db->quote($eventData['eventStartDate']));
		$query->where($db->quote($eventData['eventEndDate']) . 'OR' . $db->quoteName('enddate') . ' BETWEEN ' . $db->quote($eventData['eventStartDate']));
		$query->where($db->quote($eventData['eventEndDate']));
		$db->setQuery($query);
		$eventOnDate = $db->loadAssocList();
		$query = $db->getQuery(true);
			$query->select('id,name');
			$query->from('#__jticketing_venues');

		foreach ($eventOnDate as $event)
		{
			$query->where($db->quoteName('id') . ' != ' . $db->quote($event['venue']));
		}

		$query->where($db->quoteName('online') . ' = ' . $db->quote($eventData['radioValue']));
		$query->where($db->quoteName('privacy') . ' != 0');
		$query->where($db->quoteName('state') . ' = 1');
		$db->setQuery($query);
		$venuesAvailable = $db->loadAssocList();

		if ($eventData['enforceVendor'] == 1)
		{
			$privateVenues = $this->getPrivateVenues($eventData['vendor_id'], $eventData['radioValue']);
		}
		else
		{
			$vendorId = $this->getVendorId($eventData['created_by'], $eventData['radioValue']);
			$privateVenues = $this->getPrivateVenues($vendorId, $eventData['radioValue']);
		}

		$Available = $privateVenues + $privateVenues;

		foreach ($privateVenues as $venue)
		{
			$venuesAvailable[] = array("id" => $venue['id'], "name" => $venue['name']);
		}

		if ($eventData['radioValue'] == 0)
		{
			$venuesAvailable[] = array("id" => "0", "name" => JText::_('COM_JTICKETING_CUSTOM_LOCATION'));
		}

		$options = array();

		foreach ($venuesAvailable as $venue)
		{
			$options[] = JHtml::_('select.option', $venue['id'], $venue['name']);
		}

		return $options;
	}

	/**
	 * Method to get private venues be it online or offline of the vendor.
	 *
	 * @param   integer  $vendor_id  vendor id
	 *
	 * @param   integer  $venueType  type of venue online or offline
	 *
	 * @return   array result
	 *
	 * @since    1.6
	 */
	public function getPrivateVenues($vendor_id, $venueType)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id,name');
		$query->from('#__jticketing_venues');
		$query->where($db->quoteName('vendor_id') . ' = ' . $db->quote($vendor_id));
		$query->where($db->quoteName('online') . ' = ' . $db->quote($venueType));
		$query->where($db->quoteName('privacy') . ' = 0 ');
		$db->setQuery($query);
		$venues = $db->loadAssocList();

		return $venues;
	}

	/**
	 * Method to get Vendor id based on the venue typ[e chosen
	 *
	 * @param   integer  $created_by  user's id
	 *
	 * @param   integer  $venueType   type of venue online or offline
	 *
	 * @return   array result
	 *
	 * @since    1.6
	 */
	public function getVendorId($created_by, $venueType)
	{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName('vendor_id'));
			$query->from('#__tjvendors_vendors');
			$query->where($db->quoteName('user_id') . ' = ' . $db->quote($created_by));
			$db->setQuery($query);
			$vendor_id = $db->loadResult();

			return $vendor_id;
	}

	/**
	 * Method to get edit venue
	 *
	 * Method to create online event
	 *
	 * @param   array  $data  data
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function createOnlineEvent($data)
	{
		$app = JFactory::getApplication();
		$ticket = $data['tickettypes'];

		if ($ticket['tickettypes0']['unlimited_seats'] == 1)
		{
			$ticketCount = 'unlimited';
		}
		else
		{
			$ticketCount = array_sum($ticket['tickettypes0']['available']);
		}

		$date = new DateTime($data['beginDate']);
		$data['beginDate'] = $date->format('Y-m-d h:i:s');

		$date = new DateTime($data['onlineEndDate']);
		$data['onlineEndDate'] = $date->format('Y-m-d h:i:s');

		$startDateTime = new DateTime($data['beginDate']);
		$beginDate = $startDateTime->format(DateTime::ISO8601);

		$endDateTime = new DateTime($data['onlineEndDate']);
		$endDate = $endDateTime->format(DateTime::ISO8601);

		$venueId = $data['venue'];
		$Name = $data['title'];

		// Load AnnotationForm Model
		$model = JModelLegacy::getInstance('Venue', 'JticketingModel');
		$licenceContent = $model->getItem($venueId);
		$licence = (object) $licenceContent->params;
		$jticketingmainhelper = new jticketingmainhelper;
		$password = $jticketingmainhelper->rand_str(8);
		$userid = $data['created_by'];
		$online_provider = ltrim($licenceContent->online_provider, "plug_tjevents_");
		$online_provider = ucfirst($online_provider);

		if (empty($userid))
		{
			$userDetail = JFactory::getUser();
		}
		else
		{
			$userDetail = JFactory::getUser($userid);
		}

		// TRIGGER After create event
		if (!empty($licence))
		{
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('tjevents');

			if ($data['id'] == 0 && $data['venuechoice'] = "new")
			{
				if ($licence->event_type == 'meeting')
				{
					$result = $dispatcher->trigger('create' . $online_provider . 'Meeting', array
					($licence, $Name, $userDetail, $beginDate, $endDate, $ticketCount, $password)
					);
				}
				elseif ($licence->event_type == 'seminar')
				{
					$result = $dispatcher->trigger('create' . $online_provider . 'Seminar', array
					($licence, $Name, $userDetail, $beginDate, $endDate, $ticketCount, $password)
					);
					$res = $result['0'];
					$data['jt_params']['event_source_sco_id'] = $res['source_sco_id'];
				}

				$res = $result['0'];

				if ($res['error_message'])
				{
					$this->setError($res['error_message']);

					return false;
				}
				else
				{
					$data['jt_params']['event_url'] = $res['meeting_url'];
					$data['jt_params']['event_sco_id'] = $res['sco_id'];
					$data['jt_params'] = json_encode($data['jt_params']);

					return $data['jt_params'];
				}
			}
			else
			{
				$eventData = $this->getItem($data['id']);
				$params = json_decode($eventData->jt_params);
				$event_sco_id = $params->event_sco_id;
				$event_url = $params->event_url;

				if ($licence->event_type == 'meeting')
				{
					$result = $dispatcher->trigger('update' . $online_provider . 'Meeting', array
					($licence, $Name, $event_sco_id, $beginDate, $endDate, $event_url,$userDetail)
					);
				}
				elseif ($licence->event_type == 'seminar')
				{
					$result = $dispatcher->trigger('update' . $online_provider . 'Seminar', array
					($licence, $Name, $params, $beginDate, $endDate, $ticketCount, $userDetail)
					);
				}

				$res = $result['0'];

				if ($res['error_message'])
				{
					$this->setError($res['error_message']);

					return false;
				}
				else
				{
					return $res;
				}
			}
		}

		return false;
	}

	/**
	 * Method toget required data
	 *
	 * @param   integer  $column         table volumn
	 *
	 * @param   integer  $tableName      table name
	 *
	 * @param   integer  $condition      conditions
	 *
	 * @param   integer  $integrationId  integration id
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	public function getRequiredData($column, $tableName, $condition, $integrationId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName($column));
		$query->from($db->quoteName($tableName));
		$query->where($db->quoteName($condition) . ' = ' . $db->quote($integrationId));
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Method toget integration Id
	 *
	 * @param   integer  $event_id  event id
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	public function getIntegrationId($event_id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__jticketing_integration_xref'));
		$query->where($db->quoteName('eventid') . ' = ' . $db->quote($event_id));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Method toget category name
	 *
	 * @param   integer  &$id  category id
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	public function delete(&$id)
	{
		$data = $this->getItem($id);

		if ($data->online_events)
		{
			$params = json_decode($data->jt_params);
			$event_sco_id = $params->event_sco_id;

			$venueId = $data->venue;
			$model = JModelLegacy::getInstance('Venue', 'JticketingModel');
			$licenceContent = $model->getItem($venueId);
			$licence = (object) $licenceContent->params;
			$online_provider = ltrim($licenceContent->online_provider, "plug_tjevents_");
			$online_provider = ucfirst($online_provider);
			$userid = $data->created_by;

			if (empty($userid))
			{
				$userDetail = JFactory::getUser();
			}
			else
			{
				$userDetail = JFactory::getUser($userid);
			}

			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('tjevents');

			if (!empty($licence))
			{
				$result = $dispatcher->trigger('delete' . $online_provider . 'Meeting', array
				($licence, $event_sco_id, $userDetail)
				);

				if ($result['0'])
				{
					return parent::delete($id);
				}
			}
		}
		else
		{
			$integrationId = $this->getIntegrationId($data->id);
			$db = JFactory::getDbo();

			// Create a new query object.
			$query = $db->getQuery(true);
			$ticketIds = $this->getRequiredData('id', '#__jticketing_types', 'eventid', $integrationId);

			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'Tickettypes');
			$JTicketingModelTickettypes = JModelLegacy::getInstance('Tickettypes', 'JTicketingModel');

			foreach ($ticketIds as $key => $ticketId)
			{
				$JTicketingModelTickettypes->delete($ticketId->id);
			}

			$attendeeIds = $this->getRequiredData('id', '#__jticketing_attendee_fields', 'eventid', $integrationId);
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'attendeefields');
			$JTicketingModelAttendeefields = JModelLegacy::getInstance('Attendeefields', 'JTicketingModel');

			foreach ($attendeeIds as $key => $attendeeId)
			{
				$JTicketingModelAttendeefields->delete($attendeeId->id);
			}

			$orderIds = $this->getRequiredData('id', '#__jticketing_order', 'event_details_id', $integrationId);
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'order');
			$JticketingModelOrder = JModelLegacy::getInstance('order', 'JticketingModel');
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'orderItem');
			$JticketingModelOrderItem = JModelLegacy::getInstance('orderItem', 'JticketingModel');

			foreach ($orderIds as $key => $orderId)
			{
				$orderItems = $JticketingModelOrderItem->getOrderItems($orderId->id);

				foreach ($orderItems as $orderItem)
				{
					$JticketingModelOrderItem->delete($orderItem->id);
				}

				$JticketingModelOrder->delete($orderId->id);
			}

			$mediaIds = $this->getRequiredData('media_id', '#__media_files_xref', 'client_id', $integrationId);
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'media');
			$JticketingModelMedia = JModelLegacy::getInstance('media', 'JticketingModel');

			foreach ($mediaIds as $key => $mediaId)
			{
				$JticketingModelMedia->delete($mediaId->media_id);
			}

			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'integrationxref');
			$JticketingModelIntegrationxref = JModelLegacy::getInstance('integrationxref', 'JticketingModel');
			$JticketingModelIntegrationxref->delete($integrationId);
		}

		return parent::delete($id);
	}

	/**
	 * Method to call media save function
	 *
	 * @param   INT  $mediaGallery  mediaGallery
	 *
	 * @param   INT  $isGallery     isGallery
	 *
	 * @param   INT  $eventId       eventId
	 *
	 * @return   array result
	 *
	 * @since    2.0
	 */
	public function saveMedia($mediaGallery, $isGallery, $eventId)
	{
		$modelMediaXref = JModelLegacy::getInstance('MediaXref', 'JTicketingModel');

		if (!is_array($mediaGallery))
		{
			$mediaGallery = (array) $mediaGallery;
		}

		foreach ($mediaGallery as $mediaId)
		{
			if ($mediaId)
			{
				$mediaXref = array();
				$mediaXref['id'] = '';
				$mediaXref['client_id'] = $eventId;
				$mediaXref['media_id'] = $mediaId;
				$mediaXref['is_gallery'] = $isGallery;
				$mediaXref['client'] = 'com_jticketing.event';
				$modelMediaXref->save($mediaXref);
			}
		}

		return true;
	}
}
