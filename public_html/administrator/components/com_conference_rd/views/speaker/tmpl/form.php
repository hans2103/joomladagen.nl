<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

JHTML::_('behavior.tooltip');
if(version_compare(JVERSION, '3.0', 'ge')) {
	JHTML::_('behavior.framework');
} else {
	JHTML::_('behavior.mootools');
}
JHTML::_('behavior.modal');


// Build the script.
$script = array();
$script[] = '	function jInsertFieldValue(value, id) {';
$script[] = '		var old_value = document.id(id).value;';
$script[] = '		if (old_value != value) {';
$script[] = '			var elem = document.id(id);';
$script[] = '			elem.value = value;';
$script[] = '			elem.fireEvent("change");';
$script[] = '			if (typeof(elem.onchange) === "function") {';
$script[] = '				elem.onchange();';
$script[] = '			}';
$script[] = '			jMediaRefreshPreview(id);';
$script[] = '		}';
$script[] = '	}';

$script[] = '	function jMediaRefreshPreview(id) {';
$script[] = '		var value = document.id(id).value;';
$script[] = '		var img = document.id(id + "_preview");';
$script[] = '		if (img) {';
$script[] = '			if (value) {';
$script[] = '				img.src = "' . JURI::root() . '" + value;';
$script[] = '				document.id(id + "_preview_empty").setStyle("display", "none");';
$script[] = '				document.id(id + "_preview_img").setStyle("display", "");';
$script[] = '			} else { ';
$script[] = '				img.src = ""';
$script[] = '				document.id(id + "_preview_empty").setStyle("display", "");';
$script[] = '				document.id(id + "_preview_img").setStyle("display", "none");';
$script[] = '			} ';
$script[] = '		} ';
$script[] = '	}';

$script[] = '	function jMediaRefreshPreviewTip(tip)';
$script[] = '	{';
$script[] = '		tip.setStyle("display", "block");';
$script[] = '		var img = tip.getElement("img.media-preview");';
$script[] = '		var id = img.getProperty("id");';
$script[] = '		id = id.substring(0, id.length - "_preview".length);';
$script[] = '		jMediaRefreshPreview(id);';
$script[] = '	}';

// Add the script to the document head.
JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

$this->loadHelper('params');
$this->loadHelper('select');
$this->loadHelper('format');

// Joomla! editor object
$editor = JFactory::getEditor();

?>

<div class="conference">
	<form action="index.php" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
		<input type="hidden" name="option" value="com_conference" />
		<input type="hidden" name="view" value="speaker" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="conference_speaker_id" value="<?php echo $this->item->conference_speaker_id ?>" />
		<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />
		
		<!-- Start row -->
		<div class="row-fluid">
			<!-- Start left -->
			<div class="span7">
				<div class="control-group">
					<label for="title" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_NAME')?>
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
					<label for="user_id" class="control-label">
						<?php echo JText::_('JGLOBAL_USERNAME')?>
					</label>
					<div class="controls">
						<input type="hidden" name="user_id" id="userid" value="<?php echo $this->item->user_id?>" />
						<input type="text" class="input-medium" name="xxx_userid" id="userid_visible" value="<?php echo $this->item->user_id ? JFactory::getUser($this->item->user_id)->username : '' ?>" disabled="disabled" />
						<button onclick="return false;" class="btn modal"><?php echo JText::_('COM_CONFERENCE_FIELD_SELECT_USER')?></button>
						<a class="modal" style="display: none" id="userselect" href="index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=userid" rel="{handler: 'iframe', size: {x: 800, y: 500}}">Select</a>
					</div>
				</div>
				<div class="control-group">
					<label for="email" class="control-label">
						<?php echo JText::_('JGLOBAL_EMAIL')?>
					</label>
					<div class="controls">
						<input type="text" name="email" id="email" class="span" placeholder="mail@website.com" value="<?php echo $this->item->email?>"/>
					</div>
				</div>
				<hr>
				<div class="control-group">
					<label for="conference_event_id" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_EVENT'); ?>
					</label>	
					<div class="controls">
						<?php echo ConferenceHelperSelect::multi_events(explode(',',$this->item->conference_event_id)); ?>
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
						<?php echo $editor->display( 'bio',  $this->item->bio, '100%', '150', '50', '10', false ) ; ?>
					</div>
				</div>
				<hr>
				<div class="control-group">
					<label for="image" class="control-label">
						<?php echo JText::_('COM_CONFERENCE_FIELD_IMAGE')?>
					</label>
					<div class="controls">
						<div id="image_preview_empty" style="<?php if($this->item->image):?>display:none<?php endif;?>">
							<img class="thumbnail" src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&text=no+image" />
						</div>
						<div id="image_preview_img">
							<img class="media-preview thumbnail" id="image_preview" src="<?php echo JURI::root().$this->item->image?>">
						</div>
						<a class="btn btn-primary modal" rel="{handler: 'iframe', size: {x: 800, y: 500}}" href="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;asset=com_conference&amp;author=870&amp;fieldid=image&amp;folder=speakers"><?php echo JText::_('JSELECT')?></a>
						<a class="btn" onclick="jInsertFieldValue('', 'image');return false;" href="#"><?php echo JText::_('JSEARCH_FILTER_CLEAR')?></a>
						<input type="hidden" size="40" readonly="readonly" value="<?php echo $this->item->image?>" id="image" name="image">
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
				
				<div class="well iteminfo">
					<div class="control-group">
						<h3><?php echo JText::_('COM_CONFERENCE_FIELD_NOTES_SPEAKER')?></h3>
						<textarea name="notes" id="notes" rows="4" class="span"><?php echo $this->item->speakernotes?></textarea>
						<span class="help-block"><?php echo JText::_('COM_CONFERENCE_FIELD_NOTES_SPEAKER_DESC')?></span>
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

<script type="text/javascript">
function jSelectUser_userid(id, username)
{
	document.getElementById('userid').value = id;
	document.getElementById('userid_visible').value = username;
	try {
		document.getElementById('sbox-window').close();	
	} catch(err) {
		SqueezeBox.close();
	}
}

window.addEvent("domready", function() {
	$$("button.modal").each(function(el) {
		el.addEvent("click", function(e) {
			try {
				new Event(e).stop();
			} catch(anotherMTUpgradeIssue) {
				try {
					e.stop();
				} catch(WhateverIsWrongWithYouIDontCare) {
					try {
						DOMEvent(e).stop();
					} catch(NoBleepinWay) {
						alert('If you see this message, your copy of Joomla! is FUBAR');
					}
				}
			}
			SqueezeBox.fromElement($('userselect'), {
				parse: 'rel'
			});
		});
	});
});
</script>