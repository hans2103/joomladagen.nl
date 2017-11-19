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

    <main id="content" class="main">
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


    </main>

    <section>

    </section>

<?php /*

	<section class="section__wrapper">
		<div class="container container--shift">
			<div class="content content--analyse">
				<?php
				// Module params
				$moduleparams = array(
					'count'                      => 1,
					'catid'                      => 17,
					'article_ordering'           => 'a.created',
					'article_ordering_direction' => 'DESC',
					'style'                      => 'none',
					'layout'                     => 'analyse',
					'header_tag'                 => 'h2',
					'moduleclass_sfx'            =>  'block block--analyse '
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
	</section>

	<section class="section__wrapper">
		<div class="container container--shift-small">
			<div class="content">
				<div class="block block--congres">

					<?php
					//echo JLayoutHelper::render('template.content.article_item-list', $data);
					?>
					<article class="article__item article__item--list">
						<div class="article__image">
							<div class="media-placeholder media-placeholder--A4">
								<div class="lazyload">
									<img src="/images/300x300.png" alt="" class=" b-loaded">
									<noscript>
										&lt;img src="/images/300x300.png" alt="" /&gt;
									</noscript>
								</div>
							</div>
						</div>

						<div class="article__body">
							<div class="article__title"><h2><a href="/nieuws/lorem-ipsum-item-3" itemprop="url"
							                                   class="article__title-link">Lorem Ipsum - item 3</a></h2>
							</div>
							<div class="article__content">
								<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent at magna pharetra
									libero finibus lacinia. Duis vel odio ac leo vestibulum convallis ac id arcu. Aenean mi
									ligula, gravida a mi sit amet, blandit vehicula urna. Praesent mauris tellus, blandit
									vel massa et, lacinia fringilla metus.&nbsp;</p>


								<p class="readmore">
									<a href="/nieuws/lorem-ipsum-item-3" class="readmore__link" itemprop="url">Lees meer</a>
								</p>
							</div>
						</div>
					</article>
					<article class="article__item article__item--list">
						<div class="article__image">
							<div class="media-placeholder media-placeholder--A4">
								<div class="lazyload">
									<img src="/images/300x300.png" alt="" class=" b-loaded">
									<noscript>
										&lt;img src="/images/300x300.png" alt="" /&gt;
									</noscript>
								</div>
							</div>
						</div>

						<div class="article__body">
							<div class="article__title"><h2><a href="/nieuws/lorem-ipsum-item-3" itemprop="url"
							                                   class="article__title-link">Lorem Ipsum - item 3</a></h2>
							</div>
							<div class="article__content">
								<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent at magna pharetra
									libero finibus lacinia. Duis vel odio ac leo vestibulum convallis ac id arcu. Aenean mi
									ligula, gravida a mi sit amet, blandit vehicula urna. Praesent mauris tellus, blandit
									vel massa et, lacinia fringilla metus.&nbsp;</p>


								<p class="readmore">
									<a href="/nieuws/lorem-ipsum-item-3" class="readmore__link" itemprop="url">Lees meer</a>
								</p>
							</div>
						</div>
					</article>
				</div>
			</div>
		</div>
	</section>

	<section class="section__wrapper section__wrapper--purple">
		<div class="container container--shift">
			<div class="content block block--nascholing">
				<div class="block__title">
					<h2>NVML nascholingen</h2>
				</div>
				<div class="block__subtitle">
					<p>De NVML organiseert cursussen, nascholingen, congressen en symposia en biedt een e-learning
						systeem aan voor leden en niet-leden.</p>
				</div>
				<div class="block__content blocks">
					<?php
					$data = array(
						'title'   => 'E-learning',
						'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod bibendum
                                laoreet...</p>',
						'link'    => '/e-learning'
					);
					echo Jlayouts::render('template.content.block-item', $data);
					?>
					<?php
					$data = array(
						'title'   => 'Naschoolse congressen & symposia',
						'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod bibendum laoreet...</p>',
						'link'    => '/nieuws/spring-event-op-16-mei-succesvolle-dag'
					);
					echo Jlayouts::render('template.content.block-item', $data);
					?>
					<?php
					$data = array(
						'title'   => 'Cursussen',
						'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod bibendum laoreet...</p>',
						'link'    => '/nieuws/spring-event-op-16-mei-succesvolle-dag'
					);
					echo Jlayouts::render('template.content.block-item', $data);
					?>
				</div>
			</div>
		</div>
	</section>

	<section class="section__wrapper">
		<div class="container container--shift">
			<div class="content block block--agenda">
				<?php
				$data = array(
					'title'    => 'Agenda',
					'content'  => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod bibendum laoreet...</p>',
					'link'     => '/nieuws/spring-event-op-16-mei-succesvolle-dag',
					'linktext' => 'Bekijk alle activiteiten'
				);
				echo Jlayouts::render('template.content.block-item', $data);
				?>
				<div class="block__list">
					<?php
					$data = array(
						'date'     => '29/06',
						'title'    => 'Ne civibus aliquando quo',
						'location' => 'Amsterdam',
						'link'     => 'https://nvml.nl'
					);
					echo Jlayouts::render('template.content.agenda-list-item', $data);
					echo Jlayouts::render('template.content.agenda-list-item', $data);
					echo Jlayouts::render('template.content.agenda-list-item', $data);
					echo Jlayouts::render('template.content.agenda-list-item', $data);
					?>
				</div>
			</div>
		</div>
	</section>

	<section class="section__wrapper section__wrapper--purple">
		<div class="container container--shift">
			<div class="content block block--lidworden">
				<div class="block__title">
					<h2>Lid worden</h2>
				</div>
				<div class="block__subtitle">
					<p>De voordelen van een NVML-lidmaatschap zijn:</p>
				</div>
				<div class="block__content">
					<ul>
						<li>Solorpor ibusantium labo</li>
						<li>Nem et vel molupti quaectur</li>
						<li>Rem doluptum, et re periberero</li>
						<li>Velecest, utatis in cusdae</li>
						<li>Ommodip itataspici alicim niminul</li>
						<li>Latur Icide mil iur aut aute re</li>
						<li>Omnimustrum explandae</li>
						<li>Itatentiusti vollandit volorum</li>
						<li>Aruptat eum qui offic to este</li>
						<li>Sitibusam inctis re vid et et utenis</li>
						<li>Sed ut quae dem evendae</li>
						<li>Cuptiusda volenimaio et erunt</li>
					</ul>
				</div>
				<div class="block__action">
					<a href="/lid-worden" class="button">Lid worden!</a>
				</div>
			</div>
		</div>
	</section>

	<section class="section__wrapper">
		<div class="container container--shift">
			<?php
			// Module params
			$params = array(
				'style'           => 'tpl',
				'moduleclass_sfx' => ' module__banners',
				'count'           => 3
			);

			// Load module and add params
			$module            = JModuleHelper::getModule('mod_banners');
			$module->showtitle = 0;
			$module->params    = json_encode($params);

			// Render module
			echo JFactory::getDocument()->loadRenderer('module')->render($module);
			?>
		</div>
	</section>
*/ ?>

<?php /*
<section class="section__wrapper">
	<div class="container container--shift">
		<div class="article__item article__item--shift">
			<?php if (isset($images->image_fulltext) && !empty($images->image_fulltext)) : ?>
				<div class="article__image">
					<div class="media-placeholder media-placeholder--16by9">
						<?php $src = $images->image_fulltext; ?>
						<?php $alt = $images->image_fulltext_alt ? $images->image_fulltext_alt : ''; ?>
						<?php echo JLayouts::render('template.image', array('img' => $src, 'alt' => $alt)); ?>
					</div>
				</div>
			<?php endif; ?>
			<div class="article__body">
				<?php echo $this->item->event->beforeDisplayContent; ?>
				<?php echo $this->item->text; ?>
				<?php echo $this->item->event->afterDisplayContent; ?>
			</div>
		</div>
	</div>
</section>

<aside class="content-bottom__wrapper">
	<div class="container container--shift">
		<?php
		if ($params->get('show_other_reads') !== null && $params->get('show_other_reads') == true ) :
			// Module params
			$params = array(
				'count'                      => 3,
				'catid'                      => $this->item->catid,
				'article_ordering'           => 'a.created',
				'article_ordering_direction' => 'DESC',
				'style'                      => 'tpl',
				'header_tag'                 => 'h2',
				'moduleclass_sfx'            =>  'module__more-articles'
			);

			// Load module and add params
			$module            = JModuleHelper::getModule('mod_articles_category');
			$module->title     = "Lees ook";
			$module->showtitle = 1;
			$module->params    = json_encode($params);

			// Render module
			echo JFactory::getDocument()->loadRenderer('module')->render($module);
		endif;
		?>
	</div>
</aside>

 */ ?>