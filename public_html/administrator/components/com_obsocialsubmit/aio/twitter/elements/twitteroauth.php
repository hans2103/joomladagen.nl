<?php
/**
 * @version        $Id: twitteroauth.php 462 2012-08-16 03:22:27Z phonglq $
 * @author        Phong Lo - foobla.com
 * @package        obSocialSubmit for Joomla
 * @subpackage    externTwitter addon
 * @license        GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.html.parameter' );
$ds = DIRECTORY_SEPARATOR;
//require_once JPATH_SITE.$ds.'components'.$ds.'com_obsocialsubmit'.$ds.'helpers'.$ds.'functions.php';

// if(!class_exists('TwitterOAuth')){
// 	require dirname(dirname(__FILE__)).$ds.'helpers'.$ds.'twitteroauth'.$ds.'twitteroauth.php';
// }

class JFormFieldTwitterOauth extends JFormField {
	protected $type = 'TwitterOauth';
	protected $config = null;

	protected function getInput() {
		$app = JFactory::getApplication();
		$ds  = DIRECTORY_SEPARATOR;
		$id  = JRequest::getVar( 'id' );
		if ( ! $id ) {
			$cid = JRequest::getVar( 'cid' );
			$id  = $cid[0];
		}

		$configs            = $this->getConfig();
		$ckey               = $configs->get( 'consumer_key', '' );
		$csecret            = $configs->get( 'consumer_secret', '' );
		$oauth_token        = $configs->get( 'oauth_token', '' );
		$oauth_token_secret = $configs->get( 'oauth_token_secret', '' );

		$html = '';
		if ( ! $ckey && ! $csecret ) {
			$app->enqueueMessage( JText::_( "CONNECT_WITH_TWITTER_IS_NOT_ESTABLISHED_1" ) );
			$html = JText::_( "CONNECT_WITH_TWITTER_IS_NOT_ESTABLISHED_1" );
		} elseif ( ! $ckey ) {
			$app->enqueueMessage( JText::_( "CONNECT_WITH_TWITTER_IS_NOT_ESTABLISHED_2" ) );
			$html = JText::_( "CONNECT_WITH_TWITTER_IS_NOT_ESTABLISHED_2" );
		} elseif ( ! $csecret ) {
			$app->enqueueMessage( JText::_( "CONNECT_WITH_TWITTER_IS_NOT_ESTABLISHED#" ) );
			$html = JText::_( "CONNECT_WITH_TWITTER_IS_NOT_ESTABLISHED_3" );
		} else {
			if ( ! $oauth_token || ! $oauth_token_secret ) {
				JHTML::_( "behavior.modal" );
				$connect_url = JURI::base() . "index.php?obsstask=addonfunc&addonfunc=connect&aid={$id}&atype=ex";
				$html        = '
					<div>
						<a class="btn btn-large btn-lg btn-primary" href="' . $connect_url . '">
							<i class="fa fa-twitter fa-lg"></i> ' . JText::_( 'PLG_OBSS_EXTERN_TWITTER_CONNECT_WITH_TWITTER' ) . '
						</a>
					</div>
				';
			} else {
				/* If method is set change API call made. Test is called by default. */
				if ( ! class_exists( 'tmhOAuth' ) ) {
					require_once dirname( dirname( __FILE__ ) ) . $ds . 'helpers' . $ds . 'tmhOAuth.php';
				}
				if ( ! class_exists( 'tmhUtilities' ) ) {
					require_once dirname( dirname( __FILE__ ) ) . $ds . 'helpers' . $ds . 'tmhUtilities.php';
				}
				$tmhOAuth                        = new tmhOAuth( array(
					'consumer_key'    => $ckey,
					'consumer_secret' => $csecret,
					'curl_cainfo'     => dirname( dirname( __FILE__ ) ) . '/helpers/cacert.pem',
					'curl_capath'     => dirname( dirname( __FILE__ ) ) . '/helpers',
				) );
				$tmhOAuth->config['user_token']  = $oauth_token;
				$tmhOAuth->config['user_secret'] = $oauth_token_secret;

				$code = $tmhOAuth->request( 'GET', $tmhOAuth->url( '1.1/account/verify_credentials' ) );
				if ( $code == 200 ) {
					$res      = json_decode( $tmhOAuth->response['response'] );
					$res      = is_array( $res ) ? (object) $res : $res;
					$document = JFactory::getDocument();
					$script   = "
						function disconnectTwitter(){
							document.getElementById('jform_params_oauth_token_secret').value='';
							document.getElementById('jform_params_oauth_token').value ='';
							Joomla.submitbutton('connection.apply');
						}";
					$document->addScriptDeclaration( $script );
					$html = '<div>' .
					        '<a href="http://twitter.com/' . $res->screen_name . '" target="blank">' .
					        '<img class="img-polaroid" src="' . $res->profile_image_url . '" title="' . $res->name . '"><br/>' .
					        $res->name . '</a><br/>' .
					        '<a class="btn btn-danger" href="#" onclick="disconnectTwitter();"><i class="fa fa-twitter"></i> ' . JText::_( 'PLG_OBSS_EXTERN_TWITTER_DISCONNECT' ) . '</a>' .
					        '</div>';
				} else {
					echo 'Error: ' . $tmhOAuth->response['response'] . PHP_EOL;
					tmhUtilities::pr( $tmhOAuth );
				}
			}
		}

		return $html;
	}

	/**
	 * Method to get the field options for radio buttons.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions() {
		$options = array();

		foreach ( $this->element->children() as $option ) {

			// Only add <option /> elements.
			if ( $option->getName() != 'option' ) {
				continue;
			}

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', (string) $option['value'], trim( (string) $option ), 'value', 'text',
				( (string) $option['disabled'] == 'true' )
			);

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset( $options );

		return $options;
	}

	public function getConfig() {
		if ( ! $this->config ) {
			$id  = JRequest::getVar( 'id' );
			$res = '';
			if ( $id ) {
				$db  = JFactory::getDbo();
				$sql = "SELECT `params` FROM `#__obsocialsubmit_instances` WHERE `id`={$id} AND `addon_type`='extern'";
				$db->setQuery( $sql );
				$res = $db->loadResult();
			}
			$this->config = new JRegistry( $res );
		}

		return $this->config;
	}
}