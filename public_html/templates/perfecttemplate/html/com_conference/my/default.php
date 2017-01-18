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

if (empty($speaker->conference_speaker_id))
{
	$editprofileURL = 'index.php?option=com_conference&view=speaker&task=add';
}
else
{
	$editprofileURL = 'index.php?option=com_conference&view=speaker&task=edit&id=' . $speaker->conference_speaker_id;
}
?>


<div class="blog">
    <div class="conference card conference__my">
		<?php
		if (isset($speaker->image) && !empty($speaker->image))
		{
			echo '<div class="card__image">';
			echo JHtml::_('image', $speaker->image, $this->escape($speaker->title));
			echo '</div>';
		}
		else
		{
			echo '<div class="card__image">';
			echo '  <img src="http://placehold.it/200x200">';
			echo '</div>';
		}
		?>


        <div class="card__content">
            <div class="card__header">
                <h1><?php echo $this->escape($speaker->title) ? $this->escape($speaker->title) : JFactory::getUser()->name; ?></h1>

				<?php echo JHtml::_('link', JRoute::_($editprofileURL), JText::_('COM_CONFERENCE_MY_EDIT_PROFILE'), array('class' => 'btn')); ?>
            </div>

            <div class="card__body">
				<?php echo($speaker->bio) ?>
            </div>

            <div class="card__actions speakersocial">
				<?php
				if (($speaker->twitter) && (ConferenceHelperParams::getParam('twitter')))
				{
					$socialUrl   = 'http://twitter.com/';
					$socialId    = $speaker->twitter;
					$socialText  = '<span class="speakersocial__text">' . $speaker->twitter . '</span>';
					$socialClass = 'speakersocial__icon speakersocial__icon--twitter';
					echo JHtml::_('link', $socialUrl . $socialId, $socialText, array("class" => $socialClass));
				}
				?>
				<?php
				if (($speaker->facebook) && (ConferenceHelperParams::getParam('facebook')))
				{
					$socialUrl   = 'http://facebook.com/';
					$socialId    = $speaker->facebook;
					$socialText  = '<span class="speakersocial__text">' . $speaker->facebook . '</span>';
					$socialClass = 'speakersocial__icon speakersocial__icon--facebook';
					echo JHtml::_('link', $socialUrl . $socialId, $socialText, array("class" => $socialClass));
				}
				?>
				<?php
				if (($speaker->googleplus) && (ConferenceHelperParams::getParam('googleplus')))
				{
					$socialUrl   = 'http://plus.google.com/';
					$socialId    = $speaker->googleplus;
					$socialText  = '<span class="speakersocial__text">' . $speaker->googleplus . '</span>';
					$socialClass = 'speakersocial__icon speakersocial__icon--googleplus';
					echo JHtml::_('link', $socialUrl . $socialId, $socialText, array("class" => $socialClass));
				}
				?>
				<?php
				if (($speaker->linkedin) && (ConferenceHelperParams::getParam('linkedin')))
				{
					$socialUrl   = 'http://www.linkedin.com/in/';
					$socialId    = $speaker->linkedin;
					$socialText  = '<span class="speakersocial__text">' . $speaker->linkedin . '</span>';
					$socialClass = 'speakersocial__icon speakersocial__icon--linkedin';
					echo JHtml::_('link', $socialUrl . $socialId, $socialText, array("class" => $socialClass));
				}
				?>
				<?php
				if (($speaker->website) && (ConferenceHelperParams::getParam('website')))
				{
					$socialUrl   = 'http://';
					$socialId    = $speaker->website;
					$socialText  = '<span class="speakersocial__text">' . $speaker->website . '</span>';
					$socialClass = 'speakersocial__icon speakersocial__icon--website';
					echo JHtml::_('link', $socialUrl . $socialId, $socialText, array("class" => $socialClass));
				}
				?>

            </div>

            <div class="card__presentations">
				<?php if ($this->sessions): ?>
                    <h4><?php echo JText::_('COM_CONFERENCE_TITLE_SESSIONS') ?></h4>
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th width="10%"><?php echo JText::_('COM_CONFERENCE_FIELD_EVENT') ?></th>
                            <th ><?php echo JText::_('COM_CONFERENCE_FIELD_TITLE') ?></th>
							<?php if (ConferenceHelperParams::getParam('status', 0)): ?>
                                <th width="10%"
                                    class="center"><?php echo JText::_('COM_CONFERENCE_FIELD_STATUS') ?></th>
							<?php endif; ?>
                            <th width="12%" class="center"><?php echo JText::_('COM_CONFERENCE_FIELD_LEVEL') ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php if (empty($this->sessions)): ?>
                            <tr>
                                <td colspan="99" class="center">
									<?php echo JText::_('COM_CONFERENCE_NORECORDS') ?>
                                </td>
                            </tr>
						<?php endif; ?>
						<?php foreach ($this->sessions as $session): ?>
                            <tr>
                                <td>
									<?php echo $session->event ?>
                                </td>
                                <td>
									<?php
									if (JFactory::getUser()->authorise('core.edit.own', 'com_conference')) :
										echo JHtml::_('link', JRoute::_('index.php?option=com_conference&view=session&task=edit&id=' . $session->conference_session_id), $session->title);
									else:
										echo $session->title;
									endif; ?>
                                </td>
								<?php if (ConferenceHelperParams::getParam('status', 0)): ?>
                                    <td class="center">
										<?php $status = ConferenceHelperFormat::status($session->status); ?>
                                        <span class="label <?php echo $status[2] ?>"><?php echo $status[1] ?></span>
                                    </td>
								<?php endif; ?>
                                <td class="center">
						<span class="label <?php echo $session->level_label ?>">
							<?php echo $session->level ?>
						</span>
                                </td>
                            </tr>
						<?php endforeach; ?>
                        </tbody>
                    </table>
				<?php endif; ?>
            </div>
        </div>
    </div>
</div>
