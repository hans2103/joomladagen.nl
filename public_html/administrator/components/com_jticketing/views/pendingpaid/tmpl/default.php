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
					<div class="btn-group pull-right hidden-phone" style="margin-right:2%">
						<label for="limit" class="element-invisible" ><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
							<?php
							echo $this->pagination->getLimitBox();
							?>
					</div>
					<?php } ?>
				<div id="j-main-container" class="span10">
			<?php else : ?>
				<div id="j-main-container">
			<?php endif;
		endif;
		?>
		<?php if(JVERSION<3.0): ?>

		<div align="right">
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
		<?php endif;?>

			<table 	class="table table-striped  table-hover">
				<tr>
					<th ><?php echo JHtml::_( 'grid.sort','COM_JTICKETING_EVENT_NAME','title', $this->lists['order_Dir'], $this->lists['order']); ?></th>
					<th ><?php echo JText::_('COM_JTICKETING_NUMBER_OF_SEATS');?></th>
					<th ><?php echo JText::_('COM_JTICKETING_FULLY_PAID_SEATS');?></th>
					<th ><?php echo JText::_('COM_JTICKETING_PENDING_SEATS');?></th>


				</tr>



				<?php
				$i =$amt_to_bepaid_eventowner= $totalamount=$amtafter_disc=$totalnooftickets=$totalamount=$totaloriginalamt=$totaldiscount=$totalordertax=$totalcommission=0;

				$totalnooftickets=$totalprice=$totalcommission=$totalearn=0;
				if(!empty($this->Data)){
				foreach($this->Data as $data) {
				 if(empty($data->thumb))
					$data->thumb = JUri::root().'components/com_community/assets/event_thumb.png';
				 else
						$data->thumb = JUri::root().$data->thumb;
				$link = JRoute::_(JUri::base().'index.php?option=com_jticketing&view=attendee_list&eventid='.$data->eventid);
				$pendinglink = JRoute::_(JUri::base().'index.php?option=com_jticketing&view=attendee_list&paymentstatus=DP&eventid='.$data->eventid);
				$confirmlink = JRoute::_(JUri::base().'index.php?option=com_jticketing&view=attendee_list&paymentstatus=C&eventid='.$data->eventid);

				?>
				<tr>
					<td>
							<a href="<?php echo $link;?>"><?php echo ucfirst($data->title);?></a>
					</td>

					<td align="center"><?php echo $data->pendingcount+$data->confirmcount; ?></td>
					<td align="center"><a href="<?php echo $confirmlink;?>"><?php if(isset($data->confirmcount)) echo $data->confirmcount;else echo '0'; ?></a></td>
					<td align="center"><a href="<?php echo $pendinglink;?>"><?php if(isset($data->pendingcount))echo $data->pendingcount;else echo '0'; ?></a></td>


				</tr>
				<?php $i++;}
			}
			else{
				?>
				<tr>
					<td colspan="11">
						<?php echo JText::_('COM_JTICKETING_NO_DATA');?>
					</td>
				</tr>
				<?php
			}?>


			</table>
			<input type="hidden" name="option" value="com_jticketing" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="defaltevent" value="<?php echo $this->lists['search_event'];?>" />
			<input type="hidden" name="controller" value="pendingpaid" />
			<input type="hidden" name="view" value="pendingpaid" />
			<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
		</div><!--row fluid -->

		<div class="row-fluid">
			<div class="span12">
				<?php
					if(JVERSION<3.0)
						$class_pagination='pager';
					else
						$class_pagination='';
				?>
				<div class="<?php echo $class_pagination; ?> com_jgive_align_center">
					<?php echo $this->pagination->getListFooter(); ?>
				</div>
			</div><!--span12-->
		</div><!--row-fluid-->

	</div><!--bootstrap-->
</form>



