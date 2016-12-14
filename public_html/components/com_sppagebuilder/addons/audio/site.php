<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonAudio extends SppagebuilderAddons{

	public function render(){

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$style = (isset($this->addon->settings->style) && $this->addon->settings->style) ? $this->addon->settings->style : 'panel-default';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		// Addon options
		$mp3_link = (isset($this->addon->settings->mp3_link) && $this->addon->settings->mp3_link) ? $this->addon->settings->mp3_link : '';
		$ogg_link = (isset($this->addon->settings->ogg_link) && $this->addon->settings->ogg_link) ? $this->addon->settings->ogg_link : '';
		$autoplay = (isset($this->addon->settings->autoplay) && $this->addon->settings->autoplay) ? $this->addon->settings->autoplay : 0;
		$repeat = (isset($this->addon->settings->repeat) && $this->addon->settings->repeat) ? $this->addon->settings->repeat : 0;

		$output  = '<div class="sppb-addon sppb-addon-audio ' . $class . '">';

		if($title) {
			$output .= '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>';
		}

		$output .= '<div class="sppb-addon-content">';
		$output .='<audio controls '.$autoplay.' '.$repeat.'>';
		$output .='<source src="'.$mp3_link.'" type="audio/mp3">';
		$output .='<source src="'.$ogg_link.'" type="audio/ogg">';
		$output .='Your browser does not support the audio element.';
		$output .='</audio>';
		$output .= '</div>';

		$output .= '</div>';

		return $output;

	}
}
