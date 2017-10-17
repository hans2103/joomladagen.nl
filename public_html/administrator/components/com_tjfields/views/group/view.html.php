<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * View class for show group.
 *
 * @package     Tjfields
 * @subpackage  com_tjfields
 * @since       2.2
 */
class TjfieldsViewGroup extends JViewLegacy
{
	protected $state;

	protected $item;

	protected $form;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user  = JFactory::getUser();
		$isNew = ($this->item->id == 0);

		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}

		$canDo           = TjfieldsHelper::getActions();
		$input           = JFactory::getApplication()->input;
		$client          = $input->get('client', '', 'STRING');
		$component_title = JText::_('COM_TJFIELDS_TITLE_COMPONENT');

		if (!empty($client))
		{
			$client = explode('.', $client);

			if ($client['0'] == 'com_jticketing')
			{
				$component_title = JText::_('COM_JTICKETING_COMPONENT');
			}
		}

		if ($isNew)
		{
			$viewTitle = JText::_('COM_TJFIELDS_ADD_GROUP');
		}
		else
		{
			$viewTitle = JText::_('COM_TJFIELDS_EDIT_GROUP');
		}

		if (JVERSION >= '3.0')
		{
			JToolbarHelper::title($component_title . $viewTitle, 'pencil-2');
		}
		else
		{
			JToolbarHelper::title($component_title . $viewTitle, 'group.png');
		}

		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			JToolBarHelper::apply('group.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('group.save', 'JTOOLBAR_SAVE');
		}

		if (!$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::custom('group.newsave', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create'))
		{
			JToolBarHelper::custom('group.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}

		if (empty($this->item->id))
		{
			JToolBarHelper::cancel('group.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel('group.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
