<?php
/**
 * @package     CSVI
 * @subpackage  Tasks
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

$loggeduser = JFactory::getUser();

?>
<form action="<?php echo JRoute::_('index.php?option=com_csvi&view=tasks'); ?>" method="post" id="adminForm" name="adminForm" class="form-horizontal">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="adminlist table table-striped" id="itemsList">
				<thead>
					<tr>
						<th width="20">
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_TASK_NAME_LABEL', 'a.task_name', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_CSVI_TASKS_TASK_DESC'); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_TASK_COMPONENT_LABEL', 'a.component', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_TASK_ACTION_LABEL', 'a.action', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_TASKS_FIELD_ENABLED', 'a.enabled', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="20">
							<div class="pull-left">
								<?php
								if ($this->pagination->total > 0)
								{
									echo $this->pagination->getListFooter();
								}
								?>
							</div>
							<div class="pull-right"><?php echo $this->pagination->getResultsCounter(); ?></div>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php
					$canEdit   = $this->canDo->get('core.edit');
					$canChange = $loggeduser->authorise('core.edit.state', 'com_csvi');

					foreach ($this->items as $i => $item) :
						$taskName = $this->escape(JText::_('COM_CSVI_' . $item->component . '_' . $item->task_name));
					?>
						<tr>
							<td class="center">
								<?php if ($canEdit || $canChange) : ?>
									<?php echo JHtml::_('grid.id', $i, $item->csvi_task_id); ?>
								<?php endif; ?>
							</td>
							<td>
								<?php if ($canEdit)
								{
									echo JHtml::_(
										'link',
										JRoute::_('index.php?option=com_csvi&task=task.edit&csvi_task_id=' . (int) $item->csvi_task_id),
										$taskName,
										'title="' . JText::sprintf('COM_CSVI_EDIT_TASK', $this->escape($item->task_name)) . '"'
									);
								}
								else
								{
									echo $taskName;
								}

								if (!empty($item->url))
								{
									echo ' [ ' . JHtml::_('link', JRoute::_($item->url), JText::_($item->component), 'target="_blank"') . ' ]';
								}
								?>
							</td>
							<td>
								<?php echo JText::_('COM_CSVI_' . $item->component . '_' . $item->task_name . '_DESC'); ?>
							</td>
							<td>
								<?php echo JHtml::_(
									'link',
									JRoute::_('index.php?option=' . $item->component),
									JText::_('COM_CSVI_' . $item->component),
									'target="_blank"'
								);
								?>
							</td>
							<td>
								<?php echo JText::_('COM_CSVI_' . strtoupper($item->action)); ?>
							</td>
							<td class="center">
								<?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'tasks.'); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<?php
	$layout = new JLayoutFile('csvi.modal');
	echo $layout->render(
		array(
			'modal-header' => JText::_('COM_CSVI_CONFIRM_RESET_TEMPLATETYPES_TITLE'),
			'modal-body' => JText::_('COM_CSVI_CONFIRM_RESET_TEMPLATETYPES_TEXT'),
			'cancel-button' => true,
			'ok-button' => JText::_('COM_CSVI_RESET_TEMPLATETYPES'),
			'ok-btn-dismiss' => true
		)
	);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'tasks.reload') {
			jQuery('#csviModal').modal('show');

			jQuery('.ok-btn').on('click', function(e) {
				e.preventDefault();
				jQuery('#csviModal').modal('hide');
				Joomla.submitform(task);
			});
		}
		else Joomla.submitform(task);
	}
</script>
