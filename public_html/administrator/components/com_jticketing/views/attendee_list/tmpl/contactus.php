<?php
// no direct access
defined( '_JEXEC' ) or die();
JHTML::_('behavior.framework');
JHTML::_('behavior.formvalidation');
$input =JFactory::getApplication()->input;
		//print_r($this->selected_emails);die;

?>
<div class="row-fluid">
 <form  name="adminForm" id="adminForm" class="form-validate form-horizontal" method="post" enctype="multipart/form-data">
	<div class="control-group">
		<div class="control-label"><?php echo  JText::_('COM_JTICKETING_ENTER_EMAIL_ID') ?> *</div>
		<div class="controls">
			<textarea id="selected_emails" name="selected_emails" readonly="true" ><?php echo implode("," , $this->selected_emails);?>
			</textarea>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label"><?php echo  JText::_('COM_JTICKETING_ENTER_EMAIL_SUBJECT') ?> *</div>
		<div class="controls">
			<input type="text" id="jt-message-subject" name="jt-message-subject"  class="span2 required  " style="width:233px" placeholder="<?php echo  JText::_('COM_JTICKETING_ENTER_EMAIL_SUBJECT') ?>">
		</div>
	</div>

	<div class="control-group">
		<div class="control-label"><?php echo  JText::_('COM_JTICKETING_EMAIL_BODY') ?> *</div>
		<div class="controls">
			<?php
			$editor      = JFactory::getEditor();
			echo $editor->display("jt-message-body","",670,600,60,20,true);

			?>
		</div>
	</div>
	<input type="hidden" name="selected_order_items"  id="selected_order_items"  value="" />
	<input type="hidden" name="option" value="com_jticketing" />
	<input type="hidden" name="sendto" id="sendto"  value="<?php echo $sendto; ?>" />
	<input type="hidden" name="controller" value="attendee_list" />
		<input type="hidden" name="task" value="" />

</form>
</div>
