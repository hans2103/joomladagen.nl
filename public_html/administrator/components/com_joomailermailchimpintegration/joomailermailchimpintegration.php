<?php
/**
* Copyright (C) 2009  freakedout (www.freakedout.de)
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

// no direct access
defined('_JEXEC') or die('Restricted Access');

// Make sure the user is authorized to view this page
$user = JFactory::getUser();
if (!$user->authorise('core.manage', 'com_joomailermailchimpintegration')) {
    throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 401);
}

// set permission constants
define('JOOMLAMAILER_MANAGE_LISTS', ($user->authorise('joomlamailer.lists', 'com_joomailermailchimpintegration')) ? 1 : 0);
define('JOOMLAMAILER_CREATE_DRAFTS', ($user->authorise('joomlamailer.create', 'com_joomailermailchimpintegration')) ? 1 : 0);
define('JOOMLAMAILER_MANAGE_CAMPAIGNS', ($user->authorise('joomlamailer.campaigns', 'com_joomailermailchimpintegration')) ? 1 : 0);
define('JOOMLAMAILER_MANAGE_REPORTS', ($user->authorise('joomlamailer.reports', 'com_joomailermailchimpintegration')) ? 1 : 0);

// register classes to make sure we are using ours in case of naming conflicts
JLoader::register('jmModel', JPATH_COMPONENT . '/models/jmModel.php', true);
JLoader::register('jmView', JPATH_COMPONENT . '/views/jmView.php', true);
JLoader::register('jmController', JPATH_COMPONENT . '/controllers/jmController.php', true);

$input = JFactory::getApplication()->input;
if ($input->getWord('format') != 'raw') {

    JLoader::import('joomla.filesystem.file');
    if ($input->getWord('view') != 'installer'
        && JFile::exists(JPATH_ROOT . '/administrator/components/com_joomailermailchimpintegration/installer.init.ini')
    ) {
        JFactory::getApplication()->redirect('index.php?option=com_joomailermailchimpintegration&view=installer');
    }
    // Include css and js files
    $document = JFactory::getDocument();
    $document->addStyleSheet(JURI::root() . 'media/com_joomailermailchimpintegration/backend/css/styles.css');

    if (version_compare(JVERSION, '3.0.0') >= 0) {
        JHtml::_('jquery.framework');
    } else {
        $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/jquery.min.js');
    }
    $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/joomlamailer.js');
    $script = 'jQuery.noConflict();$j = jQuery.noConflict();
    !function($){
        $(document).ready(function(){
            joomlamailerJS.misc.baseUrl = "' . JURI::root() . '";
            joomlamailerJS.misc.adminUrl = "' . JURI::base() . '";
            joomlamailerJS.strings.pleaseSelectAList = "' . JText::_('JM_PLEASE_SELECT_A_LIST') . '";
            joomlamailerJS.strings.addingUsers = "' . JText::_('JM_ADDING_USERS') . '";
            joomlamailerJS.strings.done = "' . JText::_('JM_DONE') . '";
            joomlamailerJS.strings.confirmSyncHotnessNow = "' . JText::_('JM_SYN_HOTNESS_NOW') . '";
            joomlamailerJS.strings.confirmDraftDelete = "' . JText::_('JM_ARE_YOU_SURE_TO_DELETE_THE_SELECTED_DRAFT') . '";
            joomlamailerJS.strings.confirmSend_1 = "' . JText::_('JM_ARE_YOU_SURE') . '";
            joomlamailerJS.strings.confirmSend_2 = "' . JText::_('JM_CREDITS2') . '";
            joomlamailerJS.strings.errorInvalidEmails = "' . JText::_('JM_INVALID_EMAILS') . '";
            joomlamailerJS.strings.errorInvalidDate = "' . JText::_('JM_INVALID_DATE') . '";
            joomlamailerJS.strings.errorInvalidDeliveryDateInThePast = "' . JText::_('JM_DELIVERY_DATE_IN_THE_PAST') . '";
            joomlamailerJS.strings.errorTimewarpOnlyForPayed = "' . JText::_('JM_TIMEWARP_ONLY_FOR_PAID') . '";
            joomlamailerJS.strings.errorTimewarpNotScheduled24h = "' . JText::_('JM_TIMEWARP_MUST_BE_SCHEDULED') . '";
            joomlamailerJS.strings.errorEnterTestRecipients = "' . JText::_('JM_PLEASE_ENTER_TEST_RECIPIENTS') . '";
            joomlamailerJS.strings.errorNoRecipients = "' . JText::_('JM_NO_RECIPIENTS') . '";
            joomlamailerJS.strings.errorPleaseTestSegments = "' . JText::_('JM_PLEASE_TEST_SEGMENTS') . '";
            joomlamailerJS.strings.errorAutoresponderSetup = "' . JText::_('JM_AUTORESPONDER_SETUP_ERROR') . '";
            joomlamailerJS.strings.errorAutoresponderDays = "' . JText::_('JM_AUTORESPONDER_DAYS_ERROR') . '";
            joomlamailerJS.strings.errorCampaignName = "' . JText::_('JM_CAMPAIGN_NAME_REQUIRED') . '";
            joomlamailerJS.strings.errorCampaignNameSpecialChars = "' . JText::_('JM_CAMPAIGN_NAME_CONTAINS_SPECIAL_CHARACTERS') . '";
            joomlamailerJS.strings.errorBlankSubject = "' . JText::_('JM_PLEASE_ENTER_A_SUBJECT') . '";
            joomlamailerJS.strings.errorBlankFromName = "' . JText::_('JM_PLEASE_ENTER_A_FROM_NAME') . '";
            joomlamailerJS.strings.errorBlankFromEmail = "' . JText::_('JM_PLEASE_ENTER_A_FROM_EMAIL') . '";
            joomlamailerJS.strings.errorBlankReplyEmail = "' . JText::_('JM_PLEASE_ENTER_A_REPLY_EMAIL') . '";
            joomlamailerJS.strings.errorBlankConfirmationEmail = "' . JText::_('JM_PLEASE_ENTER_A_CONFIRMATION_EMAIL') . '";
            joomlamailerJS.strings.errorSubjectNoMergeTags = "' . JText::_('JM_NO_MERGE_TAGS_IN_SUBJECT') . '";
            joomlamailerJS.strings.errorSelectTemplate = "' . JText::_('JM_PLEASE_SELECT_A_TEMPLATE') . '";
            joomlamailerJS.helpers.ajaxLoader = "<img src=\"' . JURI::root() . 'media/com_joomailermailchimpintegration/backend/images/loader_16.gif\" width=\"16\" height=\"16\" style=\"margin: 0 0 0 10px;\"/>";
        });
    }(jQuery);';
    $document->addScriptDeclaration($script);

    if (version_compare(JVERSION, '3.0.0') >= 0) {
        JHtml::_('bootstrap.framework');
        JHtml::_('behavior.tabstate');
    } else {
        $document->addStyleSheet(JURI::root() . 'media/com_joomailermailchimpintegration/backend/css/styles_2.5.css');
        $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/bootstrap.js');
        $document->addStyleSheet(JURI::root() . 'media/com_joomailermailchimpintegration/backend/css/bootstrap.min.css');
        $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/tabs-state.js');
        JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html');
    }
}

// Require the base controller
require_once(JPATH_COMPONENT . '/controller.php');
require_once(JPATH_COMPONENT . '/libraries/Mailchimp/JoomlamailerMailchimp.php');
require_once(JPATH_COMPONENT . '/helpers/JoomlamailerMC.php');
require_once(JPATH_COMPONENT . '/helpers/CRMauth.php');
require_once(JPATH_COMPONENT . '/helpers/common.php');
require_once(JPATH_COMPONENT . '/helpers/permissions.php');

// Require specific controller if requested
$controller = $input->getCmd('controller');
if ($controller) {
    $path = JPATH_COMPONENT . '/controllers/' . $controller . '.php';
    if (file_exists($path)) {
        require_once($path);
    }
}

// Create the controller
$classname = 'joomailermailchimpintegrationController' . $controller;
$controller = new $classname();

// Perform the Request task
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
