<?php
/**
 * @version    SVN:
 * @package    Com_Jticketing
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright  2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Jticketing.
 *
 * @since  1.6
 */
class JticketingViewAttendeeCoreFields extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

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
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->input = JFactory::getApplication()->input;
		$this->user      = JFactory::getUser();
		$this->listOrder = $this->state->get('list.ordering');
		$this->listDirn  = $this->state->get('list.direction');
		$this->canOrder  = $this->user->authorise('core.edit.state', 'com_jticketing');
		$this->saveOrder = $this->listOrder == 'a.`ordering`';

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JticketingHelper::addSubmenu('attendeecorefields');

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function addToolbar()
	{
		$input = JFactory::getApplication()->input;
		$state = $this->get('State');
		$canDo = JticketingHelper::getActions();

		JToolBarHelper::title(JText::_('COM_JTICKETING_TITLE_ATTENDEE_CORE_FIELDS'), 'book');

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('attendeeCorefields.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('attendeeCorefields.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_jticketing');
		}

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_jticketing&view=attendeecorefields');

		$this->extra_sidebar = '';
	}
}
