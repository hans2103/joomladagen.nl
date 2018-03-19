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
?>
<form action="<?php echo JRoute::_('index.php?option=com_csvi&view=processes'); ?>" method="post" id="adminForm" name="adminForm" class="form-horizontal">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php
		// Search tools bar
		echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this, 'options' => array('filterButton' => false)));
		?>
		<?php if (!$this->items) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th>
							<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
						</th>
						<th>
							<?php
								echo JHtml::_('searchtools.sort', 'COM_CSVI_PROCESSES_TEMPLATE_LABEL', 't.template_name', $listDirn, $listOrder);
							?>
						</th>
						<th>
							<?php
								echo JHtml::_('searchtools.sort', 'COM_CSVI_PROCESSES_USERNAME_LABEL', 'u.name', $listDirn, $listOrder);
							?>
						</th>
						<th>
							<?php
								echo JHtml::_('searchtools.sort', 'COM_CSVI_PROCESSES_PROCESSFILE_LABEL', 'a.processfile', $listDirn, $listOrder);
							?>
						</th>
						<th>
							<?php
								echo JHtml::_('searchtools.sort', 'COM_CSVI_PROCESSES_PROCESSFOLDER_LABEL', 'a.processfolder', $listDirn, $listOrder);
							?>
						</th>
						<th>
							<?php
								echo JHtml::_('searchtools.sort', 'COM_CSVI_PROCESSES_POSITION_LABEL', 'a.position', $listDirn, $listOrder);
							?>
						</th>
						<th>
							<?php
							echo JHtml::_('searchtools.sort', 'COM_CSVI_PROCESSES_ID_LABEL', 'a.csvi_process_id', $listDirn, $listOrder);
							?>
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
					<?php foreach ($this->items as $i => $item) : ?>
						<tr>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->csvi_process_id); ?>
							</td>
							<td>
								<?php echo $item->template_name; ?>
							</td>
							<td>
								<?php echo $item->name; ?>
							</td>
							<td>
								<?php echo $item->processfile; ?>
							</td>
							<td>
								<?php echo $item->processfolder; ?>
							</td>
							<td>
								<?php echo $item->position; ?>
							</td>
							<td>
								<?php echo $item->csvi_process_id; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<input type="hidden" name="task" value="browse" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</form>
