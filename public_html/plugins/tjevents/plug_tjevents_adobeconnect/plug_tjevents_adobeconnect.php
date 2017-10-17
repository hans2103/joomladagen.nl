<?php
/**
 * @version    SVN: <svn_id>
 * @package    Techjoomla_API
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.folder');
jimport('joomla.plugin.plugin');
jimport('techjoomla.jsocial.jsocial');
jimport('techjoomla.jsocial.joomla');
$lang = JFactory::getLanguage();
$lang->load('plg_tjevents_plug_tjevents_adobeconnect', JPATH_ADMINISTRATOR);
require_once JPATH_SITE . '/plugins/tjevents/plug_tjevents_adobeconnect/plug_tjevents_adobeconnect/libraries/AdobeConnectClient.class.php';

/**
 * Class for sending sms
 *
 * @package     JTicketing
 * @subpackage  component
 * @since       1.0
 */
class Plgtjeventsplug_Tjevents_Adobeconnect extends JPlugin
{
	/**
	 * Adobeconnect plugin for online events
	 *
	 * @param   string  $subject  subject
	 * @param   array   $config   config
	 *
	 * @since   1.0
	 */
	public function __construct($subject, $config)
	{
		require_once JPATH_SITE . '/plugins/tjevents/plug_tjevents_adobeconnect/plug_tjevents_adobeconnect/libraries/AdobeConnectClient.class.php';
		parent::__construct($subject, $config);
		$api_username = $this->params->get('api_username');
		$api_password = $this->params->get('api_password');
		$host_url     = $this->params->get('host_url');
		$host_url     = trim($host_url);

		if ("/" == substr($host_url, -1))
		{
			$api_url = $host_url . 'api/';
		}
		else
		{
			$api_url = $host_url . '/api/';
		}

		$this->errorlogfile = 'adobeconnect.log.php';
		$this->user         = JFactory::getUser();
		$this->db           = JFactory::getDBO();
		$this->API_CONFIG   = array(
			'api_username' => trim($api_username),
			'api_password' => trim($api_password),
			'host_url' => trim($host_url),
			'api_url' => trim($api_url)
		);
	}

	/**
	 * Build Layout path
	 *
	 * @param   string  $layout  Layout name
	 *
	 * @since   2.2
	 *
	 * @return   string  Layout Path
	 */
	public function buildLayoutPath($layout)
	{
		$app       = JFactory::getApplication();
		$core_file = dirname(__FILE__) . '/' . $this->_name . '/tmpl/' . $layout . '.php';
		$template = $app->getTemplate();
		$override  = JPATH_BASE . '/' . 'templates' . '/' . $template . '/html/plugins/' . $this->_type . '/' . $this->_name . '/' . $layout . '.php';

		if (JFile::exists($override))
		{
			return $override;
		}
		else
		{
			return $core_file;
		}
	}

	/**
	 * Builds the layout to be shown, along with hidden fields.
	 *
	 * @param   object  $vars    Data from component
	 * @param   string  $layout  Layout name
	 *
	 * @since   2.2
	 *
	 * @return   string  Layout Path
	 */
	public function buildLayout($vars, $layout = 'default')
	{
		// Load the layout & push variables
		ob_start();
		$layout = $this->buildLayoutPath($layout);
		include $layout;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Get sco id for Adobe
	 *
	 * @param   array   $license      Adobe license details
	 * @param   string  $meeting_url  Meeting url
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function getscoID($license, $meeting_url)
	{
		$license = (object) $license;
		$meeting_url = trim($meeting_url);
		$loginInfo = $this->autologin($license);
		$connection = $this->setconnection($license);

		$scoinfo = array();

		try
		{
			$scoinfo = $connection->getScoInfobymeetingurl($meeting_url);
		}
		catch (Exception $e)
		{
		}

		if ($scoinfo['status']['@attributes']['code'] == 'ok')
		{
			return $scoinfo['sco']['@attributes']['sco-id'];
		}
	}

	/**
	 * Get all information of current logged in user on adobe
	 *
	 * @param   string  $license  meeting sco
	 *
	 * @since   2.2
	 *
	 * @return   string  Layout Path
	 */
	public function getAdobeconnectCommonInfo($license)
	{
		$loginInfo = $this->autologin($license);
		$connection = $this->setconnection($license);
		$result = $connection->getCommonInfo();

		if ($result['status']['@attributes']['code'] == 'ok')
		{
			return true;
		}
		else
		{
			$result['error_message'] = $this->reportError($result);

			return $result;
		}
	}

	/**
	 * Get all information of sco
	 *
	 * @param   string  $meeting_sco  meeting sco
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function getScoInfo($meeting_sco)
	{
		return $this->client_main->getCommonInfo($meeting_sco);
	}

	/**
	 * Get Attendance of event
	 *
	 * @param   array    $license      Adobe license details
	 * @param   string   $meeting_sco  meeting id
	 * @param   integer  $event_id     event id
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function getMeetingAttendance($license, $meeting_sco, $event_id)
	{
		// $meeting_url  = str_replace($license->host_url, "", $meeting_url);
		$loginInfo = $this->autologin($license);
		$connection = $this->setconnection($license);

		// Get attendance data using meeting sco id
		$meetingInformation   = $connection->getScoInfo($meeting_sco);
		$attendance   = $connection->getMeetingAttendance($meeting_sco);

		echo "Adobconnect getMeetingAttendance ";
		echo "<br><pre>";
		print_r($attendance);
		echo "</pre>";

		$user = JFactory::getUser();

		// Set config parma -> Enter minimum time to attend adobe connect meeting
		$minimum_time_value = $license->minimum_time_tomark_completion;

		$minimum_time = 0;

		if ($minimum_time_value)
		{
			$minimum_time = $minimum_time_value * 60;
		}

		$user_event_data = array();
		$meetingStartTime = new DateTime($meetingInformation['sco']['date-begin']);
		$meetingEndTime = new DateTime($meetingInformation['sco']['date-end']);

		if (!empty($attendance['report-meeting-attendance']))
		{
			foreach ($attendance['report-meeting-attendance'] as $key => $row)
			{
				if (key($row) == '0')
				{
					foreach ($row as $i => $acdata)
					{
						$loginUserData = $this->getUserData($acdata['login']);
						$createddate   = new DateTime($acdata['date-created']);
						$closeddate    = new DateTime($acdata['date-end']);
						$interval      = $closeddate->getTimestamp() - $createddate->getTimestamp();

						// Ex - meeting is 10 to 11
						if ($meetingStartTime > $createddate)
						{
							// Ex - Enter at 9.30
							$beforMeetingInterval = $meetingStartTime->getTimestamp() - $createddate->getTimestamp();
							$interval = ($interval - $beforMeetingInterval) < 0 ? 0 : ($interval - $beforMeetingInterval);
						}
						elseif ($closeddate < $meetingEndTime)
						{
							// Ex - close at 11.30
							$afterMeetingInterval = $closeddate->getTimestamp() - $meetingEndTime->getTimestamp();
							$interval = ($interval - $afterMeetingInterval) < 0 ? 0 : ($interval - $afterMeetingInterval);
						}

						// Date difference in minute
						$dateDiffResult = $interval * 60;

						if (array_key_exists($loginUserData['id'], $loginUserData))
						{
							$user_event_data[$loginUserData['id']][$i]['spent_time'] = $dateDiffResult;
							$user_event_data[$loginUserData['id']][$i]['checkin'] = $meetingStartTime->format('Y-m-d H:i:s');
							$user_event_data[$loginUserData['id']][$i]['checkout'] = $meetingEndTime->format('Y-m-d H:i:s');
							$user_event_data[$loginUserData['id']][$i]['event_id'] = $event_id;
						}
						else
						{
							$user_event_data[$loginUserData['id']][$i]['event_id'] = $event_id;
							$user_event_data[$loginUserData['id']][$i]['spent_time'] = $dateDiffResult;
							$user_event_data[$loginUserData['id']][$i]['checkin'] = $meetingStartTime->format('Y-m-d H:i:s');
							$user_event_data[$loginUserData['id']][$i]['checkout'] = $meetingEndTime->format('Y-m-d H:i:s');
						}
					}
				}
				else
				{
					$loginUserData = $this->getUserData($row['login']);
					$createddate   = new DateTime($row['date-created']);
					$closeddate    = new DateTime($row['date-end']);
					$interval      = $closeddate->getTimestamp() - $createddate->getTimestamp();

					// Ex - meeting is 10 to 11
					if ($meetingStartTime > $createddate)
					{
						// Ex - Enter at 9.30
						$beforMeetingInterval = $meetingStartTime->getTimestamp() - $createddate->getTimestamp();
						$interval = ($interval - $beforMeetingInterval) < 0 ? 0 : ($interval - $beforMeetingInterval);
					}
					elseif ($closeddate < $meetingEndTime)
					{
						// Ex - close at 11.30
						$afterMeetingInterval = $closeddate->getTimestamp() - $meetingEndTime->getTimestamp();
						$interval = ($interval - $afterMeetingInterval) < 0 ? 0 : ($interval - $afterMeetingInterval);
					}

					// Date difference in minute
					$dateDiffResult = $interval;

					$user_event_data[$loginUserData['id']][$i]['event_id'] = $event_id;
					$user_event_data[$loginUserData['id']][$i]['spent_time'] = $dateDiffResult;
					$user_event_data[$loginUserData['id']][$i]['checkin'] = $meetingStartTime->format('Y-m-d H:i:s');
					$user_event_data[$loginUserData['id']][$i]['checkout'] = $meetingEndTime->format('Y-m-d H:i:s');
				}
			}
		}

		return $user_event_data;
	}

	/**
	 * Get user data
	 *
	 * @param   string  $email  user email id
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function getUserData ($email)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('`#__users` AS u');
		$query->where('u.email = ' . $db->quote($email));
		$db->setQuery($query);
		$userId = $db->loadAssoc();

		return $userId;
	}

	/**
	 * Get user data
	 *
	 * @param   string  $user_id  user id
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function getUserAPIData ($user_id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from('`#__techjoomlaAPI_users` AS t');
		$query->where('t.user_id = ' . $db->quote($user_id));
		$db->setQuery($query);

		return $userCredential = $db->loadAssoc();
	}

	/**
	 * Save user credential
	 *
	 * @param   string  $license  license
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function saveUserData ($license)
	{
		$db               = JFactory::getDBO();

		// JSON Encode
		$data             = array();
		$data['email']    = $license->email;
		$data['password'] = base64_encode($license->password);
		$userData         = json_encode($data);

		// Create and populate an object.
		$profile          = new stdClass;
		$profile->user_id = $license->user_id;
		$profile->token   = $userData;

		try
		{
			$userCredential = $this->getUserAPIData($license->user_id);

			if (!$userCredential['user_id'])
			{
				// Insert the object into the #__techjoomlaAPI_users table.
				$result = $db->insertObject('#__techjoomlaAPI_users', $profile);
			}
		}
		catch (Exception $e)
		{

		}

		return;
	}

	/**
	 * Get sco archieves like recordings and stored content against sco
	 *
	 * @param   string  $param        param
	 * @param   string  $meeting_sco  meeting sco
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function getScoArchieves($param, $meeting_sco)
	{
		$license = (object) $param->licence;
		$loginInfo = $this->autologin($license);
		$connection = $this->setconnection($license);
		$scoarchieves = '';

		try
		{
			$scoarchieves = $connection->getScoArchieves($meeting_sco);
		}
		catch (Exception $e)
		{

		}

		return $scoarchieves;
	}

	/**
	 * Get Attendance of event
	 *
	 * @param   Array  $data  Invite users to meeting
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function tj_inviteUsers($data)
	{
		/*
		 stdClass Object
		 ( [api_username] => deepa_g@techjoomla.com
			[api_password] => PLO1mgT
			[host_url] => https://meet56196787.adobeconnect.com/
			[source_sco_id] => 1173507692
			[event_type] => seminar
			[minimum_time_tomark_completion] => 2
			[sco_url] => [sco_id] => 1177743829
			[user_id] => 376
			[name] => amol
			[email] => amol_g@techjoomla.com
			[password] => oVdOtxio
			[meeting_url] => /aobeseminar3/ )
		 */
		$meeting_url        = $data->meeting_url;
		$meeting_url        = str_replace($data->host_url, "", $meeting_url);
		$sco_id             = $data->sco_id;
		$name               = explode(" ", $data->name);
		$data->first_name = $first_name = $name['0'];
		$cnt              = count($name);

		if ($cnt > 1)
		{
			$last_name = $name['1'];
		}

		if (empty($last_name))
		{
			$last_name = "-";
		}

		if ("/" == substr($meeting_url, -1))
		{
			$meeting_url = rtrim($meeting_url, "/");
		}

		$userexists = $this->getUser($data);
		$connection = $this->setconnection($data);

		try
		{
			if (!empty($userexists) and $userexists['status']['@attributes']['code'] == 'ok')
			{
				$email      = $userexists['principal-list']['principal']['email'];
				$scoarchieves = $connection->inviteUserToMeeting($sco_id, $email);
			}
			else
			{
				try
				{
					// If User not exists in Adobe connect then create users
					$user = $connection->createUser(
									$data->email,
									$data->password,
									$first_name, $last_name, $type = 'user',
									$mail = 1
									);

					if ($user['status']['@attributes']['code'] == 'ok')
					{
						// Save user credential
						$this->saveUserData($data);
					}
					elseif ($user['status']['invalid']['@attributes']['subcode'] = 'duplicate')
					{
						$data->password = 'xxxxx';

						// Save user credential
						$this->saveUserData($data);
					}

					$scoarchieves = $connection->inviteUserToMeeting($sco_id, $data->email);
				}
				catch (Exception $e)
				{
				}
			}
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Invite users to meeting based on sco id and email
	 *
	 * @param   string  $sco_id  meeting sco
	 * @param   string  $email   email of user
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function inviteUsers($sco_id, $email)
	{
		try
		{
			return $scoarchieves = $this->client_main->inviteUserToMeeting($sco_id, $data['email']);
		}
		catch (Exception $e)
		{

		}
	}

	/**
	 * Generate meeting html that is meeting link and
	 *
	 * @param   Array  $params        params
	 * @param   Array  $eventdetails  array of event data
	 * @param   Array  $dataFormat    return array if dataFormat = 'raw'
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function generateMeeting_HTML($params, $eventdetails = array(),$dataFormat = '')
	{
		$this->currentTime = JFactory::getDate()->toSql();
		$param = (object) $params;

		if ($param->online_provider != $this->_name)
		{
			return;
		}

		$vars        = new StdClass;
		$meeting_url = $vars->meeting_url = $param->online_provider_params['meeting_url'];
		$meeting_url = preg_replace('{/$}', '', $meeting_url);

		$vars->host_url = $param->host_url;
		$urlpath = '';

		if ("/" == substr($vars->host_url, -1))
		{
			$api_url = $vars->host_url . 'api/';
		}
		else
		{
			$api_url = $vars->host_url . '/api/';
		}

		// Auto connect to adobconnect when lesson launch
		try
		{
			$user           = JFactory::getUser();
			$userCredential = $this->getUserAPIData($user->id);
			$userData       = json_decode($userCredential['token'], true);

			// JSON Decode
			$lo_email       = $userData['email'];
			$lo_password    = base64_decode($userData['password']);

			if ($lo_password == 'xxxxx')
			{
				$vars->meeting_url    = $vars->meeting_url;
			}
			else
			{
				$loginInfo = $this->autologin($params['licence']);
				$connection = $this->setconnection($params['licence']);
				$this->getLicenceOwnerInfo  = $connection->getCommonInfo();
				require_once JPATH_SITE . '/plugins/tjevents/plug_tjevents_adobeconnect/plug_tjevents_adobeconnect/libraries/AdobeConnectClient.class.php';

				$this->user_login         = new AdobeConnectClient($lo_email, $lo_password, $api_url);

				// Get user login details
				$this->getloginInfo       = $this->user_login->getCommonInfo();

				if ($this->getloginInfo['status']['@attributes']['code'] == 'ok')
				{
					// Login cookie
					$this->meeting_ext_cookie = $this->getloginInfo['common']['cookie'];
					$licenseLogin = $this->getLicenceOwnerInfo['common']['user']['login'];

					if ($licenseLogin == $user->email)
					{
						$vars->meeting_url = $param->online_provider_params['meeting_url'];
					}
					elseif (!empty($this->meeting_ext_cookie))
					{
						// Set session to meeting url
						$vars->meeting_url    = $vars->meeting_url . "?session=" . $this->meeting_ext_cookie;
					}
				}
			}
		}
		catch (Exception $e)
		{
		}

		return $vars->meeting_url;
	}

	/**
	 * Generate meeting html that is meeting link and
	 *
	 * @param   Array  $params        params
	 * @param   Array  $eventdetails  array of event data
	 * @param   Array  $dataFormat    return array if dataFormat = 'raw'
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function getMeetingRecording($params, $eventdetails = array(),$dataFormat = '')
	{
		$this->currentTime = JFactory::getDate()->toSql();
		$param = (object) $params;

		if ($param->online_provider != $this->_name)
		{
			return;
		}

		try
		{
			$sco_id      = $param->sco_id;
			$scoarchieves = $this->getScoArchieves($param, $sco_id);
		}
		catch (Exception $e)
		{
		}

		$vars        = new StdClass;
		$vars->host_url = $param->host_url;
		$urlpath = '';

		if ("/" == substr($vars->host_url, -1))
		{
			$api_url = $vars->host_url . 'api/';
		}
		else
		{
			$api_url = $vars->host_url . '/api/';
		}

		// Added by Snehal to fetch recording URl
		$event_param = json_decode($eventdetails->jt_params);
		$event_url = $event_param->event_url;
		$meeting_sco_id      = $this->getscoID($param, $event_url);
		$meeting_scoarchieves = $this->getScoArchieves($param, $meeting_sco_id);
		$meeting_scos = $meeting_scoarchieves['scos'];

		// Auto connect to adobconnect when lesson launch
		try
		{
			// Added by komal
			if (!empty($eventdetails->id))
			{
				$vars->eventID = $eventdetails->id;
				$vars->startdate = $eventdetails->startdate;
				$vars->enddate = $eventdetails->enddate;
			}

			$user           = JFactory::getUser();
			$userCredential = $this->getUserAPIData($user->id);
			$userData       = json_decode($userCredential['token'], true);

			// JSON Decode
			$lo_email       = $userData['email'];
			$lo_password    = base64_decode($userData['password']);
			$vars->recording_url = '';

			if ($lo_password == 'xxxxx')
			{
				if (isset($meeting_scos['sco']))
				{
					$meeting_sco = $meeting_scos['sco'];
					$size = count($meeting_sco);

					if (empty($meeting_sco[0]))
					{
						$meeting_urlpath = $meeting_sco['url-path'];
						$vars->recording_url = $param->host_url . $meeting_urlpath;
					}
					else
					{
						for ($i = 0; $i < $size; $i++)
						{
							$meeting_urlpath = $meeting_sco[$i]['url-path'];
							$vars->recording_url[$i] = $param->host_url . $meeting_urlpath;
						}
					}
				}
			}
			else
			{
				$loginInfo = $this->autologin($params['licence']);
				$connection = $this->setconnection($params['licence']);
				$this->getLicenceOwnerInfo  = $connection->getCommonInfo();
				require_once JPATH_SITE . '/plugins/tjevents/plug_tjevents_adobeconnect/plug_tjevents_adobeconnect/libraries/AdobeConnectClient.class.php';

				$this->user_login         = new AdobeConnectClient($lo_email, $lo_password, $api_url);

				// Get user login details
				$this->getloginInfo       = $this->user_login->getCommonInfo();

				if ($this->getloginInfo['status']['@attributes']['code'] == 'ok')
				{
					// Login cookie
					$this->meeting_ext_cookie = $this->getloginInfo['common']['cookie'];
					$licenseLogin = $this->getLicenceOwnerInfo['common']['user']['login'];
					$vars->recording_url = '';

					if ($licenseLogin == $user->email)
					{
						if (isset($meeting_scos['sco']))
						{
							$meeting_sco = $meeting_scos['sco'];
							$size = count($meeting_sco);

							if (empty($meeting_sco[0]))
							{
								$meeting_urlpath = $meeting_sco['url-path'];
								$vars->recording_url = $param->host_url . $meeting_urlpath . "?session=" . $this->meeting_ext_cookie;
							}
							else
							{
								for ($i = 0; $i < $size; $i++)
								{
									$meeting_urlpath = $meeting_sco[$i]['url-path'];
									$vars->recording_url[$i] = $param->host_url . $meeting_urlpath . "?session=" . $this->meeting_ext_cookie;
								}
							}
						}
					}
					elseif (!empty($this->meeting_ext_cookie))
					{
						if (isset($meeting_scos['sco']))
						{
							$meeting_sco = $meeting_scos['sco'];
							$size = count($meeting_sco);

							if (empty($meeting_sco[0]))
							{
								$meeting_urlpath = $meeting_sco['url-path'];
								$vars->recording_url = $param->host_url . $meeting_urlpath . "?session=" . $this->meeting_ext_cookie;
							}
							else
							{
								for ($i = 0; $i < $size; $i++)
								{
									$meeting_urlpath = $meeting_sco[$i]['url-path'];
									$vars->recording_url[$i] = $param->host_url . $meeting_urlpath . "?session=" . $this->meeting_ext_cookie;
								}
							}
						}
					}
				}
			}
		}
		catch (Exception $e)
		{
		}

		return $vars->recording_url;
	}

	/**
	 * Generate meeting html that is meeting link and
	 *
	 * @param   string  $license     license
	 * @param   string  $first_name  first_name
	 * @param   string  $last_name   last_name
	 * @param   string  $type        type
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function createUser($license, $first_name, $last_name, $type = 'user')
	{
		$connection = $this->setconnection($license);

		try
		{
			$user = $connection->createUser($license->email, $license->password, $first_name, $last_name, $type = 'user', $mail = 1);
		}
		catch (Exception $e)
		{
			return 0;
		}
	}

	/**
	 * Generate meeting html that is meeting link and
	 *
	 * @param   string  $license  license
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function getUser($license)
	{
		$connection = $this->setconnection($license);

		try
		{
			$userCredential = $this->getUserAPIData($license->user_id);

			if ($userCredential)
			{
				// JSON Decode
				$userData       = json_decode($userCredential['token'], true);
				$email       = $userData['email'];
				$userbyemail = $connection->getUserByEmail($email);
			}
		}
		catch (Exception $e)
		{
			return 0;
		}

		return $userbyemail;
	}

	/**
	 * Send email to users
	 *
	 * @param   string  $data      data
	 * @param   string  $randpass  randpass
	 *
	 * @since   2.2
	 *
	 * @return   object
	 */
	public function sendMailNewUser($data, $randpass)
	{
		$app      = JFactory::getApplication();
		$mailfrom = $app->getCfg('mailfrom');
		$fromname = $app->getCfg('fromname');
		$sitename = $app->getCfg('sitename');
		$email    = $data['email'];
		$subject  = JText::_('JT_REGISTRATION_SUBJECT_ADOBE_SUBJECT');
		$message  = JText::_('JT_REGISTRATION_USER_ADOBE_MESSAGE');
		$find     = array(
			'{name}',
			'{email}',
			'{sitename}',
			'{adobe_meeting_url}',
			'{username}',
			'{password}'
		);
		$replace  = array(
			$data['name'],
			$data['email'],
			$sitename,
			$data['online_provider_params']['meeting_url'],
			$data['email'],
			$randpass
		);
		$message  = str_replace($find, $replace, $message);
		$subject  = str_replace($find, $replace, $subject);

		// Send mail to user that they are registered on adobe
		$sent = JFactory::getMailer()->sendMail($mailfrom, $fromname, $email, $subject, $message);
	}

	/**
	 * Used to update the tracking
	 *
	 * @param   INT    $userId     userId
	 * @param   Array  $eventdata  result of array
	 *
	 * @return  avoid
	 *
	 * @since 1.0.0
	 */
	public function updateLessonTrack($userId, $eventdata)
	{
		// #echo "<pre>generateMeeting_HTML -- eventdata : ";print_r($eventdata); echo "</pre>"; die('eventid');

		// Find lesson data
		$db           = JFactory::getDBO();
		$query        = $db->getQuery(true);
		$query->select('k.id AS media_id');
		$query->from('`#__tjlms_media` AS k');
		$query->where('k.source = ' . $db->quote($eventdata->id));
		$query->where('k.format = "event"');
		$db->setQuery($query);
		$mediaData = $db->loadAssocList();

		foreach ($mediaData as $rsMedia)
		{
			// ============ Get Lesson Data ===================;

			$query        = $db->getQuery(true);
			$query->select('l.id AS lesson_id, l.course_id');
			$query->from('`#__tjlms_lessons` AS l');
			$query->where('l.media_id = ' . $db->quote($rsMedia['media_id']));
			$query->where('l.format = "event"');
			$db->setQuery($query);
			$mediaData = $db->loadAssoc();

			$trackObj = new stdClass;
			$trackObj->attempt          = 1;
			$trackObj->score            = 0;
			$trackObj->total_content    = '';
			$trackObj->current_position = '';
			$trackObj->lesson_status    = 'started';
			$trackObj->current_position = 0;
			$trackObj->total_content    = 0;
			$trackObj->score            = 0;

			/*echo "Adobconnect updateLessonTrack User Id : ". $userId;
			echo "<br><pre>";
			print_r($trackObj);
			echo "</pre>";*/

			/* Update lesson status
			@remove it later
			require_once JPATH_SITE . '/components/com_tjlms/helpers/tracking.php';
			$comtjlmstrackingHelper = new comtjlmstrackingHelper;
			$trackingid = $comtjlmstrackingHelper->update_lesson_track($mediaData['lesson_id'], $userId, $trackObj);
			*/
		}
	}

	/**
	 * Used to get activeted  plugins of type tjevents
	 *
	 * @param   INT  $config  tevents pllugin
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public function GetContentInfo($config = array('adobeconnect'))
	{
			$obj = array();
			$obj['name']	= 'Adobeconnect';
			$obj['id']	= $this->_name;

			return $obj;
	}

	/**
	 * Used to get render html for meeting url
	 *
	 * @return  html
	 *
	 * @since 1.0.0
	 */
	public function renderplughtml()
	{
		if (isset($jsondata['online_provider_params']['meeting_url']))
		{
			$val = $jsondata['online_provider_params']['meeting_url'];
		}

		$html  = '<div class="control-group"><div class="control-label">';
		$html .= JText::_("COM_JTICKETING_MEETING_URL");
		$html .= '</div><div class="controls"><input type="test" name="jform[jt_params][online_provider_params][meeting_url]"
		value="' . $val . '"';
		$html .= '></div></div>';

		return $html;
	}

	/**
	 * Used to set the connection
	 *
	 * @param   array    $license      User details
	 * @param   string   $name         Event name
	 * @param   array    $userDetail   User detail array
	 * @param   date     $date_begin   Event start date
	 * @param   date     $date_end     Event end date
	 * @param   integer  $ticketCount  Ticket count
	 * @param   string   $password     password
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public function createAdobeconnectMeeting($license, $name, $userDetail, $date_begin, $date_end, $ticketCount, $password)
	{
		$loginInfo      = $this->autologin($license);
		$connection     = $this->setconnection($license);
		$userCredential = $this->getUserAPIData($userDetail->id);
		$eventName      = $name;

		if ($userCredential)
		{
			// JSON Decode
			$userData       = json_decode($userCredential['token'], true);
			$email       = $userData['email'];
			$userexists = $connection->getUserByEmail($email);
		}

		$sco_id = $license->source_sco_id;
		$meetingPermission = $license->meeting_permission;
		$role = 'host';

		if (!empty($loginInfo) and !empty($userexists) and $userexists['status']['@attributes']['code'] == 'ok' )
		{
			$result = $connection->createMeeting($sco_id, $name, $date_begin, $date_end, '');
			$role = 'host';

			if ($result['status']['@attributes']['code'] == 'ok')
			{
				$permission = $connection->permissionUpdateForMeeting($result['sco']['@attributes']['sco-id'], $meetingPermission);

				// Add host to the seminar room
				$rolePermission = $connection->permissionUpdate($email, $result['sco']['@attributes']['sco-id'], $role);

				if ($permission['status']['@attributes']['code'] == 'ok')
				{
					if ($rolePermission['status']['@attributes']['code'] == 'ok')
					{
						// Add host to the seminar
						// $this->Addhost($license, $result['sco']['@attributes']['sco-id']);
						$result['meeting_url'] = $result['sco']['url-path'];
						$result['sco_id']      = $result['sco']['@attributes']['sco-id'];
					}
					else
					{
						$this->deleteAdobeconnectMeeting($license, $result['sco']['@attributes']['sco-id'], $userDetail);
						$result['error_message'] = $this->reportError($rolePermission);

						return $result;
					}
				}
				else
				{
					$this->deleteAdobeconnectMeeting($license, $result['sco']['@attributes']['sco-id'], $userDetail);
					$result['error_message'] = $this->reportError($permission);

					return $result;
				}
			}
			else
			{
				$result['error_message'] = $this->reportError($result);

				return $result;
			}
		}
		else
		{
			$intialName = $userDetail->name;
			$name       = explode(" ", $intialName);
			$cnt        = count($name);

			$first_name = $name['0'];

			if ($cnt > 1)
			{
				$last_name = $name['1'];
			}

			if (empty($last_name))
			{
				$last_name = "-";
			}

			$userDetail->user_id    = $userDetail->id;
			$userDetail->password   = $password;

			// If User not exists in Adobe connect then create users
			$user = $connection->createUser(
									$userDetail->email,
									$password,
									$first_name, $last_name, $type = 'user',
									$mail = 1
									);

			if ($user['status']['@attributes']['code'] == 'ok')
			{
				// Save user credential
				$this->saveUserData($userDetail);
			}
			elseif ($user['status']['invalid']['@attributes']['subcode'] = 'duplicate')
			{
				$userDetail->password = 'xxxxx';

				// Save user credential
				$this->saveUserData($userDetail);
			}

			if ($user['status']['@attributes']['code'] == 'ok' or $user['status']['invalid']['@attributes']['subcode'] = 'duplicate')
			{
				$result = $connection->createMeeting($sco_id, $eventName, $date_begin, $date_end, '');

				if ($result['status']['@attributes']['code'] == 'ok')
				{
					$permission = $connection->permissionUpdateForMeeting($result['sco']['@attributes']['sco-id'], $meetingPermission);

					// Add host to the seminar room
					$rolePermission = $connection->permissionUpdate($userDetail->email, $result['sco']['@attributes']['sco-id'], $role);

					if ($permission['status']['@attributes']['code'] == 'ok')
					{
						if ($rolePermission['status']['@attributes']['code'] == 'ok')
						{
							// Add host to the seminar
							// $this->Addhost($license, $result['sco']['@attributes']['sco-id']);
							$result['meeting_url'] = $result['sco']['url-path'];
							$result['sco_id']      = $result['sco']['@attributes']['sco-id'];
						}
						else
						{
							$this->deleteAdobeconnectMeeting($license, $result['sco']['@attributes']['sco-id'], $userDetail);
							$result['error_message'] = $this->reportError($rolePermission);

							return $result;
						}
					}
					else
					{
						$this->deleteAdobeconnectMeeting($license, $result['sco']['@attributes']['sco-id'], $userDetail);
						$result['error_message'] = $this->reportError($permission);

						return $result;
					}
				}
				else
				{
					$result['error_message'] = $this->reportError($result);

					return $result;
				}
			}
			else
			{
				$result['error_message'] = $this->reportError($user);

				return $result;
			}
		}

		return $result;
	}

	/**
	 * Used to set the connection
	 *
	 * @param   array    $license       User details
	 * @param   string   $name          Event name
	 * @param   array    $event_sco_id  event_sco_id
	 * @param   date     $date_begin    Event start date
	 * @param   date     $date_end      Event end date
	 * @param   integer  $event_url     event_url
	 * @param   string   $userDetail    userDetail
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public function updateAdobeconnectMeeting($license, $name, $event_sco_id, $date_begin, $date_end, $event_url,$userDetail)
	{
		$loginInfo  = $this->autologin($license);
		$connection = $this->setconnection($license);
		$userCredential = $this->getUserAPIData($userDetail->id);

		if ($userCredential)
		{
			// JSON Decode
			$userData       = json_decode($userCredential['token'], true);
			$email       = $userData['email'];
			$userexists = $connection->getUserByEmail($email);
		}

		$meetingPermission = $license->meeting_permission;

		if (!empty($loginInfo) and $userexists['status']['@attributes']['code'] == 'ok' )
		{
			$result = $connection->updateMeeting($event_sco_id, $name, $date_begin, $date_end, $event_url);

			if ($result['status']['@attributes']['code'] == 'ok')
			{
				return true;
			}
			else
			{
				$result['error_message'] = $this->reportError($result);

				return $result;
			}
		}

		return $result;
	}

	/**
	 * Used to set the connection
	 *
	 * @param   array    $license      User details
	 * @param   string   $name         Event name
	 * @param   array    $params       params
	 * @param   date     $date_begin   Event start date
	 * @param   date     $date_end     Event end date
	 * @param   integer  $ticketCount  ticketCount
	 * @param   string   $userDetail   userDetail
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public function updateAdobeconnectSeminar($license, $name, $params, $date_begin, $date_end, $ticketCount, $userDetail)
	{
		$eventSourceScoId = $params->event_source_sco_id;
		$eventScoId = $params->event_sco_id;
		$loginInfo  = $this->autologin($license);
		$connection = $this->setconnection($license);
		$userCredential = $this->getUserAPIData($userDetail->id);

		if ($userCredential)
		{
			// JSON Decode
			$userData       = json_decode($userCredential['token'], true);
			$email       = $userData['email'];
			$userexists = $connection->getUserByEmail($email);
		}

		$meetingPermission = $license->meeting_permission;

		if (!empty($loginInfo) and $userexists['status']['@attributes']['code'] == 'ok' )
		{
			$result = $connection->updateSeminarSeesion($eventSourceScoId, $name, $date_begin, $date_end, $ticketCount, $eventScoId);

			if ($result['status']['@attributes']['code'] == 'ok')
			{
				return true;
			}
			else
			{
				$result['error_message'] = $this->reportError($result);

				return $result;
			}
		}

		return $result;
	}

	/**
	 * Used to set the connection
	 *
	 * @param   array   $license       license details
	 * @param   array   $event_sco_id  event_sco_id
	 * @param   string  $userDetail    userDetail
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public function deleteAdobeconnectMeeting($license, $event_sco_id, $userDetail)
	{
		$loginInfo = $this->autologin($license);
		$connection = $this->setconnection($license);
		$userCredential = $this->getUserAPIData($userDetail->id);

		if ($userCredential)
		{
			// JSON Decode
			$userData   = json_decode($userCredential['token'], true);
			$email      = $userData['email'];
			$userexists = $connection->getUserByEmail($email);
		}

		$meetingPermission = $license->meeting_permission;

		if (!empty($loginInfo) and $userexists['status']['@attributes']['code'] == 'ok' )
		{
			$result = $connection->deleteMeeting($event_sco_id);

			if ($result['status']['@attributes']['code'] == 'ok')
			{
				return true;
			}
			else
			{
				$result['error_message'] = $this->reportError($result);

				return $result;
			}
		}

		return $result;
	}

	/**
	 * Used to set the connection
	 *
	 * @param   array   $license      User details
	 * @param   string  $name         Event name
	 * @param   array   $userDetail   User details
	 * @param   date    $date_begin   Event start date
	 * @param   date    $date_end     Event end date
	 * @param   INT     $ticketCount  ticketCount
	 * @param   string  $password     Password
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public function createAdobeconnectSeminar($license, $name, $userDetail, $date_begin, $date_end, $ticketCount, $password)
	{
		$loginInfo      = $this->autologin($license);
		$connection     = $this->setconnection($license);
		$userCredential = $this->getUserAPIData($userDetail->id);
		$eventName      = $name;

		if ($userCredential)
		{
			// JSON Decode
			$userData   = json_decode($userCredential['token'], true);
			$email      = $userData['email'];
			$userexists = $connection->getUserByEmail($email);
		}

		$source_sco_id = $license->source_sco_id;
		$meetingPermission = $license->meeting_permission;

		if ($ticketCount == 'unlimited')
		{
			$ticketCount = 1000000000;
		}

		if (!empty($loginInfo) and !empty($userexists) and $userexists['status']['@attributes']['code'] == 'ok' )
		{
			// Step1 - create a meeting room
			$meetingRoom = $connection->createMeetingRoom($source_sco_id, $name, '');

			// Step2 - Check the user exist on adobe connect or not
			$role = 'host';

			if ($meetingRoom['status']['@attributes']['code'] == 'ok')
			{
				$permission = $connection->permissionUpdateForMeeting($meetingRoom['sco']['@attributes']['sco-id'], $meetingPermission);

				// Add host to the seminar room
				$rolePermission = $connection->permissionUpdate($email, $meetingRoom['sco']['@attributes']['sco-id'], $role);

				if ($permission['status']['@attributes']['code'] == 'ok')
				{
					if ($rolePermission['status']['@attributes']['code'] == 'ok')
					{
						$result = $connection->createSeminarSeesion($meetingRoom['sco']['@attributes']['sco-id'], $name, $date_begin, $date_end, $ticketCount);

						// Store meeting URL for event table.
						if ($result['status']['@attributes']['code'] == 'ok')
						{
							$result['meeting_url']   = $meetingRoom['sco']['url-path'];
							$result['source_sco_id'] = $meetingRoom['sco']['@attributes']['sco-id'];
							$result['sco_id']        = $result['sco']['@attributes']['sco-id'];
						}
						else
						{
							// Error return if event creation fail in the room @TODO - delete meeting room if event creation fail
							$result['error_message'] = $this->reportError($result);

							return $result;
						}
					}
					else
					{
						$this->deleteAdobeconnectMeeting($license, $meetingRoom['sco']['@attributes']['sco-id'], $userDetail);
						$result['error_message'] = $this->reportError($rolePermission);

						return $result;
					}
				}
				else
				{
					$this->deleteAdobeconnectMeeting($license, $meetingRoom['sco']['@attributes']['sco-id'], $userDetail);
					$result['error_message'] = $this->reportError($permission);

					return $result;
				}
			}
			else
			{
				$meetingRoom['error_message'] = $this->reportError($meetingRoom);

				return $meetingRoom;
			}
		}
		else
		{
			$intialName = $userDetail->name;
			$name       = explode(" ", $intialName);
			$cnt        = count($name);

			$first_name = $name['0'];

			if ($cnt > 1)
			{
				$last_name = $name['1'];
			}

			if (empty($last_name))
			{
				$last_name = "-";
			}

			$userDetail->user_id    = $userDetail->id;
			$userDetail->password   = $password;

			// If User not exists in Adobe connect then create users
			$user = $connection->createUser(
								$userDetail->email,
								$password,
								$first_name, $last_name, $type = 'user',
								$mail = 1
								);

			if ($user['status']['@attributes']['code'] == 'ok')
			{
				// Save user credential
				$this->saveUserData($userDetail);
			}
			elseif ($user['status']['invalid']['@attributes']['subcode'] = 'duplicate')
			{
				$userDetail->password = 'xxxxx';

				// Save user credential
				$this->saveUserData($userDetail);
			}

			if ($user['status']['@attributes']['code'] == 'ok' or $user['status']['invalid']['@attributes']['subcode'] = 'duplicate')
			{
				// Step1 - create a meeting room
				$meetingRoom = $connection->createMeetingRoom($source_sco_id, $eventName, '');
				/*
				Example Array
				{"status":
					{
						"@attributes": {"code":"ok"}},
						"sco":{
							"@attributes":
							{
								"account-id":"1173377743",
								 "disabled":"",
								 "display-seq":"0",
								 "folder-id":"1177769929",
								 "icon":"swf",
								 "lang":"en",
								 "max-retries":"",
								 "sco-id":"1177765454",
								 "source-sco-id":"",
								 "type":"seminarsession",
								 "version":"0"
							},
							"date-created":"2016-11-09T17:51:22.540+05:30",
							"date-modified":"2016-11-09T17:51:22.540+05:30",
							"name":"adobe seminar4","url-path":"\/p7p8jy2h0gb\/"},
							"meeting_url":"\/adobeseminar4\/"
				}
				*/

				// Step2 - Check the user exist on adobe connect or not
				$role = 'host';

				if ($meetingRoom['status']['@attributes']['code'] == 'ok')
				{
					$permission = $connection->permissionUpdateForMeeting($meetingRoom['sco']['@attributes']['sco-id'], $meetingPermission);

					// Add host to the seminar room
					$rolePermission = $connection->permissionUpdate($userDetail->email, $meetingRoom['sco']['@attributes']['sco-id'], $role);

					if ($permission['status']['@attributes']['code'] == 'ok')
					{
						if ($rolePermission['status']['@attributes']['code'] == 'ok')
						{
							$result = $connection->createSeminarSeesion($meetingRoom['sco']['@attributes']['sco-id'], $eventName, $date_begin, $date_end, $ticketCount);

							// Store meeting URL for event table.
							if ($result['status']['@attributes']['code'] == 'ok')
							{
								$result['meeting_url']   = $meetingRoom['sco']['url-path'];
								$result['source_sco_id'] = $meetingRoom['sco']['@attributes']['sco-id'];
								$result['sco_id']        = $result['sco']['@attributes']['sco-id'];
							}
							else
							{
								// Error return if event creation fail in the room @TODO - delete meeting room if event creation fail
								$result['error_message'] = $this->reportError($result);

								return $result;
							}
						}
						else
						{
							$this->deleteAdobeconnectMeeting($license, $meetingRoom['sco']['@attributes']['sco-id'], $userDetail);
							$result['error_message'] = $this->reportError($rolePermission);

							return $result;
						}
					}
					else
					{
						$this->deleteAdobeconnectMeeting($license, $meetingRoom['sco']['@attributes']['sco-id'], $userDetail);
						$result['error_message'] = $this->reportError($permission);

						return $result;
					}
				}
				else
				{
					$meetingRoom['error_message'] = $this->reportError($meetingRoom);

					return $meetingRoom;
				}
			}
			else
			{
				$result['error_message'] = $this->reportError($user);

				return $result;
			}
		}

		return $result;
	}

	/**
	 * Method to report error
	 *
	 * @param   array  $result  API result
	 *
	 * @return  string
	 *
	 * @since 1.0.0
	 */
	public function reportError($result)
	{
		$status_code = $result['status']['@attributes']['code'];
		$field = $result['status']['invalid']['@attributes']['field'];
		$field1 = $result['status']['no-access']['@attributes']['field'];

		switch ($status_code)
		{
			case 'invalid':
				switch ($result['status']['invalid']['@attributes']['subcode'])
				{
					case 'duplicate':
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_DUPLICATE'), $field);
					break;
					case 'format' :
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_WRONG_FORMAT'), $field);
					break;
					case 'illegal-operation' :
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_VIOLATES_INTEGRITY_RULE'), $field);
					break;
					case 'missing' :
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_PARAMETER_MISSING'), $field);
					break;
					case 'no-such-item' :
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_INFORMATION_NOT_EXIST'), $field);
					break;
					case 'range' :
						$err_msg = $msg . JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_OUTSIDE_RANGE'), $field);
					break;

					default :
						$err_msg = $msg . $result['0']['status']['invalid']['@attributes']['subcode']
							. JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_TO_ADMIN');
				}
			break;

			case 'no-access':

				switch ($result['status']['@attributes']['subcode'])
				{
					case 'account-expired':
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_EXPIRED'), $field1);
					break;
					case 'denied':
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_DONT_HAVE_PERMISSION'), $field1);
					break;
					case 'no-login' :
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_NOT_LOOGED_IN'), $field1);
					break;
					case 'no-quota' :
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_ACCOUNT_LIMIT_REACH'), $field1);
					break;
					case 'not-available' :
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_RESOURSE_UNAVILABLE'), $field1);
					break;
					case 'not-secure' :
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_SSL'), $field1);
					break;
					case 'pending-activation' :
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_PENDING_ACTIVATION'), $field1);
					break;
					case 'pending-license' :
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_PENDING_LICENSE'), $field1);
					break;
					case 'sco-expired' :
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_SCO_EXPIRED'), $field1);
					break;
					case 'sco-not-started' :
						$err_msg = JText::sprintf(JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_SCO_NOT_STARTED'), $field1);
					break;

					default :
						$err_msg = $result['0']['status']['@attributes']['subcode'] . JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_TO_ADMIN');
				}
				break;

			case 'no-data' :
						$err_msg = JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_NO_DATA');
					break;

			case 'too-much-data' :
						$err_msg = JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_TOO_MUCH_DATA');
					break;

			default :
				$err_msg = $status_code . JText::_('PLG_JTICKETING_VENUE_ADOBE_CONNECT_ERROR_MESSAGE_TO_ADMIN');
		}

		return $err_msg;
	}

	/**
	 * Used to get sco-id of a logined user
	 *
	 * @param   array  $license  User details
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public function getScoShortcut($license)
	{
		$loginInfo = $this->autologin($license);
		$connection = $this->setconnection($license);

		if (!empty($loginInfo))
		{
			$result = $connection->getScoShortcut();
		}

		return $result['shortcuts']['sco']['0']['@attributes']['sco-id'];
	}

	/**
	 * Used to set the connection
	 *
	 * @param   array  $license  User details
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public function setconnection($license)
	{
		$api_username = $license->api_username;
		$api_password = $license->api_password;
		$host_url     = $license->host_url;
		$host_url     = trim($host_url);

		if ("/" == substr($host_url, -1))
		{
			$api_url = $host_url . 'api/';
		}
		else
		{
			$api_url = $host_url . '/api/';
		}

		$this->errorlogfile = 'adobeconnect.log.php';
		$this->API_CONFIG   = array(
			'api_username' => trim($api_username),
			'api_password' => trim($api_password),
			'host_url' => trim($host_url),
			'api_url' => trim($api_url)
		);

		$this->client_main = '';

		try
		{
			require_once JPATH_SITE . '/plugins/tjevents/plug_tjevents_adobeconnect/plug_tjevents_adobeconnect/libraries/AdobeConnectClient.class.php';
			$this->client_main = new AdobeConnectClient($api_username, $api_password, $api_url);

			return $this->client_main;
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Used to login on the site
	 *
	 * @param   array  $license  User details
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public function autologin($license)
	{
		$host_url = $license->host_url;
		$host_url     = trim($host_url);

		if ("/" == substr($host_url, -1))
		{
			$api_url = $host_url . 'api/';
		}
		else
		{
			$api_url = $host_url . '/api/';
		}

		require_once JPATH_SITE . '/plugins/tjevents/plug_tjevents_adobeconnect/plug_tjevents_adobeconnect/libraries/AdobeConnectClient.class.php';

		$this->user_login = new AdobeConnectClient($license->api_username, $license->api_password, $api_url);

		// Get user login details
		$this->getloginInfo = $this->user_login->getCommonInfo($this->user_login);

		return $this->user_login;
	}

	/**
	 * Used to get existig meetings
	 *
	 * @param   array  $license  User details
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	public function getAllMeetings($license)
	{
		$loginInfo = $this->autologin($license);
		$connection = $this->setconnection($license);

		if (!empty($loginInfo))
		{
			$result = $connection->findMeetings();
		}

		return $result;
	}
}
