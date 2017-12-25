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
 * Email view.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class JdidealgatewayViewEmail extends JViewLegacy
{
	/**
	 * Form with settings
	 *
	 * @var    JForm
	 * @since  4.0
	 */
	protected $form;

	/**
	 * The item object
	 *
	 * @var    object
	 * @since  4.0
	 */
	protected $item;

	/**
	 * Get the state
	 *
	 * @var    object
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
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   2.0
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$this->form   = $this->get('Form');
		$this->item   = $this->get('Item');
		$this->state  = $this->get('State');
		$this->canDo  = JHelperContent::getActions('com_jdidealgateway');

		// Add the toolbar
		$this->addToolbar();

		// Display it all
		parent::display($tpl);
	}

	/**
	 * Displays a toolbar for a specific page.
	 *
	 * @return  void.
	 *
	 * @since   2.0
	 *
	 * @throws  Exception
	 */
	private function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('COM_JDIDEALGATEWAY_JDIDEAL_EMAIL'), 'mail');

		if ($this->canDo->get('core.edit') || $this->canDo->get('core.create'))
		{
			JToolbarHelper::apply('email.apply');
			JToolbarHelper::save('email.save');
		}

		if ($this->canDo->get('core.create') && $this->canDo->get('core.manage'))
		{
			JToolbarHelper::save2new('email.save2new');
		}

		if (0 === count($this->item->id))
		{
			JToolbarHelper::cancel('email.cancel');
		}
		else
		{
			JToolbarHelper::cancel('email.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
