<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SpTypeSeparator{

	static function getInput($key, $attr)
	{

		if (!isset($attr['title'])) {
			$attr['title'] = '';
		}

		// Depends
		$depend_data = '';
		if(isset($attr['depends'])) {
			$array = array();
			foreach ($attr['depends'] as $operand => $value) {
				if(!is_array($value)) {
					$array[] = array(
						$operand,
						'=',
						$value
					);
				} else {
					$array = $attr['depends'];
				}
			}

			$depend_data = " data-depends='". json_encode($array) ."'";
		}

		$output  = '<div class="sp-pagebuilder-form-group"' . $depend_data . '>';
		$output .= '<div class="sp-pagebuilder-admin-separtor">';
		if($attr['title']) $output .= '<span>'.$attr['title'].'</span>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

}
