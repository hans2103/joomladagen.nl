<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonTestimonialpro extends SppagebuilderAddons {

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$style = (isset($this->addon->settings->style) && $this->addon->settings->style) ? $this->addon->settings->style : '';

		//Options
		$autoplay = (isset($this->addon->settings->autoplay) && $this->addon->settings->autoplay) ? ' data-sppb-ride="sppb-carousel"' : '';
		$arrows = (isset($this->addon->settings->arrows) && $this->addon->settings->arrows) ? $this->addon->settings->arrows : '';
		$controls = (isset($this->addon->settings->controls) && $this->addon->settings->controls) ? $this->addon->settings->controls : 0;

		//Output
		$output  = '<div id="sppb-testimonial-pro-'. $this->addon->id .'" class="sppb-carousel sppb-testimonial-pro sppb-slide sppb-text-center' . $class . '"'. $autoplay .'>';

		if($controls) {
			$output .= '<ol class="sppb-carousel-indicators">';
			foreach ($this->addon->settings->sp_testimonialpro_item as $key1 => $value) {
				$output .= '<li data-sppb-target="#sppb-carousel-'. $this->addon->id .'" '. (($key1 == 0) ? ' class="active"': '' ) .'  data-sppb-slide-to="'. $key1 .'"></li>' . "\n";
			}
			$output .= '</ol>';
		}

		$output .= '<div class="sppb-carousel-inner">';

		foreach ($this->addon->settings->sp_testimonialpro_item as $key => $value) {
			$output   .= '<div class="sppb-item ' . (($key == 0) ? ' active' : '') .'">';
			$title = '<strong class="pro-client-name">'. $value->title .'</strong>';

			if($value->url) $title .= ' - <span class="pro-client-url">'. $value->url .'</span>';
			if($value->avatar) $output .= '<img class="sppb-img-responsive sppb-avatar '. $value->avatar_style .'" src="'. $value->avatar .'" alt="">';
			$output  .= '<div class="sppb-testimonial-message">' . $value->message . '</div>';
			if($title) $output .= '<div class="sppb-testimonial-client">' . $title . '</div>';

			$output  .= '</div>';
		}
		$output	.= '</div>';

		if($arrows) {
			$output	.= '<a href="#sppb-testimonial-pro-'. $this->addon->id .'" class="left sppb-carousel-control" data-slide="prev"><i class="fa fa-angle-left"></i></a>';
			$output	.= '<a href="#sppb-testimonial-pro-'. $this->addon->id .'" class="right sppb-carousel-control" data-slide="next"><i class="fa fa-angle-right"></i></a>';
		}

		$output .= '</div>';

		return $output;

	}
}
