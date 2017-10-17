<?php
defined('JPATH_BASE') or die();
jimport('joomla.html.parameter.element');
jimport('joomla.html.html');
jimport('joomla.form.formfield');
class JFormFieldHeader extends JFormField
{
	var	$type='Header';
	function getInput()
	{
		$document=JFactory::getDocument();
		$document->addStyleSheet(JUri::base().'components/com_jlike/assets/css/like.css');
		$return='
		<div class="jlike_div_outer">
			<div class="jlike_div_inner">
				'.JText::_($this->value).'
			</div>
		</div>';
		return $return;
	}
}
?>
