<?php
/**
 * @package     perfecttemplate
 * @copyright   Copyright (c) Perfect Web Team / perfectwebteam.nl
 * @license     GNU General Public License version 3 or later
 */

defined('_JEXEC') or die();

class PWTTemplateHelper
{
	const template = 'perfecttemplate';

	/**
	 * Method to manually override the META-generator
	 *
	 * @access public
	 *
	 * @param string $generator
	 *
	 * @return null
	 *
	 * @since  PerfectSite2.1.0
	 */
	static public function setGenerator($generator)
	{
		JFactory::getDocument()->setGenerator($generator);
	}

	/**
	 * Method to set some Meta data
	 *
	 * @since PerfectSite2.1.0
	 */
	static public function setMetadata()
	{
		$doc    = JFactory::getDocument();
		$config = JFactory::getConfig();

		$doc->setCharset('utf8');
		$doc->setMetaData('X-UA-Compatible', 'IE=edge', true);
		$doc->setMetaData('viewport', 'width=device-width, initial-scale=1.0');
		$doc->setMetaData('mobile-web-app-capable', 'yes');
		$doc->setMetaData('apple-mobile-web-app-capable', 'yes');
		$doc->setMetaData('apple-mobile-web-app-status-bar-style', 'black');
		$doc->setMetaData('apple-mobile-web-app-title', $config->get('sitename'));
		$doc->setMetaData('apple-itunes-app', 'app-id=356566983');
		$doc->setMetaData('google-play-app', 'app-id=nl.omroep.npo.radio1');
		$doc->setGenerator($config->get('sitename'));
	}

	/**
	 * Method to set Favicon
	 *
	 * @since PerfectSite2.1.0
	 */
	static public function setFavicon()
	{
		$doc = JFactory::getDocument();

		$doc->addHeadLink('templates/' . self::template . '/images/favicon.ico', 'shortcut icon', 'rel', array('type' => 'image/ico'));
		$doc->addHeadLink('templates/' . self::template . '/images/favicon.png', 'shortcut icon', 'rel', array('type' => 'image/png'));
		$doc->addHeadLink('templates/' . self::template . '/images/xtouch-icon.png', 'apple-touch-icon', 'rel', array('type' => 'image/png'));
	}

	/**
	 * Method to return the current Menu Item ID
	 *
	 * @since PerfectSite2.1.0
	 */
	static public function getItemId()
	{
		return JFactory::getApplication()->input->getInt('Itemid');
	}

	/**
	 * Method to get the current sitename
	 *
	 * @since PerfectSite2.1.0
	 */
	static public function getSitename()
	{
		return JFactory::getConfig()->get('config.sitename');
	}

	/**
	 * Method to fetch the current path
	 *
	 * @access public
	 *
	 * @param string $output Output type
	 *
	 * @return mixed
	 * @since  PerfectSite2.1.0
	 */
	static public function getPath($output = 'array')
	{
		$uri  = JURI::getInstance();
		$path = $uri->getPath();
		$path = preg_replace('/^\//', '', $path);
		if ($output == 'array')
		{
			$path = explode('/', $path);

			return $path;
		}

		return $path;
	}

	/**
	 * get PageClass set with Menu Item
	 *
	 * @return mixed
	 * @since  PerfectSite2.1.0
	 */
	static public function getPageClass()
	{
		$activeMenu = JFactory::getApplication()->getMenu()->getActive();
		$pageclass  = ($activeMenu) ? $activeMenu->params->get('pageclass_sfx', '') : '';

		return $pageclass;
	}

	/**
	 * Generate a list of useful CSS classes for the body
	 *
	 * @param null
	 *
	 * @return bool
	 * @since  PerfectSite2.1.0
	 */
	static public function getBodySuffix()
	{
		$input = JFactory::getApplication()->input;

		$classes   = array();
		$classes[] = 'option-' . str_replace('_', '-', $input->getCmd('option'));
		$classes[] = 'view-' . $input->getCmd('view');
		$classes[] = 'page-' . self::getItemId();
		$classes[] = PWTTemplateHelper::getPageClass();

		if (self::isHome())
		{
			$classes[] = 'path-home';
		}

		if (!self::isHome())
		{
			$classes[] = 'path-' . implode('-', self::getPath('array'));
		}

		//$classes[] = 'home-' . (int) self::isHome();

		return implode(' ', $classes);
	}

	/**
	 * Method to determine whether the current page is the Joomla! homepage
	 *
	 * @access public
	 *
	 * @param null
	 *
	 * @return bool
	 * @since  PerfectSite2.1.0
	 */
	static public function isHome()
	{
		// Fetch the active menu-item
		$activeMenu = JFactory::getApplication()->getMenu()->getActive();

		// Return whether this active menu-item is home or not
		return (boolean) ($activeMenu) ? $activeMenu->home : false;
	}

	/**
	 * Remove unwanted CSS
	 * @since  PerfectSite2.1.0
	 */
	static public function unloadCss()
	{
		$doc = JFactory::getDocument();

		$unset_css = array('com_finder', 'com_rsform');
		foreach ($doc->_styleSheets as $name => $style)
		{
			foreach ($unset_css as $css)
			{
				if (strpos($name, $css) !== false)
				{
					unset($doc->_styleSheets[$name]);
				}
			}
		}
	}

	/**
	 * Load CSS
	 * @since  PerfectSite2.1.0
	 */
	static public function loadCss()
	{
		JFactory::getDocument()->addStyleSheet('templates/' . self::template . '/css/style.css');
	}

	/**
	 * Remove unwanted JS
	 * @since  PerfectSite2.1.0
	 */
	static public function unloadJs()
	{
		$doc = JFactory::getDocument();

		// Call JavaScript to be able to unset it correctly
		JHtml::_('behavior.framework');
		JHtml::_('bootstrap.framework');
		JHtml::_('jquery.framework');
		JHtml::_('bootstrap.tooltip');

		// Unset unwanted JavaScript
		unset($doc->_scripts[$doc->baseurl . '/media/system/js/mootools-core.js']);
		unset($doc->_scripts[$doc->baseurl . '/media/system/js/mootools-more.js']);
		unset($doc->_scripts[$doc->baseurl . '/media/system/js/caption.js']);
		unset($doc->_scripts[$doc->baseurl . '/media/system/js/core.js']);
		//unset($doc->_scripts[$doc->baseurl . '/media/jui/js/jquery.min.js']);
		unset($doc->_scripts[$doc->baseurl . '/media/jui/js/jquery-noconflict.js']);
		unset($doc->_scripts[$doc->baseurl . '/media/jui/js/jquery-migrate.min.js']);
		unset($doc->_scripts[$doc->baseurl . '/media/jui/js/bootstrap.min.js']);
		unset($doc->_scripts[$doc->baseurl . '/media/system/js/tabs-state.js']);
		unset($doc->_scripts[$doc->baseurl . '/media/system/js/validate.js']);

		if (isset($doc->_script['text/javascript']))
		{
			$doc->_script['text/javascript'] = preg_replace('%jQuery\(window\)\.on\(\'load\'\,\s*function\(\)\s*\{\s*new\s*JCaption\(\'img.caption\'\);\s*}\s*\);\s*%', '', $doc->_script['text/javascript']);
			$doc->_script['text/javascript'] = preg_replace("%\s*jQuery\(document\)\.ready\(function\(\)\{\s*jQuery\('\.hasTooltip'\)\.tooltip\(\{\"html\":\s*true,\"container\":\s*\"body\"\}\);\s*\}\);\s*%", '', $doc->_script['text/javascript']);
			$doc->_script['text/javascript'] = preg_replace('%\s*jQuery\(function\(\$\)\{\s*\$\(\"\.hasTooltip\"\)\.tooltip\(\{\"html\":\s*true,\"container\":\s*\"body\"\}\);\s*\}\);\s*%', '', $doc->_script['text/javascript']);

			// Unset completly if empty
			if (empty($doc->_script['text/javascript']))
			{
				unset($doc->_script['text/javascript']);
			}
		}
	}

	/**
	 * Load JS
	 *
	 * @since  PerfectSite2.1.0
	 */
	static public function loadJs()
	{
		$doc = JFactory::getDocument();

		$doc->addScript('templates/' . self::template . '/js/modernizr.js');
		$doc->addScript('templates/' . self::template . '/js/scripts.js');
	}

	/**
	 * Load script for Vanilla JS Responsive Menu
	 * @since  PerfectSite2.1.0
	 */
	static public function loadResponsiveMenuJS()
	{
		$javascript = '<!-- Vanilla JS Responsive Menu -->
function hasClass(e,t){return e.className.match(new RegExp("(\\s|^)"+t+"(\\s|$)"))}var el=document.documentElement;var cl="no-js";if(hasClass(el,cl)){var reg=new RegExp("(\\s|^)"+cl+"(\\s|$)");el.className=el.className.replace(reg," js")}
		';

		JFactory::getDocument()->addScriptDeclaration($javascript);
	}

	/**
	 * Load custom font in localstorage
	 *
	 * @param $fontname
	 *
	 * @since  PerfectSite2.1.0
	 */
	static public function localstorageFont($fontname)
	{
		$javascript = "<!-- Local Storage for font -->
  !function () {
    function addFont(font) {
      var style = document.createElement('style');
      style.rel = 'stylesheet';
      document.head.appendChild(style);
      style.textContent = font
    }
    var font = '" . $fontname . "';
    try {
      if (localStorage[font])addFont(localStorage[font]); else {
        var request = new XMLHttpRequest;
        request.open('GET', '" . JURI::Base() . "templates/" . self::template . "/css/font.css', !0);
        request.onload = function () {
          request.status >= 200 && request.status < 400 && (localStorage[font] = request.responseText, addFont(request.responseText))
        }, request.send()
      }
    } catch (d) {
    }
  }();";
		JFactory::getDocument()->addScriptDeclaration($javascript);
	}

	/**
	 * Method to detect a certain browser type
	 *
	 * @access public
	 *
	 * @param string $shortname
	 *
	 * @return string
	 * @since  PerfectSite2.1.0
	 */
	static public function isBrowser($shortname = 'ie6')
	{
		jimport('joomla.environment.browser');
		$browser = JBrowser::getInstance();

		switch ($shortname)
		{
			case 'edge':
				$rt = (stristr($browser->getAgentString(), 'edge')) ? true : false;
				break;
			case 'firefox':
			case 'ff':
				$rt = (stristr($browser->getAgentString(), 'firefox')) ? true : false;
				break;
			case 'ie':
				$rt = ($browser->getBrowser() == 'msie') ? true : false;
				break;
			case 'ie6':
				$rt = ($browser->getBrowser() == 'msie' && $browser->getVersion() == '6.0') ? true : false;
				break;
			case 'ie7':
				$rt = ($browser->getBrowser() == 'msie' && $browser->getVersion() == '7.0') ? true : false;
				break;
			case 'ie8':
				$rt = ($browser->getBrowser() == 'msie' && $browser->getVersion() == '8.0') ? true : false;
				break;
			case 'ie9':
				$rt = ($browser->getBrowser() == 'msie' && $browser->getVersion() == '9.0') ? true : false;
				break;
			case 'lteie9':
				$rt = ($browser->getBrowser() == 'msie' && $browser->getMajor() <= 9) ? true : false;
				break;
			default:
				$rt = (stristr($browser->getAgentString(), $shortname)) ? true : false;
				break;
		}

		return $rt;
	}

	/**
	 * load Analytics
	 *
	 * @param $template
	 *
	 * @return array
	 * @since  PerfectSite2.1.0
	 */
	static public function getAnalytics($analytics = null, $analyticsId = null)
	{
		$doc = JFactory::getDocument();

		switch ($analytics)
		{
			case 0:
				break;
			case 1:
				// Universal Google Universal Analytics - loaded in head
				if ($analyticsId)
				{
					$analyticsScript = "

        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', '" . $analyticsId . "', 'auto');
        ga('send', 'pageview');
      ";
					$doc->addScriptDeclaration($analyticsScript);
				}
				break;
			case 2:
				// Google Tag Manager - party loaded in head
				if ($analyticsId)
				{
					$analyticsScript = "

  <!-- Google Tag Manager -->
  (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . $analyticsId . "');
  <!-- End Google Tag Manager -->

          ";
					$doc->addScriptDeclaration($analyticsScript);

					// Google Tag Manager - partly loaded directly after body
					$analyticsScript = "<!-- Google Tag Manager -->
<noscript><iframe src=\"//www.googletagmanager.com/ns.html?id=" . $analyticsId . "\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
<!-- End Google Tag Manager -->
";

					return array('script' => $analyticsScript, 'position' => 'after_body_start');
				}
				break;
			case 3:
				// Mixpanel.com - loaded in head
				if ($analyticsId)
				{
					$analyticsScript = "

<!-- start Mixpanel -->(function(e,b){if(!b.__SV){var a,f,i,g;window.mixpanel=b;b._i=[];b.init=function(a,e,d){function f(b,h){var a=h.split(\".\");2==a.length&&(b=b[a[0]],h=a[1]);b[h]=function(){b.push([h].concat(Array.prototype.slice.call(arguments,0)))}}var c=b;\"undefined\"!==typeof d?c=b[d]=[]:d=\"mixpanel\";c.people=c.people||[];c.toString=function(b){var a=\"mixpanel\";\"mixpanel\"!==d&&(a+=\".\"+d);b||(a+=\" (stub)\");return a};c.people.toString=function(){return c.toString(1)+\".people (stub)\"};i=\"disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user\".split(\" \");
for(g=0;g<i.length;g++)f(c,i[g]);b._i.push([a,e,d])};b.__SV=1.2;a=e.createElement(\"script\");a.type=\"text/javascript\";a.async=!0;a.src=\"undefined\"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:\"file:\"===e.location.protocol&&\"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js\".match(/^\/\//)?\"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js\":\"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js\";f=e.getElementsByTagName(\"script\")[0];f.parentNode.insertBefore(a,f)}})(document,window.mixpanel||[]);
mixpanel.init(\"" . $analyticsId . "\");<!-- end Mixpanel -->
      ";
					$doc->addScriptDeclaration($analyticsScript);
				}
				break;
		}
	}

	static public function renderHelixTitle()
	{

		$menuitem = JFactory::getApplication()->getMenu()->getActive(); // get the active item

		if (!$menuitem)
		{
			return false;
		}

		$params = $menuitem->params; // get the menu params

		if (!$params->get('enable_page_title', 0))
		{
			return false;
		}

		$page_title          = $menuitem->title;
		$page_title_alt      = $params->get('page_title_alt');
		$page_subtitle       = $params->get('page_subtitle');
		$page_title_bg_color = $params->get('page_title_bg_color');
		$page_title_bg_image = $params->get('page_title_bg_image');

		$style = '';

		if ($page_title_bg_color)
		{
			$style .= 'background-color: ' . $page_title_bg_color . ';';
		}

		if ($page_title_bg_image)
		{
			$style .= 'background-image: url(' . JURI::root(true) . '/' . $page_title_bg_image . ');';
		}

		if ($style)
		{
			$style = 'style="' . $style . '"';
		}

		if ($page_title_alt)
		{
			$page_title = $page_title_alt;
		}

		$output = '';

		$output .= '<div class="main__title title"' . $style . '>';
		$output .= '    <div class="title__wrapper">';

		$output .= '        <h1 class="title__text">' . $page_title . '</h1>';

		if ($page_subtitle)
		{
			$output .= '		<h2>' . $page_subtitle . '</h2>';
		}

		$output .= '    </div>';
		$output .= '</div>';

		$output .= '<jdoc:include type="modules" name="breadcrumb" style="none" />';

		return $output;

	}

}
