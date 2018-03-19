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

JFormHelper::loadFieldClass('text');

/**
 * Renders a field as a button.
 *
 * @package     CSVI
 * @subpackage  Fields
 * @since       6.6.0
 */
class CsviFormFieldButton extends JFormFieldText
{
	/**
	 * Create a button field.
	 *
	 * @return  string  The HTML markup field.
	 *
	 * @since   6.6.0
	 */
	public function getInput()
	{
		$this->label = '';

		$text    = $this->element['text'];
		$class   = $this->element['class'] ? (string) $this->element['class'] : '';
		$icon    = $this->element['icon'] ? (string) $this->element['icon'] : '';
		$onclick = $this->element['onclick'] ? 'onclick="' . (string) $this->element['onclick'] . '"' : '';
		$title   = $this->element['title'] ? 'title="' . JText::_((string) $this->element['title']) . '"' : '';

		$this->value = JText::_($text);

		if ($icon)
		{
			$icon = '<span class="icon ' . $icon . '"></span>';
		}

		return '<button id="' . $this->id . '" class="btn ' . $class . '" ' .
			$onclick . $title . '>' .
			$icon .
			htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') .
			'</button>';
	}

	/**
	 * Method to get the field title.
	 *
	 * @return  string  The field title.
	 */
	protected function getTitle()
	{
		return null;
	}
}
