<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2017 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_jticketing');
$canEdit    = $user->authorise('core.edit', 'com_jticketing');
$canCheckin = $user->authorise('core.manage', 'com_jticketing');
$canChange  = $user->authorise('core.edit.state', 'com_jticketing');
$canDelete  = $user->authorise('core.delete', 'com_jticketing');
$saveOrder  = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_tjlms&task=venues.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'venueList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<div class="container-fluid">
	<?php
	if ($this->params->get('show_page_heading', 1)):
		?>
		<div class="page-header">
			<h1><?php echo JText::_('COM_JTICKETING_MY_VENUES'); ?></h1>
		</div>
		<?php
	endif;
	?>
</div>
</br>
<form action="<?php echo JRoute::_('index.php?option=com_jticketing&view=venues'); ?>" method="post" name="adminForm" id="adminForm">
	<div>
		<?php echo $this->toolbarHTML;?>
	</div>
	<div class="clearfix"> </div>
	<hr class="hr-condensed" />
	<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
		<div class="input-group">
			<input type="text" placeholder="<?php echo JText::_('COM_JTICKETING_SEARCH_VENUES_NAME'); ?>" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" class="form-control" onchange="document.adminForm.submit();" />
			<span class="input-group-btn">
				<button type="button" onclick="this.form.submit();" class="btn btn-success tip hasTooltip" data-original-title="Search"><i class="fa fa-search"></i></button>
				<button type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();" class="btn btn-primary tip hasTooltip" data-original-title="Clear"><i class="fa fa-remove"></i></button>
			</span>
		</div>
	</div>
	<div class="col-lg-7 col-md-6 col-sm-6 col-xs-12">
		<div class="input-group pull-right">
			<?php echo JHtml::_('select.genericlist', $this->venueTypeList, "venue_type", 'style="display:inline-block;" class="selectpicker" data-style="btn-primary" size="1" data-live-search="true"
				onchange="document.adminForm.submit();" name="venue_type"',"value", "text", $this->lists['venueTypeList']);
			?>
		</div>
	</div>
	<div class="col-lg-1 col-md-6 col-sm-6 col-xs-12">
		<div class="input-group pull-right">
			<?php echo JHtml::_('select.genericlist', $this->venuePrivacyList, "venue_privacy", 'style="display:inline-block;" class="inputbox" size="1"
				onchange="document.adminForm.submit();" name="venue_privacy"',"value", "text", $this->lists['venuePrivacyList']);
			?>
		</div>
	</div>
	<div class="col-lg-1 col-md-6 col-sm-6 col-xs-12">
		<div class="btn-group pull-right hidden-xs">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	</div>

	<div class="clearfix"> </div>
	<hr class="hr-condensed" />
	<?php
	if (empty($this->items ))
	{
		?>
		<div class="alert alert-info" role="alert">
		<?php echo JText::_('NODATA'); ?>
		</div>
		<?php
	}
	else
	{
		?>
		<div class = "col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<table class="table table-striped table-bordered table-hover" id="venueList">
				<thead>
					<tr>
						<th class="hidden-phone">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL');?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th class="center">
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_VENUES_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
						<?php
						if (isset($this->items[0]->state)):
							?>
							<th width="1%" class="nowrap center">
								<?php
								echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder);
								?>
							</th>
							<?php
						endif;
							?>
						<th>
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_VENUES_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_VENUES_CATEGORY', 'a.venue_category', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_VENUES_TYPE', 'a.online', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_VENUES_PRIVACY', 'a.privacy', $listDirn, $listOrder); ?>
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
				foreach ($this->items as $i => $item):

				$canEdit = $user->authorise('core.edit', 'com_jticketing');

				if (!$canEdit && $user->authorise('core.edit.own', 'com_jticketing')):
					$canEdit = JFactory::getUser()->id == $item->created_by;
				endif;
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="center">
						<?php echo $item->id; ?>
						</td>

						<td class="center" data-title="<?php echo JText::_('JSTATUS');?>">
							<div>
								<a class="btn btn-micro hasTooltip" href="<?php if ($canEdit):?>javascript:void(0);<?php else: ?><?php echo JURI::root();?>index.php?option=com_users<?php endif;?>" title="<?php echo ($item->state) ? JText::_('TJTOOLBAR_UNPUBLISH') : JText::_('TJTOOLBAR_PUBLISH');?>"
								onclick="document.adminForm.cb<?php echo $i; ?>.checked=1; document.adminForm.boxchecked.value=1; Joomla.submitbutton('<?php echo ($item->state) ? 'venues.unpublish' : 'venues.publish';?>');">
								<?php if ($item->state == 1)
								{?>
									<span>
										<i class="fa fa-check-circle" aria-hidden="true"></i>
									</span>
								<?php }
								elseif ($item->state == 0)
								{?>
									<span>
										<i class="fa fa-times-circle" aria-hidden="true"></i>
									</span>
								<?php } ?>
								</a>
							</div>
						</td>

						<td>
						<?php
						if (isset($item->checked_out) && $item->checked_out):
							echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'venues.', $canCheckin);
						endif;
							?>
							<a href="<?php echo JRoute::_('index.php?option=com_jticketing&view=venueform&layout=default&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->name); ?>
							</a>
						</td>
						<td>
							<?php echo $item->venue_category; ?>
						</td>
						<td>
							<?php echo ($item->online) ? JText::_("COM_JTICKETING_VENUE_TYPEONLINE"):JText::_("COM_JTICKETING_VENUE_TYPEOFFLINE") ?>
						</td>
						<td>
							<?php echo ($item->privacy) ? JText::_("COM_JTICKETING_VENUE_PRIVACY_PUBLIC"):JText::_("COM_JTICKETING_VENUE_PRIVACY_PRIVATE") ?>
						</td>
					</tr>
					<?php
				endforeach;
				?>
				</tbody>
			</table>
		</div>
		<?php
	}
	?>
<input type="hidden" name="task" value=""/>
<input type="hidden" name="boxchecked" value="0"/>
<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
<?php echo JHtml::_('form.token'); ?>
</form>
