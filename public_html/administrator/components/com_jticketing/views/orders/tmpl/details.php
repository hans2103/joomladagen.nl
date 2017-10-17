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

/*If user is on payment layout and log out at that time undefined order is is found 
in such condition send to home page or provide error msg */

$session = JFactory::getSession();
$isZeroAmountOrder = $session->get('JT_is_zero_amountOrder');
$JTCouponCode = $session->get('JT_coupon_code');
$couponCode = $this->orderinfo[0]->coupon_code ;

if (isset($this->orderinfo[0]->address_type))
{
	if ($this->orderinfo[0]->address_type == 'BT')
		$billinfo = $this->orderinfo[0];
	else if($this->orderinfo[1]->address_type == 'BT')
		$billinfo = $this->orderinfo[1];
}

$where ="  AND a.id=". $this->orderinfo['0']->id;

if ($this->orderinfo['0']->id)
$orderdetails = $this->jticketingmainhelper->getallEventDetailsByOrder($where);
$this->orderinfo = $this->orderinfo[0];
$ordersEmail = (isset($this->orders_email)) ? $this->orders_email : 0;
$emailstyle = "style='background-color: #cccccc'";

if (!$this->user->id && !$this->jticketingparams->get( 'allow_buy_guest'))
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

if(isset($this->orderview))
{
	$link = $session->get('backlink', '');
	if (!empty($orderdetails))
	{
		$link = $session->get('backlink', '');
		$freeTicket = JText::_('ETICKET_PRINT_DETAILS_FREE');
		$freeticket = str_replace('[EVENTNAME]', $orderdetails[0]->title, $freeTicket);
		if (isset($billinfo))
		$freeTicket = str_replace('[EMAIL]', $billinfo->user_email, $freeTicket);
	}

	$eventTitle = $this->jticketingmainhelper->getEventTitle($this->orderinfo->id);
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
		?>
		<div class="techjoomla-bootstrap">
			<h3 class=""><?php echo JText::_('JT_ORDERS_REPORT'); ?></h3>
		</div>
		<?php
}

if (isset($this->order_blocks))
{
	$orderBlocks = $this->order_blocks;
}
else
{
	$orderBlocks = array ('1'=>'billing', '2'=>'cart', '3'=>'order');
}

if (isset($orderBlocks))
{
	?>
	<div class="techjoomla-bootstrap">
		<div class="row-fluid">
		<?php
		if (in_array('order', $orderBlocks))
		{
			?>
			<div class="span6 well">
				<h3><?php echo JText::_('COM_JTICKETING_ORDER_INFO'); ?></h3>
				<table class="table" >
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_ORDER_ID');?></td>
						<td><?php echo $this->orderinfo->orderid_with_prefix;?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_ORDER_DATE');?></td>
						<td><?php echo $this->orderinfo->cdate;?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_AMOUNT');?></td>
						<td><span><?php echo $this->orderinfo->amount . ' ' . $params->get('currency');?></span></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_ORDER_USER');?></td>
						<td>
						<?php 
						$table   = JUser::getTable();
						$userId = intval( $this->orderinfo->user_id );

						if ($userId)
						{
							$creaternm = '';
							if ($table->load( $userId ))
							{
								$creaternm = JFactory::getUser($this->orderinfo->user_id);
							}
							echo (!$creaternm)?JText::_('COM_JTICKETING_NO_USER'): $creaternm->username; 
						 }
						 else
						 {
							echo $billinfo->user_email;
						 }
						 ?>
						</td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_ORDER_IP');?></td>	
						<td><?php echo $this->orderinfo->ip_address;?></td>
					</tr>
					<?php
					if ($this->orderinfo->processor)
					{
						?>
						<tr>
							<td><?php echo JText::_('COM_JTICKETING_ORDER_PAYMENT');?></td>	
							<td>
							<?php 
								if(isset($this->orderinfo->processor)){
								$plugin = JPluginHelper::getPlugin('payment',  $this->orderinfo->processor);
								if(isset($plugin->params)){
								$pluginParams = new JRegistry();
								$pluginParams->loadString($plugin->params);
								$param = $pluginParams->get('plugin_name',  $this->orderinfo->processor); 
								echo $param;}
								else
								echo $this->orderinfo->processor;
								}
							?>
							</td>
						</tr>
						<?php
					}
					if ($this->orderinfo->status)
					{
						?>
						<tr>
							<td><?php echo JText::_('COM_JTICKETING_ORDER_PAYMENT_STATUS');?></td>	
							<td><?php echo $this->payment_statuses[$this->orderinfo->status];?></td>
						</tr>
						<?php
					}
					?>
				</table>
			</div>
			<?php
		}

		if (!empty($billinfo))
		{
			?>
			<div id="jt_wholeCustInfoDiv" class="span6 well" >
			<h3><?php echo JText::_('COM_JTICKETING_BILLIN_INFO'); ?></h3>
				<table class="table" >
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_BILLIN_FNAM');?></td>	
						<td><?php echo $billinfo->firstname;?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_BILLIN_LNAM');?></td>	
						<td><?php echo $billinfo->lastname;?></td>
					</tr>
					<?php 
					if (!empty($billinfo->vat_number))
					{
						?>
						<tr>
							<td><?php echo JText::_('COM_JTICKETING_BILLIN_VAT_NUM');?></td>
							<td><?php echo $billinfo->vat_number;?></td>
						</tr>
						<?php
					}
					?>
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_BILLIN_ADDR');?></td>
						<td><?php echo $billinfo->address;?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_BILLIN_ZIP');?></td>
						<td><?php echo $billinfo->zipcode;?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_BILLIN_COUNTRY');?></td>
						<td><?php echo $billinfo->country_code;?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_BILLIN_STATE');?></td>
						<td><?php echo $billinfo->state_code;?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_BILLIN_CITY');?></td>
						<td><?php echo $billinfo->city;?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_BILLIN_PHON');?></td>
						<td><?php echo $billinfo->phone;?></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_JTICKETING_BILLIN_EMAIL');?></td>
						<td><?php echo $billinfo->user_email;?></td>
					</tr>
				</table>
			</div>  <!-- customer info end  id=qtc_wholeCustInfoDiv-->
			<?php 
		}
		?>
</div>
<div class="row-fluid"> 
	<div class="span12 well"> <!-- cart detail start -->
	<h3><?php echo JText::_('COM_JTICKETING_TICKET_INFO'); ?></h3>
		<?php
		$priceColStyle = "style=\"".(!empty($ordersEmail)?'text-align: right;' :'')."\"";
		$showOptionCol = 0;
		?>
		<table width="100%" class="table">
			<tr>
				<th colspan="2"><?php echo JText::_('COM_JTICKETING_EVENT_NAME'); ?></th>
				<th colspan="3"><?php echo $eventTitle; ?></th>
			</tr>
			<tr>
				<th class="jtitem_num" width="5%" align="right" style="<?php echo ($ordersEmail)?'text-align: left;' :'';  ?>" ><?php echo JText::_('COM_JTICKETING_NO'); ?></th>
				<th class="jtitem_name" align="left" style="<?php echo ($ordersEmail)?'text-align: left;' :'';  ?>" ><?php echo  JText::_('COM_JTICKETING_PRODUCT_NAM'); ?></th>
				<th class="jtitem_qty" align="left" style="<?php echo ($ordersEmail)?'text-align: left;' :'';  ?>" ><?php echo JText::_('COM_JTICKETING_PRODUCT_QTY'); ?></th>
				<th class="jtitem_price" align="left" <?php echo $priceColStyle;  ?> ><?php echo JText::_('COM_JTICKETING_PRODUCT_PRICE'); ?></th>
				<th class="jtitem_tprice" align="left" <?php echo $priceColStyle;  ?> ><?php echo JText::_('COM_JTICKETING_PRODUCT_TPRICE'); ?></th>
			</tr>
			<?php
			$tprice = 0;
			$i = 1;
			foreach ($this->orderitems as $order)
			{
				$totalprice = 0;
				if (!isset($order->price))
				$order->price = 0;
				?>
				<tr class="row0">
						<td class="jtitem_num" ><?php echo $i++;?></td>	
						<td class="jtitem_name" ><?php echo $order->order_item_name;?></td>
						<td class="jtitem_qty" ><?php echo $order->ticketcount;?></td>
						<td class="jtitem_price" <?php echo $priceColStyle;?>><span><?php echo $this->jticketingmainhelper->getFromattedPrice( number_format(($order->price),2), $this->currency);?></span></td>
						<td class="jtitem_tprice" <?php echo $priceColStyle;?>><span><?php $totalprice=$order->price*$order->ticketcount;echo $this->jticketingmainhelper->getFromattedPrice(number_format($totalprice,2), $this->currency); ?></span></td>
					<?php 
						$tprice = $totalprice + $tprice;
					?>
				</tr>
				<?php
			}
			?>
			<tr>
				<td colspan="6">&nbsp;</td>
			</tr>
			<tr>
				<?php
				$col = 3;
				if ($showOptionCol == 1)
				{ $col = 4; }
				?>
				<td colspan="<?php echo $col;?>" > </td>
				<td class="jtitem_tprice_label" align="left"><strong><?php echo JText::_('COM_JTICKETING_PRODUCT_TOTAL'); ?></strong></td>
				<td class="jtitem_tprice" <?php echo $priceColStyle;?>><span id= "cop_discount" ><?php echo $this->jticketingmainhelper->getFromattedPrice( number_format($tprice, 2), $this->currency); ?></span></td>
			</tr>
			<!--discount price -->
			<?php 
				$couponCode = trim($couponCode);
				$totalAmountAfterDisc = $this->orderinfo->original_amount;
				if ($this->orderinfo->coupon_discount > 0)
				{
					$totalAmountAfterDisc = $totalAmountAfterDisc - $this->orderinfo->coupon_discount;
					?>
						<tr>
							<td colspan="<?php echo $col;?>" > </td>
							<td class="jtitem_tprice_label" align="left"><strong><?php echo sprintf(JText::_('COM_JTICKETING_PRODUCT_DISCOUNT'),$this->orderinfo->coupon_code); ?></strong></td>
							<td class="jtitem_tprice" <?php echo $priceColStyle;?>><span id= "coupon_discount" >
							<?php echo $this->jticketingmainhelper->getFromattedPrice(number_format($this->orderinfo->coupon_discount,2), $this->currency); 
							?>
							</span></td>
						</tr>
						<!-- total amt after Discount row-->
						<tr class="dis_tr">
							<td colspan = "<?php echo $col;?>"></td>
							<td  class="jtitem_tprice_label" align="left"><strong><?php echo JText::_('COM_JTICKETING_NET_AMT_PAY');?></strong></td>
							<td class="jtitem_tprice" <?php echo $priceColStyle; ?> ><span id= "total_dis_cop" >
							<?php echo $this->jticketingmainhelper->getFromattedPrice(number_format($totalAmountAfterDisc, 2), $this->currency); 
							?></span></td>
						</tr>
					<?php
				}

				if (isset($this->orderinfo->order_tax) and $this->orderinfo->order_tax > 0)
				{
					$taxJson = $this->orderinfo->order_tax_details;
					$taxArr = json_decode($taxJson,true);
					?>
					<tr>
						<td colspan="<?php echo $col;?>" > </td>
						<td class="jtitem_tprice_label" align="left"><strong><?php echo JText::sprintf('TAX_AMOOUNT',$taxArr['percent']).""; ; ?></strong></td>
						<td class="jtitem_tprice" <?php echo $priceColStyle;?>><span id= "tax_amt" ><?php echo $this->jticketingmainhelper->getFromattedPrice(number_format($this->orderinfo->order_tax,2), $this->currency); ?></span></td>
					</tr>
					<?php
				}
				?>
				<tr>
					<td colspan="<?php echo $col;?>" > </td>
					<td class="jtitem_tprice_label" align="left"><strong><?php echo JText::_('COM_JTICKETING_ORDER_TOTAL'); ?></strong></td>
					<td class="jtitem_tprice" <?php echo $priceColStyle;?>><strong><span id="final_amt_pay"	name="final_amt_pay"><?php echo $this->jticketingmainhelper->getFromattedPrice(number_format($this->orderinfo->amount, 2), $this->currency); ?></span></td>
				</tr>
				<?php
				if ($this->orderinfo->amount <= 0 and empty($this->orderview))
				{
					?>
					<tr>
						<td colspan="5">
							<?php
							$vars = new Stdclass();

							if (!$this->user->id)
							{
								$guestEmail = "&email=".md5($billinfo->user_email);
							}
							$vars->url = JUri::root().substr(JRoute::_("index.php?option=com_jticketing&view=orders&layout=order" . $guestEmail . "&orderid=" . ($this->orderinfo->orderid_with_prefix) . "&processor={$pg_plugin}&Itemid=" . $orderItemid, false), strlen(JUri::base(true))+1);
							?>
							<form action="<?php echo $vars->url; ?>" method="post">
								<div class="jtspacercnf">
									<input type="submit" name="submit" class="button btn btn-primary" value="<?php echo JText::_('COM_JTICKETING_CONFIRM_ORDER');?>" />
								</div>
							</form>
						</td>
					</tr>
					<?php
				}
			}
			?>
		</table>
	</div> 
</div>
