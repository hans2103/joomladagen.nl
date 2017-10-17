<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted access');

class SpTypeBoxshadow{

	static function getInput($key, $attr)
	{

		if (!isset($attr['std'])) {
			$attr['std'] = '';
		} else {
			$attr['std'] = trim($attr['std']);
		}

		$attr['std'] = strip_tags($attr['std']);

		$shadow = array('', '', '', '', '');

		if($attr['std'] != '') {
			$shadow = explode(' ', $attr['std']);
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

		$output  = '';

    $output  = '<div class="sp-pagebuilder-form-group sp-pagebuilder-group-padding"' . $depend_data . '>';
		  $output .= '<label>'.$attr['title'].'</label>';
		  $output	.= '<input class="sp-pagebuilder-form-control sp-pagebuilder-addon-input" type="hidden" name="'. $key .'" value="' . $attr['std'] . '">';

		  $output	.= '<div class="sppb-row sp-pagebuilder-boxshadow-list">';
		    $output	.= '<div class="sppb-col-xs-2">';
		      $output	.= '<input type="text" class="sp-pagebuilder-form-control sppb-control-boxshadow" data-position="0" placeholder="Horizontal" value="' . $shadow[0] . '">';
		    $output	.= '</div>';
		    $output	.= '<div class="sppb-col-xs-2">';
		      $output	.= '<input type="text" class="sp-pagebuilder-form-control sppb-control-boxshadow" data-position="1" placeholder="Vertical" value="' . $shadow[1] . '">';
		    $output	.= '</div>';
    		$output	.= '<div class="sppb-col-xs-2">';
    		  $output	.= '<input type="text" class="sp-pagebuilder-form-control sppb-control-boxshadow" data-position="2" placeholder="Blur" value="' . $shadow[2] . '">';
    		$output	.= '</div>';
    		$output	.= '<div class="sppb-col-xs-2">';
    		  $output	.= '<input type="text" class="sp-pagebuilder-form-control sppb-control-boxshadow" data-position="3" placeholder="Spread" value="' . $shadow[3] . '">';
    		$output	.= '</div>';
        $output	.= '<div class="sppb-col-xs-4">';
    		  $output	.= '<input type="text" class="sp-pagebuilder-form-control sppb-control-boxshadow minicolors" data-position="3" placeholder="Color" value="' . $shadow[4] . '">';
    		$output	.= '</div>';
      $output	.= '</div>';

  		if( ( isset($attr['desc']) ) && ( isset($attr['desc']) != '' ) ) {
  			$output .= '<p class="sp-pagebuilder-help-block">' . $attr['desc'] . '</p>';
  		}

		$output .= '</div>';

		return $output;
	}

}
