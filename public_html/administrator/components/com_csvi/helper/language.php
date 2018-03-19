<?php
/**
 * @package     CSVI
 * @subpackage  Language
 *
 * @author      RolandD Cyber Produksi <contact@csvimproved.com>
 * @copyright   Copyright (C) 2006 - 2018 RolandD Cyber Produksi. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://csvimproved.com
 */

defined('_JEXEC') or die;

/**
 * Language helper class for the component.
 *
 * @package     CSVI
 * @subpackage  Language
 * @since       7.1.0
 */
class CsviHelperLanguage extends JLanguage
{
	/**
	 * Instantiate the language loader.
	 *
	 * @param   string  $addon   The component to load the language for.
	 * @param   bool    $reload  Set if the language should be reloaded.
	 *
	 * @return  bool  Always returns true
	 *
	 * @since   7.1.0
	 */
	public function loadAddonLanguage($addon, $reload = true)
	{
		jimport('joomla.filesystem.file');

		// Load the language files
		$language        = JFactory::getLanguage();
		$languageTag     = $language->getTag();
		$languageDefault = $language->getDefault();
		$extension       = substr($addon, 4);

		// Load the addon languages in the default locations
		$language->load($addon, JPATH_ADMINISTRATOR . '/components/' . $addon, $languageTag, true);
		$language->load($addon, JPATH_SITE . '/components/' . $addon, $languageTag, true);

		// Load the helper
		if (JFile::exists(JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $addon . '/helper/' . $addon . '.php'))
		{
			require_once JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $addon . '/helper/' . $addon . '.php';

			$db       = JFactory::getDbo();
			$settings = new CsviHelperSettings($db);
			$log      = new CsviHelperLog($settings, $db);
			$template = new CsviHelperTemplate(0);
			$fields   = new CsviHelperFields($template, $log, $db);

			$helperName = ucfirst($addon) . 'Helper' . ucfirst($addon);
			$addonHelper = new $helperName($template, $log, $fields, $db);

			if (method_exists($addonHelper, 'loadLanguage'))
			{
				$paths = $addonHelper->loadLanguage();

				foreach ($paths as $filename => $path)
				{
					$language->load($filename, $path, $languageTag, true);
				}
			}
		}

		// Load our own language files from the addon
		$language->load('com_csvi', JPATH_PLUGINS . '/csviaddon/' . $extension . '/' . $addon, $languageDefault, $reload);

		// Load the overrides
		$overrides = array(
			JPATH_ADMINISTRATOR . '/language/overrides/' . $languageTag . '.override.ini',
			JPATH_SITE . '/language/overrides/' . $languageTag . '.override.ini',
		);

		foreach ($overrides as $override)
		{
			$language->loadLanguage($override, 'override');
		}

		return true;
	}
}
