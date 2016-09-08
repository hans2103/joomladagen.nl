<?php
/**
 * @package		JoomlaDagen template
 * @copyright	 Copyright (c) 2011-2013 Sander Potjer - www.sanderpotjer.nl
 * @license	 GNU General Public License version 3 or later
 */

// No direct access.
defined('_JEXEC') or die;

// Include Joomla Bootstrap CSS & JS
JHtml::_('bootstrap.framework');
JHtmlBootstrap::loadCss();

// Include additional CSS
JFactory::getDocument()->addStyleSheet('media/jui/css/icomoon.css');
JFactory::getDocument()->addStyleSheet('templates/'.$this->template.'/css/template.css?v=2004');
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" itemscope itemtype="http://schema.org/Article">
	<head>
		<jdoc:include type="head" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

		  <link href='//fonts.googleapis.com/css?family=Nobile:400,700' rel='stylesheet' type='text/css'>

		<link rel="shortcut icon" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/images/favicon.ico">
		<link rel="apple-touch-icon" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/images/apple-touch-icon.png">
		<link rel="apple-touch-icon" sizes="72x72" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/images/apple-touch-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="114x114" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/images/apple-touch-icon-114x114.png">
	</head>

	<body>
		<!-- Google Tag Manager -->
		<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-NQZ8SP"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','GTM-NQZ8SP');</script>
		<!-- End Google Tag Manager -->

		<!-- Topbar -->
		<div class="mobileheader visible-phone">
			<img src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/images/jdnl-mobile-11.png"/>
		</div>

		<div class="navbar navbar-fixed-top navbar-inverse">
			<div class="navbar-inner">
				<div class="container">
					<!-- Menu -->
					<jdoc:include type="modules" name="menu" style="none" />

					<div class="navbar-form pull-right visible-desktop">
						<a href="https://shop.joomladagen.nl" class="btn btn-success"><span class="joomladay-cart"></span> Bestel Tickets</a>
					</div>

					<div class="navbar-form pull-right visible-tablet">
						<a href="https://shop.joomladagen.nl" class="btn btn-small btn-success"><span class="joomladay-cart"></span> Bestel Tickets</a>
					</div>

					<div class="navbar-form visible-phone">
						<a href="https://shop.joomladagen.nl" class="btn btn-block btn-success"><span class="joomladay-cart"></span> Bestel Tickets</a>
					</div>
				</div>
			</div>
		</div><!-- Topbar -->

		<!-- Container -->
		<div class="container">
			<div class="row-fluid">
				<!-- Content -->
				<div class="span8">
					<div class="row-fluid hidden-phone">
						<div class="logo"></div>
					</div>

					<?php if($this->countModules('slogan')) : ?>
					<div class="row-fluid hidden-phone">
						<div class="tagline">
							<jdoc:include type="modules" name="slogan" style="none" />
						</div>
					</div>
					<?php endif; ?>

					<div class="row-fluid">
						<div class="whitebg">
						</div>
					</div>

					<div class="row-fluid">
						<div class="span12 content">
							<jdoc:include type="message" />
							<jdoc:include type="component" />
						</div>
					</div>
					<?php if($this->countModules('content-below')) : ?>
					<div class="row-fluid">
						<div class="span12">
							<jdoc:include type="modules" name="content-below" style="none" />
						</div>
					</div>
					<?php endif; ?>
					<?php if($this->countModules('speakerinfo')) : ?>
					<div class="row-fluid">
						<div class="span12">
  						<jdoc:include type="modules" name="speakerinfo" style="none" />
						</div>
					</div>
					<?php endif; ?>

					<?php if($this->countModules('sponsoren-diamant')) : ?>
					<div class="row-fluid visible-phone">
						<jdoc:include type="modules" name="sponsoren-diamant" style="none" />
					</div>
					<?php endif; ?>

					<?php if($this->countModules('sponsoren-goud')) : ?>
					<div class="row-fluid">
						<jdoc:include type="modules" name="sponsoren-goud" style="none" />
					</div>
					<?php endif; ?>

					<div class="row-fluid hidden-phone">
						<div class="well well-small">
							<?php if($this->countModules('nieuwsbrief')) : ?>
							<jdoc:include type="modules" name="nieuwsbrief" style="xhtml" />
							<?php endif; ?>
						</div>
					</div>
				</div><!-- Content -->

				<!-- Sidebar -->
				<div class="span4 sidebar hidden-phone">
					<div class="header clearfix">
						<?php /* <div class="laatstedag <?php echo(date(D));?>"><a href="tickets"></a></div> */ ?>
					</div>
					<div class="diamant">
					<?php if($this->countModules('sponsoren-diamant')) : ?>
						<jdoc:include type="modules" name="sponsoren-diamant" style="none" />
					<?php endif; ?>
					</div>
					<?php if($this->countModules('social')) : ?>
						<jdoc:include type="modules" name="social" style="none" />
					<?php endif; ?>
					<?php if($this->countModules('right')) : ?>
						<jdoc:include type="modules" name="right" style="jdnl" />
					<?php endif; ?>
				</div><!-- Sidebar -->
			</div>

			<?php if($this->countModules('sponsoren-zilver')) : ?>
				<jdoc:include type="modules" name="sponsoren-zilver" style="none" />
			<?php endif; ?>

			<?php if($this->countModules('sponsoren-zilver-mobile')) : ?>
				<jdoc:include type="modules" name="sponsoren-zilver-mobile" style="none" />
			<?php endif; ?>
		</div><!-- container -->

		<!-- Footer -->
		<div class="footer">
			<div class="container">
				<div class="sixteen columns">
					<div class="row clearfix">
						<p>JoomlaDay&#8482; events are officially recognized, but not organized, by the Joomla!&reg; Project and Open Source Matters, Inc. <br/> Each event is managed independently by a local community.<br/>
						De Joomla!Dagen worden georganiseerd door <a title="Stichting Sympathy" href="http://www.stichtingsympathy.nl/">Stichting Sympathy</a> - KvK: 51765705 - Bank: NL60 RABO 0160 0061 98<br>
					Copyright &copy; Joomla!dagen Nederland 2009 - <?php echo date('Y');?> - <a href="<?php echo $this->baseurl ?>/login">Login</a> - <a href="<?php echo $this->baseurl ?>/disclaimer">Disclaimer</a> - Webdesign door <a href="http://www.sanderpotjer.nl" title="Sander Potjer Webdesign">Sander Potjer Webdesign</a>.</p>
					</div>
				</div>
			</div>
		</div><!-- Footer -->


		<!-- JS
		================================================== -->
		<script src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/javascript/mobilemenu.js"></script>
		<script>
			jQuery(document).ready(function(){
				jQuery('ul#menu').mobileMenu({
				  switchWidth: 753,
				  topOptionText: 'Selecteer een pagina',
				  indentString: '&nbsp;-&nbsp;'
				});
			});
		</script>
	</body>
</html>