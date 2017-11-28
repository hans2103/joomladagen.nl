<?php
/**
* @version    SVN: <svn_id>
* @package    JTicketing
* @author     Techjoomla <extensions@techjoomla.com>
* @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
* @license    GNU General Public License version 2 or later.
*/

defined('_JEXEC') or die('Restricted access');
global $mainframe;

jimport('joomla.filter.output');
jimport('joomla.utilities.date');
JHtml::_('behavior.modal', 'a.modal');
$input = JFactory::getApplication()->input;

if (empty($this->user->id))
{
	echo '<b>' . JText::_('USER_LOGOUT') . '</b>';

	return;
}

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
<script type="text/javascript">
	jtSite.orders.initOrdersJs();
</script>
<div class="floattext container-fluid">
	<h1 class="componentheading"><?php echo JText::_('COM_JTICKETING_ALL_ORDERS');?></h1>
</div>
<?php
if (empty($this->lists['search_event']))
{
	$this->lists['search_event'] = $input->get('event', '', 'INT');
}

if (empty($this->Data) or $this->noeventsfound == 1)
{
	?>
	<div class="<?php echo JTICKETING_WRAPPER_CLASS;?> container-fluid">
		<form action="" method="post" name="adminForm"	id="adminForm">
			<div id="all" class="row">
				<div class = "col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div style="float:right">
						<?php

						// If no events found dont show filter
						if ($this->noeventsfound != 1)
						{
							$searchEvent = $mainframe->getUserStateFromRequest('com_jticketingsearch_event', 'search_event', '', 'string');
						$cls =  'class="jt_selectbox" size="1" onchange="document.adminForm.submit();"';
						echo JHtml::_('select.genericlist', $this->status_event, "search_event", $cls, "value", "text", $searchEvent);
						$clsp =  'class="jt_selectbox" size="1" onchange="document.adminForm.submit();" name="search_paymentStatus"';
						echo JHtml::_('select.genericlist', $this->search_paymentStatus, "search_paymentStatus", $clsp, "value", "text", strtoupper($this->lists['search_paymentStatus']));
						}
						?>
					</div>
					<div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12 pull-right alert alert-info jtleft"><?php echo JText::_('NODATA');?></div>
					<input type="hidden" name="option" value="com_jticketing" />
					<input type="hidden" name="task" value="" />
					<input type="hidden" name="boxchecked" value="0" />
					<input type="hidden" name="defaltevent" value="<?php echo $this->lists['search_event'];?>" />
					<input type="hidden" name="defaltpaymentStatus" value="<?php echo isset($this->lists['search_paymentStatus']) ? $this->lists['search_paymentStatus'] : $this->lists['search_paymentStatus'] = '';?>" />
					<input type="hidden" name="controller" value="orders" />
					<input type="hidden" name="view" value="orders" />
					<input type="hidden" name="Itemid" value="<?php	echo isset($this->Itemid) ? $this->Itemid : $this->Itemid = '0';?>" />
				</div>
			</div>
		</form>
	</div>
	<?php
		// Newly added for JS toolbar inclusion
		if ($this->integration == 1)
		{
			$footer = '';
			$footer = $this->jticketingmainhelper->getJSfooter();

			if (!empty($footer))
			{
				echo $footer;
			}
		}

		return;
}
?>
<div class="<?php echo JTICKETING_WRAPPER_CLASS;?> container-fluid">
	<form action="" method="post" name="adminForm" id="adminForm">
		<div id="all">
			<div style="float:left">
				<?php
				$searchEvent = $mainframe->getUserStateFromRequest('com_jticketingsearch_event', 'search_event', '', 'string');
				$cls =  'class="jt_selectbox" size="1" onchange="document.adminForm.submit();"';
				echo JHtml::_('select.genericlist', $this->status_event, "search_event", $cls, "value", "text", $searchEvent);
				$clsp =  'class="jt_selectbox" size="1" onchange="document.adminForm.submit();" name="search_paymentStatus"';
				echo JHtml::_('select.genericlist', $this->search_paymentStatus, "search_paymentStatus", $clsp, "value", "text", strtoupper($this->lists['search_paymentStatus']));
				?>
			</div>
			<div class="btn-group pull-right">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox();?>
			</div>
		</div>
		<div class="clearfix"></div>
		<div id='no-more-tables' class = "order">
			<table class="table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th align="center">
						<?php echo JHtml::_('grid.sort', 'ORDER_ID', 'id', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th align="center"><?php echo JText::_('EVENT_NAME');?></th>
					<th align="center">
						<?php echo JHtml::_('grid.sort', 'PAY_METHOD', 'processor', $this->lists['order_Dir'], $this->lists['order']);
						?>
					</th>
					<th align="center"><?php echo JText::_('NUMBEROFTICKETS_SOLD');?></th>
					<th align="center"><?php echo JText::_('ORIGINAL_AMOUNT');?></th>
					<th align="center"><?php echo JText::_('DISCOUNT_AMOUNT');?></th>
					<?php
						if ($this->jticketingparams->get('allow_taxation'))
						{
						?>
						<th align="center"><?php echo JText::_('TAX_AMOUNT');?></th>
						<?php
						}
					?>
					<th align="center"><?php echo JText::_('PAID_AMOUNT');?></th>
					<th align="center"><?php echo JText::_('COM_JTICKETING_FEE');?></th>
					<th align="center"><?php echo JText::_('COUPON_CODE_DIS');?></th>
					<th align="center"><?php echo JHtml::_('grid.sort', 'PAYMENT_STATUS', 'status', $this->lists['order_Dir'], $this->lists['order']);?>
					</th>
				</tr>
			</thead>
			<?php
				$i            = $subFee = $totalTax = 0;
				$totalPaidAmt = $totalNoOfTickets = $totalPrice = $totalCommission = $totalEarn = 0;
				$subDisc      = 0;
				$subPaid      = 0;

				foreach ($this->Data as $data)
				{
					if ($data->ticketscount < 0)
					{
						$data->ticketscount = 0;
					}

					if ($data->original_amount < 0)
					{
						$data->original_amount = 0;
					}

					if ($data->fee < 0)
					{
						$data->fee = 0;
					}

					$totalNoOfTickets = $totalNoOfTickets + $data->ticketscount;

					if ($data->status == 'C')
					{
						$totalPaidAmt = $totalPaidAmt + $data->original_amount;
					}

					$totalPrice      = $totalPrice + $data->original_amount;
					$totalCommission = $totalCommission + $data->fee;
					$totalTax += $data->order_tax;

					if ($data->order_id)
					{
						$passOrderId = $data->order_id;
					}
					else
					{
						$passOrderId = $data->id;
					}

					$url = 'index.php?option=com_jticketing&view=attendees&event=';
					$linkForAttendees = JRoute::_($url . $data->evid . "&order_id=" . $passOrderId . "&Itemid=" . $this->Itemid);
					$url = 'index.php?option=com_jticketing&view=attendees&event=';
					$orderUrl = 'index.php?option=com_jticketing&view=orders&layout=order&event=';
					$linkForOrders    = JRoute::_($orderUrl . $data->evid . '&orderid=' . $passOrderId . '&Itemid=' . $this->Itemid . '&tmpl=component');
					?>
					<tr class="">
						<td  class = "dis_modal" align="center" data-title="<?php echo JText::_('ORDER_ID');?>">
							<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" class="modal" href="<?php
								echo $linkForOrders;?>">
							<?php
								if (!empty($data->order_id))
								{
									echo $data->order_id;
								}
								else
								{
									echo $data->id;
								}
								?></a>
						</td>
						<td align="center" data-title="<?php echo JText::_('EVENT_NAME');?>">
							<?php echo $data->title;?>
						</td>
						<td align="center" data-title="<?php echo JText::_('PAY_METHOD');?>">
							<?php
								if (!empty($data->processor) and $data->processor != 'Free_ticket')
								{
									$plugin       = JPluginHelper::getPlugin('payment', $data->processor);
									$pluginParams = new JRegistry;
									$pluginParams->loadString($plugin->params);
									$param = $pluginParams->get('plugin_name', $data->processor);
									echo $param;
								}
								else
								{
									echo ($data->processor == NULL) ? "-" : $data->processor;
								}
								?>
						</td>
						<td align="center" data-title="<?php echo JText::_('NUMBEROFTICKETS_SOLD');?>">
							<?php echo $data->ticketscount;?>
						</td>
						<td align="center" data-title="<?php echo JText::_('ORIGINAL_AMOUNT');?>">
							<?php echo $this->jticketingmainhelper->getFormattedPrice(number_format(($data->original_amount), 2), $this->currency);?>
						</td>
						<td align="center" data-title="<?php echo JText::_('DISCOUNT_AMOUNT');?>">
							<?php
							$subDisc += $discount = $data->coupon_discount;
							echo $this->jticketingmainhelper->getFormattedPrice(number_format(($discount), 2), $this->currency);
							?>
						</td>
						<?php
						if ($this->jticketingparams->get('allow_taxation'))
						{
							?>
							<td align="center" data-title="<?php echo JText::_('TAX_AMOUNT');?>">
								<?php
									echo $this->jticketingmainhelper->getFormattedPrice(number_format(($data->order_tax), 2), $this->currency);
									?>
							</td>
							<?php
						}
						?>
						<td align="center" data-title="<?php echo JText::_('PAID_AMOUNT');?>">
							<?php
							$subPaid += $data->paid_amount;
							echo $this->jticketingmainhelper->getFormattedPrice(number_format(($data->paid_amount), 2), $this->currency);
							?>
						</td>
						<td align="center" data-title="<?php echo JText::_('COM_JTICKETING_FEE');?>">
							<?php
							$subFee += $data->fee;
							echo $this->jticketingmainhelper->getFormattedPrice(number_format(($data->fee), 2), $this->currency);
							?>
						</td>
						<td align="center" data-title="<?php echo JText::_('COUPON_CODE_DIS');?>">
						<?php
							if (!empty($data->coupon_code))
							{
								echo $data->coupon_code . ' ';
							}
							else
							{
								echo '-';
							}
							?>
						</td>
						<td align="center" data-title="<?php echo JText::_('PAYMENT_STATUS');?>">
						<?php
							if (($data->status) and (!empty($data->processor)))
							{
								$processor = "'" . $data->processor . "'";
								$class = 'class="jt_selectbox" onChange="jtSite.orders.selectStatusOrder(' . $data->id . ',' . $processor . ',this);"';
								echo JHtml::_('select.genericlist', $this->payment_statuses, "pstatus" . $i, $class, "value", "text", $data->status);
							}
							else
							{
								echo $this->payment_statuses[$data->status];
							}
							?>
						</td>
					</tr>
					<?php
					$i++;
				}
				?>
				<tr	class="jticket_row_head">
					<td colspan="3" align="right"  class = "hidden-xs hidden-sm">
						<div class="jtright"><b><?php echo JText::_('TOTAL'); ?></b></div>
					</td>
					<td align="center" data-title="<?php echo JText::_('TOTAL_NUMBEROFTICKETS_SOLD');?>">
						<b><?php echo number_format($totalNoOfTickets, 0, '', '');?></b>
					</td>
					<td align="center" data-title="<?php echo JText::_('TOTAL_ORIGINAL_AMOUNT');?>">
						<b><?php echo $this->jticketingmainhelper->getFormattedPrice(number_format(($totalPrice), 2), $this->currency);?></b>
					</td>
					<td align="center" data-title="<?php echo JText::_('TOTAL_DISCOUNT_AMOUNT');?>">
						<b><?php echo $this->jticketingmainhelper->getFormattedPrice(number_format(($subDisc), 2), $this->currency);?></b>
					</td>
					<?php
					if ($this->jticketingparams->get('allow_taxation'))
					{
						?>
						<td align="center" data-title="<?php echo JText::_('TOTAL_TAX_AMOUNT');?>">
							<b><?php echo $this->jticketingmainhelper->getFormattedPrice(number_format(($totalTax), 2), $this->currency);?></b>
						</td>
					<?php
					}
					?>
					<td align="center" data-title="<?php echo JText::_('TOTAL_PAID_AMOUNT');?>">
						<b><?php echo $this->jticketingmainhelper->getFormattedPrice(number_format(($subPaid), 2), $this->currency);?></b>
					</td>
					<td align="center" data-title="<?php echo JText::_('TOTAL_COM_JTICKETING_FEE');?>">
						<b><?php echo $this->jticketingmainhelper->getFormattedPrice(number_format(($subFee), 2), $this->currency); ?></b>
					</td>
					<td align="center" class = "hidden-xs hidden-sm"></td>
					<td align="center" class = "hidden-xs hidden-sm"></td>
				</tr>
			</table>
		</div>
		<input type="hidden" name="option" value="com_jticketing" />
		<input type="hidden" id='order_id' name="order_id" value="" />
		<input type="hidden" id='payment_status' name="payment_status" value="" />
		<input type="hidden" id='processor' name="processor" value="" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['search_event'];?>" />
		<input type="hidden" name="controller" value="orders" />
		<input type="hidden" name="view" value="orders" />
		<input type="hidden" name="Itemid" value="<?php	echo $this->Itemid;?>" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order'];?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir'];?>" />
		<?php
		$classPagination = 'pagination';
		?>
		<div class="<?php echo $classPagination;?>">
			<?php echo $this->pagination->getListFooter();?>
		</div>
	</form>
</div>
<?php
// Newly added for JS toolbar inclusion
if ($this->integration == 1)
{
	$footer = '';
	$footer = $this->jticketingmainhelper->getJSfooter();

	if (!empty($footer))
	{
		echo $footer;
	}
}
?>
