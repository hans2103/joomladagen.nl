<?php
/**
 * @package    Pwtsitemap
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2018 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

/**
 * URLHelper class
 *
 * @since  1.0.0
 */
class PwtSitemapUrlHelper
{
	/**
	 * Static method to route a url to a SEF Url. It also decides if the url should  me with https or not
	 *
	 * @param   string $url Url to route
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function getURL($url)
	{
		return substr(JUri::base(), 0, -1) . JRoute::_($url, true);
	}
}
