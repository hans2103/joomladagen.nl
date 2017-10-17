<?php

/**
 * @package    Jlike
 * @author     TechJoomla | <extensions@techjoomla.com>
 * @copyright  Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license    GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link       http://www.techjoomla.com
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

$helperPath = JPATH_SITE . '/components/com_jlike/helpers/socialintegration.php';

if (!class_exists('socialintegrationHelper'))
{
	//  Require_once $path;
	JLoader::register('socialintegrationHelper', $helperPath);
	JLoader::load('socialintegrationHelper');
}

$helperPath = JPATH_SITE . '/components/com_jlike/helpers/integration.php';

if (!class_exists('comjlikeIntegrationHelper'))
{
	//  Require_once $path;
	JLoader::register('comjlikeIntegrationHelper', $helperPath);
	JLoader::load('comjlikeIntegrationHelper');
}

$helperPath = JPATH_SITE . '/components/com_jlike/helper.php';

if (!class_exists('comjlikeHelper'))
{
	//  Require_once $path;
	JLoader::register('comjlikeHelper', $helperPath);
	JLoader::load('comjlikeHelper');
}

$helperPath = JPATH_SITE . '/components/com_jlike/helpers/main.php';

if (!class_exists('ComjlikeMainHelper'))
{
	// Require_once $path;
	JLoader::register('ComjlikeMainHelper', $helperPath);
	JLoader::load('ComjlikeMainHelper');
}

if (!class_exists('comjlikeHelper'))
{
	/**
	 * Get button set used
	 *
	 * @since  2.2
	 */
	class ComjlikeHelper
				{
		/**
		 * Get button set used
		 *
		 * @since   2.2
		 *
		 * @return  result.
		 */
		public function getbttonset()
		{
			$db    = JFactory::getDBO();
			$query = "SELECT *  FROM `#__jlike` where published=1";
			$db->setQuery($query);
			$res = $db->loadObject();

			return $res;
		}

		/**
		 * Called from plugin to show like / Dilike buttons
		 *
		 * @since   2.2
		 *
		 * @return  result.
		 */
		public function showlike()
		{
			$mainframe     = JFactory::getApplication();
			$user          = JFactory::getUser();
			$componentPath = JPATH_SITE . '/components/com_jlike';
			require_once $componentPath . '/models/jlike_likes.php';
			require_once $componentPath . '/controller.php';
			$component = new jLikeController(array('name' => 'jlike'));
			$component->addViewPath($componentPath . '/views');
			$component->addModelPath($componentPath . '/models');
			$view  = $component->getView('jlike', 'raw');
			$model = $component->getModel('jlike_likes');
			$view->setModel($model);
			$view->setModel($model);
			$templatePath = JPATH_SITE . '/templates/' . $mainframe->getTemplate() . '/html/com_jlike/jlike';

			if (JFile::exists($templatePath . '/default.php'))
			{
				$view->addTemplatePath($templatePath);
			}
			else
			{
				$view->addTemplatePath($componentPath . '/views/jlike/tmpl');
			}

			ob_start();

			$view->display();

			$html = ob_get_contents();
			ob_end_clean();

			return $html;
		}

		/**
		 * Called from plugin to show like / Dilike buttons
		 *
		 * @since   2.2
		 *
		 * @return  result.
		 */
		public function showlikebuttons()
		{
			$mainframe     = JFactory::getApplication();
			$user          = JFactory::getUser();
			$componentPath = JPATH_SITE . '/components/com_jlike';
			require_once $componentPath . '/models/jlike_likes.php';
			require_once $componentPath . '/controller.php';
			$component = new jLikeController(array('name' => 'jlike'));
			$component->addViewPath($componentPath . '/views');
			$component->addModelPath($componentPath . '/models');
			$view  = $component->getView('jlike', 'raw');
			$model = $component->getModel('jlike_likes');
			$view->setModel($model);

			$templatePath = JPATH_SITE . '/templates/' . $mainframe->getTemplate() . '/html/com_jlike/jlike';

			if (JFile::exists($templatePath . '/likebuttons.php'))
			{
				$view->addTemplatePath($templatePath);
			}
			else
			{
				$view->addTemplatePath($componentPath . '/views/jlike/tmpl');
			}

			ob_start();

			$view->setLayout('likebuttons');

			$view->display();

			$html = ob_get_contents();
			ob_end_clean();

			return $html;
		}

		/**
		 * Save like or dislike details.
		 *
		 * @param   object  $data  post data.
		 *
		 * @since   2.2
		 * @return  list.
		 */
		public function registerLike($data)
		{
			$params = JComponentHelper::getParams('com_jlike');
			$db    = JFactory::getDBO();

			$query = $db->getQuery(true);
			$query->select(array('jc.id,jc.like_cnt,jc.dislike_cnt'));
			$query->from($db->quoteName('#__jlike_content', 'jc'));
			$query->where($db->quoteName("jc.element") . " = '" . $data['element'] . "'");
			$query->where($db->quoteName('jc.element_id') . ' = ' . $data['element_id']);

			$db->setQuery($query);
			$contentres = $db->loadObject();

			$element_id       = $like_uobjverb = '';
			$content_like_cnt = $content_dislike_cnt = 0;

			if (!$contentres)
			{
				$insert_obj             = new stdClass;
				$insert_obj->element_id = $data['element_id'];
				$insert_obj->element    = $data['element'];
				$insert_obj->url        = $data['url'];
				$insert_obj->title      = $data['title'];
				$db->insertObject('#__jlike_content', $insert_obj);
				$element_id = $db->insertid();
			}
			else
			{
				$element_id          = $contentres->id;
			}

			$query = $db->getQuery(true);
			$query->select(array('jl.id,jl.like,jl.dislike'));
			$query->from($db->quoteName('#__jlike_likes', 'jl'));
			$query->where($db->quoteName("jl.content_id") . ' = ' . $element_id);
			$query->where($db->quoteName('jl.userid') . ' = ' . JFactory::getUser()->id);

			$db->setQuery($query);
			$likeres = $db->loadObject();
			$like_id = '';

			if (!$likeres)
			{
				$insert_obj             = new stdClass;
				$insert_obj->content_id = $element_id;
				$insert_obj->userid     = JFactory::getUser()->id;
				$insert_obj->created = date("Y-m-d H:i:s");
				$db->insertObject('#__jlike_likes', $insert_obj);
				$like_id = $db->insertid();
			}
			else
			{
				$like_id = $likeres->id;
			}

			$content_uobj    = new stdClass;
			$like_uobj       = new stdClass;
			$like_uobj->date = time();

			switch ($data['method'])
			{
				case 'like':
					$like_uobj->like    = 1;
					$like_uobj->dislike = 0;
					$like_uobj->date    = time();
					$like_uobjverb      = JText::_('COM_JLIKE_LIKE_VERB');
					break;

				case 'dislike':
					$like_uobj->dislike = 1;
					$like_uobj->like    = 0;
					$like_uobj->date    = time();
					$like_uobjverb      = JText::_('COM_JLIKE_DISLIKE_VERB');
					break;

				case 'unlike':
					$like_uobj->like        = 0;
					$like_uobj->dislike     = 0;
					$like_uobjverb          = JText::_('COM_JLIKE_UNLIKE_VERB');
					break;

				case 'undislike':
					$like_uobj->like           = 0;
					$like_uobj->dislike        = 0;
					$like_uobjverb             = JText::_('COM_JLIKE_UNDISLIKE_VERB');
					break;
			}

			$like_uobj->id = $like_id;
			$like_uobj->modified = date("Y-m-d H:i:s");
			$db->updateObject('#__jlike_likes', $like_uobj, 'id');

			$count_res = $this->getLikeDislikeCount($element_id);
			$content_uobj->like_cnt = $count_res->likecnt;
			$content_uobj->dislike_cnt = $count_res->dislikecnt;
			$content_uobj->id = $element_id;

			if (!empty($data['url']))
			{
				$content_uobj->url = $data['url'];
			}

			/*Get total number of likes and dislikes*/

			$db->updateObject('#__jlike_content', $content_uobj, 'id');

			// Save the status releated things, in future rating etc
			$statusMgt = $params->get('statusMgt', 0);

			if ($statusMgt && ($data['method'] == "like" || $data['method'] == "unlike"))
			{
				$like_statusId = $data['like_statusId'];

				if ($data['method'] == "unlike")
				{
					// @TODO get completed status id dymanically
					$like_statusId = isset($data['statusParam']) ? $data['statusParam'] : 0;
				}

				$this->storeExtraData($element_id, $like_statusId);
			}

			$query = "SELECT  jc.id,jc.like_cnt,jc.dislike_cnt FROM #__jlike_content AS jc WHERE jc.id='" . $element_id . "'";
			$db->setQuery($query);
			$res             = $db->loadObject();
			$res->element_id = $data['element_id'];
			$res->like_id    = $like_id;
			$res->link       = $data['url'];
			$res->title      = $data['title'];
			$res->timestamp  = $like_uobj->date;
			$res->method     = $data['method'];
			$res->element    = $data['element'];
			$res->userid     = JFactory::getUser()->id;
			$res->username   = JFactory::getUser()->username;
			$res->verb       = $like_uobjverb;

			$extraParams  = $data['extraParams'];
			$plg_name = $plg_type = '';

			if (!empty($extraParams))
			{
				$plg_type = $extraParams['plg_type'];
				$plg_name = $extraParams['plg_name'];
			}

			// Activity Stream Integration with cb,JS,jomwall
			$comjlikeIntegrationHelper = new comjlikeIntegrationHelper;

			$jlikemainhelperObj        = new ComjlikeMainHelper;
			$params                    = $jlikemainhelperObj->getjLikeParams($plg_type, $plg_name);
			$allow_activity_stream     = $params->get('allow_activity_stream');

			if ($allow_activity_stream == 1)
			{
				$integration = $jlikemainhelperObj->getSocialIntegration($plg_type, $plg_name);
				$comjlikeIntegrationHelper->pushtoactivitystream($res, 'like', 0, $integration);
			}

			/* Activity Stream Integration with cb,JS,jomwall
			 * @pamans message, contnet id, element, url, title, plg_name, parent_id (for threaded comment)
			 * $comment, $cnt_id, $element, $url,      $title,  $plg_name, $parent_id, $plg_type, $for = '' */

			$notification_msg = $like_uobjverb . ' ' . $data['title'];
			$this->notification($like_uobjverb, $data['element_id'], $data['element'], $data['url'], $res->title, $plg_name, 0, $plg_type, $notification_msg);

			// $comment, $element_id, $element, $url, $title, $plg_name, $parent_id,$plg_type

			// Jomsociallike Integration
			if (strstr($res->element, 'com_community'))
			{
				$result = $comjlikeIntegrationHelper->registerlikeJS($res);
			}

			// Jomsociallike Integration

			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');
			$grt_response = $dispatcher->trigger('onAfterregisterlike', array($res));

			return $res;
		}

		/**
		 * Get like or dislike count for the given content
		 *
		 * @param   INT  $content_id  id of the content
		 *
		 * @return  object list
		 */
		public function getLikeDislikeCount($content_id)
		{
			$db             = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select(array('SUM(jl.like) as likecnt','SUM(jl.dislike) as dislikecnt'));
			$query->from($db->quoteName('#__jlike_likes', 'jl'));
			$query->where($db->quoteName("jl.content_id") . ' = ' . $content_id);
			$query->where($db->quoteName("jl.userid") . ' != ""');
			$query->group(array('jl.content_id'));

			$db->setQuery($query);
			$count = $db->loadObject();

			return $count;
		}

		/**
		 * getPeoplelikedthisContentBefor
		 *
		 * @param   INT    $content_id   id of the content
		 * @param   Array  $extraParams  extraparams
		 *
		 * @return  object list
		 */
		public function getPeoplelikedthisContentBefor($content_id, $extraParams)
		{
			$this->jlikemainhelperObj = new ComjlikeMainHelper;
			$jlike_settings = $this->jlikemainhelperObj->getjLikeParams();
			$integration = $this->jlikemainhelperObj->getSocialIntegration();
			$jlike_settings->get('which_users_to_show');

			$pwltcb         = array();
			$db             = JFactory::getDBO();
			$query          = "SELECT jl.userid as id ,u.email,u.name,u.username
							FROM #__jlike_likes AS jl, #__users AS u
							WHERE jl.like=1 AND jl.content_id='$content_id' AND jl.userid=u.id ";

			if ($jlike_settings->get('which_users_to_show') == 'friends')
			{
				if ($integration == 'js')
				{
					$js_installed = comjlikeHelper::Checkifinstalled('com_community');

					if ($js_installed)
					{
						include_once JPATH_ROOT . '/components/com_community/libraries/core.php';
						$friends = CFactory::getUser()->_friends;

						if ($friends)
						{
							$friends .= ',' . JFactory::getUser()->id;
							$query .= " AND jl.userid IN($friends)";
						}
					}
				}
				elseif($integration == 'easysocial')
				{
					$es_installed = comjlikeHelper::Checkifinstalled('com_easysocial');

					if ($es_installed)
					{
						$model = FD::model('friends');
						$options['idonly'] = 1;

						$EasySocialModelFriends = new EasySocialModelFriends;
						$esfriends_ids = $model->getFriends(JFactory::getUser()->id, $options);

						if ($esfriends_ids)
						{
							$friends .= ',' . JFactory::getUser()->id;
							$query .= " AND jl.userid IN($esfriends_ids)";
						}
					}
				}
				elseif ($integration == 'cb')
				{
					$cb_installed = comjlikeHelper::Checkifinstalled('com_comprofiler');

					if ($cb_installed)
					{
						// Get connected Users
						$db->setQuery("SELECT a.memberid " . "FROM #__comprofiler_members a " .
									"LEFT JOIN #__comprofiler b ON a.memberid = b.user_id " .
									"WHERE a.referenceid=" . JFactory::getUser()->id . " AND (a.accepted=1)");

						$connections_a = $db->loadColumn();

						if ($connections_a)
						{
							$connections = implode(',', $connections_a);
							$connections .= ',' . JFactory::getUser()->id;
							$query .= " AND jl.userid IN($connections)";
						}
					}
				}
			}

			$db->setQuery($query);
			$pwltcb = $db->loadObjectList();

			if ($pwltcb)
			{
				foreach ($pwltcb as $ind => $obj)
				{
					$userObject = JFactory::getUser($obj->id);
					$udetails               = comjlikeHelper::getUserDetails($userObject, $extraParams);
					$pwltcb[$ind]->img_url  = $udetails['img_url'];
					$pwltcb[$ind]->link_url = $udetails['link_url'];
				}
			}

			return $pwltcb;
		}

		/**
		 * Get user details
		 *
		 * @param   string  $obj      user object
		 * @param   Array   $plgData  Plugin info array
		 *
		 * @return  integer  $itemid
		 */
		public function getUserDetails($obj, $plgData)
		{
			$this->jlikemainhelperObj = new ComjlikeMainHelper;
			$mainHelper  = $this->jlikemainhelperObj;
			$socialintegrationHelper  = $mainHelper->getSocialLibraryObject('', $plgData);
			$user['img_url']          = $socialintegrationHelper->getAvatar($obj);
			$user['link_url']         = $socialintegrationHelper->getProfileUrl($obj);

			return $user;
		}

		/**
		 * Get ItemId function
		 *
		 * @param   string   $link          URL to find itemid for
		 *
		 * @param   integer  $skipIfNoMenu  return 0 if no menu is found
		 *
		 * @return  integer  $itemid
		 */
		public function getitemid($link, $skipIfNoMenu = 0)
		{
			$itemid    = 0;
			$mainframe = JFactory::getApplication();

			if ($mainframe->issite())
			{
				$JSite = new JSite;
				$menu  = $JSite->getMenu();
				$items = $menu->getItems('link', $link);

				if (isset($items[0]))
				{
					$itemid = $items[0]->id;
				}
			}

			if (!$itemid)
			{
				$db = JFactory::getDBO();

				if (JVERSION >= 3.0)
				{
					$query = $db->getQuery(true)->select('id')->from('#__menu')->where("'link LIKE %" . $link . "%'")->where("published =1")->limit("1");
				}
				else
				{
					$query = $db->getQuery(true);
					$query->select('id')
							->from('#__menu')->where("'link LIKE %" . $link . "%'")
							->where("published =1")->order("ordering")->limit("1");
				}

				if ($query)
				{
					$db->setQuery($query);
					$itemid = $db->loadResult();
				}
			}

			if (!$itemid)
			{
				if ($skipIfNoMenu)
				{
					$itemid = 0;
				}
				else
				{
					$jinput = JFactory::getApplication()->input;
					$itemid = JRequest::getInt('Itemid', 0);
				}
			}

			return $itemid;
		}

		/**
		 * addlabels
		 *
		 * @param   String  $data  (string) name of component
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function addlables($data)
		{
			$db                  = JFactory::getDBO();
			$insert_obj          = new stdClass;
			$insert_obj->user_id = JFactory::getUser()->id;
			$insert_obj->title   = strip_tags($data['lable']);
			$insert_obj->privacy = '1';
			$db->insertObject('#__jlike_like_lists', $insert_obj);
			$list_id = $db->insertid();

			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('system', 'jlike_api');
			$grt_response = $dispatcher->trigger('onAfteraddlable', array($list_id));

			return $list_id;
		}

		/**
		 * savedata
		 *
		 * @param   String  $data  (string) name of component
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function savedata($data)
		{
			$db	= JFactory::getDBO();

			if (empty($data['content_id']))
			{
				$data['content_id'] = $this->manageContent($data);
			}

			require_once JPATH_SITE . '/components/com_jlike/helpers/integration.php';

			$db = JFactory::getDBO();
			$query = "SELECT * FROM #__jlike_annotations
				WHERE content_id='" . $data['content_id'] . "'
				AND user_id='" . JFactory::getUser()->id . "'
				AND note = 1
				";
			$db->setQuery($query);
			$res = $db->loadObject();

			$privacy = 0;

			if (isset($data['privacy']))
			{
				$privacy = $data['privacy'];
			}

			if (isset($data['annotation']))
			{
				if (!$res)
				{
					$insert_obj             = new stdClass;
					$insert_obj->content_id = $data['content_id'];
					$insert_obj->user_id    = JFactory::getUser()->id;
					$insert_obj->annotation = strip_tags($data['annotation']);
					$insert_obj->privacy    = $privacy;
					$insert_obj->note       = 1;
					$db->insertObject('#__jlike_annotations', $insert_obj);
				}
				else
				{
					$query = "UPDATE #__jlike_annotations
				SET annotation='" . strip_tags($data['annotation']) . "' ,
				privacy='" . $privacy . "'
				WHERE content_id ='" . $data['content_id'] . "'
				AND user_id='" . JFactory::getUser()->id . "'
				AND note = 1
				";
					$db->setQuery($query);
					$db->execute();
				}

				// Activity Stream Integration
				$params                        = JComponentHelper::getParams('com_jlike');
				$allow_activity_stream_comment = $params->get('allow_activity_stream_comment');

				if ($allow_activity_stream_comment == 1)
				{
					$query = "SELECT * FROM #__jlike_content  WHERE id=" . $data['content_id'];
					$db->setQuery($query);
					$content = $db->loadObject();

					$comjlikeIntegrationHelper = new comjlikeIntegrationHelper;

					$activityObj             = new stdClass;
					$activityObj->comment              = $data['annotation'];
					$activityObj->userid               = JFactory::getUser()->id;
					$activityObj->element              = '';
					$activityObj->url                  = $content->url;
					$activityObj->title                = $content->title;
					$activityObj->access               = 0;
					$activityObj->note                 = 1;

					if ($privacy)
					{
						$activityObj->access = 40;
					}

					$comjlikeIntegrationHelper->pushtoactivitystream($activityObj, 'comment', 0);
				}
			}

			if (isset($data['label-check']))
			{
				foreach ($data['label-check'] as $list_id)
				{
					$query = "SELECT * FROM #__jlike_likes_lists_xref  WHERE content_id='" . $data['content_id'] . "' AND list_id='" . $list_id . "'";
					$db->setQuery($query);
					$res = $db->loadObject();

					if (!$res)
					{
						$insert_obj             = new stdClass;
						$insert_obj->content_id = $data['content_id'];
						$insert_obj->list_id    = $list_id;
						$db->insertObject('#__jlike_likes_lists_xref', $insert_obj);
					}
				}
			}

			return 1;
		}

		/**
		 * manageContent
		 *
		 * @param   String  $data  data provided to manage content
		 *
		 * @return  content_id
		 *
		 * @since 1.0
		 */
		public function manageContent($data)
		{
			$db	= JFactory::getDBO();

			$content_id = '';

			if (!empty($data['element_id']) && !empty($data['element']))
			{
				$content_id = $this->getContentId($data['element_id'], $data['element']);
			}

			if (!$content_id)
			{
				$insert_obj             = new stdClass;
				$insert_obj->element_id = $data['element_id'];
				$insert_obj->element    = $data['element'];
				$insert_obj->url        = $data['url'];
				$insert_obj->title      = $data['title'];
				$db->insertObject('#__jlike_content', $insert_obj);
				$content_id = $db->insertid();
			}

			return $content_id;
		}

		/**
		 * multi_d_sort
		 *
		 * @param   String  $array   (string) name of component
		 * @param   String  $column  (string) name of component
		 * @param   String  $order   (string) name of component
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function multi_d_sort($array, $column, $order)
		{
			foreach ($array as $key => $row)
			{
				// $orderby[$key]=$row['campaign']->$column;
				$orderby[$key] = $row->$column;
			}

			if ($order == 'asc')
			{
				array_multisort($orderby, SORT_ASC, $array);
			}
			else
			{
				array_multisort($orderby, SORT_DESC, $array);
			}

			return $array;
		}

		/**
		 * Check if GetMostLikes
		 *
		 * @param   String  $limit  (string) name of component
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function GetRecentLikes($limit)
		{
			$db = JFactory::getDBO();

			if (!$limit)
			{
				$limit = 5;
			}

			$query = "SELECT likecontent.*,likes.date as likedate
		FROM #__jlike_content AS likecontent
		LEFT JOIN #__jlike_likes AS likes ON likecontent.id = likes.content_id
		GROUP BY likecontent.id ORDER BY likes.date DESC  LIMIT 0 ," . $limit;

			$db->setQuery($query);

			return $db->loadObjectList();
		}

		/**
		 * Check if GetMostLikes
		 *
		 * @param   String  $limit  (string) name of component
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function GetMostLikes($limit)
		{
			$db = JFactory::getDBO();

			if (!$limit)
			{
				$limit = 5;
			}

			$query = "SELECT likecontent.* FROM #__jlike_content AS likecontent ORDER BY likecontent.like_cnt DESC  LIMIT 0 ," . $limit;
			$db->setQuery($query);

			return $db->loadObjectList();
		}

		/**
		 * Check if installed
		 *
		 * @param   String  $component  (string) name of component
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function Checkifinstalled($component)
		{
			$componentpath = JPATH_ROOT . '/components/' . $component;

			if (JFolder::exists($componentpath))
			{
				return 1;
			}
		}

		/**
		 * Add comment
		 *
		 * @param   String  $commentData  (string) name of view
		 * $cnt_id     (string) name of view
		 * $element    (string) name of view
		 * $url        (string) name of view
		 * $title      (string) name of view
		 * $plg_name   (string) name of view
		 * $parent_id  (string) name of view
		 * $note_type  Type of note
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 *
		 *public function addComment($comment, $cnt_id, $element, $url, $title, $plg_name, $parent_id, $note_type)*/

		public function addComment($commentData)
		{
			$element 	= $commentData['element'];
			$element_id = $commentData['element_id'];
			$url 		= $commentData['url'];
			$title 		= $commentData['title'];

			$db    = JFactory::getDBO();
			$query = "SELECT jc.id,jc.like_cnt,jc.dislike_cnt FROM #__jlike_content AS jc
					WHERE jc.element='" . $element . "' AND jc.element_id='" . $element_id . "'";

			$db->setQuery($query);

			$contentres       = $db->loadObject();
			$like_uobjverb = '';
			$content_like_cnt = $content_dislike_cnt = 0;

			if (!$contentres)
			{
				$insert_obj             = new stdClass;
				$insert_obj->element_id = $element_id;
				$insert_obj->element    = $element;
				$insert_obj->url        = $url;
				$insert_obj->title      = $title;

				$db->insertObject('#__jlike_content', $insert_obj);
				$content_id = $db->insertid();
			}
			else
			{
				$content_id = $contentres->id;
			}

			if ($content_id)
			{
				$comment        = $commentData['comment'];
				$parent_id 		= $commentData['parent_id'];
				$note_type 		= $commentData['note_type'];

				$CommentToSave             = new StdClass;
				$CommentToSave->user_id    = JFactory::getUser()->id;
				$CommentToSave->content_id = $content_id;

				/* replace anchor tag of user with profile tag*/
				$comment = $this->getProfiletag($comment);

				$CommentToSave->annotation = $comment;
				$CommentToSave->privacy    = 0;
				$CommentToSave->state      = 1;
				$CommentToSave->parent_id  = $parent_id;

				/* This is comment means not a note
				 * 0 : Comment
				 * 1 : Note
				 * 2 : Reviews
				 */
				$CommentToSave->note = $note_type;
				$CommentToSave->annotation_date = JFactory::getDate()->toSQL();

				if (!$db->insertObject('#__jlike_annotations', $CommentToSave))
				{
					echo $db->stderr();
				}
				else
				{
					$annotation_id   = $db->insertid();

					// Check notification is on & integration is jomsocial
					$extraParams  = $commentData['extraParams'];
					$plg_name = $plg_type = '';

					if (!empty($extraParams))
					{
						$plg_type = $extraParams['plg_type'];
						$plg_name = $extraParams['plg_name'];
					}

					// Activity Stream Integration with cb,JS,jomwall
					$comjlikeIntegrationHelper = new comjlikeIntegrationHelper;

					$jlikemainhelperObj        = new ComjlikeMainHelper;
					$params                    = $jlikemainhelperObj->getjLikeParams($plg_type, $plg_name);

					$js_notification = $params->get('js_notification');

					if ($js_notification)
					{
						$notification_msg = JText::_("COM_JLIKE_ADDED_COMMENT") . $title;
						$this->notification(
									$comment, $element_id, $element, $url,
									$title, $plg_name, $parent_id,
									$plg_type, $notification_msg
									);
					}

					// Activity stream after saving comments
					$this->activityStream($comment, $element_id, $element, $url, $title, $plg_name, $plg_type);
					/*$this->activityStream($comment, $cnt_id, $element, $url, $title);*/

					return $annotation_id;
				}
			}
		}

		/**
		 * Notification to content owner after comment added
		 *
		 * @param   String  $comment   (string) name of view
		 * @param   String  $cnt_id    (string) name of view
		 * @param   String  $element   (string) name of view
		 * @param   String  $url       (string) name of view
		 * @param   String  $title     (string) name of view
		 * @param   String  $plg_name  (string) name of view
		 * @param   String  $plg_type  (string) name of view
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function activityStream($comment, $cnt_id, $element, $url, $title, $plg_name, $plg_type)
		{
			// Activity Stream Integration
			$jlikemainhelperObj        = new ComjlikeMainHelper;
			$params                    = $jlikemainhelperObj->getjLikeParams($plg_type, $plg_name);

			/*$params                        = JComponentHelper::getParams('com_jlike');*/
			$allow_activity_stream_comment = $params->get('allow_activity_stream_comment');

			if ($allow_activity_stream_comment == 1)
			{
				$comjlikeIntegrationHelper = new comjlikeIntegrationHelper;
				$res                       = new StdClass;
				$res->comment              = $comment;
				$res->userid               = JFactory::getUser()->id;
				$res->element              = '';
				$res->url                  = $url;
				$res->title                = $title;
				$res->access               = 0;

				$integration = $jlikemainhelperObj->getSocialIntegration($plg_type, $plg_name);
				$comjlikeIntegrationHelper->pushtoactivitystream($res, 'comment', 1, $integration);
			}
		}

		/**
		 * Notification to content owner after comment added
		 *
		 * @param   String  $comment               (string) name of view
		 * @param   String  $cnt_id                (string) name of view
		 * @param   String  $element               (string) name of view
		 * @param   String  $url                   (string) name of view
		 * @param   String  $title                 (string) name of view
		 * @param   String  $plg_name              (string) name of view
		 * @param   String  $parent_id             (string) name of view
		 * @param   String  $plg_type              (string) name of view
		 * @param   String  $notification_content  (string) name of view
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function notification($comment, $cnt_id, $element, $url, $title, $plg_name, $parent_id, $plg_type, $notification_content)
		{
			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin($plg_type, $plg_name);
			$userid = $dispatcher->trigger('get' . $plg_name . 'OwnerDetails', array($cnt_id));

			$owner_id = 0;

			if (!empty($userid))
			{
				$owner_id    = $userid[0];
			}

			$comjlikeHelper = new comjlikeHelper;

			// Check notification is on & integration is jomsocial
			$socialintegrationHelper = new socialintegrationHelper;
			$jlikemainhelperObj        = new ComjlikeMainHelper;
			$integration = $jlikemainhelperObj->getSocialIntegration($plg_type, $plg_name);

			$commented_by_userid      = JFactory::getUser()->id;
			$socialintegrationHelper  = new socialintegrationHelper;
			$commented_by_name = JFactory::getUser()->name;

			$commented_by_profile_url = JRoute::_($socialintegrationHelper->getUserProfileUrl($commented_by_userid, $integration));

			// Do not send notification if content owner itself took action on his own content
			if (!empty($owner_id) && $owner_id != $commented_by_userid)
			{
				if ($integration == 'js')
				{
					$installed            = $comjlikeHelper->Checkifinstalled('com_community');

					$notification_subject = '<a href="' . $commented_by_profile_url . '" >' . $commented_by_name . '</a>' . '&nbsp';
					$notification_subject .= '<a href="' . $url . '" >' . $notification_content . '</a>';

					// If jomsocial is installed
					if ($installed)
					{
						$socialintegrationHelper->send_js_notification(
									$commented_by_userid,
									JFactory::getUser($commented_by_userid)->name,
									$owner_id,
									$notification_subject
									);
					}
				}
				elseif ($integration == 'easysocial')
				{
					// Is easysocial present on site
					if (file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/foundry.php'))
					{
						$socialintegrationHelper->send_es_notification($commented_by_userid, $commented_by_name, $owner_id, $notification_content, $url);
					}
				}
			}

			// Notification to parent on replies on comments
			$jlikemainhelperObj        = new ComjlikeMainHelper;
			$params                    = $jlikemainhelperObj->getjLikeParams($plg_type, $plg_name);
			$js_notification_replies = $params->get('js_notification_replies');
			$integration = $jlikemainhelperObj->getSocialIntegration($plg_type, $plg_name);

			if ($js_notification_replies)
			{
				if (!empty($parent_id))
				{
					// Identify function call for reply notification
					$reply = 1;
					$this->notificationOnReplyOrLike($parent_id, $comment, $reply, $integration);
				}
			}
		}

		/**
		 * method to send the notification to the user when
		 * 1> reply on comment  2> like or dislike on comment
		 * $callFrom =1 => reply on comment
		 * $callFrom =2 like comment
		 * $callFrom =3 dislike on comment
		 *
		 * @param   String  $parent_id    (string) name of view
		 * @param   String  $comment      (string) name of view
		 * @param   String  $callFrom     (string) name of view
		 * @param   String  $integration  social extension to which integration is set
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function notificationOnReplyOrLike($parent_id, $comment, $callFrom, $integration)
		{
			$commented_by_userid = JFactory::getUser()->id;

			$commented_by_name        = JFactory::getUser()->name;
			$commented_by_email       = JFactory::getUser()->email;
			$socialintegrationHelper  = new socialintegrationHelper;
			$commented_by_profile_url = JRoute::_($socialintegrationHelper->getUserProfileUrl($commented_by_userid));

			$db    = JFactory::getDBO();
			$query = "SELECT user_id FROM #__jlike_annotations WHERE id=" . $parent_id;
			$db->setQuery($query);
			$ParentUser_id = $db->loadResult();

			if (!empty($ParentUser_id))
			{
				$owner_id    = $ParentUser_id;
				$owner_email = JFactory::getUser($owner_id)->email;

				/*to add notification in JS */
				$email            = $comment;
				$email_subject    = '';
				$notification_msg = '';

				if ($callFrom == 1)
				{
					$notification_msg = JText::_('COM_JLIKE_REPLY_ON_COMMNET');
				}
				elseif ($callFrom == 2)
				{
					$notification_msg = JText::_('COM_JLIKE_ON_LIKE');
				}
				elseif ($callFrom == 3)
				{
					$notification_msg = JText::_('COM_JLIKE_ON_DISLIKE');
				}

				$email_subject       = $commented_by_name . $notification_msg;
				$comment_with_smiley = $this->replaceSmileyAsImage($comment);

				$notification_subject = '<a href="' . $commented_by_profile_url . '" >' . $commented_by_name . '</a>' . $notification_msg;
				$notification         = $comment_with_smiley;

				/*Notification to user/
				$model = CFactory::getModel('Notification');
				$model->add($commented_by_userid , $owner_id,$notification_subject.$notification,'notif_system_messaging','0','');

				to send Email to user/
				$notify    = new CNotificationLibrary();// loads notification libary and sends email.
				$notify->add( 'system_messaging' ,$commented_by_userid, $owner_email ,$email_subject,$email);*/

				if ($integration == 'js')
				{
					$comjlikeHelper = new comjlikeHelper;
					$installed      = $comjlikeHelper->Checkifinstalled('com_community');

					// If jomsocial is installed
					if ($installed)
					{
						$socialintegrationHelper->send_js_notification($commented_by_userid, $commented_by_name, $owner_id, $notification_subject);
					}
				}
				elseif ($integration == 'easysocial')
				{
					// If easysocial present on site
					if (file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/foundry.php'))
					{
						// Send_es_notification($notification_to,$username,$notification_sender,$notification_msg)
						$socialintegrationHelper->send_es_notification($commented_by_userid, $commented_by_name, $owner_id, $notification_msg);
					}
				}
			}
		}

		/**
		 * Method identify that a current user like or dislike the comment
		 *
		 * @param   String  $result  (string) name of view
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function replaceSmileyAsImage($result)
		{
			$result = str_replace(':)', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/smile.jpg" />', $result);
			$result = str_replace(':-)', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/smile.jpg" />', $result);
			$result = str_replace(':(', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/sad.jpg" />', $result);
			$result = str_replace(':-(', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/sad.jpg" />', $result);
			$result = str_replace(';)', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/wink.jpg" />', $result);
			$result = str_replace(';-)', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/wink.jpg" />', $result);
			$result = str_replace(';(', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/cry.jpg" />', $result);
			$result = str_replace('B-)', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/cool.jpg" />', $result);
			$result = str_replace('B)', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/cool.jpg" />', $result);
			$result = str_replace(':D', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/grin.jpg" />', $result);
			$result = str_replace(':-D', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/grin.jpg" />', $result);
			$result = str_replace(':o', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/shocked.jpg" />', $result);
			$result = str_replace(':O', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/shocked.jpg" />', $result);
			$result = str_replace(':-o', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/shocked.jpg" />', $result);
			$result = str_replace(':-O', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/shocked.jpg" />', $result);
			$result = str_replace(':-3', '<img src="' . JUri::root() . 'components/com_jlike/assets/images/smileys/love.png" />', $result);

			return $result;
		}

		/**
		 * Method identify that a current user like or dislike the comment
		 *
		 * @param   String  $annotationid  (string) name of view
		 * @param   String  $userId        (string) name of view
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function getUserCurrentLikeDislike($annotationid, $userId)
		{
			$db    = JFactory::getDBO();

			// Check that the current user like or dislike this comment
			$query = "SELECT `like`,`dislike`
		FROM  #__jlike_likes
		WHERE `annotation_id`=" . $annotationid . "
		 AND  `userid`=" . $userId;
			$db->setQuery($query);
			$data = $db->loadObject();

			if (!empty($data))
			{
				if ($data->like)
				{
					// User like on comment
					$userLikeDislike = 1;
				}
				elseif ($data->dislike)
				{
					// User dislike on comment
					$userLikeDislike = 2;
				}
			}
			else
			{
				// User not like dislike
				$userLikeDislike = 0;
			}

			return $userLikeDislike;
		}

		/**
		 * method to add the user id in likes table when he/she dislike the comment
		 *
		 * @param   String  $annotationid  (string) name of view
		 * @param   String  $comment       (string) name of view
		 * @param   Array   $extraParams   Array of plug name and plug type
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function increaseLikeCount($annotationid, $comment, $extraParams=array())
		{
			$db              = JFactory::getDBO();
			$userId          = JFactory::getUser()->id;
			$comjlikeHelper  = new comjlikeHelper;
			$userLikeDislike = $comjlikeHelper->getUserCurrentLikeDislike($annotationid, $userId);

			$response = '';

			// $like=2; // identify that notification for like
			$like     = '';

			// Like or Unlike
			if ($userLikeDislike == 0)
			{
				// Like
				$insert_obj                = new stdClass;
				$insert_obj->content_id    = 0;
				$insert_obj->annotation_id = $annotationid;
				$insert_obj->userid        = JFactory::getUser()->id;
				$insert_obj->like          = 1;
				$insert_obj->dislike       = 0;
				$insert_obj->date          = time();

				if ($db->insertObject('#__jlike_likes', $insert_obj, 'id'))
				{
					$response = 1;
					$like     = 2;
				}
				else
				{
					return $db->stderr();
				}
			}
			elseif ($userLikeDislike == 1)
			{
				// Unlike (user already like the comment but now want to unlike it)
				$query = "DELETE FROM #__jlike_likes
			WHERE annotation_id=" . $annotationid . "
			 AND userid=" . $userId;
				$db->setQuery($query);

				if (!$db->execute($query))
				{
					return $db->stderr();
				}
				else
				{
					$like     = 0;
					$response = 1;
				}
			}
			elseif ($userLikeDislike == 2)
			{
				// Dislike to like
				$query = "UPDATE #__jlike_likes
			SET `like`=1,`dislike`=0
			WHERE annotation_id=" . $annotationid . "
			 AND userid=" . $userId;
				$db->setQuery($query);

				if (!$db->execute($query))
				{
					return $db->stderr();
				}
				else
				{
					$like     = 2;
					$response = 2;
				}
			}

			$plg_name = $plg_type = '';

			if (!empty($extraParams))
			{
				$plg_type = $extraParams['plg_type'];
				$plg_name = $extraParams['plg_name'];
			}

			$jlikemainhelperObj        = new ComjlikeMainHelper;
			$params                    = $jlikemainhelperObj->getjLikeParams($plg_type, $plg_name);

			if ($like == 2 && $params->get('js_notification_on_like'))
			{
				$integration = $jlikemainhelperObj->getSocialIntegration($plg_type, $plg_name);
				$this->notificationOnReplyOrLike($annotationid, $comment, $like, $integration);
			}

			return $response;
		}

		/**
		 * method to add the user id in likes table when he/she dislike the comment
		 *
		 * @param   String  $annotationid  (string) name of view
		 * @param   String  $comment       (string) name of view
		 * @param   Array   $extraParams   Array of plug name and plug type
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function increaseDislikeCount($annotationid, $comment, $extraParams=array())
		{
			$db              = JFactory::getDBO();
			$userId          = JFactory::getUser()->id;
			$comjlikeHelper  = new comjlikeHelper;
			$userLikeDislike = $comjlikeHelper->getUserCurrentLikeDislike($annotationid, $userId);
			$response        = '';

			// $dislike=3; // identify that notification for dislike
			$dislike         = '';

			// Like or Unlike
			if ($userLikeDislike == 0)
			{
				// Dislike (user record not present in the table)
				$insert_obj                = new stdClass;
				$insert_obj->content_id    = 0;
				$insert_obj->annotation_id = $annotationid;
				$insert_obj->userid        = JFactory::getUser()->id;
				$insert_obj->like          = 0;
				$insert_obj->dislike       = 1;
				$insert_obj->date          = time();

				if ($db->insertObject('#__jlike_likes', $insert_obj, 'id'))
				{
					$response = 1;
					$dislike  = 3;
				}
				else
				{
					return $db->stderr();
				}
			}
			elseif ($userLikeDislike == 1)
			{
				// Like to dislike (user record present in the table update the record)

				$query = "UPDATE #__jlike_likes
			SET `like`=0,`dislike`=1
			WHERE annotation_id=" . $annotationid . "
			 AND userid=" . $userId;
				$db->setQuery($query);

				if (!$db->execute($query))
				{
					return $db->stderr();
				}
				else
				{
					$response = 2;
					$dislike  = 3;
				}
			}
			elseif ($userLikeDislike == 2)
			{
				// Unlike (user already like the comment but now want to unlike it)

				$query = "DELETE FROM #__jlike_likes
			WHERE annotation_id=" . $annotationid . "
			 AND userid=" . $userId;
				$db->setQuery($query);

				if (!$db->execute($query))
				{
					return $db->stderr();
				}
				else
				{
					$response = 1;
					$dislike  = 0;
				}
			}

			$plg_name = $plg_type = '';

			if (!empty($extraParams))
			{
				$plg_type = $extraParams['plg_type'];
				$plg_name = $extraParams['plg_name'];
			}

			$jlikemainhelperObj        = new ComjlikeMainHelper;
			$params                    = $jlikemainhelperObj->getjLikeParams($plg_type, $plg_name);

			if ($dislike == 3 AND $params->get('js_notification_on_dislike'))
			{
				$integration = $jlikemainhelperObj->getSocialIntegration($plg_type, $plg_name);
				$this->notificationOnReplyOrLike($annotationid, $comment, $dislike, $integration);
			}

			return $response;
		}

		/**
		 * method to get the user name, profile url & avtar who like or dislike the comment
		 *
		 * @param   String  $annotationid     (string) name of view
		 * @param   String  $likedOrdisliked  (string) name of view
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function getUserByCommentId($annotationid, $likedOrdisliked)
		{
			if ($likedOrdisliked)
			{
				$liked = "likes.`like`=1";
			}
			else
			{
				$liked = "likes.`dislike`=1";
			}

			$db    = JFactory::getDBO();
			$query = "SELECT u.name,u.id,u.email FROM #__jlike_likes as likes
		INNER JOIN `#__users` as u ON likes.userid=u.id
		WHERE likes.annotation_id=" . $annotationid . " AND " . $liked;
			$db->setQuery($query);
			$result = $db->LoadObjectList();

			$socialintegrationHelper = new socialintegrationHelper;

			foreach ($result as $row)
			{
				$user                  = new stdClass;
				$user->id              = $row->id;
				$user->email           = $row->email;
				$row->user_profile_url = $socialintegrationHelper->getUserProfileUrl($row->id);
				$row->avtar            = $socialintegrationHelper->getUserAvatar($user);
			}

			return $result;
		}

		/**
		 * checks for view override
		 *
		 * @param   String  $message_body  (string) name of view
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function getProfiletag($message_body)
		{
			if (strpos($message_body, '<a') !== false)
			{
				preg_match_all("/<a\s(.+?)>(.+?)<\/a>/is", $message_body, $matches);
				$all_a_tags = $matches[0];

				$hrefpattern = "/(?<=profiletag=(\"|'))[^\"']+(?=(\"|'))/";
				preg_match_all($hrefpattern, $message_body, $matches);
				$profile_tags = $matches[0];

				foreach ($all_a_tags as $all_a_tag)
				{
					foreach ($profile_tags as $profile_tag)
					{
						if (strpos($all_a_tag, $profile_tag))
						{
							$message_body = str_replace($all_a_tag, $profile_tag, $message_body);
						}
					}
				}
			}

			return $message_body;
		}

		/**
		 * checks for view override
		 *
		 * @param   String  $s       (string) name of view
		 * @param   string  $l       layout name eg order
		 * @param   string  $e       it may be admin or site. it is side(admin/site) where to search override view
		 * @param   string  $isHTML  it may be admin or site. it is side(admin/site) which VIEW shuld be use IF OVERRIDE IS NOT FOUND
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function getsubstrwithHTML($s, $l, $e = '...', $isHTML = false)
		{
			$i    = 0;
			$tags = array();

			if ($isHTML)
			{
				preg_match_all('/<[^>]+>([^<]*)/', $s, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

				foreach ($m as $o)
				{
					if ($o[0][1] - $i >= $l)
					{
						break;
					}

					$t = substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 1);

					if ($t[0] != '/')
					{
						$tags[] = $t;
					}
					elseif (end($tags) == substr($t, 1))
					{
						array_pop($tags);
					}

					$i += $o[1][1] - $o[0][1];
				}
			}

			return substr($s, 0, $l = min(strlen($s), $l + $i))
			. (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '') . (strlen($s) > $l ? $e : '');
		}

		/**
		 * checks for view override
		 *
		 * @param   String  $viewname       (string) name of view
		 * @param   string  $layout         layout name eg order
		 * @param   string  $searchTmpPath  it may be admin or site. it is side(admin/site) where to search override view
		 * @param   string  $useViewpath    it may be admin or site. it is side(admin/site) which VIEW shuld be use IF OVERRIDE IS NOT FOUND
		 *
		 * @return  if exit override view then return path
		 *
		 * @since 1.0
		 */
		public function getjLikeViewpath($viewname, $layout = "", $searchTmpPath = 'SITE', $useViewpath = 'SITE')
		{
			$searchTmpPath = ($searchTmpPath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
			$useViewpath   = ($useViewpath == 'SITE') ? JPATH_SITE : JPATH_ADMINISTRATOR;
			$app           = JFactory::getApplication();

			if (!empty($layout))
			{
				$layoutname = $layout . '.php';
			}
			else
			{
				$layoutname = "default.php";
			}

			$override = $searchTmpPath . '/templates/' . $app->getTemplate() . '/html/com_jlike/' . $viewname . '/' . $layoutname;

			if (JFile::exists($override))
			{
				return $view = $override;
			}
			else
			{
				return $view = $useViewpath . '/components/com_jlike/views/' . $viewname . '/tmpl/' . $layoutname;
			}
		}

		/**
		 * Get Content Classification
		 *
		 * @param   object  $objectList  list array
		 *
		 * @return  object list with replace element value
		 *
		 * @since 1.0
		 */
		public function classificationsValue($objectList)
		{
			if ($objectList)
			{
				$brodfile        = JPATH_SITE . "/components/com_jlike/classification.ini";
				$classifications = parse_ini_file($brodfile);

				foreach ($objectList as $row)
				{
					$element = trim($row->element);

					if ($element)
					{
						if (array_key_exists($element, $classifications))
						{
							$row->element = $classifications[$element];
						}
						else
						{
							$row->element = $element;
						}
					}
				}
			}

			return $objectList;
		}

		/**
		 * Function to Get Formated data for Chart (morris)
		 *
		 * @param   Integer  $data  Like content id.
		 *
		 * @return void
		 *
		 * @since 3.0
		 */
		public function getLineChartFormattedData($data)
		{
			/* $session = JFactory::getSession();
			// $backdate=$session->get('q2c_from_date');
			// $backdate = !empty($backdate)?$backdate:(date('Y-m-d', strtotime(date('Y-m-d').' - 30 days')));*/

			$todate = $session->get('q2c_end_date');
			$todate = !empty($todate) ? $todate : date('Y-m-d');

			$incomedata = "[
		";
			$ordersData = "[
		";
			$firstdate  = $backdate;

			//  Will be
			$keydate    = "";

			foreach ($data as $key => $income)
			{
				$keydate = date('Y-m-d', strtotime($key));

				if ($firstdate < $keydate)
				{
					while ($firstdate < $keydate)
					{
						$incomedata .= " { period:'" . $firstdate . "', amount:0 },
					";
						$ordersData .= " { period:'" . $firstdate . "', orders:0 },
					";
						$firstdate = $this->add_date($firstdate, 1);
					}
				}

				$incomedata .= " { period:'" . $income->cdate . "', amount:" . $income->amount . "},
			";
				$ordersData .= " { period:'" . $income->cdate . "', orders:" . $income->orders_count . "},
			";
				$firstdate = $keydate;
			}

			// Vm: remaing date to last date
			while ($keydate < $todate)
			{
				$keydate = $this->add_date($keydate, 1);
				$incomedata .= " { period:'" . $keydate . "', amount:0 },
			";
				$ordersData .= " { period:'" . $keydate . "', orders:0 },
			";
			}

			$incomedata .= '
		]';
			$ordersData .= '
		]';

			$returnArray    = array();
			$returnArray[0] = $incomedata;
			$returnArray[1] = $ordersData;

			return $returnArray;
		}

		/**
		 * Getting users lable list for particular article
		 *
		 * @param   Integer  $content_id  Like content id.
		 * @param   Integer  $user_id     user_id.
		 * @param   Integer  $lableArray  if set to 1 then array will be retured.
		 *
		 * @return void
		 *
		 * @since 3.0
		 */
		public function getMyContentLables($content_id, $user_id, $lableArray = 0)
		{
			$db        = JFactory::getDBO();
			$lableHtml = '';

			if ($lableArray)
			{
				$lableHtml = array();
			}

			if ($content_id && $user_id)
			{
				try
				{
					//  Delete the xref table entry first 	$query->join('LEFT', '`#__categories` AS c ON c.id=ki.category');
					$query = $db->getQuery(true);
					$query->select('list.id,list.title,lref.content_id')
							->from('#__jlike_likes_lists_xref AS lref')
							->join('INNER', '#__jlike_like_lists AS list ON list.id = lref.list_id');
					$query->where('lref.content_id=' . $content_id);
					$query->where('list.user_id=' . $user_id);

					$db->setQuery($query);
					$lists = $db->loadObjectList();

					if ($lableArray)
					{
						return $lists;
					}

					foreach ($lists as $lable)
					{
						$lableHtml = $lableHtml . $lable->title . ', ';
					}

					//  Remove last occarance ,
					return rtrim($lableHtml, ", ");
				}
				catch (Exception $e)
				{
					//  $e->getMessage();
					return $lableHtml;
				}
			}
		}

		/**
		 * Getting users lable list.
		 *
		 * @param   Integer  $user_id  user_id.
		 *
		 * @return void
		 *
		 * @since 3.0
		 */
		public function getLableList($user_id = '')
		{
			$db = JFactory::getDBO();

			if ($user_id)
			{
				try
				{
					$query = $db->getQuery(true)->select('list.id,list.title')->from('#__jlike_like_lists AS list');

					if ($user_id)
					{
						$query->where('list.user_id=' . $user_id);
					}

					$db->setQuery($query);

					return $lists = $db->loadObjectList();
				}
				catch (Exception $e)
				{
					//  $e->getMessage();
					return array();
				}
			}
		}

		/**
		 * Getting users lable list.
		 *
		 * @param   Integer  $user_id     user_id.
		 * @param   Integer  $content_id  user_id.
		 * @param   ARRAY    $lableLists  new lable list array eg (0=>15,1=18).
		 *
		 * @return void
		 *
		 * @since 3.0
		 */
		public function mapLikeWithLable($user_id, $content_id, $lableLists)
		{
			$db = JFactory::getDBO();

			try
			{
				$previousMapping = $this->getMyContentLables($content_id, $user_id, 1);

				if (!empty($previousMapping))
				{
					foreach ($previousMapping as $ind => $listID)
					{
						//  Chcek old lable exist in current lable list array
						$key = array_search($listID->id, $lableLists);

						if ($key)
						{
							//  Then remove from new list array.
							unset($lableLists[$key]);
						}
						else
						{
							//  For removed lable
							$query = $db->getQuery(true)->delete('#__jlike_likes_lists_xref')->where('content_id =' . $content_id)->where('list_id =' . $listID->id);
							$db->setQuery($query);

							if (!$db->execute())
							{
								$this->setError($this->_db->getErrorMsg());
							}
						}
					}
				}

				//  Only add the newly checked lables
				if (!empty($lableLists))
				{
					foreach ($lableLists as $listId)
					{
						$obj             = new stdClass;
						$obj->content_id = $content_id;
						$obj->list_id    = $listId;
						$db->insertObject('#__jlike_likes_lists_xref', $obj);
					}
				}
			}
			catch (Exception $e)
			{
				//  $e->getMessage();

				return false;
			}

			return true;
		}

		/**
		 * Using this function
		 *
		 * @param   Integer  $importEmailId  user_id.
		 *
		 * @return void
		 *
		 * @since 3.0
		 */
		public function getJlikeDetailFromInvitexRefTb($importEmailId)
		{
			$db = JFactory::getDbo();

			try
			{
				$query = $db->getQuery(true);
				$query->select('content.*')
						->from('#__jlike_content_inviteX_xref AS invRef')
						->join('INNER', '#__jlike_content AS content ON invRef.content_id = content.id');
				$query->where('invRef.importEmailId=' . $importEmailId);

				$db->setQuery($query);
				$lists = $db->loadObjectList();

				if (!empty($lists))
				{
					return $lists;
				}
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());
				throw new Exception($this->_db->getErrorMsg());

				return 0;
			}
		}

		/**
		 * Using this function, entry(content_id and import_email_id)) in xref table will be added
		 *
		 * @param   integer  $importEmail_id  Import if of invitex's import table.
		 *
		 * @return  void
		 *
		 * @since   1.3
		 */
		public function addEntryJlikeInvitexRefTb($importEmail_id)
		{
			$db            = JFactory::getDbo();
			$userId        = JFactory::getUser()->id;
			$jinput        = JFactory::getApplication()->input;
			$post          = $jinput->post;
			$res['status'] = 0;
			$res['msg']    = JText::_('JLIKE_MISSING_CONTENT_IDS');

			/*if (empty($cids))
			{
			$cid = $post->get('cid', array(), 'ARRAY');
			$cids = implode(',', $cid);
			}*/

			//   Get contant ids from session
			$session = JFactory::getSession();
			$cids    = $session->get('jlikeContentIds');

			if (empty($cids))
			{
				return $res;
			}

			$cidArray = explode(',', $cids);

			try
			{
				foreach ($cidArray as $content_id)
				{
					$obj                = new stdClass;
					$obj->content_id    = $content_id;
					$obj->importEmailId = $importEmail_id;

					if (!$db->insertObject('#__jlike_content_inviteX_xref', $obj, 'id'))
					{
						$this->setError($this->_db->getErrorMsg());
						$res['msg'] = $this->_db->getErrorMsg();
					}
				}

				$res['status'] = 1;

				return $res;
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());
				throw new Exception($this->_db->getErrorMsg());
				$res['msg'] = $this->_db->getErrorMsg();

				return $res;
			}
		}

		/**
		 * Using this function, entry(content_id and import_email_id)) in xref table will be added
		 *
		 * @param   integer  $importEmail_id  Import if of invitex's import table.
		 *
		 * @return  void
		 *
		 * @since   1.3
		 */
		public function DelEntryJlikeInvitexRefTb($importEmail_id)
		{
			$db = JFactory::getDBO();

			if (empty($importEmail_id))
			{
				return 0;
			}

			try
			{
				$query = $db->getQuery(true)->delete('#__jlike_content_inviteX_xref')->where('importEmailId =' . $importEmail_id);

				$db->setQuery($query);
				$db->execute();

				return 1;
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());
				throw new Exception($this->_db->getErrorMsg());

				return -1;
			}
		}

		/**
		 * Using this function, entry(content_id and import_email_id)) in xref table will be added
		 *
		 * @param   integer  $userId  User ID
		 * @param   integer  $limit   Import if of invitex's import table.
		 *
		 * @return  void
		 *
		 * @since   1.3
		 */
		public function getUserLikeDetail($userId, $limit = '')
		{
			if (empty($userId))
			{
				return array();
			}

			try
			{
				//  Build query as you want
				$db   = JFactory::getDBO();
				$user = JFactory::getUser();
				$query = $db->getQuery(true)
					->select('likecontent.*,likes.date as likedate')
					->from('#__jlike_content AS likecontent')
					->join('INNER', '#__jlike_likes AS likes ON likecontent.id = likes.content_id')
					->group('likecontent.id')
					->where('likes.userid=' . $userId)
					->where('likes.like = 1');

				if (!empty($limit))
				{
					$query->order("likes.date DESC LIMIT 0 ," . $limit);
				}

				$db->setQuery($query);

				return $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());
				throw new Exception($this->_db->getErrorMsg());

				return array();
			}
		}

		/**
		 * Get All statuses
		 *
		 * @since   2.2
		 * @return  list.
		 */
		public function getAllStatus()
		{
			$users = JFactory::getUser();

			/*if (empty($likeCont_id))
			{
				return array();
			}*/

			//  Check get status id for content
			try
			{
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select('s.id,s.status_code');
				$query->from('#__jlike_statuses AS s');
				$db->setQuery($query);

				return $db->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());
				throw new Exception($this->_db->getErrorMsg());

				return;
			}
		}

		/**
		 * This return weight unite symbol.
		 *
		 * @param   integer  $likeCont_id  Jlike Conten id.
		 *
		 * @since   2.2
		 * @return  list.
		 */
		public function getUsersContStatus($likeCont_id)
		{
			$users = JFactory::getUser();
			$db = JFactory::getDBO();

			if (empty($likeCont_id) || empty($users->id))
			{
				return 0;
			}

			//  Check get status id for content
			try
			{
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select('s.status_id');
				$query->from('#__jlike_likeStatusXref AS s');
				$query->where('content_id = ' . $likeCont_id);
				$query->where('user_id = ' . $users->id);
				$db->setQuery($query);

				return $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());
				throw new Exception($db->getErrorMsg());
			}
		}

		/**
		 * Get Content id
		 *
		 * @param   integer  $element_id  import_id.
		 * @param   string   $element     Element .
		 *
		 * @since   2.2
		 * @return  list.
		 */
		public function getContentId($element_id, $element)
		{
			if ($element_id &&  $element)
			{
				try
				{
					JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_jlike/tables');
					$table = JTable::getInstance('Content', 'JlikeTable');
					$table->load(array('element' => $element, 'element_id' => (int) $element_id));

					return $table->id;
				}
				catch (RuntimeException $e)
				{
					$this->setError($e->getMessage());
					throw new Exception($this->_db->getErrorMsg());

					return;
				}
			}
		}

		/**
		 * Get Content id
		 *
		 * @param   string  $path       path of file to load.
		 * @param   string  $classname  class name to load .
		 *
		 * @since   2.2
		 * @return  list.
		 */
		public function loadJlClass($path, $classname)
		{
			if (!class_exists($classname))
			{
				JLoader::register($classname, $path);
				JLoader::load($classname);
			}

			return new $classname;
		}

		/**
		 * On like : Store extra data. Currently status related things are stored.
		 *
		 * @param   integer  $content_id     import_id.
		 * @param   object   $like_statusId  postdata .
		 *
		 * @since   2.2
		 * @return  list.
		 */
		public function storeExtraData($content_id, $like_statusId)
		{
			$db = JFactory::getDBO();
			$user = JFactory::getUser();
			$userId = $user->id;

			if (!isset($like_statusId))
			{
				return 0;
			}

			try
			{
				// Check whethere rec already exist or not
				$query = $db->getQuery(true)
							->select('sref.id')
							->from('#__jlike_likeStatusXref AS sref');
				$query->where('sref.content_id=' . $content_id);
				$query->where('sref.user_id=' . $userId);
				$db->setQuery($query);
				$refId = $db->loadResult();
				$action = 'insertObject';

				$row = new stdClass;
				$date = JFactory::getDate();

				if (!empty($refId))
				{
					$action = 'updateObject';
					$row->id = $refId;
					$row->cdate = date("Y-m-d H:i:s");
					$row->mdate = date("Y-m-d H:i:s");
				}
				else
				{
					$row->mdate = date("Y-m-d H:i:s");
				}

				$row->content_id = $content_id;
				$row->user_id = $userId;
				$row->status_id = $like_statusId;

				if (!$db->$action('#__jlike_likeStatusXref', $row, 'id'))
				{
					$this->setError($db->getErrorMsg());

					return 0;
				}

				return 1;
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());
				throw new Exception($db->getErrorMsg());

				return 0;
			}
		}

		/**
		 * Check whether user liked to content or not.
		 *
		 * @param   integer  $content_id  import_id.
		 * @param   integer  $user_id     user id .
		 *
		 * @since   2.2
		 * @return  list.
		 */
		public function isUserLikedContent($content_id, $user_id)
		{
			if ($content_id &&  $user_id)
			{
				try
				{
					$db = JFactory::getDBO();
					$query = $db->getQuery(true);
					$query->select('c.id');
					$query->from('#__jlike_likes AS c');
					$query->where('c.content_id=' . $content_id);
					$query->order('c.userid=' . $user_id);
					$query->where('c.like=1');
					$db->setQuery($query);

					return $db->loadResult();
				}
				catch (RuntimeException $e)
				{
					$this->setError($e->getMessage());
					throw new Exception($this->_db->getErrorMsg());

					return;
				}
			}
		}

		/**
		 * Function to send recommendation
		 *
		 * @param   ARRAY  $data      data
		 * @param   ARRAY  $formdata  formdata
		 *
		 * @return  boolean
		 *
		 * @since  1.0.0
		 */
		public function send_recommendation($data, $formdata)
		{
			require_once JPATH_SITE . '/components/com_jlike/helpers/integration.php';

			$db	= JFactory::getDBO();
			$query = "SELECT jc.id FROM #__jlike_content AS jc
					WHERE jc.element='" . $formdata['element'] . "' AND jc.element_id='" . $formdata['element_id'] . "'";
			$db->setQuery($query);
			$content_id = $db->loadResult();

			if (!$content_id)
			{
					$insert_obj = new stdClass;
					$insert_obj->element_id = $formdata['element_id'];
					$insert_obj->element 	= $formdata['element'];
					$insert_obj->url = $formdata['url'];
					$insert_obj->title	=	$formdata['title'];
					$db->insertObject('#__jlike_content', $insert_obj);
					$content_id = $db->insertid();
			}

			$insert_obj = new stdClass;
			$insert_obj->content_id = $content_id;
			$insert_obj->recommend_by = JFactory::getUser()->id;
			$insert_obj->params = '';

			foreach ($data as $eachrecommendation)
			{
				$insert_obj->id = '';
				$insert_obj->recommend_to 	= $eachrecommendation;

				try
				{
					// If it fails, it will throw a RuntimeException
					$db->insertObject('#__jlike_recommend', $insert_obj, 'id');
					$dispatcher = JDispatcher::getInstance();
					JPluginHelper::importPlugin('system');
					$grt_response = $dispatcher->trigger('onAfterRecommend', array(
															$eachrecommendation,
															JFactory::getUser()->id,
															$formdata['element_id'])
															);
				}
				catch (RuntimeException $e)
				{
					JFactory::getApplication()->enqueueMessage($e->getMessage());

					return false;
				}
			}

			return true;
		}

		/**
		 * Declare language constants to use in .js file
		 *
		 * @params  void
		 *
		 * @return  void
		 *
		 * @since   1.7
		 */
		public static function getLanguageConstant()
		{
			JText::script('COM_JLIKE_SELECT_USER_TO_RECOMMEND');
			JText::script('COM_JLIKE_SELECT_GROUP_TO_ASSIGN');
			JText::script('COM_JLIKE_SELECT_FILL_DATES');
			JText::script('COM_JLIKE_START_GT_THAN_DUE_DATE');
			JText::script('COM_JLIKE_INVALID_DATE_FORMAT');
			JText::script('COM_JLIKE_START_GT_THAN_TODAY');
			JText::script('COM_JLIKE_FORM_REMINDER_DAYS_ZERO');
			JText::script('COM_JLIKE_FORM_REMINDER_NOTVALID_CC');
			JText::script('COM_JLIKE_FORM_REMINDER_CONTENTTYPE_EMPTY');
		}

		/**
		 * Sendmail
		 *
		 * @param   string  $recipient    Email
		 * @param   string  $subject      Email sub
		 * @param   string  $body         Email body
		 * @param   array   $extraParams  Email bcc
		 *
		 * @return void
		 */
		public static function sendmail($recipient, $subject, $body, $extraParams = array())
		{
			jimport('joomla.utilities.utility');

			try
			{
				$config     = JFactory::getConfig();
				$from       = $config->get('mailfrom');
				$fromname   = $config->get('fromname');
				$recipient  = trim($recipient);
				$cc         = array();
				$bcc        = array();
				$attachment = null;
				$mode       = 1;

				// Extra parameters to the email
				if (!empty($extraParams))
				{
					foreach ($extraParams as $param => $value)
					{
						if ($param == 'cc')
						{
							// Take care of $cc email addresses
							$cc = $value;
						}

						if ($param == 'bcc')
						{
							// Take care of $bcc email addresses
							$bcc = $value;
						}

						if ($param == 'attachment')
						{
							// Take care of attachments sent
							$attachment = $value;
						}
					}
				}

				return JFactory::getMailer()->sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment);
			}
			catch (Exception $e)
			{
				$msg = $e->getMessage();
				JFactory::getApplication()->enqueueMessage($msg, 'error');
			}

			return true;
		}

		/**
		 * getAvarageRating.
		 *
		 * @since   2.2
		 * @return  list.
		 */
		public function getAvarageRating()
		{
			$mainframe     = JFactory::getApplication();
			$user          = JFactory::getUser();
			$componentPath = JPATH_SITE . DS . 'components' . DS . 'com_jlike';
			require_once $componentPath . DS . 'models' . DS . 'jlike_likes.php';
			require_once $componentPath . DS . 'controller.php';

			$component = new jLikeController(array('name' => 'jlike'));
			$model = $component->getModel('jlike_likes');
			$setdata       = JRequest::get('request');
			$this->urldata = json_decode($setdata['data']);

			require_once JPATH_SITE . '/' . 'components/com_jlike/helper.php';
			$commentFilex = $this->getjLikeViewpath('jlike', 'ratingAvg');

			// Get Rating avarage
			$getRatingAvg = $model->getProductRatingAvg($this->urldata->cont_id, $this->urldata->element);

			ob_start();
				include $commentFilex;
				$htmlx = ob_get_contents();
			ob_end_clean();

			return $htmlx;
		}

		/**
		 * addRating.
		 *
		 * @param   integer  $cnt_id       content id.
		 * @param   integer  $user_rating  user ratings.
		 * @param   integer  $rating_upto  allow rating 1 to 5.
		 * @param   string   $plg_name     plg_name
		 * @param   string   $element      element.
		 * @param   string   $url          url.
		 * @param   string   $title        title.
		 *
		 * @since   2.2
		 * @return  list.
		 */
		public function addRating($cnt_id, $user_rating, $rating_upto, $plg_name, $element, $url, $title)
		{
			$db         = JFactory::getDBO();
			$element_id = $this->addContent($cnt_id, $element, $url, $title);
			$userRating = $this->checkUserRating($element_id);

			if ($element_id)
			{
				$insert_obj                = new stdClass;
				$insert_obj->content_id    = $element_id;
				$insert_obj->user_rating   = $user_rating;
				$insert_obj->rating_upto   = $rating_upto;
				$insert_obj->user_id       = JFactory::getUser()->id;
				$insert_obj->created_date  = JHtml::date($input = 'now', 'Y-m-d H:i:s', false);
				$insert_obj->modified_date = '';

				$db->insertObject('#__jlike_rating', $insert_obj);

				return $rating_id = $db->insertid();
			}
		}

		/**
		 * CheckUserRating.
		 *
		 * @param   int  $element_id  content element id
		 *
		 * @since   2.2
		 * @return  list.
		 */
		public function checkUserRating($element_id)
		{
			$db    = JFactory::getDBO();
			$query = "DELETE FROM #__jlike_rating
					WHERE user_id=" . JFactory::getUser()->id . "
					AND content_id= " . $element_id;
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

		/**
		 * Checkifinstalled.
		 *
		 * @param   integer  $cnt_id   content id.
		 * @param   string   $element  element.
		 * @param   string   $url      url.
		 * @param   string   $title    title.
		 *
		 * @since   2.2
		 * @return  list.
		 */
		public function addContent($cnt_id, $element, $url, $title)
		{
			$db    = JFactory::getDBO();
			$query = "SELECT jc.id,jc.like_cnt,jc.dislike_cnt FROM #__jlike_content AS jc WHERE jc.element='" .
			$element . "' AND jc.element_id='" . $cnt_id . "'";
			$db->setQuery($query);
			$content_id       = $db->loadResult();

			if (!$content_id)
			{
				$insert_obj             = new stdClass;
				$insert_obj->element_id = $cnt_id;
				$insert_obj->element    = $element;
				$insert_obj->url        = $url;
				$insert_obj->title      = $title;
				$db->insertObject('#__jlike_content', $insert_obj);
				$content_id = $db->insertid();
			}

			return $content_id;
		}

		/**
		 * Update user assignment status
		 *
		 * @param   int     $element_id   Element id
		 * @param   int     $element      Eelement  e.g  com_content.article
		 * @param   int     $assigned_to  The id for the user against to update assignment status
		 * @param   string  $status       Status I- Incomplete , C- Completed, S- Started
		 *
		 * @return  boolean true/false
		 *
		 * @since  1.2
		 *
		 */
		public static function updateToDoStatus($element_id, $element, $assigned_to, $status)
		{
			if ($element_id && $element && $assigned_to && $status)
			{
				// Get the content id
				$db = JFactory::getDBO();

				$query = $db->getQuery(true);
				$query->select($db->quoteName('id'));
				$query->from($db->quoteName('#__jlike_content'));
				$query->where($db->quoteName('element_id') . ' = ' . $element_id);
				$query->where($db->quoteName('element') . ' = "' . $element . '"');

				$db->setQuery($query);

				$content_id = $db->loadResult($query);

				// Update the assignment status
				if ($content_id)
				{
					$query = $db->getQuery(true);

					$fields = array(
						$db->quoteName('status') . ' = "' . $status . '"'
					);

					$cond = array(
						$db->quoteName('content_id') . ' = ' . $content_id,
						$db->quoteName('assigned_to') . ' = ' . $assigned_to
					);

					$query->update($db->quoteName('#__jlike_todos'))->set($fields)->where($cond);
					$db->setQuery($query);

					return $result = $db->execute();
				}
			}
			else
			{
				return false;
			}
		}

		/**
		 * Get start and due date of assignment
		 *
		 * @param   int  $content_id  Content id
		 * @param   int  $user_id     user id
		 *
		 * @return  Object
		 *
		 * @since  1.2
		 *
		 */
		public function getTodos($content_id, $user_id)
		{
			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('td.id', 'td.start_date', 'td.due_date', 'u.name', 'td.assigned_to')));
			$query->from($db->quoteName('#__jlike_todos', 'td'));
			$query->join('INNER', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('td.assigned_by') . ')');
			$query->where($db->quoteName('assigned_to') . " = " . $db->quote($user_id));
			$query->where($db->quoteName('content_id') . " = " . $db->quote($content_id));

			$db->setQuery($query);
			$result = $db->loadObject();

			return $result;
		}
	}
}
