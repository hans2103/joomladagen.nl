<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// no direct access
defined( '_JEXEC' ) or die( ';)' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
JHtml::_('behavior.formvalidation');

$document = JFactory::getDocument();
$pageTitle = JText::sprintf('COM_JTICKETING_STEP_SELECT_TICKETS', $this->alleventdata->title);
$document->setTitle($pageTitle);

$baseUrl = JUri::root();
$maxPerUserPerTicket = JText::_('JT_PERUSER_PER_PURCHASE_LIMIT_ERROR');

if (!$this->max_noticket_peruserperpurchase)
{
	$this->max_noticket_peruserperpurchase = 10;
}

$maxPerUserPerTicket = str_replace('[MAXPERUSER_PERPURCHASE]', $this->max_noticket_peruserperpurchase, $maxPerUserPerTicket);
$currencyDisplayFormat = str_replace(" ", "", $this->jticketing_params->get('currency_display_format'));
$showTicketDescription = $this->jticketing_params->get('show_ticket_type_description');

if ($showTicketDescription)
{
	$colspan = '4';
}
else
{
	$colspan = '3';
}

$totalAvailable = $totalCount = 0;

foreach ($this->eventtypedata as $type)
{
	if (isset($type->count))
	{
		$totalCount = $type->count + $totalCount;
	}

	$totalAvailable = $type->available + $totalAvailable;
}

if (($totalCount <= 0 and isset($this->eventtickets[0]->ticket) and $this->eventtickets[0]->ticket != 0 and $totalAvailable > 0 ))
{ ?>
	<div class="alert alert-info col-lg-8 col-md-8 col-sm-8 col-xs-12">
		<?php echo JText::_('ALL_TICKETS_SOLD');
			return;
		?>
	</div>
	<?php
}

if (isset($this->orderdata['order_info']['0']))
{
	$orderdata = $this->orderdata['order_info']['0'];
}
?>
<form action="" method="post" name="ticketform" id="ticketform"	class="">
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title"><strong><?php echo JText::sprintf('COM_JTICKETING_STEP_SELECT_TICKETS', htmlspecialchars($this->alleventdata->title, ENT_COMPAT, 'UTF-8'));?></strong></h3>
			</div>
				<div class="panel-body">
					<div class="table-responsive">
						<div class="panel-heading">
							<h3 class="panel-title"><strong><?php echo JText::_('COM_JTICKETING_EVENT_INFO');?></strong></h3>
						</div>
						<div id='no-more-tables'>
							<table class="table table-bordered table-condensed">
								<thead>
									<tr>
										<td class="text-left"><strong><?php echo JText::_('EVENT_TITLE'); ?></strong></td>
										<td class="text-left"><strong><?php echo JText::_('EVENTDATE'); ?></strong></td>
										<td class="text-left"><strong><?php echo JText::_('AVAIL_TICKETS'); ?></strong></td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td data-title="<?php echo JText::_('EVENT_TITLE'); ?>">
											<?php echo ucfirst(htmlspecialchars($this->alleventdata->title, ENT_COMPAT, 'UTF-8')); ?>
										</td>
										<td data-title="<?php echo JText::_( 'EVENTDATE' ); ?>">
											<?php echo $this->dateToShow;?>
										</td>
										<td data-title="<?php echo JText::_( 'AVAIL_TICKETS' ); ?>">
											<?php
												if ($totalCount <= 0)
												{
													echo JText::_( 'UNLIM_SEATS' );
												}
												else
												{
													echo $totalCount;
												}
											?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="panel-heading">
							<h3 class="panel-title"><strong><?php echo JText::_('COM_JTICKETING_TICKET_INFO');?></strong></h3>
						</div>
						<div id='no-more-tables'>
							<table class="table table-bordered table-condensed">
								<thead>
									<tr>
										<td class="text-left"><strong><?php echo JText::_('TICKET_TYPE_TITLE'); ?></strong></td>
										<?php
											if ($showTicketDescription)
											{
												?>
												<td class="text-left"><strong><?php echo JText::_('JT_TICKET_TYPE_DESC'); ?></strong></td>
												<?php
											}
										?>
										<td class="text-left"><strong><?php echo JText::_('TICKET_TYPE_PRICE'); ?></strong></td>
										<td class="text-left"><strong><?php echo JText::_('TICKET_TYPE_AVAILABLE'); ?></strong></td>
										<td class="text-left"><strong><?php echo JText::_('TICKET_TYPE_NO'); ?></strong></td>
										<td class="text-left"><strong><?php echo JText::_('TICKET_TYPE_TOTAL_PRICE'); ?></strong></td>
									</tr>
								</thead>
								<tbody>
									<?php
									$totalTypePrice = 0;
									$unlimited = 0;
									$ticketTypeCount = 0;
									$ticketTypePrice = 0;

									foreach($this->eventtypedata as $type)
									{
										if (((isset($type->count) and $type->count > 0 and $type->unlimited_seats == 0) or $type->unlimited_seats == 1) and !($type->hide_ticket_type))
										{
											if ($type->title)
											{
												?>
												<tr>
													<td	data-title="<?php echo JText::_('TICKET_TYPE_TITLE');?>">
														<input class="inputbox input-mini" id="type_id[]" name="type_id[]" type="hidden" value="<?php echo $type->id?>" >
															<?php echo htmlspecialchars($type->title, ENT_COMPAT, 'UTF-8'); ?>
													</td>
													<?php
													if ($showTicketDescription)
													{
														?>
														<td data-title="<?php echo JText::_('JT_TICKET_TYPE_DESC');?>">
															<?php echo htmlspecialchars($type->desc, ENT_COMPAT, 'UTF-8');?>
														</td>
														<?php
													}
													?>
													<td data-title="<?php echo JText::_('TICKET_TYPE_PRICE');?>">
													<?php
														if ($type->price == 0)
														{
															echo JText::_('FREE_TICKET');
														}
														else
														{
															if ($currencyDisplayFormat ==  '{CURRENCY_SYMBOL}{AMOUNT}')
															echo $this->currency_symbol . ' ' . $type->price;
															else
															echo $type->price . ' ' . $this->currency_symbol;
														}

														if (isset($this->orderdata['ticket_type_count'][$type->id]))
														{
															$ticketTypeCount = $this->orderdata['ticket_type_count'][$type->id];
															$ticketTypePrice = $type->price * $ticketTypeCount;
															$totalTypePrice  = $totalTypePrice + $ticketTypePrice;
														}
														else
														{
															$ticketTypeCount = 0;
															$ticketTypePrice = 0;
														}
														?>
													</td>
													<td data-title="<?php echo JText::_('TICKET_TYPE_AVAILABLE');?>">
													<?php
														if ($type->unlimited_seats == 1)
														{
															$unlimited = 1;
															echo JText::_( 'UNLIM_SEATS' );
														}
														else
														{
															echo $type->count;
														}
													?>
													</td>
													<td data-title="<?php echo JText::_('TICKET_TYPE_NO');?>">
														<input id="type_ticketcount[<?php echo $type->id?>]"
															name="type_ticketcount[<?php echo $type->id?>]"
															class="input-small type_ticketcounts"
															Onkeyup = "jtSite.order.checkForAlpha(this)"
															onblur="jtSite.order.calTotal('<?php echo $type->count;?>',
																							'<?php echo $type->id;?>',
																							'<?php echo $type->price; ?>',
																							this,'<?php echo $unlimited; ?>',
																							'<?php
																							if ($this->max_noticket_peruserperpurchase)
																								echo $this->max_noticket_peruserperpurchase;
																							else
																								echo "10"; ?>',
																								'<?php echo $maxPerUserPerTicket;?>')"
														type="text" value="<?php echo $ticketTypeCount; ?>" >
													</td>
													<td data-title="<?php echo JText::_('TICKET_TYPE_TOTAL_PRICE');?>">
														<?php
														if ($currencyDisplayFormat ==  '{CURRENCY_SYMBOL}{AMOUNT}')
															echo $this->currency_symbol;
														?>
														<span id="ticket_total_price<?php echo $type->id;?>"><?php echo $ticketTypePrice; ?></span>
														<?php
														if ($currencyDisplayFormat !=  '{CURRENCY_SYMBOL}{AMOUNT}')
															echo $this->currency_symbol;
														?>
														<input class="totalpriceclass"id="ticket_total_price_inputbox<?php echo $type->id;?>" name="ticket_total_price_inputbox<?php echo $type->id;?>" type="hidden" value="<?php echo $ticketTypePrice; ?>" >
													</td>
												</tr>
												<?php
											}
										}
									} // foreach close

									if ($this->ticketType->price > 0)
									{
										?>
										<tr id="total_price">
											<td class="hidden-xs"></td>
											<td colspan="<?php echo $colspan;?>" class="text-right">
												<strong><?php echo JText::_( 'TOTALPRICE' ); ?></strong>
											</td>
											<td>
											<?php
												if ($currencyDisplayFormat ==  '{CURRENCY_SYMBOL}{AMOUNT}')
													echo $this->currency_symbol;
												?>
												<span name="total_amt" id="total_amt"><?php echo $totalTypePrice;?></span> <?php
													if ($currencyDisplayFormat !=  '{CURRENCY_SYMBOL}{AMOUNT}')
													echo $this->currency_symbol;?>
											</td>
											<input type="hidden" value="<?php echo $totalTypePrice;?>" name="total_amt_inputbox" id="total_amt_inputbox">
										</tr>
										<?php
										if ($this->jticketing_params->get('enable_coupon'))
										{
											if (!empty($orderdata->coupon_code))
											{
												$couponDisplay = "display:display";
												$couponCheckboxStyle = "checked=checked";
												$couponCode = $orderdata->coupon_code;
											}
											else
											{
												$couponDisplay = 'display:none';
												$couponCheckboxStyle = "";
												$couponCode = '';
											}
											?>
											<tr style="<?php echo $couponDisplay;?>" id="cooupon_troption">
												<td class="hidden-xs"></td>
												<td colspan="<?php echo $colspan;?>">
													<span class="">
														<input <?php if (isset($couponCheckboxStyle)) echo $couponCheckboxStyle;?> type="checkbox" aria-invalid="false" class="" id="coupon_chk" name="coupon_chk" value="" size="10" onchange="jtSite.order.displayCoupon()">
														<?php echo JText::_('HAVE_COP');?>
													</span>
													<input id="coupon_code" style="<?php echo $couponDisplay;?> " class="input-small focused" placeholder="<?php echo JText::_('CUPCODE');?>" name="coupon_code" value="<?php if(isset($orderdata->coupon_code)) echo $orderdata->coupon_code; ?>" >
													<input type="button" style="<?php echo $couponDisplay;?>" name="coup_button" id="coup_button" class="btn  btn-default btn-medium" onclick="jtSite.order.applyCoupon()" value="<?php echo JText::_('APPLY');?>">
												</td>
											</tr>
											<?php
											if ($this->ticketType->price > 0)
											{
												?>
												<tr id= "dis_cop" style="<?php echo $couponDisplay;?>">
													<td class="hidden-xs"></td>
													<td colspan="<?php echo $colspan;?>">
														<strong><?php echo JText::_('COP_DISCOUNT');?></strong>
													</td>
													<td>
													<?php
														if ($currencyDisplayFormat ==  '{CURRENCY_SYMBOL}{AMOUNT}')
															echo $this->currency_symbol;
														?>
														<span id="dis_cop_amt">
														<?php
															if (!empty($orderdata->coupon_discount))
															{
																echo $orderdata->coupon_discount;
															}
														?>
														</span>&nbsp;<?php
														if ($currencyDisplayFormat !=  '{CURRENCY_SYMBOL}{AMOUNT}')
															echo $this->currency_symbol;
														?>
													</td>
												</tr>
											 <?php
											}
										}
										?>
										<tr id="dis_amt">
											<td class="hidden-xs"></td>
											<td colspan="<?php echo $colspan;?>" style="text-align:right">
												<strong><?php echo JText::_( 'TOTALPRICE_PAY' ); ?></strong>
											</td>
											<td>
												<?php
													if ($totalTypePrice)
													{
														$netAmountPay = (float)$totalTypePrice - (float)$orderdata->coupon_discount;
													}
													else
													{
														$netAmountPay = 0;
													}
												?>
												<?php
												if ($currencyDisplayFormat ==  '{CURRENCY_SYMBOL}{AMOUNT}')
													echo $this->currency_symbol;
												?>
												<span id="net_amt_pay" name="net_amt_pay"><?php echo $netAmountPay?></span>
												<?php
													if ($currencyDisplayFormat !=  '{CURRENCY_SYMBOL}{AMOUNT}')
														echo $this->currency_symbol;
												?>
											</td>
											<input type="hidden" class="inputbox" value="<?php echo $netAmountPay;?>" name="net_amt_pay_inputbox" id="net_amt_pay_inputbox">
										 </tr>
										<?php
										if($this->allow_taxation and isset($this->tax_per) and $this->tax_per>0)
										{
											?>
											<tr class="tax_tr">
												<td class="hidden-xs"></td>
												<td colspan="<?php echo $colspan;?>" style="text-align:right">
													<strong>
														<?php echo JText::sprintf('TAX_AMOOUNT',$this->tax_per)."%"; ?>
													</strong>
												</td>
												<td>
												<?php
													if ($currencyDisplayFormat == '{CURRENCY_SYMBOL}{AMOUNT}')
														echo $this->currency_symbol;
													?>
													<span id="tax_to_pay" name="tax_to_pay">
													<?php
														if (isset($orderdata->order_tax))
														{
															echo $orderdata->order_tax;
														}

														if (isset($orderdata->amount))
														{
															$finalAmount = $orderdata->amount;
														}
														else
														{
															$finalAmount = 0;
														}
													?>
													</span>
													<?php
													if ($currencyDisplayFormat !=  '{CURRENCY_SYMBOL}{AMOUNT}')
														echo $this->currency_symbol;
													?>
													<input type="hidden" class="inputbox" value="<?php if (isset($orderdata->order_tax)){ echo $orderdata->order_tax; } ?>" name="tax_to_pay_inputbox" id="tax_to_pay_inputbox">
												</td>
											</tr>
											<tr class="tax_tr" >
												<td class="hidden-xs"></td>
												<td colspan="<?php echo $colspan;?>" style="text-align:right">
													<strong><?php echo JText::_( 'TOTALPRICE_PAY_AFTER_TAX' ); ?></strong>
												</td>
												<td>
													<?php
													if ($currencyDisplayFormat ==  '{CURRENCY_SYMBOL}{AMOUNT}')
														echo $this->currency_symbol;
														?>
													<span id="net_amt_after_tax" name="net_amt_after_tax">
														<?php echo $finalAmount; ?>
													</span>
													<?php
														if ($currencyDisplayFormat !=  '{CURRENCY_SYMBOL}{AMOUNT}')
														echo $this->currency_symbol;
													?>
													<input type="hidden" class="inputbox" value="<?php  echo $finalAmount; ?>" name="net_amt_after_tax_inputbox" id="net_amt_after_tax_inputbox" />
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
<input type="hidden" name="allow_taxation" id="allow_taxation" value="<?php if($this->allow_taxation and isset($this->tax_per) and $this->tax_per>0) echo $this->allow_taxation;else echo 0;?>" />
<input type="hidden" name="order_tax" id="order_tax" value="0" />
<input type="hidden" name="eventid" value="<?php echo $this->eventid;?>" />
<input type="hidden" name="collect_attendee_information" id="collect_attendee_information" value="<?php if(!empty($this->collect_attendee_info_checkout)) echo $this->collect_attendee_info_checkout; else echo 0;?>" />
<input type="hidden" name="event_type" id="event_type"
value ="<?php echo $event_type = ($this->ticketType->price > 0 ? 1 : 0);?>" />
<input type="hidden" name="event_integraton_id" value="<?php echo $this->event_integraton_id;?>" />
<input type="hidden" name="item_id" id="item_id" value="<?php echo $this->Itemid;?>" />
<input type="hidden" name="order_id" value="" />
<input type="hidden" name="captch_enabled" id="captch_enabled" value="<?php echo $this->captch_enabled;?>" />
<?php
	if ($this->captch_enabled)
	{
		?>
		<div class="g-recaptcha"  id="recaptcha"></div>
		<?php
	}
?>
</form>
<script>
	var jtBaseUrl= "<?php echo $baseUrl; ?>";

	jQuery(document).ready(function(){
	jQuery("#dis_cop").hide();
	});
</script>
