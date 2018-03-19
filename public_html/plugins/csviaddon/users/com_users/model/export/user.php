<?php
/**
 * @package     CSVI
 * @subpackage  JoomlaUsers
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

namespace users\com_users\model\export;

defined('_JEXEC') or die;

/**
 * Export Joomla Users.
 *
 * @package     CSVI
 * @subpackage  JoomlaUsers
 * @since       6.0
 */
class User extends \CsviModelExports
{

	/**
	 * List of available custom fields
	 *
	 * @var    array
	 * @since  7.2.0
	 */
	private $customFields = array();

	/**
	 * Export the data.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	protected function exportBody()
	{
		if (parent::exportBody())
		{
			$this->loadCustomFields();

			// Build something fancy to only get the fieldnames the user wants
			$userfields = array();
			$exportfields = $this->fields->getFields();
			$userfields[] = $this->db->quoteName('u.id');

			foreach ($exportfields as $field)
			{
				switch ($field->field_name)
				{
					case 'fullname':
						$userfields[] = $this->db->quoteName('u.name', 'fullname');
						break;
					case 'usergroup_name':
						$userfields[] = $this->db->quoteName('id');
						break;
					case 'custom':
						break;
					default:
						if (!in_array($field->field_name, $this->customFields))
						{
							$userfields[] = $this->db->quoteName($field->field_name);
						}
						break;
				}
			}

			// Build the query
			$userfields = array_unique($userfields);
			$query = $this->db->getQuery(true);
			$query->select(implode(",\n", $userfields));
			$query->from($this->db->quoteName('#__users', 'u'));

			// Filter by published state
			$user_state = $this->template->get('user_state');

			if ($user_state != '*')
			{
				$query->where($this->db->quoteName('u.block') . ' = ' . (int) $user_state);
			}

			// Filter by active state
			$user_active = $this->template->get('user_active');

			if ($user_active == '0')
			{
				$query->where($this->db->quoteName('u.activation') . ' = ' . $this->db->quote(''));
			}
			elseif ($user_active == '1')
			{
				$query->where($this->db->quoteName('u.activation') . ' = ' . $this->db->quote('32'));
			}

			// Filter by user group
			$user_groups = $this->template->get('user_group');

			if ($user_groups && $user_groups[0] != '*')
			{
				$query->leftJoin(
						$this->db->quoteName('#__user_usergroup_map', 'map2')
						. ' ON ' . $this->db->quoteName('map2.user_id') . ' = ' . $this->db->quoteName('u.id')
				);

				if (isset($user_groups))
				{
					$query->where($this->db->quoteName('map2.group_id') . ' IN (' . implode(',', $user_groups) . ')');
				}
			}

			// Filter on range
			$user_range = $this->template->get('user_range', 'user');

			if ($user_range != '*')
			{
				jimport('joomla.utilities.date');

				// Get UTC for now.
				$dNow = new \JDate;
				$dStart = clone $dNow;

				switch ($user_range)
				{
					case 'past_week':
						$dStart->modify('-7 day');
						break;

					case 'past_1month':
						$dStart->modify('-1 month');
						break;

					case 'past_3month':
						$dStart->modify('-3 month');
						break;

					case 'past_6month':
						$dStart->modify('-6 month');
						break;

					case 'post_year':
					case 'past_year':
						$dStart->modify('-1 year');
						break;

					case 'today':
						// Ranges that need to align with local 'days' need special treatment.
						$app	= \JFactory::getApplication();
						$offset	= $app->get('offset');

						// Reset the start time to be the beginning of today, local time.
						$dStart	= new \JDate('now', $offset);
						$dStart->setTime(0, 0, 0);

						// Now change the timezone back to UTC.
						$tz = new \DateTimeZone('GMT');
						$dStart->setTimezone($tz);
						break;
				}

				if ($user_range == 'post_year')
				{
					$query->where($this->db->quoteName('u.registerDate') . ' < ' . $this->db->quote($dStart->format('Y-m-d H:i:s')));
				}
				else
				{
					$query->where(
						$this->db->quoteName('u.registerDate') . ' >= ' . $this->db->quote($dStart->format('Y-m-d H:i:s'))
						. ' AND u.registerDate <=' . $this->db->quote($dNow->format('Y-m-d H:i:s'))
					);
				}
			}

			// Add a limit if user wants us to
			$limits = $this->getExportLimit();

			// Execute the query
			$this->db->setQuery($query, $limits['offset'], $limits['limit']);
			$records = $this->db->getIterator();
			$this->log->add('Export query' . $query->__toString(), false);

			// Check if there are any records
			$logcount = $this->db->getNumRows();

			if ($logcount > 0)
			{
				foreach ($records as $record)
				{
					$this->log->incrementLinenumber();

					foreach ($exportfields as $field)
					{
						$fieldname = $field->field_name;

						// Set the field value
						if (isset($record->$fieldname))
						{
							$fieldvalue = $record->$fieldname;
						}
						else
						{
							$fieldvalue = '';
						}

						// Process the field
						switch ($fieldname)
						{
							case 'usergroup_name':
								$query = $this->db->getQuery(true);
								$query->select($this->db->quoteName('title'));
								$query->from($this->db->quoteName('#__usergroups'));
								$query->leftJoin(
									$this->db->quoteName('#__user_usergroup_map')
									. ' ON ' . $this->db->quoteName('#__user_usergroup_map.group_id') . ' = ' . $this->db->quoteName('#__usergroups.id')
								);
								$query->where($this->db->quoteName('user_id') . ' = ' . $record->id);
								$this->db->setQuery($query);
								$groups = $this->db->loadColumn();

								if (is_array($groups))
								{
									$fieldvalue = implode('|', $groups);
								}
								else
								{
									$fieldvalue = '';
								}
								break;
							default:
								if (in_array($fieldname, $this->customFields))
								{
									$query->clear()
										->select($this->db->quoteName('id'))
										->from($this->db->quoteName('#__fields'))
										->where($this->db->quoteName('name') . '  = ' . $this->db->quote($fieldname));
									$this->db->setQuery($query);
									$fieldId = $this->db->loadResult();
									$itemId = $record->id;

									$query->clear()
										->select($this->db->quoteName('value'))
										->from($this->db->quoteName('#__fields_values'))
										->where($this->db->quoteName('field_id') . ' = ' . (int) $fieldId)
										->where($this->db->quoteName('item_id') . ' = ' . (int) $itemId);
									$this->db->setQuery($query);
									$fieldResult = $this->db->loadObjectList();

									// Check if the custom field is a multiple image list
									if ($this->fields->checkCustomFieldType($fieldname, 'imagelist'))
									{
										$fieldArray = array();

										foreach ($fieldResult as $result)
										{
											$fieldArray[] = $result->value;
										}

										$fieldvalue = implode('|', $fieldArray);

									}
									else
									{
										if (!empty($fieldResult))
										{
											$fieldvalue = $fieldResult[0]->value;
										}
									}


									if ($fieldvalue && $this->fields->checkCustomFieldType($fieldname, 'calendar'))
									{
										$fieldvalue = $this->fields->getDateFormat($fieldname, $fieldvalue, $field->column_header);
									}
								}

								break;
						}

						// Store the field value
						$this->fields->set($field->csvi_templatefield_id, $fieldvalue);
					}

					// Output the data
					$this->addExportFields();

					// Output the contents
					$this->writeOutput();
				}
			}
			else
			{
				$this->addExportContent(\JText::_('COM_CSVI_NO_DATA_FOUND'));

				// Output the contents
				$this->writeOutput();
			}
		}
	}

	/**
	 * Get a list of custom fields that can be used as available field.
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 *
	 * @throws  \Exception
	 */
	private function loadCustomFields()
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('name'))
			->from($this->db->quoteName('#__fields'))
			->where($this->db->quoteName('state') . ' = 1')
			->where($this->db->quoteName('context') . ' = ' . $this->db->quote('com_users.user'));
		$this->db->setQuery($query);
		$results = $this->db->loadObjectList();

		foreach ($results as $result)
		{
			$this->customFields[] = $result->name;
		}

		$this->log->add('Load the Joomla custom fields for articles');
	}
}
