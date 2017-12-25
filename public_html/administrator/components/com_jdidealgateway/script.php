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
class Com_JdidealgatewayInstallerScript
{
	/**
	 * Method to run before an install/update/uninstall method
	 *
	 * @param   string  $type    The type of change (install, update or discover_install).
	 * @param   object  $parent  The class calling this method.
	 *
	 * @return  bool  True on success | False on failure
	 *
	 * @since   2.0
	 *
	 * @throws  Exception
	 */
	public function preflight($type, $parent)
	{
		// Check if the PHP version is correct
		if (version_compare(phpversion(), '5.2', '<') === '-1')
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('COM_JDIDEALGATEWAY_PHP_VERSION_ERROR', phpversion()), 'error');

			return false;
		}

		// Check if the Joomla version is correct
		$version = new JVersion;

		if (version_compare($version->getShortVersion(), '3.5', '<') === '-1')
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('COM_JDIDEALGATEWAY_JOOMLA_VERSION_ERROR', $version->getShortVersion()), 'error');

			return false;
		}

		// Fix the schema version
		$this->fixSchema();

		// Clean out any old files
		$this->cleanFiles();

		return true;
	}

	/**
	 * Method to run after an install/update/uninstall method
	 *
	 * @param   string  $type    The type of change (install, update or discover_install).
	 * @param   object  $parent  The class calling this method.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 */
	public function postflight($type, $parent)
	{
		// Migrate the old settings
		$this->migrateSettings();

		// Install the CLI script
		$this->installCliScript($parent);

		// Install the library
		$this->installLibrary($parent);
	}

	/**
	 * Clean up old files.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 */
	private function cleanFiles()
	{
		// Remove the old language files
		$files = array(
			JPATH_ADMINISTRATOR . '/language/nl-NL/nl-NL.com_jdidealgateway.ini',
			JPATH_ADMINISTRATOR . '/language/nl-NL/nl-NL.com_jdidealgateway.sys.ini',
			JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_jdidealgateway.ini',
			JPATH_ADMINISTRATOR . '/language/en-GB/en-GB.com_jdidealgateway.sys.ini',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/helpers/cookbook.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/helpers/bankconfig.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/fields/jdidealgatewaywaitoptions.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/fields/jdidealgatewaystatus.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/fields/jdidealgatewayform.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/fields/jdidealgatewaybanks.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/forms/jdidealgateway.xml',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/sql/install/install.mysql.utf8.sql',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/sql/update/2.0.sql',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/sql/update/2.1.sql',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/sql/update/2.2.sql',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/sql/update/2.7.1.sql',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/sql/update/2.8.sql',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/sql/update/2.8.2.sql',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/sql/update/4.0.sql',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/tables/jdidealgateway_logs.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/tables/jdidealgateway.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/config.xml',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_targetpay.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_sisow.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_omnikassa_rabo.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_ogone.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_mollie.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_lite_rabobank.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_lite_abnamro.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_internetkassa_rabo.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_internetkassa_abn.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_general.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_extra_code.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_cert_upload.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_buckaroo.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_basic.php',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/default_advanced.php',
			JPATH_SITE . '/components/com_jdidealgateway/controllers/index.html',
			JPATH_SITE . '/components/com_jdidealgateway/models/forms/index.html',
			JPATH_SITE . '/components/com_jdidealgateway/models/statusrequest.php',
			JPATH_SITE . '/components/com_jdidealgateway/models/notify.php',
			JPATH_SITE . '/components/com_jdidealgateway/models/index.html',
			JPATH_SITE . '/components/com_jdidealgateway/views/index.html',
			JPATH_SITE . '/components/com_jdidealgateway/views/checkideal/index.html',
			JPATH_SITE . '/components/com_jdidealgateway/views/checkout/index.html',
			JPATH_SITE . '/components/com_jdidealgateway/views/checkout/tmpl/index.html',
			JPATH_SITE . '/components/com_jdidealgateway/views/pay/index.html',
			JPATH_SITE . '/components/com_jdidealgateway/views/pay/tmpl/index.html',
			JPATH_SITE . '/cli/httptest.php',
		);

		JFile::delete($files);

		$folders = array(
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/models/addons',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/email/tmpl/30',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/emails/tmpl/30',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/jdidealgateway/tmpl/30',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/logs/tmpl/30',
			JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/views/pay',
			JPATH_SITE . '/components/com_jdidealgateway/models/psp',
			JPATH_SITE . '/components/com_jdidealgateway/models/security',
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
	 * Migrate old settings to the new format.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 */
	private function migrateSettings()
	{
		$db = JFactory::getDbo();
		$tables = $db->getTableList();
		$table = $db->getPrefix() . 'jdidealgateway_config';

		if (in_array($table, $tables, true))
		{
			$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array(
						'ideal',
						'payment_extrainfo'
					)
				)
			)
			->from($db->quoteName('#__jdidealgateway_config'))
			->where($db->quoteName('published') . ' = 1');
			$db->setQuery($query);
			$config = $db->loadObject();

			if ($config)
			{
				$settings = new \Joomla\Registry\Registry($config->payment_extrainfo);
				$settings->set('psp', str_replace('_', '-', $config->ideal));
				$settings->set('name', $config->ideal);
				$settings->set('alias', $config->ideal);

				$psp = str_replace('_', '-', $config->ideal);

				// Change some fields to their new name for Rabobank Omnikassa
				if ($psp === 'rabo-omnikassa')
				{
					$settings->set('testmode', strpos($settings->get('bankurl'), 'simu') ? 1 : 0);
					$settings->set('password', $settings->get('IDEAL_PrivatekeyPass'));
					$settings->set('merchantId', $settings->get('IDEAL_MerchantID'));
				}

				// Create the profile
				$query->clear()
					->insert($db->quoteName('#__jdidealgateway_profiles'))
					->columns(array('name', 'psp', 'alias', 'paymentInfo', 'ordering'))
					->values(
						$db->quote($config->ideal) . ','
						. $db->quote($psp) . ','
						. $db->quote($config->ideal) . ','
						. $db->quote($settings->toString()) . ','
						. (int) 1
					);
				$db->setQuery($query);

				try
				{
					$db->execute();

					$profileId = $db->insertid();

					// Unpublish the migrated setting
					$query->clear()
						->update($db->quoteName('#__jdidealgateway_config'))
						->set($db->quoteName('published') . ' = 0')
						->where($db->quoteName('published') . ' = 1');
					$db->setQuery($query)->execute();

					// Success message
					$messageType = $settings->get('success_text_id', false) ? 1 : 0;
					$messageTextId = $settings->get('success_text_id', 0);
					$messageText = $settings->get('success_text', '');
					$this->storeMessage($profileId, 'SUCCESS', $messageType, $messageTextId, $messageText);

					// Failure message
					$messageType = $settings->get('failed_text_id', false) ? 1 : 0;
					$messageTextId = $settings->get('failed_text_id', 0);
					$messageText = $settings->get('failed_text', '');
					$this->storeMessage($profileId, 'FAILURE', $messageType, $messageTextId, $messageText);

					// Cancelled message
					$messageType = $settings->get('cancelled_text_id', false) ? 1 : 0;
					$messageTextId = $settings->get('cancelled_text_id', 0);
					$messageText = $settings->get('cancelled_text', '');
					$this->storeMessage($profileId, 'CANCELLED', $messageType, $messageTextId, $messageText);

					// Transfer message
					$messageType = 0;
					$messageTextId = 0;
					$messageText = $settings->get('IDEAL_OVERBOEKING_MESSAGE', '');
					$this->storeMessage($profileId, 'TRANSFER', $messageType, $messageTextId, $messageText);

					// Warn the users to check their settings
					JFactory::getApplication()->enqueueMessage(JText::_('COM_JDIDEALGATEWAY_MIGRATION_WARNING'), 'notice');
				}
				catch (Exception $e)
				{
					JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				}
			}
		}
	}

	/**
	 * Store a migrated message.
	 *
	 * @param   int     $profileId      The profile ID.
	 * @param   string  $orderStatus    The order type the message is for.
	 * @param   int     $messageType    The type of message to render.
	 * @param   int     $messageTextId  The ID of the content item.
	 * @param   string  $messageText    The text to show.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 *
	 * @throws Exception
	 */
	private function storeMessage($profileId, $orderStatus, $messageType, $messageTextId, $messageText)
	{
		$db   = JFactory::getDbo();
		$date = new JDate;
		$user = JFactory::getUser();

		// Get the success message
		$query = $db->getQuery(true)
			->insert($db->quoteName('#__jdidealgateway_messages'))
			->columns(
				$db->quoteName(
					array(
						'subject',
						'orderstatus',
						'profile_id',
						'message_type',
						'message_text_id',
						'message_text',
						'language',
						'created',
						'created_by'
					)
				)
			)
			->values(
				$db->quote(ucfirst(strtolower($orderStatus))) . ', '
				. $db->quote($orderStatus) . ', '
				. (int) $profileId . ', '
				. (int) $messageType . ', '
				. (int) $messageTextId . ', '
				. $db->quote($messageText) . ', '
				. $db->quote('*') . ', '
				. $db->quote($date->toSql()) . ', '
				. (int) $user->get('id')
			);
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * Install the CLI script.
	 *
	 * @param   object  $parent  The class calling this method.
	 *
	 * @return  void.
	 *
	 * @since   3.0
	 *
	 * @throws  RuntimeException
	 */
	private function installCliScript($parent)
	{
		$src = $parent->getParent()->getPath('source');

		JFolder::copy($src . '/cli', JPATH_SITE . '/cli', '', true);
	}

	/**
	 * Install the library.
	 *
	 * @param   object  $parent  The class calling this method.
	 *
	 * @return  void.
	 *
	 * @since   4.0
	 *
	 * @throws  RuntimeException
	 */
	private function installLibrary($parent)
	{
		$src = $parent->getParent()->getPath('source');

		JFolder::copy($src . '/libraries', JPATH_LIBRARIES, '', true);
	}

	/**
	 * Fix the schema version.
	 *
	 * @return  void
	 *
	 * @since   4.2.0
	 */
	private function fixSchema()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'))
			->where($db->quoteName('element') . ' = ' . $db->quote('com_jdidealgateway'));
		$db->setQuery($query);

		$extensionId = $db->loadResult();

		if ($extensionId)
		{
			$query->clear()
				->update($db->quoteName('#__schemas'))
				->set($db->quoteName('version_id') . ' = ' . $db->quote('4.1.0'))
				->where($db->quoteName('extension_id') . ' = ' . (int) $extensionId)
				->where($db->quoteName('version_id') . ' = ' . $db->quote('4.0'));
			$db->setQuery($query)->execute();
		}
	}

	/**
	 * Called on install
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 *
	 * @since   4.8.0
	 */
	public function install(JAdapterInstance $adapter)
	{
		JFactory::getApplication()->enqueueMessage(JText::_('COM_JDIDEALGATEWAY_INSTALL_PLUGIN'), 'notice');

		return true;
	}

	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 *
	 * @since   4.8.0
	 */
	public function update(JAdapterInstance $adapter)
	{
		JFactory::getApplication()->enqueueMessage(JText::_('COM_JDIDEALGATEWAY_UPDATE_PLUGIN'), 'notice');

		return true;
	}
}
