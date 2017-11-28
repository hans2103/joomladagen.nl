<?php
// no direct access
defined( '_JEXEC' ) or die( ';)' );
jimport( 'joomla.utilities.date');
jimport('joomla.filter.output');
$bootstrapclass="";
$tableclass="table table-striped  table-hover";
$document=JFactory::getDocument();
$mainframe = JFactory::getApplication();
$com_params=JComponentHelper::getParams('com_jticketing');
$integration = $com_params->get('integration');
$siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
$show_js_toolbar = $com_params->get('show_js_toolbar');
$currency = $com_params->get('currency');
$jticketingmainhelper = new jticketingmainhelper();
$user =JFactory::getUser();
$input=JFactory::getApplication()->input;

if(empty($user->id))
{

	echo '<b>'.JText::_('USER_LOGOUT').'</b>';
	return;

}

if(JVERSION >= '1.6.0')
	$js_key="
	Joomla.submitbutton = function(task){ ";
else
	$js_key="
	function submitbutton( task ){";

	$js_key.="
		document.adminForm.action.value=task;
		if (task =='cancel')
		{";
	        if(JVERSION >= '1.6.0')
				$js_key.="	Joomla.submitform(task);";
			else
				$js_key.="document.adminForm.submit();";
	    $js_key.="

		}
	}
";

	$document->addScriptDeclaration($js_key);



		$eventid =$this->lists['search_event'];

		if(!$eventid)
		$eventid=$input->get('event','','INT');
	$linkbackbutton='';

//eoc for JS toolbar inclusion

?>


<form action="" method="post" name="adminForm" id="adminForm">
	<div class="techjoomla-bootstrap">

		<div id="all" class="row-fluid">

			<?php
				if(JVERSION>=3.0):
					if(!empty( $this->sidebar)): ?>
					<div id="sidebar" >
						<div id="j-sidebar-container" class="span2">
							<?php echo $this->sidebar; ?>
						</div>

					</div>
					<?php if(JVERSION>'3.0') {?>

					<?php } ?>
				<div id="j-main-container" class="span10">
			<?php else : ?>
				<div id="j-main-container">
			<?php endif;
		endif;
		?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php
				echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<?php if(JVERSION<3.0): ?>

		<div align="right">
			<div class="table-responsive">
				<table>
					<tr>
						<td></td>
						<td><?php

							$search_event = $mainframe->getUserStateFromRequest( 'com_jticketingsearch_event', 'search_event','', 'string' );
							echo JHtml::_('select.genericlist', $this->status_event, "search_event", 'class="ad-status" size="1" onchange="document.adminForm.submit();" name="search_event"',"value", "text", $search_event);
							?>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php endif;?>
		<?php
		if(empty($this->Data))
		{	?>
			<div class="alert alert-info"><?php echo JText::_('NODATA');?></div>

	<?php
			return;
		}
		?>
		<div class="table-responsive">
			<table 	class="table table-striped  table-hover">
				<tr>
					<th ><?php echo JHtml::_( 'grid.sort','EVENT_NAME','title', $this->lists['order_Dir'], $this->lists['order']); ?></th>
					<th ><?php echo JHtml::_( 'grid.sort','NUMBEROFTICKETS_SOLD','eticketscount', $this->lists['order_Dir'], $this->lists['order']); ?></th>
					<th align="center"><?php echo  JText::_( 'COM_JTICKETING_ORIGINAL_AMT' ); ?></th>
					<th align="center"><?php echo  JText::_( 'COM_JTICKETING_COUPON_DISCOUNT' ); ?></th>
					<th align="center"><?php echo  JText::_( 'COM_JTICKETING_AMOUNT_AFTER_DISCOUNT' ); ?></th>
					<th align="center"><?php echo  JText::_( 'COM_JTICKETING_ORDER_TAX' ); ?></th>
					<th align="center"><?php echo  JText::_( 'COM_JTICKETING_TOTAL_PAID' ); ?></th>
					<th align="center"><?php echo  JText::_( 'COM_JTICKETING_COMMISSION' ); ?></th>
					<th align="center"><?php echo  JText::_( 'COM_JTICKETING_NETAMTTOPAY_EVENT' ); ?></th>

				</tr>



				<?php
				$i =$amt_to_bepaid_eventowner= $totalamount=$amtafter_disc=$totalnooftickets=$totalamount=$totaloriginalamt=$totaldiscount=$totalordertax=$totalcommission=0;

				$totalnooftickets=$totalprice=$totalcommission=$totalearn=0;

				foreach($this->Data as $data)
				{


					$totalnooftickets=$totalnooftickets+$data->eticketscount;
					$totalamount=$totalamount+$data->eamount;
					$totaloriginalamt=$totaloriginalamt+$data->eoriginal_amount;
					$amtafter_disc=$amtafter_disc+($data->eoriginal_amount-$data->ecoupon_discount);

					$totaldiscount=$totaldiscount+$data->ecoupon_discount;
					$totalordertax=$totalordertax+$data->eorder_tax;
					$totalcommission=$totalcommission+$data->ecommission;
					$amt_to_bepaid_eventowner=$amt_to_bepaid_eventowner+($data->eamount-$data->ecommission);
					 if(empty($data->thumb))
						$data->thumb = JUri::root().'components/com_community/assets/event_thumb.png';
					 else
							$data->thumb = JUri::root().$data->thumb;
					$link = JRoute::_(JUri::base().'index.php?option=com_jticketing&view=attendee_list&eventid='.$data->evid);
				?>
					<tr>
						<td>
								<a href="<?php echo $link;?>"><?php echo ucfirst($data->title);?></a>
						</td>
						<td align="center">
							<?php echo $data->eticketscount ?>
						</td>
						<td align="center">
							<?php echo $jticketingmainhelper->getFormattedPrice( number_format(($data->eoriginal_amount),2),$currency);?>
						</td>
						<td align="center">
							<?php echo $jticketingmainhelper->getFormattedPrice( number_format(($data->ecoupon_discount),2),$currency);?>
						</td>
						<td align="center">
							<?php echo $jticketingmainhelper->getFormattedPrice( number_format(($data->eoriginal_amount-$data->ecoupon_discount),2),$currency);?>
						</td>
						<td align="center">
							<?php echo $jticketingmainhelper->getFormattedPrice( number_format(($data->eorder_tax),2),$currency);?>
						</td>
						<td align="center">
							<?php echo $jticketingmainhelper->getFormattedPrice( number_format(($data->eamount),2),$currency);?>
						</td>
						<td align="center">
							<?php echo $jticketingmainhelper->getFormattedPrice( number_format(($data->ecommission),2),$currency);?>
						</td>
						<td align="center">
							<?php echo $jticketingmainhelper->getFormattedPrice( number_format(($data->eamount-$data->ecommission),2),$currency);?>
						</td>

					</tr>
			<?php
					$i++;
				}
			?>
				<tr>
				<td>
					<div class="">
						<b><?php echo JText::_('TOTAL');?></b>
					</div>
				</td>
				<td >
					<b><?php echo number_format($totalnooftickets, 0, '', '');?></b>
				</td>
				<td >
					<b><?php echo $jticketingmainhelper->getFormattedPrice( number_format(($totaloriginalamt),2),$currency);?></b>
				</td>
				<td >
					<b><?php echo $jticketingmainhelper->getFormattedPrice( number_format(($totaldiscount),2),$currency);?></b>
				</td>
				<td >
					<b><?php echo $jticketingmainhelper->getFormattedPrice( number_format(($amtafter_disc),2),$currency);?></b>
				</td>
				<td >
					<b><?php echo $jticketingmainhelper->getFormattedPrice( number_format(($totalordertax),2),$currency);?></b>
				</td>

				<td >
					<b><?php echo $jticketingmainhelper->getFormattedPrice( number_format(($totalamount),2),$currency);?></b>
				</td>
				<td >
					<b><?php echo $jticketingmainhelper->getFormattedPrice( number_format(($totalcommission),2),$currency);?></b>
				</td>
				<td >
					<b><?php echo $jticketingmainhelper->getFormattedPrice( number_format(($amt_to_bepaid_eventowner),2),$currency);?></b>
				</td>
				</tr>
				<tfoot>
					<tr>
					<td colspan="9" align="center">
							<?php echo $this->pagination->getListFooter(); ?>
					</td>
					</tr>
				</tfoot>

			</table>
		</div>
		<input type="hidden" name="option" value="com_jticketing" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['search_event'];?>" />
		<input type="hidden" name="controller" value="allticketsales" />
		<input type="hidden" name="view" value="allticketsales" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	</div><!--row fluid -->
	</div><!--bootstrap-->
</form>



