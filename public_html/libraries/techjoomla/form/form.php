<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

jimport('joomla.filesystem.path');

/**
 * Form Class for the Joomla Platform.
 *
 * This class implements a robust API for constructing, populating, filtering, and validating forms.
 * It uses XML definitions to construct form fields and a variety of field and rule classes to
 * render and validate the form.
 *
 * @link   http://www.w3.org/TR/html4/interact/forms.html
 * @link   http://www.w3.org/TR/html5/forms.html
 * @since  11.1
 */
class TjForm extends JForm
{
	/**
	 * Form instances.
	 *
	 * @var    JForm[]
	 * @since  11.1
	 */
	protected static $forms = array();

	/**
	 * Method to get an instance of a form.
	 *
	 * @param   string          $name     The name of the form.
	 * @param   string          $data     The name of an XML file or string to load as the form definition.
	 * @param   array           $options  An array of form options.
	 * @param   boolean         $replace  Flag to toggle whether form fields should be replaced if a field
	 *                                    already exists with the same group/name.
	 * @param   string|boolean  $xpath    An optional xpath to search for the fields.
	 *
	 * @return  JForm  JForm instance.
	 *
	 * @since   11.1
	 * @throws  InvalidArgumentException if no data provided.
	 * @throws  RuntimeException if the form could not be loaded.
	 */
	public static function getInstance($name, $data = null, $options = array(), $replace = true, $xpath = false)
	{
		// Reference to array with form instances
		$forms = &self::$forms;

		// Only instantiate the form if it does not already exist.
		if (!isset($forms[$name]))
		{
			$data = trim($data);

			if (empty($data))
			{
				throw new InvalidArgumentException(sprintf('JForm::getInstance(name, *%s*)', gettype($data)));
			}

			// ^TJ
			// Instantiate the form.
			$forms[$name] = new TjForm($name, $options);

			// ^TJ - end

			// Load the data.
			if (substr($data, 0, 1) == '<')
			{
				if ($forms[$name]->load($data, $replace, $xpath) == false)
				{
					throw new RuntimeException('JForm::getInstance could not load form');
				}
			}
			else
			{
				if ($forms[$name]->loadFile($data, $replace, $xpath) == false)
				{
					throw new RuntimeException('JForm::getInstance could not load file');
				}
			}
		}

		return $forms[$name];
	}

	/**
	 * Method to validate form data.
	 *
	 * Validation warnings will be pushed into JForm::errors and should be
	 * retrieved with JForm::getErrors() when validate returns boolean false.
	 *
	 * @param   array   $data   An array of field values to validate.
	 * @param   string  $group  The optional dot-separated form group path on which to filter the
	 *                          fields to be validated.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function validate($data, $group = null)
	{
		// Make sure there is a valid JForm XML document.
		if (!($this->xml instanceof SimpleXMLElement))
		{
			return false;
		}

		$return = true;

		// Create an input registry object from the data to validate.
		$input = new Registry($data);

		// Get the fields for which to validate the data.
		$fields = $this->findFieldsByGroup($group);

		if (!$fields)
		{
			// PANIC!
			return false;
		}

		// +TJ
		$isPatch = false;
		$skipValidations = false;

		if (isset($data['isPatch']) && $data['isPatch'] == 'true')
		{
			$isPatch = true;
		}

		if (isset($data['skipValidations']) && $data['skipValidations'] == 'true')
		{
			$skipValidations = true;
		}

		unset($data['isPatch']);
		unset($data['skipValidations']);

		$dataKeys = array_keys($data);

		// @print_r($dataKeys); var_dump($isPatch); var_dump($skipValidations);
		// +TJ - end

		// Validate the fields.
		foreach ($fields as $field)
		{
			$value = null;
			$name = (string) $field['name'];

			// +TJ - Skip processing current field from jform xml if it's not posted
			if (!in_array($name, $dataKeys) && $isPatch)
			{
				continue;
			}
			// +TJ - end

			// Get the group names as strings for ancestor fields elements.
			$attrs = $field->xpath('ancestor::fields[@name]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			$group = implode('.', $groups);

			// Get the value from the input data.
			if ($group)
			{
				$value = $input->get($group . '.' . $name);
			}
			else
			{
				$value = $input->get($name);
			}

			// Validate the field.
			// Validate if skipValidations = false
			if (!$skipValidations)
			{
				$valid = $this->validateField($field, $group, $value, $input);
			}

			// Check for an error.
			if ($valid instanceof Exception)
			{
				array_push($this->errors, $valid);
				$return = false;
			}
		}

		return $return;
	}
}
