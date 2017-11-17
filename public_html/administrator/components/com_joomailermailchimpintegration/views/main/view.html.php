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

class joomailermailchimpintegrationViewMain extends jmView {

    public function display($tpl = null) {
        JToolBarHelper::title(JText::_('JM_NEWSLETTER'), $this->getPageTitleClass());

        $this->params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi = $this->params->get('params.MCapi');
        $JoomlamailerMC = new JoomlamailerMC();
        $clientDetails = $JoomlamailerMC->pingMC();

        JToolBarHelper::custom('extensions', 'power-cord', 'power-cord', 'JM_EXTENSIONS', false);
        JToolBarHelper::spacer();

        $bar = JToolBar::getInstance('toolbar');
        if ($MCapi && $clientDetails) {
            list(, $dc) = explode('-', $MCapi);
            $url = 'https://' . $dc . '.admin.mailchimp.com';
            if (JOOMLAMAILER_CREATE_DRAFTS || JOOMLAMAILER_MANAGE_CAMPAIGNS) {
                JToolBarHelper::custom('templates', 'file-2', 'file-2', 'JM_EMAIL_TEMPLATES', false);
                JToolBarHelper::spacer();
            }
        } else {
            $url = 'https://login.mailchimp.com/';
        }
        $bar->appendButton('Link', 'link', 'JM_ACCOUNT_SETTINGS', $url);
        JToolBarHelper::spacer();

        $user = JFactory::getUser();
        if ($user->authorise('core.admin', 'com_joomailermailchimpintegration')) {
            JToolBarHelper::preferences('com_joomailermailchimpintegration', '450');
            JToolBarHelper::spacer();
        }

        if ($MCapi && $clientDetails) {
            $this->details = $clientDetails;
            $this->drafts = $this->get('Drafts');

            $campaigns = $this->get('Campaigns');
            $this->campaigns = $campaigns['campaigns'];

            $this->mcBlogFeed = $this->get('McBlog');
        }

        parent::display($tpl);
        require_once(JPATH_COMPONENT . '/helpers/jmFooter.php');
    }
}
