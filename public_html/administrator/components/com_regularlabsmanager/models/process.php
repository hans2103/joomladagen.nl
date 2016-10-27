<?php
/**
 * @package         Regular Labs Extension Manager
 * @version         6.1.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2016 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

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
		$ids  = JFactory::getApplication()->input->get('ids', array(), 'array');
		$urls = JFactory::getApplication()->input->get('urls', array(), 'array');

		if (empty($ids) || empty($urls))
		{
			return array();
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
	function install($id, $url)
	{
		if (!is_string($url))
		{
			die(JText::_('RLEM_ERROR_NO_VALID_URL'));
		}

		$url = 'http://' . str_replace('http://', '', $url);

		if (!$target = JInstallerHelper::downloadPackage($url))
		{
			die(JText::_('RLEM_ERROR_CANNOT_DOWNLOAD_FILE') . ' [' . $url . ']');
		}

		$target = JFactory::getConfig()->get('tmp_path') . '/' . $target;

		require_once JPATH_LIBRARIES . '/regularlabs/helpers/functions.php';
		RLFunctions::loadLanguage('com_installer', JPATH_ADMINISTRATOR);
		jimport('joomla.installer.installer');
		jimport('joomla.installer.helper');

		// Get an installer instance
		$installer = JInstaller::getInstance();

		// Unpack the package
		$package = JInstallerHelper::unpack($target);

		// Cleanup the install files
		if (!is_file($package['packagefile']))
		{
			$config                 = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['packagefile']);

		// Install the package
		$installer->install($package['dir']);
		
		// Cleanup the install files.
		if (!is_file($package['packagefile']))
		{
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
	}

	/**
	 * Download and install
	 */
	function uninstall($id)
	{
		$model = JModelLegacy::getInstance('Default', 'RegularLabsManagerModel');
		$item  = $model->getItems(array($id));
		$item  = $item[$id];

		$ids = array();
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
		echo 1;
	}
}
