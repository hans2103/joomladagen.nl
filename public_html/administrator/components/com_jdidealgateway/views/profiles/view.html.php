<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

/**
 * Profiles view.
 *
 * @package  JDiDEAL
 * @since    4.0
 */
class JdidealgatewayViewProfiles extends JViewLegacy
{
	/**
	 * Array with profiles
	 *
	 * @var    array
	 * @since  4.0
	 */
	protected $items;

	/**
	 * Pagination class
	 *
	 * @var    JPagination
	 * @since  4.0
	 */
	protected $pagination;

	/**
	 * The user state
	 *
	 * @var    JObject
	 * @since  4.0
	 */
	protected $state;

	/**
	 * Access rights of a user
	 *
	 * @var    JObject
	 * @since  4.0
	 */
	protected $canDo;

	/**
	 * The sidebar to show
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $sidebar = '';

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$this->items	     = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->canDo         = JHelperContent::getActions('com_jdidealgateway');

		// Show the toolbar
		$this->toolbar();

		// Render the sidebar
		$jdidealgatewayHelper = new JdidealGatewayHelper;
		$jdidealgatewayHelper->addSubmenu('profiles');
		$this->sidebar = JHtmlSidebar::render();

		// Display it all
		return parent::display($tpl);
	}

	/**
	 * Displays a toolbar for a specific page.
	 *
	 * @return  void.
	 *
	 * @since   2.0
	 */
	private function toolbar()
	{
		JToolbarHelper::title(JText::_('COM_JDIDEALGATEWAY_PROFILES'), 'users');

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::addNew('profile.add');
		}

		if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
		{
			JToolbarHelper::editList('profile.edit');
		}

		if ($this->canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'profiles.delete', 'JTOOLBAR_DELETE');
		}
	}
}
