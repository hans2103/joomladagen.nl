<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_person','sp_person_addon');

function sp_person_addon($atts) {

	extract(spAddonAtts(array(
		'image' 			=> '',
		'name' 				=> '',
		'designation' 		=> '',
		'introtext' 		=> '',
		'facebook' 			=> '',
		'twitter' 			=> '',
		'google_plus' 		=> '',
		'youtube' 			=> '',
		'linkedin' 			=> '',
		'pinterest' 		=> '',
		'flickr' 			=> '',
		'dribbble' 			=> '',
		'behance' 			=> '',
		'instagram' 		=> '',
		'social_position' 	=> '',
		'alignment' 		=> '',
		'class' 			=> '',
		), $atts));

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


	if($image) {
		$output  .= '<div class="sppb-addon sppb-addon-persion ' . $alignment . ' ' . $class . '">';
		$output  .= '<div class="sppb-addon-content">';

		$output  .= '<div class="sppb-person-image">';
		$output  .= '<img class="sppb-img-responsive" src="' . $image . '" alt="">';
		$output  .= '</div>';

		if($name || $designation) {
			$output  .= '<div class="sppb-person-information">';
			if($name) $output  .= '<span class="sppb-person-name">' . $name . '</span>';
			if($designation) $output  .= '<span class="sppb-person-designation">' . $designation . '</span>';
			$output  .= '</div>';
		}

		if($social_position=='before') $output .= $social_icons;

		if($introtext) $output  .= '<div class="sppb-person-introtext">' . $introtext . '</div>';

		if($social_position=='after') $output .= $social_icons;

		$output  .= '</div>';
		$output  .= '</div>';

		return $output;

	}

	return;

}