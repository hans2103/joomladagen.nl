<?php
/**
 * @version    SVN: <svn_id>
 * @package    JLike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * jLikeViewabout form view class.
 *
 * @package     JGive
 * @subpackage  com_jlike
 * @since       1.8
 */
class JLikeViewabout extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		JlikeHelper::addSubmenu('about');

		$this->addToolbar();

		if (JVERSION >= 3.0)
		{
			$this->sidebar = JHtmlSidebar::render();
		}

		parent::display($tpl);
	}

	/**
	 * Set tool bar
	 *
	 * @return  void
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_JLIKE_TITLE_ABOUT'), 'jlike.png');
		JToolBarHelper::preferences('com_jlike');
	}
}
