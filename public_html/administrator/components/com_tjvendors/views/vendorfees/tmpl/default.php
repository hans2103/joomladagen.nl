<?php
/**
 * @version    SVN: 
 * @package    Com_Tjvendors
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::root() . 'administrator/components/com_tjvendors/assets/css/tjvendors.css');


$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_tjvendors');
$saveOrder = $listOrder == 'a.`ordering`';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_tjvendors&task=vendorfees.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'vendorList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();

?>
<script type="text/javascript">
	Joomla.orderTable = function ()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}

		Joomla.tableOrdering(order, dirn, '');
	};

	jQuery(document).ready(function ()
	{
		jQuery('#clear-search-button').on('click', function ()
		{
			jQuery('#filter_search').val('');
			jQuery('#adminForm').submit();
		});
	});

	window.toggleField = function (id, task, field)
	{
		var f = document.adminForm,
			i = 0, cbx,
			cb = f[ id ];

		if (!cb) return false;

		while (true)
		{
			cbx = f[ 'cb' + i ];

			if (!cbx) break;

			cbx.checked = false;
			i++;
		}

		var inputField   = document.createElement('input');
		inputField.type  = 'hidden';
		inputField.name  = 'field';
		inputField.value = field;
		f.appendChild(inputField);

		cb.checked = true;
		f.boxchecked.value = 1;
		window.submitform(task);

		return false;
	};

	Joomla.submitbutton = function (task)
	{
			Joomla.submitform(task, document.getElementById('adminForm'));
	}

</script>

<?php 

// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

?>
<form
action="
<?php
echo JRoute::_('index.php?option=com_tjvendors&view=vendorfees&vendor_id=' . $this->vendor_id.'&client=' . $this->input->get('client', '', 'STRING')); ?>" 
method="post" name="adminForm" id="adminForm">
<?php
if (!empty($this->sidebar))
{
	?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php
}
else
{
	?>
	<div id="j-main-container">
<?php 
}?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible">
					<?php echo JText::_('JSEARCH_FILTER'); ?>
				</label>
				<input type="text" name="filter_search" id="filter_search"
					
					placeholder="<?php echo JText::_('COM_TJVENDOR_SEARCH_BY_VENDOR_FEE'); ?>"
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
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible">
					<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
				</label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible">
					<?php echo JText::_('JFIELD_ORDERING_DESC'); ?>
				</label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
					<option value="asc" <?php echo $listDirn == 'asc' ? 'selected="selected"' : ''; ?>>
						<?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?>
					</option>
					<option value="desc" <?php echo $listDirn == 'desc' ? 'selected="selected"' : ''; ?>>
						<?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?>
					</option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
		</div>
		<div class="clearfix"></div>
		<?php
		if (empty($this->items))
		{
			?>
			<div class="clearfix">&nbsp;</div>
				<div class="alert alert-no-items">
					<?php echo JText::_('COM_TJVENDOR_NO_MATCHING_RESULTS'); ?>
				</div>
		<?php
		}
		else
		{
			?>

		<table class="table table-striped" id="vendorList">
			<thead>
				<tr>
					<?php 
					if (isset($this->items[0]->ordering))
					{
					?>
					<th width="1%" class="nowrap center hidden-phone">
					<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.`ordering`', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<?php
					}?>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<th class='left'>
						<?php echo JText::_('COM_TJVENDORS_VENDORS_ID'); ?>
					</th>
					<th class='left'>
						<?php echo JText::_('COM_TJVENDORS_FORM_LBL_VENDOR_VENDOR_FEE_TITLE'); ?>
					</th>

					<th class='left'>
						<?php echo JText::_('COM_TJVENDORS_FORM_LBL_VENDOR_CURRENCY'); ?>
					</th>
					<th class='left'>
						<?php echo JHtml::_('grid.sort',  'COM_TJVENDORS_VENDORS_PERCENT_COMMISSION', 'b.`percent_commission`', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo JHtml::_('grid.sort',  'COM_TJVENDORS_VENDORS_FLAT_COMMISSION', 'b.`flat_commission`', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
				foreach ($this->items as $i => $item)
				{
					$ordering   = ($listOrder == 'a.ordering');
					$canCreate  = $user->authorise('core.create', 'com_tjvendors');
					$canEdit    = $user->authorise('core.edit', 'com_tjvendors');
					$canCheckin = $user->authorise('core.manage', 'com_tjvendors');
					$canChange  = $user->authorise('core.edit.state', 'com_tjvendors');
					?>
					<tr class="row<?php echo $i % 2; ?>">
					<?php
						if (isset($this->items[0]->ordering))
						{
							?>
							<td class="order nowrap center hidden-phone">
								<?php
								if ($canChange)
								{
									$disableClassName = '';
									$disabledLabel    = '';

									if (!$saveOrder)
									{
										$disabledLabel    = JText::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									}
								?>
									<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>">
										<i class="icon-menu"></i>
									</span>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php
								}
								else
								{
									?>
									<span class="sortable-handler inactive">
										<i class="icon-menu"></i>
									</span>
								<?php
								}?>
							</td>
						<?php
						}?>

							<td class="hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<td>
								<?php echo $item->vendor_id; ?>
							</td>
							<td>
									<?php echo $item->vendor_title; ?>
							</td>
							<td>
								<a href="
								<?php 
								echo JRoute::_('index.php?option=com_tjvendors&task=vendorfee.edit&vendor_id=' . (int) $item->vendor_id . '&client=' . $item->client . '&fee_id=' . $item->id);?>">
								<?php echo $item->currency; ?>
								</a>
							</td>
							<td>
								<?php echo $item->percent_commission . " %";?>
							</td>
							<td>
								<?php echo $item->flat_commission; ?>
							</td>
						</tr>
				<?php
				}
				?>
			</tbody>
		</table>
		<?php
		}?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="vendor_id" value="<?php echo $this->vendor_id;?>"/>
			<input type="hidden" name="client" value="<?php echo $this->input->get('client', '', 'STRING');?>"/>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
