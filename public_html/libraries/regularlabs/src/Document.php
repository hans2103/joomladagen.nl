<?php
/**
 * @package         Regular Labs Library
 * @version         17.2.6639
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use JFactory;

/**
 * Class Document
 * @package RegularLabs\Library
 */
class Document
{

	/**
	 * Check if page is an admin page
	 *
	 * @param bool $exclude_login
	 *
	 * @return bool
	 */
	public static function isAdmin($exclude_login = false)
	{
		$app = JFactory::getApplication();

		return (
			$app->isAdmin()
			&& (!$exclude_login || !JFactory::getUser()->get('guest'))
			&& $app->input->get('task') != 'preview'
			&& !(
				$app->input->get('option') == 'com_finder'
				&& $app->input->get('format') == 'json'
			)
		);
	}

	/**
	 * Check if page is an edit page
	 *
	 * @return bool
	 */
	public static function isEditPage()
	{
		$hash = md5('isEditPage');

		if (Cache::has($hash))
		{
			return Cache::get($hash);
		}

		$app = JFactory::getApplication();

		$option = $app->input->get('option');
		// always return false for these components
		if (in_array($option, ['com_rsevents', 'com_rseventspro']))
		{
			return Cache::set(
				$hash,
				false
			);
		}

		$task = $app->input->get('task');
		if (strpos($task, '.') !== false)
		{
			$task = explode('.', $task);
			$task = array_pop($task);
		}

		$view = $app->input->get('view');
		if (strpos($view, '.') !== false)
		{
			$view = explode('.', $view);
			$view = array_pop($view);
		}

		$isedit = (
			in_array($task, ['edit', 'form', 'submission'])
			|| in_array($view, ['edit', 'form'])
			|| in_array($app->input->get('do'), ['edit', 'form'])
			|| in_array($app->input->get('layout'), ['edit', 'form', 'write'])
			|| in_array($app->input->get('option'), ['com_contentsubmit', 'com_cckjseblod'])
			|| ($app->input->get('option') == 'com_comprofiler' && in_array($task, ['', 'userdetails']))
			|| self::isAdmin()
		);

		return Cache::set(
			$hash,
			$isedit
		);
	}

	/**
	 * Checks if current page is a html page
	 *
	 * @return bool
	 */
	public static function isHtml()
	{
		return (JFactory::getDocument()->getType() == 'html');
	}

	/**
	 * Checks if current page is a feed
	 *
	 * @return bool
	 */
	public static function isFeed()
	{
		return (
			JFactory::getDocument()->getType() == 'feed'
			|| JFactory::getApplication()->input->getWord('format') == 'feed'
			|| JFactory::getApplication()->input->getWord('format') == 'xml'
			|| JFactory::getApplication()->input->getWord('type') == 'rss'
			|| JFactory::getApplication()->input->getWord('type') == 'atom'
		);
	}

	/**
	 * Checks if current page is a https (ssl) page
	 *
	 * @return bool
	 */
	public static function isHttps()
	{
		return (
			(!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off')
			|| (isset($_SERVER['SSL_PROTOCOL']))
			|| (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
			|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
		);
	}

	/**
	 * Checks if context/page is a category list
	 *
	 * @param string $context
	 *
	 * @return bool
	 */
	public static function isCategoryList($context)
	{
		$hash = md5('isCategoryList_' . $context);

		if (Cache::has($hash))
		{
			return Cache::get($hash);
		}

		// Return false if it is not a category page
		if ($context != 'com_content.category' || JFactory::getApplication()->input->get('view') != 'category')
		{
			return Cache::set($hash, false);
		}

		// Return false if it is not a list layout
		if (JFactory::getApplication()->input->get('layout') && JFactory::getApplication()->input->get('layout') != 'list')
		{
			return Cache::set($hash, false);
		}

		// Return true if it IS a list layout
		return Cache::set($hash, true);
	}

	/**
	 * Adds a script file to the page (with optional versioning)
	 *
	 * @param string $file
	 * @param string $version
	 */
	public static function script($file, $version = '')
	{
		if (strpos($file, 'regularlabs/') === 0)
		{
			$version = '17.2.6639';
		}

		if (!$file = File::getMediaFile('js', $file))
		{
			return;
		}

		if (!empty($version))
		{
			$file .= '?v=' . $version;
		}

		JFactory::getDocument()->addScript($file);
	}

	/**
	 * Adds a stylesheet file to the page(with optional versioning)
	 *
	 * @param string $file
	 * @param string $version
	 */
	public static function style($file, $version = '')
	{
		if (strpos($file, 'regularlabs/') === 0)
		{
			$version = '17.2.6639';
		}

		if (!$file = File::getMediaFile('css', $file))
		{
			return;
		}

		if (!empty($version))
		{
			$file .= '?v=' . $version;
		}

		JFactory::getDocument()->addStylesheet($file);
	}

	/**
	 * Alias of \RegularLabs\Library\Document::style()
	 *
	 * @param string $file
	 * @param string $version
	 */
	public static function stylesheet($file, $version = '')
	{
		self::style($file, $version);
	}

	/**
	 *  Adds a javascript declaration to the page
	 *
	 * @param string $content
	 * @param string $name
	 * @param bool   $minify
	 * @param string $type
	 */
	public static function scriptDeclaration($content = '', $name = '', $minify = true, $type = 'text/javascript')
	{
		if ($minify)
		{
			$content = self::minify($content);
		}

		if (!empty($name))
		{
			list($start, $end) = Protect::getInlineCommentTags($name, 'scripts');

			$spacer  = $minify ? ' ' : "\n";
			$content = $start . $spacer . $content . $spacer . $end;
		}

		JFactory::getDocument()->addScriptDeclaration($content, $type);
	}

	/**
	 *  Adds a stylesheet declaration to the page
	 *
	 * @param string $content
	 * @param string $name
	 * @param bool   $minify
	 * @param string $type
	 */
	public static function styleDeclaration($content = '', $name = '', $minify = true, $type = 'text/css')
	{
		if ($minify)
		{
			$content = self::minify($content);
		}

		if (!empty($name))
		{
			list($start, $end) = Protect::getInlineCommentTags($name, 'styles');

			$spacer  = $minify ? ' ' : "\n";
			$content = $start . $spacer . $content . $spacer . $end;
		}

		JFactory::getDocument()->addStyleDeclaration($content, $type);
	}

	/**
	 * Remove style/css blocks from html string
	 *
	 * @param string $string
	 * @param string $name
	 */
	public static function removeScriptsStyles(&$string, $name, $alias = '')
	{
		list($start, $end) = Protect::getInlineCommentTags($name, null, true);
		$alias = $alias ?: Extension::getAliasByName($name);

		$string = RegEx::replace('((?:;\s*)?)(;?)' . $start . '.*?' . $end . '\s*', '\1', $string);
		$string = RegEx::replace('\s*<link [^>]*href="[^"]*/(' . $alias . '/css|css/' . $alias . ')/[^"]*\.css[^"]*"[^>]*( /)?>', '', $string);
		$string = RegEx::replace('\s*<script [^>]*src="[^"]*/(' . $alias . '/js|js/' . $alias . ')/[^"]*\.js[^"]*"[^>]*></script>', '', $string);
	}

	/**
	 * Returns the document buffer
	 *
	 * @return null|string
	 */
	public static function getBuffer()
	{
		$buffer = JFactory::getDocument()->getBuffer('component');

		if (empty($buffer) || !is_string($buffer))
		{
			return null;
		}

		$buffer = trim($buffer);

		if (empty($buffer))
		{
			return null;
		}

		return $buffer;
	}

	/**
	 * Set the document buffer
	 *
	 * @param string $buffer
	 */
	public static function setBuffer($buffer = '')
	{
		JFactory::getDocument()->setBuffer($buffer, 'component');
	}

	/**
	 * Minify the given string
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	public static function minify($string)
	{
		// place new lines around string to make regex searching easier
		$string = "\n" . $string . "\n";

		// Remove comment lines
		$string = RegEx::replace('\n\s*//.*?\n', '', $string);
		// Remove comment blocks
		$string = RegEx::replace('/\*.*?\*/', '', $string);
		// Remove enters
		$string = RegEx::replace('\n\s*', ' ', $string);

		// Remove surrounding whitespace
		$string = trim($string);

		return $string;
	}
}
