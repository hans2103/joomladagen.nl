<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die(';)');

/**
 * Model for buy for creating order and other
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingModelAttendees extends JModelAdmin
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
	 * Method to get the record form.
	 *
	 * @param   string  $data      An optional array of data for the form to interogate.
	 * @param   string  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm   A JForm object on success, false on failure
	 *
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_jticketing.attendees', 'attendees', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Get an instance of JTable class
	 *
	 * @param   string  $type    Name of the JTable class to get an instance of.
	 * @param   string  $prefix  Prefix for the table class name. Optional.
	 * @param   array   $config  Array of configuration values for the JTable object. Optional.
	 *
	 * @return  JTable|bool JTable if success, false on failure.
	 */
	public function getTable($type = 'Attendees', $prefix = 'JticketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   data  $attendeeFields  TO  ADD
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function save($attendeeFields)
	{
		$userState = $this->getState('user_id');

		if ($userState)
		{
			$userID = $this->getState('user_id');
		}
		elseif (!empty($attendeeFields['0']['user_id']))
		{
			$userID = $attendeeFields['0']['user_id'];
		}
		else
		{
			$userID = JFactory::getUser()->id;
		}

		$attendeeId = '0';

		foreach ($attendeeFields as $attkey => $fields)
		{
			$attendee = array();
			$attendee['id'] = '';
			$attendee['owner_id'] = $userID;

			if (!empty($fields['attendee_id']))
			{
				$attendee['id'] = $fields['attendee_id'];
			}
			else
			{
				if (parent::save($attendee))
				{
					$attendeeId = (int) $this->getState($this->getName() . '.id');
				}
				else
				{
					return false;
				}
			}

			$attendeeId = isset($attendee['id']) ? $attendeeId : $attendee['id'];

			JLoader::import('components.com_jticketing.models.orderitem', JPATH_SITE);
			$ordrItemModel = JModelLegacy::getInstance('Orderitem', 'JticketingModel');
			$fields['attendee_id'] = (int) $attendeeId;
			$ordrItemModel->updateorderItems($fields);

			JLoader::import('components.com_jticketing.models.attendeefieldvalues', JPATH_SITE);
			$attendeeFieldValuesModel = JModelLegacy::getInstance('Attendeefieldvalues', 'JticketingModel');
			$fields['attendee_id'] = (int) $attendeeId;
			$attendeeFieldValuesModel->save($fields);
		}

		return true;
	}

	/**
	 * Update attendee owner
	 *
	 * @param   INT     $attendeeId  email of joomla user
	 * @param   INT     $ownerId     order creator ID
	 * @param   STRING  $ownerEmail  order creator Email
	 *
	 * @return  orderItems order item object
	 *
	 * @since   1.0
	 */
	public function updateAttendeeOwner($attendeeId, $ownerId = 0, $ownerEmail = '')
	{
		$obj = new stdClass;

		$obj->id = $attendeeId;

		if ($ownerId)
		{
			$obj->owner_id = $ownerId;
		}

		if ($ownerEmail)
		{
			$obj->owner_email = $ownerEmail;
		}

		if ($ownerId || $ownerEmail)
		{
			// Update order entry.
			if (!$this->_db->updateObject('#__jticketing_attendees', $obj, 'id'))
			{
				echo $this->_db->stderr();

				return false;
			}
			else
			{
				return true;
			}
		}

		return false;
	}
}
