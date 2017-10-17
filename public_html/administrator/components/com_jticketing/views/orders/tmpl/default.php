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

jimport('joomla.filter.output');
jimport( 'joomla.utilities.date');
JHtml::_('behavior.modal', 'a.modal');
$input = JFactory::getApplication()->input;

if (empty($this->user->id))
{
	echo '<b>'.JText::_('USER_LOGOUT').'</b>';
	return;
}

JticketingHelper::getLanguageConstant();
?>
<script type="text/javascript">
	jtAdmin.orders.initOrdersJs();
</script>
<?php
if (empty($this->lists['search_event']))
{
	$this->lists['search_event'] = $input->get('event','','INT');
}
?>
<form action="" method="post" name="adminForm" id="adminForm">
	<?php
	if (!empty( $this->sidebar)):
		?>
		<div id="sidebar" >
			<div id="j-sidebar-container" class="span2">
				<?php echo $this->sidebar; ?>
			</div>
		</div>
		<div id="j-main-container" class="span10">
		<?php
	else :
		?>
			<div id="j-main-container">
		<?php
	endif;

		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));

		if (empty($this->Data))
		{
			?>
			<div class="alert alert-info "><?php echo JText::_('NODATA');?></div>
			<?php
			return;
		}
		?>
		<div class="table-responsive">
			<table class="table table-striped table-hover">
				<tr>
					<th width="1%" class="nowrap center">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="5%" align="center">
						<?php echo JHtml::_( 'grid.sort','ORDER_ID','id', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th width="5%" align="center">
						<?php  echo JText::_( 'EVENT_NAME' ); ?>
					</th>
					<th width="5%" align="center">
						<?php  echo JText::_( 'COM_JTICKETING_BUYER_NAME' ); ?>
					</th>
					<th width="5%" align="center">
						<?php echo JText::_( 'TRANSACTION_ID' );?>
					</th>
					<th align="center">
						<?php echo JHtml::_( 'grid.sort','PAY_METHOD','processor', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
					<th align="center">
						<?php echo JText::_( 'NUMBEROFTICKETS_SOLD' );?>
					</th>
					<th align="center">
						<?php echo  JText::_( 'ORIGINAL_AMOUNT' ); ?>
					</th>
					<th align="center">
						<?php echo  JText::_( 'DISCOUNT_AMOUNT' ); ?>
					</th>
					<?php
					if ($this->jticketingparams->get('allow_taxation'))
					{
						?>
						<th align="center">
							<?php echo  JText::_( 'TAX_AMOUNT' ); ?>
						</th>
						<?php
					}
					?>
					<th align="center">
						<?php echo  JText::_( 'PAID_AMOUNT' ); ?>
					</th>
					<th align="center">
						<?php echo  JText::_( 'COM_JTICKETING_FEE' ); ?>
					</th>
					<th align="center">
						<?php echo  JText::_( 'COUPON_CODE_DIS' ); ?>
					</th>
					<th  align="center">
						<?php echo JHtml::_( 'grid.sort','PAYMENT_STATUS','status', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
				</tr>
				<?php
				$i = $subFee = $totalTax = 0;
				$totalPaidAmt = $totalNoOfTickets = $totalPrice = $totalCommission = $totalEarn = 0;
				$subdisc = 0;
				$subpaid = 0;

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

					if ($data->status=='C')
					{
						$totalPaidAmt = $totalPaidAmt + $data->original_amount;
					}

					$totalPrice = $totalPrice + $data->original_amount;
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

					$link_for_attendees = JRoute::_('index.php?option=com_jticketing&view=attendees&event='.$data->evid ."&order_id=".$passOrderId."&Itemid=".$this->Itemid);
					$link_for_orders = JRoute::_('index.php?option=com_jticketing&view=orders&layout=order&event='.$data->evid .'&orderid='.$passOrderId.'&Itemid='.$this->Itemid.'&tmpl=component');
					?>
					<tr class="">
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $data->id); ?>
						</td>
						<td  align="center">
							<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" class="modal" href="<?php echo $link_for_orders;?>"><?php if($data->order_id) echo $data->order_id; else echo $data->id;?></a>
						</td>
						<td><?php echo $data->title;?></td>
						<td><?php
							if(!empty($data->id)){ echo $data->firstname . ' ' . $data->lastname ;} else echo '-';
							?>
						</td>
						<td><?php
							if(!empty($data->transaction_id)){ echo $data->transaction_id. ' ';} else echo '-';?>
						</td>
						<td align="center"><?php
							echo $data->processor;	?>
						</td>
						<td align="center">
							<?php echo $data->ticketscount ?>
						</td>
						<td align="center">
							<?php  echo $this->jticketingmainhelper->getFromattedPrice( number_format(($data->original_amount),2),$this->currency);?>
						</td>
						<td align="center">
							<?php
							$subdisc += $discount = $data->coupon_discount;
							echo $this->jticketingmainhelper->getFromattedPrice( number_format(($discount), 2), $this->currency);
							?>
						</td>
						<?php
						if ($this->jticketingparams->get('allow_taxation'))
						{
							?>
							<td align="center">
								<?php  echo $this->jticketingmainhelper->getFromattedPrice( number_format(($data->order_tax),2),$this->currency);?>
							</td>
							<?php
						}
						?>
						<td align="center">
							<?php
							$subpaid += $data->paid_amount;
							echo $this->jticketingmainhelper->getFromattedPrice( number_format(($data->paid_amount), 2),$this->currency);?>
						</td>
						<td align="center">
							<?php
							$subFee += $data->fee;
							echo $this->jticketingmainhelper->getFromattedPrice( number_format(($data->fee), 2), $this->currency);?>
						</td>
						<td align="center">
							<?php
							if (!empty($data->coupon_code))
							{
								echo $data->coupon_code. ' ';
							}
							else
							{
								echo '-';
							}
							?>
						</td>
						<td align="center">
						<?php
						if (($data->status) AND (!empty($data->processor)))
						{
							$processor = "'" . $data->processor . "'";
							echo JHtml::_('select.genericlist', $this->payment_statuses, "pstatus" . $i, 'onChange="jtAdmin.orders.selectStatusOrder(' . $data->id . ',' . $processor . ',this);"', "value", " text", $data->status);
						}
						else
						{
							echo $this->payment_statuses[$data->status];
						}
						?>
						</td>
					</tr>
					<?php $i++;
				}
				?>
				<tr>
					<td colspan="5" align="right">
						<div class="jtright">
							<b><?php echo JText::_('TOTAL');?></b>
						</div>
					</td>
					<td align="center">
						<b><?php echo number_format($totalNoOfTickets, 0, '', '');?></b>
					</td>
					<td align="center">
						<b><?php echo $this->jticketingmainhelper->getFromattedPrice( number_format(($totalPrice),2),$this->currency);?></b>
					</td>
					<td align="center">
						<b><?php echo $this->jticketingmainhelper->getFromattedPrice( number_format(($subdisc),2),$this->currency);?></b>
					</td>
					<?php
					if ($this->jticketingparams->get('allow_taxation'))
					{
						?>
						<td align="center">
							<b><?php echo $this->jticketingmainhelper->getFromattedPrice( number_format(($totalTax),2),$this->currency);?></b>
						</td>
						<?php
					}
					?>
					<td align="center">
						<b><?php echo $this->jticketingmainhelper->getFromattedPrice( number_format(($subpaid),2),$this->currency);?></b>
					</td>
					<td align="center">
						<b><?php echo $this->jticketingmainhelper->getFromattedPrice( number_format(($subFee),2),$this->currency);?></b>
					</td>
					<td align="center" colspan="3"></td>
				</tr>
				<tfoot>
					<tr>
						<td colspan="12" align="center">
							<?php echo $this->pagination->getListFooter();  ?>
						</td>
					</tr>
				</tfoot>
			</table>
			<input type="hidden" name="option" value="com_jticketing" />
			<input type="hidden" id='order_id' name="order_id" value="" />
			<input type="hidden" id='payment_status' name="payment_status" value="" />
			<input type="hidden" id='processor' name="processor" value="" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="defaltevent" value="<?php echo $this->lists['search_event'];?>" />
			<input type="hidden" name="controller" value="orders" />
			<input type="hidden" name="view" value="orders" />
			<input type="hidden" name="jversion" value="<?php echo JText::_( 'JVERSION'); ?>" />
			<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
			</div>
		</div><!--row-fluid-->
	</div>
</form>
