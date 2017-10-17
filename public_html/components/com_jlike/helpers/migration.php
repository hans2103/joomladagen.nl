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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * MigrateHelper helper
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class ComjlikeMigrateHelper
{
	/**
	 * MigrateLikes.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function migrateLikes()
	{
		if (JFolder::exists(JPATH_ROOT . '/components/com_community') || JFolder::exists(JPATH_ROOT . '/components/com_jomlike'))
		{
			if (JFolder::exists(JPATH_ROOT . '/' . 'components' . '/' . 'com_community'))
			{
				$resjs = $this->migrateJomsocialLikes();

				return $resjs;
			}

			if (JFolder::exists(JPATH_ROOT . '/' . 'components' . '/' . 'com_jomlike'))
			{
				$resjl = $this->migrateJomlikeLikes();

				return $resjl;
			}
		}
	}

	/**
	 * MigrateJomsocialLikes.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function migrateJomsocialLikes()
	{
		$db    = JFactory::getDBO();
		$query = "SELECT *  FROM `#__community_likes`";
		$db->setQuery($query);
		$res = $db->loadObjectList();

		$jlikecontent_id = $columnname = $tablename = $jlikeurl = $jliketitle = '';
		$config          = $users_like = $users_dislike = array();

		if ($res)
		{
			foreach ($res as $ind => $object)
			{
				$objectelement = trim($object->element);
				$status = 'groups' || $objectelement == 'events';

				if ($objectelement == 'profile' || $objectelement == 'videos' || $objectelement == 'photo' || $objectelement == 'album' || $status)
				{
					$jlikecontent_id = $object->uid;

					switch ($objectelement)
					{
						case 'profile':
							$jlikeelement = "com_community.profile";
							$columnname   = 'username';
							$tablename    = '#__users';
							$jlikeurl     = "index.php?option=com_community&userid={$object->uid}&view=profile";
							break;
						case 'videos':
							$jlikeelement = "com_community.videos";
							$columnname   = 'title';
							$tablename    = '#__community_videos';
							$db->setQuery("SELECT creator FROM #__community_videos WHERE id={$jlikecontent_id}");
							$row      = $db->loadResult();
							$jlikeurl = "index.php?option=com_community&view=videos&task=video&videoid={$jlikecontent_id}&userid={$row}";
							break;
						case 'photo':
							$jlikeelement = "com_community.photo";
							$columnname   = 'caption';
							$tablename    = '#__community_photos';
							$db->setQuery("SELECT creator,albumid FROM #__community_photos WHERE id={$jlikecontent_id}");
							$row = $db->loadObject();

							$jlikeurl = "index.php?option=com_community&view=photos&task=photo&albumid={$row->albumid}&userid={$row->creator}";
							break;
						case 'album':
							$jlikeelement = "com_community.album";
							$columnname   = 'name';
							$tablename    = '#__community_photos_albums';

							$db->setQuery("SELECT albumid FROM #__community_photos WHERE id={$jlikecontent_id}");
							$row      = $db->loadResult();
							$jlikeurl = "index.php?option=com_community&view=photos&task=album&albumid={$jlikecontent_id}&userid={$row}";

							break;
						case 'groups':
							$jlikeelement = "com_community.groups";
							$columnname   = 'name';
							$tablename    = '#__community_groups';
							$jlikeurl     = "index.php?option=com_community&view=groups&task=viewgroup&groupid={$jlikecontent_id}";
							break;
						case 'events':
							$jlikeelement = "com_community.events";
							$columnname   = 'title';
							$tablename    = '#__community_events';
							$jlikeurl     = "index.php?option=com_community&view=events&task=viewevent&eventid={$jlikecontent_id}";
							break;
					}

					$sql = "SELECT `{$columnname}` FROM `{$tablename}` WHERE id={$jlikecontent_id}";
					$db->setQuery($sql);
					$jliketitle = ($db->loadResult()) ? $db->loadResult() : 'this' . $columnName;

					$users_like    = explode(',', $object->like);
					$users_dislike = explode(',', $object->dislike);

					$this->populatejliketables($jlikecontent_id, $jlikeelement, $jlikeurl, $jliketitle, $users_like, $users_dislike);
				}
			}
		}

		return true;
	}

	/**
	 * MigrateJomlikeLikes.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function migrateJomlikeLikes()
	{
		$db    = JFactory::getDBO();
		$query = "SELECT *  FROM `#__jomlike_likes`";
		$db->setQuery($query);
		$res = $db->loadObjectList();

		$jlikecontent_id = $columnname = $tablename = $jlikeurl = $jliketitle = '';
		$config          = $users_like = $users_dislike = array();

		if ($res)
		{
			foreach ($res as $ind => $object)
			{
				$objectelement = trim($object->element);

				if ($objectelement == 'article' || $objectelement == 'jomres' || $objectelement == 'k2item')
				{
					$jlikecontent_id = $object->id;
				}

				switch ($objectelement)
				{
					case 'article':
						require_once JPATH_SITE . '/components/com_content/helpers/route.php';
						$jlikeelement = "com_content.article";
						$columnname   = 'title';
						$tablename    = '#__content';

						$db->setQuery("SELECT catid FROM #__content WHERE id={$jlikecontent_id}");
						$catid = $db->loadResult();

						$jlikeurl = ContentHelperRoute::getArticleRoute($jlikecontent_id, $catid);
						break;
					case 'jomres':
						/*todo:*/
						$jlikeelement = "com_jomres.videos";
						$columnname   = 'title';
						$tablename    = '#__community_videos';
						$db->setQuery("SELECT creator FROM #__community_videos WHERE id={$jlikecontent_id}");
						$row      = $db->loadResult();
						$jlikeurl = "index.php?option=com_community&view=videos&task=video&videoid={$cont_id}&userid={$row}";
						break;
					case 'k2item':
						$jlikeelement = "com_k2.item";
						$columnname   = 'title';
						$tablename    = '#__k2_items';
						$db->setQuery("SELECT 	alias FROM #__k2_items WHERE id={$jlikecontent_id}");
						$titlealias = $db->loadResult();

						$jlikeurl = "index.php?option=com_k2&view=item&id={$jlikecontent_id}:{$titlealias}";
						break;
				}

				$sql = "SELECT `{$columnname}` FROM `{$tablename}` WHERE id={$jlikecontent_id}";
				$db->setQuery($sql);
				$jliketitle = ($db->loadResult()) ? $db->loadResult() : 'this' . $columnName;

				$users_like    = explode(',', $object->like);
				$users_dislike = explode(',', $object->dislike);

				$this->populatejliketables($jlikecontent_id, $jlikeelement, $jlikeurl, $jliketitle, $users_like, $users_dislike);
			}
		}

		return true;
	}

	/**
	 * Populatejliketables.
	 *
	 * @param   integer  $jlikecontent_id  Content id.
	 * @param   string   $jlikeelement     Elemnet.
	 * @param   integer  $jlikeurl         Url.
	 * @param   integer  $jliketitle       Titile.
	 * @param   string   $users_like       Users_like.
	 * @param   string   $users_dislike    Users_dislike.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function populatejliketables($jlikecontent_id, $jlikeelement, $jlikeurl, $jliketitle, $users_like, $users_dislike)
	{
		$users_like              = array_filter($users_like);
		$users_dislike           = array_filter($users_dislike);
		$db                      = JFactory::getDBO();
		$insert_obj              = new stdClass;
		$insert_obj->element_id  = $jlikecontent_id;
		$insert_obj->element     = $jlikeelement;
		$insert_obj->url         = $jlikeurl;
		$insert_obj->title       = $jliketitle;
		$insert_obj->like_cnt    = count($users_like);
		$insert_obj->dislike_cnt = count($users_dislike);
		$query = "SELECT id FROM `#__jlike_content` where element_id={$jlikecontent_id} and element='{$jlikeelement}'";
		$db->setQuery($query);
		$res = $db->loadResult();

		if (!$res)
		{
			$db->insertObject('#__jlike_content', $insert_obj);
			$content_id = $db->insertid();
		}
		else
		{
			$uobj              = new stdClass;
			$uobj->id          = $res;
			$uobj->like_cnt    = count($users_like);
			$uobj->dislike_cnt = count($users_dislike);
			$db->updateObject('#__jlike_content', $uobj, 'id');
			$content_id = $res;
		}

		if ($users_like)
		{
			foreach ($users_like as $userid)
			{
				if ($userid)
				{
					$query = "SELECT id FROM `#__jlike_likes` as jl where jl.userid={$userid} and jl.content_id='{$content_id}' and jl.like=1";
					$db->setQuery($query);
					$res = $db->loadResult();

					if (!$res)
					{
						$insert_obj             = new stdClass;
						$insert_obj->content_id = $content_id;
						$insert_obj->userid     = $userid;
						$insert_obj->like       = '1';
						$insert_obj->date       = time();
						$db->insertObject('#__jlike_likes', $insert_obj);
					}
				}
			}
		}

		if ($users_dislike)
		{
			foreach ($users_dislike as $userid)
			{
				if ($userid)
				{
					$query = "SELECT id FROM `#__jlike_likes` as jl where jl.userid={$userid} and jl.content_id='{$content_id}' and jl.dislike=1";
					$db->setQuery($query);
					$res = $db->loadResult();

					if (!$res)
					{
						$insert_obj             = new stdClass;
						$insert_obj->content_id = $content_id;
						$insert_obj->userid     = $userid;
						$insert_obj->dislike    = '1';
						$insert_obj->date       = time();
						$db->insertObject('#__jlike_likes', $insert_obj);
					}
				}
			}
		}

		return true;
	}
}
