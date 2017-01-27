<?php
/**
 * @version		$Id: facebook.php 1185 2011-08-29 07:01:45Z phonglq $
 * @author		Phong Lo - foobla.com
 * @package		obSocialSubmit for Joomla
 * @subpackage	externTwitter addon
 * @license		GNU/GPL
 */

defined("_JEXEC") or die("Cannot direct access!");

require_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_obsocialsubmit'.DS.'helpers'.DS.'functions.php';
require_once JPATH_ADMINISTRATOR.DS.'components'.DS.'com_obsocialsubmit'.DS.'helpers'.DS.'class.externaddon.php';
if( !class_exists( 'OBSSExAddonFacebook' ) ){
	class OBSSExAddonFacebook  extends OBSSExAddon{

		public function __construct($data=null) {
			jimport('joomla.html.parameter');
			$this->config = new JRegistry($data->params);
			$lang  = JFactory::getLanguage();
			$lang->load( 'plg_obss_extern_facebook', JPATH_ADMINISTRATOR, null, false, false )
			|| $lang->load( 'plg_obss_extern_facebook', JPATH_SITE . '/plugins/obss_extern/' . 'facebook', null, false, false );
			parent::__construct($data);
		}

		public function postMessage($input){
			if(! $input ) return;
			if( !isset($input->message) || !$input->message ) {
				return;
			}

			#------------------------------------------------------------
			# Get configuration information
			#------------------------------------------------------------
			$config 		= $this->getConfig();
			$appid 			= $config->get('app_appid','142204595809631');
			$access_token 		= $config->get('access_token');
			$update_user_status = $config->get('update_user_status');
			$uid 			= $config->get('uid');
			$debug 			= $config->get('debug');
			$post_type 		= $config->get('post_type');
			
			if (!$access_token || !$uid ) {
				return false;
			}
			# END: Get configuration information

			#----------------------------------------------------------------
			#TODO: Create post data will be post to Facebook via graph api */
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
			
			if( isset( $input->url ) && $input->url && !$post_type != 'msg_only' ) {
				$data['link'] = str_replace('&amp;','&',$input->url);
			}

			if( $post_type == 'default' ) {
				
				if( isset( $input->title ) && $input->title ) {
					$data['name'] = $input->title;
				}
				
				if( isset( $input->img ) && $input->img ) {
					$data['picture'] = $input->img;
				}
			
				if (!isset($data['picture']) || $data['picture']) {
					$default_img = $config->get( 'default_img','' );
					if($default_img){
						$data['picture']= $default_img;
					}
				}
				
				if( isset( $input->description ) && $input->description ) {
					$desc = strip_tags( $input->description );
					if (!function_exists('utf8_strlen') || !function_exists('utf8_strpos')) {
							jimport('phputf8.native.core');
					}
					if (!function_exists('utf8_is_valid')) {
						jimport('phputf8.utils.validation');
					}
					$title =  $input->title;
					$desc_len = $config->get('desc_len',0);
					if( utf8_compliant($desc) && strlen($desc)> $desc_len && $desc_len > 0  ) {
						$i = 1;
						$tdesc = $desc;
						do {
							$desc = utf8_substr($tdesc, 0, $i);
							$i++;
						} while ( strlen($desc) < $desc_len );
					} elseif( $desc_len > 0 ) {
						$desc 		= substr( $desc, 0, $desc_len );					
					}
					$data['description'] = $desc;
				}
				
				if( isset( $input->video_url ) && $input->video_url ) {
					$data['source'] = $input->video_url;
				}
				if( isset( $input->type ) && $input->type ) {
					$data['type'] = $input->type;
				}
			}

			#END: Create post data

			#----------------------------------------
			#TODO : Post data to user profile page
			#----------------------------------------
			$url_suffix = '';
			switch ($post_type){
				case 'share_link':
					$url_suffix = '/links';
					break;
				case 'msg_only':
					unset($data['link']);
					$url_suffix = '/feed';
					break;
				default:
					$url_suffix = '/feed';
					break;
			}

/*			$post_as = $config->get('post_as','user');

			if($post_as == 'app') {
				$app_secret = $config->get( 'app_secret' );
				$url = "https://graph.facebook.com/oauth/access_token?client_id=".$appid."&client_secret=".$app_secret."&grant_type=client_credentials";
				$ch 	= curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_NOBODY, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$res = trim(curl_exec( $ch ));
				$res = explode('=',$res);
				$access_token = $res[1];
			}
*/
			if( $access_token ) {
				$api_feed = 'https://graph.facebook.com/'.$uid.$url_suffix;
				$data[ 'access_token' ] = $access_token;
				$res 		= $this->postData( $api_feed, $data );
				if( $debug ){
					echo '<div>'.$api_feed.'</div>';
					echo '<hr/>';
					echo '<pre>'.print_r($data, true).'</pre>';
					echo '<hr/><h2>Result of post to Facebook</h2>';
					echo '<pre>' . print_r( $res, true ) . '</pre>';
					echo '<hr/>';
					exit();
				}
				if( $res_obj = json_decode( $res )){
					if( isset($res_obj->id) ) {
						return true;
					}
				};
				return false;
			}
			#END: Post data to user profile page
			return false;
		}

		private function postData( $url, $data ) {
			$ch 	= curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
#			curl_setopt($ch, CURLOPT_HEADER, 0);
#			curl_setopt($ch, CURLOPT_NOBODY, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
#			curl_setopt($ch, CURLOPT_TIMEOUT, 100);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Facebook-1.8-addon-for-obSocialSubmit-by-foobla');
			curl_setopt($ch, CURLOPT_POST, true);

			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

			$res = curl_exec( $ch );
			if ( curl_errno( $ch ) == 60 ) { // CURLE_SSL_CACERT
				curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__).DS.'elements'.DS.'fb_ca_chain_bundle.crt');
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
				$mainframe = &JFactory::getApplication();
				$mainframe->enqueueMessage('CURL ERROR:'.curl_error($ch),'error');
			}
			curl_close($ch);
			return $res;
		}

		public function callback() {
			$app 		= JFactory::getApplication();
			$userid 	= JRequest::getVar('userid');
			$access_token 	= JRequest::getVar('access_token');
			$aid 		= JRequest::getVar('aid');
			$configs 	= $this->getConfig();

			$app_id 	= $configs->get('app_appid');
			$app_secret = $configs->get('app_secret');
			$data 		= array();
			$expires 	= JRequest::getVar('expires');

			if( !isset($_GET['expires']) || !isset($_GET['obapp']) ) {
				$url = 'https://graph.facebook.com/oauth/access_token';
				$data = array('grant_type' => 'fb_exchange_token',
						'client_id' => $app_id,
						'client_secret' => $app_secret,
						'fb_exchange_token' => $access_token);
				$res = $this->postData( $url, $data );
				parse_str($res, $data);
			} else {
				$data['access_token'] = $access_token;
				$data['expires'] = JRequest::getVar('expires');
			}

			$aid = $this->data->id;
			$url_redirect 	= JURI::base() ."index.php?option=com_obsocialsubmit&task=connection.edit&id=".$aid;
			if( isset($data['access_token']) ) {
				$access_token 	= $data['access_token'];
				$expires		= $data['expires'];
				$parrams		= $configs->toArray();
				$parrams['access_token'] 	= $access_token;
				$parrams['uid'] 		= $userid;

				$registry = new JRegistry();
				$registry->loadArray( $parrams );
				$storedata = $registry->toString();

				$sql = "UPDATE 	`#__obsocialsubmit_instances`
						SET `params` = '{$storedata}' WHERE `id`={$aid}";
				$db = JFactory::getDbo();
				$db->setQuery($sql);
				$db->query();
				if( $db->getErrorNum() ) {
					$app->enqueueMessage(JText::_("PLG_OBSS_EXTERN_FACEBOOK_ERROR_ON_SAVE_CONNECTION"), 'error');
					$app->redirect($url_redirect);
					//echo '<pre>' . print_r( $db->getErrorMsg(), true ) . '</pre>';					
				}
				$app->enqueueMessage(JText::_("PLG_OBSS_EXTERN_FACEBOOK_CONNECT_WITH_FACEBOOK_SUCCESS"));
				$app->redirect($url_redirect);
			}
			$app->enqueueMessage(JText::_("PLG_OBSS_EXTERN_FACEBOOK_ERROR_ON_GET_ACCESS_TOKEN"));
			$app->redirect($url_redirect);
		}
	}
}
#end if class_exists
