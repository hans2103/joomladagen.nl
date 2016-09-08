<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_pricing','sp_pricing_addon');

function sp_pricing_addon($atts) {
	extract(spAddonAtts(array(
		'title' 			=> '',
		'price' 			=> '',
		'duration' 			=> '',
		'pricing_content' 	=> '',
		'button_text' 		=> '',
		'button_url' 		=> '',
		'button_size' 		=> '',
		'button_type' 		=> '',
		'button_icon' 		=> '',
		'button_block' 		=> '',
		'alignment' 		=> '',
		'background' 		=> '',
		'color' 			=> '',
		'featured' 			=> '',
		'class' 			=> '',
		), $atts));

	$style = '';

	if($background) $style .= 'background-color:' . $background . ';border-color: ' . $background . ';';
	if($color) $style .= 'color:' . $color . ';';

	if($button_icon !='') {
		$button_text = '<i class="fa ' . $button_icon . '"></i> ' . $button_text;
	}

	$output  = '<div class="sppb-addon sppb-addon-pricing-table ' . $alignment . ' ' . $class . '">';
	
	$output .= '<div style="' . $style . '" class="sppb-pricing-box '. $featured .'">';
	$output .= '<div class="sppb-pricing-header">';
	if($title) $output .= '<div class="sppb-pricing-title">' . $title . '</div>';
	if($price) $output .= '<span class="sppb-pricing-price">' . $price . '</span>';
	if($duration) $output .= '<span class="sppb-pricing-duration">' . $duration . '</span>';
	$output .= '</div>';

	if($pricing_content) {
		$output .= '<div class="sppb-pricing-features">';
		$output .= '<ul>';

		$features = explode("\n", $pricing_content);

		foreach ($features as $feature) {
			$output .= '<li>' . $feature . '</li>';
		}
		
		$output .= '</ul>';
		$output .= '</div>';
	}

	$output .= '<div class="sppb-pricing-footer">';
	$output .= '<a href="' . $button_url . '" class="sppb-btn sppb-btn-'. $button_type .' sppb-btn-'. $button_size . ' ' . $button_block .'">'. $button_text .'</a>';
	$output .= '</div>';

	$output .= '</div>';
	
	$output .= '</div>';

	return $output;

}