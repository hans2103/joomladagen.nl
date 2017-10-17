<?php
/**
 * @version    SVN:
 * @package    Com_Tjvendors
 * @author     Techjoomla  <contact@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'administrator/components/com_tjvendors/assets/css/tjvendors.css');
$document->addStyleSheet(JUri::root() . 'media/com_tjvendors/css/list.css');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_tjvendors');
$saveOrder = $listOrder == 'a.`ordering`';
$input = JFactory::getApplication()->input;
$client = $input->get('client', '', 'STRING');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_tjvendors&task=payouts.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'payoutList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>
<script type="text/javascript">
var client = "<?php echo $client; ?>";
	jQuery(document).ready(function ()
	{
		jQuery('#clear-search-button').on('click', function ()
		{
			jQuery('#filter_search').val('');
			jQuery('#adminForm').submit();
		});
	});
	Joomla.submitbutton = function (task)
	{
		if(task == "back")
		{
			window.location = "index.php?option=com_tjvendors&view=vendors&client="+client;
		}
	}

</script>
<?php
// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_tjvendors&view=payouts&vendor_id=' . $this->input->get('vendor_id', '', 'INTEGER').'&client=' . $this->input->get('client', '', 'STRING')); ?>"
method="post" name="adminForm" id="adminForm">
<?php
if(!empty($this->sidebar))
{?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php
}
else
{?>
	<div id="j-main-container">
<?php
}

if(empty($this->items))
	{?>
			<div class="alert alert-no-items">
				<?php echo JText::_('COM_TJVENDOR_NO_MATCHING_RESULTS'); ?>
			</div>
	<?php
	}
	else
	{?>
<div class="alert alert-info">
	<?php
		$com_params = JComponentHelper::getParams('com_tjvendors');
		$bulkPayoutStatus = $com_params->get('bulk_payout');
		if($bulkPayoutStatus!=0)
		{
			echo JText::_('COM_TJVENDOR_PAYOUTS_BULK_PAYOUT_NOTICE');
		}
		else
		{
			echo JText::_('COM_TJVENDOR_PAYOUTS_SINGLE_CLIENT_PAYOUT_NOTICE');
		}
	?>
</div>
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<label for="filter_search" class="element-invisible">
				<?php echo JText::_('JSEARCH_FILTER'); ?>
			</label>
			<input type="text" name="filter_search" id="filter_search"
				placeholder="<?php echo JText::_('COM_TJVENDOR_PAYOUTS_SEARCH_BY_VENDOR_TITLE'); ?>"
				value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
				title="<?php echo JText::_('JSEARCH_FILTER'); ?>"/>
		</div>

		<div class="btn-group pull-left">
			<button class="btn hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
				<i class="icon-search"></i>
			</button>
			<button class="btn hasTooltip" id="clear-search-button" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>">
				<i class="icon-remove"></i>
			</button>
		</div>

		<input id="limit" name="limit" type="hidden" value="0" default="0" />

		<div class="btn-group pull-right hidden-phone">

			<?php
			$com_params = JComponentHelper::getParams('com_tjvendors');
			$bulkPayoutStatus = $com_params->get('bulk_payout');
			if($bulkPayoutStatus!=0)
			{
				echo JText::_('COM_TJVENDOR_PAYOUTS_BULK_PAYOUT_NOTICE');
			}
			else
			{
				echo JHtml::_('select.genericlist', $this->uniqueClients, "vendor_client", 'class="input-medium" size="1" onchange="document.adminForm.submit();"', "client_value", "vendor_client", $this->state->get('filter.vendor_client'));
				echo $filterClient = $this->state->get('filter.vendor_client');
			}
			?>
		</div>

		<div class="btn-group pull-right hidden-phone">
			<?php
				// Making custom filter list
			 echo JHtml::_('select.genericlist', $this->vendor_details, "vendor_id", 'class="input-medium" size="1" onchange="document.adminForm.submit();"', "vendor_id", "vendor_title", $this->state->get('filter.vendor_id'));?>
		</div>


	</div>

		<table class="table table-striped" id="payoutList">
			<thead>
				<tr>
					<?php if (isset($this->items[0]->ordering)): ?>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.`ordering`', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<?php endif; ?>

					<?php if (isset($this->items[0]->state)){} ?>

					<th width="8%">
						<?php echo JHtml::_('grid.sort',  'COM_TJVENDORS_PAYOUTS_PAYOUT_TITLE', 'vendors.`vendor_title`', $listDirn, $listOrder); ?>
					</th>

					<th  width="5%">
						<?php echo JHtml::_('grid.sort',  'COM_TJVENDORS_PAYOUTS_CURRENCY', 'fees.`currency`', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JText::_('COM_TJVENDORS_PAYOUTS_PAID_UPTO');  ?>
					</th>
					<th  width="10%">
						<?php echo JHtml::_('grid.sort',  'COM_TJVENDORS_PAYOUTS_PAYABLE_AMOUNT', 'pass.`total`', $listDirn, $listOrder); ?>
					</th>

					<th  width="10%">
						<?php echo JText::_('COM_TJVENDORS_PAYOUTS_ACTION'); ?>
					</th>
				</tr>
			</thead>

			<tbody>
				<?php
				foreach ($this->items as $i => $item)
				{
				?>
					<tr class="row<?php echo $i % 2; ?>">
						<?php if (isset($this->items[0]->state)){}?>



						<td>
								<?php echo $this->escape($item->vendor_title); ?>
						</td>

						<td>
							<?php echo $item->currency; ?>
						</td>

						<td>
						<?php

						if($bulkPayoutStatus!=0)
						{
							$client=0;
							$paidAmount = TjvendorsHelpersTjvendors::getPaidAmount($item->vendor_id,$item->currency, $client);
							if(empty($paidAmount))
							{
								$paidAmount = '0';
							}
							echo $paidAmount;
						}
						else
						{
							$paidAmount = TjvendorsHelpersTjvendors::getPaidAmount($item->vendor_id,$item->currency, $filterClient);
							if(empty($paidAmount))
							{
								$paidAmount = '0';
							}
							echo $paidAmount;
						}
						?>
						</td>

						<td>
						<?php
						$com_params = JComponentHelper::getParams('com_tjvendors');
						$bulkPayoutStatus = $com_params->get('bulk_payout');
						if($bulkPayoutStatus==0)
						{
							$result = TjvendorsHelpersTjvendors::getPayableAmount($item->vendor_id, $item->client, $item->currency);
							//~ echo $result['payableAmount'];
							echo $result;
						}
						else
						{
							$result = TjvendorsHelpersTjvendors::getPayableAmount($item->vendor_id, $item->client, $item->currency);
							echo $result['payableAmount'];
						}

						?>
						</td>

						<td>
							<a href= "<?php echo JRoute::_('index.php?option=com_tjvendors&view=payout&layout=edit&vendor_id=' .$item->vendor_id.'&id=' .$item->id. '&client=' .$this->input->get('client', '', 'STRING'));?>"
							<button class="validate btn btn-primary">PAY</button>
						</td>
					</tr>
				<?php
				}?>
			</tbody>
		</table>
		<?php
	}?>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
