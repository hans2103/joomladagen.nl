<?php
/**
 * Copyright (C) 2009  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Determine if the joomlamailer component is installed and enabled
jimport('joomla.filesystem.file');
jimport('joomla.application.component.helper');
if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/joomailermailchimpintegration.php')
    || !JComponentHelper::isEnabled('com_joomailermailchimpintegration', true)) {
    echo JText::_('JM_PLEASE_INSTALL_JOOMLAMAILER');
    return;
} else if (!$params->get('listid')) {
    // not list selected in the module configuration
    echo JText::_('JM_PLEASE_SELECT_A_LIST_IN_THE_CONFIG');
    return;
}

require_once('includes/McSignupHelper.php');

$document = JFactory::getDocument();
if (version_compare(JVERSION, '3.0.0') >= 0) {
    JHtml::_('jquery.framework');
} else {
    $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/jquery.min.js');
}

$document->addStyleSheet(JURI::root() . 'media/mod_mailchimpsignup/css/mailchimpsignup.css');
$document->addScript(JURI::root() . 'media/mod_mailchimpsignup/js/mailchimpsignup.js');

// load language files. include en-GB as fallback
$jlang = JFactory::getLanguage();
$jlang->load('mod_mailchimpsignup', JPATH_SITE, 'en-GB', true);
$jlang->load('mod_mailchimpsignup', JPATH_SITE, $jlang->getDefault(), true);
$jlang->load('mod_mailchimpsignup', JPATH_SITE, null, true);

require JModuleHelper::getLayoutPath('mod_mailchimpsignup', $params->get('layout', 'default'));
