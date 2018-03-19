<?php
/**
 * @package     CSVI
 * @subpackage  Logdetails
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_csvi&view=logdetails&run_id=' . $this->runId); ?>"
	method="post" id="adminForm" name="adminForm">
	<div class="row-fluid">
		<div class="span2">
			<div>
				<span class="badge badge-info"><?php echo JText::_('COM_CSVI_RECORDS_PROCESSED'); ?></span>
			</div>
			<?php echo $this->logResult->records; ?>
		</div>
		<div class="span3">
			<div>
				<span class="badge badge-info"><?php echo JText::_('COM_CSVI_FILENAME'); ?></span>
			</div>
			<?php echo $this->logResult->file_name; ?>
		</div>
		<div class="span2">
			<div>
				<span class="badge badge-info"><?php echo JText::_('COM_CSVI_DEBUG_LOG'); ?></span>
			</div>
			<?php echo $this->logResult->debug; ?>
		</div>
	</div>
	<?php if ($this->logResult->resultstats) : ?>
		<table class="table table-condensed table-striped">
			<caption>
				<h3>
					<?php echo JText::_('COM_CSVI_LOG_STATISTICS'); ?>
				</h3>
			</caption>
			<thead>
			</thead>
			<tfoot>
			</tfoot>
			<tbody>
			<?php foreach ($this->logResult->resultstats as $result) : ?>
				<tr>
					<td class="span2"><?php echo $result->area; ?></td>
					<td class="span2"><?php echo $result->status; ?></td>
					<td class="span2"><?php echo $result->total; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<?php if ($this->logResult->result) : ?>
		<h3 class="center">
			<?php echo JText::_('COM_CSVI_LOG_DETAILS'); ?>
		</h3>
		<table class="table table-condensed table-striped">
			<thead>
			<tr>
				<th class="title">
					<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_LOG_LINE', 'line', $listDirn, $listOrder); ?>
				</th>
				<th class="title">
					<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_LOG_ACTION', 'status', $listDirn, $listOrder); ?>
				</th>
				<th class="title">
					<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_LOG_RESULT', 'result', $listDirn, $listOrder); ?>
				</th>
				<th class="title">
					<?php echo JText::_('COM_CSVI_LOG_MESSAGE'); ?>
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
				// Search tools bar
				echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));

				if ($this->items)
				{
					foreach ($this->items as $key => $log)
					{
						?>
						<tr>
							<td>
								<?php echo $log->line; ?>
							</td>
							<td>
								<?php echo $log->status; ?>
							</td>
							<td>
								<?php echo JText::_($log->result); ?>
							</td>
							<td>
								<?php echo nl2br($log->description); ?>
							</td>
						</tr>
					<?php
					}
				}
			?>
			</tbody>
		</table>
	<?php endif; ?>
	<input type="hidden" id="task" name="task" value="browse" />
	<input type="hidden" name="run_id" value="<?php echo $this->runId; ?>" />
	<input type="hidden" name="return" value="<?php echo $this->returnUrl; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
