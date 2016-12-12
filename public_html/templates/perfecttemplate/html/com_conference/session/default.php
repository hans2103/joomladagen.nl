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

if ($this->item->conference_speaker_id)
{
	$speakers = ConferenceHelperFormat::speakers($this->item->conference_speaker_id);
}
?>
<div class="blog">
    <div class="conference card">
		<?php if ($this->item->conference_speaker_id): ?>
			<?php foreach ($speakers as $speaker) : ?>
                <div class="card__image">
					<?php echo JHtml::_('image', $speaker->image, $this->escape($speaker->title)); ?>
					<?php if ($speaker->enabled): ?>
						<?php echo JHtml::_('link', JRoute::_('index.php?option=com_conference&view=speaker&id=' . $speaker->conference_speaker_id), trim($speaker->title)); ?>
					<?php else: ?>
						<?php echo(trim($speaker->title)); ?>
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
					<?php echo $this->escape($this->item->title) ?>
                </h1>
                <div class="card__meta">
					<?php
					if ($this->item->conference_slot_id)
					{
						echo JHtml::_('link', JRoute::_('index.php?option=com_conference&view=days'), ConferenceHelperSession::slot($this->item->conference_slot_id), array('class' => 'card__meta-item card__meta-item--slot'));
					}

					if ($this->item->conference_level_id)
					{
						echo JHtml::_('link', JRoute::_('index.php?option=com_conference&view=levels'), JText::_('COM_CONFERENCE_FIELD_LEVEL') . ": " . ConferenceHelperSession::level($this->item->conference_level_id)->title, array('class' => 'card__meta-item card__meta-item--level'));

					}

					if ($this->item->conference_room_id)
					{
						echo '<span class="card__meta-item card__meta-item--room">';
						echo '  ' . ConferenceHelperSession::room($this->item->conference_room_id)->title;
						echo '</span>';
					}

					if ((ConferenceHelperParams::getParam('language', 0)) && ($this->item->language))
					{
						echo '<span class="card__meta-item card__meta-item--language">';
						echo '  ' . ConferenceHelperSession::language($this->item->language);
						echo '</span>';
					}
					?>
					</div >
            </div >

            <div class="card__body" >
				<?php echo($this->item->description) ?>
                </div>

                <div class="card__presentations">
					<?php echo($this->item->slides) ?>
                </div>
            </div>
        </div>
    </div>
