<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 *
 * @since  1.8
 */
class JticketingViewVenue extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template name
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');
		$com_params = JComponentHelper::getParams('com_jticketing');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Get component params
		$this->params             = JComponentHelper::getParams('com_jticketing');
		$this->googleMapApiKey    = $this->params->get('google_map_api_key');

		if (!empty($this->googleMapApiKey))
		{
			$this->googleMapLink      = 'https://maps.googleapis.com/maps/api/js?libraries=places&key=' . $this->googleMapApiKey;
		}

		$this->EnableOnlineEvents = $this->params->get('enable_online_events');

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user    = JFactory::getUser();
		$isNew   = ($this->item->id == 0);

		if ($isNew)
		{
			$viewTitle = JText::_('COM_JTICKETING_TITLE_VENUES');
		}
		else
		{
			$viewTitle = JText::_('COM_JTICKETING_TITLE_VENUES');
		}

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo = JTicketingHelper::getActions();
		JToolBarHelper::title($viewTitle, 'pencil-2');

		// If not checked out, can save the item.
		JToolBarHelper::apply('venue.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('venue.save', 'JTOOLBAR_SAVE');

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::custom('venue.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		if (empty($this->item->id))
		{
			JToolBarHelper::cancel('venue.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel('venue.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
