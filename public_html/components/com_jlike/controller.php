<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access.
defined('_JEXEC') or die();
jimport('joomla.application.component.controller');

/**
 * jLikeController main Controller
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JLikeController extends JControllerLegacy
{
	/**
	 * Display.
	 *
	 * @param   boolean  $cachable   cachable status.
	 * @param   boolean  $urlparams  urlparams status.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view  = $this->getView('jlike', 'raw');
		$model = $this->getModel('jlike_likes');
		$view->setModel($model);
		parent::display();
	}

	/**
	 * Store.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function store()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		if (JVERSION < 3.0)
		{
			$data = JRequest::get('data');
		}
		else
		{
			$input = JFactory::getApplication()->input;
			$post  = $input->getArray($_POST);
			$data  = $post;
		}

		$jlikehelpobj = new comjlikeHelper;
		$result       = 0;

		if ($data)
		{
			$result = $jlikehelpobj->registerLike($data);
		}

		if ($result)
		{
			echo json_encode($result);
		}
		else
		{
			echo json_encode(-1);
		}

		jexit();
	}

	/**
	 * Addlables.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function addlables()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		if (JVERSION < 3.0)
		{
			$data = JRequest::get('data');
		}
		else
		{
			$input = JFactory::getApplication()->input;
			$post  = $input->getArray($_POST);
			$data  = $post;
		}

		$jlikehelpobj = new comjlikeHelper;
		$result       = 0;

		if ($data)
		{
			$result = $jlikehelpobj->addlables($data);
		}

		if ($result)
		{
			echo json_encode($result);
		}
		else
		{
			echo json_encode(-1);
		}

		jexit();
	}

	/**
	 * savedata.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function savedata()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		if (JVERSION < 3.0)
		{
			$data = JRequest::get('data');
		}
		else
		{
			$input = JFactory::getApplication()->input;
			$post  = $input->getArray($_POST);
			$data  = $post;
		}

		$output = array();
		parse_str($data['formdata'], $output);

		unset($data['formdata']);
		$output = array_merge($data, $output);

		$jlikehelpobj = new comjlikeHelper;
		$result       = 0;

		if ($output)
		{
			$result = $jlikehelpobj->savedata($output);
		}

		if ($result)
		{
			echo json_encode($result);
		}
		else
		{
			echo json_encode(-1);
		}

		jexit();
	}

	/**
	 * getUserdetails.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getUserdetails()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		$jlikehelpobj = new comjlikeHelper;
		$oluser       = JFactory::getUser();

		if (JVERSION < 3.0)
		{
			$data = JRequest::get('data');
		}
		else
		{
			$input = JFactory::getApplication()->input;
			$post  = $input->getArray($_POST);
			$data  = $post;
		}

		$result       = $jlikehelpobj->getUserDetails($oluser, $data['extraParams']);

		if ($result)
		{
			$oluser->uname    = $oluser->name;
			$oluser->img_url  = $result['img_url'];
			$oluser->link_url = $result['link_url'];
			echo json_encode($oluser);
		}
		else
		{
			echo json_encode(-1);
		}

		jexit();
	}

	/**
	 * Method to save the edited comment.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function SaveComment()
	{
		$input         = JFactory::getApplication()->input;
		$post          = $input->post;
		$annotation_id = $post->get('annotation_id', '', 'INT');
		$comment       = trim($post->get('comment', '', 'HTML'));
		$note_type     = $post->get('note_type', '0', 'HTML');

		// $comment =	JRequest::getVar('comment', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$model         = $this->getModel('jlike_likes');
		$response      = $model->SaveComment($annotation_id, $comment, $note_type);
		echo json_encode($response);
		jexit();
	}

	/**
	 * Method to delete the user reviews
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function DeleteReviews()
	{
		$input         = JFactory::getApplication()->input;
		$annotation_id = $input->get('annotation_id', '', 'INT');
		$model         = $this->getModel('jlike_likes');
		$response      = $model->DeleteReviews($annotation_id);
		echo json_encode($response);
		jexit();
	}

	/**
	 * Method to delete the user commment
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function DeleteComment()
	{
		$input         = JFactory::getApplication()->input;
		$annotation_id = $input->get('annotation_id', '', 'INT');
		$model         = $this->getModel('jlike_likes');
		$response      = $model->DeleteComment($annotation_id);
		echo json_encode($response);
		jexit();
	}

	/**
	 * SaveNewRating
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function SaveNewRating()
	{
		$input          = JFactory::getApplication()->input;
		$post           = $input->post;

		$idBox          = $post->get('idBox', '', 'INT');
		$action         = $post->get('action', '', 'STRING');

		$element_id     = $post->get('element_id', '', 'INT');
		$element        = $post->get('element', '', 'STRING');
		$url            = $post->get('url', '', 'STRING');
		$title          = $post->get('title', '', 'STRING');
		$user_rating    = $post->get('user_rating', '0', 'INT');
		$rating_upto    = $post->get('rating_upto', '', 'STRING');
		$plg_name       = $post->get('plg_name', '', 'STRING');

		$comjlikeHelper = new comjlikeHelper;
		$response       = $comjlikeHelper->addRating($element_id, $user_rating, $rating_upto, $plg_name, $element, $url, $title);
		echo json_encode($response);
		jexit();
	}

	/**
	 * SaveNewComment
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function SaveNewComment()
	{
		$input          = JFactory::getApplication()->input;
		$post           = $input->post;
		$commentData 	= array();
		$commentData['comment'] = trim($post->get('comment', '', 'HTML'));
		$userID         = JFactory::getUser()->id;

		if (!$userID)
		{
			echo json_encode(0);
			jexit();
		}

		// $comment =	JRequest::getVar('comment', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$commentData['element_id']     = $post->get('element_id', '', 'INT');
		$commentData['note_type']   = $post->get('note_type', '0', 'INT');
		$commentData['element'] = $post->get('element', '', 'STRING');
		$commentData['url'] = $post->get('url', '', 'STRING');
		$commentData['title'] = $post->get('title', '', 'STRING');
		$commentData['plg_name'] = $post->get('plg_name', '', 'STRING');
		$commentData['parent_id']    = $post->get('parent_id', '0', 'INT');
		$commentData['extraParams']  = $post->get('extraParams', '0', 'ARRAY');
		/*$comjlikeHelper = new comjlikeHelper;
		$response       = $comjlikeHelper->addComment($comment, $element_id, $element, $url, $title, $plg_name, $parent_id,$note_type);*/

		$comjlikeHelper = new comjlikeHelper;
		$response       = $comjlikeHelper->addComment($commentData);
		echo json_encode($response);
		jexit();
	}

	/**
	 * method to get the reviews for view more
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function LoadReviews()
	{
		$input       = JFactory::getApplication()->input;
		$post        = $input->post;
		$element_id  = $post->get('element_id', '', 'INT');
		$element     = $post->get('element', '', 'STRING');
		$callIdetity = $post->get('callIdetity', '0', 'INT');
		$ordering    = $post->get('sorting', '1', 'INT');

		// Get the comment sorting latest or oldest
		switch ($ordering)
		{
			case 1:
				$sortingType = 'DESC';
				break;
			case 2:
				$sortingType = 'ASC';
				break;
		}

		// If this request not call from assending decending function
		if (!$callIdetity)
		{
			$annotaionIdsArr = json_decode($post->get('annotaionIdsArr', '', 'STRING'));
			$viewmoreId      = $post->get('viewmoreId', '', 'ARRAY');
		}
		else
		{
			$annotaionIdsArr = '';
			$viewmoreId      = '';
		}

		$childrensId = '';
		$getchildren = $post->get('getchildren', '0', 'INT');

		if ($getchildren)
		{
			$childrensId = ($post->get('childrensId', '', 'ARRAY'));
		}

		$model = $this->getModel('jlike_likes');
		$response = json_encode(
								$model->getRatingReviewData(
								$element_id, $element, $getchildren, $childrensId,
								$sortingType, $annotaionIdsArr, $viewmoreId, 2
								)
								);

		echo $response;

		jexit();
	}

	/**
	 * method to get the commets for view more
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function LoadComment()
	{
		$input       = JFactory::getApplication()->input;
		$post        = $input->post;
		$element_id  = $post->get('element_id', '', 'INT');
		$element     = $post->get('element', '', 'STRING');
		$callIdetity = $post->get('callIdetity', '0', 'INT');
		$ordering    = $post->get('sorting', '1', 'INT');
		$extraParam  = $post->get('extraParams', '', 'ARRAY');

		// Get the comment sorting latest or oldest
		switch ($ordering)
		{
			case 1:
				$sortingType = 'DESC';
				break;
			case 2:
				$sortingType = 'ASC';
				break;
		}

		// If this request not call from assending decending function
		if (!$callIdetity)
		{
			$annotaionIdsArr = json_decode($post->get('annotaionIdsArr', '', 'STRING'));
			$viewmoreId      = $post->get('viewmoreId', '', 'ARRAY');
		}
		else
		{
			$annotaionIdsArr = '';
			$viewmoreId      = '';
		}

		$childrensId = '';
		$getchildren = $post->get('getchildren', '0', 'INT');

		if ($getchildren)
		{
			$childrensId = ($post->get('childrensId', '', 'ARRAY'));
		}

		$model = $this->getModel('jlike_likes');
		$response = json_encode(
								$model->getCommentsData(
								$element_id, $element, $getchildren, $childrensId,
								$sortingType, $annotaionIdsArr, $viewmoreId, '', $extraParam
								)
								);
		echo $response;

		jexit();
	}

	/**
	 * IncreaseLikeCount
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function increaseLikeCount()
	{
		$input          = JFactory::getApplication()->input;
		$post           = $input->post;
		$annotationid   = $post->get('annotationid', '0', 'INT');
		$comment        = $post->get('comment', '', 'STRING');
		$extraParams        = $post->get('extraParams', '', 'ARRAY');

		$comjlikeHelper = new comjlikeHelper;
		$response       = $comjlikeHelper->increaseLikeCount($annotationid, $comment, $extraParams);
		echo json_encode($response);
		jexit();
	}

	/**
	 * Method to Dislike the comment
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function increaseDislikeCount()
	{
		$input          = JFactory::getApplication()->input;
		$post           = $input->post;
		$annotationid   = $post->get('annotationid', '0', 'INT');
		$comment        = $post->get('comment', '', 'STRING');
		$extraParams        = $post->get('extraParams', '', 'ARRAY');
		$comjlikeHelper = new comjlikeHelper;
		$response       = $comjlikeHelper->increaseDislikeCount($annotationid, $comment, $extraParams);
		echo json_encode($response);
		jexit();
	}

	/**
	 * Method to getUserByCommentId
	 *
	 * @return  void
	 *
	 * @since 3.0
	 */
	public function getUserByCommentId()
	{
		$input           = JFactory::getApplication()->input;
		$post            = $input->post;
		$annotationid    = $post->get('annotationid', '0', 'INT');
		$likedOrdisliked = $post->get('likedOrdisliked', '1', 'INT');
		$comjlikeHelper  = new comjlikeHelper;
		$response        = $comjlikeHelper->getUserByCommentId($annotationid, $likedOrdisliked);
		echo json_encode($response);
		jexit();
	}

	/**
	 * Delete the lable list.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function jlike_deleteList()
	{
		$jinput = JFactory::getApplication()->input;

		// Get list id
		$lableId = $jinput->get("lableId");

		$model = $this->getModel('likes');
		$response = $model->jlike_deleteList($lableId);

		echo json_encode($response);
		jexit();
	}

	/**
	 * Delete the likes from my like view @TODO create seperate controller for likes view. (vm: Other functionality will break if i do now)
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function delete()
	{
		$jinput = JFactory::getApplication()->input;

		// Get list id
		$lableId = $jinput->get("lableId");

		$model = $this->getModel('likes');
		$response = $model->jlike_deleteList($lableId);

		echo json_encode($response);
		jexit();
	}

	/**
	 * On status change change.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function changeItemStatus()
	{
		$comjlikeHelper = new comjlikeHelper;
		$jinput = JFactory::getApplication()->input;

		$element = $jinput->get('element');
		$element_id = $jinput->get('element_id');
		$like_statusId = $jinput->get('status_id', '');

		$response['status'] = 0;
		$response['msg'] = '';

		if (!empty($element) || $element_id || !empty($like_statusId))
		{
			$content_id = $comjlikeHelper->getContentId($element_id, $element);

			// Check whether user is liked to content
			$isliked = $comjlikeHelper->isUserLikedContent($content_id, JFactory::getUser()->id);

			if ($isliked)
			{
				$response['status'] = $comjlikeHelper->storeExtraData($content_id, $like_statusId);
			}
		}
		else
		{
			$response['msg'] = ' PARAMETERS_CHECK_FAIL';
		}

		echo json_encode($response);
		jexit();
	}

	/**
	 * addContenttoList.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function manageListforContent()
	{
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		$input = JFactory::getApplication()->input;
		$post  = $input->getArray($_POST);
		$model         = $this->getModel('jlike_likes');
		$result       = 0;

		if ($post)
		{
			$result = $model->manageListforContent($post);

			if ($result)
			{
				echo json_encode($result);
			}
			else
			{
				echo json_encode(-1);
			}
		}
		else
		{
			echo json_encode($result);
		}

		jexit();
	}

	/**
	 * Send Reminders to Users before due date
	 *
	 * @return nothing
	 */
	public function remindersCron()
	{
		// Load jlike reminders model to call api to send the reminders
		require_once JPATH_ADMINISTRATOR . '/components/com_jlike/models/reminders.php';

		// Call the actual cron code which will send the reminders
		$model         = JModelLegacy::getInstance('Reminders', 'JlikeModel');
		$reminders     = $model->sendReminders();
	}
}
