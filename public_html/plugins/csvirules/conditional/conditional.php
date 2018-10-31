<?php
/**
 * @package     CSVI
 * @subpackage  Plugin.conditional
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2016 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Check the condition and replace field or value
 *
 * @package     CSVI
 * @subpackage  Plugin.Conditional
 * @since       7.2.0
 */
class PlgCsvirulesConditional extends RantaiPluginDispatcher
{
	/**
	 * The unique ID of the plugin
	 *
	 * @var    string
	 * @since  7.2.0
	 */
	private $id = 'csviconditional';

	/**
	 * Return the name of the plugin.
	 *
	 * @return  array  The name and ID of the plugin.
	 *
	 * @since   7.2.0
	 */
	public function getName()
	{
		return array('value' => $this->id, 'text' => 'CSVI Conditional');
	}

	/**
	 * Method to get the name only of the plugin.
	 *
	 * @param   string  $plugin  The ID of the plugin
	 *
	 * @return  string  The name of the plugin.
	 *
	 * @since   7.2.0
	 */
	public function getSingleName($plugin)
	{
		if ($plugin === $this->id)
		{
			return 'CSVI Conditional';
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
	 * @since   7.2.0
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
			$lang->load('plg_csvirules_conditional', JPATH_SITE . '/plugins/csvirules/conditional', 'en-GB', true);
			$lang->load('plg_csvirules_conditional', JPATH_SITE . '/plugins/csvirules/conditional', null, true);

			if (is_array($options) && array_key_exists('tmpl', $options) && $options['tmpl'] === 'component')
			{
				return JText::_('COM_CSVI_PLUGINFORM_SAVE_FIRST');
			}

			// Add the form path for this plugin
			JForm::addFormPath(JPATH_PLUGINS . '/csvirules/conditional/');

			// Load the helper that renders the form
			$helper = new CsviHelperCsvi;

			// Instantiate the form
			$form = JForm::getInstance('conditional', 'form_conditional');

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
	 * @param   array             $settings  The plugin settings set for the field.
	 * @param   array             $field     The field being process.
	 * @param   CsviHelperFields  $fields    A CsviHelperFields object.
	 *
	 * @return  void.
	 *
	 * @since   7.2.0
	 */
	public function runRule($plugin, $settings, $field, CsviHelperFields $fields)
	{
		if ($plugin !== $this->id || !$settings)
		{
			return;
		}

		$return = false;

		foreach ($settings->condition as $condition)
		{
			switch ($condition->condition)
			{
				case 'equalto':
					$field->value == $condition->comparevalue ? $return = 1 : $return = 0;
					break;
				case 'greaterthan':
					$field->value > $condition->comparevalue ? $return = 1 : $return = 0;
					break;
				case 'lessthan':
					$field->value < $condition->comparevalue ? $return = 1 : $return = 0;
					break;
				case 'empty':
					$field->value == '' ? $return = 1 : $return = 0;
			}

			if ($return)
			{
				$replacement = false;

				switch ($condition->replace)
				{
					case 'fieldvalue':
						$fromField = $fields->getField($condition->replacement);

						if ($fromField)
						{
							$replacement = $fromField->value;
						}
						break;
					case 'value':
						$replacement = $condition->replacement;
						break;
				}

				if ($replacement !== 'false')
				{
					$fields->updateField($field, $replacement);
				}
			}
		}
	}
}
