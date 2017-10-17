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
require_once JPATH_SITE . '/components/com_jticketing/helpers/order.php';

/**
 * Event creation form
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JticketingViewEventform extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

	protected $form_extra;

	protected $params;

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
		$app   = JFactory::getApplication();
		$user  = JFactory::getUser();
		$input = JFactory::getApplication()->input;
		$jticketingmainhelper = new jticketingmainhelper;
		$this->com_params = JComponentHelper::getParams('com_jticketing');
		$this->enableOnlineVenues = $this->com_params->get('enable_online_events');
		$this->accessLevel = $this->com_params->get('show_access_level');
		$JticketingCommonHelper = new JticketingCommonHelper;
		$this->vendorCheck = $JticketingCommonHelper->checkVendor();
		$this->com_params = JComponentHelper::getParams('com_jticketing');
		$this->enforceVendor = $this->com_params->get('enforce_vendor');
		$JticketingOrdersHelper = new JticketingOrdersHelper;

		$this->checkGatewayDetails  = $JticketingOrdersHelper->checkGatewayDetails($user->id);

		// Validate user login.
		if (!$user->id)
		{
			$msg = JText::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = JUri::getInstance()->toString();
			$url     = base64_encode($current);
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);
		}

		// Get the view data.
		$this->state  = $this->get('State');
		$this->item   = $this->get('Item');

		$this->params = $app->getParams('com_jticketing');
		$this->form   = $this->get('Form');
		$this->onlineEvents = $this->params->get('enable_online_events', '0');
		$this->enforceVendor = $this->params->get('enforce_vendor', '0');
		$this->mediaSize = $this->params->get('jticketing_media_size', '15');
		$this->adminApproval = $this->params->get('event_approval');

		// Event detail view resized image setting
		$this->eventMainImage = $this->params->get('front_event_detail_view', 'media_s');

		// Event detail view resized image setting
		$this->eventGalleryImage = $this->params->get('front_event_gallery_view', 'media_s');

		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'venueform');
		$jticketingModelVenueform = JModelLegacy::getInstance('Venueform', 'JTicketingModel');
		$this->venueDetails = $jticketingModelVenueform->getItem($this->item->venue);
		$this->venueName = $this->venueDetails->name;
		$this->venueId = $this->item->venue;

		$frontEndHelper = new Jticketingfrontendhelper;
		$attendeeGlobalFields = $frontEndHelper->getGlobalAtendeeFields();
		$this->attendeeList = $attendeeGlobalFields;

		if (!empty($this->item))
		{
			$input  = JFactory::getApplication()->input;
			$input->set("content_id", $this->item->id);
			$this->form_extra = array();

			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'eventform');
			$jTicketingModelEventForm = JModelLegacy::getInstance('EventForm', 'JTicketingModel');

			// The function getFormExtra is defined in Tj-fields filterFields trait.
			$this->form_extra = $jTicketingModelEventForm->getFormExtra(
						array("category" => $this->item->catid,
							"clientComponent" => 'com_jticketing',
							"client" => 'com_jticketing.event',
							"view" => 'event',
							"layout" => 'edit')
							);

			$this->form_extra_fields = array_filter($this->form_extra);
		}

		if (!empty($this->item->id))
		{
			$model = $this->getModel('eventform');
			$this->datas = $model->getvenuehtml($this->item);
		}

		// Get integration set.
		$this->integration = $this->params->get('integration', '', 'INT');
		$this->collect_attendee_info_checkout = $this->params->get('collect_attendee_info_checkout');
		$this->googleMapApiKey = $this->params->get('google_map_api_key');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		if (empty($this->item->id))
		{
			$authorised = $user->authorise('core.create', 'com_jticketing');
		}
		else
		{
			$authorised_own = $user->authorise('core.edit.own', 'com_jticketing');

			if ($authorised_own)
			{
				$authorised = true;

				// Check if logged in user is event created_by.
				if ($this->item->created_by != $user->id)
				{
					$authorised = false;
				}
			}
		}

		if ($authorised !== true)
		{
			JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		// Added by Sagar.
		$eventid = $input->get('id', '', 'INT');
		$this->jticketingfrontendhelper = new jticketingfrontendhelper;

		if ($this->collect_attendee_info_checkout)
		{
			$this->custom_fields = $this->jticketingfrontendhelper->getAllfields($eventid);
		}

		if ($eventid)
		{
			// Added by aniket for ticket tyopes
			$this->ticket_types = $jticketingmainhelper->getTicketTypes($eventid);
		}

		// Escape strings for HTML output.
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepare document
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();
		$title	= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_JTICKETING_FORM_EVENT_HEADING_CREATE'));
		}

		// Added by Manoj - start.
		if (!empty($this->item->id))
		{
			$this->params->def('page_heading', JText::_('COM_JTICKETING_FORM_EVENT_HEADING_EDIT') . '-' . $this->item->title);
		}
		// Added by Manoj - end.

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
