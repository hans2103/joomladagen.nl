<?php
/**
 * @version     SVN: <svn_id>
 * @package     Techjoomla.Libraries
 * @subpackage  JSocial
 * @author      Techjoomla <extensions@techjoomla.com>
 * @copyright   Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('JPATH_BASE') or die;
jimport('joomla.filesystem.folder');
jimport('techjoomla.jsocial');
jimport('techjoomla.jsocial.helper');
jimport('techjoomla.jsocial.jsocial');

/**
 * Interface to handle Social Extensions
 *
 * @package     Techjoomla.Libraries
 * @subpackage  JSocial
 * @since       1.0.4
 */
class JSocialCB implements JSocial
{
	/**
	 * The constructor
	 *
	 * @since  1.0
	 */
	public function __construct()
	{
		if (!$this->checkExists())
		{
			throw new Exception('Community Builder is not Installed');
		}
	}

	/**
	 * The function to get profile data of User
	 *
	 * @param   MIXED  $user  JUser Objcet
	 *
	 * @return  JUser Objcet
	 *
	 * @since   1.0
	 */
	public function getProfileData(JUser $user)
	{
	}

	/**
	 * The function to get profile link User
	 *
	 * @param   MIXED    $user      JUser Objcet
	 * @param   BOOLEAN  $relative  returns relative URL if true
	 *
	 * @return  STRING
	 *
	 * @since   1.0.4
	 */
	public function getProfileUrl(JUser $user, $relative = false)
	{
		if ($relative)
		{
			$link = 'index.php?option=com_comprofiler&task=userprofile&user=' . $user->id;
		}
		else
		{
			$link = 'index.php?com_comprofiler&view=userprofile';
			$itemid = JSocialHelper::getItemId($link);
			$link = JRoute::_('index.php?option=com_comprofiler&task=userprofile&user=' . $user->id . '&Itemid=' . $itemid);

			if (strpos($link, JUri::root()) === false)
			{
				$link = JUri::root() . substr($link, strlen(JUri::base(true)) + 1);
			}
		}

		return $link;
	}

	/**
	 * The function to get profile AVATAR of a User
	 *
	 * @param   MIXED    $user           JUser Objcet
	 *
	 * @param   INT      $gravatar_size  Size of the AVATAR
	 *
	 * @param   BOOLEAN  $relative       returns relative URL if true
	 *
	 * @return  STRING
	 *
	 * @since   1.0
	 */
	public function getAvatar(JUser $user, $gravatar_size = '', $relative = false)
	{
		$db = JFactory::getDbo();
		$q = "SELECT a.id,a.username,a.name, b.avatar, b.avatarapproved
            FROM #__users a, #__comprofiler b
            WHERE a.id=b.user_id AND a.id=" . $user->id;
		$db->setQuery($q);
		$cbuser = $db->loadObject();
		$img_path = JUri::root() . "images/comprofiler";

		if (isset($cbuser->avatar) && isset($cbuser->avatarapproved))
		{
			if (substr_count($cbuser->avatar, "/") == 0)
			{
				$uimage = $img_path . '/tn' . $cbuser->avatar;
			}
			else
			{
				$uimage = $img_path . '/' . $cbuser->avatar;
			}
		}
		elseif (isset($cbuser->avatar))
		{
			// Avatar not approved
			$uimage = JUri::root() . "components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png";
		}
		else
		{
			// No avatar
			$uimage = JUri::root() . "components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png";
		}

		if ($relative)
		{
			$uimage = str_replace(JUri::root(), '', $uimage);
		}

		return $uimage;
	}

	/**
	 * The function to get friends of a User
	 *
	 * @param   MIXED  $user      JUser Objcet
	 * @param   INT    $accepted  Optional param, bydefault true to get only friends with request accepted
	 * @param   INT    $options   Optional array.. Extra options to pass to the getFriends Query :
	 * state, limit and idonly(if idonly only ids array will be returned) are supported
	 *
	 * @return  Friends objects
	 *
	 * @since   1.0
	 */
	public function getFriends(JUser $user, $accepted=true, $options = array())
	{
	}

	/**
	 * The function to add provided users as Friends
	 *
	 * @param   MIXED  $connect_from_user  User who is requesting connection
	 * @param   INT    $connect_to_user    User whom to request
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addFriend(JUser $connect_from_user, JUser $connect_to_user)
	{
		$db = JFactory::getDbo();

		// Set frnd cnt to 1 make inviter and invitee friends
		$sql			= "SELECT * FROM #__comprofiler_members
							WHERE referenceid=" . $connect_from_user->id . " AND memberid  = " . $connect_to_user->id;
		$db->setQuery($sql);
		$once_done = $db->loadResult();

		if (!$once_done)
		{
			$insertfrnd = new stdClass;
			$insertfrnd->referenceid		=	$connect_to_user->id;
			$insertfrnd->memberid 			=	$connect_from_user->id;
			$insertfrnd->accepted 			=	1;
			$insertfrnd->pending			=	0;
			$insertfrnd->membersince		=	$dt;
			$db->insertObject('#__comprofiler_members', $insertfrnd);

			$insertfrnds = new stdClass;
			$insertfrnds->referenceid		=	$connect_from_user->id;
			$insertfrnds->memberid 			=	$connect_to_user->id;
			$insertfrnds->accepted			=	1;
			$insertfrnds->pending			=	0;
			$insertfrnds->membersince		=	$dt;
			$db->insertObject('#__comprofiler_members', $insertfrnds);
		}
	}

	/**
	 * The function to get Easysocial toolbar
	 *
	 * @return  toolbar HTML
	 *
	 * @since   1.0
	 */
	public function getToolbar()
	{
	}

	/**
	 * Add activity stream
	 *
	 * @param   INT     $actor_id         User against whom activity is added
	 * @param   STRING  $act_type         type of activity
	 * @param   STRING  $act_subtype      sub type of activity
	 * @param   STRING  $act_description  Activity description
	 * @param   STRING  $act_link         LInk of Activity
	 * @param   STRING  $act_title        Title of Activity
	 * @param   STRING  $act_access       Access level
	 *
	 * @return  true
	 *
	 * @since  1.0
	 */
	public function pushActivity($actor_id, $act_type, $act_subtype = '', $act_description = '',$act_link = '', $act_title='', $act_access='')
	{
		//  Load CB framework
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
			// Eecho 'CB Activity plugin not installed!';
			return false;
		}

		require_once JPATH_SITE . "/components/com_comprofiler/plugin/user/plug_cbactivity/cbactivity.class.php";

		// Push activity
		$linkHTML = '<a href="' . $act_link . '">' . $act_title . '</a>';

		$activity = new cbactivityActivity($_CB_database);
		$activity->set('user_id', $actor_id);
		$activity->set('type', $act_type);
		$activity->set('subtype', $act_subtype);
		$activity->set('title', $act_description . ' ' . $linkHTML);
		$activity->set('icon', 'nameplate');
		$activity->set('date', cbactivityClass::getUTCDate());
		$activity->store();

		return true;
	}

	/**
	 * The function to add stream
	 *
	 * @param   Array  $streamOption  Stram array
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function advPushActivity($streamOption)
	{
	}

	/**
	 * The function to set status of a user
	 *
	 * @param   MIXED   $user     User whose status is to be set
	 * @param   STRING  $status   status to be set
	 * @param   MIXED   $options  status to be set
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setStatus(JUser $user, $status, $options)
	{
	}

	/**
	 * The function to get registartion link for CB
	 *
	 * @param   ARRAY  $options  options
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getRegistrationLink($options)
	{
	}

	/**
	 * Send Message
	 *
	 * @param   OBJECT  $user       User who is sending Message
	 * @param   OBJECT  $recepient  User to whom Message is to send
	 *
	 * @return  boolean
	 *
	 * @since  1.0
	 */
	public function sendMessage(JUser $user, $recepient)
	{
	}

	/**
	 * The function to check if CB is installed
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function checkExists()
	{
		return JFolder::exists(JPATH_SITE . '/components/com_comprofiler');
	}

	/**
	 * Send Notification
	 *
	 * @param   OBJECT  $sender        User who is sending notification
	 * @param   OBJECT  $receiver      User to whom notification is to send
	 * @param   STRING  $content       Main content of the notification
	 * @param   STRING  $options       Optional options
	 * @param   STRING  $emailOptions  Email options. If you do not want to send email, $emailOptions should be set to false
	 *
	 * @return  boolean
	 *
	 * @since  1.0
	 */
	public function sendNotification(JUser $sender, JUser $receiver, $content = "JS Notification", $options = array(), $emailOptions = false)
	{
	}

	/**
	 * The function add points to user
	 *
	 * @param   MIXED  $receiver  User to whom points to be added
	 * @param   ARRAY  $options   is array
	 *
	 * $options[command] for example invites sent
	 * options[extension] for example com_invitex
	 *
	 * @return ARRAY success 0 or 1
	 */
	public function addpoints(JUser $receiver, $options=array())
	{
	}

	/**
	 * The function to create a group
	 *
	 * @param   ARRAY  $data     Data
	 * @param   ARRAY  $options  Additional data
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function createGroup($data, $options=array())
	{
	}

	/**
	 * The function to add member to a group
	 *
	 * @param   ARRAY   $groupId      Data
	 * @param   OBJECT  $groupmember  User object
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addMemberToGroup($groupId, JUser $groupmember)
	{
	}

	/**
	 * The function to update the custom fields
	 *
	 * @param   ARRAY   $fieldsArray  Custom field array
	 * @param   OBJECT  $userId       User Id
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function addUserFields($fieldsArray, $userId)
	{
	}
}
