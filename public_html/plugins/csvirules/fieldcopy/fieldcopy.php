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
 * @subpackage  Plugin.Copy
 * @since       6.0
 */
class PlgCsvirulesFieldcopy extends RantaiPluginDispatcher
{
	/**
	 * The unique ID of the plugin
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $id = 'csvifieldcopy';

	/**
	 * Return the name of the plugin.
	 *
	 * @return  array  The name and ID of the plugin.
	 *
	 * @since   6.0
	 */
	public function getName()
	{
		return array('value' => $this->id, 'text' => 'CSVI Field Copy');
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
		if ($plugin == $this->id)
		{
			return 'CSVI Field Copy';
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
	 *
	 */
	public function getForm($plugin, $options = array())
	{
		if ($plugin === $this->id)
		{
			// Load the language files
			$lang = JFactory::getLanguage();
			$lang->load('plg_csvirules_fieldcopy', JPATH_ADMINISTRATOR, 'en-GB', true);
			$lang->load('plg_csvirules_fieldcopy', JPATH_ADMINISTRATOR, null, true);

			// Add the form path for this plugin
			JForm::addFormPath(JPATH_PLUGINS . '/csvirules/fieldcopy/');

			// Load the helper that renders the form
			$helper = new CsviHelperCsvi;

			// Instantiate the form
			$form = JForm::getInstance('fieldcopy', 'form_fieldcopy');

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
	 * @param   string            $plugin    The ID of the plugin
	 * @param   array             $settings  The plugin settings set for the field
	 * @param   array             $field     The field being process
	 * @param   CsviHelperFields  $fields    A CsviHelperFields object
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function runRule($plugin, $settings, $field, CsviHelperFields $fields)
	{
		if ($plugin === $this->id)
		{
			// Perform the replacement
			if (!empty($settings) && ($field->field_name === $settings->source))
			{
				// Check if we have a source value
				if ($settings->target)
				{
					// Load the target fields
					$updates = explode(',', $settings->target);

					foreach ($updates as $update)
					{
						$updateField = $fields->getField($update);
						$fields->updateField($updateField, $fields->get($field->field_name));
					}
				}
			}
		}
	}
}
