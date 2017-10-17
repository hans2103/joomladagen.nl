<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
/**
 * @package		jomLike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

jimport('joomla.application.component.view');

/**
 * Annotations class
 *
 * @since  1.0
 */
class JlikeViewannotations extends JViewLegacy
{
	/**
	 * Annotations class
	 *
	 * @param   OBJECT  $tpl  optional
	 *
	 * @since   1.0
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		JFactory::getLanguage()->load('com_jomlike');
		$layout = JRequest::getVar('layout', 'default');
		$user = JFactory::getUser();

		if (1 == $user->guest)
		{
			$this->logged_userid = JFactory::getUser()->id;

			if (!$this->logged_userid)
			{
				$msg = JText::_('COM_JLIKE_LOGIN_MSG');
				$uri = $input->server->get('REQUEST_URI', '', 'STRING');
				$url = base64_encode($uri);
				$app->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . $url, false), $msg);
			}
		}

		$myfavourites = $this->getModel('annotations');
		$data = $myfavourites->getData();

		jimport('joomla.html.pagination');

		// Get data from the model
		$pagination = $this->get('Pagination');

		// Push data into the template
		$this->data = $data;
		$this->pagination = $pagination;
		$mainframe = JFactory::getApplication();
		$filter_order = $mainframe->getUserStateFromRequest('com_jlike.filter_order', 'filter_order', 'title', 'string');
		$filter_order_Dir = $mainframe->getUserStateFromRequest('com_jlike.filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

		// Load all filter values
		$this->campaign_type_filter_options = $this->get('CampaignTypeFilterOptions');
		$this->ordering_options = $this->get('OrderingOptions');
		$this->ordering_direction_options = $this->get('OrderingDirectionOptions');

		$model = $this->getModel();

		$this->filter_likecontent_classification = $model->Likecontent_classification($user);

		if ($layout == 'default')
		{
			$this->filter_likecontent_list = $model->Likecontent_list($user);
		}
		else
		{
			$this->filter_likecontent_list = $model->Likecontent_list();
		}

		$this->filter_likecontent_user = $model->Likecontent_user($user);

		// Load current value for filter
		$this->filter_search_likecontent = $mainframe->getUserStateFromRequest('com_jlikefilter_search_likecontent',
		'filter_search_likecontent', '', 'string' );
		$filter_likecontent_classification = $mainframe->getUserStateFromRequest('com_jlikefilter_likecontent_classification',
										'filter_likecontent_classification');
		$filter_likecontent_list = $mainframe->getUserStateFromRequest('com_jlikefilter_likecontent_list', 'filter_likecontent_list');
		$filter_likecontent_user = $mainframe->getUserStateFromRequest('com_jlikefilter_likecontent_user', 'filter_likecontent_user');

		// Set all filters in list
		$lists['filter_order'] = $filter_order;
		$lists['filter_order_Dir'] = $filter_order_Dir;

		$lists['filter_search_likecontent'] = $this->filter_search_likecontent;
		$lists['filter_likecontent_classification'] = $filter_likecontent_classification;
		$lists['filter_likecontent_list'] = $filter_likecontent_list;
		$lists['filter_likecontent_user'] = $filter_likecontent_user;

		$this->lists = $lists;

		parent::display($tpl);
	}

	/**
	 * Annotations class
	 *
	 * @param   OBJECT  $data  optional
	 *
	 * @since   1.0
	 *
	 * @return  void
	 */
	private function _updateJomsocial($data)
	{
		if (is_readable(JPATH_SITE . '/components/com_community/libraries/core.php'))
		{
			require_once JPATH_SITE . '/components/com_community/libraries/core.php';

			$act = new stdClass;
			$act->cmd = 'wall.write';
			$act->actor = JFactory::getUser()->id;

			// No target
			$act->target = 0;
			$act->title = '{actor} ' . JText::_(JRequest::getCMD('task') . '_VERB') . ' <a href="' . base64_decode($data['jomLikeUrl']) . '">'
				. base64_decode($data['title']) . '</a>.';
			$act->content = '';
			$act->app = 'wall';
			$act->cid = 0;
			CFactory::load('libraries', 'activities');

			if (defined('CActivities::COMMENT_SELF'))
			{
				$act->comment_id = CActivities::COMMENT_SELF;
				$act->comment_type = 'profile.location';
			}

			if (defined('CActivities::LIKE_SELF'))
			{
				$act->like_id = CActivities::LIKE_SELF;
				$act->like_type = 'profile.location';
			}

			CActivityStream::add($act);
		}
	}
}
