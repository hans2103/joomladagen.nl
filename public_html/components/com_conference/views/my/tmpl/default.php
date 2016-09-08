<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

$this->loadHelper('params');
$this->loadHelper('format');

$speaker = $this->speaker;

if(empty($speaker->conference_speaker_id)) {
	$editprofileURL = 'index.php?option=com_conference&view=speaker&task=add';
} else {
	$editprofileURL = 'index.php?option=com_conference&view=speaker&task=edit&id='.$speaker->conference_speaker_id;
}
?>

<div class="conference my">
	<div class="row-fluid">
		<div class="span12">
			<h1 class="pull-left">
			<?php if($speaker->title) {
					echo($speaker->title);
				} else {
					echo JFactory::getUser()->name;
				}
			?>
			</h1>
			<a class="btn pull-right " href="<?php echo JRoute::_($editprofileURL)?>">
				<span class="icon-pencil"></span>  <?php echo JText::_('COM_CONFERENCE_MY_EDIT_PROFILE') ?>
			</a>
		</div>
	</div>
		
	<div class="well well-small">
		<div class="row-fluid">
			<div class="span4">
				<span class="thumbnail">
					<?php if($speaker->image):?>
						<img src="<?php echo JURI::root().$speaker->image?>">
					<?php else:?>
						<img src="http://placehold.it/200x200">
					<?php endif;?>
				</span>
				<div class="speakersocial">
					<?php if(($speaker->twitter) && (ConferenceHelperParams::getParam('twitter'))):?>
						<a class="btn btn-small btn-block" target="_blank" href="http://twitter.com/<?php echo $speaker->twitter?>"><span class="icon conference-twitter"></span> <?php echo $speaker->twitter?></a>
					<?php endif;?>
					<?php if(($speaker->facebook) && (ConferenceHelperParams::getParam('facebook'))):?>
						<a class="btn btn-small btn-block" target="_blank" href="http://facebook.com/<?php echo $speaker->facebook?>"><span class="icon conference-facebook"></span> <?php echo $speaker->facebook?></a>
					<?php endif;?>
					<?php if(($speaker->googleplus) && (ConferenceHelperParams::getParam('googleplus'))):?>
						<a class="btn btn-small btn-block" target="_blank" href="http://plus.google.com/<?php echo $speaker->googleplus?>"><span class="icon conference-google-plus"></span> <?php echo $speaker->title?></a>
					<?php endif;?>
					<?php if(($speaker->linkedin) && (ConferenceHelperParams::getParam('linkedin'))):?>
						<a class="btn btn-small btn-block" target="_blank" href="http://www.linkedin.com/in/<?php echo $speaker->linkedin?>"><span class="icon conference-linkedin"></span> <?php echo $speaker->linkedin?></a>
					<?php endif;?>
					<?php if(($speaker->website) && (ConferenceHelperParams::getParam('twitter'))):?>
						<a class="btn btn-small btn-block" target="_blank" href="http://<?php echo $speaker->website?>"><span class="icon conference-earth"></span> <?php echo $speaker->website?></a>
					<?php endif;?>
				</div>
			</div>
			<div class="span8">
				<?php echo ($speaker->bio)?>
			</div>
		</div>
	</div>

	<?php if(!empty($speaker->conference_speaker_id)): ?>
	<div class="row-fluid">
		<div class="span12">
			<h2 class="pull-left"><?php echo JText::_('COM_CONFERENCE_TITLE_SESSIONS') ?></h2>
			<?php if(JFactory::getUser()->authorise('core.create', 'com_conference')) :?>
			<a class="btn pull-right btn-success" href="<?php echo JRoute::_('index.php?option=com_conference&view=session&task=add')?>">
				<span class="icon-plus"></span>  <?php echo JText::_('COM_CONFERENCE_MY_ADD_SESSION') ?>
			</a>
			<?php endif;?>
		</div>
	</div>
	<div class="well well-small">
		<table class="table table-striped">
			<thead>
				<tr>
					<th><?php echo JText::_('COM_CONFERENCE_FIELD_EVENT') ?></th>
					<th><?php echo JText::_('COM_CONFERENCE_FIELD_TITLE') ?></th>
					<?php if(ConferenceHelperParams::getParam('status',0)): ?>
					<th width="10%" class="center"><?php echo JText::_('COM_CONFERENCE_FIELD_STATUS') ?></th>
					<?php endif;?>
					<th width="10%" class="center"><?php echo JText::_('COM_CONFERENCE_FIELD_LEVEL') ?></th>
					<th width="10%" class="center"><?php echo JText::_('COM_CONFERENCE_FIELD_DESCRIPTION') ?></th>
					<th width="10%" class="center"><?php echo JText::_('COM_CONFERENCE_FIELD_SLIDES') ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if(empty($this->sessions)): ?>
			<tr>
				<td colspan="7" class="center">
				<?php echo JText::_('COM_CONFERENCE_NORECORDS') ?>
				</td>
			</tr>
			<?php endif; ?>
			<?php foreach($this->sessions as $session):?>
				<tr>
					<td>
						<?php echo $session->event ?>
					</td>
					<td>
						<?php if(JFactory::getUser()->authorise('core.edit.own', 'com_conference')) :?>
							<a href="<?php echo JRoute::_('index.php?option=com_conference&view=session&task=edit&id='.$session->conference_session_id)?>"><?php echo $session->title ?></a>
						<?php else:?>
							<?php echo $session->title ?>
						<?php endif;?>
					</td>
					<?php if(ConferenceHelperParams::getParam('status',0)): ?>
					<td class="center">
						<?php $status = ConferenceHelperFormat::status($session->status); ?>
						<span class="label <?php echo $status[2] ?>"><?php echo $status[1] ?></span>
					</td>
					<?php endif;?>
					<td class="center">
						<span class="label <?php echo $session->level_label ?>">
							<?php echo $session->level ?>
						</span>
					</td>
					<td class="center">
						<?php if($session->description): ?>
							<span class="badge badge-success"><span class="icon-checkmark"></span></span>
						<?php else:?>
							<span class="badge badge-important"><span class="icon-delete"></span></span>
						<?php endif;?>
					</td>
					<td class="center">
						<?php if($session->slides): ?>
							<span class="badge badge-success"><span class="icon-checkmark"></span></span>
						<?php else:?>
							<span class="badge badge-important"><span class="icon-delete"></span></span>
						<?php endif;?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php endif; ?>
</div>