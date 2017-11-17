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

class joomailermailchimpintegrationViewCampaignlist extends jmView {

    public function display($tpl = null) {
        if (!JOOMLAMAILER_CREATE_DRAFTS && !JOOMLAMAILER_MANAGE_CAMPAIGNS) {
            $this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration');
        }

        JToolBarHelper::title(JText::_('JM_NEWSLETTER_CAMPAIGNS'), $this->getPageTitleClass());

        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $MCapi = $params->get('params.MCapi');
        $JoomlamailerMC = new JoomlamailerMC();

        if ($MCapi && $JoomlamailerMC->pingMC()) {
            if (JOOMLAMAILER_CREATE_DRAFTS) {
                JToolBarHelper::custom('create', 'save-new', 'save-new', 'JM_CREATE_CAMPAIGN', false);
                JToolBarHelper::spacer();
            }

            $filter = $this->input->getString('filter_status', 'sent', '', 'string');
            if (JOOMLAMAILER_CREATE_DRAFTS && !JOOMLAMAILER_MANAGE_CAMPAIGNS) {
                $filter = 'save';
                $this->input->set('filter_status', 'save');
            } else if (!JOOMLAMAILER_CREATE_DRAFTS && JOOMLAMAILER_MANAGE_CAMPAIGNS) {
                if ($filter == 'save') {
                    $this->input->set('filter_status', 'sent');
                }
            }
            if ($filter == 'save') {
                if (JOOMLAMAILER_CREATE_DRAFTS) {
                    JToolBarHelper::editList();
                    JToolBarHelper::spacer();
                }
                if (JOOMLAMAILER_CREATE_DRAFTS) {
                    JToolBarHelper::deleteList(JText::_('JM_ARE_YOU_SURE_TO_DELETE_THE_SELECTED_CAMPAIGNS'));
                    JToolBarHelper::spacer();
                }
                if (JOOMLAMAILER_MANAGE_CAMPAIGNS) {
                    JToolBarHelper::custom('send', 'send', 'send', 'JM_SEND', true);
                    JToolBarHelper::spacer();
                }
            } else if ($filter == 'schedule') {
                if (JOOMLAMAILER_MANAGE_CAMPAIGNS) {
                    JToolBarHelper::deleteList(JText::_('JM_ARE_YOU_SURE_TO_DELETE_THE_SELECTED_CAMPAIGNS'));
                    JToolBarHelper::spacer();
                }
                JToolBarHelper::custom('unschedule', 'clock', 'clock', 'JM_UNSCHEDULE', true);
                JToolBarHelper::spacer();
                // you can only pause autoresponder and rss campaigns
                //  JToolBarHelper::custom('pause', 'pause', 'pause', 'Pause', true, false);
            } else if ($filter == 'sent') {
                JToolBarHelper::custom('copyCampaign', 'copy', 'copy', 'JM_REPLICATE', true);
                JToolBarHelper::spacer();
                if (JOOMLAMAILER_MANAGE_REPORTS) {
                    JToolBarHelper::deleteList(JText::_('JM_ARE_YOU_SURE_TO_DELETE_THE_SELECTED_CAMPAIGNS'));
                    JToolBarHelper::spacer();
                }
            } else if ($filter == 'sending') {
                if (JOOMLAMAILER_MANAGE_REPORTS) {
                    JToolBarHelper::deleteList(JText::_('JM_ARE_YOU_SURE_TO_DELETE_THE_SELECTED_CAMPAIGNS'));
                    JToolBarHelper::spacer();
                }
                if (JOOMLAMAILER_MANAGE_CAMPAIGNS) {
                    JToolBarHelper::custom('pause', 'pause', 'pause', 'JM_PAUSE', true);
                    JToolBarHelper::spacer();
                }
            } else if ($filter == 'paused') {
                if (JOOMLAMAILER_MANAGE_REPORTS) {
                    JToolBarHelper::deleteList(JText::_('JM_ARE_YOU_SURE_TO_DELETE_THE_SELECTED_CAMPAIGNS'));
                    JToolBarHelper::spacer();
                }
                if (JOOMLAMAILER_MANAGE_CAMPAIGNS) {
                    JToolBarHelper::custom('resume', 'play', 'play', 'JM_RESUME', true);
                    JToolBarHelper::spacer();
                }
            }
        }

        if (JOOMLAMAILER_MANAGE_REPORTS) {
            $folderId = $this->input->getString('folder_id', 0);
            $campaignFolders = array(
                array(
                    'id'   => '',
                    'name' => '*** ' .  ucfirst(JText::_('JM_ALL')) . ' ***'
                )/*, ==> "unfiled" is currently not supported by the API
                array(
                    'id'   => '0',
                    'name' => JText::_('JM_UNFILED')
                )*/
            );
            $getFolders = $this->get('Folders');
            if (!empty($getFolders['total_items'])) {
                $campaignFolders = array_merge($campaignFolders, $getFolders['folders']);
            }
            $this->foldersDropDown = JHTML::_('select.genericlist', $campaignFolders, 'folder_id',
                'onchange="document.adminForm.submit();"', 'id', 'name', $folderId);
        }

        // Get data from the model
        $this->campaigns = $this->get('Campaigns');

        $this->pagination = $this->get('Pagination');

        parent::display($tpl);
        require_once(JPATH_COMPONENT . '/helpers/jmFooter.php');
    }
}
