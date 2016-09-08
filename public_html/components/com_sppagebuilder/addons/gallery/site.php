<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_gallery','sp_gallery_addon');
AddonParser::addAddon('sp_gallery_item','sp_gallery_item_addon');

$sppbGalleryParam = array();

function sp_gallery_addon($atts, $content){

	global $sppbGalleryParam;

	$doc = JFactory::getDocument();
	$doc->addStylesheet( JURI::base(true) . '/components/com_sppagebuilder/assets/css/magnific-popup.css');
	$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.magnific-popup.min.js');
	$doc->addScriptdeclaration('jQuery(function($){
		$(document).ready(function(){
    		$(".sppb-gallery-btn").magnificPopup({
				  type: "image",
				  mainClass: "mfp-no-margins mfp-with-zoom",
				  gallery:{
				    enabled:true
				  },
				  image: {
				  	verticalFit: true
				  },
				  zoom: {
				  	enabled: true,
				  	duration: 300 
				  }
				});
  		});
	})');

	extract(spAddonAtts(array(
		'title'					=> '',
		"heading_selector" 		=> 'h3',
		"title_fontsize" 		=> '',
		"title_fontweight" 		=> '',
		"title_text_color" 		=> '',
		"title_margin_top" 		=> '',
		"title_margin_bottom" 	=> '',		
		'width'					=> '',
		'height'				=> '',
		"class"					=> '',
		), $atts));

	if($width=='') {
		$width = 200;
	}

	if($height=='') {
		$height = 200;
	}

	$sppbGalleryParam['width'] = (int) $width;
	$sppbGalleryParam['height'] = (int) $height;

	$output  = '<div class="sppb-addon sppb-addon-gallery ' . $class . '">';

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
	$output .= '<ul class="sppb-gallery clearfix">';
	$output .= AddonParser::spDoAddon($content);
	$output .= '</ul>';
	$output	.= '</div>';
	
	$output .= '</div>';

	$sppbGalleryParam = array();

	return $output;

}

function sp_gallery_item_addon( $atts ){

	global $sppbGalleryParam;

	extract(spAddonAtts(array(
		"title"=>'',
		"thumb"=>'',
		"full"=>'',
		), $atts));

	$output = '';

	if($thumb) {
		
		$output .= '<li>';
		
		if($full) {
			$output .= '<a href="' . $full . '" class="sppb-gallery-btn">';
		}

		$output .= '<img class="sppb-img-responsive" src="' . $thumb . '" width="' . $sppbGalleryParam['width'] . '" height="' . $sppbGalleryParam['height'] . '" alt="' . $title . '">';

		if($full) {
			$output .= '</a>';
		}

		$output .= '</li>';
	}

	return $output;

}