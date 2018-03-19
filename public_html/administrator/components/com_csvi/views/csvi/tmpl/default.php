<?php
/**
 * @package     CSVI
 * @subpackage  Dashboard
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;
JHtml::_('behavior.modal');
$returnUrl = base64_encode(JUri::getInstance()->toString());
?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<table class="table table-condensed table-striped">
	<thead>
		<tr>
			<th>
				<?php echo JText::_('COM_CSVI_ACTION'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_CSVI_ACTION_TYPE'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_CSVI_TEMPLATE_NAME_TITLE'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_CSVI_START'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_CSVI_END'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_CSVI_USER'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_CSVI_RECORDS'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_CSVI_RUN_CANCELLED'); ?>
			</th>
			<th>
				<?php echo JText::_('COM_CSVI_DEBUG_LOG'); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="11"></td>
		</tr>
	</tfoot>
	<tbody>
		<?php
		if (0 !== count($this->items))
		{
			foreach ($this->items as $i => $item)
			{
				$link 	= 'index.php?option=com_csvi&view=logdetails&run_id=' . $item->csvi_log_id . '&return=' . $returnUrl;
				?>
				<tr>
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
						<?php echo JHtml::_('date', $item->start, 'd-m-Y H:i:s'); ?>
					</td>
					<td>
						<?php
						if ($item->end !== JFactory::getDbo()->getNullDate())
						{
							echo JHtml::_('date', $item->end, 'd-m-Y H:i:s');
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
						<?php echo $item->run_cancelled ? JText::_('COM_CSVI_YES') : JText::_('COM_CSVI_NO'); ?>
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
									JRoute::_('index.php?option=com_csvi&task=logs.downloaddebug&run_id=' . $item->csvi_log_id),
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
