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
jimport('techjoomla.jsocial.jsocial');
jimport('techjoomla.jsocial.joomla');
jimport('techjoomla.jsocial.jomsocial');
jimport('techjoomla.jsocial.easysocial');

/**
 * Main helper
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class ComjlikeMainHelper
{
	/**
	 * Method acts as a consturctor
	 *
	 * @since   1.0.0
	 */
	public function __construct()
	{
	}

	/**
	 * Get social library object depending on the integration set.
	 *
	 * @param   STRING  $integration_option  Soical integration set
	 * @param   Array   $options             Plugin info array
	 *
	 * @return  Soical library object
	 *
	 * @since 1.0.0
	 */
	public static function getSocialLibraryObject($integration_option, $options)
	{
		if (!$integration_option)
		{
			$integration_option = self::getSocialIntegration($options['plg_type'], $options['plg_name']);
		}

		// Send notification
		switch (strtolower($integration_option))
		{
			case 'joomla':
				$SocialLibraryObject = new JSocialJoomla;
			break;

			case 'easysocial':
				$SocialLibraryObject = new JSocialEasySocial;
			break;

			case 'jomsocial':
			case 'js':
				$SocialLibraryObject = new JSocialJomSocial;
			break;

			case 'cb':
				$SocialLibraryObject = new JSocialCB;
			break;

			case 'jomwall':
				$SocialLibraryObject = new JSocialJomwall;
			break;

			case 'easyprofile':
			break;
		}

		return $SocialLibraryObject;
	}

	/**
	 * Function to get the elements
	 *
	 * @param   STRING  $plg_type  Type of which elements to be fetched
	 * @param   STRING  $plg_name  plg name
	 *
	 * @return  File path
	 *
	 * @since  1.0.0
	 */
	public function getjLikeParams($plg_type='', $plg_name='')
	{
		$params = array();

		if (!empty($plg_type) && !empty($plg_name))
		{
			// Get Params each component
			$dispatcher        = JDispatcher::getInstance();
			JPluginHelper::importPlugin($plg_type, $plg_name);
			$paramsArray = $dispatcher->trigger($plg_name . 'GetParams', array());
			$params = !empty ($paramsArray[0]) ? $paramsArray[0] : '';
		}

		if (empty($params))
		{
			$params = JComponentHelper::getParams('com_jlike');
		}

		return $params;
	}

	/**
	 * Get Social integration
	 *
	 * @param   STRING  $plg_type  Plugin type e.g content, system
	 * @param   STRING  $plg_name  Plugin name e.g jlike_article
	 *
	 * @return  void
	 *
	 * @since   1.7
	 */
	public static function getSocialIntegration($plg_type='', $plg_name='')
	{
		$integration = array();

		if (!empty($plg_type) && !empty($plg_name))
		{
			$dispatcher = JDispatcher::getInstance();

			JPluginHelper::importPlugin($plg_type, $plg_name);

			$integration = $dispatcher->trigger($plg_name . 'GetSocialIntegration', array());
		}

		if (!empty($integration))
		{
			return $integration[0];
		}
		else
		{
			return $integration = strtolower(JComponentHelper::getParams('com_jlike')->get('integration'));
		}
	}

	/**
	 * Function to get the elements
	 *
	 * @param   STRING  $type     Type of which elements to be fetched
	 * @param   INT     $userId   User ID
	 * @param   STRING  $element  element eg com_tjlms.course
	 * @param   INT     $count    Limit
	 *
	 * @return  File path
	 *
	 * @since  1.0.0
	 */
	public function getElements($type, $userId, $element, $count = 5)
	{
		if (empty($userId))
		{
			return false;
		}

		// Create a new query object.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Join over the content for content title & url
		$query->select('a.id, a.assigned_by, a.created_date, c.title AS content_title');
		$query->from('`#__jlike_todos` AS a');
		$query->select('c.url AS content_url');
		$query->join('LEFT', '#__jlike_content AS c ON c.id=a.content_id');

		// Join over the created by field 'created_by'
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');
		$query->where('c.element = "' . $element . '"');

		if (!$userId)
		{
			return;
		}

		switch ($type)
		{
			case 'recommendByMe':
				$query->where('a.assigned_by = ' . $userId);
				$query->where('a.type = "reco"');
			break;

			case 'recommendToMe':
				$query->where('a.assigned_by <> ' . $userId);
				$query->where('a.assigned_to = ' . $userId);
				$query->where('a.type = "reco"');
			break;

			case 'sentassign':
				$query->where('a.assigned_by = ' . $userId);
				$query->where('a.type = "assign"');
			break;

			case 'myassign':
				$query->where('a.assigned_to = ' . $userId);
				$query->where('a.type = "assign"');
			break;
		}

		$query->group('a.content_id');
		$query->group('c.element');

		$db->setQuery($query);

		$total_rows = $db->query();

		// Get total number of rows
		$total_rows = $db->getNumRows();

		$query->setlimit($count);

		$db->setQuery($query);
		$data = $db->loadObjectlist();

		$result = array();
		$result['totalCount'] = $total_rows;
		$result['data'] = $data;

		return $result;
	}

	/**
	 * Function to Update assignment details
	 *
	 * @param   ARRAY  $data         formdata
	 * @param   ARRAY  $options      plugin details
	 * @param   INT    $notify_user  notification flag
	 *
	 * @return  boolean
	 *
	 * @since  1.0.0
	 */
	public function updateTodos($data, $options, $notify_user = 1)
	{
		$lang      = JFactory::getLanguage();
		$extension = 'com_jlike';
		$base_dir  = JPATH_ROOT;
		$lang->load($extension, $base_dir);

		if (!empty($data) && !empty($options))
		{
			JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_jlike/models');
			$JlikeModelRecommend              = JModelLegacy::getInstance('Recommend', 'JlikeModel');
			$res                 = $JlikeModelRecommend->assignRecommendUsers($data, $options, $data['notify_user']);

			return $res;
		}
	}

	/**
	 * To getAssignDetails
	 *
	 * @param   int  $user_id    The user ID
	 * @param   int  $course_id  The user ID
	 *
	 * @return  int $content_id The content ID
	 *
	 * @since  1.0.0
	 */
	public function getAssignDetails($user_id, $course_id)
	{
		if (!empty($user_id) &&  !empty($course_id))
		{
			$db     = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('todo.start_date,todo.due_date,todo.id as todo_id');
			$query->join('LEFT', '#__jlike_content as con ON con.id=todo.content_id');
			$query->from('`#__jlike_todos` AS todo');
			$query->where('todo.state=1 AND todo.assigned_to=' . $user_id . ' AND con.element_id=' . $course_id . ' AND con.element="com_tjlms.course"');
			$db->setQuery($query);
			$assigndetails     = $db->loadObject();

			if (!empty($assigndetails))
			{
				return $assigndetails;
			}
		}
	}

	/**
	 * To delete to do function
	 *
	 * @param   int  $todo_id  The user ID
	 *
	 * @return  true/false
	 *
	 * @since  1.0.0
	 */
	public function deleteTodo($todo_id)
	{
		if (!$todo_id)
		{
			return;
		}

		// Load jlike main helper to call api function for assigndetails and other
		$path = JPATH_SITE . '/components/com_jlike/models/recommend.php';

		if (JFile::exists($path))
		{
			if (!class_exists('JlikeModelRecommend'))
			{
				JLoader::register('JlikeModelRecommend', $path);
				JLoader::load('JlikeModelRecommend');
			}
		}

		$JlikeModelRecommend = new JlikeModelRecommend;

		return $res                 = $JlikeModelRecommend->deleteTodo($todo_id);
	}

	/**
	 * To Goal details
	 *
	 * @param   int  $user_id     The user ID
	 * @param   int  $content_id  The content ID
	 *
	 * @return  object
	 *
	 * @since  1.0.0
	 */
	public function getGoalDetails($user_id, $content_id)
	{
		if (!empty($user_id) &&  !empty($content_id))
		{
			$db     = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('todo.start_date,todo.due_date,todo.assigned_to,todo.assigned_by,todo.id as todo_id');
			$query->join('LEFT', '#__jlike_content as con ON con.id=todo.content_id');
			$query->from('`#__jlike_todos` AS todo');
			$query->where('todo.state=1 AND todo.assigned_to=' . $user_id . ' AND con.id=' . $content_id . ' AND con.element="com_tjlms.course"');

			$db->setQuery($query);
			$goalsdetails     = $db->loadObject();

			if (!empty($goalsdetails))
			{
				return $goalsdetails;
			}
		}
	}
}
