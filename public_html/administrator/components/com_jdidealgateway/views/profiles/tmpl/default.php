<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
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
	$saveOrderingUrl = 'index.php?option=com_jdidealgateway&task=profiles.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'profilesList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="index.php?option=com_jdidealgateway&view=profiles" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php
		// Search tools bar
		// echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" id="profilesList">
				<thead>
				<tr>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th class="left">
						<?php echo JHtml::_('searchtools.sort', 'COM_JDIDEALGATEWAY_PROFILES_NAME', 'a.name', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JDIDEALGATEWAY_PROFILES_PSP', 'a.psp', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('searchtools.sort', 'COM_JDIDEALGATEWAY_PROFILES_ALIAS', 'a.alias', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="15">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php
					$canEdit   = $this->canDo->get('core.edit');
					$canChange = $loggeduser->authorise('core.edit.state',	'com_users');

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
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							<?php endif; ?>
						</td>
						<td>
							<div class="name break-word">
								<?php if ($canEdit) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_jdidealgateway&task=profile.edit&id=' . (int) $item->id); ?>" title="<?php echo JText::sprintf('COM_JDIDEALGATEWAY_EDIT_PROFILE', $this->escape($item->name)); ?>">
										<?php echo $this->escape($item->name); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->name); ?>
								<?php endif; ?>
							</div>
							<?php
								if ($primary)
								{
									?><div class="small">[<?php echo JText::_('COM_JDIDEALGATEWAY_PRIMARY_PROFILE'); ?>]</div><?php
								}
							?>
						</td>
						<td class="break-word">
							<?php echo $this->escape($item->psp); ?>
						</td>
						<td class="break-word">
							<?php echo $this->escape($item->alias); ?>
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
