<?php
/**
 * @package     CSVI
 * @subpackage  Plugin.Fieldcopy
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Copies a field into 1 or more other fields.
 *
 * @package     CSVI
 * @subpackage  Plugin.Combine
 * @since       6.0
 */
class PlgCsvirulesFieldcombine extends RantaiPluginDispatcher
{
	/**
	 * The unique ID of the plugin
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $id = 'csvifieldcombine';

	/**
	 * Return the name of the plugin.
	 *
	 * @return  array  The name and ID of the plugin.
	 *
	 * @since   6.0
	 */
	public function getName()
	{
		return array('value' => $this->id, 'text' => 'CSVI Field combine');
	}

	/**
	 * Method to get the name only of the plugin.
	 *
	 * @param   string  $plugin  The ID of the plugin
	 *
	 * @return  string  The name of the plugin.
	 *
	 * @since   6.0
	 */
	public function getSingleName($plugin)
	{
		if ($plugin === $this->id)
		{
			return 'CSVI Field combine';
		}
	}

	/**
	 * Method to get the field options.
	 *
	 * @param   string  $plugin   The ID of the plugin
	 * @param   array   $options  An array of settings
	 *
	 * @return  string  The rendered form with plugin options.
	 *
	 * @since   6.0
	 *
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 */
	public function getForm($plugin, $options = array())
	{
		if ($plugin === $this->id)
		{
			// Load the language files
			$lang = JFactory::getLanguage();
			$lang->load('plg_csvirules_fieldcombine', JPATH_ADMINISTRATOR, 'en-GB', true);
			$lang->load('plg_csvirules_fieldcombine', JPATH_ADMINISTRATOR, null, true);

			// Add the form path for this plugin
			JForm::addFormPath(JPATH_PLUGINS . '/csvirules/fieldcombine/');

			// Load the helper that renders the form
			$helper = new CsviHelperCsvi;

			// Instantiate the form
			$form = JForm::getInstance('fieldcombine', 'form_fieldcombine');

			// Bind any existing data
			$form->bind(array('pluginform' => $options));

			// Create some dummies
			$input = new JInput;

			// Render the form
			return $helper->renderCsviForm($form, $input);
		}
	}

	/**
	 * Run the rule.
	 *
	 * @param   string            $plugin    The ID of the plugin.
	 * @param   object            $settings  The plugin settings set for the field.
	 * @param   array             $field     The field being process.
	 * @param   CsviHelperFields  $fields    A CsviHelperFields object.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function runRule($plugin, $settings, $field, CsviHelperFields $fields)
	{
		if ($plugin == $this->id)
		{
			// Perform the combine
			if (!empty($settings))
			{
				// Check if we have a source value
				if ($settings->source)
				{
					// Load the friendly field names that need to be combined
					$updates = explode(',', $settings->source);

					// Load all field values and combine them
					$values = array();
					$fieldcounter = array();

					foreach ($updates as $update)
					{
						// Clean any spaces
						$update = trim($update);

						if (!isset($fieldcounter[$update]))
						{
							$fieldcounter[$update] = 0;
						}

						$fieldcounter[$update]++;

						$value = $fields->get($update, '', $fieldcounter[$update]);

						if ($settings->combine_empty || $value)
						{
							$values[] = $value;
						}
					}

					// Find the field to update
					$oldFields = $fields->getFields();
					$target = strtolower($settings->target);

					foreach ($oldFields as $column_header => $oldField)
					{
						if (is_array($oldField))
						{
							// Make it a single dimensional array
							$oldField = reset($oldField);
						}

						if ($oldField)
						{
							$columnHeader = '';

							if (isset($oldField->column_header))
							{
								$columnHeader = $oldField->column_header;
							}

							// For import purposes, check the XML node as well, so we can be more flexible
							if ($columnHeader === '' && isset($oldField->xml_node))
							{
								$columnHeader = $oldField->xml_node;
							}

							$haystack = array(strtolower($columnHeader), strtolower($oldField->field_name));

							if (in_array($target, $haystack))
							{
								$fields->updateField($oldField, implode($settings->combine_character, $values));

								// Let's end
								break;
							}
						}
					}
				}
			}
		}
	}
}
