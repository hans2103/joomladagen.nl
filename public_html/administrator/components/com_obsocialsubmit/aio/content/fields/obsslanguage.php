<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 5/4/2015
 * Time: 1:49 PM
 */
defined('JPATH_PLATFORM') or die;
require_once JPATH_SITE.DS.'libraries'.DS.'joomla'.DS.'form'.DS.'fields'.DS.'list.php';
//JFormHelper::loadFieldClass('list');
class JFormFieldObsslanguage extends JFormFieldList
{
	public $type = 'Obsslanguage';
	protected function getOptions(){
		$rows	= JLanguageHelper::createLanguageList($this->value, JPATH_SITE, true, true);
		$option	= JHTML::_('select.option', '*', JText::_('JALL_LANGUAGE'), 'value', 'text');
		array_unshift($rows, $option);
		return $rows;
	}
}