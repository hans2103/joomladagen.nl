<?php
/**
 * @package     CSVI
 * @subpackage  Plugin.Margin
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
 * @subpackage  Plugin.Margin
 * @since       6.0
 */
class plgCsvirulesMargin extends RantaiPluginDispatcher
{

	/**
	 * The unique ID of the plugin
	 *
	 * @var    string
	 * @since  6.0
	 */
	private $id = 'csvimargin';

	/**
	 * Return the name of the plugin.
	 *
	 * @return  array  The name and ID of the plugin.
	 *
	 * @since   6.0
	 */
	public function getName()
	{
		return array('value' => 'csvimargin', 'text' => 'CSVI Margin');
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
			return 'CSVI Margin';
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
	 * @throws  UnexpectedValueException
	 */
	public function getForm($plugin, $options = array())
	{
		if ($plugin === $this->id)
		{
			// Load the language files
			$lang = JFactory::getLanguage();
			$lang->load('plg_csvirules_margin', JPATH_ADMINISTRATOR, 'en-GB', true);
			$lang->load('plg_csvirules_margin', JPATH_ADMINISTRATOR, null, true);

			// Add the form path for this plugin
			JForm::addFormPath(JPATH_PLUGINS . '/csvirules/margin/');

			// Load the helper that renders the form
			$helper = new CsviHelperCsvi;

			// Instantiate the form
			$form = JForm::getInstance('margin', 'form_margin');

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
	 * @param   string  $plugin    The ID of the plugin.
	 * @param   array   $settings  The plugin settings set for the field.
	 * @param   array   $field     The field being process.
	 * @param   array   $fields    All fields used for import/export.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function runRule($plugin, $settings, $field, $fields)
	{
		if ($plugin === $this->id && $settings)
		{
			$value      = $field->value;
			$clean      = str_replace(",", ".", $value);
			$lastpos    = strrpos($clean, '.');
			$value      = str_replace('.', '', substr($clean, 0, $lastpos)) . substr($clean, $lastpos);
			$priceValue = (float) $settings->pricevalue;
			$process    = false;

			if ($priceValue && $settings->comparison)
			{
				switch ($settings->comparison)
				{
					case 'equalto':
						$process = $value === $priceValue;
						break;
					case 'greaterthan':
						$process = $value > $priceValue;
						break;
					case 'greaterthanequalto':
						$process = $value >= $priceValue;
						break;
					case 'lessthan':
						$process = $value < $priceValue;
						break;
					case 'lessthanequalto':
						$process = $value <= $priceValue;
						break;
				}
			}
			elseif (!$priceValue || !$settings->comparison)
			{
				$process = true;
			}

			if ($process)
			{
				// Check if we have a percentage
				if ($settings->valuetype === 'percentage')
				{
					// Calculate the margin
					$settings->margin = $settings->margin / 100 * $value;
				}

				switch ($settings->operation)
				{
					case 'multiplication':
						$value = $value * $settings->margin;
						break;
					case 'addition':
						$value = $value + $settings->margin;
						break;
					case 'subtraction':
						$value = $value - $settings->margin;
						break;
					case 'division':
						$value = $value / $settings->margin;
						break;
				}

				$fields->updateField($field, $value);
			}
		}
	}
}
