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
<?php echo PWTTemplateHelper::getAnalytics(2,'GTM-NQZ8SP'); ?>

<header class="header" role="banner">
    <div class="header__wrapper">
        <div class="header__logo">
            <a href="<?php echo JURI::base(); ?>" class="logo">
                <span class="logo--brand">Joomla</span>Dagen 2017
            </a>
        </div>
        <div class="header__navigation">
            <nav class="navigation_container" role="navigation" aria-label="Hoofdmenu">
                <jdoc:include type="modules" name="mainmenu" style="none"/>
            </nav>
        </div>
    </div>
</header>

<?php

$date      = JFactory::getDate();
$edate     = new DateTime('2017-03-01');
$remain    = $edate->diff($date);
$imgTitle  = '';
$imgNumber = '';

if ($remain->days >= 6 && $remain->days <= 13)
{
	$imgTitle  = 'weeks';
	$imgNumber = ceil($remain->days / 7);
}

if ($remain->days >= 0 && $remain->days <= 5)
{
	$imgTitle  = 'days';
	$imgNumber = $remain->days;
}

//if (isset($imgTitle) && !empty($imgTitle))
if(false)
{
?>
    <div class="block__countdown">
        <div class="block__countdown--container">
            <div class="block__countdown--content">
				<?php $img = JHtml::_('image', 'images/countdown/' . $imgTitle . '-' . $imgNumber . '.png', 'Geniet nu nog van de Early Bird'); ?>
				<?php echo JHtml::_('link', 'https://shop.joomladagen.nl', $img, array()); ?>
            </div>
        </div>
    </div>
<?php } ?>

<?php if (PWTTemplateHelper::isHome() == true) : ?>
	<?php if ($this->countModules('header')) : ?>
        <div class="block__paralax block__header--home">
            <div class="block__wrapper">
                <jdoc:include type="modules" name="header" style="tpl"/>
            </div>
        </div>
	<?php endif; ?>
<?php endif; ?>

<main class="main" role="main">
	<?php if (PWTTemplateHelper::isHome() == true) : ?>
        intro<br>
        twee blokken<br>
        locatie<br>
        onze sponsoren<br>
        Google maps<br>


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


		<?php if ($this->countModules('block-info')) : ?>
            <div class="block block--info">
                <div class="block__wrapper">
                    <jdoc:include type="modules" name="block-info" style="tpl"/>
                </div>
            </div>
		<?php endif; ?>

		<?php if ($this->countModules('block-news')) : ?>
            <div class="block block--news">
                <div class="block__wrapper">
                    <jdoc:include type="modules" name="block-news" style="tpl"/>
                </div>
            </div>
		<?php endif; ?>

		<?php if ($this->countModules('block-interviews')) : ?>
            <div class="block block--interviews">
                <div class="block__wrapper">
                    <jdoc:include type="modules" name="block-interviews" style="tpl"/>
                </div>
            </div>
		<?php endif; ?>

	<?php endif; ?>

	<?php if (PWTTemplateHelper::isHome() == false) : ?>

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

	<?php if ($this->countModules('block-sponsors')) : ?>
        <div class="block block--sponsors">
            <div class="block__wrapper">
                <jdoc:include type="modules" name="block-sponsors" style="tpl"/>
            </div>
        </div>
	<?php endif; ?>

</main>

<footer class="footer" role="contentinfo">
    <div class="footer__wrapper">
        <jdoc:include type="modules" name="footer" style="tpl"/>
    </div>
</footer>

<div class="copyright" role="contentinfo">
    <div class="copyright__wrapper">
        <div class="copyright__content">
            <jdoc:include type="modules" name="copyright" style="tpl"/>
        </div>
    </div>
</div>

<script type="text/javascript">
    responsivemenu.init({
        wrapper: document.querySelector('.navigation_container'),
        togglecontent: '<span class="toggle-text">menu</span><span class="hamburger"><span class="bar1"></span><span class="bar2"></span><span class="bar3"></span></span>',
        width: 760
    });
</script>

</body>
</html>
