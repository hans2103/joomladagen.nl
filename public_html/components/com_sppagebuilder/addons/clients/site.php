<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonClients extends SppagebuilderAddons {

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$alignment = (isset($this->addon->settings->alignment) && $this->addon->settings->alignment) ? $this->addon->settings->alignment : '';
		$columns = (isset($this->addon->settings->count) && $this->addon->settings->count) ? $this->addon->settings->count : 2;
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		$output   = '';
		$output  = '<div class="sppb-addon sppb-addon-clients ' . $alignment . ' ' . $class . '">';

		if($title) {
			$output .= '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>';
		}

		$output .= '<div class="sppb-addon-content">';
		$output .= '<div class="sppb-row">';

		foreach ($this->addon->settings->sp_clients_item as $key => $value) {
			if($value->image) {
				$output .= '<div class="' . $columns . '">';
				if($value->url) $output .= '<a target="_blank" href="'. $value->url .'">';
				$output .= '<img class="sppb-img-responsive" src="' . $value->image . '" alt="' . $value->title . '">';
				if($value->url) $output .= '</a>';
				$output .= '</div>';
			}
		}

		$output  .= '</div>';
		$output  .= '</div>';
		$output  .= '</div>';

		return $output;
	}
}
