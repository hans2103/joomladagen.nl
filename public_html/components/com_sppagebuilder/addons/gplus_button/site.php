<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

AddonParser::addAddon('sp_gplus_button','sp_gplus_button_addon');

function sp_gplus_button_addon($atts){

	extract(spAddonAtts(array(
		'size'=>'',
		'annotation'=>'',
		'width'=>'',
		), $atts));

	$doc = JFactory::getDocument();
	$doc->addScript('https://apis.google.com/js/plusone.js');

	$output = '<div class="g-plusone" data-href="' . JURI::current() . '" data-size="' . $size . '" data-annotation="' . $annotation . '"></div>';

	return $output;
	
}