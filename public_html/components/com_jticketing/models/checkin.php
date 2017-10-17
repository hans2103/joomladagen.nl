<?php
/**
 * @version    SVN: <svn_id>
 * @package    Com_Jticketing
 * @copyright  Copyright (C) 2005 - 2017. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Shika is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access.
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Methods supporting a jticketing checkin.
 *
 * @since  2.0.0
 */
class JticketingModelCheckin extends JModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return	JTable	A database object
	 *
	 * @since	2.0
	 */
	public function getTable($type = 'Checkin', $prefix = 'JticketingTable', $config = array())
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
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return    JForm    A JForm object on success, false on failure
	 *
	 * @since    2.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.checkin', 'checkin', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  The user id on success, false on failure.
	 *
	 * @since  2.0
	 */
	public function save($data)
	{
		if (empty($data['orderItemId']))
		{
			return false;
		}

		$orderItemId = $data['orderItemId'];
		$state = isset($data['state']) ? $data['state'] : 0;
		$event = isset($data['event_obj']) ? $data['event_obj'] : '';

		if (empty($event) && isset($data['eventid']))
		{
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
			$eventModel = JModelLegacy::getInstance('EventForm', 'JticketingModel');
			$event = $eventModel->getItem($data['eventid']);
		}

		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models');
		$modelOrderItem = JModelLegacy::getInstance('OrderItem', 'JticketingModel');
		$orderItemData = $modelOrderItem->getItem($orderItemId);

		if ($orderItemData->status != 'C')
		{
			return;
		}

		$tableCheckin = $this->getTable("Checkin", "JTicketingTable");
		$tableCheckin->load(array('ticketid' => (int) $orderItemId));

		$checkinData = array();

		// Here orderItemId consider as unique ticketid in checkin details
		$checkinData['ticketid']     = $orderItemId;
		$checkinData['checkintime']  = isset($event->checkintime) ? $event->checkintime : '';
		$checkinData['checkouttime'] = isset($event->checkouttime) ? $event->checkouttime : '';
		$checkinData['spend_time']   = isset($event->spend_time) ? $event->spend_time : '';

		if (empty($checkinData['spend_time']))
		{
			$checkinData['spend_time'] = strtotime($checkinData['checkintime']) + strtotime($checkinData['checkouttime']);
		}

		$checkinData['checkin']      = $state;
		$checkinData['eventid']      = $orderItemData->event_details_id;
		$checkinData['attendee_id']  = $orderItemData->user_id;
		$checkinData['attendee_email'] = !empty($orderItemData->email) ? $orderItemData->email : JFactory::getUser($orderItemData->user_id)->email;
		$checkinData['attendee_name']  = !empty($orderItemData->name) ? $orderItemData->name : JFactory::getUser($orderItemData->user_id)->name;
		$result = $this->getTable();
		$result->load(array('ticketid' => (int) $orderItemId));
		$checkinData['id'] = $result->id;

		if (!parent::save($checkinData))
		{
			return false;
		}

		// Now UPDATE INTEGRATIONXREF TABLE FOR CHECKIN COUNT
		$this->checkinIntegration($state, $orderItemData);

		// Added plugin trigger tobe executed after check in done
		JPluginHelper::importPlugin('system');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterEventCheckin', array($checkinData));

		return $checkinData;
	}

	/**
	 * update jticketing checkin integration
	 *
	 * @param   INT     $state          publish/unpublish
	 * @param   OBJECT  $orderItemData  orderItemData
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function checkinIntegration($state, $orderItemData)
	{
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$jticketingmainhelper = new jticketingmainhelper;

		if ($orderItemData)
		{
			$query = $db->getQuery(true);

			if ($state == 1)
			{
				$fields = array($db->quoteName('checkin') . ' = checkin+1');
				$conditions = array($db->quoteName('id') . ' = ' . (int) $orderItemData->event_details_id);
			}
			else
			{
				$fields = array($db->quoteName('checkin') . ' = checkin-1');
				$conditions = array($db->quoteName('checkin') . '> 0',
					$db->quoteName('id') . ' = ' . (int) $orderItemData->event_details_id);
			}

			$query->update($db->quoteName('#__jticketing_integration_xref'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();
		}

		if ($state == 1)
		{
			$com_params           = JComponentHelper::getParams('com_jticketing');
			$socialintegration    = $com_params->get('integrate_with', 'none');
			$streamCheckinTicket  = $com_params->get('streamCheckinTicket', 0);

			$actor_id = isset($orderItemData->user_id) ? $orderItemData->user_id : $user->id;
			$jteventHelper = new jteventHelper;

			if ($socialintegration != 'none' && !empty($orderItemData->user_id))
			{
				// Add in activity.
				if ($streamCheckinTicket == 1)
				{
					$libclass    = $jteventHelper->getJticketSocialLibObj();
					$action      = 'streamCheckinTicket';
					$orderinfo   = $jticketingmainhelper->getorderinfo($orderItemData->id);
					$eventLink   = '<a class="" href="' . $orderinfo['eventinfo']->event_url . '">' . $orderinfo['eventinfo']->summary . '</a>';
					$collect_attendee_info = $com_params->get('collect_attendee_info_checkout', '0');

					if ($orderItemData->attendee_id and $collect_attendee_info)
					{
						$field_array = array(
							'first_name',
							'last_name',
						);

						// Get Attendee Details
						$attendee_details = $jticketingmainhelper->getAttendees_details($orderItemData->attendee_id, $field_array);

						if (isset($attendee_details['first_name']))
						{
							$buyername = implode(" ", $attendee_details);
						}
						else
						{
							$db = JFactory::getDBO();

							// If collect attendee info is set  to no in backend then take first and last name from billing info.
							if ($result->id)
							{
								$query = $db->getQuery(true);
								$query->select(array('firstname', 'lastname'));
								$query->from($db->quoteName('#__jticketing_users'));
								$query->where($db->quoteName('order_id') . '=' . (int) $orderItemData->id);
								$db->setQuery($query);
								$attname = $db->loadObject();
								$buyername = $attname->firstname . ' ' . $attname->lastname;
							}
						}

						$originalMsg = JText::sprintf('COM_JTICKETING_CHECKIN_SUCCESS_ACT_NAME', $buyername, $eventLink);
					}
					else
					{
						$originalMsg = JText::sprintf('COM_JTICKETING_CHECKIN_SUCCESS_ACT', $eventLink);
					}

					$libclass->pushActivity($actor_id, $act_type = '', $act_subtype = '', $originalMsg, $act_link = '', $title = '', $act_access = 0);
				}
			}
		}

		return 1;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 *
	 * @since	2.0
	 */
	public function getItem($pk = null)
	{
		if ($pk)
		{
			return $item = parent::getItem($pk);
		}

		return false;
	}

	/**
	 * Method to delete media record
	 *
	 * @param   string  &$mediaId  post data
	 *
	 * @return	JForm	A JForm object on success, false on failure
	 *
	 * @since   2.0
	 */
	public function delete(&$mediaId)
	{
		if (parent::delete($mediaId))
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to get checkin details from order items id
	 *
	 * @param   int  $ticketid  order items id jticketin_order_items table
	 * @param   int  $eventid   eventid        eventid
	 *
	 * @return  void
	 */
	public function getCheckinStatus($ticketid, $eventid = '')
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('checkin');
		$query->from('#__jticketing_checkindetails');
		$query->where($db->quoteName('ticketid') . ' = ' . $db->quote($ticketid));
		$db->setQuery($query);
		$eventOnDate = $db->loadResult();

		if (!empty($eventOnDate))
		{
			return 1;
		}

		return 0;
	}
}
