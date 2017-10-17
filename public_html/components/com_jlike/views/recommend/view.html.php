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
 * View class for a list of JLike.
 *
 * @since  1.0.0
 */
class JlikeViewrecommend extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 */
	public function display($tpl = null)
	{
		$app   = JFactory::getApplication();
		$input = JFactory::getApplication()->input;
		$this->type   = $input->get('type', 'reco', 'STRING');
		$this->state		= $this->get('State');

		JLoader::import('administrator.components.com_tjlms.helpers.tjlms', JPATH_SITE);
		$courseId   = $input->get('id', 0, 'INT');
		$canManage = TjlmsHelper::canManageCourseEnrollment($courseId);

		if ($this->type == 'assign')
		{
			// Only Manager
			if ($canManage == -2)
			{
				$this->state->set('list.subuserfilter', 1);
			}
		}
		else
		{
			$this->state->set('list.subuserfilter', '');
		}

		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if ($canManage == -2)
		{
			$this->filterForm->removeField('subuserfilter', 'list');
		}

		$this->logged_userid = JFactory::getUser()->id;

		if (!$this->logged_userid)
		{
			$msg = JText::_('COM_JLIKE_LOGIN_MSG');
			$uri = $input->server->get('REQUEST_URI', '', 'STRING');
			$url = base64_encode($uri);
			$app->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		$this->peopleToRecommend = $this->items;

		// Get selected content/element data
		$this->element = $this->_getElementData();

		parent::display($tpl);
	}

	/**
	 * Get Element Data
	 *
	 * @return  Array  Element data
	 *
	 * @since  1.5
	 *
	 */
	public function _getElementData()
	{
		$input = JFactory::getApplication()->input;
		$plg_type            = $input->get('plg_type', 'content', 'STRING');
		$plg_name            = $input->get('plg_name', '', 'STRING');
		$elementId           = $input->get('id', '', 'INT');
		$element             = $input->get('element', '', 'INT');

		// Get URL and title form respective component
		$dispatcher  = JDispatcher::getInstance();
		JPluginHelper::importPlugin($plg_type, $plg_name);
		$elementdata = $dispatcher->trigger($plg_name . 'GetElementData', array($elementId));

		return $elementdata[0];
	}
}
