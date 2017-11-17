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

class joomailermailchimpintegrationModelCampaignlist extends jmModel {

    private $cacheGroup = 'joomlamailerMisc';
    private static $total;

    public function __construct($config = array()) {
        parent::__construct($config);
        $this->app = JFactory::getApplication();
    }

    public function getCampaigns() {
        $params = JComponentHelper::getParams('com_joomailermailchimpintegration');
        $archiveDir = $params->get('params.archiveDir', '/administrator/components/com_joomailermailchimpintegration/archive');
        $filter = $this->input->getString('filter_status', 'sent');
        $folder_id = $this->input->getString('folder_id', 0);

        if ($filter == 'save') {
            $query = $this->db->getQuery(true);
            $where = array();
            $where[] = $this->db->qn('sent') . ' != ' . $this->db->q(1);
            if ($folder_id) {
                $where[] = $this->db->qn('folder_id') . ' = ' . $this->db->q($folder_id);
            }
            $query->select('SQL_CALC_FOUND_ROWS *, FROM_UNIXTIME(creation_date) AS date')
                ->from($this->db->qn('#__joomailermailchimpintegration_campaigns'))
                ->where($where)
                ->order($this->db->q('creation_date') . ' DESC');
            $data = $this->db->setQuery($query)->loadObjectList();

            $campaigns = array();
            $i = 0;
            if ($data) {
                foreach($data as $dat) {
                    $campaigns[$i] = [
                        'id'          => $dat->creation_date,
                        'cid'         => $dat->cid,
                        'settings'    => [
                            'title'        => $dat->name,
                            'subject_line' => $dat->subject
                        ],
                        'creation_date' => $dat->creation_date
                    ];

                    $campaignNameSafe = JApplicationHelper::stringURLSafe($dat->name);
                    if (JFile::exists(JPATH_SITE . '/' . (substr($archiveDir, 1)) . '/' . $campaignNameSafe . '.html')) {
                        $campaigns[$i]['archive_url'] = JURI::root() . substr($archiveDir, 1) . '/' . $campaignNameSafe . '.html';
                    } else {
                        $campaigns[$i]['archive_url'] = JURI::root() . substr($archiveDir, 1) . '/' . $campaignNameSafe . '.txt';
                    }
                    $i++;
                }

                $query = 'SELECT FOUND_ROWS()';
                joomailermailchimpintegrationModelCampaignlist::$total = $this->db->setQuery($query)->loadResult();
            } else {
                $campaigns = array();
                joomailermailchimpintegrationModelCampaignlist::$total = 0;
            }
        } else {
            $filters = array();
            $filters['status'] = $filter;
            if ($folder_id != '') {
                $filters['folder_id'] = $folder_id;
            }
            $limit = $this->app->getUserStateFromRequest('campaignlist_limit', 'campaignlist_limit', $this->app->getCfg('list_limit'), 'int');
            $limitstart = $this->app->getUserStateFromRequest('campaignlist_limitstart', 'campaignlist_limitstart', 0, 'int');

            $campaigns = $this->getModel('campaigns')->getCampaigns($filters, $limit, $limitstart);
            joomailermailchimpintegrationModelCampaignlist::$total = $campaigns['total_items'];
            $campaigns = $campaigns['campaigns'];
        }

        // get report data for each campaign
        foreach ($campaigns as $index => $campaign) {
            if (isset($campaign['status']) && $campaign['status'] == 'schedule') {
                $campaigns[$index]['emails_sent'] = '-';
                $campaigns[$index]['report']['unique_opens'] = '-';
                $campaigns[$index]['report']['clicks'] = '-';
            } else if (isset($campaign['type']) && $campaign['type'] == 'auto') {
                if ($campaign['status'] == 'paused') {
                    $campaigns[$index]['status'] = 'JM_AUTORESPONDER_PAUSED';
                } else {
                    $campaigns[$index]['status'] = 'JM_AUTORESPONDER';
                }
                $campaigns[$index]['send_time'] = JText::_('JM_VARIABLE');

                $campaigns[$index]['report'] = $this->getReport($campaign['cid']);
            } else if ($filter != 'save') {
                $campaigns[$index]['report'] = json_decode(json_encode($this->getReport($campaign['id'])), true);
            }
        }

        return $campaigns;
    }

    public function getReport($campaignId) {
        return $this->getModel('campaigns')->getReport($campaignId);
    }

    public function getPagination() {
        jimport('joomla.html.pagination');
        $limit = $this->app->getUserStateFromRequest('campaignlist_limit', 'campaignlist_limit', $this->app->getCfg('list_limit'), 'int');
        $limitstart = $this->app->getUserStateFromRequest('campaignlist_limitstart', 'campaignlist_limitstart', 0, 'int');

        return new JPagination(joomailermailchimpintegrationModelCampaignlist::$total, $limitstart, $limit, 'campaignlist_');
    }

    /**
     * Get all existing campaign folders
     * @return json
     */
    public function getFolders() {
        $cacheGroup = 'joomlamailerFolders';
        $cacheID = 'CampaignFolders';
        if (!$this->caching || !$this->cache($cacheGroup)->get($cacheID, $cacheGroup)) {
            $data = $this->getMcObject()->campaignFolders();
            if ($this->caching) {
                $this->cache($cacheGroup)->store(json_encode($data), $cacheID, $cacheGroup);
            } else {
                return $data;
            }
        }
        $folders = json_decode($this->cache($cacheGroup)->get($cacheID, $cacheGroup), true);

        return $folders;
    }

    /**
     * Create new campaign folder unless one with the same name exists.
     * @param $folderName
     * @return int
     */
    public function createFolder($folderName) {
        $folderName = trim($folderName);
        if (!$folderName) {
            return 0;
        }

        // check if a folder with the given name exists
        $folders = $this->getFolders();
        if (!empty($folders['total_items'])) {
            foreach ($folders['folders'] as $folder) {
                if ($folder['name'] == $folderName) {
                    return $folder['id'];
                }
            }
        }

        // create new folder
        $folder = $this->getMcObject()->campaignFolders('POST', null, $folderName);

        // clear folder cache
        $this->getModel('main')->emptyCache('joomlamailerFolders');

        return $folder['id'];
    }
}
