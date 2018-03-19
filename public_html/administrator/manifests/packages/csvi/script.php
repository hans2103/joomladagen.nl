<?php
/**
 * @package     CSVI
 * @subpackage  Install
 *
 * @author      Roland Dalmulder <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - @year@ RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        http://www.csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Script to run on installation of CSVI package.
 *
 * @package     CSVI
 * @subpackage  Install
 * @since       6.0
 */
class Pkg_CsviInstallerScript
{
	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 *
	 * @since 7.0
	 */
	protected $minimumPHPVersion = '5.4';

	/**
	 * The minimum Joomla version required to install this extension
	 *
	 * @var   string
	 *
	 * @since 7.0
	 */
	protected $minimumJoomlaVersion = '3.6.5';

	/**
	 * Method to run after an install/update/uninstall method.
	 *
	 * @param   string  $type    The type of installation being done
	 * @param   object  $parent  The parent calling class
	 *
	 * @return  bool  True on success | False if a version is not supported.
	 *
	 * @since   7.0
	 */
	public function preflight($type, $parent)
	{
		if (defined('PHP_VERSION'))
		{
			$version = PHP_VERSION;
		}
		elseif (function_exists('phpversion'))
		{
			$version = phpversion();
		}
		else
		{
			// No idea, we assume the PHP version is supported
			$version = '5.4';
		}

		if (!version_compare($version, $this->minimumPHPVersion, 'ge'))
		{
			$msg = "<p>You need PHP $this->minimumPHPVersion or later to install this component</p>";

			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		// Check the minimum Joomla! version
		if (!empty($this->minimumJoomlaVersion) && !version_compare(JVERSION, $this->minimumJoomlaVersion, 'ge'))
		{
			$msg = "<p>You need Joomla! $this->minimumJoomlaVersion or later to install this component</p>";

			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Method to run after an install/update/uninstall method
	 *
	 * @param   string  $type    The type of installation being done
	 * @param   object  $parent  The parent calling class
	 *
	 * @return void
	 *
	 * @since   7.0
	 *
	 * @throws  RuntimeException
	 */
	public function postflight($type, $parent)
	{
		// All Joomla loaded, set our exception handler
		require_once JPATH_BASE . '/components/com_csvi/rantai/error/exception.php';

		// Load the default classes
		require_once JPATH_ADMINISTRATOR . '/components/com_csvi/controllers/default.php';
		require_once JPATH_ADMINISTRATOR . '/components/com_csvi/models/default.php';

		// Setup the autoloader
		JLoader::registerPrefix('Csvi', JPATH_ADMINISTRATOR . '/components/com_csvi');

		// Load language files
		$jlang = JFactory::getLanguage();
		$jlang->load('com_csvi', JPATH_ADMINISTRATOR . '/components/com_csvi/', 'en-GB', true);
		$jlang->load('com_csvi', JPATH_ADMINISTRATOR . '/components/com_csvi/', $jlang->getDefault(), true);
		$jlang->load('com_csvi', JPATH_ADMINISTRATOR . '/components/com_csvi/', null, true);

		// Load the tasks
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_csvi/models');
		$tasksModel = JModelLegacy::getInstance('Task', 'CsviModel', array('ignore_request' => true));

		try
		{
			$tasksModel->reload();
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		// Clean the update sites if needed
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('update_site_id'))
			->from($db->quoteName('#__update_sites'))
			->where($db->quoteName('name') . ' = ' . $db->quote('CSVI Pro'))
			->where($db->quoteName('type') . ' = ' . $db->quote('extension'));
		$db->setQuery($query);
		$entry = $db->loadResult();

		if (!$entry)
		{
			$query->clear()
				->insert($db->quoteName('#__update_sites'))
				->columns(
					$db->quoteName(
						array(
							'name',
							'type',
							'location',
							'enabled',
							'last_check_timestamp'
						)
					)
				)
				->values(
					$db->quote('CSVI Pro') . ', '
					. $db->quote('extension') . ', '
					. $db->quote('https://csvimproved.com/updates/csvipro.xml') . ', '
					. '1, '
					. '0'
				);
			$db->setQuery($query)->execute();
		}
	}
}
