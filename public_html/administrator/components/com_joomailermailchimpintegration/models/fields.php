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

class joomailermailchimpintegrationModelFields extends jmModel {

    public function __construct($config = array()) {
        parent::__construct($config);
        jimport('joomla.filesystem.file');
        jimport('joomla.application.component.helper');
    }

    /**
     * Get merge fields for a particular list
     * @param $listId
     * @return array
     */
    public function getMergeFields($listId = null) {
        if (!$listId) {
            $listId = $this->input->getString('listId');
        }

        $cacheGroup = 'joomlamailerMisc';
        $cacheID = 'ListsMergeFields_' . $listId;
        if (!$this->caching || !$this->cache($cacheGroup)->get($cacheID, $cacheGroup)) {
            $data = $this->getMcObject()->listMergeFields($listId);

            // remove mandatory fields as these are not supposed to be altered via the API
            if ($data['total_items'] > 0) {
                foreach ($data['merge_fields'] as $index => $merge) {
                    if (in_array($merge['tag'], array('FNAME', 'LNAME', 'EMAIL'))) {
                        unset($data['merge_fields'][$index]);
                        $data['total_items'] -= 1;
                    }
                }
            }

            if ($this->caching) {
                $this->cache($cacheGroup)->store(json_encode($data), $cacheID, $cacheGroup);
            } else {
                return $data;
            }
        }

        return json_decode($this->cache($cacheGroup)->get($cacheID, $cacheGroup), true);
    }

    /**
     * Get merge fields for all lists
     * @return array
     */
    public function getMergeFieldsAll() {
        $result = array();
        $lists = $this->getModel('lists')->getLists();

        if ($lists['total_items'] > 0) {
            foreach ($lists['lists'] as $list) {
                $mergeFields = $this->getMergeFields($list['id']);
                if (!empty($mergeFields['total_items'])) {
                    $result[$list['name']] = $mergeFields;
                }
            }
        }

        return $result;
    }

    public function getCBfields() {
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_comprofiler/comprofiler.xml')) {
            return false;
        }
        $cHelper = JComponentHelper::getComponent('com_comprofiler', true);
        if (!$cHelper->enabled) {
            return false;
        }

        $query = $this->db->getQuery(true)
            ->select('*')
            ->from($this->db->qn('#__comprofiler_fields'))
            ->where($this->db->qn('published') . ' = ' . $this->db->q(1))
            ->where($this->db->qn('type') . ' IN (' . implode(',', $this->db->q(array(
                'predefined',
                'checkbox',
                //'multicheckbox',
                'select',
                //'multiselect',
                'radio',
                'text',
                'textarea',
                'datetime',
                'date'
            ))) . ')')
            ->order($this->db->qn('name'));
        $fields = $this->db->setQuery($query)->loadObjectList();

        return ($fields) ? $fields : false;
    }

    public function getJSfields() {
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_community/community.xml')) {
            return false;
        }
        $cHelper = JComponentHelper::getComponent('com_community', true);
        if (!$cHelper->enabled) {
            return false;
        }

        $query = $this->db->getQuery(true)
            ->select('*')
            ->from($this->db->qn('#__community_fields'))
            ->where($this->db->qn('published') . ' = ' . $this->db->q(1))
            ->where($this->db->qn('type') . ' IN (' . implode(',', $this->db->q(array(
                'country',
                'select',
                'gender',
                'radio',
                'date',
                'birthdate',
                'textarea',
                'text',
                'url'
            ))) . ')')

            ->order($this->db->qn('name'));
        $fields = $this->db->setQuery($query)->loadObjectList();

        return ($fields) ? $fields : false;
    }

    public function getVMfields() {
        if (!JFile::exists(JPATH_ADMINISTRATOR . '/components/com_virtuemart/virtuemart.xml')) {
            return false;
        }
        $cHelper = JComponentHelper::getComponent('com_virtuemart', true);
        if (!$cHelper->enabled) {
            return false;
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->qn(array('virtuemart_userfield_id', 'name'), array('id', 'name')))
            ->from($this->db->qn('#__virtuemart_userfields'))
            ->where($this->db->qn('published') . ' = ' . $this->db->q(1))
            ->where($this->db->qn('registration') . ' = ' . $this->db->q(1))
            ->where($this->db->qn('type') . ' NOT IN (' . implode(',', $this->db->q(array(
                'delimiter',
                'password',
                'multiselect',
                'checkbox',
                'multicheckbox',
                'textarea',
                'text'
            ))) . ')')
            ->order($this->db->qn('ordering'));
        $fields = $this->db->setQuery($query)->loadObjectList();

        return ($fields) ? $fields : false;
    }
}
