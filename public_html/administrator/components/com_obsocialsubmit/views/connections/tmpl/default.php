<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;
global $isJ25;
if(!$isJ25){
	JHtml::_('bootstrap.tooltip');
	JHtml::_('dropdown.init');
	JHtml::_('formbehavior.chosen', 'select');
    JHtml::_('behavior.modal');    
}
JHtml::_('behavior.multiselect');
JHTML::_( 'behavior.modal' );
// $client		= $this->state->get('filter.client_id') ? 'administrator' : 'site';
$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', 'com_obsocialsubmit');
$saveOrder	= $listOrder == 'i.ordering';
/*Return url*/
$redirectUrl = JFactory::getURI();
$redirectUrl = $redirectUrl->toString();

$session = JFactory::getSession();
$session->set( 'return', $redirectUrl );
if(!$isJ25){
	if ($saveOrder)
	{
		$saveOrderingUrl = 'index.php?option=com_obsocialsubmit&task=connections.saveOrderAjax&tmpl=component';
		JHtml::_('sortablelist.sortable', 'articleList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
	}
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_obsocialsubmit&view=connections'); ?>" method="post" name="adminForm" id="adminForm">
<div id="foobla">
	<div class="row-fluid">
<?php if(!empty( $this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-select fltrt pull-left">
				<select name="filter_connection" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('COM_OBSOCIALSUBMIT_CONNECTION_TYPE');?></option>
					<?php 
					echo JHtml::_('select.options', $this->connectiontypes, 'element', 'element', $this->state->get('filter.connection'));?>
				</select>
				<select name="filter_state" class="inputbox" onchange="this.form.submit()">
					<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
					<?php echo JHtml::_('select.options', obSocialSubmitHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state')); ?>
				</select>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		</div>
		<div class="clearfix"> </div>
		<table class="table table-striped adminlist" id="connectionList">
			<thead>
				<tr>
				<?php if(!$isJ25){?>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'i.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
				<?php }?>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'i.published', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap title">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'i.title', $listDirn, $listOrder); ?>
					</th>
					<th width="10%" class="nowrap hidden-phone" >
						<?php echo JHtml::_('grid.sort', 'COM_OBSOCIALSUBMIT_HEADING_ADDON', 'i.name', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'i.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="10">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$ordering   = ($listOrder == 'ordering');
				$canCreate  = $user->authorise('core.create',     'com_obsocialsubmit');
				$canEdit    = $user->authorise('core.edit',       'com_obsocialsubmit');
				$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->get('id')|| $item->checked_out == 0;
				$canChange  = $user->authorise('core.edit.state', 'com_obsocialsubmit') && $canCheckin;
			?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->addon_type; ?>">
				<?php if(!$isJ25){?>
					<td class="order nowrap center hidden-phone">
					<?php if ($canChange) :
						$disableClassName = '';
						$disabledLabel	  = '';
						if (!$saveOrder) :
							$disabledLabel    = JText::_('JORDERINGDISABLED');
							$disableClassName = 'inactive tip-top';
						endif; ?>
						<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
							<i class="icon-menu"></i>
						</span>
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order" />
					<?php else : ?>
						<span class="sortable-handler inactive" >
							<i class="icon-menu"></i>
						</span>
					<?php endif; ?>
					</td>
				<?php }?>
					<td width="1%" class="hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'connections.'); ?>
					</td>
					<td class="nowrap has-context">
						<div class="pull-left">
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->checked_out, $item->checked_out_time, 'connections.', true); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_obsocialsubmit&task=connection.edit&id='.(int) $item->id); ?>">
									<?php echo $this->escape($item->title); ?></a>
							<?php else : ?>
									<?php echo $this->escape($item->title); ?>
							<?php endif; ?>
							
							<?php if (!empty($item->note)) : ?>
								<div class="small">
									<?php echo JText::sprintf('JGLOBAL_LIST_NOTE', $this->escape($item->note));?>
								</div>
							<?php endif; ?>

							<?php if (!empty($item->description)) : ?>
							<div class="small">
								<?php echo $this->escape($item->description);?>
							</div>
							<?php endif; ?>
						</div>
					</td>
					<td class="small hidden-phone">
						<span class="label label-info"><?php echo $item->addon;?></span>
					</td>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php //Load the batch processing form. ?>
		<?php echo $this->loadTemplate('batch'); ?>
        <?php echo $this->loadTemplate('select'); ?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	</div>
</div>
</form>
