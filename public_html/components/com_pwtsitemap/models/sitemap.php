<?php
/**
 * @package    Pwtsitemap
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

/**
 * PWT Sitemap Component Model
 *
 * @since  1.0.0
 */
class PwtSitemapModelSitemap extends JModelItem
{
	/**
	 * JApplication instance
	 *
	 * @var    JApplicationCms
	 * @since  1.0.0
	 */
	private $app;

	/**
	 * Holds the dispatcher object
	 *
	 * @var    JEventDispatcher
	 * @since  1.0.0
	 */
	private $jDispatcher;

	/**
	 * PWT sitemap object instance
	 *
	 * @var    PwtSitemap
	 * @since  1.0.0
	 */
	protected $sitemap;

	/**
	 * Display format
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $format;

	/**
	 * Type of the sitemap that is generated
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type;

	/**
	 * Constructor
	 *
	 * @param  array  $config
	 *
	 * @since  1.0.0
	 */
	public function __construct(array $config = array())
	{
		$this->app         = JFactory::getApplication();
		$this->jDispatcher = JEventDispatcher::getInstance();

		$this->format      = $this->app->input->getCmd('format', 'html');
		$this->type        = 'default';
		$this->sitemap     = new PwtSitemap($this->format);

		parent::__construct($config);
	}

	public function populateState()
	{
		parent::populateState();

		$params = $this->app->getParams();
		$this->setState('params', $params);
	}

	/**
	 * Build the sitemap
	 *
	 * @return  PwtSitemap
	 *
	 * @since   1.0.0
	 */
	public function getSitemap()
	{
		$skipped_items = array();

		// Get menu items
		$menuitems = $this->app->getMenu()->getMenu();

		// allow for plugins to change the menu items
		$this->jDispatcher->trigger('onPwtSitemapBeforeBuild', array(&$menuitems, $this->type));

		// Filter menu items and add articles
		foreach ($menuitems as $menuitem)
		{
			// Filter menu items
			if ($this->filter($menuitem))
			{
				$skipped_items[] = $menuitem->id;

				continue;
			}

			// Filter menu items we don't want to show for the display format and items where the parent is skipped
			if ($menuitem->params->get('addto' . $this->format . 'sitemap', 1) == false || in_array($menuitem->parent_id, $skipped_items))
			{
				$skipped_items[] = $menuitem->id;
				continue;
			}

			// Convert menu item to a PwtSitemap item
			$menuitem->link             = $menuitem->link . '&Itemid=' . $menuitem->id;
			$menuitem->addtohtmlsitemap = $menuitem->params->get('addtohtmlsitemap', 1);
			$menuitem->addtoxmlsitemap  = $menuitem->params->get('addtoxmlsitemap', 1);

			// Add item to sitemap
			$this->AddMenuItemToSitemap($menuitem);

			// Trigger plugin event
			$results = $this->jDispatcher->trigger('onPwtSitemapBuildSitemap', array($menuitem, $this->format, $this->type));

			foreach ($results as $sitemapItems)
			{
				if (!empty($sitemapItems))
				{
					$this->addItemsToSitemap($sitemapItems);
				}
			}
		}

		// Allow for plugins to change the entire sitemap along with what was processed
		$this->jDispatcher->trigger('onPwtSitemapAfterBuild', array(&$this->sitemap->sitemapItems, $menuitems, $this->type));

		return $this->sitemap;
	}

	/**
	 * Add a menu item to the sitemap
	 *
	 * @param   $menuitem  JMenuItem  Menu item to add to the sitemap
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function AddMenuItemToSitemap($menuitem)
	{
		$this->sitemap->addItem(new PwtSitemapItem($menuitem->title, $menuitem->link, $menuitem->level));
	}

	/**
	 * Add a array of PwtSitemapItems to the sitemap (used for the result of plugin triggers)
	 *
	 * @param   $items  array  Menu item to add to the sitemap
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function AddItemsToSitemap($items)
	{
		$this->sitemap->addItem($items);
	}

	/**
	 * Filter a menu item on content type, language and access
	 *
	 * @param   $menuitem  JMenuItem  Menu item to filter
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	protected function filter($menuitem)
	{
		$lang                   = JFactory::getLanguage();
		$authorizedAccessLevels = JFactory::getUser()->getAuthorisedViewLevels();

		return (!PwtSitemapHelper::filterMenuType($menuitem->type)
			|| ($menuitem->language != $lang->getTag() and $menuitem->language != '*')
			|| !in_array($menuitem->access, $authorizedAccessLevels)
		);
	}
}
