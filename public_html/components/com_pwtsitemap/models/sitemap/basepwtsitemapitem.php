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
 * PWT Sitemap Item Interface
 *
 * @since  1.0.0
 */
abstract class BasePwtSitemapItem
{
	/**
	 * Sitemap item title
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	public $title;

	/**
	 * Sitemap item link
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	public $link;

	/**
	 * Sitemap item level
	 *
	 * @var    int
	 * @since  1.0.0
	 */
	public $level;

	/**
	 * Sitemap item modified date
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	public $modified;

	/**
	 * Context of the sitemap item (ex: com_content.article.1)
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	public $context;

	/**
	 * Constructor.
	 *
	 * @param  string  $title     Title
	 * @param  string  $link      URL
	 * @param  int     $level     Level
	 * @param  mixed   $modified  Modification date
	 *
	 * @since  1.0.0
	 */
	public function __construct($title, $link, $level, $modified = null)
	{
		$this->title = $title;
		$this->link  = PwtSitemapUrlHelper::getURL($link);
		$this->level = $level;

		if (!empty($modified) && $modified != JDatabaseDriver::getInstance()->getNullDate() && $modified != '1001-01-01 00:00')
		{
			$this->modified = JHtml::_('date', $modified, 'Y-m-d');
		}
	}

	/**
	 * Render this item for a XML sitemap
	 *
	 * @return  string  Rendered sitemap item
	 *
	 * @since   1.0.0
	 */
	abstract function renderXml();
}