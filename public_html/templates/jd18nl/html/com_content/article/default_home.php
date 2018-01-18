<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$this->template = JFactory::getApplication()->getTemplate();
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

JPluginHelper::importPlugin('content');

// Create shortcuts to some parameters.
$params      = $this->item->params;
$images      = json_decode($this->item->images);
$urls        = json_decode($this->item->urls);
$title       = JHtml::_('content.prepare', $this->item->title);
$description = ($this->item->introtext) ? $this->item->introtext : '';

//echo JLayouts::render('template.content.header', array('title' => $title, 'intro' => $description, 'breadcrumbs' => false));
?>

<header class="header header--home" role="banner">
    <div class="header__inner">
        <div class="container">
            <div class="header__content">
                <h1 class="header__title">JoomlaDagen Nederland</h1>
                <div class="header__text-logo"><?php echo JLayouts::icon('joomla_logo'); ?></div>
                <div class="header__text-meta">
                    <div class="header__text-date">13 & 14 April 2018</div>
                    <div class="header__text-location">High Tech Campus Eindhoven</div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="section section--blue section__numbers">
    <div class="container">
        <div class="numbers__wrapper">
            <div class="numbers" id="clockdiv">
                <div class="numbers__item">
                    <div class="numbers__item-head">Zo lang nog tot de JoomlaDagen 2018: </div>
                </div>
                <div class="numbers__item">
                    <span class="numbers__item-head days"></span>
                    <div class="numbers__item-label">dagen</div>
                </div>
                <div class="numbers__item">
                    <span class="numbers__item-head hours"></span>
                    <div class="numbers__item-label">uren</div>
                </div>
                <div class="numbers__item">
                    <span class="numbers__item-head minutes"></span>
                    <div class="numbers__item-label">min</div>
                </div>
                <div class="numbers__item">
                    <span class="numbers__item-head seconds"></span>
                    <div class="numbers__item-label">sec</div>
                </div>
                <script>
                    function getTimeRemaining(endtime) {
                        var t = Date.parse(endtime) - Date.parse(new Date());
                        var seconds = Math.floor((t / 1000) % 60);
                        var minutes = Math.floor((t / 1000 / 60) % 60);
                        var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
                        var days = Math.floor(t / (1000 * 60 * 60 * 24));
                        return {
                            'total': t,
                            'days': days,
                            'hours': hours,
                            'minutes': minutes,
                            'seconds': seconds
                        };
                    }

                    function initializeClock(id, endtime) {
                        var clock = document.getElementById(id);
                        var daysSpan = clock.querySelector('.days');
                        var hoursSpan = clock.querySelector('.hours');
                        var minutesSpan = clock.querySelector('.minutes');
                        var secondsSpan = clock.querySelector('.seconds');

                        function updateClock() {
                            var t = getTimeRemaining(endtime);

                            if (t.total <= 0) {
                                addClass('#' + id, 'hidden');
                                return;
                            }

                            daysSpan.innerHTML = t.days;
                            hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
                            minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
                            secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);

                            if (t.total <= 0) {
                                clearInterval(timeinterval);
                            }
                        }

                        updateClock();
                        var timeinterval = setInterval(updateClock, 1000);
                    }

                    var deadline = new Date('04/13/2018 09:00:00');
                    initializeClock('clockdiv', deadline);
                </script>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="content content__payoff">
            <h2><?php echo JLayouts::icon('vernieuwde'); ?>
                De JoomlaDagen 2018</h2>
            <h3><?php echo $this->item->title; ?></h3>
			<?php echo $this->item->text; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid--flex grid--1-1">
            <div class="grid__item grid__item--dark">
				<?php
				$modules = JModuleHelper::getModules('home_vrijdag');
				$attribs = array(
					'style' => 'tpl'
				);

				if (count($modules > 0))
				{
					echo JModuleHelper::renderModule($modules[0], $attribs);
				}
				?>
            </div>
            <div class="grid__item">
				<?php
				$modules = JModuleHelper::getModules('home_zaterdag');
				$attribs = array(
					'style' => 'tpl'
				);

				if (count($modules > 0))
				{
					echo JModuleHelper::renderModule($modules[0], $attribs);
				}
				?>
            </div>
        </div>
    </div>
</section>


<section class="section">
    <div class="container">
        <div class="module module__youtube">
            <div class="module__header">
                <p class="module__title module__title--h2">Daarom Joomla!</p>
            </div>
            <div class="module__content">
                <div class="video-container">
					<?php
					$url = 'https://www.youtube.com/watch?v=Xe4fcNRjP_Q';
					echo PWTTemplateHelper::youtube($url);
					?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="module module__nieuws">
            <div class="module__header">
                <p class="module__title module__title--h2">Nieuws</p>
            </div>
            <div class="module__content">
                <div class="grid--flex grid-2-1">
                    <div class="grid__item grid--flex grid--1-1-1">
						<?php
						// Module params
						$moduleparams = array(
							'count'            => 2,
							'catid'            => 7,
							'article_ordering' => 'a.ordering',
							'style'            => 'none',
							'layout'           => 'block',
							'header_tag'       => 'h2'
						);

						// Load module and add params
						$module            = JModuleHelper::getModule('mod_articles_category');
						$module->showtitle = 0;
						$module->params    = json_encode($moduleparams);

						// Render module
						echo JFactory::getDocument()->loadRenderer('module')->render($module);
						?>
						<?php
						// Module params
						$moduleparams = array(
							'count'            => 1,
							'catid'            => 33,
							'article_ordering' => 'a.created',
							'style'            => 'none',
							'layout'           => 'block',
							'header_tag'       => 'h2'
						);

						// Load module and add params
						$module            = JModuleHelper::getModule('mod_articles_category');
						$module->showtitle = 0;
						$module->params    = json_encode($moduleparams);

						// Render module
						echo JFactory::getDocument()->loadRenderer('module')->render($module);
						?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
		<?php
		$modules = JModuleHelper::getModules('home_locatie');
		$attribs = array(
			'style' => 'tpl'
		);

		if (count($modules > 0))
		{
			echo JModuleHelper::renderModule($modules[0], $attribs);
		}
		?>
    </div>
</section>
