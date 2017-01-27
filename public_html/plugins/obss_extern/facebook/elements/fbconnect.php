<?php
/**
 * @version		$Id: fbconnect.php 990 2011-08-11 03:30:18Z phonglq $
 * @author		Phong Lo - foobla.com
 * @package		obSocialSubmit for Joomla
 * @subpackage	externTwitter addon
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.html.parameter');

class JFormFieldFBConnect extends JFormField
{
	protected $type			='FBConnect';
	public $config			= null;
	public $addon_params	= null;
	function getConfig(){
		if(!$this->addon_params){
			$db 	= JFactory::getDBO();
			$id 	= JRequest::getVar('id');
			if( !$id ) {
				$cid = JRequest::getVar('cid');
				$id  = $cid[0];
			}
			
			$param_text = '';
			if ($id) {
				$sql 	= "SELECT params FROM `#__obsocialsubmit_instances`
							WHERE `addon`='facebook' AND `addon_type`='extern' AND `id`=$id LIMIT 1";
				$db->setQuery($sql);
				$param_text 	= $db->loadResult();
			}
			
			$this->config 	= new JRegistry($param_text);
		}
		return $this->config;
	}

/*
	function fetchTooltip($label, $description, &$xmlElement, $control_name='', $name='')
	{
		return '';
	}
*/
	function getInput()
	{
		$id = JRequest::getVar('id');
		if( !$id ) {
			$cid = JRequest::getVar('cid');
			$id  = $cid[0];
		}
		$formControl 	= $this->formControl;
		$group 			= $this->group;
		$control_name 	= $formControl.'_'.$group;

		$btn_disconnect = '';
		$callback_url	= JURI::base().'index.php?obsstask=addonfunc&addonfunc=callback&aid='.$id.'&atype=ex';
		$configs	= $this->getConfig();
		$data		= $configs->toArray();
		$access_token = $this->value;
		//$auto_key 	= $configs->get('auto_key');

		$appid		= trim($configs->get('app_appid',''));
		$html		= '';
		$html .= '<div id="fb-root"></div>';
		$html .= '<script src="https://connect.facebook.net/en_US/all.js"></script>';
		$html .= '<script>
				FB.init({
					appId  : "'.$appid.'",
					status : true, // check login status
					cookie : true, // enable cookies to allow the server to access the session
					xfbml  : true  // parse XFBML
				});

				function saveAccessToken( accessToken, uid ) {
					//var base46AccessToken = encode64( accessToken );
					document.getElementById("'.$this->id.'").value = accessToken;
					document.getElementById("'.$control_name.'uid").value = uid;
					submitbutton("apply");
				}

				function fbdisconnect(){
					document.getElementById("'.$this->id.'").value="";
					document.getElementById("'.$control_name.'_uid").value="";
					Joomla.submitbutton("connection.apply")
				}
				
				function ajaxFormSubmit(){
					
				}

				function fblogin(){

					var app_appid 	= jQuery("#'.$control_name.'_app_appid").val();
					var app_secret 	= jQuery("#'.$control_name.'_app_secret").val();
					if( !app_appid && !app_secret ) {
						alert("'.JText::_("PLG_OBSS_EXTERN_FACEBOOK_APPID_AND_APPSECRET_IS_REQUIRED_MSG").'");
						//jQuery("#'.$control_name.'_app_appid").addClass("required invalid");
						jQuery("#'.$control_name.'_app_appid-lbl").addClass("required invalid");
						//jQuery("#'.$control_name.'_app_secret").addClass("required invalid");
						jQuery("#'.$control_name.'_app_secret-lbl").addClass("required invalid");
						jQuery(\'.nav-tabs a[href="#user_app_setting"]\').tab(\'show\') // Select tab by name
						jQuery(\'.nav-tabs a[href="#user_app_setting"]\').addClass("required invalid");
						return;
					}


					FB.login(function(response) {
						if ( response.authResponse ) {
							if(response.authResponse.accessToken){
								// open callback link
								var callback= "'.$callback_url.'&access_token="+response.authResponse.accessToken+"&userid="+response.authResponse.userID;
								window.parent.location.assign(callback);
								//window.parent.saveAccessToken( response.authResponse.accessToken, response.authResponse.userID );
							}else{
								alert("Login failse.");
							}
						} else {
							//alert("user is not logged in");
						}
					}, {scope:"publish_actions"});
					
				}

				function getKeyFromOurApp(){
					var callback = encodeURI(window.location.href);
					window.location.href = "http://demo.foobla.com/connect/fb.php?callback_url="+"' . urlencode( $callback_url ) .'";
				}

			</script>';
		if( !$access_token ) {
			$html 	.= '<input type="button" class="btn btn-info" value="'.JText::_("PLG_OBSS_EXTERN_FACEBOOK_BTN_CONNECT_WITH_USER_APP").'" onclick="fblogin();"/>';
			//$html 	.= '<br/><input type="button" class="btn btn-success" value="'.JText::_("PLG_OBSS_EXTERN_FACEBOOK_BTN_CONNECT_WITH_OUR_APP").'" onclick="getKeyFromOurApp();"/>';
			return $html;
		}

		$uid 	= $configs->get('uid','');

		if( $uid && $access_token ) {
			$api_user = 'https://graph.facebook.com/'.$uid;
			#TODO: get user infor 
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $api_user);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_NOBODY, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Facebook-1.6.8-addon-for-obSocialSubmit-by-foobla');
			curl_setopt($ch, CURLOPT_TIMEOUT, 100);
			$res = curl_exec($ch);
			if (curl_errno($ch) == 60) { // CURLE_SSL_CACERT
				curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__).DS.'fb_ca_chain_bundle.crt');
				$res = curl_exec($ch);
			}
			if(curl_errno($ch))
			{
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
				$res = curl_exec($ch);
			}
			if(curl_errno($ch))
			{
				$mainframe = &JFactory::getApplication();
				$mainframe->enqueueMessage('CURL ERROR:'.print_r(curl_error($ch), true),'error');
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			}
			curl_close($ch);
			$user = json_decode( $res );
#			$html.= '<pre>'.print_r($user, true).'</pre>';
			#END: get user info
			
			if( $user && isset($user->name) ) {
				$html .= '<a alt="'.$user->name.'" title="'.$user->name.'" href="'.$user->link.'">';
				$html .= '<img height="50" width="50" alt="'.$user->name.'" title="'.$user->name.'" src="http://graph.facebook.com/'.$uid.'/picture"/></a>';
				$html .= '<br/><a alt="'.$user->name.'" title="'.$user->name.'" href="'.$user->link.'">'.$user->name.'</a>';
				$html .= '<br/>';
			}
			$btn_disconnect = '<input type="button" class="btn btn-danger" value="'.JText::_("PLG_OBSS_EXTERN_FACEBOOK_BTN_DISCONNECT_VALUE").'" onclick="fbdisconnect();"/>';
		}
		$html 	.= '';
		$html 	.= '<input id="'.$this->id.'" name="'.$this->name.'" type="hidden" value="'.$this->value.'"/>';
		$html	.= $btn_disconnect;
		return $html;
	}

	function getLabel(){
		return '';
	}
}
