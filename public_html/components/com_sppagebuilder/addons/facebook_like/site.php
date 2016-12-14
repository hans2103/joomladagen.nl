<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonFacebook_like extends SppagebuilderAddons{

	public function render() {

		$layout  	= (isset($this->addon->settings->layout) && $this->addon->settings->layout) ? $this->addon->settings->layout : '';
		$width  	= (isset($this->addon->settings->width) && $this->addon->settings->width) ? $this->addon->settings->width : 225;
		$appid  	= (isset($this->addon->settings->appid) && $this->addon->settings->appid) ? $this->addon->settings->appid : '';

		$output = '';

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

		$output .= '<div class="fb-like" data-href="'.JURI::current().'" data-layout="' . $layout . '" data-width="' . $width . '" data-action="like" data-show-faces="false" data-share="false"></div>';

		return $output;
	}

}
