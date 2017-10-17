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
defined('_JEXEC') or die('Restricted access');

if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

// Component Helper
jimport('joomla.application.component.helper');

/**
 * MigrateHelper helper
 *
 * @package     Jlike
 * @subpackage  site
 * @since       2.2
 */
class SocialintegrationHelper
{
	/**
	 * GetUserProfileUrl.
	 *
	 * @param   integer  $userid  User id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getUserProfileUrl($userid, $integration_option = '')
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

		$comjlikeHelper     = new comjlikeHelper;
		$link               = '';

		if ($integration_option == 'joomla')
		{
			// $itemid=jgiveFrontendHelper::getItemId('option=com_users');
			$link = '';
		}
		elseif ($integration_option == 'cb')
		{
			$installed = $comjlikeHelper->Checkifinstalled('com_comprofiler');

			if ($installed)
			{
				$itemid = $comjlikeHelper->getItemId('option=com_comprofiler');
				$URL = 'index.php?option=com_comprofiler&task=userprofile&user=' . $userid . '&Itemid=' . $itemid;
				$link   = JUri::root() . substr(JRoute::_($URL), strlen(JUri::base(true)) + 1);
			}
		}
		elseif ($integration_option == 'js')
		{
			$installed = $comjlikeHelper->Checkifinstalled('com_community');

			if ($installed)
			{
				$link   = '';
				$jspath = JPATH_ROOT . DS . 'components' . DS . 'com_community';

				if (file_exists($jspath))
				{
					include_once $jspath . DS . 'libraries' . DS . 'core.php';

					$link = JUri::root() . substr(CRoute::_('index.php?option=com_community&view=profile&userid=' . $userid), strlen(JUri::base(true)) + 1);
				}
			}
		}
		elseif ($integration_option == 'jomwall')
		{
			$installed = $comjlikeHelper->Checkifinstalled('com_awdwall');

			if ($installed)
			{
				if (!class_exists('AwdwallHelperUser'))
				{
					require_once JPATH_SITE . DS . 'components' . DS . 'com_awdwall' . DS . 'helpers' . DS . 'user.php';
				}

				$awduser = new AwdwallHelperUser;
				$Itemid  = $awduser->getComItemId();
				$link    = JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $userid . '&Itemid=' . $Itemid);
			}
		}
		elseif ($integration_option == 'EasySocial')
		{
			$espath = JPATH_ROOT . DS . 'components' . DS . 'com_easysocial';

			if ($espath)
			{
				$link = '';

				if (file_exists($espath))
				{
					require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
					$user = Foundry::user($userid);
					$link = JRoute::_($user->getPermalink());
				}
			}
		}

		return $link;
	}

	/**
	 * GetUserAvtar.
	 *
	 * @param   Object  $user  User Obj.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getUserAvatar($user)
	{
		$comjlikeHelper          = new comjlikeHelper;
		$socialintegrationHelper = new socialintegrationHelper;
		$userid                  = $user->id;
		$useremail               = $user->email;
		$params                  = JComponentHelper::getParams('com_jlike');
		$integration_option      = $params->get('integration');
		$uimage                  = '';

		if ($integration_option == "joomla")
		{
			$uimage = $socialintegrationHelper->get_gravatar($useremail, '40', 'mm', 'g');
		}
		elseif ($integration_option == "cb")
		{
			$installed = $comjlikeHelper->Checkifinstalled('com_comprofiler');

			if ($installed)
			{
				$uimage = $socialintegrationHelper->getCBUserAvatar($userid);
			}
		}
		elseif ($integration_option == "js")
		{
			$installed = $comjlikeHelper->Checkifinstalled('com_community');

			if ($installed)
			{
				$uimage = $socialintegrationHelper->getJomsocialUserAvatar($userid);
			}
		}
		elseif ($integration_option == "jomwall")
		{
			$installed = $comjlikeHelper->Checkifinstalled('com_awdwall');

			if ($installed)
			{
				$uimage = $socialintegrationHelper->getJomwallUserAvatar($userid);
			}
		}
		elseif ($integration_option == "EasySocial")
		{
			$uimage = $socialintegrationHelper->getEasySocialUserAvatar($userid);
		}

		return $uimage;
	}

	/**
	 * GetUserProfileUrl.
	 *
	 * @param   integer  $userid  User id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getEasySocialUserAvatar($userid)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
		$user   = Foundry::user($userid);
		$uimage = $user->getAvatar();

		return $uimage;
	}

	/**
	 * GetUserProfileUrl.
	 *
	 * @param   integer  $userid  User id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getCBUserAvatar($userid)
	{
		$db = JFactory::getDBO();
		$q  = "SELECT a.id,a.username,a.name, b.avatar, b.avatarapproved
			FROM #__users a, #__comprofiler b
			WHERE a.id=b.user_id AND a.id=" . $userid;
		$db->setQuery($q);
		$user     = $db->loadObject();
		$img_path = JUri::root() . "images/comprofiler";

		if (isset($user->avatar) && isset($user->avatarapproved))
		{
			if (substr_count($user->avatar, "/") == 0)
			{
				$uimage = $img_path . '/tn' . $user->avatar;
			}
			else
			{
				$uimage = $img_path . '/' . $user->avatar;
			}
		}
		elseif (isset($user->avatar))
		{
			$uimage = JUri::root() . "/components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png";
		}
		else
		{
			$uimage = JUri::root() . "/components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png";
		}

		return $uimage;
	}

	/**
	 * GetUserProfileUrl.
	 *
	 * @param   integer  $userid  User id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getJomsocialUserAvatar($userid)
	{
		$mainframe = JFactory::getApplication();
		/*included to get jomsocial avatar*/
		$uimage    = '';
		$jspath    = JPATH_ROOT . DS . 'components' . DS . 'com_community';

		if (file_exists($jspath))
		{
			include_once $jspath . DS . 'libraries' . DS . 'core.php';

			$user   = CFactory::getUser($userid);
			$uimage = $user->getThumbAvatar();

			if (!$mainframe->isSite())
			{
				$uimage = str_replace('administrator/', '', $uimage);
			}
		}

		return $uimage;
	}

	/**
	 * GetJomwallUserAvatar.
	 *
	 * @param   integer  $userid  User id.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function getJomwallUserAvatar($userid)
	{
		if (!class_exists('AwdwallHelperUser'))
		{
			require_once JPATH_SITE . DS . 'components' . DS . 'com_awdwall' . DS . 'helpers' . DS . 'user.php';
		}

		$awduser = new AwdwallHelperUser;
		$uimage  = $awduser->getAvatar($userid);

		return $uimage;
	}

	/**
	 * GetJomwallUserAvatar.
	 *
	 * @param   string   $email  email.
	 * @param   integer  $s      80.
	 * @param   string   $d      mm.
	 * @param   string   $r      g.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function get_gravatar($email, $s = 80, $d = 'mm', $r = 'g')
	{
		$url = 'http://www.gravatar.com/avatar/';
		$url .= md5(strtolower(trim($email)));
		$url .= "?s=$s&d=$d&r=$r";

		return $url;
	}

	/**
	 * notification_sender : the user who send notification.
	 *
	 * @param   string  $notification_to      to.
	 * @param   string  $username             username.
	 * @param   string  $notification_sender  sender.
	 * @param   string  $notification_msg     msg.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function send_es_notification($notification_to, $username, $notification_sender, $notification_msg, $notify_url = '')
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';

		// $recipient - can be either array of user id or id or user objects
		$recipient[]  = $notification_sender;

		/* If you do not want to send email, $emailOptions should be set to false
		 $emailOptions - An array of options to define in the mail
		 Email template */
		$emailOptions = false;

		/* If you do not want to send system notifications, set this to false.
		$systemOptions - The internal system notifications
		System notification template */

		$myUser        = Foundry::user($notification_to);
		$title         = $myUser->getName() . " " . $notification_msg;

		$tem = array();
		$tem['uid'] = 'Jlike_notification';
		$tem['actor_id'] = $notification_to;
		//$tem['type'] = $Jlike_notifive;
		$tem['title'] = $title;

		if (empty($notify_url))
		{
			$notify_url = JRoute::_($myUser->getPermalink());
		}

		$tem['url'] = $notify_url;
		$tem['image'] = '';

		$systemOptions = $tem;
		Foundry::notify('Jlike_notification.create', $recipient, $emailOptions, $systemOptions);
	}

	/**
	 * notification_sender : the user who send notification.
	 *
	 * @param   string  $notification_to      to.
	 * @param   string  $username             username.
	 * @param   string  $notification_sender  sender.
	 * @param   string  $notification_msg     msg.
	 *
	 * @return void
	 *
	 * @since 3.0
	 */
	public function send_js_notification($notification_to, $username, $notification_sender, $notification_msg)
	{
		// $invitex_settings	= cominvitexHelper::getconfigData();
		// $to_direct        =  $invitex_settings["reg_direct"];

		// $activitysocialintegrationprofiledata=new activitysocialintegrationprofiledata();

		// $invitee_profile_url=JRoute::_($activitysocialintegrationprofiledata->getUserProfileUrl($to_direct,$notification_to));
		// $notification_subject='<a href="'.$invitee_profile_url.'" >'.$username.'</a>'. $notification_msg;

		$model = CFactory::getModel('Notification');
		$model->add($notification_to, $notification_sender, $notification_msg, 'notif_system_messaging', '0', '');
	}
}
