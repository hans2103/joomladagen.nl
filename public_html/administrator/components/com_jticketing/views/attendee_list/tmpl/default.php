<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

defined('_JEXEC') or die('Restricted access');
$mainframe                = JFactory::getApplication();
$input                    = JFactory::getApplication()->input;
$option                   = $input->get('option');
$search = $mainframe->getUserStateFromRequest($option . 'filter.search', 'filter_search');

$document =JFactory::getDocument();
$jticketingmainhelper = new jticketingmainhelper();
if(JVERSION>=3.0)
{
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
}
JHtml::_('behavior.modal', 'a.modal');

jimport('joomla.filter.output');
jimport( 'joomla.utilities.date');
$com_params=JComponentHelper::getParams('com_jticketing');
$currency = $com_params->get('currency');
$collect_attendee_info_checkout = $com_params->get('collect_attendee_info_checkout','','INT');
$user =JFactory::getUser();


if(empty($user->id))
{
echo '<b>'.JText::_('USER_LOGOUT').'</b>';
return;
}



$payment_statuses=array('P'=>JText::_('JT_PSTATUS_PENDING'),
'C'=>JText::_('JT_PSTATUS_COMPLETED'),
		'D'=>JText::_('JT_PSTATUS_DECLINED'),
		'E'=>JText::_('JT_PSTATUS_FAILED'),
		'UR'=>JText::_('JT_PSTATUS_UNDERREVIW'),
		'RF'=>JText::_('JT_PSTATUS_REFUNDED'),
		'CRV'=>JText::_('JT_PSTATUS_CANCEL_REVERSED'),
		'RV'=>JText::_('JT_PSTATUS_REVERSED'),
);

?>
<script type="text/javascript">
function checkeventselected()
{
	var event_selected = techjoomla.jQuery('#search_event_list').val();

	if(!event_selected)
	{
		alert("<?php echo JText::_('COM_JTICKETING_SELECT_EVENT');	?>");
		return false;
	}
	document.getElementById('task').value = 'attendee_list.csvexport';
	document.getElementById('controller').value = 'attendee_list';
	document.adminForm.submit();
	document.getElementById('task').value = '';
}
	jQuery(document).ready(function () {
		jQuery('#clear-search-button').on('click', function () {
			jQuery('#filter_search').val('');
			jQuery('#adminForm').submit();
		});


	});
</script>
<div style="display:none">
	<div id="import_events">
		<form action="<?php echo JUri::base(); ?>index.php?option=com_jticketing&task=attendee_list.csvImport&tmpl=component&format=html" id="uploadForm" class="form-inline center"  name="uploadForm" method="post" enctype="multipart/form-data">
			<table>
				<tr>&nbsp;</tr>
				<tr>
					<div id="uploadform">
						<fieldset id="upload-noflash" class="actions">
							<label for="upload-file" class="control-label"><?php echo JText::_('COMJTICKETING_UPLOADE_FILE'); ?></label>
							<input type="file" id="upload-file" name="csvfile" id="csvfile" />
							<button class="btn btn-primary" id="upload-submit">
								<i class="icon-upload icon-white"></i>
								<?php echo JText::_('COMJTICKETING_EVENT_IMPORT_CSV'); ?>
							</button>
							<hr class="hr hr-condensed">
							<div class="alert alert-warning" role="alert"><i class="icon-info"></i>
									<?php
									$link = '<a href="' . JUri::root() . 'media/com_jticketing/samplecsv/AttendeeImport.csv' . '">' . JText::_("COM_JTICKETING_CSV_SAMPLE") . '</a>';
								echo JText::sprintf('COM_JTICKETING_CSVHELP_ATTENDEE', $link);
								?>
							</div>
						</fieldset>
					</div>
				</tr>
			</table>
		</form>
	</div>
</div>
<form action="" method="post" name="adminForm"	id="adminForm">
<?php
	if (!empty( $this->sidebar)):
		?>
		<div id="sidebar" >
			<div id="j-sidebar-container" class="span2">
				<?php echo $this->sidebar; ?>
			</div>
		</div>
		<div id="j-main-container" class="span10">
		<?php
	else :
		?>
		<div id="j-main-container">
		<?php
	endif;

			// Search tools bar
			echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));

			if(empty($this->Data))
			{ ?>
				<div class="alert alert-info"><?php echo JText::_('NODATA'); ?></div>
				<div></div><!--//complete both bootstrap and class well-->
				<?php
				return;
			}
			?>
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<tr><td colspan="12"><div class="alert alert-info jtleft"><?php echo JText::_( 'JT_TICKET_NOTE' ); ?></div></td></tr>
					<tr >
						<th width="1%" >
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th align="center">
							<?php echo JHtml::_( 'grid.sort','TICKET_ID','id,order_items_id', $this->lists['order_Dir'], $this->lists['order']);?>
						</th>
						<th ><?php echo JHtml::_( 'grid.sort','ATTENDER_NAME','name', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th align="center"><?php echo JText::_( 'EVENT_NAME' );?></th>
						<th align="center"><?php echo  JText::_( 'PREVIEW_TICKET' ); ?></th>
						<th align="center"><?php echo  JText::_( 'COM_JTICKETING_CHECKIN' ); ?></th>
					</tr>
					<?php
					$i = $j = 0;
					$totalnooftickets = $totalprice = $totalcommission = $totalearn = 0;

					foreach($this->Data as $data)
					{
						$ticketid = JText::_("TICKET_PREFIX") . $data->id . '-' . $data->order_items_id;

						if ($data->ticketcount < 0)
						$data->ticketcount = 0;
						if ($data->amount < 0)
						$data->amount = 0;
						if ($data->totalamount < 0)
						$data->totalamount = 0;

						$totalnooftickets = $totalnooftickets + $data->ticketcount;
						$totalprice = $totalprice + $data->amount;
						$totalearn = $totalearn + $data->totalamount;

						if (empty($data->thumb))
						$data->thumb = 'components/com_community/assets/user_thumb.png';
						$link = JRoute::_('index.php?option=com_community&view=profile&userid='.$data->user_id);
					?>
					<tr>
						<td align="center">
							<?php echo JHtml::_('grid.id',$j,$data->order_items_id);?>
							<input type="hidden" value="<?php echo $data->order_items_id;?>" name="order_items_id[]" id="order_items_id<?php echo $j;?>">
							<input type="hidden" value="<?php echo $data->buyeremail;?>" name="buyeremail[]" id="buyeremail<?php echo $j;?>">
						</td>
						<td align="center">
							<?php if($data->status=='C') echo $ticketid;?>
						</td>
						<td align="center">
						<?php
							if(!empty($data->id))
							{
								echo ucfirst($data->firstname);
								echo "<br/>".$data->lastname;
							}
							else
							{
								echo JText::_('COM_JTICKETING_GUEST');
							}
							?>
						</td>
						<td align="center">
								<?php echo $data->title;
										if (isset($data->short_description))
										{
											 echo "<br>".$data->short_description;
										}
								?>
						</td>
						<td	align="center">
								<?php
								$attendee_details = JRoute::_(JUri::base().'index.php?option=com_jticketing&view=attendee_list&layout=attendee_details&eventid='.$data->event_details_id.'&attendee_id='.$data->attendee_id.'&tmpl=component');
								$link_cancel_ticket = '';
								if($data->status=='C')
								{
									$link = JRoute::_(JUri::root().'index.php?option=com_jticketing&view=mytickets&tmpl=component&
									layout=ticketprint&$jticketing_usesess=0&jticketing_eventid='.$data->evid.'
									&jticketing_userid='.$data->user_id.'&jticketing_ticketid='.$data->id.'&jticketing_order_items_id='.$data->order_items_id);

									$link_change_assignee = JRoute::_(JUri::base().'index.php?option=com_jticketing&view=attendee_list&tmpl=component&
									layout=change_assinee&eventid='.$data->evid.'
									&buyer_id='.$data->user_id.'&order_id='.$data->id.'&order_items_id='.$data->order_items_id);

									$link_cancel_ticket = JRoute::_(JUri::base().'index.php?option=com_jticketing&view=attendee_list&tmpl=component&
									layout=cancel_ticket&eventid='.$data->evid.'
									&buyer_id='.$data->user_id.'&order_id='.$data->id.'&order_items_id='.$data->order_items_id.'&ticketid='.$ticketid);
								?>

								<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" href="<?php echo $link; ?>" class="modal">
									<span class="editlinktip hasTip" title="<?php echo JText::_('PREVIEW_DES');?>" ><?php echo JText::_('PREVIEW');?><br/></span>
								</a>
								<?php

									//For Extra Attendee Fields
									if($collect_attendee_info_checkout)
									{
									?>
										<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" class="modal" href="<?php echo $attendee_details; ?>" >
										<span class="editlinktip hasTip" title="<?php echo JText::_('COM_JTICKETING_VIEW_ATTENDEE');?>" ><?php echo JText::_('COM_JTICKETING_VIEW_ATTENDEE');?></span>
										</a>
									<?php
									}

								}
								else
								{
									//For Extra Attendee Fields
									if($collect_attendee_info_checkout)
									{
									?>
										<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" class="modal" href="<?php echo $attendee_details; ?>" >
										<span class="editlinktip hasTip" title="<?php echo JText::_('COM_JTICKETING_VIEW_ATTENDEE');?>" ><?php echo JText::_('COM_JTICKETING_VIEW_ATTENDEE');?></span>
										</a>
									<?php
									}
								}
								?>
							</td>
							<td align="center">
								<?php if($data->status=='C'){
								?>

								<a href="javascript:void(0);" class="hasTooltip" data-original-title="<?php echo ( $data->checkin ) ? JText::_( 'COM_JTICKETING_CHECKIN_FAIL' ) : JText::_( 'COM_JTICKETING_CHECKIN_SUCCESS' );?>" onclick=" listItemTask('cb<?php echo $j;?>','<?php echo ( $data->checkin ) ? 'attendee_list.undochekin' : 'attendee_list.checkin';?>')">
									<img src="<?php echo JUri::root();?>administrator/components/com_jticketing/assets/images/<?php echo ( $data->checkin ) ? 'publish.png' : 'unpublish.png';?>" width="16" height="16" border="0" />
								</a>
								<?php
								}?>
							</td>
						</tr>
					<?php
						$i++;
						$j++;
					} ?>
					<tfoot>
						<tr>
							<td colspan="10" align="center">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<input type="hidden" name="option" value="com_jticketing" />
					<input type="hidden" name="task" id="task" value="" />
					<input type="hidden" name="boxchecked" value="0" />
					<input type="hidden" name="defaltevent_list" value="<?php echo $this->lists['search_event_list'];?>" />
					<input type="hidden" name="controller" id="controller" value="attendee_list" />
					<input type="hidden" name="view" value="attendee_list" />
					<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
					<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
				</table>
			</div>
		</div>
	</div>
</form>
