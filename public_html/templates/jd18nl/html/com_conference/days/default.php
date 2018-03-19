<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://stichtingsympathy.nl
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

$params = JComponentHelper::getParams('com_conference');

$this->template = Factory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers');

$array = array(
	'title' => Text::_('COM_CONFERENCE_DAYS_TITLE'),
	'intro' => (($this->items[0]->description) ? $this->items[0]->description : '')
);

echo JLayouts::render('template.content.header', $array);
?>
<section class="section__wrapper">
    <div class="container">
        <div class="content">
			<?php if (!empty($this->items)) : ?>
                <div class="tabs">

                    <div role="tablist" aria-label="Programma">
						<?php foreach ($this->items as $key => &$item) : ?>
							<?php $item->alias = str_replace(' ', '-', strtolower($item->title)); ?>
                            <button role="tab"
                                    aria-selected="<?php echo $key == 0 ? 'true' : 'false'; ?>"
                                    aria-controls="<?php echo $item->alias; ?>-tab"
                                    id="<?php echo $item->alias; ?>"
	                                <?php echo $key != 0 ? ' tabindex="-1"' : ''; ?>>
								<?php echo $item->title; ?>
                            </button>
						<?php endforeach; ?>
                    </div>
					<?php foreach ($this->items as $key => &$item) : ?>
                        <div    tabindex="0"
                                role="tabpanel"
                                id="<?php echo $item->alias; ?>-tab"
                                aria-labelledby="<?php echo $item->alias; ?>"
                                <?php echo $key == 0 ? '' : 'hidden'; ?>>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="hidden-phone">
                                    <tr>
                                        <th width="10%"></th>
										<?php
										if (!empty($this->rooms)):
											foreach ($this->rooms as $room):
												echo '<th width="' . (90 / count($this->rooms)) . '%">';
												echo $room->title . '<br>';
												echo '<small>' . $room->subtitle . '</small>';
												echo '</th>';
											endforeach;
										endif;
										?>
                                    </tr>
                                    </thead>

                                    <tbody>
									<?php if (!empty($item->slots)): ?>
										<?php foreach ($item->slots as $slot) : ?>
											<?php if ($slot->general): ?>
                                                <tr class="info">
                                                    <td><?php echo HTMLHelper::_('date', $slot->start_time, 'H:i'); ?></td>
                                                    <td colspan="<?php echo(count($this->rooms)); ?>">
														<?php if (isset($this->sessions[$slot->conference_slot_id][$this->generalRoom])) : ?>
															<?php $session = $this->sessions[$slot->conference_slot_id][$this->generalRoom]; ?>
															<?php if ($session->listview): ?>
																<?php echo HTMLHelper::_('link', Route::_('index.php?option=com_conference&view=session&id=' . $session->conference_session_id), $session->title); ?>
															<?php else: ?>
																<?php echo $session->title ?>
															<?php endif; ?>
														<?php endif; ?>
                                                    </td>
                                                </tr>
											<?php endif; ?>

											<?php if (!$slot->general): ?>
                                                <tr>
                                                    <td><?php echo HTMLHelper::_('date', $slot->start_time, 'H:i'); ?></td>
													<?php if (!empty($this->rooms)): ?>
														<?php foreach ($this->rooms as $room): ?>
															<?php if (isset($this->sessions[$slot->conference_slot_id][$room->conference_room_id])) : ?>
                                                                <td>
																	<?php $session = $this->sessions[$slot->conference_slot_id][$room->conference_room_id]; ?>
                                                                    <span class="visible-phone roomname">
                                                                    <?php echo $room->title ?>
                                                                    </span>
																	<?php if ($session->level): ?>
																		<?php
																		$url   = Route::_('index.php?option=com_conference&view=levels');
																		$text  = $session->level;
																		$class = 'label ' . $session->level_label;
																		echo HTMLHelper::_('link', $url, $text, array('class' => $class));
																		?>
																	<?php endif; ?>
                                                                    <div class="session">
																		<?php
																		if ($session->listview):
																			if ($session->slides):
																				echo '<span class="icon-grid-view" rel="tooltip" data-original-title="' . Text::_('COM_CONFERENCE_SLIDES_AVAILABLE') . '"></span>';
																			endif;
																			$url  = Route::_('index.php?option=com_conference&view=sessions&id=' . $session->conference_session_id);
																			$text = $session->title;
																			echo HTMLHelper::_('link', $url, $text);
																		else:
																			echo $session->title;
																		endif;

																		if ($params->get('language', 0)):
																			if ($session->language == 'en'):
																				$src   = 'media/mod_languages/images/' . $session->language . '.gif';
																				$alt   = 'language flag';
																				$class = 'lang';
																				echo ' ' . HTMLHelper::_('image', $src, $alt, array('class' => $class));
																			endif;
																		endif;
																		?>
                                                                    </div>
																	<?php
																	if ($session->speakers):
																		$sessionspeakers = array();

																		foreach ($session->speakers as $speaker)
																		{
																			$text = trim($speaker->title);

																			if ($speaker->enabled)
																			{
																				$url               = Route::_('index.php?option=com_conference&view=speaker&conference_speaker_id=' . $speaker->conference_speaker_id);
																				$sessionspeakers[] = '<span class="icon-user"></span> ' . HTMLHelper::_('link', $url, $text);
																			}

																			if (!$speaker->enabled)
																			{
																				$sessionspeakers[] = '<span class="icon-user"></span> ' . $text;
																			}
																		}

																		echo '<div class="speaker">';
																		echo '  <small>' . implode('<br/> ', $sessionspeakers) . '</small>';
																		echo '</div>';
																	endif;
																	?>
                                                                </td>
															<?php else: ?>
                                                                <td class="hidden-phone"></td>
															<?php endif; ?>
														<?php endforeach; ?>
													<?php endif; ?>
                                                </tr>
											<?php endif; ?>

										<?php endforeach; ?>
									<?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
					<?php endforeach; ?>
                </div>
			<?php endif; ?>
        </div>
    </div>
</section>
<script type="text/javascript">
    var tabs = new Tabs();
</script>
