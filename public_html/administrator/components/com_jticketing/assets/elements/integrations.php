<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.folder');
jimport('joomla.form.formfield');

/**
 * JFormFieldIntegrations class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JFormFieldIntegrations extends JFormField
{
	/**
	 * Method to get the field input markup.
	 *
	 * @since  1.6
	 *
	 * @return   string  The field input markup
	 */
	public function getInput ()
	{
		return $this->fetchElement($this->name, $this->value, $this->element, $this->options['controls']);
	}

	/**
	 * Method fetchElement
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
	public function fetchElement ($name, $value, &$node, $control_name)
	{
		$communityMainFile = JPATH_SITE . '/components/com_community/community.php';
		$cbMainFile = JPATH_SITE . '/components/com_comprofiler/comprofiler.php';
		$esMainFile = JPATH_SITE . '/components/com_easysocial/easysocial.php';
		$jeventsMainFile = JPATH_SITE . '/components/com_jevents/jevents.php';

		if ($name == 'jform[integration]')
		{
			$options = array();
			$options[] = JHTML::_('select.option', '2', JText::_('COM_JTICKETING_NATIVE'));

			if (JFile::exists($communityMainFile))
			{
				$options[] = JHtml::_('select.option', '1', JText::_('COM_JTICKETING_JOMSOCIAL'));
			}

			if (JFile::exists($esMainFile))
			{
				$options[] = JHtml::_('select.option', '4', JText::_('COM_JTICKETING_EASYSOCIAL'));
			}

			if (JFile::exists($jeventsMainFile))
			{
				$options[] = JHtml::_('select.option', '3', JText::_('COM_JTICKETING_JEVENT'));
			}

			$fieldName = $name;
		}

		if ($name == 'jform[social_integration]')
		{
			$options = array();
			$options[] = JHtml::_('select.option', 'joomla', JText::_('COM_JTICKETING_INTERATION_JOOMLA'));

			if (JFile::exists($communityMainFile))
			{
				$options[] = JHtml::_('select.option', 'jomsocial', JText::_('COM_JTICKETING_INTERATION_JOMSOCIAL'));
			}

			if (JFile::exists($esMainFile))
			{
				$options[] = JHtml::_('select.option', 'EasySocial', JText::_('COM_JTICKETING_EASYSOCIAL'));
			}

			if (JFile::exists($cbMainFile))
			{
				$options[] = JHtml::_('select.option', 'cb', JText::_('COM_JTICKETING_INTERATION_CB'));
			}

			$fieldName = $name;
		}

		$html = JHtml::_('select.genericlist',  $options, $fieldName, 'class="inputbox"  ', 'value', 'text', $value, $control_name . $name);

		return $html;
	}
}
