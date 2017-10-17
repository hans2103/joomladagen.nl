<?php
/**
 * @package    JLike
 * @copyright  Copyright (C) 2005 - 2014. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * jlike view
 *
 * @since  1.0
 */
class JlikeViewjlike extends JViewLegacy
{
	/**
	 * jlike view
	 *
	 * @param   OBJECT  $tpl  boolean
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function display($tpl = null)
	{
		$this->params        = JComponentHelper::getParams('com_jlike');
		$input    = JFactory::getApplication()->input;
		$post     = $input->post;
		$ordering = $post->get('sorting', '', 'STRING');

		JFactory::getLanguage()->load('com_jlike');
		$setdata       = JRequest::get('request');
		$this->urldata = json_decode($setdata['data']);
		$extraParams = array("plg_name" => $this->urldata->plg_name, "plg_type" => $this->urldata->plg_type);

		$array = array('show_like_buttons', 'show_comments', 'show_note', 'show_list',
		'toolbar_buttons', 'showrecommendbtn', 'showsetgoalbtn', 'showassignbtn', 'plg_type', 'jlike_allow_rating', 'show_reviews');

		// Remove warnings for not set variables
		foreach ($array as $key => $value)
		{
			if (!isset($this->urldata->$value))
			{
				$this->urldata->$value = null;
			}
		}

		$oluser               = JFactory::getUser();
		$this->jlikehelperObj = new comjlikeHelper;
		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$this->jlikemainhelperObj = new ComjlikeMainHelper;

		// Get Params each component
		$this->params = $this->jlikemainhelperObj->getjLikeParams();

		$model             = $this->getModel('jlike_likes');
		$this->userdetails = $this->jlikehelperObj->getUserDetails($oluser, $extraParams);
		$this->data        = $model->getData($this->urldata->cont_id, $this->urldata->element, $this->params->get('show_users'), $extraParams);
		$this->userlables = '';

		$this->content_id = (isset($this->data['content_id'])) ? $this->data['content_id'] : '';

		// Get goal details
		$this->goaldetails = $this->jlikemainhelperObj->getGoalDetails($oluser->id, $this->content_id);

		$this->userlables = $model->getUserlabels($this->content_id);

		$this->userNote = $model->geUserNote($this->urldata->element, $this->urldata->cont_id, $oluser->id);
		$this->buttonset   = $this->jlikehelperObj->getbttonset();

		// Get Comments Data
		$this->comments = $model->getCommentsData($this->urldata->cont_id, $this->urldata->element, 0, 0, $ordering, '', '', '', $extraParams);
		$this->ordering = $ordering;

		// Get Comments count
		$this->comments_count = $model->getCommentsCount($this->urldata->cont_id, $this->urldata->element);

		// Default count 0
		if (empty($this->comments_count))
		{
			$this->comments_count = 0;
		}

		$this->statusMgt = $this->params->get('statusMgt', 0);

		if ($this->statusMgt)
		{
			// Get status List
			$this->Allstatuses = $this->jlikehelperObj->getAllStatus();
			$this->likeContId = $this->jlikehelperObj->getContentId($this->urldata->cont_id, $this->urldata->element);

			// Get users content status
			$this->userStatusId = $this->jlikehelperObj->getUsersContStatus($this->likeContId);
		}

		// Rating & Reviews
		// Get Reviews Data
		$this->reviews = $model->getRatingReviewData($this->urldata->cont_id, $this->urldata->element, 0, 0, $ordering, '', '', 2);

		// Get Reviews count
		$this->reviews_count = $model->getReviewsCount($this->urldata->cont_id, $this->urldata->element, 2, '');
		$this->allowRating = isset($this->urldata->jlike_allow_rating) ? $this->urldata->jlike_allow_rating : 0;

		// Get Rating avarage
		// $this->getRatingAvg = $model->getRatingAvg($this->urldata->cont_id);

		$this->reviews_count_loginuser = $model->getReviewsCount($this->urldata->cont_id, $this->urldata->element, 2, 'loginuser');

		$this->oluser = $oluser;

		parent::display($tpl);
	}
}
