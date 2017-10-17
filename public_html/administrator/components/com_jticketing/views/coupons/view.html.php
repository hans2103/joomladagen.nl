<?php

/**
 * @version    SVN:<SVN_ID>
 * @package    Com_Jticketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Jticketing.
 *
 * @since  1.6
 */
class JticketingViewCoupons extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   array  $tpl  An optional associative array.
	 *
	 * @return  array
	 *
	 * @since  1.6
	 */
	public function display($tpl = null)
	{
		$params = JComponentHelper::getParams('com_jticketing');
		$integration = $params->get('integration');

		// Native Event Manager.
		if($integration<1)
		{
			$this->sidebar = JHtmlSidebar::render();
			JToolBarHelper::preferences('com_jticketing');
		?>
			<div class="alert alert-info alert-help-inline">
		<?php echo JText::_('COMJTICKETING_INTEGRATION_NOTICE');
		?>
			</div>
		<?php
			return false;
		}

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		JticketingHelper::addSubmenu('coupons');
		$this->publish_states = array(
			'' => JText::_('JOPTION_SELECT_PUBLISHED'),
			'1'  => JText::_('JPUBLISHED'),
			'0'  => JText::_('JUNPUBLISHED')
		);
		$this->addToolbar();

		if (JVERSION >= '3.0')
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT . '/helpers/jticketing.php';
		$state = $this->get('State');
		$canDo = JticketingHelper::getActions($state->get('filter.category_id'));
		JToolBarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_('COM_JTICKETING_COMPONENT') . JText::_('COM_JTICKETING_TITLE_COUPONS'), 'list');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_JTICKETING_COMPONENT') . JText::_('COM_JTICKETING_TITLE_COUPONS'), 'coupons.png');
		}

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/coupon';

		if (file_exists($formPath))
		{
			if ($canDo->get('core.create'))
			{
				JToolBarHelper::addNew('coupon.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit') && isset($this->items[0]))
			{
				JToolBarHelper::editList('coupon.edit', 'JTOOLBAR_EDIT');
			}
		}

		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->state))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('coupons.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('coupons.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}

			if (isset($this->items[0]->checked_out))
			{
				JToolBarHelper::custom('coupons.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		if (isset($this->items[0]))
		{
			if ($canDo->get('core.delete'))
			{
				JToolBarHelper::deleteList(JText::_('COM_JTICKETING_ARE_YOU_SURE_YOU_TO_DELETE_THE_COUPON'), 'coupons.delete', 'JTOOLBAR_DELETE');
			}
		}

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_jticketing');
		}

		if (JVERSION >= '3.0')
		{
			// Set sidebar action - New in 3.0
			JHtmlSidebar::setAction('index.php?option=com_jticketing&view=coupons');
		}

		$this->extra_sidebar = '';
	}

	/**
	 * For sorting filter.
	 *
	 * @return  void
	 *
	 * @since  1.6
	 */
	protected function getSortFields()
	{
		return array(
		'a.id' => JText::_('JGRID_HEADING_ID'),
		'a.name' => JText::_('COM_JTICKETING_COUPONS_NAME'),
		'a.code' => JText::_('COM_JTICKETING_COUPONS_CODE'),
		'a.value' => JText::_('COM_JTICKETING_COUPONS_VALUE'),
		'a.state' => JText::_('JSTATUS'),
		'a.max_use' => JText::_('COM_JTICKETING_COUPONS_MAX_USE'),
		'a.from_date' => JText::_('COM_JTICKETING_COUPONS_FROM_DATE'),
		'a.exp_date' => JText::_('COM_JTICKETING_COUPONS_EXP_DATE'),
		);
	}
}
