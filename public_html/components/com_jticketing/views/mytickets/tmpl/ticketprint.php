<?php
// no direct access
defined( '_JEXEC' ) or die( ';)' );

$user =JFactory::getUser();
$app =JFactory::getApplication();
$issite=$app->isSite();
$users = JFactory::getuser();
$document =JFactory::getDocument();

$jticketing_sess_not=0;

$input=JFactory::getApplication()->input;
$eventid = $input->get('jticketing_eventid','','INT');
$ticketid = $input->get('jticketing_order_items_id','','INT');
$this->jticketingmainhelper=new jticketingmainhelper();


$data=$this->jticketingmainhelper->getticketDetails($eventid,$ticketid);
if(!empty($data))
{
	$data->ticketprice=$data->price;
	$data->nofotickets=$data->ticketscount;
	$data->totalprice=$data->totalamount;
	$data->evid=$eventid;

	$html=$this->jticketingmainhelper->getticketHTML($data,$jticketing_usesess=0);
	if($html)
	{	?>
		<div class="">
			<div class="">
			<table width="100%">
				<tr>
					<td align='right'>
						<input type="button" class="btn  btn-default  btn-success no-print" onclick="javascript:window.print()" value="<?php echo JText::_('PRINT');?>">
						<a  class="btn  btn-default  btn-success btn-medium no-print" href="<?php echo JRoute::_('?option=com_jticketing&view=mytickets&layout=pdf_gen&ticketid='.$ticketid.'&eventid='.$eventid);?>"><?php echo JText::_('PRINT_PDF');?></a>
					</td>
				</tr>
			</table>
			</div>
		<div>
		<?php
		echo $html;
	}
}
else
echo '<b>'.JText::_('NO_TICKET').'</b>';
