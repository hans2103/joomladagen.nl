<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

$this->loadHelper('params');
$this->loadHelper('select');
$this->loadHelper('format');

// Joomla! editor object
$editor = JFactory::getEditor();
?>

<div class="conference">
	<form action="index.php" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
		<input type="hidden" name="option" value="com_conference" />
		<input type="hidden" name="view" value="session" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="conference_session_id" value="<?php echo $this->item->conference_session_id ?>" />
		<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />
	
		<!-- Start row -->
		<div class="row-fluid">
			<!-- Start left -->
			<div class="span7">
				<div class="control-group">
					<label for="title" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_TITLE')?>
					</label>
					<div class="controls">
						<input type="text" name="title" id="title" class="span" value="<?php echo $this->item->title?>"/>
					</div>
				</div>
				<div class="control-group">
					<label for="title" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_SLUG')?>
					</label>
					<div class="controls">
						<input type="text" name="slug" id="slug" class="span" value="<?php echo $this->item->slug?>"/>
					</div>
				</div>
				<div class="control-group">
					<label for="conference_speaker_id" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_SPEAKER'); ?>
					</label>	
					<div class="controls">
						<?php echo ConferenceHelperSelect::speakers(explode(',',$this->item->conference_speaker_id)); ?>
					</div>
				</div>
				<?php if(ConferenceHelperParams::getParam('status',0)): ?>
				<div class="control-group">
					<label for="language" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_STATUS'); ?>
					</label>
					<div class="controls">
						<?php echo ConferenceHelperSelect::status($this->item->status); ?>
					</div>
				</div>
				<?php endif;?>
				<div class="control-group">
					<label for="conference_level_id" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_LEVEL'); ?>
					</label>
					<div class="controls">
						<?php echo ConferenceHelperSelect::levels($this->item->conference_level_id); ?>
					</div>
				</div>
				<div class="control-group">
					<label for="conference_room_id" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_ROOM'); ?>
					</label>
					<div class="controls">
						<?php echo ConferenceHelperSelect::rooms($this->item->conference_room_id); ?>
					</div>
				</div>
				<div class="control-group">
					<label for="conference_slot_id" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_SLOT'); ?>
					</label>
					<div class="controls">
						<?php echo ConferenceHelperSelect::slots($this->item->conference_slot_id); ?>
					</div>
				</div>
				<?php if(ConferenceHelperParams::getParam('language',0)): ?>
				<div class="control-group">
					<label for="language" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_LANGUAGE'); ?>
					</label>
					<div class="controls">
						<?php echo ConferenceHelperSelect::language($this->item->language); ?>
					</div>
				</div>
				<?php endif;?>
				<hr>
				<div class="control-group">
					<label for="description" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_DESCRIPTION'); ?>
					</label>
					<div class="controls">
						<?php echo $editor->display( 'description',  $this->item->description, '100%', '200', '50', '10', false ) ; ?>
					</div>
				</div>
				<hr>
				<div class="control-group">
					<label for="slides" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_SLIDES')?>
					</label>
					<div class="controls">
						<textarea name="slides" id="slides" rows="8" class="span"><?php echo $this->item->slides?></textarea>
						<span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_SLIDES_DESC')?></span>
					</div>
				</div>
				<hr>
				<div class="control-group">
					<label for="video" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_VIDEO')?>
					</label>
					<div class="controls">
						<textarea name="video" id="video" rows="8" class="span"><?php echo $this->item->video?></textarea>
						<span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_VIDEO_DESC')?></span>
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
					<div class="control-group">
						<label for="listview" class="control-label">
							<?php echo JText::_('COM_CONFERENCE_FIELD_SHOW_LIST'); ?>
						</label>
						<div class="controls">
							<?php echo ConferenceHelperSelect::booleanlist('listview','',$this->item->listview); ?>
						</div>
					</div>
				</div>
				
				<div class="well iteminfo">
					<div class="control-group">
						<h3><?php echo JText::_('COM_CONFERENCE_FIELD_NOTES_INTERNAL')?></h3>
						<textarea name="notes" id="notes" rows="4" class="span"><?php echo $this->item->notes?></textarea>
						<span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_NOTES_INTERNAL_DESC')?></span>
					</div>
				</div>
			</div>
			<!-- End right -->
		</div>
		<!-- End row -->
	</form>
</div>