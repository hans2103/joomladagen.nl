<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_pie_progress','sp_pie_progress_addon');

function sp_pie_progress_addon($atts){

	extract(spAddonAtts(array(
		'percentage'			=> '',
		'border_color'			=> '',
		'border_active_color'	=> '',
		'border_width'			=> '',
		'size'					=> '',
		'icon_name'				=> '',
		'icon_size'				=> '',
		'title'					=> '',
		'text'					=> '',
		'class'					=> '',
		), $atts));

	$doc = JFactory::getDocument();
	$doc->addScript( JURI::base(true) . '/components/com_sppagebuilder/assets/js/jquery.easypiechart.min.js');

	$style = 'height: '. (int) $size .'px; width: '. (int) $size .'px;';

	$output  = '<div class="sppb-addon sppb-addon-pie-progress '. $class .'">';
	$output .= '<div class="sppb-addon-content sppb-text-center">';
	$output .= '<div class="sppb-pie-chart" data-size="'. (int) $size .'" data-percent="'.$percentage.'" data-width="'.$border_width.'" data-barcolor="'.$border_active_color.'" data-trackcolor="'.$border_color.'" style="'. $style .'">';

	if($icon_name) {
		$output .= '<div class="sppb-chart-icon"><span><i class="fa '. $icon_name . ' ' .  $icon_size .'"></i></span></div>';
	} else {
		$output .= '<div class="sppb-chart-percent"><span></span></div>';
	}

	$output .= '</div>';

	if ($title) $output .= '<h3>' . $title . '</h3>';

	$output .= '<div class="sppb-addon-text">';
	$output .= $text;
	$output .= '</div>';

	$output .= '</div>';
	$output .= '</div>';

	return $output;
}