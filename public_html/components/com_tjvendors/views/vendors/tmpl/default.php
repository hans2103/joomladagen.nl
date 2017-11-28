<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tjvendors
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');

$listOrder     = $this->state->get('list.ordering');
$listDirn      = $this->state->get('list.direction');

?>
<script type="text/javascript">


	jQuery(document).ready(function ()
	{
		jQuery('#clear-search-button').on('click', function ()
		{
			jQuery('#filter_search').val('');
			jQuery('#adminForm').submit();
		});
	});
	jQuery(document).ready(function ()
	{
		jQuery('#clear-calendar').on('click', function ()
		{
			jQuery('#date').val('');
			jQuery('#dates').val('');
			jQuery('#adminForm').submit();
		});
	});
</script>
<script type="text/javascript">
	tjVSite.vendors.initVendorsJs();
</script>
	<div class="page-header">
		<h2>
			<?php
				echo JText::_('COM_TJVENDOR_VENDOR_PAYOUT_REPORTS');
			?>
		</h2>
	</div>
<?php
$user_id = JFactory::getUser()->id;
if (!empty($this->vendor_id))
{?>
	<form action="<?php
		echo JRoute::_('index.php?option=com_tjvendors&view=vendors');
	?>" method="post" id="adminForm" name="adminForm">
	<div id="j-main-container">

			<div class="btn-group pull-left hidden-phone">
				<div class="row">
					<div class="col-lg-4">
						<div class="input-group">
							<span class="input-group-btn">
								<?php echo JHTML::_('calendar',$this->state->get('filter.fromDate'), 'fromDates', 'dates', '%Y-%m-%d',array( 'class' => 'inputbox', 'onchange' => 'document.adminForm.submit()' ));?>
							</span>
							<span class="input-group-btn">
								<?php echo JHTML::_('calendar',$this->state->get('filter.toDate'), 'toDates', 'date', '%Y-%m-%d',array( 'class' => 'inputbox', 'onchange' => 'document.adminForm.submit()' ));?>
							</span>
							<span class="">
								<button class="btn btn-default" id="clear-calendar" type="button" title="<?php echo JText::_('JSEARCH_CALENDAR_CLEAR'); ?>">
									<i class="fa fa-remove"></i>
								</button>
							</span>
						</div>
					</div>
					<div class="col-lg-5">
						<div class="input-group">
							<span class="input-group-btn">
								<button class="btn btn-default" type="submit"><?php echo JText::_('COM_TJVENDORS_SEARCH');?></button>
							</span>
							<input type="text" class="form-control" name="filter_search" id="filter_search"placeholder="<?php echo JText::_('COM_TJVENDOR_PAYOUTS_SEARCH_BY_CURRENCY');?>"
								value="<?php echo $this->escape($this->state->get('filter.search')); ?>"title="<?php echo JText::_('JSEARCH_FILTER');?>"/>
							<span class="input-group-btn">
								<button class="btn btn-default" id="clear-search-button" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR');?>">
									<i class="fa fa-remove"></i>
								</button>
							</span>
						</div>
					</div>
				</div>
			</div>

				<div class="btn-group pull-left hidden-phone row">
					<div class="">
						<div class="col-lg-6">
							<div class="input-group">
								<span class="input-group-btn">
									<?php echo JHtml::_('select.genericlist', $this->currencies, "currency", 'class="input-medium" size="1" onchange="document.adminForm.submit();"', "currency", "currency", $this->state->get('filter.currency'));
									$currency = $this->state->get('filter.currency');?>
								</span>
								<span class="input-group-btn">
									<?php echo JHtml::_('select.genericlist', $this->uniqueClients, "vendor_client", 'class="input-medium" size="1" onchange="document.adminForm.submit();"', "client", "client", $this->state->get('filter.vendor_client'));
										$client = $this->state->get('filter.vendor_client');?>
								</span>
								<span class="input-group-btn">
									<?php $transactionType[] = array("transactionType"=>JText::_('COM_TJVENDORS_REPORTS_FILTER_ALL_TRANSACTIONS'),"transactionValue" => "0");
											$transactionType[] = array("transactionType"=>JText::_('COM_TJVENDORS_REPORTS_FILTER_CREDIT'),"transactionValue" => JText::_('COM_TJVENDORS_REPORTS_FILTER_CREDIT'));
											$transactionType[] = array("transactionType"=>JText::_('COM_TJVENDORS_REPORTS_FILTER_DEBIT'),"transactionValue" => JText::_('COM_TJVENDORS_REPORTS_FILTER_DEBIT'));
											echo JHtml::_('select.genericlist', $transactionType, "transactionType", 'class="input-medium" size="1" onchange="document.adminForm.submit();"', "transactionValue", "transactionType", $this->state->get('filter.transactionType'));
											$transactionType = $this->state->get('filter.transactionType'); ?>
								</span>
								<span class="input-group-btn">
									<label for="limit" class="element-invisible">
										<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
									</label>
									<?php echo $this->pagination->getLimitBox(); ?>
								</span>
							</div>
						</div>
					</div>
				</div>
		</div>
					<?php
						if (empty($this->items))
						{?>
							<div class="clearfix">&nbsp;</div>
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 pull-right alert alert-info jtleft"><?php echo JText::_('COM_TJVENDOR_NO_MATCHING_RESULTS');?></div>
						<?php }
						else
						{
						?>
		<table class="table table-striped table-hover">
			<thead>
					<tr>
						<div class="pull-left alert alert-info">
							<div>
								<?php echo JText::_('COM_TJVENDORS_REPORTS_CREDIT_NOTE');?>
							</div>
							<div>
								<?php echo JText::_('COM_TJVENDORS_REPORTS_DEBIT_NOTE'); ?>
							</div>
						</div>
					</tr>
					<tr>
						<th width="1%">
							<?php echo "Sr.No";?>
					   </th>
						<th width="5%">
							<?php echo JHtml::_('grid.sort', 'COM_TJVENDORS_REPORTS_TRANSACTION_ID', 'pass.`transaction_id`', $listDirn, $listOrder);?>
						</th>
					<?php if($client == '0')
						{?>
						<th width="5%">
							<?php echo JHtml::_('grid.sort', 'COM_TJVENDORS_REPORTS_CLIENT', 'vendors.`vendor_client`', $listDirn, $listOrder);?>
						</th>
					<?php }
						if ($currency == '0')
						{?>

						<th width="5%">
							<?php echo JHtml::_('grid.sort', 'COM_TJVENDORS_REPORTS_CURRENCY', 'pass.`currency`', $listDirn, $listOrder); ?>
					   </th>
					<?php }
						if($transactionType == "credit" || empty($transactionType))
						{?>
							<th class='left' width="10%">
								<?php echo JHtml::_('grid.sort',  'COM_TJVENDORS_REPORTS_CREDIT_AMOUNT', 'pass.`credit`', $listDirn, $listOrder);?>
							</th>
					<?php
						}
						if($transactionType == "debit" || empty($transactionType))
						{?>
							<th class='left' width="10%">
								<?php	echo JHtml::_('grid.sort',  'COM_TJVENDORS_REPORTS_DEBIT_AMOUNT', 'pass.`debit`', $listDirn, $listOrder);?>
							</th>
					<?php
						}
					?>
						<th width="10%">
							<?php echo JHtml::_('grid.sort', 'COM_TJVENDORS_REPORTS_REFERENCE_ORDER_ID', 'pass.`reference_order_id`', $listDirn, $listOrder);?>
						</th>
						<th width="15%">
							<?php echo JHtml::_('grid.sort', 'COM_TJVENDORS_REPORTS_TRANSACTION_TIME', 'pass.`transaction_time`', $listDirn, $listOrder);?>
						</th>
						<th width="10%">
							<?php echo JHtml::_('grid.sort', 'COM_TJVENDORS_REPORTS_PENDING_AMOUNT', 'pass.`total`', $listDirn, $listOrder);?>
						</th>
						<th>
							<?php echo JText::_('COM_TJVENDORS_REPORTS_ENTRY_STATUS'); ?>
						</th>
					<th>
						<?php echo JText::_('COM_TJVENDORS_REPORTS_CUSTOMER_NOTE'); ?>
					</th>
					</tr>
			</thead>
			<tfoot>
				<td colspan="5">
			<?php if($currency != '0'):?>
					<div class="pull-right">
						<tr>
							<th colspan="8"></th>
							<th colspan="12">
									<?php echo JText::_('COM_TJVENDORS_REPORTS_CREDIT_AMOUNT'). '&nbsp:&nbsp&nbsp ' .$this->totalDetails['creditAmount']. '&nbsp' . $currency;?>
							</th>
						</tr>
						<tr>
							<th colspan="8"></th>
							<th colspan="12">
									<?php echo JText::_('COM_TJVENDORS_REPORTS_DEBIT_AMOUNT'). '&nbsp:&nbsp&nbsp ' . $this->totalDetails['debitAmount']. '&nbsp' . $currency;?>
							</th>
						</tr>
						<tr>
							<th colspan="8"></th>
							<th colspan="12">
									<?php echo JText::_('COM_TJVENDORS_REPORTS_PENDING_AMOUNT') . '&nbsp:&nbsp&nbsp ' . $this->totalDetails['pendingAmount']. '&nbsp' . $currency?>
							</th>
						</tr>
					</div>
				<?php endif;?>
				</td>
				<td colspan="5">
					<?php echo $this->pagination->getListFooter();?>
				</td>
			</tfoot>
			<tbody>
						<?php
						foreach ($this->items as $i => $row)
						{
						?>
							<tr>
								<td>
									<?php echo $this->pagination->getRowOffset($i);?>
								</td>
								<td>
									<?php echo $row->transaction_id;?>
								</td>
							<?php if($client == '0')
								{?>
								<td>
									<?php	echo JText::_("COM_TJVENDORS_VENDOR_CLIENT_".strtoupper($row->client));?>
								</td>
							<?php }
								if($currency == '0')
								{
								?>
								<td>
									<?php echo $row->currency;?>
								</td>
							<?php }
								if($transactionType == "credit" || empty($transactionType))
						{ ?>
							<td>
								<?php
									if($row->credit <='0')
									{
										echo "0";
									}
									else
									{
										echo $row->credit;
									}
								?>
							</td>
						<?php
						}
						if($transactionType == "debit" || empty($transactionType))
						{
						?>
							<td>
							<?php echo $row->debit;?>
							</td>
						<?php
						}
						?>
								<td>
									<?php echo $row->reference_order_id;?>
								</td>
								<td>
									<?php echo $row->transaction_time;?>
								</td>
								<td>
									<?php echo abs($row->total);?>
								</td>
								<td>
								<?php
								$status = json_decode($row->params, true);
								if($status['entry_status'] == "debit_payout")
								{
									if($row->status == 1)
									{
										echo "Payout Done";
									}
									else
									{
										echo "Payout Pending";
									}
								}
								elseif($status['entry_status'] == "credit_for_ticket_buy")
								{
									echo "Credit Done";
								}
								?>
								</td>
								<td class="center">
								<?php
									if(!empty($status['customer_note']))
									{
										echo $status['customer_note'];
									}
									else
									{
										echo "-";
									}
								?>
								</td>
							</tr>
							<?php
						}
					?>
			</tbody>
		</table>
	<?php
	}
	?>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder;?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo JHtml::_('form.token');?>
	</form>
<?php
}
elseif(!$this->vendor_id &&  $user_id)
{
?>
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 pull-right alert alert-info jtleft"><?php echo JText::_('COM_TJVENDOR_REPORTS_ERROR');?></div>
<?php
}
else
{
	$link =JRoute::_('index.php?option=com_users');
	$app = JFactory::getApplication();
	$app->redirect($link);
}?>
