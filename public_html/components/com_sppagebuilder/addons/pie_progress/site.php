<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonPie_progress extends SppagebuilderAddons{

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		//Options
		$percentage = (isset($this->addon->settings->percentage) && $this->addon->settings->percentage) ? $this->addon->settings->percentage : '';
		$border_color = (isset($this->addon->settings->border_color) && $this->addon->settings->border_color) ? $this->addon->settings->border_color : '#eeeeee';
		$border_active_color = (isset($this->addon->settings->border_active_color) && $this->addon->settings->border_active_color) ? $this->addon->settings->border_active_color : '';
		$border_width = (isset($this->addon->settings->border_width) && $this->addon->settings->border_width) ? $this->addon->settings->border_width : '';
		$size = (isset($this->addon->settings->size) && $this->addon->settings->size) ? $this->addon->settings->size : '';
		$icon_name = (isset($this->addon->settings->icon_name) && $this->addon->settings->icon_name) ? $this->addon->settings->icon_name : '';
		$icon_size = (isset($this->addon->settings->icon_size) && $this->addon->settings->icon_size) ? $this->addon->settings->icon_size : '';
		$text = (isset($this->addon->settings->text) && $this->addon->settings->text) ? $this->addon->settings->text : '';

		$output  = '<div class="sppb-addon sppb-addon-pie-progress '. $class .'">';
		$output .= '<div class="sppb-addon-content sppb-text-center">';
		$output .= '<div class="sppb-pie-chart" data-size="'. (int) $size .'" data-percent="'.$percentage.'" data-width="'.$border_width.'" data-barcolor="'.$border_active_color.'" data-trackcolor="'.$border_color.'">';

		if($icon_name) {
			$output .= '<div class="sppb-chart-icon"><span><i class="fa '. $icon_name . ' ' .  $icon_size .'"></i></span></div>';
		} else {
			$output .= '<div class="sppb-chart-percent"><span></span></div>';
		}

		$output .= '</div>';
		$output .= ($title) ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';
		$output .= '<div class="sppb-addon-text">';
		$output .= $text;
		$output .= '</div>';

		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	public function scripts() {
		$js[] = JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.easypiechart.min.js';
		return $js;
	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$css = '';
		$style = (isset($this->addon->settings->size) && $this->addon->settings->size) ? 'height: '. (int) $this->addon->settings->size .'px; width: '. (int) $this->addon->settings->size .'px;' : '';

		if($style) {
			$css .= $addon_id . ' .sppb-pie-chart {';
			$css .= $style;
			$css .= '}';
		}

		return $css;
	}

}
