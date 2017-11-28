<?php
/**
 * @version    SVN: 0.1a
 * @package    Techjoomla_API
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    MIT license http://www.opensource.org/licenses/MIT
 * AdobeConnect 8 api client
 * @see        https://github.com/sc0rp10/AdobeConnect-php-api-client
 * @see        http://help.adobe.com/en_US/connect/8.0/webservices/index.html
 */

jimport('joomla.log.log');

/**
 * Class AdobeConnectClient
 *
 * @since  2017
 */
class AdobeConnectClient
{
	/**
	 * adobe connect username
	 * @see const USERNAME = 'ashwin.date@tekdi.net';
	 * @see define('USERNAME', $username);
	 */

	/**
	 * @const
	 * adobe connect password
	 * @see const PASSWORD = '789D8C796F';
	 */

	/**
	 * @const
	 * your personally api URL
	 * @see const BASE_DOMAIN = 'https://meet28721552.adobeconnect.com/api/';
	 */

	/**
	 * @const
	 * your personally root-folder id
	 * @see http://forums.adobe.com/message/2620180#2620180
	 */

	// Root folder id
	const ROOT_FOLDER_ID = 0;

	/**
	 * @var string filepath to cookie-jar file
	 */
	private $cookie;

	/**
	 * @var resource
	 */
	private $curl;

	/**
	 * @var bool
	 */
	private $is_authorized = false;

	protected $breezecookie = null;

	protected $username;

	protected $password;

	protected $root_folder;

	protected $template_folder;

	protected $host;

	/**
	 * Constructor
	 *
	 * @param   string  $username         username
	 * @param   string  $password         password
	 * @param   string  $base_domain      base_domain
	 * @param   string  $root_folder      root_folder
	 * @param   string  $template_folder  template_folder
	 */
	public function __construct ($username, $password, $base_domain='',$root_folder = null, $template_folder = null)
	{
		$temp = str_replace('/', '', $base_domain);

		$this->base_domain = $base_domain;
		$ch = curl_init();
		$this->base_domain = $base_domain;
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_REFERER, $this->base_domain);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_COOKIEJAR, sys_get_temp_dir() . DIRECTORY_SEPARATOR . $temp . '.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, sys_get_temp_dir() . DIRECTORY_SEPARATOR . $temp . '.txt');

		$this->curl = $ch;
		$this->username = $username;
		$this->password = $password;
		$this->root_folder = $root_folder;
		$this->template_folder = $template_folder;
		$this->makeAuth();
	}

	/**
	 * make auth-request with stored username and password
	 *
	 * @param   string  $login     login
	 * @param   string  $password  password
	 *
	 * @return AdobeConnectClient
	 */
	public function makeAuth($login = null, $password = null)
	{
		$this->username = $login ?: $this->username;
		$this->password = $password ?: $this->password;

		$result = null;

		if (!$this->breezecookie)
		{
			try
			{
				$this->makeRequest('login',
					array(
						'login'    => $this->username,
						'password' => $this->password
					)
				);
			}
			catch (Exception $e)
			{
				$e = new Exception(sprintf('Cannot auth with credentials: [%s:%s@%s]', $this->username, $this->password, $this->host), 0, $e);
				$e->setHost($this->host);
				$e->setLogin($this->username);
				$e->setPassword($this->password);

				throw $e;
			}
		}
		else
		{
			$this->getCommonInfo();
		}

		$this->is_authorized = true;

		return $this;
	}

	/**
	 * get common info about current user
	 *
	 * @return array
	 */
	public function getCommonInfo()
	{
		return $this->makeRequest('common-info');
	}

	/**
	 * get common info about current user
	 *
	 * @return array
	 */
	/*public function getMeetingAttendance($sco_id,$session_id) {
		return $this->makeRequest('report-meeting-attendance',array(
				'sco-id'    => $sco_id,
				'session' => $session_id
			));
	}*/

	/**
	 * get common info about current user
	 *
	 * @param   string  $sco_id  sco_id
	 *
	 * @return array
	 */
	public function getMeetingAttendance($sco_id)
	{
		return $this->makeRequest('report-meeting-attendance',
			array(
				'sco-id'    => $sco_id
				)
			);
	}

	/**
	 * create user
	 *
	 * @param   string  $email       email
	 * @param   string  $password    password
	 * @param   string  $first_name  first_name
	 * @param   string  $last_name   last_name
	 * @param   string  $type        type
	 *
	 * @return array
	 */
	public function createUser($email, $password, $first_name, $last_name, $type = 'user')
	{
		$result = $this->makeRequest('principal-update',
			array(
				'first-name'   => $first_name,
				'last-name'    => $last_name,
				'email'        => $email,
				'password'     => $password,
				'type'         => $type,
				'has-children' => 0
			)
		);

		return $result;
	}

	/**
	 * Get user by email
	 *
	 * @param   string  $email    email
	 * @param   bool    $only_id  only_id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 */
	public function getUserByEmail($email, $only_id = false)
	{
		$result = $this->makeRequest('principal-list',
			array(
				'filter-email' => $email
			)
		);

		if (empty($result['principal-list']))
		{
			return false;
		}

		if ($only_id)
		{
			return $result['principal-list']['principal']['@attributes']['principal-id'];
		}

		return $result;
	}

	/**
	 * update user fields
	 *
	 * @param   string  $email  email
	 * @param   array   $data   data
	 *
	 * @return mixed
	 */
	public function updateUser($email, array $data = array())
	{
		$principal_id = $this->getUserByEmail($email, true);
		$data['principal-id'] = $principal_id;

		return $this->makeRequest('principal-update', $data);
	}

	/**
	 * get all users list
	 *
	 * @return array
	 */
	public function getUsersList()
	{
		$users = $this->makeRequest('principal-list');
		$result = array();

		foreach ($users['principal-list']['principal'] as $key => $value)
		{
			$result[$key] = $value['@attributes'] + $value;
		};
		unset($result[$key]['@attributes']);

		return $result;
	}

	/**
	 * get all meetings
	 *
	 * @return array
	 */
	public function getAllMeetings()
	{
		return $this->makeRequest('report-my-meetings');
	}

	/**
	 * create meeting-folder
	 *
	 * @param   string  $name  name
	 * @param   string  $url   url
	 *
	 * @return array
	 */
	public function createFolder($name, $url)
	{
		$result = $this->makeRequest('sco-update',
			array(
				'type'       => 'folder',
				'name'       => $name,
				'folder-id'  => self::FOLDER_ID,
				'depth'      => 1,
				'url-path'   => $url
			)
		);

		return $result['sco']['@attributes']['sco-id'];
	}

	/**
	 * Register attendee to event
	 *
	 * @param   int     $sco_id           sco_id
	 * @param   string  $login            login of adobe connect
	 * @param   string  $password         Password of adobe connect
	 * @param   string  $password_verify  Verify Password of adobe connect
	 * @param   string  $first_name       First name for user
	 * @param   string  $last_name        Last name for user
	 * @param   string  $campaign_id      campaign_id
	 *
	 * @return array
	 */
	public function registerusertoEvent($sco_id, $login, $password, $password_verify, $first_name, $last_name,$campaign_id='')
	{
		$result = $this->makeRequest('event-register',
			array(
				'sco-id' => $sco_id,
				'login' => $login,
				'password' => $password,
				'password-verify' => $password_verify,
				'first-name' => $first_name,
				'last-name' => $last_name,
				'campaign-id' => $campaign_id

			)
		);

		return $result;
	}

	/**
	 * Get sco info by passing meeting url
	 *
	 * @param   int  $url_id  url_id
	 *
	 * @return array
	 */
	public function getScoInfobymeetingurl($url_id)
	{
		$result = $this->makeRequest('sco-by-url',
			array(
				'url-path' => $url_id,
			)
		);

		return $result;
	}

	/**
	 * Register attendee to event
	 *
	 * @param   int  $sco_id  sco_id
	 *
	 * @return array
	 */
	public function getScoInfo($sco_id)
	{
		$result = $this->makeRequest('sco-info',
			array(
				'sco-id' => $sco_id,
			)
		);

		return $result;
	}

	/**
	 * create meeting
	 *
	 * @param   int     $folder_id   folder_id
	 * @param   string  $name        name
	 * @param   string  $date_begin  date_begin
	 * @param   string  $date_end    date_end
	 * @param   string  $url         url
	 *
	 * @return array
	 */
	public function createMeeting($folder_id, $name, $date_begin, $date_end, $url)
	{
		$result = $this->makeRequest('sco-update',
			array(
				'type'       => 'meeting',
				'name'       => $name,
				'folder-id'  => $folder_id,
				'date-begin' => $date_begin,
				'date-end'   => $date_end,
				'url-path'   => $url
			)
		);

		return $result;
	}

	/**
	 * delete meeting
	 *
	 * @param   int  $sco_id  sco_id
	 *
	 * @return array
	 */
	public function deleteMeeting($sco_id)
	{
		$result = $this->makeRequest('sco-delete',
			array(
				'sco-id'     => $sco_id
			)
		);

		return $result;
	}

	/**
	 * update meeting
	 *
	 * @param   int     $sco_id      sco_id
	 * @param   string  $name        name
	 * @param   string  $date_begin  date_begin
	 * @param   string  $date_end    date_end
	 * @param   string  $url         url
	 *
	 * @return array
	 */
	public function updateMeeting($sco_id, $name, $date_begin, $date_end, $url)
	{
		$result = $this->makeRequest('sco-update',
			array(
				'type'       => 'meeting',
				'name'       => $name,
				'sco-id'     => $sco_id,
				'date-begin' => $date_begin,
				'date-end'   => $date_end,
				'url-path'   => $url
			)
		);

		return $result;
	}

	/**
	 * create Seminar session
	 *
	 * @param   int     $folder_id    folder_id
	 * @param   string  $name         name
	 * @param   string  $date_begin   date_begin
	 * @param   string  $date_end     date_end
	 * @param   string  $ticketCount  ticketCount
	 *
	 * @return array
	 */
	public function createSeminarSeesion($folder_id, $name, $date_begin, $date_end, $ticketCount)
	{
		$response = new stdclass;
		$result = $this->makeRequest('sco-update',
			array(
				'type'       => 'seminarsession',
				'name'       => $name,
				'folder-id'  => $folder_id,
			)
		);

		if (!empty($result['sco']))
		{
			$session = $this->makeRequest('seminar-session-sco-update',
				array(
					'sco-id'       => $result['sco']['@attributes']['sco-id'],
					'source-sco-id'       => $folder_id,
					'parent-acl-id'  => $folder_id,
					'date-begin'  => $date_begin,
					'date-end'  => $date_end,
				)
			);

			$acl_update = $this->makeRequest('acl-field-update',
				array(
					'acl-id'       => $result['sco']['@attributes']['sco-id'],
					'field-id'       => '311',
					'value'  => $ticketCount,
					'date-begin'  => $date_begin,
					'date-end'  => $date_end
				)
			);
		}

		return $result;
	}

	/**
	 * create Seminar session
	 *
	 * @param   int     $folder_id    folder_id
	 * @param   string  $name         name
	 * @param   string  $date_begin   date_begin
	 * @param   string  $date_end     date_end
	 * @param   string  $ticketCount  ticketCount
	 * @param   string  $sco_id       sco_id
	 *
	 * @return array
	 */
	public function updateSeminarSeesion($folder_id, $name, $date_begin, $date_end, $ticketCount, $sco_id)
	{
		$response = new stdclass;
		$result = $this->makeRequest('sco-update',
			array(
				'type'       => 'meeting',
				'name'       => $name,
				'sco-id'     => $folder_id,
			)
		);

		if ($result['status']['@attributes']['code'] == 'ok')
		{
			$session = $this->makeRequest('seminar-session-sco-update',
				array(
					'sco-id'       => $sco_id,
					'source-sco-id'       => $folder_id,
					'parent-acl-id'  => $folder_id,
					'date-begin'  => $date_begin,
					'date-end'  => $date_end,
				)
			);

			$acl_update = $this->makeRequest('acl-field-update',
				array(
					'acl-id'       => $sco_id,
					'field-id'       => '311',
					'value'  => $ticketCount,
					'date-begin'  => $date_begin,
					'date-end'  => $date_end
				)
			);
		}

		return $result;
	}

	/**
	 * create meeting rrom
	 *
	 * @param   int     $folder_id  folder_id
	 * @param   string  $name       name
	 * @param   string  $url        url
	 *
	 * @return array
	 */
	public function createMeetingRoom($folder_id, $name, $url)
	{
		$result = $this->makeRequest('sco-update',
			array(
				'type'       => 'meeting',
				'name'       => $name,
				'folder-id'  => $folder_id,
				'url-path'   => $url
			)
		);

		return $result;
	}

	/**
	 * invite user to meeting
	 *
	 * @param   int     $meeting_id  meeting_id
	 * @param   string  $email       email
	 *
	 * @return mixed
	 */
	public function inviteUserToMeeting($meeting_id, $email)
	{
		$user_id = $this->getUserByEmail($email, true);

		$result = $this->makeRequest('permissions-update',
			array(
				'principal-id'  => $user_id,
				'acl-id'        => $meeting_id,
				'permission-id' => 'view'
			)
		);

		return $result;
	}

	/**
	 * invite user to meeting
	 *
	 * @param   int     $email   userDetail
	 * @param   string  $sco_id  sco_id
	 * @param   string  $role    role
	 *
	 * @return mixed
	 */
	public function permissionUpdate($email , $sco_id, $role)
	{
		$user_id = $this->getUserByEmail($email, true);
		$result = $this->makeRequest('permissions-update',
			array(
				'principal-id'  => $user_id,
				'acl-id'        => $sco_id,
				'permission-id' => $role
			)
		);

		return $result;
	}

	/**
	 * invite user to meeting
	 *
	 * @param   string  $sco_id  sco_id
	 *
	 * @return mixed
	 */
	public function getScoArchieves($sco_id)
	{
		$result = $this->makeRequest('sco-contents',
			array(
				'sco-id'  => $sco_id,
				'filter-icon'  => 'archive',

			)
		);

		return $result;
	}

	/**
	 * Find meeting
	 *
	 * @return $result
	 */
	public function findMeetings()
	{
		$result = $this->makeRequest('report-bulk-objects');

		return $result;
	}

	/**
	 * Destructor
	 *
	 */
	public function __destruct()
	{
		@curl_close($this->curl);
	}

	/**
	 * Get meeting url
	 *
	 * @param   string  $hosturl             action
	 * @param   array   $urlpath_forsession  parameters
	 *
	 * @return $data
	 *
	 * @throws Exception
	 */
	public function getMeetingurl($hosturl, $urlpath_forsession)
	{
		return $hosturl . $urlpath_forsession;
	}

	/**
	 * Get quota
	 *
	 * @return null
	 */
	public function getQuota()
	{
		$result = $this->makeRequest('report-quotas');

		return $result;
	}

	/**
	 * Get Breeze cookie
	 *
	 * @return null
	 */
	public function getBreezeCookie ()
	{
		return $this->breezecookie;
	}

	/**
	 * Make request
	 *
	 * @param   string  $action  action
	 * @param   array   $params  parameters
	 *
	 * @return $data
	 *
	 * @throws Exception
	 */
	private function makeRequest($action, array $params = array())
	{
		$url = $this->base_domain;
		$url .= 'xml?action=' . $action;
		$url .= '&' . http_build_query($params);

		if ($this->breezecookie)
		{
			$params['session'] = $this->breezecookie;
			$url .= '&session=' . $params['session'];
		};

		curl_setopt($this->curl, CURLOPT_URL, $url);

		$response = curl_exec($this->curl);

		if ($action != 'report-bulk-objects')
		{
			preg_match('/BREEZESESSION=(\w+);/', $response, $m);

			if (isset($m[1]))
			{
				$this->breezecookie = $m[1];
			}

			$temp = explode("\r\n\r\n", $response);

			$result = '';

			if (isset($temp[1]))
			{
				$response = $result = $temp[1];
			}
		}

		libxml_use_internal_errors();
		$xml = simplexml_load_string($response);
		$errors = libxml_get_errors();

		$json = json_encode($xml);
		$response = $json;
		$options = "{DATE}\t{TIME}\t{ACTION}\t{RESPONSE}";

		JLog::addLogger(
				array(
					'text_file' => 'adobeConnect.log',
					'text_entry_format' => $options,
				),
				JLog::ALL, 'com_jticketing'
			);

		$logEntry            = new JLogEntry(
									"Adobe Connect Events", JLog::INFO, 'com_jticketing');
		$logEntry->action      = $action;
		$logEntry->response    = $response;
		JLog::add($logEntry);

		$data = json_decode($json, true);

		return $data;
	}

	/**
	 * Get all information of current logged in user on adobe
	 *
	 * @since   2.2
	 *
	 * @return   string  result result
	 */
	public function getScoShortcut()
	{
		$result = $this->makeRequest('sco-shortcuts');

		return $result;
	}

	/**
	 * Set meeting permissions
	 *
	 * @param   int  $sco_id             sco id
	 * @param   int  $meetingPermission  permission
	 *
	 * @return mixed
	 */
	public function permissionUpdateForMeeting($sco_id, $meetingPermission)
	{
		$result = $this->makeRequest('permissions-update',
			array(
				'acl-id'        => $sco_id,
				'principal-id' => 'public-access',
				'permission-id' => $meetingPermission
			)
		);

		return $result;
	}
}
