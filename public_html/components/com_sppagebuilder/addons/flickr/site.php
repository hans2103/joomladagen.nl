<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

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
		$output  = '<div class="sppb-addon sppb-addon-flickr' . $class . '">';
		$output .= ($title) ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';
		$output .= '<div class="sppb-addon-content">';
		$output .= '<ul class="sppb-flickr-gallery">';

		for ($i=0; $i < $count; $i++) {
			$output .= '<li>';
			$output .= '<a target="_blank" href="'. $images[$i]->link .'"><img class="sppb-img-responsive" src="'. $images[$i]->media->m .'" alt="'. $images[$i]->title .'"></a>';
			$output .= '</li>';
		}

		$output .= '</ul>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;

	}

	private function getImages() {

		jimport( 'joomla.filesystem.folder' );

		$cache_path = JPATH_CACHE . '/com_sppagebuilder/addons/addon-' . $this->addon->id;
		$cache_file = $cache_path . '/flickr.json';

		if(!file_exists($cache_path)) {
			JFolder::create($cache_path, 0755);
		}

		if (file_exists($cache_file) && (filemtime($cache_file) > (time() - 60 * 30 ))) {
			$images = file_get_contents($cache_file);
		} else {
			$id = (isset($this->addon->settings->id) && $this->addon->settings->id) ? $this->addon->settings->id : '35591378@N03';
			$api = 'http://api.flickr.com/services/feeds/photos_public.gne?id='. $id .'&format=json&nojsoncallback=1';
			$images = file_get_contents($api);
			file_put_contents($cache_file, $images, LOCK_EX);
		}

		$json = json_decode($images);
		if(isset($json->items)) {
			return $json->items;
		}

		return array();

	}

}
