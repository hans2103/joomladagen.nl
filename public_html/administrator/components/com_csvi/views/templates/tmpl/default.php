<?php
/**
 * @package     CSVI
 * @subpackage  Templates
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

$loggeduser = JFactory::getUser();
$saveOrder = $listOrder === 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_csvi&task=templates.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'templatesList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_csvi&view=templates'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
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
			<table class="table table-striped" id="templatesList">
				<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th class="left">
						<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_TEMPLATES_FIELD_NAME', 'a.template_name', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_TEMPLATES_FIELD_ACTION', 'a.action', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_TEMPLATES_FIELD_ENABLED', 'a.enabled', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_TEMPLATES_FIELD_FRONTEND', 'a.frontend', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_TEMPLATES_FIELD_LASTRUN', 'a.lastrun', $listDirn, $listOrder); ?>
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
				$canEdit   = $this->canDo->get('core.edit');
				$canChange = $loggeduser->authorise('core.edit.state', 'com_csvi');

				foreach ($this->items as $i => $item) :

					$primary = false;

					if ($i === 0)
					{
						$primary = true;
					}
					?>
					<tr>
						<td class="order nowrap center hidden-phone">
							<?php
							$iconClass = '';

							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
							elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
							}
							?>
							<span class="sortable-handler <?php echo $iconClass ?>">
									<span class="icon-menu"></span>
								</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display:none" name="order[]" size="5"
								       value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
							<?php endif; ?>
						</td>
						<td class="center">
							<?php if ($canEdit || $canChange) : ?>
								<?php echo JHtml::_('grid.id', $i, $item->csvi_template_id); ?>
							<?php endif; ?>
						</td>
						<td>
							<div class="name break-word">
								<?php if ($canEdit)
								{
									echo JHtml::_(
										'link',
										JRoute::_('index.php?option=com_csvi&task=template.edit&csvi_template_id=' . (int) $item->csvi_template_id),
										$this->escape($item->template_name),
										'title="' . JText::sprintf('COM_CSVI_EDIT_TEMPLATE', $this->escape($item->template_name)) . '"'
									);
								}
								else
								{
									echo $this->escape($item->template_name);
								}
								?>
							</div>
						</td>
						<td class="break-word">
							<?php echo $this->escape($item->action); ?>
						</td>
						<td class="small">
							<?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'templates.', $canChange); ?>
						</td>
						<td class="small">
							<?php echo JHtml::_('jgrid.published', $item->frontend, $i, 'templates.', false); ?>
						</td>
						<td class="break-word">
							<?php
								$displayDate = JText::_('COM_CSVI_TEMPLATE_NEVER_RUN');

								if ($item->lastrun !== $this->db->getNullDate())
								{
									$displayDate = JHtml::_('date', $item->lastrun, 'd-m-Y H:i:s');
								}

								echo $displayDate;
							?>
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
