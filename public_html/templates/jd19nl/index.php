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

// Helpers
$favicolor           = '#495781';
$favicolorBackground = '#ffffff';
PWTTemplateHelper::setMetadata($favicolor, $favicolorBackground);
PWTTemplateHelper::setFavicon($favicolor);
PWTTemplateHelper::unloadCss();
PWTTemplateHelper::unloadJs();
PWTTemplateHelper::loadCss();
PWTTemplateHelper::loadJs();

?>
<!DOCTYPE html>
<html class="html no-js" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<jdoc:include type="head"/>
</head>

<body class="<?php echo PWTTemplateHelper::getBodySuffix(); ?>" id="home">
<?php echo PWTTemplateHelper::getAnalytics(2, 'GTM-NQZ8SP'); ?>
<div class="body-inner">
	<header id="header" class="header header-classic">
		<div class="container">
			<nav class="navbar navbar-expand-lg navbar-light">
				<a class="navbar-brand" href="index.html">
					JoomlaDagen 2019
				</a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"><i class="icon icon-menu"></i></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarNavDropdown">
					<ul class="navbar-nav ml-auto">
						<li class="nav-item scroll">
							<a href="#home" class="">Home</a>
						</li>
						<li class="nav-item scroll">
							<a href="#programma" class="">Programma</a>
						</li>
						<li class="nav-item scroll">
							<a href="#locatie">Locatie</a>
						</li>
						<li class="nav-item scroll">
							<a href="#sponsors">Sponsors</a>
						</li>
						<li class="header-ticket nav-item scroll">
							<a class="ticket-btn btn" href="#tickets">Tickets</a>
						</li>
					</ul>
				</div>
			</nav>
		</div>
	</header>

	<section class="hero-area centerd-item">
		<div class="banner-item" style="background-image:url(templates/jd19nl/images/pattern/jd19nl-pattern.png)">
			<div class="container">
				<div class="row">
					<div class="col-lg-8 mx-auto">
						<div class="banner-content-wrap text-center">

							<p class="banner-info">17 en 18 mei 2019, Carlton President, Utrecht</p>
							<h1 class="banner-title">JoomlaDagen Nederland</h1>

							<div class="countdown wow fadeIn" data-wow-duration="1.5s" data-wow-delay="400ms">
								<div class="counter-item">
									<i class="icon icon-ring-1Asset-1"></i>
									<span class="days">00</span>
									<div class="smalltext">dagen</div>
								</div>
								<div class="counter-item">
									<i class="icon icon-ring-4Asset-3"></i>
									<span class="hours">00</span>
									<div class="smalltext">uur</div>
								</div>
								<div class="counter-item">
									<i class="icon icon-ring-3Asset-2"></i>
									<span class="minutes">00</span>
									<div class="smalltext">minuten</div>
								</div>
								<div class="counter-item counter-seconds">
									<i class="icon icon-ring-4Asset-3"></i>
									<span class="seconds">00</span>
									<div class="smalltext">seconden</div>
								</div>
							</div>

							<div class="banner-btn">
								<a href="https://joomladagen.paydro.com/jd19nl" class="btn">Koop je tickets</a>
							</div>
							<small>Nu met Early Bird korting!</small>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="ts-intro-item section-bg">
		<div class="container">
			<div class="row">
				<div class="col-lg-4">
					<div class="intro-left-content">
						<h2 class="column-title">
							<span>4 redenen om te komen</span>
							Hét jaarlijkse Joomla event
						</h2>
						<p>
							Voor iedereen die websites bouwt met Joomla, van beginner tot (ver)gevorderde en van contentbeheerder tot extensie-ontwikkelaar.
							<br><br>Deel je kennis, ervaring, ideeën en code. Ontmoet andere Joomlers en laat je inspireren!
						</p>
						<a href="https://joomladagen.paydro.com/jd19nl" class="btn">Koop je tickets</a><br>
					</div>
				</div>
				<div class="col-lg-8">
					<div class="row">
						<div class="col-lg-6 wow fadeInUp" data-wow-duration="1.5s" data-wow-delay="400ms">
							<div class="single-intro-text mb-30">
								<i class="icon icon-netwrorking"></i>
								<h3 class="ts-title">Leren op vrijdag</h3>
								<p>
									Vergroot je Joomla-kennis door workshops te volgen van experts en leer hoe je zelf kan bijdragen aan het Joomla-project.
								</p>
								<span class="count-number green">#1</span>
							</div>
						</div>
						<div class="col-lg-6 wow fadeInUp" data-wow-duration="1.5s" data-wow-delay="500ms">
							<div class="single-intro-text mb-30">
								<i class="icon icon-speaker"></i>
								<h3 class="ts-title">Inspiratie op zaterdag</h3>
								<p>
									Volg een uitgebreid aanbod van presentaties en laat je inspireren over de nieuwste technieken, handige tips & tricks en de status van Joomla 4.
								</p>
								<span class="count-number yellow">#2</span>
							</div>
						</div>
						<div class="col-lg-6 wow fadeInUp" data-wow-duration="1.5s" data-wow-delay="600ms">
							<div class="single-intro-text mb-30">
								<i class="icon icon-people"></i>
								<h3 class="ts-title">Vergroot je netwerk</h3>
								<p>
									Op de vrijdagmiddag en -avond organiseren we het social-event! Leer nieuwe mensen kennen en geniet van een diner met Joomlers.
								</p>
								<span class="count-number blue">#3</span>
							</div>
						</div>
						<div class="col-lg-6 wow fadeInUp" data-wow-duration="1.5s" data-wow-delay="700ms">
							<div class="single-intro-text mb-30">
								<i class="icon icon-fun"></i>
								<h3 class="ts-title">Twee dagen plezier!</h3>
								<p>
									Ga met nieuwe inspiratie én energie naar huis om aan je Joomla-projecten te werken na twee dagen Joomla-fun in Utrecht!
								</p>
								<span class="count-number red">#4</span>
							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</section>

	<section class="ts-funfact" style="background-image: url(templates/jd19nl/images/pattern/jd19nl-pattern-blue.png)">
		<div class="container">
			<div class="row">
				<div class="col-lg-3 col-md-6">
					<div class="ts-single-funfact">
						<h3 class="funfact-num"><span class="counterUp" data-counter="2">2</span></h3>
						<h4 class="funfact-title">Dagen</h4>
					</div>
				</div>
				<div class="col-lg-3 col-md-6">
					<div class="ts-single-funfact">
						<h3 class="funfact-num"><span class="counterUp" data-counter="5">5</span></h3>
						<h4 class="funfact-title">Workshops</h4>
					</div>
				</div>
				<div class="col-lg-3 col-md-6">
					<div class="ts-single-funfact">
						<h3 class="funfact-num"><span class="counterUp" data-counter="20">20</span></h3>
						<h4 class="funfact-title">Presentaties</h4>
					</div>
				</div>
				<div class="col-lg-3 col-md-6">
					<div class="ts-single-funfact">
						<h3 class="funfact-num"><span class="counterUp" data-counter="200">200</span>+</h3>
						<h4 class="funfact-title">Deelnemers</h4>
					</div>
				</div>

			</div>
		</div>
	</section>

	<section class="ts-schedule" id="programma">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<h2 class="section-title intro">
						<span>Vrijdag 17 mei</span>
						Joomla DIY & workshops
					</h2>
					<p class="lead text-center">Op vrijdag organiseren we iets nieuws!
						<strong>Joomla DIY</strong> (Do It Yourself) waarbij je in groepen onder leiding van een Joomla-expert aan de slag gaat rond een bepaald onderwerp. Uiteraard met als doel om veel van elkaar te leren! Je kan er ook voor kiezen om in de ochtend en/of middag workshops te volgen voor 25 euro per workshop.
					</p>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">

					<div class="schedule-listing launce">
						<div class="schedule-slot-time">
							<span>08:45 - 09:15</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Ontvangst</h3>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing launce">
						<div class="schedule-slot-time">
							<span>09:15 - 09:30</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Opening</h3>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>09:30 - 12:30</span>
						</div>
						<div class="schedule-slot-info">

							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/algemeen.png" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Joomla DIY
										<strong>Diverse Joomla-experts</strong>
									</h3>
									<p>Onder leiding van diverse experts ga je aan de slag om zelf een steentje bij te dragen aan Joomla en daar tegelijkertijd veel van te leren! Schuif aan bij een tafel en leer van en mét elkaar. Je kan de gehele dag tussen de tafels wisselen en met een aantal mensen een nieuwe tafel rond een bepaald onderwerp starten. Je hebt in ieder geval keuze uit:</p>
									<ul>
										<li>Code schrijven voor Joomla 4</li>
										<li>Testen van Joomla features</li>
										<li>Joomla Marketing in Nederland</li>
										<li>Werken aan de website van joomlacommunity.nl (template en/of content)</li>
										<li>Vertalen van Joomla en extensies</li>
										<li>... wordt aangevuld</li>
									</ul>
									<p>Zelf nog een goed idee voor een onderwerp of vindt je het leuk om een Joomla DIY tafel te begeleiden? Neem dan contact met ons op!</p>
								</div>
							</div>

							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/rachel-walraven.jpg" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">
										<span class="label-workshop">Workshop</span> Je eerste eigen Joomla-template
										<strong>Rachel Walraven</strong>
									</h3>
									<p>Je hebt altijd al een eigen template willen bouwen voor je Joomla! website. Maar het is er nooit van gekomen of je weet niet goed hoe je het aan moet pakken. In deze workshop gaan we dit stap voor stap oppakken. Op basis van een ‘lege’ basistemplate ga je je eigen template maken.  Je leert hoe de template in elkaar zit, hoe je de structuur van je site opbouwt, je moduleposities bepaalt en hoe je de site opmaakt. Als we tijd (en zin) hebben kijken we nog even naar eenvoudige template overrides. Niveau: Beginner, met (basis)kennis van HTML en CSS.</p>
								</div>
							</div>

							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers first" src="images/sprekers/marco-dings.jpg" alt="">
								<img class="schedule-slot-speakers second" src="images/sprekers/rene-kreijveld.jpg" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">
										<span class="label-workshop">Workshop</span> Bouw je eigen Joomla-component
										<strong>Marco Dings & René Kreijveld</strong>
									</h3>
									<p>In deze workshop laten Marco en René zien hoe je met behulp van Joomla Component Builder. </p>
									<p><em>Meer informatie volgt binnenkort.</em></p>
								</div>
							</div>

							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/algemeen.png" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Joomla Administrator certificeringsexamen</h3>
									<p>Word een gecertificeerd Joomla! beheerder! Toon met trots de Joomla! Administrator badge op je website en laat aan klanten en collega's zien dat je beschikt over grondige Joomla! kennis. Op de JoomlaDagen is het mogelijk om examen te doen. Meer informatie over het Joomla! Certification Program is te vinden op
										<a href=" https://certification.joomla.org">https://certification.joomla.org</a>.
									</p>
								</div>
							</div>

						</div>
					</div>


					<div class="schedule-listing launce">
						<div class="schedule-slot-time">
							<span>12:30 - 13:30</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Uitgebreid lunchbuffet</h3>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>13:30 - 16:30</span>
						</div>
						<div class="schedule-slot-info">

							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/algemeen.png" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Joomla DIY
										<strong>Diverse Joomla-experts</strong>
									</h3>
									<p>Onder leiding van diverse experts ga je aan de slag om zelf een steentje bij te dragen aan Joomla en daar tegelijkertijd veel van te leren! Schuif aan bij een tafel en leer van en mét elkaar. Je kan de gehele dag tussen de tafels wisselen en met een aantal mensen een nieuwe tafel rond een bepaald onderwerp starten. Je hebt in ieder geval keuze uit:</p>
									<ul>
										<li>Code schrijven voor Joomla 4</li>
										<li>Testen van Joomla features</li>
										<li>Joomla Marketing in Nederland</li>
										<li>Werken aan de website van joomlacommunity.nl (template en/of content)</li>
										<li>Vertalen van Joomla en extensies</li>
										<li>... wordt aangevuld</li>
									</ul>
									<p>Zelf nog een goed idee voor een onderwerp of vindt je het leuk om een Joomla DIY tafel te begeleiden? Neem dan contact met ons op!</p>
								</div>
							</div>

							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/hans-kuijpers.jpg" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">
										<span class="label-workshop">Workshop</span> Joomla Template Overrides & jLayouts
										<strong>Hans Kuijpers</strong>
									</h3>
									<p>Waarom een 3th party extensie gebruiken als Joomla core de benodigde content al bevat? Een kalender, slideshow, carrousel en smoelenboek. Allemaal met Joomla, zonder uitbreiding van 3th party extensies. Met deze workshop legt Hans Kuijpers aan de hand van vele voorbeelden uit hoe je template overrides en jLayouts toepast. Als deelnemer ga je ook zelf aan de slag. Dus neem je laptop mee en zorg ervoor dat je met een Joomla website aan de slag kunt. We gaan dingen stuk maken, dus pak niet de website die nu live staat. Voorkennis van PHP, HTML, JS en CSS is wel handig. Een dosis Gezond BoerenVerstand is nog veel belangrijker.</p>
								</div>
							</div>

							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/patrick-faasse.jpg" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">
										<span class="label-workshop">Workshop</span> Optimalisatietechnieken voor een snelle site
										<strong>Patrick Faasse</strong>
									</h3>
									<p>Zo scoor je in de 90% bij Yslow & Pagespeed (GTMetrix), zelfs met veel plaatjes en effecten op je website. Tijdens de workshop worden alle aspecten zoals template keuze, extensies, compressie, htaccess, hosting, en foto optimalisatie besproken. Verschillende (eigen) websites worden als praktijkvoorbeeld gebruikt.</p>
								</div>
							</div>

						</div>
					</div>
				</div>

			</div>
		</div>
		<div class="shapes">
			<img class="shap2" src="templates/jd19nl/images/shapes/shape1.png" alt="">
			<img class="shap1" src="templates/jd19nl/images/shapes/shape2.png" alt="">
			<img class="shap3" src="templates/jd19nl/images/shapes/shape3.png" alt="">
		</div>
	</section>

	<section class="ts-schedule section-bg">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<h2 class="section-title intro">
						<span>Vrijdagmiddag en -avond</span>
						Social event
					</h2>
					<p class="lead text-center">Tijdens het social event van de JoomlaDagen leer je andere Joomlers kennen en haal je mooie herinneringen op samen met je bestaande Joomla-vrienden! Misschien ben je op zoek naar samenwerkingen of ben je voor het eerst op de JoomlaDagen (leuk, welkom!). Aan het eind van de avond heb je een groter netwerk!</p>

				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">

					<div class="schedule-listing launce">
						<div class="schedule-slot-time">
							<span>16:15 - 16:45</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Ontvangst social event
										<strong>& einde workshops</strong>
									</h3>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>16:45 - 18:30</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/locatie/borrel.jpg" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Nieuwe Joomlers leren kennen & borrel</h3>
									<p>We vertellen nog niet hoe, maar één ding weten we zeker: je gaat nieuwe Joomlers leren kennen tijdens dit social event! Aansluitend gaan we naar de bar voor de Joomla-borrel. Bij je socialticket zijn hapjes en twee drankjes tijdens de borrel inbegrepen.</p>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>18:30 - 21:00</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/locatie/diner.jpg" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">3-gangen diner</h3>
									<p>Tijd voor diner! In het sfeervolle restaurant laten we ons door de chef-kok en zijn team verrassen met klassieke en hedendaagse creaties met een Mediterrane en Oosterse tint in de vorm van een 3-gangen diner. Ook het diner en twee drankjes tijdens het diner zijn bij het socialticket inbegrepen.</p>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>21.00 - ??:??</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/locatie/bar.jpg" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Evening social</h3>
									<p>Na het diner is de bar nog geopend zodat je de avond met wellicht wel nieuwe Joomla-vrienden kan afsluiten. Als je in het hotel blijft overnachten ben je in een paar stappen in je prachtige hotelkamer om op te laden voor de zaterdag van de JoomlaDagen.</p>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
		<div class="shapes">
			<img class="shap3" src="templates/jd19nl/images/shapes/shape4.png" alt="">
			<img class="shap2" src="templates/jd19nl/images/shapes/shape5.png" alt="">
		</div>
	</section>

	<section class="ts-schedule">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<h2 class="section-title intro">
						<span>Zaterdag 18 mei</span>
						Presentaties
					</h2>
					<p class="lead text-center">Op zaterdag staat er een vol programma op je te wachten! Via presentaties en expertpanels vertellen we je alles over Joomla 4, geven we je inspiratie voor de Joomla-sites waar je aan werkt en tips & ervaringen over het runnen van je Joomla-bedrijf. En wie weet ga jij aan het eind van de dag met de Joomla-bokaal naar huis!</p>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">

					<div class="schedule-listing launce">
						<div class="schedule-slot-time">
							<span>08:45 - 09:15</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Ontvangst</h3>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>09:15 - 09:45</span>
							Keynote
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/alexander-metzler.jpg" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">
										<span class="label-engels">Engels</span> Joomla in Perspective
										<strong>Alexander Metzler (Joomla vice-president)</strong>
									</h3>
									<p>What's Joomla? What are the challenges for an Open Source project in 2019? What prospects does the rapid change of the market offer for our software and for the different (business) models? And why should we be proud of ourselves?</p>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>09:45 - 10:30</span>
							Expertpanel
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/algemeen.png" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Joomla 4
										<strong>Diverse sprekers</strong>
									</h3>
									<p>Ben jij ook zo benieuwd naar de status van Joomla 4 en wanneer deze uitkomt? En wat zijn eigenlijk de nieuwe features? Wordt de migratie lastig? Een panel van sprekers zal korte presentaties geven waarbij deze aspecten aan bod komen en staat klaar om antwoord te geven op al je vragen over Joomla 4.</p>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing launce">
						<div class="schedule-slot-time">
							<span>10:30 - 11:00</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Break
										<strong>met Utrechtse koekjes</strong></h3>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>11:00 - 11:40</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/algemeen.png" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Presentatieronde 1
										<strong>Diverse sprekers</strong>
									</h3>
									<p>Maak een keuze uit een van de presentaties die binnenkort bekend zullen worden gemaakt. Zelf graag een presentatie geven op deze plek? Vul dan nog snel de "<a href="https://goo.gl/forms/nBw6i4YocvOcQywx2">Call for speakers</a>" in!
									</p>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>11:50 - 12:30</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/algemeen.png" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Presentatieronde 2
										<strong>Diverse sprekers</strong>
									</h3>
									<p>Maak een keuze uit een van de presentaties die binnenkort bekend zullen worden gemaakt. Zelf graag een presentatie geven op deze plek? Vul dan nog snel de "<a href="https://goo.gl/forms/nBw6i4YocvOcQywx2">Call for speakers</a>" in!
									</p>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing launce">
						<div class="schedule-slot-time">
							<span>12:30 - 13:30</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Uitgebreid lunchbuffet</h3>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>13:30 - 14:10</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/algemeen.png" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Presentatieronde 3
										<strong>Diverse sprekers</strong>
									</h3>
									<p>Maak een keuze uit een van de presentaties die binnenkort bekend zullen worden gemaakt. Zelf graag een presentatie geven op deze plek? Vul dan nog snel de "<a href="https://goo.gl/forms/nBw6i4YocvOcQywx2">Call for speakers</a>" in!
									</p>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>14:20 - 15:00</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/algemeen.png" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Presentatieronde 4
										<strong>Diverse sprekers</strong>
									</h3>
									<p>Maak een keuze uit een van de presentaties die binnenkort bekend zullen worden gemaakt. Zelf graag een presentatie geven op deze plek? Vul dan nog snel de "<a href="https://goo.gl/forms/nBw6i4YocvOcQywx2">Call for speakers</a>" in!
									</p>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing launce">
						<div class="schedule-slot-time">
							<span>15:00 - 15:30</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Break
										<strong>Met (gezond) lekkers uit de candy wall</strong>
									</h3>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>15:30 - 16:15</span>
							Expertpanel
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/algemeen.png" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Tips & tricks voor je eigen Joomla bedrijf
										<strong>Diverse sprekers</strong>
									</h3>
									<p>Hoe ga jij om met onderhoud van websites? Werk je volgens een vaste prijs of op uurbasis? En hoe stel je het tarief vast? Veel van ons hebben een eigen Joomla bedrijf waarbij we goede Joomla-sites bouwen maar alle "randzaken" best lastig kunnen zijn. Hoe gaan anderen daar mee om? Tijdens dit panel delen diverse experts hun tips & tricks over deze onderwerpen en geven antwoord op jouw vragen.</p>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing">
						<div class="schedule-slot-time">
							<span>16:15 - 17:15</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<img class="schedule-slot-speakers" src="images/sprekers/bokaal.jpg" alt="">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">De Joomla-bokaal
										<strong>Neem jij de bokaal mee naar huis?</strong>
									</h3>
									<p>Ga jij naar huis met de Joomla-bokaal? Binnenkort vertellen we meer over deze nieuwe wedstrijd en hoe je de bokaal kan winnen!</p>
								</div>
							</div>
						</div>
					</div>

					<div class="schedule-listing launce">
						<div class="schedule-slot-time">
							<span>17:15 - 18:30</span>
						</div>
						<div class="schedule-slot-info">
							<div class="schedule-slot-session">
								<div class="schedule-slot-info-content">
									<h3 class="schedule-slot-title">Borrel
										<strong>Sluit de JoomlaDagen af met een hapje en een drankje</strong>
									</h3>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
		<div class="shapes">
			<img class="shap2" src="templates/jd19nl/images/shapes/shape2.png" alt="">
			<img class="shap1" src="templates/jd19nl/images/shapes/shape3.png" alt="">
			<img class="shap3" src="templates/jd19nl/images/shapes/shape6.png" alt="">
		</div>
	</section>

	<section class="ts-pricing gradient" id="tickets" style="background-image: url(templates/jd19nl/images/pattern/jd19nl-pattern-blue.png)">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<h2 class="section-title white">
						<span>Tot 17 april met Early Bird korting!</span>
						Koop je tickets voor de JoomlaDagen
					</h2>
				</div>
			</div>

			<div class="row">
				<div class="col-lg-3">
					<div class="pricing-item wow fadeIn" data-wow-duration="1.5s">
						<img class="pricing-dot " src="templates/jd19nl/images/pricing/dot.png" alt="">
						<div class="ts-pricing-box">
							<div class="ts-pricing-header">
								<h2 class="ts-pricing-name">Vrijdagticket</h2>
								<h3 class="ts-pricing-price">
									<span class="currency">€</span>30
								</h3>
								<small>Na 17 april: 40 euro</small>
							</div>
							<div class="ts-pricing-progress">
								<h4>Inclusief:</h4>
								<ul>
									<li>Toegang op vrijdag<br> tot social-event</li>
									<li>Onbeperkt koffie, thee en sap</li>
									<li>Utrechtse koekjes, handfruit</li>
									<li>Uitgebreid lunchbuffet</li>
									<li>&nbsp;</li>
								</ul>
							</div>
							<div class="promotional-code">
								<a href="https://joomladagen.paydro.com/jd19nl" class="btn pricing-btn">Koop ticket</a>
							</div>
						</div>
						<img class="pricing-dot1" src="templates/jd19nl/images/pricing/dot.png" alt="">
					</div>
				</div>

				<div class="col-lg-3">
					<div class="pricing-item wow fadeIn" data-wow-duration="1.5s">
						<img class="pricing-dot " src="templates/jd19nl/images/pricing/dot.png" alt="">
						<div class="ts-pricing-box">
							<span class="big-dot"></span>
							<div class="ts-pricing-header">
								<h2 class="ts-pricing-name">Workshopticket</h2>
								<h3 class="ts-pricing-price">
									<span class="currency">€</span>25
								</h3>
								<small>Prijs per workshop</small>
							</div>
							<div class="ts-pricing-progress">
								<h4>Inclusief:</h4>
								<ul>
									<li>Toegang tot 1 workshop</li>
									<li><em>Let op: je hebt tevens een vrijdagticket nodig.</em></li>
									<li>&nbsp;</li>
									<li>&nbsp;</li>
									<li>&nbsp;</li>
								</ul>
							</div>
							<div class="promotional-code">
								<a href="https://joomladagen.paydro.com/jd19nl" class="btn pricing-btn">Koop ticket</a>
							</div>
						</div>
						<img class="pricing-dot1" src="templates/jd19nl/images/pricing/dot.png" alt="">
					</div>
				</div>

				<div class="col-lg-3 wow fadeIn" data-wow-duration="1.5s">
					<div class="pricing-item">
						<img class="pricing-dot " src="templates/jd19nl/images/pricing/dot.png" alt="">
						<div class="ts-pricing-box">
							<span class="big-dot"></span>
							<div class="ts-pricing-header">
								<h2 class="ts-pricing-name">Socialticket</h2>
								<h3 class="ts-pricing-price">
									<span class="currency">€</span>50
								</h3>
								<small>&nbsp;</small>
							</div>
							<div class="ts-pricing-progress">
								<h4>Inclusief:</h4>
								<ul>
									<li>Toegang op vrijdag<br>vanaf 16:00 uur</li>
									<li>Social event</li>
									<li>Hapjes & 2 drankjes bij borrel</li>
									<li>3-gangen diner met 2 drankjes</li>
									<li>&nbsp;</li>
								</ul>
							</div>
							<div class="promotional-code">
								<a href="https://joomladagen.paydro.com/jd19nl" class="btn pricing-btn">Koop ticket</a>
							</div>
						</div>
						<img class="pricing-dot1" src="templates/jd19nl/images/pricing/dot.png" alt="">
					</div>
				</div>

				<div class="col-lg-3 wow fadeIn" data-wow-duration="1.5s">
					<div class="pricing-item">
						<img class="pricing-dot " src="templates/jd19nl/images/pricing/dot.png" alt="">
						<div class="ts-pricing-box">
							<span class="big-dot"></span>
							<div class="ts-pricing-header">
								<h2 class="ts-pricing-name">Zaterdag</h2>
								<h3 class="ts-pricing-price">
									<span class="currency">€</span>80
								</h3>
								<small>Na 17 april: 100 euro</small>
							</div>
							<div class="ts-pricing-progress">
								<h4>Inclusief:</h4>
								<ul>
									<li>Toegang op zaterdag<br> gehele dag</li>
									<li>Onbeperkt koffie, thee en sap</li>
									<li>Utrechtse koekjes, handfruit</li>
									<li>Uitgebreid lunchbuffet</li>
									<li>Afsluitende borrel</li>
								</ul>
							</div>
							<div class="promotional-code">
								<a href="https://joomladagen.paydro.com/jd19nl" class="btn pricing-btn">Koop ticket</a>
							</div>
						</div>
						<img class="pricing-dot1" src="templates/jd19nl/images/pricing/dot.png" alt="">
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-lg-12">
					<p class="text-center text-white">
						<br>Stichting Joomla Nederland is vrijgesteld van BTW, er zal dus 0% BTW op de factuur van de tickets staan. De prijzen zijn exclusief transactiekosten.
					</p>
				</div>
			</div>
		</div>
	</section>

	<section class="ts-gallery p-60" id="locatie">
		<div class="container">
			<div class="row">
				<div class="col-lg-10 mx-auto">
					<h2 class="section-title intro">
						<span>De locatie</span>
						Carlton President Utrecht
					</h2>
					<p class="lead text-center">De JoomlaDagen vinden plaats in het recent gerenoveerde Carlton President in Utrecht.<br>Met mooie conferentiezalen, hoogwaardige hotelkamers, gezellig restaurant en bar en goede bereikbaarheid de ideale plek voor de JoomlaDagen!
					</p>
				</div>
			</div>
			<div class="row gallery-wrap">
				<div class="col-lg-8">
					<img class="img-fluid gallery-1" src="images/locatie/grotezaal.jpg" alt="">
				</div>
				<div class="col-lg-4 pl-0">
					<img class="img-fluid gallery-2" src="images/locatie/subzaal.jpg" alt="">
					<img class="img-fluid gallery-3" src="images/locatie/hotelkamer.jpg" alt="">
				</div>
			</div>
			<div class="row">
				<div class="col-lg-10 mx-auto">
					<p class="text-center"><br>Alvast een kijkje nemen? Bekijk dan de
						<a href="https://my.matterport.com/show/?m=v48nabTXFJZ">digitale rondleiding</a>!</p>
				</div>
			</div>
		</div>
		<div class="shapes">
			<img class="shap1" src="templates/jd19nl/images/shapes/shape6.png" alt="">
			<img class="shap2" src="templates/jd19nl/images/shapes/shape4.png" alt="">
		</div>
	</section>

	<section class="ts-map-direction section-bg">
		<div class="container">
			<div class="row">
				<div class="col-lg-6">
					<h2 class="column-title ">
						<span>Overnachten & bereikbaarheid</span>
						Carlton President
					</h2>
					<p class="derecttion-vanue">
						Floraweg 25<br>
						3542 DX Utrecht<br>
						<a href="https://www.carlton.nl/president-hotel-utrecht">https://www.carlton.nl/president-hotel-utrecht</a>
					</p>

					<div class="ts-map-tabs">
						<ul class="nav" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" href="#overnachten" role="tab" data-toggle="tab">Overnachten</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="#auto" role="tab" data-toggle="tab">Met de auto</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="#ov" role="tab" data-toggle="tab">Met openbaar vervoer</a>
							</li>
						</ul>

						<div class="tab-content direction-tabs">
							<div role="tabpanel" class="tab-pane active" id="overnachten">
								<div class="direction-tabs-content">

									<p>Voor de bezoekers van de JoomlaDagen is er een speciaal aanbod, overnacht in een Premium of Deluxe kamer voor
										<strong>115 euro</strong> bij eenpersoonsgebruik en
										<strong>132,50 euro</strong> bij tweepersoonsgebruik.</p>
									<ul class="no-lines">
										<li>Prachtig vernieuwde Premium of Deluxe kamer</li>
										<li>Inclusief uitgebreid ontbijtbuffet (t.w.v. 19,50 per persoon)</li>
										<li>Inclusief btw, exclusief 6% stadsbelasting</li>
										<li>Gebruik van het Leisure Centre (sauna, Turks stoombad, whirlpool en fitness)</li>
										<li>Gratis parkeren</li>
									</ul>
									<p>Om gebruik te maken van dit aanbod mail je naar
										<a href="mailto:reservations@president.carlton.nl">reservations@president.carlton.nl</a> met daarin de vermelding dat het om de JoomlaDagen gaat en de datum van overnachting, vol=vol!
									</p>
								</div>
							</div>

							<div role="tabpanel" class="tab-pane" id="auto">
								<div class="direction-tabs-content">
									<p>Door de locatie vlakbij de A2 tussen Amsterdam en Utrecht is het hotel buitengewoon goed gelegen. Parkeren kan gratis op het parkeerterrein met ruim 230 parkeerplaatsen.</p>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane fade" id="ov">
								<div class="direction-tabs-content">
									<h4>Vanaf Utrecht CS</h4>
									<p>Neem vanaf Utrecht CS buslijn 37, richting Maarssen. Stap na ongeveer 15 minuten uit bij bushalte Zonnebaan.</p>
									<h4>Vanaf station Maarssen</h4>
									<p>Neem vanaf station Maarssen buslijn 37 richting Utrecht CS. Stap na ongeveer 5 minuten uit bij bushalte. Het hotel is 300 meter van de bushalte gelegen.</p>
									<br>
									<p>Controleer vooraf altijd de laatste actuele reisinformatie via
										<a href="https://9292.nl">9292.nl</a>.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-5 offset-lg-1">
					<div class="ts-map">
						<div class="mapouter">
							<div class="gmap_canvas">
								<a href="https://www.google.nl/maps/place/Carlton+President+Hotel/@52.1207937,5.0206722,14z/data=!4m5!3m4!1s0x47c66fd67708f46d:0x2a1d1cfc1c011850!8m2!3d52.12079!4d5.0381863">
									<img src="images/locatie/carlton-president-locatie.png"/>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section id="sponsors" class="ts-sponsors">
		<div class="container">
			<div class="row text-center">
				<div class="col-lg-12 mx-auto">
					<h2 class="section-title text-center">
						<span>Zonder sponsoren geen JoomlaDagen!</span>
						Onze sponsors
					</h2>
				</div>
			</div>

			<div class="sponsors-wrap">
				<h3 class="sponsor-title text-center">Diamant sponsor</h3>
				<div class="row sponsor-padding text-center justify-content-md-center">
					<div class="col-lg-5">
						<a href="https://www.regularlabs.com" class="sponsors-logo">
							<img class="img-fluid" src="images/sponsors/regularlabs.png" alt="">
						</a>
					</div>
				</div>
			</div>

			<div class="sponsors-wrap">
				<h3 class="sponsor-title text-center">Gouden sponsors</h3>
				<div class="row sponsor-padding text-center justify-content-md-center">
					<div class="col-lg-4">
						<a href="https://joomladagen.nl/sponsoring.pdf" class="sponsors-logo placeholder">
							Wil jij hier staan?
						</a>
					</div>
					<div class="col-lg-4">
						<a href="https://joomladagen.nl/sponsoring.pdf" class="sponsors-logo placeholder">
							Wil jij hier staan?
						</a>
					</div>
				</div>
			</div>

			<div class="sponsors-wrap">
				<h3 class="sponsor-title text-center">Zilveren sponsors</h3>
				<div class="row sponsor-padding text-center justify-content-md-center">
					<div class="col-lg-3">
						<a href="https://joomladagen.nl/sponsoring.pdf" class="sponsors-logo placeholder">
							Wil jij hier staan?
						</a>
					</div>
					<div class="col-lg-3">
						<a href="https://www.cyberfusion.nl" class="sponsors-logo">
							<img class="img-fluid" src="images/sponsors/cyberfusion.png" alt="">
						</a>
					</div>
					<div class="col-lg-3">
						<a href="https://joomladagen.nl/sponsoring.pdf" class="sponsors-logo placeholder">
							Wil jij hier staan?
						</a>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-lg-12 mx-auto">
					<div class="general-btn text-center">
						<a class="btn" href="https://joomladagen.nl/sponsoring.pdf">Sponsor worden?</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<div class="footer-area">

		<section class="ts-book-seat" style="background-image: url(images/joomlacommunity-welkom.jpg)">
			<div class="container">
				<div class="row">
					<div class="col-lg-8 mx-auto">
						<div class="book-seat-content text-center mb-70">
							<h2 class="section-title white">
								<span>Tot 17 april 2019</span>
								Early Bird tickets met korting
							</h2>
							<a href="https://joomladagen.paydro.com/jd19nl" class="btn">Bestel nu je tickets</a>
						</div>
					</div>
				</div>
				<div class="ts-footer-newsletter">
					<div class="ts-newsletter">
						<div class="row">
							<div class="col-lg-8 mx-auto">
								<div class="ts-newsletter-content">
									<h2 class="section-title">
										<span>Blijf op de hoogte van het laatste nieuws</span>
										Schrijf je in op onze nieuwsbrief
									</h2>
								</div>
								<div class="newsletter-form">
									<form action="https://joomladagen.us1.list-manage.com/subscribe/post?u=3301c1f6d25798a6137bac5f1&amp;id=185540decf" method="post" class="media align-items-end">
										<div class="email-form-group media-body">
											<input type="email" name="EMAIL" id="newsletter-form-email" class="form-control" placeholder="E-mailadres" autocomplete="off" required="" id="mce-EMAIL">
										</div>
										<div class="d-flex ts-submit-btn">
											<button class="btn btn-primary">Aanmelden</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<footer class="ts-footer">
			<div class="container">
				<div class="row">
					<div class="col-lg-12 mx-auto">
						<div class="ts-footer-social text-center mb-30">
							<ul>
								<li>
									<a href="https://facebook.com/joomladagen"><i class="fa fa-facebook"></i></a>
								</li>
								<li>
									<a href="https://twitter.com/joomladagen"><i class="fa fa-twitter"></i></a>
								</li>
								<li>
									<a href="https://instagram.com/joomladagen"><i class="fa fa-instagram"></i></a>
								</li>
							</ul>
						</div>

						<div class="copyright-text text-center">
							<p>De JoomlaDagen worden georganiseerd door
								<a href="https://www.stichtingjoomlanederland.nl/">Stichting Joomla Nederland</a>. Vragen?
								<a href="mailto:info@joomladagen.nl">info@joomladagen.nl</a></p>
							<p>
								<small>
									JoomlaDay™ events are officially recognized and licensed by, but not organized or operated by, Open Source Matters, Inc. (OSM) on behalf of The Joomla! Project™. Each JoomlaDay is managed independently by a local community. Use of the Joomla!® name, symbol, logo, JoomlaDay,™ and JDay™ and related trademarks is licensed by Open Source Matters, Inc.
								</small>
							</p>
						</div>
					</div>
				</div>
			</div>
		</footer>

		<div class="BackTo">
			<a href="#" class="fa fa-angle-up" aria-hidden="true"></a>
		</div>

	</div>
</div>
</body>
</html>
