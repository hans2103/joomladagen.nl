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
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

JHtml::_('behavior.formvalidation');

$document = JFactory::getDocument();
$pageTitle = JText::sprintf('COM_JTICKETING_STEP_SELECT_TICKETS', $this->alleventdata->title);
$document->setTitle($pageTitle);
$session = JFactory::getSession();

$showTicketDescription = $this->jticketing_params->get('show_ticket_type_description');
$currencyDisplayFormat = str_replace(" ", "", $this->jticketing_params->get('currency_display_format'));

if ($showTicketDescription)
{
	$colspan = '4';
}
elseif ($this->EventData->online_events == 1)
{
	$colspan = '1';
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
?>

<?php
if(($totalCount <= 0 && isset($this->eventtickets[0]->ticket) && $this->eventtickets[0]->ticket != 0 && $totalAvailable > 0))
{
	?>
	<div class="alert alert-info col-lg-8 col-md-8 col-sm-8 col-xs-12">
		<?php
			echo JText::_('ALL_TICKETS_SOLD');

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
										<td class="text-left">
											<strong>
												<?php echo JText::_('TICKET_TYPE_TITLE'); ?>
											</strong>
										</td>
										<?php
											if ($showTicketDescription)
											{
												?>
												<td class="text-left">
													<strong>
														<?php echo JText::_('JT_TICKET_TYPE_DESC'); ?>
													</strong>
												</td>
												<?php
											}
										?>
										<td class="text-left">
											<strong>
												<?php echo JText::_('TICKET_TYPE_AVAILABLE'); ?>
											</strong>
										</td>
										<td class="text-left">
											<strong>
												<?php echo JText::_('TICKET_TYPE_TOTAL_PRICE'); ?>
											</strong>
										</td>
									</tr>
								</thead>
								<tbody>
								<tr class="jticketing_details">
									<td data-title="<?php echo JText::_('TICKET_TYPE_TITLE');?>" id="ticket_type">
										<input class="inputbox input-mini" id="type_id" name="type_id[]" type="hidden" value="" >
										<?php
											$i = 0;

											foreach ($this->eventtypedata as $type)
											{
												if ($i == 0)
												{
													$defaultTicket = $type->title;
													$i++;
												}

												$options[] = JHtml::_('select.option', $type->title, $type->title);
											}

											echo JHtml::_('select.genericlist', $options, 'tickets_types', 'class="inputbox"  size="5"', 'value', 'text', $defaultTicket);
										?>
									</td>

									<?php
									if ($showTicketDescription)
									{
										?>
										<td	data-title="<?php echo JText::_('JT_TICKET_TYPE_DESC');?>" id="ticket_desc" ></td>
										<?php
									}
									?>
									<td data-title="<?php echo JText::_('TICKET_TYPE_AVAILABLE');?>" id="ticket_available"> </td>
									<td data-title="<?php echo JText::_('TICKET_TYPE_TOTAL_PRICE');?>">
										<?php
											if ($currencyDisplayFormat ==  '{CURRENCY_SYMBOL}{AMOUNT}')
												echo $this->currency_symbol;
											?>
										<span id="ticket_total_price"></span>
										<?php
											if ($currencyDisplayFormat !=  '{CURRENCY_SYMBOL}{AMOUNT}')
												echo $this->currency_symbol;
											?>
										<input class="totalpriceclass" id="ticket_total_price_inputbox" name="ticket_total_price_inputbox" type="hidden" value="" >
									</td>
								</tr>
								<?php
								if ($this->jticketing_params->get('enable_coupon'))
								{
									?>
									<tr id="cooupon_troption">
										<td class="hidden-xs"></td>
										<td colspan="<?php echo $colspan;?>">
												<span class="">
													<input <?php if(isset($couponCheckboxStyle)) echo $couponCheckboxStyle;?> type="checkbox" aria-invalid="false" class="" id="coupon_chk" name="coupon_chk" value="" size="10" onchange="jtSite.order.displayCoupon()">
													<?php echo JText::_('HAVE_COP');?>
												</span>
												<input id="coupon_code" class="input-small focused" placeholder="<?php echo JText::_('CUPCODE');?>" name="coupon_code" value="<?php if(isset($orderdata->coupon_code)) echo $orderdata->coupon_code; ?>" >
												<input type="button" name="coup_button" id="coup_button" class="btn  btn-default btn-medium" onclick="jtSite.order.applyCoupon()" value="<?php echo JText::_('APPLY');?>">
										</td>
									</tr>
									<tr id= "dis_cop">
										<td class="hidden-xs"></td>
										<td colspan="<?php echo $colspan;?>"><strong><?php echo JText::_('COP_DISCOUNT');?></strong></td>
										<td >
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
													else
													{
														$orderdata->coupon_discount = 0;
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
								?>
								<tr id="dis_amt">
									<td class="hidden-xs"></td>
									<td colspan="<?php echo $colspan;?>" class="text-right">
										<strong><?php echo JText::_( 'TOTALPRICE_PAY' ); ?></strong>
									</td>
									<td>
										<?php
											if ($currencyDisplayFormat ==  '{CURRENCY_SYMBOL}{AMOUNT}')
											echo $this->currency_symbol;
											?>
										<span id="net_amt_pay" name="net_amt_pay"></span>
										<?php
											if ($currencyDisplayFormat != '{CURRENCY_SYMBOL}{AMOUNT}')
											echo $this->currency_symbol;
											?>
									</td>
									<input type="hidden" class="inputbox" value="" name="net_amt_pay_inputbox" id="net_amt_pay_inputbox">
								</tr>
								<?php

								if ($this->allow_taxation and isset($this->tax_per) and $this->tax_per>0)
								{
									?>
									<tr class="tax_tr">
										<td class="hidden-xs"></td>
										<td colspan="<?php echo $colspan;?>" class="text-right">
											<strong>
												<?php echo JText::sprintf('TAX_AMOOUNT',$this->tax_per)."%"; ?>
											</strong>
										</td>
										<td>
											<?php
												if ($currencyDisplayFormat ==  '{CURRENCY_SYMBOL}{AMOUNT}')
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
											<input type="hidden" class="inputbox" value="" name="tax_to_pay_inputbox" id="tax_to_pay_inputbox">
										</td>
									</tr>
									<tr class="tax_tr" >
										<td class="hidden-xs"></td>
										<td colspan="<?php echo $colspan;?>" class="text-right">
											<strong>
												<?php echo JText::_( 'TOTALPRICE_PAY_AFTER_TAX' ); ?>
											</strong>
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
<script type="text/javascript">
	var unlimitedSeats = "<?php echo $type->unlimited_seats; ?>"; 
</script>
