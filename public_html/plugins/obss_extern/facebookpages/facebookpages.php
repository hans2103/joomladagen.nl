<?php
/**
 * @version		$Id: facebookpage.php 1185 2011-08-29 07:01:45Z phonglq $
 * @author		Phong Lo - foobla.com
 * @package		obSocialSubmit for Joomla
 * @subpackage	externTwitter addon
 * @license		GNU/GPL
 */

defined("_JEXEC") or die("Cannot direct access!");
jimport('joomla.html.parameter');
require_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_obsocialsubmit'.DS.'helpers'.DS.'functions.php';
require_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_obsocialsubmit'.DS.'helpers'.DS.'class.externaddon.php';
if( !class_exists('OBSSExAddonFacebookPages') ) {
	class OBSSExAddonFacebookPages extends OBSSExAddon {

		public $functions 	= array('connect', 'callback', 'update_pages');
		
		public function __construct($data = null) {
			$lang 	= JFactory::getLanguage();
			$addon 	= 'plg_obss_extern_facebookpages';
			$lang->load($addon, JPATH_ADMINISTRATOR, null, false, false)
				|| $lang->load( $addon, JPATH_SITE . DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'obss_extern'.DIRECTORY_SEPARATOR.$addon, null, false, false)
				|| $lang->load( $addon, JPATH_SITE , $lang->getDefault(), false, false)
				|| $lang->load( $addon, JPATH_SITE .DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'obss_extern'.DIRECTORY_SEPARATOR. $addon, $lang->getDefault(), false, false);
			parent::__construct($data);
		}

		public function postMessage($input) {
			if(! $input ) return;
			if( !isset($input->message) || !$input->message ) {
				return;
			}
			$mainframe = JFactory::getApplication();
			/* Post message to facebook */
			$config 	= $this->getConfig();
			$appid 		= $config->get('app_appid');
			$base64_access_token = $config->get('base64_access_token_manager_pages');
			
			$post_type 	= $config->get('post_type','default');
			if(!$base64_access_token ){
//				$log = 'Appid is null';
//				$this->addLog($log);
				return;
			}

			#----------------------------------------------------------------
			# Create post data will be post to Facebook via graph api */
			#----------------------------------------------------------------
			/**
			 *	$data = array(
			 *		'message'=>$input->message,
			 *		'link'=>'http://www.example.com/article.html',
			 *		'picture'=>'http://test.foobla.com/phonglo/components/com_virtuemart/shop_image/product/7a36a05526e93964a086f2ddf17fc609.jpg',
			 *		'name'=>'Article Title',
			 *		'caption'=>'Caption for the link',
			 *		'description'=>'Longer description of the link, Longer description of the link, Longer description of the link'
			 *	);
			 *	$url	= 'https://graph.facebook.com/'.$pages[0]->id.'/feed';
			 **/

			$data = array();

			if( isset( $input->message ) && $input->message ) {
				$data['message'] = strip_tags($input->message);
			}
			if( isset( $input->url ) && $input->url ) {
				$data['link'] = str_replace('&amp;','&',$input->url);
			}

			#----------------------------------------
			#TODO : Post data to Facebook Pages
			#----------------------------------------
			#TODO: get Facebook Pages
			$debug = $config->get( 'debug', 0 );
			$pages = $config->get( 'pages' );
			$config_data = array();

			if( !empty($pages) ) {
				$config_data = $config->toArray();
			}

			if(!$base64_access_token) {
				$log = 'Access token empty';
				$this->addLog($log);
				return;
			}

			if( !$input->title && !$input->url && !$input->shorturl && !$input->template ) {
				$post_type = 'msg_only';
			}

			$url_suffix = '';
			switch ($post_type){
				case 'upload_photo':
					# Cach 2: moi hon
					if($input->img){
						$data['url']=$input->img;
						$url_suffix	= '/photos';
						break;
					}

				case 'upload_photo2':
					# Cach 1: van hoatdong tot
					$source = str_replace( JURI::root(), JPATH_SITE.DIRECTORY_SEPARATOR, $input->img );
					$source = str_replace( '/', DIRECTORY_SEPARATOR, $source );
					if( $input->img && trim($source) ){
						if(JFile::exists( $source )){
							$data['source']		= '@'.$source;
							$data['message'] 	= $data['message']."\n".$data['description'];
							$url_suffix 	= '/photos';
							break;
						}
					}

				case 'default':
					$url_suffix = '/feed';
					if( isset( $input->title ) && $input->title ) {
						$data['name'] = $input->title;
					}

					$data['caption'] = parse_url($data['link'], PHP_URL_HOST);

					if( isset( $input->img ) && $input->img ) {
						$data['picture'] = $input->img;
					}
					if (!isset($data['picture']) || !$data['picture']) {
						$default_img	= $config->get( 'default_img','' );
						$parse_url		= parse_url($default_img);
						if( !key_exists('scheme',$parse_url)&& !key_exists('host',$parse_url) ){
							$default_img = JURI::root().trim($default_img,"/ ");
						}
						if($default_img){
							$data['picture']= $default_img;
						}
					}

					if( isset( $input->description ) && $input->description ) {
						$desc = strip_tags( $input->description );
						$strlen = mb_strlen($desc, mb_detect_encoding($desc));
						$desc_len = $config->get('desc_len');
						$desc_len = ($desc_len)?$desc_len:1000;
						if( $strlen > $desc_len ){
							$desc = mb_substr( $desc, 0, $desc_len, mb_detect_encoding($desc));
						}
						$data['description'] = $desc;
					}

					if( isset( $input->video_url ) && $input->video_url ) {
						$data['source'] = $input->video_url;
					}

					if( isset( $input->type ) && $input->type ) {
						$data['type'] = $input->type;
					}

					break;

				case 'share_link':
					$url_suffix 	= '/links';
					break;

				case 'msg_only':
					unset($data['link']);
					$url_suffix = '/feed';
					break;
			}

			if($pages){
				$pages = is_array($pages)?$pages: array($pages);
				if( count( $pages ) ) {
					$result = array();
					foreach( $pages as $page ) {
						$page_details = $config_data['page_'.$page];
						$page_json = base64_decode($page_details);
						$page_data = json_decode($page_json, true);
						if(!isset($page_data['access_token'])) continue;
						$data['access_token'] = $page_data['access_token'];
						$url	= 'https://graph.facebook.com/'.$page_data['id'].$url_suffix;
						$result[$page_data['id']] = $this->postData($url, $data);
						$res = json_decode($result[$page_data['id']]);
						$log = print_r($data, true).print_r($res, true);
						$this->addLog($log);
						if(is_object($res) && property_exists($res, 'error')){
							$msg = $res->error->message;
							$mainframe->enqueueMessage($msg,'error');
							if ( $debug ) {
								echo '<pre>'.print_r( $data, true ).'</pre>';
								echo '<pre>'.print_r( $result, true ).'</pre>';
								echo '<pre>' . print_r( $res, true ) . '</pre>';
							}
							continue;
						}
					}
					
					if ( $debug ) {
						echo '<pre>'.print_r( $data, true ).'</pre>';
						echo '<pre>'.print_r( $result, true ).'</pre>';
						exit();
					}
					return true;
				}
			}
			#END: Post data to Facebook Pages

			return false;
		}

		private function postData( $url, $data ) {
			$ch 	= curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
#			curl_setopt($ch, CURLOPT_HEADER, 0);
#			curl_setopt($ch, CURLOPT_NOBODY, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
#			curl_setopt($ch, CURLOPT_TIMEOUT, 100);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Facebook-Pages-1.8-addon-for-obSocialSubmit-by-foobla');
			curl_setopt($ch, CURLOPT_POST, true);

			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
			
			$res = curl_exec( $ch );
			
			if (curl_errno($ch) == 60) { // CURLE_SSL_CACERT
				curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__).DS.'helpers'.DS.'fb_ca_chain_bundle.crt');
				$res = curl_exec($ch);
			}
			
			if(curl_errno($ch))
			{
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				$res = curl_exec($ch);
			}
			
			if (curl_errno($ch)){
				$fields_string = '';
				foreach($data as $key=>$value) {
					$fields_string .= $key.'='.$value.'&';
				}
				rtrim($fields_string,'&');
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields_string);
				$res = curl_exec($ch);
			}
			
			if(curl_errno($ch))
			{
				$mainframe = JFactory::getApplication();
				$mainframe->enqueueMessage('CURL ERROR:'.curl_error($ch),'error');
			}
			curl_close($ch);
			return $res;
		}
		
		public function getFacebookPages( $access_token_manager_pages ) {
			$config 	= $this->getConfig();
			$limit_pages = $config->get('pages_limit', 25);
			$url 	= 'https://graph.facebook.com/me/accounts?access_token=' . $access_token_manager_pages . '&limit=' . $limit_pages;
			$ch 	= curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_NOBODY, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Facebook-Pages-1.6.5-addon-for-obSocialSubmit-by-foobla');
			curl_setopt($ch, CURLOPT_TIMEOUT, 100);
			$json_fb_pages = curl_exec($ch);
			if (curl_errno($ch) == 60) {
				// CURLE_SSL_CACERT
				curl_setopt($ch, CURLOPT_CAINFO, dirname(dirname(__FILE__)).DS.'fb_ca_chain_bundle.crt');
				$json_fb_pages = curl_exec($ch);
			}
			if(curl_errno($ch))
			{
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				$json_fb_pages = curl_exec($ch);
			}
			if(curl_errno($ch))
			{
				$mainframe = JFactory::getApplication();
				$mainframe->enqueueMessage( 'CURL ERROR: ' . curl_error( $ch ), 'error' );
			}
			curl_close($ch);
			return $json_fb_pages;
		}
		
		public function callback(){
			$access_token = '';
			$base64_access_token = '';
			$request = JRequest::get('request');
			$configs 		= $this->getConfig();
			if( !isset( $request['error'] ) && !isset( $request['error_reason'] ) && isset($request['access_token']) ) {
				$access_token = isset( $request['access_token'] ) ? $request['access_token']:'';
				$client_id 		= $configs->get('app_appid');
				$client_secret 	= $configs->get('app_appsecret');
				$fb_exchange_token 	= $access_token;
				$expires 			= JRequest::getVar('expires');
				$res_arr 		= array();
				if( $client_id && $client_secret && (!isset($_GET['expires']) || !isset($_GET['obapp'])) ) {
					#Extending Page Access Tokens
					$data = array (
							'grant_type'=>'fb_exchange_token',
							'client_id'=>$client_id,
							'client_secret'=>$client_secret,
							'fb_exchange_token'=>$fb_exchange_token
						);
					$exchange_token_url = 'https://graph.facebook.com/oauth/access_token';
					$res_str = $this->postData($exchange_token_url, $data);
					parse_str( $res_str, $res_arr );
				} else {
					$res_arr['access_token'] 	= $access_token;
					$res_arr['expires'] 		= $expires;
				}
				# echo '$access_token:'.$access_token;
				$expires 				= isset( $res_arr['expires'] ) ? $res_arr['expires'] : '';
				$access_token 			= $res_arr['access_token'];
				$json_fb_pages 			= $this->getFacebookPages($access_token);
				$base64_access_token 	= base64_encode( $access_token );
				$base64_fb_pages 		= base64_encode( $json_fb_pages );
				$debug = $configs->get('debug');
				if($debug){
					echo '<pre>'.print_r($_REQUEST, true).'</pre>';
					echo '<pre>'.print_r($access_token, true).'</pre>';
					echo '<pre>'.print_r($json_fb_pages, true).'</pre>';
				}
			}
			?><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" dir="ltr">
			<head>
				<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
			</head>
			<body>
<?php 		if( isset( $request['error'] ) || isset( $request['error_reason'] ) ) {
				echo '<pre>'.print_r($request['error']).'</pre>'; 
				echo '<pre>'.print_r($request['error_reason']).'</pre>';
			} else if( $base64_access_token ) { 
				$aid = JRequest::getVar('aid');
			?>
				<form method="post" name="obss_facebook_pages" action="index.php?obsstask=addonfunc&addonfunc=update_pages&aid=<?php echo $aid; ?>&atype=ex">
					<h1><?php echo JText::_('PLG_OBSS_EXTERN_FACEBOOKPAGES_SELECT_FACEBOOK_PAGES'); ?></h1>
					<div id="obss_facebook_pages" class="obss_facebook_pages">
					<?php 
//					$next_link 		= $array_fb_pages['paging']['next'];
					$array_fb_pages = json_decode($json_fb_pages, true);
					$pages 			= $array_fb_pages['data'];
					
					foreach( $pages as $page ) {
						$link 			= 'http://www.facebook.com/pages/'.$page['name'].'/'.$page['id'];
						$disabled 		= ($page['category']=='Application')?' disabled="disabled" ' : '' ;
						if($disabled) continue;
						$value_json 	= json_encode( $page );
						$value_base64 	= base64_encode( $value_json );
						echo '<div class="facebook_page">';
						echo '<input type="checkbox" name="pages[]" id="page_'.$page['id'].'" value="'.$page['id'].'"'.$disabled.'><label for="page_'.$page['id'].'">'.$page['name'].'</label>';
						echo '<input type="hidden" name="page_'.$page['id'].'" value="'.$value_base64.'" />';
						echo (!$disabled)?'&nbsp;<a href="'.$link.'" target="blank">view</a>':'';
						echo '<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small>'.$page['category'].'</small>';
						echo '</div>';
					}
					?>
					</div>
					<input type="hidden" name="expires_in" value="<?php echo $expires; ?>"/>
					<input type="hidden" name="base64_access_token_manager_pages" value="<?php echo $base64_access_token; ?>"/>
					<input type="submit" name="submit" value="<?php echo JText::_('PLG_OBSS_EXTERN_FACEBOOKPAGES_SUBMIT')?>"/>
				</form>
<?php 			} else {
					
					?>
					<h1><?php echo JText::_("Error on connect with Facebook");?></h1>
					<?php 
					$debug = $configs->get('debug');
					if($debug){
						echo '<pre>'.$configs.'</pre>';
					}
					?>
<?php 			} ?>
			</body>
			</html>
			<?php
			exit();
		}
		
		public function update_pages(){
			$app 		= JFactory::getApplication();
			$aid 		= JRequest::getVar('aid');
			$config 	= $this->getConfig();
			$debug		= $config->get('debug');
			$param_value 	= $config->toArray();
			
			# - Remove old pages data
//			$base64_access_token_manager_pages = JRequest::getVar( 'base64_access_token_manager_pages' );
			$pages_old = isset($param_value['pages'])?$param_value['pages']:array();
			$pages_old = is_array( $pages_old ) ? $pages_old :array($pages_old);
			if(count($pages_old)){
				foreach($pages_old as $page_old){
					unset($param_value['page_'.$page_old]);
				}
			}
			

			# Add new data
			$param_value['base64_access_token_manager_pages'] = JRequest::getVar('base64_access_token_manager_pages');
			$pages 	= JRequest::getVar('pages');
			$pages 	= is_array($pages)?$pages:array($pages);
			if( count($pages) ){
				$param_value['pages'] = $pages;
				foreach( $pages as $page ){
					$param_value['page_'.$page] = JRequest::getVar( 'page_'.$page );
				}
			}

			$registry = new JRegistry();
			$registry->loadArray($param_value);
			$storedata = $registry->toString();
			$storedata = addslashes($storedata);

			$aid = $this->data->id;
			$sql = "UPDATE 	`#__obsocialsubmit_instances`
					SET `params` = '{$storedata}' WHERE `id` = ".$aid;
			$db = JFactory::getDbo();
			$db->setQuery($sql);
			$db->query();
			$redirect_url = 'index.php?option=com_obsocialsubmit&view=connection&layout=edit&id='.$aid;
			if($db->getErrorNum()){
				if($debug){
					print_r($db->getErrorMsg());
					echo '<pre>'.print_r($db, true).'</pre>';
					exit('<br/>'.__LINE__);
				}
				$msg=JText::_("PLG_OBSS_EXTERN_FACEBOOKPAGES_ERROR_ON_UPDATE_CONNECTION");
				$app->redirect( $redirect_url, $msg, 'error' );
			}else{
				$msg = JText::_("PLG_OBSS_EXTERN_FACEBOOKPAGES_UPDATE_CONNECTION_SUCCESS");
				$app->redirect( $redirect_url, $msg, 'message' );
			}
		}


		public function addLog($content, $time=''){
			if( method_exists('OBSSAddon', 'addLog') ){
				parent::addLog($content, $time);
				return;
			}
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
}
#end if class_exists