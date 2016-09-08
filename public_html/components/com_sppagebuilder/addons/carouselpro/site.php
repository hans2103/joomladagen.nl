<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_carouselpro','sp_carouselpro_addon');
AddonParser::addAddon('sp_carouselpro_item','sp_carouselpro_item_addon');

function sp_carouselpro_addon($atts, $content){

	extract(spAddonAtts(array(
		'autoplay'=>'',
		'controllers'=>'',
		'arrows'=>'',
		'color'=>'',
		'background'=>'',
		"class"=>'',
		), $atts));

	if($background) {
		$background = 'background-color:' . $background . ';';
	}

	if($color) {
		$color = 'color:' . $color . ';';
	}

	$carousel_autoplay = ($autoplay)?'data-sppb-ride="sppb-carousel"':'';

	$output  = '<div style="' . $background . $color . '" class="sppb-carousel sppb-carousel-pro sppb-slide ' . $class . '" ' . $carousel_autoplay . '>';

	if($controllers) {
	$output .= '<ol class="sppb-carousel-indicators">';
    $output .= '</ol>';
	}

	$output .= '<div class="sppb-carousel-inner">';
	$output .= AddonParser::spDoAddon($content);
	$output	.= '</div>';

	if($arrows) {
		$output	.= '<a style="' . $color . '" class="sppb-carousel-arrow left sppb-carousel-control" role="button" data-slide="prev"><i class="fa fa-chevron-left"></i></a>';
		$output	.= '<a style="' . $color . '" class="sppb-carousel-arrow right sppb-carousel-control" role="button" data-slide="next"><i class="fa fa-chevron-right"></i></a>';
	}
	
	$output .= '</div>';

	return $output;

}

function sp_carouselpro_item_addon( $atts ){

	extract(spAddonAtts(array(
		"title"=>'',
		"bg"=>'',
		"image"=>'',
		"video"=>'',
		'content'=>'',
		"button_text"=>'',
		"button_url"=>'',
		"button_size" => '',
		"button_type"=>'',
		"button_icon"=>'',
		), $atts));

	if($button_icon) {
		$button_text = '<i class="fa ' . $button_icon . '"></i> ' . $button_text;
	}

	$has_bg = '';

	if($bg) {
		$has_bg = ' sppb-item-has-bg';
	}
	
	$output   = '<div class="sppb-item'. $has_bg .'">';
	
	if($bg) {
		$output  .= '<img src="' . $bg . '" alt="' . $title . '">';		
	}

	$output  .= '<div class="sppb-carousel-item-inner">';
	$output  .= '<div>';
	$output  .= '<div>';

	$output  .= '<div class="sppb-row">';

	$output  .= '<div class="sppb-col-sm-6">';
	$output  .= '<div class="sppb-carousel-pro-text">';


	if(($title) || ($content) ) {
		
		if($title){
			$output  .= '<h2>' . $title . '</h2>';
		}

        $output  .= '<p>' . $content . '</p>';

        if($button_text && $button_url) {
        	$output  .= '<a href="' . $button_url . '" class="sppb-btn sppb-btn-' . $button_type . ' sppb-btn-' . $button_size . '" role="button">' . $button_text . '</a>';
        }
	}

	$output  .= '</div>';
	$output  .= '</div>';
	
	$output  .= '<div class="sppb-col-sm-6">';
	$output  .= '<div class="sppb-text-right">';

	if($video) {

		$video = parse_url($video);
		
		switch($video['host']) {
			case 'youtu.be':
				$id = trim($video['path'],'/');
				$src = '//www.youtube.com/embed/' . $id;
			break;
			
			case 'www.youtube.com':
			case 'youtube.com':
				parse_str($video['query'], $query);
				$id = $query['v'];
				$src = '//www.youtube.com/embed/' . $id;
			break;
			
			case 'vimeo.com':
			case 'www.vimeo.com':
				$id = trim($video['path'],'/');
				$src = "//player.vimeo.com/video/{$id}";
		}

		$output .= '<div class="sppb-embed-responsive sppb-embed-responsive-16by9">';
		$output .= '<iframe class="sppb-embed-responsive-item" src="' . $src . '" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
		$output .= '</div>';

	} else {
		if($image) {
			$output  .= '<img class="sppb-img-reponsive" src="' . $image . '" alt="">';
		}
	}


	$output  .= '</div>';
	$output  .= '</div>';

	$output  .= '</div>';


	$output  .= '</div>';
	$output  .= '</div>';

	$output  .= '</div>';
	$output  .= '</div>';

	return $output;

}