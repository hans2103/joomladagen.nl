<?php
/**
 * @package         Regular Labs Extension Manager
 * @version         7.1.4
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use RegularLabs\Library\Language as RL_Language;

jimport('joomla.application.component.modelitem');

/**
 * Process Model
 */
class RegularLabsManagerModelProcess extends JModelItem
{
	/**
	 * Get the extensions data
	 */
	public function getItems()
	{
		$ids  = JFactory::getApplication()->input->get('ids', [], 'array');
		$urls = JFactory::getApplication()->input->get('urls', [], 'array');

		if (empty($ids) || empty($urls))
		{
			return [];
		}

		$model       = JModelLegacy::getInstance('Default', 'RegularLabsManagerModel');
		$this->items = $model->getItems($ids);
		foreach ($ids as $i => $id)
		{
			if (isset($urls[$i]))
			{
				$this->items[$id]->url = $urls[$i];
			}
			else
			{
				unset($this->items[$id]);
			}
		}

		return $this->items;
	}

	/**
	 * Download and install
	 */
	public function install($id, $url)
	{
		if ( ! is_string($url))
		{
			die(JText::_('RLEM_ERROR_NO_VALID_URL'));
		}

		$url = 'http://' . str_replace('http://', '', $url);

		if ( ! $target = JInstallerHelper::downloadPackage($url))
		{
			die(JText::_('RLEM_ERROR_CANNOT_DOWNLOAD_FILE') . ' [' . $url . ']');
		}

		$target = JFactory::getConfig()->get('tmp_path') . '/' . $target;

		RL_Language::load('com_installer', JPATH_ADMINISTRATOR);
		jimport('joomla.installer.installer');
		jimport('joomla.installer.helper');

		// Get an installer instance
		$installer = JInstaller::getInstance();

		// Unpack the package
		$package = JInstallerHelper::unpack($target);

		if ( ! isset($package['extractdir']))
		{
			return;
		}

		// Cleanup the install files
		if ( ! is_file($package['packagefile']))
		{
			$config                 = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['packagefile']);

		// Install the package
		$installer->install($package['dir']);

		// Cleanup the install files.
		if ( ! is_file($package['packagefile']))
		{
			$config                 = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
	}

	/**
	 * Download and install
	 */
	public function uninstall($id)
	{
		$model = JModelLegacy::getInstance('Default', 'RegularLabsManagerModel');
		$item  = $model->getItems([$id]);
		$item  = $item[$id];

		$ids = [];
		foreach ($item->types as $type)
		{
			if ($type->id)
			{
				$ids[] = $type->id;
			}
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_installer/models/manage.php';
		$installer = JModelLegacy::getInstance('Manage', 'InstallerModel');
		$installer->remove($ids);

		die('1');
	}
}
