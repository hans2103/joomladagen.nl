<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SpTypeEditor
{

	static function getInput($key, $attr)
	{

		if(!isset($attr['std'])){
			$attr['std'] = '';
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
		$output .= '<label>'.$attr['title'].'</label>';

		$apps = JFactory::getApplication();

		$conf   = JFactory::getConfig();
		$name   = $conf->get('editor');

		if ($name !== 'jce') {
			$name = 'tinymce';
		}
		$editor = JEditor::getInstance($name);
		$output	.= $editor->display( $key, htmlspecialchars($attr['std'], ENT_COMPAT, 'UTF-8'), '100%', 300, 100, 70,false);

		if( ( isset($attr['desc']) ) && ( isset($attr['desc']) != '' ) )
		{
			$output .= '<p class="sp-pagebuilder-help-block">' . $attr['desc'] . '</p>';
		}

		$output .= '</div>';

		return $output;
	}

}
