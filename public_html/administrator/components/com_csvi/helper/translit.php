<?php
/**
 * @package     CSVI
 * @subpackage  Helper
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Helper class for transliteration.
 *
 * @package     CSVI
 * @subpackage  Helper
 * @since       6.0
 */
final class CsviHelperTranslit
{
	/**
	 * Holds the template
	 *
	 * @var    CsviHelperTemplate
	 * @since  6.0
	 */
	protected $template = null;

	/**
	 * Construct the class and its settings.
	 *
	 * @param   CsviHelperTemplate  $template  An instance of CsviHelperTemplate
	 *
	 * @since   3.0
	 */
	public function __construct(CsviHelperTemplate $template)
	{
		// Set the parameters
		$this->template = $template;
	}

	/**
	 * This method transliterates a string into an URL
	 * safe string or returns a URL safe UTF-8 string
	 * based on the global configuration
	 *
	 * @param   string  $string  String to process
	 *
	 * @return  string  Processed string
	 *
	 * @since   3.2
	 */
	public function stringURLSafe($string)
	{
		if (JFactory::getConfig()->get('unicodeslugs') == 1)
		{
			$output = $this->getURLUnicodeSlug($string);
		}
		else
		{
			$output = $this->getURLSafe($string);
		}

		return $output;
	}

	/**
	 * This method implements unicode slugs instead of transliteration.
	 *
	 * @param   string  $string  String to process
	 *
	 * @return  string  Processed string
	 *
	 * @since   11.1
	 */
	public static function getURLUnicodeSlug($string)
	{
		// Replace double byte whitespaces by single byte (East Asian languages)
		$str = preg_replace('/\xE3\x80\x80/', ' ', $string);

		// Remove any '-' from the string as they will be used as concatenator.
		// Would be great to let the spaces in but only Firefox is friendly with this

		$str = str_replace('-', ' ', $str);

		// Replace forbidden characters by whitespaces
		$str = preg_replace('#[:\#\*"@+=;!><&\.%()\]\/\'\\\\|\[]#', "\x20", $str);

		// Delete all '?'
		$str = str_replace('?', '', $str);

		// Trim white spaces at beginning and end of alias and make lowercase
		$str = trim(JString::strtolower($str));

		// Remove any duplicate whitespace and replace whitespaces by hyphens
		$str = preg_replace('#\x20+#', '-', $str);

		return $str;
	}

	/**
	 * This method processes a string and replaces all accented UTF-8 characters by unaccented
	 * ASCII-7 "equivalents", whitespaces are replaced by hyphens and the string is lowercase.
	 *
	 * @param   string  $string  String to process
	 *
	 * @return  string  Processed string
	 *
	 * @since   11.1
	 */
	public function getURLSafe($string)
	{
		// Remove any '-' from the string since they will be used as concatenaters
		$str = str_replace('-', ' ', $string);

		$lang = $this->getLanguage();
		$str = $lang->transliterate($str);

		// Trim white spaces at beginning and end of alias and make lowercase
		$str = trim(JString::strtolower($str));

		// Remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $str);

		// Trim dashes at beginning and end of alias
		$str = trim($str, '-');

		return $str;
	}

	/**
	 * Load a language file.
	 *
	 * @param   string  $locale  The language to load.
	 *
	 * @return  object  The translation object.
	 *
	 * @since   6.0
	 */
	public function getLanguage($locale = '')
	{
		if (empty($locale))
		{
			$locale = $this->template->get('locale', 'en-GB');
		}

		$lang = JLanguage::getInstance($locale);

		return $lang;
	}
}
