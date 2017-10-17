<?php
/**
 * @package     Conference
 *
 * @author      Stichting Sympathy <info@stichtingsympathy.nl>
 * @copyright   Copyright (C) 2013 - [year] Stichting Sympathy. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://joomladagen.nl
 */

defined('_JEXEC') or die;

/**
 * Events model.
 *
 * @package     Conference
 * @since       1.0
 */
final class ConferenceHelperConference
{
	/**
	 * Render submenu.
	 *
	 * @param   string  $vName  The name of the current view.
	 *
	 * @return  void.
	 *
	 * @since   1.0
	 */
	public function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(JText::_('COM_CONFERENCE_TITLE_EVENTS'), 'index.php?option=com_conference&view=events', $vName == 'events');
		JHtmlSidebar::addEntry(JText::_('COM_CONFERENCE_TITLE_DAYS'), 'index.php?option=com_conference&view=days', $vName == 'days');
		JHtmlSidebar::addEntry(JText::_('COM_CONFERENCE_TITLE_SLOTS'), 'index.php?option=com_conference&view=slots', $vName == 'slots');
		JHtmlSidebar::addEntry(JText::_('COM_CONFERENCE_TITLE_LEVELS'), 'index.php?option=com_conference&view=levels', $vName == 'levels');
		JHtmlSidebar::addEntry(JText::_('COM_CONFERENCE_TITLE_ROOMS'), 'index.php?option=com_conference&view=rooms', $vName == 'rooms');
		JHtmlSidebar::addEntry(JText::_('COM_CONFERENCE_TITLE_SESSIONS'), 'index.php?option=com_conference&view=sessions', $vName == 'sessions');
		JHtmlSidebar::addEntry(JText::_('COM_CONFERENCE_TITLE_SPEAKERS'), 'index.php?option=com_conference&view=speakers', $vName == 'speakers');
	}
}
