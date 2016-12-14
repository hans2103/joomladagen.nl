<?php
/**
* @package     Joomla.Administrator
* @subpackage  com_installer
*
* @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;


/**
* Installer controller for Joomla! installer class.
*
* @since  1.5
*/
class SppagebuilderControllerIntegrations extends JControllerLegacy
{
	/**
	* Install an extension.
	*
	* @return  void
	*
	* @since   1.5
	*/

	public function install() {
		$report = array();
		$user = JFactory::getUser();
		$input = JFactory::getApplication()->input;
		$model = $this->getModel('integrations');

		// Return if not authorised
		if (!$user->authorise('core.admin', 'com_sppagebuilder')) {
			$report['message'] = JText::_('JERROR_ALERTNOAUTHOR');
			$report['success'] = false;
			die(json_encode($report));
		}

		$component = $input->get('integration', 'com_content', 'STRING');
		$integrations = json_decode(file_get_contents('https://www.joomshaper.com/updates/pagebuilder/integrations.json'));

		if(isset($integrations->$component) && $integrations->$component) {
			$url = $integrations->$component->downloadUrl;
			$integration = $integrations->$component;
		} else {
			$report['message'] = JText::_('Unsble to find the download package');
			$report['success'] = false;
			die(json_encode($report));
		}

		$p_file = JInstallerHelper::downloadPackage($url);

		if (!$p_file) {
			$report['message'] = JText::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL');
			$report['success'] = false;
			die(json_encode($report));
		}

		$config   = JFactory::getConfig();
		$tmp_dest = $config->get('tmp_path');
		$package = JInstallerHelper::unpack($tmp_dest . '/' . $p_file, true);
		$installer = JInstaller::getInstance();

		// Was the package unpacked?
		if (!$package || !$package['type']) {
			if (in_array($installType, array('upload', 'url'))) {
				JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
			}

			$report['message'] = JText::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE');
			$report['success'] = false;
			die(json_encode($report));
		}

		// Install the package.
		if (!$installer->install($package['dir'])) {
			// There was an error installing the package.
			$report['message'] = JText::sprintf('COM_INSTALLER_INSTALL_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$report['success'] = false;
		} else {
			// Package installed sucessfully.
			$report['message'] = JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$report['success'] = true;
			$model->storeInstall($integration);
		}

		// Cleanup the install files.
		if (!is_file($package['packagefile'])) {
			$package['packagefile'] = $tmp_dest . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		die(json_encode($report));
	}

	// Activate
	public function enable() {
		$report = array();
		$user = JFactory::getUser();
		$input = JFactory::getApplication()->input;
		$model = $this->getModel('integrations');

		// Return if not authorised
		if (!$user->authorise('core.admin', 'com_sppagebuilder')) {
			$report['message'] = JText::_('JERROR_ALERTNOAUTHOR');
			$report['success'] = false;
			die(json_encode($report));
		}

		$component = $input->get('integration', 'com_content', 'STRING');
		$model->toggleActivate($component, 1);

		$report['success'] = true;
		die(json_encode($report));
	}

	// Deactivate
	public function disable() {
		$report = array();
		$user = JFactory::getUser();
		$input = JFactory::getApplication()->input;
		$model = $this->getModel('integrations');

		// Return if not authorised
		if (!$user->authorise('core.admin', 'com_sppagebuilder')) {
			$report['message'] = JText::_('JERROR_ALERTNOAUTHOR');
			$report['success'] = false;
			die(json_encode($report));
		}

		$component = $input->get('integration', 'com_content', 'STRING');
		$model->toggleActivate($component, 0);

		$report['success'] = true;
		die(json_encode($report));
	}

	public function uninstall() {
		$report = array();
		$user = JFactory::getUser();
		$input = JFactory::getApplication()->input;
		$model = $this->getModel('integrations');

		// Return if not authorised
		if (!$user->authorise('core.admin', 'com_sppagebuilder')) {
			$report['message'] = JText::_('JERROR_ALERTNOAUTHOR');
			$report['success'] = false;
			die(json_encode($report));
		}

		$component = $input->get('integration', 'com_content', 'STRING');
		$model->uninstall($component);

		$report['success'] = true;
		die(json_encode($report));
	}

}
