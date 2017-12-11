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
		<input type="hidden" name="view" value="level" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="conference_level_id" value="<?php echo $this->item->conference_level_id ?>" />
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
					<label for="label" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_LABEL_CLASS')?>
					</label>
					<div class="controls">
						<input type="text" name="label" id="label" class="span" value="<?php echo $this->item->label?>"/>
						<span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_LABEL_CLASS_DESC')?></span>
					</div>
				</div>
				<div class="control-group">
					<label for="description" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_DESCRIPTION')?>
					</label>
					<div class="controls">
						<?php echo $editor->display( 'description',  $this->item->description, '100%', '200', '50', '10', false ) ; ?>
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