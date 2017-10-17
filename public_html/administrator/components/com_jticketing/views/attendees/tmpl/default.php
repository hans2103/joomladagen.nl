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
if(JVERSION>=3.0)
	JHtml::_('formbehavior.chosen', 'select');
$input=JFactory::getApplication()->input;
$com_params=JComponentHelper::getParams('com_jticketing');
$currency = $com_params->get('currency');
$document->addStyleSheet(JUri::base().'components/com_jticketing/css/jticketing.css');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal', 'a.modal');
$user =JFactory::getUser();
if(empty($user->id))
{
echo '<b>'.JText::_('USER_LOGOUT').'</b>';
return;
}
if(empty($this->lists['search_event']))
{
	$this->lists['search_event']=$input->get('event','','INT');
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


		$jticketingmainhelper = new jticketingmainhelper();
		if(!$this->lists['search_event'])
		{
			if(!empty($_GET['event']))
			{
				$eventid =$_GET['event'];
				$eventnm = $jticketingmainhelper->getEventcustominfo($eventid,'title');
				$evnt_nm=$eventnm;
			}
		}
		else
		{
			$eventnm  = $jticketingmainhelper->getEventcustominfo($this->lists['search_event'],'title');
			$evnt_nm=$eventnm;

		}

?>



<form action="" method="post" name="adminForm"	id="adminForm">

<!--  -->
<?php
if(!empty( $this->sidebar)): ?>
	<div id="sidebar" >
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>

	</div>
			<?php if(JVERSION>'3.0') {?>
			<div class="btn-group pull-right hidden-phone" style="margin-right:2%">
				<label for="limit" class="element-invisible" ><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
					<?php
					echo $this->pagination->getLimitBox();
					?>
			</div>
			<?php } ?>
		<div id="j-main-container" class="span10">

	<?php else : ?>
		<div id="j-main-container">
	<?php endif;

?>

<?php if(JVERSION<3.0): ?>
<div align="right">
	<table >
		<tr>
			<td><?php

					echo JHtml::_('select.genericlist', $this->status_event, "search_event", 'class="ad-status" size="1"
					onchange="document.adminForm.submit();" name="search_event"',"value", "text", $this->lists['search_event']);		 ?></td>

		</tr>
	</table>
</div>
<?php endif;?>


<?php
if(empty($this->Data))
{
	echo JText::_('NODATA');
	return;
}
?>

<table  width="100%" class="adminlist table table-striped ">
	<tr >
	<th align="center">
			<?php echo JHtml::_( 'grid.sort','TICKET_ID','id,order_items_id', $this->lists['order_Dir'], $this->lists['order']);?>
		</th>
		<th ><?php echo JHtml::_( 'grid.sort','ATTENDER_NAME','name', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th ><?php echo JHtml::_( 'grid.sort','BOUGHTON','cdate', $this->lists['order_Dir'], $this->lists['order']); ?></th>
		<th align="center"><?php echo JText::_( 'TICKET_TYPE_TITLE' );?></th>
		<th align="center"><?php echo JText::_( 'TICKET_TYPE_RATE' );?></th>
		<th align="center"><?php echo JText::_( 'NUMBEROFTICKETS_BOUGHT' );?></th>
		<th align="center"><?php echo  JText::_( 'TOTAL_AMOUNT' ); ?></th>
		<th align="center"><?php echo JText::_( 'PAYMENT_STATUS'); ?></th>
		<th align="center"><?php echo  JText::_( 'PREVIEW_TICKET' ); ?></th>

	</tr>



	<?php
			$i = 0;
			$totalnooftickets=$totalprice=$totalcommission=$totalearn=0;
			foreach($this->Data as $data) {
			$ticketid=JText::_("TICKET_PREFIX").$data->id.'-'.$data->order_items_id;	;
			$totalnooftickets=$totalnooftickets+$data->ticketcount;
			$totalprice=$totalprice+$data->amount;
			$totalearn=$totalearn+$data->totalamount;
					 if(empty($data->thumb))
					 	$data->thumb = 'components/com_community/assets/user_thumb.png';
						$link = JRoute::_('index.php?option=com_community&view=profile&userid='.$data->user_id);

	?>
	<tr >
	<td align="center">



				<?php if($data->status=='C') echo $ticketid;?>


			</td>
			<td align="center">



				<?php echo ucfirst($data->name);?>


			</td>
			<td align="center"><?php

			$jdate = new JDate($data->cdate);


			 if(JVERSION<'1.6.0')
			 echo  str_replace('00:00:00','',$jdate->Format('d-m-Y'));

			else
			 echo  str_replace('00:00:00','',$jdate->Format('d-m-Y'));

			 ?></td>
			 <td ><?php echo $data->ticket_type_title; ?></td>
			<td align="center"><?php echo $data->amount.' '.$currency;?></td>
			<td align="center"><?php echo $data->ticketcount ?></td>
			<td align="center"><?php echo $data->totalamount.' '.$currency;?></td>
			<td align="center"><?php echo $payment_statuses[$data->status];?></td>
			<td	align="center">
				<?php
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
				}
				else
				echo '-';
				?>
			</td>


	</tr>
	<?php $i++;} ?>
			<tr>
			<td colspan="4" align="right"><b><?php echo JText::_('TOTAL');?></b></td>
			<td align="center"><b><?php echo number_format($totalnooftickets, 2, '.', '');?></b></td>
			<td align="center"><b><?php echo number_format($totalprice, 2, '.', '').' '.$currency;?></b></td>
			<td align="center"><b><?php echo number_format($totalearn, 2, '.', '').' '.$currency;?></b></td>
			<td></td>
			</tr>

</table>

			<tfoot>
				<tr>
				<td colspan="6">
				<?php echo $this->pagination->getListFooter(); ?>
				</td>
				</tr>
			</tfoot>



<input type="hidden" name="option" value="com_jticketing" />
<input type="hidden" name="task" id="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="defaltevent" value="<?php echo $this->lists['search_event'];?>" />
<input type="hidden" name="controller" value="attendees" />
<input type="hidden" name="view" value="attendees" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

</form>


