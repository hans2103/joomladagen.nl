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

// Load lang file for regions
$lang = JFactory::getLanguage();
$lang->load('tjgeo.regions', JPATH_SITE, null, false, true);

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canOrder = $user->authorise('core.edit.state', 'com_tjfields');
$saveOrder = $listOrder == 'a.ordering';

// Allow adding non select list filters
if (! empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}
?>

<form
		action="<?php echo JRoute::_('index.php?option=com_tjfields&view=regions&client=' . $this->input->get('client', '', 'STRING')); ?>"
		method="post" name="adminForm" id="adminForm">
	<div class="<?php echo TJFIELDS_WRAPPER_CLASS;?> tj-regions">
		<?php
		// JHtmlsidebar for menu.
		if (JVERSION >= '3.0'):
			if (!empty( $this->sidebar)) : ?>
				<div id="j-sidebar-container" class="span2">
					<?php echo $this->sidebar; ?>
				</div>
				<div id="j-main-container" class="span10">
					<?php
						// Search tools bar
						echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
					?>
			<?php else : ?>
				<div id="j-main-container">
					<?php
						// Search tools bar
						echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
					?>
			<?php endif;
		endif;
		?>

		<?php if (JVERSION < '3.0'): ?>
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search"
					placeholder="<?php echo JText::_('COM_TJFIELDS_FILTER_SEARCH_DESC_REGIONS'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					class="hasTooltip"
					title="<?php echo JText::_('COM_TJFIELDS_FILTER_SEARCH_DESC_REGIONS'); ?>" />
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

				<div class="filter-select fltrt hidden-phone pull right">
					<?php if (JVERSION < '3.0') :
						echo JHtml::_('select.genericlist', $this->countries, "filter_country", 'class="" size="1" onchange="document.adminForm.submit();" name="filter_country"', "value", "text", $this->state->get('filter.country'));

						echo JHtml::_('select.genericlist', $this->sstatus, "filter_state", 'class="" size="1" onchange="document.adminForm.submit();" name="filter_state"', "value", "text", $this->state->get('filter.state'));
						?>
					<?php endif; ?>
				</div>
			</div>

			<div class="clearfix"></div>

		<?php endif; ?>

		<?php if (empty($this->items)) : ?>
			<div class="clearfix">&nbsp;</div>
			<div class="alert alert-no-items">
				<?php echo JText::_('COM_TJFIELDS_NO_MATCHING_RESULTS'); ?>
			</div>
			<?php
			else : ?>
				<table class="table table-striped" id="regionList">
					<thead>
						<tr>
							<th width="1%" class="hidden-phone"><input
								type="checkbox" name="checkall-toggle" value=""
								title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
								onclick="Joomla.checkAll(this)" />
							</th>

							<?php if (isset($this->items[0]->state)): ?>
								<th width="1%" class="nowrap center">
									<?php echo JHtml::_('grid.sort', 'JSTATUS', 'state', $listDirn, $listOrder); ?>
								</th>
							<?php endif; ?>

							<th class='left'>
								<?php echo JHtml::_('grid.sort', 'COM_TJFIELDS_REGIONS_REGION', 'a.region', $listDirn, $listOrder); ?>
							</th>

							<th class="left">
								<?php echo JHtml::_('grid.sort', 'COM_TJFIELDS_REGIONS_COUNTRY', 'c.country', $listDirn, $listOrder); ?>
							</th>

							<th class="center hidden-phone">
								<?php echo JHtml::_('grid.sort', 'COM_TJFIELDS_REGIONS_REGION_3_CODE', 'a.region_3_code', $listDirn, $listOrder); ?>
							</th>
							<th class="center hidden-phone">
								<?php echo JHtml::_('grid.sort', 'COM_TJFIELDS_REGIONS_REGION_CODE', 'a.region_code', $listDirn, $listOrder); ?>
							</th>
							<th class='left hidden-phone'>
								<?php echo JHtml::_('grid.sort', 'COM_TJFIELDS_REGIONS_REGION_JTEXT', 'a.region_jtext', $listDirn, $listOrder); ?>
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
								<td class="center hidden-phone">
									<?php echo JHtml::_('grid.id', $i, $item->id); ?>
								</td>

								<?php if (isset($this->items[0]->state)): ?>
									<td class="center">
										<?php echo JHtml::_('jgrid.published', $item->state, $i, 'regions.', $canChange, 'cb'); ?>
									</td>
								<?php endif; ?>

								<td>
									<?php if (isset($item->checked_out) && $item->checked_out) : ?>
										<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'regions.', $canCheckin); ?>
									<?php endif; ?>
									<?php if ($canEdit) : ?>
										<a href="<?php echo JRoute::_('index.php?option=com_tjfields&task=region.edit&id=' . (int) $item->id . '&client=' . $this->input->get('client', '', 'STRING')); ?>">
											<?php echo $this->escape($item->region); ?>
										</a>
										<?php else : ?>
											<?php echo $this->escape($item->region); ?>
									<?php endif; ?>
								</td>

								<td class="left">
									<?php echo $this->escape($item->country); ?>
								</td>

								<td class="center hidden-phone">
									<?php echo $item->region_3_code; ?>
								</td>

								<td class="center hidden-phone">
									<?php echo $item->region_code; ?>
								</td>

								<td class="left hidden-phone">
									<?php
									if ($lang->hasKey(strtoupper($item->region_jtext)))
									{
										echo JText::_($item->region_jtext);
									}
									elseif ($item->region_jtext !== '')
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
	</div>
</form>
