<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonBlocknumber extends SppagebuilderAddons{

	public function render() {

		$class  	= (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title  	= (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : '';
		$text     	= (isset($this->addon->settings->text) && $this->addon->settings->text) ? $this->addon->settings->text : '';
		$number     = (isset($this->addon->settings->number) && $this->addon->settings->number) ? $this->addon->settings->number : '';
		$alignment  = (isset($this->addon->settings->alignment) && $this->addon->settings->alignment) ? $this->addon->settings->alignment : '';
		$heading  	= (isset($this->addon->settings->heading) && $this->addon->settings->heading) ? $this->addon->settings->heading : '';

		if ($number) {
			$block_number = '<span class="sppb-blocknumber-number">' . $number . '</span>';
		}

		if($text) {
			$output  = '<div class="sppb-addon sppb-addon-blocknumber ' . $class . '">';

			if($title) {
				$output  .= '<' . $heading_selector . ' class="sppb-addon-title">' . $title .'</' . $heading_selector . '>';
			}

			$output .= '<div class="sppb-addon-content">';
			$output .= '<div class="sppb-blocknumber sppb-media">';
			if( $alignment =='center' ) {
				if ($number) {
					$output .= '<div class="sppb-text-center">'.$block_number.'</div>';
				}
				$output .= '<div class="sppb-media-body sppb-text-center">';
				if($heading) $output .= '<h3 class="sppb-media-heading">'.$heading.'</h3>';
				$output .= $text;
			} else {
				if ($number) {
					$output .= '<div class="pull-'.$alignment.'">'.$block_number.'</div>';
				}
				$output .= '<div class="sppb-media-body sppb-text-'. $alignment .'">';
				if($heading) $output .= '<h3 class="sppb-media-heading">'.$heading.'</h3>';
				$output .= $text;
			}

			$output .= '</div>'; //.sppb-media-body
			$output .= '</div>'; //.sppb-media
			$output .= '</div>'; //.sppb-addon-content
			$output .= '</div>'; //.sppb-addon-blocknumber

			return $output;
		}

		return ;
	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$number_style = '';

		//number_style
		if($this->addon->settings->size) $number_style .= 'width: ' . (int) $this->addon->settings->size . 'px; height: ' . (int) $this->addon->settings->size . 'px; line-height: ' . (int) $this->addon->settings->size . 'px;';
		if($this->addon->settings->background) $number_style .= 'background-color: ' . $this->addon->settings->background . ';';
		if($this->addon->settings->color) $number_style .= 'color: ' . $this->addon->settings->color . ';';
		if($this->addon->settings->border_radius) $number_style .= 'border-radius: ' . (int) $this->addon->settings->border_radius . 'px;';

		$css = '';

		if($number_style) {
			$css .= $addon_id . ' .sppb-blocknumber-number {';
			$css .= $number_style;
			$css .= "\n" . '}' . "\n"	;
		}

		return $css;
	}
}
