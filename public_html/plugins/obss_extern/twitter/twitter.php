<?php
/**
 * @version        $Id: twitter.php 1367 2013-06-19 02:48:48Z phonglq $
 * @author        Phong Lo - foobla.com
 * @package        obSocialSubmit for Joomla
 * @subpackage    externTwitter addon
 * @license        GNU/GPL
 */

defined( "_JEXEC" ) or die( "Cannot direct access!" );

if ( ! class_exists( 'tmhOAuth' ) ) {
	require_once dirname( __FILE__ ) . DS . 'helpers' . DS . 'tmhOAuth.php';
}
if ( ! class_exists( 'tmhUtilities' ) ) {
	require_once dirname( __FILE__ ) . DS . 'helpers' . DS . 'tmhUtilities.php';
}
require_once JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obsocialsubmit' . DS . 'helpers' . DS . 'functions.php';
require_once JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_obsocialsubmit' . DS . 'helpers' . DS . 'class.externaddon.php';

if ( ! class_exists( 'OBSSExAddonTwitter' ) ) {
	class OBSSExAddonTwitter extends OBSSExAddon {
		public $functions = array( 'connect', 'callback', 'update_pages' );

		function postMessage( $input ) {
			$mainframe = JFactory::getApplication();
			if ( ! isset( $input->message ) ) {
				return;
			}

			#get config information
			$config = $this->getConfig();

			$ckey               = trim( $config->get( 'consumer_key' ) );
			$csecret            = trim( $config->get( 'consumer_secret' ) );
			$oauth_token        = trim( $config->get( 'oauth_token' ) );
			$oauth_token_secret = trim( $config->get( 'oauth_token_secret' ) );

			if ( ! $oauth_token || ! $oauth_token_secret || ! $ckey || ! $csecret ) {
				return false;
			}

			$debug  = $config->get( 'debug' );
			$maxlen = 140;

			$msg    = trim( strip_tags( $input->message ) );
			$msglen = mb_strlen( $msg );

			$url    = trim( strip_tags( $input->url ) );
			$urllen = mb_strlen( $url );                                # Lenght of url

			$shorturl    = trim( strip_tags( $input->shorturl ) );
			$shorturllen = mb_strlen( $shorturl );                            # Lenght of url

			$url_wrap_len = 20;

			$title       = trim( strip_tags( $input->title ) );
			$title2      = $title;
			$titlelen    = mb_strlen( $title );
			$titleminlen = ( $titlelen > 20 ) ? 20 : $titlelen;

			$img_path = $this->isUploadImage( $input );
			if ( $img_path ) {
				$maxlen = 116;
			}                                # $maxlen + len(image link) ~ 140
			if ( $debug ) {
				echo '<pre>' . print_r( $input, true ) . '</pre>';
			}
			$template = ( $input->template ) ? $input->template : '';
			$tags     = $this->getTags( $template );

			if ( $msglen > $maxlen ) {
				if ( in_array( '[url]', $tags ) && $urllen > $url_wrap_len ) {
					$maxlen = $maxlen + $urllen - $url_wrap_len;
				}

				if ( in_array( '[shorturl]', $tags ) && $shorturllen > $url_wrap_len ) {
					$maxlen = $maxlen + $shorturllen - $url_wrap_len;
				}
			}

			if ( $msglen > $maxlen && in_array( '[title]', $tags ) ) {
				# cut title
				$titlelen2 = $titlelen - $msglen + $maxlen;
				if ( $titlelen2 > $titleminlen ) {
					//$title 	= mb_substr($title, 0, $titleminlen );
					$title  = mb_substr( $title, 0, $titlelen2 - 1 );//new trial way
					$msglen = $msglen - $titlelen + $titlelen2;
				}
			}


			if ( $msglen > $maxlen ) {
				if ( in_array( '[shorturl]', $tags ) ) {
					$msg = $title . ' ' . $shorturl;
					if ( mb_strlen( $msg ) > 140 ) {
						$title = mb_substr( $title, 0, mb_strlen( $msg ) - 140 );
						$msg   = $title . ' ' . $shorturl;
					}
				}
			} else {
				if ( $title != $title2 ) {
					$msg = str_replace( $title2, $title, $msg );
				}
			}

			if ( $debug ) {
				echo '<hr/>';
				var_dump( $debug );
				echo '<hr/>';
			}

			# POST MESSAGE TO TWITTER
			$tmhOAuth = new tmhOAuth( array(
				'consumer_key'    => $ckey,
				'consumer_secret' => $csecret,
				'user_token'      => $oauth_token,
				'user_secret'     => $oauth_token_secret,
				'curl_cainfo'     => dirname( __FILE__ ) . '/helpers/cacert.pem',
				'curl_capath'     => dirname( __FILE__ ) . '/helpers',
			) );

			if ( $debug ) {
				echo '<pre>' . print_r( $img_path, true ) . '</pre>';
			}
			$media_ids = '';
			if ( $img_path ) {
				/*$media_arr = array(
						'media[]' => file_get_contents( $img_path )
					);*/
				$parameters = array(
					'media_data' => base64_encode(file_get_contents($img_path)),
				);
				$code = $tmhOAuth->request(
					'POST',
					'https://upload.twitter.com/1.1/media/upload.json',
					$parameters,
					true, // use auth
					true  // multipart
				);
				$media_data = json_decode( $tmhOAuth->response['response'] );
				if ( isset($media_data->media_id) && $media_data->media_id ) {
					$media_ids = $media_data->media_id;
				}
				if ( $debug ) {
					echo '<pre>Media_data: ' . print_r( json_decode( $tmhOAuth->response['response'] ), true ) . '</pre>';
				}
			}
			if ( $debug ) {
				echo '<pre>' . print_r( $media_ids, true ) . '</pre>';
			}

			$data_params = array(
				'wrap_links' => true,
				'status'     => $msg
			);

			if ($media_ids) {
				$data_params['media_ids'] = $media_ids;
			}

			$code = $tmhOAuth->request( 'POST', $tmhOAuth->url( '1.1/statuses/update' ), $data_params );
			if ( $code == 200 ) {
				$res = json_decode( $tmhOAuth->response['response'] );
				$log = print_r( $input, true ) . print_r( $res, true );
				$this->addLog( $log );
				if ( $debug ) {
					echo '<pre>' . print_r( $input, true ) . '</pre>';
					echo '<pre>' . print_r( $msg, true ) . '</pre>';
					echo '<pre>' . print_r( $res, true ) . '</pre>';
					exit( '' . __LINE__ );
				}

				return true;
			} else {
				$res = $tmhOAuth->response['response'];
				$log = print_r( $input, true ) . print_r( $res, true );
				$this->addLog( $log );
				$return = false;
				$obj    = json_decode( $res );
				if ( isset( $obj->errors[0]->code ) && intval( $obj->errors[0]->code ) == 187 ) {
					$return = true;
				}

				if ( ! $return ) {
					$mainframe->enqueueMessage( $res, 'error' );
					if ( $debug ) {
						echo '<pre>' . print_r( $input, true ) . '</pre>';
						echo '<pre>' . print_r( $msg, true ) . '</pre>';
						echo '<pre>' . print_r( $res, true ) . '</pre>';
						exit( '' . __LINE__ );
					}

					return false;
				} else {
					if ( $debug ) {
						echo '<pre>' . print_r( $input, true ) . '</pre>';
						echo '<pre>' . print_r( $msg, true ) . '</pre>';
						echo '<pre>' . print_r( $res, true ) . '</pre>';
						exit( '' . __LINE__ );
					}

					return true;
				}
			}
		}

		public function connect() {
			$mainframe    = JFactory::getApplication();
			$aid          = $this->data->id;
			$url_redirect = JURI::base() . 'index.php?option=com_obsocialsubmit&controller=addons&task=editext&cid[]=' . $aid;
			$user         = JFactory::getUser();
			if ( $user->guest ) {
				$mainframe->redirect( $url_redirect );
			}
			$config  = $this->getConfig();
			$ckey    = $config->get( 'consumer_key', '' );
			$csecret = $config->get( 'consumer_secret', '' );

			if ( ! $ckey || ! $csecret ) {
				$mainframe->redirect( $url_redirect );
			}

			if ( ! class_exists( 'tmhOAuth' ) ) {
				require_once 'helpers' . DS . 'tmhOAuth.php';
			}
			if ( ! class_exists( 'tmhUtilities' ) ) {
				require_once 'helpers' . DS . 'tmhUtilities.php';
			}

			$callback_url = JURI::base() . "index.php?obsstask=addonfunc&addonfunc=callback&aid={$aid}&atype=ex";
			$tmhOAuth     = new tmhOAuth( array(
				'consumer_key'    => $ckey,
				'consumer_secret' => $csecret,
			) );
			$params       = array( 'oauth_callback' => $callback_url );
			$code         = $tmhOAuth->request( 'POST', $tmhOAuth->url( 'oauth/request_token', '' ), $params );
			$debug        = $config->get( 'debug' );
			if ( $code == 200 ) {
				$request_token = $tmhOAuth->extract_params( $tmhOAuth->response['response'] );
				@$mainframe->setUserState( 'oauth_token', $request_token['oauth_token'] );
				@$mainframe->setUserState( 'oauth_token_secret', $request_token['oauth_token_secret'] );
				$authurl = $tmhOAuth->url( "oauth/authorize", '' ) . "?oauth_token={$request_token['oauth_token']}&force_login=1";
				$mainframe->redirect( $authurl );
			} else {
				if ( $debug ) {
					tmhUtilities::pr( $tmhOAuth );
					exit( '<br/>' . __LINE__ );
				}
				$mainframe->redirect( $callback_url, 'Error: ' . $tmhOAuth->response['response'], 'error' );
			}
		}

		public function callback() {
			$mainframe    = JFactory::getApplication();
			$db           = JFactory::getDbo();
			$aid          = $this->data->id;
			$url_redirect = JURI::base() . "index.php?option=com_obsocialsubmit&task=connection.edit&id=" . $aid;
			$config       = $this->getConfig();
			$debug        = $config->get( 'debug' );

			$oauth_token_request = &JRequest::getVar( 'oauth_token' );
			$oauth_verifier      = &JRequest::getVar( 'oauth_verifier' );
			if ( $oauth_token_request && $oauth_verifier ) {

				$ckey    = $config->get( 'consumer_key', '' );
				$csecret = $config->get( 'consumer_secret', '' );

				$oauth_token        = $mainframe->getUserState( 'oauth_token' );
				$oauth_token_secret = $mainframe->getUserState( 'oauth_token_secret' );
				if ( ! $oauth_token || ! $oauth_token_secret ) {
					$mainframe->redirect( $url_redirect );
				}

				$tmhOAuth                        = new tmhOAuth( array(
					'consumer_key'    => $ckey,
					'consumer_secret' => $csecret,
				) );
				$tmhOAuth->config['user_token']  = $oauth_token;
				$tmhOAuth->config['user_secret'] = $oauth_token_secret;
				$code                            = $tmhOAuth->request(
					'POST',
					$tmhOAuth->url( 'oauth/access_token', '' ),
					array( 'oauth_verifier' => $oauth_verifier )
				);
				if ( $code == 200 ) {
					$access_token                      = $tmhOAuth->extract_params( $tmhOAuth->response['response'] );
					$param_value                       = $config->toArray();
					$param_value['oauth_token']        = $access_token['oauth_token'];
					$param_value['oauth_token_secret'] = $access_token['oauth_token_secret'];

					$registry = new JRegistry();
					$registry->loadArray( $param_value );
					$storedata = $registry->toString();

					$sql = "UPDATE 	`#__obsocialsubmit_instances`
							SET `params` = '$storedata' WHERE `id` = " . $aid;
					$db->setQuery( $sql );
					$db->query();
					if ( $db->getErrorNum() ) {
						if ( $debug ) {
							print_r( $db->getErrorMsg() );
							echo '<pre>' . print_r( $db, true ) . '</pre>';
							exit( '<br/>' . __LINE__ );
						}
						$mainframe->enqueueMessage( sprintf( JText::_( 'JLIB_APPLICATION_ERROR_SAVE_FAILED' ), JText::_( "ERROR_ON_UPDATE_TWITTER_ADDON" ) ), 'error' );
						$mainframe->redirect( $url_redirect );
					} else {
						// $mainframe->redirect($url_redirect, sprintf(JText::_('JLIB_APPLICATION_ERROR_SAVE_FAILED'),JText::_("ERROR_ON_UPDATE_TWITTER_ADDON")),'error');
						$mainframe->enqueueMessage( JText::_( "JLIB_APPLICATION_SAVE_SUCCESS" ) );
						$mainframe->redirect( $url_redirect );
					}
				} else {
					$mainframe->enqueueMessage( 'Error: ' . $tmhOAuth->response['response'], 'error' );
					$mainframe->redirect( $url_redirect );
				}
			} else {
				if ( $debug ) {
					echo '<pre>' . print_r( $_REQUEST, true ) . '</pre>';
					exit( '<br/>' . __LINE__ );
				}
				$mainframe->enqueueMessage( JText::_( "ERROR_ON_CONNECT_WITH_TWITTER" ), 'error' );
				$mainframe->redirect( $url_redirect );
			}
			$mainframe->redirect( $url_redirect );
		}


		public function getTags( $subject, $pattern = '/\[[^\]]+\]/i' ) {
			preg_match_all( $pattern, $subject, $result );
			if ( $result ) {
				return $result[0];
			}

			return array();
		}


		/**
		 * Check image is upload
		 *
		 * @param unknown $input
		 *
		 * @return false if not upload or image not exist | image_path
		 */
		public function isUploadImage( $input ) {
			$config     = $this->getConfig();
			$upload_img = $config->get( 'upload_img' );
			if ( $upload_img && $input->img ) {
				$juri_root  = JURI::root();
				$parse_url = parse_url($input->img);
				$jpath_site = JPATH_SITE . DS;
				$img_path   = str_replace( $juri_root, $jpath_site, $input->img );

				$img_path   = str_replace( '/', DS, $img_path );
				if ( ! is_file( $img_path ) ) {
					if (isset($parse_url['path']) && $parse_url['path'] != '') {
						$img_path = JPATH_SITE . $parse_url['path'];
					}
				}
				if ( ! is_file( $img_path ) ) {

					return false;
				} else {
					return $img_path;
				}
			}

			return false;
		}

		public function addLog( $content, $time = '' ) {
			if ( method_exists( 'OBSSAddon', 'addLog' ) ) {
				parent::addLog( $content, $time );

				return;
			}
			$config = JFactory::getConfig();
			if ( ! $content ) {
				return;
			}
			$id       = $this->data->id;
			$datetime = '';
			if ( ! $time ) {
				$date = JFactory::getDate();
				if ( method_exists( $date, 'toSql' ) ) {
					$time     = $date->format( 'Ymd' );
					$datetime = $date->toSql();
				} elseif ( method_exists( $date, 'toMySql' ) ) {
					$time     = $date->format( 'Ymd' );
					$datetime = $date->toMysql();
				}
			}

			$file_name = 'LOG_' . $id . '_' . $time . '.log';
			$log_path  = $config->get( 'log_path' );

			$addon      = $this->data->addon;
			$addon_type = $this->data->addon_type;
			$file_path  = JPath::clean( $log_path . DS . 'obss' . DS . $addon_type . DS . $addon . DS . $file_name );

			$content = "\n--------------------\n" . $datetime . ":\n--------------------\n" . print_r( $content, true ) . "\n";
			if ( JFile::exists( $file_path ) ) {
				$oldcontent = file_get_contents( $file_path );
				$content .= $oldcontent;
			}
			JFile::write( $file_path, $content );
		}
	}
}