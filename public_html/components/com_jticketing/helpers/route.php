<?php
/**
 * @version    SVN: <svn_id>
 * @package    JTicketing
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');

/**
 * JTRouteHelper
 *
 * @since  1.0
 */
class JTRouteHelper
{
	/**
	 * Wrapper to JRoute to handle itemid We need to try and capture the correct itemid for different view
	 *
	 * @param   string   $url    Absolute or Relative URI to Joomla resource.
	 * @param   boolean  $xhtml  Replace & by &amp; for XML compliance.
	 * @param   integer  $ssl    Secure state for the resolved URI.
	 *
	 * @return  url with itemif
	 *
	 * @since  1.0
	 */
	public function JTRoute($url, $xhtml = true, $ssl = null)
	{
		static $eventsItemid = array();

		$mainframe = JFactory::getApplication();
		$jinput = $mainframe->input;

		if (empty($eventsItemid[$url]))
		{
			require_once JPATH_SITE . "/components/com_jticketing/helpers/main.php";
			$jticketingMainHelper = new jticketingmainhelper;
			$eventsItemid[$url] = $jticketingMainHelper->getItemId($url);
		}

		$pos = strpos($url, '#');

		if ($pos === false)
		{
			if (isset($eventsItemid[$url]))
			{
				if (strpos($url, 'Itemid=') === false && strpos($url, 'com_jticketing') !== false)
				{
					$url .= '&Itemid=' . $eventsItemid[$url];
				}
			}
		}
		else
		{
			if (isset($eventsItemid[$url]))
			{
				$url = str_ireplace('#', '&Itemid=' . $eventsItemid[$view] . '#', $url);
			}
		}

		$routedUrl = JRoute::_($url, $xhtml, $ssl);
		$routedUrl = htmlspecialchars_decode($routedUrl);

		return $routedUrl;
	}
}
