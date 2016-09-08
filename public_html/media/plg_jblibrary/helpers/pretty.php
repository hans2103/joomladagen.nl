<?php
/**
* @package  JB Library
* @copyright Copyright (C) 2006 - 2010 Joomla Bamboo. http://www.joomlabamboo.com  All rights reserved.
* @license  GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
*/
//Resize image options: exact, portrait, landscape, auto, smart, topleft, center, enlarge
//Pretty photo types: popup, iframe, inline, gallery, slideshow, picasa, flickr, quicktime, flash, vimeo, youtube
//Pretty photo themes: light_rounded, dark_rounded, light_square, dark_square, facebook
// no direct access
defined('_JEXEC') or die('Restricted access');
class prettyPhotoHelper
{
	public function getPrettyPhoto($type, $source, $width=50, $height=50, $option='smart', $quality=90, $theme='facebook', $padding=10, $opacity='0.8', $title=false, $speed='normal', $user=false, $apikey=false, $setid=false, $number=false, $extlink ){
		// Import libraries
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		//define variables
		$show_title = ($title===false)?false:true;
		$html = '';
		switch($type){
			case 'popup':
				if (Jfile::exists($source)===false){return;}
				$html .= '<script type="text/javascript" charset="utf-8">
				(function($) {
				$(document).ready(function(){
				$("a[rel^=\'prettyPhoto\']").prettyPhoto({
				animation_speed: "'.$speed.'",
				slideshow: false, 
				autoplay_slideshow: false,
				opacity: "'.$opacity.'", 
				show_title: "'.$show_title.'",
				allow_resize: true,
				default_width: 500,
				default_height: 350,
				counter_separator_label: "/", 
				theme: "'.$theme.'",
				hideflash: false, 
				wmode: "opaque", 
				autoplay: true,
				modal: false,
				overlay_gallery: true,
				keyboard_shortcuts: true
				});
				})})(jQuery);</script>';
				$resized = resizeImageHelper::getResizedImage($source, $width, $height, $option, $quality);
				$html .= '<a href="'.$source.'" rel="prettyPhoto" title="'.$title.'"><img src="'.$resized.'" class="thumbnail" alt="'.$title.'" border="0" title="'.$title.'"></a>';
				break;
		
			case "iframe":
				//if (Jfile::exists($source)===false){return;}
				//if (Jfile::exists($source)===false){return;}
				//define variables
				$show_title = ($title===false)?false:true;				
				$extlink = 'http://'.str_replace('http://', '', trim($extlink));
				
				$html .= '<script type="text/javascript" charset="utf-8">
				(function($) {
				$(document).ready(function(){
				$("a[rel^=\'prettyPhoto[iframes]\']").prettyPhoto({
				animation_speed: "'.$speed.'",
				slideshow: false, 
				autoplay_slideshow: false,
				opacity: "'.$opacity.'", 
				show_title: "'.$show_title.'",
				allow_resize: true,
				default_width: 500,
				default_height: 350,
				counter_separator_label: "/", 
				theme: "'.$theme.'",
				hideflash: false, 
				wmode: "opaque", 
				autoplay: true,
				modal: false,
				overlay_gallery: true,
				keyboard_shortcuts: true
				});
				})})(jQuery);</script>';
				$resized = resizeImageHelper::getResizedImage($source, $width, $height, $option, $quality);
				$html .= '<a href="'.$extlink.'?iframe=true&width=100%&height=100%" rel="prettyPhoto[iframes]" title="'.$title.'"><img src="'.$resized.'" class="thumbnail" alt="'.$title.'" border="0" title="'.$title.'"></a>';
				break;
				
			case "inline":
				$html .= '<script type="text/javascript" charset="utf-8">
				(function($) {
				$(document).ready(function(){
				$("a[rel^=\'prettyPhoto[inline]\']").prettyPhoto({
				animation_speed: "'.$speed.'",
				slideshow: false, 
				autoplay_slideshow: false,
				opacity: "'.$opacity.'", 
				show_title: "'.$show_title.'",
				allow_resize: true,
				default_width: 500,
				default_height: 350,
				counter_separator_label: "/", 
				theme: "'.$theme.'",
				hideflash: false, 
				wmode: "opaque", 
				autoplay: true,
				modal: false,
				overlay_gallery: true,
				keyboard_shortcuts: true
				});
				})})(jQuery);</script>';				
				$html .= '<div class="pp_inline clearfix">{content}</div>';
				$html .= '<a href="#inline-1" rel="prettyPhoto" ><img src="/wp-content/themes/NMFE/images/thumbnails/earth-logo.jpg" alt="" width="50" /></a>
		<div id="inline-1" class="hide">
			<p>This is inline content opened in prettyPhoto.</p>
			<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p></div>
		</div>';
				break;				
			
			case "quicktime":
				$html .= '<script type="text/javascript" charset="utf-8">
				(function($) {
				$(document).ready(function(){
				$("a[rel^=\'prettyPhoto\']").prettyPhoto({
				animation_speed: "'.$speed.'",
				slideshow: false, 
				autoplay_slideshow: false,
				opacity: "'.$opacity.'", 
				show_title: "'.$show_title.'",
				allow_resize: true,
				default_width: 500,
				default_height: 350,
				counter_separator_label: "/", 
				theme: "'.$theme.'",
				hideflash: false, 
				wmode: "opaque", 
				autoplay: true,
				modal: false,
				overlay_gallery: true,
				keyboard_shortcuts: true
				});
				})})(jQuery);</script>';				
				$html .= '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" height="{height}" width="{width}"><param name="src" value="{path}"><param name="autoplay" value="{autoplay}"><param name="type" value="video/quicktime"><embed src="{path}" height="{height}" width="{width}" autoplay="{autoplay}" type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/"></embed></object>';
				break;

			case "flash":
				$html .= '<script type="text/javascript" charset="utf-8">
				(function($) {
				$(document).ready(function(){
				$("a[rel^=\'prettyPhoto\']").prettyPhoto({
				animation_speed: "'.$speed.'",
				slideshow: false, 
				autoplay_slideshow: false,
				opacity: "'.$opacity.'", 
				show_title: "'.$show_title.'",
				allow_resize: true,
				default_width: 500,
				default_height: 350,
				counter_separator_label: "/", 
				theme: "'.$theme.'",
				hideflash: false, 
				wmode: "opaque", 
				autoplay: true,
				modal: false,
				overlay_gallery: true,
				keyboard_shortcuts: true
				});
				})})(jQuery);</script>';
				$html .= '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{width}" height="{height}"><param name="wmode" value="{wmode}" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="{path}" /><embed src="{path}" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="{width}" height="{height}" wmode="{wmode}"></embed></object>';
				break;
			case "flickr":
				$html .= '<script type="text/javascript" charset="utf-8">
				(function($) {
				$(document).ready(function(){
				$("a[rel^=\'prettyPhoto\']").prettyPhoto({
				animation_speed: "'.$speed.'",
				slideshow: false, 
				autoplay_slideshow: false,
				opacity: "'.$opacity.'", 
				show_title: "'.$show_title.'",
				allow_resize: true,
				default_width: 500,
				default_height: 350,
				counter_separator_label: "/", 
				theme: "'.$theme.'",
				hideflash: false, 
				wmode: "opaque", 
				autoplay: true,
				modal: false,
				overlay_gallery: true,
				keyboard_shortcuts: true
				});
				})})(jQuery);</script>';
				break;
			case "picasa":
				$html .= '<script type="text/javascript" charset="utf-8">
				(function($) {
				$(document).ready(function(){
				$("a[rel^=\'prettyPhoto\']").prettyPhoto({
				animation_speed: "'.$speed.'",
				slideshow: false, 
				autoplay_slideshow: false,
				opacity: "'.$opacity.'", 
				show_title: "'.$show_title.'",
				allow_resize: true,
				default_width: 500,
				default_height: 350,
				counter_separator_label: "/", 
				theme: "'.$theme.'",
				hideflash: false, 
				wmode: "opaque", 
				autoplay: true,
				modal: false,
				overlay_gallery: true,
				keyboard_shortcuts: true
				});
				})})(jQuery);</script>';
				break;
			
			case "gallery":
				if (Jfolder::exists($source)===false){return;}
				$html .= '<script type="text/javascript" charset="utf-8">
				(function($) {
				$(document).ready(function(){
				$("a[rel^=\'prettyPhoto\']").prettyPhoto({
				animation_speed: "'.$speed.'",
				slideshow: false, 
				autoplay_slideshow: false,
				opacity: "'.$opacity.'", 
				show_title: "'.$show_title.'",
				allow_resize: true,
				default_width: 500,
				default_height: 350,
				counter_separator_label: "/", 
				theme: "'.$theme.'",
				hideflash: false, 
				wmode: "opaque", 
				autoplay: true,
				modal: false,
				overlay_gallery: true,
				keyboard_shortcuts: true
				});
				})})(jQuery);</script>';
				$html .= '<div class="pp_gallery">
									<a href="#" class="pp_arrow_previous">Previous</a>
									<ul>
										{gallery}
									</ul>
									<a href="#" class="pp_arrow_next">Next</a>
								</div>';
				break;
			case "slideshow":
				if (Jfolder::exists($source)===false){return;}
				$html .= '<script type="text/javascript" charset="utf-8">
				(function($) {
				$(document).ready(function(){
				$("a[rel^=\'prettyPhoto\']").prettyPhoto({
				animation_speed: "'.$speed.'",
				slideshow: false, 
				autoplay_slideshow: false,
				opacity: "'.$opacity.'", 
				show_title: "'.$show_title.'",
				allow_resize: true,
				default_width: 500,
				default_height: 350,
				counter_separator_label: "/", 
				theme: "'.$theme.'",
				hideflash: false, 
				wmode: "opaque", 
				autoplay: true,
				modal: false,
				overlay_gallery: true,
				keyboard_shortcuts: true
				});
				})})(jQuery);</script>';
				$html .= '<div class="pp_pic_holder">
							<div class="ppt">&nbsp;</div>
							<div class="pp_top">
								<div class="pp_left"></div>
								<div class="pp_middle"></div>
								<div class="pp_right"></div>
							</div>
							<div class="pp_content_container">
								<div class="pp_left">
								<div class="pp_right">
									<div class="pp_content">
										<div class="pp_loaderIcon"></div>
										<div class="pp_fade">
											<a href="#" class="pp_expand" title="Expand the image">Expand</a>
											<div class="pp_hoverContainer">
												<a class="pp_next" href="#">next</a>
												<a class="pp_previous" href="#">previous</a>
											</div>
											<div id="pp_full_res"></div>
											<div class="pp_details clearfix">
												<p class="pp_description"></p>
												<a class="pp_close" href="#">Close</a>
												<div class="pp_nav">
													<a href="#" class="pp_arrow_previous">Previous</a>
													<p class="currentTextHolder">0/0</p>
													<a href="#" class="pp_arrow_next">Next</a>
												</div>
											</div>
										</div>
									</div>
								</div>
								</div>
							</div>
							<div class="pp_bottom">
								<div class="pp_left"></div>
								<div class="pp_middle"></div>
								<div class="pp_right"></div>
							</div>
						</div>
						<div class="pp_overlay"></div>';
			break;
		}
		return $html;
	}
}
