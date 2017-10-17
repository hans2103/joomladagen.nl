<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

jimport('joomla.form.formfield');

/**
 * Supports an HTML select list of gateways
 */
class JFormFieldBssetup extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	var	$type = 'Bssetup';

	function getInput()
	{
		return $this->fetchElement($this->name, $this->value, $this->element, $this->options['control']);
	}

	function fetchElement($name, $value, $node, $control_name)
	{
		$actionLink = JURI::base() . "index.php?option=com_jticketing&view=cp&layout=setup";
		// Show link for payment plugins.
		$html = '<a
			href="' . $actionLink . '" target="_blank"
			class="btn btn-small btn-primary ">'
				. JText::_('COM_JTICKETING_CLICK_BS_SETUP_INSTRUCTION') .
			'</a>';

		return $html;
	}
}
