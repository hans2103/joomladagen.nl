<?php
/**
 * @package     CSVI
 * @subpackage  Logs
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen');
JHtml::_('formbehavior.chosen', '.inputbox');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

$loggeduser = JFactory::getUser();
?>

<form action="<?php echo JRoute::_('index.php?option=com_csvi&view=logs', false); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
	<div id="availablefieldslist">
		<table class="table table-striped" id="logsList">
			<thead>
			<tr>
				<th width="1%" class="nowrap center">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th class="left">
					<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_ACTION', 'l.action', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_ACTION_TYPE', 'l.action_type', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_TEMPLATE_NAME_TITLE', 'l.template_name', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_START', 'l.start', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_END', 'l.end', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_USER', 'u.name', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_RECORDS', 'l.records', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_RUN_CANCELLED', 'l.run_cancelled', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_FILENAME', 'l.file_name', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap">
					<?php echo JText::_('COM_CSVI_DEBUG_LOG'); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="15">
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
				if (!empty($this->items))
				{
					foreach ($this->items as $i => $item)
					{
						$checkedOut = false;
						$link 	= 'index.php?option=com_csvi&view=logdetails&run_id=' . $item->csvi_log_id;
						?>
						<tr>
							<td align="center">
								<?php echo JHtml::_('grid.id', $i, $item->csvi_log_id, $checkedOut); ?>
							</td>
							<td>
								<a href="<?php echo $link; ?>">
									<?php echo JText::_('COM_CSVI_' . $item->action); ?>
								</a>
							</td>
							<td>
								<?php echo JText::_('COM_CSVI_' . $item->addon . '_' . $item->action_type); ?>
							</td>
							<td>
								<?php echo $item->template_name; ?>
							</td>
							<td>
								<?php echo JHtml::_('date', $item->start, 'Y-m-d H:i:s'); ?>
							</td>
							<td>
								<?php
									if ($item->end != '0000-00-00 00:00:00')
									{
										echo JHtml::_('date', $item->end, 'Y-m-d H:i:s');
									}
									else
									{
										echo JText::_('COM_CSVI_LOG_ENDDATE_UNKNOWN');
									}
								?>
							</td>
							<td>
								<?php echo $item->runuser; ?>
							</td>
							<td>
								<?php echo $item->records; ?>
							</td>
							<td>
								<?php $run_cancelled = ($item->run_cancelled) ? JText::_('COM_CSVI_YES') : JText::_('COM_CSVI_NO');
								echo $run_cancelled;?>
							</td>
							<td>
								<?php echo $item->file_name; ?>
							</td>
							<td>
								<?php
								if ($item->action === 'import' || $item->action === 'export')
								{
									if (file_exists(CSVIPATH_DEBUG . '/com_csvi.log.' . $item->csvi_log_id . '.php'))
									{
										$attribs = 'class="modal" onclick="" rel="{handler: \'iframe\', size: {x: 950, y: 500}}"';
										echo JHtml::_(
											'link',
											JRoute::_('index.php?option=com_csvi&view=logs&layout=logreader&tmpl=component&run_id=' . $item->csvi_log_id),
											JText::_('COM_CSVI_SHOW_LOG'),
											$attribs
										);
										echo ' | ';
										echo JHtml::_(
											'link',
											JRoute::_('index.php?option=com_csvi&view=logs&layout=logreader&tmpl=component&run_id=' . $item->csvi_log_id),
											JText::_('COM_CSVI_OPEN_LOG'),
											'target="_new"'
										);
										echo ' | ';
										echo JHtml::_(
											'link',
											JRoute::_('index.php?option=com_csvi&task=logs.downloadDebug&run_id=' . $item->csvi_log_id),
											JText::_('COM_CSVI_DOWNLOAD_LOG')
										);
									}
									else
									{
										echo JText::_('COM_CSVI_NO_DEBUG_LOG');
									}
								}
								else
								{
									echo JText::_('COM_CSVI_NO_DEBUG_LOG');
								}
								?>
							</td>
						</tr>
					<?php
					}
				}
				else
				{
					echo '<tr><td colspan="11" class="center">' . JText::_('COM_CSVI_NO_LOG_ENTRIES_FOUND') . '</td></tr>';
				}
				?>
			</tbody>
		</table>
	</div>
	</div>
	<input type="hidden" id="task" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<!-- Load the modal skeleton -->
<?php
$layout = new JLayoutFile('csvi.modal');
echo $layout->render(array('cancel-button' => true));
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'logs.deleteAll')
		{
			showMsg(
				'<?php echo JText::_('COM_CSVI_DELETE_ALL'); ?>',
				'<?php echo JText::_('COM_CSVI_LOG_ARE_YOU_SURE_REMOVE_ALL'); ?>',
				'<?php echo JText::_('COM_CSVI_OK'); ?>',
				''
			);

			jQuery('.ok-btn').on('click', function(e) {
				e.preventDefault();
				Joomla.submitform(task);
			});

			jQuery('.cancel-btn').on('click', function(e) {
				e.preventDefault();
				document.adminForm.task.value = '';
				jQuery('#csviModal').modal('hide');
			});
		}
		else
		{
			Joomla.submitform(task);
		}
	}
</script>
