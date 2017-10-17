<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://joomladagen.nl
 */

defined('_JEXEC') or die;

/**
 * Speaker View.
 *
 * @package     Conference
 * @since       1.0
 */
class ConferenceViewSpeaker extends JViewLegacy
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
	 * Hold the user rights
	 *
	 * @var    object
	 * @since  1.0
	 */
	private $canDo;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   1.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 * @throws  InvalidArgumentException
	 * @throws  UnexpectedValueException
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->canDo = JHelperContent::getActions('com_conference');

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 *
	 * @throws  Exception
	 */
	private function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$isNew      = $this->item->conference_speaker_id === 0;
		$canDo      = $this->canDo;

		JToolbarHelper::title(JText::_('COM_CONFERENCE') . ' - ' . JText::_('COM_CONFERENCE_TITLE_SPEAKERS_EDIT'), 'user');

		// If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
		if ($isNew && $canDo->get('core.create'))
		{
			if ($canDo->get('core.edit'))
			{
				JToolbarHelper::apply('speaker.apply');
			}

			JToolbarHelper::save('speaker.save');
		}

		// If not checked out, can save the item.
		if (!$isNew && $canDo->get('core.edit'))
		{
			JToolbarHelper::apply('speaker.apply');
			JToolbarHelper::save('speaker.save');
		}

		// If the user can create new items, allow them to see Save & New
		if ($canDo->get('core.create'))
		{
			JToolbarHelper::save2new('speaker.save2new');
		}

		// If an existing item, can save to a copy only if we have create rights.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolbarHelper::save2copy('speaker.save2copy');
		}

		if ($isNew)
		{
			JToolbarHelper::cancel('speaker.cancel');
		}
		else
		{
			JToolbarHelper::cancel('speaker.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
