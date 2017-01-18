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
$this->loadHelper('session');

if($this->item->conference_speaker_id) {
	 $speakers = ConferenceHelperFormat::speakers($this->item->conference_speaker_id);
}
?>
<div class="conference">
	<div class="row-fluid">
		<h1>
			<?php echo $this->escape($this->item->title)?>
			<?php if($this->item->conference_level_id):?>
			<?php $level = ConferenceHelperSession::level($this->item->conference_level_id);?>
			&nbsp;<span class="label <?php echo $level->label ?>">
				<?php echo $level->title ?>
			</span>
			<?php endif;?>
		</h1>
	</div>
	<div class="well well-small">
		<div class="row-fluid">
			<div class="span4">
				<span class="thumbnail">
					<?php if($this->item->conference_speaker_id):?>
						<?php foreach($speakers as $speaker) :?>
						<img src="<?php echo $speaker->image?>">
						<?php endforeach;?>
					<?php else:?>
						<img src="http://placehold.it/200x200">
					<?php endif;?>
				</span>
				<div class="speakersocial">
					<?php
						$sessionspeakers = array();
						foreach($speakers as $speaker) :
							if($speaker->enabled):?>

								<a class="btn btn-small btn-block" href="index.php?option=com_conference&view=speaker&id=<?php echo $speaker->conference_speaker_id?>"><span class="icon icon-user"></span> <?php echo(trim($speaker->title))?></a>
							<?php else:?>
								<span class="btn btn-small btn-block disabled"><span class="icon icon-user"></span> <?php echo(trim($speaker->title))?></span>
						<?php endif;
						endforeach;
					?>
					<?php if($this->item->conference_room_id):?>
						<span class="btn btn-small btn-block disabled">
							<span class="icon icon-home"></span> <?php echo (ConferenceHelperSession::room($this->item->conference_room_id)->title)?>
						</span>
					<?php endif;?>
					<?php if($this->item->conference_slot_id):?>
						<a class="btn btn-small btn-block" href="<?php echo JRoute::_('index.php?option=com_conference&view=days')?>">
							<span class="icon icon-clock"></span> <?php echo (ConferenceHelperSession::slot($this->item->conference_slot_id))?>
						</a>
					<?php endif;?>
					<?php if($this->item->conference_level_id):?>
						<a class="btn btn-small btn-block" href="<?php echo JRoute::_('index.php?option=com_conference&view=levels')?>">
							<span class="icon icon-equalizer"></span> <?php echo (ConferenceHelperSession::level($this->item->conference_level_id)->title)?>
						</a>
					<?php endif;?>
					<?php if((ConferenceHelperParams::getParam('language',0)) && ($this->item->language)):?>
						<span class="btn btn-small btn-block disabled">
							<span class="icon icon-comments-2"></span> <?php echo (ConferenceHelperSession::language($this->item->language))?>
						</span>
					<?php endif;?>
				</div>
			</div>
			<div class="span8">
				<?php echo ($this->item->description)?>

				<?php echo ($this->item->slides)?>
			</div>
		</div>
	</div>
</div>
