<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
global $mainframe;

$document =JFactory::getDocument();

jimport('joomla.filter.output');
jimport( 'joomla.utilities.date');

JHtml::_('behavior.modal', 'a.modal');
$user =JFactory::getUser();

if(empty($user->id))
{
	echo '<div class="alert alert-warning">'.JText::_('USER_LOGOUT').'</div>';
	return;
}

$Itemid=$eventid='';
$eventid=JRequest::getInt('event');
$Itemid=JRequest::getInt('Itemid');
$com_params=JComponentHelper::getParams('com_jticketing');
$integration = $com_params->get('integration');
$siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
$currency = $com_params->get('currency');
$allow_buy_guestreg = $com_params->get('allow_buy_guestreg');
$tnc = $com_params->get('tnc');
$collect_attendee_info_checkout = $com_params->get('collect_attendee_info_checkout','','INT');
$tableclass="table table-striped  table-hover ";
$buttonclass="btn";
$buttonclassprimary="btn  btn-default btn-primary";
$appybtnclass="btn btn-default btn-primary";
$payment_statuses=array('P'=>JText::_('JT_PSTATUS_PENDING'),
'C'=>JText::_('JT_PSTATUS_COMPLETED'),
		'D'=>JText::_('JT_PSTATUS_DECLINED'),
		'E'=>JText::_('JT_PSTATUS_FAILED'),
		'UR'=>JText::_('JT_PSTATUS_UNDERREVIW'),
		'RF'=>JText::_('JT_PSTATUS_REFUNDED'),
		'CRV'=>JText::_('JT_PSTATUS_CANCEL_REVERSED'),
		'RV'=>JText::_('JT_PSTATUS_REVERSED'),
);
$pstatus=array();
$pstatus[]=JHtml::_('select.option','P', JText::_('JT_PSTATUS_PENDING'));
$pstatus[]=JHtml::_('select.option','C', JText::_('JT_PSTATUS_COMPLETED'));
$pstatus[]=JHtml::_('select.option','RF', JText::_('JT_PSTATUS_REFUNDED'));
if(!empty($this->lists['search_event']))
$eventid =$this->lists['search_event'];
?>
<div class = "row container-fluid">
<h3 class="componentheading"><?php echo JText::_('ATTND_LIST'); ?>	</h3>
</div>
<?php
	$integration=$this->jticketingmainhelper->getIntegration();
	//if Jomsocial show JS Toolprogress-bar Header
	if($integration==1)
	{
		$jspath=JPATH_ROOT.DS.'components'.DS.'com_community';
		if(file_exists($jspath))
		{
			require_once($jspath.DS.'libraries'.DS.'core.php');
		}

		$header='';
		$header=$this->jticketingmainhelper->getJSheader();
		if(!empty($header))
		echo $header;
	}

	//if Jomsocial show JS Toolprogress-bar Header
	if(empty($eventid))
	$eventid=JRequest::getInt('event');
	$linkbackbutton='';
?>
<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">
<form  method="post" name="adminForm" id="adminForm">
			<div id="all">
					<?php
						if(JVERSION>='3.0')
						{
						?>
						<div class=" pull-right" style="margin-left: 3px;">
							<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
								<?php
									echo $this->pagination->getLimitBox();
								?>
						</div>
						<?php
						}
						?>
					<div class="pull-right">
						<button  onclick="if (document.adminForm.boxchecked.value==0){
						alert('Please first make a selection from the list');}else{ Joomla.submitbutton('attendee_list.checkin')}" class="btn  btn-default  btn-sm">
							<span class="glyphicon glyphicon-ok"></span>
							<?php echo JText::_('COM_JTICKETING_CHECKIN_MSG')?>
						</button>
						<button  onclick="if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list');}else{ Joomla.submitbutton('attendee_list.undochekin')}" class="btn  btn-default  btn-sm">
							<span class="glyphicon glyphicon-remove"></span>
							<?php echo JText::_('COM_JTICKETING_CHECKIN_FAIL')?>
						</button>
						<button type="button" class="btn  btn-default  btn-success" title="<?php echo JText::_('EXPORT_CSV')?>" onclick="checkeventselected()" >
							<?php echo JText::_('EXPORT_CSV')?>
						</button>
					</div>

				</div>
				<!-- End of form actions-->
					<div class="jticketing-form-actions">
						<?php
						$search_event = $mainframe->getUserStateFromRequest( 'com_jticketingsearch_event', 'search_event','', 'string' );
						echo JHtml::_('select.genericlist', $this->status_event, "search_event_list", 'style="display:inline-block;" class="ad-status" size="1"
						onchange="document.getElementById(\'task\').value =\'\';document.getElementById(\'controller\').value =\'\';document.adminForm.submit();" name="search_event_list"',"value", "text", $this->lists['search_event_list']);?>
							<?php echo JHtml::_('select.genericlist', $this->search_paymentStatuslist, "search_paymentStatuslist", 'style="display:inline-block;" class="search-status" size="1"
							onchange="document.adminForm.submit();" name="search_paymentStatuslist"',"value", "text", $this->lists['search_paymentStatuslist']);
							?>

					</div>
						<div class="clearfix"></div>
						<?php
						if(empty($this->Data))
							{
							?>
							<div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12 pull-right alert alert-info jtleft"><?php echo JText::_('NODATA');?></div>
							<?php
							}
					else{?>
				<div id='no-more-tables' class = "order">
				<table class="table table-striped table-bordered table-hover">
					<thead>
					<tr>
						<th class = "hidden-xs hidden-xm">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th>
							<?php echo JHtml::_( 'grid.sort','TICKET_ID','id,order_items_id', $this->lists['order_Dir'], $this->lists['order']);?>
						</th>
						<th ><?php echo JHtml::_( 'grid.sort','ATTENDER_NAME','name', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th><?php echo JHtml::_( 'grid.sort','BOUGHTON','cdate', $this->lists['order_Dir'], $this->lists['order']); ?></th>
							<th align="center"><?php echo JText::_( 'TICKET_TYPE_TITLE' );?></th>
						<th><?php echo JText::_( 'TICKET_TYPE_RATE' );?></th>
						<th><?php echo  JText::_( 'ORIGINAL_AMOUNT' ); ?></th>
						<th ><?php echo JText::_( 'PAYMENT_STATUS'); ?></th>
						<th ><?php echo  JText::_( 'PREVIEW_TICKET' ); ?></th>
						<th ><?php echo  JText::_( 'COM_JTICKETING_CHECKIN' ); ?></th>

					</tr>
				</thead>

					<?php
					$j= $i = 0;
					$totalnooftickets = $totalprice = $totalcommission = $totalearn = 0;

					foreach($this->Data as $data) {
					$ticketid=JText::_("TICKET_PREFIX").$data->id.'-'.$data->order_items_id;	;
					if($data->ticketcount<0)
					$data->ticketcount=0;
					if($data->amount<0)
					$data->amount=0;
					if($data->totalamount<0)
					$data->totalamount=0;
					$totalnooftickets=$totalnooftickets+$data->ticketcount;
					$totalprice=$totalprice+$data->amount;
					$totalearn=$totalearn+$data->totalamount;
							 if(empty($data->thumb))
								$data->thumb = 'components/com_community/assets/user_thumb.png';
								$link = JRoute::_('index.php?option=com_community&view=profile&userid='.$data->user_id);

					?>
				<tr>
					<td align="center" class = "hidden-xs hidden-xm">
				     <?php echo JHtml::_('grid.id',$j,$data->order_items_id);

				     ?>
				</td>
					<td align="center"  data-title="<?php echo JText::_('TICKET_ID'); ?>">
							<?php echo ($data->status=='C') ? $ticketid : "-";
							?>

					</td>
					<!--<td align="enter">
					<?php

						echo ucfirst($data->title);?>
					</td>-->
					<td align="center" data-title="<?php echo JText::_('ATTENDER_NAME'); ?>">
							<?php
								if(!empty($data->name))
								{
									echo ucfirst($data->name);
								}
								else
								{
									echo JText::_('COM_JTICKETING_GUEST');
								} ?>
					</td>
					<td align="center" data-title="<?php echo JText::_('BOUGHTON'); ?>"><?php
					$jdate = new JDate($data->cdate);

					 echo  str_replace('00:00:00','',$jdate->Format('d-m-Y'));

					 ?></td>
					 <td data-title="<?php echo JText::_('TICKET_TYPE_TITLE'); ?>">
						 <?php echo $data->ticket_type_title; ?>
					</td>
					<td align="center" data-title="<?php echo JText::_('TICKET_TYPE_RATE'); ?>">
							<?php  echo $this->jticketingmainhelper->getFromattedPrice( number_format(($data->amount),2),$currency);?>
					</td>
					<td align="center" data-title="<?php echo JText::_('ORIGINAL_AMOUNT'); ?>">
						<?php  echo $this->jticketingmainhelper->getFromattedPrice( number_format(($data->totalamount),2),$currency);?>
					</td>
					<td align="center" data-title="<?php echo JText::_('PAYMENT_STATUS'); ?>">
						<?php echo $payment_statuses[$data->status];?>
					</td>
					<td	align="center" class = "dis_modal" data-title="<?php echo JText::_('PREVIEW_TICKET'); ?>">
							<?php

							$attendee_details = JRoute::_(JUri::root().'index.php?option=com_jticketing&view=attendee_list&layout=attendee_details&eventid='.$data->event_details_id.'&attendee_id='.$data->attendee_id.'&tmpl=component');
							if($data->status=='C')
							{
								$link = JRoute::_(JUri::root().'index.php?option=com_jticketing&view=mytickets&tmpl=component&
								layout=ticketprint&$jticketing_usesess=0&jticketing_eventid='.$data->evid.'
								&jticketing_userid='.$data->user_id.'&jticketing_ticketid='.$data->id.'&jticketing_order_items_id='.$data->order_items_id);

							?>

							<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" href="<?php echo $link; ?>" class="modal">
								<span class="editlinktip hasTip" title="<?php echo JText::_('PREVIEW_DES');?>" ><?php echo JText::_('PREVIEW');?></span>
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
								else
								{
									echo "-";
								}
							}
							?>
					</td>
					<td align="center" data-title="<?php echo JText::_('COM_JTICKETING_CHECKIN'); ?>">
								<?php if($data->status=='C')
								{
								?>

								<a href="javascript:void(0);" class="hasTooltip" data-original-title="<?php echo ( $data->checkin ) ? JText::_( 'COM_JTICKETING_CHECKIN_FAIL' ) : JText::_( 'COM_JTICKETING_CHECKIN_MSG' );?>" onclick=" listItemTask('cb<?php echo $j;?>','<?php echo ( $data->checkin ) ? 'attendee_list.undochekin' : 'attendee_list.checkin';?>')">
									<img src="<?php echo JUri::root();?>components/com_jticketing/assets/images/<?php echo ( $data->checkin ) ? 'publish.png' : 'unpublish.png';?>" width="16" height="16" border="0" />
								</a>
								<?php
								}
								else
								{
									echo "-";
								}
								?>
					</td>
				</tr>
				<?php
					$i++;
					$j++;
					}
					?>

				<?php
				}
				?>

			</table>
		</div>
		<div class="row">
			<div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<?php
					if(JVERSION<3.0)
						$class_pagination='pager';
					else
						$class_pagination='pagination';
				?>
				<div class="<?php echo $class_pagination; ?> com_jgive_align_center">
					<?php echo $this->pagination->getListFooter(); ?>
				</div>
			</div><!-- col-lg-12 col-md-12 col-sm-12 col-xs-12-->
		</div><!--row-->

<input type="hidden" name="option" value="com_jticketing" />
<input type="hidden" name="task" id="task" value="" />
<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
<input type="hidden" name="defaltevent_list" value="<?php echo $this->lists['search_event_list'];?>" />
<input type="hidden" name="controller" id="controller" value="attendee_list" />
<input type="hidden" name="view" value="attendee_list" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
</div>

<!-- newly added for JS toolbar inclusion  -->
<?php
if($integration==1) //if Jomsocial show JS Toolbar Footer
{
$footer='';
	$footer=$this->jticketingmainhelper->getJSfooter();
	if(!empty($footer))
	echo $footer;
}
?>
<!-- eoc for JS toolbar inclusion	 -->


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
</script>

