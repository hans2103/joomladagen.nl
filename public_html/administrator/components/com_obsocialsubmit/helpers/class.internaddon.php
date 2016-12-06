<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');
require_once dirname(__FILE__).DS.'class.addon.php';
jimport('joomla.html.parameter');

class OBSSInAddon extends OBSSAddon {
	public $config 	= null;
	public $data 	= null;
	public $revent 	= array('onAfterDispatch');
	public static $_item_title = array();
	
	function __construct($data=null, $revent=null) {
		if($revent){
			if( is_object( $revent ) ) {
				exit("NICE TRY");
			}
			if( !is_array( $revent ) ) {
				$revent = array($revent);
			}
		}
		$this->data 	= $data;
		$this->revent 	= $revent;
		

//		Load the core and/or local language file(s).
		$lang 	= JFactory::getLanguage();
		$classname = strtolower(get_class($this));
		$addon 	= str_replace( 'obssinaddon', '', $classname );
		$lang->load('plg_obss_intern_'.$addon, JPATH_ADMINISTRATOR, null, false, false)
			|| $lang->load( 'plg_obss_intern_'.$addon, JPATH_SITE . DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'obss_intern'.DIRECTORY_SEPARATOR.$addon, null, false, false)
			|| $lang->load( 'plg_obss_intern_'.$addon, JPATH_SITE, $lang->getDefault(), false, false)
			|| $lang->load( 'plg_obss_intern_'.$addon, JPATH_SITE .DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'obss_intern'.DIRECTORY_SEPARATOR. $addon, $lang->getDefault(), false, false);
	}
	
	
	
	function onCronJob() {
		return null;
	}
	
	/**
	 * Get scr of first img in string
	 * @param string $text
	 */
	public function getFirstImage($text=''){
		preg_match( '/<img\s.*?\/>/', $text, $matches_img );
		$img 		= '';
		if( isset( $matches_img[0] ) ) {
			$imgtag = $matches_img[0];
			preg_match( '/(src)=("([^"]*)")/', $imgtag, $result);
			$img     = $result[3];
		};
		if(!$img) return null;
		$parse_url = parse_url($img);
		if( !key_exists('scheme',$parse_url)&& !key_exists('host',$parse_url) ){
			$img = JURI::root().trim($img,"/ ");
		}
		return $img;
	}
	
	
	/**
	 * Get IP address
	 */
	public function getRealIp(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			// check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { 
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
}