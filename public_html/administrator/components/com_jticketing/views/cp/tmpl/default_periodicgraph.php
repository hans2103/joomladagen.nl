<?php
/**
 * @version    SVN:<SVN_ID>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */

$document = JFactory::getDocument();
$backdate = date('Y-m-d', strtotime(date('Y-m-d') . ' - 30 days'));
$curr_sym = $this->currency;

$js = "function refreshViews()
	{
		var curr_sym = \"" . $curr_sym . "\";
		fromDate = document.getElementById('from').value;
		toDate = document.getElementById('to').value;
		fromDate1 = new Date(fromDate.toString());
		toDate1 = new Date(toDate.toString());
		difference = toDate1 - fromDate1;
		days = Math.round(difference/(1000*60*60*24));
		
		if (parseInt(days)< 0)
		{
			alert(\"" . JText::_('COM_JTICKETING_DATELESS') . "\");

			return;
		}

		/*Set Session Variables*/
		var info = {};
		jQuery.ajax({
			type: 'GET',
			url: 'index.php?option=com_jticketing&task=cp.SetsessionForGraph&fromDate='+fromDate+'&toDate='+toDate,
			dataType: 'json',
			async:false,
			success: function(data) {
			}
		});

		/*Get periodic data and redraw chart*/
		jQuery.ajax({
			type: 'GET',
			url: 'index.php?option=com_jticketing&task=cp.makechart',
			dataType: 'json',
			success: function(data)
			{
				jQuery('#bar_chart_graph').html('' + data.barchart);
				/*Reset hidden field values*/
				document.getElementById('pending_orders').value=data.pending_orders;
				document.getElementById('confirmed_orders').value=data.confirmed_orders;
				document.getElementById('denied_orders').value=data.denied_orders;
				document.getElementById('failed_orders').value=data.failed_orders;
				document.getElementById('reversed_orders').value=data.reversed_orders;
				document.getElementById('canceled_orders').value=data.canceled_orders;
				document.getElementById('underReview').value=data.underReview;
				document.getElementById('refunded_orders').value=data.refunded_orders;

				/*Redraw charts*/
				document.getElementById('periodic_orders').innerHTML = curr_sym + ' ' + data.periodicorderscount;
				drawPeriodicOrdersChart();
			}
		});
	}";

$document->addScriptDeclaration($js);

?>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-pie-chart fa-fw"></i>
		<?php echo JText::_('COM_JTICKETING_PERIODIC_ORDERS');?>
	</div>
	<div class="panel-body">
		<div class="form-inline">
			<div class="col-lg-5 col-md-5 col-sm-5 pull-left">
				<label for="from" class="hidden-xs"><?php echo JText::_('COM_JTICKETING_FROM_DATE'); ?></label>
				<?php echo JHtml::_('calendar',
					$backdate, 'fromDate', 'from', '%Y-%m-%d',
					array('class' => 'inputbox input-xs jt-dashboard-calender','readonly' => 'true')
					);?>
			</div>
			<div class="col-lg-5 col-md-5 col-sm-5 pull-left">
				<label for="from" class="hidden-xs"><?php echo JText::_('COM_JTICKETING_TO_DATE'); ?></label>
				<?php echo JHtml::_('calendar',
				date('Y-m-d'), 'toDate', 'to', '%Y-%m-%d',
				array('class' => 'inputbox input-xs jt-dashboard-calender','readonly' => 'true')
				); ?>
			</div>
			<div class="col-lg-2 col-md-2 col-sm-2 pull-left">
				<label class="hidden-xs">&nbsp;</label>
				<input id="btnRefresh"
							class="btn btn-micro btn-primary"
							type="button"
							value="<?php echo JText::_('COM_JTICKETING_GO'); ?>"
							title="<?php echo JText::_('COM_JTICKETING_ORDERS_GO_TOOLTIP');?>"
							style="font-weight: bold;" onclick="refreshViews();"/>
			</div>
			<div class="clearfix"></div>
		</div>
		<div class="clearifx">&nbsp;</div>
		<div class="list-group">
			<span class="list-group-item">
				<i class="fa fa-money fa-fw"></i> <?php echo JText::_('COM_JTICKETING_PERIODIC_ORDERS_AMOUNT');?>
				<span class="text-muted small">
					<strong id="periodic_orders">
						<?php echo $this->tot_periodicorderscount ? $this->tot_periodicorderscount : 0;
						?>
					</strong>
				</span>
			</span>
		</div>
		<!-- Periodic donations - graph start -->
		<div id="graph-periodic-orders"></div>
		<hr class="hr hr-condensed"/>
		<div class="center">
			<strong class="">
				<?php echo JText::_('COM_JTICKETING_PERIODIC_ORDERS_AMOUNT');?>
			</strong>
		</div>
		<!-- Periodic donations - graph end -->
	</div>
</div>
<?php

// Get data for periodic orders chart
$statsforpie = $this->statsForPie;


$currentmonth = '';

		$pending_orders = $confirmed_orders = $denied_orders = 0;
		$failed_orders = $underReview = $refunded_orders = $canceled_orders = $reversed_orders = 0;

		if (empty($statsforpie[0][0]) && empty($statsforpie[1][0]) && empty($statsforpie[2][0]) && empty($statsforpie[3][0]) && empty($statsforpie[4][0]) && empty($statsforpie[5][0]) && empty($statsforpie[6][0]) && empty($statsforpie[7][0]))
		{
			$barchart = JText::_('COM_JTICKETING_NO_STATS');
			$emptylinechart = 1;
		}
		else
		{
			if (!empty($statsforpie[0]))
			{
				$pending_orders = $statsforpie[0][0]->orders;
			}

			if (!empty($statsforpie[1]))
			{
				$confirmed_orders = $statsforpie[1][0]->orders;
			}

			if (!empty($statsforpie[2]))
			{
				$denied_orders = $statsforpie[2][0]->orders;
			}

			if (!empty($statsforpie[3]))
			{
				$failed_orders = $statsforpie[3][0]->orders;
			}

			if (!empty($statsforpie[4]))
			{
				$underReview = $statsforpie[4][0]->orders;
			}

			if (!empty($statsforpie[5]))
			{
				$refunded_orders = $statsforpie[5][0]->orders;
			}

			if (!empty($statsforpie[6]))
			{
				$canceled_orders = $statsforpie[6][0]->orders;
			}

			if (!empty($statsforpie[7]))
			{
				$reversed_orders = $statsforpie[7][0]->orders;
			}
		}

$emptypiechart = 0;

if (!$pending_orders && !$confirmed_orders && !$denied_orders && !$failed_orders &&! $underReview && !$refunded_orders && !$canceled_orders && !$reversed_orders)
{
	$emptypiechart = 1;
}
?>
<input type = "hidden" name="pending_orders" id = "pending_orders"
value = "<?php echo !empty($pending_orders) ? $pending_orders : '0'; ?>">

<input type = "hidden" name="confirmed_orders" id="confirmed_orders"
value = "<?php echo !empty($confirmed_orders) ? $confirmed_orders : '0';  ?>">

<input type = "hidden" name = "denied_orders" id = "denied_orders"
value = "<?php echo !empty($denied_orders) ? $denied_orders : '0'; ?>">

<input type = "hidden" name = "refunded_orders" id = "refunded_orders"
value = "<?php echo !empty($refunded_orders) ? $refunded_orders: '0';  ?>">

<input type = "hidden" name = "failed_orders" id = "failed_orders"
value = "<?php echo !empty($failed_orders) ? $failed_orders : '0'; ?>">

<input type = "hidden" name = "reversed_orders" id = "reversed_orders"
value = "<?php echo !empty($reversed_orders) ? $reversed_orders : '0'; ?>">

<input type = "hidden" name = "underReview" id = "underReview"
value = "<?php echo !empty($underReview) ? $underReview : '0'; ?>">

<input type = "hidden" name = "canceled_orders" id = "canceled_orders"
value = "<?php echo !empty($canceled_orders) ? $canceled_orders : '0'; ?>">

<script type = 'text/javascript'>
techjoomla.jQuery(document).ready(function()
{
	document.getElementById("pending_orders").value = <?php echo !empty($pending_orders) ? $pending_orders : '0'; ?>;
	document.getElementById("confirmed_orders").value = <?php echo !empty($confirmed_orders) ? $confirmed_orders : '0';?>;
	document.getElementById("denied_orders").value = <?php  echo !empty($denied_orders) ? $denied_orders : '0';?>;
	document.getElementById("refunded_orders").value = <?php echo !empty($refunded_orders) ? $refunded_orders : '0'; ?>;
	document.getElementById("failed_orders").value = <?php  echo !empty($failed_orders) ?$failed_orders : '0'; ?>;
	document.getElementById("reversed_orders").value = <?php  echo !empty($reversed_orders) ?$reversed_orders : '0'; ?>;
	document.getElementById("canceled_orders").value = <?php  echo !empty($canceled_orders) ?$canceled_orders : '0'; ?>;
	document.getElementById("underReview").value = <?php  echo !empty($underReview) ?$underReview : '0'; ?>;
	drawPeriodicOrdersChart();
});
function drawPeriodicOrdersChart()
{
	techjoomla.jQuery('#graph-periodic-orders').html('');

	var pending_orders = document.getElementById('pending_orders').value;
	var confirmed_orders = document.getElementById('confirmed_orders').value;
	var denied_orders = document.getElementById('denied_orders').value;
	var refunded_orders = document.getElementById('refunded_orders').value;
	var failed_orders = document.getElementById('failed_orders').value;
	var reversed_orders = document.getElementById('reversed_orders').value;
	var canceled_orders = document.getElementById('canceled_orders').value;
	var underReview = document.getElementById('underReview').value;

	if (pending_orders > 0 || confirmed_orders > 0 || denied_orders > 0 || refunded_orders > 0 || failed_orders > 0 || reversed_orders > 0 || canceled_orders > 0 || underReview > 0)
	{
		Morris.Donut({
			element: 'graph-periodic-orders',
			data: [
						{
							label: "<?php echo JText::_("JT_PSTATUS_PENDING");?>",
							value: pending_orders
						},
						{
							label: "<?php echo JText::_("JT_PSTATUS_COMPLETED");?>",
							value: confirmed_orders
						},
						{
							label: "<?php echo JText::_("JT_PSTATUS_DECLINED");?>",
							value: denied_orders
						},
						{
							label: "<?php echo JText::_("JT_PSTATUS_REFUNDED");?>",
							value: refunded_orders
						},
						{
							label: "<?php echo JText::_("JT_PSTATUS_FAILED");?>",
							value: failed_orders
						},
						{
							label: "<?php echo JText::_("JT_PSTATUS_REVERSED");?>",
							value: reversed_orders
						},
						{
							label: "<?php echo JText::_("JT_PSTATUS_UNDER_REVIEW");?>",
							value: underReview
						},
						{
							label: "<?php echo JText::_("JT_PSTATUS_CANCEL_REVERSED");?>",
							value: canceled_orders
						}],
			colors: ["#f0ad4e", "#5cb85c", "#428bca", "#d9534f", "#8A2BE2", "#FFCE33", "#33AFFF", "#8A33FF"],
			resize: true
		});
	}
	else
	{
		techjoomla.jQuery('#graph-periodic-orders').html("<div class = 'center'><?php echo JText::_("COM_JTICKETING_NO_MATCHING_RESULTS");?></div>");
	}
}
</script>
