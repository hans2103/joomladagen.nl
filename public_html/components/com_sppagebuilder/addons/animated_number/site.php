<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonAnimated_number extends SppagebuilderAddons{

	public function render() {

		$number = (isset($this->addon->settings->number) && $this->addon->settings->number) ? $this->addon->settings->number : 0;
		$duration = (isset($this->addon->settings->duration) && $this->addon->settings->duration) ? $this->addon->settings->duration : 0;
		$counter_title = (isset($this->addon->settings->counter_title) && $this->addon->settings->counter_title) ? $this->addon->settings->counter_title : '';
		$alignment = (isset($this->addon->settings->alignment) && $this->addon->settings->alignment) ? $this->addon->settings->alignment : '';
		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';

		$output  = '<div class="sppb-addon sppb-addon-animated-number '. $alignment . ' ' . $class .'">';
		$output .= '<div class="sppb-addon-content">';
		$output .= '<div class="sppb-animated-number" data-digit="'. $number .'" data-duration="' . $duration . '">0</div>';
		if($counter_title) {
			$output .= '<div class="sppb-animated-number-title">' . $counter_title . '</div>';
		}
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$number_style  = (isset($this->addon->settings->color) && $this->addon->settings->color) ? "\tcolor: " . $this->addon->settings->color  . ";\n" : '';
		$number_style .= (isset($this->addon->settings->font_size) && $this->addon->settings->font_size) ? 'font-size:' . (int) $this->addon->settings->font_size . 'px;line-height:' . (int) $this->addon->settings->font_size . 'px;' : '';
		$text_style = (isset($this->addon->settings->counter_color) && $this->addon->settings->counter_color) ? "\tcolor: " . $this->addon->settings->counter_color  . "px;\n" : '';
		$text_style .= (isset($this->addon->settings->title_font_size) && $this->addon->settings->title_font_size) ? 'font-size:' . (int) $this->addon->settings->title_font_size . 'px;line-height:' . (int) $this->addon->settings->title_font_size . 'px;': '';

		$css = '';

		if($number_style) {
			$css .= $addon_id . ' .sppb-animated-number {';
			$css .= $number_style;
			$css .= '}';
		}

		if($text_style) {
			$css .= $addon_id . ' .sppb-animated-number-title {';
			$css .= $text_style;
			$css .= '}';
		}

		return $css;
	}
}
