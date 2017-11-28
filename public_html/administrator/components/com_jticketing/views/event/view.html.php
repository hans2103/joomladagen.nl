<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_jticketing
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;
require_once JPATH_SITE . "/components/com_tjfields/filterFields.php";

/**
 * jticketing View
 *
 * @since  0.0.1
 */
class JTicketingViewEvent extends JViewLegacy
{
	use TjfieldsFilterField;

	/**
	 * View form
	 *
	 * @var         form
	 */
	public $form = null;

	/**
	 * Display the Hello World view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// Get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
		JticketingHelper::getLanguageConstant();
		$this->com_params = JComponentHelper::getParams('com_jticketing');
		$this->enforceVendor = $this->com_params->get('enforce_vendor');
		$this->accessLevel = $this->com_params->get('show_access_level');
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jticketing/models', 'venue');
		$JTicketingModelVenue = JModelLegacy::getInstance('Venue', 'JTicketingModel');
		$this->venueDetails = $JTicketingModelVenue->getItem($item->venue);
		$this->mediaSize = $this->com_params->get('jticketing_media_size', '15');
		$this->enableOnlineVenues = $this->com_params->get('enable_online_events');
		$this->venueName = $this->venueDetails->name;
		$this->venueId = $item->venue;
		$frontEndHelper = new Jticketingfrontendhelper;
		$attendeeGlobalFields = $frontEndHelper->getGlobalAtendeeFields();
		$this->attendeeList = $attendeeGlobalFields;
		$adaptivePayment = $this->com_params->get('gateways');
		$this->arra_check = in_array('adaptive_paypal', $adaptivePayment);

		if (!empty($item))
		{
			$input  = JFactory::getApplication()->input;
			$input->set("content_id", $item->id);
			$this->form_extra = array();

			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jticketing/models', 'eventform');
			$jTicketingModelEventForm = JModelLegacy::getInstance('EventForm', 'JTicketingModel');

			// The function getFormExtra is defined in Tj-fields filterFields trait.
			$this->form_extra = $jTicketingModelEventForm->getFormExtra(
						array("category" => $item->catid,
							"clientComponent" => 'com_jticketing',
							"client" => 'com_jticketing.event',
							"view" => 'event',
							"layout" => 'edit')
							);

			$this->form_extra = array_filter($this->form_extra);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}

		// Assign the Data
		$this->form = $form;
		$this->item = $item;
		$this->params = JComponentHelper::getParams('com_jticketing');
		$this->googleMapApiKey = $this->params->get('google_map_api_key');
		$this->collect_attendee_info_checkout = $this->params->get('collect_attendee_info_checkout');

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolBar()
	{
		$input = JFactory::getApplication()->input;

		// Hide Joomla Administrator Main menu
		$input->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);

		if ($isNew)
		{
			$title = JText::_('COM_JTICKETING_MANAGER_JTICKETING_NEW');
		}
		else
		{
			$title = JText::_('COM_JTICKETING_MANAGER_JTICKETING_EDIT');
		}

		JToolBarHelper::title($title, 'event');
		JToolBarHelper::apply('event.apply');
		JToolBarHelper::save('event.save');
		JToolBarHelper::save2new('event.save2new');

		JToolBarHelper::cancel(
			'event.cancel',
			$isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE'
		);
	}
}
