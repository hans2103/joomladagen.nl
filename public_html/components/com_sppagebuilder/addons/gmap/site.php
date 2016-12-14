<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonGmap extends SppagebuilderAddons {

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		//Options
		$map = (isset($this->addon->settings->map) && $this->addon->settings->map) ? $this->addon->settings->map : '';
		$gmap_api = (isset($this->addon->settings->gmap_api) && $this->addon->settings->gmap_api) ? $this->addon->settings->gmap_api : '';
		$type = (isset($this->addon->settings->type) && $this->addon->settings->type) ? $this->addon->settings->type : '';
		$zoom = (isset($this->addon->settings->zoom) && $this->addon->settings->zoom) ? $this->addon->settings->zoom : '';
		$mousescroll = (isset($this->addon->settings->mousescroll) && $this->addon->settings->mousescroll) ? $this->addon->settings->mousescroll : '';

		if($map) {
			$map = explode(',', $map);
			$output  = '<div id="sppb-addon-map-'. $this->addon->id .'" class="sppb-addon sppb-addon-gmap ' . $class . '">';
			$output .= ($title) ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';
			$output .= '<div class="sppb-addon-content">';
			$output .= '<div id="sppb-addon-gmap-'. $this->addon->id .'" class="sppb-addon-gmap-canvas" data-lat="' . trim($map[0]) . '" data-lng="' . trim($map[1]) . '" data-maptype="' . $type . '" data-mapzoom="' . $zoom . '" data-mousescroll="' . $mousescroll . '"></div>';
			$output .= '</div>';
			$output .= '</div>';
			return $output;

		}

		return;
	}

	public function scripts() {

		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_sppagebuilder');
		$gmap_api = $params->get('gmap_api', '');

		return array(
			'//maps.googleapis.com/maps/api/js?key='. $gmap_api,
			JURI::base(true) . '/components/com_sppagebuilder/assets/js/gmap.js'
		);
	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$height = (isset($this->addon->settings->height) && $this->addon->settings->height) ? $this->addon->settings->height : 0;

		$css = '';
		if($height) {
			$css .= $addon_id . ' .sppb-addon-gmap-canvas {';
			$css .= 'height:' . (int) $height . 'px;';
			$css .= '}';
		}

		return $css;
	}
}
