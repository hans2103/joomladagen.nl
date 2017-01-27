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
$this->loadHelper('message');
$this->loadHelper('schedule');


JFactory::getApplication()->enqueueMessage(JText::_('COM_CONFERENCE_DAYS_NOTICE_TEXT'), 'info');
?>

<div class="conference schedule">
	<?php if ($this->params->get('show_page_heading', 0)) : ?>
        <div class="row-fluid">
            <h1><?php echo JText::_('COM_CONFERENCE_DAYS_TITLE') ?></h1>
        </div>
	<?php endif; ?>

    <ul class="accordion-tabs-minimal">
		<?php if (!empty($this->items)) foreach ($this->items as $i => $item): ?>
            <li class="tab-header-and-content tab-header-and-content--<?php echo $item->slug ?>">
                <a href="#"
                   class="tab-link<?php /*if ($i == 1): ?> is-active<?php endif;*/ ?>"><?php echo $item->title ?></a>
                <div class="tab-content">
					<?php
					$slots = ConferenceHelperSchedule::slots($item->conference_day_id);
					$rooms = ConferenceHelperSchedule::rooms();
					?>
                    <table class="table table-bordered">
                        <thead class="hidden-phone">
                        <tr>
                            <th width="10%"></th>
							<?php if ($item->title === "Vrijdag 31 maart") : ?>
                                <th width="90%">R2H zaal</th>
							<?php else: ?>
								<?php if (!empty($rooms)) foreach ($rooms as $room): ?>
                                    <th width="<?php echo(90 / count($rooms)); ?>%"><?php echo $room->title ?></th>
								<?php endforeach; ?>
							<?php endif; ?>
                        </tr>
                        </thead>

                        <tbody>
						<?php if (!empty($slots)) foreach ($slots as $slot): ?>
							<?php if ($slot->general): ?>
                                <tr class="info">
                                    <td class="hidden-phone"><?php echo JHtml::_('date', $slot->start_time, 'H:i'); ?></td>
                                    <td colspan="<?php echo(count($rooms)); ?>">
								<span class="visible-phone">
									<?php echo JHtml::_('date', $slot->start_time, 'H:i'); ?>:
								</span>
										<?php if (isset($this->sessions[$slot->conference_slot_id][ConferenceHelperSchedule::generalroom()])) : ?>
											<?php $session = $this->sessions[$slot->conference_slot_id][ConferenceHelperSchedule::generalroom()]; ?>
											<?php if ($session->listview): ?>
                                                <a href="<?php echo JRoute::_('index.php?option=com_conference&view=session&id=' . $session->conference_session_id) ?>"><?php echo $session->title ?></a>
											<?php else: ?>
												<?php echo $session->title ?>
											<?php endif; ?>

										<?php endif; ?>
                                    </td>
                                </tr>
							<?php else: ?>
								<?php if ($item->title === "Vrijdag 31 maart") : ?>
                                    <tr>
                                        <td><?php echo JHtml::_('date', $slot->start_time, 'H:i'); ?></td>
                                        <td>
	                                        <?php $session = $this->sessions[$slot->conference_slot_id][$rooms[1]->conference_room_id]; ?>
                                             <span class="visible-phone roomname"><?php echo $session->room ?></span>
                                            <div class="session">
		                                        <?php if ($session->listview): ?>
			                                        <?php if ($session->slides): ?>
                                                        <span class="icon-grid-view" rel="tooltip"
                                                              data-original-title="<?php echo JText::_('COM_CONFERENCE_SLIDES_AVAILABLE') ?>"></span>
			                                        <?php endif; ?>
                                                    <a href="<?php echo JRoute::_('index.php?option=com_conference&view=session&id=' . $session->conference_session_id) ?>"><?php echo $session->title ?></a>
		                                        <?php else: ?>
			                                        <?php echo $session->title ?>
		                                        <?php endif; ?>
		                                        <?php if (ConferenceHelperParams::getParam('language', 0)): ?>
			                                        <?php if ($session->language == 'en'): ?>
                                                        <img class="lang"
                                                             src="media/mod_languages/images/<?php echo($session->language) ?>.gif"/>
			                                        <?php endif; ?>
		                                        <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
								<?php else: ?>
                                <tr>
                                    <td><?php echo JHtml::_('date', $slot->start_time, 'H:i'); ?></td>
									<?php if (!empty($rooms)) foreach ($rooms as $room): ?>
										<?php if (isset($this->sessions[$slot->conference_slot_id][$room->conference_room_id])) : ?>
                                            <td>
												<?php $session = $this->sessions[$slot->conference_slot_id][$room->conference_room_id]; ?>
                                                <span class="visible-phone roomname">
										<?php echo $session->room ?>
									</span>
												<?php if ($session->level): ?>
                                                    <a href="<?php echo JRoute::_('index.php?option=com_conference&view=levels') ?>"><span
                                                                class="label <?php echo $session->level_label ?>">
										<?php echo $session->level ?>
									</span></a><br/>
												<?php endif; ?>
                                                <div class="session">
													<?php if ($session->listview): ?>
														<?php if ($session->slides): ?>
                                                            <span class="icon-grid-view" rel="tooltip"
                                                                  data-original-title="<?php echo JText::_('COM_CONFERENCE_SLIDES_AVAILABLE') ?>"></span>
														<?php endif; ?>
                                                        <a href="<?php echo JRoute::_('index.php?option=com_conference&view=session&id=' . $session->conference_session_id) ?>"><?php echo $session->title ?></a>
													<?php else: ?>
														<?php echo $session->title ?>
													<?php endif; ?>
													<?php if (ConferenceHelperParams::getParam('language', 0)): ?>
														<?php if ($session->language == 'en'): ?>
                                                            <img class="lang"
                                                                 src="media/mod_languages/images/<?php echo($session->language) ?>.gif"/>
														<?php endif; ?>
													<?php endif; ?>
                                                </div>
												<?php if ($session->conference_speaker_id): ?>
													<?php $speakers = ConferenceHelperFormat::speakers($session->conference_speaker_id); ?>
													<?php if (!empty($speakers)): ?>
														<?php
														$sessionspeakers = array();
														foreach ($speakers as $speaker) :
															if ($speaker->enabled)
															{
																$sessionspeakers[] = '<span class="icon-user"></span> <a href="index.php?option=com_conference&view=speaker&id=' . $speaker->conference_speaker_id . '">' . trim($speaker->title) . '</a>';
															}
															else
															{
																$sessionspeakers[] = '<span class="icon-user"></span> ' . trim($speaker->title);
															}
														endforeach;
														?>
                                                        <div class="speaker">
                                                            <small><?php echo implode('<br/> ', $sessionspeakers); ?></small>
                                                        </div>
													<?php endif; ?>
												<?php endif; ?>
                                            </td>
										<?php else: ?>
                                            <td class="hidden-phone"></td>
										<?php endif; ?>
									<?php endforeach; ?>
                                </tr>
								<?php endif; ?>
							<?php endif; ?>
						<?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </li>
		<?php endforeach; ?>
    </ul>

	<?php /*    <script>
        jQuery(document).ready(function ($) {
            $('.accordion-tabs-minimal').each(function (index) {
                $(this).children('li').first().children('a').addClass('is-active').next().addClass('is-open').show();
            });
            $('.accordion-tabs-minimal').on('click', 'li > a.tab-link', function (event) {
                if (!$(this).hasClass('is-active')) {
                    event.preventDefault();
                    var accordionTabs = $(this).closest('.accordion-tabs-minimal');
                    accordionTabs.find('.is-open').removeClass('is-open').hide();

                    $(this).next().toggleClass('is-open').toggle();
                    accordionTabs.find('.is-active').removeClass('is-active');
                    $(this).addClass('is-active');
                } else {
                    event.preventDefault();
                }
            });
        });
    </script>*/ ?>

</div>
