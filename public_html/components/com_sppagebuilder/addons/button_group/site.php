<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderAddonButton_group extends SppagebuilderAddons{

	public function render() {

		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? ' ' . $this->addon->settings->class : '';
		$class .= (isset($this->addon->settings->alignment) && $this->addon->settings->alignment) ? ' ' . $this->addon->settings->alignment : '';

		$output  = '<div class="sppb-addon sppb-addon-button-group' . $class . '">';
		$output .= '<div class="sppb-addon-content">';

		foreach ($this->addon->settings->sp_button_group_item as $key => $value) {
			if($value->title) {
				$class  = (isset($value->type) && $value->type) ? ' sppb-btn-' . $value->type : '';
				$class .= (isset($value->size) && $value->size) ? ' sppb-btn-' . $value->size : '';
				$class .= (isset($value->block) && $value->block) ? ' ' . $value->block : '';
				$class .= (isset($value->shape) && $value->shape) ? ' sppb-btn-' . $value->shape: ' sppb-btn-rounded';
				$class .= (isset($value->appearance) && $value->appearance) ? ' sppb-btn-' . $value->appearance : '';
				$attribs = (isset($value->target) && $value->target) ? ' target="' . $value->target . '"': '';
				$attribs .= (isset($value->url) && $value->url) ? ' href="' . $value->url . '"': '';
				$attribs .= ' id="btn-' . ($this->addon->id + $key) . '"';
				$text = (isset($value->title) && $value->title) ? $value->title: '';
				$icon = (isset($value->icon) && $value->icon) ? $value->icon: '';
				$icon_position = (isset($value->icon_position) && $value->icon_position) ? $value->icon_position: 'left';

				if($icon_position == 'left') {
					$text = ($icon) ? '<i class="fa ' . $icon . '"></i> ' . $text : $text;
				} else {
					$text = ($icon) ? $text . ' <i class="fa ' . $icon . '"></i>' : $text;
				}

				$output  .= '<a' . $attribs . ' class="sppb-btn ' . $class . '">' . $text . '</a>';
			}
		}

		$output .= '</div>';
		$output .= '</div>';

		return $output;

	}

	public function css() {

		$addon_id = '#sppb-addon-' . $this->addon->id;
		$layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';
		$margin = ($this->addon->settings->margin && $this->addon->settings->margin) ? $this->addon->settings->margin : '';

		$css = '';
		if($margin) {
			$css .= $addon_id . ' .sppb-addon-content {';
			$css .= 'margin: -' . (int) $margin . 'px;';
			$css .= '}';

			$css .= $addon_id . ' .sppb-addon-content .sppb-btn {';
			$css .= 'margin: ' . (int) $margin . 'px;';
			$css .= '}';
		}

		// Buttons style
		foreach ($this->addon->settings->sp_button_group_item as $key => $value) {
			if($value->title) {
				$css_path = new JLayoutFile('addon.css.button', $layout_path);

				$options = new stdClass;
				$options->button_type = (isset($value->type) && $value->type) ? $value->type : '';
				$options->button_appearance = (isset($value->appearance) && $value->appearance) ? $value->appearance : '';
				$options->button_color = (isset($value->color) && $value->color) ? $value->color : '';
				$options->button_color_hover = (isset($value->color_hover) && $value->color_hover) ? $value->color_hover : '';
				$options->button_background_color = (isset($value->background_color) && $value->background_color) ? $value->background_color : '';
				$options->button_background_color_hover = (isset($value->background_color_hover) && $value->background_color_hover) ? $value->background_color_hover : '';
				$options->button_fontstyle = (isset($value->fontstyle) && $value->fontstyle) ? $value->fontstyle : '';
				$options->button_letterspace = (isset($value->letterspace) && $value->letterspace) ? $value->letterspace : '';

				$css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $options, 'id' => 'btn-' . ($this->addon->id + $key) ));
			}
		}

		return $css;
	}
}
