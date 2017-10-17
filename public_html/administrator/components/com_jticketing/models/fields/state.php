<?php
/**
 * @version    SVN: <svn_id>
 * @package    JGive
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.form.formfield');

/**
 * render plugin selection of type online event
 *
 * @since  1.0
 */
class JFormFieldState extends JFormField
{
	protected $type = 'State';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 *
	 * @since	1.6
	 */
	protected function getInput()
	{
		return self::fetchElement($this->name, $this->value, $this->element, $this->options['control']);
	}

	/**
	 * Returns html element select plugin
	 *
	 * @param   string  $name          Name of control
	 * @param   string  $value         Value of control
	 * @param   string  &$node         Node name
	 * @param   array   $control_name  Control Name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function fetchElement($name, $value, &$node, $control_name)
	{
		$options = array();
		$options[] = JHtml::_('select.option', "", JText::_('COM_JTICKETING_VENUES_STATE'));

		return JHtml::_('select.genericlist',
					$options,
					'jform[state_id]',
					'class="input-style"  required="required" aria-invalid="false" size="1" ',
					'value', 'text', $this->value, 'state_id');
	}
}
