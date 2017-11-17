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

require_once(JPATH_ADMINISTRATOR . '/components/com_joomailermailchimpintegration/models/jmModel.php');

class joomailermailchimpintegrationModelGroups extends jmModel {

    public function __construct() {
        jimport('joomla.filesystem.file');
        parent::__construct();
    }

    /**
     * Get interest categories (groups) for a given list
     * @param $listId
     * @param string $categoryId
     * @return array
     */
    public function getListInterestCategories($listId, $categoryId = '') {
        $cacheGroup = 'joomlamailerMisc';
        $cacheID = 'ListsInterestCategories_' . $listId;
        if ($categoryId) {
            $cacheID .= '_' . $categoryId;
        }
        if (!$this->caching || !$this->cache($cacheGroup)->get($cacheID, $cacheGroup)) {
            $data = $this->getMcObject()->listInterestCategories($listId, $categoryId);
            if ($this->caching) {
                $this->cache($cacheGroup)->store(json_encode($data), $cacheID, $cacheGroup);
            } else {
                return $data;
            }
        }

        return json_decode($this->cache($cacheGroup)->get($cacheID, $cacheGroup), true);
    }

    public function getCBfields() {
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/comprofiler.xml')) {
            return false;
        }
        jimport('joomla.application.component.helper');
        $cHelper = JComponentHelper::getComponent('com_comprofiler', true);
        if (!$cHelper->enabled) {
            return false;
        }

        $query = $this->db->getQuery(true)
            ->select('*')
            ->from('#__comprofiler_fields')
            ->where($this->db->qn('published') . ' = ' . $this->db->q(1))
            ->where($this->db->qn('type') . ' IN (' . implode(',', $this->db->q(array('checkbox', 'multicheckbox',
            'select', 'multiselect', 'radio'))) . ')');
        $fields = $this->db->setQuery($query)->loadObjectList();

        return ($fields) ? $fields : false;
    }

    public function getJSfields() {
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_community/community.xml')) {
            return false;
        }
        jimport('joomla.application.component.helper');
        $cHelper = JComponentHelper::getComponent('com_community', true);
        if (!$cHelper->enabled) {
            return false;
        }

        $query = $this->db->getQuery(true)
            ->select('*')
            ->from('#__community_fields')
            ->where($this->db->qn('published') . ' = ' . $this->db->q(1))
            ->where($this->db->qn('type') . ' IN ('
                . $this->db->q('checkbox') . ','
                . $this->db->q('select') . ','
                . $this->db->q('list') . ','
                . $this->db->q('gender') . ','
                . $this->db->q('radio') . ')');
        $fields = $this->db->setQuery($query)->loadObjectList();

        return ($fields) ? $fields : false;
    }

    public function getVMfields() {
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_virtuemart/virtuemart.xml')) {
            return false;
        }

        jimport('joomla.application.component.helper');
        $cHelper = JComponentHelper::getComponent('com_virtuemart', true);
        if (!$cHelper->enabled){
            return false;
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('virtuemart_userfield_id', 'name'), array('id', 'name')))
            ->from('#__virtuemart_userfields')
            ->where($this->db->qn('published') . ' = ' . $this->db->q(1))
            ->where($this->db->qn('registration') . ' = ' . $this->db->q(1))
            ->where($this->db->qn('type') . ' NOT IN ('
                . $this->db->q('delimiter') . ','
                . $this->db->q('password') . ','
                . $this->db->q('emailaddress') . ','
                . $this->db->q('text') . ','
                . $this->db->q('euvatid') . ','
                . $this->db->q('editorta') . ','
                . $this->db->q('textarea') . ','
                . $this->db->q('webaddress') . ','
                . $this->db->q('age_verification') . ')')
            ->order('ordering');
        $fields = $this->db->setQuery($query)->loadObjectList();

        return ($fields) ? $fields : false;
    }
}
