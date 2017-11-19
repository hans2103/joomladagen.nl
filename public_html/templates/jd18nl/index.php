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

// Helpers
PWTTemplateHelper::setMetadata();
PWTTemplateHelper::setFavicon();
PWTTemplateHelper::unloadCss();
PWTTemplateHelper::unloadJs();
PWTTemplateHelper::loadCss();
PWTTemplateHelper::loadJs();
//PWTTemplateHelper::localstorageFont();

?>
<!DOCTYPE html>
<html class="html no-js" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <jdoc:include type="head"/>
    <noscript>
        <link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/font.css"
              rel="stylesheet" type="text/css"/>
    </noscript>
</head>

<body class="<?php echo PWTTemplateHelper::getBodySuffix(); ?>">
<?php //echo PWTTemplateHelper::getAnalytics(2,'GTM-XXXXXX'); ?>

<noscript>
    <div class="svg-sprite"><?php include_once JPATH_THEMES . '/' . $this->template . '/icons/icons.svg'; ?></div>
</noscript>

<div class="skip">
    <div class="container">
        <a href="#content">Spring naar de inhoud</a>
    </div>
</div>

<div class="topbar">
    <div class="container">
        <div class="content">
            <div class="logo">
                <a href="<?php echo $url = JURI::base(); ?>"><?php echo JLayouts::icon('joomladagen_logo'); ?></a>
            </div>
            <div class="logo--name">
                <?php $url = JURI::base(); ?>
                <?php $text = '<span class="logo--brand">Joomla</span>dagen 2018'; ?>
				<?php echo JHtml::_('link', $url, $text); ?>
            </div>
        </div>
    </div>
</div>
<?php if ($this->countModules('mainmenu')) : ?>
    <div class="navigation">
        <div class="container">
            <nav class="navigation-container" role="navigation" aria-label="Hoofdmenu">
                <jdoc:include type="modules" name="mainmenu" style="none"/>
            </nav>
        </div>
    </div>
<?php endif; ?>

<jdoc:include type="component"/>

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

<?php /*<script type="text/javascript">
    responsivemenu.init({
        wrapper: document.querySelector('.navigation-container'),
        togglecontent: '<span class="toggle-text">menu</span><span class="hamburger"><span class="bar1"></span><span class="bar2"></span><span class="bar3"></span></span>',
    });
</script>*/ ?>

<?php
if (PWTTemplateHelper::isDevelopment())
{
	include_once JPATH_THEMES . '/' . $this->template . '/grid.php';
}
?>

</body>
</html>
