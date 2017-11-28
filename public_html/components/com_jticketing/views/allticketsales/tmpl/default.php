<?php
// no direct access
defined( '_JEXEC' ) or die( ';)' );
jimport( 'joomla.utilities.date');
jimport('joomla.filter.output');
$bootstrapclass="";
$tableclass="table table-striped  table-hover";
$document=JFactory::getDocument();
$jticketingmainhelper = new jticketingmainhelper();
$com_params=JComponentHelper::getParams('com_jticketing');
$integration = $com_params->get('integration');
$siteadmin_comm_per = $com_params->get('siteadmin_comm_per');
$show_js_toolbar = $com_params->get('show_js_toolbar');
$currency = $com_params->get('currency');
$user =JFactory::getUser();
$input=JFactory::getApplication()->input;

if(empty($user->id))
{
	echo '<div class="alert alert-warning">'.JText::_('USER_LOGOUT').'</div>';
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

if($integration==1) //if Jomsocial show JS Toolbar Header
{
	$jspath=JPATH_ROOT.'/components/com_community';

	if(file_exists($jspath))
	{
		require_once($jspath.DS.'libraries'.DS.'core.php');
	}

	$header='';
	$header=$this->jticketingmainhelper->getJSheader();

	if(!empty($header))
	{
		echo $header;
	}
}
?>

<div  class="floattext col-xs-12 col-sm-8">
   <h3 class="componentheading"><?php echo JText::_('TICK_SALES'); ?>	</h3>
</div>
	<?php
	   $eventid =$this->lists['search_event'];
	   if(!$eventid)
	   $eventid=$input->get('event','','INT');
	   $linkbackbutton='';

	   //eoc for JS toolbar inclusion
	   if(empty($this->Data))
	   {
	   ?>
		   <form action="" method="post" name="adminForm"	id="adminForm">
		  <div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12 pull-right alert alert-info jtleft"><?php echo JText::_('NODATA');?></div>
			<input type="hidden" name="option" value="com_jticketing" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="defaltevent" value="<?php echo $this->lists['search_event'];?>" />
			<input type="hidden" name="controller" value="allticketsales" />
			<input type="hidden" name="view" value="allticketsales" />
			<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
		   </form>

		<?php
		   if($integration==1)
		   {
			$footer='';
			$footer=$this->jticketingmainhelper->getJSfooter();
			if(!empty($footer))
				echo $footer;
		   }
		   return;
	   }
	 ?>
	<div class="<?php echo JTICKETING_WRAPPER_CLASS;?>">
	<form action="" method="post" name="adminForm" id="adminForm" class = "container-fluid">
			<div id="all" class="row">
				<div class = "col-lg-12 col-md-12 col-sm-12 col-xs-12">
			 <div style="float:left" class="">
				<?php echo JHtml::_('select.genericlist', $this->status_event, "search_event", 'class="ad-status" size="1"
				   onchange="document.adminForm.submit();" name="search_event"',"value", "text", $this->lists['search_event']); ?>
			 </div>
			 <?php if(JVERSION>='3.0') {?>
			 <div class="btn-group pull-right">
				<?php
					echo $this->pagination->getLimitBox();
				?>
			 </div>
		<div class="clearfix"></div>
			 <?php }?>
			 <div id='no-more-tables'>
				<table class="table table-striped table-bordered table-hover">
				   <thead>
					  <tr>
						 <th ><?php echo JHtml::_( 'grid.sort','EVENT_NAME','title', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						 <!--<th ><?php echo JHtml::_( 'grid.sort','BOUGHTON','cdate', $this->lists['order_Dir'], $this->lists['order']); ?></th>-->
						 <th ><?php echo JHtml::_( 'grid.sort','NUMBEROFTICKETS_SOLD','eticketscount', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						 <th align="center"><?php echo  JText::_( 'EARNINGTOTAL_AMOUNT' ); ?></th>
						 <th align="center"><?php echo  JText::_( 'COMMISSION' ); ?></th>
						 <th align="center"><?php echo  JText::_( 'TOTAL_AMOUNT' ); ?></th>
					  </tr>
				   </thead>
				   <?php
					  $i = $subtotalamount=0;$sclass='';

					  $totalnooftickets=$totalprice=$totalcommission=$totalearn=0;

					  foreach($this->Data as $data) {

					  $totalnooftickets=$totalnooftickets+$data->eticketscount;
					  $totalprice=$totalprice+$data->eamount;
					  $totalcommission=$totalcommission+$data->ecommission;
					  $totalearn=$totalearn+($data->eamount-$data->ecommission);
					   if(empty($data->thumb))
						$data->thumb = JUri::root().'components/com_community/assets/event_thumb.png';
					   else
						$data->thumb = JUri::root().$data->thumb;
						require_once JPATH_SITE . "/components/com_jticketing/helpers/route.php";
						$JTRouteHelper = new JTRouteHelper;
						$attendeesLink = 'index.php?option=com_jticketing&view=attendee_list&event=' . $data->evid;
						$link = $JTRouteHelper->JTRoute($attendeesLink);
					?>
				   <tr>
					  <td data-title="<?php echo JText::_('EVENT_NAME');?>">
						 <a href="<?php echo $link;?>"><?php echo ucfirst($data->title);?></a>
					  </td>
					  <td align="center" data-title="<?php echo JText::_('NUMBEROFTICKETS_SOLD');?>"><?php echo $data->eticketscount ?></td>
					  <td align="center" data-title="<?php echo JText::_('EARNINGTOTAL_AMOUNT');?>"><?php echo $jticketingmainhelper->getFormattedPrice( number_format(($data->eamount),2),$currency); ?></td>
					  <td align="center" data-title="<?php echo JText::_('COMMISSION');?>"><?php echo $jticketingmainhelper->getFormattedPrice( number_format(($data->ecommission),2),$currency); ?></td>
					  <td align="center" data-title="<?php echo JText::_('TOTAL_AMOUNT');?>"><?php	$subtotalearn=$data->eamount-$data->ecommission;
						 echo $jticketingmainhelper->getFormattedPrice( number_format(($subtotalearn),2),$currency); ?>
					  </td>
				   </tr>
				   <?php $i++;} ?>
				   <tr>
					  <td>
						 <div class="jtright hidden-xs hidden-sm"><b><?php echo JText::_('TOTAL');?></b></div>
					  </td>
					  <td data-title="<?php echo JText::_('TOTAL_NUMBEROFTICKETS_SOLD');?>"><b><?php echo number_format($totalnooftickets, 0, '', '');?></b></td>
					  <td data-title="<?php echo JText::_('EARNINGTOTAL_AMOUNT');?>"><b><?php echo $jticketingmainhelper->getFormattedPrice( number_format(($totalprice),2),$currency); ?></b></td>
					  <td data-title="<?php echo JText::_('COMMISSION');?>"><b><?php echo $jticketingmainhelper->getFormattedPrice( number_format(($totalcommission),2),$currency); ?></b></td>
					  <td data-title="<?php echo JText::_('TOTAL_AMOUNT');?>"><b><?php echo $jticketingmainhelper->getFormattedPrice( number_format(($totalearn),2),$currency); ?></b></td>
				   </tr>
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
		  </div>
		  <!--row fluid -->
		  <div class="row">
			 <div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12">
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
			 <!-- col-lg-12 col-md-12 col-sm-12 col-xs-12-->
		  </div>
		  <!--row-->
	   </div>
	</form>
</div>
<!--bootstrap-->
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
