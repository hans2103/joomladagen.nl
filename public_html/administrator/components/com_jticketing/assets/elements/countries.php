<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.parameter.element');
jimport('joomla.form.formfield');

/**
 * Class for get html select box  for countries
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldCountries extends JFormField
{
	/**
	 * Get html select box  for countries
	 *
	 * @return  html select box
	 *
	 * @since   1.0
	 */
	public function getInput()
	{
		return $this->fetchElement($this->name, $this->value, $this->element, $this->options['control']);
	}

	/**
	 * Get country data
	 *
	 * @param   string  $name          name of element
	 * @param   string  $value         value of element
	 * @param   string  &$node         node
	 * @param   string  $control_name  control name
	 *
	 * @return  array country list
	 *
	 * @since   1.0
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$TjGeoHelper = JPATH_ROOT . '/components/com_tjfields/helpers/geo.php';

		if (!class_exists('TjGeoHelper'))
		{
			JLoader::register('TjGeoHelper', $TjGeoHelper);
			JLoader::load('TjGeoHelper');
		}

		$this->TjGeoHelper = new TjGeoHelper;
		$countries         = $this->TjGeoHelper->getCountryList();
		$options           = array();

		foreach ($countries as $country)
		{
			$options[] = JHtml::_('select.option', $country['id'], $country['country']);
		}

		if (JVERSION >= 1.6)
		{
			$fieldName = $name;
		}
		else
		{
			$fieldName = $control_name . '[' . $name . ']';
		}

		return JHtml::_('select.genericlist', $options, $fieldName, 'class="inputbox required"', 'value', 'text', $value, $control_name . $name);
	}

	/**
	 * Get tooltip of element
	 *
	 * @param   string  $label         name of element
	 * @param   string  $description   description
	 * @param   string  &$node         node
	 * @param   string  $control_name  control name
	 * @param   string  $name          name of element
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function fetchTooltip($label, $description, &$node, $control_name, $name)
	{
		return null;
	}
}
