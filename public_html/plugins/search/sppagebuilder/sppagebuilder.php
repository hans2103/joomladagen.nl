<?php

/**
 * @package     JoomShaper Plugin
 * @subpackage  Search.sppagebuilder
 *
 * @copyright   Copyright (C) 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
* Sppagebuilder search plugins
*
* @since 1.0.4
*/
class PlgSearchSppagebuilder extends JPlugin
{

	protected $autoloadLanguage = true;


	/**
	 * Determine areas searchable by this plugin.
	 *
	 * @return  array  An array of search areas.
	 *
	 */

	public function onContentSearchAreas()
	{
		static $areas = array(
			'sppagebuilder' => 'SP_PAGEBUILDER_SEARCH_AREAS'
		);

		return $areas;
	}

	/**
	 * Search content (SP Pagebuilder).
	 *
	 * The SQL must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav.
	 *
	 * @param   string  $text      Target search string.
	 * @param   string  $phrase    Matching option (possible values: exact|any|all).  Default is "any".
	 * @param   string  $ordering  Ordering option (possible values: newest|oldest|popular|alpha|category).  Default is "newest".
	 * @param   mixed   $areas     An array if the search is to be restricted to areas or null to search all areas.
	 *
	 * @return  array  Search results.
	 *
	 */

	public function onContentSearch( $text, $phrase = '', $ordering = '', $areas = null )
	{
		$db 	= JFactory::getDbo();
		$limit 	= $this->params->def('search_limit', 50);

		if (is_array($areas)) {
			if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
				return array();
			}
		}

		$text = trim($text);

		if ($text == '') {
			return array();
		}

		switch ($phrase)
		{
			case 'exact':
			case 'all':
			case 'any':
			default:
				$text = $db->quote('%' . $db->escape($text, true) . '%', false);
				$wheres1 = array();
				$wheres1[] = 'title LIKE ' . $text;
				$wheres1[] = 'text LIKE ' . $text;
				$where = '((' . implode(') OR (', $wheres1) . ')) AND extension = "com_sppagebuilder" AND published = 1';
				break;
		}

		switch ($ordering)
		{
			case 'oldest':
				$order = 'created_time ASC';
				break;

			/*
			case 'popular':
				$order = 'a.hits DESC';
				break;
			*/

			case 'alpha':
				$order = 'title ASC';
				break;

			case 'newest':
			case 'category':
			case 'popular':
			default:
				$order = 'created_on DESC';
				break;
		}

		$query = $db->getQuery(true);

		if ( $limit > 0 ) {
			$query->clear();

			$query->select('id, title AS title, created_on as created, \'\' AS browsernav, \'Page\' AS section, language');
			$query->from('#__sppagebuilder');
			$query->where($where);
			$query->order($order);

			$db->setQuery($query, 0, $limit);
		}

		try
		{
			$list = $db->loadObjectList();

			if (isset($list))
			{
				foreach ($list as $key => $item)
				{
					$list[$key]->href = JRoute::_('index.php?option=com_sppagebuilder&view=page&id='.$item->id.((($item->language != '*'))? '&lang='.$item->language:'') . $this->getItemid($item->id));
				}
			}
		}
		catch (RuntimeException $e)
		{
			$list = array();
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
		}

		return $list;
	}

	private function getItemid($id = null) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id')));
		$query->from($db->quoteName('#__menu'));
		$query->where($db->quoteName('link') . ' LIKE '. $db->quote('%view=page&id='. $id .'%'));
		$query->where($db->quoteName('published') . ' = '. $db->quote('1'));
		$db->setQuery($query);
		$result = $db->loadResult();

		if(count($result)) {
			return '&Itemid=' . $result;
		}

		return;
	}
}
