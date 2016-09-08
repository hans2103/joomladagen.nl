<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_blocknumber','sp_blocknumber_addon');

function sp_blocknumber_addon($atts){

	extract(spAddonAtts(array(
		"title"					=> '',
		"heading_selector" 		=> 'h3',
		"title_fontsize" 		=> '',
		"title_fontweight" 		=> '',
		"title_text_color" 		=> '',
		"title_margin_top" 		=> '',
		"title_margin_bottom" 	=> '',	
		"heading"				=> '',
		"text"					=> '',
		"number"				=> '',
		"size"					=> '',
		"background"			=> '',
		"color"					=> '',
		"border_radius"			=> '',
		"alignment"				=> '',
		'class'					=> '',
		), $atts));

	$style = '';
	$block_number = '';

	if($number) {
		if($size) $style .= 'width: ' . (int) $size . 'px; height: ' . (int) $size . 'px; line-height: ' . (int) $size . 'px;';
		if($background) $style .= 'background-color: ' . $background . ';';
		if($color) $style .= 'color: ' . $color . ';';
		if($border_radius) $style .= 'border-radius: ' . (int) $border_radius . 'px;';
		$block_number .= '<span class="sppb-blocknumber-number" style="'. $style .'">' . $number . '</span>';
	}

	if($text) {
		$output  = '<div class="sppb-addon sppb-addon-blocknumber ' . $class . '">';

		if($title) {

			$title_style = '';
			if($title_margin_top !='') $title_style .= 'margin-top:' . (int) $title_margin_top . 'px;';
			if($title_margin_bottom !='') $title_style .= 'margin-bottom:' . (int) $title_margin_bottom . 'px;';
			if($title_text_color) $title_style .= 'color:' . $title_text_color  . ';';
			if($title_fontsize) $title_style .= 'font-size:'.$title_fontsize.'px;line-height:'.$title_fontsize.'px;';
			if($title_fontweight) $title_style .= 'font-weight:'.$title_fontweight.';';

			$output .= '<'.$heading_selector.' class="sppb-addon-title" style="' . $title_style . '">' . $title . '</'.$heading_selector.'>';
		}

		$output .= '<div class="sppb-addon-content">';
		$output .= '<div class="sppb-blocknumber sppb-media">';
		if( $alignment=='center' ) {
			$output .= '<div class="sppb-text-center">'.$block_number.'</div>';
			$output .= '<div class="sppb-media-body sppb-text-center">';
			if($heading) $output .= '<h3 class="sppb-media-heading">'.$heading.'</h3>';
			$output .= $text;
		} else {
			$output .= '<div class="pull-'.$alignment.'">'.$block_number.'</div>';
			$output .= '<div class="sppb-media-body sppb-text-'. $alignment .'">';
			if($heading) $output .= '<h3 class="sppb-media-heading">'.$heading.'</h3>';
			$output .= $text;
		}

		$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';

		$output .= '</div>';

		return $output;
	}

	return;
	
}