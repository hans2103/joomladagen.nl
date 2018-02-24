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

defined('_JEXEC') or die;

$params = ComponentHelper::getParams('com_conference');

$this->template = Factory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

$array = array(
	'title' => Text::_('COM_CONFERENCE_LEVELS_TITLE')
);

echo JLayouts::render('template.content.header', $array);
?>
<section class="section__wrapper">
    <div class="container">
		<?php if (!empty($this->items)): ?>
            <div class="article__item">
				<?php foreach ($this->items as $item): ?>
                    <h2>Niveau <?php echo strtolower($this->escape($item->title)); ?></h2>
                    <p><span class="label <?php echo $item->label ?>"><?php echo $this->escape($item->title) ?></span></p>
                    <?php echo($item->description); ?>

					<?php if (!empty($item->sessions)) : ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th><?php echo JText::_('COM_CONFERENCE_FIELD_SESSION') ?></th>
                                    <th width="30%"><?php echo Text::_('COM_CONFERENCE_FIELD_SPEAKER') ?></th>
                                    <th width="25%"><?php echo Text::_('COM_CONFERENCE_FIELD_SLOT') ?></th>
                                </tr>
                                </thead>
                                <tbody>
								<?php foreach ($item->sessions as $session) : ?>
                                    <tr>
                                        <td>
											<?php
											if ($session->listview):
												echo HTMLHelper::_('link', Route::_('index.php?option=com_conference&view=sessions&id=' . $session->conference_session_id), $session->title);
											else:
												echo($session->title);
											endif;

											if ($params->get('language', 0)):
												if ($session->language == 'en'):
													echo ' ' . HTMLHelper::_('image', 'media/mod_languages/images/' . $session->language . '.gif', 'language flag ' . $session->language, array('class' => 'lang'));
												endif;
											endif;
											?>
                                        </td>
                                        <td>
											<?php if (!empty($session->speakers)): ?>
												<?php
												$sessionspeakers = array();

												foreach ($session->speakers as $speaker)
												{
													if ($speaker->enabled)
													{
														$sessionspeakers[] = HTMLHelper::_('link', 'index.php?option=com_conference&view=speakers&id=' . $speaker->conference_speaker_id, trim($speaker->title));
													}
													else
													{
														$sessionspeakers[] = trim($speaker->title);
													}
												}
												?>
                                                <div class="speaker">
													<?php echo implode(', ', $sessionspeakers); ?>
                                                </div>
											<?php endif; ?>
                                        </td>
                                        <td>
											<?php echo HTMLHelper::_('date', $session->date, 'l j F'); ?>
                                            <br/>
                                            <span aria-hidden="true" class="icon-clock"></span>
											<?php echo HTMLHelper::_('date', $session->start_time, 'H:i') ?>
                                            - <?php echo HTMLHelper::_('date', $session->end_time, 'H:i') ?>
                                        </td>
                                    </tr>
								<?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
					<?php endif; ?>
				<?php endforeach; ?>
            </div>
		<?php endif; ?>
    </div>
</section>