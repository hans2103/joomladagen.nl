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

class joomailermailchimpintegrationModelSend extends jmModel {

    public function getClientDetails() {
        return $this->getModel('main')->getClientDetails();
    }

    public function getDrafts() {
        $query = $this->db->getQuery(true)
            ->select('*')
            ->from('#__joomailermailchimpintegration_campaigns')
            ->where($this->db->qn('sent') . ' != ' . $this->db->q(2))
            ->order($this->db->qn('creation_date') . ' DESC');

        return $this->db->setQuery($query)->loadObjectList();
    }

    public function getLists() {
        return $this->getModel('lists')->getLists();
    }

    public function getSentCampaigns() {
        return $this->getModel('campaigns')->getCampaigns(array('status' => 'sent'));
    }

    public function getInterestCategories($listId, $categoryId = '') {
        return $this->getMcObject()->listInterestCategories($listId, $categoryId);
    }

    public function getMergeFields($listId){
        return $this->getMcObject()->listMergeFields($listId);
    }

    /**
    * getAecAmbraVm
    *
    * check if either extension is installed: AEC, Ambra subscriptions, VirtueMart
    */
    public function getAecAmbraVm(){
        if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_acctexp/admin.acctexp.php')
            || JFile::exists(JPATH_ADMINISTRATOR . '/components/com_ambrasubs/ambrasubs.php')
            || JFile::exists(JPATH_ADMINISTRATOR . '/components/com_virtuemart/admin.virtuemart.php')){
            return true;
        } else {
            return false;
        }
    }

}
