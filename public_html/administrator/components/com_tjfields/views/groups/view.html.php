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
 * View class for editing group.
 *
 * @package     Tjfields
 * @subpackage  com_tjfields
 * @since       2.2
 */
class TjfieldsViewGroups extends JViewLegacy
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

		TjfieldsHelper::addSubmenu('groups');
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
		$input = jFactory::getApplication()->input;
		require_once JPATH_COMPONENT . '/helpers/tjfields.php';
		$client          = $input->get('client', '', 'STRING');

		$component_title = '';

		if (!empty($client))
		{
			$client = explode('.', $client);

			switch ($client['0'])
			{
				case 'com_jticketing' :
					$component_title = JText::_('COM_JTICKETING_COMPONENT');
					break;
				case 'com_tjlms':
					$component_title = JText::_('COM_TJLMS_COMPONENT');
					break;
			}
		}

		if (!empty($client) and $client['0'] == 'com_jticketing')
		{
			JToolBarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');
		}

		$state = $this->get('State');
		$canDo = TjfieldsHelper::getActions($state->get('filter.category_id'));
		JToolBarHelper::title($component_title . JText::_('COM_TJFIELDS_TITLE_GROUPS'), 'list.png');

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/group';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('group.add', 'JTOOLBAR_NEW');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('groups.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('groups.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'groups.delete', 'JTOOLBAR_DELETE');
			}

			if (isset($this->items[0]->checked_out))
			{
				JToolBarHelper::custom('groups.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('', 'groups.delete', 'JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			}
			elseif ($canDo->get('core.edit.state'))
			{
				JToolBarHelper::trash('groups.trash', 'JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_tjfields');
		}

		$this->extra_sidebar = '';

		if (JVERSION >= '3.0')
		{
			// Set sidebar action - New in 3.0
			$filter_state = $this->state->get('filter.state');
			$pub_text = JText::_('JOPTION_SELECT_PUBLISHED');
			$publish_opt = JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $filter_state, true);
			JHtmlSidebar::setAction('index.php?option=com_tjfields&view=groups');
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
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.state' => JText::_('JSTATUS'),
			'a.created_by' => JText::_('COM_TJFIELDS_GROUPS_CREATED_BY'),
			'a.name' => JText::_('COM_TJFIELDS_GROUPS_NAME'),
			'a.client' => JText::_('COM_TJFIELDS_GROUPS_CLIENT'),
			'a.client_type' => JText::_('COM_TJFIELDS_GROUPS_CLIENT_TYPE')
		);
	}
}
