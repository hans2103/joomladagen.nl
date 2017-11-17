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

jimport('joomla.application.component.view');

if (version_compare(JVERSION, '3.0', 'ge')) {
    class jmViewHelper extends JViewLegacy {
        public function __construct($config = array()) {
            parent::__construct($config);
        }
    }
} else {
    class jmViewHelper extends JView {
        public function __construct($config = array()) {
            parent::__construct($config);
        }
    }
}

class jmView extends jmViewHelper {

    protected $app;
    protected $input;
    protected $db;
    protected $session;
    public $sidebar = '';

    public function __construct($config = array()) {
        parent::__construct($config);

        $this->app = JFactory::getApplication();
        $this->input = $this->app->input;
        $this->db = JFactory::getDBO();
        $this->session = JFactory::getSession();
    }

    public function display($tpl = null) {
        $this->createSidebar();
        $this->sidebar = '<div id="j-sidebar-container" class="span2">' . JHtmlSidebar::render() . '</div>'
            . '<div id="j-main-container" class="span10">';

        parent::display($tpl);
    }

    public function getPageTitleClass() {
        return (version_compare(JVERSION, '3.0', 'ge')) ? 'mc_title_logo' : 'mc_title_logo_25';
    }

    public function getModelInstance($model) {
        if (version_compare(JVERSION, '3.0', 'ge')) {
            return JModelLegacy::getInstance($model, 'joomailermailchimpintegrationModel');
        } else {
            return JModel::getInstance($model, 'joomailermailchimpintegrationModel');
        }
    }

    private function createSidebar() {
        // create meta menu
        $ext = JFactory::getApplication()->input->getWord('view', 'main');
        if (in_array($ext, array('subscribers', 'joomailermailchimpintegration'))) {
            $ext = 'lists';
        }

        $subMenu = array();
        $subMenu['JM_DASHBOARD'] = 'main';
        if (JOOMLAMAILER_MANAGE_LISTS) {
            $subMenu['JM_LISTS'] = 'lists';
        }
        if (JOOMLAMAILER_MANAGE_CAMPAIGNS) {
            $subMenu['JM_CAMPAIGNS'] = 'campaignlist';
        }
        if (JOOMLAMAILER_MANAGE_REPORTS) {
            $subMenu['JM_REPORTS'] = 'campaigns';
        }

        foreach ($subMenu as $name => $extension) {
            JHtmlSidebar::addEntry(JText::_($name), 'index.php?option=com_joomailermailchimpintegration&view=' . $extension
                . '" onclick="joomlamailerJS.functions.preloader()', $extension == $ext);
        }
    }
}
