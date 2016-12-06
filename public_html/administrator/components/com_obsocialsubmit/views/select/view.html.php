<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;

class ObSocialSubmitViewSelect extends JViewLegacy
{
	protected $state;

	protected $items;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->state = &$state;
		$this->items = &$items;

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   3.0
	 */
	protected function addToolbar()
	{
		// Add page title
		JToolbarHelper::title(JText::_('COM_OBSOCIALSUBMIT_MANAGER_PLUGINS'), 'module.png');

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		$type = filter_input(INPUT_GET,'type');
		$view = $type.'s';
		// Cancel
		$title = JText::_('JTOOLBAR_CANCEL');
		$dhtml = "<button onClick=\"location.href='index.php?option=com_obsocialsubmit&view=".$view."'\" class=\"btn\">
					<i class=\"icon-remove\" title=\"$title\"></i>
					$title</button>";
		$bar->appendButton('Custom', $dhtml, 'new');
	}
}
