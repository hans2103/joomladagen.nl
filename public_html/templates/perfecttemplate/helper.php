<?php
/*
 * @package     perfecttemplate
 * @copyright   Copyright (c) Perfect Web Team / perfectwebteam.nl
 * @license     GNU General Public License version 3 or later
 */

// Prevent direct access
defined('_JEXEC') or die();

// Define the base-path of this template
define('TEMPLATE_BASE', dirname(__FILE__));

// Instantiate the helper class
$helper = new ThisTemplateHelper();

// initiate changes to HEAD
$helper->setMetadata($this);
$helper->unloadCss();
$helper->unloadJs();
$helper->loadCss();
$helper->loadJs();
$helper->loadResponsiveMenuJS();

// Font
$helper->localstorageFont('Clear Sans');

// Analytics
$analyticsData = $helper->getAnalytics($this);

/**
 * ThisTemplate class
 */
class ThisTemplateHelper
{
	/**
	 * Template settings
	 */
	public $settings = array(
		'debug'       => false,
		'unset_css'   => array('com_finder', 'com_rsform', 'com_docman'),
		'analytics'   => 0, // 0 = none, GA = Universal Google Analytics, GTM = Google Tag Manager, Mix = Mixpanel
		'analyticsid' => '',
		'pagelayout'  => '1column',
	);

	/**
	 * Document instance
	 */
	protected $doc = null;
	/**
	 * Application instance
	 */
	protected $app = null;
	/**
	 * JInput instance
	 */
	protected $input = null;
	/**
	 * Menu instance
	 */
	protected $menu = null;

	/**
	 * Constructor called when instantiating this class
	 */
	public function __construct()
	{
		// Fetch system variables
		$this->doc      = JFactory::getDocument();
		$this->app      = JFactory::getApplication();
		$this->config   = JFactory::getConfig();
		$this->input    = $this->app->input;
		$this->menu     = $this->app->getMenu();
		$this->template = $this->app->getTemplate();
	}

	/**
	 * Method to manually override the META-generator
	 *
	 * @access public
	 *
	 * @param string $generator
	 *
	 * @return null
	 */
	public function setGenerator($generator)
	{
		$this->doc->setGenerator($generator);
	}

	/**
	 * Method to set some Meta data
	 *
	 * @param $template
	 */
	public function setMetadata($template)
	{
		$this->doc->setCharset('utf8');
		$this->doc->setMetaData('X-UA-Compatible', 'IE=edge', true);
		$this->doc->setMetaData('viewport', 'width=device-width, initial-scale=1.0');
		$this->doc->setMetaData('mobile-web-app-capable', 'yes');
		$this->doc->setMetaData('apple-mobile-web-app-capable', 'yes');
		$this->doc->setMetaData('apple-mobile-web-app-status-bar-style', 'black');
		$this->doc->setMetaData('apple-mobile-web-app-title', $this->config->get('sitename'));
		$this->doc->setGenerator($this->config->get('sitename'));
	}

	/**
	 * Method to return the current Menu Item ID
	 *
	 * @access public
	 *
	 * @param null
	 *
	 * @return int
	 */
	public function getItemId()
	{
		return $this->input->getInt('Itemid');
	}

	/**
	 * Method to fetch the current path
	 *
	 * @access public
	 *
	 * @param string $output Output type
	 *
	 * @return mixed
	 */
	public function getPath($output = 'array')
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
	 * Method to get the current sitename
	 *
	 * @access public
	 *
	 * @param null
	 *
	 * @return string
	 */
	public function getSitename()
	{
		return JFactory::getConfig()->get('config.sitename');
	}

	/**
	 * Generate a list of useful CSS classes for the body
	 *
	 * @param null
	 *
	 * @return bool
	 */
	public function getBodySuffix()
	{
		$classes   = array();
		$classes[] = 'option-' . str_replace('_', '-', $this->input->getCmd('option'));
		$classes[] = 'view-' . $this->input->getCmd('view');
		//$classes[] = 'layout-' . $this->input->getCmd('layout');
		$classes[] = 'page-' . $this->getItemId();
		$classes[] = $this->getPageClass();

		if ($this->isHome())
		{
			$classes[] = 'path-home';
		}
		else
		{
			$classes[] = 'path-' . implode('-', $this->getPath('array'));
		}
		$classes[] = 'home-' . (int) $this->isHome();

		return implode(' ', $classes);
	}

	/**
	 * get PageClass set with Menu Item
	 *
	 * @return mixed
	 */
	public function getPageClass()
	{
		$activeMenu = $this->menu->getActive();
		$pageclass  = ($activeMenu) ? $activeMenu->params->get('pageclass_sfx', '') : '';

		return $pageclass;
	}

	/**
	 * Method to determine whether the current page is the Joomla! homepage
	 *
	 * @access public
	 *
	 * @param null
	 *
	 * @return bool
	 */
	public function isHome()
	{
		// Fetch the active menu-item
		$activeMenu = $this->menu->getActive();

		// Return whether this active menu-item is home or not
		return (boolean) ($activeMenu) ? $activeMenu->home : false;
	}

	/**
	 * Remove unwanted CSS
	 */
	public function unloadCss()
	{
		$unset_css = $this->settings['unset_css'];
		foreach ($this->doc->_styleSheets as $name => $style)
		{
			foreach ($unset_css as $css)
			{
				if (strpos($name, $css) !== false)
				{
					unset($this->doc->_styleSheets[$name]);
				}
			}
		}
	}

	/**
	 * Load CSS
	 */
	public function loadCss()
	{
		$this->doc->addStyleSheet('templates/' . $this->template . '/css/style.css');
	}

	/**
	 * Remove unwanted JS
	 */
	public function unloadJs()
	{
		return;

		// Call JavaScript to be able to unset it correctly
		JHtml::_('behavior.framework');
		JHtml::_('bootstrap.framework');
		JHtml::_('jquery.framework');
		JHtml::_('bootstrap.tooltip');

		// Unset unwanted JavaScript
		unset($this->doc->_scripts[$this->doc->baseurl . '/media/system/js/mootools-core.js']);
		unset($this->doc->_scripts[$this->doc->baseurl . '/media/system/js/mootools-more.js']);
		unset($this->doc->_scripts[$this->doc->baseurl . '/media/system/js/caption.js']);
		unset($this->doc->_scripts[$this->doc->baseurl . '/media/system/js/core.js']);
		//unset($this->doc->_scripts[$this->doc->baseurl . '/media/jui/js/jquery.min.js']);
		//unset($this->doc->_scripts[$this->doc->baseurl . '/media/jui/js/jquery-noconflict.js']);
		//unset($this->doc->_scripts[$this->doc->baseurl . '/media/jui/js/jquery-migrate.min.js']);
		//unset($this->doc->_scripts[$this->doc->baseurl . '/media/jui/js/bootstrap.min.js']);
		unset($this->doc->_scripts[$this->doc->baseurl . '/media/system/js/tabs-state.js']);
		unset($this->doc->_scripts[$this->doc->baseurl . '/media/system/js/validate.js']);

		if (isset($this->doc->_script['text/javascript']))
		{
			$this->doc->_script['text/javascript'] = preg_replace('%jQuery\(window\)\.on\(\'load\'\,\s*function\(\)\s*\{\s*new\s*JCaption\(\'img.caption\'\);\s*}\s*\);\s*%', '', $this->doc->_script['text/javascript']);
			$this->doc->_script['text/javascript'] = preg_replace("%\s*jQuery\(document\)\.ready\(function\(\)\{\s*jQuery\('\.hasTooltip'\)\.tooltip\(\{\"html\":\s*true,\"container\":\s*\"body\"\}\);\s*\}\);\s*%", '', $this->doc->_script['text/javascript']);
			$this->doc->_script['text/javascript'] = preg_replace('%jQuery(.*)\.hasTooltip(.*)%', '', $this->doc->_script['text/javascript']);


			// Unset completly if empty
			if (empty($this->doc->_script['text/javascript']))
			{
				unset($this->doc->_script['text/javascript']);
			}
		}
	}

	/**
	 * Load JS
	 *
	 */
	public function loadJs()
	{
		$this->doc->addScript('templates/' . $this->template . '/js/modernizr.js');
		$this->doc->addScript('templates/' . $this->template . '/js/scripts.js');
	}

	/**
	 * Load script for Vanilla JS Responsive Menu
	 */
	public function loadResponsiveMenuJS()
	{
		$javascript = '<!-- Vanilla JS Responsive Menu -->
function hasClass(e,t){return e.className.match(new RegExp("(\\s|^)"+t+"(\\s|$)"))}var el=document.documentElement;var cl="no-js";if(hasClass(el,cl)){var reg=new RegExp("(\\s|^)"+cl+"(\\s|$)");el.className=el.className.replace(reg," js")}
		';
		$this->doc->addScriptDeclaration($javascript);
	}

	/**
	 * Load custom font in localstorage
	 *
	 * @param $fontname
	 */

	public function localstorageFont($fontname)
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
        request.open('GET', '" . JURI::Base() . "templates/" . $this->template . "/css/font.css', !0);
        request.onload = function () {
          request.status >= 200 && request.status < 400 && (localStorage[font] = request.responseText, addFont(request.responseText))
        }, request.send()
      }
    } catch (d) {
    }
  }();";
		$this->doc->addScriptDeclaration($javascript);
	}

	/**
	 * Method to detect a certain browser type
	 *
	 * @access public
	 *
	 * @param string $shortname
	 *
	 * @return string
	 */
	public function isBrowser($shortname = 'ie6')
	{
		jimport('joomla.environment.browser');
		$browser = JBrowser::getInstance();

		$rt = false;
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
	 */
	public function getAnalytics($template)
	{
		$analytics   = $this->settings['analytics'];
		$analyticsId = $this->settings['analyticsid'];

		// Analytics
		switch ($analytics)
		{
			case 0:
				break;
			case GA:
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
					$this->doc->addScriptDeclaration($analyticsScript);
				}
				break;
			case GTM:
				// Google Tag Manager - party loaded in head
				if ($analyticsId)
				{
					$analyticsScript = "

  <!-- Google Tag Manager -->
  (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . $analyticsId . "');
  <!-- End Google Tag Manager -->

          ";
					$this->doc->addScriptDeclaration($analyticsScript);

					// Google Tag Manager - partly loaded directly after body
					$analyticsScript = "<!-- Google Tag Manager -->
<noscript><iframe src=\"//www.googletagmanager.com/ns.html?id=" . $analyticsId . "\" height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>
<!-- End Google Tag Manager -->
";

					return array('script' => $analyticsScript, 'position' => 'after_body_start');
				}
				break;
			case Mix:
				// Mixpanel.com - loaded in head
				if ($analyticsId)
				{
					$analyticsScript = "

<!-- start Mixpanel -->(function(e,b){if(!b.__SV){var a,f,i,g;window.mixpanel=b;b._i=[];b.init=function(a,e,d){function f(b,h){var a=h.split(\".\");2==a.length&&(b=b[a[0]],h=a[1]);b[h]=function(){b.push([h].concat(Array.prototype.slice.call(arguments,0)))}}var c=b;\"undefined\"!==typeof d?c=b[d]=[]:d=\"mixpanel\";c.people=c.people||[];c.toString=function(b){var a=\"mixpanel\";\"mixpanel\"!==d&&(a+=\".\"+d);b||(a+=\" (stub)\");return a};c.people.toString=function(){return c.toString(1)+\".people (stub)\"};i=\"disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user\".split(\" \");
for(g=0;g<i.length;g++)f(c,i[g]);b._i.push([a,e,d])};b.__SV=1.2;a=e.createElement(\"script\");a.type=\"text/javascript\";a.async=!0;a.src=\"undefined\"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:\"file:\"===e.location.protocol&&\"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js\".match(/^\/\//)?\"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js\":\"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js\";f=e.getElementsByTagName(\"script\")[0];f.parentNode.insertBefore(a,f)}})(document,window.mixpanel||[]);
mixpanel.init(\"" . $analyticsId . "\");<!-- end Mixpanel -->
      ";
					$this->doc->addScriptDeclaration($analyticsScript);
				}
				break;
		}
	}
}
