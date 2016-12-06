<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;
global $isJ25;
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.tooltip');
if(!$isJ25){
	JHtml::_('bootstrap.popover');
}
$document = JFactory::getDocument();
$type = JRequest::getVar('type','adapter');
?>

<h2><?php echo JText::_('COM_OBSOCIALSUBMIT_'.strtoupper($type).'_TYPE_CHOOSE')?></h2>
<ul id="new-modules-list" class="list list-striped">
<?php foreach ($this->items as &$item) : ?>
	<?php
		$addon_type='';
		if( $item->folder=='obss_extern' ) {
			$addon_type='connection';
		} else {
			$addon_type='adapter';
		}

		// Prepare variables for the link.
		$link		= 'index.php?option=com_obsocialsubmit&task='.$addon_type.'.add&addon='. $item->element;
		$name		= $this->escape($item->name);
		$desc		= JHTML::_('string.truncate', ($this->escape($item->desc)), 200);
		$short_desc	= JHTML::_('string.truncate', ($this->escape($item->desc)), 90);
	?>
	<?php if ($document->direction != "rtl") : ?>
	<li>
		<a href="<?php echo JRoute::_($link);?>">
			<strong><?php echo $name; ?></strong>
		</a>
		<small class="hasPopover" data-placement="right" title="<?php echo $name; ?>" data-content="<?php echo $desc; ?>"><?php echo $short_desc; ?></small>
	</li>
	<?php else : ?>
	<li>
		<small rel="popover" data-placement="left" title="<?php echo $name; ?>" data-content="<?php echo $desc; ?>"><?php echo $short_desc; ?></small>
		<a href="<?php echo JRoute::_($link);?>">
			<strong><?php echo $name; ?></strong>
		</a>
	</li>
	<?php endif?>
<?php endforeach; ?>
</ul>
<div class="clr"></div>
