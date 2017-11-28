<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access
defined('_JEXEC') or die();

JLoader::import('components.com_jticketing.helpers.main', JPATH_SITE);
JLoader::import('components.com_jticketing.router', JPATH_SITE);


/**
 * Jticketing search plugin.
 *
 * @package     Jticketing
 * @subpackage  Search.Jticketing
 * @since       2.2
 */
class PlgSearchJtevents extends JPlugin
{
	/**
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 * @since   1.6
	 */
	public function onContentSearchAreas()
	{
		JPlugin::loadLanguage('plg_search_jtevents', JPATH_ADMINISTRATOR);

		static $areas = array(
			'jticketing' => 'PLG_SEARCH_JTEVENTS'
		);

		return $areas;
	}

	/**
	 * Search Jticketing (Events).
	 * The SQL must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav.
	 *
	 * @param   string  $text      Target search string.
	 * @param   string  $phrase    Matching option (possible values: exact|any|all).  Default is "any".
	 * @param   string  $ordering  Ordering option (possible values: newest|oldest|popular|alpha|category).  Default is "newest".
	 * @param   mixed   $areas     An array if the search it to be restricted to areas or null to search all areas.
	 *
	 * @return  array  Search results.
	 *
	 * @since   1.6
	 */
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
	{
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$tag = JFactory::getLanguage()->getTag();

		require_once JPATH_ADMINISTRATOR . '/components/com_search/helpers/search.php';

		$searchText = $text;

		if (is_array($areas))
		{
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas())))
			{
				return array();
			}
		}

		$sContent = $this->params->get('search_events', 1);
		$limit = $this->params->def('search_limit', 20);

		$nullDate = $db->getNullDate();
		$date = JFactory::getDate();
		$now = $date->toSql();

		$text = trim($text);

		if ($text == '')
		{
			return array();
		}

		switch ($phrase)
		{
			case 'exact':
				$text = $db->quote('%' . $db->escape($text, true) . '%', false);
				$wheres2 = array();
				$wheres2[] = $db->quoteName('a.title') . ' LIKE ' . $text;
				$wheres2[] = $db->quoteName('a.long_description') . ' LIKE ' . $text;
				$where = '(' . implode(') OR (', $wheres2) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode(' ', $text);
				$wheres = array();

				foreach ($words as $word)
				{
					$word = $db->quote('%' . $db->escape($word, true) . '%', false);
					$wheres2 = array();
					$wheres2[] = $db->quoteName('a.title') . ' LIKE ' . $word;
					$wheres2[] = $db->quoteName('a.long_description') . ' LIKE ' . $word;
					$wheres[] = implode(' OR ', $wheres2);
				}

				$where = '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'a.created ASC';
				break;

			/*case 'popular':
				$order = 'a.hits DESC';
				break;*/

			case 'alpha':
				$order = 'a.title ASC';
				break;
			/*
			case 'category':
				$order = 'c.title ASC, a.title ASC';
				break;*/

			case 'newest':
			default:
				$order = 'a.created DESC';
				break;
		}

		$rows = array();
		$query = $db->getQuery(true);

		// Search Events.
		if ($sContent && $limit > 0)
		{
			$query->clear();

			$query->select('a.id, a.title, a.created')
				->select('a.long_description AS text')
				->select('c.title AS section')

				->from($db->quoteName('#__jticketing_events', 'a'))
				->join('INNER', $db->quoteName('#__categories', 'c') . 'ON' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
				->where('(' . $where . ') AND ' . $db->quoteName('a.state') . ' = 1 AND ' . $db->quoteName('c.published') . '= 1')
				->group(
				$db->quoteName('a.id'), $db->quoteName('a.title'), $db->quoteName('a.long_description'),
					$db->quoteName('a.created'), $db->quoteName('c.title'), $db->quoteName('c.id')
					)
				->order($order);

			$db->setQuery($query, 0, $limit);
			$list = $db->loadObjectList();

			$limit -= count($list);

			if (isset($list))
			{
				$jticketingMainHelper = new Jticketingmainhelper;
				$itemId = $jticketingMainHelper->getItemId('index.php?option=com_jticketing&view=events&layout=default');

				foreach ($list as $key => $item)
				{
					$link = 'index.php?option=com_jticketing&view=event&id=' . $item->id . '&Itemid=' . $itemId;
					$list[$key]->href = JRoute::_($link, false);
				}
			}

			$rows[] = $list;
		}

		$results = array();

		if (count($rows))
		{
			foreach ($rows as $row)
			{
				$new_row = array();

				foreach ($row as $article)
				{
					$article->browsernav = '';

					if (SearchHelper::checkNoHTML($article, $searchText, array('text', 'title', 'long_description')))
					{
						$new_row[] = $article;
					}
				}

				$results = array_merge($results, (array) $new_row);
			}
		}

		return $results;
	}
}
