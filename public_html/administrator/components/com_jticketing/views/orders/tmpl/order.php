<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined( '_JEXEC' ) or die( ';)' );

/* If user is on payment layout and log out at that time undefined order is is found
in such condition send to home page or provide error msg */

$session = JFactory::getSession();
$isZeroAmountOrder = $session->get('JT_is_zero_amountOrder');
$JTCouponCode = $session->get('JT_coupon_code');

$billinfo = '';

if (isset($this->orderinfo))
{
	$couponCode = $this->orderinfo[0]->coupon_code;
	if (isset($this->orderinfo[0]->address_type) && $this->orderinfo[0]->address_type == 'BT')
	{
		$billinfo = $this->orderinfo[0];
	}
	else if(isset($this->orderinfo[1]->address_type) && $this->orderinfo[1]->address_type == 'BT')
	{
			$billinfo = $this->orderinfo[1];
	}
}

if (isset($this->orderinfo))
{
	$where =" AND a.id=".$this->orderinfo['0']->order_id;

	if ($this->orderinfo['0']->order_id)
	{
		$orderdetails = $this->jticketingmainhelper->getallEventDetailsByOrder($where);
	}

	$this->orderinfo = $this->orderinfo[0];
}

$ordersEmail = (isset($this->orders_email) ) ? $this->orders_email : 0;

// Show login to guest if the option 'allow_buy_guest' is disable
if (!$this->user->id && !$this->jticketingparams->get( 'allow_buy_guest' ) )
{
	?>
	<div class="well" >
		<div class="alert alert-error">
			<span ><?php echo JText::_('COM_JTICKETING_LOGIN'); ?> </span>
		</div>
	</div>
	<?php
	return false;
}

if (!isset($this->order_authorized))
{
	?>
	<div class="well" >
		<div class="alert alert-error">
			<span ><?php echo JText::_('COM_JTICKETING_ORDER_UNAUTHORISED'); ?> </span>
		</div>
	</div>
	<?php
	return false;
}

if (isset($this->orderview))
{
	$link = $session->get('backlink', '');

	if (!empty($orderdetails))
	{
		$link = $session->get('backlink', '');
		$freeticket = JText::_('ETICKET_PRINT_DETAILS_FREE');
		$freeticket = str_replace('[EVENTNAME]', $orderdetails[0]->title, $freeticket);
		if (!empty($billinfo))
			$freeticket = str_replace('[EMAIL]', $billinfo->user_email, $freeticket);
	}
}
?>
<div class="row-fluid">
	<div class="span12">
		<div class="invoice-title">
			<?php
			if (!empty($billinfo))
			{
				?>
				<h2><?php echo JText::_('JT_ORDERS_REPORT'); ?></h2>
				<?php
					$eventid = $session->get('JT_eventid', 0);

					if ($this->orderinfo->amount <= 0)
					{
						echo $freeticket;
					}
			}?>
			<h3 class="pull-right">
			<?php
			if (isset($this->orderview))
			{
				?>
				<input type="button" class="btn btn-success no-print" onclick="javascript:window.print()" value="<?php echo JText::_('COM_JTICKEING_PRINT');?>">
				<?php
			}
			?>
			</h3></br>
			<h3 class="text-center"><?php echo $orderdetails[0]->title; ?></h3>
		</div>
		<hr>
		<div class="row">
			<div class="span6 text-right">
				<address>
					<?php echo htmlspecialchars($this->company_name, ENT_COMPAT, 'UTF-8'); ?><br>
					<?php echo htmlspecialchars($this->company_address, ENT_COMPAT, 'UTF-8'); ?><br>
					<?php echo htmlspecialchars($this->company_vat_no, ENT_COMPAT, 'UTF-8'); ?><br>
					<strong><?php echo JText::_('COM_JTICKETING_ORDER_ID'); ?></strong>  <?php echo $this->orderinfo->orderid_with_prefix; ?><br>
					<strong><?php echo JText::_('COM_JTICKETING_ORDER_PAYMENT_STATUS'); ?></strong>  <?php echo $this->payment_statuses[$this->orderinfo->status]; ?><br>
					<strong><?php echo JText::_('COM_JTICKETING_ORDER_DATE');?></strong>
					<?php echo $bookdate = JHtml::_('date', $this->orderinfo->cdate, $this->dateFormat);?>
				<br>
				</address>
			</div>
			<div class="span6">
				<address>
				<strong><?php echo JText::_('COM_JTICKETING_BILLED_TO'); ?></strong><br>
					<?php
					if (!empty($billinfo))
					{
						isset($billinfo->lastname) ? $billinfo->lastname : '';
						$billinfo->firstname =  htmlspecialchars($billinfo->firstname, ENT_COMPAT, 'UTF-8');
						$billinfo->lastname  =  htmlspecialchars($billinfo->lastname, ENT_COMPAT, 'UTF-8');
						echo $billinfo->firstname . ' ' . $billinfo->lastname;
						?>
						<br>
						<?php
						if (!empty($billinfo->vat_number))
						{
							echo htmlspecialchars($billinfo->vat_number, ENT_COMPAT, 'UTF-8');
							?>
							<br>
							<?php
						}
						echo $billinfo->phone;?>
						<br>
						<?php
						echo htmlspecialchars($billinfo->user_email, ENT_COMPAT, 'UTF-8');?>
						<br>
						<?php
							$billinfo->address = htmlspecialchars($billinfo->address, ENT_COMPAT, 'UTF-8');
							$billinfo->city    = htmlspecialchars($billinfo->city, ENT_COMPAT, 'UTF-8');
							echo $billinfo->address . ' ' . $billinfo->city;
							?>
						<br>
						<?php
							$zipCode = htmlspecialchars($billinfo->zipcode, ENT_COMPAT, 'UTF-8');
							echo $zipCode . ' ' . $billinfo->state_code . ' ' . $billinfo->country_code;?>
						<br>
						<?php
					}
					?>
				</address>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="span12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><strong><?php echo JText::_('COM_JTICKETING_TICKET_INFO'); ?></strong></h3>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-condensed">
						<thead>
							<tr>
								<td><strong><?php echo JText::_('COM_JTICKETING_NO'); ?></strong></td>
								<td class="text-left"><strong><?php echo  JText::_('COM_JTICKETING_PRODUCT_NAM'); ?></strong></td>
								<td class="text-left"><strong><?php echo JText::_('COM_JTICKETING_PRODUCT_QTY'); ?></strong></td>
								<td class="text-left"><strong><?php echo JText::_('COM_JTICKETING_PRODUCT_PRICE'); ?></strong></td>
								<td class="text-left"><strong><?php echo JText::_('COM_JTICKETING_PRODUCT_TPRICE'); ?></strong></td>
							</tr>
						</thead>
						<tbody>
							<?php
								$tprice = 0;
								$i = 1;

								foreach ($this->orderitems as $order)
								{
									$totalprice = 0;
									if (!isset($order->price))
									$order->price = 0;
									?>
									<tr>
										<td><?php echo $i++;?></td>
										<td class="text-left"><?php echo htmlspecialchars($order->order_item_name, ENT_COMPAT, 'UTF-8');?></td>
										<td class="text-left"><?php echo $order->ticketcount;?></td>
										<td class="text-left"><?php echo $this->jticketingmainhelper->getFormattedPrice( number_format(($order->price),2),$this->currency);?>
										</td>
										<td class="text-left"><?php $totalprice = $order->price * $order->ticketcount; echo $this->jticketingmainhelper->getFormattedPrice(number_format($totalprice,2),$this->currency);?>
										</td>
										<?php
											$tprice = $totalprice + $tprice;
										?>
									</tr>
									<?php
								}
								?>
							<tr>
								<td class="thick-line"> </td>
								<td class="thick-line"> </td>
								<td class="thick-line"> </td>
								<td class="thick-line text-left">
									<strong><?php echo JText::_('COM_JTICKETING_PRODUCT_TOTAL'); ?></strong>
								</td>
								<td class="thick-line text-lfet">
									<span id= "cop_discount" ><?php echo $this->jticketingmainhelper->getFormattedPrice( number_format($tprice,2),$this->currency);
									?>
									</span>
								</td>
							</tr>
							<?php
							$couponCode = trim($couponCode);
							$totalAmountAfterDisc = $this->orderinfo->original_amount;

							if ($this->orderinfo->coupon_discount > 0)
							{
								$totalAmountAfterDisc = $totalAmountAfterDisc - $this->orderinfo->coupon_discount;
								?>
								<tr>
									<td class="thick-line"> </td>
									<td class="thick-line"> </td>
									<td class="thick-line"> </td>
									<td class="thick-line text-left">
										<strong><?php echo sprintf(JText::_('COM_JTICKETING_PRODUCT_DISCOUNT'),$this->orderinfo->coupon_code); ?>
										</strong>
									</td>
									<td class="no-line text-left">
										<span id= "coupon_discount" >
										<?php echo $this->jticketingmainhelper->getFormattedPrice(number_format($this->orderinfo->coupon_discount,2),$this->currency);
										?>
										</span>
									</td>
								</tr>
								<tr class="dis_tr">
									<td class="thick-line"> </td>
									<td class="thick-line"> </td>
									<td class="thick-line"> </td>
									<td class="thick-line text-left">
										<strong><?php echo JText::_('COM_JTICKETING_NET_AMT_PAY');?></strong>
									</td>
									<td class="no-line text-left">
										<span id= "total_dis_cop" >
										<?php
											echo $this->jticketingmainhelper->getFormattedPrice(number_format($totalAmountAfterDisc,2),$this->currency);
										?>
										</span>
									</td>
								</tr>
								<?php
							}

							if (isset($this->orderinfo->order_tax) and $this->orderinfo->order_tax > 0)
							{
								$taxJson = $this->orderinfo->order_tax_details;
								$taxArr = json_decode($taxJson,true);
								?>
								<tr>
									<td class="thick-line"> </td>
									<td class="thick-line"> </td>
									<td class="thick-line"> </td>
									<td class="thick-line text-lfet">
										<strong>
											<?php echo JText::sprintf('TAX_AMOOUNT', $taxArr['percent']) . "";?>
										</strong>
									</td>
									<td class="no-line text-left">
										<span id= "tax_amt" ><?php echo $this->jticketingmainhelper->getFormattedPrice(number_format($this->orderinfo->order_tax, 2), $this->currency);?>
										</span>
									</td>
								</tr>
								<?php
							}
							?>
							<tr>
								<td class="thick-line"> </td>
								<td class="thick-line"> </td>
								<td class="thick-line"> </td>
								<td class="thick-line text-left">
									<strong><?php echo JText::_('COM_JTICKETING_ORDER_TOTAL'); ?></strong>
								</td>
								<td class="no-line text-left">
									<strong>
										<span id="final_amt_pay" name="final_amt_pay"><?php echo $this->jticketingmainhelper->getFormattedPrice(number_format($this->orderinfo->amount,2),$this->currency); ?>
										</span>
									</strong>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
