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
 * List of available banks.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealFormFieldBanks extends JFormFieldList
{
	/**
	 * Type of field
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $type = 'Banks';

	/**
	 * Build a list of available banks.
	 *
	 * @return  string  HTML select list with banks.
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
		$options[] = JHtml::_('select.option', 'INGSEPA', 'ING');
		$options[] = JHtml::_('select.option', 'RABOBANKSEPA', 'Rabobank');
		$options[] = JHtml::_('select.option', 'ABNAMROSEPA', 'ABN AMRO');
		$options[] = JHtml::_('select.option', 'DBSEPA', 'Deutsche Bank');
		$options[] = JHtml::_('select.option', 'INGSEPATEST', 'ING | TEST Server');
		$options[] = JHtml::_('select.option', 'RABOBANKSEPATEST', 'Rabobank | TEST Server');
		$options[] = JHtml::_('select.option', 'ABNAMROSEPATEST', 'ABN AMRO | TEST Server');
		$options[] = JHtml::_('select.option', 'DBSEPATEST', 'Deutsche Bank | TEST Server');

		return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
	}
}
