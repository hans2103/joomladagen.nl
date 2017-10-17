<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for list of fields.
 *
 * @package     Tjfields
 * @subpackage  com_tjfields
 * @since       2.2
 */
class TjfieldsViewFields extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		TjfieldsHelper::addSubmenu('fields');

		$this->addToolbar();

		if (JVERSION >= '3.0')
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/tjfields.php';
		$input           = jFactory::getApplication()->input;
		$client          = $input->get('client', '', 'STRING');
		$client          = explode('.', $client);
		$component_title = '';

		if (!empty($client))
		{
			switch ($client['0'])
			{
				case 'com_jticketing' :
					$component_title = JText::_('COM_JTICKETING_COMPONENT');
					JToolBarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');
					break;

				case 'com_tjlms':
					$component_title = JText::_('COM_TJLMS_COMPONENT_LABEL') . ' : ';

					$lang = JFactory::getLanguage();
					$lang->load('com_tjlms', JPATH_ADMINISTRATOR, 'en-GB', true);

					break;
			}
		}

		$state = $this->get('State');
		$canDo = TjfieldsHelper::getActions($state->get('filter.category_id'));

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title($component_title . JText::_('COM_TJFIELDS_TITLE_FIELDS'), 'list');
		}
		else
		{
			JToolBarHelper::title($component_title . JText::_('COM_TJFIELDS_TITLE_FIELDS'), 'fields.png');
		}

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/field';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('field.add', 'JTOOLBAR_NEW');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('fields.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('fields.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'fields.delete', 'JTOOLBAR_DELETE');
			}

			if (isset($this->items[0]->checked_out))
			{
				JToolBarHelper::custom('fields.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('', 'fields.delete', 'JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			}
			elseif ($canDo->get('core.edit.state'))
			{
				JToolBarHelper::trash('fields.trash', 'JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_tjfields');
		}

		$this->extra_sidebar = '';

		// Filter for the field field_type
		$select_label       = JText::sprintf('COM_TJFIELDS_FILTER_SELECT_LABEL', 'Field Type');
		$options            = array();
		$options[0]         = new stdClass;
		$options[0]->value  = "text";
		$options[0]->text   = "Text";
		$options[1]         = new stdClass;
		$options[1]->value  = "radio";
		$options[1]->text   = "Radio";
		$options[2]         = new stdClass;
		$options[2]->value  = "single_select";
		$options[2]->text   = "Single select";
		$options[3]         = new stdClass;
		$options[3]->value  = "multi_select";
		$options[3]->text   = "Multiple select";
		$options[4]         = new stdClass;
		$options[4]->value  = "hidden";
		$options[4]->text   = "Hidden";
		$options[5]         = new stdClass;
		$options[5]->value  = "textarea";
		$options[5]->text   = "Textarea";
		$options[6]         = new stdClass;
		$options[6]->value  = "checkbox";
		$options[6]->text   = "Checkbox";
		$options[7]         = new stdClass;
		$options[7]->value  = "calender";
		$options[7]->text   = "Calender";
		$options[8]         = new stdClass;
		$options[8]->value  = "editor";
		$options[8]->text   = "Editor";
		$options[9]         = new stdClass;
		$options[9]->value  = "email_field";
		$options[9]->text   = "Email";
		$options[10]        = new stdClass;
		$options[10]->value = "password";
		$options[10]->text  = "Password";
		$options[11]        = new stdClass;
		$options[11]->value = "file";
		$options[11]->text  = "File";

		if (JVERSION >= '3.0')
		{
			// Set sidebar action - New in 3.0
			$action = 'index.php?option=com_tjfields&view=fields&client="' . $input->get('client', '', 'STRING') . '"';
			JHtmlSidebar::setAction($action);
			$filter_type = $this->state->get('filter.type');
			JHtmlSidebar::addFilter($select_label, 'filter_field_type', JHtml::_('select.options', $options, "value", "text", $filter_type, true));
			$filter_state = $this->state->get('filter.state');
			$pub_text = JText::_('JOPTION_SELECT_PUBLISHED');
			$publish_opt = JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $filter_state, true);
			JHtmlSidebar::addFilter($pub_text, 'filter_published', $publish_opt);
		}
	}

	/**
	 * Add the sort
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function getSortFields()
	{
		return array(
			'a.id' => JText::_('JGRID_HEADING_ID'),
			'a.label' => JText::_('COM_TJFIELDS_FIELDS_LABEL'),
			'a.type' => JText::_('COM_TJFIELDS_FIELDS_FIELD_TYPE'),
			'a.state' => JText::_('JSTATUS'),
			'a.client' => JText::_('COM_TJFIELDS_FIELDS_CLIENT')
		);
	}
}
