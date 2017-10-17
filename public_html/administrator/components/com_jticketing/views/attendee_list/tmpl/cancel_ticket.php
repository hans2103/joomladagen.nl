<?php
defined('_JEXEC') or die();
JHTML::_('behavior.framework');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.formvalidation');
$input =JFactory::getApplication()->input;
// Get some variables from the request
$buyer_id	= $input->get('buyer_id');
$eventid	= $input->get('eventid');
$order_id	= $input->get('order_id');
$order_items_id	=$input->get('order_items_id');
$ticketid	=$input->get('ticketid','','STRING');



if(empty($order_items_id))
{
	JFactory::getApplication()->enqueueMessage(JText::_('COM_JTICKETING_CANCEL_TICKET_SUCCESS'));

	return;
}

$link_cancel_ticket = JRoute::_(JUri::base().'index.php?option=com_jticketing&view=attendee_list&tmpl=component&layout=cancel_ticket');
?>

<form action="" method="post" name="cancelTicket" id="cancelTicket" class="form-validate">
	<div  class="row-fluid form-horizontal">
		<div class="control-group">
			<label  for="search_event_category" class="control-label"><?php
				echo JText::_('COM_JTICKETING_CANCEL_TICKET_REASON');
				?></label>
			<div class="controls">
				<textarea
				id="comment"
				class="input-medium bill inputbox required"
				name="comment"  maxlength="250" rows="3" title="<?php
				echo JText::_('COM_JTICKETING_CANCEL_TICKET_REASON_DESC');
				?>" ></textarea>
			</div>
		</div>
<input type="hidden" name="task" value="attendee_list.cancelTicket" />
<input type="hidden" name="order_items_id" value="<?php if(isset($order_items_id)) echo $order_items_id;  ?>" />
<input type="hidden" name="order_id" value="<?php if(isset($order_id)) echo $order_id; ?>" />
<input type="hidden" name="ticketid" value="<?php if(isset($ticketid)) echo $ticketid; ?>" />

<input type="hidden" name="payment_status" value="D" />
<input type="hidden" name="redirectview" value="<?php echo $link_cancel_ticket;?>" />


<?php echo JHtml::_('form.token'); ?>
<div class="form actions center">
<button id="btnWizardNext" type="submit" class="btn btn-success btn-next" data-last="Finish" >
	<?php
	echo JText::_('COM_JTICKETING_CANCEL_TICKET_DESC');
	?>
</button>
</div>
</form>

