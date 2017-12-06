<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';
class JFormFieldTagList extends JFormFieldList {

	protected $type = 'taglist';

	public function getInput() {
	    if(J2Store::isPro() != 1){
	        return '<span class="alert alert-danger">'.JText::_('J2STORE_TAG_MENU_PRO_MESSAGE').'</span>';
        }
		$db = JFactory::getDbo ();
		$query = $db->getQuery (true);
		$query->select('id,alias,title,level')->from ( '#__tags' )
			->where ( 'published='.$db->q ( 1 ) )
			->where ( 'parent_id !='.$db->q ( 0 ) );
		$query->order('lft ASC');
		$db->setQuery ( $query );
		$taglist = $db->loadObjectList ();
		$attr = array();
		// Get the field options.
		// Initialize some field attributes.
		if(isset( $this->multiple ) && $this->multiple){
			$attr['multiple'] =  true;
		}

		$attr['class']= !empty($this->class) ? $this->class: '';
		// Initialize JavaScript field attributes.
		$attr ['onchange']= $this->onchange ?  $this->onchange : '';

		//generate country filter list
		$taglist_options = array();
		foreach($taglist as $row) {
			$title_prefix = $this->getDash($row->level);
			$taglist_options[$row->alias] =  $title_prefix.' '.JText::_($row->title);
		}
		return J2Html::select()->clearState()
			->type('genericlist')
			->name($this->name)
			->attribs($attr)
			->value($this->value)
			->setPlaceHolders($taglist_options)
			->getHtml();
	}

	function getDash($level){
		if($level == 1){
			return '';
		}
		$prefix = '';
		for($i=1;$i<$level;$i++){
			$prefix .= '-';
		}
		return $prefix;
	}
}
