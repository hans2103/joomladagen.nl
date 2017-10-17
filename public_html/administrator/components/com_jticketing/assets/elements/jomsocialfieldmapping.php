<?php
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidation');
$document=JFactory::getDocument();
//load techjoomla bootstrapper

jimport('joomla.html.parameter.element');
jimport('joomla.form.formfield');
jimport( 'joomla.html.html.access' );
jimport( 'joomla.utilities.xmlelement' );
require_once(JPATH_SITE.DS.'libraries/joomla/form/fields/textarea.php');

class JFormFieldjomsocialfieldmapping extends JFormFieldTextarea {
	var	$type = 'jomsocialfieldmapping';

	function getInput(){
		return $textarea=$this->fetchElement($this->name,$this->value, $this->element, $this->options['control']);
	}

	var $_name = 'jomsocial_fieldmap';
	function fetchElement($name, $value, &$node, $control_name)
	{
		$rows = $node->attributes()->rows;
		$cols = $node->attributes()->cols;
		$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"' );
		// To render field which already saved in db
		$fieldvalue=trim($this->renderedfield());
		// for first time installation check value or textarea is empty
		if(($fieldvalue==''))
		{
			$fieldvalue='firstname=name'."\n";
			$fieldvalue.='address= FIELD_ADDRESS '."\n";
			$fieldvalue.='city= FIELD_CITY '."\n";
			$fieldvalue.='phone= FIELD_LANDPHONE '."\n";
			$fieldvalue.='website_address= FIELD_WEBSITE '."\n";
			$fieldvalue.='user_email=email'."\n";
		}
		$fieldavi='firstname=name'."\n";
		$fieldavi.='lastname='."\n";
		$fieldavi.='address= FIELD_ADDRESS '."\n";
		$fieldavi.='address2='."\n";
		$fieldavi.='city= FIELD_CITY '."\n";
		$fieldavi.='zipcode='."\n";
		$fieldavi.='phone= FIELD_LANDPHONE '."\n";
		$fieldavi.='website_address= FIELD_WEBSITE '."\n";
		$fieldavi.='user_email=email'."\n";

	$html= '<textarea name="'.$control_name.$name.'" cols="'.$cols.'" rows="'.$rows.'" '.$class.' id="'.$control_name.$name.'" >'.$fieldvalue.'</textarea>';

	if(JVERSION<'3.0.0')
		$html.='<span style="float:left;">  '.JText::_('COM_JTICKETING_FIELDS_JOMSOCIAL').':</span>';
	else
		$html.='  '.JText::_('COM_JTICKETING_FIELDS_JOMSOCIAL').':';

	return $html.= '<textarea  cols="'.$cols.'" rows="'.$rows.'" '.$class.' disabled="disabled" >'.$fieldavi.'</textarea>';

	}
	function renderedfield()
	{
		$params=JComponentHelper::getParams('com_jticketing');
		$mapping=trim($params->get('jomsocial_fieldmap'));
		$field_explode=explode('\n',$mapping);
		$fieldvalue='';
		if(isset($mapping)) // check value exist in array
			foreach($field_explode as $field)
				$fieldvalue.=$field."\n";

	return $fieldvalue;
	}
}
?>
