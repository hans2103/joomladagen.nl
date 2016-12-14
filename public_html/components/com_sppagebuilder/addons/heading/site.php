<?php

/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined('_JEXEC') or die('restricted aceess');

class SppagebuilderAddonHeading extends SppagebuilderAddons {

  public function render() {

    $class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? ' ' . $this->addon->settings->class : '';
    $class .= (isset($this->addon->settings->alignment) && $this->addon->settings->alignment) ? ' ' . $this->addon->settings->alignment : ' sppb-text-center';
    $title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
    $heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h2';
    $use_link = (isset($this->addon->settings->use_link) && $this->addon->settings->use_link) ? $this->addon->settings->use_link : false;
    $title_link = ($use_link) ? ((isset($this->addon->settings->title_link) && $this->addon->settings->title_link) ? $this->addon->settings->title_link : '') : false;
    $link_target = (isset($this->addon->settings->link_new_tab) && $this->addon->settings->link_new_tab) ? 'target="_blank"' : '';

    $output = '';
    if($title) {
      $output .= '<div class="sppb-addon sppb-addon-header' . $class . '">';
      $output .= ($title_link) ? '<a '. $link_target .' href="'. $title_link .'">' : '';
      $output .= '<'.$heading_selector.' class="sppb-addon-title">' . nl2br($title) . '</'.$heading_selector.'>';
      $output .= ($title_link) ? '</a>' : '';
      $output .= '</div>';
    }

    return $output;
  }

  public function css() {
    $addon_id = '#sppb-addon-' . $this->addon->id;

    $style  = (isset($this->addon->settings->title_lineheight) && $this->addon->settings->title_lineheight) ? "line-height: " . $this->addon->settings->title_lineheight  . ";" : "";
    $style .= (isset($this->addon->settings->title_letterspace) && $this->addon->settings->title_letterspace) ? 'letter-spacing: ' . $this->addon->settings->title_letterspace . ';' : '';
    $style .= (isset($this->addon->settings->title_margin) && $this->addon->settings->title_margin) ? 'margin: ' . $this->addon->settings->title_margin  . '; ' : '';
    $style .= (isset($this->addon->settings->title_padding) && $this->addon->settings->title_padding) ? 'padding: ' . $this->addon->settings->title_padding  . '; ' : '';
    $font_style = (isset($this->addon->settings->title_fontstyle) && $this->addon->settings->title_fontstyle) ? $this->addon->settings->title_fontstyle : '';

    if(is_array($font_style) && count($font_style)) {
      if(in_array('underline', $font_style)) {
        $style .= 'text-decoration: underline;';
      }

      if(in_array('uppercase', $font_style)) {
        $style .= 'text-transform: uppercase;';
      }

      if(in_array('italic', $font_style)) {
        $style .= 'font-style: italic;';
      }

      if(in_array('lighter', $font_style)) {
        $style .= 'font-weight: lighter;';
      } else if(in_array('normal', $font_style)) {
        $style .= 'font-weight: normal;';
      } else if(in_array('bold', $font_style)) {
        $style .= 'font-weight: bold;';
      } else if(in_array('bolder', $font_style)) {
        $style .= 'font-weight: bolder;';
      }
    }

    $heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h2';

    $css = '';
    if ($style) {
      $css .= $addon_id . ' ' . $heading_selector . '.sppb-addon-title {' . $style . '}';
    }

		return $css;
  }

}
