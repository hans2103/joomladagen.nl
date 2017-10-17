<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2017 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

//no direct accees
defined ('_JEXEC') or die ('restricted access');

class SpTypeAcymailinglist{

	static function getInput($key, $attr){

		if(!isset($attr['std'])){
			$attr['std'] = '';
		}

		if(!isset($attr['extension'])){
			$attr['extension'] = 'AcyMailing';
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

		$acymailing_installed = self::isComponentInstalled($attr['extension']);

		if (!$acymailing_installed) {
			$output  = '<div class="sp-pagebuilder-form-group"' . $depend_data . '>';
				$output .= '<label>'.$attr['title'].'</label>';
				$output .= '<p class="sp-pagebuilder-help-block">'.JText::_('COM_SPPAGEBUILDER_GLOBAL_ACYMAILING_NOTINSTALLED').'</p>';
			$output .= '</div>';
		} else {
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('listid', 'name')));
			$query->from($db->quoteName('#__acymailing_list'));
			$query->where($db->quoteName('published') . ' = '. $db->quote(1));
			$query->order('ordering ASC');
			$db->setQuery($query);
			$lists = $db->loadObjectList();

			// multiple
			$multiple = false;
			if(isset($attr['multiple'])) {
				$multiple = 'multiple';
			}

			$output  = '<div class="sp-pagebuilder-form-group"' . $depend_data . '>';
			$output .= '<label>'.$attr['title'].'</label>';

			$output .= '<select class="sp-pagebuilder-form-control sp-pagebuilder-addon-input" name="'.$key.'" id="field_'.$key.'" '. $multiple .'>';

			// if selected all categories
			$allcat = (is_array($attr['std']) && in_array('', $attr['std'])) ? 'selected' : '';
			$output .= '<option value="" ' . $allcat . '> - '. JTEXT::_('COM_SPPAGEBUILDER_ALL') .' - </option>';

			foreach( $lists as $key=>$list ){
				if($multiple) {
					$selected = '';
					if(is_array($attr['std']) && (in_array($list->listid, $attr['std']))) {
						$selected = ' selected';
					} else if ($list->listid == $attr['std']) {
						$selected = ' selected';
					}

					$output .= '<option value="'. $list->listid .'"'. $selected .'>'. $list->name .'</option>';
				} else {
					$output .= '<option value="'.$list->listid.'" '.(($attr['std'] == $list->listid )?'selected':'').'>'.$list->name.'</option>';
				}
			}

			$output .= '</select>';

			if( ( isset($attr['desc']) ) && ( isset($attr['desc']) != '' ) )
			{
				$output .= '<p class="sp-pagebuilder-help-block">' . $attr['desc'] . '</p>';
			}

			$output .= '</div>';
		}

		return $output;
	}

	static function isComponentInstalled($component_name){

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select( 'a.enabled' );
		$query->from($db->quoteName('#__extensions', 'a'));
		$query->where($db->quoteName('a.name')." = ".$db->quote($component_name));
		$db->setQuery($query);
		$is_enabled = $db->loadResult();

		return $is_enabled;
	}

}
