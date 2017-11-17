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

class joomailermailchimpintegrationControllerMain extends joomailermailchimpintegrationController {

    public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function save() {
        $this->session->clear('MCping');

        $MCapi = trim($this->input->getString('MCapi'));
        if (!$MCapi) {
            $this->app->enqueueMessage(JText::_('JM_INVALID_API_CLIENT_ID'), 'error');
        } else {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true)
                ->select($db->qn('params'))
                ->from($db->qn('#__extensions'))
                ->where($db->qn('element') . ' = ' . $db->q('com_joomailermailchimpintegration'));
            $db->setQuery($query);
            $parameters = $db->loadResult();

            $parameters = json_decode($parameters);
            $parameters->params->MCapi = $MCapi;
            $parameters = json_encode($parameters);

            $query = $db->getQuery(true);
            $query->update('#__extensions')
                ->set($db->qn('params') . ' = ' . $db->q($parameters))
                ->where($db->qn('element') . ' = ' . $db->q('com_joomailermailchimpintegration'));
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (Exception $e) {
                $this->app->enqueueMessage('Database error: ' . $e->getMessage(), 'error');
                $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=main');
            }

            // purge cache
            $this->getModel('main')->purgeCache();
        }

        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=main');
    }

    public function copyCampaign() {
        $cid = $this->input->getString('cid', '', 'post', 'string');
        $db = JFactory::getDBO();
        $query = $db->getQuery(true)
            ->select($db->qn('cdata'))
            ->from($db->qn('#__joomailermailchimpintegration_campaigns'))
            ->where($db->qn('cid') . ' = ' . $db->q($cid));
        $db->setQuery($query);
        $cdata = $db->loadResult();

        if (!$cdata) {
            $this->app->enqueueMessage(JText::_('JM_UNABLE_TO_COPY_CAMPAIGN'), 'error');
            $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=main');
        }

        $cdata = json_decode($cdata, true);
        if (count($cdata)) {
            foreach ($cdata as $k => $v){
                $this->input->set($k, $v);
            }
        }
        $this->input->set('cid', $cid);
        $this->input->set('view', 'create');
        $this->input->set('layout', 'default');
        $this->input->set('action', 'copy');
        $this->input->set('hidemainmenu', 0);
        $this->input->set('offset', 0);

        parent::display();
    }

    public function edit() {
        $cid =  $this->input->getString('campaign', '', 'post', 'string');
        $db = JFactory::getDBO();
        $query = $db->getQuery(true)
            ->select($db->qn(array('cdata', 'folder_id')))
            ->from($db->qn('#__joomailermailchimpintegration_campaigns'))
            ->where($db->qn('creation_date') . ' = ' . $db->q($cid));
        $db->setQuery($query);
        $result = $db->loadObject();
        $cdata = json_decode($result->cdata);

        $this->input->set('cid', $cid);
        foreach ($cdata as $k => $v) {
            $this->input->set($k, $v);
        }
        $this->input->set('folder_id', $result->folder_id);
        $this->input->set('view', 'create');
        $this->input->set('action', 'edit');
        $this->input->set('layout', 'default');
        $this->input->set('hidemainmenu', 0);
        $this->input->set('offset', 0);

        parent::display();
    }

    public function send() {
        $cid = $this->input->getString('campaign', '', 'post', 'string');
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=send&campaign=' . $cid);
    }

    public function archive() {
        $cid = $this->input->getString('cid', '', 'post', 'string');
        $this->app->enqueueMessage('Campaign archived: ' . $cid);
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=main');
    }

    public function templates() {
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=templates');
    }

    public function extensions() {
        $this->app->redirect('index.php?option=com_joomailermailchimpintegration&view=extensions');
    }

    public function hideSetupInfo() {
        $data = new stdClass();
        $data->type = 'setup_info';
        $data->value = 1;
        $db = JFactory::getDBO();
        $db->insertObject('#__joomailermailchimpintegration_misc', $data);

        echo json_encode(array('success' => 1));
    }

    public function showSetupInfo() {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true)
            ->delete($db->qn('#__joomailermailchimpintegration_misc'))
            ->where($db->qn('type') . ' = ' . $db->q('setup_info'));
        $db->setQuery($query);
        $db->execute();

        echo json_encode(array('success' => 1));
    }
}
