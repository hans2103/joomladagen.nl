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
                    <h1 class="header__title">Joomladagen Nederland</h1>
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
                <div class="numbers">
                    <div class="numbers__item">
                        <span class="numbers__item-head">271</span>
                        <div class="numbers__item-label">deelnemers</div>
                    </div>
                    <div class="numbers__item">
                        <span class="numbers__item-head">42</span>
                        <div class="numbers__item-label">sprekers</div>
                    </div>
                    <div class="numbers__item">
                        <span class="numbers__item-head">54</span>
                        <div class="numbers__item-label">presentaties</div>
                    </div>
                    <div class="numbers__item">
                        <span class="numbers__item-head">175</span>
                        <div class="numbers__item-label">overnachtingen</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <section class="section">
            <div class="container">
                <div class="content content__payoff">

                    <h2><?php echo JLayouts::icon('vernieuwde'); ?>
                        De Joomla dagen 2018</h2>
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

                        if(count($modules > 0))
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

	                    if(count($modules > 0))
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
	            <?php
	            $modules = JModuleHelper::getModules('home_locatie');
	            $attribs = array(
		            'style' => 'tpl'
	            );

	            if(count($modules > 0))
	            {
		            echo JModuleHelper::renderModule($modules[0], $attribs);
	            }
	            ?>
            </div>
        </section>

        <div class="block block__gmap"><?php
		    $array = array(
			    'title'      => $this->item->title,
			    'latitude'   => '51.4105738',
			    'longitude'  => '5.4571851',
			    'adres'      => 'High Tech Campus 1b',
			    'postcode'   => '5656 AE',
			    'woonplaats' => 'Eindhoven'
		    );

		    echo Jlayouts::render('block-gmap', $array);
		?>
        </div>