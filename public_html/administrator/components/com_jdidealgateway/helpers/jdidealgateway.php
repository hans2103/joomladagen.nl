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
 * JD iDEAL Gateway helper.
 *
 * @package     JDiDEAL
 * @subpackage  Core
 * @since       3.0
 */
class JdidealGatewayHelper
{
	/**
	 * @var    String  base update url, to decide whether to process the event or not
	 *
	 * @since  1.0.0
	 */
	private $baseUrl = 'https://jdideal.nl/updates/';

	/**
	 * @var    String  Extension identifier, to retrieve its params
	 *
	 * @since  1.0.0
	 */
	private $extension = 'com_jdidealgateway';

	/**
	 * @var    String  Extension title, to retrieve its params
	 *
	 * @since  1.0.0
	 */
	private $extensionTitle = 'JD iDEAL Gateway';

	/**
	 * Render submenu.
	 *
	 * @param   string  $vName  The name of the current view.
	 *
	 * @return  void.
	 *
	 * @since   2.8
	 */
	public function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_JDIDEALGATEWAY_DASHBOARD'), 'index.php?option=com_jdidealgateway&view=jdidealgateway', $vName === 'jdidealgateway'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_JDIDEALGATEWAY_LOGS'), 'index.php?option=com_jdidealgateway&view=logs', $vName === 'logs'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_JDIDEALGATEWAY_PROFILES'), 'index.php?option=com_jdidealgateway&view=profiles', $vName === 'profiles'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_JDIDEALGATEWAY_MESSAGES'), 'index.php?option=com_jdidealgateway&view=messages', $vName === 'messages'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_JDIDEALGATEWAY_EMAILS'), 'index.php?option=com_jdidealgateway&view=emails', $vName === 'emails'
		);
		JHtmlSidebar::addEntry(
			'<hr />', false
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_JDIDEALGATEWAY_PAYMENTS'), 'index.php?option=com_jdidealgateway&view=pays', $vName === 'pays'
		);
	}

	/**
	 * Adding required headers for successful extension update
	 *
	 * @param   string $url     url from which package is going to be downloaded
	 * @param   array  $headers headers to be sent along the download request (key => value format)
	 *
	 * @return  boolean true    Always true, regardless of success
	 *
	 * @since   1.0.0
	 *
	 * @throws  Exception
	 */
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		// Are we trying to update our own extensions?
		if (strpos($url, $this->baseUrl) !== 0)
		{
			return true;
		}

		// Load language file
		$jLanguage = JFactory::getLanguage();
		$jLanguage->load('com_jdidealgateway', JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/', 'en-GB', true, true);
		$jLanguage->load('com_jdidealgateway', JPATH_ADMINISTRATOR . '/components/com_jdidealgateway/', null, true, false);

		// Get the Download ID from component params
		$downloadId = JComponentHelper::getComponent($this->extension)->params->get('downloadid', '');

		// Set Download ID first
		if (empty($downloadId))
		{
			JFactory::getApplication()->enqueueMessage(
				JText::sprintf('COM_JDIDEALGATEWAY_DOWNLOAD_ID_REQUIRED',
					$this->extension,
					$this->extensionTitle
				),
				'error'
			);

			return true;
		}
		// Append the Download ID
		else
		{
			$separator = strpos($url, '?') !== false ? '&' : '?';
			$url       .= $separator . 'key=' . $downloadId;
		}

		return true;
	}
}
