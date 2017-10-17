<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_hierarchy
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Hierarchy.
 *
 * @since  1.6
 */
class JticketingViewCatimpexp extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
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

		JticketingHelper::addSubmenu('catimpexp');

		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();

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
		require_once JPATH_COMPONENT . '/helpers/jticketing.php';

		$state = $this->get('State');
		$canDo = JticketingHelper::getActions($state->get('filter.category_id'));

		if (JVERSION >= '3.0')
		{
			JToolBarHelper::title(JText::_('COM_JTICKETING_COMPONENT') . JText::_('COM_JTICKETING_TITLE_CATIMPORTEXPORT'), 'list');
		}
		else
		{
			JToolBarHelper::title(JText::_('COM_JTICKETING_COMPONENT') . JText::_('COM_JTICKETING_TITLE_CATIMPORTEXPORT'), 'hierarchys.png');
		}

		$bar = JToolBar::getInstance('toolbar');
		$layout = JFactory::getApplication()->input->get('layout', 'default');
		JToolBarHelper::back('COM_JTICKETING_HOME', 'index.php?option=com_jticketing&view=cp');

		if ($layout == 'default')
		{
			$button = "<a class='btn' class='button'
			type='submit' id='export-submit' href='#eventCsv'><span title='Export'
			class='icon-download icon-white'></span>" . JText::_('CSV_EXPORT') . "</a>";
			$bar->appendButton('Custom', $button);
		}

		$buttonImport = '<a href="#import_category" class="btn ImportButton modal" rel="{size: {x: 800, y: 120}, ajaxOptions: {method: &quot;get&quot;}}">
		<span class="icon-upload icon-white"></span>' . JText::_('COMJTICKETING_EVENT_IMPORT_CSV') . '</a>';
		$bar->appendButton('Custom', $buttonImport);

		// Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/catimpexp';

		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences('com_jticketing');
		}

		// Set sidebar action - New in 3.0
		JHtmlSidebar::setAction('index.php?option=com_jticketing&view=catimpexp');

		$this->extra_sidebar = '';
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.id' => JText::_('JGRID_HEADING_ID'),
			'a.user_id' => JText::_('COM_HIERARCHY_HIERARCHYS_USER_ID'),
			'a.subuser_id' => JText::_('COM_HIERARCHY_HIERARCHYS_SUBUSER_ID')
		);
	}
}
