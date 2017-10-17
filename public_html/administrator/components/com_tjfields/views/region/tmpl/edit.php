<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'region.cancel')
		{
			Joomla.submitform(task, document.getElementById('region-form'));
		}
		else
		{
			if (task != 'region.cancel' && document.formvalidator.isValid(document.id('region-form')))
			{
				Joomla.submitform(task, document.getElementById('region-form'));
			}
			else
			{
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}

	techjoomla.jQuery(document).ready(function()
	{
		techjoomla.jQuery('#jform_country_id').attr('data-chosen', 'com_tjfields');
	});
</script>

<div class="<?php echo TJFIELDS_WRAPPER_CLASS;?> tj-region">
	<form
		action="<?php echo JRoute::_('index.php?option=com_tjfields&layout=edit&id=' . (int) $this->item->id . '&client=' . $this->input->get('client', '', 'STRING')); ?>"
		method="post" enctype="multipart/form-data" name="adminForm" id="region-form" class="form-validate">

		<div class="form-horizontal">
			<div class="row-fluid">
				<div class="span12 form-horizontal">
					<fieldset class="adminform">

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('id'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('region'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('region'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('region_3_code'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('region_3_code'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('region_code'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('region_code'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('country_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('country_id'); ?>
							</div>
						</div>

						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('region_jtext'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('region_jtext'); ?>
								<div class="row-fluid">
									<div class="span12">
										<p class="text text-warning">
										<br/>
										<?php echo JText::_('COM_TJFIELDS_FORM_DESC_REGION_REGION_JTEXT_HELP'); ?>
										</p>
									</div>
								</div>
							</div>
						</div>

					</fieldset>
				</div>
			</div>

			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
