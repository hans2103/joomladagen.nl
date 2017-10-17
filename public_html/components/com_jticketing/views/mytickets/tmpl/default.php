<?php
	/**
	 * @version    SVN: <svn_id>
	 * @package    JTicketing
	 * @author     Techjoomla <extensions@techjoomla.com>
	 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
	 * @license    GNU General Public License version 2 or later.
	 */
	defined('_JEXEC') or die(';)');
	global $mainframe;
	$document = JFactory::getDocument();
	$input   = JFactory::getApplication()->input;
	$eventid = $input->get('eventid', '', 'INT');
	JHtml::_('behavior.tooltip');
	JHtml::_('behavior.modal', 'a.modal');
	jimport('joomla.filter.output');
	$com_params         = JComponentHelper::getParams('com_jticketing');
	$integration        = $com_params->get('integration');
	$siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
	$currency           = $com_params->get('currency');
	$allow_buy_guestreg = $com_params->get('allow_buy_guestreg');
	$tnc                = $com_params->get('tnc');
	$user               = JFactory::getUser();

	if (empty($user->id))
	{
		echo '<b>' . JText::_('USER_LOGOUT') . '</b>';

		return;
	}

	$payment_statuses = array(
		'P' => JText::_('JT_PSTATUS_PENDING'),
		'C' => JText::_('JT_PSTATUS_COMPLETED'),
		'D' => JText::_('JT_PSTATUS_DECLINED'),
		'E' => JText::_('JT_PSTATUS_FAILED'),
		'UR' => JText::_('JT_PSTATUS_UNDERREVIW'),
		'RF' => JText::_('JT_PSTATUS_REFUNDED'),
		'CRV' => JText::_('JT_PSTATUS_CANCEL_REVERSED'),
		'RV' => JText::_('JT_PSTATUS_REVERSED')
	);

	$integration = $this->jticketingmainhelper->getIntegration();

	if ($integration == 1)
	{
		$jspath = JPATH_ROOT . '/components/com_community';

		if (file_exists($jspath))
		{
			require_once $jspath . '/libraries/core.php';
		}

		$header = '';
		$header = $this->jticketingmainhelper->getJSheader();

		if (!empty($header))
		{
			echo $header;
		}
	}

	?>
<div  class="floattext container-fluid">
	<h1 class="componentheading"><?php	echo JText::_('MY_TICKET');?>	</h1>
</div>
<?php
	$linkbackbutton = '';
	$k = 0;

	if (empty($this->Data))
	{?>
		<div class="clearfix">&nbsp;</div>
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 pull-right alert alert-info jtleft"><?php echo JText::_('NODATA');?></div>
	<?php
		$input   = JFactory::getApplication()->input;
		$eventid = $input->get('event', '', 'INT');

		// If Jomsocial show JS Toolbar Header
		if ($integration == 1)
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
<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">
<form action="" method="post" name="adminForm" id="adminForm">
		<div id="all" class="">
			<div class = " col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="btn-toolbar pull-right">
				<?php
					$class = 'class="ad-status" size="1" onchange="document.adminForm.submit();" name="search_order"';
					echo JHtml::_('select.genericlist', $this->status_order, "search_order", $class, "value", "text", $this->lists['search_order']);
					?>
				<?php
					if (JVERSION > '3.0')
					{
					?>
				<span style="margin-left:5px"><label for="limit"  class="element-invisible"><?php    echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php    echo $this->pagination->getLimitBox();?>
			</div>
			<?php
				}
				?>
			</span>
		<div class="clearfix"></div>
			 <div id='no-more-tables'>
				<table class="table table-striped table-bordered table-hover">
					<thead>
					<tr>
						<th align="center">
							<?php	echo JHtml::_('grid.sort', 'EVENT_NAME', 'title', $this->lists['order_Dir'], $this->lists['order']);?>
						</th>
						<th align="center"><?php	echo JText::_('EVENTDATE');?></th>
						<th align="center"><?php	echo JText::_('TICKET_RATE');?></th>
						<!--
							<th align="center"><?php	echo JText::_('NUMBEROFTICKETS_BOUGHT');?></th>
							-->
						<th align="center"><?php	echo JText::_('TOTAL_AMOUNT_BUY');?></th>
						<th align="center"><?php	echo JText::_('PAYMENT_STATUS');?></th>
						<th align="center"><?php	echo JText::_('VIEW_TICKET');?></th>
					</tr>
				</thead>
					<?php
						$totalnooftickets = 0;
						$totalprice       = 0;
						$i                = 0;

						foreach ($this->Data as $data)
						{
							$datetoshow = '';
							$totalnooftickets = $totalnooftickets + $data->ticketscount;
							$totalprice       = $totalprice + $data->totalamount;
							$timezonestring = $this->jticketingmainhelper->getTimezoneString($data->eventid);

							if($timezonestring['startdate']==$timezonestring['enddate'])
							$datetoshow = $timezonestring['startdate'];
							else
							$datetoshow = JText::_('COM_JTICKETING_FROM') . $timezonestring['startdate'] . JText::_('COM_JTICKETING_TO').$timezonestring['enddate'];

							if(!empty($timezonestring['eventshowtimezone']))
							$datetoshow.='<br/>'.$timezonestring['eventshowtimezone'];

						?>
					<tr>
						<td data-title="<?php echo JText::_('EVENT_NAME');?>">
							<a href="<?php	echo $this->jticketingmainhelper->getEventlink($data->eventid);?>">
							<?php    echo $data->title;?></a>
						</td>
						<td align="center" data-title="<?php echo JText::_('EVENTDATE');?>"> <?php    echo $datetoshow;?></td>
						<td align="center" data-title="<?php echo JText::_('TICKET_RATE');?>">
							<?php
								echo $this->jticketingmainhelper->getFromattedPrice(number_format(($data->price), 2), $currency);
								?>
						</td>
						<!--
							<td align="center"><?php	echo $data->ticketscount;?></td>
							-->
						<td align="center" data-title="<?php echo JText::_('TOTAL_AMOUNT_BUY');?>">
							<?php
								echo $this->jticketingmainhelper->getFromattedPrice(number_format(($data->totalamount), 2), $currency);
								?>
						</td>
						<td align="center" data-title="<?php echo JText::_('PAYMENT_STATUS');?>">
							<?php	echo $payment_statuses[$data->STATUS];?>
						</td>
						<td	align="center" data-title="<?php echo JText::_('VIEW_TICKET');?>">
							<?php
								if ($data->STATUS == 'C')
								{
									$link_o = '';
									$link_o = 'index.php?option=com_jticketing&view=mytickets&tmpl=component&layout=ticketprint&jticketing_usesess=0&jticketing_eventid=';
									$link_o .= $data->eventid . '&jticketing_userid=' . $data->user_id . '&jticketing_ticketid=';
									$link_o .= $data->id . '&jticketing_order_items_id=' . $data->order_items_id;
									$link = JRoute::_($link_o);
								?>
							<a rel="{handler: 'iframe', size: {x: 700, y: 400}}" href="<?php
								echo $link;
								?>" class="modal">
							<span class="editlinktip hasTip" title="<?php echo JText::_('PREVIEW_DES');?>" >
							<?php        echo JText::_('PREVIEW');?></span>
							</a>
							<?php
								}
								else
								{
									echo '-';
								}
								?>
						</td>
					</tr>
					<?php
						}
						?>
					<tr>
						<td colspan="3" align="right" class = "hidden-xs hidden-sm"><?php	echo JText::_('TOTAL');?></td>
						<td align="center" data-title="<?php echo JText::_('TOTAL');?>">
							<b><?php	echo $this->jticketingmainhelper->getFromattedPrice(number_format(($totalprice), 2));?></b>
						</td>
						<td ></td>
						<td ></td>
					</tr>
				</table>
			</div>
		</div>
		</div>
		<!--row-->
		<div class="row">
			<div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<?php    $class_pagination = 'pagination';?>
				<div class="<?php	echo $class_pagination;?> com_jticketing_align_center">
					<?php	echo $this->pagination->getListFooter();?>
				</div>
			</div>
			<!-- col-lg-12 col-md-12 col-sm-12 col-xs-12-->
		</div>
		<!--row-->
	<input type="hidden" name="option" value="com_jticketing" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="controller" value="mytickets" />
	<input type="hidden" name="view" value="mytickets" />
	<input type="hidden" name="Itemid" value="<?php	echo $this->Itemid;?>" />
</form>
</div>
<!--bootstrap-->
<?php
if ($integration == 1)
{
$footer = '';
$footer = $this->jticketingmainhelper->getJSfooter();
if (!empty($footer))
{
echo $footer;
}
}
