<?php
/**
 * @package     Perfect_Sitemap
 * @subpackage  com_perfectsitemap
 *
 * @copyright   Copyright (C) 2017 Perfect Web Team. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since       1.4.2
 */

defined('_JEXEC') or die;

/**
 * URLHelper class
 *
 * @since  1.4.2
 */
class PerfectSitemapUrlHelper
{
	/**
	 * Static method to route a url to a SEF Url. It also decides if the url should  me with https or not
	 *
	 * @param   string  $url  Url to route
	 *
	 * @return  string
	 *
	 * @since  1.4.2
	 */
	public static function getURL($url)
	{
		return substr(JUri::base(), 0, -1) . JRoute::_($url, true);
	}
}
