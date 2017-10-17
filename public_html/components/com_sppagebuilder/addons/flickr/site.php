<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted access');

class SppagebuilderAddonFlickr extends SppagebuilderAddons {

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? ' ' . $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
		$count = (isset($this->addon->settings->count) && $this->addon->settings->count) ? $this->addon->settings->count : 0;
		$images = $this->getImages();

		if(count($images) < $count) {
			$count = count($images);
		}

		//Output
		$output  = '<div class="sppb-addon sppb-addon-flickr ' . $class . '">';
		$output .= ($title) ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';
		$output .= '<div class="sppb-addon-content">';
		$output .= '<ul class="sppb-flickr-gallery">';

		for ($i=0; $i < $count; $i++) {
			$output .= '<li>';
			$output .= '<a target="_blank" href="'. str_replace('_m', '_b', $images[$i]->media->m) .'" class="sppb-flickr-gallery-btn">';
				$output .= '<img class="sppb-img-responsive" src="'. str_replace('_m', '_q', $images[$i]->media->m) .'" alt="'. $images[$i]->title .'">';
			$output .= '</a>';
			$output .= '</li>';
		}

		$output .= '</ul>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	public function css() {
		$addon_id    = '#sppb-addon-' . $this->addon->id;
		$thumb_per_row  = (isset($this->addon->settings->thumb_per_row) && $this->addon->settings->thumb_per_row) ? $this->addon->settings->thumb_per_row : 4;

		$width = round((100/$thumb_per_row), 2);

		$css = '';
		if($thumb_per_row) {
			$css .= $addon_id . ' .sppb-flickr-gallery li {';
			$css .= 'width:'.$width.'%;';
			$css .= 'height:auto;';
			$css .= '}';
		}

		return $css;
	}

	public function stylesheets() {
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/css/magnific-popup.css');
	}

	public function scripts() {
		return array(JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.magnific-popup.min.js');
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

		//Get protocol
		$root 		= JURI::base();
		$root 		= new JURI($root);
		$protocol 	= $root->getScheme();
		$protocol 	= ($protocol == 'https') ? 'https:' : 'http:' ;

		$cache_path = JPATH_CACHE . '/com_sppagebuilder/addons/addon-' . $this->addon->id;
		$cache_file = $cache_path . '/flickr.json';

		if(!file_exists($cache_path)) {
			JFolder::create($cache_path, 0755);
		}

		if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 60 * 30 ))) {
			$images = file_get_contents($cache_file);
		} else {
			$id = (isset($this->addon->settings->id) && $this->addon->settings->id) ? $this->addon->settings->id : '35591378@N03';
			$api = $protocol . '//api.flickr.com/services/feeds/photos_public.gne?id='. $id .'&format=json&nojsoncallback=1';

			if( ini_get('allow_url_fopen') ) {
				$images = file_get_contents($api);
				file_put_contents($cache_file, $images, LOCK_EX);
			} else {
				$images = $this->curl($api);
			}

		}
		$json = json_decode($images);
		if(isset($json->items)) {
			return $json->items;
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
