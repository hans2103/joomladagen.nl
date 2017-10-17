<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * View for events
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewEvents extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $params;

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
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// Default layout is default.
		$this->layout = JFactory::getApplication()->input->get('layout', 'default');
		$this->setLayout($this->layout);

		if ($this->layout == 'my')
		{
			// Validate user login.
			if (empty($user->id))
			{
				$msg = JText::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

				// Get current url.
				$current = JUri::getInstance()->toString();
				$url     = base64_encode($current);
				$app->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);
			}
		}

		$this->state      = $this->get('State');

		$events_to_show = $app->input->get('events_to_show');
		$model = $this->getModel('events');
		$model->setState('events_to_show', $events_to_show);

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params     = $app->getParams('com_jticketing');
		$this->dateFormat = $this->params->get('date_format_show');

		// Get integration set
		$this->integration = $this->params->get('integration', '', 'INT');

		// Native Event Manager.
		if ($this->integration != 2)
		{
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COMJTICKETING_INTEGRATION_NATIVE_NOTICE');?>
			</div>
		<?php
		return false;
		}

		// Get ordering filters
		$this->filter_order     = $this->escape($this->state->get('list.ordering'));
		$this->filter_order_Dir = $this->escape($this->state->get('list.direction'));

		// Get itemid.
		$this->jticketingmainhelper = $jticketingmainhelper       = new jticketingmainhelper;
		$this->create_event_itemid  = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=eventform');
		$this->event_details_itemid = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=event');

		// Get itemid
		$this->singleEventItemid = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=events&layout=default');

		if (empty($this->singleEventItemid))
		{
			$this->singleEventItemid = JFactory::getApplication()->input->get('Itemid');
		}

		$this->myEventsItemid     = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=events&layout=my');
		$this->allEventsItemid    = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=events&layout=default');
		$this->createEventsItemid = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=eventform');
		$this->buyTicketItemId = $jticketingmainhelper->getItemId('index.php?option=com_jticketing&view=order&layout=default');

		// Category fillter
		$jteventHelper        = new jteventHelper;
		$this->cat_options    = $jteventHelper->getEventCategories();
		$this->event_type    = $jteventHelper->getEventType();
		$events_to_show       = $jteventHelper->eventsToShowOptions();
		$this->events_to_show = $events_to_show;

		// Get filter value and set list
		$filter_event_cat           = $app->getUserStateFromRequest('com_jticketing.filter_events_cat', 'filter_events_cat', '', 'INT');
		$lists['filter_events_cat'] = $filter_event_cat;

		// Ordering option
		$default_sort_by_option = $this->params->get('default_sort_by_option');
		$filter_order_Dir = $this->params->get('filter_order_Dir');
		$filter_order     = $app->getUserStateFromRequest('com_jticketing.filter_order', 'filter_order', $default_sort_by_option, 'string');
		$filter_order_Dir = $app->getUserStateFromRequest('com_jticketing.filter_order_Dir', 'filter_order_Dir', $filter_order_Dir, 'string');
		$this->ordering_options           = $this->get('OrderingOptions');
		$this->ordering_direction_options = $this->get('OrderingDirectionOptions');

		// Get creator and location filter
		$filter_creator = $app->getUserStateFromRequest('com_jticketing' . 'filter_creator', 'filter_creator');
		$this->creator  = $this->get('Creator');
		$filter_location = $app->getUserStateFromRequest('com_jticketing' . 'filter_location', 'filter_location');
		$this->location  = $this->get('Location');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->online_events = array();
		$this->online_events[] = JHtml::_('select.option', '', JText::_('COM_JTK_FILTER_SELECT_EVENT_DEFAULT'));
		$this->online_events[] = JHtml::_('select.option', '0', JText::_('COM_JTK_FILTER_SELECT_EVENT_OFFLINE'));
		$this->online_events[] = JHtml::_('select.option', '1',  JText::_('COM_JTK_FILTER_SELECT_EVENT_ONLINE'));

		foreach ($this->online_events as $value)
		{
			$online_event = $value->text;
		}

		// Set all filters in list
		$lists['filter_order']     = $filter_order;
		$lists['filter_order_Dir'] = $filter_order_Dir;
		$lists['filter_creator']   = $filter_creator;
		$lists['filter_location']  = $filter_location;
		$lists['online_events']  = $online_event;
		$this->lists = $lists;

		// Search and filter
		$filter_state            = $app->getUserStateFromRequest('com_jticketing' . 'search', 'search', '', 'string');
		$filter_events_to_show   = $app->getUserStateFromRequest('com_jticketing' . 'events_to_show', 'events_to_show');
		$lists['search']         = $filter_state;
		$lists['events_to_show'] = $filter_events_to_show;
		$this->jt_itemid         = $this->assignRef('lists', $lists);
		$this->assignRef('date', $date);

		// Escape strings for HTML output.
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Method to display events
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title, we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			if ($this->layout == "my")
			{
				$this->params->def('page_heading', JText::_('COM_JTICKETING_EVENTS_PAGE_HEADING_MY'));
			}
			else
			{
				$this->params->def('page_heading', JText::_('COM_JTICKETING_EVENTS_PAGE_HEADING'));
			}
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
