<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Jticketing
 * @author     Techjoomla <kiran_l@techjoomla.com>
 * @copyright  2016 techjoomla
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Jticketing.
 *
 * @since  1.6
 */
class JticketingViewVenues extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

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
		global $mainframe, $option;
		$input      = JFactory::getApplication()->input;
		$mainframe  = JFactory::getApplication();
		$user       = JFactory::getUser();

		// Validate user login.
		if (empty($user->id))
		{
			$msg = JText::_('COM_JTICKETING_MESSAGE_LOGIN_FIRST');

			// Get current url.
			$current = JUri::getInstance()->toString();
			$url     = base64_encode($current);
			$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);
		}

		$this->state         = $this->get('State');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$option              = $input->get('option');
		$venue_type          = $mainframe->getUserStateFromRequest($option . 'venue_type', 'venue_type', '', 'string');
		$venue_privacy       = $mainframe->getUserStateFromRequest($option . 'venue_privacy', 'venue_privacy', '', 'string');

		$venueTypeList    = array();
		$venueTypeList[]  = JHtml::_('select.option', '', JText::_('COM_JTICKETING_FILTER_SELECT_VENUE_TYPE'));
		$venueTypeList[]  = JHtml::_('select.option', '1', JText::_('COM_JTICKETING_VENUE_TYPEONLINE'));
		$venueTypeList[]  = JHtml::_('select.option', '0', JText::_('COM_JTICKETING_VENUE_TYPEOFFLINE'));

		$this->venueTypeList    = $venueTypeList;
		$lists['venueTypeList'] = $venue_type;

		$venuePrivacyList    = array();
		$venuePrivacyList[]  = JHtml::_('select.option', '', JText::_('COM_JTICKETING_FILTER_SELECT_VENUE_PRIVACY'));
		$venuePrivacyList[]  = JHtml::_('select.option', '1', JText::_('COM_JTICKETING_VENUE_PRIVACY_PUBLIC'));
		$venuePrivacyList[]  = JHtml::_('select.option', '0', JText::_('COM_JTICKETING_VENUE_PRIVACY_PRIVATE'));

		$this->venuePrivacyList    = $venuePrivacyList;
		$lists['venuePrivacyList'] = $venue_privacy;
		$this->lists               = $lists;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		// Get component params
		$this->params     = JComponentHelper::getParams('com_jticketing');
		$this->addTJtoolbar();
		parent::display($tpl);
	}

	/**
	 * Setup ACL based tjtoolbar
	 *
	 * @return  void
	 *
	 * @since   2.2
	 */
	protected function addTJtoolbar()
	{
		JLoader::register('JticketingHelper', JPATH_ADMINISTRATOR . '/components/com_jticketing/helpers/jticketing.php');

		$state = $this->get('State');
		$canDo = JticketingHelper::getActions($state->get('filter.category_id'));

		// Add toolbar buttons
		jimport('techjoomla.tjtoolbar.toolbar');
		$tjbar = TJToolbar::getInstance('tjtoolbar', 'pull-right');

		if ($canDo->get('core.create'))
		{
			$tjbar->appendButton('venueform.add', 'TJTOOLBAR_NEW', '', 'class="btn btn-small btn-success"');
		}

		if ($canDo->get('core.edit.own') && isset($this->items[0]))
		{
			$tjbar->appendButton('venueform.edit', 'TJTOOLBAR_EDIT', '', 'class="btn btn-small btn-success"');
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]))
			{
				$tjbar->appendButton('venues.publish', 'TJTOOLBAR_PUBLISH', '', 'class="btn btn-small btn-success"');
				$tjbar->appendButton('venues.unpublish', 'TJTOOLBAR_UNPUBLISH', '', 'class="btn btn-small btn-warning"');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]))
			{
				$tjbar->appendButton('venues.delete', 'TJTOOLBAR_DELETE', '', 'class="btn btn-small btn-danger"');
			}
		}

		$this->toolbarHTML = $tjbar->render();
	}
}
