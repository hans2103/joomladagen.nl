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
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgJoomlamailerMyspace_icon extends JPlugin {
    
    protected $app;

    // specify an unique id following php variable naming conventions (Do not use hyphens!)
    // http://php.net/manual/en/language.variables.basics.php
    private $id = 'myspace';

    public function getSocialIcon() {
        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_myspace_icon', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_myspace_icon', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_myspace_icon', JPATH_ADMINISTRATOR, null, true);

        $params = JComponentHelper::getParams( 'com_joomailermailchimpintegration' );
        $myspaceName = $params->get('params.myspace_name', '');
        $myspace = $this->app->input->getString('ms', false);
        if (!$myspace) {
            $myspace = $this->app->input->getString($this->id, $myspaceName);
        }

        $data = array();
        $data['title']	 = '<label for="' . $this->id . '">' . JText::_('JM_MYSPACE_ICON') . ':</label>';
        $data['element'] = '<input class="text_area" type="text" name="' . $this->id . '" id="' . $this->id .
            '" size="25" maxlength="250" value="' . $myspace . '" style="margin-right: 20px;" />' .
            '<div class="inputInfo">' . JText::_('JM_MYSPACE_INFO') . '</div>' .
            '<script type="text/javascript">socialIcons[Object.keys(socialIcons).length] = "' . $this->id . '";</script>';

        return $data;
    }

    public function insert_myspace($value, $template) {
        $regex = '!<#myspace#>(.*)<#/myspace#>!is';
        preg_match($regex, $template, $placeholder);

        if (isset($placeholder[0])) {
            $result = str_ireplace( '<#myspace-name#>', $value, $placeholder[0]);
            $toReplace = array('<#myspace#>', '<#/myspace#>');
            $result = str_ireplace($toReplace, '', $result);

            $result = ($value) ? $result : '';
            $template = preg_replace('!<#myspace#[^>]*>(.*?)<#/myspace#>!is', $result, $template);
        }

        return $template;
    }

    public function insert(&$template) {
        $value = $this->app->input->getString($this->id);
        $template = $this->insert_myspace($value, $template);
    }

    public function addPlaceholderToTemplateEditor() {
        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_myspace_icon', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_myspace_icon', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_myspace_icon', JPATH_ADMINISTRATOR, null, true);

        $data = array();
        $data['js'] = 'joomlamailerJS.templates.placeholders["' . $this->id . '"] = \'<#myspace#><a href="http://www.myspace.com/<#myspace-name#>">' .
            '<img src="myspace.png" id="myspaceIcon" /></a><#/myspace#>\';';
        $data['checkbox'] = '<input type="checkbox" class="phCb" value="' . $this->id . '" id="' . $this->id . '"/>' .
            '<label for="' . $this->id . '">' . JText::_('JM_MYSPACE_ICON') . '</label>';

        return $data;
    }

    public function addImageUploader() {
        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_myspace_icon', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_myspace_icon', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_myspace_icon', JPATH_ADMINISTRATOR, null, true);

        global $imageUploader;
        if (!$imageUploader) {
            $imageUploader = 1;
        }
        $data = array();
        $data['js'] = 'joomlamailerJS.templates.createUploader("' . $this->id . 'Upload");';
        $data['html'] = '<div id="' . $this->id . 'Upload" class="imageUploader" title="' . JText::_('JM_UPLOAD_MYSPACE_ICON') . '"></div>';
        $iconPath = 'media/plg_joomlamailer_myspace_icon/images/addms.png';
        $data['css'] = '#' . $this->id . 'Upload .qq-upload-button { background: url(' . JURI::root() . $iconPath . ') no-repeat 0 0; z-index:' . $imageUploader . '; }';
        $imageUploader++;

        return $data;
    }
}
