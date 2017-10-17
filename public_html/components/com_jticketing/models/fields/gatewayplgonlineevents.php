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
class JFormFieldGatewayplgonlineevents extends JFormField
{
	protected $type = 'Gatewayplgonlineevents';

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
		$db  = JFactory::getDBO();
		$jinput = JFactory::getApplication()->input;
		$venueid = $jinput->get('id', '', 'INT');
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('tjevents');
		$results = $dispatcher->trigger('GetContentInfo', array());

		if (empty($results[0]))
		{
			$html = '<div class="alert alert-warning" id="enable_tjevents">';

			$html .= sprintf(JText::_("COM_JTICKETING_ENABLE_TJEVENTS_PLG"));

		$html .= '</div>';

		return $html;
		}
		else
		{
			$options[] = JHTML::_('select.option', 0, JText::_("COM_JTICKETING_SELECT_ONLINE_EVENT"));

			foreach ($results as $result)
			{
				$options[]   = JHtml::_('select.option', $result['id'], $result['name']);
			}

			if (!$venueid)
			{
				$this->value = "";
			}

			return JHtml::_('select.genericlist', $options, $name, 'class="inputbox"  size="5"', 'value', 'text', $this->value);
		}
	}
}
