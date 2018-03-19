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
 * Export Joomla Accesslevel.
 *
 * @package     CSVI
 * @subpackage  JoomlaUsers
 * @since       7.1.0
 */
class Accesslevel extends \CsviModelExports
{
	/**
	 * The addon helper
	 *
	 * @var    \Com_UsersHelperCom_Users
	 * @since  7.1.0
	 */
	protected $helper = null;

	/**
	 * Export the data.
	 *
	 * @return  void.
	 *
	 * @since   7.1.0
	 */
	protected function exportBody()
	{
		if (parent::exportBody())
		{
			// Build something fancy to only get the fieldnames the user wants
			$userfields = array();
			$exportfields = $this->fields->getFields();

			// Group by fields
			$groupbyfields = json_decode($this->template->get('groupbyfields', '', 'string'));
			$groupby = array();

			if (isset($groupbyfields->name))
			{
				$groupbyfields = array_flip($groupbyfields->name);
			}
			else
			{
				$groupbyfields = array();
			}

			// Sort selected fields
			$sortfields = json_decode($this->template->get('sortfields', '', 'string'));
			$sortby = array();

			if (isset($sortfields->name))
			{
				$sortbyfields = array_flip($sortfields->name);
			}
			else
			{
				$sortbyfields = array();
			}

			foreach ($exportfields as $field)
			{
				switch ($field->field_name)
				{
					case 'usergroup_name':
						$userfields[] = $this->db->quoteName('rules');

						if (array_key_exists($field->field_name, $groupbyfields))
						{
							$groupby[] = $this->db->quoteName('rules');
						}

						if (array_key_exists($field->field_name, $sortbyfields))
						{
							$sortby[] = $this->db->quoteName('rules');
						}
						break;
					case 'custom':
						break;
					default:
						$userfields[] = $this->db->quoteName($field->field_name);

						if (array_key_exists($field->field_name, $groupbyfields))
						{
							$groupby[] = $this->db->quoteName($field->field_name);
						}

						if (array_key_exists($field->field_name, $sortbyfields))
						{
							$sortby[] = $this->db->quoteName($field->field_name);
						}
						break;
				}
			}

			// Build the query
			$userfields = array_unique($userfields);
			$query = $this->db->getQuery(true);
			$query->select(implode(",\n", $userfields));
			$query->from($this->db->quoteName('#__viewlevels'));

			// Group the fields
			$groupby = array_unique($groupby);

			if (!empty($groupby))
			{
				$query->group($groupby);
			}

			// Sort set fields
			$sortby = array_unique($sortby);

			if (!empty($sortby))
			{
				$query->order($sortby);
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
						$fieldvalue = '';

						// Set the field value
						if (isset($record->$fieldname))
						{
							$fieldvalue = $record->$fieldname;
						}

						// Process the field
						switch ($fieldname)
						{
							case 'usergroup_name':
								$rules = $record->rules;

								if ($rules)
								{
									$rulesValue = str_replace(array('[', ']'), '', $rules);
									$rulesArray = explode(',', $rulesValue);
									$userGroupArray = array();

									foreach ($rulesArray as $rule)
									{
										$userGroupName = $this->helper->getAccessLevelGroupId($rule, 'title', 'id');

										if ($userGroupName)
										{
											$userGroupArray[] = $userGroupName;
										}
									}

									$fieldvalue = implode('|', $userGroupArray);
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
}
