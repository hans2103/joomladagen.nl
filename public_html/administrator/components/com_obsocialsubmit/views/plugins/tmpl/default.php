<?php
/**
 * @package 	obSocialSubmit
 * @author 		foobla.com.
 * @copyright	Copyright (C) 2007-2014 foobla.com. All rights reserved.
 * @license		GNU/GPL
 */

defined('_JEXEC') or die;
global $isJ25;

JHTML::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'html');
if(!$isJ25){
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('dropdown.init');
//	JHtml::_('formbehavior.chosen', 'select');
}
$connections  = $this->get('Connections');
$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', 'com_obsocialsubmit');
$saveOrder	= $listOrder == 'i.ordering';
if(!$isJ25){
	if ($saveOrder)
	{
		$saveOrderingUrl = 'index.php?option=com_obsocialsubmit&task=adapters.saveOrderAjax&tmpl=component';
		JHtml::_('sortablelist.sortable', 'pluginList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
	}
}
$sortFields = $this->getSortFields();
?>
<form action="<?php echo JRoute::_('index.php?option=com_obsocialsubmit&view=plugins'); ?>" method="post" name="adminForm" id="adminForm">
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
				<div class="btn-wrapper">
					<select name="filter_type" class="" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_OBSOCIALSUBMIT_SELECT_PLUGIN_TYPE');?></option>
						<?php
						echo JHtml::_('select.options', $this->get('PluginTypes'), 'value', 'text', $this->state->get('filter.type'));?>
					</select>
				</div>
				<div class="btn-wrapper">
					<select name="filter_state" class="inputbox" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
						<?php echo JHtml::_('select.options', obSocialSubmitHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state')); ?>
					</select>
				</div>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<div class="btn-wrapper">
					<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			</div>
		</div>
		<div class="clearfix"> </div>
		<table class="table table-striped adminlist" id="pluginList">
			<thead>
				<tr>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('grid.sort', 'JSTATUS', 'enabled', $listDirn, $listOrder); ?>
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'name', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" class="nowrap hidden-phone" >
						<?php echo JHtml::_('grid.sort', 'COM_OBSOCIALSUBMIT_HEADING_ADDON', 'folder', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
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
					<td class="hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="center">
						<div class="btn-group">
							<?php echo JHtml::_('jgrid.published', $item->enabled, $i,'plugins.');?>
						</div>
					</td>
					<td class="has-context">
						<div class="pull-left">
							<?php echo $this->escape($item->name); ?>
						</div>
						
					</td>
					<td class="small hidden-phone">
						<span class="label label-info"><?php echo $item->folder;?></span>
					</td>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php //Load the batch processing form. ?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	</div>
</div>
</form>
<script type="text/javascript">

jQuery( document ).ready(function() {

	jQuery('a.obss_show_more').click(function(e){
		e.preventDefault();
//		console.log(jQuery(this).prev('ul').children('li.hide').length);
		if(jQuery(this).prev('ul').children('li.hide').length){
			jQuery(this).prev('ul').children('li.hide').removeClass('hide');
		} else {
			jQuery(this).prev('ul').children('li').each(function( index, el ){
//				console.log(index);
				if(index >= 3){
					el.addClass('hide');
				}
			});
		}
	});
	
	jQuery('.obss_con input[type="checkbox"]').change(function(e){
		var ec = jQuery(this);
		var es = jQuery(this).parent();
		var ei = es.children('i').get(0);
		var cf = confirm("<?php echo JText::_("COM_OBSOCIALSUBMIT_SET_CONNECT_CONFIRM_MSG");?>");

		if( !cf ) {
			if(this.checked){
				this.checked = false;
			} else {
				this.checked = true;
			}
			return;
		}

		if( es.hasClass('disabled') ) {
			if(this.checked){
				this.checked=false;
			}else{
				this.checked=true;
			}
			return;
		}
		
		jQuery(this).parent().addClass('disabled')
		var data = 'id='+jQuery(this).val();
		jQuery.post(
			'index.php?option=com_obsocialsubmit&task=adapters.set_connect',
			data, 
			function(data, textStatus, jqXHR){
				if( ec.is(':checked') ) {
//					console.log(ei);
					ei.addClass('icon-publish');
					ei.removeClass('icon-unpublish');
				} else {
					ei.removeClass('icon-publish');
					ei.addClass('icon-unpublish');
				}
//				console.log(ec);
				es.removeClass('disabled');
//				console.log(data);
			}, 'json');
   });
   
   
});
</script>
