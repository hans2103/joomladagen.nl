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
PWTTemplateHelper::localstorageFont();

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
<?php echo PWTTemplateHelper::getAnalytics(2, 'GTM-NQZ8SP'); ?>

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


			<?php if ($this->countModules('mainmenu')) : ?>
                <div class="navigation">
                    <nav class="navigation-container" role="navigation" aria-label="Hoofdmenu">
                        <jdoc:include type="modules" name="mainmenu" style="none"/>
                    </nav>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>

<main role="main" id="content">
    <jdoc:include type="component"/>
</main>


<?php if(PWTTemplateHelper::isHome() | PWTTemplateHelper::isPage('/sponsors/sponsor-informatie')) : ?>
<section class="section section--accent section--sponsors">
    <div class="container">
		<?php
		// Module params
		$params = array(
			'catid'           => 14,
			'style'           => 'tpl',
			'moduleclass_sfx' => ' module__banners module__banners--diamant',
			'count'           => 1,
			'header_tag'       => 'h2 module__title--center',

		);

		// Load module and add params
		$module            = JModuleHelper::getModule('mod_banners');
		$module->title     = "Diamant sponsor";
		$module->showtitle = 1;
		$module->params    = json_encode($params);

		// Render module
		echo JFactory::getDocument()->loadRenderer('module')->render($module);
		?>
    </div>
    <div class="container">
		<?php
		// Module params
		$params = array(
			'catid'           => 3,
			'style'           => 'tpl',
			'moduleclass_sfx' => ' module__banners',
			'count'           => 3,
			'header_tag'       => 'h2 module__title--center'
		);

		// Load module and add params
		$module            = JModuleHelper::getModule('mod_banners');
		$module->title     = "Gouden sponsor";
		$module->showtitle = 1;
		$module->params    = json_encode($params);

		// Render module
		echo JFactory::getDocument()->loadRenderer('module')->render($module);
		?>
    </div>
    <div class="container">
		<?php
		// Module params
		$params = array(
			'catid'           => 15,
			'style'           => 'tpl',
			'moduleclass_sfx' => ' module__banners',
			'count'           => 6,
			'header_tag'       => 'h2 module__title--center'
		);

		// Load module and add params
		$module            = JModuleHelper::getModule('mod_banners');
		$module->title     = "Zilveren sponsor";
		$module->showtitle = 1;
		$module->params    = json_encode($params);

		// Render module
		echo JFactory::getDocument()->loadRenderer('module')->render($module);
		?>
    </div>
</section>
<? endif; ?>

<?php if(PWTTemplateHelper::isHome() | PWTTemplateHelper::isPage('/locatie')) : ?>
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
<? endif; ?>

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

<script type="text/javascript">
    responsivemenu.init({
        wrapper: document.querySelector('.navigation-container'),
        togglecontent: '<span class="toggle-text">menu</span><span class="hamburger"><span class="bar1"></span><span class="bar2"></span><span class="bar3"></span></span>',
        //before_element: document.querySelector('.logo'),
        subtogglecontent: ' '
    });

    var bLazy = new Blazy({
        selector: '.lazyload img',
        offset: 100
    });
</script>

<?php
if (PWTTemplateHelper::isDevelopment())
{
	include_once JPATH_THEMES . '/' . $this->template . '/grid.php';
}
?>

</body>
</html>
