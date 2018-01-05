<?php
/**
 * @package    Pwtsitemap
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
JLoader::register('ContentHelperQuery', JPATH_SITE . '/components/com_content/helpers/query.php');
JLoader::register('ContentAssociationsHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/associations.php');
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');

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
	 * @param   JMenuItem $item         Menu items
	 * @param   string    $format       Sitemap format that is rendered
	 * @param   string    $sitemap_type Type of sitemap that is generated
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
			$children   = ($category) ? $category->getChildren(true) : null;

			$aCatId   = [];
			$aCatId[] = $item->query['id'];

			if ($children) array_walk($children, function ($a) use (&$aCatId, &$iMaxLevel) {
				if ($iMaxLevel === -1 || $a->level <= $iMaxLevel + 1)
				{
					$aCatId[] = $a->id;
				}
			});

			// Get articles of all categories
			$articles = $this->getArticles($aCatId, $item->language, $item->params);

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
				$sitemapItem               = new PwtMultilanguageSitemapItem($article->title, $link, $item->level + 1, $modified);
				$sitemapItem->associations = $this->getAssociatedArticles($article);

				return $sitemapItem;
				break;
			case "image":
				$sitemapItem         = new PwtSitemapImageItem($article->title, $link, $item->level + 1, $modified);
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
	private function getArticles($categories, $language, $params)
	{
		// Get ordering from menu
		$articleOrderby   = $params->get('orderby_sec', 'rdate');
		$articleOrderDate = $params->get('order_date');
		$secondary        = ContentHelperQuery::orderbySecondary($articleOrderby, $articleOrderDate);

		// Get an instance of the generic articles model
		$articles = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		$articles->setState('params', $params);
		$articles->setState('filter.published', 1);
		$articles->setState('filter.access', 1);
		$articles->setState('filter.language', $language);
		$articles->setState('filter.category_id', $categories);
		$articles->setState('list.start', 0);
		$articles->setState('list.limit', 0);
		$articles->setState('list.ordering', $secondary . ', a.created DESC');
		$articles->setState('list.direction', '');

		// Get results
		return $articles->getItems();
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
		$helper       = new ContentAssociationsHelper();
		$associations = $helper->getAssociations('article', $article->id);

		// Map associations to Article objects
		$associations = array_map(function ($value) use ($helper) {
			return $helper->getItem('article', explode(':', $value->id)[0]);
		}, $associations);

		// Append links
		foreach ($associations as $language => $association)
		{
			$association->link = ContentHelperRoute::getArticleRoute($association->id . ':' . $association->alias, $association->catid, $association->language);
		}

		return $associations;
	}

	/**
	 * Get the images of an article
	 *
	 * @param $article stdClass  Article
	 *
	 * @return array
	 *
	 * @since version
	 */
	private function getArticleImages($article)
	{
		$images        = [];
		$articleImages = json_decode($article->images);

		if (!empty($articleImages->image_intro))
		{
			$image          = new stdClass();
			$image->url     = PwtSitemapUrlHelper::getURL('/' . $articleImages->image_intro);
			$image->caption = (!empty($articleImages->image_intro_caption)) ? $articleImages->image_intro_caption : $articleImages->image_intro_alt;

			$images[] = $image;
		}

		if (!empty($articleImages->image_fulltext))
		{
			$image          = new stdClass();
			$image->url     = PwtSitemapUrlHelper::getURL('/' . $articleImages->image_fulltext);
			$image->caption = (!empty($articleImages->image_fulltext_caption)) ? $articleImages->image_fulltext_caption : $articleImages->image_fulltext_alt;

			$images[] = $image;
		}

		return $images;
	}
}