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
 * @package		jlike
 * @author 		Techjoomla http://www.techjoomla.com
 * @copyright 	Copyright (C) 2011-2012 Techjoomla. All rights reserved.
 * @license 	GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

jimport('joomla.application.component.model');
jimport('joomla.filesystem.folder');

/**
 * JlikeModeljlike_likes
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class JLikeModelDashboard extends JModelLegacy
{

	function __construct() {

		// Get download id
		$params           = JComponentHelper::getParams('com_jlike');
		$this->downloadid = $params->get('downloadid');

		// Setup vars
		$this->updateStreamName = 'JLike';
		$this->updateStreamType = 'extension';
		$this->updateStreamUrl  = "https://techjoomla.com/component/ars/updates/components/jlike?format=xml&dummy=extension.xml";
		$this->extensionElement = 'com_jlike';
		$this->extensionType    = 'component';

		parent::__construct ();
	}

	/**
	 *  GetLineChartValues
	 *
	 * @return  null
	 */
	public function getLineChartValues()
	{
		$input = JFactory::getApplication()->input;
		$post  = $input->getArray($_POST);

		// ToDate Value
		if (isset($post['todate']))
		{
			$to_date = $post['todate'];
		}
		else
		{
			$to_date = date('Y-m-d', strtotime(date('Y-m-d') . ' + 1 days'));
		}

		// FromDate Value
		if (isset($post['fromdate']))
		{
			$from_date = $post['fromdate'];
		}
		else
		{
			$from_date = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
		}

		$diff     = strtotime($to_date) - strtotime($from_date);
		$days     = round($diff / 86400);
		$days_arr = array();

		// Get like & Dislike  against date
		$db  = JFactory::getDBO();
		$que = "SELECT jl.like,jl.dislike,jl.date FROM #__jlike_likes as jl  where jl.date >= '" . strtotime($from_date) . "' AND  jl.date <= '" . strtotime($to_date) . "'";
		$db->setQuery($que);
		$like_result = $db->loadObjectList();

		// Get Comment against date
		$query = "SELECT count(ant.annotation) as commentsCnt, DATE(ant.annotation_date) as comment_date
		FROM #__jlike_annotations as ant
		where ant.annotation_date >= '" . $from_date . "'
		AND  ant.annotation_date <= '" . $to_date . "'
		AND ant.annotation <> ''
		AND ant.state = 1
		AND ant.note = 0
		GROUP BY DATE(ant.annotation_date)";
		$db->setQuery($query);
		$commentsCount = $db->loadObjectList();

		//Array of object
		$potdata = array();

		// Prepare data for dashboard Area Chart
		for ($i = 0; $i <= $days; $i++)
		{
			$object = new stdclass;

			$ondate         = date('Y-m-d', strtotime($from_date . ' +  ' . $i . 'days'));
			$object->ondate = $ondate;

			$like_cnt    = 0;
			$dislike_cnt = 0;

			foreach ($like_result as $k => $v)
			{
				if ($ondate === date('Y-m-d', $v->date))
				{
					$like_cnt += $v->like;
					$dislike_cnt += $v->dislike;
				}
			}

			$object->like_cnt    = $like_cnt;
			$object->dislike_cnt = $dislike_cnt;

			// Get Comments count of each day if not then add zero
			$comment_cnt = 0;

			foreach ($commentsCount as $k => $v)
			{
				if ($ondate === $v->comment_date)
				{
					$comment_cnt += $v->commentsCnt;
				}
			}

			$object->comment_cnt = $comment_cnt;

			// Put object in array
			$potdata[] = $object;

		}

		return $potdata;
	}


	public function getcheckMigrate()
	{
		$db             = JFactory::getDBO();
		$jomsociallikes = $jomlikelikes = $jlikelikes = '';
		if (JFolder::exists(JPATH_ROOT . DS . 'components' . DS . 'com_community'))
		{
			$query = "SELECT *  FROM `#__community_likes`";
			$db->setQuery($query);
			$jomsociallikes = $db->loadObjectList();
		}
		if (JFolder::exists(JPATH_ROOT . DS . 'components' . DS . 'com_jomlike'))
		{
			$query = "SELECT *  FROM `#__jomlike_likes`";
			$db->setQuery($query);
			$jomlikelikes = $db->loadObjectList();
		}
		if ($jomsociallikes || $jomlikelikes)
		{
			$query = "SELECT *  FROM `#__jlike_likes`";
			$db->setQuery($query);
			$jlikelikes = $db->loadObjectList();
			if ($jlikelikes)
				return true;
			else
				return false;
		}
		return true;
	}
	public function getData()
	{
		// Load the data
		if (empty($this->_data))
		{
			$query       = $this->_buildQuery();
			$this->_data = $this->_getList($query);
		}

		if (!$this->_data)
		{
			$this->_data     = new stdClass();
			$this->_data->id = 0;
		}
		return $this->_data;
	}

	// Function to get data require for dashboard
	public function getDashboardData()
	{

		$db   = JFactory::getDBO();
		$data = new stdclass();

		//Get comments count
		$query = "SELECT count(ant.id) as comment_count
			FROM #__jlike_content as jc
			LEFT JOIN #__jlike_annotations as ant ON jc.id=ant.content_id
			LEFT JOIN #__users as u ON u.id =  ant.user_id
			WHERE  ant.annotation <> ''
			AND ant.state = 1
			AND ant.note = 0
			";

		$db->setQuery($query);
		$data->comment_count = $db->loadResult();

		// Get likes Count
		$db  = JFactory::getDBO();
		$que = "SELECT count(jl.like) as like_count
			FROM #__jlike_likes as jl
			WHERE jl.like=1 ";

		$db->setQuery($que);
		$data->like_count = $db->loadResult();

		// Get dislikes Count
		$db  = JFactory::getDBO();
		$que = "SELECT count(jl.dislike) as dislike_count
				FROM #__jlike_likes as jl
				WHERE jl.dislike = 1 ";

		$db->setQuery($que);
		$data->dislike_count = $db->loadResult();

		// Get users Count who liked/disliked comment
		$db  = JFactory::getDBO();
		$que = "SELECT jl.userid
				FROM #__jlike_likes as jl
				WHERE jl.userid<>0
				GROUP BY jl.userid
				";
		$db->setQuery($que);
		$users_who_liked = $db->loadColumn();

		// Get users Count who added comment or note
		$db  = JFactory::getDBO();
		$que = "SELECT ant.user_id
				FROM #__jlike_annotations as ant
				WHERE ant.state =1
				AND ant.user_id<>0
				GROUP BY ant.user_id
				";
		$db->setQuery($que);
		$commenter = $db->loadColumn();

		//Meger results of both the queries & get users count
		$data->users_count = count(array_unique(array_merge($users_who_liked, $commenter)));

		return $data;
	}

	public function getExtensionId()
	{
		$db = $this->getDbo();

		// Get current extension ID
		$query = $db->getQuery(true)
			->select($db->qn('extension_id'))
			->from($db->qn('#__extensions'))
			->where($db->qn('type') . ' = ' . $db->q($this->extensionType))
			->where($db->qn('element') . ' = ' . $db->q($this->extensionElement));
		$db->setQuery($query);

		$extension_id = $db->loadResult();

		if (empty($extension_id))
		{
			return 0;
		}
		else
		{
			return $extension_id;
		}
	}

	/**
	 * Refreshes the Joomla! update sites for this extension as needed
	 *
	 * @return  void
	 */
	public function refreshUpdateSite()
	{
		// Extra query for Joomla 3.0 onwards
		$extra_query = null;

		if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $this->downloadid))
		{
			$extra_query = 'dlid=' . $this->downloadid;
		}

		// Setup update site array for storing in database
		$update_site = array(
			'name' => $this->updateStreamName,
			'type' => $this->updateStreamType,
			'location' => $this->updateStreamUrl,
			'enabled'  => 1,
			'last_check_timestamp' => 0,
			'extra_query'          => $extra_query
		);

		// For joomla versions < 3.0
		if (version_compare(JVERSION, '3.0.0', 'lt'))
		{
			unset($update_site['extra_query']);
		}

		$db = $this->getDbo();

		// Get current extension ID
		$extension_id = $this->getExtensionId();

		if (!$extension_id)
		{
			return;
		}

		// Get the update sites for current extension
		$query = $db->getQuery(true)
			->select($db->qn('update_site_id'))
			->from($db->qn('#__update_sites_extensions'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
		$db->setQuery($query);

		$updateSiteIDs = $db->loadColumn(0);

		if (!count($updateSiteIDs))
		{
			// No update sites defined. Create a new one.
			$newSite = (object) $update_site;
			$db->insertObject('#__update_sites', $newSite);

			$id = $db->insertid();

			$updateSiteExtension = (object) array(
				'update_site_id' => $id,
				'extension_id'   => $extension_id,
			);

			$db->insertObject('#__update_sites_extensions', $updateSiteExtension);
		}
		else
		{
			// Loop through all update sites
			foreach ($updateSiteIDs as $id)
			{
				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__update_sites'))
					->where($db->qn('update_site_id') . ' = ' . $db->q($id));
				$db->setQuery($query);
				$aSite = $db->loadObject();

				if(!empty($aSite->name))
				{
					// Does the name and location match?
					if (($aSite->name == $update_site['name']) && ($aSite->location == $update_site['location']))
					{
						// Do we have the extra_query property (J 3.2+) and does it match?
						if (property_exists($aSite, 'extra_query'))
						{
							if ($aSite->extra_query == $update_site['extra_query'])
							{
								continue;
							}
						}
						else
						{
							// Joomla! 3.1 or earlier. Updates may or may not work.
							continue;
						}
					}
				}

				$update_site['update_site_id'] = $id;
				$newSite = (object) $update_site;
				$db->updateObject('#__update_sites', $newSite, 'update_site_id', true);
			}
		}
	}

	public function getLatestVersion()
	{
		// Get current extension ID
		$extension_id = $this->getExtensionId();

		if (!$extension_id)
		{
			return 0;
		}

		$db = $this->getDbo();

		// Get current extension ID
		$query = $db->getQuery(true)
			->select($db->qn(array('version', 'infourl')))
			->from($db->qn('#__updates'))
			->where($db->qn('extension_id') . ' = ' . $db->q($extension_id));
		$db->setQuery($query);

		$latestVersion = $db->loadObject();

		if (empty($latestVersion))
		{
			return 0;
		}
		else
		{
			return $latestVersion;
		}
	}
}
