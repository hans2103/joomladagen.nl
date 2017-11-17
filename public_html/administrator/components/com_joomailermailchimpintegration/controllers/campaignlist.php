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

class joomailermailchimpintegrationControllerCampaignlist extends joomailermailchimpintegrationController {

     public function __construct($config = array()) {
        parent::__construct($config);

        $this->registerTask('add' , 'edit');
    }

    public function edit() {
        $cid = $this->input->get('cid');

        $query = $this->db->getQuery(true);
        $query->select($this->db->qn(array('cdata', 'folder_id')))
            ->from('#__joomailermailchimpintegration_campaigns')
            ->where($this->db->qn('creation_date') . ' = ' . $this->db->q($cid[0]));
        $this->db->setQuery($query);
        $result = $this->db->loadAssocList();
        $cdata = json_decode($result[0]['cdata']);

        $this->input->set('cid', $cid[0]);
        foreach ($cdata as $k => $v) {
            $this->input->set($k, $v);
        }
        $this->input->set('view',   'create');
        $this->input->set('action', 'edit');
        $this->input->set('layout', 'default' );
        $this->input->set('hidemainmenu', 0);
        $this->input->set('offset', 0);
        parent::display();
    }

    public function send() {
        $cid = $this->input->get('cid');
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=send&campaign=' . $cid[0]);
    }

    public function unschedule() {
        $error = false;
        $cid = $this->input->get('cid');

        if (empty($cid)) {
            $this->app->enqueueMessage(JText::_('JM_INVALID_CAMPAIGNID'), 'error');
            $error = true;
        } else {
            foreach ($cid as $c) {
                try {
                    $this->getModel('campaignlist')->getMcObject()->campaigns(array('campaign_id' => $c), 'DELETE');

                    $query = $this->db->getQuery(true)
                        ->update('#__joomailermailchimpintegration_campaigns')
                        ->set($this->db->qn('sent') . ' = ' . $this->db->q(0))
                        ->set($this->db->qn('cid') . ' = ""')
                        ->where($this->db->qn('cid') . ' = ' . $this->db->q($c));
                    $this->db->setQuery($query)->execute();
                } catch (Exception $e) {
                    $this->app->enqueueMessage($e->getMessage(), 'error');
                    $error = true;
                }
            }
        }

        if ($error == false) {
            $this->getModel('main')->emptyCache('joomlamailerReports');
            $this->app->enqueueMessage(JText::_('JM_CAMPAIGNS_UNSCHEDULED'));
        }

        $link = 'index.php?option=com_joomailermailchimpintegration&view=campaignlist&filter_status='
            . $this->input->getString('filter_status', 'sent');
        $this->app->redirect($link);
    }

    // you can only pause autoresponder and rss campaigns
    public function pause(){
        $error = false;
        $cid = $this->input->get('cid');

        if (empty($cid)) {
            $this->app->enqueueMessage(JText::_('JM_INVALID_CAMPAIGNID'), 'error');
            $error = true;
        } else {
            foreach($cid as $c){
                try {
                    $this->getModel('campaignlist')->getMcObject()->campaignActions($c, 'pause');
                } catch (Exception $e) {
                    $this->app->enqueueMessage($e->getMessage(), 'error');
                    $error = true;
                }
            }
        }

        if ($error == false) {
            $this->getModel('main')->emptyCache('joomlamailerReports');
            $this->app->enqueueMessage(JText::_('JM_CAMPAIGNS_PAUSED'));
        }

        $link = 'index.php?option=com_joomailermailchimpintegration&view=campaignlist&filter_status='
            . $this->input->getString('filter_status', 'sent');
        $this->app->redirect($link);
    }

    public function resume() {
        $error = false;
        $cid = $this->input->get('cid');

        if (empty($cid)) {
            $this->app->enqueueMessage(JText::_('JM_INVALID_CAMPAIGNID'), 'error');
            $error = true;
        } else {
            foreach ($cid as $c) {
                try {
                    $this->getModel('campaignlist')->getMcObject()->campaignActions($c, 'resume');
                } catch (Exception $e) {
                    $this->app->enqueueMessage($e->errorMessage(), 'error');
                    $error = true;
                }
            }
        }

        $link = 'index.php?option=com_joomailermailchimpintegration&view=campaignlist';
        if ($error == false) {
            $this->getModel('main')->emptyCache('joomlamailerReports');
            $this->app->enqueueMessage(JText::_('JM_CAMPAIGNS_RESUMED'));
            $link .= '&filter_status=sending';
        } else {
            $link .= '&filter_status=paused';
        }

        $this->app->redirect($link);
    }

    public function copyCampaign() {
        $cid = $this->input->get('cid');
        $this->input->set('cid', $cid[0]);
        require_once(JPATH_COMPONENT . '/controllers/main.php');
        $mainController = new joomailermailchimpintegrationControllerMain();
        $mainController->copyCampaign();
    }

    public function remove() {
        $error = false;
        $status = $this->input->getString('filter_status', '');
        $cid = $this->input->get('cid');

        if (empty($cid)) {
            $this->app->enqueueMessage(JText::_('JM_INVALID_CAMPAIGNID'), 'error');
            $error = true;
        } else {
            if ($status == 'save') {
                jimport('joomla.filesystem.file');
                jimport('joomla.client.helper');
                JClientHelper::setCredentialsFromRequest('ftp');
                $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
                $archiveDir = $params->get('params.archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive');
                $path = JPATH_SITE . $archiveDir . '/';
                foreach($cid as $c){
                    $query = $this->db->getQuery(true)
                        ->select($this->db->qn('name'))
                        ->from('#__joomailermailchimpintegration_campaigns')
                        ->where($this->db->qn('creation_date') . ' = ' . $this->db->q($c));
                    $cName = $this->db->setQuery($query)->loadResult();
                    $cName = str_replace(' ', '_', $cName);
                    $cName = htmlentities($cName);

                    if ((JFile::exists($path . $cName . '.html') && !JFile::delete($path . $cName . '.html')) ||
                        (JFile::exists($path . $cName . '.txt') && !JFile::delete($path . $cName . '.txt')) ){
                        $this->app->enqueueMessage(JText::_('JM_DELETE_FAILED'), 'error');
                        $error = true;
                    } else {
                        $query = $this->db->getQuery(true)
                            ->delete('#__joomailermailchimpintegration_campaigns')
                            ->where($this->db->qn('creation_date') . ' = ' . $this->db->q($c));
                        $this->db->setQuery($query)->execute();
                    }
                }
            } else {
                foreach ($cid as $c) {
                    try {
                        $this->getModel('campaignlist')->getMcObject()->campaigns(array('campaign_id' => $c), 'DELETE');
                    } catch (Exception $e) {
                        $this->app->enqueueMessage($e->errorMessage(), 'error');
                        $error = true;
                    }
                }
            }
        }

        if ($error == false) {
            $this->getModel('main')->purgeCache();
            $msg = ($status == 'save') ? JText::_('JM_DRAFT_DELETED') : JText::_('JM_CAMPAIGNS_DELETED');
            $this->app->enqueueMessage($msg);
        }

        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=campaignlist&filter_status=' . $status);
    }

    public function create() {
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=create');
    }

    public function cancel() {
        $link = 'index.php?option=com_joomailermailchimpintegration&view=campaignlist&filter_status='
            . $this->input->getString('filter_status', 'sent');
        $this->app->redirect($link);
    }
}
