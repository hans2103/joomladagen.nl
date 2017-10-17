<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

//no direct accees
defined ('_JEXEC') or die ('restricted access');

class SpTypeK2category{

	static function getInput($key, $attr){

		if(!isset($attr['std'])){
			$attr['std'] = '';
		}

		if(!isset($attr['extension'])){
			$attr['extension'] = 'com_k2';
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

		$k2_installed = self::isComponentInstalled($attr['extension']);

		if (!$k2_installed) {
			$output  = '<div class="sp-pagebuilder-form-group"' . $depend_data . '>';
			$output .= '<label>'.$attr['title'].'</label>';
			$output .= '<p class="sp-pagebuilder-help-block">'.JText::_('COM_SPPAGEBUILDER_ADDON_ARTICLE_ERORR_K2_NOTINSTALLED').'</p>';
			$output .= '</div>';

		} else {
			$db = JFactory::getDBO();
			$query = 'SELECT m.* FROM #__k2_categories m WHERE trash = 0 ORDER BY parent, ordering';
			$db->setQuery($query);
			$mitems = $db->loadObjectList();
			$children = array();
			if ($mitems){
					foreach ($mitems as $v)
					{
							if (K2_JVERSION != '15')
							{
									$v->title = $v->name;
									$v->parent_id = $v->parent;
							}
							$pt = $v->parent;
							$list = @$children[$pt] ? $children[$pt] : array();
							array_push($list, $v);
							$children[$pt] = $list;
					}
			}

			$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
			$mitems = array();

			foreach ($list as $item)
			{
					$item->treename = JString::str_ireplace('&#160;', '- ', $item->treename);
					$mitems[] = JHTML::_('select.option', $item->id, '   '.$item->treename);
			}

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
			$output .= '<option value="" ' . $allcat . '> - '. JTEXT::_('COM_SPPAGEBUILDER_ADDON_ARTICLE_ALL_CAT') .' - </option>';

			foreach( $mitems as $key=>$category ){
				if($multiple) {
					$selected = '';
					if(is_array($attr['std']) && (in_array($category->value, $attr['std']))) {
						$selected = ' selected';
					} else if ($category->value == $attr['std']) {
						$selected = ' selected';
					}

					$output .= '<option value="'. $category->value .'"'. $selected .'>'. $category->text .'</option>';
				} else {
					$output .= '<option value="'.$category->value.'" '.(($attr['std'] == $category->value )?'selected':'').'>'.$category->text.'</option>';
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
