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

class joomailermailchimpintegrationModelMain extends jmModel {

    public $cacheGroup = 'joomlamailerMisc';

    public function __construct() {
        parent::__construct();
    }

    public function setupInfo() {
        $query = $this->db->getQuery(true)
            ->select($this->db->qn('value'))
            ->from($this->db->qn('#__joomailermailchimpintegration_misc'))
            ->where($this->db->qn('type') . ' = ' . $this->db->q('setup_info'));
        $showInfo = $this->db->setQuery($query)->loadResult();

        $output = '<div id="setupInfo"' . ($showInfo ? ' style="display:none;"' : '') . '>
            <script type="text/javascript">var baseUrl = "' . JURI::base() . '";</script>
            <div class="alert alert-info">
                <div id="hideSetupInfo">' . JText::_('JM_HIDE') . '</div>
                <p>' . JText::_('JM_SETUP_INFO') . '</p>
            </div>
            </div>';

        return $output;
    }

    public function getDrafts() {
        $query = $this->db->getQuery(true)
            ->select('*')
            ->from($this->db->qn('#__joomailermailchimpintegration_campaigns'))
            ->where($this->db->qn('sent') . ' = ' . $this->db->q(0))
            ->order($this->db->qn('creation_date') . ' DESC');

        return $this->db->setQuery($query, 0, 5)->loadObjectList();
    }

    public function getMailChimpDataCenter() {
        return $this->getMcObject()->getDc();
    }

    public function getClientDetails() {
        $cacheID = 'AccountDetails';
        if (!$this->caching || !$this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup)) {
            $data = isset($_SESSION['mcAccountData']) ?
                $_SESSION['mcAccountData'] : $this->getMcObject()->getAccountDetails();

            if ($this->caching) {
                $this->cache($this->cacheGroup)->store(json_encode($data), $cacheID, $this->cacheGroup);
            } else {
                return $data;
            }
        }

        return json_decode($this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup));
    }

    public function getCampaigns() {
        return $this->getModel('campaigns')->getCampaigns(array('status' => 'sent,paused'), 5);
    }

    public function getReport($campaignId) {
        return $this->getModel('campaigns')->getReport($campaignId);
    }

    public function getMcBlog() {
        $cacheID = 'McBlog';
        if (!$this->caching || !$this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup)) {
            $data = @file_get_contents('http://www.mailchimp.com/blog/feed');
            if ($this->caching) {
                $this->cache($this->cacheGroup)->store(json_encode($data), $cacheID, $this->cacheGroup);
            } else {
                return simplexml_load_string($data);
            }
        }

        return simplexml_load_string(json_decode($this->cache($this->cacheGroup)->get($cacheID, $this->cacheGroup)));
    }

    public function emptyCache($cacheGroup) {
        $this->cache($cacheGroup)->clean($cacheGroup);
    }

    public function purgeCache() {
        $cacheGroups = array(
            'joomlamailerMisc',
            'joomlamailerReports'
        );

        foreach ($cacheGroups as $group) {
            $this->emptyCache($group);
        }
    }
}
