<?php
/**
 * @version    SVN: <svn_id>
 * @package    Jlike
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.model');
/**
 * JlikeModelAnnotations
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JlikeModeljlike_Likes extends JModelLegacy
{
	/**
	 * construct
	 *
	 * @since	1.6
	 */
	public function __construct()
	{
		parent::__construct();
		$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

		if (!class_exists('comjlikeHelper'))
		{
			// Require_once $path;
			JLoader::register('comjlikeHelper', $helperPath);
			JLoader::load('comjlikeHelper');
		}

		$this->jlikehelperObj = new comjlikeHelper;

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$this->jlikemainhelperObj = new ComjlikeMainHelper;
	}

	/**
	 * getUserlabels.
	 *
	 * @param   integer  $contentId  id.s
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getUserlabels($contentId = 0)
	{
		$res    = '';
		$db     = JFactory::getDBO();
		$userId = JFactory::getUser()->id;
		$sql    = "SELECT id,title FROM #__jlike_like_lists WHERE user_id='{$userId}'";
		$db->setQuery($sql);
		$res = $db->loadObjectList();

		if ($contentId > 0)
		{
			foreach ($res as $list)
			{
				$list_ids[] = $list->id;
			}

			if (!empty($list_ids))
			{
				$sql = "SELECT list_id FROM #__jlike_likes_lists_xref WHERE content_id='{$contentId}' AND list_id IN('" . implode("','", $list_ids) . "') ";
				$db->setQuery($sql);
				$content_lists = $db->loadColumn();

				foreach ($res as $ind => $listobj)
				{
					$res[$ind]->checked = '';

					if (in_array($listobj->id, $content_lists))
					{
						$res[$ind]->checked = 'checked';
					}
				}
			}
		}

		return $res;
	}

	/**
	 * getbttonset.
	 *
	 * @param   integer  $bid  id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getbttonset($bid)
	{
		$db  = JFactory::getDBO();
		$sql = "SELECT * FROM #__jlike where id='" . $bid . "'";
		$db->setQuery($sql);
		$item = $db->loadObject();

		return $item;
	}

	/**
	 * getbttonset.
	 *
	 * @param   integer  $cont_id       id.
	 * @param   integer  $element       element.
	 * @param   integer  $pwltcb_check  pwltcb_check.
	 * @param   array    $extraParams   Additional parameters
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getData($cont_id, $element, $pwltcb_check, $extraParams = array())
	{
		$db                 = JFactory::getDBO();
		$userid             = JFactory::getUser()->id;
		$data               = $pwltcb = array();
		$data['likeaction'] = $data['dislikeaction'] = '';
		$data['likecount']  = $data['dislikecount'] = 0;
		$query              = "SELECT jl.id,jl.like,jl.dislike FROM `#__jlike_likes` AS jl,#__jlike_content AS jc
								WHERE jc.element_id='$cont_id' AND jc.element='$element' AND jl.userid='" . $userid . "' AND jl.content_id = jc.id ";
		$db->setQuery($query);
		$res = $db->loadObject();

		if ($res)
		{
			if ($res->like)
			{
				$data['likeaction']    = 'unlike';
				$data['dislikeaction'] = 'dislike';
			}
			elseif ($res->dislike)
			{
				$data['dislikeaction'] = 'undislike';
				$data['likeaction']    = 'like';
			}
			else
			{
				$data['likeaction']    = 'like';
				$data['dislikeaction'] = 'dislike';
			}
		}
		else
		{
			$data['likeaction']    = 'like';
			$data['dislikeaction'] = 'dislike';
		}

		$query = "SELECT jc.id,jc.like_cnt,jc.dislike_cnt FROM #__jlike_content AS jc WHERE jc.element_id='$cont_id' AND jc.element='$element'";
		$db->setQuery($query);
		$res = $db->loadObject();

		if ($res)
		{
			$data['likecount']    = $res->like_cnt;
			$data['dislikecount'] = $res->dislike_cnt;
			$data['content_id']   = $res->id;

			if ($pwltcb_check == 1)
			{
				$pwltcb               = $this->jlikehelperObj->getPeoplelikedthisContentBefor($res->id, $extraParams);
			}
		}

		$data['pwltcb']        = $pwltcb;
		$data['liketext']      = JText::_('LIKE');
		$data['unliketext']    = JText::_('UNLIKE');
		$data['disliketext']   = JText::_('DISLIKE');
		$data['undisliketext'] = JText::_('UNDISLIKE');

		return $data;
	}

	/**
	 * Method to get allow rating to bought the product user
	 *
	 * @param   integer  $cont_id  id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	/* Vm: As this is plugin Q2C specific code so moved in plugin
	public function getAllowRating($cont_id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT ko.id
			FROM #__kart_order_item as koi
			LEFT JOIN #__kart_orders as ko ON ko.id = koi.order_id
			LEFT JOIN #__users as u ON u.id = ko.user_info_id
			WHERE koi.item_id='{$cont_id}'
			AND u.id = '" . JFactory::getUser()->id . "'
			AND ko.status = 'C'";

		$db->setQuery($query);

		return $result = count($db->loadObjectList());
	}*/

	/**
	 * getUserRatingAvg.
	 *
	 * @param   object  $result  result obj.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getUserRatingAvg($result)
	{
		$params        = $this->jlikemainhelperObj->getjLikeParams();
		$rating_length = $params->get('rating_length');

		foreach ($result as $row)
		{
			$userRating     = $row->user_rating;
			$ratingUpto     = $row->rating_upto;
			$avarageRatingA = $rating_length * $userRating;

			$row->user_rating = $avarageRatingA / $ratingUpto;
		}

		return $result;
	}

	/**
	 * Method to get product rating avarage
	 *
	 * @param   integer  $element_id  id.
	 * @param   integer  $element     element.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getProductRatingAvg($element_id, $element)
	{
		$params        = $this->jlikemainhelperObj->getjLikeParams();
		$rating_length = $params->get('rating_length');
		$db            = JFactory::getDBO();
		$select_or_not_id_query = "";

		$query = "SELECT jc.id as contentid,jc.element_id,ant.user_id,ant.id as annotation_id,ant.annotation,ant.annotation as
		 smileyannotation,ant.parent_id,ant.annotation_date,u.name,u.email,jr.rating_upto,jr.user_rating
			FROM #__jlike_content as jc
			LEFT JOIN #__jlike_annotations as ant ON jc.id=ant.content_id
			LEFT JOIN #__jlike_rating as jr ON jc.id=jr.content_id AND jr.user_id = ant.user_id
			LEFT JOIN #__users as u ON u.id =  ant.user_id
			WHERE jc.element_id='{$element_id}'
			AND jc.element='{$element}'
			$select_or_not_id_query
			AND ant.state=1
			AND ant.note='2'";

		$db->setQuery($query);
		$result = $db->loadObjectList();

		$i     = 0;
		$count = 0;

		foreach ($result as $row)
		{
			$avarageRatingA   = $rating_length * $row->user_rating;
			$row->user_rating = $avarageRatingA / $row->rating_upto;
			$i                = $i + $row->user_rating;
			$count++;
		}

		if ($count != 0)
		{
			$avg = $i / $count;
		}
		else
		{
			$avg = 0;
		}

		return $avg;
	}

	/**
	 * Method to get The comments related data e.g userids,user names, user profile pics, comments against user
	 *
	 * @param   integer   $eleId            id.
	 * @param   integer   $ele              element.
	 * @param   integer   $childs           getchildren.
	 * @param   integer   $childsId         childrensId.
	 * @param   string    $order            ordering.
	 * @param   string    $annotaionIdsArr  annotaionIdsArr.
	 * @param   string    $viewmoreIdArr    viewmoreIdArr.
	 * @param   Interger  $note_type        Type of note.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getRatingReviewData($eleId, $ele, $childs, $childsId, $order = 'DESC', $annotaionIdsArr = '', $viewmoreIdArr = '', $note_type = '')
	{
		$db                     = JFactory::getDBO();
		$select_or_not_id_query = '';
		$limit                  = '';

		// If call to print child comment
		if ($childs)
		{
			if (!empty($childsId[0]))
			{
				$id_in                  = join(',', $childsId);
				$select_or_not_id_query = " AND ant.id IN (" . $id_in . ") ";
			}
		}
		else
		{
			// View more / latest or oldest
			if (!empty($viewmoreIdArr[0]))
			{
				$result                 = array_merge((array) $annotaionIdsArr, (array) $viewmoreIdArr);
				$not_ind_id             = join(',', $result);
				$select_or_not_id_query = " AND ant.id NOT IN (" . $not_ind_id . ") AND ant.parent_id=0 ";
			}
			elseif (!empty($annotaionIdsArr[0]))
			{
				$not_ind_id             = join(',', $annotaionIdsArr);
				$select_or_not_id_query = " AND ant.id NOT IN (" . $not_ind_id . ")  AND ant.parent_id=0 ";
			}
			else // Oon default page load or refresh
			{
				$select_or_not_id_query = " AND ant.parent_id=0 ";
			}

			// LIMIT
			$params        = $this->jlikemainhelperObj->getjLikeParams();
			$comment_limit = $params->get('no_of_commets_to_show');
			$limit         = '';

			if (!empty($comment_limit))
			{
				$limit = "LIMIT 0," . $comment_limit;
			}
		}

		if (!$order)
		{
			$order = ' DESC ';
		}

		$query = "SELECT jc.id as contentid,jc.element_id,ant.user_id,ant.id as annotation_id,ant.annotation,ant.annotation as
		 smileyannotation,ant.parent_id,ant.annotation_date,u.name,u.email,jr.rating_upto,jr.user_rating
			FROM #__jlike_content as jc
			LEFT JOIN #__jlike_annotations as ant ON jc.id=ant.content_id
			LEFT JOIN #__jlike_rating as jr ON jc.id=jr.content_id AND jr.user_id = ant.user_id
			LEFT JOIN #__users as u ON u.id =  ant.user_id
			WHERE jc.element_id='{$eleId}'
			AND jc.element='$ele'
			$select_or_not_id_query
			AND ant.state=1
			AND ant.note='" . $note_type . "'
			ORDER BY ant.annotation_date " . $order . " " . $limit;

		$db->setQuery($query);
		$result = $db->loadObjectList();

		// Get the user rating avarage against review
		$result = $this->getUserRatingAvg($result);

		// Get the comment like & dislike count against comment
		$result = $this->getLikeDislikeCount($result);

		// Get the comment commentDateTime
		$result = $this->commentDateTime($result);
		$result = $this->replaceSmileyAsImage($result);

		// Get the user info user profile pics & url
		$data = $this->getUserInfo($result);
		$data = $this->getReplyAgainstComment($result);

		return $data;
	}

	/**
	 * Method to get The comments related data e.g userids,user names, user profile pics, comments against user
	 *
	 * @param   integer   $contId    id.
	 * @param   integer   $ele       element.
	 * @param   integer   $children  getchildren.
	 * @param   integer   $childsId  childrensId.
	 * @param   string    $order     ordering.
	 * @param   string    $annoArr   annotaionIdsArr.
	 * @param   string    $vmArr     viewmoreIdArr.
	 * @param   Interger  $notetype  Type of note.
	 * @param   array     $exParam   Extra parameter array
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getCommentsData($contId, $ele, $children, $childsId, $order = 'DESC', $annoArr = '', $vmArr = '', $notetype = '', $exParam = array())
	{
		$db                     = JFactory::getDBO();
		$select_or_not_id_query = '';
		$limit                  = '';

		// If call to print child comment
		if ($children)
		{
			if (!empty($childsId[0]))
			{
				$id_in                  = join(',', $childsId);
				$select_or_not_id_query = " AND ant.id IN (" . $id_in . ") ";
			}
		}
		else
		{
			// View more / latest or oldest
			if (!empty($vmArr[0]))
			{
				$result                 = array_merge((array) $annoArr, (array) $vmArr);
				$not_ind_id             = join(',', $result);
				$select_or_not_id_query = " AND ant.id NOT IN (" . $not_ind_id . ") AND ant.parent_id=0 ";
			}
			elseif (!empty($annoArr[0]))
			{
				$not_ind_id             = join(',', $annoArr);
				$select_or_not_id_query = " AND ant.id NOT IN (" . $not_ind_id . ")  AND ant.parent_id=0 ";
			}
			else // Oon default page load or refresh
			{
				$select_or_not_id_query = " AND ant.parent_id=0 ";
			}

			// LIMIT
			$params        = $this->jlikemainhelperObj->getjLikeParams();
			$comment_limit = $params->get('no_of_commets_to_show');
			$limit         = '';

			if (!empty($comment_limit))
			{
				$limit = "LIMIT 0," . $comment_limit;
			}
		}

		if (!$order)
		{
			$order = ' DESC ';
		}

		$query = "SELECT jc.id as contentid,jc.element_id,ant.user_id,ant.id as annotation_id,ant.annotation,ant.annotation as
		 smileyannotation,ant.parent_id,ant.annotation_date,u.name,u.email
			FROM #__jlike_content as jc
			LEFT JOIN #__jlike_annotations as ant ON jc.id=ant.content_id
			LEFT JOIN #__users as u ON u.id =  ant.user_id
			WHERE jc.element_id='$contId'
			AND jc.element='$ele'
			$select_or_not_id_query
			AND ant.state=1
			AND ant.note='" . $notetype . "'
			ORDER BY ant.annotation_date " . $order . " " . $limit;

		$db->setQuery($query);
		$result = $db->loadObjectList();

		// Get the comment like & dislike count against comment
		$result = $this->getLikeDislikeCount($result);

		// Get the comment commentDateTime
		$result = $this->commentDateTime($result);
		$result = $this->replaceSmileyAsImage($result);

		$data = $this->getReplyAgainstComment($result);

		$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

		if (!class_exists('ComjlikeMainHelper'))
		{
			// Require_once $path;
			JLoader::register('ComjlikeMainHelper', $helperPath);
			JLoader::load('ComjlikeMainHelper');
		}

		$ComjlikeMainHelper = new ComjlikeMainHelper;

		foreach ($data as $index => $comment_info)
		{
			$student                = JFactory::getUser($comment_info->user_id);
			$comment_userid         = $comment_info->user_id;
			$data[$index]->username = JFactory::getUser($comment_userid)->username;

			$plgData = array("plg_type" => $exParam['plg_type'], "plg_name" => $exParam['plg_name']);
			$sLibObj  = $ComjlikeMainHelper->getSocialLibraryObject('', $plgData);
			$data[$index]->avtar    = $sLibObj->getAvatar($student, 50);
			$link                   = '';
			$link                   = $profileUrl = $sLibObj->getProfileUrl($student);

			if ($profileUrl)
			{
				if (!parse_url($profileUrl, PHP_URL_HOST))
				{
					$link = JUri::root() . substr(JRoute::_($sLibObj->getProfileUrl($student)), strlen(JUri::base(true)) + 1);
				}
			}

			$data[$index]->user_profile_url = $link;
		}

		return $data;
	}

	/**
	 * Get the number of reply against comment
	 *
	 * @param   Array  $result  result.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getReplyAgainstComment($result)
	{
		$db = JFactory::getDBO();

		foreach ($result as $ind => $row)
		{
			$query = "SELECT id
			FROM #__jlike_annotations
			WHERE parent_id=" . $row->annotation_id . " GROUP BY id";
			$db->setQuery($query);
			$result[$ind]->children = $db->loadColumn();
			$row->replycount        = count($result[$ind]->children);
		}

		return $result;
	}

	/**
	 * getCommentsCount.
	 *
	 * @param   integer   $cont_id    result.
	 * @param   string    $element    element.
	 * @param   Interger  $note_type  Type of note.
	 * @param   Interger  $loginuser  User.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getReviewsCount($cont_id, $element, $note_type = '', $loginuser = '')
	{
		if ($element == 'com_content.category')
		{
			$element = 'com_content.article';
		}

		$loginuser_where = '';

		if ($loginuser == 'loginuser')
		{
			$loginuser_where = "AND ant.user_id = '" . JFactory::getUser()->id . "'";
		}

		$comment_count = array();
		$db            = JFactory::getDBO();

		$query = "SELECT count(ant.id) as comment_count
			FROM #__jlike_content as jc
			LEFT JOIN #__jlike_annotations as ant ON jc.id=ant.content_id
			LEFT JOIN #__users as u ON u.id =  ant.user_id
			WHERE jc.element_id='$cont_id'
			AND jc.element='$element'
			AND ant.annotation <> ''
			AND ant.parent_id=0
			AND ant.state = 1
			AND ant.note = '" . $note_type . "'
			{$loginuser_where}
			";

		$db->setQuery($query);
		$comment_count[0] = $result = $db->loadResult();

		$params        = $this->jlikemainhelperObj->getjLikeParams();
		$comment_limit = $params->get('no_of_commets_to_show');
		$limit         = '';

		if ($comment_limit)
		{
			if ($comment_limit < $result)
			{
				$result           = $result - $comment_limit;
				$comment_count[1] = $result;

				return $comment_count;
			}
		}

		return $comment_count[0];
	}

	/**
	 * getCommentsCount.
	 *
	 * @param   integer   $cont_id    result.
	 * @param   string    $element    element.
	 * @param   Interger  $note_type  Type of note.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getCommentsCount($cont_id, $element, $note_type = '')
	{
		if ($element == 'com_content.category')
		{
			$element = 'com_content.article';
		}

		$comment_count = array();
		$db            = JFactory::getDBO();

		$query = "SELECT count(ant.id) as comment_count
			FROM #__jlike_content as jc
			LEFT JOIN #__jlike_annotations as ant ON jc.id=ant.content_id
			LEFT JOIN #__users as u ON u.id =  ant.user_id
			WHERE jc.element_id='$cont_id'
			AND jc.element='$element'
			AND ant.annotation <> ''
			AND ant.state = 1
			AND ant.note = '" . $note_type . "'
			";

		$db->setQuery($query);
		$comment_count[0] = $result = $db->loadResult();

		$params        = $this->jlikemainhelperObj->getjLikeParams();
		$comment_limit = $params->get('no_of_commets_to_show');
		$limit         = '';

		if ($comment_limit)
		{
			if ($comment_limit < $result)
			{
				$result           = $result - $comment_limit;
				$comment_count[1] = $result;

				return $comment_count;
			}
		}

		return $comment_count[0];
	}

	/**
	 * replaceSmileyAsImage.
	 *
	 * @param   array  $result  result.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function replaceSmileyAsImage($result)
	{
		$basePath = JUri::root() . 'components/com_jlike/assets/images/';

		foreach ($result as $row)
		{
			$row->smileyannotation = str_replace(':)', '<img src="' . $basePath . 'smileys/smile.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace(':-)', '<img src="' . $basePath . 'smileys/smile.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace(':(', '<img src="' . $basePath . 'smileys/sad.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace(':-(', '<img src="' . $basePath . 'smileys/sad.jpg" />', $row->smileyannotation);

			$row->smileyannotation = str_replace(';)', '<img src="' . $basePath . 'smileys/wink.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace(';-)', '<img src="' . $basePath . 'smileys/wink.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace(';(', '<img src="' . $basePath . 'smileys/cry.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace('B-)', '<img src="' . $basePath . 'smileys/cool.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace('B)', '<img src="' . $basePath . 'smileys/cool.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace(':D', '<img src="' . $basePath . 'smileys/grin.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace(':-D', '<img src="' . $basePath . 'smileys/grin.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace(':o', '<img src="' . $basePath . 'smileys/shocked.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace(':O', '<img src="' . $basePath . 'smileys/shocked.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace(':-o', '<img src="' . $basePath . 'smileys/shocked.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace(':-O', '<img src="' . $basePath . 'smileys/shocked.jpg" />', $row->smileyannotation);
			$row->smileyannotation = str_replace(':-3', '<img src="' . $basePath . 'smileys/love.png" />', $row->smileyannotation);
			$row->smileyannotation = $this->parsetags($row->smileyannotation);
		}

		return $result;
	}

	/**
	 * parsetags.
	 *
	 * @param   array  $result  result.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function parsetags($result)
	{
		$matches = array();
		preg_match_all("/{profiletag([^}]*)}/", $result, $matches);
		$k = 0;

		foreach ($matches[1] as $ptag)
		{
			$id   = explode('|', $ptag);
			$id   = $id[0];
			$name = explode('|', $ptag);

			if (isset($name[1]))
			{
				$name = $name[1];
			}

			$path          = JURI::BASE() . 'index.php?option=com_community&view=profile&userid=' . $id;
			$link[$k]['0'] = '<a href="' . $path . '">' . $name . '</a>';
			$nm            = $id . '|' . $name;
			$profiletg     = '{profiletag';
			$link[$k]['1'] = $profiletg . '' . $nm . '}';
			$result        = str_replace($link[$k]['1'], $link[$k]['0'], $result);
			$k++;
		}

		return $result;
	}

	/**
	 * getUserInfo.
	 *
	 * @param   array  $data  userdata.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getUserInfo($data)
	{
		$socialintegrationHelper = new socialintegrationHelper;

		foreach ($data as $row)
		{
			$user                  = new stdClass;
			$user->id              = $row->user_id;
			$user->email           = $row->email;
			$row->user_profile_url = $socialintegrationHelper->getUserProfileUrl($row->user_id);
			$row->avtar            = $socialintegrationHelper->getUserAvatar($user);
		}

		return $data;
	}

	/**
	 * SaveComment.
	 *
	 * @param   integer  $annotation_id  annotation_id.
	 * @param   string   $comment        comment.
	 * @param   string   $note_type      note type.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function SaveComment($annotation_id, $comment, $note_type)
	{
		$date           = JFactory::getDate();
		$db             = JFactory::getDBO();
		$update_obj     = new stdClass;
		$update_obj->id = $annotation_id;

		/* Replace anchor tag of user with profile tag*/
		$comment                = $this->jlikehelperObj->getProfiletag($comment);
		$update_obj->annotation = $comment;

		// This is comment means not a note
		$update_obj->note = $note_type;

		if ($note_type == 2)
		{
			$update_obj->annotation_date = JFactory::getDate()->toSQL();
		}

		// $update_obj->annotation_date=$date->Format('Y-m-d H:i:s');
		if (!$db->updateObject('#__jlike_annotations', $update_obj, 'id', true))
		{
			$db->stderr();
		}
		else
		{
			return '1';
		}
	}

	/**
	 * DeleteReviews.
	 *
	 * @param   integer  $annotation_id  annotation_id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function DeleteReviews($annotation_id)
	{
		$result       = $this->getChildren($annotation_id);
		$deleteRating = $this->deleteRating($annotation_id);

		if ($result)
		{
			foreach ($result as $row)
			{
				$arr[] = $row;
			}
		}

		$arr   = array();
		$arr[] = $annotation_id;
		$count = count($arr);

		for ($i = 0; $i < $count; $i++)
		{
			$result = $this->getChildren($arr[$i]);

			if ($result)
			{
				foreach ($result as $row)
				{
					$arr[] = $row;
				}
			}

			$count = count($arr);
		}

		if (isset($arr))
		{
			$arrChildren = implode(',', $arr);

			if ($arrChildren)
			{
				$db    = JFactory::getDBO();
				$query = "DELETE FROM  #__jlike_annotations WHERE id IN(" . $arrChildren . ")";
				$db->setQuery($query);

				if (!$db->execute())
				{
					$db->stderr();
				}
				else
				{
					return $arr;
				}
			}
		}
	}

	/**
	 * deleteRating.
	 *
	 * @param   integer  $annotation_id  content element id
	 *
	 * @since   2.2
	 *
	 * @return  list.
	 */
	public function deleteRating($annotation_id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT content_id FROM  #__jlike_annotations WHERE id=" . $annotation_id;
		$db->setQuery($query);
		$content_id = $db->loadResult();

		if ($content_id)
		{
			$query = "DELETE FROM #__jlike_rating
					WHERE user_id=" . JFactory::getUser()->id . "
					AND content_id= " . $content_id;
			$db->setQuery($query);

			if (!$db->execute($query))
			{
				return $db->stderr();
			}
			else
			{
				return;
			}
		}

		return;
	}

	/**
	 * DeleteComment.
	 *
	 * @param   integer  $annotation_id  annotation_id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function DeleteComment($annotation_id)
	{
		$result = $this->getChildren($annotation_id);

		if ($result)
		{
			foreach ($result as $row)
			{
				$arr[] = $row;
			}
		}

		$arr   = array();
		$arr[] = $annotation_id;
		$count = count($arr);

		for ($i = 0; $i < $count; $i++)
		{
			$result = $this->getChildren($arr[$i]);

			if ($result)
			{
				foreach ($result as $row)
				{
					$arr[] = $row;
				}
			}

			$count = count($arr);
		}

		if (isset($arr))
		{
			$arrChildren = implode(',', $arr);

			if ($arrChildren)
			{
				$db    = JFactory::getDBO();
				$query = "DELETE FROM  #__jlike_annotations WHERE id IN(" . $arrChildren . ")";
				$db->setQuery($query);

				if (!$db->execute())
				{
					$db->stderr();
				}
				else
				{
					return $arr;
				}
			}
		}
	}

	/**
	 * getChildren.
	 *
	 * @param   integer  $annotation_id  annotation_id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getChildren($annotation_id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT id FROM  #__jlike_annotations WHERE parent_id=" . $annotation_id;
		$db->setQuery($query);
		$result = $db->loadColumn();

		if ($result)
		{
			foreach ($result as $row)
			{
				$arrChildren[] = $row;
			}

			return $arrChildren;
		}
	}

	/**
	 * getLikeDislikeCount.
	 *
	 * @param   object  $result  result obj.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getLikeDislikeCount($result)
	{
		$db     = JFactory::getDBO();
		$userId = JFactory::getUser()->id;

		foreach ($result as $row)
		{
			// Get the like count against comment
			$query = "SELECT count(id) as likecount
			FROM  #__jlike_likes
			WHERE `annotation_id`=" . $row->annotation_id . "
			 AND  `like`=1 GROUP BY `annotation_id`";
			$db->setQuery($query);
			$likecount      = $db->loadResult();
			$row->likeCount = $likecount;

			// Get the dislike count against comment
			$query = "SELECT count(id) as dislikecount
			FROM  #__jlike_likes
			WHERE `annotation_id`=" . $row->annotation_id . "
			 AND  `dislike`=1 GROUP BY `annotation_id`";
			$db->setQuery($query);
			$dislikecount      = $db->loadResult();
			$row->dislikeCount = $dislikecount;

			// Check that the current user like or dislike this comment
			$query = "SELECT `like`,`dislike`
			FROM  #__jlike_likes
			WHERE `annotation_id`=" . $row->annotation_id . "
			 AND  `userid`=" . $userId;
			$db->setQuery($query);
			$data = $db->loadObject();

			if (!empty($data))
			{
				if ($data->like)
				{
					// User like on comment
					$row->userLikeDislike = 1;
				}
				elseif ($data->dislike)
				{
					// User dislike on comment
					$row->userLikeDislike = 2;
				}
			}
			else
			{
				$row->userLikeDislike = 0;
			}
		}

		return $result;
	}

	/**
	 * commentDateTime.
	 *
	 * @param   object  $result  result obj.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function commentDateTime($result)
	{
		foreach ($result as $row)
		{
			$time = JHtml::date($row->annotation_date, JText::_('COM_JLIKE_COMMENT_TIME_FORMAT'), true);

			$row->date = JHtml::date($row->annotation_date, JText::_('COM_JLIKE_COMMENT_DATE_FORMAT'));
			$row->time = JText::_('COM_JLIKE_COMMENT_DATE_TIME_SEPERATOR') . $time;
		}

		return $result;
	}

	/**
	 * manageListforContent.
	 *
	 * @param   ARRAY  $post  result obj.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function manageListforContent($post)
	{
		$db = JFactory::getDBO();

		if (isset($post['list_id']))
		{
			if (isset($post['content_id']) && !empty($post['content_id']))
			{
				$content_id = $post['content_id'];
			}
			else
			{
				$content_id = $this->jlikehelperObj->addContent($post['element_id'], $post['element'], $post['url'], $post['title']);
			}

			$db = JFactory::getDBO();

			$query = "SELECT * FROM #__jlike_likes_lists_xref  WHERE content_id='" . $content_id . "' AND list_id='" . $post['list_id'] . "'";
			$db->setQuery($query);
			$res = $db->loadObject();

			if ($post['action'] == 'add')
			{
				if (!$res)
				{
					$insert_obj             = new stdClass;
					$insert_obj->content_id = $content_id;
					$insert_obj->list_id    = $post['list_id'];

					try
					{
						$db->insertObject('#__jlike_likes_lists_xref', $insert_obj);
					}
					catch (Exception $e)
					{
						JLog::add($e->getMessage(), JLog::ERROR, 'com_jlike');

						return 0;
					}
				}
			}
			else
			{
				if ($res)
				{
					$db = JFactory::getDbo();

					$query = $db->getQuery(true);

					$conditions = array(
						$db->quoteName('content_id') . " = " . $content_id,
						$db->quoteName('list_id') . " = " . $post['list_id']
					);

					$query->delete($db->quoteName('#__jlike_likes_lists_xref'));
					$query->where($conditions);
					$db->setQuery($query);

					try
					{
						$result = $db->execute();
					}
					catch (Exception $e)
					{
						JLog::add($e->getMessage(), JLog::ERROR, 'com_jlike');

						return 0;
					}
				}
			}

			return 1;
		}

		return 0;
	}

	/**
	 * Get Note saved by user against lesson.
	 *
	 * @param   STRING   $element     result obj.
	 * @param   INTEGER  $element_id  result obj.
	 * @param   INTEGER  $user_id     result obj.
	 *
	 * @return NOTE
	 *
	 * @since 1.0.0
	 */
	public function geUserNote($element, $element_id, $user_id)
	{
		$db    = JFactory::getDBO();
		$query = "SELECT jc.id FROM #__jlike_content AS jc WHERE jc.element='" . $element . "' AND jc.element_id='" . $element_id . "'";
		$db->setQuery($query);
		$content_id = $db->loadResult();

		$note = '';

		if ($content_id)
		{
			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('annotation');
			$query->from('#__jlike_annotations');
			$query->where('content_id=' . $content_id . ' AND note=1 AND user_id=' . $user_id);
			$db->setQuery($query);
			$note = $db->loadResult();
		}

		return $note;
	}
}
