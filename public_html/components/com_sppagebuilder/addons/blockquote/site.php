<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonBlockquote extends SppagebuilderAddons{
	public function render(){

		$class  = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title  = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$text   = (isset($this->addon->settings->text) && $this->addon->settings->text) ? $this->addon->settings->text : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : '';
		$footer = (isset($this->addon->settings->footer) && $this->addon->settings->footer) ? $this->addon->settings->footer : '';

		if($text) {

			$output  = '<div class="sppb-addon sppb-addon-blockquote ' . $class . '">';
			if($title) {
				$output  .= '<' . $heading_selector . ' class="sppb-addon-title">' . $title .'</' . $heading_selector . '>';
			}
			$output .= '<div class="sppb-addon-content">';
			$output .= '<div class="sppb-blockquote">';
			$output .= '<p>'. $text .'</p>';
			if($footer) {
				$output .= '<footer>'. $footer .'</footer>';
			}
			$output .= '</div>';
			$output .= '</div>';

			$output .= '</div>';

			return $output;
		}

		return ;
	}
}
