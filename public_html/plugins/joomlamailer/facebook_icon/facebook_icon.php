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

class plgJoomlamailerFacebook_icon extends JPlugin {

    protected $app;

    // specify an unique id following php variable naming conventions (Do not use hyphens!)
    // http://php.net/manual/en/language.variables.basics.php
    private $id = 'facebook';

    public function getSocialIcon() {
        $this->app = JFactory::getApplication();

	    // load language files. include en-GB as fallback
	    $jlang = JFactory::getLanguage();
	    $jlang->load('plg_joomlamailer_facebook_icon', JPATH_ADMINISTRATOR);

	    $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
	    $facebookLink = $params->get('params.facebook_link', '');
	    $value = $this->app->input->getString('fb', false);
	    if (!$value) {
            $value = $this->app->input->getString($this->id, $facebookLink);
        }

	    $data = array();
	    $data['title']	 = '<label for="' . $this->id . '">' . JText::_('JM_FACEBOOK_LINK') . ':</label>';
	    $data['element'] = '<input class="text_area" type="text" name="' . $this->id . '" id="' . $this->id .
            '" size="25" maxlength="250" value="' . $value . '" style="margin-right: 20px;" />' .
		    '<div class="inputInfo">' . JText::_('JM_FACEBOOK_INFO') . '</div>' .
		    '<script type="text/javascript">socialIcons[Object.keys(socialIcons).length] = "' . $this->id . '";</script>';

	    return $data;
    }

    public function insert_facebook($value, $template) {
	    $regex = '!<#facebook#>(.*)<#/facebook#>!is';
	    if (preg_match($regex, $template, $placeholder) && isset($placeholder[0])) {
	        $result = str_ireplace('<#facebook-url#>', $value, $placeholder[0]);
	        $to_replace  = array('<#facebook#>', '<#/facebook#>');
	        $result = str_ireplace($to_replace, '', $result);

	        $result = ($value) ? $result : '';
	        $template = preg_replace('!<#facebook#[^>]*>(.*?)<#/facebook#>!is', $result, $template);
	    }

	    return $template;
    }

    public function insert(&$template) {
        $this->app = JFactory::getApplication();
	    $value = $this->app->input->getString($this->id);
	    $template = $this->insert_facebook($value, $template);
    }

    public function addPlaceholderToTemplateEditor() {
	    // load language files. include en-GB as fallback
	    $jlang = JFactory::getLanguage();
	    $jlang->load('plg_joomlamailer_facebook_icon', JPATH_ADMINISTRATOR);

	    $data = array();
	    $data['js'] = 'joomlamailerJS.templates.placeholders["' . $this->id . '"] = \'<#facebook#><a href="<#facebook-url#>"><img src="facebook.png" id="facebookIcon" /></a><#/facebook#>\';';
	    $data['checkbox'] = '<input type="checkbox" class="phCb" value="' . $this->id . '" id="' . $this->id .
            '"/><label for="' . $this->id . '">' . JText::_('JM_FACEBOOK_ICON') . '</label>';

	    return $data;
    }

    public function addImageUploader() {
	    // load language files. include en-GB as fallback
	    $jlang = JFactory::getLanguage();
	    $jlang->load('plg_joomlamailer_facebook_icon', JPATH_ADMINISTRATOR);

	    global $imageUploader;
	    if (!$imageUploader) {
            $imageUploader = 1;
        }
	    $data = array();
	    $data['js'] = 'joomlamailerJS.templates.createUploader("' . $this->id . 'Upload");';
	    $data['html'] = '<div id="' . $this->id . 'Upload" class="imageUploader" title="' . JText::_('JM_UPLOAD_FACEBOOK_ICON') . '"></div>';
	    $iconPath = 'media/plg_joomlamailer_facebook_icon/images/addfb.png';
	    $data['css'] = '#' . $this->id . 'Upload .qq-upload-button { background: url(' . JURI::root() . $iconPath . ') no-repeat 0 0; z-index:' . $imageUploader . '; }';
	    $imageUploader++;

	    return $data;
    }
}
