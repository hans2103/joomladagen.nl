<?php
/*
 * @package		Conference Schedule Manager
 * @copyright	Copyright (c) 2013-2014 Sander Potjer / sanderpotjer.nl
 * @license		GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

$this->loadHelper('params');
$this->loadHelper('modules');
$this->loadHelper('format');

// Load Template helper
$this->template = JFactory::getApplication()->getTemplate();
JHtml::addIncludePath(JPATH_THEMES . '/' . $this->template . '/helper.php');

?>
<div class="blog">
    <div class="conference card">
		<?php
		if (isset($this->item->image) && !empty($this->item->image))
		{
			echo '<div class="card__image">';
			echo JHtml::_('image', $this->item->image, $this->escape($this->item->title));
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
                <h1><?php echo $this->escape($this->item->title); ?></h1>
            </div>

            <div class="card__body">
				<?php echo($this->item->bio) ?>
            </div>

            <div class="card__actions speakersocial">
				<?php
				if (($this->item->twitter) && (ConferenceHelperParams::getParam('twitter')))
				{
					$socialUrl   = 'http://twitter.com/';
					$socialId    = $this->item->twitter;
					$socialText  = '<span class="speakersocial__text">' . $this->item->twitter . '</span>';
					$socialClass = 'speakersocial__icon speakersocial__icon--twitter';
					echo JHtml::_('link', $socialUrl . $socialId, $socialText, array("class" => $socialClass));
				}
				?>
				<?php
				if (($this->item->facebook) && (ConferenceHelperParams::getParam('facebook')))
				{
					$socialUrl   = 'http://facebook.com/';
					$socialId    = $this->item->facebook;
					$socialText  = '<span class="speakersocial__text">' . $this->item->facebook . '</span>';
					$socialClass = 'speakersocial__icon speakersocial__icon--facebook';
					echo JHtml::_('link', $socialUrl . $socialId, $socialText, array("class" => $socialClass));
				}
				?>
				<?php
				if (($this->item->googleplus) && (ConferenceHelperParams::getParam('googleplus')))
				{
					$socialUrl   = 'http://plus.google.com/';
					$socialId    = $this->item->googleplus;
					$socialText  = '<span class="speakersocial__text">' . $this->item->googleplus . '</span>';
					$socialClass = 'speakersocial__icon speakersocial__icon--googleplus';
					echo JHtml::_('link', $socialUrl . $socialId, $socialText, array("class" => $socialClass));
				}
				?>
				<?php
				if (($this->item->linkedin) && (ConferenceHelperParams::getParam('linkedin')))
				{
					$socialUrl   = 'http://www.linkedin.com/in/';
					$socialId    = $this->item->linkedin;
					$socialText  = '<span class="speakersocial__text">' . $this->item->linkedin . '</span>';
					$socialClass = 'speakersocial__icon speakersocial__icon--linkedin';
					echo JHtml::_('link', $socialUrl . $socialId, $socialText, array("class" => $socialClass));
				}
				?>
				<?php
				if (($this->item->website) && (ConferenceHelperParams::getParam('website')))
				{
					$socialUrl   = 'http://';
					$socialId    = $this->item->website;
					$socialText  = '<span class="speakersocial__text">' . $this->item->website . '</span>';
					$socialClass = 'speakersocial__icon speakersocial__icon--website';
					echo JHtml::_('link', $socialUrl . $socialId, $socialText, array("class" => $socialClass));
				}
				?>

            </div>

            <div class="card_presentations">
				<?php if ($this->sessions): ?>
                    <h4><?php echo JText::_('COM_CONFERENCE_TITLE_SESSIONS') ?></h4>
                    <table class="table table-striped">
                        <tbody>
						<?php foreach ($this->sessions as $session): ?>
                            <tr>
                                <td>
									<?php if ($session->listview): ?>
                                        <a href="<?php echo JRoute::_('index.php?option=com_conference&view=session&id=' . $session->conference_session_id) ?>">
											<?php echo($session->title) ?>
                                        </a>
									<?php else: ?>
										<?php echo($session->title) ?>
									<?php endif; ?>
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
