<?php
/**
 * @package         Regular Labs Extension Manager
 * @version         7.1.4
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use RegularLabs\Library\Document as RL_Document;

JHtml::_('jquery.framework');
JHtml::_('bootstrap.framework');

$task = JFactory::getApplication()->input->get('task');

$config = JComponentHelper::getParams('com_regularlabsmanager');

RL_Document::script('regularlabs/script.min.js');
RL_Document::style('regularlabs/style.min.css');

$update_extensionmanager = count($this->items) > 1 && isset($this->items['extensionmanager']);

$ids = $update_extensionmanager ? ['extensionmanager'] : array_keys($this->items);

$script = "
	/* Regular Labs Extension Manager variable */
	var RLEM_IDS =   [ '" . implode("', '", $ids) . "' ];
	var RLEM_TOKEN = '" . JSession::getFormToken() . "';
";
RL_Document::scriptDeclaration($script);

RL_Document::script('regularlabsmanager/process.min.js', '7.1.4');
RL_Document::style('regularlabsmanager/process.min.css', '7.1.4');
?>

<div id="rlem">
	<div class="titles">
		<div class="title pre process">
			<h2>
				<?php echo JText::_('RLEM_TITLE_' . strtoupper($task)); ?>:
				<span class="btn btn-primary" onclick="RegularLabsManagerProcess.process('<?php echo $task; ?>');">
					<?php echo JText::_('RL_START'); ?>
				</span>
			</h2>
			<?php if ($update_extensionmanager): ?>
				<div class="alert alert-warning">
					<?php echo JText::_('RLEM_UPDATE_EXTENSIONMANAGER_FIRST'); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="title failed process hide">
			<h2>
				<?php echo JText::_('RLEM_TITLE_' . strtoupper($task)); ?>:
				<span class="btn btn-primary" onclick="RegularLabsManagerProcess.process('<?php echo $task; ?>', true);">
					<?php echo JText::_('RLEM_TITLE_RETRY'); ?>
				</span>
			</h2>

			<div class="alert alert-danger errors" style="display:none;">
				<h4 class="alert-heading"><?php echo JText::_('ERROR'); ?></h4>

				<div>
					<p class="alert-message"><?php echo JText::_('RLEM_MEET_REQUIREMENTS'); ?></p>
				</div>
			</div>
			<div class="alert alert-warning warnings" style="display:none;">
				<h4 class="alert-heading"><?php echo JText::_('WARNING'); ?></h4>

				<div></div>
			</div>
		</div>

		<div class="title processing hide">
			<h2><?php echo JText::sprintf('RLEM_PROCESS_' . strtoupper($task), '...'); ?></h2>
		</div>

		<div class="title done process hide">
			<div class="alert alert-success">
				<h2><?php echo JText::_('RLEM_TITLE_FINISHED'); ?></h2>
				<?php if ($update_extensionmanager): ?>
					<?php echo JText::_('RLEM_UPDATE_OTHER_EXTENSIONS'); ?>
				<?php endif; ?>
			</div>
			<?php if ($task != 'uninstall') : ?>
				<div class="alert alert-notice"><?php echo JText::_('RLEM_CLEAN_CACHE'); ?></div>
				<div class="alert alert-warning warnings" style="display:none;">
					<h4 class="alert-heading"><?php echo JText::_('WARNING'); ?></h4>

					<div></div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<table class="table processlist">
		<tbody>
			<?php foreach ($this->items as $item) : ?>
				<?php
				$disabled = $update_extensionmanager && $item->id != 'extensionmanager';
				?>
				<tr id="row_<?php echo $item->id; ?>" class="<?php echo $disabled ? 'ghosted' : ''; ?>">
					<td width="1%" nowrap="nowrap" class="ext_name">
						<span class="icon-reglab icon-<?php echo $item->alias; ?>"></span>
						<span id="ext_name_<?php echo $item->id; ?>"><?php echo JText::_($item->name); ?></span>
					</td>
					<td class="statuses">
						<?php if ( ! $disabled): ?>
							<input type="hidden" id="url_<?php echo $item->id; ?>" value="<?php echo $item->url; ?>">

							<div class="queue_<?php echo $item->id; ?> status process queued">
								<span class="label"><?php echo JText::_('RLEM_QUEUED'); ?></span>
							</div>
							<div class="processing_<?php echo $item->id; ?> status processing hide">
								<div class="progress progress-striped active">
									<div class="bar" style="width: 100%;"></div>
								</div>
							</div>
							<div class="success_<?php echo $item->id; ?> status success process hide">
								<span class="label label-success"><?php echo JText::_(($task == 'uninstall') ? 'RLEM_UNINSTALLED' : 'RLEM_INSTALLED'); ?></span>
							</div>
							<div class="failed_<?php echo $item->id; ?> status failed process hide">
								<span class="label label-important"><?php echo JText::_('RLEM_INSTALLATION_FAILED'); ?></span>
							</div>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
