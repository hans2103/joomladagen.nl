<?php
/**
 * @package         Regular Labs Extension Manager
 * @version         6.1.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2016 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_regularlabsmanager'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

require_once JPATH_LIBRARIES . '/regularlabs/helpers/functions.php';

RLFunctions::loadLanguage('com_regularlabsmanager');
RLFunctions::loadLanguage('com_modules', JPATH_ADMINISTRATOR);
RLFunctions::loadLanguage('plg_system_regularlabs');

$helper = new RegularLabsManagerHelper;

if (!$helper->isFrameworkEnabled())
{
	return false;
}

if (version_compare(PHP_VERSION, '5.3', '<'))
{
	$helper->throwError(JText::sprintf('RLEM_NOT_COMPATIBLE_PHP', PHP_VERSION, '5.3'));

	return false;
}

$helper->uninstallNoNumberExtensionManager();

JControllerLegacy::getInstance('RegularLabsManager')
	->execute(JFactory::getApplication()->input->get('task'))
	->redirect();

class RegularLabsManagerHelper
{
	private $_title       = 'COM_REGULARLABSMANAGER';
	private $_lang_prefix = 'RLEM';

	/**
	 * Check if the Regular Labs Library is enabled
	 *
	 * @return bool
	 */
	public function isFrameworkEnabled()
	{
		// Return false if Regular Labs Library is not installed
		if (!$this->isFrameworkInstalled())
		{
			return false;
		}

		$regularlabs = JPluginHelper::getPlugin('system', 'regularlabs');
		if (!isset($regularlabs->name))
		{
			$this->throwError(
				JText::_($this->_lang_prefix . '_REGULAR_LABS_LIBRARY_NOT_ENABLED')
				. ' ' . JText::sprintf($this->_lang_prefix . '_EXTENSION_CAN_NOT_FUNCTION', JText::_($this->_title))
			);

			return false;
		}

		return true;
	}

	/**
	 * Check if the Regular Labs Library is installed
	 *
	 * @return bool
	 */
	public function isFrameworkInstalled()
	{
		jimport('joomla.filesystem.file');

		if (
			!JFile::exists(JPATH_PLUGINS . '/system/regularlabs/regularlabs.xml')
			|| !JFile::exists(JPATH_LIBRARIES . '/regularlabs/regularlabs.xml')
		)
		{
			$this->throwError(
				JText::_($this->_lang_prefix . '_REGULAR_LABS_LIBRARY_NOT_INSTALLED')
				. ' ' . JText::sprintf($this->_lang_prefix . '_EXTENSION_CAN_NOT_FUNCTION', JText::_($this->_title))
			);

			return false;
		}

		return true;
	}

	/**
	 * Place an error in the message queue
	 */
	public function throwError($text)
	{
		JFactory::getApplication()->enqueueMessage($text, 'error');
	}

	public function uninstallNoNumberExtensionManager()
	{
		jimport('joomla.filesystem.folder');

		// Check if old NoNumber Extension Manager is still installed
		if (!JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_nonumbermanager'))
		{
			return;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('extension_id')
			->from('#__extensions')
			->where($db->quoteName('element') . ' = ' . $db->quote('com_nonumbermanager'));

		$db->setQuery($query);
		$id = $db->loadResult();

		if (empty($id))
		{
			return;
		}

		$installer = new JInstaller;
		$installer->uninstall('component', $id);
	}
}
