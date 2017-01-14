<?php
/**
 * @package     Perfect_Sitemap
 * @subpackage  com_perfectsitemap
 *
 * @copyright   Copyright (C) 2017 Perfect Web Team. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Perfect Sitemap Component Model
 *
 * @since  1.0.0
 */
class PerfectSitemapModelSitemap extends JModelItem
{
	/**
	 * Method to get an object.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function getItems()
	{
		$app           = JFactory::getApplication();
		$sitemap_items = array();
		$skipped_items = array();

		// Get menu items
		$menuitems = $app->getMenu()->getMenu();

		// Filter menu items and add articles
		foreach ($menuitems as $menuitem)
		{
		    // Filter menu items
		    if ($this->filter($menuitem))
            {
                $skipped_items[] = $menuitem->id;
                continue;
            }

			// Filter menu items we don't want to show for the HTML sitemap and items where the parent is skipped
			if ($menuitem->params->get('addtohtmlsitemap', 1) == false || in_array($menuitem->parent_id, $skipped_items))
			{
				$skipped_items[] = $menuitem->id;
				continue;
			}

			// Add item to sitemap_items
			$menuitem->link             = URLHelper::getURL('index.php?Itemid=' . $menuitem->id); //JRoute::_('index.php?Itemid=' . $menuitem->id, true, -1);
			$menuitem->addtohtmlsitemap = $menuitem->params->get('addtohtmlsitemap', 1);
			$menuitem->addtoxmlsitemap  = $menuitem->params->get('addtoxmlsitemap', 1);
			$sitemap_items[]            = $menuitem;

			// Trigger plugin
			$dispatcher = JEventDispatcher::getInstance();
			$results    = $dispatcher->trigger('onPerfectSitemapBuildSitemap', array($menuitem));

			foreach ($results as $result)
			{
				$sitemap_items = array_merge($sitemap_items, $result);
			}
		}

		// Filters items from the plugin event we don't want to show. We don't show when explicitly set
		$sitemap_items = array_filter($sitemap_items, function ($item) use ($app) {
			if ($app->input->getCmd('format', 'html') === 'html')
			{
				return !isset($item->addtohtmlsitemap) || $item->addtohtmlsitemap != 0;
			}

			return !isset($item->addtoxmlsitemap) || $item->addtoxmlsitemap != 0;
		});

		return $sitemap_items;
	}

    /**
     * Filter a menu item on content type, language and access
     *
     * @param $menuitem
     * @return bool
     */
	protected function filter($menuitem)
    {
        $aFilter                = array('separator', 'heading', 'url', 'alias');
        $lang                   = JFactory::getLanguage();
        $authorizedAccessLevels = JFactory::getUser()->getAuthorisedViewLevels();

        return (in_array($menuitem->type, $aFilter)
            || ($menuitem->language != $lang->getTag() and $menuitem->language != '*')
            || !in_array($menuitem->access, $authorizedAccessLevels)
        );
    }
}
