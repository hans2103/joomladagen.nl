<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

/**
 * main helper class
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class JTicketingIntegrationsHelper
{
	/**
	 * IntegrationHelper constructor
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$params                       = JComponentHelper::getParams('com_jticketing');
		$socialIntegrationOption      = $params->get('social_integration') ? $params->get('social_integration') : $params->get('socail_integration');

		if ($socialIntegrationOption != 'none')
		{
			if ($socialIntegrationOption == 'joomla')
			{
				jimport('techjoomla.jsocial.joomla');
			}
			elseif ($socialIntegrationOption == 'jomsocial')
			{
				jimport('techjoomla.jsocial.jomsocial');
			}
			elseif ($socialIntegrationOption == 'EasySocial')
			{
				jimport('techjoomla.jsocial.easysocial');
			}
			elseif($socialIntegrationOption == 'cb')
			{
				jimport('techjoomla.jsocial.cb');
			}
		}
	}

	/**
	 * Get user profile url
	 *
	 * @param   integer  $userid    userid
	 * @param   integer  $relative  relative
	 *
	 * @return  string  profile url
	 *
	 * @since   1.0
	 */
	public function getUserProfileUrl($userid, $relative = false)
	{
		$jticketingFrontendHelper = new jticketingFrontendHelper;
		$params                   = JComponentHelper::getParams('com_jticketing');
		$socialIntegrationOption  = $params->get('social_integration') ? $params->get('social_integration') : $params->get('socail_integration');
		$link                     = '';
		$length                   = strlen(JUri::base(true)) + 1;
		$user = JFactory::getUser($userid);

		if ($socialIntegrationOption == 'joomla')
		{
			$sociallibraryclass = new JSocialJoomla;
		}
		elseif ($socialIntegrationOption == 'cb')
		{
			$sociallibraryclass = new JSocialCB;
		}
		elseif ($socialIntegrationOption == 'jomsocial')
		{
			$sociallibraryclass = new JSocialJomsocial;
		}
		elseif ($socialIntegrationOption == 'EasySocial')
		{
			$sociallibraryclass = new JSocialEasysocial;
		}

		$link = $sociallibraryclass->getProfileUrl($user, $relative);

		return $link;
	}

	/**
	 * Get user Avatar
	 *
	 * @param   integer  $userid    userid
	 * @param   integer  $relative  relative
	 *
	 * @return  string  profile url
	 *
	 * @since   1.0
	 */
	public function getUserAvatar($userid, $relative = false)
	{
		$user = JFactory::getUser($userid);
		$JTicketingIntegrationsHelper = new JTicketingIntegrationsHelper;
		$params                       = JComponentHelper::getParams('com_jticketing');
		$socialIntegrationOption      = $params->get('social_integration') ? $params->get('social_integration') : $params->get('socail_integration');
		$gravatar                     = $params->get('gravatar');
		$uimage                       = '';

		if ($socialIntegrationOption == "joomla")
		{
			if ($gravatar)
			{
				$user     = JFactory::getUser($userid);
				$usermail = $user->get('email');

				// Refer https://en.gravatar.com/site/implement/images/php/
				$hash     = md5(strtolower(trim($usermail)));
				$uimage   = 'http://www.gravatar.com/avatar/' . $hash . '?s=32';

				return $uimage;
			}
			else
			{
				if ($relative)
				{
					$uimage = 'media/com_jticketing/images/default_avatar.png';
				}
				else
				{
					JUri::root() . 'media/com_jticketing/images/default_avatar.png';
				}
			}
		}
		else
		{
			if ($socialIntegrationOption == "cb")
			{
				$sociallibraryclass = new JSocialCB;
			}
			elseif ($socialIntegrationOption == "jomsocial")
			{
				$sociallibraryclass = new JSocialJomsocial;
			}
			elseif ($socialIntegrationOption == "EasySocial")
			{
				$sociallibraryclass = new JSocialEasysocial;
			}

			$uimage = $sociallibraryclass->getAvatar($user, '', $relative);
		}

		return $uimage;
	}

	/**
	 * Get user profile url
	 *
	 * @param   string  $paymentform  script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function profileImport($paymentform = '')
	{
		$JTicketingIntegrationsHelper = new JTicketingIntegrationsHelper;
		$cdata['userbill'] = new stdclass;

		$params      = JComponentHelper::getparams('com_jticketing');
		$socialIntegrationOption = $params->get('social_integration') ? $params->get('social_integration') : $params->get('socail_integration');

		if ($socialIntegrationOption == 'joomla')
		{
			$cdata = $JTicketingIntegrationsHelper->joomlaProfileimport($paymentform);
		}
		elseif ($socialIntegrationOption == 'jomsocial')
		{
			$cdata = $JTicketingIntegrationsHelper->jomsocialProfileimport($paymentform);
		}
		elseif ($socialIntegrationOption == 'cb')
		{
			$cdata = $JTicketingIntegrationsHelper->cbProfileimport($paymentform);
		}
		elseif ($socialIntegrationOption == 'EasySocial')
		{
			$cdata = $JTicketingIntegrationsHelper->EasySocialProfileimport($paymentform);
		}

		return $cdata;
	}

	/**
	 * Get user profile url
	 *
	 * @param   string  $paymentform  script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function joomlaProfileimport($paymentform = '')
	{
		$cdata['userbill'] = new stdclass;
		$params            = JComponentHelper::getparams('com_jticketing');
		$user              = JFactory::getuser();
		$userinfo          = JArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$user_profile      = JUserHelper::getProfile($user->id);

		// Convert object to array
		$user_profile  = JArrayHelper::fromObject($user_profile, $recurse = true, $regex = null);
		$mapping       = $params->get('fieldmap');
		$mapping_field = explode("\n", $mapping);

		foreach ($mapping_field as $each_field)
		{
			$field            = explode("=", $each_field);
			$jticketing_field = '';
			$joomla_field     = '';

			if (isset($field[1]))
			{
				$jticketing_field = trim($field[0]);
				$joomla_field     = trim($field[1]);
				$joomla_field     = trim(str_replace(',*', '', $joomla_field));
			}

			if ($joomla_field != 'password')
			{
				if (array_key_exists($joomla_field, $userinfo))
				{
					if ($paymentform)
					{
						$cdata[$jticketing_field] = $userinfo[$joomla_field];
					}
					else
					{
						$cdata['userbill']->$jticketing_field = $userinfo[$joomla_field];
					}
				}
				else
				{
					if (!empty($user_profile['profile']))
					{
						if (array_key_exists($joomla_field, $user_profile['profile']))
						{
							if ($paymentform)
							{
								$cdata[$jticketing_field] = $user_profile['profile'][trim($joomla_field)];
							}
							else
							{
								$cdata['userbill']->$jticketing_field = $user_profile['profile'][trim($joomla_field)];
							}
						}
					}
				}
			}
		}

		return $cdata;
	}

	/**
	 * Get user profile url
	 *
	 * @param   string  $paymentform  script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function cbProfileimport($paymentform)
	{
		// Load CB framework
		global $_CB_framework, $mainframe, $_CB_database, $ueConfig;

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
		$myId   = $_CB_framework->myId();
		$cbUser = CBuser::getInstance($myId);

		if (!$cbUser)
		{
			$cbUser = CBuser::getInstance(null);
		}

		$user          = $cbUser->getUserData();
		$cdata         = array();
		$params        = JComponentHelper::getparams('com_jticketing');
		$userinfo      = JArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$mapping       = $params->get('cb_fieldmap');
		$mapping_field = explode("\n", $mapping);
		$cdata['userbill'] = new StdClass;

		foreach ($mapping_field AS $each_field)
		{
			$field            = explode("=", $each_field);
			$jticketing_field = '';
			$CB_field         = '';

			if (isset($field[1]))
			{
				$jticketing_field = trim($field[0]);
				$CB_field         = trim($field[1]);
				$CB_field         = trim(str_replace(',*', '', $CB_field));
			}

			if ($CB_field != 'password')
			{
				if (array_key_exists($CB_field, $userinfo))
				{
					if ($paymentform)
					{
						$cdata[$jticketing_field] = $userinfo[$CB_field];
					}
					else
					{
						$cdata['userbill']->$jticketing_field = $userinfo[$CB_field];
					}
				}
			}
		}

		return $cdata;
	}

	/**
	 * Get user profile url
	 *
	 * @param   string  $paymentform  script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function jomsocialProfileimport($paymentform = '')
	{
		$cdata['userbill'] = new stdclass;
		$params            = JComponentHelper::getparams('com_jticketing');
		$jspath            = JPATH_ROOT . DS . 'components' . DS . 'com_community';

		if (!file_exists($jspath))
		{
			return;
		}

		include_once $jspath . '/libraries/core.php';
		$userpro       = CFactory::getUser();
		$user          = CFactory::getUser();
		$userinfo      = JArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$mapping       = $params->get('jomsocial_fieldmap');
		$mapping_field = explode("\n", $mapping);

		foreach ($mapping_field as $each_field)
		{
			$field            = explode("=", $each_field);
			$jticketing_field = '';
			$jomsocial_field  = '';

			if (isset($field[1]))
			{
				$jticketing_field = trim($field[0]);
				$jomsocial_field  = trim($field[1]);
				$jomsocial_field  = trim(str_replace(',*', '', $jomsocial_field));
			}

			if ($jomsocial_field != 'password')
			{
				if (array_key_exists($jomsocial_field, $userinfo))
				{
					if ($paymentform)
					{
						if (!empty($userinfo[$jomsocial_field]))
						{
							$cdata[$jticketing_field] = $userinfo[$jomsocial_field];
						}
					}
					else
					{
						if (!empty($userinfo[$jomsocial_field]))
						{
							$cdata['userbill']->$jticketing_field = $userinfo[$jomsocial_field];
						}
					}
				}
				else
				{
					$userInfo = $userpro->getInfo($jomsocial_field);

					if (!empty($userInfo))
					{
						if ($paymentform)
						{
							$cdata[$jticketing_field] = $userInfo;
						}
						else
						{
							$cdata['userbill']->$jticketing_field = $userInfo;
						}
					}
				}
			}
		}

		return $cdata;
	}

	/**
	 * Get user profile url
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function profileChecking()
	{
		$JTicketingIntegrationsHelper = new JTicketingIntegrationsHelper;
		$params                       = JComponentHelper::getParams('com_jticketing');
		$integration                  = $params->get('integration');
		$msg_field_required           = array();

		if ($integration == 'joomla')
		{
			$msg_field_required = $JTicketingIntegrationsHelper->joomlaProfileChecking($params);
		}
		elseif ($integration == 'jomsocial')
		{
			// $msg_field_required=JTicketingIntegrationsHelper::jomsocialProfileChecking($params);
		}

		return $msg_field_required;
	}

	/**
	 * Get user profile url
	 *
	 * @param   string  $params  script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function joomlaProfileChecking($params)
	{
		$msg_field_required = array();
		$user               = JFactory::getUser();
		$user_profile       = JUserHelper::getProfile($user->id);
		$user               = JArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$user_profile       = JArrayHelper::fromObject($user_profile, $recurse = true, $regex = null);
		$mapping            = $params->get('fieldmap');
		$required_field     = explode("\n", $mapping);

		if (isset($required_field))
		{
			foreach ($required_field as $eachfield)
			{
				$eachfield = explode(",", $eachfield);

				if (isset($eachfield[1]))
				{
					$row            = $eachfield[0];
					$required_tmp   = explode("=", $row);
					$required_field = $required_tmp[1];

					if ($required_field != 'password')
					{
						// If field not set is user array  then check  field in user profile array
						if ((array_key_exists($required_field, $user)) or (array_key_exists($required_field, $user_profile['profile'])))
						{
							$userfield        = '';
							$userProfilefield = '';

							if (!empty($user[$required_field]))
							{
								$userfield = trim($user[$required_field]);
							}

							if (empty($userfield))
							{
								if (!empty($user_profile['profile'][$required_field]))
								{
									$userProfilefield = trim($user_profile['profile'][$required_field]);
								}

								if (empty($userProfilefield))
								{
									$msg_field_required[] = $required_field;
								}
							}
						}
						elseif (empty($user_profile['profile']))
						{
							// If user not edit his account first time after profile plugin is enabled
							$msg_field_required[] = $required_field;
						}
					}
				}
			}
		}

		return $msg_field_required;
	}

	/**
	 * Get user profile url
	 *
	 * @param   string  $params  script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function jomsocialProfileChecking($params)
	{
		$jspath = JPATH_ROOT . '/components/com_community';

		if (!file_exists($jspath))
		{
			return;
		}

		include_once $jspath . '/libraries/core.php';
		$user               = CFactory::getUser();
		$msg_field_required = array();
		$user               = JArrayHelper::fromObject($user, $recurse = true, $regex = null);
		$mapping            = $params->get('jomsocial_fieldmap');
		$required_field     = explode("\n", $mapping);

		if (isset($required_field))
		{
			foreach ($required_field as $eachfield)
			{
				$eachfield = explode(",", $eachfield);

				if (isset($eachfield[1]))
				{
					$row            = $eachfield[0];
					$required_tmp   = explode("=", $row);
					$required_field = trim($required_tmp[1]);

					if ($required_field != 'password')
					{
						if (array_key_exists($required_field, $user))
						{
							$userfield        = '';
							$userProfilefield = '';

							if (!empty($user[$required_field]))
							{
								$userfield = trim($user[$required_field]);
							}
						}
						else
						{
							$userpro  = CFactory::getUser();
							$userInfo = $userpro->getInfo($required_field);

							if (empty($userInfo))
							{
								$msg_field_required[] = $required_field;
							}
						}
					}
				}
			}
		}

		return $msg_field_required;
	}

	/**
	 * Get user profile url
	 *
	 * @param   string  $paymentform  script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function EasySocialProfileimport($paymentform = '')
	{
		$db     = JFactory::getDBO();
		$params = JComponentHelper::getparams('com_jticketing');

		if (defined('JPATH_ADMINISTRATOR'))
		{
			if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_easysocial/foundry.php'))
			{
				echo 'EeasySocial not installed!';

				return false;
			}
		}
		else
		{
			if (!file_exists($mainframe->getCfg('absolute_path') . '/administrator/components/com_easysocial/foundry.php'))
			{
				echo 'EeasySocial not installed!';

				return false;
			}
		}

		$cdata             = array();
		$cdata['userbill'] = new stdclass;
		$mapping       = $params->get('easysocial_fieldmap');
		$mapping_field = explode("\n", $mapping);
		$socialtypes   = '';

		foreach ($mapping_field as $each_field)
		{
			$field = explode("=", $each_field);

			if (isset($field[1]))
			{
				$jticketing_field = trim($field[0]);
				$Esocial_field    = trim($field[1]);

				// Remove campalsory star
				$socialtypes .= "'" . trim(str_replace('*', '', $Esocial_field)) . "',";
			}
		}

		$socialtypes = substr($socialtypes, 0, -1);
		$userid      = JFactory::getUser()->id;
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('data', 'datakey')));
		$query->from($db->quoteName('#__social_fields_data'));
		$query->where($db->quoteName('uid') . ' = ' . $userid);
		$query->where($db->quoteName('datakey') . ' IN (' . $socialtypes . ')');
		$db->setQuery($query);
		$results = $db->loadObjectList('datakey');

		foreach ($results as $k => $row)
		{
			switch ($k)
			{
				case 'name':
					$name                         = explode(" ", $row->data);
					$cdata['userbill']->firstname = $first_name = $name['0'];
					$cnt                          = count($name);

					if ($cnt > 1)
					{
						$cdata['userbill']->lastname = $name['1'];
					}

					break;
				case 'address':
					$cdata['userbill']->address = $row->data;
					break;

				case 'address2':
					$cdata['userbill']->address2 = $row->data;
					break;
				case 'phon':
					$cdata['userbill']->phon = $row->textbox;
					break;

				case 'country':
					$cdata['userbill']->country      = $row->data;
					$country                         = "`country` LIKE '$row->data'";
					$cdata['userbill']->country_code = $this->getdata('id', '#__tj_country', $country);
					break;

				case 'state':
					$state                         = "`region` LIKE '$row->data'";
					$cdata['userbill']->state_code = $this->getdata('id', '#__tj_region', $state);
					break;

				case 'city':
					$cdata['userbill']->city = $row->data;
					break;

				case 'zip':
					$cdata['userbill']->zip = $row->data;
					break;
			}
		}

		// For payment_paymentform layout
		if ($paymentform)
		{
			$cdata['paypal_email'] = JFactory::getUser()->email;
		}
		else
		{
			$cdata['userbill']->paypal_email = JFactory::getUser()->email;
		}

		return $cdata;
	}

	/**
	 * Get user profile url
	 *
	 * @param   string  $compare_fields_array  script
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function getUserInfo($compare_fields_array = null)
	{
		$db           = JFactory::getDBO();
		$result_array = array();
		$user         = JFactory::getUser();

		foreach ($compare_fields_array as $ind => $filedid)
		{
			// Attach custom fields into the user object
			$strSQL = 'SELECT fdata.data ' . 'FROM `#__social_fields_data` AS fdata ' . '
			WHERE fdata.uid=' . $user->id . ' ' . ' AND fdata.field_id=' . $filedid->value;
			$db->setQuery($strSQL);
			$result = $db->loadResult();

			if ($db->getErrorNum())
			{
				JError::raiseError(500, $db->stderr());
			}

			if ($result)
			{
				$result_array[$filedid->text] = $result;
			}
			else
			{
				$result_array[$filedid->text] = '';
			}
		}

		$result_array['email'] = $user->email;

		return $result_array;
	}

	/**
	 * DisplayjlikeButton
	 *
	 * @param   string  $data   name of view
	 * @param   string  $table  layout of view
	 * @param   string  $cond   site/admin template
	 *
	 * @return  void
	 */
	public function getdata($data, $table, $cond)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($data);
		$query->from($table);
		$query->where($cond);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * DisplayjlikeButton
	 *
	 * @param   string  $event_url          name of view
	 * @param   string  $id                 layout of view
	 * @param   string  $title              site/admin template
	 * @param   string  $show_comments      site/admin view
	 * @param   string  $show_like_buttons  site/admin view
	 *
	 * @return  void
	 */
	public function DisplayjlikeButton($event_url, $id, $title, $show_comments, $show_like_buttons)
	{
		$jlikeparams            = array();
		$jlikeparams['url']     = $event_url;
		$jlikeparams['eventid'] = $id;
		$jlikeparams['title']   = $title;
		$dispatcher             = JDispatcher::getInstance();
		JPluginHelper::importPlugin('content', 'jlike_events');
		$grt_response = $dispatcher->trigger('onBeforeDisplaylike', array('com_jticketing.event', $jlikeparams, $show_comments, $show_like_buttons));

		if (!empty($grt_response['0']))
		{
			return $grt_response['0'];
		}
		else
		{
			return '';
		}
	}
}
