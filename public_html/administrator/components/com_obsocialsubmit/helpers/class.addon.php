<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.parameter');

class OBSSAddon {
	public $config 	= null; 					#store object parameter
	public $data 	= null; 
	public $isJ15 	= null; 
	public $isJ25 	= null; 
	function __construct($data=null) {
		$this->config = null;
		$this->data = $data;
	}
	
	public function getConfig(){
		if( !$this->config ) {
			$param_value = isset($this->data->params)?$this->data->params:'';
			$this->config = new JRegistry($param_value);
		}
		return $this->config;
	}


	public function isJ15(){
		if(is_null($this->isJ15)){
			$jver = new JVersion();
			$this->isJ15 = ($jver->RELEASE == '1.5');
		}
		return $this->isJ15;
	}


	public function isJ25(){
		if(is_null($this->isJ25)){
			$jver = new JVersion();
			$this->isJ25 = ($jver->RELEASE == '2.5');
		}
		return $this->isJ25;
	}


	public function route($unroute_url){
		$app = JFactory::getApplication();
		$urlcommand 	= JURI::root().'index.php?obsstask=getsef&url='.base64_encode($unroute_url);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $urlcommand);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_NOBODY, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100);
		$url_router = curl_exec($ch);
		if(curl_errno($ch))
		{
			$app->enqueueMessage('OBSS: Error on get sef url:.'.curl_error($ch),'error');
		}
		curl_close($ch);
		if(!$url_router){
			$url_router = file_get_contents($urlcommand);
		}
		return $url_router;
	}


	public function shortUrl($longurl){
		require_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_obsocialsubmit'.DS.'helpers'.DS.'shorturls.php';
		$shorturl = ShortUrls::shortUrl( $longurl );
		return $shorturl;
	}


	public function addLog($content, $time=''){
		$config = JFactory::getConfig();
		if(!$content) {
			return;
		}
		$id			= $this->data->id;
		$datetime	= '';
		if( !$time ) {
			$date = JFactory::getDate();
			if(method_exists($date, 'toSql')){
				$time = $date->format('Ymd');
				$datetime = $date->toSql();
			} elseif( method_exists($date, 'toMySql') ) {
				$time = $date->format('Ymd');
				$datetime = $date->toMysql();
			}
		}

		$file_name	= 'LOG_'.$id.'_'.$time.'.log';
		$log_path	= $config->get('log_path');

		$addon		= $this->data->addon;
		$addon_type = $this->data->addon_type;
		$file_path	= JPath::clean($log_path.DS.'obss'.DS.$addon_type.DS.$addon.DS.$file_name);

		$content = "\n--------------------\n".$datetime.":\n--------------------\n".print_r($content,true)."\n";
		if(JFile::exists($file_path)){
			$oldcontent = file_get_contents($file_path);
			$content .= $oldcontent;
		}
		JFile::write($file_path, $content);
	}

}