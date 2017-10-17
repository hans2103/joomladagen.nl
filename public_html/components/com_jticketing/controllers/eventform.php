<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';
require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/models/venue.php';

$helperPath = JPATH_SITE . '/components/com_jticketing/helpers/time.php';

if (!class_exists('JticketingTimeHelper'))
{
	JLoader::register('JticketingTimeHelper', $helperPath);
	JLoader::load('JticketingTimeHelper');
}

/**
 * Event controller class.
 *
 * @since  1.6
 */
class JticketingControllerEventForm extends JticketingController
{
	/**
	 * Constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		parent::__construct();

		$this->JTRouteHelper = new JTRouteHelper;
	}

	/**
	 * Method to check out an item for editing and redirect to the edit form.
	 *
	 * @return  void
	 *
	 * @since	1.6
	 */
	public function edit()
	{
		$app = JFactory::getApplication();

		// Get the previous edit id (if any) and the current edit id.
		$previousId = (int) $app->getUserState('com_jticketing.edit.event.id');
		$editId	= JFactory::getApplication()->input->getInt('id', null, 'array');

		// Flush the data from the session.
		// Tweak - Manoj.
		$app->setUserState('com_jticketing.edit.event.data', null);

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_jticketing.edit.event.id', $editId);

		// Get the model.
		$model = $this->getModel('EventForm', 'JticketingModel');

		// Check out the item
		if ($editId)
		{
			$model->checkout($editId);
		}

		// Check in the previous user.
		if ($previousId)
		{
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=eventform&id=' . $editId, false));
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	public function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = JFactory::getApplication();
		$model = $this->getModel('EventForm', 'JticketingModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');

		// Get formatted start time & end time for event.
		$formattedTime = JticketingTimeHelper::getFormattedDateTime($data);

		// Append formatted start time & end time for event to startdate & enddate.
		$data['startdate'] = $data['startdate'] . " " . $formattedTime['event_start_time'];
		$data['enddate']   = $data['enddate'] . " " . $formattedTime['event_end_time'];
		$data['booking_start_date'] = $data['booking_start_date'] . " " . $formattedTime['booking_start_time'];
		$data['booking_end_date']   = $data['booking_end_date'] . " " . $formattedTime['booking_end_time'];

		if ($data['online_events'] == '1')
		{
			$beginDate = $data['startdate'];
			$endDate = $data['enddate'];
		}

		$all_jform_data = $data;

		$start_dt_timestamp = strtotime($data['startdate']);
		$end_dt_timestamp = strtotime($data['enddate']);
		$booking_start_dt_timestamp = strtotime($data['booking_start_date']);
		$booking_end_dt_timestamp = strtotime($data['booking_end_date']);

		// Validate if start-date-time <= end-date-time.
		if ($start_dt_timestamp > $end_dt_timestamp)
		{
			// Save the data in the session.
			// Tweak.
			$app->setUserState('com_jticketing.edit.event.data', $data);

			// Tweak *important
			$app->setUserState('com_jticketing.edit.event.id', $data['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.event.id');
			$this->setMessage(JText::_('COM_JTICKETING_EVENT_START_DATE_LESS_EVENT_END_DATE_ERROR'), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=eventform&id=' . $id, false));

			return false;
		}

		// Validate if start-date-time <= end-date-time.
		if ($booking_start_dt_timestamp > $booking_end_dt_timestamp)
		{
			// Save the data in the session.
			// Tweak.
			$app->setUserState('com_jticketing.edit.event.data', $data);

			// Tweak *important
			$app->setUserState('com_jticketing.edit.event.id', $data['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.event.id');
			$this->setMessage(JText::_('COM_JTICKETING_EVENT_START_DATE_LESS_EVENT_END_DATE_ERROR'), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=eventform&id=' . $id, false));

			return false;
		}

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		// Validate the posted data.
		if (!empty($form))
		{
			$data = $model->validate($form, $data);
		}

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session. Tweak
			$app->setUserState('com_jticketing.edit.event.data', $all_jform_data);

			// Tweak *important
			$app->setUserState('com_jticketing.edit.event.id', $all_jform_data['id']);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.event.id');
			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

			return false;
		}

		// Jform tweaking - get data for extra fields jform.
		$extra_jform_data = array_diff_key($all_jform_data, $data);

		// Check if form file is present.
		$filePath = JPATH_SITE . '/components/com_jticketing/models/forms/eventform_extra.xml';

		if (JFile::exists($filePath))
		{
			// Validate the posted data.
			$formExtra = $model->getFormExtra(
						array("category" => $data['catid'],
							"clientComponent" => 'com_jticketing',
							"client" => 'com_jticketing.event',
							"view" => 'event',
							"layout" => 'edit')
							);

			if (!$formExtra)
			{
				JError::raiseWarning(500, $model->getError());

				return false;
			}

			$formExtra = array_filter($formExtra);

			if (!empty($formExtra))
			{
				if (!empty($formExtra[0]))
				{
					// Validate the posted extra data.
					$extra_jform_data = $model->validateExtra($formExtra[0], $extra_jform_data);
				}
				else
				{
					// Validate the posted extra data.
					$extra_jform_data = $model->validateExtra($formExtra[1], $extra_jform_data);
				}
			}

			// Check for errors.
			if ($extra_jform_data === false)
			{
				// Get the validation messages.
				$errors = $model->getErrors();

				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				// Save the data in the session.
				// Tweak.
				$app->setUserState('com_jticketing.edit.event.data', $all_jform_data);

				// Tweak *important
				$app->setUserState('com_jticketing.edit.event.id', $all_jform_data['id']);

				// Redirect back to the edit screen.
				$id = (int) $app->getUserState('com_jticketing.edit.event.id');
				$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=eventform&id=' . $id, false));

				return false;
			}
		}

		if (!empty($data['id']))
		{
			$data['extra_jform_data'] = $extra_jform_data;

			if ($data['venue'])
			{
				$data['location'] = '';
			}
		}

		$com_params    = JComponentHelper::getParams('com_jticketing');
		$enforceVendor = $com_params->get('enforce_vendor');

		if ($enforceVendor == 1)
		{
			$JticketingCommonHelper = new JticketingCommonHelper;
			$data['vendor_id'] = $JticketingCommonHelper->checkVendor();
		}

		$data['userName'] = JFactory::getUser($data['created_by'])->name;
		$data['beginDate'] = $beginDate;
		$data['onlineEndDate'] = $endDate;

		$return = $model->save($data);

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_jticketing.edit.event.id', null);

		// Redirect to the list screen.
		$link     = 'index.php?option=com_jticketing&view=events&layout=my';
		$redirect = $this->JTRouteHelper->JTRoute($link);
		$msg      = JText::_('COM_JTICKETING_MSG_SUCCESS_SAVE_EVENT');

		$this->setRedirect($redirect, $msg);

		// Flush the data from the session.
		$app->setUserState('com_jticketing.edit.event.data', null);
	}

	/**
	 * cancel a ad fields
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function cancel()
	{
		$app   = JFactory::getApplication();

		// Clear the profile id from the session.
		$app->setUserState('com_jticketing.edit.event.id', null);

		// Flush the data from the session.
		$app->setUserState('com_jticketing.edit.event.data', null);

		// Redirect to the list screen.
		$link     = 'index.php?option=com_jticketing&view=events&layout=my';
		$redirect = $this->JTRouteHelper->JTRoute($link);
		$msg      = JText::_('COM_JTICKETING_MSG_CANCEL_CREATE_EVENT');
		$this->setRedirect($redirect, $msg);
	}

	/**
	 * remove a ad fields
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function remove()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app = JFactory::getApplication();
		$model = $this->getModel('EventForm', 'JticketingModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_jticketing.edit.event.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.event.id');
			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

			return false;
		}

		// Attempt to save the data.
		$return	= $model->delete($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_jticketing.edit.event.data', $data);

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.event.id');
			$this->setMessage(JText::sprintf('Delete failed', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

			return false;
		}

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_jticketing.edit.event.id', null);

		// Redirect to the list screen.
		$this->setMessage(JText::_('COM_JTICKETING_ITEM_DELETED_SUCCESSFULLY'));
		$menu = & JSite::getMenu();
		$item = $menu->getActive();
		$this->setRedirect(JRoute::_($item->link, false));

		// Flush the data from the session.
		$app->setUserState('com_jticketing.edit.event.data', null);
	}

	/**
	 * Method to get edit venue
	 *
	 * Method to create online event
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function createSeminar()
	{
		$post = JFactory::getApplication()->input->post;
		$formData = new JRegistry($post->get('jform', '', 'array'));
		$unlimitedCount = $post->get('ticket_type_unlimited_seats', '', 'array');

		if ($unlimitedCount['0'] == 1)
		{
			$ticketCount = 'unlimited';
		}
		else
		{
			$ticketCount = array_sum($post->get('ticket_type_available', '', 'array'));
		}

		$venueId = $formData->get('venue');
		$Name = $formData->get('title');
		$startTime = $post->get('event_start_time_hour') . ':' . $post->get('event_start_time_min') . ' ' . $post->get('event_start_time_ampm');

		if ($post->get('event_start_time_min') != '00')
		{
			$startTimeFormated = date("H:i", strtotime($startTime));
		}
		else
		{
			$startTimeFormated = date("H:i", strtotime($post->get('event_start_time_hour') . ' ' . $post->get('event_start_time_ampm')));
		}

		$endTime = $post->get('event_end_time_hour') . ':' . $post->get('event_end_time_min') . ' ' . $post->get('event_end_time_ampm');

		if ($post->get('event_end_time_min') != '00')
		{
			$endTimeFormated = date("H:i", strtotime($endTime));
		}
		else
		{
			$endTimeFormated = date("H:i", strtotime($post->get('event_end_time_hour') . ' ' . $post->get('event_end_time_ampm')));
		}

		$beginDate = $formData->get('startdate') . 'T' . $startTimeFormated;
		$endDate = $formData->get('enddate') . 'T' . $endTimeFormated;
		$jticketingfrontendhelper = new jticketingfrontendhelper;

		// Load AnnotationForm Model
		$model = JModelLegacy::getInstance('Venue', 'JticketingModel');
		$licenceContent = $model->getItem($venueId);
		$licence = (object) $licenceContent->params;
		$jticketingmainhelper = new jticketingmainhelper;
		$password = $jticketingmainhelper->rand_str(8);
		$userid = $post->get('jform_created_by');

		if ($userid == 0)
		{
			$userDetail = JFactory::getUser();
		}
		elseif ($userid == -1)
		{
			$userDetail->id = 0;
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

			if ($licence->event_type == 'meeting')
			{
				$result = $dispatcher->trigger('createMeeting', array($licence, $Name, $userDetail, $beginDate, $endDate, $ticketCount, $password));
			}
			elseif ($licence->event_type == 'seminar')
			{
				$result = $dispatcher->trigger('createSeminar', array($licence, $Name, $userDetail, $beginDate, $endDate, $ticketCount, $password));
			}
		}

		echo json_encode($result['0']);

		jexit();
	}

	/**
	 * Method to get all existing events
	 *
	 * @return	void
	 *
	 * @since	1.6
	 */
	public function getAllMeetings()
	{
		$post = JFactory::getApplication()->input->post;
		$venueId = $post->get('venueId');

		// Load AnnotationForm Model
		$model = JModelLegacy::getInstance('Venue', 'JticketingModel');
		$licenceContent = $model->getItem($venueId);
		$licence = (object) $licenceContent->params;

		if (!empty($venueId))
		{
			// TRIGGER After create event
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('tjevents');
			$result = $dispatcher->trigger('getAllMeetings', array($licence));
			echo json_encode($result);
		}

		jexit();
	}
}
