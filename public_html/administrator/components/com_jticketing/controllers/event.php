<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticketing
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Jticketing is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
// No direct access to this file
defined('_JEXEC') or die;

// Import helper for date and time format
$helperPath = JPATH_SITE . '/components/com_jticketing/helpers/time.php';
jimport('joomla.filesystem.file');

if (!class_exists('JticketingTimeHelper'))
{
	JLoader::register('JticketingTimeHelper', $helperPath);
	JLoader::load('JticketingTimeHelper');
}

/**
 * com_jticketing Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_helloworld
 * @since       0.0.9
 */
class JTicketingControllerEvent extends JControllerForm
{
	/**
	 * Method to save a user's profile data.
	 *
	 * @param   string  $key     TO ADD
	 * @param   string  $urlVar  TO ADD
	 *
	 * @return    void
	 *
	 * @since    1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app   = JFactory::getApplication();
		$model = $this->getModel('Event', 'JticketingModel');

		// Get the user data.
		$data = JFactory::getApplication()->input->get('jform', array(), 'array');

		if (empty($data['created_by']))
		{
			$data['created_by'] = JFactory::getUser()->id;
		}

		// Get formatted start time & end time for event.
		$formattedTime = JticketingTimeHelper::getFormattedDateTime($data);

		// Append formatted start time & end time for event to startdate & enddate.
		$data['startdate']  = $data['startdate'] . " " . $formattedTime['event_start_time'];
		$data['enddate']    = $data['enddate'] . " " . $formattedTime['event_end_time'];
		$data['booking_start_date'] = $data['booking_start_date'] . " " . $formattedTime['booking_start_time'];
		$data['booking_end_date']   = $data['booking_end_date'] . " " . $formattedTime['booking_end_time'];

		if ($data['online_events'] == '1')
		{
			$beginDate = $data['startdate'];
			$endDate = $data['enddate'];
		}

		// JForm tweak - Save all jform array data in a new array for later reference.
		$all_jform_data = $data;
		$start_dt_timestamp = strtotime($data['startdate']);
		$end_dt_timestamp = strtotime($data['enddate']);
		$booking_start_dt_timestamp = strtotime($data['booking_start_date']);
		$booking_end_dt_timestamp = strtotime($data['booking_end_date']);

		// Jform tweak - Get all posted data.
		$post = JFactory::getApplication()->input->post;

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
			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

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
			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

			return false;
		}

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		if ($data['id'] && $data['venue'])
		{
			$data['location'] = '';
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
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
			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

			return false;
		}

		$data['userName'] = JFactory::getUser($data['created_by'])->name;

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
				$this->setRedirect(JRoute::_('index.php?option=com_jticketing&view=event&layout=edit&id=' . $id, false));

				return false;
			}
		}

		/* Attempt to save the data.
		$return = $model->save($data);
		Tweaked. */

		if (!empty($data['id']))
		{
			$data['extra_jform_data'] = $extra_jform_data;
		}

		$data['beginDate'] = $beginDate;
		$data['onlineEndDate'] = $endDate;

		$return = $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			/* Save the data in the session.
			$app->setUserState('com_jticketing.edit.event.data', $data);
			Tweak.*/
			$app->setUserState('com_jticketing.edit.event.data', $all_jform_data);

			/* Tweak *important.
			$app->setUserState('com_jticketing.edit.event.id', $all_jform_data['id']);*/

			// Redirect back to the edit screen.
			$id = (int) $app->getUserState('com_jticketing.edit.event.id');
			$this->setMessage(JText::sprintf('COM_JTICKETING_EVENT_ERROR_MSG_SAVE', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_jticketing&&view=event&layout=edit&id=' . $id, false));

			return false;
		}

		$msg      = JText::_('COM_JTICKETING_MSG_SUCCESS_SAVE_EVENT');
		$input = JFactory::getApplication()->input;
		$id = $input->get('id');

		if (empty($id))
		{
			$id = $return;
		}

		$task = $input->get('task');

		if ($task == 'apply')
		{
			$redirect = JRoute::_('index.php?option=com_jticketing&&view=event&layout=edit&id=' . $id, false);
			$app->redirect($redirect, $msg);
		}

		if ($task == 'save2new')
		{
			$redirect = JRoute::_('index.php?option=com_jticketing&&view=event&layout=edit', false);
			$app->redirect($redirect, $msg);
		}

		// Clear the profile id from the session.
		$app->setUserState('com_jticketing.edit.event.id', null);

		// Check in the profile.
		if ($return)
		{
			$model->checkin($return);
		}

		// Redirect to the list screen.
		$redirect = JRoute::_('index.php?option=com_jticketing&view=events', false);
		$app->redirect($redirect, $msg);

		// Flush the data from the session.
		$app->setUserState('com_jticketing.edit.event.data', null);
	}
}
