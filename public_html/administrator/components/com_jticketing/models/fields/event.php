<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Import Joomla modelitem library
jimport('joomla.application.component.modelitem');

jimport('joomla.form.formfield');

/**
 * JFormFieldModal_Single event class.
 *
 * @package  JTicketing
 * @since    1.8
 */
class JFormFieldModal_Event extends JFormField
{
	/**
	 * field type
	 * @var string
	 */
	protected $type = 'Modal_Event';

	/**
	 * Method to get the field input markup
	 *
	 * @return  Array
	 *
	 * @since   1.0
	 */
	protected function getInput()
	{
		// Load modal behavior
		JHtml::_('behavior.modal', 'a.modal');

		// Build the script
		$script   = array();
		$script[] = '    function jSelectBook_' . $this->id . '(id, title, object) {';
		$script[] = '        document.id("' . $this->id . '_id").value = id;';
		$script[] = '        document.id("' . $this->id . '_name").value = title;';
		$script[] = '        SqueezeBox.close();';
		$script[] = '    }';

		// Add to document head
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Setup variables for display
		$html = array();
		$link = 'index.php?option=com_jticketing&amp;view=events&amp;layout=modal' . '&amp;tmpl=component&amp;function=jSelectBook_' .
		$this->id;

		$db    = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('e.title');
		$query->from($db->quoteName('#__jticketing_integration_xref', 'i'));
		$query->join('LEFT', $db->quoteName('#__jticketing_events', 'e') . ' ON (' . $db->quoteName('i.eventid') . ' = ' . $db->quoteName('e.id') . ')');
		$query->where($db->quoteName('i.source') . ' = ' . $db->quote("com_jticketing"));
		$db->setQuery($query);
		$title = $db->loadResult();

		if (empty($title))
		{
			$title = JText::_('COM_JTICKETING_FIELD_SELECT_EVENT');
		}

		// The current book input field
		$html[] = '<div class="">';
		$html[] = '  <input type="text" id="' . $this->id . '_name" value="' . $title . '" disabled="disabled" size="35" />';
		$html[] = '</div>';

		// The book select button
		$html[] = '<div class="button2-left">';
		$html[] = '  <div class="blank">';
		$html[] = '    <a class="modal" title="' .
		JText::_('COM_JTICKETING_SELECT_EVENT_TITLE') . '" href="' .
		$link . '" rel="{handler: \'iframe\', size: {x:800, y:450}}">' . JText::_('COM_JTICKETING_SELECT_CHANGE') . '</a>';
		$html[] = '  </div>';
		$html[] = '</div>';

		// The active book id field
		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		// Class='required' for client side validation
		$class = '';

		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}
}
