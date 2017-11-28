<?php
/**
 * @package    Pwtseo
 *
 * @author     Perfect Web Team <extensions@perfectwebteam.com>
 * @copyright  Copyright (C) 2016 - 2017 Perfect Web Team. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://extensions.perfectwebteam.com
 */

defined('_JEXEC') or die;

/**
 * Load the PWT SEO installer.
 *
 * @since    1.0
 */
class Pkg_PwtSEOInstallerScript
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
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('COM_PWTSEO_PHP_VERSION_ERROR', phpversion()), 'error');

			return false;
		}

		// Check if the Joomla! version is correct
		$version = new JVersion;

		if (version_compare($version->getShortVersion(), '3.8', '<') === true)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('COM_PWTSEO_JOOMLA_VERSION_ERROR', $version->getShortVersion()), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Method to run after an install/update/uninstall method
	 * $parent is the class calling this method
	 * $type is the type of change (install, update or discover_install)
	 *
	 * @param   string $type   The type of change (install, update or discover_install).
	 * @param   object $parent The class calling this method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function postflight($type, $parent)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . ' = 1')
			->where(
				array(
					$db->quoteName('type') . ' = ' . $db->quote('plugin'),
					$db->quoteName('element') . ' = ' . $db->quote('pwtseo'),
					$db->quoteName('folder') . ' = ' . $db->quote('system')
				)
			);

		$db->setQuery($query)->execute();
	}
}
