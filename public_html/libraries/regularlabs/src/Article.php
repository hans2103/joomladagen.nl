<?php
/**
 * @package         Regular Labs Library
 * @version         17.5.13702
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

/**
 * Class Article
 * @package RegularLabs\Library
 */
class Article
{
	/**
	 * Passes the different article parts through the given plugin method
	 *
	 * @param object $article
	 * @param string $context
	 * @param object $helper
	 * @param string $method
	 * @param array  $params
	 * @param array  $ignore
	 */
	public static function process(&$article, &$context, &$helper, $method, $params = [], $ignore = [])
	{
		self::processText('description', $article, $helper, $method, $params, $ignore);
		self::processText('title', $article, $helper, $method, $params, $ignore);
		self::processText('created_by_alias', $article, $helper, $method, $params, $ignore);

		if (Document::isCategoryList($context))
		{
			return;
		}

		self::processText('text', $article, $helper, $method, $params, $ignore);

		if (!empty($article->text))
		{
			return;
		}

		self::processText('introtext', $article, $helper, $method, $params, $ignore);
		self::processText('fulltext', $article, $helper, $method, $params, $ignore);
	}

	private static function processText($type = '', &$article, &$helper, $method, $params = [], $ignore = [])
	{
		if (empty($article->{$type}))
		{
			return;
		}

		if (in_array($type, $ignore))
		{
			return;
		}

		call_user_func_array([$helper, $method], array_merge([&$article->{$type}], $params));
	}
}
