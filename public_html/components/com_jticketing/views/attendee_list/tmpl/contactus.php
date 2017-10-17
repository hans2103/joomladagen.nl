<?php
// no direct access
defined( '_JEXEC' ) or die();
JHTML::_('behavior.framework');

JHTML::_('behavior.tooltip');
JHTML::_('behavior.formvalidation');
$input =JFactory::getApplication()->input;
$post=$input->post;
// Get some variables from the request
$cid	=$input->get('cid',array(), 'post', 'array');
$sendto	=$input->get('sendto','','GET','STRING');
?>

 <script type="text/javascript">

	jQuery(function() {
	 var f = window.parent.document.adminForm;

	for (var i = 0; true; i++) {
		var cbx = f['cb'+i];
		if (!cbx)
		break;
		if(window.parent.document.adminForm.getElementById('cb'+i).checked)
		{
			var value=window.parent.document.adminForm.getElementById('order_items_id'+i).value;
			value1=jQuery("#selected_cids").val();
			if(value1)
			var main_value=value1+","+value;
			else
			var main_value=value;
			jQuery("#selected_cids").val(main_value);

			var value_buyer_email=window.parent.document.adminForm.getElementById('buyeremail'+i).value;
			value1=main_value="";

			value1=jQuery("#selected_emails").val();
			if(value_buyer_email)
			var main_value=value1+","+value_buyer_email;
			else
			var main_value=value;
			main_value = main_value.split(",");


			var uniqueNames = [];
			jQuery.each(main_value, function(i, el){
			if(jQuery.inArray(el, uniqueNames) === -1) uniqueNames.push(el);
			});

			jQuery("#selected_emails").val(uniqueNames);
		}

	}

	if(jQuery("#sendto").val()!='all'){
		if(jQuery("#selected_cids").val()=="" || jQuery("#selected_cids").val()=="undefined")
		{
			alert("Please first make a selection from the list");
			if(jQuery("#sendto").val()=='selected_event')
			window.parent.location="index.php?option=com_jticketing&view=events&layout=all_list";
			else
			window.parent.location="index.php?option=com_jticketing&view=attendee_list";
			return;
		}
	}
	else
	{
		var allvaluers=window.parent.document.adminForm.getElementById('selected_order_items').value
		jQuery("#selected_order_items").text(allvaluers)

	}
});

</script>
<form  name="contactform" id="contactform" class="form-validate " method="post" enctype="multipart/form-data">
	<div class="form-horizontal">
		<div class="">
		<button type="submit" class="btn  btn-default  btn-medium pull-right"><i class="icon-envelope icon-white"></i><?php echo  JText::_('COM_JTICKETING_SEND') ?> </button>
		</div>

			<div class="">
				<label><?php echo  JText::_('COM_JTICKETING_ENTER_EMAIL_ID') ?></label>
				<div class="input-group">
					<textarea id="selected_emails" name="selected_emails" readonly="true" style="margin: 0px; width: 592px; height: 105px;"></textarea>
				</div>
		</div>

		<div class="">
				<label><?php echo  JText::_('COM_JTICKETING_ENTER_EMAIL_SUBJECT') ?></label>
				<div class="input-group">
					<input type="text" id="jt-message-subject" name="jt-message-subject"  class="col-lg-2 col-md-2 col-sm-2 col-xs-12  required  " style="width:233px" placeholder="<?php echo  JText::_('COM_JTICKETING_ENTER_EMAIL_SUBJECT') ?>">
					<input type="hidden" id="selected_cids" name="selected_cids" class="col-lg-2 col-md-2 col-sm-2 col-xs-12 " style="width:233px" placeholder="" value="">
			</div>
		</div>
		<!--
		<div class="">
				<label><?php echo  JText::_('COM_JTICKETING_EMAIL_ATTACHEMENT') ?></label>
			<input class="span2 " type="file" name="file_upload" id="file">
		</div>
		-->
		<div class="">
			<label><?php echo  JText::_('COM_JTICKETING_EMAIL_BODY') ?></label>
			<?php
			$editor      = JFactory::getEditor();
			echo $editor->display("jt-message-body","", '', '', '50', '10');
			?>
		</div>
		<div class="">
		<button type="submit" class="btn  btn-default  btn-medium pull-right"><i class="icon-envelope icon-white"></i><?php echo  JText::_('COM_JTICKETING_SEND') ?> </button>
		</div>
	</div>
	<input type="hidden" name="selected_order_items"  id="selected_order_items"  value="" />
	<input type="hidden" name="option" value="com_jticketing" />
	<input type="hidden" name="sendto" id="sendto"  value="<?php echo $sendto; ?>" />
	<input type="hidden" name="task" value="<?php echo 'attendee_list.emailtoSelected';?>" />
	<input type="hidden" name="controller" value="attendee_list" />
</form>

