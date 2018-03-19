<?php
/**
 * Layout blocks
 *
 * @author 		RolandD Cyber Produksi
 * @link 		https://csvimproved.com
 * @copyright 	Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license 	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: default_subscription.php 2273 2013-01-03 16:33:30Z RolandD $
 */

defined('_JEXEC') or die;

class CsviHtml {

	/**
	 * Bootstrap wrapper
	 *
	 * @copyright
	 * @author 		RolandD
	 * @todo
	 * @see
	 * @access 		public
	 * @param
	 * @return
	 * @since 		6.0
	 */
	public function wrapper($name, $group, $controls) {
		$language = strtoupper($name.'_'.$group);
		$html = '<div class="control-group">
					<label id="'.$group.'_'.$name.'-lbl" for="'.$group.'_'.$name.'" class="hasTip control-label" title="'.JText::_('COM_CSVI_'.$language.'_LABEL').'::'.JText::_('COM_CSVI_'.$language.'_DESC').'">
						'.JText::_('COM_CSVI_'.$language.'_LABEL').'
					</label>
					<div class="controls">
						'.$controls.'
					</div>
				</div>';
		return $html;
	}

	/**
	 * Create a Yes/No block
	 *
	 * @copyright
	 * @author 		RolandD
	 * @todo
	 * @see
	 * @access 		protected
	 * @param
	 * @return 		array	an array of options
	 * @since 		4.0
	 */
	public function yesNo($name, $group, $selected) {
		if ($selected) {
			$select1 = 'selected="selected"';
			$select0 = '';
		}
		else {
			$select1 = '';
			$select0 = 'selected="selected"';
		}

		$controls = '<select name="form['.$group.']['.$name.']" id="'.$group.'_'.$name.'">
							<option value="1"'.$select1.'>'.JText::_('JYES').'</option>
							<option value="0"'.$select0.'>'.JText::_('JNO').'</option>
						</select>';
		return self::wrapper($name, $group, $controls);
	}

	/**
	 * Create a text input field
	 *
	 * @copyright
	 * @author 		RolandD
	 * @todo
	 * @see
	 * @access 		public
	 * @param
	 * @return
	 * @since 		6.0
	 */
	public function textInput($name, $group, $selected, $attribs='') {
		$controls = '<input name="form['.$group.']['.$name.']" id="'.$group.'_'.$name.'" value="'.$selected.'" '.$attribs.' type="text">';
		return self::wrapper($name, $group, $controls);
	}

	/**
	 * Create a text area field
	 *
	 * @copyright
	 * @author 		RolandD
	 * @todo
	 * @see
	 * @access 		public
	 * @param
	 * @return
	 * @since 		6.0
	 */
	public function textArea($name, $group, $selected, $attribs='') {
		$controls = '<textarea name="form['.$group.']['.$name.']" id="'.$group.'_'.$name.'" '.$attribs.'>'.$selected.'</textarea>';
		return self::wrapper($name, $group, $controls);
	}

	/**
	 * Create an editor input field
	 *
	 * @copyright
	 * @author 		RolandD
	 * @todo
	 * @see
	 * @access 		public
	 * @param
	 * @return
	 * @since 		6.0
	 */
	public function editor($name, $group, $selected, $width, $height, $col, $row, $buttons = true) {
		$editor = JFactory::getEditor();
		$controls = $editor->display('form['.$group.']['.$name.']', $selected, $width, $height, $col, $row, $buttons);
		return self::wrapper($name, $group, $controls);
	}

	/**
	 * Create a select list
	 *
	 * @copyright
	 * @author 		RolandD
	 * @todo
	 * @see
	 * @access 		public
	 * @param
	 * @return
	 * @since 		6.0
	 */
	public function selectList($values, $name, $group, $selected, $attribs='') {
		$options = JHtml::_('select.options', $values, 'value', 'text', $selected, true);
		$controls = JHtml::_('select.genericlist', $values, $group.'_'.$name, $attribs, 'value', 'text', $selected, $group.'_'.$name, true);
		return self::wrapper($name, $group, $controls);
	}

	/**
	 * Create a calendar
	 *
	 * @copyright
	 * @author 		RolandD
	 * @todo
	 * @see
	 * @access 		public
	 * @param
	 * @return
	 * @since 		6.0
	 */
	public function calendar($name, $group, $selected, $format='%d-%m-%Y %H:%M:%S') {
		$controls = JHtml::_('calendar', $selected, 'form['.$group.']['.$name.']', $group.'_'.$name, $format);
		return self::wrapper($name, $group, $controls);
	}

	/**
	 * Create an image select list
	 *
	 * @copyright
	 * @author 		RolandD
	 * @todo
	 * @see
	 * @access 		public
	 * @param
	 * @return
	 * @since 		6.0
	 */
	public function image($name, $group, $selected, $directory=null) {
		// Load the modal behavior script.
		JHtml::_('behavior.modal');

		// Build the script.
		$script = array();
		$script[] = '	function jInsertFieldValue(value, id) {';
		$script[] = '		var old_value = document.id(id).value;';
		$script[] = '		if (old_value != value) {';
		$script[] = '			var elem = document.id(id);';
		$script[] = '			elem.value = value;';
		$script[] = '			elem.fireEvent("change");';
		$script[] = '			if (typeof(elem.onchange) === "function") {';
		$script[] = '				elem.onchange();';
		$script[] = '			}';
		$script[] = '			jMediaRefreshPreview(id);';
		$script[] = '		}';
		$script[] = '	}';

		$script[] = '	function jMediaRefreshPreview(id) {';
		$script[] = '		var value = document.id(id).value;';
		$script[] = '		var img = document.id(id + "_preview");';
		$script[] = '		if (img) {';
		$script[] = '			if (value) {';
		$script[] = '				img.src = "' . JURI::root() . '" + value;';
		$script[] = '				document.id(id + "_preview_empty").setStyle("display", "none");';
		$script[] = '				document.id(id + "_preview_img").setStyle("display", "");';
		$script[] = '			} else { ';
		$script[] = '				img.src = ""';
		$script[] = '				document.id(id + "_preview_empty").setStyle("display", "");';
		$script[] = '				document.id(id + "_preview_img").setStyle("display", "none");';
		$script[] = '			} ';
		$script[] = '		} ';
		$script[] = '	}';

		$script[] = '	function jMediaRefreshPreviewTip(tip)';
		$script[] = '	{';
		$script[] = '		var img = tip.getElement("img.media-preview");';
		$script[] = '		tip.getElement("div.tip").setStyle("max-width", "none");';
		$script[] = '		var id = img.getProperty("id");';
		$script[] = '		id = id.substring(0, id.length - "_preview".length);';
		$script[] = '		jMediaRefreshPreview(id);';
		$script[] = '		tip.setStyle("display", "block");';
		$script[] = '	}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

		// Initialize variables.
		$html = array();
		$attr = '';

		// The text field.
		$html[] = '<div class="input-prepend input-append">';

		if ($selected && file_exists(JPATH_ROOT . '/' . $selected))
		{
			$src = JURI::root() . $selected;
		}
		else
		{
			$src = '';
		}

		// The Preview.
		$options = array(
				'onShow' => 'jMediaRefreshPreviewTip',
		);
		JHtml::_('behavior.tooltip', '.hasTipPreview', $options);

		if ($selected && file_exists(JPATH_ROOT . '/' . $selected))
		{
			$src = JURI::root() . $selected;
		}
		else
		{
			$src = '';
		}

		$width = 300;
		$height = 200;
		$style = '';
		$style .= ($width > 0) ? 'max-width:' . $width . 'px;' : '';
		$style .= ($height > 0) ? 'max-height:' . $height . 'px;' : '';

		$imgattr = array(
			'id' => $group.'_'.$name . '_preview',
			'class' => 'media-preview',
			'style' => $style,
		);
		$img = JHtml::image($src, JText::_('JLIB_FORM_MEDIA_PREVIEW_ALT'), $imgattr);
		$previewImg = '<div id="' . $group.'_'.$name . '_preview_img"' . ($src ? '' : ' style="display:none"') . '>' . $img . '</div>';
		$previewImgEmpty = '<div id="' . $group.'_'.$name . '_preview_empty"' . ($src ? ' style="display:none"' : '') . '>'
			. JText::_('JLIB_FORM_MEDIA_PREVIEW_EMPTY') . '</div>';

		$html[] = '<div class="media-preview add-on">';

		$tooltip = $previewImgEmpty . $previewImg;
		$options = array(
			'title' => JText::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'),
			'text' => '<i class="icon-eye icon-eye-open"></i>',
			'class' => 'hasTipPreview'
		);
		$html[] = JHtml::tooltip($tooltip, $options);

		$html[] = '</div>';

		$html[] = '	<input type="text" class="input-small" name="' . $name . '" id="' . $group.'_'.$name . '"' . ' value="'
				. htmlspecialchars($selected, ENT_COMPAT, 'UTF-8') . '"' . ' readonly="readonly" />';

		if ($selected && file_exists(JPATH_ROOT . '/' . $selected))
		{
			$folder = explode('/', $selected);
			array_shift($folder);
			array_pop($folder);
			$folder = implode('/', $folder);
		}
		elseif (file_exists(JPATH_ROOT . '/' . JComponentHelper::getParams('com_media')->get('image_path', 'images') . '/' . $directory))
		{
			$folder = $directory;
		}
		else
		{
			$folder = '';
		}

		// The button.
		JHtml::_('bootstrap.tooltip');

		$html[] = '<a class="modal btn" title="' . JText::_('JLIB_FORM_BUTTON_SELECT') . '"' . ' href="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=com_csvi&amp;author=&amp;fieldid=' . $group.'_'.$name . '&amp;folder=' . $folder . '" rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
		$html[] = JText::_('JLIB_FORM_BUTTON_SELECT') . '</a><a class="btn hasTooltip" title="' . JText::_('JLIB_FORM_BUTTON_CLEAR') . '"' . ' href="#" onclick="';
		$html[] = 'jInsertFieldValue(\'\', \'' . $group.'_'.$name . '\');';
		$html[] = 'return false;';
		$html[] = '">';
		$html[] = '<i class="icon-remove"></i></a>';

		$html[] = '</div>';


		$controls = implode("\n", $html);
		return self::wrapper($name, $group, $controls);
	}
}
