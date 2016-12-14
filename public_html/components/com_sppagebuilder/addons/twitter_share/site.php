<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonTwitter_share extends SppagebuilderAddons {

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';

		//Options
		$showcount = (isset($this->addon->settings->showcount) && $this->addon->settings->showcount) ? $this->addon->settings->showcount : '';
		$size = (isset($this->addon->settings->size) && $this->addon->settings->size) ? $this->addon->settings->size : '';

		//Output
		$output = '';
		if(!defined('_SPPB_TWITTER')) {
			define('_SPPB_TWITTER', 1);
			$output .= "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>";
		}

		$data = (!$showcount) ? ' data-count="none"' : '';
		$data .= ($size=='large') ? ' data-size="large"' : '';
		$output .= '<a href="'.JURI::current().'" class="twitter-share-button" '. $data .'>Tweet</a>';

		return $output;

	}
}
