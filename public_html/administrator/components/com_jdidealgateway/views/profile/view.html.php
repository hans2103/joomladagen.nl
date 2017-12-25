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
 * Profile view.
 *
 * @package  JDiDEAL
 * @since    4.0
 */
class JdidealgatewayViewProfile extends JViewLegacy
{
	/**
	 * JD iDEAL Gateway helper
	 *
	 * @var    JdIdealgatewayHelper
	 * @since  4.0
	 */
	protected $jdidealgatewayHelper;

	/**
	 * Form with settings
	 *
	 * @var    JForm
	 * @since  4.0
	 */
	protected $form;

	/**
	 * Payment provider form with settings
	 *
	 * @var    JForm
	 * @since  4.0
	 */
	protected $pspForm;

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
	 * Holds the active payment provider
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $activeProvider = '';

	/**
	 * Check if the certificates exist
	 *
	 * @var    bool
	 * @since  3.0
	 */
	protected $filesExist = false;

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
	 * @since   4.0
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');
		$this->canDo         = JHelperContent::getActions('com_jdidealgateway');

		// Load the PSP form
		/** @var JdidealgatewayModelProfile $model */
		$model = $this->getModel();
		$this->pspForm = $model->getPspForm($this->item->psp);

		if ($this->pspForm)
		{
			$this->pspForm->bind($this->item->paymentInfo);
		}

		// Set the active provider
		$this->activeProvider = $this->item->psp;

		// Clear the user state
		JFactory::getApplication()->setUserState('profile.psp', false);

		// Check if the security files exist for advanced
		if ($this->item->psp === 'advanced')
		{
			$this->filesExist = $this->get('FilesExist');
		}

		// Add the toolbar
		$this->addToolbar();

		// Display it all
		return parent::display($tpl);
	}

	/**
	 * Displays a toolbar for a specific page.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 */
	private function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		JToolbarHelper::title(JText::_('COM_JDIDEALGATEWAY_JDIDEAL_PROFILE'), 'user');

		if ($this->canDo->get('core.edit') || $this->canDo->get('core.create'))
		{
			JToolbarHelper::apply('profile.apply');
			JToolbarHelper::save('profile.save');
		}

		if ($this->canDo->get('core.create') && $this->canDo->get('core.manage'))
		{
			JToolbarHelper::save2new('profile.save2new');
		}

		if ($this->canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('profile.save2copy');
		}

		if (0 === count($this->item->id))
		{
			JToolbarHelper::cancel('profile.cancel');
		}
		else
		{
			JToolbarHelper::cancel('profile.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
