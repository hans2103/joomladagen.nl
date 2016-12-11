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
JHtml::_('behavior.combobox');
JHtml::_('behavior.formvalidation');
if( !$isJ25 ){
	JHtml::_('formbehavior.chosen', 'select');
}

// Get Params Fieldsets
$this->fieldsets = $this->form->getFieldsets('params');
$script = "Joomla.submitbutton = function(task)
	{
			if (task == 'connection.cancel' || document.formvalidator.isValid(document.id('connection-form'))) {
				Joomla.submitform(task, document.getElementById('connection-form'));
				if (self != top) {
					window.top.setTimeout('window.parent.SqueezeBox.close()', 1000);
				}
			} else {
				alert('".$this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'))."');
			}
	}";
JFactory::getDocument()->addScriptDeclaration($script);
$class4fieldset = ( $isJ25 )?'class="radio"':'';
?>
<form action="<?php echo JRoute::_('index.php?option=com_obsocialsubmit&view=connection&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="connection-form" class="form-validate form-horizontal">
<div id="foobla">
	<div class="row-fluid">
	<div class="span6 form-horizontal">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('title'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('title'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('published'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('published'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('description'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('description'); ?>
				</div>
			</div>
			<div class="control-group">
				<h3>
					<?php echo JText::_('COM_OBSOCIALSUBMIT_PLUGIN_INFORMATION'); ?>
				</h3>
				<div class="control-group">
					<div class="control-label">
						<label><?php echo JText::_('COM_OBSOCIALSUBMIT_TYPE');?></label>
					</div>
					<div class="controls">
						<fieldset <?php echo $class4fieldset; ?>>
							<label>
								<span class="label label-info"><strong><?php echo $this->item->xml->name;?></strong></span>
							</label>
						</fieldset>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label><?php echo JText::_('COM_OBSOCIALSUBMIT_VERSION');?></label>
					</div>
					<div class="controls">
						<fieldset <?php echo $class4fieldset; ?>><label><span class="label label-info"><strong><?php echo $this->item->xml->version;?></strong></span></label></fieldset>
					</div>
				</div>
				<div style="clear:both;">
					<?php if ($this->item->xml) : ?>
						<?php if ($text = trim($this->item->xml->description)) : ?>
							<p><?php echo JText::_($text); ?></p>
						<?php endif; ?>
					<?php else : ?>
						<p class="alert alert-error"><?php echo JText::_('COM_OBSOCIALSUBMIT_ERR_XML'); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>


		<div class="span6 form-horizontal">
			<?php echo $this->loadTemplate('options'); ?>
		</div>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
		<?php echo $this->form->getInput('addon'); ?>
		<?php echo $this->form->getInput('addon_type'); ?>
	</div>
</div>
</form>