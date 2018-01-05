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
 * PWT Sitemap helper
 *
 * @since  1.0.0
 */
abstract class PwtSitemapHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_PWTSITEMAP_TITLE_DASHBOARD'),
			'index.php?option=com_pwtsitemap&view=dashboard',
			$vName == 'dashboard'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_PWTSITEMAP_TITLE_ITEMS'),
			'index.php?option=com_pwtsitemap&view=items',
			$vName == 'items'
		);
	}

	/**
	 * Filter a menu type
	 *
	 * @param   string  $type  Menutype
	 *
	 * @return  bool    Returns true if the menutype is not filtered, false otherwise
	 *
	 * @since   1.0.0
	 */
	public static function filterMenuType($type)
	{
		$aFilter = array('separator', 'url', 'alias');

		if (in_array($type, $aFilter))
		{
			return false;
		}

		return true;
	}

	/**
	 * Get the paraemter of a menu item
	 *
	 * @param   int  $itemId  Menu item id
	 *
	 * @return  stdClass
	 *
	 * @since   1.0.0
	 */
	public static function GetMenuItemParameters($itemId)
	{
		$db = JFactory::getDbo();
		$q  = $db
			->getQuery(true)
			->select($db->qn('params'))
			->from($db->qn('#__menu'))
			->where($db->qn('id') . '=' . (int) $itemId);

		return json_decode($db->setQuery($q)->loadResult());
	}

	/**
	 * Save a menu item parameter
	 *
	 * @param   int     $itemId     Menu item id
	 * @param   string  $parameter  Parameter to change
	 * @param   mixed   $value      Value of parameter
	 *
	 * @return  bool  True on success, false otherwise
	 *
	 * @since   1.0.0
	 */
	public static function SaveMenuItemParameter($itemId, $parameter, $value)
	{
		// Get current parameters and set new
		$params = self::GetMenuItemParameters($itemId);
		$params->{$parameter} = $value;

		// Save parameters
		$params = json_encode($params);

		$db = JFactory::getDbo();
		$q  = $db
			->getQuery(true)
			->clear()
			->update($db->qn('#__menu'))
			->set($db->qn('params') . '=' . $db->q($params))
			->where($db->qn('id') . '=' . (int) $itemId);
		$db->setQuery($q)->execute();

		return true;
	}
}
