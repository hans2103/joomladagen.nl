<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2019 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

/**
 * PWTSEO helper for the backend.
 *
 * @since    1.0
 */
class PWTSEOHelper
{
	/**
	 * Render submenu.
	 *
	 * @param   string $vName The name of the current view.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			Text::_('COM_PWTSEO_DASHBOARD_LABEL'),
			'index.php?option=com_pwtseo',
			$vName == 'pwtseo'
		);

		JHtmlSidebar::addEntry(
			Text::_('COM_PWTSEO_ARTICLES_LABEL'),
			'index.php?option=com_pwtseo&view=articles',
			$vName == 'articles'
		);

		JHtmlSidebar::addEntry(
			Text::_('COM_PWTSEO_CUSTOM_LABEL'),
			'index.php?option=com_pwtseo&view=customs',
			$vName == 'customs'
		);

		JHtmlSidebar::addEntry(
			Text::_('COM_PWTSEO_MENUS_LABEL'),
			'index.php?option=com_pwtseo&view=menus',
			$vName == 'menus'
		);

		JHtmlSidebar::addEntry(
			Text::_('COM_PWTSEO_DATALAYER_LABEL'),
			'index.php?option=com_pwtseo&view=datalayers',
			$vName == 'datalayers'
		);
	}

	/**
	 * Returns a human readable version of the given context
	 *
	 * @param   string $sContext The context to get the label for
	 *
	 * @return  string The human readable label
	 *
	 * @since   1.0
	 */
	public static function getContextLabel($sContext)
	{
		$aArr = array(
			'com_content.article' => Text::_('COM_PWTSEO_CONTEXT_CONTENT_ARTICLES_LABEL')
		);

		return isset($aArr[$sContext]) ? $aArr[$sContext] : '';
	}

	/**
	 * Returns the ID of the PWT SEO plugin on this system
	 *
	 * @return  int The ID or 0 on failure
	 *
	 * @since   1.2.0
	 */
	public static function getPlugin()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('client_id') . ' = 0')
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('element') . ' = ' . $db->quote('pwtseo'));

		try
		{
			return $db->setQuery($query)->loadResult();
		}
		catch (Exception $e)
		{
		}

		return 0;
	}

	/**
	 * @param   string $text      The text of which the words should be counted
	 * @param   string $blacklist Optional string with words that should be ignored
	 * @param   int    $max       Max number of words to return
	 *
	 * @return  array The list of most common words, sorted by occurrence
	 *
	 * @since   1.3.0
	 */
	public static function getMostCommenWords($text, $blacklist = '', $max = 15)
	{
		// Remove most common tags and html
		$text = array_count_values(explode(' ', preg_replace('/{+?.*?}+?|\.|:/i', ' ', strip_tags($text))));

		uasort(
			$text,
			function ($a, $b) {
				return $b - $a;
			}
		);

		foreach ($text as $word => $count)
		{
			if (stripos($blacklist, $word) !== false)
			{
				unset($text[$word]);
			}
		}

		return array_slice(array_keys($text), 0, $max);
	}
}
