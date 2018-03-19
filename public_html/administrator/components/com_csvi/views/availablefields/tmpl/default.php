<?php
/**
 * @package     CSVI
 * @subpackage  AvailableFields
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
<form action="<?php echo JRoute::_('index.php?option=com_csvi&view=availablefields'); ?>" method="post" id="adminForm" name="adminForm" class="form-horizontal">
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
			<div id="availablefieldslist" style="text-align: left;">
				<table class="adminlist table table-striped" id="available_fields itemsList">
					<thead>
					<tr>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_AV_CSVI_NAME', 'tbl.csvi_name', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_AV_COMPONENT_NAME', 'tbl.component_name', $listDirn, $listOrder); ?>
						</th>
						<th class="title">
							<?php echo JHtml::_('searchtools.sort', 'COM_CSVI_AV_TABLE', 'tbl.template_name', $listDirn, $listOrder); ?>
						</th>
					</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="7">
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
							foreach ($this->items as $i => $item)
							{
						?>
								<tr>
									<td>
										<?php
											echo $item->csvi_name;

											if ($item->isprimary)
											{
												echo '<span class="isprimary">' . JText::_('COM_CSVI_IS_PRIMARY') . '</span>';
											}
										?>
									</td>
									<td>
										<?php echo $item->component_name; ?>
									</td>
									<td>
										<?php echo $item->component_table; ?>
									</td>
								</tr>
						<?php
							}
						?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
		<input type="hidden" id="task" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>