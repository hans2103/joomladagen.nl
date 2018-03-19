<?php
/**
 * @package     CSVI
 * @subpackage  Core
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Core controller for CSVI.
 *
 * @package     CSVI
 * @subpackage  Core
 * @since       6.0
 */
class CsviControllerSefs extends JControllerLegacy
{
	/**
	 * Generate a SEF URL.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 */
	public function getsef()
	{
		$uri     = JUri::getInstance();
		$sefUrls = array();

		$urls = json_decode($this->input->getString('parseurl'));

		if (!is_array($urls))
		{
			$urls = (array) $urls;
		}

		foreach ($urls as $url)
		{
			$sefUrls[$url] = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port')) . JRoute::_($url, false);
		}

		echo new JResponseJson($sefUrls);

		JFactory::getApplication()->close();
	}
}
