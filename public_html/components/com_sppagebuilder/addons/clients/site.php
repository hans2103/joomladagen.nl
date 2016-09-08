<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_clients','sp_clients_addon');
AddonParser::addAddon('sp_clients_item','sp_clients_item_addon');

$sppbClientsParam = array();

function sp_clients_addon($atts, $content){

	global $sppbClientsParam;

	extract(spAddonAtts(array(
		'title'					=> '',
		"heading_selector" 		=> 'h3',
		"title_fontsize" 		=> '',
		"title_fontweight" 		=> '',
		"title_text_color" 		=> '',
		"title_margin_top" 		=> '',
		"title_margin_bottom" 	=> '',
		'count'					=> '',
		'alignment'				=> '',
		"class"					=> '',
		), $atts));

	$sppbClientsParam['col'] = $count;

	$output  = '<div class="sppb-addon sppb-addon-clients ' . $alignment . ' ' . $class . '">';

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
	$output .= '<div class="sppb-row">';
	$output .= AddonParser::spDoAddon($content);
	$output .= '</div>';
	$output	.= '</div>';
	
	$output .= '</div>';

	$sppbClientsParam = array();

	return $output;

}

function sp_clients_item_addon( $atts ){

	global $sppbClientsParam;

	extract(spAddonAtts(array(
		"image"	=> '',
		"url"	=> '',
		"title"	=> '',
		), $atts));

	$output = '';

	if($image) {

		$output .= '<div class="' . $sppbClientsParam['col'] . '">';
		if($url) $output .= '<a target="_blank" href="'. $url .'">';
		$output .= '<img class="sppb-img-responsive" src="' . $image . '" alt="' . $title . '">';
		if($url) $output .= '</a>';
		$output .= '</div>';
	}

	return $output;

}