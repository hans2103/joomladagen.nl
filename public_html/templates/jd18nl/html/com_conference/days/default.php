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
                            <button class="tab-button" role="tab"
                                    aria-selected="<?php echo $key == 0 ? 'true' : 'false'; ?>"
                                    aria-controls="<?php echo $item->alias; ?>-tab"
                                    id="<?php echo $item->alias; ?>">
								<?php echo $item->title; ?>
                            </button>
						<?php endforeach; ?>
                    </div>
					<?php foreach ($this->items as $key => &$item) : ?>
                        <div class="tab-content" tabindex="0" role="tabpanel" id="<?php echo $item->alias; ?>-tab"
                             aria-labelledby="<?php echo $item->alias; ?>" <?php echo $key == 0 ? '' : 'hidden'; ?>>
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

<script>
    /*
	*   This content is licensed according to the W3C Software License at
	*   https://www.w3.org/Consortium/Legal/2015/copyright-software-and-document
	*/
    (function () {
        let tablist = document.querySelectorAll('[role="tablist"]')[0];
        let tabs;
        let panels;
        let delay = determineDelay();

        generateArrays();

        function generateArrays() {
            tabs = document.querySelectorAll('[role="tab"]');
            panels = document.querySelectorAll('[role="tabpanel"]');
        }

        // For easy reference
        let keys = {
            end: 35,
            home: 36,
            left: 37,
            up: 38,
            right: 39,
            down: 40,
            delete: 46
        };

        // Add or substract depenign on key pressed
        let direction = {
            37: -1,
            38: -1,
            39: 1,
            40: 1
        };

        // Bind listeners
        for (i = 0; i < tabs.length; ++i) {
            addListeners(i);
        }

        function addListeners(index) {
            tabs[index].addEventListener('click', clickEventListener);
            tabs[index].addEventListener('keydown', keydownEventListener);
            tabs[index].addEventListener('keyup', keyupEventListener);

            // Build an array with all tabs (<button>s) in it
            tabs[index].index = index;
        }

        // When a tab is clicked, activateTab is fired to activate it
        function clickEventListener(event) {
            let tab = event.target;
            console.log(event.target);
            activateTab(tab, false);
        }

        // Handle keydown on tabs
        function keydownEventListener(event) {
            let key = event.keyCode;

            switch (key) {
                case keys.end:
                    event.preventDefault();
                    // Activate last tab
                    activateTab(tabs[tabs.length - 1]);
                    break;
                case keys.home:
                    event.preventDefault();
                    // Activate first tab
                    activateTab(tabs[0]);
                    break;

                // Up and down are in keydown
                // because we need to prevent page scroll >:)
                case keys.up:
                case keys.down:
                    determineOrientation(event);
                    break;
            }
        }

        // Handle keyup on tabs
        function keyupEventListener(event) {
            let key = event.keyCode;

            switch (key) {
                case keys.left:
                case keys.right:
                    determineOrientation(event);
                    break;
                case keys.delete:
                    determineDeletable(event);
                    break;
            }
        }

        // When a tablistâ€™s aria-orientation is set to vertical,
        // only up and down arrow should function.
        // In all other cases only left and right arrow function.
        function determineOrientation(event) {
            let key = event.keyCode;
            let vertical = tablist.getAttribute('aria-orientation') == 'vertical';
            let proceed = false;

            if (vertical) {
                if (key === keys.up || key === keys.down) {
                    event.preventDefault();
                    proceed = true;
                }
            }
            else {
                if (key === keys.left || key === keys.right) {
                    proceed = true;
                }
            }

            if (proceed) {
                switchTabOnArrowPress(event);
            }
        }

        // Either focus the next, previous, first, or last tab
        // depening on key pressed
        function switchTabOnArrowPress(event) {
            let pressed = event.keyCode;

            for (x = 0; x < tabs.length; x++) {
                tabs[x].addEventListener('focus', focusEventHandler);
            }

            if (direction[pressed]) {
                let target = event.target;
                if (target.index !== undefined) {
                    if (tabs[target.index + direction[pressed]]) {
                        tabs[target.index + direction[pressed]].focus();
                    }
                    else if (pressed === keys.left || pressed === keys.up) {
                        focusLastTab();
                    }
                    else if (pressed === keys.right || pressed == keys.down) {
                        focusFirstTab();
                    }
                }
            }
        }

        // Activates any given tab panel
        function activateTab(tab, setFocus) {
            setFocus = setFocus || true;
            // Deactivate all other tabs
            deactivateTabs();

            // Remove tabindex attribute
            tab.removeAttribute('tabindex');

            // Set the tab as selected
            tab.setAttribute('aria-selected', 'true');

            // Get the value of aria-controls (which is an ID)
            let controls = tab.getAttribute('aria-controls');

            // Remove hidden attribute from tab panel to make it visible
            document.getElementById(controls).removeAttribute('hidden');

            // Set focus when required
            if (setFocus) {
                tab.focus();
            }
        }

        // Deactivate all tabs and tab panels
        function deactivateTabs() {
            for (t = 0; t < tabs.length; t++) {
                tabs[t].setAttribute('tabindex', '-1');
                tabs[t].setAttribute('aria-selected', 'false');
                tabs[t].removeEventListener('focus', focusEventHandler);
            }

            for (p = 0; p < panels.length; p++) {
                panels[p].setAttribute('hidden', 'hidden');
            }
        }

        // Make a guess
        function focusFirstTab() {
            tabs[0].focus();
        }

        // Make a guess
        function focusLastTab() {
            tabs[tabs.length - 1].focus();
        }

        // Detect if a tab is deletable
        function determineDeletable(event) {
            target = event.target;

            if (target.getAttribute('data-deletable') !== null) {
                // Delete target tab
                deleteTab(event, target);

                // Update arrays related to tabs widget
                generateArrays();

                // Activate the closest tab to the one that was just deleted
                if (target.index - 1 < 0) {
                    activateTab(tabs[0]);
                }
                else {
                    activateTab(tabs[target.index - 1]);
                }
            }
        }

        // Deletes a tab and its panel
        function deleteTab(event) {
            let target = event.target;
            let panel = document.getElementById(target.getAttribute('aria-controls'));

            target.parentElement.removeChild(target);
            panel.parentElement.removeChild(panel);
        }

        // Determine whether there should be a delay
        // when user navigates with the arrow keys
        function determineDelay() {
            let hasDelay = tablist.hasAttribute('data-delay');
            let delay = 0;

            if (hasDelay) {
                let delayValue = tablist.getAttribute('data-delay');
                if (delayValue) {
                    delay = delayValue;
                }
                else {
                    // If no value is specified, default to 300ms
                    delay = 300;
                }
            }

            return delay;
        }

        //
        function focusEventHandler(event) {
            let target = event.target;

            setTimeout(checkTabFocus, delay, target);
        }

        // Only activate tab on focus if it still has focus after the delay
        function checkTabFocus(target) {
            focused = document.activeElement;

            if (target === focused) {
                activateTab(target, false);
            }
        }

        // When URL contains hash of existing ID, activateTab is fired to activate it
        if (window.location.hash) {
            let hash = window.location.hash.substr(1);
            let tab = document.getElementById(hash);

            if(tab === null) {
                return;
            }

            activateTab(tab, false);
        }
    }());
</script>
