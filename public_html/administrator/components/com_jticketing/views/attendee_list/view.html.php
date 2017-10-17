<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

// Import Csv export button
jimport('techjoomla.tjtoolbar.button.csvexport');

/**
 * Attendee List form
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewattendee_List extends JViewLegacy
{
	/**
	 * Display view
	 *
	 * @param   STRING  $tpl  template name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$bar = JToolBar::getInstance('toolbar');
		global $mainframe, $option;
		$mainframe            = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_jticketing');
		$integration = $params->get('integration');
		$this->state      = $this->get('State');

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

		$input                = JFactory::getApplication()->input;
		$layout               = JFactory::getApplication()->input->get('layout', 'default');
		$event_id             = $input->get('eventid', '', 'INT');
		$jticketingmainhelper = new jticketingmainhelper;
		JToolBarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');

		if ($layout == 'change_assinee')
		{
			$modelPath               = JPATH_ROOT . '/components/com_jticketing/models/fields/jtcategories.php';
			$options = array();

			if (!class_exists('JFormFieldJtcategories'))
			{
				JLoader::register('JFormFieldJtcategories', $modelPath);
				JLoader::load('JFormFieldJtcategories');
			}

			$JFormFieldJtcategories = new JFormFieldJtcategories;
			$eventid = $input->get('eventid', '', 'INT');
			$helperPath               = JPATH_ROOT . '/components/com_jticketing/helpers/event.php';
			$options = array();

			if (!class_exists('jteventHelper'))
			{
				JLoader::register('jteventHelper', $helperPath);
				JLoader::load('jteventHelper');
			}

			$defoptions[] = JHtml::_('select.option', "0", JText::_('COM_JTICKETING_SELECT_CATEGORY'));
			$event_option[] = JHtml::_('select.option', "0", JText::_('COM_JTICKETING_SELECT_EVENT_ASIGNEE'));
			$ticket_type_option[] = JHtml::_('select.option', "0", JText::_('COM_JTICKETING_SELECT_TICKET_TYPE_ASIGNEE'));
			$jteventHelper = new jteventHelper;
			$this->cat_options = $jteventHelper->getEventCategories();
			$this->cat_options = array_merge($defoptions, $this->cat_options);
			$this->event_option = $event_option;
			$this->ticket_type_option = $ticket_type_option;
		}
		elseif ($layout == 'attendee_details')
		{
			$attendee_id            = $input->get('attendee_id', '', 'INT');
			$extraFieldslabel       = $jticketingmainhelper->extraFieldslabel($event_id, $attendee_id);
			$this->extraFieldslabel = $extraFieldslabel;

			// Get customer note
			$this->customerNote = $this->get('CustomerNote');
		}

		elseif ($layout == 'contactus')
		{
			$session = JFactory::getSession();
			$selected_order_item_ids = $session->get('selected_order_item_ids');
			$this->selected_emails = $this->getModel()->getAttendeeEmail($selected_order_item_ids);
			$this->_setToolBar();
		}
		else
		{
			JToolbarHelper::divider();
			JToolBarHelper::custom('attendee_list.checkin', 'publish.png', '', JText::_('COM_JTICKETING_CHECKIN_SUCCESS'));
			JToolBarHelper::custom('attendee_list.undochekin', 'unpublish.png', '', JText::_('COM_JTICKETING_CHECKIN_FAIL'));
			JToolBarHelper::custom('attendee_list.redirectforEmail', 'mail.png', '', JText::_('COM_JTICKETING_EMAIL_TO_ALL_SELECTED_PARTICIPANT'));
			$option               = $input->get('option');
			$search_event         = $mainframe->getUserStateFromRequest($option . 'search_event_list', 'search_event_list', $event_id, 'string');
			$search_event         = JString::strtolower($search_event);
			$search_paymentstatus = $mainframe->getUserStateFromRequest($option . 'search_paymentStatuslist', 'search_paymentStatuslist', '', 'string');
			$status_event         = array();

			$eventlist = $jticketingmainhelper->geteventnamesByCreator();
			$status_event[] = JHtml::_('select.option', '0', JText::_('SELONE_EVENT'));

			foreach ($eventlist as $key => $event)
			{
				$event_id = $event->id;
				$event_nm = $event->title;
				/*if ($event->short_description)
				{
					$event->title = "".$event->short_description. " ".$event->title;
				}
				if ($event->start_date)
				{
					$event->start_date = JFactory::getDate($event->start_date)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM'));
					$event->title = $event->title.JText::_('COM_JTICKETING_FROM').$event->start_date;
				}

				if ($event->end_date)
				{
					$event->end_date = JFactory::getDate($event->end_date)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM'));
					$event->title =$event->title.JText::_('COM_JTICKETING_TO').$event->end_date;
				}*/

				if ($event_nm)
				{
					$status_event[] = JHtml::_('select.option', $event_id, $event->title);
				}
			}

			$this->status_event = $status_event;
			$paymentStatus      = array();
			$paymentStatus[]    = JHtml::_('select.option', '0', JText::_('SEL_PAY_STATUS'));
			$paymentStatus[]    = JHtml::_('select.option', 'P', JText::_('JT_PSTATUS_PENDING'));
			$paymentStatus[]    = JHtml::_('select.option', 'C', JText::_('JT_PSTATUS_COMPLETED'));
			$paymentStatus[]    = JHtml::_('select.option', 'D', JText::_('JT_PSTATUS_DECLINED'));
			$paymentStatus[]    = JHtml::_('select.option', 'E', JText::_('JT_PSTATUS_FAILED'));
			$paymentStatus[]    = JHtml::_('select.option', 'UR', JText::_('JT_PSTATUS_UNDERREVIW'));
			$paymentStatus[]    = JHtml::_('select.option', 'RF', JText::_('JT_PSTATUS_REFUNDED'));
			$paymentStatus[]    = JHtml::_('select.option', 'CRV', JText::_('JT_PSTATUS_CANCEL_REVERSED'));
			$paymentStatus[]    = JHtml::_('select.option', 'RV', JText::_('JT_PSTATUS_REVERSED'));

			// Payment statuses
			$this->search_paymentStatuslist    = $paymentStatus;
			$lists['search_paymentStatuslist'] = $search_paymentstatus;
			$eventid = $input->get('eventid', '', 'INT');

			if ($search_event)
			{
				$lists['search_event_list'] = $search_event;
			}
			elseif ($eventid)
			{
				$lists['search_event_list'] = $eventid;
			}
			else
			{
				$lists['search_event_list'] = '';
			}

			$Data                              = $this->get('Items');
			$earning                           = $this->get('earning');
			$this->eventlist                   = $eventlist;
			$this->earning                     = $earning;
			$pagination                        = $this->get('Pagination');

			// Push data into the template
			$this->Data         = $Data;
			$this->pagination   = $pagination;
			$this->lists        = $lists;
			$filter_order_Dir   = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
			$filter_type        = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', 'id', 'string');
			$title              = '';
			$lists['order_Dir'] = '';
			$lists['order']     = '';

			$title = $mainframe->getUserStateFromRequest('com_jticketing' . 'title', '', 'string');

			if ($title == null)
			{
				$title = '-1';
			}

			$bar = JToolBar::getInstance('toolbar');
			$buttonImport = '<a href="#import_events" class="btn ImportButton modal" rel="{size: {x: 800, y: 250}, ajaxOptions: {method: &quot;get&quot;}}">
			<span class="icon-upload icon-white"></span>' . JText::_('COMJTICKETING_EVENT_IMPORT_CSV') . '</a>';
			$bar->appendButton('Custom', $buttonImport);

			$message = array();
			$message['success'] = JText::_("COM_JTICKETING_EXPORT_FILE_SUCCESS");
			$message['error'] = JText::_("COM_JTICKETING_EXPORT_FILE_ERROR");
			$message['inprogress'] = JText::_("COM_JTICKETING_EXPORT_FILE_NOTICE");

			if (!empty($this->Data))
			{
				$bar->appendButton('CsvExport',  $message);
			}

			$lists['title']     = $title;
			$lists['order_Dir'] = $filter_order_Dir;
			$lists['order']     = $filter_type;

			$this->lists = $lists;

			if (JVERSION >= '3.0')
			{
				JHtmlBehavior::framework();
			}
			else
			{
				JHtml::_('behavior.mootools');
			}

			$JticketingHelper = new JticketingHelper;
			$JticketingHelper->addSubmenu('attendee_list');
			$this->_setToolBar($eventlist);

			if (JVERSION >= '3.0')
			{
				$this->sidebar = JHtmlSidebar::render();
			}
		}

		$this->setLayout($layout);
		parent::display($tpl);
	}

	/**
	 * Display toolbar
	 *
	 * @param   STRING  $eventlist  template name
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function _setToolBar($eventlist='')
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::base() . 'components/com_jticketing/css/jticketing.css');
		$bar = JToolBar::getInstance('toolbar');

		JToolBarHelper::title(JText::_('COM_JTICKETING_COMPONENT') . JText::_('COM_JTICKETING_ATTENDEES_LIST'), 'list');

		$layout = JFactory::getApplication()->input->get('layout', 'default');

		if ($layout == 'default')
		{
			/*if (JVERSION >= 3.0)
			{
				JHtmlSidebar::setAction('index.php?option=com_jticketing');
				$pstatus_ev = JHtml::_('select.options', $this->status_event, 'value', 'text', $this->lists['search_event_list'], true);
				JHtmlSidebar::addFilter(JText::_('SELONE_EVENT'), 'search_event_list', $pstatus_ev);
				$pstatus_a = JHtml::_('select.options', $this->search_paymentStatuslist, 'value', 'text', $this->lists['search_paymentStatuslist'], true);
				JHtmlSidebar::addFilter(JText::_('SEL_PAY_STATUS'), 'search_paymentStatuslist', $pstatus_a);
			}*/
		}
		elseif ($layout == 'contactus')
		{
			JFactory::getApplication()->input->set('hidemainmenu', true);
			JToolbarHelper::title(JText::_('COM_JTICKETING_SEND_EMAIL'), 'jticketing email');
			JToolbarHelper::custom('attendee_list.emailtoSelected', 'envelope.png', 'send_f2.png', 'COM_JTICKETING_SEND_MAIL', false);
			JToolbarHelper::cancel('attendee_list.cancelEmail');
		}

		JToolBarHelper::preferences('com_jticketing');
	}
}
