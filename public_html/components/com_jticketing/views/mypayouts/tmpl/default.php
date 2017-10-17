<?php
	// no direct access
	defined( '_JEXEC' ) or die( ';)' );
	jimport('joomla.filter.output');
	$document=JFactory::getDocument();
	$input=JFactory::getApplication()->input;
	$eventid = $input->get('event','','INT');
	$integration=$this->jticketingmainhelper->getIntegration();
	$com_params=JComponentHelper::getParams('com_jticketing');
	$integration = $com_params->get('integration');
	$siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
	$currency = $com_params->get('currency');
	$allow_buy_guestreg = $com_params->get('allow_buy_guestreg');
	$tnc = $com_params->get('tnc');
	$user =JFactory::getUser();

	if(empty($user->id)){

		echo '<b>'.JText::_('USER_LOGOUT').'</b>';
		return;
	}

	//if Jomsocial show JS Toolbar Header
	if($integration==1)
	{
		$header='';
		$header=$this->jticketingmainhelper->getJSheader();
		if(!empty($header))
		echo $header;
	}
	?>
<!-- Header toolbar -->
<div  class="floattext container-fluid">
	<h1 class="componentheading"><?php echo JText::_('MY_EVENT_PAYOUTS'); ?>	</h1>
</div>
<?php
	$eventid=$input->get('event','','INT');
	$linkbackbutton='';
	if(empty($this->Data))
	{?>
<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">
	<div id="all" class="row">
			<div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12 pull-right alert alert-info jtleft">
				<?php
				echo JText::_('NODATA');
				?>
		</div>
	</div>
</div>
<?php
	$input=JFactory::getApplication()->input;
	$eventid = $input->get('event','','INT');
	//if Jomsocial show JS Toolbar Header
	if($integration==1){
		$footer='';
		$footer=$this->jticketingmainhelper->getJSfooter();
		if(!empty($footer))
		echo $footer;

	}

		return;
	}
	?>
<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">
<form action="" method="post" name="adminForm" id="adminForm">
	<div id="all" class="">
		<div class = "col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<?php if(JVERSION>'3.0') {?>
		<div class="btn-group pull-right">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
			<?php
				echo $this->pagination->getLimitBox();
				?>
		</div>
		<?php } ?>
			<div class="clearfix"></div>
			 <div id='no-more-tables'>
				<table class="table table-striped table-bordered table-hover">
				<thead>
				<tr >
					<th ><?php echo JHtml::_( 'grid.sort','PAYPAL_EMAIL_MASSPAYMENT','payee_id', $this->lists['order_Dir'], $this->lists['order']); ?></th>
					<th align="center"><?php echo JText::_( 'TRANSACTIONID'); ?></th>
					<th ><?php echo JHtml::_( 'grid.sort','PAYOUTDATE','date', $this->lists['order_Dir'], $this->lists['order']); ?></th>
					<th ><?php echo JHtml::_( 'grid.sort','PAYOUTAMOUNT','amount', $this->lists['order_Dir'], $this->lists['order']); ?></th>
				</tr>
				</thead>
				<?php
					$i = 0;
					$totalpaidamount=0;
					foreach($this->Data as $data)
					{
							 if(empty($data->thumb))
							 	$data->thumb = 'components/com_community/assets/user_thumb.png';
					?>
				<tr >
					<td class = "jt_nowrap" data-title="<?php echo JText::_('PAYPAL_EMAIL_MASSPAYMENT');?>"><?php echo $data->payee_id;?></td>
					<td align="center" data-title="<?php echo JText::_('TRANSACTIONID');?>"><?php echo $data->transction_id;?></td>
					<td align="center" data-title="<?php echo JText::_('PAYOUTDATE');?>"><?php
						if(JVERSION<'1.6.0')
							echo JHtml::_( 'date', $data->date, '%Y/%m/%d');
						else
							echo JHtml::_( 'date', $data->date, 'Y-m-d');

						?>
					</td>
					<td align="center" data-title="<?php echo JText::_('PAYOUTAMOUNT');?>"><?php echo $this->jticketingmainhelper->getFromattedPrice( number_format(($data->amount),2),$currency);?></td>
				</tr>
				<?php $i++;} ?>

				<tr>
					<td align="right" colspan="3"  class = "hidden-xs hidden-xm">
						<div class="jtright"><b><?php echo JText::_( 'SUBTOTAL'); ?></b></div>
					</td>
					<td align="center" data-title="<?php echo JText::_('SUBTOTAL');?>"><b><?php
						$subtotalamount=$this->subtotalamount;
						echo $this->jticketingmainhelper->getFromattedPrice( number_format(($this->subtotalamount),2),$currency);?></b></td>
				</tr>
				<tr >
					<td align="right" colspan="3" class = "hidden-xs hidden-xm">
						<div class="jtright"><b><?php echo JText::_( 'PAID'); ?></b></div>
					</td>
					<td align="center" data-title="<?php echo JText::_('PAID');?>"><b><?php echo $this->jticketingmainhelper->getFromattedPrice( number_format(($this->totalpaidamt),2),$currency);?></b></td>
				</tr>
				<tr>
					<td align="right" colspan="3"  class = "hidden-xs hidden-xm">
						<b>
							<div class="jtright"><?php echo JText::_( 'BAL_AMT'); ?>
						</b>
						</div>
					</td>
					<td align="center" data-title="<?php echo JText::_('BAL_AMT');?>"><b><?php
						$balanceamt1=$subtotalamount-$this->totalpaidamt;
						 $balanceamt=number_format($balanceamt1, 2, '.', '');
							if($balanceamt=='-0.00')
							echo $this->jticketingmainhelper->getFromattedPrice( number_format((0.00),2),$currency);
							else
							echo $this->jticketingmainhelper->getFromattedPrice( number_format(($balanceamt1),2),$currency);

							?></b></td>
				</tr>
			</table>
		</div>
		</div>
		<input type="hidden" name="option" value="com_jticketing" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="controller" value="mypayouts" />
		<input type="hidden" name="view" value="mypayouts" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	</div>
	<!--row-->
	<div class="row">
		<div class = "col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<?php
				if(JVERSION<3.0)
					$class_pagination='pager';
				else
					$class_pagination='pagination';
				?>
			<div class="<?php echo $class_pagination; ?> com_jgive_align_center">
				<?php echo $this->pagination->getListFooter(); ?>
			</div>
		</div>
		<!--col-lg-12 col-md-12 col-sm-12 col-xs-12-->
	</div>
	<!--row-->
</form>
</div>
<!--jticketing-wrapper-->

<!-- newly added for JS toolbar inclusion  -->
<?php
	if($integration==1) //if Jomsocial show JS Toolbar Footer
	{
	$footer='';
		$footer=$this->jticketingmainhelper->getJSfooter();
		if(!empty($footer))
		echo $footer;
	}
	?>
<!-- eoc for JS toolbar inclusion	 -->
