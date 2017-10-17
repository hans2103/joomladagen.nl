<?php
	/**
	 * @version    1.6.2
	 * @package    Com_Jticketing
	 * @copyright  Copyright (C) 2015. All rights reserved.
	 * @license    GNU General Public License version 2 or later; see LICENSE.txt
	 * @author     Renuka Shikarkhane <renuka_s@tekdi.net> - http://
	 */

// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');
$sms_template = 0;
$rem_content = $this->form->getInput('sms_template');
$input=JFactory::getApplication()->input;
$rid = $input->get( 'id','','INT' );

if (!empty($rem_content))
{
	$sms_template_len = strlen($this->form->getvalue('sms_template'));
}

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_jticketing/assets/css/jticketing.css');
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function()
    {
		var edit_chars = 140-<?php echo $sms_template_len?>;
		var characters_original = 140;
		var display_chars = characters = 140;

		if (edit_chars<characters_original)
		{
			var display_chars = edit_chars;
		}

		js("#counter").append("You have <strong>"+  display_chars+"</strong> characters remaining. <?php echo JText::_('COM_JTICKETING_CHARS_REM_CONSIDER_TAGS');?>");
		js("#jform_sms_template").keypress(function(){
		if(js(this).val().length > characters)
		{
			js(this).val(js(this).val().substr(0, characters));
		}
		var remaining = characters -  js(this).val().length;
		js("#counter").html("You have <strong>"+  remaining+"</strong> characters remaining. <?php echo JText::_('COM_JTICKETING_CHARS_REM_CONSIDER_TAGS')?>");
			if(remaining <= 10)
			{
				js("#counter").css("color","red");
			}
			else
			{
				js("#counter").css("color","black");
			}
		});


    });

    Joomla.submitbutton = function(task)
    {

		if (task == 'reminder.apply')
		{
			var check = reminderDuplicateCheck();

			if(check === 0)
			{
				return false;
			}
		}

		if (task == 'reminder.save')
		{
			var check = reminderDuplicateCheck();

			if(check === 0)
			{
				return false;
			}
		}

		if (task == 'reminder.save2new')
		{
			var check = reminderDuplicateCheck();

			if(check === 0)
			{
				return false;
			}
		}

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

    function reminderDuplicateCheck()
	{
		var reminderDays=document.getElementById('jform_days').value;
		var rid=<?php if($rid) echo $rid;else echo "0"; ?>;
		var duplicateDays = 0;

		if(parseInt(rid)==0)
		{
			var url = "index.php?option=com_jticketing&tmpl=component&task=reminder.getDays&selecteddays="+reminderDays;
		}
		else
		{
			var url = "index.php?option=com_jticketing&tmpl=component&task=reminder.getselectDays&id="+rid+"&selecteddays="+reminderDays;
		}

		jQuery.ajax({
		url:url,
		type: 'GET',
		async:false,
		success: function(response) {
				if(parseInt(response)==1)
				{
					alert("<?php echo JText::_('COM_JTICKETING_DUPLICATE_ERMINDER');?>");
					duplicateDays = 1;
				}
				else
				{
					return 1;
				}
			}
		});

		if(duplicateDays === 1)
		{
			return 0;
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jticketing&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="reminder-form" class="form-validate">

<div  class="techjoomla-bootstrap">
	<table border="0" width="100%" cellspacing="10" class="adminlist">
		<tr>
			<td width="70%" align="left" valign="top">

	<div class="form-horizontal">
        <?php //echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

        <?php	// echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_JTICKETING_TITLE_REMINDER', true)); ?>
        <div class="row-fluid">
            <div class="span10 form-horizontal">
                <fieldset class="adminform">

				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
				<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
				<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
				<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
				<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />

				<?php if (empty($this->item->created_by))
					{ ?>
			<input type="hidden" name="jform[created_by]" value="<?php echo JFactory::getUser()->id; ?>" /><?php }
					else
					{ ?>
			<input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>" /> <?php } ?>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('description'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('description'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('days'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('days'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('subject'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('subject'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('replytoemail'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('replytoemail'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('sms'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('sms'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('email'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('email'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('sms_template'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('sms_template'); ?> <?php echo JText::_('COM_JTICKETING_SMS_TAG_NOTICE'); ?></div>
				<div class="controls" id="counter"></div>
			</div>

			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('email_template'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('email_template'); ?></div>
			</div>
				<input type="hidden" name="jform[event_id]" value="<?php echo $this->item->event_id; ?>" />

                </fieldset>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab');

			/*if (JFactory::getUser()->authorise('core.admin', 'jticketing'))
			{
				echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true)); echo $this->form->getInput('rules');
				echo JHtml::_('bootstrap.endTab');
			}*/

				echo JHtml::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>

				</div>
			</td>
			<td width="30%" valign="top">
				<table>

					<tr>
						<td colspan="2"><div class="alert alert-info"><?php echo JText::_('COM_JTICKETING_EB_TAGS_DESC') ?> <br/></div>
										</tr>

					<tr>
						<td width="30%"><b>&nbsp;&nbsp;[NAME] </b> </td>
						<td><?php echo JText::_('TAGS_NAME'); ?></td>

					</tr>

					<tr>
						<td><b>&nbsp;&nbsp;[BOOKING_DATE]</b></td>
						<td><?php echo JText::_('TAGS_BOOKING_DATE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_IMAGE]</b></td>
						<td><?php echo JText::_('TAGS_EVENT_IMAGE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_NAME]</b></td>
						<td><?php echo JText::_('TAGS_EVENT_NAME'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[ST_DATE]</b> </td>
						<td><?php echo JText::_('TAGS_EVENT_START_DATE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EN_DATE]</b></td>
						<td><?php echo JText::_('TAGS_EVENT_END_DATE'); ?></td>

					</tr>

					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_LOCATION]</b> </td>
						<td><?php echo JText::_('TAGS_EVENT_LOCATION'); ?></td>
					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[TICKET_ID]</b></td>
						<td><?php echo JText::_('TAGS_TICKET_ID'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[TICKET_TYPE]</b></td>
						<td><?php echo JText::_('TAGS_TICKET_TYPE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[TICKET_PRICE]</b> </td>
						<td><?php echo JText::_('TAGS_TICKET_PRICE'); ?></td>
					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[TOTAL_PRICE]</b></b></td>
						<td><?php echo JText::_('TAGS_TOTAL_PRICE'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[EVENT_DESCRIPTION] </b></td>
						<td><?php echo JText::_('TAGS_EVENT_DESCRIPTION'); ?></td>

					</tr>
					<tr>
						<td><b>&nbsp;&nbsp;[QR_CODE]</b></td>
						<td><?php echo JText::_('TAGS_QC_CODE'); ?></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</form>
