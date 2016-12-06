<?php
/**
 * @version        $Id: content.php 900 2011-07-27 08:47:50Z phonglq $
 * @author         Phong Lo - foobla.com
 * @package        obSocialSubmit for Joomla
 * @subpackage     internContent addon
 * @license        GNU/GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
require_once( JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'route.php' );

class OBSSInAddonContent extends OBSSInAddon {

	public static $_og_tags_added = false;
	public static $_obss_publish_box = false;
	public static $_connections = array();

	public function onAfterInitialise() {
		#your code at here
	}

	public function onAfterRoute() {
		#your code at here
	}

	public function onAfterDispatch() {
		#your code at here
	}

	public function onAfterRender() {
		global $isJ25;
		$option    = JRequest::getVar( 'option' );
		$task      = JRequest::getVar( 'task' );
		$view      = JRequest::getVar( 'view' );
		$layout    = JRequest::getVar( 'layout' );
		$mainframe = JFactory::getApplication();
		if ( $option == 'com_content' &&
		     ( ( $mainframe->isAdmin() && ( ( $task == 'add' || $task == 'edit' ) ) || ( $view == 'article' && $layout == 'edit' ) )
		       || ( $mainframe->isSite() && ( $view == 'form' && $layout == 'edit' ) ) )
		) {
			$id      = JRequest::getVar( 'id' );
			$configs = $this->getConfig();
			$posted  = false;
			if ( $id ) {
				$sql = "SELECT `catid` FROM `#__content` WHERE `id`=" . $id;
				$db  = JFactory::getDbo();
				$db->setQuery( $sql );
				$catid           = $db->loadResult();
				$categories      = $configs->get( 'category' );
				$categories      = is_array( $categories ) ? $categories : array( $categories );
				$include_subcats = $configs->get( 'include_subcats' );

				if ( $include_subcats ) {
					$categories = $this->getSubCategoryId( $categories );
				}

				if ( $categories && $categories[0] ) {
					if ( $categories && ! in_array( $catid, $categories ) ) {
						return;
					}
				}
				$sql = "SELECT `language` FROM `#__content` WHERE `id`=" . $id;
				$db  = JFactory::getDbo();
				$db->setQuery( $sql );
				$article_language = $db->loadResult();
				$language        = $configs->get( 'languages' );
				if( ( $language != '*' ) && ( $article_language != $language) ){
					return;
				}
				$cids = preg_split( '/,/i', $this->data->cids, null, 1 );
				if ( $cids ) {
					$sql = 'SELECT `id`
							FROM `#__obsocialsubmit_instances`
							WHERE `addon_type`="extern" and published="1" and id in(' . implode( ',', $cids ) . ')';
					$db->setQuery( $sql );
					$res = $db->loadColumn();
					if ( $res ) {
						$sql = 'SELECT `cid` '
						       . 'FROM `#__obsocialsubmit_logs` '
						       . 'WHERE `iid`=' . $id . ' AND `aid`=' . $this->data->id . ' AND `cid` IN (' . implode( ',', $res ) . ') AND `processed`=1 AND `status`=1';
						$db->setQuery( $sql );
						$posted_cids = $db->loadColumn();
						$posted      = array_diff( $res, $posted_cids ) ? false : true;
					}
				}
			}

			$publishbox = $configs->get( 'publishbox' );
			if ( ! $publishbox ) {
				return;
			}
			$publishbox_default = $configs->get( 'publishbox_default' );
			if ( $posted ) {
				$publishbox_default = 0;
			}
			$doc  = JFactory::getDocument();
			$html = JResponse::getBody();

			$jv    = new JVersion();
			$isJ25 = ( $jv->RELEASE == '2.5' );

			$search  = '';
			$replace = '';
			if ( self::$_obss_publish_box ) {
				$search = '<div id="obss_adapter_holder"></div>';
			} else {
				$result = array();
				preg_match( '/<form [^>]* name="adminForm"[^>]*>/i', $html, $result );
				if ( isset( $result[0] ) && $result[0] ) {
					$search = $result[0];
				}
				$replace .= $search . '<div id="obss_publish_box_wrap">';
			}

			if ( $search ) {
				$cids = preg_split( '/[\s,]/i', $this->data->cids, null, 1 );
				$res  = self::getConnections( $cids, $this->data->id, $id );

				$checked = ( $res->checked ) ? ' checked="checked" ' : '';
				$checked = ( ! $publishbox_default ) ? '" ' : $checked;
				$replace .= '<div class="obss_adapter_wrap">';
				$replace .= '<div><label><input type="checkbox" value="1" name="obss_publish_box' . $this->data->id . '"' . $checked . '/>&nbsp;<a href="index.php?option=com_obsocialsubmit&task=adapter.edit&id=' . $this->data->id . '"><strong>' . $this->data->title . '</strong></a></label></div>';
				$replace .= '<div class="obss_connections_wrap">';
				$replace .= $res->output;
				$replace .= '</div>';
				$replace .= '</div>';
			}

			$replace .= '<div id="obss_adapter_holder"></div>';
			if ( ! self::$_obss_publish_box ) {
				$replace .= '<div style="clear:both;"></div></div>';
				self::$_obss_publish_box = true;
			}

			$html = str_replace( $search, $replace, $html );
			JResponse::setBody( $html );
		}
	}

	public function onContentAfterSave( $context, $article, $isNew ) {
		global $isJ25;
		if ( in_array( $context, array( 'com_obsocialsubmit.adapter', 'com_obsocialsubmit.connection' ) ) ) {
			return;
		}
		if( strpos( $context, 'com_content' ) === false ){
			return;
		}
		$task = JRequest::getVar( 'task' );
		if ( $task == 'save2copy' ) {
			return;
		}

		$db                  = JFactory::getDBO();
		$configs             = $this->getConfig();
		$action              = $configs->get( 'action' );
		$publishbox          = $configs->get( 'publishbox' );
		$obss_publish_option = 0;
		$option              = JRequest::getVar( 'option' );

		if ( $publishbox && $option == 'com_content' ) {
			$obss_publish_option = JRequest::getVar( 'obss_publish_box' . $this->data->id );
			if ( ! $obss_publish_option ) {
				return;
			}
		}

		$option   = JRequest::getVar( 'option' );
		$language = $configs->get( 'languages' );

		$autoarticles    = $configs->get( 'autoarticles' );
		$publish_up      = $article->publish_up;
		$publish_down    = $article->publish_down;
		$categories      = $configs->get( 'category', array() );
		$categories      = is_array( $categories ) ? $categories : array( $categories );
		$include_subcats = $configs->get( 'include_subcats' );

		if ( $include_subcats ) {
			$categories = $this->getSubCategoryId( $categories );
		}

		if ( $isNew && $action == 'edit' ) {
			return; // chi post khi save edit
		}

		if ( ! $isNew && $action == 'new' ) {
			return; // chi post khi save new
		}

		if ( $categories && $categories[0] ) {
			if ( $categories && ! in_array( $article->catid, $categories ) ) {
				return;
			}
		}

		# filter by tags
		if ( ! $isJ25 && isset( $article->newTags ) ) {
			$tags_id = $configs->get( 'tags' );
			$tags_id = is_array( $tags_id ) ? $tags_id : array( $tags_id );
			foreach ( $tags_id as $tag_key => $tag_id ) {
				if ( ! is_numeric( $tag_id ) ) {
					unset( $tags_id[ $tag_key ] );
				}
			}
			$check_tags = array_intersect( $article->newTags, $tags_id );
			if ( count( $check_tags ) == 0 && ! empty( $tags_id ) ) {
				return;
			}
		}
		if( ( $language != '*' ) && ( $article->language != $language) ){
			return;
		}

		$afterdate = $configs->get( 'afterdate', '' );

		if ( $afterdate && $article->created < $afterdate ) {
			return;
		}

		$date_createdate = JFactory::getDate();

		$now      = $date_createdate->toSQL();
		$nullDate = $db->getNullDate();

		$post_option = $configs->get( 'post_option', 0 );

		if ( ( $now < $publish_up ) || ( $option != 'com_content' && $autoarticles ) || $post_option ) {
			if ( ! isset( $article->id ) ) {
				return;
			}
			$id         = $article->id;
			$aid        = $this->data->id;
			$cids       = preg_split( '/,/i', $this->data->cids, null, 1 );
			$cids_str   = implode( ',', $cids );
			$exist_cids = array();
			#Get existed  cids
			if ( ! empty( $cids ) && $cids_str ) {
				$sql = "SELECT * FROM `#__obsocialsubmit_logs` WHERE `aid`=" . $aid . " AND `cid` in (" . $cids_str . ") AND `iid`=" . $id;
				$db->setQuery( $sql );
				$logs           = $db->loadObjectList( 'cid' );
				$exist_cids     = array_keys( $logs );
				$exist_cids_str = implode( $exist_cids, "," );
			}
			$new_cids = array_diff( $cids, $exist_cids );
			if ( ! $publish_up ) {
				$publish_up = $now;
			}
			#UPGRADE
			if ( $exist_cids && count( $exist_cids ) && $exist_cids_str ) {
				$republish     = $configs->get( 'republish' );
				$processed_sql = $republish ? ', `processed`=0 ' : '';
				$sql           = "UPDATE `#__obsocialsubmit_logs` SET `publish_up` = '" . $publish_up . "', `status`=0 {$processed_sql} WHERE `aid`=" . $aid . " AND `iid`=" . $id . " AND `cid` IN (" . $exist_cids_str . ")";
				$db->setQuery( $sql );
				$db->query();
			}

			#INSERT NEW
			if ( ! empty( $new_cids ) ) {
				$t = implode( $new_cids );
				if ( ! $t ) {
					return;
				}
				$new_cids   = array_values( $new_cids );
				$sql        = "INSERT INTO `#__obsocialsubmit_logs`(`aid`,`iid`,`cid`,`status`,`publish_up`) VALUES";
				$values_arr = array();
				for ( $i = 0; $i < count( $new_cids ); $i ++ ) {
					$values_arr[] = "(" . $aid . "," . $id . "," . $new_cids[ $i ] . ",0,'" . $publish_up . "')";
				}
				$values_str = implode( ",\n", $values_arr );
				$sql .= $values_str;
				$db->setQuery( $sql );
				$db->query();
			}

			$mainframe = JFactory::getApplication();
			$mainframe->enqueueMessage( JText::_( 'PLG_OBSS_INTERN_CONTENT_MESSAGE_ADDED_TO_QUEUE' ) );

			return;
		} elseif ( $article->state && ( $now < $publish_down || $publish_down == $nullDate || $publish_down == '' ) ) {
			$message = $this->getPostObject( $article );
		}

		return $message;
	}

	function getPostObject( &$article, $msg = true ) {
		$configs = $this->getConfig();
		$debug   = $configs->get( 'debug' );
		$ip      = $configs->get( 'ip' );
		$realip  = $this->getRealIp();
		if ( $debug && ( $ip == $realip || ! $ip ) ) {
			ini_set( 'display_errors', 1 );
			echo '<h3>' . __LINE__ . '</h3>';
		}
		#----------------------------------------------------------------------
		# GET ROUTE URL
		#----------------------------------------------------------------------
		$db   = JFactory::getDBO();
		$slug = $article->id . ':' . $article->alias;
		$sql  = "SELECT alias from #__categories WHERE id = " . $article->catid;
		$db->setQuery( $sql );

		$cat_alias   = $db->loadResult();
		$catslug     = $article->catid . ':' . $cat_alias;
		$unroute_url = ContentHelperRoute::getArticleRoute( $slug, $catslug );
		$unroute_url = str_replace( "\n", "", $unroute_url );
		if ( $debug && ( $ip == $realip || ! $ip ) ) {
			echo '<br/>' . $unroute_url;
			echo '<br/>' . base64_encode( $unroute_url );
			echo '<br/>' . JURI::root() . 'index.php?obsstask=getsef&url=' . base64_encode( $unroute_url );
		}

		$url_root = JURI::root();

		$language = $article->language;
		if ( $language != '*' ) {
			#todo: get shortcode of language
			$sql = "SELECT `sef` FROM `#__languages` WHERE `lang_code` LIKE '" . $language . "'";
			$db  =& JFactory::getDbo();
			$db->setQuery( $sql );
			$shortcode = $db->loadResult();
		}
		if ( isset( $shortcode ) && $shortcode ) {
			$unroute_url .= '&lang=' . $shortcode;
		}

		if ( $debug && ( $ip == $realip || ! $ip ) ) {
			echo '<br/>' . $unroute_url;
			echo '<br/>' . base64_encode( $unroute_url );
			echo '<br/>' . JURI::root() . 'index.php?obsstask=getsef&url=' . base64_encode( $unroute_url );
		}

		$link    = $this->route( $unroute_url );
		$is_link = preg_match( '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $link );
		if ( ! $is_link ) {
			$link = $url_root . $unroute_url;
		}
		$sefurl = $configs->get( 'sefurl' );
		if ( ! $sefurl ) {
			$link = $url_root . $unroute_url;
		}

		#----------------------------------------------------------------------
		# END: GET ROUTE URL
		#----------------------------------------------------------------------
		$title = $article->title;


		#-------------------------------------------------------
		# Get first img in article
		#-------------------------------------------------------
		$text = $article->introtext . $article->fulltext;


		$images = json_decode( $article->images, true );
		$img    = '';
		if ( $images && is_array( $images ) ) {
			if ( array_key_exists( 'image_intro', $images ) && $images['image_intro'] ) {
				$img = $images['image_intro'];
			} elseif ( array_key_exists( 'image_fulltext', $images ) && $images['image_fulltext'] ) {
				$img = $images['image_fulltext'];
			}
		}
		if ( $img == '' ) {
			$img = $this->getFirstImage( $text );
		}

		# END: Get first img in article
		/*if($img)
		if( $debug && ( $ip == $realip || !$ip) ){
			echo '<h3>'.__LINE__.'</h3>';
		}*/


		#-------------------------------------------------------
		# Create shorturl
		#-------------------------------------------------------
		$message  = $template = $configs->get( 'template', 'New article: [title] read more here [shorturl]' );
		$shorturl = '';
		if ( $msg ) {
			preg_match( '/\[shorturl\]/i', $template, $tag_shorturl );
			if ( isset( $tag_shorturl[0] ) && $tag_shorturl[0] == '[shorturl]' ) {
				$shorturl = $this->shortUrl( $link );
			}
		}
		# END: Create shorturl
		if ( $debug && ( $ip == $realip || ! $ip ) ) {

		}

		#-------------------------------------------------------
		# Create youtube link
		#-------------------------------------------------------
		$youtube = $this->getYoutubeLink( $text );
		$message = str_replace( '[youtube]', $youtube, $message );

		#-------------------------------------------------------
		# Create post object
		#-------------------------------------------------------
		$introtext = strip_tags( $article->introtext );
		preg_match( '/\[introtext:(\d+)\]/i', $template, $tintrotext );

		if ( isset( $tintrotext[0] ) && isset( $tintrotext[1] ) ) {
			if ( $tintrotext[1] < strlen( $introtext ) ) {
				$message = str_replace( $tintrotext[0], substr( $introtext, 0, $tintrotext[1] ), $message );
			} else {
				$message = str_replace( $tintrotext[0], $introtext, $message );
			}
		}

		$fulltext = strip_tags( $article->fulltext );
		preg_match( '/\[fulltext:(\d+)\]/i', $template, $tfulltext );

		if ( isset( $tfulltext[0] ) && isset( $tfulltext[1] ) ) {
			if ( $tfulltext[1] < strlen( $fulltext ) ) {
				$message = str_replace( $tfulltext[0], substr( $fulltext, 0, $tfulltext[1] ), $message );
			} else {
				$message = str_replace( $tfulltext[0], $fulltext, $message );
			}
		}

		#TODO: select user name, name
		$message = str_replace( "[title]", $title, $message );
		$message = str_replace( "[shorturl]", $shorturl, $message );
		$message = str_replace( "[url]", $link, $message );
		$message = str_replace( "[introtext]", $introtext, $message );
		$message = str_replace( '[fulltext]', $article->fulltext, $message );

		$desc_template = $configs->get( 'desc_template', '[introtext]' );
		$desc          = str_replace( '[introtext]', $article->introtext, $desc_template );
		$desc          = str_replace( "[title]", $title, $desc );
		$desc          = str_replace( "[shorturl]", $shorturl, $desc );
		$desc          = str_replace( "[url]", $link, $desc );
		$desc          = str_replace( '[fulltext]', $article->fulltext, $desc );

		$sql = "SELECT `name`, `username`, `email` FROM `#__users` WHERE `id`=" . $article->created_by;
		$db->setQuery( $sql );
		$user_info = $db->loadObject();

		if ( $user_info ) {
			if ( $article->created_by_alias ) {
				$message = str_replace( "[author]", $article->created_by_alias, $message );
				$desc    = str_replace( "[author]", $article->created_by_alias, $desc );
			} else {
				$message = str_replace( "[author]", $user_info->name, $message );
				$desc    = str_replace( "[author]", $user_info->name, $desc );
			}
			$message = str_replace( "[username]", $user_info->name, $message );
			$desc    = str_replace( "[username]", $user_info->name, $desc );
		}

		preg_match( '/\[introtext:(\d+)\]/i', $desc, $tdesc );
		if ( isset( $tdesc[0] ) && isset( $tdesc[1] ) ) {
			if ( $tdesc[1] < strlen( $fulltext ) ) {
				$desc = str_replace( $tdesc[0], substr( $introtext, 0, $tdesc[1] ), $desc );
			} else {
				$desc = str_replace( $tdesc[0], $introtext, $desc );
			}
		}

		preg_match( '/\[fulltext:(\d+)\]/i', $desc, $tdesc );
		if ( isset( $tdesc[0] ) && isset( $tdesc[1] ) ) {
			if ( $tdesc[1] < strlen( $fulltext ) ) {
				$desc = str_replace( $tdesc[0], substr( $fulltext, 0, $tdesc[1] ), $desc );
			} else {
				$desc = str_replace( $tdesc[0], $fulltext, $desc );
			}
		}


		$post_obj              = new stdClass();
		$post_obj->aid         = $this->data->id;
		$post_obj->iid         = $article->id;
		$post_obj->url         = $link;
		$post_obj->shorturl    = $shorturl;
		$post_obj->title       = $title;
		$post_obj->message     = $message;
		$post_obj->description = $desc;
		$post_obj->publish_up  = strtotime( $article->publish_up );
		if ( ! $img ) {
			$default_img = $configs->get( 'default_img' );
			if ( $default_img ) {
				$img = $default_img;
			}
		}
		$parse_url = parse_url( $img );
		if ( ! key_exists( 'scheme', $parse_url ) && ! key_exists( 'host', $parse_url ) && $img != '' ) {
			$img = JURI::root() . trim( $img, "/ " );
		}
		$post_obj->img      = $img;
		$post_obj->template = $template;

		if ( $debug && ( $ip == $realip || ! $ip ) ) {
			echo '<pre>' . print_r( $article, true ) . '</pre>';
			echo '<hr/>';
			echo $unroute_url . '<br/>';
			echo $link;
			echo '<hr/>';
			echo $link;
			echo '<hr/>';
			echo '<pre>' . print_r( $post_obj, true ) . '</pre>';
			exit();
		}

		# END: Create post object
		return $post_obj;
	}

	function getArticle( &$article ) {
		$configs     = $this->getConfig();
		$language_id = $configs->get( 'languages' );
		if ( ! $language_id ) {
			return $article;
		}
		$sql = "SELECT * FROM `#__jf_content` WHERE `published`=1 AND `reference_table`='content' AND `language_id`=" . $language_id . " AND `reference_id`=" . $article->id;
		$db  = &JFactory::getDbo();
		$db->setQuery( $sql );
		$res = $db->loadAssocList( 'reference_field' );
		if ( ! $res ) {
			return $article;
		}

		foreach ( $res as $field ) {
			$article->$field['reference_field'] = $field['value'];
		}

		return $article;
	}
	/*
		public function onContentBeforeSave( $context, $row ){
			if($row->published) return;
			$obss_publish_option = &JRequest::getVar('obss_publish_option');
			if(!$obss_publish_option) return true;
			$mainframe 	= &JFactory::getApplication();
			$configs 	= $this->getConfig();
			$action 	= $configs->get('action');
			if($action != 'published') return;
			$categories = $configs->get( 'category', array(0) );
			$categories = is_array( $categories ) ? $categories : array($categories);
			if( !in_array($row->catid, $categories ) ) {
				return;
			}
			$mainframe->setUserState( 'addon_'.$this->data->id.'_content_item_id_'.$row->id, 0 );
			return true;
		}
	*/
	/**
	 *
	 * Add open graph tas in to article page
	 *
	 * @param unknown_type $post_obj
	 */
	public function addOpenGraphTags( $post_obj ) {
		$app = JFactory::getApplication();
		JFilterOutput::objectHTMLSafe( $post_obj );
		$customtag = '';
		$customtag .= '<meta property="og:type" content="article"/>' . "\n";
		$desc   = strip_tags( $post_obj->description );
		$length = ( strlen( strip_tags( $post_obj->description ) ) > 1000 ) ? 1000 :
			$customtag .= '<meta property="og:description" content="' . substr( str_replace( '"', '&quot;', strip_tags( $post_obj->description ) ), 0, 1000 ) . '"/>' . "\n";
		$customtag .= '<meta property="og:title" content="' . $post_obj->title . '"/>' . "\n";
		if ( isset( $post_obj->img ) ) {
			$customtag .= '<meta property="og:image" content="' . $post_obj->img . '"/>' . "\n";
		}
		$uri        = JFactory::getURI();
		$currenturl = $uri->toString();
		$currenturl = str_replace( '&amp;', '&', $currenturl );
		$customtag .= '<meta property="og:url" content="' . $currenturl . '"/>' . "\n";
		$doc = JFactory::getDocument();
		$doc->addCustomTag( $customtag );
	}

	function onContentAfterDisplay( $context, $article, $params, $limitstart = 0 ) {
		$view = JRequest::getVar( 'view' );
		if ( $view != 'article' ) {
			return '';
		}
		$configs           = $this->getConfig();
		$opengraphprotocol = $configs->get( 'opengraphprotocol', 0 );

		if ( ! $opengraphprotocol ) {
			return '';
		}

		$categories = $configs->get( 'category' );
		if ( $categories ) {
			$categories      = is_array( $categories ) ? $categories : array( $categories );
			$include_subcats = $configs->get( 'include_subcats' );

			if ( $include_subcats ) {
				$categories = $this->getSubCategoryId( $categories );
			}
			if ( $categories && ! in_array( $article->catid, $categories ) ) {
				return;
			}
		}

		$mainframe = JFactory::getApplication();
		if ( ! self::$_og_tags_added ) {
			$post_obj = $this->getPostObject( $article, false );
			if ( $post_obj ) {
				$this->addOpenGraphTags( $post_obj );
				self::$_og_tags_added = true;
			}
		}

		return '';
	}


	public function getPostObjecByItemId( $id, $msg = 1 ) {
		return $this->getPostObjectByItemId( $id, $msg );
	}


	public function getPostObjectByItemId( $id, $msg = 1 ) {
		$sql = "
			SELECT
				`c`.*,
				CONCAT(`c`.`id`,'-',`c`.`alias`) AS `slug`,
				CONCAT(`cat`.`id`, '-', `cat`.`alias`) AS `catslug`
			FROM
				`#__content` AS c INNER JOIN `#__categories` AS `cat` ON `c`.`catid` = `cat`.`id`
			WHERE
				`c`.`id`=$id AND `c`.`state` = 1";
		$db  = JFactory::getDbo();
		$db->setQuery( $sql );
		$article = $db->loadObject();
		if ( ! $article ) {
			return;
		}
		$post_object = $this->getPostObject( $article, $msg );

		return $post_object;
	}


	public function getYoutubeLink( $text ) {
		#http://youtu.be/WvovjJKiVoM?a
		$pattern = '/\{youtube\}([^\{]+)\{\/youtube\}/i';
		preg_match( $pattern, $text, $result );
		if ( isset( $result[1] ) ) {
			return 'http://youtu.be/' . $result[1] . '?a';
		}

		return '';
	}


	public static function getItemTitle( $item_id ) {
		if ( ! isset( self::$_item_title[ $item_id ] ) ) {
			$db  = JFactory::getDbo();
			$sql = "SELECT `title` FROM `#__content` WHERE `id`={$item_id}";
			$db->setQuery( $sql );
			$res                           = $db->loadResult();
			$res                           = strlen( $res ) >= 25 ? substr( $res, 0, 25 ) . '...' : $res;
			$title                         = '#' . $item_id . ' - <a targe="blank" href="index.php?option=com_content&task=article.edit&id=' . $item_id . '">' . $res . '</a>';
			self::$_item_title[ $item_id ] = $title;
		}

		return self::$_item_title[ $item_id ];
	}


	public function getSubCategoryId( $catids = array() ) {
		$db = JFactory::getDbo();
		if ( ! empty( $catids ) ) {
			$catids_str = implode( ',', $catids );
			$sql        = 'SELECT `path` FROM `#__categories` WHERE `id` IN (' . $catids_str . ')';
			$db->setQuery( $sql );
			$paths = $db->loadColumn();
			if ( ! empty( $paths ) ) {
				$sql_path = '( `path` LIKE "' . implode( '/%" OR `path` LIKE "', $paths ) . '/%" )';
				$sql      = 'SELECT `id` FROM `#__categories` where `extension`="com_content" AND ' . $sql_path . ' order by `lft`;';
				$db->setQuery( $sql );
				$subcatids = $db->loadColumn();
				$catids    = array_unique( array_merge( $catids, $subcatids ) );
			}
		}

		return $catids;
	}


	public static function getConnections( $cids, $aid, $iid ) {

		$output = '';
		$db     = JFactory::getDbo();
		if ( ! $aid || ! $cids ) {
			return false;
		}
		if ( $iid ) {
			$sql = 'SELECT `i`.*,`l`.`status`
					FROM `#__obsocialsubmit_instances` as `i`
						LEFT join `#__obsocialsubmit_logs` as `l`
							on (`i`.`id` = `l`.`cid` AND `l`.`aid`=' . $aid . ' and `l`.`iid`=' . $iid . ')
					WHERE `i`.`id` in (' . implode( ',', $cids ) . ') AND `i`.`addon_type`="extern" AND `i`.`published`=1';
		} else {
			$sql = 'SELECT `i`.*, 0 AS `status`
					FROM `#__obsocialsubmit_instances` as `i`
					WHERE `i`.`id` in (' . implode( ',', $cids ) . ') AND `i`.`addon_type`="extern" AND `i`.`published`=1';
		}
		$db->setQuery( $sql );
		$objects     = $db->loadObjectList();
		$count       = count( $objects );
		$count_check = 0;
		if ( ! empty( $objects ) ) {
			foreach ( $objects as $object ) {
				if ( $object->status ) {
					$checked = ' checked="checked" ';
				} else {
					$checked = '';
					$count_check ++;
				}
				$output .= '<div><label><input type="checkbox" disabled="disabled" name="obss_adapter_' . $aid . '" value="' . $object->id . '" ' . $checked . '>&nbsp;' . $object->title . '</label></div>';
			}
		}
		$return          = new stdClass();
		$return->checked = ( $count_check > 0 );
		$return->output  = $output;

		return $return;
	}
}
