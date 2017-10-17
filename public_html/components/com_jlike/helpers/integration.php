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


/**
 * Integration helper
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class ComjlikeIntegrationHelper
{
	/**
	 * Function to push like,Data And comment to Activity Stream
	 *
	 * @param   object  $contentdata    Plugin name.
	 * @param   string  $type           Plugin name.
	 * @param   string  $commentAddedd  Plugin name.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function pushtoactivitystream($contentdata, $type = 'like', $commentAddedd = '', $integration = '')
	{
		// INSERT COMMENT ENTRIES TO KOMENTO COMPONENTS TABLE
		if ($contentdata->element == 'komento.comment')
		{
			$this->registerlike_Komento($contentdata);
		}
		// INSERT COMMENT ENTRIES TO KOMENTO COMPONENTS TABLE

		// Push activity to various activity streams
		// Set some of the data for activity
		$act_type = 'jlike';

		if ($type == 'like')
		{
			$actDes = JText::_('COM_JLIKE_LIKE_ACTIVITY');

			//  $actDes=str_replace('[username]',$contentdata->username,$actDes);
			$actDes = str_replace('[action]', $contentdata->verb, $actDes);

			// $actDes=str_replace('[link]',$contentdata->link,$actDes);
			$actorId        = $contentdata->userid;
			$actSubtype     = $contentdata->element;
			$link        = $contentdata->link;
			$title       = $contentdata->title;
			$act_access      = 0;
		}
		elseif ($type == 'comment')
		{
			if ($commentAddedd)
			{
				$actDes = JText::_('COM_JLIKE_LIKE_COMMENT_ADDED');
			}
			else
			{
				$actDes = JText::_('COM_JLIKE_LIKE_ACTIVITY_COMMENT');
			}

			$actDes = str_replace("[link]", "<a href=" . $contentdata->url . "> " . $contentdata->title . "</a>", $actDes);
			$actDes = str_replace("[comment]", $contentdata->comment, $actDes);

			// $actDes="added comment  on <a href=".$contentdata->url."> ".$contentdata->url."</a> as".$contentdata->comment;
			$actorId    = $contentdata->userid;
			$actSubtype = 'comment';
			$link    = '';
			$title   = "";
			$act_access  = $contentdata->access;
		}

		$result = $this->pushActivity($actorId, $act_type, $actSubtype, $actDes, $link, $title, $act_access, $integration);

		if (!$result)
		{
			return false;
		}

		return true;
	}

	/**
	 * Register like JS.
	 *
	 * @param   object  $contentdata  Plugin name.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function registerlikeJS($contentdata)
	{
		/* Defined JS Element in classification.ini
		/com_community.profile=Jomsocial Profile
		 *com_community.groups=Jomsocial Group
		 *com_community.album=Jomsocial Album
		 *com_community.photo=Jomsocial Photo
		 *com_community.videos=Jomsocial video
		 * com_community.events=Jomsocial Event
		 */
		$like->element = '';
		$like          = new stdClass;

		if ($contentdata->element == 'com_community.profile')
		{
			$like->element = 'profile';
		}
		elseif ($contentdata->element == 'com_community.groups')
		{
			$like->element = 'groups';
		}
		elseif ($contentdata->element == 'com_community.album')
		{
			$like->element = 'album';
		}
		elseif ($contentdata->element == 'com_community.photo')
		{
			$like->element = 'photo';
		}
		elseif ($contentdata->element == 'com_community.videos')
		{
			$like->element = 'videos';
		}
		elseif ($contentdata->element == 'com_community.events')
		{
			$like->element = 'events';
		}

		if (empty($like->element))
		{
			$like->element = '';
		}

		// Uid is element id
		if (strstr($contentdata->method, 'like'))
		{
			$this->addLikeJS($like->element, $contentdata->element_id);
		}

		if (strstr($contentdata->method, 'dislike'))
		{
			$this->addDislikeJS($like->element, $contentdata->element_id);
		}

		if (strstr($contentdata->method, 'unlike') or strstr($contentdata->method, 'undislike'))
		{
			$this->unlikeJS($like->element, $contentdata->element_id);
		}
	}

	/**
	 * Add like JS.
	 *
	 * @param   object   $element  Plugin name.
	 * @param   integer  $itemId   Plugin name.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function addLikeJS($element, $itemId)
	{
		$jspath = JPATH_ROOT . DS . 'components' . DS . 'com_community';

		if (file_exists($jspath))
		{
			include_once $jspath . DS . 'libraries' . DS . 'core.php';
			$my = CFactory::getUser();
		}
		else
		{
			return;
		}

		$like = JTable::getInstance('Like', 'CTable');
		$like->loadInfo($element, $itemId);

		$like->element = $element;
		$like->uid     = $itemId;

		// Check if user already like
		$likesInArray  = explode(',', trim($like->like, ','));
		array_push($likesInArray, $my->id);
		$likesInArray = array_unique($likesInArray);
		$like->like   = ltrim(implode(',', $likesInArray), ',');

		// Check if the user already dislike
		$dislikesInArray = explode(',', trim($like->dislike, ','));

		if (in_array($my->id, $dislikesInArray))
		{
			// Remove user dislike from array
			$key = array_search($my->id, $dislikesInArray);
			unset($dislikesInArray[$key]);

			$like->dislike = implode(',', $dislikesInArray);
		}

		$like->store();
	}

	/**
	 * Add dislike JS.
	 *
	 * @param   element  $element  Plugin name.
	 * @param   Integer  $itemId   Plugin name.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function addDislikeJS($element, $itemId)
	{
		$my = CFactory::getUser();
		$dislike = JTable::getInstance('Like', 'CTable');
		$dislike->loadInfo($element, $itemId);

		$dislike->element = $element;
		$dislike->uid     = $itemId;

		$dislikesInArray = explode(',', $dislike->dislike);
		array_push($dislikesInArray, $my->id);
		$dislikesInArray  = array_unique($dislikesInArray);
		$dislike->dislike = ltrim(implode(',', $dislikesInArray), ',');

		// Check if the user already like
		$likesInArray = explode(',', $dislike->like);

		if (in_array($my->id, $likesInArray))
		{
			// Remove user like from array
			$key = array_search($my->id, $likesInArray);
			unset($likesInArray[$key]);

			$dislike->like = implode(',', $likesInArray);
		}

		$dislike->store();
	}

	/**
	 * Add unlikelike JS.
	 *
	 * @param   element  $element  Plugin name.
	 * @param   Integer  $itemId   Plugin name.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function unlikeJS($element, $itemId)
	{
		$my = CFactory::getUser();

		$like = JTable::getInstance('Like', 'CTable');
		$like->loadInfo($element, $itemId);

		// Check if the user already like
		$likesInArray = explode(',', $like->like);

		if (in_array($my->id, $likesInArray))
		{
			// Remove user like from array
			$key = array_search($my->id, $likesInArray);
			unset($likesInArray[$key]);

			$like->like = implode(',', $likesInArray);
		}

		// Check if the user already dislike
		$dislikesInArray = explode(',', $like->dislike);

		if (in_array($my->id, $dislikesInArray))
		{
			// Remove user dislike from array
			$key = array_search($my->id, $dislikesInArray);
			unset($dislikesInArray[$key]);

			$like->dislike = implode(',', $dislikesInArray);
		}

		$like->store();
	}

	/**
	 * UpdateJSlikedata.
	 *
	 * @param   Object  $like  Plugin name.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function updateJSlikedata($like)
	{
		$query = "SELECT jl.id,jl.like,jl.dislike FROM #__community_likes AS cl";
		$query .= " WHERE cl.element='" . $like->element . "' AND Cl.uid=$like->uid AND ";
		$query .= " (cl.like=$like->userid OR cl.dislike=$like->userid)";
		$db->setQuery($query);
		$likepresent = $db->loadResult();

		if ($likepresent)
		{
			// If alredy liked content
			if ($likepresent->like)
			{
			}
		}
	}

	/**
	 * PushToEasySocialActivity.
	 *
	 * @param   integer  $actorId     user id.
	 * @param   integer  $act_type    user id.
	 * @param   integer  $actSubtype  user id.
	 * @param   string   $actDes      description of activity.
	 * @param   string   $link        link.
	 * @param   string   $title       title.
	 * @param   string   $act_access  act_access.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function pushActivity($actorId, $act_type, $actSubtype, $actDes, $link, $title, $act_access, $integration_option)
	{

		if (empty($integration_option))
		{
			$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

			if (!class_exists('ComjlikeHelper'))
			{
				// Require_once $path;
				JLoader::register('ComjlikeHelper', $helperPath);
				JLoader::load('ComjlikeHelper');
			}

			$jlikehelperObj = new ComjlikeMainHelper;

			$integration_option = $jlikehelperObj->getSocialIntegration();
		}

		if ($integration_option == 'joomla')
		{
			return true;
		}
		elseif ($integration_option == 'cb')
		{
			$result = $this->pushToCBActivity($actorId, $act_type, $actSubtype, $actDes, $link, $title, $act_access);

			if (!$result)
			{
			}
		}
		elseif ($integration_option == 'js')
		{
			$result = $this->pushToJomsocialActivity($actorId, $act_type, $actSubtype, $actDes, $link, $title, $act_access);

			if (!$result)
			{
			}
		}
		elseif ($integration_option == 'jomwall' and $allow_activity_stream == 1)
		{
			$result = $this->pushToJomwallActivity($actorId, $act_type, $actSubtype, $actDes, $link, $title, $act_access);

			if (!$result)
			{
				return false;
			}
		}
		elseif ($integration_option == 'easysocial')
		{
			$result = $this->pushToEasySocialActivity($actorId, $act_type, $actSubtype, $actDes, $link, $title);

			if (!$result)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * PushToEasySocialActivity.
	 *
	 * @param   integer  $actorId     user id.
	 * @param   integer  $act_type    user id.
	 * @param   integer  $actSubtype  user id.
	 * @param   string   $actDes      description of activity.
	 * @param   string   $link        link.
	 * @param   string   $title       title.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function pushToEasySocialActivity($actorId, $act_type = '', $actSubtype = '', $actDes = '', $link = '', $title = '')
	{
		require_once JPATH_ROOT . '/administrator/components/com_easysocial/includes/foundry.php';

		$linkHTML = '<a href="' . $link . '">' . $title . '</a>';

		if ($actorId != 0)
		{
			$myUser = Foundry::user($actorId);
		}

		$stream   = Foundry::stream();
		$template = $stream->getTemplate();

		$template->setActor($actorId, SOCIAL_TYPE_USER);
		$template->setContext($actorId, "ALL");
		$template->setVerb('jlike_comments');
		$template->setType(SOCIAL_STREAM_DISPLAY_MINI);

		if ($actorId != 0)
		{
			$userProfileLink = '<a href="' . $myUser->getPermalink() . '">' . $myUser->getName() . '</a>';
			$title           = ($userProfileLink . " " . $actDes . "" . $linkHTML);
		}
		else
		{
			$title = ("A guest " . $actDes);
		}

		$template->setTitle($title);
		$template->setAggregate(false);

		$template->setPublicStream('core.view');
		$stream->add($template);

		return true;
	}

	/**
	 * pushToCBActivity.
	 *
	 * @param   integer  $actorId     user id.
	 * @param   integer  $act_type    user id.
	 * @param   integer  $actSubtype  user id.
	 * @param   string   $actDes      description of activity.
	 * @param   string   $link        link.
	 * @param   string   $title       title.
	 * @param   string   $act_access  act_access.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function pushToCBActivity($actorId, $act_type, $actSubtype = '', $actDes = '', $link = '', $title = '', $act_access = '')
	{
		// Load CB framework
		global $_CB_framework, $mainframe;

		if (defined('JPATH_ADMINISTRATOR'))
		{
			if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php'))
			{
				echo 'CB not installed!';

				return false;
			}

			include_once JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php';
		}
		else
		{
			if (!file_exists($mainframe->getCfg('absolute_path') . '/administrator/components/com_comprofiler/plugin.foundation.php'))
			{
				echo 'CB not installed!';

				return false;
			}

			include_once $mainframe->getCfg('absolute_path') . '/administrator/components/com_comprofiler/plugin.foundation.php';
		}

		cbimport('cb.plugins');
		cbimport('cb.html');
		cbimport('cb.database');
		cbimport('language.front');
		cbimport('cb.snoopy');
		cbimport('cb.imgtoolbox');

		global $_CB_framework, $_CB_database, $ueConfig;

		// Load cb activity plugin class
		if (!file_exists(JPATH_SITE . "/components/com_comprofiler/plugin/user/plug_cbactivity/cbactivity.class.php"))
		{
			return false;
		}

		require_once JPATH_SITE . "/components/com_comprofiler/plugin/user/plug_cbactivity/cbactivity.class.php";

		// Push activity
		$linkHTML = '<a href="' . $link . '">' . $title . '</a>';

		$activity = new cbactivityActivity($_CB_database);
		$activity->set('user_id', $actorId);
		$activity->set('type', $act_type);
		$activity->set('subtype', $actSubtype);
		$activity->set('title', $actDes . ' ' . $linkHTML);
		$activity->set('icon', 'nameplate');
		$activity->set('date', cbactivityClass::getUTCDate());
		$activity->store();

		return true;
	}

	/**
	 * UpdateJSlikedata.
	 *
	 * @param   integer  $actorId     user id.
	 * @param   integer  $act_type    user id.
	 * @param   integer  $actSubtype  user id.
	 * @param   string   $actDes      description of activity.
	 * @param   string   $link        link.
	 * @param   string   $title       title.
	 * @param   string   $act_access  act_access.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function pushToJomsocialActivity($actorId, $act_type, $actSubtype = '', $actDes = '', $link = '', $title = '', $act_access = '')
	{
		/*load Jomsocial core*/
		$linkHTML = '';
		$jspath   = JPATH_ROOT . DS . 'components' . DS . 'com_community';

		if (file_exists($jspath))
		{
			include_once $jspath . DS . 'libraries' . DS . 'core.php';
		}

		// Push activity
		if ($title and $link)
		{
			$linkHTML = '<a href="' . $link . '">' . $title . '</a>';
		}

		$act          = new stdClass;
		$act->cmd     = 'wall.write';
		$act->actor   = $actorId;
		$act->target  = 0;
		$act->title   = '{actor} ' . $actDes . ' ' . $linkHTML;
		$act->content = '';
		$act->app     = 'wall';
		$act->cid     = 0;
		$act->access  = $act_access;
		CFactory::load('libraries', 'activities');

		if (defined('CActivities::COMMENT_SELF'))
		{
			$act->comment_id   = CActivities::COMMENT_SELF;
			$act->comment_type = 'profile.location';
		}

		if (defined('CActivities::LIKE_SELF'))
		{
			$act->like_id   = CActivities::LIKE_SELF;
			$act->like_type = 'profile.location';
		}

		$res = CActivityStream::add($act);

		return true;
	}

	/**
	 * UpdateJSlikedata.
	 *
	 * @param   integer  $actorId     user id.
	 * @param   integer  $act_type    user id.
	 * @param   integer  $actSubtype  user id.
	 * @param   string   $actDes      description of activity.
	 * @param   string   $link        link.
	 * @param   string   $title       title.
	 * @param   string   $act_access  act_access.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function pushToJomwallActivity($actorId, $act_type, $actSubtype = '', $actDes = '', $link = '', $title = '', $act_access ='')
	{
		if (!class_exists('AwdwallHelperUser'))
		{
			require_once JPATH_SITE . DS . 'components' . DS . 'com_awdwall' . DS . 'helpers' . DS . 'user.php';
		}

		$linkHTML   = '<a href="' . $link . '">' . $title . '</a>';
		$comment    = $actDes . ' ' . $linkHTML;
		$attachment = $link;
		$type       = 'text';
		$imgpath    = null;
		$params     = array();

		AwdwallHelperUser::addtostream($comment, $attachment, $type, $actorId, $imgpath, $params);

		return true;
	}

	/**
	 * UpdateJSlikedata.
	 *
	 * @param   Object  $contentdata  Content details.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function registerlike_Komento($contentdata)
	{
		$db = JFactory::getDBO();

		if ($contentdata->method == 'like')
		{
			$type                     = 'likes';
			$actionsTable             = new stdClass;
			$now                      = JFactory::getDate()->toMySQL();
			$actionsTable->id         = '';
			$actionsTable->type       = $type;
			$actionsTable->comment_id = $contentdata->element_id;
			$actionsTable->action_by  = $contentdata->userid;
			$actionsTable->actioned   = $now;

			if (!$db->insertObject('#__komento_actions', $actionsTable, 'id'))
			{
				return false;
			}
		}
		elseif ($contentdata->method == 'unlike')
		{
			$comment_id = $contentdata->element_id;
			$user_id    = $contentdata->userid;
			$type       = 'likes';
			$where      = array();
			$query      = 'DELETE FROM `#__komento_actions`';

			if ($type !== 'all')
			{
				$where[] = '`type` = ' . $db->quote($type);
			}

			if ($comment_id)
			{
				$where[] = '`comment_id` = ' . $db->quote($comment_id);
			}

			if ($user_id !== 'all')
			{
				$where[] = '`action_by` = ' . $db->quote($user_id);
			}

			if (count($where))
			{
				$query .= ' WHERE ' . implode(' AND ', $where);
			}

			$db->setQuery($query);

			return $db->query();
		}
	}
}
