<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonPerson extends SppagebuilderAddons{

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';

		//Options
		$image 				= (isset($this->addon->settings->image) && $this->addon->settings->image) ? $this->addon->settings->image : '';
		$name 				= (isset($this->addon->settings->name) && $this->addon->settings->name) ? $this->addon->settings->name : '';
		$designation 	= (isset($this->addon->settings->designation) && $this->addon->settings->designation) ? $this->addon->settings->designation : '';
		$email 				= (isset($this->addon->settings->email) && $this->addon->settings->email) ? $this->addon->settings->email : '';
		$introtext 		= (isset($this->addon->settings->introtext) && $this->addon->settings->introtext) ? $this->addon->settings->introtext : '';
		$facebook 		= (isset($this->addon->settings->facebook) && $this->addon->settings->facebook) ? $this->addon->settings->facebook : '';
		$twitter 			= (isset($this->addon->settings->twitter) && $this->addon->settings->twitter) ? $this->addon->settings->twitter : '';
		$google_plus 	= (isset($this->addon->settings->google_plus) && $this->addon->settings->google_plus) ? $this->addon->settings->google_plus : '';
		$youtube 			= (isset($this->addon->settings->youtube) && $this->addon->settings->youtube) ? $this->addon->settings->youtube : '';
		$linkedin 		= (isset($this->addon->settings->linkedin) && $this->addon->settings->linkedin) ? $this->addon->settings->linkedin : '';
		$pinterest 		= (isset($this->addon->settings->pinterest) && $this->addon->settings->pinterest) ? $this->addon->settings->pinterest : '';
		$flickr 			= (isset($this->addon->settings->flickr) && $this->addon->settings->flickr) ? $this->addon->settings->flickr : '';
		$dribbble 		= (isset($this->addon->settings->dribbble) && $this->addon->settings->dribbble) ? $this->addon->settings->dribbble : '';
		$behance 			= (isset($this->addon->settings->behance) && $this->addon->settings->behance) ? $this->addon->settings->behance : '';
		$instagram 		= (isset($this->addon->settings->instagram) && $this->addon->settings->instagram) ? $this->addon->settings->instagram : '';
		$social_position = (isset($this->addon->settings->social_position) && $this->addon->settings->social_position) ? $this->addon->settings->social_position : '';
		$alignment 		= (isset($this->addon->settings->alignment) && $this->addon->settings->alignment) ? $this->addon->settings->alignment : '';

		$output = '';
		$social_icons = '';

		if($facebook || $twitter || $google_plus || $youtube || $linkedin || $pinterest || $flickr || $dribbble || $behance || $instagram) {
			$social_icons  	.= '<div class="sppb-person-social-icons">';
			$social_icons 	.= '<ul class="sppb-person-social">';

			if($facebook) 		$social_icons .= '<li><a target="_blank" href="' . $facebook . '"><i class="fa fa-facebook"></i></a></li>';
			if($twitter) 		$social_icons .= '<li><a target="_blank" href="' . $twitter . '"><i class="fa fa-twitter"></i></a></li>';
			if($google_plus) 	$social_icons .= '<li><a target="_blank" href="' . $google_plus . '"><i class="fa fa-google-plus"></i></a></li>';
			if($youtube) 		$social_icons .= '<li><a target="_blank" href="' . $youtube . '"><i class="fa fa-youtube"></i></a></li>';
			if($linkedin) 		$social_icons .= '<li><a target="_blank" href="' . $linkedin . '"><i class="fa fa-linkedin"></i></a></li>';
			if($pinterest) 		$social_icons .= '<li><a target="_blank" href="' . $pinterest . '"><i class="fa fa-pinterest"></i></a></li>';
			if($flickr) 		$social_icons .= '<li><a target="_blank" href="' . $flickr . '"><i class="fa fa-flickr"></i></a></li>';
			if($dribbble) 		$social_icons .= '<li><a target="_blank" href="' . $dribbble . '"><i class="fa fa-dribbble"></i></a></li>';
			if($behance) 		$social_icons .= '<li><a target="_blank" href="' . $behance . '"><i class="fa fa-behance"></i></a></li>';
			if($instagram) 		$social_icons .= '<li><a target="_blank" href="' . $instagram . '"><i class="fa fa-instagram"></i></a></li>';

			$social_icons 	.= '</ul>';
			$social_icons 	.= '</div>';
		}

			$output  .= '<div class="sppb-addon sppb-addon-persion ' . $alignment . ' ' . $class . '">';
			$output  .= '<div class="sppb-addon-content">';
			if($image) {
					$output  .= '<div class="sppb-person-image">';
					$output  .= '<img class="sppb-img-responsive" src="' . $image . '" alt="'. $name .'">';
					$output  .= '</div>';
			}
			if($name || $designation || $email) {
				$output  .= '<div class="sppb-person-information">';
				if($name) $output  .= '<span class="sppb-person-name">' . $name . '</span>';
				if($designation) $output  .= '<span class="sppb-person-designation">' . $designation . '</span>';
				if($email) $output  .= '<span class="sppb-person-email">' . $email . '</span>';
				$output  .= '</div>';
			}

			if($social_position=='before') $output .= $social_icons;

			if($introtext) $output  .= '<div class="sppb-person-introtext">' . $introtext . '</div>';

			if($social_position=='after') $output .= $social_icons;

			$output  .= '</div>';
			$output  .= '</div>';

			return $output;
	}

}
