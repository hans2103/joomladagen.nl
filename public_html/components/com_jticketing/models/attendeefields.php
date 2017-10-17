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

// No direct access
defined('_JEXEC') or die;

/**
 * jticketing Model
 *
 * @since  0.0.1
 */
class JTicketingModelAttendeefields extends JModelAdmin
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_JTICKETING';

	/**
	 * @var   string  Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_jticketing.attendeefields';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

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
		// Get the form.
		$form = $this->loadForm(
			'com_jticketing.attendeefields',
			'attendeefields',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   string  $type    Data for the form.
	 * @param   string  $prefix  True if the form is to load its own data (default case), false if not.
	 * @param   array   $config  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  table 
	 *
	 * @since   1.6
	 */
	public function getTable($type = 'Attendeefields', $prefix = 'JticketingTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
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
		$details = '';
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('attnds.id')));
		$query->from($db->quoteName('#__jticketing_attendees', 'attnds'));

		if (isset($params['attendee_id']))
		{
			$query->where($db->quoteName('attnds.id') . ' = ' . $db->quote($params['attendee_id']));
		}

		$db->setQuery($query);
		$attendeeIds = $db->loadObjectlist();

		foreach ($attendeeIds AS $attendeeId)
		{
			$result = '';
			$query = $db->getQuery(true);
			$query->select($db->qn(array('fv.field_value','fv.field_id', 'fields.name' )));
			$query->from($db->qn('#__jticketing_attendees', 'attnds'));
			$query->join('INNER', $db->qn('#__jticketing_attendee_field_values', 'fv')
			. 'ON (' . $db->qn('attnds.id') . ' = ' . $db->qn('fv.attendee_id') . ')');
			$query->join('INNER', $db->qn('#__jticketing_attendee_fields', 'fields') .
			'ON (' . $db->qn('fields.id') . ' = ' . $db->qn('fv.field_id') . ')');
			$query->where($db->qn('field_source') . ' = "com_jticketing"');
			$query->where($db->qn('attnds.id') . ' = ' . $db->quote($attendeeId->id));

			if (isset($params['user_id']))
			{
				$query->where($db->quoteName('attnds.owner_id') . ' = ' . $db->quote($params['user_id']));
			}

			if (isset($params['field_id']))
			{
				$query->where($db->quoteName('fv.field_id') . ' = ' . $db->quote($params['field_id']));
			}

			$db->setQuery($query);
			$result = $db->loadObjectlist();

			if ($result)
			{
				$details[$attendeeId->id] = $result;
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
		$fieldManagerPath = JPATH_SITE . '/components/com_tjfields/helpers/tjfields.php';

		if (file_exists($fieldManagerPath))
		{
			$TjfieldsHelper          = new TjfieldsHelper;
			$universalAttendeeFields = $TjfieldsHelper->getUniversalFields('com_jticketing.ticket');
			$details = '';

			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('attnds.id')));
			$query->from($db->quoteName('#__jticketing_attendees', 'attnds'));

			if (isset($params['attendee_id']))
			{
				$query->where($db->quoteName('attnds.id') . ' = ' . $db->quote($params['attendee_id']));
			}

			$db->setQuery($query);
			$attendeeIds = $db->loadObjectlist();
			$result = '';

			if ($universalAttendeeFields)
			{
				foreach ($attendeeIds AS $attendeeId)
				{
					$i = 0;

					foreach ($universalAttendeeFields AS $field)
					{
						$query = $db->getQuery(true);
						$query->select($db->qn(array('fv.field_value','fv.field_id')));
						$query->from($db->qn('#__jticketing_attendees', 'attnds'));
						$query->join('INNER', $db->qn('#__jticketing_attendee_field_values', 'fv')
						. 'ON (' . $db->qn('attnds.id') . ' = ' . $db->qn('fv.attendee_id') . ')');
						$query->where($db->qn('field_source') . ' = `com_tjfields.com_jticketing.ticket`');
						$query->where($db->qn('fv.field_id') . ' = ' . $db->quote($field->id));
						$query->where($db->qn('attnds.id') . ' = ' . $db->quote($attendeeId->id));

						if (isset($params['user_id']))
						{
							$query->where($db->quoteName('attnds.owner_id') . ' = ' . $db->quote($params['user_id']));
						}

						if (isset($params['field_id']))
						{
							$query->where($db->quoteName('fv.field_id') . ' = ' . $db->quote($params['field_id']));
						}

						$db->setQuery($query);
						$resultObj = $db->loadObject();

						if (!empty($resultObj))
						{
							$result[$i]           = $resultObj;
							$result[$i]->name     = $field->name;
							$result[$i]->field_id = $field->id;
							$i++;
						}
					}

					if (!empty($result))
					{
						$details[$attendeeId->id] = $result;
					}
				}
			}

			if (!empty($details))
			{
				return $details;
			}
		}
	}
}
