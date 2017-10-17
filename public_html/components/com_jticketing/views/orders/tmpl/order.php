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
in such condition send to home page or provide error msg
*/

$jinput = JFactory::getApplication()->input;
$orderid = $jinput->get('orderid','','STRING');
$processor = $jinput->get('processor','','STRING');
$helperobj = new jticketingmainhelper();
$jinput = JFactory::getApplication()->input;
$session = JFactory::getSession();
$isZeroAmountOrder = $session->get('JT_is_zero_amountOrder');
$JTCouponCode = $session->get('JT_coupon_code');
$billinfo = '';
$rootUrl = $url = JUri::root();

if (isset($this->orderinfo))
{
	$couponCode = $this->orderinfo[0]->coupon_code;
	if (isset($this->orderinfo[0]->address_type) && $this->orderinfo[0]->address_type == 'BT')
	{
		$billinfo = $this->orderinfo[0];
	}
	elseif (isset($this->orderinfo[1]->address_type) && $this->orderinfo[1]->address_type == 'BT')
	{
		$billinfo = $this->orderinfo[1];
	}
}

if (isset($this->orderinfo))
{
	$where =" AND a.id=" . $this->orderinfo['0']->order_id;

	if ($this->orderinfo['0']->order_id)
	{
		$orderdetails = $helperobj->getallEventDetailsByOrder($where);
		$link = $helperobj->getEventlink($orderdetails[0]->eventid);

	}

	$this->orderinfo = $this->orderinfo[0];
}
$ordersEmail = ( isset($this->orders_email) ) ? $this->orders_email : 0;
$emailstyle = "style='background-color: #cccccc'";

// Show login to guest if the option 'allow_buy_guest' is disable
if (!$this->user->id && !$this->jticketingparams->get( 'allow_buy_guest' ) )
{
	?>
	<div class="well" >
		<div class="alert alert-danger">
			<span ><?php echo JText::_('COM_JTICKETING_LOGIN'); ?> </span>
		</div>
	</div>
	<?php

	return false;
}

if ($this->order_authorized == 0)
{
	?>
	<div class="well fluid-container" >
		<div class="alert alert-danger">
			<span ><?php echo JText::_('COM_JTICKETING_ORDER_UNAUTHORISED'); ?> </span>
		</div>
	</div>
	<?php

	return false;
}

if (isset($this->orderview))
{
	if (!empty($orderdetails))
	{
		$freeticket = JText::_('ETICKET_PRINT_DETAILS_FREE');
		$freeticket = str_replace('[EVENTNAME]',$orderdetails[0]->title,$freeticket);
		$freeticket = str_replace('[EMAIL]', isset($billinfo->user_email) ? $billinfo->user_email : '', $freeticket);
	}
}
?>
<div class="container-fluid">
	<div class="panel panel-default">
		<div class="panel-body">
			<div id="printDiv">
				<div class="row">
					<div class="col-xs-12">
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
									elseif ($this->orderinfo->status == 'P')
									{
										echo JText::sprintf('ETICKET_PRINT_DETAILS', $billinfo->user_email);
									}
							}?>
							<h3 class="pull-right">
							<?php
							if (isset($this->orderview))
							{
								?>
									<input type="button" class="btn  btn-default  btn-success no-print" onclick="jtSite.orders.printDiv()" value="<?php echo JText::_('COM_JTICKEING_PRINT');?>">
									<h4><a  class="btn  btn-default  btn-info no-print" href="<?php echo $link; ?>"><?php echo JText::_('COM_JTICKETING_BACK_EVENT'); ?></a></h4>
								<?php
							}
							?>
							</h3>
							<h3 class="text-center"><?php echo $orderdetails[0]->title; ?></h3>
						</div>
						<hr>
						<div class="row">
							<div class="col-xs-6">
								<address>
								<strong><?php echo JText::_('COM_JTICKETING_BILLED_TO'); ?></strong><br>
								<?php
								if (!empty($billinfo))
								{
									if (!empty($billinfo->firstname))
									{
										isset($billinfo->lastname) ? $billinfo->lastname : '';
										$billinfo->firstname =  htmlspecialchars($billinfo->firstname, ENT_COMPAT, 'UTF-8'); 
										$billinfo->lastname  =  htmlspecialchars($billinfo->lastname, ENT_COMPAT, 'UTF-8'); 
										echo $billinfo->firstname . ' ' . $billinfo->lastname;
										?>
										</br>
										<?php
									}
									if (!empty($billinfo->vat_number))
									{
										echo htmlspecialchars($billinfo->vat_number, ENT_COMPAT, 'UTF-8');
										?>
										<br>
										<?php 
									}
									if (!empty($billinfo->phone))
									{
										echo $billinfo->phone;?>
										<br>
										<?php
									}
									if (!empty($billinfo->user_email))
									{
										echo htmlspecialchars($billinfo->user_email, ENT_COMPAT, 'UTF-8');
										?>
										<br>
										<?php
									}
									if (!empty($billinfo->city))
									{ 
										isset($billinfo->address) ? $billinfo->address : '';
										$billinfo->address = htmlspecialchars($billinfo->address, ENT_COMPAT, 'UTF-8');
										$billinfo->city    = htmlspecialchars($billinfo->city, ENT_COMPAT, 'UTF-8');
										echo $billinfo->address . ' ' . $billinfo->city;
										?>
										<br>
										<?php
									}
									if (!empty($billinfo->zipcode))
									{
										echo htmlspecialchars($billinfo->zipcode, ENT_COMPAT, 'UTF-8');
										if (!empty($billinfo->state_code))
										{
											echo ", " . $state = $this->TjGeoHelper->getRegionNameFromId($billinfo->state_code);
										}
										if (!empty($billinfo->state_code))
										{
											echo ", " . $country = $this->TjGeoHelper->getCountryNameFromId($billinfo->country_code);
										}
										?>
										<br>
										<?php
									}
								}
								?>
								</address>
							</div>
							<div class="col-xs-6 text-right">
								<address>
									<?php echo htmlspecialchars($this->company_name, ENT_COMPAT, 'UTF-8'); ?><br>
									<?php echo htmlspecialchars($this->company_address, ENT_COMPAT, 'UTF-8'); ?><br>
									<?php echo htmlspecialchars($this->company_vat_no, ENT_COMPAT, 'UTF-8'); ?><br>
									<strong><?php echo JText::_('COM_JTICKETING_ORDER_ID'); ?></strong>  <?php echo $this->orderinfo->orderid_with_prefix; ?><br>
									<strong><?php echo JText::_('COM_JTICKETING_ORDER_PAYMENT_STATUS'); ?></strong>  <?php echo $this->payment_statuses[$this->orderinfo->status]; ?><br>
									<strong><?php echo JText::_('COM_JTICKETING_ORDER_DATE');?></strong>
									<?php echo $orderDate =  JHtml::date($this->orderinfo->cdate, $this->dateFormat, true);?>
								<br>
								</address>
							</div>
						</div>
						<div class="row">
							<?php
								if ($this->orderinfo->processor=="bycheck" || $this->orderinfo->processor=="byorder")
								{
									$plugin = JPluginHelper::getPlugin('payment', $this->orderinfo->processor);
									$params = new JRegistry($plugin->params);
									?>
									<div class="col-xs-6">
										<address>
											<strong><?php echo JText::_('COM_JTICKETING_PAYMENT_INFO'); ?></strong><br>
											<?php echo $this->jticketingparams->get('plugin_mail','');?>
										</address>
									</div>
									<?php
								}
								?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h3 class="panel-title"><strong><?php echo JText::_('COM_JTICKETING_TICKET_INFO'); ?></strong></h3>
							</div>
							<div class="panel-body">
								<div class="table-responsive">
									<div id='no-more-tables'>
										<table class="table table-borderd table-condensed">
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
															<td data-title="<?php echo JText::_('COM_JTICKETING_NO'); ?>"><?php echo $i++;?></td>
															<td class="text-left" data-title="<?php echo JText::_('COM_JTICKETING_PRODUCT_NAM'); ?>"><?php echo htmlspecialchars($order->order_item_name, ENT_COMPAT, 'UTF-8');?></td>
															<td class="text-left" data-title="<?php echo JText::_('COM_JTICKETING_PRODUCT_QTY'); ?>"><?php echo $order->ticketcount;?></td>
															<td class="text-left" data-title="<?php echo JText::_('COM_JTICKETING_PRODUCT_PRICE'); ?>"><?php echo $helperobj->getFromattedPrice( number_format(($order->price),2),$this->currency);?>
															</td>
															<td class="text-left" data-title="<?php echo JText::_('COM_JTICKETING_PRODUCT_TPRICE'); ?>"><?php $totalprice=$order->price*$order->ticketcount; echo $helperobj->getFromattedPrice(number_format($totalprice,2),$this->currency);?>
															</td>
															<?php
																$tprice = $totalprice + $tprice;
															?>
														</tr>
														<?php
													}
													?>
												<tr>
													<td class="thick-line hidden-xs"> </td>
													<td class="thick-line hidden-xs"> </td>
													<td class="thick-line hidden-xs"> </td>
													<td class="thick-line text-left">
														<strong><?php echo JText::_('COM_JTICKETING_PRODUCT_TOTAL'); ?></strong>
													</td>
													<td class="thick-line text-lfet">
														<span id= "cop_discount" ><?php echo $helperobj->getFromattedPrice( number_format($tprice,2),$this->currency);
														?>
														</span>
													</td>
												</tr>
												<?php
												$couponCode = trim($couponCode);
												$totalAmountAfterDisc = $this->orderinfo->original_amount;

												if ($this->orderinfo->coupon_discount > 0)
												{
													$totalAmountAfterDisc = $totalAmountAfterDisc-$this->orderinfo->coupon_discount;
													?>
													<tr>
														<td class="thick-line hidden-xs"> </td>
														<td class="thick-line hidden-xs"> </td>
														<td class="thick-line hidden-xs"> </td>
														<td class="thick-line text-left">
															<strong><?php echo sprintf(JText::_('COM_JTICKETING_PRODUCT_DISCOUNT'),$this->orderinfo->coupon_code); ?>
															</strong>
														</td>
														<td class="no-line text-left">
															<span id= "coupon_discount" >
															<?php echo $helperobj->getFromattedPrice(number_format($this->orderinfo->coupon_discount,2),$this->currency);
															?>
															</span>
														</td>
													</tr>
													<tr class="dis_tr">
														<td class="thick-line hidden-xs"> </td>
														<td class="thick-line hidden-xs"> </td>
														<td class="thick-line hidden-xs"> </td>
														<td class="thick-line text-left">
															<strong><?php echo JText::_('COM_JTICKETING_NET_AMT_PAY');?></strong>
														</td>
														<td class="no-line text-left">
															<span id= "total_dis_cop" >
															<?php
																echo $helperobj->getFromattedPrice(number_format($totalAmountAfterDisc,2),$this->currency);
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
														<td class="thick-line hidden-xs"> </td>
														<td class="thick-line hidden-xs"> </td>
														<td class="thick-line hidden-xs"> </td>
														<td class="thick-line text-lfet">
															<strong>
																<?php echo JText::sprintf('TAX_AMOOUNT', $taxArr['percent']) . "";?>
															</strong>
														</td>
														<td class="no-line text-left">
															<span id= "tax_amt" ><?php echo $helperobj->getFromattedPrice(number_format($this->orderinfo->order_tax, 2), $this->currency);?>
															</span>
														</td>
													</tr>
													<?php
												}
												?>
												<tr>
													<td class="thick-line hidden-xs"> </td>
													<td class="thick-line hidden-xs"> </td>
													<td class="thick-line hidden-xs"> </td>
													<td class="thick-line text-left">
														<strong><?php echo JText::_('COM_JTICKETING_ORDER_TOTAL'); ?></strong>
													</td>
													<td class="no-line text-left">
														<strong>
															<span id="final_amt_pay" name="final_amt_pay"><?php echo $helperobj->getFromattedPrice(number_format($this->orderinfo->amount,2),$this->currency); ?>
															</span>
														</strong>
													</td>
												</tr>
												<?php
												if (!empty($this->orderinfo->status))
												{
													if ( $this->orderinfo->status != 'C')
													{
													?>
														<tr>
															<td class="thick-line hidden-xs"> </td>
															<td class="thick-line hidden-xs"> </td>
															<td class="thick-line hidden-xs"> </td>
															<td class="thick-line hidden-xs"> </td>
															<td class="thick-line text-left">
																<button class="btn btn-sm btn-primary no-print" name="show_getways" id="show_getways" onclick="jtSite.orders.showPaymentGetways();"><?php echo JText::_('COM_JTICKETING_RETRY_PAYMENT'); ?>
																</button>
															</td>
														</tr>
													<?php
													}
												}
												?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php if($ordersEmail == 0): ?>
	<div id="gatewaysContent" style="display:none;">
		<div class="form-group">
			<div class="row">
				<label class="col-lg-12 col-md-12 col-sm-12 col-xs-12 control-label"> <?php echo JText::_('COM_JTICKETING_PAY_METHODS');?></label>
			</div>
			<?php
				if (empty($this->gateways))
				{
					echo JText::_('NO_PAYMENT_GATEWAY');
				}
				else
				{
					foreach($this->gateways as $gateway)
					{ ?>
						<div class="radio">
							<label> <input type="radio" name="gateways" id="<?php echo $gateway; ?>" value="<?php echo $gateway; ?>" aria-label="..." autocomplete="off" /><?php echo $gateway; ?></label>
						</div>
					<?php
					}
				} ?>

		</div>
	</div>
<?php endif; ?>
<div style="clear:both;"></div>
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"id="html-container" name=""></div>
<script type="text/javascript">
	var rootUrl = "<?php echo $rootUrl?>";
	var orderID = "<?php echo $orderid?>";
	jtSite.orders.initOrdersJs();
</script>
