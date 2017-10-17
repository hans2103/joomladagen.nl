<?php
/**
 * @version     1.0.0
 * @package     com_jlike
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <contact@techjoomla.com> - http://techjoomla.com
 */
// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canCreate = $user->authorise('core.create', 'com_jlike');
$canEdit = $user->authorise('core.edit', 'com_jlike');
$canCheckin = $user->authorise('core.manage', 'com_jlike');
$canChange = $user->authorise('core.edit.state', 'com_jlike');
$canDelete = $user->authorise('core.delete', 'com_jlike');
?>

<form action="<?php echo JRoute::_('index.php?option=com_jlike&view=recommendations'); ?>" method="post" name="adminForm" id="adminForm">

	<table class="table table-striped" id = "todosList" >
		<thead >
			<tr>
				<?php if (isset($this->items[0]->state)): ?>
				<th width="1%" class="nowrap center">
					<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
				</th>
				<?php endif; ?>

				<th class='left'>
				<?php echo JHtml::_('grid.sort',  'COM_JLIKE_RECOMMENDATIONS_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>

				<th class='left'>
				<?php echo JHtml::_('grid.sort',  'COM_JLIKE_RECOMMENDATIONS_ASSIGNED_BY', 'a.assigned_by', $listDirn, $listOrder); ?>
				</th>
				<th class='left'>
				<?php echo JHtml::_('grid.sort',  'COM_JLIKE_RECOMMENDATIONS_ASSIGNED_TO', 'a.assigned_to', $listDirn, $listOrder); ?>
				</th>
				<th class='left'>
				<?php echo JHtml::_('grid.sort',  'COM_JLIKE_RECOMMENDATIONS_CREATED', 'a.created', $listDirn, $listOrder); ?>
				</th>
<!--
				<th class='left'>
				<?php echo JHtml::_('grid.sort',  'COM_JLIKE_RECOMMENDATIONS_STATUS', 'a.status', $listDirn, $listOrder); ?>
				</th>
-->

				<?php if (isset($this->items[0]->id)): ?>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				<?php endif; ?>

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
	<?php foreach ($this->items as $i => $item) : ?>
		<?php $canEdit = $user->authorise('core.edit', 'com_jlike'); ?>

		<tr class="row<?php echo $i % 2; ?>">

			<?php if (isset($this->items[0]->state)): ?>
				<?php $class = ($canEdit || $canChange) ? 'active' : 'disabled'; ?>
				<td class="center">
					<a class="btn btn-micro <?php echo $class; ?>"
					   href="<?php echo ($canEdit || $canChange) ? JRoute::_('index.php?option=com_jlike&task=todos.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
						<?php if ($item->state == 1): ?>
							<i class="icon-publish"></i>
						<?php else: ?>
							<i class="icon-unpublish"></i>
						<?php endif; ?>
					</a>
				</td>
			<?php endif; ?>

				<td>
					<a href="<?php echo $item->content_url; ?>" target="_blank"> <?php echo $item->content_title; ?></a>
				</td>

				<td>
					<?php echo JFactory::getUser($item->assigned_by)->name; ?>
				</td>
				<td>

					<?php echo JFactory::getUser($item->assigned_to)->name; ?>
				</td>
				<td>

					<?php echo $item->created; ?>
				</td>
<!--
				<td>
				<?php if (isset($item->checked_out) && $item->checked_out) : ?>
					<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'recommendations.', $canCheckin); ?>
				<?php endif; ?>
				<a href="<?php echo JRoute::_('index.php?option=com_jlike&view=todosform&id='.(int) $item->id); ?>">
				<?php echo $this->escape($item->status); ?></a>
				</td>
-->

			<?php if (isset($this->items[0]->id)): ?>
				<td class="center hidden-phone">
					<?php echo (int)$item->id; ?>
				</td>
			<?php endif; ?>

		</tr>
	<?php endforeach; ?>
	</tbody>
	</table>

<!--
	<?php if ($canCreate): ?>
		<a href="<?php echo JRoute::_('index.php?option=com_jlike&task=todosform.edit&id=0', false, 2); ?>"
		   class="btn btn-success btn-small"><i
				class="icon-plus"></i> <?php echo JText::_('COM_JLIKE_ADD_ITEM'); ?></a>
	<?php endif; ?>
-->

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">

	jQuery(document).ready(function () {
		jQuery('.delete-button').click(deleteItem);
	});

	function deleteItem() {
		var item_id = jQuery(this).attr('data-item-id');
		if (confirm("<?php echo JText::_('COM_JLIKE_DELETE_MESSAGE'); ?>")) {
			window.location.href = '<?php echo JRoute::_('index.php?option=com_jlike&task=todosform.remove&id=', false, 2) ?>' + item_id;
		}
	}
</script>


