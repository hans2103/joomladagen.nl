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
$speakers = array();

if($this->item->conference_speaker_id)
{
	 $speakers = ConferenceHelperFormat::speakers($this->item->conference_speaker_id);
}
?>
<div class="blog">
	<div class="conference card">
		<?php if($this->item->conference_speaker_id):?>
			<?php foreach($speakers as $speaker) :?>
				<div class="card__image">
					<?php echo JHtml::_('image', $speaker->image, $this->escape($speaker->title)); ?>
					<?php if($speaker->enabled):?>

						<a class="btn btn-small btn-block" href="index.php?option=com_conference&view=speaker&id=<?php echo $speaker->conference_speaker_id?>">
							<span class="icon icon-user"></span> <?php echo(trim($speaker->title))?>
						</a>
					<?php else: ?>
						<span class="btn btn-small btn-block disabled"><span class="icon icon-user"></span> <?php echo(trim($speaker->title))?></span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<div class="card__image">
			  <img src="http://placehold.it/200x200">
			</div>
		<?php endif; ?>

		<div class="card__content">
			<div class="card__header">
				<h1>
					<?php echo $this->escape($this->item->title)?>
				</h1>
				<div class="card__actions speakersocial">
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
				</div>
				<?php if($this->item->conference_room_id):?>
					<span class="">
								<span class="icon icon-home"></span> <?php echo (ConferenceHelperSession::room($this->item->conference_room_id)->title)?>
							</span>
				<?php endif;?>
				<?php if((ConferenceHelperParams::getParam('language',0)) && ($this->item->language)):?>
					<span class="">
								<span class="icon icon-comments-2"></span> <?php echo (ConferenceHelperSession::language($this->item->language))?>
							</span>
				<?php endif;?>
			</div>

			<div class="card__body">
				<?php echo($this->item->description) ?>
			</div>
			<div class="card_presentations">
				<?php echo ($this->item->slides)?>
			</div>
		</div>
	</div>
</div>