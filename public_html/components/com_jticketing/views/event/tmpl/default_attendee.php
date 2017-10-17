<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
$attendee_records_config = 5;
if ($this->item->allow_view_attendee == 1 && count($this->item->eventAttendeeInfo) >= 1)
{
?>
<div class="row">
	<div class="col-sm-12">
		<span class="searchAttendee" id="SearchAttendeeinputbox">
			<input type="text" 
			id="attendeeInput" 
			onkeyup="jtSite.event.searchAttendee()" 
			placeholder="<?php echo JTEXT::_('COM_JGIVE_EVENT_ATTENDEE_SEARCH_PLACEHOLDER');?>" 
			title="searchAttendeeRecord"
			size="35">
		</span>
	</div>
</div>
<div class="row">
	<div class="col-sm-12 no-more-tables">
		<table class="table user-list" id="eventAttender">
			<thead>
				<tr>
					<th><span><?php echo JText::_("COM_JTICKETING_ATTENDER_NAME");?></span></th>
					<th><span><?php echo JText::_("COM_JTICKEING_BOUGHTON");?></span></th>
				</tr>
			</thead>
			<tbody id="jticketing_attendee_pic">
				<?php
					$j = 1;

					foreach ($this->item->eventAttendeeInfo as $this->eventAttendeeInfo)
					{
						echo $this->loadTemplate("attendeelist");
					?>
					<?php
					$j++;
					}
				?>
			</tbody>
		</table>
	</div>
</div>
<input type="hidden" id="attendee_pro_pic_index" value="<?php echo $j; ?>" />
<input type="hidden" id="event_id" name="event_id" value="<?php echo $this->item->id;?>"/>
<?php
	if ($this->item->eventAttendeeCount > $attendee_records_config  && $this->item->allow_view_attendee == 1)
	{
	?>
		<button id="btn_showMorePic" class="btn btn-info btn-md pull-right" type="button" onclick="jtSite.event.viewMoreAttendee()">
			<?php
				echo JText::_('COM_JTICKETING_SHOW_MORE_ATTENDEE');
			?>
		</button>
	<?php
	}
}?>

<script type="text/javascript">
var gbl_jticket_index = 0 ;
var attedee_count = <?php
							if ($this->item->eventAttendeeCount)
							{
								echo $this->item->eventAttendeeCount;
							}
							else
							{
								echo 0;
							}
							?>;
var gbl_jticket_pro_pic = 0;
var jticket_baseurl = "<?php echo JUri::root(); ?>";
</script>
