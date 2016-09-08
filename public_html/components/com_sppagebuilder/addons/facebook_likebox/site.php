<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_facebook_likebox','sp_facebook_likebox_addon');

function sp_facebook_likebox_addon($atts){

	extract(spAddonAtts(array(
		'title'					=> '',
		"heading_selector" 		=> 'h3',
		"title_fontsize" 		=> '',
		"title_fontweight" 		=> '',
		"title_text_color" 		=> '',
		"title_margin_top" 		=> '',
		"title_margin_bottom" 	=> '',		
		'url'					=> '',
		'appid'					=> '354400064582736',
		'width'					=> '',
		'height'				=> '',
		'colorscheme'			=> '',
		'showposts'				=> '',
		'class'					=> '',
		), $atts));

	if($width=='') {
		$width = 292;
	}

	if($height=='') {
		$height = 300;
	}

	$output  = '<div class="sppb-addon sppb-addon-facebook-likebox ' . $class . '">';

	if($title) {

		$title_style = '';
		if($title_margin_top !='') $title_style .= 'margin-top:' . (int) $title_margin_top . 'px;';
		if($title_margin_bottom !='') $title_style .= 'margin-bottom:' . (int) $title_margin_bottom . 'px;';
		if($title_text_color) $title_style .= 'color:' . $title_text_color  . ';';
		if($title_fontsize) $title_style .= 'font-size:'.$title_fontsize.'px;line-height:'.$title_fontsize.'px;';
		if($title_fontweight) $title_style .= 'font-weight:'.$title_fontweight.';';

		$output .= '<'.$heading_selector.' class="sppb-addon-title" style="' . $title_style . '">' . $title . '</'.$heading_selector.'>';
	}

	if(!defined('_SPPB_FB')) {

		define('_SPPB_FB', 1);

		$output .= '<div id="fb-root"></div>';
		$output .= '<script>(function(d, s, id) {
					  var js, fjs = d.getElementsByTagName(s)[0];
					  if (d.getElementById(id)) return;
					  js = d.createElement(s); js.id = id;
					  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId='.$appid.'&version=v2.0";
					  fjs.parentNode.insertBefore(js, fjs);
					}(document, "script", "facebook-jssdk"));</script>';
	}

	$output .= '<div class="fb-like-box" data-href="'.$url.'" data-height="'.(int) $height.'" data-width="'.(int) $width.'" data-colorscheme="'.$colorscheme.'" data-show-faces="true" data-header="false" data-stream="'.$showposts.'" data-show-border="false"></div>';

	$output .= '</div>';

	return $output;

}