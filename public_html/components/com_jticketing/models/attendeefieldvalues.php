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
class JticketingModelAttendeefieldvalues extends JModelAdmin
{
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
		$form = $this->loadForm('com_jticketing.attendeefieldvalues', 'attendeefieldvalues', array('control' => 'jform', 'load_data' => $loadData));

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
	public function getTable($type = 'Attendeefieldvalues', $prefix = 'JticketingTable', $config = array())
	{
		$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   data  $data  TO  ADD
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public function save($data)
	{
		$attendeeId = $data['attendee_id'];

		// Save Custom user Entry Fields
		foreach ($data as $key => $field)
		{
			if ($key == 'order_items_id' || $key == 'ticket_type' || $key == 'attendee_id')
			{
				continue;
			}

			$db    = JFactory::getDBO();

			// Using id for Event specific custom fields
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('id')));
			$query->from($db->quoteName('#__jticketing_attendee_fields'));
			$query->where($db->quoteName('id') . ' LIKE ' . $db->quote($key));
			$db->setQuery($query);
			$fieldId = $db->loadResult();

			if ($fieldId)
			{
				$fieldSource = "com_jticketing";
			}
			else
			{
				// Using name for Universal custom fields
				$query = $db->getQuery(true);
				$query->select($db->quoteName(array('id')));
				$query->from($db->quoteName('#__tjfields_fields'));
				$query->where($db->quoteName('name') . ' LIKE ' . $db->quote($key));
				$db->setQuery($query);
				$fieldId = $db->loadResult();
				$fieldSource = "com_tjfields.com_jticketing.ticket";
			}

			if ($fieldId)
			{
				$row             = new stdClass;
				$row->id         = '';
				$fieldIdExists = 0;

				// Changed this for phpcs error
				$query = $db->getQuery(true);
				$query->select($db->qn(array('id')));
				$query->from($db->qn('#__jticketing_attendee_field_values'));
				$query->where($db->qn('attendee_id') . ' = ' . $db->quote($attendeeId));
				$query->where($db->qn('field_id') . ' = ' . $db->quote($fieldId));
				$query->where($db->qn('field_source') . ' = ' . $db->quote($fieldSource));

				// Important to use field source in query
				$db->setQuery($query);
				$fieldIdExists = $db->loadResult();
				$row->field_source = $fieldSource;
				$row->field_id     = $fieldId;
				$row->attendee_id  = $attendeeId;

				if (is_array($field))
				{
					$field = implode('|', $field);
				}

				$row->field_value = $field;

				if ($fieldIdExists)
				{
					$row->id = $fieldIdExists;
				}

				$data = (array) $row;
				parent::save($data);
			}
		}
	}
}
