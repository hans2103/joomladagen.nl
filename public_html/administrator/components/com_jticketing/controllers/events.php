<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Com_Jticketing
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * Jticketing is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');

/**
 * Events list controller class.
 *
 * @since  1.5
 */
class JticketingControllerEvents extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModel
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'event', $prefix = 'JticketingModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Function used to get the formaated time
	 *
	 * @param   ARRAY  $data  Post data
	 *
	 * @return  string  $formattedTime  Final formatted time
	 *
	 * @since  1.0.0
	 */
	private function getFormattedTime($data)
	{
		// Start Date/Time
		$event_start_date      = explode(' ', $data->startdate);
		$event_start_time      = explode(':', $event_start_date[1]);
		$event_start_time_hour = $event_start_time[0];
		$event_start_time_min  = $event_start_time[1];

		// Convert hours into 12 hour format.
		if ($event_start_time_hour > 12)
		{
			$starthours = $event_start_time_hour - 12;
			$start_hour = $starthours;
			$starthours = $starthours . ":" . $event_start_time_min . ":00";
			$event_start_time_ampm = "pm";

			// $starthours = $starthours .":".$event_start_time_min. ":00 pm";
		}
		else
		{
			$start_hour = $event_start_time_hour;
			$starthours = $event_start_date[1];
			$event_start_time_ampm = "am";

			// $starthours = $event_start_date[1] . " am";
		}

		// End Date/Time
		$event_end_date      = explode(' ', $data->enddate);
		$event_end_time      = explode(':', $event_end_date[1]);
		$event_end_time_hour = $event_end_time[0];
		$event_end_time_min  = $event_end_time[1];

		// Convert hours into 12 hour format.
		if ($event_end_time_hour > 12)
		{
			$endhours = $event_end_time_hour - 12;
			$end_hour = $endhours;
			$endhours = $endhours . ":" . $event_end_time_min . ":00";

			$event_end_time_ampm = "pm";

			// $endhours = $endhours .":".$event_end_time_min. ":00 pm";
		}
		else
		{
			$endhours = $event_end_date[1];
			$end_hour = $event_end_time_hour;
			$event_end_time_ampm = "am";

			// $endhours = $event_end_date[1] . " am";
		}

		$formattedTime = array();

		// Set return values.
		$formattedTime['event_start_date']       = $event_start_date[0];
		$formattedTime['event_start_time']       = $starthours;
		$formattedTime['event_start_time_hours'] = $start_hour;
		$formattedTime['event_start_time_min']   = $event_start_time_min;
		$formattedTime['event_start_time_ampm']  = $event_start_time_ampm;
		$formattedTime['event_end_date']         = $event_end_date[0];
		$formattedTime['event_end_time']         = $endhours;
		$formattedTime['event_end_time_hours']   = $end_hour;
		$formattedTime['event_end_time_min']     = $event_end_time_min;
		$formattedTime['event_end_time_ampm']    = $event_end_time_ampm;

		return $formattedTime;
	}

	/**
	 * Method to csv Import
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function csvImport()
	{
		jimport('joomla.html.html');
		jimport('joomla.filesystem.file');

		$mainframe = JFactory::getApplication();

		// $rs1 = @mkdir(JFactory::getApplication()->getCfg('tmp_path') . '/', 0777);

		// Start file heandling functionality *
		$fname       = $_FILES['csvfile']['name'];
		$rowNum      = 0;
		$uploads_dir = JFactory::getApplication()->getCfg('tmp_path') . '/' . $fname;
		JFile::upload($_FILES['csvfile']['tmp_name'], $uploads_dir);

		if ($file = fopen($uploads_dir, "r"))
		{
			$ext = JFile::getExt($uploads_dir);

			if ($ext != 'csv')
			{
				$msg = JText::_('NOT_CSV_MSG');
				$mainframe->redirect(JRoute::_('index.php?option=com_jticketing&view=catimpexp', false), "<b>" . $msg . "</b>");

				return;
			}

			while (($data = fgetcsv($file)) !== false)
			{
				if ($rowNum == 0)
				{
					// Parsing the CSV header
					$headers = array();

					foreach ($data as $d)
					{
						$headers[] = $d;
					}
				}
				else
				{
					// Parsing the data rows
					$rowData = array();

					foreach ($data as $d)
					{
						$rowData[] = $d;
					}

					$eventData[] = array_combine($headers, $rowData);
				}

				$rowNum++;
			}

			fclose($file);
		}
		else
		{
			// $msg = JText::_('File not open');
			$application = JFactory::getApplication();
			$application->enqueueMessage(JText::_('COM_JTICKETING_SOME_ERROR_OCCURRED'), 'error');
			$mainframe->redirect(JRoute::_('index.php?option=com_jticketing&view=events', false));

			return;
		}

		$output['return'] = 1;
		$output['successmsg'] = '';
		$output['errormsg'] = '';

		if (!empty($eventData))
		{
			$location = $booking_end_date = $booking_start_date = $enddate = $startdate = $emptyFile = 0;
			$catidx = $titlex = $idnotfound = $catidnotfound = $sucess = $venueChoice = $miss_col = 0;
			$totalEvents = count($eventData);

			foreach ($eventData as $eachEvent)
			{
				foreach ($eachEvent as $key => $value)
				{
					if (!array_key_exists('Title', $eachEvent) || !array_key_exists('Long Description', $eachEvent)
						|| !array_key_exists('CategoryId', $eachEvent) || !array_key_exists('Start Date', $eachEvent)
						|| !array_key_exists('End Date', $eachEvent))
					{
						$miss_col = 1;
						break;
					}

					switch ($key)
					{
						case 'Id' :
							$data['id'] = 0;

							if (!empty ($value))
							{
								$data['id'] = $value;
							}

						break;

						case 'State' :
							$data['state'] = 0;

							if (!empty($value))
							{
								$data['state'] = $value;
							}

						case 'Venue' :
							$data['venue'] = $value;
						break;

						case 'Title' :
							$data['title'] = $value;
						break;

						case 'CategoryId' :
							$data['catid'] = $value;
						break;

						case 'Short Description' :
							$data['short_description'] = $value;
						break;

						case 'Long Description' :
							$data['long_description'] = $value;
						break;

						case 'Start Date' :
							// $data['startdate'] = date('Y-m-d H:i:s', strtotime($value));
							$startdate1        = $value;

							$startdate_format  = str_replace('/', '-', $value);
							$data['startdate'] = date('Y-m-d H:i:s', strtotime($startdate_format));

							// $startDateO = DateTime::createFromFormat('Y-m-d H:i:s', $data['startdate']);
						break;

						case 'End Date' :
							// $data['enddate'] = date('Y-m-d H:i:s', strtotime($value));
							$enddate1        = $value;

							$enddate_format  = str_replace('/', '-', $value);
							$data['enddate'] = date('Y-m-d H:i:s', strtotime($enddate_format));
						break;

						case 'Access' :
							$data['access'] = $value;
						break;

						case 'Booking Start Date' :
							// $data['booking_start_date'] = date('Y-m-d H:i:s', strtotime($value));
							$booking_start_date1        = $value;

							$booking_start_dateformat  = str_replace('/', '-', $value);
							$data['booking_start_date'] = date('Y-m-d', strtotime($booking_start_dateformat));
						break;

						case 'Booking End Date' :
							// $data['booking_end_date'] = date('Y-m-d H:i:s', strtotime($value));
							$booking_end_date1        = $value;

							$booking_end_date_format  = str_replace('/', '-', $value);
							$data['booking_end_date'] = date('Y-m-d', strtotime($booking_end_date_format));
						break;

						case 'Location' :
							$data['location'] = $value;
						break;

						case 'Online Event' :
							$data['online_events'] = $value;
						break;

						case 'Image' :
							$data['image']['new_image'] = $value;
						break;

						case 'Gallery File' :
							$data['gallery_file']['media']['0'] = $value;
						break;

						case 'Venue Choice' :
							$data['venuechoice'] = $value;
						break;

						default :
						// All other fields would be treated as 'event fields' in field manager of jticketing
						$field_name = $this->checkEventField($key);

						if (!empty($field_name))
						{
							$data['extra_jform_data'] = $value;
						}

						break;
					}
				}

				if ($startdate1 != date('Y-m-d H:i:s', strtotime($data['startdate'])))
				{
					$startdate ++;
				}

				if ($enddate1 != date('Y-m-d H:i:s', strtotime($data['enddate'])))
				{
					$enddate ++;
				}

				if ($booking_start_date1 != date('Y-m-d', strtotime($data['booking_start_date'])))
				{
					$booking_start_date ++;
				}

				if ($booking_end_date1 != date('Y-m-d', strtotime($data['booking_end_date'])))
				{
					$booking_end_date ++;
				}

				$checkId = $this->getValidateId($data['id']);

				if ($checkId == 'notExistId')
				{
					$idnotfound ++;
				}

				$catidE = $this->categoryexit($data['catid']);

				if ($catidE == 'notExistCatId')
				{
					$catidnotfound ++;
				}

				if ($data['online_events'] == 1 and empty($data['venuechoice']))
				{
					$venueChoice ++;
				}

				if (is_numeric($data['catid']))
				{
					if ($data['location'] != '' or $data['venue'] != 0)
					{
						$data['created_by'] = JFactory::getUser()->id;
						$data['featured'] = 0;
					}
					else
					{
						$location ++;
					}
				}
				else
				{
					$catidx ++;
					break;
				}

				$ticketId = $this->checkTicket($data['id']);
				$output = array();
				$output['title']               = 'free';
				$output['id']                  = $ticketId;
				$output['desc']                = '';
				$output['state']               = '1';
				$output['price']               = '0';
				$output['access']              = '1';
				$output['unlimited_seats']     = '1';
				$output['available']           = '0';

				$data['tickettypes']['tickettypes0']  = $output;

				$model = $this->getModel('event');

				if ($data['title'] != '')
				{
					if ($model->save($data))
					{
						$sucess ++;
					}
					else
					{
						$badData ++;
					}
				}
			}
		}
		else
		{
			$emptyFile ++;
		}

		if ($emptyFile == 1)
		{
			$output['errormsg'] = JText::sprintf('COM_JTICKETING_IMPORT_BLANK_FILE');
		}
		else
		{
			if ($miss_col)
			{
				$output['successmsg'] = "";
				$output['errormsg'] = JText::_('COM_JTICKETING_CSV_IMPORT_COLUMN_MISSING');
			}
			else
			{
				$output['successmsg'] = JText::sprintf('COM_JTICKETING_EVENTS_IMPORT_TOTAL_ROWS_CNT_MSG', $totalEvents) . "<br />";

				if ($sucess > 0)
				{
					$output['successmsg'] .= JText::sprintf('COM_JTICKETING_EVENTS_IMPORT_NEW_EVENTS_MSG', $sucess) . "<br />";
				}

				if ($catidx > 0)
				{
					$output['errormsg'] .= JText::sprintf('COM_JTICKETING_EVENTS_IMPORT_CAT_EVENTS_MSG', $catidx) . "<br />";
				}

				if ($idnotfound > 0)
				{
					$output['errormsg'] .= JText::sprintf('COM_JTICKETING_EVENTS_IMPORT_ID_EVENTS_MSG', $idnotfound) . "<br />";
				}

				if ($startdate > 0)
				{
					$output['errormsg'] .= JText::sprintf('COM_JTICKETING_EVENTS_IMPORT_STARTDATE_EVENTS_MSG', $startdate) . "<br />";
				}

				if ($enddate > 0)
				{
					$output['errormsg'] .= JText::sprintf('COM_JTICKETING_EVENTS_IMPORT_ENDDATE_EVENTS_MSG', $enddate) . "<br />";
				}

				if ($booking_start_date > 0)
				{
					$output['errormsg'] .= JText::sprintf('COM_JTICKETING_EVENTS_IMPORT_BOOKING_STARTDATE_EVENTS_MSG', $booking_start_date) . "<br />";
				}

				if ($booking_end_date > 0)
				{
					$output['errormsg'] .= JText::sprintf('COM_JTICKETING_EVENTS_IMPORT_BOOKING_ENDDATE_EVENTS_MSG', $booking_end_date) . "<br />";
				}

				if ($location > 0)
				{
					$output['errormsg'] .= JText::sprintf('COM_JTICKETING_EVENTS_IMPORT_LOCATION_EVENTS_MSG', $location) . "<br />";
				}

				if ($venueChoice > 0)
				{
					$output['errormsg'] .= JText::sprintf('COM_JTICKETING_EVENTS_IMPORT_VENUE_CHOICE_EVENTS_MSG', $venueChoice) . "<br />";
				}
			}
		}

		if ($output['errormsg'])
		{
			$mainframe->enqueueMessage($output['errormsg'], 'error');
		}

		if ($output['successmsg'])
		{
			$mainframe->enqueueMessage($output['successmsg']);
		}

		$mainframe->redirect(JRoute::_('index.php?option=com_jticketing&view=events', false));

		return;
	}

	/**
	 * checkTicket.
	 *
	 * @param   integer  $id  event id
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function checkTicket($id)
	{
		$ticketId = 0;
		$db    = JFactory::getDBO();
		$query = "SELECT id FROM #__jticketing_integration_xref WHERE eventid ='{$id}'";
		$db->setQuery($query);
		$integration_xref = $db->loadResult();

		if ($integration_xref)
		{
			$query = "SELECT id FROM #__jticketing_types WHERE eventid ='{$integration_xref}'";
			$db->setQuery($query);
			$ticketId = $db->loadResult();
		}

		return $ticketId;
	}

	/**
	 * checkEventField.
	 *
	 * @param   integer  $label  label
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function checkEventField($label)
	{
		$ticketId = 0;
		$db    = JFactory::getDBO();
		$query = "SELECT name FROM #__tjfields_fields WHERE name  LIKE '{$label}'";
		$db->setQuery($query);

		return $field_name = $db->loadResult();
	}

	/**
	 * getValidateId.
	 *
	 * @param   integer  $id  event id
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function getValidateId($id)
	{
		$eventId = '';

		if ($id)
		{
			$db    = JFactory::getDBO();
			$query = "SELECT id FROM #__jticketing_events WHERE id ='{$id}'";
			$db->setQuery($query);
			$eventId = $db->loadResult();

			if ($eventId == '')
			{
				return 'notExistId';
			}
		}

		return $eventId;
	}

	/**
	 * categoryexit.
	 *
	 * @param   integer  $id  id
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function categoryexit($id)
	{
		$catId = '';

		if ($id)
		{
			$db    = JFactory::getDBO();
			$query = "SELECT id FROM #__categories WHERE id ='{$id}' AND extension = 'com_jticketing'";
			$db->setQuery($query);
			$catId = $db->loadResult();

			if ($catId == '')
			{
				return 'notExistCatId';
			}
		}

		return $catId;
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks   = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}

	/**
	 * Method to feature selected events.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function feature()
	{
		$input = JFactory::getApplication()->input;
		$cid   = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);
		$model        = $this->getModel('events');
		$successCount = $model->setItemFeatured($cid, 1);

		if ($successCount)
		{
			if ($successCount > 1)
			{
				$msg = JText::sprintf(JText::_('COM_JTICKETING_N_ITEMS_FEATURED'), $successCount);
			}
			else
			{
				$msg = JText::sprintf(JText::_('COM_JTICKETING_N_ITEMS_FEATURED_1'), $successCount);
			}
		}
		else
		{
			$msg = JText::_('COM_JTICKETING_FEATURED_ERROR') . '</br>' . $model->getError();
		}

		$redirect = JRoute::_('index.php?option=com_jticketing&view=events', false);

		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}

	/**
	 * Method to unfeature selected events.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function unfeature()
	{
		$input = JFactory::getApplication()->input;
		$cid   = $input->get('cid', '', 'array');
		JArrayHelper::toInteger($cid);
		$model        = $this->getModel('events');
		$successCount = $model->setItemFeatured($cid, 0);

		if ($successCount)
		{
			if ($successCount > 1)
			{
				$msg = JText::sprintf(JText::_('COM_JTICKETING_N_ITEMS_UNFEATURED'), $successCount);
			}
			else
			{
				$msg = JText::sprintf(JText::_('COM_JTICKETING_N_ITEMS_UNFEATURED_1'), $successCount);
			}
		}
		else
		{
			$msg = JText::_('COM_JTICKETING_UNFEATURED_ERROR') . '</br>' . $model->getError();
		}

		$redirect = JRoute::_('index.php?option=com_jticketing&view=events', false);

		$this->setMessage($msg);
		$this->setRedirect($redirect);
	}

	/**
	 * Function that allows child controller access to model data
	 * after the item has been deleted.
	 *
	 * @param   JModelLegacy  $model  The data model object.
	 * @param   integer       $ids    The validated data.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	protected function postDeleteHook(JModelLegacy $model, $ids = null)
	{
		$jteventHelperPath = JPATH_ROOT . DS . 'components' . DS . 'com_jticketing' . DS . 'helpers' . DS . 'event.php';

		if (!class_exists('jteventHelper'))
		{
			JLoader::register('jteventHelper', $jteventHelperPath);
			JLoader::load('jteventHelper');
		}

		$jticketingmainhelperPath = JPATH_ROOT . DS . 'components' . DS . 'com_jticketing' . DS . 'helpers' . DS . 'main.php';

		if (!class_exists('jticketingmainhelper'))
		{
			JLoader::register('jticketingmainhelper', $jticketingmainhelperPath);
			JLoader::load('jticketingmainhelper');
		}

		$jticketingmainhelper = new jticketingmainhelper;

		$jteventHelper = new jteventHelper;
		$integration_arr = array();

		// Firstly find integration id based on event id
		foreach ($ids AS $evid)
		{
			$integration_arr[] = $jticketingmainhelper->getEventrefid($evid);
		}

		/*Pass the integration id and delete event
			@TODO Snehal add validation for delete event
			$jteventHelper->delete_Event($integration_arr);
		*/
	}

	/**
	 * Method to delete the model state.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function delete()
	{
		$app = JFactory::getApplication();
		$input		= JFactory::getApplication()->input;
		$cid 		= $input->post->get('cid', array(), 'array');
		JArrayHelper::toInteger($cid);
		$modelEvent = JModelLegacy::getInstance('EventForm', 'JticketingModel');
		$validCount = 0;
		$count['valid'] = 0;
		$count['invalid'] = 0;
		$orderCount = 0;

		foreach ($cid as $id)
		{
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'Event');
			$JTicketingModelEvent = JModelLegacy::getInstance('Event', 'JTicketingModel');
			$eventData = $JTicketingModelEvent->getItem($id);
			$currentDate = JFactory::getDate();

			if ($eventData->enddate <= $currentDate)
			{
				$confirm = $JTicketingModelEvent->delete($id);

				if ($confirm == "true")
				{
					$count['valid'] = $count['valid'] + 1;
				}
			}
			else
			{
				$integrationId = $JTicketingModelEvent->getIntegrationId($id);
				$orderIds = $JTicketingModelEvent->getRequiredData('id', '#__jticketing_order', 'event_details_id', $integrationId);

				foreach ($orderIds as $key => $orderId)
				{
					$jticketingmainhelper = new jticketingmainhelper;
					$orderDetails = $jticketingmainhelper->getOrderDetail($orderId->id);

					if (!empty($orderDetails))
					{
						if ($orderDetails->status == "C")
						{
							$orderCount++;
						}
					}
				}

				if ($orderCount == 0)
				{
						$confirm = $JTicketingModelEvent->delete($id);

					if ($confirm == "true")
					{
						$count['valid'] = $count['valid'] + 1;
					}
				}
				else
				{
						$count['invalid'] = $count['invalid'] + 1;
				}
			}
		}

			if ($count['valid'] != 0)
			{
				if ($count['valid'] > 1)
				{
					$languageConstantValid = 'COM_JTICKETING_N_ITEMS_DELETED';
				}
				else
				{
					$languageConstantValid = 'COM_JTICKETING_N_ITEMS_DELETED_1';
				}

				$app->enqueueMessage($count['valid'] . JText::_($languageConstantValid));
			}

			if ($count['invalid'] != 0)
			{
				if ($count['invalid'] > 1)
				{
					$languageConstantInvalid = 'COM_JTICKETING_DELETED_ERROR_MULTIPLE';
				}
				else
				{
					$languageConstantInvalid = 'COM_JTICKETING_DELETED_ERROR_SINGLE';
				}

				$app->enqueueMessage($count['invalid'] . JText::_($languageConstantInvalid), 'error');
			}

		$redirect = JRoute::_('index.php?option=com_jticketing&view=events', false);
		$this->setRedirect($redirect);
	}
}
