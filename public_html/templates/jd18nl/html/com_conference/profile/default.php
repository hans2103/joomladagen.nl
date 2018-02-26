<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://stichtingsympathy.nl
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

$params         = ComponentHelper::getParams('com_conference');
$this->item     = $this->profile;
$returnUrl      = base64_encode(Uri::root() . Route::_('index.php?option=com_conference&view=profile', false));
$editprofileURL = 'index.php?option=com_conference&conference_speaker_id=' . $this->item->conference_speaker_id . '&return=' . $returnUrl;
$task           = '&task=speaker.edit';

// @todo Check if we need to entertain the add task
if (!$this->item->conference_speaker_id)
{
	$task = '&task=speaker.add';
}

$editprofileURL .= $task;

$this->template = Factory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

$array = array(
	'title' => $this->item->title ? $this->item->title : Factory::getUser()->name
);

echo JLayouts::render('template.content.header', $array);
?>
<section class="section__wrapper">
    <div class="container">
        <div class="article__item">
            <div class="article__image">
                <div class="media-placeholder media-placeholder--1by1">
					<?php $src = $this->item->image ? $this->item->image : 'http://placehold.it/200x200'; ?>
					<?php $alt = 'foto van spreker ' . ($this->item->title ? $this->item->title : Factory::getUser()->name); ?>
					<?php echo JLayouts::render('template.image', array('img' => $src, 'alt' => $alt)); ?>
                </div>
            </div>
            <div class="article__body">
				<p><?php
				$url  = Route::_($editprofileURL);
				$text = '<span class="icon-pencil"></span> ' . Text::_('COM_CONFERENCE_MY_EDIT_PROFILE');
				echo HTMLHelper::_('link', $url, $text);
				?></p>

				<?php echo($this->item->bio) ?>

                <div class="article__social">
					<?php
					if (($this->item->twitter) && ($params->get('twitter'))):
						$src  = 'https://twitter.com/' . $this->item->twitter;
						$text = '<span class="icon conference-twitter"></span>' . $this->item->twitter;
						echo HTMLHelper::_('link', $src, $text);
					endif;

					if (($this->item->facebook) && ($params->get('facebook'))):
						$src  = 'https://facebook.com/' . $this->item->facebook;
						$text = '<span class="icon conference-facebook"></span>' . $this->item->facebook;
						echo HTMLHelper::_('link', $src, $text);
					endif;

					if (($this->item->googleplus) && ($params->get('googleplus'))):
						$src  = 'https://plus.google.com/' . $this->item->googleplus;
						$text = '<span class="icon conference-google-plus"></span>' . $this->item->title;
						echo HTMLHelper::_('link', $src, $text);
					endif;

					if (($this->item->linkedin) && ($params->get('linkedin'))):
						$src  = 'https://www.linkedin.com/in/' . $this->item->linkedin;
						$text = '<span class="icon conference-linkedin"></span>' . $this->item->linkedin;
						echo HTMLHelper::_('link', $src, $text);
					endif;

					if (($this->item->website) && ($params->get('website'))):
						$src  = 'http://' . $this->item->website;
						$text = '<span class="icon conference-earth"></span>' . $this->item->website;
						echo HTMLHelper::_('link', $src, $text);
					endif;
					?>
                </div>

				<?php if ($this->item->conference_speaker_id) : ?>
                    <h2><?php echo JText::_('COM_CONFERENCE_TITLE_SESSIONS') ?></h2>
					<?php
					if ($this->canDo->get('core.create')) :
						$url  = Route::_('index.php?option=com_conference&view=sessions&task=edit&layout=edit');
						$text = '<span class="icon-plus"></span> ' . Text::_('COM_CONFERENCE_MY_ADD_SESSION');
						echo HTMLHelper::_('link', $url, $text);
					endif;
					?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th><?php echo Text::_('COM_CONFERENCE_FIELD_EVENT') ?></th>
                                <th width="25%"><?php echo Text::_('COM_CONFERENCE_FIELD_SLOT') ?></th>
                                <th><?php echo Text::_('COM_CONFERENCE_FIELD_TITLE') ?></th>
                                <th width="10%" class="center"><?php echo Text::_('COM_CONFERENCE_FIELD_LEVEL') ?></th>
                                <th width="10%"
                                    class="center"><?php echo Text::_('COM_CONFERENCE_FIELD_DESCRIPTION') ?></th>
                                <th width="10%" class="center"><?php echo Text::_('COM_CONFERENCE_FIELD_SLIDES') ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php if (empty($this->sessions)): ?>
                                <tr>
                                    <td colspan="7" class="center">
										<?php echo Text::_('COM_CONFERENCE_NORECORDS') ?>
                                    </td>
                                </tr>
							<?php endif; ?>
							<?php foreach ($this->sessions as $session): ?>
                                <tr>
                                    <td>
										<?php echo $session->event ?>
                                    </td>
                                    <td>
										<?php echo HTMLHelper::_('date', $session->date, 'l j F'); ?>
                                        <br/>
                                        <span aria-hidden="true" class="icon-clock"></span>
										<?php echo HTMLHelper::_('date', $session->start_time, 'H:i') ?>
                                        - <?php echo HTMLHelper::_('date', $session->end_time, 'H:i') ?>
                                    </td>
                                    <td>
										<?php
										if ($this->canDo->get('core.edit.own')) :
											$url  = Route::_('index.php?option=com_conference&view=sessions&task=edit&layout=edit&id=' . $session->conference_session_id);
											$text = $session->title;
											echo HTMLHelper::_('link', $url, $text);
										else :
											echo $session->title;
										endif;
										?>
                                    </td>
                                    <td class="center">
                                        <span class="label <?php echo $session->level_label ?>"><?php echo $session->level ?></span>
                                    </td>
                                    <td class="center">
										<?php if ($session->description): ?>
                                            <span class="badge badge-success"><span
                                                        class="icon-checkmark"></span></span>
										<?php else: ?>
                                            <span class="badge badge-important"><span class="icon-delete"></span></span>
										<?php endif; ?>
                                    </td>
                                    <td class="center">
										<?php if ($session->slides): ?>
                                            <span class="badge badge-success"><span
                                                        class="icon-checkmark"></span></span>
										<?php else: ?>
                                            <span class="badge badge-important"><span class="icon-delete"></span></span>
										<?php endif; ?>
                                    </td>
                                </tr>
							<?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
				<?php endif; ?>
            </div>
        </div>
    </div>
</section>