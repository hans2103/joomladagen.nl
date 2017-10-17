<?php
/*
 * AdobeConnect 8 api client
 * @see https://github.com/sc0rp10/AdobeConnect-php-api-client
 * @see http://help.adobe.com/en_US/connect/8.0/webservices/index.html
 * @version 0.1a
 *
 * Copyright 2012, sc0rp10
 * https://weblab.pro
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 *
 */

class AdobeConnectClient {
	/**
	 * @const
	 * your personally root-folder id
	 * @see http://forums.adobe.com/message/2620180#2620180
	 */
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

	/**
	 *
	 */
	public function __construct ($username, $password,$base_domain='') {
		$this->cookie = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cookie_'.time().'.txt';
		$this->base_domain = $base_domain;
		$this->username = $username;
		$this->password = $password;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_REFERER, $this->base_domain);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$this->curl = $ch;
		$this->makeAuth();
	}

	/**
	 * make auth-request with stored username and password
	 *
	 * @return AdobeConnectClient
	 */
	public function makeAuth() {
		$this->makeRequest('login',
			array(
				'login'    => $this->username,
				'password' => $this->password
			)
		);
		$this->is_authorized = true;
		return $this;
	}

	/**
	 * get common info about current user
	 *
	 * @return array
	 */
	public function getCommonInfo() {
		return $this->makeRequest('common-info');
	}


	/**
	 * get common info about current user
	 *
	 * @return array
	 */
	public function getMeetingAttendance($sco_id,$session_id) {
		return $this->makeRequest('report-meeting-attendance',array(
				'sco-id'    => $sco_id,
				'session' => $session_id
			));
	}

	/**
	 * create user
	 *
	 * @param string $email
	 * @param string $password
	 * @param string $first_name
	 * @param string $last_name
	 * @param string $type
	 *
	 * @return array
	 */
	public function createUser($email, $password, $first_name, $last_name, $type = 'user',$send_mail=1) {
		$result = $this->makeRequest('principal-update',
			array(
				'first-name'   => $first_name,
				'last-name'    => $last_name,
				'email'        => $email,
				'password'     => $password,
				'type'         => $type,
				'has-children' => 0,
				'send-email'=>$send_mail
			)
		);
		return $result;
	}


	/**
	 * @param string $email
	 * @param bool   $only_id
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 */
	public function getUserByEmail($email, $only_id = false) {
		$result = $this->makeRequest('principal-list',
			array(
				'filter-email' => $email
			)
		);
		if (empty($result['principal-list'])) {
			throw new Exception('Cannot find user');
		}
		if ($only_id) {
			return $result['principal-list']['principal']['@attributes']['principal-id'];
		}
		return $result;
	}

	/**
	 * update user fields
	 *
	 * @param string $email
	 * @param array  $data
	 *
	 * @return mixed
	 */
	public function updateUser($email, array $data = array()) {
		$principal_id = $this->getUserByEmail($email, true);
		$data['principal-id'] = $principal_id;
		return $this->makeRequest('principal-update', $data);
	}

	/**
	 * get all users list
	 *
	 * @return array
	 */
	public function getUsersList() {
		$users = $this->makeRequest('principal-list');
		$result = array();
		foreach($users['principal-list']['principal'] as $key => $value) {
			$result[$key] = $value['@attributes'] + $value;
		};
		unset($result[$key]['@attributes']);
		return $result;
	}

	/**
	 * get all meetings
	 *
	 * @return array
	 */public function getAllMeetings() {
		return $this->makeRequest('report-my-meetings');
	}

	/**
	 * create meeting-folder
	 *
	 * @param string $name
	 * @param string $url
	 *
	 * @return array
	 */
	public function createFolder($name, $url) {
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
	 * @param int    $sco_id
	 * @param string $login            login of adobe connect
	 * @param string $password         Password of adobe connect
	 * @param string $password_verify  Verify Password of adobe connect
	 * @param string $first_name		First name for user
	 * @param string $last_name			Last name for user
	 *
	 * @return array
	 */
	public function registerusertoEvent($sco_id, $login, $password, $password_verify, $first_name, $last_name,$campaign_id='')
	{
		$result = $this->makeRequest('event-register',
			array(
				'sco-id'=> $sco_id,
				'login'=> $login,
				'password'=> $password,
				'password-verify'=> $password_verify,
				'first-name'=> $first_name,
				'last-name'=> $last_name,
				'campaign-id'=>$campaign_id

			)
		);

		return $result;
	}

	/**
	 * Get sco info by passing meeting url
	 *
	 * @param int    $sco_id
	 * @param string $login            login of adobe connect
	 * @param string $password         Password of adobe connect
	 * @param string $password_verify  Verify Password of adobe connect
	 * @param string $first_name		First name for user
	 * @param string $last_name			Last name for user
	 *
	 * @return array
	 */
	public function getScoInfobymeetingurl($url_id)
	{
		$result = $this->makeRequest('sco-by-url',
			array(
				'url-path'=> $url_id,
			)
		);

		return $result;
	}

	/**
	 * Register attendee to event
	 *
	 * @param int    $sco_id
	 * @param string $login            login of adobe connect
	 * @param string $password         Password of adobe connect
	 * @param string $password_verify  Verify Password of adobe connect
	 * @param string $first_name		First name for user
	 * @param string $last_name			Last name for user
	 *
	 * @return array
	 */
	public function getScoInfo($sco_id)
	{
		$result = $this->makeRequest('sco-info',
			array(
				'sco-id'=> $sco_id,
			)
		);

		return $result;
	}

	/**
	 * create meeting
	 *
	 * @param int    $folder_id
	 * @param string $name
	 * @param string $date_begin
	 * @param string $date_end
	 * @param string $url
	 *
	 * @return array
	 */
	public function createMeeting($folder_id, $name, $date_begin, $date_end, $url) {
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
		return $result['sco']['@attributes']['sco-id'];
	}

	/**
	 * invite user to meeting
	 *
	 * @param int    $meeting_id
	 * @param string $email
	 *
	 * @return mixed
	 */
	public function inviteUserToMeeting($meeting_id, $email) {
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
	 * @param int    $meeting_id
	 * @param string $email
	 *
	 * @return mixed
	 */
	public function getScoArchieves($sco_id) {

		$result = $this->makeRequest('sco-contents',
			array(
				'sco-id'  => $sco_id,
				'filter-icon'  => 'archive',

			)
		);
		return $result;
	}


	public function __destruct() {
		@curl_close($this->curl);
	}

	/**
	 * @param       $action
	 * @param array $params
	 * @return mixed
	 * @throws Exception
	 */
	 public function getMeetingurl($hosturl, $urlpath_forsession) {
		return $hosturl.$urlpath_forsession;
	}

	/**
	 * @param       $action
	 * @param array $params
	 * @return mixed
	 * @throws Exception
	 */
	 public function getQuota() {

		$result = $this->makeRequest('report-quotas');
		return $result;
	}


	/**
	 * @param       $action
	 * @param array $params
	 * @return mixed
	 * @throws Exception
	 */
	 private function makeRequest($action, array $params = array()) {
		$url = $this->base_domain;
		$url .= 'xml?action='.$action;
		$url .= '&'.http_build_query($params);
		curl_setopt($this->curl, CURLOPT_URL, $url);
		$result = curl_exec($this->curl);
		$xml = simplexml_load_string($result);
		$json = json_encode($xml);
		$data = json_decode($json, TRUE);
		if (!isset($data['status']['@attributes']['code']) || $data['status']['@attributes']['code'] !== 'ok') {

			throw new Exception('Coulnd\'t perform the action: '.$action);
		}

		return $data;
	}
}
