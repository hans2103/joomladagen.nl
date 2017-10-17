<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidation');
jimport('joomla.html.parameter.element');
jimport('joomla.form.formfield');
jimport('joomla.html.html.access');
jimport('joomla.utilities.xmlelement');
$document = JFactory::getDocument();
require_once JPATH_SITE . '/libraries/joomla/form/fields/textarea.php';

/**
 * Integrations mapping class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldeasysocialfieldmapping extends JFormFieldTextarea
{
	/**
	 * mapping fields for joomla,cb,jomsocial to fill in billing form
	 *
	 * @return  html mapping fields
	 *
	 * @since   1.0
	 */
	public function getInput()
	{
		return $textarea = $this->fetchElement($this->name, $this->value, $this->element, $this->options['control']);
	}

	/**
	 * Get mapping fields
	 *
	 * @param   string  $name          name of element
	 * @param   string  $value         value of element
	 * @param   string  &$node         node
	 * @param   string  $control_name  control name
	 *
	 * @return  html  select box of mapping fields
	 *
	 * @since   1.0
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$rows = $node->attributes()->rows;
		$cols = $node->attributes()->cols;
		$class = ($node->attributes('class') ? 'class="' . $node->attributes('class') . '"' : 'class="text_area"');

		// To render field which already saved in db
		$fieldvalue = trim($this->renderedfield());

		// For first time installation check value or textarea is empty
		if (($fieldvalue == ''))
		{
			$fieldvalue = 'firstname=name' . "\n";
			$fieldvalue = 'lastname=name' . "\n";
			$fieldvalue .= 'user_email=email' . "\n";
		}

		$fieldavi = 'firstname=name' . "\n";
		$fieldavi .= 'lastname=name' . "\n";
		$fieldavi .= 'address=address' . "\n";
		$fieldavi .= 'phone=textbox' . "\n";
		$fieldavi .= 'website_address=url' . "\n";
		$fieldavi .= 'user_email=email' . "\n";

		$html = '<textarea name="' . $control_name . $name . '" cols="' . $cols . '" rows="'
		. $rows . '" ' . $class . ' id="' . $control_name . $name . '" >' . $fieldvalue . '</textarea>';
		$html .= '  ' . JText::_('COM_JTICKETING_FIELDS_EASYSOCIAL') . ':';

		return $html .= '<textarea  cols="' . $cols . '" rows="' . $rows . '" ' .
						$class . ' disabled="disabled" >' . $fieldavi . '</textarea>';
	}

	/**
	 * Render fields
	 *
	 * @return  array country list
	 *
	 * @since   1.0
	 */
	public function renderedfield()
	{
		$params = JComponentHelper::getParams('com_jticketing');
		$mapping = trim($params->get('easysocial_fieldmap'));
		$field_explode = explode('\n', $mapping);
		$fieldvalue = '';

		// Check value exist in array
		if (isset($mapping))
		{
			foreach ($field_explode as $field)
			{
				$fieldvalue .= $field . "\n";
			}

			return $fieldvalue;
		}
	}
}
