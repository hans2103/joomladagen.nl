<?php

/**
 * @package     CSVI
 * @subpackage  Templatefield
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Template field edit screen.
 *
 * @package     CSVI
 * @subpackage  Templatefield
 * @since       6.0
 */
class CsviViewTemplatefield extends JViewLegacy
{
	/**
	 * The form with the field
	 *
	 * @var    JForm
	 * @since  1.0
	 */
	protected $form;

	/**
	 * The property item
	 *
	 * @var    object
	 * @since  1.0
	 */
	protected $item;

	/**
	 * The user state
	 *
	 * @var    JObject
	 * @since  1.0
	 */
	protected $state;

	/**
	 * Show the extra help
	 *
	 * @var    int
	 * @since  6.5.0
	 */
	protected $extraHelp;

	/**
	 * Hold the user rights
	 *
	 * @var    object
	 * @since  1.0
	 */
	private $canDo;

	/**
	 * An instance of CsviHelperTemplate
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.6.0
	 */
	protected $template;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   6.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 * @throws  UnexpectedValueException
	 */
	public function display($tpl = null)
	{
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		// Load the template
		$this->loadSelectedTemplate();

		// Load the helper
		$helper = new CsviHelperCsvi;

		// Build the form
		$form        = $this->get('Form');
		$this->form  = $helper->renderCsviForm($form, JFactory::getApplication()->input);

		$this->canDo = JHelperContent::getActions('com_csvi');

		// Set the extra help option
		$this->setExtraHelp();

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Load the selected template.
	 *
	 * @return  void.
	 *
	 * @since   6.6.0
	 *
	 * @throws  Exception
	 */
	private function loadSelectedTemplate()
	{
		/** @var CsviModelTemplates $templateModel */
		$templateModel = JModelLegacy::getInstance('Templates', 'CsviModel', array('ignore_request' => true));
		$templates = $templateModel->getItems();

		// Load a chosen template ID
		$input = JFactory::getApplication()->input;
		$csvi_template_id = $this->state->get('filter.csvi_template_id', $input->getInt('csvi_template_id', $this->item->csvi_template_id));

		// Check if we have a template ID, if not take the first one
		if ($csvi_template_id < 1 && $templates)
		{
			$template = reset($templates);
			$csvi_template_id = $template->csvi_template_id;
		}

		// Save the state
		$this->state->set('filter.csvi_template_id', $csvi_template_id);

		// Load the selected template
		$this->template = new CsviHelperTemplate($csvi_template_id);
		$this->state->set('template', $this->template);
	}

	/**
	 * Set the extra help option for this user.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	private function setExtraHelp()
	{
		// Load the extra help settings
		$db = JFactory::getDbo();
		$settings = new CsviHelperSettings($db);
		$this->extraHelp = $settings->get('extraHelp');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   6.6.0
	 *
	 * @throws  Exception
	 */
	private function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$isNew      = $this->item->csvi_templatefield_id === 0;
		$canDo      = $this->canDo;

		JToolbarHelper::title('CSVI - ' . JText::_('COM_CSVI_TITLE_TEMPLATEFIELDS_EDIT'), 'list-view');

		// If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
		if ($isNew && $canDo->get('core.create'))
		{
			if ($canDo->get('core.edit'))
			{
				JToolbarHelper::apply('templatefield.apply');
			}

			JToolbarHelper::save('templatefield.save');
		}

		// If not checked out, can save the item.
		if (!$isNew && $canDo->get('core.edit'))
		{
			JToolbarHelper::apply('templatefield.apply');
			JToolbarHelper::save('templatefield.save');
		}

		// If the user can create new items, allow them to see Save & New
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::save2new('templatefield.save2new');
		}

		// If an existing item, can save to a copy only if we have create rights.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('templatefield.save2copy');
		}

		if ($isNew)
		{
			JToolbarHelper::cancel('templatefield.cancel');
		}
		else
		{
			JToolbarHelper::cancel('templatefield.cancel', 'JTOOLBAR_CLOSE');
		}

		JToolbarHelper::custom('hidetips', 'help', 'help', JText::_('COM_CSVI_HELP'), false);
	}
}
