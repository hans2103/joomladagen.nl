<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_button_group','sp_button_group_addon');
AddonParser::addAddon('sp_button_group_item','sp_button_group_item_addon');

$sppbButtonGroup_margin = '';
function sp_button_group_addon($atts, $content){
	global $sppbButtonGroup_margin;

	extract(spAddonAtts(array(
		'alignment'	=>'',
		'margin'	=>'',
		'class'		=>''
		), $atts));

	$sppbButtonGroup_margin = $margin;

	$output  = '<div class="sppb-addon sppb-addon-button-group ' . $alignment . ' ' . $class . '">';
	$output .= '<div class="sppb-addon-content" style="margin:-' . (int) $margin . 'px;">';

	$output .= AddonParser::spDoAddon($content);

	$output .= '</div>';
	$output .= '</div>';

	$sppbButtonGroup_margin = '';

	return $output;	
}


function sp_button_group_item_addon($atts){

	global $sppbButtonGroup_margin;

	extract(spAddonAtts(array(
		"title" => '',
		"url" => '',
		"size" => '',
		"type"=>'',
		"icon"=>'',
		"target"=>'',
		"class"=>''
		), $atts));

	if($icon !='') {
		$title = '<i class="fa ' . $icon . '"></i> ' . $title;
	}

	$output  = '<a target="' . $target . '" href="' . $url . '" class="sppb-btn sppb-btn-' . $type . ' sppb-btn-' . $size . $class . '" style="margin:'. (int) $sppbButtonGroup_margin .'px;" role="button">' . $title . '</a>';

	return $output;
	
}