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

/**
 * Attendee list view
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewattendee_List extends JViewLegacy
{
	/**
	 * Method to display events
	 *
	 * @param   object  $tpl  tpl
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		jimport('joomla.html.toolbar');
		global $mainframe, $option;
		$input = JFactory::getApplication()->input;
		$mainframe                  = JFactory::getApplication();
		$params     = $mainframe->getParams('com_jticketing');
		$integration = $params->get('integration');

		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// Validate user login.
		if (!$user->id)
		{
			$msg = JText::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = JUri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);
		}

		// Native Event Manager.

		if ($integration < 1)
		{
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		$option                     = $input->get('option');
		$this->jticketingmainhelper = new jticketingmainhelper;
		$layout                     = JFactory::getApplication()->input->get('layout');
		$search_event               = $mainframe->getUserStateFromRequest($option . 'search_event_list', 'search_event_list', '', 'string');
		$search_event               = JString::strtolower($search_event);
		$search_paymentstatus       = $mainframe->getUserStateFromRequest($option . 'search_paymentStatuslist', 'search_paymentStatuslist', '', 'string');
		$user                       = JFactory::getUser();
		$status_event               = array();
		$eventlist                  = $this->jticketingmainhelper->geteventnamesByCreator($user->id);
		$status_event[]             = JHtml::_('select.option', "", JText::_('SELONE_EVENT'));

		foreach ($eventlist as $key => $event)
		{
			$event_id = $event->id;
			$event_nm = $event->title;

			if ($event_nm)
			{
				$status_event[] = JHtml::_('select.option', $event_id, $event_nm);
			}
		}

		if ($layout == 'attendee_details')
		{
			$event_id               = $input->get('eventid', '', 'INT');
			$attendee_id            = $input->get('attendee_id', '', 'INT');
			$extraFieldslabel       = $this->jticketingmainhelper->extraFieldslabel($event_id, $attendee_id);
			$this->extraFieldslabel = $extraFieldslabel;

			// Get customer note
			$this->customerNote     = $this->get('CustomerNote');
		}
		else
		{
			$this->status_event = array();

			if ($status_event)
			{
				$this->status_event = $status_event;
			}

			$eventid = JRequest::getInt('event');

			if ($eventid)
			{
				$eventcreator = $this->jticketingmainhelper->getEventCreator($eventid);

				if ($user->id != $eventcreator)
				{
					$this->eventauthorisation = 0;
					echo '<b>' . JText::_('COM_JTICKETING_USER_UNAUTHORISED') . '</b>';

					return;
				}
			}

			$paymentStatus                     = array();
			$paymentStatus[]                   = JHtml::_('select.option', '0', JText::_('SEL_PAY_STATUS'));
			$paymentStatus[]                   = JHtml::_('select.option', 'P', JText::_('JT_PSTATUS_PENDING'));
			$paymentStatus[]                   = JHtml::_('select.option', 'C', JText::_('JT_PSTATUS_COMPLETED'));
			$paymentStatus[]                   = JHtml::_('select.option', 'D', JText::_('JT_PSTATUS_DECLINED'));
			$paymentStatus[]                   = JHtml::_('select.option', 'E', JText::_('JT_PSTATUS_FAILED'));
			$paymentStatus[]                   = JHtml::_('select.option', 'UR', JText::_('JT_PSTATUS_UNDERREVIW'));
			$paymentStatus[]                   = JHtml::_('select.option', 'RF', JText::_('JT_PSTATUS_REFUNDED'));
			$paymentStatus[]                   = JHtml::_('select.option', 'CRV', JText::_('JT_PSTATUS_CANCEL_REVERSED'));
			$paymentStatus[]                   = JHtml::_('select.option', 'RV', JText::_('JT_PSTATUS_REVERSED'));

			$this->search_paymentStatuslist    = $paymentStatus;
			$lists['search_paymentStatuslist'] = $search_paymentstatus;

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

			$Data            = $this->get('Data');
			$this->Data      = $Data;
			$earning         = $this->get('earning');
			$this->eventlist = $eventlist;
			$this->earning   = $earning;
			$pagination      = $this->get('Pagination');

			// Push data into the template
			$this->pagination = $pagination;
			$this->lists      = $lists;
			$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
			$filter_type      = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', 'id', 'string');
			$title              = '';
			$lists['order_Dir'] = '';
			$lists['order']     = '';
			$title = $mainframe->getUserStateFromRequest('com_jticketing' . 'title', '', 'string');

			if ($title == null)
			{
				$title = '-1';
			}

			$lists['title']      = $title;
			$lists['order_Dir']  = $filter_order_Dir;
			$lists['order']      = $filter_type;
			$lists['pagination'] = $pagination;
			$this->lists         = $lists;
		}

		if (JVERSION >= '3.0')
		{
			JHtmlBehavior::framework();
		}
		else
		{
			JHtml::_('behavior.mootools');
		}

		$this->setLayout($layout);
		parent::display($tpl);
	}

	/**
	 * Method to set toolbar
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function _setToolBar()
	{
		$document = JFactory::getDocument();
		$bar      = JToolBar::getInstance('toolbar');
		JToolBarHelper::title(JText::_('JT_SOCIAL'), 'icon-48-jticketing.png');
		JToolBarHelper::back(JText::_('JT_HOME'), 'index.php?option=com_jticketing&view=settings');
	}
}
