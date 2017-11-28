<?php
/**
* @version    SVN: <svn_id>
* @package    JTicketing
* @author     Techjoomla <extensions@techjoomla.com>
* @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
* @license    GNU General Public License version 2 or later.
*/

// No direct access
defined('_JEXEC') or die('Restricted access');
global $mainframe;

JHtml::_('behavior.modal', 'a.modal');
$document = JFactory::getDocument();
(JText::_('ORDER_VIEW'));
jimport('joomla.filter.output');
jimport( 'joomla.utilities.date');
$input=JFactory::getApplication()->input;

if (empty($this->user->id))
{
	echo '<b>'.JText::_('USER_LOGOUT').'</b>';

	return;
}

$bootstrapClass = "";
$tableClass = "table table-striped table-hover ";
$buttonClass = "button";
$buttonClassPrimary = "button";

$bootstrapClass = "JTICKETING_WRAPPER_CLASS";
$tableClass = "table table-striped table-hover ";
$buttonClass = "btn";
$buttonClassPrimary = "btn btn-default btn-primary";
$appybtnClass = "btn btn-default btn-primary";

// If Jomsocial show JS Toolbar Header
if ($this->integration == 1)
{
	$header = '';
	$header = $this->jticketingmainhelper->getJSheader();

	if (!empty($header))
	{
		echo $header;
	}
}
?>
<div class="floattext">
	<h1 class="componentheading"><?php echo JText::_('MY_ORDERS'); ?>	</h1>
</div>
<script type="text/javascript">
	jtSite.orders.initOrdersJs();
</script>
<?php
if (empty($this->lists['search_event']))
{
	$this->lists['search_event']=$input->get('event','','INT');
}

if (empty($this->Data) or $this->noeventsfound==1)
{
	echo '<form action="" method="post" name="adminForm" id="adminForm">';
?>
	<div class="<?php echo $bootstrapClass;?>  container-fluid">
		<div id="all" class="row">
			<div class = "col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div style="float:right">
					<?php
					if ($this->noeventsfound!=1)
					{
						// If no events found dont show filter
						$searchEvent = $mainframe->getUserStateFromRequest( 'com_jticketingsearch_event', 'search_event','', 'string' );
						echo JHtml::_('select.genericlist', $this->status_event, "search_event", 'class="ad-status" size="1" onchange="document.adminForm.submit();" name="search_event"',"value", "text",$searchEvent);
						echo JHtml::_('select.genericlist', $this->search_paymentStatus, "search_paymentStatus", 'class="ad-status" size="1" onchange="document.adminForm.submit();" name="search_paymentStatus"',"value", "text", strtoupper($this->lists['search_paymentStatus']));
					}
					?>
				</div>
				<div class="clearfix">&nbsp;</div>
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 pull-right alert alert-info jtleft"><?php echo JText::_('NODATA');?></div>
				<input type="hidden" name="option" value="com_jticketing" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="defaltevent" value="<?php echo $this->lists['search_event'];?>" />
				<input type="hidden" name="defaltpaymentStatus" value="<?php echo isset($this->lists['search_paymentStatus']) ? $this->lists['search_paymentStatus'] : $this->lists['search_paymentStatus'] = '';?>" />
				<input type="hidden" name="controller" value="orders" />
				<input type="hidden" name="view" value="orders" />
				<input type="hidden" name="Itemid" value="<?php echo isset($this->Itemid) ? $this->Itemid : $this->Itemid = '0'; ?>" />
			</div>
		</div>
	</div>
	</form>
	<?php
	// Newly added for JS toolbar inclusion
	if ($this->integration==1)
	{
		$footer = '';
		$footer = $this->jticketingmainhelper->getJSfooter();

		if (!empty($footer))
			echo $footer;
	}

	return;
}
	$params = JComponentHelper::getParams('com_jticketing');
	$handle_transactions = $params->get('handle_transactions');
	$adaptivePayment = $params->get('gateways');

	if($this->checkGatewayDetails == "true" && ($handle_transactions == 1 || in_array('adaptive_paypal', $adaptivePayment)))
	{
	?>
	<div class="alert alert-warning">
		<?php
		$vendor_id = $this->vendorCheck;
		$link = 'index.php?option=com_tjvendors&view=vendor&layout=profile&client=com_jticketing';
		echo JText::_('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG1');
		$Jticketingmainhelper = new Jticketingmainhelper;
		$itemid = $Jticketingmainhelper->getItemId($link);?>
			<a href="<?php echo JRoute::_($link . '&itemId='. $itemid .'&vendor_id=' . $vendor_id,false);?>">
			<?php echo JText::_('COM_JTICKETING_VENDOR_FORM_LINK'); ?></a>
		<?php echo " <br> ".JText::_('COM_JTICKETING_PAYMENT_DETAILS_ERROR_MSG2');?>
	</div>
<?php
}
?>
<form action="" method="post" name="adminForm" id="adminForm">
	<div class="<?php echo $bootstrapClass;?> container-fluid">
		<div id="all" class="row">
			<div class="btn-toolbar pull-right">
				<?php
					$searchEvent = $mainframe->getUserStateFromRequest( 'com_jticketingsearch_event', 'search_event','', 'string' );
					echo JHtml::_('select.genericlist', $this->status_event, "search_event", 'class="ad-status" size="1" onchange="document.adminForm.submit();" name="search_event"',"value", "text", $searchEvent);
					echo JHtml::_('select.genericlist', $this->search_paymentStatus, "search_paymentStatus", 'class="ad-status col-lg-6 col-md-56col-sm-4 col-xs-12" size="1" onchange="document.adminForm.submit();" name="search_paymentStatus"',"value", "text", strtoupper($this->lists['search_paymentStatus']));
					?>
					<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		</div>
		<div id='no-more-tables'>
			<table class="table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th align="center"><?php echo JHtml::_( 'grid.sort','ORDER_ID','id', $this->lists['order_Dir'], $this->lists['order']); ?></th>
					<th align="center"><?php echo JText::_('EVENT_NAME');?></th>
					<th align="center"><?php echo JHtml::_( 'grid.sort','PAY_METHOD','processor', $this->lists['order_Dir'], $this->lists['order']); ?></th>
					<th align="center"><?php echo JText::_( 'NUMBEROFTICKETS_BOUGHT' );?></th>
					<th align="center"><?php echo  JText::_( 'ORIGINAL_AMOUNT' ); ?></th>
					<th align="center"><?php echo  JText::_( 'DISCOUNT_AMOUNT' ); ?></th>
					<?php
					if ($this->jticketingparams->get('allow_taxation'))
					{
						?>
						<th align="center"><?php echo  JText::_( 'TAX_AMOUNT' ); ?></th>
						<?php
					}
					?>
					<th align="center"><?php echo  JText::_( 'PAID_AMOUNT' ); ?></th>
					<th align="center"><?php echo  JText::_( 'COUPON_CODE_DIS' ); ?></th>
					<th  align="center"><?php echo JHtml::_( 'grid.sort','PAYMENT_STATUS','status', $this->lists['order_Dir'], $this->lists['order']); ?></th>
				</tr>
			</thead>
			<?php
				$totalTax = $i = 0;
				$totalPaidAmt = $totalNoOfTickets = $totalPrice = $totalCommission = $totalEarn = 0;$subDisc = 0;
				$subPaid = 0;

				foreach ($this->Data as $data)
				{
					if ($data->ticketscount < 0)
					$data->ticketscount = 0;
					if ($data->original_amount < 0)
					$data->original_amount = 0;
					if ($data->fee < 0)
					$data->fee = 0;

					$totalNoOfTickets = $totalNoOfTickets + $data->ticketscount;
					if ($data->status == 'C')
					$totalPaidAmt = $totalPaidAmt + $data->original_amount;
					$totalPrice = $totalPrice + $data->original_amount;
					$totalCommission = $totalCommission + $data->fee;
					$totalTax += $data->order_tax;

					if ($data->order_id)
					$passOrderId = $data->order_id;
					else
					$passOrderId = $data->id;

					$linkForAttendees = JRoute::_('index.php?option=com_jticketing&view=attendees&event='.$data->evid . "&order_id=" . $passOrderId . "&Itemid=" . $this->Itemid);
					$linkForOrders = JRoute::_('index.php?option=com_jticketing&view=orders&layout=order&event='.$data->evid .'&orderid='.$passOrderId.'&Itemid='.$this->Itemid.'&tmpl=component');
					?>
					<tr class="">
						<td align="center" data-title="<?php echo JText::_('ORDER_ID');?>">
							<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" class="jticketing_modal modal" href="<?php echo $linkForOrders;?>"><?php if($data->order_id) echo $data->order_id; else echo $data->id;?></a>
						</td>
						<td align="center" data-title="<?php echo JText::_('EVENT_NAME');?>">
							<?php echo $data->title;?>
						</td>
						<td align="center"  data-title="<?php echo JText::_('PAY_METHOD');?>"><?php
							if (!empty($data->processor) and $data->processor!='Free_ticket')
							{
								$plugin = JPluginHelper::getPlugin('payment', $data->processor);
								$pluginParams = new JRegistry();
								$pluginParams->loadString($plugin->params);
								$param = $pluginParams->get('plugin_name', $data->processor);
								echo $param;
							}
							else
							{
								echo JText::_(($data->processor == NULL) ? "-" : $data->processor);
							}
							?>
						</td>
						<td align="center" data-title="<?php echo JText::_('NUMBEROFTICKETS_BOUGHT');?>">
							<?php echo $data->ticketscount ?>
						</td>
						<td align="center" data-title="<?php echo JText::_('ORIGINAL_AMOUNT');?>">
							<?php echo $this->jticketingmainhelper->getFormattedPrice( number_format(($data->original_amount),2),$this->currency);?>
						</td>
						<td align="center" data-title="<?php echo JText::_('DISCOUNT_AMOUNT');?>">
							<?php $subDisc += $discount = $data->coupon_discount;
								echo $this->jticketingmainhelper->getFormattedPrice( number_format(($discount),2),$this->currency);?>
						</td>
						<?php
						if ($this->jticketingparams->get('allow_taxation'))
						{
							?>
							<td align="center" data-title="<?php echo JText::_('TAX_AMOUNT');?>">
							<?php echo $this->jticketingmainhelper->getFormattedPrice( number_format(($data->order_tax),2),$this->currency); ?>
							</td>
							<?php
						}
						?>
						<td align="center" data-title="<?php echo JText::_('PAID_AMOUNT');?>">
							<?php $subPaid += $data->paid_amount;
								echo $this->jticketingmainhelper->getFormattedPrice( number_format(($data->paid_amount),2),$this->currency); ?>
						</td>
						<td align="center" data-title="<?php echo JText::_('COUPON_CODE_DIS');?>">
							<?php if(!empty($data->coupon_code)){ echo $data->coupon_code. ' ';} else echo '-';?>
						</td>
						<td align="center" data-title="<?php echo JText::_('PAYMENT_STATUS');?>">
							<?php echo $this->payment_statuses[$data->status]; ?>
						</td>
					</tr>
					<?php
				$i++;
				}
				?>
				<tr	class="jticket_row_head">
					<td colspan="3" align="right" class = "hidden-xs">
						<div class="jtright hidden-xs"><b><?php echo JText::_('TOTAL');?></b> </div>
					</td>
					<td align="center" data-title="<?php echo JText::_('TOTAL_NUMBEROFTICKETS_BOUGHT');?>">
						<b><?php echo number_format($totalNoOfTickets, 0, '', '');?></b>
					</td>
					<td align="center" data-title="<?php echo JText::_('TOTAL_ORIGINAL_AMOUNT');?>">
						<b><?php echo $this->jticketingmainhelper->getFormattedPrice( number_format(($totalPrice),2),$this->currency); ?></b>
					</td>
					<td align="center" data-title="<?php echo JText::_('TOTAL_DISCOUNT_AMOUNT');?>">
						<b><?php echo $this->jticketingmainhelper->getFormattedPrice( number_format(($totalEarn),2),$this->currency); ?></b>
					</td>
					<?php
					if ($this->jticketingparams->get('allow_taxation'))
					{
						?>
						<td align="center" data-title="<?php echo JText::_('TOTAL_TAX_AMOUNT');?>">
							<b><?php echo $this->jticketingmainhelper->getFormattedPrice( number_format(($totalTax),2),$this->currency); ?></b>
						</td>
						<?php
					}
					?>
					<td align="center" data-title="<?php echo JText::_('TOTAL_PAID_AMOUNT');?>">
						<b><?php echo $this->jticketingmainhelper->getFormattedPrice( number_format(($subPaid),2),$this->currency); ?></b>
					</td>
					<td align="center" class = "hidden-xs hidden-sm"></td>
					<td align="center" class = "hidden-xs hidden-sm"></td>
				</tr>
			</table>
		</div>
		<input type="hidden" name="option" value="com_jticketing" />
		<input type="hidden" name="task" value="display" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['search_event'];?>" />
		<input type="hidden" name="controller" value="orders" />
		<input type="hidden" name="view" value="orders" />
		<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	</div>
	<?php
		$classPagination = 'pagination';
	?>
	<div class="<?php echo $classPagination; ?>">
		<?php echo $this->pagination->getListFooter(); ?>
	</div>
</form>
<?php
// Newly added for JS toolbar inclusion
if ($this->integration == 1) //if Jomsocial show JS Toolbar Footer
{
	$footer = '';
	$footer = $this->jticketingmainhelper->getJSfooter();
	if (!empty($footer))
	echo $footer;
}
?>
