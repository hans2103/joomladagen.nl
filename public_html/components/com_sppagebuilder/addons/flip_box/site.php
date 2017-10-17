<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('restricted access');

class SppagebuilderAddonFlip_box extends SppagebuilderAddons {

    public function render() {
        //get data
        $class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
        $front_text = (isset($this->addon->settings->front_text) && $this->addon->settings->front_text) ? $this->addon->settings->front_text : '';
        $flip_text = (isset($this->addon->settings->flip_text) && $this->addon->settings->flip_text) ? $this->addon->settings->flip_text : '';
        $rotate = (isset($this->addon->settings->rotate) && $this->addon->settings->rotate) ? $this->addon->settings->rotate : 'flip_right';
        $flip_bhave = (isset($this->addon->settings->flip_bhave) && $this->addon->settings->flip_bhave) ? $this->addon->settings->flip_bhave : 'hover';
        $text_align = (isset($this->addon->settings->text_align) && $this->addon->settings->text_align) ? $this->addon->settings->text_align : 'hover';

        //Flip Style
        $flip_style = (isset($this->addon->settings->flip_style) && $this->addon->settings->flip_style) ? $this->addon->settings->flip_style : 'rotate_style';

        if ($flip_style != '') {
            if ($flip_style == 'slide_style') {
                $flip_style = 'slide-flipbox';
            } elseif ($flip_style == 'fade_style') {
                $flip_style = 'fade-flipbox';
            } elseif ($flip_style == 'threeD_style') {
                $flip_style = 'threeD-flipbox';
            }
        }

        $output = '';
        $output .= '<div class="sppb-addon sppb-addon-sppb-flibox ' . $class . ' ' . $flip_style . ' ' . $rotate . ' flipon-' . $flip_bhave . ' sppb-text-' . $text_align . '">';

        if ($flip_style == 'threeD-flipbox') {

            $output .= '<div class="threeD-content-wrap">';
            $output .= '<div class="threeD-item">';
            $output .= '<div class = "threeD-flip-front">';
            $output .= '<div class = "threeD-content-inner">';
            $output .= $front_text;
            $output .= '</div>';
            $output .= '</div>';
            $output .= '<div class = "threeD-flip-back">';
            $output .= '<div class = "threeD-content-inner">';
            $output .= $flip_text;
            $output .= '</div>';
            $output .= '</div >';
            $output .= '</div>'; //.threeD-item
            $output .= '</div>'; //.threeD-content-wrap
        } else {

            $output .= '<div class="sppb-flipbox-panel">';
            $output .= '<div class="sppb-flipbox-front flip-box">';
            $output .= '<div class="flip-box-inner">';
            $output .= $front_text;
            $output .= '</div>'; //.flip-box-inner
            $output .= '</div>'; //.front
            $output .= '<div class="sppb-flipbox-back flip-box">';
            $output .= '<div class="flip-box-inner">';
            $output .= $flip_text;
            $output .= '</div>'; //.flip-box-inner
            $output .= '</div>'; //.back
            $output .= '</div>'; //.sppb-flipbox-panel
        }
        $output .= '</div>'; //.sppb-addon-sppb-flibox
        return $output;
    }

    public function css() {
        $addon_id = '#sppb-addon-' . $this->addon->id;

        //flip style
        $flip_syles = '';
        //$flip_syles .= (isset($this->addon->settings->text_align) && $this->addon->settings->text_align) ? "text-align: " . $this->addon->settings->text_align  . ";" : "text-align: center";
        $flip_syles .= (isset($this->addon->settings->height) && $this->addon->settings->height) ? "height: " . $this->addon->settings->height . "px;" : "";

        $border_styles = (isset($this->addon->settings->border_styles) && $this->addon->settings->border_styles) ? $this->addon->settings->border_styles : "";
        if (is_array($border_styles) && count($border_styles)) {
            if (in_array('border_radius', $border_styles)) {
                $flip_syles .= 'border-radius: 8px;';
            }
            if (in_array('dashed', $border_styles)) {
                $flip_syles .= 'border-style: dashed;';
            } else if (in_array('solid', $border_styles)) {
                $flip_syles .= 'border-style: solid;';
            } else if (in_array('dotted', $border_styles)) {
                $flip_syles .= 'border-style: dotted;';
            }

            if (in_array('dashed', $border_styles) || in_array('solid', $border_styles) || in_array('dotted', $border_styles)) {
                $flip_syles .= 'border-width: 2px;';
                $flip_syles .= 'border-color:' . $this->addon->settings->border_color . ';';
            }
        }

        //front style
        $front_style = '';
        if(isset($this->addon->settings->front_bgimg) && $this->addon->settings->front_bgimg){
          if(strpos($this->addon->settings->front_bgimg, "http://") !== false || strpos($this->addon->settings->front_bgimg, "https://") !== false){
            $front_bgimg = $this->addon->settings->front_bgimg;
        	} else {
            $front_bgimg = JURI::root() . $this->addon->settings->front_bgimg;
        	}
          $front_style .= "background-image: url(" . $front_bgimg . ");";
        }

        $front_style .= (isset($this->addon->settings->front_textcolor) && $this->addon->settings->front_textcolor) ? "color: " . $this->addon->settings->front_textcolor . ";" : "";

        //back style
        $back_style = '';
        if(isset($this->addon->settings->back_bgimg) && $this->addon->settings->back_bgimg){
          if(strpos($this->addon->settings->back_bgimg, "http://") !== false || strpos($this->addon->settings->back_bgimg, "https://") !== false){
            $back_bgimg = $this->addon->settings->back_bgimg;
        	} else {
            $back_bgimg = JURI::root() . $this->addon->settings->back_bgimg;
        	}
          $back_style .= "background-image: url(" . $back_bgimg . ");";
        }

        $back_style .= (isset($this->addon->settings->back_textcolor) && $this->addon->settings->back_textcolor) ? "color: " . $this->addon->settings->back_textcolor . ";" : "";

        //front bg color
        $front_bg_color = '';
        $front_bg_color .= (isset($this->addon->settings->front_bgcolor) && $this->addon->settings->front_bgcolor) ? "background-color: " . $this->addon->settings->front_bgcolor . ";" : "";
        //Bg color back
        $back_bg_color = '';
        $back_bg_color .= (isset($this->addon->settings->back_bgcolor) && $this->addon->settings->back_bgcolor) ? "background-color: " . $this->addon->settings->back_bgcolor . ";" : "";

        $css = '';
        if ($flip_syles) {
            $css .= $addon_id . ' .sppb-flipbox-panel {';
            $css .= $flip_syles;
            $css .= '}';
        }
        if ($flip_syles) {
            $css .= $addon_id . ' .threeD-item {';
            $css .= $flip_syles;
            $css .= '}';
        }

        if ($front_style) {
            $css .= $addon_id . ' .sppb-flipbox-front {';
            $css .= $front_style;
            $css .= '}';
        }
        if ($front_style) {
            $css .= $addon_id . ' .threeD-flip-front {';
            $css .= $front_style;
            $css .= '}';
        }

        if ($back_style) {
            $css .= $addon_id . ' .sppb-flipbox-back {';
            $css .= $back_style;
            $css .= '}';
        }
        if ($back_style) {
            $css .= $addon_id . ' .threeD-flip-back {';
            $css .= $back_style;
            $css .= '}';
        }
        //front bg color
        if ($front_bg_color) {
            $css .= $addon_id . ' .threeD-flip-front:before{';
            $css .= $front_bg_color;
            $css .= '}';
        }
        if ($front_bg_color) {
            $css .= $addon_id . ' .sppb-flipbox-front.flip-box:before{';
            $css .= $front_bg_color;
            $css .= '}';
        }
        //Back bg color
        if ($back_bg_color) {
            $css .= $addon_id . ' .threeD-flip-back:before{';
            $css .= $back_bg_color;
            $css .= '}';
        }
        if ($back_bg_color) {
            $css .= $addon_id . ' .sppb-flipbox-back.flip-box:before{';
            $css .= $back_bg_color;
            $css .= '}';
        }

        return $css;
    }

}
