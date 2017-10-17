<?php
// no direct access
defined( '_JEXEC' ) or die( ';)' );
global $mainframe;
// Add the CSS and JS

$document =JFactory::getDocument();

$input=JFactory::getApplication()->input;
$eventid = $input->get('eventid','','INT');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal', 'a.modal');
jimport('joomla.filter.output');
$com_params=JComponentHelper::getParams('com_jticketing');
$integration = $com_params->get('integration');
$siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
$currency = $com_params->get('currency');
$allow_buy_guestreg = $com_params->get('allow_buy_guestreg');
$tnc = $com_params->get('tnc');
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

<?php

$integration=$this->jticketingmainhelper->getIntegration();
if($integration==1) //if Jomsocial show JS Toolbar Header
{
	$jspath=JPATH_ROOT.DS.'components'.DS.'com_community';
	if(file_exists($jspath)){
	require_once($jspath.DS.'libraries'.DS.'core.php');
}

	$header='';
	$header=$this->jticketingmainhelper->getJSheader();
	if(!empty($header))
	echo $header;
}

?>
<div  class="floattext">
	<h1 class="componentheading"><?php echo JText::_('MY_TICKET'); ?>	</h1>
</div>
<?php
$k=0;

if(empty($this->Data))
{
	echo JText::_('NODATA');
$input=JFactory::getApplication()->input;
$eventid = $input->get('event','','INT');
	 //if Jomsocial show JS Toolbar Header
if($integration==1)
{
	$footer='';
	$footer=$this->jticketingmainhelper->getJSfooter();
	if(!empty($footer))
	echo $footer;

}
	return;
}
?>
<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">
<form action="" method="post" name="adminForm" id="adminForm">
		<div id="all" class="row">
			<div style="float:left">
			<?php echo JHtml::_('select.genericlist', $this->status_order, "search_order", 'class="ad-status" size="1"
				onchange="document.adminForm.submit();" name="search_order"',"value", "text", $this->lists['search_order']);		 ?>
			</div>
			<?php if(JVERSION>'3.0') {?>
			<div class="btn-group pull-right hidden-xm">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
					<?php
					echo $this->pagination->getLimitBox();
					?>
			</div>
			<?php } ?>
			<div class="table-responsive">
				<table  class="table table-striped table-hover " >
					<tr>
						<th align="center"><?php echo JHtml::_( 'grid.sort','EVENT_NAME','title', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th align="center"><?php echo JText::_( 'EVENTDATE' ); ?></th>
						<th align="center"><?php echo JText::_('TIMING'); ?></th>
						<th align="center"><?php echo JText::_( 'TICKET_RATE' ).'('.$currency.')';?></th>
						<th align="center"><?php echo JText::_( 'NUMBEROFTICKETS_BOUGHT' );?></th>
						<th align="center"><?php echo  JText::_( 'TOTAL_AMOUNT_BUY' ).'('.$currency.')'; ?></th>
								<th align="center"><?php echo JText::_( 'PAYMENT_STATUS'); ?></th>
						<th align="center"><?php echo  JText::_( 'VIEW_TICKET' ); ?></th>
					</tr>
						<?php
						$totalnooftickets=0;
						$totalprice=0;
						$i=0;

								foreach($this->Data as $data) {

								$totalnooftickets=$totalnooftickets+$data->ticketscount;
								 $totalprice=$totalprice+$data->totalamount;
								if(JVERSION<'1.6.0')
								{
								$startdate = JHtml::_('date', $data->startdate, '%Y/%m/%d');
								$enddate = JHtml::_('date', $data->enddate, '%Y/%m/%d');
								}
								else
								{
								 $startdate = JHtml::_('date', $data->startdate, 'Y-m-d');
								 $enddate = JHtml::_('date', $data->enddate, 'Y-m-d');

								}
								if($startdate==$enddate)
								 $datetoshow=JText::sprintf('EVENTS_DURATION_ONE',$startdate);
								else
								$datetoshow=JText::sprintf('EVENTS_DURATION',$startdate,$enddate);

								if(JVERSION<'1.6.0')
								{
								 $starttime = JHtml::_('date', $data->startdate, "%I:%M %p");
								$enddtime = JHtml::_('date', $data->enddate, "%I:%M %p");
								}
								else
								{
									$starttime = JHtml::_('date', $data->startdate, "g:i a");
									$enddtime = JHtml::_('date', $data->enddate, "g:i a");
								}

								if($enddtime==$starttime)
								$timetoshow=JText::sprintf('EVENTS_DURATION_ONE',$starttime);
								else
									$timetoshow=JText::sprintf('EVENTS_DURATION',$starttime,$enddtime);

										if($integration==1 OR $integration==2)
										{	if($data->thumb)
												$avatar = $data->thumb;
											else
												$avatar = JUri::base().'components/com_community/assets/event_thumb.png';
										}
								?>
							<tr>

									<td >
									<img width="32" class="jticket_div_myticket_img" src="<?php echo $avatar;?>" />
									<?php if($integration==1){ ?>
									<a href="<?php echo CRoute::_('index.php?option=com_community&view=events&task=viewevent&eventid='.$data->eventid);?>"><?php echo $data->title;?></a>

									<?php }else if($integration==2){ ?>
									<a href="<?php echo JRoute::_('index.php?option=com_jticketing&view=event&eventid='.$data->eventid);?>"><?php echo $data->title;?></a>
									<?php } else if($integration==3){ ?>
											<a href="<?php echo JRoute::_('index.php?option=com_jevents&task=icalrepeat.detail&evid='.$data->eventid);?>"><?php echo $data->title;?></a>
									<?php }?>

									</td>
									<td align="center"> <?php echo $datetoshow ?></td>
									<td align="center"> <?php echo $timetoshow  ?></td>
									<td align="center"><?php echo $data->price .' ';?></td>
									<td align="center"><?php echo $data->ticketscount ?></td>
									<td align="center"><?php echo $data->totalamount;?></td>
									<td align="center"><?php echo $payment_statuses[$data->STATUS];?></td>
									<td	align="center">
										<?php
										if($data->STATUS=='C')
										{

											$link = JRoute::_('index.php?option=com_jticketing&view=mytickets&tmpl=component&layout=ticketprint&$jticketing_usesess=0&jticketing_eventid='.$data->eventid.'&jticketing_userid='.$data->user_id.'&jticketing_ticketid='.$data->id.'&jticketing_order_items_id='.$data->order_items_id);

										?>
											<a rel="{handler: 'iframe', size: {x: 700, y: 400}}" href="<?php echo $link; ?>" class="modal">
												<span class="editlinktip hasTip" title="<?php echo JText::_('PREVIEW_DES');?>" ><?php echo JText::_('PREVIEW');?></span>
											</a>
										<?php
										}
										else
										 echo '-';
										?>
									</td>
					   </tr>
					<?php } ?>


						<tr>
						<td colspan="4" align="right"><?php echo JText::_('TOTAL');?></td>
						<td align="center"><b><?php echo $totalnooftickets;?></b></td>
						<td align="center"><b><?php echo $totalprice;?></b></td>
						<td ></td><td ></td>
						</tr>
					</table>
				</div>
			</div><!--row-->

					<div class="row">
						<div class="span12">
							<?php
								if(JVERSION<3.0)
									$class_pagination='pager';
								else
									$class_pagination='pagination';
							?>
							<div class="<?php echo $class_pagination; ?> com_jgive_align_center">
								<?php echo $this->pagination->getListFooter(); ?>
							</div>
						</div><!--span12-->
					</div><!--row-->
			<input type="hidden" name="option" value="com_jticketing" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="controller" value="mytickets" />
			<input type="hidden" name="view" value="mytickets" />
			<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	</form>
</div><!--bootstrap-->
<?php

//if Jomsocial show JS Toolbar Footer
if($integration==1)
{
	$footer='';
	$footer=$this->jticketingmainhelper->getJSfooter();
	if(!empty($footer))
	echo $footer;
}
//eoc for JS toolbar inclusion


