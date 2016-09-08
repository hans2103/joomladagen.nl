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
			<h1 class="pull-left"><?php echo JText::_('COM_CONFERENCE_FIELD_SPEAKER')?></h1>
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
			<input type="hidden" name="view" value="speaker" />
			<input type="hidden" name="task" value="save" />
			<input type="hidden" name="conference_speaker_id" value="<?php echo $this->item->conference_speaker_id ?>" />
			<input type="hidden" name="Itemid" value="<?php echo $itemId ?>" />
			<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />
			<input type="hidden" name="user_id" value="<?php echo JFactory::getUser()->id?>" />
			<input type="hidden" name="enabled" value="<?php echo($this->item->enabled); ?>" />
			
			<!-- Start row -->
			<div class="row-fluid">
				<!-- Start left -->
				<div class="span12">
					<div class="control-group">
						<label for="title" class="control-label">
							<?php echo JText::_('COM_CONFERENCE_FIELD_SPEAKER')?>
						</label>
						<div class="controls">
							<input type="text" name="title" id="title" class="span" value="<?php echo $this->item->title?>" required="required"/>
						</div>
					</div>
					<?php if(!JFactory::getUser()->id):?>
					<div class="control-group">
						<label for="email" class="control-label">
							<?php echo JText::_('JGLOBAL_EMAIL')?>
						</label>
						<div class="controls">
							<input type="text" name="email" id="email" class="span" placeholder="mail@website.com" value="<?php echo $this->item->email?>" required="required"/>
							<span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_EMAIL_DESC')?></span>
						</div>
					</div>
					<?php endif;?>
					<div class="control-group">
						<label for="speakernotes" class="control-label">
							<?php echo JText::_('COM_CONFERENCE_FIELD_NOTES')?>
						</label>
						<div class="controls">
							<textarea name="speakernotes" id="speakernotes" rows="4" class="span"><?php echo $this->item->speakernotes?></textarea>
							<span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_NOTES_DESC')?></span>
						</div>
					</div>
					<hr>
					<?php if(ConferenceHelperParams::getParam('twitter',1)): ?>
					<div class="control-group">
						<label for="twitter" class="control-label">
							<?php echo JText::_('COM_CONFERENCE_FIELD_TWITTER')?>
						</label>
						<div class="controls">
							<div class="input-prepend">
								<span class="add-on">@</span>
								<input type="text" name="twitter" id="twitter" class="span" placeholder="username" value="<?php echo $this->item->twitter?>"/>
							</div>
						</div>
					</div>
					<?php endif;?>
					<?php if(ConferenceHelperParams::getParam('facebook',1)): ?>
					<div class="control-group">
						<label for="facebook" class="control-label">
							<?php echo JText::_('COM_CONFERENCE_FIELD_FACEBOOK')?>
						</label>
						<div class="controls">
							<div class="input-prepend">
								<span class="add-on">http://www.facebook.com/</span>
								<input type="text" name="facebook" id="facebook" class="span" placeholder="username" value="<?php echo $this->item->facebook?>"/>
							</div>
						</div>
					</div>
					<?php endif;?>
					<?php if(ConferenceHelperParams::getParam('googleplus',1)): ?>
					<div class="control-group">
						<label for="googleplus" class="control-label">
							<?php echo JText::_('COM_CONFERENCE_FIELD_GOOGLEPLUS')?>
						</label>
						<div class="controls">
							<div class="input-prepend">
								<span class="add-on">http://plus.google.com/</span>
								<input type="text" name="googleplus" id="googleplus" class="span" placeholder="username" value="<?php echo $this->item->googleplus?>"/>
							</div>
						</div>
					</div>
					<?php endif;?>
					<?php if(ConferenceHelperParams::getParam('linkedin',1)): ?>
					<div class="control-group">
						<label for="googleplus" class="control-label">
							<?php echo JText::_('COM_CONFERENCE_FIELD_LINKEDIN')?>
						</label>
						<div class="controls">
							<div class="input-prepend">
								<span class="add-on">http://www.linkedin.com/in/</span>
								<input type="text" name="linkedin" id="linkedin" class="span" placeholder="username" value="<?php echo $this->item->linkedin?>"/>
							</div>
						</div>
					</div>
					<?php endif;?>
					<?php if(ConferenceHelperParams::getParam('website',1)): ?>
					<div class="control-group">
						<label for="website" class="control-label">
							<?php echo JText::_('COM_CONFERENCE_FIELD_WEBSITE')?>
						</label>
						<div class="controls">
							<div class="input-prepend">
								<span class="add-on">http://</span>
								<input type="text" name="website" id="website" class="span" placeholder="www.website.com" value="<?php echo $this->item->website?>"/>
							</div>
						</div>
					</div>
					<?php endif;?>
					<hr>
					<div class="control-group">
						<label for="bio" class="control-label">
							<?php echo JText::_('COM_CONFERENCE_FIELD_BIO')?>
						</label>
						<div class="controls">
							<?php echo $editor->display( 'bio',  $this->item->bio, '100%', '300', '50', '10', false ) ; ?>
						</div>
					</div>
					<hr>
					<div class="control-group">
						<label for="image" class="control-label">
							<?php echo JText::_('COM_CONFERENCE_FIELD_IMAGE')?>
						</label>
						<div class="controls">
							<input type="file" name="image" id="image" class="span" />
							<span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_IMAGE_DESC')?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>