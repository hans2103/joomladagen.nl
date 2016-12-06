<?php
/**
 * @package        obSocialSubmit
 * @author         foobla.com.
 * @copyright      Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license        GNU/GPL
 */

// ensure a valid entry point
defined( '_JEXEC' ) or die( 'Restricted Access' );
defined( '_JEXEC' ) or die( 'Cannot direct access' );

class ShortUrls {
	public static function getParams() {
		$params = JComponentHelper::getParams( 'com_obsocialsubmit' );

		return $params;
	}

	/**
	 * provide: bitly, isgd, yourls
	 */
	public static function shortUrl( $longurl ) {
		$param   = self::getParams();
		$provide = $param->get( 'shorturl', '' );
		if ( ! $provide || $provide == 'none' || ! method_exists( "ShortUrls", $provide ) ) {
			return $longurl;
		}

		return self::$provide( $longurl );
	}

	public static function runCommand( $urlcommand ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $urlcommand );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_NOBODY, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 100 );
		$json_str = curl_exec( $ch );
		if ( curl_errno( $ch ) ) {
			$mainframe = JFactory::getApplication();
			$mainframe->enqueueMessage( 'SHORTURL ERROR:' . curl_error( $ch ), 'error' );
		}
		curl_close( $ch );
		if ( ! $json_str ) {
			$json_str = file_get_contents( $urlcommand );
		}

		return $json_str;
	}

	public static function bitly( $longurl ) {
		$params     = self::getParams();
		$login      = $params->get( 'bitly_login', '' );
		$api_key    = $params->get( 'bitly_api_key', '' );
		$urlcommand = 'http://api.bit.ly/v3/shorten?format=json&login=' . $login . '&apiKey=' . $api_key . '&longUrl=' . urlencode( $longurl );
		$json_str   = self::runCommand( $urlcommand );
		#End: Short url
		$res = json_decode( $json_str );
		if ( isset( $res->data->url ) ) {
			return $res->data->url;
		} else {
			$error_msg = "bitly error:" . print_r( $res, true );
			$mainframe = JFactory::getApplication();
			$mainframe->enqueueMessage( 'SHORTURL ERROR:' . $error_msg, 'error' );

			return $longurl;
		}

		return $longurl;
	}

	public static function yourls( $longurl ) {
		$params        = self::getParams();
		$api_url       = trim( $params->get( 'yourls_api_url', '' ) );
		$api_signature = trim( $params->get( 'yourls_api_signature', '' ) );
		$username      = trim( $params->get( 'yourls_username', '' ) );
		$password      = trim( $params->get( 'yourls_password', '' ) );

		if ( $api_signature != '' ) {
			$urlcommand = $api_url . '?signature=' . $api_signature . '&action=shorturl&format=json&url=' . urlencode( $longurl );
			$array_post = array(     // Data to POST
				'url' => $longurl,
				'format'   => 'json',
				'action'   => 'shorturl',
				'signature'=> $api_signature
				//'username' => $username,
				//'password' => $password
			);
		} else {
			$urlcommand = $api_url . '?username=' . $username . '&password=' . $password . '&action=shorturl&format=json&url=' . urlencode( $longurl );
			$array_post = array(     // Data to POST
				'url' => $longurl,
				'format'   => 'json',
				'action'   => 'shorturl',
				//'signature'=> $api_signature
				'username' => $username,
				'password' => $password
			);
		}

		$json_str = self::runCommand( $urlcommand );
		#End: Short url
		$res = json_decode( $json_str );
		if( $res->shorturl == '' || !isset($res->shorturl) ){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $api_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);            // No header in the result
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return, do not echo result
			curl_setopt($ch, CURLOPT_POST, 1);              // This is a POST request
			curl_setopt($ch, CURLOPT_POSTFIELDS, $array_post);

			// Fetch and return content
			$data = curl_exec($ch);
			curl_close($ch);
			$res = json_decode($data );//echo'<pre>';print_r($res->shorturl);die;
		}
		if( $res->shorturl == '' || !isset($res->shorturl) ){
			$res->shorturl = $longurl;
		}

		return $res->shorturl;
	}

	public static function goo( $longurl ) {
		$params     = self::getParams();
		$apiKey      = $params->get( 'googl_api_key', '' );

		if( !$apiKey ) {
			$apiKey = 'AIzaSyCDL89h01mwCZuAhFhcEGLaxuavYROTRQ4';
		}

		$postData = array( 'longUrl' => $longurl, 'key' => $apiKey );
		$jsonData = json_encode( $postData );

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url' );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-type:application/json' ) );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonData );

		$response = curl_exec( $ch );
		$error    = curl_error( $ch );
		// Change the response json string to object
		$json = json_decode( $response );
		curl_close( $ch );
		if ( ! isset( $json->id ) ) {
			$mainframe = JFactory::getApplication();
			$mainframe->enqueueMessage( 'SHORTURL ERROR:' . $error, 'error' );

			return $longurl;
		}

		return $json->id;
	}

	public static function shurl( $longurl ) {
		$app       = JFactory::getApplication();
		$db        = JFactory::getDbo();
		$juri_root = JURI::root();
		$oldurl    = str_replace( $juri_root, '', $longurl );
		$oldurl    = addslashes( $oldurl );

		$sql = "SELECT `newurl` FROM `#__sh404sef_urls` WHERE `oldurl`='{$oldurl}'";
		$db->setQuery( $sql );
		$newurl = $db->loadResult();
		if ( $db->getErrorNum() ) {
			$app->enqueueMessage( "obSocialSubmit SHURL error:" . $db->getErrorMsg(), 'error' );

			return $longurl;
		}
		if ( ! $newurl ) {
			return $longurl;
		}
		$sql = "SELECT `pageid` FROM `#__sh404sef_pageids` WHERE `newurl`='{$newurl}'";
		$db->setQuery( $sql );
		$pageid = $db->loadResult();
		if ( ! $pageid ) {
			file_get_contents( $longurl );
			$sql = "SELECT `pageid` FROM `#__sh404sef_pageids` WHERE `newurl`='{$newurl}'";
			$db->setQuery( $sql );
			$pageid = $db->loadResult();
		}
		if ( $db->getErrorNum() ) {
			$app->enqueueMessage( "obSocialSubmit SHURL error:" . $db->getErrorMsg(), 'error' );

			return $longurl;
		}
		if ( $pageid ) {
			return JUri::root() . $pageid;
		} else {
			return $longurl;
		}
	}
}
