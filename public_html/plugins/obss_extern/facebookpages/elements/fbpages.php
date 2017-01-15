<?php
/**
 * @version           $Id: fbpages.php 724 2011-06-15 07:20:37Z phonglq $
 * @author            Phong Lo - foobla.com
 * @package           obSocialSubmit for Joomla
 * @subpackage        obss extern addon Facebook
 * @license           GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.html.parameter' );

//JFormFieldTwitterOauth
class JFormFieldFBPages extends JFormField {
	protected $type = 'FBPages';
	public $config = null;
	public $addon_params;

	function getConfig() {
		if ( ! $this->addon_params ) {
			$db = JFactory::getDBO();
			$id = JRequest::getVar( 'id' );
			if ( ! $id ) {
				$cid = JRequest::getVar( 'cid' );
				$id  = $cid[0];
			}

			$param_text = '';
			if ( $id ) {
				$sql = "SELECT params FROM `#__obsocialsubmit_instances`
							WHERE `addon`='facebookpages' AND `addon_type`='extern' AND `id`={$id} LIMIT 1";
				$db->setQuery( $sql );
				$param_text = $db->loadResult();
			}

			$this->config = new JRegistry( $param_text );
		}

		return $this->config;
	}

	function getInput() {
		$control_name = $this->formControl . '[' . $this->group . ']';
		$id_prefix = $this->formControl.'_'.$this->group.'_';
		$lang         = JFactory::getLanguage();
		$lang->load( 'plg_obss_extern_facebookpages' );
		$id = JRequest::getVar( 'id' );
		if ( ! $id ) {
			$cid = JRequest::getVar( 'cid' );
			$id  = $cid[0];
		}

		$config 		= $this->getConfig();
		$appid 			= trim( $config->get( 'app_appid', '' ) );
		$base64_access_token_manager_pages = trim( $config->get( 'base64_access_token_manager_pages' ) );
		$callback_url 	= JURI::base() . 'index.php?obsstask=addonfunc&addonfunc=callback&aid=' . $id . '&atype=ex';
		$html 			= '';
		$html .= '<div id="fb-root"></div>';
		$html .= '<script src="https://connect.facebook.net/en_US/all.js"></script>';
		$html .= '<script>
				FB.init({
					appId  : "' . $appid . '",
					status : true, // check login status
					cookie : true, // enable cookies to allow the server to access the session
					xfbml  : true  // parse XFBML
				});

				function requestPerManagePages(){
					var app_appid 	= jQuery("#'.$id_prefix.'app_appid").val();
					var app_secret 	= jQuery("#'.$id_prefix.'app_secret").val();
					if( !app_appid && !app_secret ) {
						alert("'.JText::_("PLG_OBSS_EXTERN_FACEBOOKPAGES_APPID_AND_APPSECRET_IS_REQUIRED_MSG").'");
						jQuery("#'.$id_prefix.'app_appid-lbl").addClass("required invalid");
						jQuery("#'.$id_prefix.'app_appsecret-lbl").addClass("required invalid");
						jQuery(\'.nav-tabs a[href="#user_app_setting"]\').tab(\'show\') // Select tab by name
						jQuery(\'.nav-tabs a[href="#user_app_setting"]\').addClass("required invalid");
						return;
					}
					FB.login(function(response) {
						if ( response.authResponse ) {
							if(response.authResponse.accessToken){
								// open callback link
								alert("Logged in");
								var callback= "' . $callback_url . '&access_token="+response.authResponse.accessToken;
								window.parent.location.assign(callback);
								//window.parent.saveAccessToken( response.authResponse.accessToken, response.authResponse.userID );
							}else{
								alert("Login false.");
							}
						} else {
							alert("user is not logged in");
						}
					}, {scope:"publish_pages,manage_pages,publish_actions"});
				}

				function fbpageDisconnect(){
					//jQuery("#'.$id_prefix.'app_appid").val("");
					//jQuery("#'.$id_prefix.'app_appsecret").val("");
					jQuery("#'.$id_prefix.'base64_access_token_manager_pages").val("");
					//jQuery("#'.$id_prefix.'auto_key").val(""); 
					jQuery(".fbpages_page_details").val(""); 
					jQuery(".fbpages_page_id").prop("checked", false)||jQuery(".fbpages_page_id").removeAttr("checked");
					Joomla.submitbutton("connection.apply");
				}


				function getKeyFromOurApp(){
					var callback = encodeURI(window.location.href);
					window.location.href = "http://demo.foobla.com/connect/fbpages.php?callback_url="+"' . urlencode( $callback_url ) .'";
				}
			</script>';
		$html .= '<div style="float:left;">';
		if ( ! $base64_access_token_manager_pages ) {
			//$html .= '<span style="color:red;">' . JText::_( 'PLG_OBSS_EXTERN_FACEBOOKPAGES_FACEBOOK_APPLICATION_ID_IS_REQUIRED' ) . '</span>';
			//$html .= '<input type="button" class="btn btn-success" value="'.JText::_("PLG_OBSS_EXTERN_FACEBOOKPAGES_BTN_CONNECT_WITH_OUR_APP").'" onclick="getKeyFromOurApp();"/>';
			$html .= '<br/><br/><input type="button" class="btn btn-primary" onclick="requestPerManagePages();return false;" value="' . JText::_( 'PLG_OBSS_EXTERN_FACEBOOKPAGES_LOAD_FACEBOOK_PAGES' ) . '"/>';
			$html .= '</div>';

			return $html;
		}


		$pages  = $config->get( 'pages', array() );
		$data 	= $config->toArray();

		$keys 		= array_keys( $data );
		$key_pages 	= preg_grep( "/^page_\d+$/", $keys );

		if ( $key_pages && count( $key_pages ) ) {
			$html .= '<div>';
			foreach ( $key_pages as $key_page ) {
				$page_details = $data[$key_page];
				if ( ! $page_details ) {
					continue;
				}
				$page_json = base64_decode( $page_details );
				$page_data = json_decode( $page_json, true );
				$html .= '<div>';
				$checked = in_array( $page_data['id'], $pages ) ? ' checked="checked" ' : '';
				$link = 'http://www.facebook.com/pages/' . $page_data['name'] . '/' . $page_data['id'];
				$html .= '<input class="fbpages_page_id" type="checkbox"' . $checked . 'name="' . $control_name . '[pages][]" value="' . $page_data['id'] . '"/><a href="' . $link . '" target="blank">' . $page_data['name'] . '</a>';
				$html .= '<input class="fbpages_page_details" type="hidden" name="' . $control_name . '[' . $key_page . ']" value="' . $page_details . '" />';
				$html .= '</div>';
			}
			$html .= '</div>';
		}

		$html .= '<br/><input type="button" class="btn btn-danger" onclick="fbpageDisconnect();return false;" value="' . JText::_( 'PLG_OBSS_EXTERN_FACEBOOKPAGES_DISCONNECT' ) . '"/>';
		$html .= '</div><div style="clear:both;"></div>';

		return $html;
	}
}