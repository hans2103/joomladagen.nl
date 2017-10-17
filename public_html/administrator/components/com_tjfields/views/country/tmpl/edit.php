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

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'country.cancel')
		{
			Joomla.submitform(task, document.getElementById('country-form'));
		}
		else
		{
			if (task != 'country.cancel' && document.formvalidator.isValid(document.id('country-form')))
			{
				Joomla.submitform(task, document.getElementById('country-form'));
			}
			else
			{
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<div class="<?php echo TJFIELDS_WRAPPER_CLASS;?> tj-country">
	<form action="<?php echo JRoute::_('index.php?option=com_tjfields&layout=edit&id=' . (int) $this->item->id . '&client=' . $this->input->get('client', '', 'STRING')); ?>"
		  method="post" enctype="multipart/form-data" name="adminForm" id="country-form" class="form-validate">

		<div class="form-horizontal">
			<div class="row-fluid">
				<div class="span12 form-horizontal">
					<fieldset class="adminform">

						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
						</div>

						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('country'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('country'); ?></div>
						</div>

						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('country_3_code'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('country_3_code'); ?></div>
						</div>

						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('country_code'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('country_code'); ?></div>
						</div>

						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('country_jtext'); ?></div>
							<div class="controls">
								<?php echo $this->form->getInput('country_jtext'); ?>
								<div class="row-fluid">
									<div class="span12">
										<p class="text text-warning">
										<br/>
										<?php echo JText::_('COM_TJFIELDS_FORM_DESC_COUNTRY_COUNTRY_JTEXT_HELP'); ?>
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
