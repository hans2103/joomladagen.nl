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
 * Class for get html select box  for access levels to enroll
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldAccesslevelsenroll extends JFormField
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
		$Jticketingmainhelper = JPATH_ROOT . '/components/com_jticketing/helpers/main.php';

		if (!class_exists('Jticketingmainhelper'))
		{
			JLoader::register('Jticketingmainhelper', $Jticketingmainhelper);
			JLoader::load('Jticketingmainhelper');
		}

		$Jticketingmainhelper = new Jticketingmainhelper;
		$accesslevels         = $Jticketingmainhelper->getAccessLevels();
		$accesslevels_options = array();

		if ($accesslevels)
		{
			foreach ($accesslevels AS $accesslevel)
			{
				$accesslevels_options[] = JHtml::_('select.option', $accesslevel->id, $accesslevel->title);
			}
		}

		$fieldName = $name;
		$class = 'class="inputbox required" multiple="multiple"';

		return JHtml::_('select.genericlist', $accesslevels_options, $fieldName, $class, 'value', 'text', $value, $control_name . $name);
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
