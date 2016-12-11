<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined( 'JPATH_PLATFORM' ) or die;

/**
 * Form Field class for the Joomla Platform.
 * Provides spacer markup to be used in form layouts.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldPlaceHolder extends JFormField {
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'PlaceHolder';

	protected function getLabel() {
		return false;
	}

	/**
	 * Method to get the field input markup for a spacer.
	 * The spacer does not have accept input.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput() {
		global $isJ25;
		$html  = array();
		$class = ! empty( $this->class ) ? ' class="' . $this->class . '"' : '';
//		$html[] = '<span class="spacer">';
//		$html[] = '<span class="before"></span>';
//		$html[] = '<span' . $class . '>';

		if ( (string) $this->element['hr'] == 'true' ) {
			$html[] = '<hr' . $class . ' />';
		} else {
			$label = '';

			// Get the label text from the XML element, defaulting to the element name.
			$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
			$text = $this->translateLabel ? JText::_( $text ) : $text;

			// Build the class for the label.
//			$class = !empty($this->description) ? 'hasTooltip' : '';
//			$class = $this->required == true ? $class . ' required' : $class;
//			$class = !empty($this->class) ? ' class="' . $this->class . '"' : '';

			// Add the opening label tag and main attributes attributes.
			if ( $isJ25 ) {
				$style = 'style="margin-left: -150px; display: inline-block"';
			} else {
				$style = 'style="margin-left: -180px; display: inline-block"';
			}
			$label .= '<div ' . $style . ' id="' . $this->id . '-lbl" ' . $class . '';

			// If a description is specified, use it to build a tooltip.
			if ( ! empty( $this->description ) ) {
				JHtml::_( 'bootstrap.tooltip' );
				$label .= ' title="' . JHtml::tooltipText( trim( $text, ':' ), JText::_( $this->description ), 0 ) . '"';
			}

			// Add the label text and closing tag.
			$label .= '>';
			$label .= '<h4>' . JText::_( 'COM_OBSOCIALSUBMIT_PLACEHOLDER' ) . '</h4>';
			$label .= $text;
			$label .= '</div>';
			$html[] = $label;
		}

//		$html[] = '</span>';
//		$html[] = '<span class="after"></span>';
//		$html[] = '</span>';

		return implode( '', $html );
	}

	/**
	 * Method to get the field label markup for a spacer.
	 * Use the label text or name from the XML element as the spacer or
	 * Use a hr="true" to automatically generate plain hr markup
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   11.1
	 */
	protected function getInput_() {
		return '';
	}
}