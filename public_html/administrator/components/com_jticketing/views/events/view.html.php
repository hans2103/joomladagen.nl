<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jticketing
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

// Import Csv export button
jimport('techjoomla.tjtoolbar.button.csvexport');

/**
 * View class for a list of Jticketing.
 *
 * @since  1.6
 */
class JticketingViewEvents extends JViewLegacy
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
		$input            = JFactory::getApplication()->input;
		$layout        	= $input->get('layout');
		$params = JComponentHelper::getParams('com_jticketing');
		$integration = $params->get('integration');

		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->issite = 0;

		JLoader::import('main', JPATH_SITE . '/components/com_jticketing/helpers');
		$jticketingMainHelper       = new Jticketingmainhelper;
		$this->singleEventItemid = $jticketingMainHelper->getItemId('index.php?option=com_jticketing&view=event');

		// Get filter form.
		$this->filterForm = $this->get('FilterForm');

		// Get active filters.
		$this->activeFilters = $this->get('ActiveFilters');

		// Native Event Manager.
		if ($integration < 1)
		{
			$this->sidebar = JHtmlSidebar::render();
			JToolBarHelper::preferences('com_jticketing');
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		if ($layout == 'tickettypes')
		{
			$this->ticketypes      = $this->get('TicketTypes');
		}

		$this->pagination = $this->get('Pagination');

		$this->params = JComponentHelper::getParams('com_jticketing');

		// Get integration set.
		$this->integration = $this->params->get('integration', '', 'INT');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JticketingHelper::addSubmenu('events');

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
		require_once JPATH_COMPONENT . '/helpers/jticketing.php';

		$state = $this->get('State');
		$canDo = JticketingHelper::getActions($state->get('filter.category_id'));

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_('COM_JTICKETING_COMPONENT') . JText::_('COM_JTICKETING_TITLE_EVENTS'), 'list');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_JTICKETING_COMPONENT') . JText::_('COM_JTICKETING_TITLE_EVENTS'), 'events.png');
		}

			JToolBarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');

		// Check if the form exists before showing the add/edit buttons.
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/event';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('event.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('event.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('events.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('events.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
			elseif (isset($this->items[0]))
			{
				// If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'events.delete', 'JTOOLBAR_DELETE');
			}

			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::archiveList('events.archive', 'JTOOLBAR_ARCHIVE');
			}

			if (isset($this->items[0]->checked_out))
			{
				JToolBarHelper::custom('events.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		// Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state))
		{
			if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'events.delete', 'JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			}
			elseif ($canDo->get('core.edit.state'))
			{
				JToolBarHelper::trash('events.trash', 'JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}

			$bar = JToolBar::getInstance('toolbar');
			$buttonImport = '<a href="#import_events" class="btn ImportButton modal" rel="{size: {x: 800, y: 170}, ajaxOptions: {method: &quot;get&quot;}}">
			<span class="icon-upload icon-white"></span>' . JText::_('COMJTICKETING_EVENT_IMPORT_CSV') . '</a>';
			$bar->appendButton('Custom', $buttonImport);

			$message = array();
			$message['success'] = JText::_("COM_JTICKETING_EXPORT_FILE_SUCCESS");
			$message['error'] = JText::_("COM_JTICKETING_EXPORT_FILE_ERROR");
			$message['inprogress'] = JText::_("COM_JTICKETING_EXPORT_FILE_NOTICE");

			if (!empty($this->items))
			{
				$bar->appendButton('CsvExport',  $message);
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_jticketing');
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.state' => JText::_('COM_JTICKETING_EVENTS_PUBLISHED'),
			'a.title' => JText::_('COM_JTICKETING_EVENTS_TITLE'),
			'a.catid' => JText::_('COM_JTICKETING_EVENTS_CATEGORY_ID'),
			'a.created_by' => JText::_('COM_JTICKETING_EVENTS_CREATOR'),
			'a.startdate' => JText::_('COM_JTICKETING_EVENTS_STARTDATE'),
			'a.enddate' => JText::_('COM_JTICKETING_EVENTS_ENDDATE'),
			'a.location' => JText::_('COM_JTICKETING_EVENTS_LOCATION'),
			'a.featured' => JText::_('COM_JTICKETING_EVENTS_FEATURED'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
