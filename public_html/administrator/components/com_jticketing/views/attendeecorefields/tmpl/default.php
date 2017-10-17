<?php
/**
 * @version    SVN:
 * @package    Com_Jticketing
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright  2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
$user      = JFactory::getUser();
// Joomla Component Creator code to allow adding non select list filters
if (!empty($this->extra_sidebar))
{
	$this->sidebar .= $this->extra_sidebar;
}

?>
<form
action="<?php echo JRoute::_('index.php?option=com_jticketing&view=attendeecorefields&client=' . $this->input->get('client', '', 'STRING')); ?>" method="post" name="adminForm" id="adminForm">
<?php
if (!empty($this->sidebar))
{?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php
}
else
{?>
	<div id="j-main-container">
<?php
}?>
		<?php
		if(!empty($this->items))
		{
		?>
		<table class="table table-striped" id="vendorList">
			<thead>
				<tr>
					<?php if (isset($this->items[0]->ordering)) :?>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.`ordering`', $this->listDirn, $this->listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<?php endif;?>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>

					<?php if (isset($this->items[0]->state)) :?>
					<th width="5%" >
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $this->listDirn, $this->listOrder); ?>
					</th>
					<?php endif?>
					
					<th width="10%">
						<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_ATTENDEE_TITLE', 'a.`label`', $this->listDirn, $this->listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_ATTENDEE_TYPE', 'a.`type`', $this->listDirn, $this->listOrder); ?>
					</th>

					<th width="1%" >
						<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_ATTENDEE_ID', 'a.`id`', $this->listDirn, $this->listOrder); ?>
					</th>
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
				<?php
				foreach ($this->items as $i => $item)
				{
					$ordering   = ($this->listOrder == 'a.ordering');
					$canChange  = $user->authorise('core.edit.state', 'com_jticketing');
					?>
					<tr class="row<?php echo $i % 2; ?>">
					<?php
						if (isset($this->items[0]->ordering))
						{?>
							<td class="order nowrap center hidden-phone">
								<?php
								if ($canChange)
								{
									$disableClassName = '';
									$disabledLabel    = '';

									if (!$this->saveOrder)
									{
										$disabledLabel    = JText::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									}
								?>
									<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>">
										<i class="icon-menu"></i>
									</span>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php
								}
								else
								{?>
									<span class="sortable-handler inactive">
										<i class="icon-menu"></i>
									</span>
								<?php
								}?>
							</td>
						<?php
						}?>

							<td class="hidden-phone">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<?php if (isset($this->items[0]->state)) : ?>
							<?php $class = ($canChange) ? 'active' : 'disabled'; ?>
							<td>
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'attendeecorefields.', $canChange, 'cb'); ?>
							</td>
							<?php endif; ?>
							<td >
								<?php echo $item->label; ?>
							</td>
							<td >
								<?php echo $item->type; ?>
							</td>
							<td >
								<?php echo $item->id; ?>
							</td>
						</tr>
				<?php
				}?>
			</tbody>
		</table>
		<?php
		}?>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="filter_order" value="<?php echo $this->listOrder; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $this->listDirn; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
