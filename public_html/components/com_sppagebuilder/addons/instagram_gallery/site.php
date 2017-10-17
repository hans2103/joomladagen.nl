<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

//no direct accees
defined('_JEXEC') or die('restricted access');

class SppagebuilderAddonInstagram_gallery extends SppagebuilderAddons {
    public static $assets = array();

    public function render() {
        $class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
        $heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
        $title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
        $count = (isset($this->addon->settings->limit) && $this->addon->settings->limit) ? $this->addon->settings->limit : 0;

        $output = '';
        $output .= '<div class="sppb-addon sppb-addon-instagram-gallery ' . $class . '">';
          $output .= '<div class="sppb-addon-instagram-text-wrap">';
      			$output .= ($title) ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';
		      $output .= '</div>'; //.sppb-addon-instagram-text-wrap

          $items = $this->getImages();

          if ( !$items ) {
            $output .= '<p class="alert alert-warning">' . JText::_('COM_SPPAGEBUILDER_ADDON_INSTAGRAM_ERORR') . '</p>';
          } else {
            $output .= '<ul class="sppb-instagram-images">'; //.sppb-addon-instagram-gallery
              for ($i=0; $i < $count; $i++) {
                $output .= '<li>';
                  $output .= ($items[$i]->images->standard_resolution->url) ? '<a class="sppb-instagram-gallery-btn" href="' .  $items[$i]->images->standard_resolution->url . '">' : '';
                     $output .= '<img class="instagram-image sppb-img-responsive" src="' . $items[$i]->images->standard_resolution->url . '" alt="">';
                  $output .= ($items[$i]->images->standard_resolution->url) ? '</a>' : '';
                $output .= '</li>';
              }
            $output .= '</ul>'; //.sppb-addon-instagram-gallery
          }

        $output .= '</div>'; //.sppb-addon-instagram-gallery
        return $output;
    }

    public function stylesheets() {
  		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/magnific-popup.css');
  	}

  	public function scripts() {
  		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.magnific-popup.min.js');
  	}

    public function css() {
  		$addon_id    = '#sppb-addon-' . $this->addon->id;
  		$thumb_per_row  = (isset($this->addon->settings->thumb_per_row) && $this->addon->settings->thumb_per_row) ? $this->addon->settings->thumb_per_row : 4;

      $width = round((100/$thumb_per_row), 2);

  		$css = '';
  		if($thumb_per_row) {
  			$css .= $addon_id . ' .sppb-instagram-images li {';
  			$css .= 'width:'.$width.'%;';
  			$css .= 'height:auto;';
  			$css .= '}';
  		}

  		return $css;
  	}

    public function js() {
      $addon_id = '#sppb-addon-' . $this->addon->id;
  		$js ='jQuery(function($){
  			$("'.$addon_id.' ul li").magnificPopup({
  				delegate: "a",
  				type: "image",
  				mainClass: "mfp-no-margins mfp-with-zoom",
  				gallery:{
  					enabled:true
  				},
  				image: {
  					verticalFit: true
  				},
  				zoom: {
  					enabled: true,
  					duration: 300
  				}
  			});
  		})';

  		return $js;
  	}

    private function getImages() {
  		jimport( 'joomla.filesystem.folder' );
  		$cache_path = JPATH_CACHE . '/com_sppagebuilder/addons/addon-' . $this->addon->id;
  		$cache_file = $cache_path . '/instagram.json';

      if(!file_exists($cache_path)) {
  			JFolder::create($cache_path, 0755);
  		}

      if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 60 * 30 ))) {
  			$images = file_get_contents($cache_file);
  		} else {
        $user_id      = (isset($this->addon->settings->user_id) && $this->addon->settings->user_id) ? $this->addon->settings->user_id : '1369270727';
        $access_token = (isset($this->addon->settings->access_token) && $this->addon->settings->access_token) ? $this->addon->settings->access_token : '1369270727.1677ed0.c9ccdebc98a9451695e9986d456a12e3';
        $limit        = (isset($this->addon->settings->limit) && $this->addon->settings->limit) ? $this->addon->settings->limit : '3';
        if (!$user_id || !$access_token) {
          echo '<p class="alert alert-warning">' . JText::_('COM_SPPAGEBUILDER_ADDON_INSTAGRAM_ERROR') . '</p>';
          return;
        }
  			$api = "https://api.instagram.com/v1/users/". $user_id  ."/media/recent/?access_token=" . $access_token . "&count=". $limit;
        if( ini_get('allow_url_fopen') ) {
  				$images = file_get_contents($api);
  				file_put_contents($cache_file, $images, LOCK_EX);
  			} else {
  				$images = $this->curl($api);
  			}
  		}
      $json = json_decode($images);
  		if(isset($json->data)) {
  			return $json->data;
  		}

  		return array();
  	}

    function curl($url) {
  	    $ch = curl_init();
  	    curl_setopt($ch, CURLOPT_URL, $url);
  	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  	    $data = curl_exec($ch);
  	    curl_close($ch);
  	    return $data;
  	}

}
