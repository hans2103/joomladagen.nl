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

class joomailermailchimpintegrationViewSend extends jmView {

    public function display($tpl = null) {
        $user = JFactory::getUser();
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi = $params->get('params.MCapi');
        $JoomlamailerMC = new JoomlamailerMC();

        JToolBarHelper::title(JText::_('JM_NEWSLETTER_SEND_CAMPAIGN'), $this->getPageTitleClass());
        if (!$MCapi) {
            if ($user->authorise('core.admin', 'com_joomailermailchimpintegration')) {
                JToolBarHelper::preferences('com_joomailermailchimpintegration', '350');
                JToolBarHelper::spacer();
            }
        } else {
            if (!$JoomlamailerMC->pingMC()) {
                if ($user->authorise('core.admin', 'com_joomailermailchimpintegration')) {
                    JToolBarHelper::preferences('com_joomailermailchimpintegration', '350');
                    JToolBarHelper::spacer();
                }
            } else {
                JFactory::getDocument()
                    ->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/jquery.clockpick.1.2.9.min.js')
                    ->addStyleSheet(JURI::root() . 'media/com_joomailermailchimpintegration/backend/css/jquery.clockpick.1.2.9.css')
                    ->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/joomlamailer.send.js')
                    ->addScript(JURI::root() . 'media/com_joomailermailchimpintegration/backend/js/joomlamailer.sync.js');

                $AECambraVM = $this->get('AecAmbraVm');
                if ($AECambraVM){
                    JToolBarHelper::custom('syncHotness', 'hotness', 'hotness', 'Sync Hotness', false, false);
                    JToolBarHelper::spacer();
                }

                if ($this->input->getString('campaign', 0)){
                    JToolBarHelper::custom('send', 'send', 'send', 'JM_SEND', false, false);
                    JToolBarHelper::spacer();
                }

                // assign data to the template
                $this->drafts = $this->get('Drafts');
                $this->sentCampaigns = $this->get('SentCampaigns');
                $this->clientDetails = $this->get('ClientDetails');
                $this->lists = $this->get('lists');

                $this->campaignStamp = $this->input->getUint('campaign', 0);

                $this->campaignDetails = false;
                if ($this->campaignStamp) {
                    $query = $this->db->getQuery(true)
                        ->select('*')
                        ->from('#__joomailermailchimpintegration_campaigns')
                        ->where($this->db->qn('creation_date') . ' = ' . $this->db->q($this->campaignStamp));
                    $this->campaignDetails = $this->db->setQuery($query)->loadObject();
                }
            }
        }

        parent::display($tpl);
        require_once(JPATH_COMPONENT . '/helpers/jmFooter.php');
    }
}
