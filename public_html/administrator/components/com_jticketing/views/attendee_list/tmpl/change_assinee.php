<?php


JHTML::_('behavior.framework');
JHTML::_('behavior.tooltip');
JHTML::_('behavior.formvalidation');
$input =JFactory::getApplication()->input;
// Get some variables from the request
$buyer_id	= $input->get('buyer_id');
$eventid	= $input->get('eventid');
$order_id	= $input->get('order_id');
$order_items_id	=$input->get('order_items_id');


?>
<script type="text/javascript">
var buyer = "<?php $buyer_id;?>"
	function showEvents()
	{
			var buyer = "<?php $buyer_id;?>"

		var category=techjoomla.jQuery('#search_event_category').val();
		if(category==undefined || category==0)
		{
			return (false);
		}


		jQuery.ajax({
			url: 'index.php?option=com_jticketing&task=attendee_list.getEventsforpurchase&buyer_id='+buyer+'&category_id='+category+'&tmpl=component',
			type: 'GET',
			dataType: 'json',
			success: function(data)
			{
				generateoption(data)
			}
		});
	}

	function generateoption(data)
	{
		var options, index, select, option;
		select = techjoomla.jQuery('#search_event_assignee');
		select.find('option').remove().end();
		selected="selected=\"selected\"";
		default_opt = 'No Events found';

		if (data !== undefined && data !== null)
		{
			default_opt = 'Select Event';
			var op='<option '+selected+' value="">'  +default_opt+   '</option>' ;
			techjoomla.jQuery('#search_event_assignee').append(op);
			options = data;

			for (index = 0; index < data.length; ++index)
			{
				selected="";
				var opObj = data[index];
				var op='<option '+selected+' value=\"'+opObj.eventid+'\">'  +opObj.title+   '</option>';

				{
					techjoomla.jQuery('#search_event_assignee').append(op);
				}
			}
		}
		else
		{
			default_opt = 'No Events found';
			var op='<option '+selected+' value="">'  +default_opt+   '</option>' ;
			techjoomla.jQuery('#search_event_assignee').append(op);
		}
	}

	function showTicketTypes(data)
	{
		var buyer = "<?php $buyer_id;?>"
		var eventid=techjoomla.jQuery('#search_event_assignee').val();
		if(eventid==undefined || event==0)
		{
			return (false);
		}

		jQuery.ajax({
			url: 'index.php?option=com_jticketing&task=attendee_list.getTicketTypes&buyer_id='+buyer+'&eventid='+eventid+'&tmpl=component',
			type: 'GET',
			dataType: 'json',
			success: function(data)
			{

				generateoptionTicket(data)
			}
		});
	}

	function generateoptionTicket(data)
	{
		var options, index, select, option;
		select = techjoomla.jQuery('#search_ticket_assignee');
		select.find('option').remove().end();
		selected="selected=\"selected\"";
		default_opt = 'No tickets found';

		if (data !== undefined && data !== null)
		{
			options = data;

			for (index = 0; index < data.length; ++index)
			{
				selected="";
				var opObj = data[index];
				var op='<option '+selected+' value=\"'+opObj.id+'\">'  +opObj.title+   '</option>';
				{
					techjoomla.jQuery('#search_ticket_assignee').append(op);
				}
			}
		}
		else
		{
			default_opt = 'No tickets found';
			var op='<option '+selected+' value="">'  +default_opt+   '</option>' ;
			techjoomla.jQuery('#search_ticket_assignee').append(op);
		}
	}
</script>
<form action="" method="post" name="changeAsignee" id="changeAsignee" class="form-validate">
	<div  class="row-fluid form-horizontal">
		<div class="control-group">
			<label  for="search_event_category" class="control-label"><?php
				echo JText::_('COM_JTICKETING_SELECT_CATEGORY');
				?></label>
			<div class="controls">
				<?php
				echo JHtml::_('select.genericlist', $this->cat_options, "search_event_category", 'class="required chzn-done ad-status" size="1" data-chosen="com_jticketing"	onchange="showEvents(this.value)" id="search_event_category" name="search_event_category"',"value", "text");
				?>
			</div>
		</div>
		<div class="control-group">
			<label for="search_event_assignee" class="control-label"><?php
				echo JText::_('COM_JTICKETING_SELECT_EVENT_ASIGNEE');
				?></label>
			<div class="controls">
				<?php
				echo JHtml::_('select.genericlist', $this->event_option, "search_event_assignee", 'class="required chzn-done ad-status" size="1" onchange="showTicketTypes(this.value)" data-chosen="com_jticketing" name="search_event_assignee"',"value", "text");
				?>
			</div>
		</div>
		<div class="control-group">
			<label for="search_ticket_assignee" class="control-label"><?php
				echo JText::_('COM_JTICKETING_SELECT_TICKET_TYPE_ASIGNEE');
				?></label>
			<div class="controls">
				<?php
				echo JHtml::_('select.genericlist', $this->ticket_type_option, "search_ticket_assignee", 'class="required chzn-done ad-status" size="1"  data-chosen="com_jticketing" name="search_ticket_assignee"',"value", "text");
				?>
			</div>
		</div>
	</div>


<input type="hidden" name="task" value="attendee_list.changeTicketAssignment" />
<input type="hidden" name="order_items_id" value="<?php if(isset($order_items_id)) echo $order_items_id;  ?>" />
<input type="hidden" name="order_id" value="<?php if(isset($order_id)) echo $order_id; ?>" />

<?php echo JHtml::_('form.token'); ?>
<div class="form actions center">
<button id="btnWizardNext" type="submit" class="btn btn-success btn-next" data-last="Finish" >
	<?php
	echo JText::_('COM_JTICKETING_CHANGE_EVENT_FOR_ATTNEDEE');
	?>
</button>
</div>
</form>

