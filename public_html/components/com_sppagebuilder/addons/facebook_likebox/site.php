<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonFacebook_likebox extends SppagebuilderAddons{

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		//Options
		$appid = (isset($this->addon->settings->appid) && $this->addon->settings->appid) ? $this->addon->settings->appid : '';
		$url = (isset($this->addon->settings->url) && $this->addon->settings->url) ? $this->addon->settings->url : '';
		$width = (isset($this->addon->settings->width) && $this->addon->settings->width) ? $this->addon->settings->width : 292;
		$height = (isset($this->addon->settings->height) && $this->addon->settings->height) ? $this->addon->settings->height : 300;
		$colorscheme = (isset($this->addon->settings->colorscheme) && $this->addon->settings->colorscheme) ? $this->addon->settings->colorscheme : '';
		$showposts = (isset($this->addon->settings->showposts) && $this->addon->settings->showposts) ? $this->addon->settings->showposts : '';

		$output  = '<div class="sppb-addon sppb-addon-facebook-likebox ' . $class . '">';
		$output .= ($title) ? '<'.$heading_selector.' class="sppb-addon-title">' . $title . '</'.$heading_selector.'>' : '';

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

		$output .= '<div class="fb-like-box" data-href="' . $url . '" data-height="' . (int) $height . '" data-width="' . (int) $width . '" data-colorscheme="' . $colorscheme . '" data-show-faces="true" data-header="false" data-stream="' . $showposts . '" data-show-border="false"></div>';

		$output .= '</div>';

		return $output;
	}

}
