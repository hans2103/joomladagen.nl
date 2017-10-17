<?php
/**
 * @version     1.5
 * @package     com_jticketing
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Techjoomla <extensions@techjoomla.com> - http://techjoomla.com
 */

// No direct access.
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

if(JVERSION >= '3.0')
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('formbehavior.chosen', 'select');
	JHtml::_('behavior.multiselect');
	JHTML::_('behavior.modal', 'a.modal');
}
$Itemid = JFactory::getApplication()->input->get('Itemid');
// Import CSS.
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_jticketing/assets/css/jticketing.css');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canOrder   = $user->authorise('core.edit.state', 'com_jticketing');
$saveOrder  = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_jticketing&task=events.saveOrderAjax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'eventList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
?>

<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;

		if (order != '<?php echo $listOrder; ?>')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}

		Joomla.tableOrdering(order, dirn, '');
	}
	techjoomla.jQuery(document).ready(function () {
		techjoomla.jQuery('#export-submit').on('click', function () {
			document.getElementById('task').value = 'events.csvexport';
			document.adminForm.submit();
			document.getElementById('task').value = '';
		});
	});
</script>

<?php
if (!empty($this->extra_sidebar))
{
    $this->sidebar .= $this->extra_sidebar;
}
?>

	<form action="<?php echo JRoute::_('index.php?option=com_jticketing&view=events'); ?>" method="post" name="adminForm" id="adminForm">
		<?php if(!empty($this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
		<?php endif;?>

			<?php
			// Search tools bar
			echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));

			// Native Event Manager.
			if($this->integration != 2)
			{
			?>
			<div class="alert alert-info alert-help-inline">
				<?php echo JText::_('COMJTICKETING_INTEGRATION_NATIVE_NOTICE');?>
			</div>
			<?php
			return false;
			}
			?>

			<div class="clearfix"> </div>
			<div class="table-responsive">
				<?php if (empty($this->items)) : ?>
				<div class="clearfix">&nbsp;</div>
				<div class="alert alert-no-items">
					<?php echo JText::_('NODATA'); ?>
				</div>
			<?php
			else : ?>
				<table class="table table-striped" id="eventList">
					<thead>
						<tr>

						<?php if (isset($this->items[0]->ordering)): ?>
							<th width="1%" class="nowrap center hidden-phone">
								<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
							</th>
						<?php endif; ?>
							<th width="1%">
								<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>

						<?php if (isset($this->items[0]->state)): ?>
							<th width="1%" class="nowrap center">
								<?php echo JHtml::_('grid.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>

						<th class=''>
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_EVENTS_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
<!--
						<th class='hidden-phone'>
							<?php // echo JHtml::_('grid.sort',  'COM_JTICKETING_EVENTS_DESC', 'a.short_description', $listDirn, $listOrder); ?>
						</th>
-->
						<th class='hidden-phone'>
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_EVENTS_TICKET_TYPES', '', $listDirn, $listOrder); ?>
						</th>
						<th class='hidden-phone'>
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_EVENTS_CATEGORY', 'a.catid', $listDirn, $listOrder); ?>
						</th>

						<th class='hidden-phone hidden-tablet'>
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_EVENTS_CREATOR', 'a.created_by', $listDirn, $listOrder); ?>
						</th>

						<th class='hidden-phone'>
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_EVENTS_STARTDATE', 'a.startdate', $listDirn, $listOrder); ?>
						</th>

						<th class='hidden-phone'>
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_EVENTS_ENDDATE', 'a.enddate', $listDirn, $listOrder); ?>
						</th>

						<th class='hidden-phone'>
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_EVENTS_LOCATION', 'a.location', $listDirn, $listOrder); ?>
						</th>


						<th class='center'>
							<?php echo JHtml::_('grid.sort',  'COM_JTICKETING_EVENTS_FEATURED', 'a.featured', $listDirn, $listOrder); ?>
						</th>

						<?php if (isset($this->items[0]->id)): ?>
							<th width="1%" class="nowrap center hidden-phone hidden-tablet">
								<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>
						</tr>
					</thead>

					<tfoot>
						<?php
						if(isset($this->items[0]))
						{
							$colspan = count(get_object_vars($this->items[0]));
						}
						else
						{
							$colspan = 10;
						}
						?>
						<tr>
							<td colspan="<?php echo $colspan ?>">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>

					<tbody>
					<?php
					foreach ($this->items as $i => $item) :
						$ordering   = ($listOrder == 'a.ordering');
						$canCreate	= $user->authorise('core.create',		'com_jticketing');
						$canEdit	= $user->authorise('core.edit',			'com_jticketing');
						$canCheckin	= $user->authorise('core.manage',		'com_jticketing');
						$canChange	= $user->authorise('core.edit.state',	'com_jticketing');
					$link_ticket_types =JRoute::_("index.php?option=com_jticketing&view=events&layout=tickettypes&tmpl=component&id=".$item->id);
						?>

						<tr class="row<?php echo $i % 2; ?>">

						<?php if (isset($this->items[0]->ordering)): ?>
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
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
							<?php else : ?>
								<span class="sortable-handler inactive" >
									<i class="icon-menu"></i>
								</span>
							<?php endif; ?>
							</td>
						<?php endif; ?>
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>

						<?php if (isset($this->items[0]->state)): ?>
							<td class="center">
								<?php echo JHtml::_('jgrid.published', $item->state, $i, 'events.', $canChange, 'cb'); ?>
							</td>
						<?php endif; ?>

						<td class="">
							<?php if (isset($item->checked_out) && $item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'events.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_jticketing&task=event.edit&id='.(int) $item->id); ?>">
								<?php echo $this->escape($item->title); ?></a>
							<?php else : ?>
								<?php echo $this->escape($item->title); ?>
							<?php endif; ?>
						</td>
<!--
						<td class="hidden-phone">
							<?php
							//if (strlen($item->short_description)>50)
							//echo substr($item->short_description, 0, 50) . " ...";
							//else
							//echo $item->short_description;
							?>
						</td>
-->
						<td class="hidden-phone">

								<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" href="<?php echo $link_ticket_types; ?>" class="modal">
									<span class="editlinktip hasTip" title="<?php echo JText::_('COM_JTICKETING_VIEW_TICKET_TYPES');?>" ><?php echo JText::_('COM_JTICKETING_VIEW_TICKET_TYPES');?></span>
								</a>
						</td>
						<td class="hidden-phone">
							<?php echo $item->catid; ?>
						</td>

						<td class="hidden-phone hidden-tablet">
							<?php echo $item->creator; ?>
						</td>

						<td class="hidden-phone">
							<?php
								$statrtDate = JFactory::getDate($item->startdate)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM'));
								$startDate =  JHtml::date($item->startdate, JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM'), true);
								echo $startDate;
							?>
						</td>

						<td class="hidden-phone">
							<?php $endDate =  JFactory::getDate($item->enddate)->Format(JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM'));
								$endDate =  JHtml::date($item->enddate, JText::_('COM_JTICKETING_DATE_FORMAT_SHOW_AMPM'), true);
								echo $endDate;
							?>
						</td>

						<td class="hidden-phone">
							<?php
							if ($item->online_events == "1")
							{
								if ($item->online_provider == "plug_tjevents_adobeconnect")
								{
									echo JText::_('COM_JTICKETING_ADOBECONNECT_PLG_NAME') . " - " . $item->name;
								}
							}
							elseif($item->online_events == "0")
							{
								if ($item->venue != "0")
								{
									echo $item->name . " : " . JText::_('COM_JTICKETING_BILLIN_ADDR') .  "- " . $item->address . ", " . $item->city . ", " . $item->region . ", " . $item->coutryName . ", " . JText::_('COM_JTICKETING_FORM_LBL_VENUE_ZIPCODE') . " - " . $item->zipcode;
								}
								else
								{
									echo $item->location;
								}
							}
							else
							{
								echo "-";
							}?>
						</td>

						<td class="center">
							<a href="javascript:void(0);"
							class="btn btn-micro active hasTooltip"
							onclick=" listItemTask('cb<?php echo $i;?>','<?php echo ($item->featured) ? 'events.unfeature' : 'events.feature';?>')"
							title="<?php echo ( $item->featured ) ? JText::_('COM_JTICKETING_UNFEATURE_ITEM') : JText::_('COM_JTICKETING_FEATURE_ITEM'); ;?>">
								<?php
								if(JVERSION > '3.0')
								{
									$featuredClass = ($item->featured) ? 'featured' : 'unfeatured';
								}
								else
								{
									$featuredClass = ($item->featured) ? 'star' : 'star-empty';
								}
								?>
								<i class="icon-<?php echo $featuredClass;?>"></i>
							</a>
						</td>

						<?php if (isset($this->items[0]->id)): ?>
							<td class="center hidden-phone hidden-tablet">
								<?php echo (int) $item->id; ?>
							</td>
						<?php endif; ?>
					</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php endif; ?>
			</div>
			<div class="bs-callout bs-callout-info" id="callout-xref-input-group">
				<p><?php echo JText::_('COMJTICKETING_EVENT_CSV_HELP_TEXT'); ?></p>
				<p><?php echo JText::_('COMJTICKETING_EVENT_CSV_EXPORT_HELP_TEXT'); ?></p>
				<p><?php echo JText::_('COMJTICKETING_EVENT_CSV_IMPORT_HELP_TEXT'); ?></p>
			</div>
			<input type="hidden" id="task" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>

<div style="display:none">
	<div id="import_events">
		<form action="<?php echo JUri::base(); ?>index.php?option=com_jticketing&task=events.csvImport&tmpl=component&format=html" id="uploadForm" class="form-inline center"  name="uploadForm" method="post" enctype="multipart/form-data">
			<table>
				<tr>&nbsp;</tr>
				<tr>
					<div id="uploadform">
						<fieldset id="upload-noflash" class="actions">
							<label for="upload-file" class="control-label"><?php echo JText::_('COMJTICKETING_UPLOADE_FILE'); ?></label>
							<input type="file" id="upload-file" name="csvfile" id="csvfile" />
							<button class="btn btn-primary" id="upload-submit">
								<i class="icon-upload icon-white"></i>
								<?php echo JText::_('COMJTICKETING_EVENT_IMPORT_CSV'); ?>
							</button>
							<hr class="hr hr-condensed">
							<div class="alert alert-warning" role="alert"><i class="icon-info"></i>
									<?php
									$link = '<a href="' . JUri::root() . 'media/com_jticketing/samplecsv/EventImport.csv' . '">' . JText::_("COM_JTICKETING_CSV_SAMPLE") . '</a>';
								echo JText::sprintf('COM_JTICKETING_CSVHELP', $link);
								?>
							</div>
						</fieldset>
					</div>
				</tr>
			</table>
		</form>
	</div>
</div>
