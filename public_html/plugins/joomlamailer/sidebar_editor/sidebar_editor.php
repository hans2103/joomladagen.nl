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
defined('_JEXEC') or die('Restricted access');

class plgJoomlamailerSidebar_editor extends JPlugin {

    protected $app;

    private $id = 'sidebarEditor';
    private static $editor = null;

    public function getSidebarElement() {
        if (!$this->app) {
            $this->app = JFactory::getApplication();
        }

        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_sidebar_editor', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_sidebar_editor', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_sidebar_editor', JPATH_ADMINISTRATOR, null, true);

        $data = array();
        $data['title'] = JText::_('JM_SIDEBAR_EDITOR');

        $sidebarcontent	= urldecode($this->app->input->get($this->id, false, 'RAW'));
        $buttons2exclude = array('pagebreak', 'readmore');

        $data['element']  = $this->getEditor()->display($this->id, $sidebarcontent, '100%', '250', '60', '10', $buttons2exclude);
        $data['element'] .= '<script type="text/javascript">
            sidebarElements[Object.keys(sidebarElements).length] = "' . $this->id . '";
            /**
            * if-statement in the next line is a workaround for buggy tinyMCE.setActive(), as we need active editor to 
            * be "$this->id" right now.
            * Apparently tinyMCE does not work well with more than one instance per page.
            * For other editors only the part behind the if-statement is required (editor-getContent).
            **/
            postData["' . $this->id . '"] = "if (jQuery(\'#editorType\').val() === \'tinymce\') {tinyMCE.get(\'' . $this->id . '\').execCommand(\'mceInsertContent\', false, \'\');}' . trim($this->getEditor()->getContent($this->id)) . '";
        </script>';

        return $data;
    }

    public function insert_sidebarEditor ($sidebarData, $template) {
        $abs_path  = '$1="' . JURI::root() . '$2$3';
        $sidebarData = urldecode($sidebarData[$this->id]);
        if (get_magic_quotes_gpc()) {
            $sidebarData = stripslashes($sidebarData);
        }
        $sidebarData = preg_replace('#(href|src)="([^:"]*)("|(?:(?:%20|\s|[.]|\+)[^"]*"))#i', $abs_path, $sidebarData);
        $template = str_ireplace('<#sidebar#>', $sidebarData, $template);

        return $template;
    }

    public function insert (&$template) {
        if ($this->app) {
            $this->app = JFactory::getApplication();
        }

        $sidebarData = array();
        $sidebarData[$this->id] = $this->app->input->get($this->id, '', 'RAW');
        $template = $this->insert_sidebarEditor($sidebarData, $template);
    }

    public function submitformJavascript() {
        return "\n" . $this->getEditor()->save($this->id) . "\n";
    }

    public function addPlaceholderToTemplateEditor() {
        // load language files. include en-GB as fallback
        $jlang = JFactory::getLanguage();
        $jlang->load('plg_joomlamailer_sidebar_editor', JPATH_ADMINISTRATOR, 'en-GB', true);
        $jlang->load('plg_joomlamailer_sidebar_editor', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
        $jlang->load('plg_joomlamailer_sidebar_editor', JPATH_ADMINISTRATOR, null, true);

        $data = array();
        $data['js'] = 'joomlamailerJS.templates.placeholders["' . $this->id . '"] = \'<#sidebar#><br />\';';
        $data['checkbox'] = '<input type="checkbox" class="phCb" value="' . $this->id . '" id="' . $this->id . '"/><label for="' . $this->id . '">' . JText::_('JM_SIDEBAR_EDITOR') . '</label>';

        return $data;
    }

    private function getEditor() {
        if (self::$editor === null) {
            self::$editor = JEditor::getInstance(JFactory::getConfig()->get('editor'));
        }

        return self::$editor;
    }
}
