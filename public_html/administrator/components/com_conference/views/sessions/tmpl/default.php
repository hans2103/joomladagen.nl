<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

// Load the helpers
$this->loadHelper('params');
$this->loadHelper('select');
$this->loadHelper('format');

// Sorting filters
$sortFields = array(
	'enabled' 				=> JText::_('JPUBLISHED'),
	'title'					=> JText::_('COM_CONFERENCE_FIELD_TITLE'),
	'conference_speaker_id'	=> JText::_('COM_CONFERENCE_FIELD_SPEAKER'),
	'conference_status_id'	=> JText::_('COM_CONFERENCE_FIELD_STATUS'),
	'conference_level_id'	=> JText::_('COM_CONFERENCE_FIELD_LEVEL'),
	'conference_room_id'	=> JText::_('COM_CONFERENCE_FIELD_ROOM'),
	'conference_room_id'	=> JText::_('COM_CONFERENCE_FIELD_SLOT'),
	'modified_on'			=> JText::_('JGLOBAL_FIELD_MODIFIED_LABEL'),
);

?>

<?php if (version_compare(JVERSION, '3.0', 'ge')): ?>
	<script type="text/javascript">
		Joomla.orderTable = function() {
			table = document.getElementById("sortTable");
			direction = document.getElementById("directionTable");
			order = table.options[table.selectedIndex].value;
			if (!order)
			{
				dirn = 'asc';
			}
			else {
				dirn = direction.options[direction.selectedIndex].value;
			}
			Joomla.tableOrdering(order, dirn);
		}
	</script>
<?php endif; ?>

<div class="conference">
	<form name="adminForm" id="adminForm" action="index.php" method="post">
		<input type="hidden" name="option" id="option" value="com_conference" />
		<input type="hidden" name="view" id="view" value="sessions" />
		<input type="hidden" name="task" id="task" value="browse" />
		<input type="hidden" name="boxchecked" id="boxchecked" value="0" />
		<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
		<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>" />
		<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>" />
		<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />
		
		<?php if(version_compare(JVERSION, '3.0', 'gt')): ?>
		<div id="filter-bar" class="btn-toolbar">
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC') ?></label>
				<?php echo $this->getModel()->getPagination()->getLimitBox(); ?>
			</div>
			<?php
			$asc_sel	= ($this->getLists()->order_Dir == 'asc') ? 'selected="selected"' : '';
			$desc_sel	= ($this->getLists()->order_Dir == 'desc') ? 'selected="selected"' : '';
			?>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC') ?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC') ?></option>
					<option value="asc" <?php echo $asc_sel ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING') ?></option>
					<option value="desc" <?php echo $desc_sel ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING') ?></option>
				</select>
			</div>
			<div class="btn-group pull-right">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY') ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY') ?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $this->getLists()->order) ?>
				</select>
			</div>
		</div>
		<div class="clearfix"> </div>
		<?php endif; ?>
	
		<table class="adminlist table table-striped">
			<thead>
				<tr>
					<th width="20">
						<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ) + 1; ?>);" />
					</th>
					<th width="20">
						<?php echo JHTML::_('grid.sort', 'COM_CONFERENCE_FIELD_STATUS', 'enabled', $this->lists->order_Dir, $this->lists->order); ?>
					</th>
					<th width="20">
						<?php echo JHTML::_('grid.sort', 'COM_CONFERENCE_FIELD_SHOW_LIST', 'listview', $this->lists->order_Dir, $this->lists->order); ?>
					</th>
					<th>
						<?php echo JHTML::_('grid.sort', 'COM_CONFERENCE_FIELD_TITLE', 'title', $this->lists->order_Dir, $this->lists->order) ?>
						/
						<?php echo JHTML::_('grid.sort', 'COM_CONFERENCE_FIELD_SPEAKER', 'conference_speaker_id', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
			
					<?php if(ConferenceHelperParams::getParam('status',0)): ?>
					<th width="8%">
						<?php echo JHTML::_('grid.sort', 'COM_CONFERENCE_FIELD_STATUS', 'conference_status_id', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
					<?php endif;?>
					<th width="8%">
						<?php echo JHTML::_('grid.sort', 'COM_CONFERENCE_FIELD_LEVEL', 'conference_level_id', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
					<th width="10%">
						<?php echo JHTML::_('grid.sort', 'COM_CONFERENCE_FIELD_ROOM', 'conference_room_id', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
					<th width="10%">
						<?php echo JHTML::_('grid.sort', 'COM_CONFERENCE_FIELD_SLOT', 'start_time', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
					<th>
						<?php echo JHTML::_('grid.sort', 'COM_CONFERENCE_FIELD_EVENT', 'event', $this->lists->order_Dir, $this->lists->order) ?>
					</th>
					<th class="center" width="7%">
						<?php echo JText::_('COM_CONFERENCE_FIELD_DESCRIPTION') ?>
					</th>
					<th class="center" width="7%">
						<?php echo JText::_('COM_CONFERENCE_FIELD_SLIDES') ?>
					</th>
					<th class="center" width="7%">
						<?php echo JText::_('COM_CONFERENCE_FIELD_VIDEO') ?>
					</th>
					<th width="7%" class="nowrap">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_FIELD_MODIFIED_LABEL', 'modified_on', $this->lists->order_Dir, $this->lists->order, 'browse') ?>
					</th>
				</tr>
				<tr>
					<td></td>
					<td class="center">
						<?php echo ConferenceHelperFormat::enabled($this->escape($this->getModel()->getState('enabled',''))); ?>
					</td>
					<td></td>
					<td>
						<?php echo ConferenceHelperFormat::search($this->escape($this->getModel()->getState('title',''))); ?>
						<?php echo ConferenceHelperSelect::speakers($this->getModel()->getState('speaker',''), 'speaker', array('onchange'=>'this.form.submit();', 'class'=>'input-medium'),true) ?>
					</td>
					
					<?php if(ConferenceHelperParams::getParam('status',0)): ?>
					<td>
						<?php echo ConferenceHelperSelect::status($this->getModel()->getState('status',''), 'status', array('onchange'=>'this.form.submit();', 'class'=>'input-small')) ?>
					</td>
					<?php endif;?>
					<td>
						<?php echo ConferenceHelperSelect::levels($this->getModel()->getState('level',''), 'level', array('onchange'=>'this.form.submit();', 'class'=>'input-small')) ?>
					</td>
					<td>
						<?php echo ConferenceHelperSelect::rooms($this->getModel()->getState('room',''), 'room', array('onchange'=>'this.form.submit();', 'class'=>'input-small')) ?>
					</td>
					<td>
						<?php echo ConferenceHelperSelect::days($this->getModel()->getState('day',''), 'day', array('onchange'=>'this.form.submit();', 'class'=>'input-small')) ?>
					</td>
					<td>
						<?php echo ConferenceHelperSelect::events($this->getModel()->getState('event',''), 'event', array('onchange'=>'this.form.submit();', 'class'=>'input-small')) ?>
					</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			</thead>
			
			<tfoot>
				<tr>
					<td colspan="20">
						<?php if($this->pagination->total > 0) echo $this->pagination->getListFooter() ?>	
					</td>
				</tr>
			</tfoot>
			
			<tbody>
			<?php if($count = count($this->items)): ?>
			<?php $i = -1; $m = 1; ?>
				<?php foreach ($this->items as $item) : ?>
				<?php
					$i++; $m = 1-$m;
					$checkedOut = ($item->locked_by != 0);
					$ordering = $this->lists->order == 'ordering';
					$item->published = $item->enabled;
				?>
				<tr class="<?php echo 'row'.$m; ?>">
					<td>
						<?php echo JHTML::_('grid.id', $i, $item->conference_session_id, $checkedOut); ?>
					</td>
					<td class="center">
						<?php echo JHTML::_('jgrid.published', $item->enabled, $i); ?>
					</td>
					<td class="center">
						<?php if($item->listview): ?>
						<span class="badge badge-success"><span class="icon-checkmark"></span></span>
						<?php else:?>
						<span class="badge badge-important"><span class="icon-delete"></span></span>
						<?php endif;?>
					</td>
					<td align="left">					
						<a href="<?php echo JRoute::_('index.php?option=com_conference&view=session&id='.$item->conference_session_id) ?>" class="conferenceitem">
							<strong><?php echo $this->escape($item->title) ?></strong>
						</a>
						<?php if($item->conference_speaker_id):?>
						<br/><?php echo JText::_('COM_CONFERENCE_FIELD_SPEAKER'); ?>:
						<?php $speakers = ConferenceHelperFormat::speakers($item->conference_speaker_id); ?>
						<?php $i=0;?>
						<?php foreach($speakers as $key=>$speaker) :?>
							<?php if($i > 0):?>,<?php endif;?>
							<a href="<?php echo JRoute::_('index.php?option=com_conference&view=speaker&id='.$speaker->conference_speaker_id) ?>"><?php echo(trim($speaker->title));?></a>
							<?php $i++;?>
						<?php endforeach;?>
						<?php endif;?>
					</td>
					<?php if(ConferenceHelperParams::getParam('status',0)): ?>
					<td class="center">
						<?php $status = ConferenceHelperFormat::status($item->status); ?>
						<span class="label <?php echo $status[2] ?>"><?php echo $status[1] ?></span>
					</td>
					<?php endif;?>
					<td class="center">
						<?php if($item->conference_level_id):?>
						<a href="<?php echo JRoute::_('index.php?option=com_conference&view=level&id='.$item->conference_level_id) ?>">
							<span class="label <?php echo $item->level_label ?>"><?php echo $this->escape($item->level) ?></span>
						</a>
						<?php endif;?>
					</td>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_conference&view=room&id='.$item->conference_room_id) ?>">
							<?php echo $this->escape($item->room)?>
						</a>
					</td>
					<td>
						<?php if($item->day):?>
						<a href="<?php echo JRoute::_('index.php?option=com_conference&view=day&id='.$item->day_id) ?>">
							<?php echo $this->escape($item->day)?>
						</a><br/>
						<span aria-hidden="true" class="icon-clock"></span> 
						<a href="<?php echo JRoute::_('index.php?option=com_conference&view=slot&id='.$item->conference_slot_id) ?>">
							<?php echo JHtml::_('date', $item->start_time,'H:i')?> - <?php echo JHtml::_('date', $item->end_time, 'H:i')?>
						</a>
						<?php endif;?>
					</td>
					<td align="left">						
						<a href="<?php echo JRoute::_('index.php?option=com_conference&view=event&id='.$item->conference_event_id) ?>" class="conferenceitem">
							<strong><?php echo $this->escape($item->event) ?></strong>
						</a>
					</td>
					<td class="center">
						<?php if($item->description): ?>
						<span class="badge badge-success"><span class="icon-checkmark"></span></span>
						<?php else:?>
						<span class="badge badge-important"><span class="icon-delete"></span></span>
						<?php endif;?>
					</td>
					<td class="center">
						<?php if($item->slides): ?>
						<span class="badge badge-success"><span class="icon-checkmark"></span></span>
						<?php else:?>
						<span class="badge badge-important"><span class="icon-delete"></span></span>
						<?php endif;?>
					</td>
					<td class="center">
						<?php if($item->video): ?>
						<span class="badge badge-success"><span class="icon-checkmark"></span></span>
						<?php else:?>
						<span class="badge badge-important"><span class="icon-delete"></span></span>
						<?php endif;?>
					</td>
					<td class="nowrap">
						<?php if($item->modified_on == '0000-00-00 00:00:00'): ?>
							&mdash;
						<?php else: ?>
							<?php echo JHtml::_('date',$item->modified_on, JText::_('DATE_FORMAT_LC4')); ?>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr>
					<td colspan="20">
						<?php echo  JText::_('COM_CONFERENCE_NORECORDS') ?>
					</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
	</form>
</div>