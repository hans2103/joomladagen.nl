<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2017 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

//no direct accees
defined ('_JEXEC') or die ('restricted access');

class SppagebuilderAddonOptin_form extends SppagebuilderAddons{

	public function render() {
		// get pageid
		$input 		= JFactory::getApplication()->input;
		$page_id 	= $input->get('id', 0, 'INT');

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$content = (isset($this->addon->settings->content) && $this->addon->settings->content) ? $this->addon->settings->content : '';

		$grid = (isset($this->addon->settings->grid) && $this->addon->settings->grid) ? $this->addon->settings->grid : '';
		$media_type = (isset($this->addon->settings->media_type) && $this->addon->settings->media_type) ? $this->addon->settings->media_type : '';
		$image = (isset($this->addon->settings->image) && $this->addon->settings->image) ? $this->addon->settings->image : '';
		$alt_text = (isset($this->addon->settings->alt_text) && $this->addon->settings->alt_text) ? $this->addon->settings->alt_text : '';
		$icon_name = (isset($this->addon->settings->icon_name) && $this->addon->settings->icon_name) ? $this->addon->settings->icon_name : '';
		$media_position = (isset($this->addon->settings->media_position) && $this->addon->settings->media_position) ? $this->addon->settings->media_position : 'top';

		$form_inline = (isset($this->addon->settings->form_inline) && $this->addon->settings->form_inline) ? $this->addon->settings->form_inline : '';
		$alignment = (isset($this->addon->settings->alignment) && $this->addon->settings->alignment) ? $this->addon->settings->alignment : '';

		// Addon Options
		$platform = (isset($this->addon->settings->platform) && $this->addon->settings->platform) ? $this->addon->settings->platform : 'mailchimp';
		$hide_name = (isset($this->addon->settings->hide_name)) ? $this->addon->settings->hide_name : 0;

		$mailchimp_api 		= (isset($this->addon->settings->mailchimp_api) && $this->addon->settings->mailchimp_api) ? $this->addon->settings->mailchimp_api : '';
		$sendgrid_api = (isset($this->addon->settings->sendgrid_api) && $this->addon->settings->sendgrid_api) ? $this->addon->settings->sendgrid_api : '';
		$sendinblue_api = (isset($this->addon->settings->sendinblue_api) && $this->addon->settings->sendinblue_api) ? $this->addon->settings->sendinblue_api : '';
		$madmimi_api = (isset($this->addon->settings->madmimi_api) && $this->addon->settings->madmimi_api) ? $this->addon->settings->madmimi_api : '';

		$optin_type 	= (isset($this->addon->settings->optin_type) && $this->addon->settings->optin_type) ? $this->addon->settings->optin_type : 'normal';

		$button_text = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_FORM_SUBCSCRIBE');
		$use_custom_button = (isset($this->addon->settings->use_custom_button) && $this->addon->settings->use_custom_button) ? $this->addon->settings->use_custom_button : 0;
		$button_position = (isset($this->addon->settings->button_position) && $this->addon->settings->button_position) ? $this->addon->settings->button_position : '';
		$button_class = (isset($this->addon->settings->button_type) && $this->addon->settings->button_type) ? ' sppb-btn-' . $this->addon->settings->button_type : ' sppb-btn-success';

		if($use_custom_button) {
			$button_text = (isset($this->addon->settings->button_text) && $this->addon->settings->button_text) ? $this->addon->settings->button_text : JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_FORM_SUBCSCRIBE');
			$button_class .= (isset($this->addon->settings->button_size) && $this->addon->settings->button_size) ? ' sppb-btn-' . $this->addon->settings->button_size : '';
			$button_class .= (isset($this->addon->settings->button_shape) && $this->addon->settings->button_shape) ? ' sppb-btn-' . $this->addon->settings->button_shape: ' sppb-btn-rounded';
			$button_class .= (isset($this->addon->settings->button_appearance) && $this->addon->settings->button_appearance) ? ' sppb-btn-' . $this->addon->settings->button_appearance : '';
			$button_class .= (isset($this->addon->settings->button_block) && $this->addon->settings->button_block) ? ' ' . $this->addon->settings->button_block : '';
			$button_icon = (isset($this->addon->settings->button_icon) && $this->addon->settings->button_icon) ? $this->addon->settings->button_icon : '';
			$button_icon_position = (isset($this->addon->settings->button_icon_position) && $this->addon->settings->button_icon_position) ? $this->addon->settings->button_icon_position: 'left';

			if($button_icon_position == 'left') {
				$button_text = ($button_icon) ? '<i class="fa ' . $button_icon . '"></i> ' . $button_text : $button_text;
			} else {
				$button_text = ($button_icon) ? $button_text . ' <i class="fa ' . $button_icon . '"></i>' : $button_text;
			}
		}
		$output  = '';

		// if cURL has't loaded or available in the server
		if (!extension_loaded('curl')) {
			$output  .= '<div class="sppb-addon sppb-addon-optin-forms sppb-alert sppb-alert-warning">';
				$output  .= '<p>' . JTEXT::_('COM_SPPAGEBUILDER_GLOBAL_CURL_NOT_AVAILABLE') . '</p>';
			$output  .= '</div>';
			return $output;
		}

		// if selected platform hasn't api key inserted
		if ( ($platform == 'mailchimp' && $mailchimp_api == '') || ($platform == 'sendgrid' && $sendgrid_api == '') || ($platform == 'sendinblue' && $sendinblue_api == '') || ($platform == 'madmimi' && $madmimi_api == '') ) {
			$output  .= '<div class="sppb-addon sppb-addon-optin-forms sppb-alert sppb-alert-warning">';
				$output  .= '<p>' . JTEXT::_('COM_SPPAGEBUILDER_ADDON_OPTIN_FORM_EMPTY_API') . ' ' . $platform . '.</p>';
			$output  .= '</div>';
			return $output;
		} elseif($platform == 'acymailing' && !include_once( rtrim(JPATH_ADMINISTRATOR,'/') . '/components/com_acymailing/helpers/helper.php')){
			// if acymailing isn't installed
			$output  .= '<div class="sppb-addon sppb-addon-optin-forms sppb-alert sppb-alert-warning">';
				$output  .= '<p>' . JTEXT::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_ACYMAILING_NOT_INSTALLED') . '</p>';
			$output  .= '</div>';
			return $output;
		}

		$info_wrap = '';
		$form_wrap = '';
		$raw_wrap  = '';
		switch ($grid) {
			case '6-6':
				$raw_wrap  = 'has-grid';
				$info_wrap = 'sppb-col-sm-6';
				$form_wrap = 'sppb-col-sm-6';
				break;
			case '5-7':
				$raw_wrap  = 'has-grid';
				$info_wrap = 'sppb-col-sm-5';
				$form_wrap = 'sppb-col-sm-7';
				break;
			case '8-4':
				$raw_wrap  = 'has-grid';
				$info_wrap = 'sppb-col-sm-8';
				$form_wrap = 'sppb-col-sm-4';
				break;
			case '2-10':
				$raw_wrap  = 'has-grid';
				$info_wrap = 'sppb-col-sm-2';
				$form_wrap = 'sppb-col-sm-10';
				break;

			default:
				$info_wrap = 'sppb-col-sm-12';
				$form_wrap = 'sppb-col-sm-12';
				break;
		}

		$output  .= '<div class="sppb-addon sppb-addon-optin-forms optintype-' . $optin_type . ' ' . $class . ' ' . $alignment . ' grid' . $grid . '">';
		$media = '';
		$media_class = '';
		if($media_type == 'img'){
			$media_class .= ' sppb-optin-form-img';
			if($image){
				$media .= '<img class="sppb-img-responsive" src="' . $image . '" alt="'. $alt_text .'">';
			}
		} else{
			$media_class .= ' sppb-optin-form-icon';
			if($icon_name){
				$media .= '<i class="fa ' . $icon_name . '"></i>';
			}
		}

		if($grid == 'ws-4-4-4'){
			$output .= '<div class="sppb-row">';
			$output .= '<div class="sppb-col-sm-offset-4 sppb-col-sm-4">';
		} elseif($grid == 'ws-2-8-2'){
			$output .= '<div class="sppb-row">';
			$output .= '<div class="sppb-col-sm-offset-2 sppb-col-sm-8">';
		} elseif($grid == 'ws-3-6-3'){
			$output .= '<div class="sppb-row">';
			$output .= '<div class="sppb-col-sm-offset-3 sppb-col-sm-6">';
		}

		$output .= '<div class="sppb-optin-form-box sppb-row '. $raw_wrap .'">';

			$output .= '<div class="sppb-optin-form-info-wrap media-position-'. $media_position .' ' . $info_wrap . '">';
				$output .= '<div class="sppb-optin-form-img-wrap ' . $media_class .'">' . $media . '</div>';
				if(isset($title) || isset($content)){
					$output .= '<div class="sppb-optin-form-details-wrap">';
				}
					if($title) {
						$output .= '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>';
					}
					if($content) {
						$output .= '<div class="sppb-optin-form-details">' . $content . '</div>';
					}
				if(isset($title) || isset($content)){
					$output .= '</div>'; //.sppb-optin-form-details-wrap
				}
			$output .= '</div>'; //.sppb-optin-form-info-wrap


			$output .= '<div class="sppb-optin-form-content '. $form_wrap .'">';
				$forminline = ($form_inline) ? 'form-inline' : '';
				$output .= '<form class="sppb-optin-form ' . $forminline . '">';
					if (!$hide_name) {
						$output .= '<div class="sppb-form-group name-wrap">';
							$output .= '<input type="text" name="fname" class="sppb-form-control" placeholder="'. JText::_('COM_SPPAGEBUILDER_ADDON_AJAX_CONTACT_NAME') .'" required="required">';
						$output .= '</div>';
					}

					$output .= '<div class="sppb-form-group email-wrap">';
						$output .= '<input type="email" name="email" class="sppb-form-control" placeholder="'. JText::_('COM_SPPAGEBUILDER_ADDON_AJAX_CONTACT_EMAIL') .'" required="required">';
					$output .= '</div>';

					$output .= '<input type="hidden" name="platform" value="'. $platform .'">';
					$output .= '<input type="hidden" name="hidename" value="'. $hide_name .'">';
					$output .= '<input type="hidden" name="pageid" value="'. $page_id .'">';
					$output .= '<input type="hidden" name="addonId" value="'. $this->addon->id .'">';

					$output .= '<div class="button-wrap ' . $button_position . '">';
						$output .= '<button type="submit" id="btn-' . $this->addon->id . '" class="sppb-btn' . $button_class . '"><i class="fa"></i> '. $button_text .'</button>';
					$output .= '</div>'; //.button-wrap

				$output .= '</form>';
				$output .= '<div style="display:none;margin-top:10px;" class="sppb-optin-form-status"></div>';
			$output .= '</div>'; //.sppb-optin-form-content

		$output .= '</div>'; //.sppb-optin-form-box

		if(($grid == 'ws-4-4-4') || ($grid == 'ws-2-8-2') || ($grid == 'ws-3-6-3')){
			$output .= '</div>'; //sppb-offset
			$output .= '</div>'; //sppb-row
		}

		$output .= '</div>'; //.sppb-addon-optin-forms
		return $output;
	}

	public static function getAjax() {

		$input = JFactory::getApplication()->input;
		//inputs
		$inputs = $input->get('data', array(), 'ARRAY');

		foreach ($inputs as $input) {
			if( $input['name'] == 'email' ) {
				$email = $input['value'];
			}

			if( $input['name'] == 'hidename' ) {
				$hidename	= $input['value'];
			}

			if( $input['name'] == 'fname' ) {
				$name	= $input['value'];
			}

			if( $input['name'] == 'platform' ) {
				$platform		= $input['value'];
			}

			if( $input['name'] == 'pageid' ) {
				$pageid		= $input['value'];
			}
			if( $input['name'] == 'addonId' ) {
				$addonId		= $input['value'];
			}
		}

		// get addon infos
		$page_info = self::getPageInfoById( $pageid );
		$addon_info = self::getAddonSettingByPageInfo( $page_info->text, $addonId );

		$output = array();
		$output['status'] = false;

		// valited email address
		if ($email) {
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_INVALID_EMAIL');
				$output['status'] = false;
				return json_encode($output);
			}
		}

		// if hide name field then set value NULL
		if ($hidename) {
			$name = '';
		}

		if($platform == 'mailchimp') {
			//mailchimp get crecentials
			$mcapi 				= (isset($addon_info->mailchimp_api) && $addon_info->mailchimp_api) ? $addon_info->mailchimp_api : '';
			$mclistid 		= (isset($addon_info->mailchimp_listid) && $addon_info->mailchimp_listid) ? $addon_info->mailchimp_listid : '';
			$mcaction 		= (isset($addon_info->mailchimp_action) && $addon_info->mailchimp_action) ? $addon_info->mailchimp_action : '';

	    $memberId = md5(strtolower($email));
	    $dataCenter = substr($mcapi,strpos($mcapi,'-')+1);
	    $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/'. $mclistid .'/members/' . $memberId;
	    $json = json_encode([
	        'email_address' => $email,
	        'status'        => $mcaction, // "subscribed","unsubscribed","cleaned","pending"
	        'merge_fields'  => [
            'FNAME'     => $name,
            'LNAME'     => ''
	        ]
	    ]);

	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $mcapi);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	    $result = curl_exec($ch);
	    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$err = curl_error($ch);
	    curl_close($ch);

			//$response = json_decode($result)->status;
			// if curl error
			if ($err) {
				$output['content'] = 'cURL error: ' . $err;
				$output['status'] = false;
				return json_encode($output);
			}

			// store the status message based on response code
	    if ($httpCode == 200) {
				if ($mcaction == 'pending') {
						$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_PENDING');
				} else {
						$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_CONFIRMED');
				}
				$output['status'] = true;
	    } else {
	        switch ($httpCode) {
	            case 214: // if success
	                $output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_EXIST');
									$output['status'] = false;
	                break;
	            default:
	                $output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_ERROR');
									$output['status'] = false;
	                break;
	        } // if got response
	    }
		} elseif ($platform == 'sendgrid') {
				//sendgrid get crecentials
				$sgapi = (isset($addon_info->sendgrid_api) && $addon_info->sendgrid_api) ? $addon_info->sendgrid_api : '';

				$input_data = json_encode(
					array(
						'email' => $email,
						'first_name' => $name,
						'last_name' => ''
					)
				);
				$input_data = '['.$input_data. ']';
				$access_api = array(
					"authorization: Bearer " . $sgapi,
					"cache-control: no-cache"
				);

				$curl = curl_init();
				curl_setopt($curl,CURLOPT_URL, "https://api.sendgrid.com/v3/contactdb/recipients");
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_ENCODING, '');
				curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
				curl_setopt($curl, CURLOPT_TIMEOUT, 30);
				curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, CURLOPT_SSL_VERIFYPEER);
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($curl, CURLOPT_POSTFIELDS, $input_data);
				curl_setopt($curl, CURLOPT_HTTPHEADER, $access_api);

				$result = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				$result_decode = json_decode($result);
				// if curl error
				if ($err) {
					$output['content'] = 'cURL error: ' . $err;
					$output['status'] = false;
					return json_encode($output);
				}

				if (isset($result_decode->error_count) && $result_decode->error_count == 0) {
					if ($result_decode->updated_count) {
						$output['status'] = true;
						$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_UPDATED');
					} else {
						$output['status'] = true;
						$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_CONFIRMED');
					}
				} else {
					$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_ERROR');
					$output['status'] = false;
					return json_encode($output);
				}

		} if ($platform == 'sendinblue') { // if sendinblue
			//sendinBlue get crecentials
			$sbapi 				= (isset($addon_info->sendinblue_api) && $addon_info->sendinblue_api) ? $addon_info->sendinblue_api : '';
			$sblistid 		= (isset($addon_info->sendinblue_listid) && $addon_info->sendinblue_listid) ? $addon_info->sendinblue_listid : '';

			$data_input = array(
					'email' => $email,
	        'attributes' => array('NAME'=> $name),
	        'listid' => array($sblistid)
	    );

			$ch = curl_init('https://api.sendinblue.com/v2.0/user/createdituser');
			$auth_header = 'api-key:' . $sbapi;
			$content_header = "Content-Type:application/json";
			$timeout = 30000; //default timeout: 30 secs
			if ($timeout!='' && ($timeout <= 0 || $timeout > 60000)) {
					throw new \Exception('value not allowed for timeout');
			}
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
					// Windows only over-ride
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth_header,$content_header));
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT_MS, 30000);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_input));
			$data = curl_exec($ch);
			$err = curl_error($ch);
			if (!is_string($data) || !strlen($data)) {
				$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_ERROR');
				$output['status'] = false;
				return json_encode($output);
			}
			curl_close($ch);
			$result = json_decode($data, true);
			// if curl error
			if ($err) {
				$output['content'] = 'cURL error: ' . $err;
				$output['status'] = false;
				return json_encode($output);
			}
			if ($result['code'] == 'success') { // if success
				$output['status'] = true;
				if (isset($result['data']['updated']) && $result['data']['updated']) {
					$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_UPDATED');
				} else {
					$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_CONFIRMED');
				}
			} else {
				$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_ERROR');
				$output['status'] = false;
			}
		} elseif ($platform == 'madmimi') { // if madmimi
			//madmimi get crecentials
			$mmuname 			= (isset($addon_info->madmimi_user) && $addon_info->madmimi_user) ? $addon_info->madmimi_user : '';
			$mmapi 				= (isset($addon_info->madmimi_api) && $addon_info->madmimi_api) ? $addon_info->madmimi_api : '';
			$mmlistname 	= (isset($addon_info->madmimi_listname) && $addon_info->madmimi_listname) ? $addon_info->madmimi_listname : '';

			$user = array('email' => $email, 'firstName' => $name, 'add_list' => $mmlistname);
			$authenticate = array('username' => $mmuname, 'api_key' => $mmapi);
			// generate CSV
			$csv = "";
			$keys = array_keys($user);
			foreach ($keys as $key => $value) {
				$value = self::escape_for_csv($value);
				$csv .= $value . ",";
			}
			$csv = substr($csv, 0, -1);
			$csv .= "\n";
			foreach ($user as $key => $value) {
				$value = self::escape_for_csv($value);
				$csv .= $value . ",";
			}
			$csv = substr($csv, 0, -1);
			$csv .= "\n";

			$options = array('csv_file' => $csv) + $authenticate;
			// do reqiest
			$request_options = http_build_query($options);
			$url = 'https://api.madmimi.com/audience_members';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:"));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request_options);
			$result = curl_exec($ch);
			$err = curl_error($ch);
			curl_close($ch);

			// if curl error
			if ($result === false && $err) {
				$output['content'] = 'cURL error: ' . $err;
				$output['status'] = false;
				return json_encode($output);
			}

			if ( is_numeric($result) ) {
				$output['status'] = true;
				$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_CONFIRMED');
			} else {
				$output['status'] = false;
				$output['content'] = $result;
				return json_encode($output);
			}
		} elseif ($platform == 'acymailing') { // if AcyMailing
			// if acymailing isn't installed
			if(!include_once( rtrim(JPATH_ADMINISTRATOR,'/') . '/components/com_acymailing/helpers/helper.php')){
			 $output['status'] = false;
			 $output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_ACYMAILING_NOT_INSTALLED');
			 return json_encode($output);
			}

			$acymailing_listids 	= (isset($addon_info->acymailing_listids) && $addon_info->acymailing_listids) ? $addon_info->acymailing_listids : '';

			$user_info = new stdClass();
			$user_info->email = $email;
			$user_info->name = $name;
			$subscriberClass = acymailing_get('class.subscriber');
			$subid = $subscriberClass->save($user_info); //this function will return you the ID of the user inserted in the AcyMailing table

			// if selected all list
			if ( (is_array($acymailing_listids) && in_array('', $acymailing_listids)) || $acymailing_listids == '') {
				$acy_list_class = acymailing_get('class.list');
				$acy_lists = $acy_list_class->getLists();

				$acymailing_listids = array();
				foreach ($acy_lists as $key => $acy_list) {
					$acymailing_listids[$key] = $acy_list->listid;
				}
			}

			$userClass = acymailing_get('class.subscriber');
			$new_subscription = array();
			if(!empty($acymailing_listids)){
				foreach($acymailing_listids as $listId){
				 $newList = array();
				 $newList['status'] = 1;
				 $new_subscription[$listId] = $newList;
				}
			}
			if(empty($new_subscription) || empty($subid) ) {
				$output['status'] = false;
				$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_ERROR');
			}
			if ($userClass->subid($subid)) {
				$subid = $userClass->subid($subid);
			}
			$results = $userClass->saveSubscription($subid, $new_subscription);

			if ($results) {
				$output['status'] = true;
				$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_CONFIRMED');
			} else {
				$output['status'] = false;
				$output['content'] = JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_PLATFORM_EMAIL_ERROR');
			}

		}
		return json_encode($output);
	}

	public static function escape_for_csv($s) {
		// Watch out! We may have quotes! So quote them.
		$s = str_replace('"', '""', $s);
		if(preg_match('/,/', $s) || preg_match('/"/', $s) || preg_match("/\n/", $s)) {
			// Quote the whole thing b/c we have a newline, comma or quote.
			return '"'.$s.'"';
		} else {
			// False alarm. We're good.
			return $s;
		}
	}

	public function stylesheets() {
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/magnific-popup.css');
	}

	public function scripts() {
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.magnific-popup.min.js');
	}

	public static function getPageInfoById($pageid){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select( array('a.*') );
    $query->from($db->quoteName('#__sppagebuilder', 'a'));
	  $query->where($db->quoteName('a.id') . " = " . $db->quote((int) $pageid));
    $db->setQuery($query);
    $result = $db->loadObject();

		return $result;

	}

	public static function getAddonSettingByPageInfo($pageContent,$addonId){
			$addonInfo = false;
			$pageContent = json_decode($pageContent);

			foreach ($pageContent as $key => $row) {
				foreach ($row->columns as $key => $column) {
					foreach ($column->addons as $key => $addon) {
						if ($addon->id == $addonId) {
							$addonInfo = $addon->settings;
							break;
						}
					}
				}
			}
			return $addonInfo;
	}

	public function js() {

		$optin_timein 	= (isset($this->addon->settings->optin_timein) && $this->addon->settings->optin_timein) ? $this->addon->settings->optin_timein : 0;
		$optin_timeout 	= (isset($this->addon->settings->optin_timeout) && $this->addon->settings->optin_timeout) ? $this->addon->settings->optin_timeout : 0;

		$addon_id = '#sppb-addon-' . $this->addon->id;
		$js ='jQuery(function($){

			var addonId 				= $("'.$addon_id.'"),
					prentSectionId	= addonId.parent().closest("section");

			if($("'.$addon_id.'").find(".optintype-popup").length !== 0 && $("body:not(.layout-edit)").length !== 0){
					prentSectionId.hide();
			}

			if($("'.$addon_id.'").find(".optintype-popup").length !== 0 && $("body:not(.layout-edit)").length !== 0){
					//var parentSection 	= $("'.$addon_id.'").parent().closest("section"),
					var addonWidth 			= addonId.parent().outerWidth(),
							optin_timein		= '. $optin_timein .',
							optin_timeout		= '. $optin_timeout .',
 							prentSectionId	= ".com-sppagebuilder:not(.layout-edit) #" + addonId.attr("id");

						$(window).load(function () {
						setTimeout(function(){
							$.magnificPopup.open({
					        items: {
					            src: "<div class=\"sppb-optin-form-popup-wrap\" \">"+$(addonId)[0].outerHTML + "</div>"
											//src: "<div style=\"width:+"addonWidth"+\">" + $(addonId)[0].outerHTML + "</div>"
					        },
					        type: "inline",
									mainClass: "mfp-fade",
									disableOn: function() {
							      return true;
							    },
									callbacks: {
						        open: function() {
											if(optin_timeout){
						            setTimeout(function(){
						                $("'.$addon_id.'").magnificPopup("close");
						            }, optin_timeout);
											}
						        }
						    	}
					    });
						}, optin_timein);
						}); //window
			};
		})';

		return $js;
	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';
		$css_path = new JLayoutFile('addon.css.button', $layout_path);

		$icon_style  = (isset($this->addon->settings->icon_size) && $this->addon->settings->icon_size) ? 'font-size: ' . $this->addon->settings->icon_size . 'px;' : '';
		$icon_style .= (isset($this->addon->settings->icon_color) && $this->addon->settings->icon_color) ? 'color: ' . $this->addon->settings->icon_color . ';' : '';
		$optin_width 	= (isset($this->addon->settings->optin_width) && $this->addon->settings->optin_width) ? 'width: ' . $this->addon->settings->optin_width . 'px;' : 'width: 500px;';

		$border_position = (isset($this->addon->settings->custom_input_border_side) && $this->addon->settings->custom_input_border_side) ? $this->addon->settings->custom_input_border_side : '';
		$custom_input = (isset($this->addon->settings->custom_input) && $this->addon->settings->custom_input) ? $this->addon->settings->custom_input : '';
		$custom_input_borderless = (isset($this->addon->settings->custom_input_borderless) && $this->addon->settings->custom_input_borderless) ? $this->addon->settings->custom_input_borderless : '';

		$custom_input_style = '';
		if ($custom_input) {
			$custom_input_style	.= (isset($this->addon->settings->custom_input_bgcolor) && $this->addon->settings->custom_input_bgcolor) ? 'background-color: ' . $this->addon->settings->custom_input_bgcolor . ';' : '';
			$custom_input_style	.= (isset($this->addon->settings->custom_input_color) && $this->addon->settings->custom_input_color) ? 'color: ' . $this->addon->settings->custom_input_color . ';' : '';
			$custom_input_style	.= (isset($this->addon->settings->custom_input_borderless) && $this->addon->settings->custom_input_borderless) ? 'border:none;' : '';
			$custom_input_style	.= (isset($this->addon->settings->custom_input_border) && $this->addon->settings->custom_input_border && $custom_input_borderless == 0) ? 'border: none; border-' . $border_position . 'width: ' . $this->addon->settings->custom_input_border . 'px;' : '';
			$custom_input_style	.= (isset($this->addon->settings->custom_input_border_color) && $this->addon->settings->custom_input_border_color && $custom_input_borderless == 0) ? 'border-' . $border_position . 'color: ' . $this->addon->settings->custom_input_border_color . ';' : '';
			$custom_input_style	.= (isset($this->addon->settings->custom_input_border_style) && $this->addon->settings->custom_input_border_style && $custom_input_borderless == 0) ? 'border-' . $border_position . 'style: ' . $this->addon->settings->custom_input_border_style . ';' : '';
			$custom_input_style	.= (isset($this->addon->settings->custom_input_bdr) && $this->addon->settings->custom_input_bdr) ? 'border-radius: ' . $this->addon->settings->custom_input_bdr . 'px;' : '';
			$custom_input_style	.= (isset($this->addon->settings->custom_input_padding) && $this->addon->settings->custom_input_padding) ? 'padding: ' . $this->addon->settings->custom_input_padding . ';' : '';
		}

		$css = '';
		if($icon_style) {
			$css .= $addon_id . ' .sppb-optin-form-icon {';
			$css .= $icon_style;
			$css .= "\n" . '}' . "\n"	;
		}

		if($optin_width) {
			$css .= '.sppb-optin-form-popup-wrap > ' . $addon_id . ' {';
			$css .= $optin_width;
			$css .= "\n" . '}' . "\n"	;
		}

		if($custom_input_style) {
			$css .= $addon_id . ' .sppb-optin-form input {';
			$css .= $custom_input_style;
			$css .= "\n" . '}' . "\n"	;
		}

		$use_custom_button = (isset($this->addon->settings->use_custom_button) && $this->addon->settings->use_custom_button) ? $this->addon->settings->use_custom_button : 0;

		if($use_custom_button) {
			$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $this->addon->settings, 'id' => 'btn-' . $this->addon->id));
		}

		return $css;
	}
}
