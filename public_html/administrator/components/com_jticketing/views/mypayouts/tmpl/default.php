<?php

/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.utilities.date');
if(JVERSION>=3.0)
	JHtml::_('formbehavior.chosen', 'select');
global $mainframe;
$document =JFactory::getDocument();
$input=JFactory::getApplication()->input;
$event= $input->get( 'event','' );
$jticketingmainhelper = new jticketingmainhelper();
JToolBarHelper::publishList('mypayouts.publish','COM_JTICKETING_PAID');
JToolBarHelper::unpublishList('mypayouts.unpublish','COM_JTICKETING_NOT_PAID');

jimport('joomla.filter.output');
$com_params=JComponentHelper::getParams('com_jticketing');
$currency = $com_params->get('currency');
$document->addStyleSheet(JURI::base().'components/com_jticketing/assets/css/jticketing.css');
$user =JFactory::getUser();
?>
<?php

if(JVERSION>=3.0):

	if(!empty( $this->sidebar)): ?>
	<div id="sidebar">
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>

	</div>

		<div id="j-main-container" class="span10">

	<?php else : ?>
		<div id="j-main-container">
	<?php endif;
endif;
?>
<!-- Header toolbar -->
<?php
if(empty($this->Data))
{
	echo '<form action="" method="post" name="adminForm"	id="adminForm">';
	?>
	<div class="alert alert-info"><?php echo JText::_('NODATA'); ?></div>

	<input type="hidden" name="option" value="com_jticketing" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="defaltevent" value="<?php if(!empty($this->lists['search_event']))echo $this->lists['search_event'];?>" />
<input type="hidden" name="controller" value="mypayouts" />
<input type="hidden" name="view" value="mypayouts" />

</form>
</div>
	<?php
	return;
}
?>
	<form action="" method="post" name="adminForm"	id="adminForm">
		<div class="techjoomla-bootstrap">
			<div class="row-fluid" id="well">
				<?php if(JVERSION>'3.0') {?>
				<div class="btn-group  hidden-phone" style="margin-left:93%">
					<label for="limit" class="element-invisible" ><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
						<?php
						echo $this->pagination->getLimitBox();
						?>
				</div>
				<?php } ?>


			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<tr>
						<th width="1%" class="nowrap center">
								<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th align="center"><?php echo JHTML::_( 'grid.sort','PAYEE_ID','id', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th align="center"><?php echo JHTML::_( 'grid.sort','PAYEE_NM','payee_name', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th align="center"><?php echo JText::_( 'PAYPAL_EMAIL'); ?></th>
						<th align="center"><?php echo JText::_( 'TRANSACTIONID'); ?></th>
						<th align="center"><?php echo JHTML::_( 'grid.sort','PAYOUTDATE','date', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th align="center"><?php echo JHTML::_( 'grid.sort','PAYOUTAMOUNT','amount', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th align="center"><?php echo JHTML::_( 'grid.sort','AMOUT_TO_PAY','amount_to_pay', $this->lists['order_Dir'], $this->lists['order']); ?></th>
						<th align="center"><?php echo JHTML::_( 'grid.sort','PAYOUT_STATUS','status', $this->lists['order_Dir'], $this->lists['order']); ?></th>

					</tr>
					<?php
							$i = 0;
							$totalpaidamount=0;

					foreach($this->Data as $data)
					{

						$row=$data;
						$canChange	= $user->authorise('core.edit.state',	'com_jticketing');
						$published 	= JHTML::_('grid.published',$row, $i);
						$published=str_replace('Unpublish Item','Click here to change from Paid to Unpaid ',$published);
						$published=str_replace('Publish Item','Click here to change from Unpaid to Paid ',$published);

						$link 	= 'index.php?option=com_jticketing&amp;view=mypayouts&amp;task=publishunpublish&amp;layout=default&amp;cid[]='. $row->id. '';

						if ($data->published)
						{
							$totalpaidamount=$data->amount+$totalpaidamount;
						}
					?>
						<tr>
							<td class="center">
								<?php echo JHtml::_('grid.id',$i,$data->id); ?>
							</td>
							<td align="center">
								<a href="<?php
								if(strlen($data->id)<=6)
								{
									$append='';
									for($z=0;$z<(6-strlen($data->id));$z++){
										$append.='0';
									}
									$data->id=$append.$data->id;
								}
								echo 'index.php?option=com_jticketing&view=mypayouts&layout=edit_payout&task=edit&payout_id='.$data->id; ?>"
								title="<?php echo JText::_('COM_JTICKETING_PAYOUT_ID_TOOLTIP');?>">
									<?php echo $data->id;
								?></a>
							</td>

							<td align="center">
								<?php echo $data->payee_name;?>
							</td>
							<td align="center">
								<?php echo $data->payee_id;?>
							</td>
							<td align="center">
								<?php echo $data->transction_id;?>
							</td>
							<td align="center"><?php
								if(JVERSION<'1.6.0')
								echo JHTML::_( 'date', $data->date, '%Y/%m/%d');
								else
								echo JHTML::_( 'date', $data->date, "Y-m-d");?>
							</td>

							<td align="center">
								<?php  echo $jticketingmainhelper->getFormattedPrice( number_format(($data->amount),2),$currency);?>
							</td>
							<td align="center">
								<?php 	if(!empty($this->user_amount_map[$data->user_id]))
											echo $this->user_amount_map[$data->user_id];
										else
											echo '-';
								?>
							 </td>
							<td align="center">
								<?php echo JHtml::_('jgrid.published', $data->published, $i, 'mypayouts.', $canChange, 'cb'); ?>
							</td>

						</tr>
					<?php
						$i++;
					}
					?>
							<tr rowspan="2" height="20">
								<td align="right" colspan="8"></td>
								<td  ></td>
							</tr>
							<tr >

								<td align="right" colspan="7"><div class="jtright"><?php echo JText::_( 'SUBTOTAL'); ?></div></td>
								<td  colspan="2">
									<?php $subtotalamount = $jticketingmainhelper->getsubtotalamount();
									echo $jticketingmainhelper->getFormattedPrice( number_format(($subtotalamount),2),$currency);?>
								</td>
							</tr>

							<tr>
							<td align="right" colspan="7"><div class="jtright"><?php echo JText::_( 'PAID'); ?></div></td>
							<td colspan="2" ><?php  echo $jticketingmainhelper->getFormattedPrice( number_format(($totalpaidamount),2),$currency);?></td>
							</tr>

							<tr>
								<td align="right" colspan="7"><div class="jtright"><?php echo JText::_( 'BAL_AMT'); ?></div></td>
								<td colspan="2" ><?php
										$balanceamt1=$subtotalamount-$totalpaidamount;
										$balanceamt=number_format($balanceamt1, 2, '.', '');
									if($balanceamt=='-0.00')
										echo $jticketingmainhelper->getFormattedPrice( number_format((0.00),2),$currency);
									else
										echo $jticketingmainhelper->getFormattedPrice( number_format(($balanceamt1),2),$currency);?>
								</td>
							</tr>

							<tr><td colspan="9" align="center"><?php echo $this->pagination->getListFooter(); ?></td></tr>
						</table>
					</div>
						<input type="hidden" name="option" value="com_jticketing" />
						<input type="hidden" name="task" value="" />
						<input type="hidden" name="boxchecked" value="0" />
						<input type="hidden" name="controller" value="" />
						<input type="hidden" name="view" value="mypayouts" />

						<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
						<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
					</div>
				</div>
</form>
</div>

