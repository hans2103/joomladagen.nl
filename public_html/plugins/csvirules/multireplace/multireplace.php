<?php
/**
 * @package     CSVI
 * @subpackage  Plugin.Multireplace
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Replaces values.
 *
 * @package     CSVI
 * @subpackage  Plugin.Multireplace
 * @since       6.4
 */
class PlgCsvirulesMultireplace extends RantaiPluginDispatcher
{
	/**
	 * The unique ID of the plugin
	 *
	 * @var    string
	 * @since  6.4
	 */
	private $id = 'csvimultireplace';

	/**
	 * Return the name of the plugin.
	 *
	 * @return  array  The name and ID of the plugin.
	 *
	 * @since   6.4
	 */
	public function getName()
	{
		return array('value' => $this->id, 'text' => 'CSVI Multireplace');
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
			return 'CSVI MultiReplace';
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
	 */
	public function getForm($plugin, $options = array())
	{
		if ($plugin === $this->id)
		{
			// Load the language files
			$lang = JFactory::getLanguage();
			$lang->load('plg_csvirules_multireplace', JPATH_ADMINISTRATOR, 'en-GB', true);
			$lang->load('plg_csvirules_multireplace', JPATH_ADMINISTRATOR, null, true);

			if (is_array($options) && array_key_exists('tmpl', $options) && $options['tmpl'] === 'component')
			{
				return JText::_('COM_CSVI_PLUGINFORM_SAVE_FIRST');
			}

			// Load the Javascript
			JFactory::getDocument()->addScriptVersion(JUri::root() . '/plugins/csvirules/multireplace/media/js/multireplace.js');

			// Add the form path for this plugin
			JForm::addFormPath(JPATH_PLUGINS . '/csvirules/multireplace/');

			// Load the helper that renders the form
			$helper = new CsviHelperCsvi;

			// Instantiate the form
			$form = JForm::getInstance('replace', 'form_multireplace');

			// Bind any existing data
			$form->bind(array('pluginform' => $options));

			// Create some dummies
			$input = new JInput;

			// Render the form
			return $helper->renderCsviForm($form, $input);
		}

		return '';
	}

	/**
	 * Run the rule.
	 *
	 * @param   string            $plugin    The ID of the plugin
	 * @param   array             $settings  The plugin settings set for the field
	 * @param   array             $field     The field being process
	 * @param   CsviHelperFields  $fields    All fields used for import/export
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function runRule($plugin, $settings, $field, $fields)
	{
		if ($plugin == $this->id)
		{
			// Perform the replacement
			if (!empty($settings))
			{
				// Set the old value
				$value = $field->value;

				foreach ($settings as $setting)
				{
					foreach ($setting as $replacement)
					{
						$value = call_user_func('self::' . $replacement->operation, $value, $replacement);
					}
				}

				// Update the field if needed
				if ($field->value !== $value)
				{
					$fields->updateField($field, $value);
				}
			}
		}
	}

	/**
	 * A function to get an absolute value.
	 *
	 * @param   array   $value        The value to perform replacements on.
	 * @param   object  $replacement  The replacement settings.
	 *
	 * @return  string  Absolute value.
	 *
	 * @since   7.0.0
	 */
	private function abs($value, $replacement)
	{
		return abs($value);
	}

	/**
	 * A function to find a given string.
	 *
	 * @param   string  $value        The value to perform replacements on.
	 * @param   object  $replacement  The replacement settings.
	 *
	 * @return  string  Lowercase string.
	 *
	 * @since   7.0.0
	 */
	private function findreplace($value, $replacement)
	{
		// Check if we have a multiple values
		$findText = $replacement->findtext;
		$replaceText = $replacement->replacetext;

		if ($replacement->multivalue)
		{
			$separator = $replacement->separator;
			$findText = explode($separator, $replacement->findtext);
			$replaceText = explode($separator, $replacement->replacetext);
		}

		// Check if we need to apply the change
		if (!isset($replacement->applywhenempty))
		{
			$replacement->applywhenempty = 1;
		}

		// If the field is empty and applywhenempty setting is no then do nothing
		if ('' !== $value || $replacement->applywhenempty)
		{
			switch ($replacement->method)
			{
				case 'text':
					if ($value === '' && ($findText === ' ' || $findText === '') && $replacement->applywhenempty)
					{
						$value = $replaceText;
					}
					else
					{
						$value = str_ireplace($findText, $replaceText, $value);
					}

					break;
				case 'regex':
					$value = preg_replace($findText, $replaceText, $value);
					break;
			}
		}

		return $value;
	}

	/**
	 * A function to find the position of the first occurrence of a substring in a string.
	 *
	 * @param   array   $value        The value to perform replacements on.
	 * @param   object  $replacement  The replacement settings.
	 *
	 * @return  string  Lowercase string.
	 *
	 * @since   7.0.0
	 */
	private function strlen($value, $replacement)
	{
		return strlen($value);
	}

	/**
	 * A function to find the position of the first occurrence of a substring in a string.
	 *
	 * @param   array   $value        The value to perform replacements on.
	 * @param   object  $replacement  The replacement settings.
	 *
	 * @return  string  Lowercase string.
	 *
	 * @since   7.0.0
	 */
	private function strpos($value, $replacement)
	{
		return strpos($value, $replacement->needle, $replacement->start);
	}

	/**
	 * A function to make a string lowercase.
	 *
	 * @param   array   $value        The value to perform replacements on.
	 * @param   object  $replacement  The replacement settings.
	 *
	 * @return  string  Lowercase string.
	 *
	 * @since   7.0.0
	 */
	private function strtolower($value, $replacement)
	{
		return strtolower($value);
	}

	/**
	 * A function to make a string uppercase.
	 *
	 * @param   array   $value        The value to perform replacements on.
	 * @param   object  $replacement  The replacement settings.
	 *
	 * @return  string  Uppercase string.
	 *
	 * @since   7.0.0
	 */
	private function strtoupper($value, $replacement)
	{
		return strtoupper($value);
	}

	/**
	 * A function to make a string uppercase.
	 *
	 * @param   array   $value        The value to perform replacements on.
	 * @param   object  $replacement  The replacement settings.
	 *
	 * @return  string  Lowercase string.
	 *
	 * @since   7.0.0
	 */
	private function substr($value, $replacement)
	{
		return substr($value, $replacement->start, $replacement->end);
	}

	/**
	 * A function to uppercase the first character.
	 *
	 * @param   array   $value        The value to perform replacements on.
	 * @param   object  $replacement  The replacement settings.
	 *
	 * @return  string  First letter uppercase string.
	 *
	 * @since   7.0.0
	 */
	private function ucfirst($value, $replacement)
	{
		return ucfirst($value);
	}

	/**
	 * A function to prepend a string to the value.
	 *
	 * @param   array   $value        The value to perform replacements on.
	 * @param   object  $replacement  The replacement settings.
	 *
	 * @return  string  New value with prepended string.
	 *
	 * @since   7.0.0
	 */
	private function addbefore($value, $replacement)
	{
		return $replacement->addvalue . $value;
	}

	/**
	 * A function to append a string to the value.
	 *
	 * @param   array   $value        The value to perform replacements on.
	 * @param   object  $replacement  The replacement settings.
	 *
	 * @return  string  New value with appended string.
	 *
	 * @since   7.0.0
	 */
	private function addafter($value, $replacement)
	{
		return $value . $replacement->addvalue;
	}

	/**
	 * A function to format the current date.
	 *
	 * @param   array   $value        The value to perform replacements on.
	 * @param   object  $replacement  The replacement settings.
	 *
	 * @return  string  New value with appended string.
	 *
	 * @since   7.0.0
	 */
	private function date($value, $replacement)
	{
		return date($replacement->dateFormat, time());
	}

	/**
	 * Format a date and time.
	 *
	 * @param   string  $date         The date to convert
	 * @param   object  $replacement  The replacement settings.
	 *
	 * @return  string  A dateformat based on the supplied format.
	 *
	 * @since   7.5.0
	 */
	private function customDate($date, $replacement)
	{
		if ($parsedDate = strtotime($date) === false)
		{
			return $date;
		}

		return date($replacement->customDateFormat, $parsedDate);
	}
}
