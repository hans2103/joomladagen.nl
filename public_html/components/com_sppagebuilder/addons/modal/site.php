<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_modal','sp_modal_addon');

function sp_modal_addon($atts) {
	extract(spAddonAtts(array(
		'modal_selector' 				=> '',
		'button_text' 					=> '',
		'button_size' 					=> '',
		'button_type' 					=> '',
		'button_icon' 					=> '',
		'button_block' 					=> '',
		'selector_image' 				=> '',
		'selector_icon_name'			=> '',
		'selector_icon_size'			=> '',
		'selector_icon_color'			=> '',
		'selector_icon_background'		=> '',
		'selector_icon_border_color'	=> '',
		'selector_icon_border_width'	=> '',
		'selector_icon_border_radius'	=> '',
		'selector_icon_padding'			=> '',
		'selector_margin_top'			=> '',
		'selector_margin_bottom'		=> '',
		'alignment' 					=> '',
		'modal_unique_id' 				=> 'mymodal',
		'modal_content_type' 			=> 'text',
		'modal_content_text' 			=> '',
		'modal_content_image' 			=> '',
		'modal_content_video_url' 		=> '',
		'modal_popup_width' 			=> '',
		'modal_popup_height' 			=> '',
		'class' 						=> '',
		), $atts));

	if ( $modal_content_type == 'text' ) {
		$mfg_type = 'inline';
	} else if ( $modal_content_type == 'video' ) {
		$mfg_type = 'iframe';
	} else if ( $modal_content_type == 'image' ) {
		$mfg_type = 'image';
	}

	$doc = JFactory::getDocument();
	$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/magnific-popup.css');
	$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.magnific-popup.min.js');
	$doc->addScriptdeclaration('jQuery(function($){
		$(document).ready(function(){
    		$(".mymodal").magnificPopup({
				  type: "'. $mfg_type .'",
				  mainClass: "mfp-no-margins"
				});
  		});
	})');

	$output = '';
	$selector_style = '';
	if($selector_margin_top != '') $selector_style .= 'margin-top:' . (int) $selector_margin_top . 'px;';
	if($selector_margin_bottom != '') $selector_style .= 'margin-bottom:' . (int) $selector_margin_bottom . 'px;';

	if($modal_content_type == 'text') {
		$url = '#' . $modal_unique_id;
		
		$output .= '<div id="' . $modal_unique_id . '" class="mfp-hide white-popup-block">';
		$output .= $modal_content_text;
		$output .= '</div>';

	} else if( $modal_content_type == 'video') {
		$url = $modal_content_video_url;
	} else {
		$url = $modal_content_image;
	}


	$output .= '<div class="' . $class . ' ' . $alignment . '">';

	if($modal_selector=='image') {
		if($selector_image) $output .= '<a class="sppb-modal-selector mymodal" href="'. $url . '" id="'. $modal_unique_id .'" style="' . $selector_style . '"><img src="' . $selector_image . '" alt=""></a>';
	} else if ($modal_selector=='icon') {
		if($selector_icon_name) {
			
			$selector_font_size  = '';
			$selector_icon_style = $selector_style;
			if($selector_icon_padding) $selector_icon_style .= 'padding:' . (int) $selector_icon_padding  . 'px;';
			if($selector_icon_color) $selector_icon_style .= 'color:' . $selector_icon_color  . ';';
			if($selector_icon_background) $selector_icon_style .= 'background-color:' . $selector_icon_background  . ';';
			if($selector_icon_border_color) $selector_icon_style .= 'border-style:solid;border-color:' . $selector_icon_border_color  . ';';
			if($selector_icon_border_width) $selector_icon_style .= 'border-width:' . (int) $selector_icon_border_width  . 'px;';
			if($selector_icon_border_radius) $selector_icon_style .= 'border-radius:' . (int) $selector_icon_border_radius  . 'px;';

			if($selector_icon_size) $selector_font_size = 'font-size:' . (int) $selector_icon_size . 'px;width:' . (int) $selector_icon_size . 'px;height:' . (int) $selector_icon_size . 'px;line-height:' . (int) $selector_icon_size . 'px;';

			$output  .= '<a class="sppb-modal-selector mymodal" href="'. $url . '" id="'. $modal_unique_id .'">';
			$output  .= '<span style="display:inline-block;line-height:1;' . $selector_icon_style . '">';
			$output  .= '<i class="fa ' . $selector_icon_name . '" style="' . $selector_font_size . '"></i>';
			$output  .= '</span>';
			$output  .= '</a>';

		}
	} else {

		if($button_icon !='') {
			$button_text = '<i class="fa ' . $button_icon . '"></i> ' . $button_text;
		}

		$output .= '<a class="sppb-btn mymodal sppb-btn-'. $button_type .' sppb-btn-'. $button_size . ' ' . $button_block .' sppb-modal-selector" href="'. $url . '" id="'. $modal_unique_id .'" style="' . $selector_style . '">'. $button_text .'</a>';
	}

	$output .= '</div>';

	return $output;

}