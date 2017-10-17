<?php
/**
 * @version    SVN: <svn_id>
 * @package    ActivityStream
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access to this file
defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');
$sortFields = $this->getSortFields();
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
?>
<form action="index.php?option=com_activitystream&view=activities" method="post" id="adminForm" name="adminForm">
	<div class="row-fluid">
		<div class="span6">
			<?php
				echo JLayoutHelper::render(
					'joomla.searchtools.default',
					array('view' => $this)
				);
			?>
		</div>
	</div>
	<div id="filter-bar" class="btn-toolbar">
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<div class="btn-group pull-right hidden-phone">
			<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
			<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
				<option value="asc" <?php if ($listDirn == 'asc')
				{
					echo 'selected="selected"';
				} ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
				<option value="desc" <?php if ($listDirn == 'desc')
				{
					echo 'selected="selected"';
				} ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?></option>
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
	<table class="table table-striped table-hover">
		<thead>
		<?php if (!empty($this->items) && empty($this->items['error'])) : ?>
			<tr>
				<th width="1%" class="hidden-phone">
					<input type="checkbox" name="checkall-toggle" value=""
					title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
				</th>
				<th width="2%">
					<?php echo JHtml::_('grid.sort', JText::_("COM_ACTIVITYSTREAM_ACTIVITY_ID"), 'id', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', JText::_("COM_ACTIVITYSTREAM_ACTIVITY_STATE"), 'state', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', JText::_("COM_ACTIVITYSTREAM_ACTIVITY_TYPE"), 'type', $listDirn, $listOrder);?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', JText::_('COM_ACTIVITYSTREAM_ACTIVITY_CREATED_DATE'), 'created_date', $listDirn, $listOrder);?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', JText::_('COM_ACTIVITYSTREAM_ACTIVITY_UPDATED_DATE'), 'updated_date', $listDirn, $listOrder);?>
				</th>
			</tr>
		<?php endif;?>
		</thead>
		<tfoot>
			<tr>
				<td colspan="6">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</th>
			</tr>
		</tfoot>
		<tbody>
			<?php if (!empty($this->items) && empty($this->items['error'])) : ?>
				<?php foreach ($this->items as $i => $row) :
					$link = JRoute::_('index.php?option=com_activitystream&task=activity.edit&id=' . $row->id);
				?>
					<tr>
						<td>
							<?php echo JHtml::_('grid.id', $i, $row->id); ?>
						</td>
						<td align="center">
							<a href="<?php echo $link; ?>" title="<?php echo JText::_('type'); ?>">
								<?php echo $row->id; ?>
							</a>
						</td>
						<td align="center">
							<?php echo JHtml::_('jgrid.published', $row->state, $i, 'activities.', true, 'cb'); ?>
						</td>
						<td>
							<?php echo $row->type; ?>
						</td>
						<td align="center">
							<?php echo $row->created_date; ?>
						</td>
						<td align="center">
							<?php echo $row->updated_date; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<div>&nbsp;</div>
				<div class="alert alert-error">
					<div>
						<?php
						if (!empty($this->items['message']))
						{
							echo $this->items['message'];
						}
						?>
					</div>
				</div>
			<?php endif;?>
		</tbody>
	</table>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

