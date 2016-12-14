<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonPricing extends SppagebuilderAddons {

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		//Options
		$price = (isset($this->addon->settings->price) && $this->addon->settings->price) ? $this->addon->settings->price : '';
		$duration = (isset($this->addon->settings->duration) && $this->addon->settings->duration) ? $this->addon->settings->duration : '';
		$pricing_content = (isset($this->addon->settings->pricing_content) && $this->addon->settings->pricing_content) ? $this->addon->settings->pricing_content : '';
		$button_text = (isset($this->addon->settings->button_text) && $this->addon->settings->button_text) ? $this->addon->settings->button_text : '';
		$button_url = (isset($this->addon->settings->button_url) && $this->addon->settings->button_url) ? $this->addon->settings->button_url : '';
		$button_classes = (isset($this->addon->settings->button_size) && $this->addon->settings->button_size) ? ' sppb-btn-' . $this->addon->settings->button_size : '';
		$button_classes .= (isset($this->addon->settings->button_type) && $this->addon->settings->button_type) ? ' sppb-btn-' . $this->addon->settings->button_type : '';
		$button_classes .= (isset($this->addon->settings->button_shape) && $this->addon->settings->button_shape) ? ' sppb-btn-' . $this->addon->settings->button_shape: ' sppb-btn-rounded';
		$button_classes .= (isset($this->addon->settings->button_appearance) && $this->addon->settings->button_appearance) ? ' sppb-btn-' . $this->addon->settings->button_appearance : '';
		$button_classes .= (isset($this->addon->settings->button_block) && $this->addon->settings->button_block) ? ' ' . $this->addon->settings->button_block : '';
		$button_icon = (isset($this->addon->settings->button_icon) && $this->addon->settings->button_icon) ? $this->addon->settings->button_icon : '';
		$button_icon_position = (isset($this->addon->settings->button_icon_position) && $this->addon->settings->button_icon_position) ? $this->addon->settings->button_icon_position: 'left';
		$button_position = (isset($this->addon->settings->button_position) && $this->addon->settings->button_position) ? $this->addon->settings->button_position : '';
		$button_attribs = (isset($this->addon->settings->button_target) && $this->addon->settings->button_target) ? ' target="' . $this->addon->settings->button_target . '"' : '';
		$button_attribs .= (isset($this->addon->settings->button_url) && $this->addon->settings->button_url) ? ' href="' . $this->addon->settings->button_url . '"' : '';
		$alignment = (isset($this->addon->settings->alignment) && $this->addon->settings->alignment) ? $this->addon->settings->alignment : '';
		$featured = (isset($this->addon->settings->featured) && $this->addon->settings->featured) ? $this->addon->settings->featured : '';

		if($button_icon_position == 'left') {
			$button_text = ($button_icon) ? '<i class="fa ' . $button_icon . '"></i> ' . $button_text : $button_text;
		} else {
			$button_text = ($button_icon) ? $button_text . ' <i class="fa ' . $button_icon . '"></i>' : $button_text;
		}

		$button_output = ($button_text) ? '<a' . $button_attribs . ' id="btn-'. $this->addon->id .'" class="sppb-btn' . $button_classes . '">' . $button_text . '</a>' : '';

		//Output
		$output  = '<div class="sppb-addon sppb-addon-pricing-table ' . $alignment . ' ' . $class . '">';
		$output .= '<div class="sppb-pricing-box '. $featured .'">';
		$output .= '<div class="sppb-pricing-header">';
		$output .= ($title) ? '<div class="sppb-pricing-title">' . $title . '</div>' : '';
		$output .= ($price) ? '<span class="sppb-pricing-price">' . $price . '</span>' : '';
		$output .= ($duration) ? '<span class="sppb-pricing-duration">' . $duration . '</span>' : '';
		$output .= '</div>';

		if($pricing_content) {
			$output .= '<div class="sppb-pricing-features">';
			$output .= '<ul>';

			$features = explode("\n", $pricing_content);

			foreach ($features as $feature) {
				$output .= '<li>' . $feature . '</li>';
			}

			$output .= '</ul>';
			$output .= '</div>';
		}

		$output .= '<div class="sppb-pricing-footer">';
		$output .= $button_output;
		$output .= '</div>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	public function css() {
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$css = '';
		$style = (isset($this->addon->settings->global_background_color) && $this->addon->settings->global_background_color) ? 'border: 0; background-color: '. $this->addon->settings->global_background_color .';' : '';

		if($style) {
			$css .= $addon_id . ' .sppb-pricing-box {';
			$css .= $style;
			$css .= '}';
		}

		// Button css
		$layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';
		$css_path = new JLayoutFile('addon.css.button', $layout_path);
		$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $this->addon->settings, 'id' => 'btn-' . $this->addon->id));

		return $css;
	}

}
