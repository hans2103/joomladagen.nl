<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * PWT SEO plugin
 * Plugin to give the user an approximation of the effectiveness in SEO of the article
 *
 * @since  1.0
 */
class PlgSystemPWTSEO extends CMSPlugin
{
	/**
	 * @var JDatabaseDriver
	 * @since 1.0
	 */
	protected $db;

	/**
	 * @var JApplication
	 * @since 1.0
	 */
	protected $app;

	/**
	 * Load the language file on instantiation
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Array to hold all the contexts in which our plugin works
	 *
	 * @var    array
	 * @since  1.0
	 */
	private $aAllowedContext = array('com_content.article', 'com_pwtseo.custom', 'com_menus.item');

	/**
	 * @var    String  base update url, to decide whether to process the event or not
	 *
	 * @since  1.0.0
	 */
	private $baseUrl = 'https://extensions.perfectwebteam.com/pwt-seo';

	/**
	 * @var    String  Extension identifier, to retrieve its params
	 *
	 * @since  1.0.0
	 */
	private $extension = 'com_pwtseo';

	/**
	 * @var    String  Extension title, to retrieve its params
	 *
	 * @since  1.0.0
	 */
	private $extensiontitle = 'PWT SEO';

	/**
	 * Adding required headers for successful extension update
	 *
	 * @param   string $url     url from which package is going to be downloaded
	 * @param   array  $headers headers to be sent along the download request (key => value format)
	 *
	 * @return  boolean true    Always true, regardless of success
	 *
	 * @since   1.0.0
	 *
	 * @throws  Exception
	 */
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		// Are we trying to update our own extensions?
		if (strpos($url, $this->baseUrl) !== 0)
		{
			return true;
		}

		// Load language file
		$jLanguage = Factory::getLanguage();
		$jLanguage->load($this->extension, JPATH_ADMINISTRATOR . '/components/' . $this->extension . '/', 'en-GB', true, true);
		$jLanguage->load($this->extension, JPATH_ADMINISTRATOR . '/components/' . $this->extension . '/', null, true, false);

		// Get the Download ID from component params
		$downloadId = ComponentHelper::getComponent($this->extension)->params->get('downloadid', '');

		// Set Download ID first
		if (empty($downloadId))
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf($this->extension . '_DOWNLOAD_ID_REQUIRED',
					$this->extension,
					$this->extensiontitle
				),
				'error'
			);

			return true;
		}
		// Append the Download ID
		else
		{
			$separator = strpos($url, '?') !== false ? '&' : '?';
			$url       .= $separator . 'key=' . $downloadId;
		}

		// Get the domain for this site
		$domain = preg_replace('(^https?://)', '', rtrim(Uri::root(), '/'));

		// Append domain
		$url .= '&domain=' . $domain;

		return true;
	}

	/**
	 * Once the user is logged in, we want to check for the robots setting in global config.
	 *
	 * @param   array $options Array holding options
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0.1
	 */
	public function onUserAfterLogin($options)
	{
		if ($this->app->isClient('administrator'))
		{
			if (Factory::getUser()->authorise('core.admin') && Factory::getConfig()->get('robots') === 'noindex, nofollow')
			{
				$this->app->enqueueMessage(Text::_('PLG_SYSTEM_PWTSEO_ERROR_NOINDEX_NOFOLLOW'), 'warning');
			}
		}

		return true;
	}

	/**
	 * Alters the form that is loaded
	 *
	 * @param   JForm  $form Object to be displayed. Use the $form->getName() method to check whether this is the form you want to work with.
	 * @param   Object $data Containing the data for the form.
	 *
	 * @return  bool True is method succeeds
	 *
	 * @since   1.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof Form))
		{
			return false;
		}

		// Setup values that are used by our field
		if ($form->getName() === 'com_pwtseo')
		{
			$form->setValue('articletitleselector', 'pwtseo', $this->params->get('articletitleselector', ''));
		}

		if (in_array($form->getName(), $this->aAllowedContext))
		{
			HTMLHelper::_('jquery.framework');
			$form->loadFile(JPATH_PLUGINS . '/system/pwtseo/form/' . $form->getName() . '.xml', false);

			/**
			 * TODO: seperate editor logic so we can add it depending on which editor that we are using
			 * TODO: mind the 'Toggle Editor' button
			 */

			HTMLHelper::script('plg_system_pwtseo/vue.min.js', array('version' => 'auto', 'relative' => true));
			HTMLHelper::script('plg_system_pwtseo/lodash.min.js', array('version' => 'auto', 'relative' => true));

			HTMLHelper::script('plg_system_pwtseo/pwtseo.min.js', array('version' => 'auto', 'relative' => true));
			HTMLHelper::stylesheet('plg_system_pwtseo/pwtseo.css', array('version' => 'auto', 'relative' => true));

			$iMinTitle    = (int) $this->params->get('count_min_title', 40);
			$iMaxTitle    = (int) $this->params->get('count_max_title', 50);
			$iMinMetadesc = (int) $this->params->get('count_min_metadesc', 275);
			$iMaxMetadesc = (int) $this->params->get('count_max_metadesc', 325);

			$aWordsFilter = array();

			$language        = Factory::getLanguage();
			$currentLanguage = $language->getTag();
			$langs           = LanguageHelper::getContentLanguages(true, false);

			foreach ($langs as $lang)
			{
				$sWords   = $this->params->get('blacklist_' . $lang->lang_code, '');
				$sDefault = '';

				// Load the new language which takes precedence over default and check that it has the key.
				if ($language->load('plg_system_pwtseo', JPATH_PLUGINS . '/system/pwtseo', $lang->lang_code, true, false)
					&& $language->hasKey('PLG_SYSTEM_PWTSEO_DEFAULT_BLACKLISTED'))
				{
					$sDefault = Text::_('PLG_SYSTEM_PWTSEO_DEFAULT_BLACKLISTED');
				}

				// If nothing has been saved in the plugin, use the default values
				if (!$sWords && $sDefault !== 'PLG_SYSTEM_PWTSEO_DEFAULT_BLACKLISTED')
				{
					$sWords = $sDefault;
				}

				$aWordsFilter[$lang->lang_code]
					= array_values(
					array_filter(
						array_map(
							'trim',
							explode(',', $sWords)
						)
					)
				);
			}

			Factory::getLanguage()->load('plg_system_pwtseo', JPATH_PLUGINS . '/system/pwtseo', $currentLanguage, true);

			// All parameters required by the JS
			Factory::getDocument()->addScriptOptions('PWTSeoConfig',
				array(
					'context'                                        => $form->getName(),
					'min_title_length'                               => $iMinTitle,
					'max_title_length'                               => $iMaxTitle,
					'min_metadesc_length'                            => $iMinMetadesc,
					'max_metadesc_length'                            => $iMaxMetadesc,
					'min_focus_length'                               => (int) $this->params->get('min_focus_length', 3),
					'baseurl'                                        => Uri::root(),
					'ajaxurl'                                        => Uri::base(true) . '/index.php?option=com_ajax&format=json',
					'frontajaxurl'                                   => Uri::root() . 'index.php?option=com_ajax&format=json',
					'domain'                                         => Uri::getInstance()->toString(array('scheme', 'host', 'port')),
					'requirements_article_title_good'                => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_TITLE_GOOD'),
					'requirements_article_title_bad'                 => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_TITLE_BAD'),
					'requirements_page_title_good'                   => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_GOOD'),
					'requirements_page_title_bad'                    => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_BAD'),
					'requirements_meta_description_none'             => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_META_DESCRIPTION_NONE'),
					'requirements_meta_description_too_short_bad'    => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_META_DESCRIPTION_TOO_SHORT_BAD',
						$iMinMetadesc,
						$iMaxMetadesc
					),
					'requirements_meta_description_too_short_medium' => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_META_DESCRIPTION_TOO_SHORT_MEDIUM',
						$iMinMetadesc,
						$iMaxMetadesc
					),
					'requirements_meta_description_too_long_medium'  => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_META_DESCRIPTION_TOO_LONG_MEDIUM',
						$iMinMetadesc,
						$iMaxMetadesc
					),
					'requirements_meta_description_medium'           => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_META_DESCRIPTION_MEDIUM'),
					'requirements_meta_description_good'             => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_META_DESCRIPTION_GOOD'),
					'requirements_images_none'                       => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_NONE'),
					'requirements_images_bad'                        => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_BAD'),
					'requirements_images_good'                       => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_GOOD'),
					'requirements_images_resulting_none'             => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_RESULTING_NONE'),
					'requirements_images_resulting_bad'              => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_RESULTING_BAD'),
					'requirements_images_resulting_good'             => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IMAGES_RESULTING_GOOD'),
					'requirements_subheadings_none'                  => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_SUBHEADINGS_NONE'),
					'requirements_subheadings_bad'                   => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_SUBHEADINGS_BAD'),
					'requirements_subheadings_medium'                => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_SUBHEADINGS_MEDIUM'),
					'requirements_subheadings_good'                  => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_SUBHEADINGS_GOOD'),
					'requirements_first_paragraph_bad'               => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_FIRST_PARAGRAPH_BAD'),
					'requirements_first_paragraph_good'              => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_FIRST_PARAGRAPH_GOOD'),
					'requirements_density_none'                      => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_NONE'),
					'requirements_density_too_few_bad'               => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_TOO_FEW_BAD'),
					'requirements_density_resulting_too_few_bad'     => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_RESULTING_TOO_FEW_BAD'),
					'requirements_density_too_much_bad'              => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_TOO_MUCH_BAD'),
					'requirements_density_resulting_too_much_bad'    => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_RESULTING_TOO_MUCH_BAD'),
					'requirements_density_too_few_medium'            => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_TOO_FEW_MEDIUM'),
					'requirements_density_resulting_too_few_medium'  => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_RESULTING_TOO_FEW_MEDIUM'),
					'requirements_density_too_much_medium'           => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_TOO_MUCH_MEDIUM'),
					'requirements_density_good'                      => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_GOOD'),
					'requirements_density_resulting_good'            => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_DENSITY_RESULTING_GOOD'),
					'requirements_length_bad'                        => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_LENGTH_BAD'),
					'requirements_length_medium'                     => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_LENGTH_MEDIUM'),
					'requirements_length_good'                       => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_LENGTH_GOOD'),
					'requirements_page_title_length_too_few_bad'     => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_LENGTH_TOO_FEW_BAD',
						$iMinTitle,
						$iMaxTitle
					),
					'requirements_page_title_length_too_much_bad'    => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_LENGTH_TOO_MUCH_BAD',
						$iMinTitle,
						$iMaxTitle
					),
					'requirements_page_title_length_too_few_medium'  => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_LENGTH_TOO_FEW_MEDIUM',
						$iMinTitle,
						$iMaxTitle
					),
					'requirements_page_title_length_too_much_medium' => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_LENGTH_TOO_MUCH_MEDIUM',
						$iMinTitle,
						$iMaxTitle
					),
					'requirements_page_title_length_good'            => Text::sprintf(
						'PLG_SYSTEM_PWTSEO_REQUIREMENTS_PAGE_TITLE_LENGTH_GOOD',
						$iMinTitle,
						$iMaxTitle
					),
					'requirements_in_url_bad'                        => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IN_URL_BAD'),
					'requirements_in_url_good'                       => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_IN_URL_GOOD'),
					'requirements_not_used_loading'                  => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_LOADING'),
					'requirements_not_used_good'                     => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_NOT_USED_GOOD'),
					'requirements_not_used_medium'                   => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_NOT_USED_MEDIUM'),
					'requirements_not_used_bad'                      => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_NOT_USED_BAD'),
					'requirements_robots_reachable_good'             => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ROBOTS_REACHABLE_GOOD'),
					'requirements_robots_reachable_bad'              => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ROBOTS_REACHABLE_BAD'),
					'requirements_article_title_unique_none'         => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_TITLE_UNIQUE_NONE'),
					'requirements_article_title_unique_good'         => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTICLE_TITLE_UNIQUE_GOOD'),
					'requirements_article_title_unique_bad'          => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_ARTCILE_TITLE_UNIQUE_BAD'),
					'requirements_metadesc_unique_none'              => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_METADESC_UNIQUE_NONE'),
					'requirements_metadesc_unique_good'              => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_METADESC_UNIQUE_GOOD'),
					'requirements_metadesc_unique_bad'               => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_METADESC_UNIQUE_BAD'),
					'information_most_common_words'                  => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_INFORMATION_COMMON_WORDS'),
					'information_most_common_blocked_words'          => Text::_('PLG_SYSTEM_PWTSEO_REQUIREMENTS_INFORMATION_COMMON_BLACKLISTED_WORDS'),
					'show_counters'                                  => (int) $this->params->get('show_counters', 1),
					'found_resulting_page'                           => Text::_('PLG_SYSTEM_PWTSEO_FOUND_RESULTING_PAGE'),
					'resulting_page_unreachable'                     => Text::_('PLG_SYSTEM_PWTSEO_RESULTING_PAGE_UNREACHABLE'),
					'error_invalid_url'                              => Text::_('PLG_SYSTEM_PWTSEO_ERROR_INVALID_URL'),
					'words_filter'                                   => $aWordsFilter,
					'editor_type'                                    => Factory::getUser()->getParam('editor', Factory::getConfig()->get('editor', 'tinymce'))
				)
			);
		}

		// When we editing the plugin settings
		if ($form->getField('plg_system_pwtseo', 'params'))
		{
			$language        = Factory::getLanguage();
			$currentLanguage = $language->getName();
			$langs           = LanguageHelper::getContentLanguages(true, false);

			// Load the language file for each installed content language
			foreach ($langs as $lang)
			{
				$form->loadFile(JPATH_PLUGINS . '/system/pwtseo/form/lang.xml');

				$form->setFieldAttribute('spacer', 'label', $lang->lang_code, 'params');
				$form->setFieldAttribute('spacer', 'name', 'spacer_' . $lang->lang_code, 'params');
				$form->setFieldAttribute('blacklist', 'name', 'blacklist_' . $lang->lang_code, 'params');

				// Load the new language which takes precedence over default and check that it has the key.
				if ($language->load('plg_system_pwtseo', JPATH_PLUGINS . '/system/pwtseo', $lang->lang_code, true, false)
					&& $language->hasKey('PLG_SYSTEM_PWTSEO_DEFAULT_BLACKLISTED'))
				{
					$form->setFieldAttribute('blacklist_' . $lang->lang_code, 'default', Text::_('PLG_SYSTEM_PWTSEO_DEFAULT_BLACKLISTED'), 'params');
				}

			}

			// Return to original language
			Factory::getLanguage()->load('plg_system_pwtseo', JPATH_PLUGINS . '/system/pwtseo', $currentLanguage, true);
		}

		return true;
	}

	/**
	 * Alters the loaded data that is injected into the form. When we are given an object we don't need the reference
	 * but when we are given an array we *do* need it.
	 *
	 * @param   string $context Context of the content being passed to the plugin
	 * @param   mixed  $data    Array|Object containing the data for the form
	 *
	 * @return  bool True if method succeeds.
	 *
	 * @since   1.0
	 */
	public function onContentPrepareData($context, $data)
	{
		if (in_array($context, $this->aAllowedContext) && !$this->app->isSite())
		{
			// Com_menus gives us an array, while com_content gives us an object
			if (is_object($data))
			{
				$iId = isset($data->id) ? $data->id : 0;

				if ($iId > 0)
				{
					$data->pwtseo = $this->getSEOData($iId, 'context_id', $context);
				}
			}

			if (is_array($data))
			{
				$iId = isset($data['id']) ? $data['id'] : 0;

				if ($iId > 0)
				{
					$data['pwtseo'] = (array) $this->getSEOData($iId, 'context_id', $context);
				}
			}
		}

		return true;
	}

	/**
	 * Get record based on given value with key
	 *
	 * @param   string $sValue   The value to look for
	 * @param   string $sKey     The key of the column
	 * @param   string $sContext The context of the item
	 *
	 * @return  object the record or empty if not found
	 *
	 * @since   1.0
	 */
	private function getSEOData($sValue, $sKey = 'context_id', $sContext = 'com_content.article')
	{
		$q = $this->db->getQuery(true);

		$q
			->select(
				$this->db->quoteName(
					array(
						'pwtseo.context',
						'pwtseo.context_id',
						'pwtseo.focus_word',
						'pwtseo.pwtseo_score',
						'pwtseo.facebook_title',
						'pwtseo.facebook_description',
						'pwtseo.facebook_image',
						'pwtseo.twitter_title',
						'pwtseo.twitter_description',
						'pwtseo.twitter_image',
						'pwtseo.google_title',
						'pwtseo.google_description',
						'pwtseo.google_image',
						'pwtseo.adv_open_graph',
						'pwtseo.override_page_title',
						'pwtseo.page_title',
						'pwtseo.expand_og',
						'pwtseo.override_canonical',
						'pwtseo.canonical',
						'pwtseo.articletitleselector',
						'pwtseo.twitter_card',
						'pwtseo.twitter_site_username'
					)
				)
			)
			->from($this->db->quoteName('#__plg_pwtseo', 'pwtseo'))
			->where($this->db->quoteName('pwtseo.' . $sKey) . ' = ' . $this->db->quote($sValue))
			->where($this->db->quoteName('pwtseo.context') . ' = ' . $this->db->quote($sContext))
			->where($this->db->quoteName('pwtseo.focus_word') . ' <> ""');

		try
		{
			return $this->db->setQuery($q)->loadObject();
		}
		catch (Exception $e)
		{
		}

		return new stdClass;
	}

	/**
	 * When previewing an article, set the values we got from the form
	 *
	 * @param   string   $context The context of the current page
	 * @param   Object   $article The article that is prepared
	 * @param   Registry $params  Any parameters
	 * @param   string   $page    The name of the page
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onContentPrepare($context, &$article, &$params, $page)
	{
		if ($this->app->isClient('site') && $this->app->input->getInt('pwtseo_preview', 0))
		{
			$aForm = $this->app->input->post->get('jform', '', 'raw');

			foreach ($aForm as $sKey => $sValue)
			{
				if (is_array($sValue) || is_object($sValue))
				{
					$rTmp = new Registry;

					foreach ((array) $sValue as $key => $val)
					{
						$rTmp->set($key, $val);
					}

					$article->{$sKey} = $rTmp;
				}
				else
				{
					$article->{$sKey} = $sValue;
				}
			}

			// Some don't overlap, so we have to do it manually
			if (isset($aForm['articletext']) && $aForm['articletext'])
			{
				$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
				$tagPos  = preg_match($pattern, $aForm['articletext']);

				if ($tagPos == 0)
				{
					$article->introtext = $aForm['articletext'];
					$article->fulltext  = '';
				}
				else
				{
					list ($article->introtext, $article->fulltext) = preg_split($pattern, $aForm['articletext'], 2);
				}
			}

			// If we are checking from the menu item
			if (isset($aForm['params']['menu-meta_description']) && $aForm['params']['menu-meta_description'])
			{
				$article->metadesc = $aForm['params']['menu-meta_description'];
			}
		}
	}

	/**
	 * Store the score and additional info for this article
	 *
	 * @param   string $context The context of the content being passed to the plugin
	 * @param   Object $article A reference to the JTableContent object that is being saved which holds the article data
	 * @param   bool   $isNew   A boolean which is set to true if the content is about to be created
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
		// Do not process internal items
		if (in_array($context, $this->aAllowedContext) && strpos($context, 'com_pwtseo') === false)
		{
			$jFilter = InputFilter::getInstance();
			$aSEO    = $this->app->input->post->get('jform', array(), 'array')['pwtseo'];

			array_walk($aSEO, array($jFilter, 'clean'));
			$aSEO['context_id'] = $article->id;

			// We store the link so we can recognize it in the frontend
			if ($context === 'com_menus.item')
			{
				$aSEO['url'] = $article->link;
			}

			$oInput = (object) $aSEO;

			// Replace any version modifiers when inserting
			$oInput->version = preg_replace('/-\w+/', '', '1.2.1');
			$oInput->context = $context;

			$iId = $this->getHasSEOData($article->id, 'context_id', $context);

			if ($iId)
			{
				$oInput->id = $iId;
				$this->db->updateObject('#__plg_pwtseo', $oInput, array('id'));
			}
			else
			{
				$this->db->insertObject('#__plg_pwtseo', $oInput);
			}

		}

		return true;
	}

	/**
	 * Find record id based on com_content.article id
	 *
	 * @param   string $sValue   The value to look for
	 * @param   string $sKey     The key of the column
	 * @param   string $sContext The context of the item
	 *
	 * @return  integer|null the ID of the record or null if nothing found
	 *
	 * @since   1.0
	 */
	private function getHasSEOData($sValue, $sKey = 'context_id', $sContext = 'com_content.article')
	{
		$q = $this->db->getQuery(true);

		$q
			->select('id')
			->from($this->db->quoteName('#__plg_pwtseo', 'seodata'))
			->where($this->db->quoteName('seodata.' . $sKey) . ' = ' . $this->db->quote($sValue))
			->where($this->db->quoteName('seodata.context') . ' = ' . $this->db->quote($sContext));

		try
		{
			return $this->db->setQuery($q)->loadResult();
		}
		catch (Exception $e)
		{
		}

		return 0;
	}

	/**
	 * Handle on BeforeRender to set the page title
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function onBeforeRender()
	{
		$input = $this->app->input;
		$doc   = Factory::getDocument();

		if ($this->app->isSite() && $doc instanceof HtmlDocument)
		{
			$sId      = (int) $input->getInt('id');
			$sContext = $input->getCmd('context', $input->getCmd('option') . '.' . $input->getCmd('view'));

			// First we try original context
			$aSEO = (array) $this->getSEOData($sId, 'context_id', $sContext);

			// Try menu-items
			if (!$aSEO)
			{
				// Check for link, which is new way, make sure we have b/c so check for path aswell
				$link = Factory::getApplication()->getMenu()->getActive()->link;
				$aSEO = (array) $this->getSEOData($link, 'url', 'com_menus.item');

				if (!$aSEO)
				{
					$aSEO = (array) $this->getSEOData(Uri::getInstance()->getPath(), 'url', 'com_menus.item');
				}
			}

			// Finally maybe custom url
			if (!$aSEO)
			{
				$aSEO = (array) $this->getSEOData(Uri::getInstance()->getPath(), 'url', 'com_pwtseo.custom');
			}

			$bPreview = $input->getBool('pwtseo_preview', false);

			if ($aSEO || $bPreview)
			{
				if ($bPreview)
				{
					$aSEO = (array) $input->post->get('jform', '', 'raw')['pwtseo'];
				}

				if (strlen($aSEO['page_title']) && $aSEO['override_page_title'] === '1')
				{
					$doc->setTitle($this->getFullTitle($aSEO['page_title']));
				}

				if (isset($aSEO['override_canonical']) && $aSEO['override_canonical'])
				{
					switch ((int) $aSEO['override_canonical'])
					{
						// Self referencing
						case 2:
							$this->setCanonical(Uri::getInstance());
							break;
						// Custom
						case 3:
							$this->setCanonical($aSEO['canonical']);
							break;
						// Use plugin settings
						case 1:
						default:
							if ($this->params->get('set_canonical', 1))
							{
								$this->setCanonical(Uri::getInstance());
							}
					}
				}

				// Handle repeatable field
				if (isset($aSEO['adv_open_graph']))
				{
					$aAdvancedFields = json_decode($aSEO['adv_open_graph']);

					if ($aAdvancedFields && isset($aAdvancedFields->og_title))
					{
						$aKeys = array_keys($aAdvancedFields->og_title);

						foreach ($aKeys as $iKey)
						{
							$doc->addCustomTag(
								'<meta property="' . $aAdvancedFields->og_title[$iKey] . '" content="' . $aAdvancedFields->og_content[$iKey] . '" >'
							);
						}
					}
				}

				if (strlen($aSEO['facebook_title']))
				{
					$doc->setMetaData(
						'og:title', $aSEO['facebook_title'], 'property'
					);
				}

				if (strlen($aSEO['facebook_description']))
				{
					$doc->setMetaData(
						'og:description', $aSEO['facebook_description'], 'property'
					);
				}

				if (strlen($aSEO['facebook_image']))
				{
					$doc->setMetaData(
						'og:image', Uri::base() . $aSEO['facebook_image'], 'property'
					);
				}

				if (strlen($aSEO['twitter_title']))
				{
					$doc->setMetaData(
						'twitter:title', $aSEO['twitter_title'], 'property'
					);
				}

				if (strlen($aSEO['twitter_description']))
				{
					$doc->setMetaData(
						'twitter:description', $aSEO['twitter_description'], 'property'
					);
				}

				if (strlen($aSEO['twitter_image']))
				{
					$doc->setMetaData(
						'twitter:image', Uri::base() . $aSEO['twitter_image'], 'property'
					);
				}

				if (strlen($aSEO['twitter_card']))
				{
					$doc->setMetaData(
						'twitter:card', $aSEO['twitter_card'], 'property'
					);
				}

				if (strlen($aSEO['twitter_site_username']))
				{
					$doc->setMetaData(
						'twitter:site', $aSEO['twitter_site_username'], 'property'
					);
				}

				if (strlen($aSEO['google_title']))
				{
					$doc->setMetaData(
						'google:title', $aSEO['google_title']
					);
				}

				if (strlen($aSEO['google_description']))
				{
					$doc->setMetaData(
						'google:description', $aSEO['google_description']
					);
				}

				if (strlen($aSEO['google_image']))
				{
					$doc->setMetaData(
						'google:image', Uri::base() . $aSEO['google_image']
					);
				}

			}
			else
			{
				if ($this->params->get('set_canonical', 1))
				{
					$this->setCanonical(Uri::getInstance());
				}
			}
		}
	}

	/**
	 * Returns the full page title, with either prefix or suffix
	 *
	 * @param   string $title The title to process
	 *
	 * @return  string The full page title
	 *
	 * @since   1.2.1
	 */
	protected function getFullTitle($title)
	{
		if ($this->app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
		}
		elseif ($this->app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
		}

		return $title;
	}

	/**
	 * Method to set the canonical url for the current page.
	 *
	 * @param   string $sUrl The url to set as canonical
	 *
	 * @return  void
	 *
	 * @since   1.0.2
	 */
	protected function setCanonical($sUrl)
	{
		/** @var HtmlDocument $doc */
		$doc = Factory::getApplication()->getDocument();

		// We might get a OpenSearchDocument, which doesn't have headlinks
		if (method_exists($doc, 'addHeadLink'))
		{
			// Remove the tag if it already exists
			foreach ($doc->_links as $linkUrl => $link)
			{
				if (isset($link['relation']) && $link['relation'] === 'canonical')
				{
					unset($doc->_links[$linkUrl]);
					break;
				}
			}

			$doc->addHeadLink(htmlspecialchars($sUrl), 'canonical');
		}
	}

	/**
	 * This function is called form the backend to check if the focus word is used already
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function onAjaxPWTSeo()
	{
		if ($this->app->isSite())
		{
			die('Restricted Access');
		}

		$aResponse  = array('count' => 0);
		$sFocusWord = $this->app->input->getCmd('focusword', '');
		$iArticleId = $this->app->input->getInt('id', '');
		$uUrl       = $this->app->input->get('url', '', 'html');

		$q = $this->db->getQuery(true);

		$q
			->select('COUNT(*)')
			->from($this->db->quoteName('#__plg_pwtseo', 'a'))
			->where('LOWER(a.`focus_word`) = ' . $this->db->quote(strtolower($sFocusWord)))
			->where($this->db->quoteName('context_id') . ' != ' . $iArticleId);

		try
		{
			$aResponse['count'] = (int) $this->db->setQuery($q)->loadResult();
		}
		catch (Exception $e)
		{
			$aResponse['count'] = 0;
		}

		$aResponse['reachable'] = $uUrl ? $this->isReachable($uUrl) : 1;

		return $aResponse;
	}

	/**
	 * Checks if a given url is allowed by robots.txt
	 *
	 * @param   string $sUrl The url to check
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	protected function isReachable($sUrl)
	{
		jimport('joomla.filesystem.file');
		$sRobots = JPATH_ROOT . '/robots.txt';

		if (!JFile::exists($sRobots))
		{
			return true;
		}

		$aRobots = file($sRobots, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$i       = count($aRobots);
		$sDomain = rtrim(Uri::root(), '/');

		while ($i--)
		{
			// If it's not a regular disallow directive, skip it
			if (strpos($aRobots[$i], 'Disallow:') !== 0)
			{
				continue;
			}

			list($cmd, $url) = explode(': ', $aRobots[$i]);

			if (stripos($sUrl, $sDomain . $url) !== false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Anything we need to do after initialising:
	 *  - We need to ensure the correct language is set (meaning the language in the article)
	 *
	 * @return  void
	 *
	 * @since   1.2.1
	 */
	public function onAfterInitialise()
	{
		// We can recognize our calls by 'pwtseo_preview' in the request
		if ($this->app->input->get('pwtseo_preview', false) === '1')
		{
			$router = $this->app->getRouter();

			$router->attachBuildRule(array($this, 'preprocessBuildRule'), JRouter::PROCESS_BEFORE);
		}
	}

	/**
	 * Pre-process the URI to ensure the correct language is set for further components.
	 *
	 * @param   Router $router JRouter object.
	 * @param   Uri    $uri    JUri object.
	 *
	 * @return  void
	 *
	 * @since   1.2.1
	 */
	public function preprocessBuildRule(&$router, &$uri)
	{
		$lang = $this->app->input->get('lang');

		// If set to all, we resort to default. It will be empty if site is not multi-lingual
		if ($lang === '*')
		{
			$lang = Factory::getLanguage()->getDefault();
		}

		// Override the language with the one provided in the form, or use default
		if ($lang)
		{
			$uri->setVar('lang', $lang);
		}
	}

	/**
	 * The resulting page is retrieved here and processed for the backend
	 *
	 * @return  object
	 *
	 * @since   1.0
	 */
	public function onAjaxPWTSEOPage()
	{
		if (!$this->app->isSite())
		{
			die('Restricted Access');
		}

		$aResponse = array();
		$aData     = $this->app->input->get('jform', array(), 'array');

		$iStep = !$this->app->input->getInt('find_unique', 0) ? 1 : 2;

		switch ($this->app->input->get('context'))
		{
			case 'com_content.article':
				$aResponse = $this->processArticle($aData, $iStep);
				break;
			case 'com_pwtseo.custom':
				$aResponse = $this->processCustom($aData, $iStep);
				break;
			case 'com_menus.item':
				$aResponse = $this->processMenuItem($aData, $iStep);
				break;
		}

		return (object) $aResponse;
	}

	/**
	 * Process an content article. Depending on the step, different data is returned because we process data in two seperate steps.
	 *
	 * @param   array $aData The request data
	 * @param   int   $iStep The step to get the data for
	 *
	 * @return  array The array with data required for the processing
	 *
	 * @since   1.2.0
	 */
	protected function processArticle($aData, $iStep = 1)
	{
		$aArr = array();
		$iId  = isset($aData['id']) && $aData['id'] ? (int) $aData['id'] : 0;

		require_once JPATH_SITE . '/components/com_content/helpers/route.php';

		if ($iStep === 1)
		{
			$aArr['url'] = Uri::root(
				true,
				str_replace(
					'%2F',
					'/',
					rawurlencode(
						urldecode(
							Route::_(ContentHelperRoute::getArticleRoute($iId, (int) $aData['catid'], $aData['language']))
						)
					)
				)
			);

			$aArr['reachable'] = $this->isReachable($aArr['url']);
			$aArr['count']     = $this->findUsages($aData['pwtseo']['focus_word'], $iId);
		}

		if ($iStep === 2)
		{
			$aArr['page_title_unique']    = $this->isTitleUnique($aData['title'], $iId);
			$aArr['page_metadesc_unique'] = isset($aData['metadesc']) ? $this->isMetaDescriptionUnique($aData['metadesc']) : true;

			// Here we modify the alias and get the route based on the given alias
			if ($iId > 0)
			{
				$db = Factory::getDbo();
				$q  = $db->getQuery(true);

				$q
					->select($db->quoteName('a.alias'))
					->from($db->quoteName('#__content', 'a'))
					->where('a.id = ' . $iId);

				try
				{
					$sOriginalAlias = $db->setQuery($q)->loadResult();
					$sModifiedAlias = OutputFilter::stringURLUnicodeSlug($aData['alias']);

					$oTmpAlias = (object) array(
						'id'    => $iId,
						'alias' => $sModifiedAlias
					);

					$db->updateObject('#__content', $oTmpAlias, array('id'));

					$aArr['new_url'] = Uri::root(
						true,
						str_replace(
							'%2F',
							'/',
							rawurlencode(
								urldecode(
									Route::_(ContentHelperRoute::getArticleRoute($iId, (int) $aData['catid'], $aData['language']))
								)
							)
						)
					);

					// Revert the change
					$oTmpAlias->alias = $sOriginalAlias;
					$db->updateObject('#__content', $oTmpAlias, array('id'));
				}
				catch (Exception $e)
				{
				}
			}
		}

		return $aArr;
	}

	/**
	 * Function that checks the database to see how many times a given word is used
	 *
	 * @param   string $sWord The word to check
	 * @param   int    $iPK   The id of the content item, this is needed to exclude current article from the count
	 *
	 * @return  int The amount of times the focus word is used
	 *
	 * @since   1.0
	 */
	protected function findUsages($sWord, $iPK)
	{
		$q     = $this->db->getQuery(true);
		$sWord = InputFilter::getInstance()->clean($sWord);

		$q
			->select('COUNT(*)')
			->from($this->db->qn('#__plg_pwtseo', 'a'))
			->where('LOWER(a.`focus_word`) = ' . $this->db->quote(strtolower($sWord)))
			->where('context_id != ' . $iPK);

		try
		{
			return (int) $this->db->setQuery($q)->loadResult();
		}
		catch (Exception $e)
		{
		}

		return 0;
	}

	/**
	 * Function to check if given title is unique across articles. For now we only check com_content
	 *
	 * @param   string $sTitle The title to check
	 * @param   int    $iId    The id of the article to ignore
	 *
	 * @return  bool True if title is found only once, false otherwise
	 *
	 * @since   1.0.1
	 */
	protected function isTitleUnique($sTitle, $iId = 0)
	{
		$q = $this->db->getQuery(true);

		$q
			->select('COUNT(*)')
			->from($this->db->quoteName('#__content', 'content'))
			->where('LOWER(' . $this->db->quoteName('content.title') . ') = ' . $this->db->quote(strtolower($sTitle)));

		if ($iId)
		{
			$q->where($this->db->quoteName('id') . ' != ' . $iId);
		}

		try
		{
			// If article to ignore is provided, we don't want to find any result. Otherwise we assume the 1 found is current article
			return (int) $this->db->setQuery($q)->loadResult() <= ($iId ? 0 : 1);
		}
		catch (Exception $e)
		{
			return true;
		}
	}

	/**
	 * Function to check if given meta description is unique across articles. For now we only check com_content
	 *
	 * @param   string $sDescription The meta description to check
	 *
	 * @return  bool True if the description is found only once, false otherwise
	 *
	 * @since   1.0.1
	 */
	protected function isMetaDescriptionUnique($sDescription)
	{
		$q = $this->db->getQuery(true);

		$q
			->select('COUNT(*)')
			->from($this->db->quoteName('#__content', 'content'))
			->where('LOWER(' . $this->db->quoteName('content.metadesc') . ') = ' . $this->db->quote(strtolower($sDescription)));

		// Handle JoomSEF
		if (ComponentHelper::isEnabled('com_sef'))
		{
			$q
				->clear()
				->select('COUNT(*)')
				->from($this->db->quoteName('#__sefurls', 'content'))
				->where('LOWER(' . $this->db->quoteName('content.metadesc') . ') = ' . $this->db->quote(strtolower($sDescription)));
		}

		// Handle sh404SEF
		if (ComponentHelper::isEnabled('com_sh404sef'))
		{
			$q
				->clear()
				->select('COUNT(*)')
				->from($this->db->quoteName('#__content', 'content'))
				->where('LOWER(' . $this->db->quoteName('content.metadesc') . ') = ' . $this->db->quote(strtolower($sDescription)));
		}

		try
		{
			// Regardless of where we look, if we find it more then once there's a duplicate
			return $this->db->setQuery($q)->loadResult() <= 1;
		}
		catch (Exception $e)
		{
			return true;
		}
	}

	/**
	 * Process an custom url. Depending on the step, different data is returned because we process data in two seperate steps.
	 *
	 * @param   array $aData The request data
	 * @param   int   $iStep The step to get the data for
	 *
	 * @return  array The array with data required for the processing
	 *
	 * @since   1.2.0
	 */
	protected function processCustom($aData, $iStep = 1)
	{
		$aArr = array();
		$iId  = (int) Uri::getInstance($this->app->input->get('form_url', '', 'html'))->getQuery(true)['id'];

		if ($iStep === 2)
		{
			$aArr['page_title_unique']    = $this->isTitleUnique($aData['title'], $iId);
			$aArr['page_metadesc_unique'] = isset($aData['metadesc']) ? $this->isMetaDescriptionUnique($aData['metadesc']) : true;
			$aArr['new_url']              = Uri::root(true, $aData['url']);

			$count         = $this->findUsages($aData['pwtseo']['focus_word'], $iId);
			$aArr['count'] = $count > 1 ? $count - 1 : 0;
		}

		return $aArr;
	}

	/**
	 * Process a menu item. Depending on the step, different data is returned because we process data in two seperate steps.
	 *
	 * @param   array $aData The request data
	 * @param   int   $iStep The step to get the data for
	 *
	 * @return  array The array with data required for the processing
	 *
	 * @since   1.2.0
	 */
	protected function processMenuItem($aData, $iStep = 1)
	{
		$aArr = array();
		$iId  = (int) Uri::getInstance($this->app->input->get('form_url', '', 'html'))->getQuery(true)['id'];

		if ($iStep === 1)
		{
			// We have to decode/encode to support Hebrew and similar languages
			$aArr['url'] = Uri::root(
				true,
				str_replace(
					'%2F',
					'/',
					rawurlencode(
						urldecode(
							Route::_('index.php?Itemid=' . $aData['id'])
						)
					)
				)
			);


			$aArr['reachable'] = $this->isReachable($aArr['url']);
			$aArr['count']     = $this->findUsages($aData['pwtseo']['focus_word'], $iId);
		}

		if ($iStep === 2)
		{
			$aArr['page_title_unique']    = $this->isTitleUnique($aData['title'], $iId);
			$aArr['page_metadesc_unique'] = isset($aData['metadesc']) ? $this->isMetaDescriptionUnique($aData['metadesc']) : true;
			$aArr['new_url']              = Uri::root(
				true,
				str_replace(
					'%2F',
					'/',
					rawurlencode(
						urldecode(
							Route::_('index.php?Itemid=' . $aData['id'])
						)
					)
				)
			);
		}

		return $aArr;
	}
}
