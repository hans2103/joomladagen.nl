<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('JPATH_PLATFORM') or die;

class JFormFieldObSSTextarea extends JFormField
{
	protected $type = 'obSSTextarea';
	public static $loadjs = false;

	protected function getInput()
	{
		if(!JFormFieldObSSTextarea::$loadjs){
			JHtml::_( 'stylesheet', JURI::root().'administrator/components/com_obsocialsubmit/assets/wysiwygbbcode/editor.css' );
			JHtml::_('script', JURI::root().'administrator/components/com_obsocialsubmit/assets/wysiwygbbcode/editor.js', false, true);
			JFormFieldObSSTextarea::$loadjs = true;
		}
		// Translate placeholder text

		$layout = isset($this->element['layout'])?$this->element['layout']:0;
		$hint	= $this->translateHint ? JText::_($this->hint) : $this->hint;

		// Initialize some field attributes.
		$class			= !empty($this->element['class']) ? ' class="' . $this->element['class'] . '"' : '';
		$disabled		= $this->disabled ? ' disabled' : '';
		$readonly		= $this->readonly ? ' readonly' : '';
		$columns		= $this->columns ? ' cols="' . $this->columns . '"' : '';
		$rows			= $this->rows ? ' rows="' . $this->rows . '"' : '';
		$required		= $this->required ? ' required aria-required="true"' : '';
		$hint			= $hint ? ' placeholder="' . $hint . '"' : '';
		$autocomplete	= !$this->autocomplete ? ' autocomplete="off"' : ' autocomplete="' . $this->autocomplete . '"';
		$autocomplete	= $autocomplete == ' autocomplete="on"' ? '' : $autocomplete;
		$autofocus		= $this->autofocus ? ' autofocus' : '';
		$spellcheck		= $this->spellcheck ? '' : ' spellcheck="false"';

		// Initialize JavaScript field attributes.
		$onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
		$onclick = $this->onclick ? ' onclick="' . $this->onclick . '"' : '';

		// Including fallback code for HTML5 non supported browsers.
		//JHtml::_('jquery.framework');
		//JHtml::_('script', 'system/html5fallback.js', false, true);
		
		$tags = $this->getTags();

		$html = '<div class="richeditor">';
		if($layout==1){
			$html .= '<div class="editbar">';
			
			
			if( $tags ) {
				foreach( $tags as $tag ) {
					$html .= '<a href="javascript:MyEditor.doAction(\'' . $this->id . '\',\'InsertTag\',\''.$tag->value.'\');"><span class="label label-info" title="'.$tag->text.'" >'.$tag->value.'</span></a>';
				}
			}
			$html .= '</div>';
		} else {
			$html .= '<div class="editbar">';
			$html .= '	<div id="obss-insert-tag-btn-wrap-'.$this->id.'" class="obss-insert-tag-btn-wrap">';
			$html .= '		<input id="obss_test_el" class="btn btn-small btn-success" style="float:right;" onclick="obssShowTags(document.getElementById(\'obss-insert-tag-btn-wrap-'.$this->id.'\'));" type="button" value="Insert Tags" />';
			$html .= '		<div style="clear:both;"></div>';
			$html .= '		<div class="obss-tags-container">';
			$html .= '			<ul>';
			if( $tags ) {
				foreach( $tags as $tag ) {
					//$html .= '<span class="label obss_tag drag_drop" title="'.$tag->text.'" onclick="MyEditor.doAction(\'' . $this->id . '\',\'InsertTag\',\''.$tag->value.'\');">'.$tag->value.'</span>';
					$html 	.= '<li>';
					$html 	.= 		'<a href="javascript:MyEditor.doAction(\'' . $this->id . '\',\'InsertTag\',\''.$tag->value.'\');obssShowTags(document.getElementById(\'obss-insert-tag-btn-wrap-'.$this->id.'\'));"><span class="label label-info" title="'.$tag->text.'" onclick="">'.$tag->value.'</span></a>';
					$html 	.= '</li>';
				}
			}

			$html .='			</ul>';
			$html .= '		</div>';
			$html .= '	</div>';
			$html .= '	<div style="clear:both;"></div>';

			$html .= '</div>';
		}
		
		$html .= '<div class="editor_container">';
		$html .= '<textarea name="' . $this->name . '" id="' . $this->id . '"' . $columns . $rows . $class
			. $onchange . $onclick . $autofocus . $spellcheck . ' >'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '</textarea>';
		$html .= '</div>';
		$html .= '<div class="editbar">';

		$html .= '<button style="float:right;" class="btn btn-small" title="switch to source" type="button" onclick="MyEditor.doAction(\'' . $this->id . '\',\'SwitchEditor\');">'.JText::_('COM_OBSOCIALSUBMIT_BBCODE_EDITOR_TOGGLE').'</button>';
		$html .= '<div style="clear:both;"></div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<script type="text/javascript">';
		$html .= 'MyEditor.initEditor("' . $this->id . '", true);';
		$html .= '</script>';
		return $html;
	}
	
	protected function getTags()
	{
		$options = array();

		foreach ($this->element->children() as $option)
		{
			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			// Filter requirements
			if ($requires = explode(',', (string) $option['requires']))
			{
				// Requires multilanguage
				if (in_array('multilanguage', $requires) && !JLanguageMultilang::isEnabled())
				{
					continue;
				}

				// Requires associations
				if (in_array('associations', $requires) && !JLanguageAssociations::isEnabled())
				{
					continue;
				}
			}

			$value = (string) $option['value'];

			$disabled = (string) $option['disabled'];
			$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');

			$disabled = $disabled || ($this->readonly && $value != $this->value);

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
				'select.option', $value,
				JText::alt(trim((string) $option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text',
				$disabled
			);

			// Set some option attributes.
			$tmp->class = (string) $option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) $option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
