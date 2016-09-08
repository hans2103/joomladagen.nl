<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_animated_number','sp_animated_number_addon');

function sp_animated_number_addon($atts){

	extract(spAddonAtts(array(
		'number'			=> '',
		'duration'			=> '',
		'font_size'			=> '',
		'border_color' 		=> '',
		'border_width' 		=> '',
		'border_radius' 	=> '',
		'color' 			=> '',
		'background' 		=> '',
		'counter_title' 	=> '',
		'title_font_size'	=> '',
		'counter_color' 	=> '',
		'alignment'			=> '',
		'class'				=>'',
		), $atts));

	$style 			= '';
	$number_style 	= '';
	$text_style 	= '';

	if($background) $class .= $class . ' sppb-hasbg';

	if($background) $style .= 'background-color:' . $background  . ';';
	if($border_color) $style .= 'border-style:solid;border-color:' . $border_color  . ';';
	if($border_width) $style .= 'border-width:' . (int) $border_width  . 'px;';
	if($border_radius) $style .= 'border-radius:' . (int) $border_radius  . 'px;';

	if($color) $number_style .= 'color:' . $color  . ';';
	if($font_size) $number_style .= 'font-size:' . (int) $font_size . 'px;line-height:' . (int) $font_size . 'px;';

	if($counter_color) $text_style .= 'color:' . $counter_color  . ';';
	if($title_font_size) $text_style .= 'font-size:' . (int) $title_font_size . 'px;line-height:' . (int) $title_font_size . 'px;';

	$output  = '<div class="sppb-addon sppb-addon-animated-number '. $alignment . ' ' . $class .'">';

	$output .= '<div class="sppb-addon-content" style="' . $style . '">';
	$output .= '<div class="sppb-animated-number" data-digit="'. $number .'" data-duration="' . $duration . '" style="'. $number_style .'">0</div>';
	if($counter_title) $output .= '<div class="sppb-animated-number-title" style="' . $text_style . '">' . $counter_title . '</div>';
	$output .= '</div>';

	$output .= '</div>';

	return $output;
	
}