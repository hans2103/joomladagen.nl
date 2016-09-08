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
$this->loadHelper('speaker');

// Joomla! editor object
$editor = JFactory::getEditor();

// Get the Itemid
$itemId = FOFInput::getInt('Itemid',0,$this->input);
if($itemId != 0) {
	$actionURL = 'index.php?Itemid='.$itemId;
} else {
	$actionURL = 'index.php';
}

?>
<div class="conference">
	<form name="adminForm" class="form form-horizontal" action="<?php echo $actionURL ?>" method="post" enctype="multipart/form-data">
		<div class="row-fluid">
			<h1 class="pull-left"><?php echo JText::_('COM_CONFERENCE_FIELD_SESSION')?></h1>
			<div class="btn-toolbar pull-right">
				<div id="toolbar-cancel" class="btn-group">
					<a class="btn btn-small" href="<?php echo JRoute::_('index.php?option=com_conference&view=my')?>">
						<span class="icon-cancel"></span> <?php echo JText::_('JCANCEL')?>
					</a>
				</div>
				<div id="toolbar-apply" class="btn-group">
					<button class="btn btn-small btn-success" type="submit">
						<span class="icon-pencil"></span> <?php echo JText::_('JSAVE')?>
					</button>
				</div>
			</div>
		</div>
		<div class="well well-small">
			<input type="hidden" name="option" value="com_conference" />
			<input type="hidden" name="view" value="session" />
			<input type="hidden" name="task" value="save" />
			<input type="hidden" name="conference_session_id" value="<?php echo $this->item->conference_session_id ?>" />
			<input type="hidden" name="Itemid" value="<?php echo $itemId ?>" />
			<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />
			<input type="hidden" name="conference_speaker_id" value="<?php echo(ConferenceHelperSpeaker::speakerid(JFactory::getUser()->id)); ?>" />
			<input type="hidden" name="enabled" value="<?php echo($this->item->enabled); ?>" />
		
			<!-- Start row -->
			<div class="row-fluid">
				<!-- Start left -->
				<div class="span12">
					<div class="control-group">
						<label for="title" class="control-label">
							<?php echo JText::_('COM_CONFERENCE_FIELD_TITLE')?>
						</label>
						<div class="controls">
							<input type="text" name="title" id="title" class="span" value="<?php echo $this->item->title?>" required="required"/>
						</div>
					</div>
					<div class="control-group">
						<label for="conference_level_id" class="control-label">
							<?php echo JText::_('COM_CONFERENCE_FIELD_LEVEL'); ?>
						</label>
						<div class="controls">
							<?php echo ConferenceHelperSelect::levels($this->item->conference_level_id); ?>
							<span class="help-block"><a href="<?php echo JRoute::_('index.php?option=com_conference&view=levels')?>"><?php echo JText::_('COM_CONFERENCE_FIELD_LEVEL_DESC'); ?></a></span>
							
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
							<?php echo $editor->display( 'description',  $this->item->description, '100%', '300', '50', '10', false ) ; ?>
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
				</div>
				<!-- End left -->
			</div>
		</div>
	</form>
</div>