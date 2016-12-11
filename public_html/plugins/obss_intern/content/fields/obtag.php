<?php
/**
 * @package          obRSS
 * @version          $Id: obtag.php 216 2014-02-17 03:35:04Z tung $
 * @author           Tung Pham - foobla.com
 * @copyright    (c) 2007-2014 foobla.com. All rights reserved.
 * @license          GNU/GPL, see LICENSE
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
global $isJ25;
if ( ! $isJ25 && is_file( JPATH_SITE . DS . 'libraries' . DS . 'cms' . DS . 'form' . DS . 'field' . DS . 'tag.php' ) ) {
	require_once JPATH_SITE . DS . 'libraries' . DS . 'cms' . DS . 'form' . DS . 'field' . DS . 'tag.php';


//jimport('joomla.form.form.fields.list');
	class JFormFieldObtag extends JFormFieldTag {
		public $type = 'obtag';

		protected function getInput() {

			$html = parent::getInput();

			return $html;
		}
	}
} else {
	require_once JPATH_SITE . DS . 'libraries' . DS . 'joomla' . DS . 'form' . DS . 'fields' . DS . 'text.php';

	class JFormFieldObtag extends JFormFieldText {
		public $type = 'obtag';

		protected function getInput() {
			$html   = "";
			$script = '
				<script type="text/javascript">
					var tag_label = document.getElementById("content_default_tags-lbl");
					tag_label.hide();
				</script>
			';

			return $html . $script;
		}
	}
}
/*

jimport('joomla.html.parameter.element');

class JElementK2categories extends JElement
{

	var	$_name = 'K2categories';
		
	
	public static function fetchElement($name, $value, &$node, $control_name)
	{
		$db = JFactory::getDBO();
		$query = "SELECT u.id as value, u.name as text FROM #__k2_categories as u ORDER BY u.name";	
	
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ($rows) {
			$options[] = JHTML::_('select.option', '', JText::_('COM_OBGRABBER_ADAPTER_K2_SELECT_CATEGORY'));
			$options = array_merge($options, $rows);
			$authors = JHTML::_('select.genericlist', $options, ''.$control_name.'['.$name.'][]', 'multiple="multiple"', 'value', 'text', $value, $control_name.$name );
			return $authors;
		} else {
			return JText::_('COM_OBGRABBER_ADAPTER_K2_PARAMS_NO_DATA');
		}
	}
}

*/