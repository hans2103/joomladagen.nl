<?php
/**
 * @package    Pwtsitemap
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

JLoader::register('ContentHelperRoute',  JPATH_SITE . '/components/com_content/helpers/route.php');
JLoader::register('ContentAssociationsHelper', JPATH_ROOT . '/administrator/components/com_content/helpers/associations.php');

/**
 * PWT Sitemap Content Plugin
 *
 * @since  1.0.0
 */
class PlgPwtSitemapContent extends PwtSitemapPlugin
{
	/**
	 * Populate the PWT Sitemap Content plugin to use it a base class
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	function populateSitemapPlugin()
	{
		$this->component = 'com_content';
		$this->views     = ['category'];
	}

	/**
	 * Run for every menuitem passed
	 *
	 * @param   JMenuItem  $item          Menu items
	 * @param   string     $format        Sitemap format that is rendered
	 * @param   string     $sitemap_type  Type of sitemap that is generated
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function onPwtSitemapBuildSitemap($item, $format, $sitemap_type = 'default')
	{
		$sitemap_items = null;

		if ($this->checkDisplayParameters($item, $format))
		{
			// Save new items
			$sitemap_items = array();

			// Get all child categories
			$iMaxLevel  = (int) $item->params->get('maxLevel', -1);
			$categories = JCategories::getInstance('Content');
			$category   = $categories->get($item->query['id']);
			$children   = $category->getChildren(true);

			$aCatId   = [];
			$aCatId[] = $item->query['id'];

			array_walk($children, function ($a) use (&$aCatId, &$iMaxLevel)
			{
				if ($iMaxLevel === -1 || $a->level <= $iMaxLevel + 1)
				{
					$aCatId[] = $a->id;
				}
			});

			// Get articles of all categories
			$articles = $this->getArticles($aCatId, $item->language);

			foreach ($articles as $article)
			{
				$oParam = new Registry;
				$oParam->loadString($article->metadata);

				if (strpos($oParam->get('robots'), 'noindex') !== false)
				{
					continue;
				}

				$link     = ContentHelperRoute::getArticleRoute($article->id . ':' . $article->alias, $article->catid, $article->language);
				$modified = HTMLHelper::_('date', $article->modified, 'Y-m-d');

				$sitemap_items[] = $this->converToSitemapItem($article, $item, $link, $modified, $sitemap_type);
			}
		}

		return $sitemap_items;
	}

	/**
	 * Convert the given paramters to a PwtSitemapItem
	 *
	 * @param   $article       stdClass
	 * @param   $item          JMenuItem
	 * @param   $link          string
	 * @param   $modified      string
	 * @param   $sitemap_type  string
	 *
	 * @return  BasePwtSitemapItem
	 *
	 * @since   1.0.0
	 */
	private function converToSitemapItem($article, $item, $link, $modified, $sitemap_type)
	{
		switch ($sitemap_type)
		{
			case "multilanguage":
				$sitemapItem = new PwtMultilanguageSitemapItem($article->title, $link, $item->level + 1, $modified);
				$sitemapItem->associations = $this->getAssociatedArticles($article);

				return $sitemapItem;
				break;
			case "image":
				$sitemapItem = new PwtSitemapImageItem($article->title, $link, $item->level + 1, $modified);
				$sitemapItem->images = $this->getArticleImages($article);

				return $sitemapItem;
				break;
			default:
				return new PwtSitemapItem($article->title, $link, $item->level + 1, $modified);
		}
	}

	/**
	 * Get articles from the #__content table
	 *
	 * @param   array  $categories Category id
	 * @param   string $language   Language prefix
	 *
	 * @return  stdClass
	 *
	 * @since   1.0.0
	 */
	private function getArticles($categories, $language)
	{
		// Get database connection
		$query = $this->db->getQuery(true);

		// Build query
		$query
			->select(
				$this->db->qn(
					array(
						'id', 'title', 'alias', 'catid', 'language', 'modified', 'metadata', 'images'
					)
				)
			)
			->from($this->db->quoteName('#__content'))
			->where($this->db->quoteName('access') . ' = 1')
			->where($this->db->quoteName('catid') . ' IN (' . implode(', ', $categories) . ')')
			->where($this->db->quoteName('language') . ' IN (' . $this->db->quote($language) . ', ' . $this->db->quote("*") . ')')
			->where($this->db->quoteName('state') . ' = 1')
			->order($this->db->quoteName('title'));

		// Send query
		$this->db->setQuery($query);

		// Get results
		return $this->db->loadObjectList();
	}

	/**
	 * Get language associated articles
	 *
	 * @param   $article  stdClass  Article to find associations
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	private function getAssociatedArticles($article)
	{
		$helper = new ContentAssociationsHelper();
		$associations = $helper->getAssociations('article', $article->id);

		// Map associations to Article objects
		$associations = array_map(function($value) use ($helper) {
			return $helper->getItem('article', explode(':', $value->id)[0]);
		}, $associations);

		// Append links
		foreach ($associations as $language => $association)
		{
			$association->link = ContentHelperRoute::getArticleRoute($association->id . ':' . $association->alias, $association->catid, $association->language);
		}

		return $associations;
	}

	private function getArticleImages($article)
	{
		$images = [];
		$articleImages = json_decode($article->images);

		if (!empty($articleImages->image_intro))
		{
			$image = new stdClass();
			$image->url = PwtSitemapUrlHelper::getURL('/' . $articleImages->image_intro);
			$image->caption = (!empty($articleImages->image_intro_caption)) ? $articleImages->image_intro_caption : $articleImages->image_intro_alt;

			$images[] = $image;
		}

		if (!empty($articleImages->image_fulltext))
		{
			$image = new stdClass();
			$image->url = PwtSitemapUrlHelper::getURL('/' . $articleImages->image_fulltext);
			$image->caption = (!empty($articleImages->image_fulltext_caption)) ? $articleImages->image_fulltext_caption : $articleImages->image_fulltext_alt;

			$images[] = $image;
		}

		return $images;
	}
}