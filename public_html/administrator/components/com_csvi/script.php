<?php
/**
 * @package     CSVI
 * @subpackage  Install
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Script to run on installation of CSVI.
 *
 * @package     CSVI
 * @subpackage  Install
 * @since       6.0
 */
class Com_CsviInstallerScript
{
	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 *
	 * @since 6.0
	 */
	protected $minimumPHPVersion = '5.4';

	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 *
	 * @since 6.0
	 */
	protected $minimumJoomlaVersion = '3.6.5';

	/**
	 * The version of the extension installed
	 *
	 * @var   string
	 *
	 * @since  7.4.1
	 */
	protected $extensionVersion;

	/**
	 * Method to install the component
	 *
	 * @param   string  $type    Installation type (install, update, discover_install)
	 * @param   object  $parent  The parent calling class
	 *
	 * @return  boolean  True to let the installation proceed, false to halt the installation
	 *
	 * @since   6.0
	 *
	 * @throws  \Exception
	 */
	public function preflight($type, $parent)
	{
		if (!defined('CSVIPATH_DEBUG'))
		{
			define('CSVIPATH_DEBUG', JPath::clean(JFactory::getConfig()->get('log_path'), '/'));
		}

		// Get the extension version number
		$this->extensionVersion = $parent->get('manifest')->version;

		// Clean up files and folders if any
		$this->cleanFiles();

		/** @var JDatabaseDriver $db */
		$db     = JFactory::getDbo();
		$tables = $db->getTableList();
		$table  = $db->getPrefix() . 'csvi_settings';
		$app    = JFactory::getApplication();
		$query  = $db->getQuery(true);

		// Move the settings from the old csvi_settings to the Joomla Global Settings
		if (in_array($table, $tables))
		{
			try
			{
				$query->clear()
					->select($db->quoteName('params'))
					->from($db->quoteName($table))
					->where($db->quoteName('csvi_setting_id') . ' = 1');
				$db->setQuery($query);
				$csvisettings = $db->loadResult();
			}
			catch (Exception $e)
			{
				$csvisettings = false;
			}

			if ($csvisettings === false)
			{
				// The csvi_settings table exists but not the csvi_setting_id column, let's try with the old ID column
				try
				{
					$query->clear()
						->select($db->quoteName('params'))
						->from($db->quoteName($table))
						->where($db->quoteName('id') . ' = 1');
					$db->setQuery($query);
					$csvisettings = $db->loadResult();
				}
				catch (Exception $e)
				{
					$app->enqueueMessage(JText::_('COM_CSVI_INSTALL_CORRUPT_TABLE'));

					return false;
				}
			}

			// Make sure the user has any saved settings
			if ($csvisettings !== '')
			{
				$csviregistry = json_decode($csvisettings, true);

				$query->clear()
					->select($db->quoteName('params'))
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('element') . ' = ' . $db->quote('com_csvi'))
					->where($db->quoteName('type') . ' = ' . $db->quote('component'));
				$db->setQuery($query);
				$extsettings = $db->loadResult();

				if (!$extsettings)
				{
					$extsettings = array();
				}

				$extregistry = json_decode($extsettings, true);
				$newparams   = array_merge($csviregistry, $extregistry);
				$newparams   = new Registry($newparams);

				$query->clear()
					->update($db->quoteName('#__extensions'))
					->set($db->quoteName('params') . ' = ' . $db->quote($newparams))
					->where($db->quoteName('element') . ' = ' . $db->quote('com_csvi'))
					->where($db->quoteName('type') . ' = ' . $db->quote('component'));
				$db->setQuery($query)->execute();
			}
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
	 * @since   6.0
	 *
	 * @throws  RuntimeException
	 */
	public function postflight($type, $parent)
	{
		// Check the database structure is OK
		$this->checkDatabase();

		// Convert any pre version 6 templates if needed
		$this->convertTemplates();
	}

	/**
	 * Convert old templates to the new CSVI 6 format.
	 *
	 * @return  void.
	 *
	 * @since   6.0
	 *
	 * @throws  RuntimeException
	 */
	private function convertTemplates()
	{
		/** @var JDatabaseDriver $db */
		$db = JFactory::getDbo();

		// Load all the existing templates
		$query = $db->getQuery(true)
			->select(
				array(
					$db->quoteName('csvi_template_id'),
					$db->quoteName('settings'),
				)
			)
			->from($db->quoteName('#__csvi_templates'));
		$db->setQuery($query);
		$templates = $db->loadObjectList('csvi_template_id');

		foreach ($templates as $csvi_template_id => $template)
		{
			// Check if the template is in the old format
			if (0 === strpos($template->settings, '{"options'))
			{
				// Get the old data format
				$oldformat = json_decode($template->settings);

				// Store everything in the new format
				$newformat = array();

				foreach ($oldformat as $section => $settings)
				{
					$newformat = array_merge($newformat, (array) $settings);
				}

				// Perform some extra changes
				if (isset($newformat['operation']))
				{
					$newformat['operation'] = str_replace(array('import', 'export'), '', $newformat['operation']);
				}

				if (isset($newformat['exportto']))
				{
					$newformat['exportto'] = array($newformat['exportto']);
				}

				// Store the new template format
				$query->clear()
					->update($db->quoteName('#__csvi_templates'))
					->set($db->quoteName('settings') . ' = ' . $db->quote(json_encode($newformat)))
					->where($db->quoteName('csvi_template_id') . ' = ' . (int) $csvi_template_id);
				$db->setQuery($query)->execute();
			}
		}
	}

	/**
	 * Rename any files after installation if needed.
	 *
	 * @return  void.
	 *
	 * @since   6.6.0
	 */
	private function cleanFiles()
	{
		$files = array(
			JPATH_ADMINISTRATOR . '/components/com_csvi/models/abouts.php',
			JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_csvi.ini',
			JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_csvi.sys.ini',
			JPATH_ADMINISTRATOR . '/components/com_csvi/assets/css/images/index.html',
			JPATH_ADMINISTRATOR . '/components/com_csvi/addon/com_categories/install/csvi_templates.xml',
			JPATH_ADMINISTRATOR . '/components/com_csvi/log.txt',
			JPATH_ADMINISTRATOR . '/components/com_csvi/assets/js/autocomplete.js',
			JPATH_ADMINISTRATOR . '/components/com_csvi/assets/js/jquery-ui.js',
			JPATH_ADMINISTRATOR . '/components/com_csvi/assets/js/jquery.js',
			JPATH_ADMINISTRATOR . '/components/com_csvi/controllers/availablefield.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/controllers/cpanel.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/controllers/settings.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/controllers/settings.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/models/abouts.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/models/analyzers.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/models/forms/settings_google.xml',
			JPATH_ADMINISTRATOR . '/components/com_csvi/models/forms/settings_icecat.xml',
			JPATH_ADMINISTRATOR . '/components/com_csvi/models/forms/settings_log.xml',
			JPATH_ADMINISTRATOR . '/components/com_csvi/models/forms/settings_site.xml',
			JPATH_ADMINISTRATOR . '/components/com_csvi/models/forms/settings_yandex.xml',
			JPATH_ADMINISTRATOR . '/components/com_csvi/models/settings.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/views/map/tmpl/form.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/views/rule/tmpl/form.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/views/task/tmpl/form.form.xml',
			JPATH_ADMINISTRATOR . '/components/com_csvi/views/task/tmpl/form.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/views/templatefield/tmpl/form.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/views/templates/tmpl/form.default.xml',
			JPATH_ADMINISTRATOR . '/components/com_csvi/views/templates/view.form.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/controllers/addons.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/dispatcher.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/helper/db.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/models/addons.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/models/maintenances.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/toolbar.php',
			JPATH_ADMINISTRATOR . '/components/com_csvi/views/imports/tmpl/steps.php',
			JPATH_SITE . '/components/com_csvi/controllers/exports.php',
			JPATH_SITE . '/components/com_csvi/controllers/imports.php',
			JPATH_SITE . '/components/com_csvi/models/imports.php',
		);

		JFile::delete($files);

		$folders = array(
			JPATH_ADMINISTRATOR . '/components/com_csvi/views/settings',
			JPATH_ADMINISTRATOR . '/components/com_csvi/views/cpanel',
			JPATH_ADMINISTRATOR . '/components/com_csvi/assets/render',
			JPATH_ADMINISTRATOR . '/components/com_csvi/views/addons',
			JPATH_ADMINISTRATOR . '/components/com_csvi/views/default',
			JPATH_ADMINISTRATOR . '/components/com_csvi/addon',
			JPATH_SITE . '/layouts/csvi',
			JPATH_SITE . '/components/com_csvi',
		);

		foreach ($folders as $folder)
		{
			if (JFolder::exists($folder))
			{
				JFolder::delete($folder);
			}
		}
	}

	/**
	 * Actions to perform after un-installation.
	 *
	 * @param   object  $parent  The parent object.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   7.0.1
	 */
	public function uninstall($parent)
	{
		// Clean up the cache
		$cache = JFactory::getCache('com_csvi', '');
		$cache->clean('com_csvi');

		return true;
	}

	/**
	 * Check the database structure.
	 *
	 * @return  void
	 *
	 * @since   7.5.0
	 */
	private function checkDatabase()
	{
		/** @var JDatabaseDriver $db */
		$db = JFactory::getDbo();

		require_once JPATH_ADMINISTRATOR . '/components/com_csvi/helper/database.php';
		$databaseCheck = new CsviHelperDatabase($db);
		$databaseCheck->process(JPATH_ADMINISTRATOR . '/components/com_csvi/assets/core/database.xml');
	}
}
