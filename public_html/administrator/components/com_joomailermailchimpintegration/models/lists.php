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

if (!class_exists('jmModel')) {
    require_once('jmModel.php');
}

class joomailermailchimpintegrationModelLists extends jmModel {

    public function getLists() {
        $cacheGroup = 'joomlamailerMisc';
        $cacheID = 'Lists';
        if (!$this->caching || !$this->cache($cacheGroup)->get($cacheID, $cacheGroup)) {
            $data = $this->getMcObject()->lists();
            if ($this->caching) {
                $this->cache($cacheGroup)->store(json_encode($data), $cacheID, $cacheGroup);
            } else {
                return $data;
            }
        }

        return json_decode($this->cache($cacheGroup)->get($cacheID, $cacheGroup), true);
    }

    /**
     * Get a filtered set of lists. Possible filters include:
     * count, offset, before_date_created, since_date_created, before_campaign_last_sent, since_campaign_last_sent, email
     */
    public function getListsWithFilter($params = array()) {
        return $this->getMcObject()->lists($params);
    }

    public function getListMergeFields($listId) {
        return $this->getModel('fields')->getListMergeFields($listId);
    }

    public function getListInterestCategories($listId, $categoryId = '') {
        return $this->getModel('groups')->getListInterestCategories($listId, $categoryId);
    }

    public function getGrowthHistory($listId){
        return $this->getMcObject()->listGrowthHistory($listId);
    }

    public function getListDetails($listId) {
        $lists = $this->getLists();

        if (!empty($lists['total_items'])) {
            foreach ($lists['lists'] as $list) {
                if ($list['id'] == $listId) {
                    return $list;
                }
            }
        }

        return false;
    }

    /*
    public function getAssociatedDrafts($listId) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select($db->qn('name'))
            ->from($db->qn('#__joomailermailchimpintegration_campaigns'))
            ->where($db->qn('list_id') . ' = ' . $db->q($listId));
        $db->setQuery($query);
        $drafts = $db->loadObjectList();

        $listNames = array();
        foreach($drafts as $draft){
            $listNames[] = $draft->name;
        }

        return implode(', ', $listNames);
    }
    */
}
