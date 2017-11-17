<?php
/*
 * @package     perfecttemplate
 * @copyright   Copyright (c) Perfect Web Team / perfectwebteam.nl
 * @license     GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

// Load Perfect Template Helper
require_once JPATH_THEMES . '/' . $this->template . '/helper.php';

// JLayout render
require_once JPATH_THEMES . '/' . $this->template . '/html/layouts/render.php';

PWTTemplateHelper::setMetadata();
PWTTemplateHelper::setFavicon();
PWTTemplateHelper::unloadCss();
PWTTemplateHelper::unloadJs();
PWTTemplateHelper::loadCss();
PWTTemplateHelper::loadJs();
PWTTemplateHelper::localstorageFont();

?>
<!DOCTYPE html>
<html class="html no-js" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <jdoc:include type="head"/>
</head>

<body class="<?php echo PWTTemplateHelper::getBodySuffix(); ?>">
<?php echo PWTTemplateHelper::getAnalytics(2, 'GTM-NQZ8SP'); ?>

<?php /*<header class="header" role="banner">
    <div class="header__wrapper">
        <div class="header__logo">
            <a href="<?php echo JURI::base(); ?>" class="logo">
                <span class="logo--brand">Joomla</span>Dagen 2018
            </a>
        </div>
        <div class="header__navigation">
            <nav class="navigation_container" role="navigation" aria-label="Hoofdmenu">
                <jdoc:include type="modules" name="mainmenu" style="none"/>
            </nav>
        </div>
    </div>
</header> */ ?>

<?php echo JLayouts::render('countdown'); ?>

<?php if (PWTTemplateHelper::isHome() == true) : ?>
	<?php if ($this->countModules('header')) : ?>
        <div class="block__paralax block__header--home">
            <div class="block__wrapper">
                <div class="block__jdnl">
                    <h1 class="jdnl__title">Joomladagen Nederland</h1>
                    <div class="jdnl__logo"><?php echo JLayouts::icon('joomla_logo'); ?></div>
                    <div class="jdnl__meta">
                        <div class="jdnl__date">13 & 14 April 2018</div>
                        <div class="jdnl__location">High Tech Campus Eindhoven</div>
                    </div>
                </div>
            </div>
        </div>
	<?php endif; ?>
    <aside class="section section--blue section__numbers">
        <div class="container">
            <div class="numbers__wrapper">
                <div class="numbers">
                    <div class="numbers__item countdown__dd"><span>145</span>dagen</div>
                    <!--<div class="numbers__item countdown__hh"><span>07</span>uren</div>
                    <div class="numbers__item countdown__mm"><span>46</span>dagen</div>
                    <div class="numbers__item countdown__ss"><span>27</span>dagen</div>-->
                </div>
                <div class="numbers">
                    <div class="numbers__item"><span>271</span>deelnemers</div>
                    <div class="numbers__item"><span>42</span>sprekers</div>
                    <div class="numbers__item"><span>54</span>presentaties</div>
                    <div class="numbers__item"><span>175</span>overnachtingen</div>
                </div>
            </div>
<?php /*
            <script>
                const countDownClock = (number = 100, format = 'seconds') => {

                    const d = document;
                    const daysElement = d.querySelector('.days');
                    const hoursElement = d.querySelector('.hours');
                    const minutesElement = d.querySelector('.minutes');
                    const secondsElement = d.querySelector('.seconds');
                    let countdown;
                    convertFormat(format);


                    function convertFormat(format) {
                        switch(format) {
                            case 'seconds':
                                return timer(number);
                            case 'minutes':
                                return timer(number * 60);
                            case 'hours':
                                return timer(number * 60 * 60);
                            case 'days':
                                return timer(number * 60 * 60 * 24);;
                        }
                    }

                    function timer(seconds) {
                        const now = Date.now();
                        const then = now + seconds * 1000;

                        countdown = setInterval(() => {
                            const secondsLeft = Math.round((then - Date.now()) / 1000);

                        if(secondsLeft <= 0) {
                            clearInterval(countdown);
                            return;
                        };

                        displayTimeLeft(secondsLeft);

                    },1000);
                    }

                    function displayTimeLeft(seconds) {
                        daysElement.textContent = Math.floor(seconds / 86400);
                        hoursElement.textContent = Math.floor((seconds % 86400) / 3600);
                        minutesElement.textContent = Math.floor((seconds % 86400) % 3600 / 60);
                        secondsElement.textContent = seconds % 60 < 10 ? `0${seconds % 60}` : seconds % 60;
                    }
                }
                countDownClock(20, 'days');
            </script>*/ ?>
        </div>
    </aside>
<?php endif; ?>

<main class="main" role="main">
	<?php if (PWTTemplateHelper::isHome() == true) : ?>
        <section class="section">
            <div class="container">
                <div class="block block__payoff">

                    <h2><?php echo JLayouts::icon('vernieuwde'); ?>
                        De Joomla dagen 2018</h2>

                    <jdoc:include type="modules" name="home_intro" style="tpl"/>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="grid--flex grid--1-1">
                    <div class="grid__item grid__item--dark">
                        <jdoc:include type="modules" name="home_vrijdag" style="tpl"/>
                    </div>
                    <div class="grid__item">
                        <jdoc:include type="modules" name="home_zaterdag" style="tpl"/>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <jdoc:include type="modules" name="home_locatie" style="tpl"/>
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
			?></div>


		<?php if (false && $this->countModules('block-info')) : ?>
            <div class="block block--info">
                <div class="block__wrapper">
                    <jdoc:include type="modules" name="block-info" style="tpl"/>
                </div>
            </div>
		<?php endif; ?>

		<?php if (false && $this->countModules('block-news')) : ?>
            <div class="block block--news">
                <div class="block__wrapper">
                    <jdoc:include type="modules" name="block-news" style="tpl"/>
                </div>
            </div>
		<?php endif; ?>

		<?php if (false && $this->countModules('block-interviews')) : ?>
            <div class="block block--interviews">
                <div class="block__wrapper">
                    <jdoc:include type="modules" name="block-interviews" style="tpl"/>
                </div>
            </div>
		<?php endif; ?>

	<?php endif; ?>

	<?php if (false && PWTTemplateHelper::isHome() == false) : ?>

		<?php echo PWTTemplateHelper::renderHelixTitle(); ?>

        <div class="main__wrapper">
            <div class="main__content">
				<?php if (count(JFactory::getApplication()->getMessageQueue())) : ?>
                    <jdoc:include type="message"/>
				<?php endif; ?>
                <jdoc:include type="component"/>

				<?php if ($this->countModules('block-content-below')) : ?>
                    <div class="block block--content--below">
                        <div class="block__wrapper block__wrapper--reset">
                            <jdoc:include type="modules" name="block-content-below" style="tpl"/>
                        </div>
                    </div>
				<?php endif; ?>

            </div>
        </div>
	<?php endif; ?>

	<?php if (false && $this->countModules('block-sponsors')) : ?>
        <div class="block block--sponsors">
            <div class="block__wrapper">
                <jdoc:include type="modules" name="block-sponsors" style="tpl"/>
            </div>
        </div>
	<?php endif; ?>

</main>


<footer class="footer" role="contentinfo">
    <div class="container">
        <div class="footer__content">
            <jdoc:include type="modules" name="footer" style="tpl"/>
        </div>
    </div>
</footer>

<div class="copyright" role="contentinfo">
    <div class="container">
        <div class="copyright__content">
            <jdoc:include type="modules" name="copyright" style="none"/>
            <p class="copyright__text">&copy; Copyright 2005<?php echo(date('Y') != 2005 ? ' - ' . date('Y') : ''); ?>
				<?php echo PWTTemplateHelper::getSitename(); ?></p>
        </div>
    </div>
</div>

<?php /* <script type="text/javascript">
    responsivemenu.init({
        wrapper: document.querySelector('.navigation_container'),
        togglecontent: '<span class="toggle-text">menu</span><span class="hamburger"><span class="bar1"></span><span class="bar2"></span><span class="bar3"></span></span>',
        width: 760
    });
</script> */ ?>

</body>
</html>
