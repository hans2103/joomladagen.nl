<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

	jimport('joomla.form.formfield');
	class JFormFieldGatewayplgsms extends JFormField {

		var	$type = 'Gatewayplgsms';

		function getInput(){
			return JFormFieldGatewayplgsms::fetchElement($this->name, $this->value, $this->element, $this->options['control']);
		}

		function fetchElement($name, $value, &$node, $control_name){

		$db = JFactory::getDBO();

		$condtion = array(0 => '\'tjsms\'');
		$condtionatype = join(',',$condtion);
 		if(JVERSION >= '1.6.0'){
			$query = "SELECT extension_id as id,name,element,enabled as published FROM #__extensions WHERE folder in ($condtionatype) AND enabled=1";
		}
		else{
			$query = "SELECT id,name,element,published FROM #__plugins WHERE folder in ($condtionatype) AND published=1";
		}
		$db->setQuery($query);
		$gatewayplugin = $db->loadobjectList();
		$options = array();
		foreach($gatewayplugin as $gateway){
			$gatewayname = ucfirst(str_replace('plug_tjsms_', '',$gateway->element));
			$options[] = JHtml::_('select.option',$gateway->element, $gatewayname);
		}

		if(JVERSION>=1.6) {
			$fieldName = $name;
		}
		else {
			$fieldName = $control_name.'['.$name.']';
		}

		return JHtml::_('select.genericlist',  $options, $fieldName, 'class="inputbox"  size="5"', 'value', 'text', $value, $control_name.$name,"plug_tjsms_clickatell" );

	}

	function fetchTooltip($label, $description, &$node, $control_name, $name){
		return NULL;
	}

	}




