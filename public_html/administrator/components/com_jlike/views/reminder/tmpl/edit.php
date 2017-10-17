<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Jlike
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (C) 2016 - 2017. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'media/com_jlike/css/form.css');
$document->addScript(JUri::root() . 'libraries/techjoomla/assets/js/tjvalidator.js');

$input = JFactory::getApplication()->input;
$reminder_id = $input->get('id',0,'INT');
$extension = $input->get('extension','','CMD');

?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {

		// Disable select content if the content_type is not selected
		if (jQuery( "#jform_content_type option:selected" ).length == 0)
		{
			jQuery("#jform_select_content_chzn ul li input").attr('disabled','disabled');
		}

		// Reminder already created display selected content_ids of content_type
		jQuery('#no_content_type').hide();
		getContentByType();
	});

	Joomla.submitbutton = function (task) {
		if (task == 'reminder.cancel') {
			Joomla.submitform(task, document.getElementById('reminder-form'));
		}
		else {

			if (task != 'reminder.cancel' && document.formvalidator.isValid(document.id('reminder-form'))) {

				Joomla.submitform(task, document.getElementById('reminder-form'));
			}
			else {
				alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}

	// function which will call ajax
    function getContentByType()
    {
		var content_type = jQuery( "#jform_content_type option:selected" ).val();
		var reminder_id = <?php echo $reminder_id; ?>;

		// Disable select content if the content_type is not selected
		if (jQuery( "#jform_content_type option:selected" ).length == 0)
		{
			jQuery("#jform_select_content_chzn ul li input").attr('disabled','disabled');
		}

			if (content_type)
			{
				jQuery.ajax({
				url: 'index.php?option=com_jlike&task=reminder.getContentByType',
				type: 'GET',
				data:'content_type='+content_type+'&reminder_id='+reminder_id,
				dataType: 'json',
				success: function(data)
				{
					jQuery('#no_content_type').hide();

					if (data.all.length == 0)
 					{
						jQuery('#no_content_type').show();
 					}

					// Remove all the elements from the select box
					jQuery("#jform_select_content").empty();

					// Display All the content_ids of content type
					  jQuery.each(data.all, function(index, itemData) {
						var newOption = "<option value='" + itemData.id + "'>" + itemData.title + "</option>";
						jQuery("#jform_select_content").append(newOption);

					});

					// Display already selected content ids
					jQuery.each(data.selected, function(index, itemData) {
						  jQuery('#jform_select_content option[value="' + itemData.id + '"]').prop('selected', true);

						});

					jQuery("#jform_select_content").trigger("liszt:updated");

				}
			});
		}
	}

	// Validate emails
	 function validemail()
    {
		var emailList= jQuery("#jform_cc").val();
		var emails = emailList.split(",");
		var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

		for (var i = 0; i < emails.length; i++)
		{
			if(! regex.test(emails[i]))
			{
				alert(Joomla.JText._('COM_JLIKE_FORM_REMINDER_NOTVALID_CC'));
				jQuery("#jform_cc").val('');
			}
		}
	}

</script>
<form
	action="<?php echo JRoute::_('index.php?option=com_jlike&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="reminder-form" class="form-validate">

	<div  class="techjoomla-bootstrap">
	<table border="0" width="100%" cellspacing="10" class="adminlist">
		<tr>
			<td width="70%" align="left" valign="top">

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_JLIKE_TITLE_REMINDER', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset class="adminform">

				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
				<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
				<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
				<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
				<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
				<input type="hidden" name="extension" value="<?php echo $extension; ?>" />

				<?php if(empty($this->item->created_by)){ ?>
					<input type="hidden" name="jform[created_by]" value="<?php echo JFactory::getUser()->id; ?>" />

				<?php }
				else{ ?>
					<input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>" />

				<?php } ?>
				<?php if(empty($this->item->modified_by)){ ?>
					<input type="hidden" name="jform[modified_by]" value="<?php echo JFactory::getUser()->id; ?>" />

				<?php }
				else{ ?>
					<input type="hidden" name="jform[modified_by]" value="<?php echo $this->item->modified_by; ?>" />

				<?php } ?>
				<?php echo $this->form->renderField('title'); ?>

			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('content_type'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('content_type'); ?></div>
			</div>
<!--

				<?php //echo $this->form->renderField('content_type'); ?>
-->
				<div class="control-group">
				<span>
					<?php echo JText::_("COM_JLIKE_FORM_LBL_REMINDER_SELECT_CONTENT_IDS"); ?>
				</span>
				</div>
				<?php echo $this->form->renderField('select_content'); ?>

				<div class="control-group" id="no_content_type">
					<div class="control-label"></div>
					<div class="controls"><?php echo JText::_("COM_JLIKE_FORM_REMINDER_CONTENTTYPE_EMPTY"); ?></div>
				</div>

				<?php echo $this->form->renderField('days_before'); ?>
				<?php echo $this->form->renderField('subject'); ?>
				<?php echo $this->form->renderField('cc'); ?>
				<?php echo $this->form->renderField('email_template'); ?>
<?php /*
				<input type="hidden" name="jform[last_sent_limit]" value="<?php echo $this->item->last_sent_limit; ?>" />
*/ ?>
<!--
					<?php if ($this->state->params->get('save_history', 1)) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
					</div>
					<?php endif; ?>
-->
				</fieldset>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>



		<?php echo JHtml::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value=""/>
		<?php echo JHtml::_('form.token'); ?>

	</div>
	</td>
		<td width="30%" valign="top">
				<table>

					<tr>
						<td colspan="2"><div class="alert alert-info"><?php echo JText::_('COM_JLIKE_EB_TAGS_DESC') ?> <br/></div>
										</tr>

					<tr>
						<td width="30%"><b>&nbsp;&nbsp;{content_title} </b> </td>
						<td><?php echo JText::_('TAGS_CONTENT_NAME'); ?></td>

					</tr>

					<tr>
						<td><b>&nbsp;&nbsp;{username}</b> </td>
						<td><?php echo JText::_('TAGS_CONTENT_ASSIGNMENT_USERNAME'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;{name}</b> </td>
						<td><?php echo JText::_('TAGS_CONTENT_ASSIGNMENT_NAME'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;{content_link}</b></td>
						<td><?php echo JText::_('TAGS_CONTENT_LINK'); ?></td>

					</tr>

					<tr>
						<td><b>&nbsp;&nbsp;{content_due_date} </b></td>
						<td><?php echo JText::_('TAGS_CONTENT_DUE_DATE'); ?></td>

					</tr>

					<tr>
						<td><b>&nbsp;&nbsp;{days_before}</b></td>
						<td><?php echo JText::_('TAGS_REMINDER_DAYS'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;{content_url}</b></td>
						<td><?php echo JText::_('TAGS_CONTENT_ACTUAL_LINK'); ?></td>
					</tr>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
