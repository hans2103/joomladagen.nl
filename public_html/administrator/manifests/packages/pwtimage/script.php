<?php
/**
 * @package    Pwtimage
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

defined('_JEXEC') or die;

/**
 * Load the PWT Image installer.
 *
 * @since    1.0
 */
class Pkg_PwtimageInstallerScript
{
	/**
	 * Method to run before an install/update/uninstall method
	 *
	 * @param   string  $type    The type of change (install, update or discover_install).
	 * @param   object  $parent  The class calling this method.
	 *
	 * @return  bool  True on success | False on failure
	 *
	 * @since   1.0
	 *
	 * @throws  Exception
	 */
	public function preflight($type, $parent)
	{

		// Check if the PHP version is correct
		if (version_compare(phpversion(), '5.6', '<') === true)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::sprintf('COM_PWTIMAGE_PHP_VERSION_ERROR', phpversion()), 'error');

			return false;
		}

		// Check if the Joomla! version is correct
		$version = new Version;

		if (version_compare($version->getShortVersion(), '3.8', '<') === true)
		{
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::sprintf('COM_PWTIMAGE_JOOMLA_VERSION_ERROR', $version->getShortVersion()), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Run after installing.
	 *
	 * @param   object  $parent  The calling class.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   1.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 */
	public function postflight($parent)
	{
		$app = Factory::getApplication();
		$db  = Factory::getDbo();

		// Enable the plugins
		$plugins = array('editors-xtd', 'system');

		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . ' =  1');

		try
		{
			foreach ($plugins as $index => $plugin)
			{
				$query->clear('where')
					->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
					->where($db->quoteName('element') . ' = ' . $db->quote('pwtimage'))
					->where($db->quoteName('folder') . ' = ' . $db->quote($plugin));

				$db->setQuery($query)->execute();
			}

			// Unpublish the Joomla image plugin
			$query->clear('set')
				->clear('where')
				->set($db->quoteName('enabled') . ' =  0')
				->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
				->where($db->quoteName('element') . ' = ' . $db->quote('image'))
				->where($db->quoteName('folder') . ' = ' . $db->quote('editors-xtd'));
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::sprintf('PKG_PWTIMAGE_PLUGINS_NOT_ENABLED', $e->getMessage()), 'error');

			return false;
		}

		$app->enqueueMessage(Text::_('PKG_PWTIMAGE_PLUGINS_ENABLED'));

		return true;
	}

	/**
	 * Run after installing.
	 *
	 * @param   object  $parent  The calling class.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   1.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 */
	public function uninstall($parent)
	{
		$db  = Factory::getDbo();

		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . ' =  1')
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('element') . ' = ' . $db->quote('image'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('editors-xtd'));
			$db->setQuery($query)->execute();

		return true;
	}
}
