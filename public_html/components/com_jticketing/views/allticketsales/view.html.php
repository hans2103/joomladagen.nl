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
class JticketingViewallticketsales extends JViewLegacy
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
		$input = JFactory::getApplication()->input;
		$this->jticketingmainhelper = new jticketingmainhelper;
		$mainframe = JFactory::getApplication();
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

		$option = $input->get('option');
		$search_event = $mainframe->getUserStateFromRequest($option . 'search_event', 'search_event', '', 'string');
		$search_event = JString::strtolower($search_event);
		$user = JFactory::getUser();
		$status_event = array();
		$eventlist = $this->jticketingmainhelper->geteventnamesByCreator($user->id);
		$status_event[] = JHtml::_('select.option', '', JText::_('SELONE_EVENT'));

		if (!empty($eventlist))
		{
			foreach ($eventlist as $key => $event)
			{
				$event_id = $event->id;
				$event_nm = $event->title;

				if ($event_nm)
				{
					$status_event[] = JHtml::_('select.option', $event_id, $event_nm);
				}
			}
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

		$this->status_event = $status_event;

		$this->user_filter_options = $this->get('UserFilterOptions');

		$user_filter = $mainframe->getUserStateFromRequest('com_jticketing' . 'user_filter', 'user_filter');

		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$filter_type = $mainframe->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', 'id', 'string');

		$lists['search_event'] = $search_event;

		$Data = $this->get('Data');
		$pagination = $this->get('Pagination');

		$Itemid = $input->get('Itemid');

		if (empty($Itemid))
		{
			$Session = JFactory::getSession();
			$Itemid = $Session->get("JT_Menu_Itemid");
		}

		$this->Data = $Data;
		$this->pagination = $pagination;
		$this->lists = $lists;
		$this->Itemid = $Itemid;
		$this->status_event = $status_event;
		$title = '';
		$lists['order_Dir'] = '';
		$lists['order'] = '';
		$title = $mainframe->getUserStateFromRequest('com_jticketing' . 'title', '', 'string');

		if ($title == null)
		{
			$title = '-1';
		}

		$lists['title'] = $title;
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order'] = $filter_type;
		$lists['pagination'] = $pagination;

		$lists['user_filter'] = $user_filter;
		$this->lists = $lists;

		parent::display($tpl);
	}
}
