<?php
/**
 * @package         Sourcerer
 * @version         7.0.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Sourcerer;

defined('_JEXEC') or die;

use JFactory;
use JFile;
use JText;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;

class Replace
{
	static $current_area = null;

	/* <<< [PRO] <<< */

	public static function replace(&$string, $area = 'article', $article = '', $remove = false)
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		Protect::_($string);

		$regex = Params::getRegex();

		$array       = self::stringToSplitArray($string, $regex, false);
		$array_count = count($array);

		if ($array_count <= 1)
		{
			return;
		}

		for ($i = 1; $i < $array_count - 1; $i++)
		{
			if (!fmod($i, 2) || !RL_RegEx::match($regex, $array[$i], $match))
			{
				continue;
			}

			$content = self::handleMatch($match, $area, $article, $remove);

			$array[$i] = $match['start_pre'] . $match['start_post'] . $content . $match['end_pre'] . $match['end_post'];
		}

		$string = implode('', $array);
	}

	public static function replaceInTheRest(&$string)
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		$params = Params::get();

		list($start_tags, $end_tags) = Params::getTags();

		list($pre_string, $string, $post_string) = RL_Html::getContentContainingSearches(
			$string,
			$start_tags,
			$end_tags
		);

		if ($string == '')
		{
			$string = $pre_string . $string . $post_string;

			return;
		}

		// COMPONENT
		if (RL_Document::isFeed())
		{
			$string = RL_RegEx::replace('(<item[^>]*>)', '\1<!-- START: SRC_COMPONENT -->', $string);
			$string = str_replace('</item>', '<!-- END: SRC_COMPONENT --></item>', $string);
		}

		if (strpos($string, '<!-- START: SRC_COMPONENT -->') === false)
		{
			Area::tag($string, 'component');
		}

		$components = Area::get($string, 'component');
		foreach ($components as $component)
		{
			self::replace($component['1'], 'components', '');
			$string = str_replace($component['0'], $component['1'], $string);
		}

		// EVERYWHERE
		self::replace($string, 'other');

		$string = $pre_string . $string . $post_string;
	}

	private static function handleMatch(&$match, $area = 'article', $article = '', $remove = false)
	{
		if ($remove)
		{
			return '';
		}

		$params = Params::get();

		$data    = RL_PluginTag::getAttributesFromStringOld(trim($match['data']), []);
		$content = trim($match['content']);

		$remove_html = !in_array('0', $data->params);

		// Remove html tags if code is placed via the WYSIWYG editor
		if ($remove_html)
		{
			$content = RL_Html::convertWysiwygToPlainText($content);
		}

		self::replacePhpShortCodes($content);

		// Add the include file if file=... or include=... is used in the {source} tag
		$file = !empty($data->file) ? $data->file : (!empty($data->include) ? $data->include : '');
		if (!empty($file) && JFile::exists(JPATH_SITE . $params->include_path . $file))
		{
			$content = '<?php include JPATH_SITE . \'' . $params->include_path . $file . '\'; ?>' . $content;
		}

		self::replaceTags($content, $area, $article);

		if (!$remove_html)
		{
			return $content;
		}

		$trim = isset($data->trim) ? $data->trim : $params->trim;

		if ($trim)
		{
			$tags = RL_Html::cleanSurroundingTags([
				'start_pre'  => $match['start_pre'],
				'start_post' => $match['start_post'],
			]);

			$match = array_merge($match, $tags);

			$tags = RL_Html::cleanSurroundingTags([
				'end_pre'  => $match['end_pre'],
				'end_post' => $match['end_post'],
			]);

			$match = array_merge($match, $tags);

			$tags = RL_Html::cleanSurroundingTags([
				'start_pre' => $match['start_pre'],
				'end_post'  => $match['end_post'],
			]);

			$match = array_merge($match, $tags);
		}

		return $content;
	}

	private static function replacePhpShortCodes(&$string)
	{
		// Replace <? with <?php
		$string = RL_RegEx::replace('<\?(\s.*?)\?>', '<?php\1?>', $string);
		// Replace <?= with <?php echo
		$string = RL_RegEx::replace('<\?=\s*(.*?)\?>', '<?php echo \1?>', $string);
	}

	private static function replaceTags(&$string, $area = 'article', $article = '')
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		$params = Params::get();

		// allow in component?
		if (RL_Protect::isRestrictedComponent(isset($params->components) ? $params->components : [], $area))
		{
			Protect::protectTags($string);

			return;
		}

		self::replaceTagsByType($string, $area, 'php', $article);
		self::replaceTagsByType($string, $area, 'all');
		self::replaceTagsByType($string, $area, 'js');
		self::replaceTagsByType($string, $area, 'css');
	}

	private static function replaceTagsByType(&$string, $area = 'article', $type = 'all', $article = '')
	{
		$params = Params::get();

		if (!is_string($string) || $string == '')
		{
			return;
		}

		$type_ext = '_' . $type;
		if ($type == 'all')
		{
			$type_ext = '';
		}

		$a             = Params::getArea($area);
		$security_pass = isset($a->{'security_pass' . $type_ext}) ? $a->{'security_pass' . $type_ext} : true;
		$enable = isset($a->{'enable' . $type_ext}) ? $a->{'enable' . $type_ext} : true;

		switch ($type)
		{
			case 'php':
				self::replaceTagsPHP($string, $enable, $security_pass, $article);
				break;
			case 'js':
				self::replaceTagsJS($string, $enable, $security_pass);
				break;
			case 'css':
				self::replaceTagsCSS($string, $enable, $security_pass);
				break;
			default:
				self::replaceTagsAll($string, $enable, $security_pass);
				break;
		}
	}

	/**
	 * Replace any html style tags by a comment tag if not permitted
	 * Match: <...>
	 */
	private static function replaceTagsAll(&$string, $enabled = true, $security_pass = true)
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		if (!$enabled)
		{
			// replace source block content with HTML comment
			$string = Protect::getMessageCommentTag(JText::_('SRC_CODE_REMOVED_NOT_ENABLED'));

			return;
		}

		if (!$security_pass)
		{
			// replace source block content with HTML comment
			$string = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_SECURITY', ''));

			return;
		}

		self::cleanTags($string);

		$a = Params::getArea(self::$current_area);
		$forbidden_tags_array = explode(',', $a->forbidden_tags);
		RL_Array::clean($forbidden_tags_array);
		// remove the comment tag syntax from the array - they cannot be disabled
		$forbidden_tags_array = array_diff($forbidden_tags_array, ['!--']);
		// reindex the array
		$forbidden_tags_array = array_merge($forbidden_tags_array);

		$has_forbidden_tags = false;
		foreach ($forbidden_tags_array as $forbidden_tag)
		{
			if (!(strpos($string, '<' . $forbidden_tag) == false))
			{
				$has_forbidden_tags = true;
				break;
			}
		}

		if (!$has_forbidden_tags)
		{
			return;
		}

		// double tags
		$tag_regex = '<\s*([a-z\!][^>\s]*?)(?:\s+.*?)?>.*?</\1>';
		RL_RegEx::matchAll($tag_regex, $string, $matches);

		if (!empty($matches))
		{
			foreach ($matches as $match)
			{
				if (!in_array($match['1'], $forbidden_tags_array))
				{
					continue;
				}

				$tag    = Protect::getMessageCommentTag(JText::sprintf('SRC_TAG_REMOVED_FORBIDDEN', $match['1']));
				$string = str_replace($match['0'], $tag, $string);
			}
		}

		// single tags
		$tag_regex = '<\s*([a-z\!][^>\s]*?)(?:\s+.*?)?>';
		RL_RegEx::matchAll($tag_regex, $string, $matches);

		if (!empty($matches))
		{
			foreach ($matches as $match)
			{
				if (!in_array($match['1'], $forbidden_tags_array))
				{
					continue;
				}

				$tag    = Protect::getMessageCommentTag(JText::sprintf('SRC_TAG_REMOVED_FORBIDDEN', $match['1']));
				$string = str_replace($match['0'], $tag, $string);
			}
		}
	}

	/**
	 * Replace the PHP tags with the evaluated PHP scripts
	 * Or replace by a comment tag the PHP tags if not permitted
	 */
	private static function replaceTagsPHP(&$src_str, $src_enabled = 1, $src_security_pass = 1, $article = '')
	{
		if (!is_string($src_str) || $src_str == '')
		{
			return;
		}

		if ((strpos($src_str, '<?') === false) && (strpos($src_str, '[[?') === false))
		{
			return;
		}

		global $src_vars;

		// Match ( read {} as <> ):
		// {?php ... ?}
		// {? ... ?}
		$src_string_array       = self::stringToSplitArray($src_str, '-start-' . '\?(?:php)?[\s<](.*?)\?' . '-end-');
		$src_string_array_count = count($src_string_array);

		if ($src_string_array_count < 1)
		{
			$src_str = implode('', $src_string_array);

			return;
		}

		if (!$src_enabled)
		{
			// replace source block content with HTML comment
			$src_string_array      = [];
			$src_string_array['0'] = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_NOT_ALLOWED', JText::_('SRC_PHP'), JText::_('SRC_PHP')));

			$src_str = implode('', $src_string_array);

			return;
		}
		if (!$src_security_pass)
		{
			// replace source block content with HTML comment
			$src_string_array      = [];
			$src_string_array['0'] = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_SECURITY', JText::_('SRC_PHP')));

			$src_str = implode('', $src_string_array);

			return;
		}

		// if source block content has more than 1 php block, combine them
		if ($src_string_array_count > 3)
		{
			for ($i = 2; $i < $src_string_array_count - 1; $i++)
			{
				if (fmod($i, 2) == 0)
				{
					$src_string_array['1'] .= "<!-- SRC_SEMICOLON --> ?>" . $src_string_array[$i] . "<?php ";
					unset($src_string_array[$i]);
					continue;
				}

				$src_string_array['1'] .= $src_string_array[$i];
				unset($src_string_array[$i]);
			}
		}

		// fixes problem with _REQUEST being stripped if there is an error in the code
		$src_backup_REQUEST = $_REQUEST;
		$src_backup_vars    = array_keys(get_defined_vars());

		$semicolon  = '<!-- SRC_SEMICOLON -->';
		$src_script = trim($src_string_array['1']) . $semicolon;
		$src_script = RL_RegEx::replace('(;\s*)?' . RL_RegEx::quote($semicolon), ';', $src_script);

		$a = Params::getArea(self::$current_area);

		$src_forbidden_php_array = explode(',', $a->forbidden_php);
		RL_Array::clean($src_forbidden_php_array);

		$src_forbidden_php_regex = '[^a-z_](' . implode('|', $src_forbidden_php_array) . ')(\s*\(|\s+[\'"])';

		RL_RegEx::matchAll($src_forbidden_php_regex, ' ' . $src_script, $src_functions);

		if (!empty($src_functions))
		{
			$src_functionsArray = [];
			foreach ($src_functions as $src_function)
			{
				$src_functionsArray[] = $src_function['1'] . ')';
			}

			$src_comment = JText::_('SRC_PHP_CODE_REMOVED_FORBIDDEN') . ': ( ' . implode(', ', $src_functionsArray) . ' )';

			$src_string_array['1'] = RL_Document::isHtml()
				? Protect::getMessageCommentTag($src_comment)
				: $src_string_array['1'] = '';

			$src_str = implode('', $src_string_array);

			return;
		}

		if (!isset($Itemid))
		{
			$Itemid = JFactory::getApplication()->input->getInt('Itemid');
		}
		if (!isset($mainframe) || !isset($app))
		{
			$mainframe = $app = JFactory::getApplication();
		}
		if (!isset($document) || !isset($doc))
		{
			$document = $doc = JFactory::getDocument();
		}
		if (!isset($database) || !isset($db))
		{
			$database = $db = JFactory::getDbo();
		}
		if (!isset($user))
		{
			$user = JFactory::getUser();
		}

		$src_script = '
			if (is_array($src_vars)) {
				foreach ($src_vars as $src_key => $src_value) {
					${$src_key} = $src_value;
				}
			}
			' . $src_script . ';
			return get_defined_vars();
			';

		$temp_PHP_func = create_function('&$src_vars, &$article, &$Itemid, &$mainframe, &$app, &$document, &$doc, &$database, &$db, &$user', $src_script);

		// evaluate the script
		// but without using the the evil eval
		ob_start();
		$src_new_vars = $temp_PHP_func($src_vars, $article, $Itemid, $mainframe, $app, $document, $doc, $database, $db, $user);
		unset($temp_PHP_func);
		$src_string_array['1'] = ob_get_contents();
		ob_end_clean();

		$src_diff_vars = array_diff(array_keys($src_new_vars), $src_backup_vars);
		foreach ($src_diff_vars as $src_diff_key)
		{
			if (!in_array($src_diff_key, ['src_vars', 'article', 'Itemid', 'mainframe', 'app', 'document', 'doc', 'database', 'db', 'user'])
				&& substr($src_diff_key, 0, 4) != 'src_'
			)
			{
				$src_vars[$src_diff_key] = $src_new_vars[$src_diff_key];
			}
		}

		$src_str = implode('', $src_string_array);
	}

	/**
	 * Replace the JavaScript tags by a comment tag if not permitted
	 */
	private static function replaceTagsJS(&$string, $enabled = 1, $security_pass = 1)
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		// quick check to see if i is necessary to do anything
		if ((strpos($string, 'script') === false))
		{
			return;
		}

		// Match:
		// <script ...>...</script>
		$tag_regex =
			'(-start-' . '\s*script\s[^' . '-end-' . ']*?[^/]\s*' . '-end-'
			. '(.*?)'
			. '-start-' . '\s*\/\s*script\s*' . '-end-)';
		$arr       = self::stringToSplitArray($string, $tag_regex);
		$arr_count = count($arr);

		// Match:
		// <script ...>
		// single script tags are not xhtml compliant and should not occur, but just incase they do...
		if ($arr_count == 1)
		{
			$tag_regex = '(-start-' . '\s*script\s.*?' . '-end-)';
			$arr       = self::stringToSplitArray($string, $tag_regex);
			$arr_count = count($arr);
		}

		if ($arr_count <= 1)
		{
			return;
		}

		if (!$enabled)
		{
			// replace source block content with HTML comment
			$string = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_NOT_ALLOWED', JText::_('SRC_JAVASCRIPT'), JText::_('SRC_JAVASCRIPT')));

			return;
		}

		if (!$security_pass)
		{
			// replace source block content with HTML comment
			$string = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_SECURITY', JText::_('SRC_JAVASCRIPT')));

			return;
		}
	}

	/**
	 * Replace the CSS tags by a comment tag if not permitted
	 */
	private static function replaceTagsCSS(&$string, $enabled = 1, $security_pass = 1)
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		// quick check to see if i is necessary to do anything
		if ((strpos($string, 'style') === false) && (strpos($string, 'link') === false))
		{
			return;
		}

		// Match:
		// <script ...>...</script>
		$tag_regex =
			'(-start-' . '\s*style\s[^' . '-end-' . ']*?[^/]\s*' . '-end-'
			. '(.*?)'
			. '-start-' . '\s*\/\s*style\s*' . '-end-)';
		$arr       = self::stringToSplitArray($string, $tag_regex);
		$arr_count = count($arr);

		// Match:
		// <script ...>
		// single script tags are not xhtml compliant and should not occur, but just in case they do...
		if ($arr_count == 1)
		{
			$tag_regex = '(-start-' . '\s*link\s[^' . '-end-' . ']*?(rel="stylesheet"|type="text/css").*?' . '-end-)';
			$arr       = self::stringToSplitArray($string, $tag_regex);
			$arr_count = count($arr);
		}

		if ($arr_count <= 1)
		{
			return;
		}

		if (!$enabled)
		{
			// replace source block content with HTML comment
			$string = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_NOT_ALLOWED', JText::_('SRC_CSS'), JText::_('SRC_CSS')));

			return;
		}

		if (!$security_pass)
		{
			// replace source block content with HTML comment
			$string = Protect::getMessageCommentTag(JText::sprintf('SRC_CODE_REMOVED_SECURITY', JText::_('SRC_CSS')));

			return;
		}
	}

	private static function stringToSplitArray($string, $search, $tags = true)
	{
		$params = Params::get();

		if (!$tags)
		{
			$string = RL_RegEx::replace($search, $params->splitter . '\1' . $params->splitter, $string);

			return explode($params->splitter, $string);
		}

		foreach ($params->html_tags_syntax as $html_tags_syntax)
		{
			list($start, $end) = $html_tags_syntax;

			$tag_search = str_replace('-start-', $start, $search);
			$tag_search = str_replace('-end-', $end, $tag_search);
			$string     = RL_RegEx::replace($tag_search, $params->splitter . '\1' . $params->splitter, $string);
		}

		return explode($params->splitter, $string);
	}

	private static function cleanTags(&$string)
	{
		$params = Params::get();

		foreach ($params->html_tags_syntax as $html_tags_syntax)
		{
			list($start, $end) = $html_tags_syntax;

			$tag_regex = $start . '\s*(\/?\s*[a-z\!][^' . $end . ']*?(?:\s+.*?)?)' . $end;
			$string    = RL_RegEx::replace($tag_regex, '<\1\2>', $string);
		}
	}
}
