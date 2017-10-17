<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

//no direct accees
defined ('_JEXEC') or die ('restricted access');

class SppagebuilderAddonCarouselpro extends SppagebuilderAddons {

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? ' ' . $this->addon->settings->class : '';

		//Addons option
		$autoplay = (isset($this->addon->settings->autoplay) && $this->addon->settings->autoplay) ? ' data-sppb-ride="sppb-carousel"' : 0;
		$controllers = (isset($this->addon->settings->controllers) && $this->addon->settings->controllers) ? $this->addon->settings->controllers : 0;
		$arrows = (isset($this->addon->settings->arrows) && $this->addon->settings->arrows) ? $this->addon->settings->arrows : 0;
		$alignment = (isset($this->addon->settings->alignment) && $this->addon->settings->alignment) ? $this->addon->settings->alignment : 0;
		$carousel_autoplay = ($autoplay) ? ' data-sppb-ride="sppb-carousel"':'';
		$interval = (isset($this->addon->settings->interval) && $this->addon->settings->interval) ? ((int) $this->addon->settings->interval * 1000) : 5000;

		$output  = '<div id="sppb-carousel-'. $this->addon->id .'" data-interval="'.$interval.'" class="sppb-carousel sppb-carousel-pro sppb-slide' . $class . '"'. $carousel_autoplay .'>';

		if($controllers) {
			$output .= '<ol class="sppb-carousel-indicators">';
				foreach ($this->addon->settings->sp_carouselpro_item as $key1 => $value) {
					$output .= '<li data-sppb-target="#sppb-carousel-'. $this->addon->id .'" '. (($key1 == 0) ? ' class="active"': '' ) .'  data-sppb-slide-to="'. $key1 .'"></li>' . "\n";
				}
			$output .= '</ol>';
		}

		$output .= '<div class="sppb-carousel-inner ' . $alignment . '">';

		foreach ($this->addon->settings->sp_carouselpro_item as $key => $value) {
			$hide_title = '';
			$hide_title .= ( isset( $value->hide_title_md ) && $value->hide_title_md ) ? ' sppb-hidden-md sppb-hidden-lg' : '';
			$hide_title .= ( isset( $value->hide_title_sm ) && $value->hide_title_sm ) ? ' sppb-hidden-sm' : '';
			$hide_title .= ( isset( $value->hide_title_xs ) && $value->hide_title_xs ) ? ' sppb-hidden-xs' : '';

			$hide_content = '';
			$hide_content .= ( isset( $value->hide_content_md ) && $value->hide_content_md ) ? ' sppb-hidden-md sppb-hidden-lg' : '';
			$hide_content .= ( isset( $value->hide_content_sm ) && $value->hide_content_sm ) ? ' sppb-hidden-sm' : '';
			$hide_content .= ( isset( $value->hide_content_xs ) && $value->hide_content_xs ) ? ' sppb-hidden-xs' : '';

			$hide_button = '';
			$hide_button .= ( isset( $value->hide_button_md ) && $value->hide_button_md ) ? ' sppb-hidden-md sppb-hidden-lg' : '';
			$hide_button .= ( isset( $value->hide_button_sm ) && $value->hide_button_sm ) ? ' sppb-hidden-sm' : '';
			$hide_button .= ( isset( $value->hide_button_xs ) && $value->hide_button_xs ) ? ' sppb-hidden-xs' : '';

			$output  .= '<div class="sppb-item'. (($value->bg) ? ' sppb-item-has-bg' : '') . (($key == 0) ? ' active' : '') .'">';
			$output  .= ($value->bg) ? '<img src="' . $value->bg . '" alt="' . $value->title . '">' : '';

			$output  .= '<div class="sppb-carousel-item-inner">';
			$output  .= '<div>';
			$output  .= '<div>';

			$output  .= '<div class="sppb-row">';

			$output  .= '<div class="sppb-col-sm-6">';
			$output  .= '<div class="sppb-carousel-pro-text">';

			if(($value->title) || ($value->content) ) {
				$output  .= ($value->title) ? '<h2 class="'.$hide_title.'">' . $value->title . '</h2>' : '';
				$output  .= ($value->content) ? '<p class="'.$hide_content.'">' . $value->content . '</p>' : '';
				if($value->button_text) {
					$button_class = (isset($value->button_type) && $value->button_type) ? ' sppb-btn-' . $value->button_type : ' sppb-btn-default';
					$button_class .= (isset($value->button_size) && $value->button_size) ? ' sppb-btn-' . $value->button_size : '';
					$button_class .= (isset($value->button_shape) && $value->button_shape) ? ' sppb-btn-' . $value->button_shape: ' sppb-btn-rounded';
					$button_class .= (isset($value->button_appearance) && $value->button_appearance) ? ' sppb-btn-' . $value->button_appearance : '';
					$button_class .= (isset($value->button_block) && $value->button_block) ? ' ' . $value->button_block : '';
					$button_icon = (isset($value->button_icon) && $value->button_icon) ? $value->button_icon : '';
					$button_icon_position = (isset($value->button_icon_position) && $value->button_icon_position) ? $value->button_icon_position: 'left';
					$button_target = (isset($value->button_target) && $value->button_target) ? $value->button_target : '_self';

					if($button_icon_position == 'left') {
						$value->button_text = ($button_icon) ? '<i class="fa ' . $button_icon . '"></i> ' . $value->button_text : $value->button_text;
					} else {
						$value->button_text = ($button_icon) ? $value->button_text . ' <i class="fa ' . $button_icon . '"></i>' : $value->button_text;
					}

					$output  .= '<a href="' . $value->button_url . '"  target="' . $button_target . '" id="btn-'. ($this->addon->id + $key) .'" class="sppb-btn'. $button_class . $hide_button .'">' . $value->button_text . '</a>';
				}
			}

			$output  .= '</div>';
			$output  .= '</div>';

			$output  .= '<div class="sppb-col-sm-6">';
			$output  .= '<div class="sppb-text-right">';

			if($value->video) {

				$video = parse_url($value->video);

				switch($video['host']) {
					case 'youtu.be':
					$id = trim($video['path'],'/');
					$src = '//www.youtube.com/embed/' . $id;
					break;

					case 'www.youtube.com':
					case 'youtube.com':
					parse_str($video['query'], $query);
					$id = $query['v'];
					$src = '//www.youtube.com/embed/' . $id;
					break;

					case 'vimeo.com':
					case 'www.vimeo.com':
					$id = trim($video['path'],'/');
					$src = "//player.vimeo.com/video/{$id}";
				}

				$output .= '<div class="sppb-embed-responsive sppb-embed-responsive-16by9">';
				$output .= '<iframe class="sppb-embed-responsive-item" src="' . $src . '" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
				$output .= '</div>';

			} else {
				$output  .= ($value->image) ? '<img class="sppb-img-reponsive" src="' . $value->image . '" alt="'. $value->title .'">' : '';
			}


			$output  .= '</div>';
			$output  .= '</div>';

			$output  .= '</div>';

			$output  .= '</div>';
			$output  .= '</div>';

			$output  .= '</div>';
			$output  .= '</div>';
		}

		$output	.= '</div>';

		if($arrows) {
			$output	.= '<a href="#sppb-carousel-'. $this->addon->id .'" class="sppb-carousel-arrow left sppb-carousel-control" data-slide="prev"><i class="fa fa-chevron-left"></i></a>';
			$output	.= '<a href="#sppb-carousel-'. $this->addon->id .'" class="sppb-carousel-arrow right sppb-carousel-control" data-slide="next"><i class="fa fa-chevron-right"></i></a>';
		}

		$output .= '</div>';

		return $output;
	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';
		$css = '';

		// Buttons style
		foreach ($this->addon->settings->sp_carouselpro_item as $key => $value) {
			if($value->button_text) {
				$css_path = new JLayoutFile('addon.css.button', $layout_path);
				$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $value, 'id' => 'btn-' . ($this->addon->id + $key)));
			}
		}

		$speed = (isset($this->addon->settings->speed) && $this->addon->settings->speed) ? $this->addon->settings->speed : 600;

		$css .= $addon_id.' .sppb-carousel-inner > .sppb-item{-webkit-transition-duration: '.$speed.'ms; transition-duration: '.$speed.'ms;}';

		return $css;
	}

}
