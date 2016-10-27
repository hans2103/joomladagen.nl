<?php
/*
 * @package     perfecttemplate
 * @copyright   Copyright (c) Perfect Web Team / perfectwebteam.nl
 * @license     GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

// Load Perfect Template Helper
include_once JPATH_THEMES . '/' . $this->template . '/helper.php';

?>
<!DOCTYPE html>
<html class="html no-js" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head"/>
</head>

<body class="<?php echo $helper->getBodySuffix(); ?>">
<?php
if (!empty($analyticsData) && $analyticsData['position'] == 'after_body_start')
{
	echo $analyticsData['script'];
}
?>

<header class="header" role="banner">
    <div class="header__wrapper">
        <div class="header__logo">
            <a href="<?php echo JURI::base(); ?>" class="logo">
                <img src="<?php echo JURI::base(); ?>/images/logo-kerstgeschenk-vu.svg"
                     alt="logo Joomla!dagen"
                     class="inject-me site-header__logo"/>
            </a>
        </div>
        <div class="header__navigation">
            <nav class="navigation_container" role="navigation" aria-label="Hoofdmenu">
                <jdoc:include type="modules" name="mainmenu" style="none"/>
            </nav>
        </div>
    </div>
</header>

<div class="main">
    <main class="main__wrapper">
		<?php if ($this->countModules('breadcrumbs') && !$helper->isHome()) : ?>
            <div class="breadcrumbs">
                <jdoc:include type="modules" name="breadcrumbs" style="none"/>
            </div>
		<?php endif; ?>
		<?php if (count(JFactory::getApplication()->getMessageQueue())) : ?>
            <jdoc:include type="message"/>
		<?php endif; ?>
        <jdoc:include type="component"/>
    </main>
    <aside class="main__aside">
		<?php if ($this->countModules('sidebar-a')) : ?>
            <jdoc:include type="modules" name="sidebar-a" style="tpl"/>
		<?php endif; ?>
    </aside>
</div>

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

<?php if ($helper->settings['debug']) : ?>
	<div class="overlay-grid-container" style="display: none;">
		<div class="overlay-grid">
			<div class="overlay-grid__item"></div>
			<div class="overlay-grid__item"></div>
			<div class="overlay-grid__item"></div>
			<div class="overlay-grid__item"></div>
			<div class="overlay-grid__item"></div>
			<div class="overlay-grid__item"></div>
			<div class="overlay-grid__item"></div>
			<div class="overlay-grid__item"></div>
			<div class="overlay-grid__item"></div>
			<div class="overlay-grid__item"></div>
			<div class="overlay-grid__item"></div>
			<div class="overlay-grid__item"></div>
		</div>
		<script>
			var isCtrl = false;
			document.onkeyup = function (e) {
				if (e.keyCode == 17) isCtrl = false;
			};

			document.onkeydown = function (e) {
				e = e || window.event;
				if (e.keyCode == 17) isCtrl = true;

				// Grid (G key)
				if (e.keyCode == 71 && isCtrl == true) {
					var gridContainer = document.getElementsByClassName('overlay-grid-container')[0];
					if (gridContainer.style.display == 'none') {
						gridContainer.style.display = 'block';
					} else {
						gridContainer.style.display = 'none';
					}
				}

				// Remove all modernizr classes
				if (e.keyCode == 77 && isCtrl == true) { // M key
					document.documentElement.className = "";
				}
			};
		</script>
	</div>
<?php endif; ?>

<script type="text/javascript">
	responsivemenu.init({
		wrapper: document.querySelector('.navigation_container'),
		togglecontent: '<span class="toggle-text">menu</span><span class="hamburger"><span class="bar1"></span><span class="bar2"></span><span class="bar3"></span></span>',
		width: 760
	});
</script>

</body>
</html>
