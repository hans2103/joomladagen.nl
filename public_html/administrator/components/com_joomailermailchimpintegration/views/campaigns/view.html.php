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

jimport('joomla.cache.cache');

class joomailermailchimpintegrationViewCampaigns extends jmView {

    public function display($tpl = null) {
        if (!JOOMLAMAILER_MANAGE_REPORTS) {
            $this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration');
        }

        JToolBarHelper::title(JText::_('JM_NEWSLETTER_CAMPAIGN_STATS'), $this->getPageTitleClass());

        $this->setModel($this->getModelInstance('main'));
        $this->setModel($this->getModelInstance('campaignlist'));

        $cacheGroup = 'joomlamailerReports';

        $cacheOptions = array();
        $cacheOptions['cachebase'] = JPATH_ADMINISTRATOR . '/cache';
        $cacheOptions['lifetime'] = 31556926;
        $cacheOptions['storage'] = 'file';
        $cacheOptions['defaultgroup'] = 'joomlamailerReports';
        $cacheOptions['locking'] = false;
        $cacheOptions['caching'] = true;

        $cache = new JCache($cacheOptions);
        require_once(JPATH_COMPONENT . '/helpers/JoomlamailerCache.php');

        $layout = $this->input->getString('layout');
        $this->limit = $this->app->getUserStateFromRequest('global.list.limit', 'limit', $this->app->get('list_limit'), 'int');
        $this->limitstart = $this->app->getUserStateFromRequest($layout . '.limitstart', $layout . 'limitstart', 0, 'int');

        if ($layout != '') {
            JToolBarHelper::custom('goToCampaigns', 'chart', 'chart', 'JM_REPORTS', false);
        }

        if ($layout == 'clickedlinks') {
            $this->cid = $this->input->getString('cid', 0);
            $this->data = $this->getModel()->getClicks($this->cid, $this->limit, $this->limitstart);

            jimport('joomla.html.pagination');
            $this->pagination = new JPagination($this->data['total_items'], $this->limitstart, $this->limit, $layout);

        } else if ($layout == 'clickedlinkdetails') {
            $this->cid = $this->input->getString('cid', 0);
            $id = $this->input->getString('id');
            $this->details = $this->getModel()->getClickDetails($this->cid, $id);
            $this->data = $this->getModel()->getClickDetailsMembers($this->cid, $id, $this->limit, $this->limitstart);

            jimport('joomla.html.pagination');
            $this->pagination = new JPagination($this->data['total_items'], $this->limitstart, $this->limit, $layout);

        } else if ($layout == 'recipients') {
            $this->cid = $this->input->getString('cid', 0);
            $this->data = $this->getModel()->getRecipients($this->cid, $this->limit, $this->limitstart);

            jimport('joomla.html.pagination');
            $this->pagination = new JPagination($this->data['total_items'], $this->limitstart, $this->limit, $layout);

        } else if ($layout == 'abuse') {
            $this->cid = $this->input->getString('cid', 0);
            $this->data = $this->getModel()->getAbuse($this->cid, $this->limit, $this->limitstart);

            jimport('joomla.html.pagination');
            $this->pagination = new JPagination($this->data['total_items'], $this->limitstart, $this->limit, $layout);

        } else if ($layout == 'unsubscribes') {
            $this->cid = $this->input->getString('cid', 0);
            $this->data = $this->getModel()->getUnsubscribes($this->cid, $this->limit, $this->limitstart);

            jimport('joomla.html.pagination');
            $this->pagination = new JPagination($this->data['total_items'], $this->limitstart, $this->limit, $layout);

        } else {
            // force number of entries per page to avoid API timeouts
            $this->limit = 5;

            JFactory::getDocument()
                ->addStyleSheet(JURI::root() . 'media/com_joomailermailchimpintegration/backend/css/campaigns.css');

            $JoomlamailerMC = new JoomlamailerMC();
            if (!$JoomlamailerMC->pingMC()) {
                $user = JFactory::getUser();
                if ($user->authorise('core.admin', 'com_joomailermailchimpintegration')) {
                    JToolBarHelper::preferences('com_joomailermailchimpintegration', '450');
                    JToolBarHelper::spacer();
                }
            } else {
                JToolBarHelper::custom('create', 'save-new', 'save-new', 'JM_CREATE_CAMPAIGN', false, false);
                JToolBarHelper::spacer();

                /*JToolBarHelper::custom('analytics', 'chart', 'chart', 'Analytics360°', false, false);
                JToolBarHelper::spacer();*/
                $user = JFactory::getUser();
                if ($user->authorise('core.admin', 'com_joomailermailchimpintegration')) {
                    JToolBarHelper::preferences('com_joomailermailchimpintegration', '450');
                    JToolBarHelper::spacer();
                }

                //	JToolBarHelper::custom('delete', 'delete', 'delete', 'JM_DELETE_REPORT', true, false);

                // Campaign folders dropdown
                $folderId = $this->input->getString('folder_id', 0);
                $campaignFolders = array(array('id' => 0, 'name' => '- ' . JText::_('JM_SELECT_FOLDER') . ' -'));
                $getFolders = $this->getModel('campaignlist')->getFolders();
                if (!empty($getFolders['total_items'])) {
                    $campaignFolders = array_merge($campaignFolders, $getFolders['folders']);
                }
                $this->foldersDropDown = JHTML::_('select.genericlist', $campaignFolders, 'folder_id',
                    'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"', 'id', 'name' , $folderId);

                $cacheID = 'sent_campaigns_' . $this->limit . '_' . $this->limitstart . '_' . $folderId;
                if (!$cache->get($cacheID, $cacheGroup) || !$this->getModel()->isCaching()) {

                    $params = array('status' => 'sent');
                    if ($folderId) {
                        $params['folder_id'] = $folderId;
                    }
                    $campaigns = $this->getModel()->getCampaigns($params, $this->limit, $this->limitstart, 'send_time');

                    if ($campaigns['total_items'] > 0) {
                        foreach ($campaigns['campaigns'] as $index => $c) {
                            $campaigns['campaigns'][$index]['twitter'] = $this->getModel()->getEepUrlStats($c['id']);
                            $campaigns['campaigns'][$index]['locations'] = $this->getModel()->getLocationStats($c['id']);

                            $campaigns['campaigns'][$index]['stats'] = '';
                            $campaigns['campaigns'][$index]['advice'] = '';
                            $stats = $this->getModel()->getReport($c['id']);
                            if ($stats) {
                                $campaigns['campaigns'][$index]['stats'] = $stats;
                            }
                            $advice = $this->getModel()->getAdvice($c['id']);
                            if ($advice['total_items'] > 0) {
                                $campaigns['campaigns'][$index]['advice'] = $advice['advice'][0];
                            }
                        }
                    }
                    $cache->store(json_encode($campaigns), $cacheID, $cacheGroup);
                }

                $this->items = json_decode($cache->get($cacheID, $cacheGroup), true);

                // get timestamp of when the cache was modified
                $joomlamailerCache = new JoomlamailerCache('file');
                $this->cacheDate = $joomlamailerCache->getCreationTime($cacheID, $cacheGroup);

                jimport('joomla.html.pagination');
                $this->pagination = new JPagination($this->items['total_items'], $this->limitstart, $this->limit, $layout);
            }
        }

        parent::display($tpl);
        require_once(JPATH_COMPONENT . '/helpers/jmFooter.php');
    }
}
