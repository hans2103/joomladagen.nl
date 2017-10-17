<?php
/**
 * @version    SVN: <svn_id>
 * @package    Tjfields
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load lang file for countries
$lang = JFactory::getLanguage();
$lang->load('tjgeo.countries', JPATH_SITE, null, false, true);

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canOrder = $user->authorise('core.edit.state', 'com_tjfields');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_tjfields&task=countries.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'countryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
?>

<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;

		if (order !== '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}

		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<?php
if (! empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>

<div class="<?php echo TJFIELDS_WRAPPER_CLASS;?> tj-countries">
	<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">

	<?php else : ?>
		<div id="j-main-container">
		<?php endif; ?>
	<form
		action="<?php echo JRoute::_('index.php?option=com_tjfields&view=countries&client=' . $this->input->get('client', '', 'STRING')); ?>"
		method="post" name="adminForm" id="adminForm">
		<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_TJFIELDS_FILTER_SEARCH_DESC_COUNTRIES'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip"
					title="<?php echo JText::_('COM_TJFIELDS_FILTER_SEARCH_DESC_COUNTRIES'); ?>" />
				</div>

				<div class="btn-group pull-left">
					<button type="submit" class="btn hasTooltip"
					title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
						<i class="icon-search"></i>
					</button>
					<button type="button" class="btn hasTooltip"
					title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
					onclick="document.id('filter_search').value='';this.form.submit();">
						<i class="icon-remove"></i>
					</button>
				</div>

				<?php if (JVERSION >= '3.0') : ?>
					<div class="btn-group pull-right hidden-phone">
						<label for="limit" class="element-invisible">
							<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
						</label>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>

					<div class="btn-group pull-right hidden-phone hidden-tablet">
						<label for="directionTable" class="element-invisible">
							<?php echo JText::_('JFIELD_ORDERING_DESC'); ?>
						</label>
						<select name="directionTable" id="directionTable"
							class="input-medium" onchange="Joomla.orderTable()">
							<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
							<option value="asc"
								<?php
									if ($listDirn == 'asc')
									{
										echo 'selected="selected"';
									}
								?>>
									<?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?>
							</option>
							<option value="desc"
								<?php
								if ($listDirn == 'desc')
								{
									echo 'selected="selected"';
								}
								?>>
									<?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?>
							</option>
						</select>
					</div>

					<div class="btn-group pull-right hidden-phone hidden-tablet">
						<label for="sortTable" class="element-invisible">
							<?php echo JText::_('JGLOBAL_SORT_BY'); ?>
						</label>
						<select name="sortTable" id="sortTable" class="input-medium"
							onchange="Joomla.orderTable()">
							<option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
							<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
						</select>
					</div>
				<?php endif; ?>

				<div class="btn-group pull-right hidden-phone">
					<?php
					echo JHtml::_('select.genericlist', $this->publish_states, "filter_published", 'class="input-medium" size="1" onchange="document.adminForm.submit();" name="filter_published"', "value", "text", $this->state->get('filter.state'));
					?>
				</div>
			</div>

			<div class="clearfix"> </div>

			<?php if (empty($this->items)) : ?>
				<div class="clearfix">&nbsp;</div>
				<div class="alert alert-no-items">
					<?php echo JText::_('COM_TJFIELDS_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php
			else : ?>
				<table class="table table-striped" id="countryList">
					<thead>
						<tr>
							<?php if (JVERSION >= '3.0'): ?>
								<?php if (isset($this->items[0]->ordering)): ?>
									<th width="1%" class="nowrap center hidden-phone">
										<?php
										echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>',
											'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING');
										?>
									</th>
								<?php endif; ?>
							<?php endif; ?>

							<th width="1%" class="hidden-phone">
								<input type="checkbox" name="checkall-toggle" value=""
								title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
								onclick="Joomla.checkAll(this)" />
							</th>

							<?php if (isset($this->items[0]->state)): ?>
								<th width="1%" class="nowrap center">
									<?php echo JHtml::_('grid.sort', 'JSTATUS', 'state', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>

							<th class='left'>
								<?php echo JHtml::_('grid.sort', 'COM_TJFIELDS_COUNTRIES_COUNTRY', 'a.country', $listDirn, $listOrder); ?>
							</th>

							<th class="center hidden-phone">
								<?php echo JHtml::_('grid.sort', 'COM_TJFIELDS_COUNTRIES_COUNTRY_3_CODE', 'a.country_3_code', $listDirn, $listOrder); ?>
							</th>

							<th class="center hidden-phone">
								<?php echo JHtml::_('grid.sort', 'COM_TJFIELDS_COUNTRIES_COUNTRY_CODE', 'a.country_code', $listDirn, $listOrder); ?>
							</th>

							<th class='left hidden-phone'>
								<?php echo JHtml::_('grid.sort', 'COM_TJFIELDS_COUNTRIES_COUNTRY_JTEXT', 'a.country_jtext', $listDirn, $listOrder); ?>
							</th>

							<?php if (isset($this->items[0]->id)): ?>
								<th width="1%" class="nowrap center hidden-phone">
									<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>
						</tr>
					</thead>

					<tfoot>
						<?php
						if (isset($this->items[0]))
						{
							$colspan = count(get_object_vars($this->items[0]));
						}
						else
						{
							$colspan = 10;
						}
						?>
						<tr>
							<td colspan="<?php echo $colspan ?>">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>

					<tbody>
						<?php
						foreach ($this->items as $i => $item):
							$ordering = ($listOrder == 'a.ordering');
							$canCreate = $user->authorise('core.create', 'com_tjfields');
							$canEdit = $user->authorise('core.edit', 'com_tjfields');
							$canCheckin = $user->authorise('core.manage', 'com_tjfields');
							$canChange = $user->authorise('core.edit.state', 'com_tjfields');
						?>

						<tr class="row<?php echo $i % 2; ?>">
							<?php if (JVERSION >= '3.0'): ?>
								<?php if (isset($this->items[0]->ordering)): ?>
									<td class="order nowrap center hidden-phone">
										<?php
										if ($canChange):
											$disableClassName = '';
											$disabledLabel = '';

											if (! $saveOrder):
												$disabledLabel = JText::_('JORDERINGDISABLED');
												$disableClassName = 'inactive tip-top';
											endif;
										?>

											<span class="sortable-handler hasTooltip <?php echo $disableClassName; ?>" title="<?php echo $disabledLabel ?>">
													<i class="icon-menu"></i>
											</span>

											<input type="text" style="display: none" name="order[]"
												size="5" value="<?php echo $item->ordering; ?>"
												class="width-20 text-area-order " />

										<?php else : ?>
												<span class="sortable-handler inactive">
													<i class="icon-menu"></i>
												</span>
										<?php endif; ?>
									</td>
								<?php endif; ?>
							<?php endif; ?>

							<td class="center hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>

							<?php if (isset($this->items[0]->state)): ?>
								<td class="center">
									<?php echo JHtml::_('jgrid.published', $item->state, $i, 'countries.', $canChange, 'cb'); ?>
								</td>
							<?php endif; ?>

							<td>
								<?php if (isset($item->checked_out) && $item->checked_out) : ?>
									<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'countries.', $canCheckin); ?>
								<?php endif; ?>

								<?php if ($canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_tjfields&task=country.edit&id=' . (int) $item->id . '&client=' . $this->input->get('client', '', 'STRING')); ?>">
										<?php echo $this->escape($item->country); ?>
									</a>
									<?php else : ?>
										<?php echo $this->escape($item->country); ?>
								<?php endif; ?>
							</td>

							<td class="center hidden-phone">
								<?php echo $item->country_3_code; ?>
							</td>

							<td class="center hidden-phone">
								<?php echo $item->country_code; ?>
							</td>

							<td class="left hidden-phone">
								<?php
								if ($lang->hasKey(strtoupper($item->country_jtext)))
								{
									echo JText::_($item->country_jtext);
								}
								else if ($item->country_jtext !== '')
								{
									echo "<span class='text text-warning'>" . JText::_('COM_TJFIELDS_MISSING_LANG_CONSTANT') . "</span>";
								}
								?>
							</td>

							<?php if (isset($this->items[0]->id)): ?>
								<td class="center hidden-phone">
									<?php echo (int) $item->id; ?>
								</td>
							<?php endif; ?>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
