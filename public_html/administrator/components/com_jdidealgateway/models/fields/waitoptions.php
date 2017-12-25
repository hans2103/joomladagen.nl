<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * List of wait options.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealFormFieldWaitoptions extends JFormFieldList
{
	/**
	 * Type of field
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $type = 'Waitoptions';

	/**
	 * Build a list of wait options.
	 *
	 * @return  string  HTML select list with wait options.
	 *
	 * @since   2.0
	 */
	public function getInput()
	{
		// Initialize variables.
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Create the list of banks
		$options = array();
		$options[] = JHtml::_('select.option', 'wait', JText::_('COM_JDIDEALGATEWAY_OPTION_WAIT_TIME'));
		$options[] = JHtml::_('select.option', 'timer', JText::_('COM_JDIDEALGATEWAY_OPTION_TIMER_TIME'));
		$options[] = JHtml::_('select.option', 'direct', JText::_('COM_JDIDEALGATEWAY_OPTION_DIRECT_TIME'));

		return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
	}
}
