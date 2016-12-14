<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonGplus_button extends SppagebuilderAddons{

	public function render() {

		$size = (isset($this->addon->settings->size) && $this->addon->settings->size) ? $this->addon->settings->size : '';
		$annotation = (isset($this->addon->settings->annotation) && $this->addon->settings->annotation) ? $this->addon->settings->annotation : '';
		$width = (isset($this->addon->settings->width) && $this->addon->settings->width) ? $this->addon->settings->width : 292;

		$doc = JFactory::getDocument();
		$doc->addScript('//apis.google.com/js/plusone.js');
		$output = '<div class="g-plusone" data-href="' . JURI::current() . '" data-size="' . $size . '" data-annotation="' . $annotation . '"></div>';

		return $output;
	}

}
