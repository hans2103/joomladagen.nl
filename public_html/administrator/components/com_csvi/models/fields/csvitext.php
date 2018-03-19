<?php
/**
 * @package     CSVI
 * @subpackage  Fields
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('CsviForm');

/**
 * A custom text field with placeholder support.
 *
 * @package     CSVI
 * @subpackage  Fields
 * @since       6.0
 */
class JFormFieldCsviText extends JFormFieldCsviForm
{
	/**
	 * The type of field.
	 *
	 * @var    string
	 * @since  6.0
	 */
	protected $type = 'CsviText';

	/**
	 * Create a text input field.
	 *
	 * @return  string  The HTML markup inputbox.
	 *
	 * @since   6.0
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$required = $this->required ? ' required="required" aria-required="true"' : '';

		// Check if we need to set the local path as placeholder
		if ($this->element['localpath'])
		{
			$this->element['placeholder'] = JPATH_ROOT;
		}

		$placeholder = $this->element['placeholder'] ? ' placeholder="' . (string) $this->element['placeholder'] . '"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		return '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $required . $placeholder . '/>';
	}
}
