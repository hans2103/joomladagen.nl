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

class joomailermailchimpintegrationViewTemplates extends jmView {

    public function display($tpl = null) {
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi = $params->get('params.MCapi');
        $JoomlamailerMC = new JoomlamailerMC();

        if (!$MCapi || !$JoomlamailerMC->pingMC()) {
            JToolBarHelper::title(  JText::_('JM_NEWSLETTER').' : '.JText::_('JM_UPLOAD_TEMPLATE'), $this->getPageTitleClass());
            $user = JFactory::getUser();
            if ($user->authorise('core.admin', 'com_joomailermailchimpintegration')) {
                JToolBarHelper::preferences('com_joomailermailchimpintegration', '350');
                JToolBarHelper::spacer();
            }
        } else {
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');

            $document = JFactory::getDocument();
            $script = '!function($){
    $(document).ready(function(){
        joomlamailerJS.strings.templateDownloadError = "' . JText::_('JM_TEMPLATE_DOWNLOAD_ERROR', true) . '";
        joomlamailerJS.strings.templateWidthOf = "' . JText::_('JM_TEMPLATE_WIDTH_OF', true) . '";
        joomlamailerJS.strings.uploadButtonText = "' . JText::_('JM_UPLOAD_HEADER_IMAGE', true) . '";
        joomlamailerJS.strings.errorInvalidFileType = "' . JText::_('JM_INVALID_FILE_TYPE', true) . '";
        joomlamailerJS.strings.allowedFileTypes = "' . JText::_('JM_ALLOWED_EXTENSIONS', true) . '";
        joomlamailerJS.strings.clickToEdit = "' . JText::_('JM_CLICK_TO_EDIT', true) . '";
        joomlamailerJS.strings.confirmClearPosition = "' . JText::_('JM_ARE_YOU_SURE_TO_DELETE_EVERYTHING_FROM_THE', true) . '";
        joomlamailerJS.strings.position = "' . JText::_('JM_POSITION_DELETE', true) . '";
        joomlamailerJS.strings.confirmOverwriteTemplate = "' . JText::_('JM_OVERWRITE_TEMPLATE', true) . '";
        joomlamailerJS.strings.errorTemplateName = "' . JText::_('JM_INVALID_TEMPLATE_NAME_SUPPLIED', true) . '";
    });
}(jQuery);';
            $document->addScriptDeclaration($script);
            $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/joomlamailer.templates.js');

            $this->layout = $this->input->getCmd('layout');
            if ($this->layout == 'upload') {
                JToolBarHelper::title(JText::_('JM_NEWSLETTER') . ' : ' . JText::_('JM_UPLOAD_TEMPLATE'), $this->getPageTitleClass());
                JToolBarHelper::custom('startUpload', 'upload', 'upload', 'JM_START_UPLOAD', false, false);
                JToolBarHelper::spacer();
                JToolBarHelper::cancel();
                JToolBarHelper::spacer();
            } else if ($this->layout == 'edit') {
                JToolBarHelper::title(JText::_('JM_NEWSLETTER') . ' : ' . JText::_('JM_EDIT_TEMPLATE'), $this->getPageTitleClass());
                JToolBarHelper::save();
                JToolBarHelper::spacer();
                JToolBarHelper::cancel();
                JToolBarHelper::spacer();

                $document->addStyleSheet(JURI::root() . 'media/com_joomailermailchimpintegration/backend/css/templateEditor.css');
                $document->addStyleSheet(JURI::root() . 'media/com_joomailermailchimpintegration/backend/css/picker.css');
                $document->addStyleSheet(JURI::root() . 'media/com_joomailermailchimpintegration/backend/css/fileuploader.css');

                $document->addScript(JURI::root().'media/com_joomailermailchimpintegration/backend/js/jquery-ui-1.10.4.custom.min.js');
                $document->addScript(JURI::root().'media/com_joomailermailchimpintegration/backend/js/jquery.jeditable.js');
                $document->addScript(JURI::root().'media/com_joomailermailchimpintegration/backend/js/picker.js');
                $document->addScript(JURI::root().'media/com_joomailermailchimpintegration/backend/js/fileuploader.js');

                $this->templateFolder = $this->input->get('template', array());
                $this->templateFolder = urldecode($this->templateFolder[0]);

                // create tmp copy of the edited template
                $this->getModel('templates')->createTmpCopy($this->templateFolder);

                $this->tmpFile = JPATH_SITE . '/tmp/' . $this->templateFolder . '/template.html';
                $this->tmpFileURL = '../tmp/' . $this->templateFolder . '/template.html';

                // Get data from the model
                $this->palettes = $this->get('Palettes');
            } else {
                JToolBarHelper::title(JText::_('JM_NEWSLETTER') . ' : ' . JText::_('JM_EMAIL_TEMPLATES'), $this->getPageTitleClass());
                JToolBarHelper::addNew('add', 'JM_UPLOAD_TEMPLATE');
                JToolBarHelper::spacer();
                JToolBarHelper::editList();
                JToolBarHelper::spacer();
                JToolBarHelper::deleteList(JText::_('JM_ARE_YOU_SURE_TO_DELETE_THIS_TEMPLATE'));
                JToolBarHelper::spacer();

                $this->templateFolders = $this->get('templateFolders');
            }
        }

        parent::display($tpl);
        require_once(JPATH_COMPONENT . '/helpers/jmFooter.php');
    }
}
