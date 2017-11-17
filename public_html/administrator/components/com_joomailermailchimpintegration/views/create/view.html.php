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

class joomailermailchimpintegrationViewCreate extends jmView {

    public function display($tpl = null) {
        if (!JOOMLAMAILER_CREATE_DRAFTS) {
            $this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration');
        }

        JToolBarHelper::title(JText::_('JM_NEWSLETTER_CREATE_DRAFT'), $this->getPageTitleClass());

        JToolBarHelper::back();

        $this->user = JFactory::getUser();

        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi = $params->get('params.MCapi');
        $JoomlamailerMC = new JoomlamailerMC();

        if (!$MCapi) {
            if ($this->user->authorise('core.admin', 'com_joomailermailchimpintegration')) {
                JToolBarHelper::preferences('com_joomailermailchimpintegration', '350');
            }
        } else if (!$JoomlamailerMC->pingMC()) {
            if ($this->user->authorise('core.admin', 'com_joomailermailchimpintegration')) {
                JToolBarHelper::preferences('com_joomailermailchimpintegration', '350');
                JToolBarHelper::spacer();
            }
        } else {
            JHTML::_('behavior.modal');
            JHTML::_('behavior.tooltip');

            // Include css/js files
            $document = JFactory::getDocument();
            $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/joomlamailer.create.js');
            $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/joomlamailer.preview.js');
            $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/tablednd.js');
            $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/sorttable.js');

            if (version_compare(JVERSION, '3.0.0') < 0) {
                $document->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/joomlamailer.create_2_5.js');
            }

            // plugin support
            JPluginHelper::importPlugin('joomlamailer');
            $this->plugins = JEventDispatcher::getInstance();

            // get intro editor
            $this->editor = JEditor::getInstance(JFactory::getConfig()->get('editor'));
            $script = '!function($){
                $(document).ready(function(){
                    joomlamailerJS.create.getIntroContent = function() {
                        /**
                        * if-statement below is a workaround for buggy tinyMCE.setActive(), as we need active editor to 
                        * be "intro" right now.
                        * Apparently tinyMCE does not work well with more than one instance per page.
                        * For other editors only the part after the if-statement is required (editor-getContent).
                        **/
                        if (jQuery("#editorType").val() === "tinymce") {
                            tinyMCE.get("intro").execCommand("mceInsertContent", false, "");
                        }
                        
                        return ' . $this->editor->getContent('intro') . ';
                    }
                    joomlamailerJS.create.onSubmit = function() {' .
                        $this->editor->save('intro');
                        $submitformJavascript = $this->plugins->trigger('submitformJavascript');
                        foreach($submitformJavascript as $sj){
                            $script .= $sj;
                        }
            $script.= '}';
                    if ($this->input->getUint('text_only', 0)) {
                         $script .= "$('.create_sidebar').css('display', 'none');";
                    }
            $script.= '});
                }(jQuery);
                var includeComponents = new Object();
                var includeComponentsOptions = new Object();
                var includeComponentsFields = new Object();
                var sidebarElements = new Object();
                var postData = new Object();
                var socialIcons = new Object();';
            $document->addScriptDeclaration($script);

            $this->categories = $this->get('Categories');
            $this->mergeFields = $this->get('MergeFieldsAll');

            $this->K2Installed = $this->get('K2Installed');
            if ($this->K2Installed) {
                $this->allk2cat = $this->getModel()->getK2Cat();
            }

            // Campaign folders dropdown
            $campaignFolders = array(array('id' => 0, 'name' => JText::_('JM_UNFILED')));
            $getFolders = $this->get('Folders');
            if (!empty($getFolders['total_items'])) {
                $campaignFolders = array_merge($campaignFolders, $getFolders['folders']);
            }
            $this->foldersDropDown = JHTML::_('select.genericlist', $campaignFolders, 'folder_id', '', 'id', 'name',
                $this->input->getString('folder_id'));
        }

        parent::display($tpl);
        require_once(JPATH_COMPONENT . '/helpers/jmFooter.php');
    }
}
