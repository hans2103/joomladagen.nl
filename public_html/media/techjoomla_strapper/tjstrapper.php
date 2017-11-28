<?php
/**
 * @version    SVN: <svn_id>
 * @package    TechjoomlaStrapper
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

// Define directory separator
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Class for TjStrapper
 *
 * @package  TechjoomlaStrapper
 * @since    1.5
 */
class TjStrapper
{
	// Declare extension_installed var for all extensions
	public static $com_emailbeautifier_installed = 0;

	public static $com_invitex_installed         = 0;

	public static $com_jbolo_installed           = 0;

	public static $com_jgive_installed           = 0;

	public static $com_jlike_installed           = 0;

	public static $com_jmailalerts_installed     = 0;

	public static $com_jticketing_installed      = 0;

	public static $com_quick2cart_installed      = 0;

	public static $com_socialads_installed       = 0;

	public static $com_tjfields_installed        = 0;

	public static $com_tjlms_installed           = 0;

	public static $com_tjreports_installed       = 0;

	public static $com_tmt_installed             = 0;

	public static $com_people_suggest_installed  = 0;

	public static $tj_extensions                 = array();

	public static $firstThingsScriptDeclaration  = array();

	public static $jboloFrontendHelper;

	// Declare load_extension_assets var for all extensions
	public static $load_com_emailbeautifier_assets = 0;

	public static $load_com_invitex_assets         = 0;

	public static $load_com_jbolo_assets           = 0;

	public static $load_com_jgive_assets           = 0;

	public static $load_com_jlike_assets           = 0;

	public static $load_com_jmailalerts_assets     = 0;

	public static $load_com_jticketing_assets      = 0;

	public static $load_com_quick2cart_assets      = 0;

	public static $load_com_socialads_assets       = 0;

	public static $load_com_tjfields_assets        = 0;

	public static $load_com_tjlms_assets           = 0;

	public static $load_com_tjreports_assets       = 0;

	public static $load_com_tmt_assets             = 0;

	public static $load_com_people_suggest_assets  = 0;

	public static $fix_js           = 1;

	public static $headtag_position = 1;

	public static $force_js_load    = 1;

	public static $loadBsRelatedFiles    = 1;

	/**
	 * Intialize class vars.
	 *
	 * @return   void
	 *
	 * @since   1.5
	 */
	public static function initializeVars()
	{
		// Get assets loader plugin
		$plugin = JPluginHelper::getPlugin('system', 'tjassetsloader');

		// If the plugin is enabled
		if ($plugin)
		{
			// Get plugin params
			$pluginParams = new JRegistry($plugin->params);

			self::$fix_js           = $pluginParams->get('fix_js');
			self::$headtag_position = $pluginParams->get('headtag_position');
			self::$force_js_load    = $pluginParams->get('force_js_load');
			self::$loadBsRelatedFiles    = $pluginParams->get('loadBsRelatedFiles');
		}

		jimport('joomla.filesystem.file');

		if (self::$load_com_emailbeautifier_assets)
		{
			// Check if EB is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_emailbeautifier/emailbeautifier.php'))
			{
				if (JComponentHelper::isEnabled('com_emailbeautifier', true))
				{
					self::$com_emailbeautifier_installed = 1;
					self::$tj_extensions[]               = 'com_emailbeautifier';
				}
			}
		}

		if (self::$load_com_invitex_assets)
		{
			// Check if invitex is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_invitex/invitex.php'))
			{
				if (JComponentHelper::isEnabled('com_invitex', true))
				{
					self::$com_invitex_installed = 1;
					self::$tj_extensions[]       = 'com_invitex';
				}
			}
		}

		if (self::$load_com_jbolo_assets)
		{
			// Check if JBOLO is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_jbolo/jbolo.php'))
			{
				if (JComponentHelper::isEnabled('com_jbolo', true))
				{
					self::$com_jbolo_installed = 1;
					self::$tj_extensions[]     = 'com_jbolo';

					// Load jboloFrontendHelper
					$jboloHelperPath           = JPATH_SITE . "/components/com_jbolo/helpers/helper.php";
					self::$jboloFrontendHelper = self::TjloadClass($jboloHelperPath, 'jboloFrontendHelper');
				}
			}
		}

		if (self::$load_com_jgive_assets)
		{
			// Check if jgive is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_jgive/jgive.php'))
			{
				if (JComponentHelper::isEnabled('com_jgive', true))
				{
					self::$com_jgive_installed = 1;
					self::$tj_extensions[]     = 'com_jgive';
				}
			}
		}

		if (self::$load_com_jlike_assets)
		{
			// Check if JLike is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_jlike/jlike.php'))
			{
				if (JComponentHelper::isEnabled('com_jlike', true))
				{
					self::$com_jlike_installed = 1;
					self::$tj_extensions[]     = 'com_jlike';
				}
			}
		}

		if (self::$load_com_jmailalerts_assets)
		{
			// Check if jmailalerts is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_jmailalerts/jmailalerts.php'))
			{
				if (JComponentHelper::isEnabled('com_jmailalerts', true))
				{
					self::$com_jmailalerts_installed = 1;
					self::$tj_extensions[]     = 'com_jmailalerts';
				}
			}
		}

		if (self::$load_com_jticketing_assets)
		{
			// Check if jticketing is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_jticketing/jticketing.php'))
			{
				if (JComponentHelper::isEnabled('com_jticketing', true))
				{
					self::$com_jticketing_installed = 1;
					self::$tj_extensions[]          = 'com_jticketing';
				}
			}
		}

		if (self::$load_com_quick2cart_assets)
		{
			// Check if quick2cart is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_quick2cart/quick2cart.php'))
			{
				if (JComponentHelper::isEnabled('com_quick2cart', true))
				{
					if (self::$load_com_quick2cart_assets)
					{
						self::$com_quick2cart_installed = 1;
						self::$tj_extensions[]          = 'com_quick2cart';
					}
				}
			}
		}

		if (self::$load_com_socialads_assets)
		{
			// Check if com_socialads is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_socialads/socialads.php'))
			{
				if (JComponentHelper::isEnabled('com_socialads', true))
				{
					self::$com_socialads_installed = 1;
					self::$tj_extensions[]         = 'com_socialads';
				}
			}
		}

		if (self::$load_com_tjfields_assets)
		{
			// Check if com_tjfields is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_tjfields/tjfields.php'))
			{
				if (JComponentHelper::isEnabled('com_tjfields', true))
				{
					self::$com_tjfields_installed = 1;
					self::$tj_extensions[]        = 'com_tjfields';
				}
			}
		}

		if (self::$load_com_tjlms_assets)
		{
			// Check if com_tjlms is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_tjlms/tjlms.php'))
			{
				if (JComponentHelper::isEnabled('com_tjlms', true))
				{
					self::$com_tjlms_installed = 1;
					self::$tj_extensions[]     = 'com_tjlms';
				}
			}
		}

		if (self::$load_com_tjreports_assets)
		{
			// Check if com_tjreports is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_tjreports/tjreports.php'))
			{
				if (JComponentHelper::isEnabled('com_tjreports', true))
				{
					self::$com_tjreports_installed = 1;
					self::$tj_extensions[]     = 'com_tjreports';
				}
			}
		}

		if (self::$load_com_tmt_assets)
		{
			// Check if com_tmt is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_tmt/tmt.php'))
			{
				if (JComponentHelper::isEnabled('com_tmt', true))
				{
					self::$com_tmt_installed = 1;
					self::$tj_extensions[]   = 'com_tmt';
				}
			}
		}

		if (self::$load_com_people_suggest_assets)
		{
			// Check if com_tmt is installed
			if (JFile::exists(JPATH_ROOT . '/components/com_psuggest/psuggest.php'))
			{
				if (JComponentHelper::isEnabled('com_psuggest', true))
				{
					self::$com_people_suggest_installed = 1;
					self::$tj_extensions[]   = 'com_psuggest';
				}
			}
		}
	}

	/**
	 * Validates conditions where plugin will be activated
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public static  function validateLoading()
	{
		$app    = JFactory::getApplication();
		$input  = JFactory::getApplication()->input;
		$option = $input->get('option', '', 'string');

		// Admin side loading
		if ($app->isAdmin())
		{
			// For component options changed this to load jquery on techjoomla extensions
			// @TODO if error on 3.0 add if else
			$component = $input->get('component', '', 'string');

			if ($option === "com_config" && in_array($component, self::$tj_extensions))
			{
				return true;
			}

			// For Q2c
			if (self::$com_quick2cart_installed)
			{
				self::$tj_extensions[] = 'com_content';
				self::$tj_extensions[] = 'com_flexicontent';
				self::$tj_extensions[] = 'com_k2';
				self::$tj_extensions[] = 'com_zoo';
			}

			// For Shika
			if (self::$com_tjlms_installed)
			{
				self::$tj_extensions[] = 'com_categories';
			}

			$extension = $input->get('extension', '', 'string');

			if ($option === "com_categories" and in_array($extension, self::$tj_extensions))
			{
				return true;
			}

			// On admin side, only load CSS on TJ component pages
			if (!in_array($option, self::$tj_extensions))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns array of TJ js files
	 *
	 * @param   int  $firstThingsFirst  A Flag to decide if a JS file is to be loaded on the top of other files
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public static function getTechjoomlaJSArray($firstThingsFirst = 0)
	{
		// Define jsFilesArray
		$jsFilesArray = array();

		// These need to be loaded first before other JS files.
		if ($firstThingsFirst)
		{
			if (JVERSION >= '3.0')
			{
				// If Fix javascript errors
				if (self::$fix_js)
				{
					$jsFilesArray[] = 'media/jui/js/jquery.min.js';

					// $jsFilesArray[] = 'media/jui/js/jquery.js';
					$jsFilesArray[] = 'media/jui/js/jquery-noconflict.js';

					// $jsFilesArray[] = 'media/jui/js/jquery-migrate.js';
				}
				else
				{
					// Load jQuery.
					JHtml::_('jquery.framework');
				}

				$jsFilesArray[] = 'media/techjoomla_strapper/js/namespace.min.js';

				// Load bootstrap
				JHtml::_('bootstrap.framework');
			}
			else
			{
				$jsFilesArray[] = 'media/techjoomla_strapper/js/akeebajq.js';
				$jsFilesArray[] = 'media/techjoomla_strapper/js/bootstrap.min.js';
			}

			if (self::$load_com_jbolo_assets)
			{
				if (self::$com_jbolo_installed == 1)
				{
					if (method_exists(self::$jboloFrontendHelper, "getJBoloJsFiles"))
					{
						self::$jboloFrontendHelper->getJBoloJsFiles($jsFilesArray, $firstThingsFirst);
					}
				}
			}

			if (self::$load_com_jlike_assets)
			{
				if (self::$com_jlike_installed == 1)
				{
					// Load jlike plugin.
					$path           = JPATH_SITE . "/components/com_jlike/helper.php";
					$comjlikeHelper = self::TjloadClass($path, 'comjlikeHelper');

					if (method_exists($comjlikeHelper, "comjlikeHelper"))
					{
						// Add component specific js file in provided array.
						$comjlikeHelper->getJlikeJsFiles($jsFilesArray);
					}
				}
			}
		}
		else
		{
			if (self::$load_com_invitex_assets)
			{
				if (self::$com_invitex_installed == 1)
				{
					// Load invitex helper class for js files.
					$path             = JPATH_SITE . "/components/com_invitex/helper.php";
					$cominvitexHelper = self::TjloadClass($path, 'cominvitexHelper');

					if (method_exists($cominvitexHelper, "getInvitexJsFiles"))
					{
						$cominvitexHelper->getInvitexJsFiles($jsFilesArray);
					}
				}
			}

			if (self::$load_com_jbolo_assets)
			{
				if (self::$com_jbolo_installed == 1)
				{
					if (method_exists(self::$jboloFrontendHelper, "getJBoloJsFiles"))
					{
						self::$jboloFrontendHelper->getJBoloJsFiles($jsFilesArray, $firstThingsFirst);
					}
				}
			}

			if (self::$load_com_jgive_assets)
			{
				if (self::$com_jgive_installed == 1)
				{
					// Load jgive js files
					$path                = JPATH_SITE . "/components/com_jgive/helper.php";
					$jgiveFrontendHelper = self::TjloadClass($path, 'jgiveFrontendHelper');

					if (method_exists($jgiveFrontendHelper, "getJGiveJsFiles"))
					{
						// Add component specific js file in provided array.
						$jgiveFrontendHelper->getJGiveJsFiles($jsFilesArray, self::$firstThingsScriptDeclaration);
					}
				}
			}

			if (self::$load_com_jlike_assets)
			{
				if (self::$com_jlike_installed == 1)
				{
					// Load jlike plugin.
					$path           = JPATH_SITE . "/components/com_jlike/helper.php";
					$comjlikeHelper = self::TjloadClass($path, 'comjlikeHelper');

					if (method_exists($comjlikeHelper, "comjlikeHelper"))
					{
						// Add component specific js file in provided array.
						$comjlikeHelper->getJlikeJsFiles($jsFilesArray);
					}
				}
			}

			if (self::$load_com_jticketing_assets)
			{
				if (self::$com_jticketing_installed == 1)
				{
					// Load jticketing js files.
					$path                = JPATH_SITE . "/components/com_jticketing/helpers/frontendhelper.php";
					$comjticketingHelper = self::TjloadClass($path, 'jticketingfrontendhelper');

					if (method_exists($comjticketingHelper, "getJticketingJsFiles"))
					{
						// Add component specific js file in provided array.
						$comjticketingHelper->getJticketingJsFiles($jsFilesArray);
					}
				}
			}

			if (self::$load_com_quick2cart_assets)
			{
				if (self::$com_quick2cart_installed == 1)
				{
					// Load Quick2cart helper class for js files.
					$path                = JPATH_SITE . "/components/com_quick2cart/helper.php";
					$comquick2cartHelper = self::TjloadClass($path, 'comquick2cartHelper');

					if (method_exists($comquick2cartHelper, "getQuick2cartJsFiles"))
					{
						// Add component specific js file in provided array && Get first things scripts eg variable declaration.
						$comquick2cartHelper->getQuick2cartJsFiles($jsFilesArray, self::$firstThingsScriptDeclaration);
					}
				}
			}

			if (self::$load_com_socialads_assets)
			{
				if (self::$com_socialads_installed == 1)
				{
					// Load SocialAds helper class for js files.
					$path            = JPATH_SITE . "/components/com_socialads/helper.php";

					// Find out SocialAds version to load helper class
					$xml             = JFactory::getXML(JPATH_SITE . '/administrator/components/com_socialads/socialads.xml');
					$currentversion  = (string) $xml->version;

					if ($currentversion < 3.1)
					{
						$socialadsFrontendHelper = self::TjloadClass($path, 'socialadshelper');
					}
					else
					{
						$socialadsFrontendHelper = self::TjloadClass($path, 'socialadsFrontendHelper');
					}

					if (method_exists($socialadsFrontendHelper, "getSocialadsJsFiles"))
					{
						// Add component specific js file in provided array && Get first things scripts eg variable declaration.
						$socialadsFrontendHelper->getSocialadsJsFiles($jsFilesArray, self::$firstThingsScriptDeclaration);
					}
				}
			}

			if (self::$load_com_tjlms_assets)
			{
				if (self::$com_tjlms_installed	==	1)
				{
					// Load jticketing js files
					$path = JPATH_SITE . "/components/com_tjlms/helpers/main.php";
					$tjlmsFrontendHelper = self::TjloadClass($path, 'comtjlmsHelper');

					if (method_exists($tjlmsFrontendHelper, "getTjlmsJsFiles"))
					{
						// Add component specific js file in provided array.
						$tjlmsFrontendHelper->getTjlmsJsFiles($jsFilesArray, self::$firstThingsScriptDeclaration);
					}
				}
			}

			if (self::$load_com_tmt_assets)
			{
				if (self::$com_tmt_installed	==	1)
				{
					// Load jticketing js files
					$path = JPATH_SITE . "/components/com_tmt/helper.php";
					$TmtFrontendHelper = self::TjloadClass($path, 'TmtFrontendHelper');

					if (method_exists($TmtFrontendHelper, "getTmtJsFiles"))
					{
						// Add component specific js file in provided array.
						$TmtFrontendHelper->getTmtJsFiles($jsFilesArray, self::$firstThingsScriptDeclaration);
					}
				}
			}

			/*if (self::$load_com_people_suggest_assets)
			{
				Load peoplesugget js files
				if (self::$com_people_suggest_installed	==	1)
				{
					 Load jticketing js files
					$path = JPATH_SITE . "/components/com_psuggest/helper.php";
					$PsFrontendHelper = self::TjloadClass($path, 'PsuggestFrontendHelper');

					if (method_exists($PsFrontendHelper, "getPsuggestJsFiles"))
					{
						Add component specific js file in provided array.
						$PsFrontendHelper->getPsuggestJsFiles($jsFilesArray);
					}
				}
			}*/
		}

		return $jsFilesArray;
	}

	/**
	 * This function to load class.
	 *
	 * @param   string  $path       Path of file.
	 * @param   string  $className  Class Name to load.
	 *
	 * @return  Object of provided class.
	 */
	public static function TjloadClass($path, $className)
	{
		if (!class_exists($className))
		{
			JLoader::register($className, $path);
			JLoader::load($className);
		}

		if (class_exists($className))
		{
			return new $className;
		}
		else
		{
			throw new RuntimeException(sprintf('Unable to load class: %s', $className));

			// JFactory::getApplication()->enqueueMessage(sprintf('Unable to load class: %s, $className), 'error');
		}
	}

	/**
	 * Removes JS files from given array of js files if those files are already present in document
	 *
	 * @param   array  $assetsarray  Array of js files
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public static function remove_duplicate_files($assetsarray)
	{
		if (!count($assetsarray))
		{
			return $assetsarray;
		}

		$doc                  = JFactory::getDocument();
		$flg                  = 0;
		$notToRemoveDuplicate = 0;

		foreach ($assetsarray as $key => $file)
		{
			if ($file[0] == '/')
			{
				$assets_name_relative = JUri::root(true) . $file;
				$assets_name_absolute = JUri::root() . $file;
			}
			else
			{
				$assets_name_relative = JUri::root(true) . '/' . $file;
				$assets_name_absolute = JUri::root() . '/' . $file;
			}

			if (self::$force_js_load)
			{
				$notToRemoveDuplicate = strrpos($file, 'jquery.min.js');
			}

			// Not to remove duplicate jquery.min.js
			if ($notToRemoveDuplicate == 0 OR $notToRemoveDuplicate === false)
			{
				if (array_key_exists($assets_name_relative, $doc->_scripts))
				{
					unset($assetsarray[$key]);
				}

				if (array_key_exists($assets_name_absolute, $doc->_scripts))
				{
					unset($assetsarray[$key]);
				}
			}
		}

		return $assetsarray;
	}

	/**
	 * Returns array of TJ css files
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public static function getTJCssArray()
	{
		$app    = JFactory::getApplication();
		$input  = JFactory::getApplication()->input;
		$option = $input->get('option', '', 'string');

		$loadCssFlag = 1;
		$loadBootstrap = 1;
		$cssFiles    = array();

		// Admin side loading
		if ($app->isAdmin())
		{
			// On admin side, only load CSS on TJ component pages
			if (!in_array($option, self::$tj_extensions))
			{
				$loadCssFlag = 0;
			}
		}

		if ($loadCssFlag)
		{
			if (self::$loadBsRelatedFiles)
			{
				if (JVERSION < '3.0')
				{
					$cssFiles[] = 'media/techjoomla_strapper/css/bootstrap.min.css';
					$cssFiles[] = 'media/techjoomla_strapper/css/bootstrap-responsive.min.css';
				}
				else
				{
					// For BS2.x, overriders for Joomla 3.x
					$cssFiles[] = 'media/techjoomla_strapper/css/bootstrap.j3.min.css';
				}

				$cssFiles[] = 'media/techjoomla_strapper/css/strapper.min.css';
			}

			if (self::$load_com_tjlms_assets)
			{
				if (self::$com_tjlms_installed == 1)
				{
					$path           = JPATH_SITE . "/components/com_tjlms/helpers/main.php";
					$ComtjlmsHelper = self::TjloadClass($path, 'ComtjlmsHelper');

					if (method_exists($ComtjlmsHelper, "getTjlmsCssFiles"))
					{
						// Add component specific css file in provided array.
						$ComtjlmsHelper->getTjlmsCssFiles($cssFiles);
					}
				}
			}

			if (self::$load_com_tmt_assets)
			{
				if (self::$com_tmt_installed == 1)
				{
					$path           = JPATH_SITE . "/components/com_tmt/helper.php";
					$TmtFrontendHelper = self::TjloadClass($path, 'TmtFrontendHelper');

					if (method_exists($TmtFrontendHelper, "getTmtCssFiles"))
					{
						// Add component specific css file in provided array.
						$TmtFrontendHelper->getTmtCssFiles($cssFiles);
					}
				}
			}
		}

		return $cssFiles;
	}

	/**
	 * Creates html <link> tags for all files in given array
	 *
	 * @param   int  &$scriptList  Array of css files
	 * @param   int  $filenames    Array of css files
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function getCSSscripts(&$scriptList, $filenames)
	{
		// Clear file status cache
		clearstatcache();

		$cssfile_path = JPATH_SITE . "/components/com_jbolo/css/jbolocss.php";

		// @TODO change
		$comb_mini = 0;

		/*Added By Deepa*/
		$css_script = array();

		// Combine and minify css
		// if ($this->params->get('comb_mini') && is_writable($cssfile_path))
		if ($comb_mini && is_writable($cssfile_path))
		{
			// $sitepath=JPATH_SITE;
			$sitepath = JPATH_SITE . '/';

			foreach ($filenames as $file)
			{
				// $css_script[]="include('".$sitepath."/components/com_jbolo/css/".$file."');";
				$css_script[] = "include('" . $sitepath . $file . "');";
			}

			$css_script   = implode("\n", $css_script);
			$cssfile_path = JPATH_SITE . "/components/com_jbolo/css/jbolocss.php";
			$cssgzip      = 'header("Content-type: text/css");
				ob_start("compress");
				function compress($buffer){
					/* remove comments */
					$buffer = preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", "", $buffer);
					/* remove tabs, spaces, newlines, etc. */
					$buffer = str_replace(array("\r\n", "\r", "\n", "\t", "  ", "    ", "    "), "", $buffer);
					return $buffer;
				}';

			$data = "<?php " . $cssgzip . "\n" . $css_script . "\n ob_end_flush();?>";

			if (JFile::write($cssfile_path, $data))
			{
				$scriptList[] = '<link rel="stylesheet" href="' . JUri::root(true) . '/components/com_jbolo/css/jbolocss.php" type="text/css" />';
			}
			else
			{
				foreach ($filenames as $file)
				{
					// $scriptList[]='<link rel="stylesheet" href="'.JUri::root(true).'/components/com_jbolo/css/'.$file.'" type="text/css" />';
					$scriptList[] = '<link rel="stylesheet" href="' . JUri::root(true) . '/' . $file . '" type="text/css" />';
				}
			}
		}
		else
		{
			foreach ($filenames as $file)
			{
				$cssFileName = basename($file, ".min.css");
				$cssFileName = basename($cssFileName, ".css");
				$file = self::getOverridedFiles($file, 'css', $cssFileName);

				$scriptList[] = '<link rel="stylesheet" href="' . JUri::root(true) . '/' . $file . '" type="text/css" />';
			}
		}
	}

	/**
	 * Get the all files in array from script function 
	 *
	 * @param   string  $file              Array of files
	 * @param   string  $pathName          Folder name of path
	 * @param   string  $overrideFileName  File name
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function getOverridedFiles($file, $pathName, $overrideFileName)
	{
			$app    = JFactory::getApplication();
			$config = JFactory::getConfig();

			// Get the Debug configuration set To 'YES' or 'NO'
			$debug = $config->get('debug');

			// If debug is set to 'NO' then $debugCss load .min files
			$debugCss = $debug ? '' : '.min';

			// If debug is set to 'NO' then $notdebugCss load .css/.js files
			$notdebugCss = $debugCss ? '' : '.min';

			// Get the path from component name
			$path = strstr($file, "com_");

			// Get the files from component name
			$fileExtension = strstr($file, ".");

			$component = explode("/", $path);

			$override = JPATH_BASE . '/templates/' . $app->getTemplate() . '/' .
						$pathName . '/' . $component[0] . '/' . $overrideFileName . $debugCss . $fileExtension;
			$nonDebudOverride = JPATH_BASE . '/templates/' . $app->getTemplate() . '/' .
						$pathName . '/' . $component[0] . '/' . $overrideFileName . $notdebugCss . $fileExtension;

			if (file_exists($override))
			{
				$file = '/templates/' . $app->getTemplate() . '/' . $pathName . '/' . $component[0] . '/' . $overrideFileName . $debugCss . $fileExtension;
			}
			elseif (file_exists($nonDebudOverride))
			{
				$file = '/templates/' . $app->getTemplate() . '/' . $pathName . '/' . $component[0] . '/' . $overrideFileName . $notdebugCss . $fileExtension;
			}

				return $file;
	}

	/**
	 * Creates html <script> tags for all files in given array
	 *
	 * @param   int  &$scriptList  Array of js files
	 * @param   int  $filenames    Array of js files
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function getJSscripts(&$scriptList, $filenames)
	{
		// Clear file status cache
		clearstatcache();

		$jsfile_path = JPATH_SITE . "/components/com_jbolo/js/jbolojs.php";

		// @TODO change
		$comb_mini = 0;

		/* Added By Deepa */
		$js_script = array();

		// Combine and minify js
		// if ($this->params->get('comb_mini') && is_writable($jsfile_path) && self::$com_jbolo_installed)
		if ($comb_mini && is_writable($jsfile_path) && self::$com_jbolo_installed)
		{
			// $sitepath= JPATH_SITE;
			$sitepath = JPATH_SITE . '/';

			foreach ($filenames as $file)
			{
				if ($file[0] == '/')
				{
					// $js_script[] = "include('".$sitepath."/components/com_jbolo".$file."');";
					$js_script[] = "include('" . $sitepath . $file . "');";
				}
				else
				{
					// $js_script[] = "include('".$sitepath."/components/com_jbolo/js/".$file."');";
					$js_script[] = "include('" . $sitepath . $file . "');";
				}
			}

			// $js_script[] = "include('".JRoute::_('index.php?option=com_jbolo&view=js&format=raw')."');";
			$js_script = implode("\n", $js_script);

			$jsgzip = 'header("Content-type: text/javascript;");
				ob_start("compress");
				function compress($buffer){
					/* remove comments */
					$buffer = preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", "", $buffer);
					/* remove tabs, spaces, newlines, etc. */
					$buffer = str_replace(array("\r\n", "\r", "\n", "\t", "  ", "    ", "    "), "", $buffer);
					return $buffer;
				}';

			$data = "<?php " . $jsgzip . "\n" . $js_script . "\n ob_end_flush();?>";

			if (JFile::write($jsfile_path, $data))
			{
				$scriptList[] = '<script type="text/javascript" src="' . JUri::root(true) . '/components/com_jbolo/js/jbolojs.php"> </script>';
			}
			else
			{
				foreach ($filenames as $file)
				{
					if ($file[0] == '/')
					{
						$scriptList[] = '<script src="' . JUri::root(true) . '/' . $file . '" type="text/javascript"></script>';
					}
					else
					{
						$scriptList[] = '<script src="' . JUri::root(true) . '/' . $file . '" type="text/javascript"></script>';
					}
				}
			}
		}
		else
		{
			if (count($filenames))
			{
				foreach ($filenames as $file)
				{
					$jsFileName = basename($file, ".min.js");
					$jsFileName = basename($jsFileName, ".js");

					$file = self::getOverridedFiles($file, 'js', $jsFileName);

					if ($file[0] == '/')
					{
						$scriptList[] = '<script src="' . JUri::root(true) . '/' . $file . '" type="text/javascript"></script>';
					}
					else
					{
						$scriptList[] = '<script src="' . JUri::root(true) . '/' . $file . '" type="text/javascript"></script>';
					}
				}
			}
		}
	}

	/**
	 * Replace only the first occurance of search string
	 * http://stackoverflow.com/questions/1252693/using-str-replace-so-that-it-only-acts-on-the-first-match
	 *
	 * @param   string  $search   Search string
	 * @param   string  $replace  Replace with
	 * @param   string  $subject  String to be searched in
	 *
	 * @return  string
	 *
	 * @since   3.1.4
	 */
	public static function str_replace_first($search, $replace, $subject)
	{
		return implode($replace, explode($search, $subject, 2));
	}

	/**
	 * Returns array of jlike css files
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public static function getJlikeCssArray()
	{
		// Load css for jLike
		/*Changed by Deepa*/
		$cssfilesArray = array();
		$cssfilesArray[] = 'components/com_jlike/assets/css/like.css';

		return $cssfilesArray;
	}

	/**
	 * Loads assets for given extension
	 *
	 * @param   string  $extension  Name of the extension
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function loadTjAssets($extension = '')
	{
		if ($extension != '')
		{
			switch ($extension)
			{
				case 'com_emailbeautifier':
				self::$load_com_emailbeautifier_assets = 1;
				break;

				case 'com_invitex':
				self::$load_com_invitex_assets = 1;
				break;

				case 'com_jbolo':
				self::$load_com_jbolo_assets = 1;
				break;

				case 'com_jgive':
				self::$load_com_jgive_assets = 1;
				break;

				case 'com_jlike':
				self::$load_com_jlike_assets = 1;
				break;

				case 'com_jmailalerts':
				self::$load_com_jmailalerts_assets = 1;
				break;

				case 'com_jticketing':
				self::$load_com_jticketing_assets = 1;
				break;

				case 'com_quick2cart':
				self::$load_com_quick2cart_assets = 1;
				break;

				case 'com_socialads':
				self::$load_com_socialads_assets = 1;
				break;

				case 'com_tjfields':
				self::$load_com_tjfields_assets = 1;
				break;

				case 'com_tjreports':
				self::$load_com_tjreports_assets = 1;
				break;

				case 'com_tjlms':
				self::$load_com_tjlms_assets = 1;
				break;

				case 'com_tmt':
				self::$load_com_tmt_assets = 1;
				break;

				case 'com_psuggest':
				self::$load_com_people_suggest_assets = 1;
				break;

				default:
				break;
			}

			onAfterRouteTj();
			onAfterRenderTj();
		}
	}
}

/**
 * On after route system event
 *
 * @return  void
 *
 * @since   3.0
 */
function onAfterRouteTj()
{
	TjStrapper::initializeVars();

	if (! TjStrapper::validateLoading())
	{
		return false;
	}

	$document = JFactory::getDocument();

	// If Fix javascript errors parameter is set to NO
	if (!TjStrapper::$fix_js)
	{
		// Get all JS files array, load the important ones first.
		$tjjsFiles = TjStrapper::getTechjoomlaJSArray($firstThingsFirst = 1);

		// Remove JS files if those files are already present in document
		$tjjsFiles = TjStrapper::remove_duplicate_files($tjjsFiles);

		if (!empty($tjjsFiles))
		{
			foreach ($tjjsFiles as $file)
			{
				if ($file[0] == '/')
				{
					$document->addScript(JUri::root(true) . $file);
				}
				else
				{
					$document->addScript(JUri::root(true) . '/' . $file);
				}
			}
		}
	}
}

/**
 * On after render system event
 *
 * @return  void
 */
function onAfterRenderTj()
{
	TjStrapper::initializeVars();

	if (! TjStrapper::validateLoading())
	{
		return false;
	}

	$jbolo_dynamic_js = $jsChatTemplates = '';
	$cssScripts = $jsScripts1 = $jsScripts2 = array();

	if (TjStrapper::$load_com_jbolo_assets)
	{
		if (TjStrapper::$com_jbolo_installed == 1)
		{
			if (method_exists(TjStrapper::$jboloFrontendHelper, "getJBoloCssFiles"))
			{
				$cssFiles = TjStrapper::$jboloFrontendHelper->getJBoloCssFiles();

				// Call CSS loader function.
				TjStrapper::getCSSscripts($cssScripts, $cssFiles);
			}

			// Get all dynamic JS code required for JBOLo.
			if (method_exists(TjStrapper::$jboloFrontendHelper, "getJboloDynamicJs"))
			{
				$jbolo_dynamic_js = TjStrapper::$jboloFrontendHelper->getJboloDynamicJs();
			}

			// Get all jQuery chat templates reqyired for JBOLO.
			if (method_exists(TjStrapper::$jboloFrontendHelper, "getJboloJqueryChatTemplates"))
			{
				$jsChatTemplates = TjStrapper::$jboloFrontendHelper->getJboloJqueryChatTemplates();
			}
		}
	}

	if (TjStrapper::$com_jlike_installed == 1)
	{
		$cssFiles = TjStrapper::getJlikeCssArray();

		// Call CSS loader function.
		TjStrapper::getCSSscripts($cssScripts, $cssFiles);
	}

	$cssFiles = TjStrapper::getTJCssArray();

	// Call CSS loader function.
	TjStrapper::getCSSscripts($cssScripts, $cssFiles);

	if (TjStrapper::$fix_js == 1)
	{
		// Get first JS files.
		$jsFiles1 = TjStrapper::getTechjoomlaJSArray($firstThingsFirst = 1);

		// Remove JS files if those files are alraedy present in document
		$jsFiles1 = TjStrapper::remove_duplicate_files($jsFiles1);

		// Call JS loader function.
		TjStrapper::getJSscripts($jsScripts1, $jsFiles1);
	}

	// Get other JS files.
	$jsFiles2 = TjStrapper::getTechjoomlaJSArray($firstThingsFirst = 0);

	// Get first things scripts eg variable declaration
	$allScriptDeclarations = '';

	if (!empty(TjStrapper::$firstThingsScriptDeclaration))
	{
		$allScriptDeclarations = '<script type="text/javascript">' . implode(' ', TjStrapper::$firstThingsScriptDeclaration) . '</script>';
	}

	// Remove JS files if those files are alraedy present in document
	$jsFiles2 = TjStrapper::remove_duplicate_files($jsFiles2);

	// Call JS loader function.
	TjStrapper::getJSscripts($jsScripts2, $jsFiles2);

	// Insert all scripts into head tag.
	// Get page HTML.
	$body = JResponse::getBody();

	if (is_array($jsScripts1))
	{
		$jsScripts1 = implode("\n", $jsScripts1);
	}

	// Set all css and js and dynamic js
	$includescripts = implode("\n", $cssScripts) .
						$allScriptDeclarations .
						$jsScripts1 .
						$jbolo_dynamic_js .
						implode("\n", $jsScripts2) . $jsChatTemplates;

	if (TjStrapper::$fix_js == 1)
	{
		// Push JS into head at start or end of head tag.
		if (TjStrapper::$headtag_position)
		{
			$body = TjStrapper::str_replace_first('<head>', '<head>' . $includescripts, $body);
		}
		else
		{
			$body = TjStrapper::str_replace_first('</head>', $includescripts . '</head>', $body);
		}
	}
	else
	{
		$body = TjStrapper::str_replace_first('</head>', $includescripts . '</head>', $body);
	}

	// Push jbolo HTML before closing body tag.
	if (TjStrapper::$load_com_jbolo_assets)
	{
		if (TjStrapper::$com_jbolo_installed == 1)
		{
			$jbolo_html_code = '';

			if (method_exists(TjStrapper::$jboloFrontendHelper, "getJboloHtmlCode"))
			{
				$jbolo_html_code = TjStrapper::$jboloFrontendHelper->getJboloHtmlCode();
			}

			$body = str_replace('</body>', $jbolo_html_code . '</body>', $body);
		}
	}

	JResponse::setBody($body);

	return true;
}

$app = JFactory::getApplication();

// Register our own events
$app->registerEvent('onAfterRoute', 'onAfterRouteTj');
$app->registerEvent('onAfterRender', 'onAfterRenderTj');
