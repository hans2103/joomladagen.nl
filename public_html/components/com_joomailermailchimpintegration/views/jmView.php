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

    public function __construct($config = array()) {
        parent::__construct($config);

        $this->app = JFactory::getApplication();

        // Include the css file
        $document = JFactory::getDocument();
        $document->addStyleSheet('media/com_joomailermailchimpintegration/frontend/css/default.css', 'text/css', 'screen');
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
}
