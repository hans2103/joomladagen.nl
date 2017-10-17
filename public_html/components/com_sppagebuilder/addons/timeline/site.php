<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('restricted access');

class SppagebuilderAddonTimeline extends SppagebuilderAddons {

    public function render() {
        $class              = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
        $heading_selector   = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';
        $title              = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';

        $output = '';
        $output .= '<div class="sppb-addon sppb-addon-timeline ' . $class . '">';
        $output .= '<div class="sppb-addon-timeline-text-wrap">';
        $output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';
        $output .= '</div>'; //.sppb-addon-instagram-text-wrap

        $output .= '<div class="sppb-addon-timeline-wrapper">';

        foreach ($this->addon->settings->sp_timeline_items as $key => $timeline) {
            $oddeven = (round($key % 2) == 0) ? 'even' : 'odd';
            $output .= '<div class="sppb-row timeline-movement ' . $oddeven . '">';
            $output .= '<div class="timeline-badge"></div>';
            if ($oddeven == 'odd') {
                $output .= '<div class="sppb-col-xs-12 sppb-col-sm-6  timeline-item">';
                $output .= '<p class="timeline-date text-right">' . $timeline->date . '</p>';
                $output .= '</div>';
                $output .= '<div class="sppb-col-xs-12 sppb-col-sm-6  timeline-item">';
                $output .= '<div class="timeline-panel">';
                $output .= '<p class="title">' . $timeline->title . '</p>';
                $output .= '<div class="details">' . $timeline->content . '</div>';
                $output .= '</div>';
                $output .= '</div>';
            } elseif ($oddeven == 'even') {
                $output .= '<div class="sppb-col-xs-12 sppb-col-sm-6  timeline-item">';
                $output .= '<div class="timeline-panel left-part">';
                $output .= '<p class="title">' . $timeline->title . '</p>';
                $output .= '<div class="details">' . $timeline->content . '</div>';
                $output .= '</div>';
                $output .= '</div>';
                $output .= '<div class="sppb-col-xs-12 sppb-col-sm-6  timeline-item">';
                $output .= '<p class="timeline-date text-left">' . $timeline->date . '</p>';
                $output .= '</div>';
            }
            $output .= '</div>'; //.timeline-movement
        } // foreach timelines

        $output .= '</div>'; //.Timeline

        $output .= '</div>'; //.sppb-addon-instagram-gallery

        return $output;
    }

    public function css() {
        $addon_id = '#sppb-addon-' . $this->addon->id;
        $bar_color = (isset($this->addon->settings->bar_color) && $this->addon->settings->bar_color) ? $this->addon->settings->bar_color : '#0095eb';

        $css = '';
        if ($bar_color) {
            $css .= $addon_id . ' .sppb-addon-timeline .sppb-addon-timeline-wrapper:before, ' . $addon_id . ' .sppb-addon-timeline .sppb-addon-timeline-wrapper .timeline-badge:after, '. $addon_id .' .sppb-addon-timeline .timeline-movement.even:before{';
            $css .= 'background-color: ' . $bar_color . ';';
            $css .= '}';

            $css .= $addon_id . ' .sppb-addon-timeline .sppb-addon-timeline-wrapper .timeline-badge:before, '. $addon_id .' .sppb-addon-timeline .timeline-movement.even:after{';
            $css .= 'border-color: ' . $bar_color . ';';
            $css .= '}';
        }

        return $css;
    }

}
