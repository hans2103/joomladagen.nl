<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 *
 * @since  1.6
 */
class JticketingViewVenueform extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

	protected $params;

	protected $canSave;

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

		$this->com_params = JComponentHelper::getParams('com_jticketing');
		$this->enforceVendor = $this->com_params->get('enforce_vendor');
		$JticketingCommonHelper = new JticketingCommonHelper;
		$this->vendorCheck = $JticketingCommonHelper->checkVendor();
		$this->state   = $this->get('State');
		$this->item    = $this->get('Item');
		$this->params  = $app->getParams('com_jticketing');
		$this->canSave = $this->get('CanSave');
		$this->form    = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		if (empty($this->item->id))
		{
			$authorised = $user->authorise('core.create', 'com_jticketing');
		}
		else
		{
			$authorisedOwn = $user->authorise('core.edit.own', 'com_jticketing');

			if ($authorisedOwn)
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

		// Get component params
		$this->params             = JComponentHelper::getParams('com_jticketing');
		$this->googleMapApiKey    = $this->params->get('google_map_api_key');
		$this->integration        = $this->params->get('integration');

		// Data: {element:element,venue_id:jQuery("[name='jform[id]']").val()},
		if (!empty($this->googleMapApiKey))
		{
			$this->googleMapLink = 'https://maps.googleapis.com/maps/api/js?libraries=places&key=' . $this->googleMapApiKey;
		}

		$this->EnableOnlineEvents = $this->params->get('enable_online_events');

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	/*protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		Because the application sets a default page title,
		we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_JTICKETING_FORM_EVENT_HEADING_CREATE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
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
	}*/
	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function addToolbar()
	{
		JLoader::register('JToolBarHelper', JPATH_ADMINISTRATOR . '/includes/toolbar.php');
		$this->toolbar = JToolbar::getInstance('toolbar');

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

		/*require_once JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php';
		$canDo = JTicketingHelper::getActions();
		JToolBarHelper::title($viewTitle, 'pencil-2');

		If not checked out, can save the item.
		JToolBarHelper::apply('venueform.apply', 'COM_JTICKETING_VENUE_SAVE');
		JToolBarHelper::save('venueform.save', 'COM_JTICKETING_VENUE_SAVE_AND_CLOSE');

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::custom('venueform.save2new', 'save-new.png', 'save-new_f2.png', 'COM_JTICKETING_VENUE_SAVE_AND_NEW', false);
		}

		if (empty($this->item->id))
		{
			JToolBarHelper::cancel('venueform.cancel', 'COM_JTICKETING_VENUE_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel('venueform.cancel', 'COM_JTICKETING_VENUE_CLOSE');
		}*/
	}
}
