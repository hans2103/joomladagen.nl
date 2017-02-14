<?php
/**
 * @package     Perfect_Sitemap
 * @subpackage  plg_perfectsitemap_content
 *
 * @copyright   Copyright (C) 2017 Perfect Web Team. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Perfect Sitemap Content Plugin
 *
 * @since  1.0.0
 */
class PlgPerfectSitemapContent extends JPlugin
{
	/**
	 * Automatic load plugin language files
	 *
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	/**
	 * Joomla Application instance
	 *
	 * @var  JApplicationSite
	 */
	public $app;

	/**
	 * Adds additional fields to the user editing form
	 *
	 * @param   JForm $form The form to be altered.
	 * @param   mixed $data The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		// Make sure form element is a JForm object
		if (!($form instanceof JForm))
		{
			$this->_subject->setError("JERROR_NOT_A_FORM");

			return false;
		}

		// Make sure we are on the edit menu item page
		if (!in_array($form->getName(), array('com_menus.item')))
		{
			return true;
		}

		// Load selected option and view if selected
		if (isset($data['request']['view']) && isset($data['request']['option']))
		{
			$view   = $data['request']['view'];
			$option = $data['request']['option'];

			if ($option == 'com_content' && $view == 'category')
			{
				JForm::addFormPath(__DIR__ . '/forms');

				$form->loadFile('content');
			}
		}

		return true;
	}

	/**
	 * Run for every menuitem passed by Perfect Sitemap
	 *
	 * @param   StdClass $item   Menu items
	 * @param   string   $format Sitemap format that is rendered
	 *
	 * @return  array
	 *
	 * @since   1.4.0
	 */
	public function onPerfectSitemapBuildSitemap($item, $format)
	{
		$sitemap_items = null;

		if (isset($item->query['option']) && isset($item->query['view']) && $this->checkDisplayFormat($item, $format))
		{
			if ($item->query['option'] == 'com_content' && $item->query['view'] == 'category')
			{
				// Save new items
				$sitemap_items = array();
				$articles      = $this->getArticles($item->query['id'], $item->language);

				// Add article to sitemap_items
				foreach ($articles as $article)
				{
					$item_title    = $article->title;
					$item_link     = 'index.php?option=com_content&view=article&id=' . $article->id . ':' . $article->alias . '&catid=' . $article->catid . '&Itemid=' . $item->id;
					$item_level    = $item->level + 1;
					$item_modified = JHtml::_("date", $article->modified, "Y-m-d");

					$sitemap_items[] = new PerfectSitemapItem($item_title, $item_link, $item_level, $item_modified);
				}
			}
		}

		return $sitemap_items;
	}

	/**
	 * Get articls from the #__content table
	 *
	 * @param  int    $catid    Category id
	 * @param  string $language Language prefix
	 *
	 * @return stdClass
	 *
	 * @since  2.0.0
	 */
	private function getArticles($catid, $language)
	{
		// Get database connection
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Build query
		$query->select($db->quoteName(array('id', 'title', 'alias', 'catid', 'language', 'modified')));
		$query->from($db->quoteName('#__content'));
		$query->where($db->quoteName('access') . ' = ' . $db->quote('1'));
		$query->where($db->quoteName('catid') . ' = ' . $db->quote($catid));
		$query->where($db->quoteName('language') . ' = ' . $db->quote($language));
		$query->where($db->quoteName('state') . ' = 1');

		// Send query
		$db->setQuery($query);

		// Get results
		return $db->loadObjectList();
	}

	/**
	 * Check the display format against the parameters and determ if we can skip the item or not
	 *
	 * @param  StdClass $item Sitemap item
	 *
	 * @return bool
	 *
	 * @since  1.4.5
	 */
	private function checkDisplayFormat($item, $format)
	{
		if ($format == "html" && $item->params->get('addarticletohtmlsitemap', 1))
		{
			return true;
		}
		elseif ($format == "xml" && $item->params->get('addarticletoxmlsitemap', 1))
		{
			return true;
		}

		return false;
	}
}
