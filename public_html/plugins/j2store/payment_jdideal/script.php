<?php
/**
 * @package    JDiDEAL
 *
 * @author     Roland Dalmulder <contact@jdideal.nl>
 * @copyright  Copyright (C) 2009 - 2017 RolandD Cyber Produksi. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://jdideal.nl
 */

defined('_JEXEC') or die;

/**
 * Load the JD iDEAL Gateway installer.
 *
 * @package  JDiDEAL
 * @since    2.0
 */
class Plgj2storepayment_jdidealInstallerScript
{
	/**
	 * Run the preflight operations.
	 *
	 * @param   object  $parent  The parent class.
	 *
	 * @return  bool  True on success | False on failure.
	 *
	 * @since   4.7.0
	 */
	public function preflight($parent)
	{
		// Check if JD iDEAL Gateway is installed
		if ($parent === 'install' && JComponentHelper::isInstalled('com_jdidealgateway') === 0)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('PLG_J2STORE_JDIDEAL_JDIDEALGATEWAY_NOT_INSTALLED'), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Run the postflight operations.
	 *
	 * @param   object  $parent  The parent class.
	 *
	 * @return bool True on success | False on failure.
	 *
	 * @throws Exception
	 * @since   2.0
	 */
	public function postflight($parent)
	{
		$app = JFactory::getApplication();

		// Enable the plugin
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('enabled') . ' =  1')
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('element') . ' = ' . $db->quote('payment_jdideal'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('j2store'));

		$db->setQuery($query);

		if (!$db->execute())
		{
			$app->enqueueMessage(JText::sprintf('PLG_JDIDEAL_PLUGIN_NOT_ENABLED', $db->getErrorMsg()), 'error');

			return false;
		}

		$app->enqueueMessage(JText::_('PLG_JDIDEAL_PLUGIN_ENABLED'));

		// Copy the addons folder
		$src = JPATH_SITE . '/plugins/j2store/payment_jdideal/addons/j2store.php';
		$dest = JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/addons/j2store.php';

		if (!JFile::copy($src, $dest))
		{
			return false;
		}

		return true;
	}

	/**
	 * Cleanup after uninstallation.
	 *
	 * @param   object  $parent  The parent class.
	 *
	 * @return  void.
	 *
	 * @since   3.3
	 */
	public function uninstall($parent)
	{
		JFile::delete(JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/addons/j2store.php');
	}
}
