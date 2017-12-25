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
 * Emails list.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealgatewayViewEmails extends JViewLegacy
{
	/**
	 * JD iDEAL Gateway helper
	 *
	 * @var    JdidealGatewayHelper
	 * @since  4.0
	 */
	protected $jdidealgatewayHelper;

	/**
	 * List of properties
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $items = array();

	/**
	 * The pagination object
	 *
	 * @var    JPagination
	 * @since  1.0
	 */
	protected $pagination;

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
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->canDo         = JHelperContent::getActions('com_jdidealgateway');

		// Show the toolbar
		$this->toolbar();

		// Render the sidebar
		$this->jdidealgatewayHelper = new JdidealGatewayHelper;
		$this->jdidealgatewayHelper->addSubmenu('emails');
		$this->sidebar = JHtmlSidebar::render();

		// Display it all
		parent::display($tpl);
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
		JToolbarHelper::title(JText::_('COM_JDIDEALGATEWAY_JDIDEAL_EMAILS'), 'mail');

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::addNew('email.add');
		}

		if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
		{
			JToolbarHelper::editList('email.edit');
		}

		if ($this->canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'emails.delete', 'JTOOLBAR_DELETE');
		}

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::custom('emails.testemail', 'mail', 'mail', JText::_('COM_JDIDEALGATEWAY_SEND_TESTMAIL'));
		}
	}
}
