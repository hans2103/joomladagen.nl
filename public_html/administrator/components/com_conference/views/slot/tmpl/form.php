<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

$this->loadHelper('select');
$this->loadHelper('format');

// Joomla! editor object
$editor = JFactory::getEditor();
?>

<div class="conference">
	<form action="index.php" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
		<input type="hidden" name="option" value="com_conference" />
		<input type="hidden" name="view" value="slot" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="conference_slot_id" value="<?php echo $this->item->conference_slot_id ?>" />
		<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />
		
		<!-- Start row -->
		<div class="row-fluid">
			<!-- Start left -->
			<div class="span7">
				<div class="control-group">
					<label for="conference_day_id" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_DAY'); ?>
					</label>
					<div class="controls">
						<?php echo ConferenceHelperSelect::days($this->item->conference_day_id); ?>
					</div>
				</div>
				<div class="control-group">
					<label for="start_time" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_TIME_START'); ?>
					</label>
					<div class="controls">
						<input type="text" name="start_time" id="start_time" value="<?php echo JHtml::_('date', $this->item->start_time, 'H:i'); ?>"/>
					</div>
				</div>
				<div class="control-group">
					<label for="end_time" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_TIME_END'); ?>
					</label>
					<div class="controls">
						<input type="text" name="end_time" id="end_time" value="<?php echo JHtml::_('date', $this->item->end_time, 'H:i'); ?>"/>
					</div>
				</div>
				<div class="control-group">
					<label for="general" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_GENERAL'); ?>
					</label>
					<div class="controls">
						<?php echo ConferenceHelperSelect::booleanlist('general','',$this->item->general); ?>
					</div>
				</div>
			</div>
			<!-- End left -->
			
			<!-- Start right -->
			<div class="span5">
				<div class="well iteminfo">
					<div class="control-group">
						<label for="enabled" class="control-label">
							<?php echo JText::_('JPUBLISHED'); ?>
						</label>
						<div class="controls">
							<?php echo ConferenceHelperSelect::published($this->item->enabled); ?>
						</div>
					</div>
					<div class="control-group">
						<label for="created_by" class="control-label">
							<?php echo JText::_('JGLOBAL_FIELD_CREATED_BY_LABEL'); ?>
						</label>
						<div class="controls">
							<input type="text" class="input" name="created_by" id="created_by" value="<?php echo JFactory::getUser($this->item->created_by)->name; ?>" disabled="disabled" />
						</div>
					</div>
					<div class="control-group">
						<label for="created_on" class="control-label">
							<?php echo JText::_('JGLOBAL_FIELD_CREATED_LABEL'); ?>
						</label>
						<div class="controls">
							<?php echo JHTML::_('calendar', $this->item->created_on, 'created_on', 'created_on'); ?>
						</div>
					</div>
					<div class="control-group">
						<label for="modified_by" class="control-label">
							<?php echo JText::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL'); ?>
						</label>
						<div class="controls">
							<input type="text" class="input" name="modified_by" id="modified_by" value="<?php echo JFactory::getUser($this->item->modified_by)->name; ?>" disabled="disabled" />
						</div>
					</div>
					<div class="control-group">
						<label for="modified_on" class="control-label">
							<?php echo JText::_('JGLOBAL_FIELD_MODIFIED_LABEL'); ?>
						</label>
						<div class="controls">
							<?php echo JHTML::_('calendar', $this->item->modified_on, 'modified_on', 'modified_on'); ?>
						</div>
					</div>
				</div>
			</div>
			<!-- End right -->
		</div>
		<!-- End row -->
	</form>
</div>