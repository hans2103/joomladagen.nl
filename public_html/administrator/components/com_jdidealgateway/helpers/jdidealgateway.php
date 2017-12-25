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
}
