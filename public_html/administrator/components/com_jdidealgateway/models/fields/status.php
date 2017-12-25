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
 * Order statuses.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealFormFieldStatus extends JFormFieldList
{
	/**
	 * Type of field
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $type = 'Status';

	/**
	 * Build a list of available order statuses.
	 *
	 * @return  string  HTML select list with order statuses.
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

		// Create the list of statuses
		$options = array();
		$options[] = JHtml::_('select.option', 'C', 'COM_JDIDEALGATEWAY_STATUS_SUCCESS');
		$options[] = JHtml::_('select.option', 'P', 'COM_JDIDEALGATEWAY_STATUS_PENDING');
		$options[] = JHtml::_('select.option', 'X', 'COM_JDIDEALGATEWAY_STATUS_CANCELLED');
		$options[] = JHtml::_('select.option', 'F', 'COM_JDIDEALGATEWAY_STATUS_FAILURE');

		return JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id, true);
	}
}
